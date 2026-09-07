<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;


class SyncZraInvoices extends Command
{
    protected $signature = 'zra:sync-invoices
                            {--limit=50 : Max number of invoices to sync in a single run (safety cap)}
                            {--dry-run : Fetch invoice JSON via SP but do not POST to DBInvoices or call the response SP}';

    protected $description = 'Sync invoices with DocStatus = Pending ZRA Int. to DBInvoices for ZRA smart invoicing';

    /** Cache keys for the DBInvoices bearer token — shared with SyncZraItems, so a login
     *  from either command benefits the other and avoids duplicate logins. */
    protected string $tokenCacheKey = 'dbinvoices_token';
    protected string $clientIdCacheKey = 'dbinvoices_client_id';

    public function handle(): int
{
    $limit = (int) $this->option('limit');
    $dryRun = (bool) $this->option('dry-run');

    $this->info("Starting ZRA invoice sync (limit={$limit}, dry-run=" . ($dryRun ? 'yes' : 'no') . ')');
    Log::info('zra:sync-invoices starting', ['limit' => $limit, 'dry_run' => $dryRun]);

    $invoiceIds = $this->fetchPendingInvoiceIds($limit);

    if (empty($invoiceIds)) {
        $this->info('No invoices with DocStatus = Pending ZRA Int. found.');
        Log::info('zra:sync-invoices: no pending invoices found');
        return self::SUCCESS;
    }

    $this->info('Found ' . count($invoiceIds) . ' pending invoice(s).');
    Log::info('zra:sync-invoices: pending invoice ids fetched', ['count' => count($invoiceIds), 'ids' => $invoiceIds]);

    $synced = 0;
    $failed = 0;

    foreach ($invoiceIds as $i => $invoiceId) {
        $generated = $this->fetchInvoiceSubmissionJson($invoiceId);

        if ($generated === null) {
            // Data-specific failure on this one invoice (e.g. sp_ZRA_ARIntegration
            // returned 'Failed', or the JSON it produced didn't parse). Other invoices
            // in this batch are independent records, so we log and move on rather than
            // aborting the whole run.
            $failed++;
            continue;
        }

        $invoiceRef = $generated['submissionJson']['cisInvcNo'] ?? 'unknown';

        $this->line("[{$i}] invoice_id={$invoiceId} cisInvcNo={$invoiceRef}");
        Log::info('zra:sync-invoices: generated submission JSON', [
            'invoice_id' => $invoiceId,
            'cisInvcNo' => $invoiceRef,
            'submission_json' => $generated['submissionJson'],
        ]);

        if ($dryRun) {
            $this->line('  (dry-run) skipping API push and response SP');
            Log::info('zra:sync-invoices: dry-run, skipping push and response SP', [
                'invoice_id' => $invoiceId,
            ]);
            continue;
        }

        try {
            $response = $this->pushInvoiceToDbInvoices($generated['submissionJson']);
        } catch (\Throwable $e) {
            $failed++;
            Log::error('ZRA invoice sync: DBInvoices push failed', [
                'invoice_id' => $invoiceId,
                'cisInvcNo' => $invoiceRef,
                'error' => $e->getMessage(),
            ]);
            $this->error("  Failed to push {$invoiceRef}: {$e->getMessage()}");
            $this->markInvoiceFailed($invoiceId, $e->getMessage());
            // Push failures are more likely systemic (auth, network, DBInvoices down)
            // than data-specific, so stop the whole run rather than burning through
            // the rest of the batch against a broken connection.
            break;
        }

        Log::info('zra:sync-invoices: pushed invoice to DBInvoices', [
            'invoice_id' => $invoiceId,
            'cisInvcNo' => $invoiceRef,
            'dbinvoices_id' => $response['data']['id'] ?? null,
        ]);

        $updateResult = $this->markInvoiceSynced($response);

        // Treat @Remark = 'Success' as authoritative — it's what actually came back
        // clean here. @Status vocabulary from this SP isn't fully confirmed yet
        // (seen: 'Completed' on ZMR2600005, 'Subitted' — likely a typo for
        // 'Submitted' — on ZMR2600003), so it's kept only as a secondary signal.
        $statusNormalized = strtolower(trim((string) $updateResult['status']));
        $remarkNormalized = strtolower(trim((string) $updateResult['remark']));

        $looksSuccessful = $remarkNormalized === 'success'
            || in_array($statusNormalized, ['completed', 'submitted', 'subitted', 'success', 'sucess', 'succes'], true);

        if (! $looksSuccessful) {
            $failed++;
            Log::error('ZRA invoice sync: sp_ZRA_ARIntegration_Response failed', [
                'invoice_id' => $invoiceId,
                'cisInvcNo' => $invoiceRef,
                'status' => $updateResult['status'],
                'remark' => $updateResult['remark'],
            ]);
            $this->error("  Response SP failed for {$invoiceRef}: {$updateResult['remark']}");
            $this->markInvoiceFailed($invoiceId, $updateResult['remark']);
            continue;
        }

        $synced++;
        $this->info("  Synced {$invoiceRef} -> DBInvoices id={$response['data']['id']}");
        Log::info('zra:sync-invoices: marked invoice synced', [
            'invoice_id' => $invoiceId,
            'cisInvcNo' => $invoiceRef,
            'dbinvoices_id' => $response['data']['id'] ?? null,
        ]);
    }

    $this->info("Done. Synced={$synced} Failed={$failed}");
    Log::info('zra:sync-invoices finished', ['synced' => $synced, 'failed' => $failed]);

    return $failed > 0 ? self::FAILURE : self::SUCCESS;
}

    /**
     * Fetches a batch of invoice IDs (as strings) with DocStatus = 'Pending ZRA Int.'
     */
    protected function fetchPendingInvoiceIds(int $limit): array
    {
        $rows = DB::select(
            'SELECT TOP (?) Id FROM Bill_BillingDocumentHeader WHERE DocStatus = ? ORDER BY DocNo',
            [$limit, 'Pending RA Int.']
        );

        return array_map(fn ($row) => (string) $row->Id, $rows);
    }

    /**
     * Calls sp_ZRA_ARIntegration for a single invoice ID, returns the decoded
     * submission JSON, or null if the SP failed or returned unusable JSON.
     */
    protected function fetchInvoiceSubmissionJson(string $invoiceId): ?array
    {
        $pdo = DB::connection()->getPdo();

        $stmt = $pdo->prepare('
            SET NOCOUNT ON;
            EXEC dbo.sp_ZRA_ARIntegration
                @InvoiceID = ?,
                @Status = ?,
                @Remark = ?,
                @SubmissionJson = ?
        ');

        $status = null;
        $remark = null;
        $submissionJson = null;

        $stmt->bindParam(1, $invoiceId, \PDO::PARAM_STR); // uniqueidentifier passed as string
        $stmt->bindParam(2, $status, \PDO::PARAM_STR | \PDO::PARAM_INPUT_OUTPUT, 20);
        $stmt->bindParam(3, $remark, \PDO::PARAM_STR | \PDO::PARAM_INPUT_OUTPUT, 200);
        // ODBC Driver 17 caps nvarchar OUTPUT params at 4000 chars (see earlier debugging
        // on sp_ZRA_ItemJson — anything above throws SQLSTATE[HY104] Invalid precision).
        // A single item's JSON stayed well under this. An invoice's submission JSON embeds
        // a full customer object AND an itemList array per charge line, so a multi-line
        // invoice is much more likely to approach or exceed 4000 chars. If that happens,
        // SQL Server silently truncates the output rather than erroring at the DB layer —
        // but our json_decode() + json_last_error() check below will catch the resulting
        // malformed JSON and log it rather than pushing corrupt data to DBInvoices.
        // TEST THIS against your largest real invoice (most charge lines) before trusting
        // this in production, and watch the logs for "returned invalid JSON" entries.
        $stmt->bindParam(4, $submissionJson, \PDO::PARAM_STR | \PDO::PARAM_INPUT_OUTPUT, 4000);

        $stmt->execute();

        do {
            // drain rowsets
        } while ($stmt->nextRowset());

        // NOTE: success here is the literal string 'Pending ZRA Int.', not 'Success'.
        // Only 'Failed' indicates an actual error from this SP.
        if (strcasecmp(trim((string) $status), 'Failed') === 0) {
            Log::error('sp_ZRA_ARIntegration returned Failed', [
                'invoice_id' => $invoiceId,
                'status' => $status,
                'remark' => $remark,
            ]);
            return null;
        }

        if (empty($submissionJson)) {
            Log::warning('sp_ZRA_ARIntegration returned no SubmissionJson', [
                'invoice_id' => $invoiceId,
                'status' => $status,
                'remark' => $remark,
            ]);
            return null;
        }

        $decoded = json_decode($submissionJson, true);

        if (json_last_error() !== JSON_ERROR_NONE || empty($decoded)) {
            Log::error('sp_ZRA_ARIntegration returned invalid/truncated JSON', [
                'invoice_id' => $invoiceId,
                'raw' => $submissionJson,
                'json_error' => json_last_error_msg(),
            ]);
            return null;
        }

        return [
            'status' => $status,
            'remark' => $remark,
            'submissionJson' => $decoded,
        ];
    }

    /**
     * POST the invoice submission JSON to DBInvoices /api/invoices/save, returns the
     * decoded response body. Forwards the SP's output directly (no manual field
     * re-listing) — re-listing fields by hand is what caused item_code to get silently
     * dropped in the items sync command.
     */
    protected function pushInvoiceToDbInvoices(array $submissionJson): array
    {
        $baseUrl = config('services.dbinvoices.base_url');

        Log::info('zra:sync-invoices: sending payload to DBInvoices invoices/save', [
            'url' => "{$baseUrl}/api/invoices/save",
            'payload' => $submissionJson,
        ]);

        $response = Http::withToken($this->getToken())
            ->withHeaders(['X-Client-Id' => $this->getClientId()])
            ->acceptJson()
            ->post("{$baseUrl}/api/invoices/save", $submissionJson);

        Log::info('zra:sync-invoices: raw response from DBInvoices invoices/save', [
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
                ->post("{$baseUrl}/api/invoices/save", $submissionJson);

            Log::info('zra:sync-invoices: raw response from DBInvoices invoices/save (after token refresh)', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
        }

        if (! $response->successful()) {
            throw new \RuntimeException(
                "DBInvoices invoices/save returned HTTP {$response->status()}: {$response->body()}"
            );
        }

        $body = $response->json();

        if (empty($body['success']) || empty($body['data']['id'])) {
            throw new \RuntimeException(
                "DBInvoices invoices/save did not return a usable id: {$response->body()}"
            );
        }

        return $body;
    }

    /**
     * Wraps the DBInvoices response and calls sp_ZRA_ARIntegration_Response to mark
     * the invoice as synced.
     *
     * TODO: we only have this SP's signature (@InputJson, @Status OUTPUT, @Remark OUTPUT),
     * not its body — so we don't know exactly what shape it expects @InputJson in, or
     * what field it matches back to Bill_BillingDocumentHeader / Bill_RAIntegrationDetails.
     * Passing the full raw DBInvoices response for now (same pattern as items). If it
     * expects a specific wrapper (e.g. {"data": {...}}) or a different key name, adjust
     * $inputJson below once confirmed — the same way item_code had to be force-corrected
     * for the items flow.
     *
     * @return array{status: string, remark: string}
     */
    protected function markInvoiceSynced(array $dbInvoicesResponse): array
    {
        $inputJson = json_encode($dbInvoicesResponse);

        Log::info('zra:sync-invoices: calling sp_ZRA_ARIntegration_Response', [
            'input_json' => $inputJson,
        ]);

        $pdo = DB::connection()->getPdo();

        $stmt = $pdo->prepare('
            SET NOCOUNT ON;
            EXEC dbo.sp_ZRA_ARIntegration_Response
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
     * Marks an invoice as Failed on Bill_BillingDocumentHeader. Called whenever we
     * can't get it successfully through to DBInvoices — either the HTTP push itself
     * failed, or sp_ZRA_ARIntegration_Response came back with a non-success status.
     * Without this, a failed invoice just stays at 'Pending ZRA Int.' (or whatever
     * DocStatus it was already in) and gets silently re-picked-up (and re-fails) on
     * every subsequent run.
     */
    protected function markInvoiceFailed(string $invoiceId, ?string $remark = null): void
    {
        DB::update(
            'UPDATE Bill_BillingDocumentHeader SET DocStatus = ? WHERE Id = ?',
            ['Failed', $invoiceId]
        );

        Log::warning('zra:sync-invoices: DocStatus set to Failed', [
            'invoice_id' => $invoiceId,
            'remark' => $remark,
        ]);
    }

    /**
     * Returns a cached bearer token, logging in to DBInvoices if needed.
     * Identical to SyncZraItems::getToken() — same cache key, so a login from either
     * command reuses the token rather than logging in twice.
     */
    protected function getToken(): string
    {
        return Cache::remember($this->tokenCacheKey, now()->addMinutes(50), function () {
            $baseUrl = config('services.dbinvoices.base_url');
            $apiKey = config('services.dbinvoices.api_key');

            $response = Http::acceptJson()
                ->post("{$baseUrl}/api/auth/login", ['api_key' => $apiKey]);

            if (! $response->successful() || empty($response->json('data.token'))) {
                throw new \RuntimeException(
                    "DBInvoices login failed: {$response->body()}"
                );
            }

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
            Cache::forget($this->tokenCacheKey);
            $this->getToken();
        }

        return (string) Cache::get($this->clientIdCacheKey);
    }
}
