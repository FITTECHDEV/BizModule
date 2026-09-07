<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * php artisan zra:sync-items
 *
 * Loop:
 *   1. EXEC sp_ZRA_ItemJson            -> get next unsynced charge code as item JSON
 *   2. POST {{baseUrl}}/api/items/save -> register item with DBInvoices
 *   3. EXEC sp_ZRA_ItemJson_Update     -> wrap DBInvoices response, mark charge code as synced
 *   4. Repeat until sp_ZRA_ItemJson returns no OutputJson (or a --limit is hit)
 *
 * Intended to run on a schedule (Task Scheduler on D9/D21, or Laravel scheduler ->everyFiveMinutes())
 */
class SyncZraItems extends Command
{
    protected $signature = 'zra:sync-items
                            {--limit=50 : Max number of items to sync in a single run (safety cap)}
                            {--dry-run : Fetch items from SP but do not POST to DBInvoices or update SP}';

    protected $description = 'Sync unsynced Bill_ChargeCode entries to DBInvoices as ZRA items';

    /** Cache key for the DBInvoices bearer token */
    protected string $tokenCacheKey = 'dbinvoices_token';
    protected string $clientIdCacheKey = 'dbinvoices_client_id';

    public function handle(): int
    {
        $limit = (int) $this->option('limit');
        $dryRun = (bool) $this->option('dry-run');

        $this->info("Starting ZRA item sync (limit={$limit}, dry-run=" . ($dryRun ? 'yes' : 'no') . ')');
        Log::info('zra:sync-items starting', ['limit' => $limit, 'dry_run' => $dryRun]);

        $synced = 0;
        $failed = 0;

        for ($i = 0; $i < $limit; $i++) {
            $item = $this->fetchNextItemJson();

            if ($item === null) {
                $this->info('No more unsynced items. Stopping.');
                Log::info('zra:sync-items: no more unsynced items, stopping', ['iteration' => $i]);
                break;
            }

            $this->line("[{$i}] item_code={$item['item_code']} item_name={$item['item_name']}");
            Log::info('zra:sync-items: fetched item from sp_ZRA_ItemJson', [
                'iteration' => $i,
                'item_code' => $item['item_code'],
                'item_name' => $item['item_name'],
            ]);

            if ($dryRun) {
                $this->line('  (dry-run) skipping API push and SP update');
                Log::info('zra:sync-items: dry-run, skipping push and SP update', [
                    'item_code' => $item['item_code'],
                ]);
                continue;
            }

            try {
                $response = $this->pushItemToDbInvoices($item);
            } catch (\Throwable $e) {
                $failed++;
                Log::error('ZRA item sync: DBInvoices push failed', [
                    'item_code' => $item['item_code'],
                    'error' => $e->getMessage(),
                ]);
                $this->error("  Failed to push {$item['item_code']}: {$e->getMessage()}");
                // Stop the loop on failure so we don't hammer the API or loop forever
                // on the same unsynced record (sp_ZRA_ItemJson always returns the same
                // "Top 1" row until it's marked synced).
                break;
            }

            Log::info('zra:sync-items: pushed item to DBInvoices', [
                'item_code' => $item['item_code'],
                'dbinvoices_id' => $response['data']['id'] ?? null,
            ]);

            // DBInvoices sandbox mode may auto-generate its own item_code
            // (e.g. "SBX-...") regardless of what we sent. sp_ZRA_ItemJson_Update
            // matches on item_code against our local Bill_ChargeCode table, so we
            // must force it back to the code we actually track locally.
            if (($response['data']['item_code'] ?? null) !== $item['item_code']) {
                Log::warning('zra:sync-items: DBInvoices returned a different item_code than sent, overriding with local code', [
                    'sent_item_code' => $item['item_code'],
                    'returned_item_code' => $response['data']['item_code'] ?? null,
                ]);
                $response['data']['item_code'] = $item['item_code'];
            }

            $updateResult = $this->markItemSynced($response);

            if ($updateResult['status'] !== 'Success' && strtolower($updateResult['status']) !== 'sucess') {
                // NOTE: sp_ZRA_ItemJson_Update currently returns the typo 'Sucess'/'Succes'
                // on its success path. Comparing loosely here until the SP is fixed.
                $failed++;
                Log::error('ZRA item sync: local SP update failed', [
                    'item_code' => $item['item_code'],
                    'status' => $updateResult['status'],
                    'remark' => $updateResult['remark'],
                ]);
                $this->error("  SP update failed for {$item['item_code']}: {$updateResult['remark']}");
                break;
            }

            $synced++;
            $this->info("  Synced {$item['item_code']} -> DBInvoices id={$response['data']['id']}");
            Log::info('zra:sync-items: marked item synced locally', [
                'item_code' => $item['item_code'],
                'dbinvoices_id' => $response['data']['id'] ?? null,
            ]);
        }

        $this->info("Done. Synced={$synced} Failed={$failed}");
        Log::info('zra:sync-items finished', ['synced' => $synced, 'failed' => $failed]);

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }

    /**
     * Calls sp_ZRA_ItemJson and returns the decoded item array, or null if nothing to sync.
     */
    protected function fetchNextItemJson(): ?array
    {
        $pdo = DB::connection()->getPdo();

        $stmt = $pdo->prepare('
            SET NOCOUNT ON;
            EXEC dbo.sp_ZRA_ItemJson
                @Status = ?,
                @Remark = ?,
                @OutputJson = ?
        ');

        // SQL Server OUTPUT params via PDO_SQLSRV
        $status = null;
        $remark = null;
        $outputJson = null;

        $stmt->bindParam(1, $status, \PDO::PARAM_STR | \PDO::PARAM_INPUT_OUTPUT, 20);
        $stmt->bindParam(2, $remark, \PDO::PARAM_STR | \PDO::PARAM_INPUT_OUTPUT, 200);
        // ODBC Driver 17 for SQL Server caps nvarchar OUTPUT param length at 4000 —
        // anything higher throws SQLSTATE[HY104] Invalid precision value. The item
        // JSON payload is small (a handful of short fields), so 4000 is comfortable
        // headroom without crossing that boundary.
        $stmt->bindParam(3, $outputJson, \PDO::PARAM_STR | \PDO::PARAM_INPUT_OUTPUT, 4000);

        $stmt->execute();

        // Drain any result sets before the OUTPUT params are reliably populated
        do {
            // no-op, just advancing rowsets
        } while ($stmt->nextRowset());

        if (strcasecmp((string) $status, 'Success') !== 0) {
            Log::warning('sp_ZRA_ItemJson did not return Success', [
                'status' => $status,
                'remark' => $remark,
            ]);
            return null;
        }

        if (empty($outputJson)) {
            Log::info('sp_ZRA_ItemJson returned Success but empty OutputJson (nothing left to sync)', [
                'status' => $status,
                'remark' => $remark,
            ]);
            return null;
        }

        $decoded = json_decode($outputJson, true);

        if (json_last_error() !== JSON_ERROR_NONE || empty($decoded)) {
            Log::error('sp_ZRA_ItemJson returned invalid JSON', ['raw' => $outputJson]);
            return null;
        }

        return $decoded;
    }

    /**
     * POST the item to DBInvoices /api/items/save, returns the decoded response body.
     */
    protected function pushItemToDbInvoices(array $item): array
    {
        $baseUrl = config('services.dbinvoices.base_url');

        // Forward the SP's output directly rather than re-listing fields by hand —
        // manually reconstructing this array is what caused item_code to get
        // silently dropped in an earlier version of this command.
        $payload = $item;

        Log::info('zra:sync-items: sending payload to DBInvoices items/save', [
            'url' => "{$baseUrl}/api/items/save",
            'payload' => $payload,
        ]);

        $response = Http::withToken($this->getToken())
            ->withHeaders(['X-Client-Id' => $this->getClientId()])
            ->acceptJson()
            ->post("{$baseUrl}/api/items/save", $payload);

        Log::info('zra:sync-items: raw response from DBInvoices items/save', [
            'status' => $response->status(),
            'body' => $response->body(),
        ]);

        // Token expired mid-run - refresh once and retry
        if ($response->status() === 401) {
            Cache::forget($this->tokenCacheKey);
            Cache::forget($this->clientIdCacheKey);

            $response = Http::withToken($this->getToken())
                ->withHeaders(['X-Client-Id' => $this->getClientId()])
                ->acceptJson()
                ->post("{$baseUrl}/api/items/save", $payload);

            Log::info('zra:sync-items: raw response from DBInvoices items/save (after token refresh)', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
        }

        if (! $response->successful()) {
            throw new \RuntimeException(
                "DBInvoices items/save returned HTTP {$response->status()}: {$response->body()}"
            );
        }

        $body = $response->json();

        if (empty($body['success']) || empty($body['data']['id'])) {
            throw new \RuntimeException(
                "DBInvoices items/save did not return a usable id: {$response->body()}"
            );
        }

        return $body;
    }

    /**
     * Wraps the DBInvoices response and calls sp_ZRA_ItemJson_Update to mark the
     * local charge code as synced.
     *
     * @return array{status: string, remark: string}
     */
    protected function markItemSynced(array $dbInvoicesResponse): array
    {
        $inputJson = json_encode($dbInvoicesResponse);

        Log::info('zra:sync-items: calling sp_ZRA_ItemJson_Update', [
            'input_json' => $inputJson,
        ]);

        $pdo = DB::connection()->getPdo();

        $stmt = $pdo->prepare('
            SET NOCOUNT ON;
            EXEC dbo.sp_ZRA_ItemJson_Update
                @InputJson = ?,
                @Status = ?,
                @Remark = ?
        ');

        $status = null;
        $remark = null;

        $stmt->bindParam(1, $inputJson, \PDO::PARAM_STR);
        $stmt->bindParam(2, $status, \PDO::PARAM_STR | \PDO::PARAM_INPUT_OUTPUT, 25);
        $stmt->bindParam(3, $remark, \PDO::PARAM_STR | \PDO::PARAM_INPUT_OUTPUT, 200);

        $stmt->execute();

        do {
            // drain rowsets
        } while ($stmt->nextRowset());

        return [
            'status' => (string) $status,
            'remark' => (string) $remark,
        ];
    }

    /**
     * Returns a cached bearer token, logging in to DBInvoices if needed.
     */
    protected function getToken(): string
    {
        return Cache::remember($this->tokenCacheKey, now()->addMinutes(50), function () {
            $baseUrl = config('services.dbinvoices.base_url'); // https://beta.dbinvoices.com
            $apiKey = config('services.dbinvoices.api_key');

            $response = Http::acceptJson()
                ->post("{$baseUrl}/api/auth/login", ['api_key' => $apiKey]);

            if (! $response->successful() || empty($response->json('data.token'))) {
                throw new \RuntimeException(
                    "DBInvoices login failed: {$response->body()}"
                );
            }

            // Cache client id alongside the token so both refresh together
            Cache::put(
                $this->clientIdCacheKey,
                (string) $response->json('data.client_info.id'),
                now()->addMinutes(50)
            );

            return $response->json('data.token');
        });
    }

    protected function getClientId(): string
    {
        if (! Cache::has($this->clientIdCacheKey)) {
            // Force a fresh login to populate both token + client id together
            Cache::forget($this->tokenCacheKey);
            $this->getToken();
        }

        return (string) Cache::get($this->clientIdCacheKey);
    }
}