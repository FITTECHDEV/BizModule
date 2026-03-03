<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SyncCrmQuotations extends Command
{
    // ============================================================
    // USAGE
    //   php artisan crm:sync-quotations                  ← sync all
    //   php artisan crm:sync-quotations --booking=7037   ← single record
    //   php artisan crm:sync-quotations --dry-run        ← preview only
    //   php artisan crm:sync-quotations --limit=100      ← first N records
    // ============================================================

    protected $signature = 'crm:sync-quotations
                            {--limit=      : Cap the number of records processed}
                            {--booking=    : Sync a single booking number}
                            {--dry-run     : Preview without writing to DB}';

    protected $description = 'Migrate quotation records from CRM_Details (MySQL) into Quote_QuotationInfo (SQL Server)';

    // ── Connection names — update if yours differ ───────────────
    private const CRM_CONNECTION = 'pgsql';        // PostgreSQL uat — source
    private const SCM_CONNECTION = 'sqlsrv';     // SQL Server — destination

    // ── Status map: CRM → SCM ───────────────────────────────────
    private const STATUS_MAP = [
        'won'      => 'Won',
        'lost'     => 'Lost',
        'cancel'   => 'Cancel',
        'proposal' => 'Proposal',
        'pending'  => 'Pending',
    ];

    private int $inserted = 0;
    private int $updated  = 0;
    private int $skipped  = 0;
    private int $failed   = 0;

    public function handle(): int
    {
        $isDryRun      = $this->option('dry-run');
        $singleBooking = $this->option('booking');
        $limit         = $this->option('limit');

        $this->info('');
        $this->info('╔══════════════════════════════════════════╗');
        $this->info('║    CRM → SCM Quotation Migration         ║');
        $this->info('╚══════════════════════════════════════════╝');
        $this->info('  Mode    : ' . ($isDryRun ? '⚠  DRY RUN (no writes)' : '✔  LIVE'));
        if ($singleBooking) $this->info("  Booking : #{$singleBooking}");
        if ($limit)         $this->info("  Limit   : {$limit} records");
        $this->info('');

        // ── 1. Call sp_QuoteDetails() to populate CRM_Details ──
        // The SP creates/refreshes the CRM_Details staging table.
        // Without calling it first, the table does not exist.
        try {
            $this->info('  Calling sp_QuoteDetails() to refresh CRM staging table...');
            DB::connection(self::CRM_CONNECTION)->statement('CALL sp_QuoteDetails()');
            $this->info('  ✔  sp_QuoteDetails() completed.');
            $this->info('');
        } catch (\Exception $e) {
            $this->error('❌ Failed to call sp_QuoteDetails(): ' . $e->getMessage());
            Log::error('CRM Sync - sp_QuoteDetails failed', ['error' => $e->getMessage()]);
            return self::FAILURE;
        }

        // ── 2. Fetch from crm_details temp table ────────────────
        // sp_QuoteDetails() creates a LOCAL TEMPORARY table called crm_details
        // in a pg_temp_* schema. Laravel's query builder breaks on temp tables
        // (wrong case + wrong schema), so we use a raw query instead.
        try {
            $sql      = 'SELECT bookingno, bookingdetailsjson FROM crm_details ORDER BY bookingno ASC';
            $bindings = [];

            if ($singleBooking) {
                $sql      = 'SELECT bookingno, bookingdetailsjson FROM crm_details WHERE bookingno = ? ORDER BY bookingno ASC';
                $bindings = [$singleBooking];
            }

            $rows = DB::connection(self::CRM_CONNECTION)->select($sql, $bindings);

            // Apply limit manually (raw SELECT doesn't support fluent limit here)
            if ($limit && !$singleBooking) {
                $rows = array_slice($rows, 0, (int) $limit);
            }

            $crmRecords = collect($rows);
        } catch (\Exception $e) {
            $this->error('❌ Failed to read crm_details temp table: ' . $e->getMessage());
            Log::error('CRM Sync - crm_details fetch failed', ['error' => $e->getMessage()]);
            return self::FAILURE;
        }

        $total = $crmRecords->count();

        if ($total === 0) {
            $this->warn('  No records found in CRM_Details. Nothing to sync.');
            return self::SUCCESS;
        }

        $this->info("  Found {$total} record(s) in CRM_Details.");
        $this->info('');

        $bar = $this->output->createProgressBar($total);
        $bar->setFormat(' %current%/%max% [%bar%] %percent:3s%% — %message%');
        $bar->setMessage('Starting...');
        $bar->start();

        // ── 2. Process each record ──────────────────────────────
        foreach ($crmRecords as $crmRow) {
            $bookingNo = $crmRow->bookingno;
            $bar->setMessage("booking #{$bookingNo}");

            try {
                // Validate JSON
                if (empty($crmRow->bookingdetailsjson)) {
                    $this->skipped++;
                    Log::warning('CRM Sync - Empty JSON skipped', ['bookingno' => $bookingNo]);
                    $bar->advance();
                    continue;
                }

                $data = json_decode($crmRow->bookingdetailsjson, true);

                if (json_last_error() !== JSON_ERROR_NONE) {
                    $this->failed++;
                    Log::error('CRM Sync - JSON parse error', [
                        'bookingno'  => $bookingNo,
                        'json_error' => json_last_error_msg(),
                    ]);
                    $bar->advance();
                    continue;
                }

                $payload = $this->buildPayload($data);

                // Check if record already exists in SQL Server
                $existing = DB::connection(self::SCM_CONNECTION)
                    ->table('Quote_QuotationInfo')
                    ->where('bookingno', $bookingNo)
                    ->first(['ID']);

                if ($isDryRun) {
                    $existing ? $this->updated++ : $this->inserted++;
                    $bar->advance();
                    continue;
                }

                if ($existing) {
                    DB::connection(self::SCM_CONNECTION)
                        ->table('Quote_QuotationInfo')
                        ->where('bookingno', $bookingNo)
                        ->update($payload);

                    $this->updated++;
                    Log::info('CRM Sync - Updated', ['bookingno' => $bookingNo]);
                } else {
                    DB::connection(self::SCM_CONNECTION)
                        ->table('Quote_QuotationInfo')
                        ->insert(array_merge($payload, [
                            'ID'        => DB::connection(self::SCM_CONNECTION)->raw('NEWID()'),
                            'bookingno' => $bookingNo,
                        ]));

                    $this->inserted++;
                    Log::info('CRM Sync - Inserted', ['bookingno' => $bookingNo]);
                }
            } catch (\Exception $e) {
                $this->failed++;
                Log::error('CRM Sync - Record failed', [
                    'bookingno' => $bookingNo,
                    'error'     => $e->getMessage(),
                    'file'      => $e->getFile(),
                    'line'      => $e->getLine(),
                ]);
            }

            $bar->advance();
        }

        $bar->setMessage('Done.');
        $bar->finish();
        $this->newLine(2);

        // ── 3. Summary ──────────────────────────────────────────
        $this->printSummary($isDryRun, $total);

        // ── 4. Verification queries (live run only) ─────────────
        if (!$isDryRun && !$singleBooking) {
            $this->printVerification();
        }

        if ($singleBooking && !$isDryRun) {
            $this->printSingleVerification($singleBooking);
        }

        Log::info('CRM Sync - Completed', [
            'total'    => $total,
            'inserted' => $this->inserted,
            'updated'  => $this->updated,
            'skipped'  => $this->skipped,
            'failed'   => $this->failed,
        ]);

        return $this->failed > 0 ? self::FAILURE : self::SUCCESS;
    }

    // ============================================================
    // FIELD MAP: CRM JSON → Quote_QuotationInfo columns
    // ============================================================
    private function buildPayload(array $d): array
    {
        return [
            // Identity / Team
            'clientid'                => $d['clientid']              ?? null,
            'userid'                  => $d['userid']                ?? null,
            'userpartnerid'           => $d['userpartnerid']         ?? null,
            'username'                => $d['username']              ?? null,
            'teamid'                  => $d['teamid']                ?? null,
            'teamname'                => $d['teamname']              ?? null,
            'salespersoncode'         => $d['salespersoncode']       ?? null,

            // Company / Contact
            'companyname'             => $d['companyname']           ?? null,
            'contact_name'            => $d['contact_name']         ?? null,
            'type'                    => $d['type']                  ?? null,
            'email_from'              => $d['email_from']            ?? null,
            'phone'                   => $d['phone']                 ?? null,
            'mobile'                  => $d['mobile']                ?? null,

            // Primary Partner address
            'pri_partner_steet'       => $d['pri_partner_steet']        ?? null,
            'pri_partner_steet2'      => $d['pri_partner_steet2']       ?? null,
            'pri_partner_city'        => $d['pri_partner_city']         ?? null,
            'pri_partner_zip'         => $d['pri_partner_zip']          ?? null,
            'pri_partner_countryname' => $d['pri_partner_countryname']  ?? null,
            'pri_partner_email'       => $d['pri_partner_email']        ?? null,
            'pri_partner_phone'       => $d['pri_partner_phone']        ?? null,

            // Geography
            'origincountry'           => $d['origincountry']         ?? null,
            'destinationcountry'      => $d['destinationcountry']    ?? null,
            'countrycode'             => $d['countrycode']           ?? null,
            'countryname'             => $d['countryname']           ?? null,

            // Shipment
            'serviceverticalname'     => $d['svname'] ?? $d['serviceverticalname'] ?? null,
            'TransportMode'           => $d['transportmode']         ?? null,
            'ServiceClass'            => $d['serviceclass']          ?? null,
            'shipmentmode'            => $d['shipmentmode']          ?? null,
            'incoterm'                => $d['incoterm']              ?? null,
            'process'                 => $d['process']               ?? null,
            'commodity'               => $d['commodity']             ?? null,
            'scopevolume'             => $d['scopevolume']           ?? null,
            'cargovolume'             => isset($d['cargovolume'])    ? (float) $d['cargovolume'] : null,
            'containerlist'           => $d['containerlist']         ?? null,

            // Financials
            'expected_revenue'        => isset($d['expected_revenue']) ? (float) $d['expected_revenue'] : null,
            'prorated_revenue'        => isset($d['prorated_revenue']) ? (float) $d['prorated_revenue'] : null,
            'quoted_revenue'          => isset($d['quoted_revenue'])   ? (float) $d['quoted_revenue']   : null,

            // CRM metadata
            'source_id'               => $d['source_id']             ?? null,
            'source_name'             => $d['source_name']           ?? null,
            'medium_id'               => $d['medium_id']             ?? null,
            'stage_id'                => $d['stage_id']              ?? null,
            'industryname'            => $d['industryname']          ?? null,
            'state'                   => $d['state']                 ?? null,

            // Status
            'status'                  => $this->mapStatus($d['status']     ?? null),
            'QuoteStatus'             => $this->mapStatus($d['won_status'] ?? $d['status'] ?? null),

            // Dates
            'date_deadline'           => $this->parseDate($d['date_deadline'] ?? null),
            'date_closed'             => $this->parseDate($d['date_closed']   ?? null),
            'createdon'               => $this->parseDate($d['createdon']     ?? null),
            'updatedon'               => $this->parseDate($d['updatedon']     ?? null),

            // Audit trail — makes it easy to query all synced rows
            'LastModifiedBy'          => 'crm_sync',
        ];
    }

    // ============================================================
    // HELPERS
    // ============================================================
    private function mapStatus(?string $status): string
    {
        if (is_null($status)) return 'Proposal';
        return self::STATUS_MAP[strtolower(trim($status))] ?? 'Proposal';
    }

    private function parseDate(?string $value): ?string
    {
        if (empty($value)) return null;
        $ts = strtotime($value);
        return $ts !== false ? date('Y-m-d H:i:s', $ts) : null;
    }

    // ============================================================
    // OUTPUT
    // ============================================================
    private function printSummary(bool $isDryRun, int $total): void
    {
        $tag = $isDryRun ? ' [DRY RUN]' : '';
        $this->info("  ┌─────────────────────────────────────┐");
        $this->info("  │  Migration Summary{$tag}");
        $this->info("  ├─────────────────────────────────────┤");
        $this->info("  │  Total CRM records : {$total}");
        $this->info("  │  Inserted          : {$this->inserted}");
        $this->info("  │  Updated           : {$this->updated}");
        $this->info("  │  Skipped           : {$this->skipped}");
        if ($this->failed > 0) {
            $this->error("  │  ❌ Failed          : {$this->failed}  ← check laravel.log");
        } else {
            $this->info("  │  Failed            : {$this->failed}");
        }
        $this->info("  └─────────────────────────────────────┘");
        $this->newLine();
    }

    /**
     * Run quick verification queries against SQL Server after a full sync
     * so you can immediately confirm the data landed correctly.
     */
    private function printVerification(): void
    {
        $this->info('  ── Verification (SQL Server) ──────────────────────');

        try {
            $scm = DB::connection(self::SCM_CONNECTION);

            $syncedTotal = $scm->table('Quote_QuotationInfo')
                ->where('LastModifiedBy', 'crm_sync')
                ->count();

            $statusBreakdown = $scm->table('Quote_QuotationInfo')
                ->where('LastModifiedBy', 'crm_sync')
                ->selectRaw('ISNULL(QuoteStatus, \'(null)\') AS QuoteStatus, COUNT(*) as cnt')
                ->groupBy('QuoteStatus')
                ->orderByDesc('cnt')
                ->get();

            $recentRows = $scm->table('Quote_QuotationInfo')
                ->where('LastModifiedBy', 'crm_sync')
                ->orderByDesc('updatedon')
                ->limit(5)
                ->get(['bookingno', 'companyname', 'teamname', 'QuoteStatus', 'updatedon']);

            $this->info("  Total rows tagged 'crm_sync': {$syncedTotal}");
            $this->newLine();

            $this->info('  Status breakdown:');
            $this->table(
                ['QuoteStatus', 'Count'],
                $statusBreakdown->map(fn($r) => [$r->QuoteStatus, $r->cnt])->toArray()
            );

            $this->info('  5 most recently synced records:');
            $this->table(
                ['BookingNo', 'Company', 'Team', 'Status', 'UpdatedOn'],
                $recentRows->map(fn($r) => [
                    $r->bookingno,
                    mb_strimwidth($r->companyname ?? '-', 0, 35, '…'),
                    $r->teamname    ?? '-',
                    $r->QuoteStatus ?? '-',
                    $r->updatedon   ?? '-',
                ])->toArray()
            );
        } catch (\Exception $e) {
            $this->warn('  Could not run verification queries: ' . $e->getMessage());
        }
    }

    /**
     * Verify a single booking was written correctly.
     */
    private function printSingleVerification(string $bookingNo): void
    {
        $this->info("  ── Verification for booking #{$bookingNo} ──────────");

        try {
            $row = DB::connection(self::SCM_CONNECTION)
                ->table('Quote_QuotationInfo')
                ->where('bookingno', $bookingNo)
                ->first([
                    'bookingno', 'companyname', 'teamname', 'username',
                    'origincountry', 'destinationcountry', 'serviceverticalname',
                    'QuoteStatus', 'status', 'updatedon', 'LastModifiedBy',
                ]);

            if (!$row) {
                $this->error("  ❌ Booking #{$bookingNo} NOT found in Quote_QuotationInfo.");
                return;
            }

            $this->info("  ✔  Record found:");
            $this->table(
                ['Field', 'Value'],
                [
                    ['bookingno',           $row->bookingno],
                    ['companyname',         $row->companyname         ?? '-'],
                    ['teamname',            $row->teamname            ?? '-'],
                    ['username',            $row->username            ?? '-'],
                    ['origincountry',       $row->origincountry       ?? '-'],
                    ['destinationcountry',  $row->destinationcountry  ?? '-'],
                    ['serviceverticalname', $row->serviceverticalname ?? '-'],
                    ['QuoteStatus',         $row->QuoteStatus         ?? '-'],
                    ['status',              $row->status              ?? '-'],
                    ['updatedon',           $row->updatedon           ?? '-'],
                    ['LastModifiedBy',      $row->LastModifiedBy      ?? '-'],
                ]
            );
        } catch (\Exception $e) {
            $this->warn('  Could not run verification: ' . $e->getMessage());
        }
    }
}