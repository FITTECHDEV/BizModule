<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CrmSyncService
{
    private const CRM_CONNECTION = 'pgsql';
    private const SCM_CONNECTION = 'sqlsrv';

    private const STATUS_MAP = [
        'won' => 'Won',
        'lost' => 'Lost',
        'cancel' => 'Cancel',
        'proposal' => 'Proposal',
        'pending' => 'Pending',
    ];

    /**
     * Run the full sync and return a result summary array.
     *
     * Flow:
     *   1. Get one persistent PDO connection to Postgres
     *   2. Call sp_QuoteDetails() — creates CRM_Details temp table in THIS session
     *   3. SELECT * FROM CRM_Details in the SAME session (temp table would be lost otherwise)
     *   4. Loop each row, parse bookingdetailsjson, upsert into SQL Server
     *   5. Verify only the records touched in this run
     *
     * @param  bool        $dryRun    Preview only — no writes
     * @param  int|null    $limit     Max records to process
     * @param  string|null $bookingNo Sync a single booking number
     */
    public function run(bool $dryRun = false, ?int $limit = null, ?string $bookingNo = null, string $triggeredBy = 'system'): array
    {
        $inserted = 0;
        $updated = 0;
        $skipped = 0;
        $failed = 0;
        $errors = [];
        $records = [];
        $processedBookingNos = [];

        // ============================================================
        // STEP 1 + 2: Call SP and read CRM_Details in the SAME PDO session
        //
        // CRITICAL: CRM_Details is a temp table scoped to the session.
        // Using DB::statement() then DB::select() can hit different
        // pool connections and lose the temp table. We pin to one PDO.
        // ============================================================
        try {
            $pdo = DB::connection(self::CRM_CONNECTION)->getPdo();

            // Step 1: Call the procedure — creates CRM_Details in THIS session
            Log::info('CRM Sync - Calling sp_QuoteDetails()');
            $pdo->exec('CALL sp_QuoteDetails()');
            Log::info('CRM Sync - sp_QuoteDetails() completed');

            // Step 2: Read CRM_Details IN THE SAME SESSION
            // bookingdetailsjson is the full CRM record as a JSON string
            if ($bookingNo) {
                $stmt = $pdo->prepare(
                    'SELECT bookingno, bookingdetailsjson
                     FROM   crm_details
                     WHERE  bookingno = ?
                     ORDER  BY bookingno ASC'
                );
                $stmt->execute([$bookingNo]);
            } else {
                $stmt = $pdo->query(
                    'SELECT bookingno, bookingdetailsjson
                     FROM   crm_details
                     ORDER  BY bookingno ASC'
                );
            }

            $rows = $stmt->fetchAll(\PDO::FETCH_OBJ);

            Log::info('CRM Sync - crm_details fetched', ['row_count' => count($rows)]);
        } catch (\Exception $e) {
            Log::error('CRM Sync - Setup failed', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return $this->result(0, 0, 0, 0, 0, [
                ['bookingno' => null, 'error' => 'CRM setup failed: ' . $e->getMessage()]
            ]);
        }

        // Apply limit after fetch (temp table + PDO doesn't easily support LIMIT inline)
        if ($limit && !$bookingNo) {
            $rows = array_slice($rows, 0, $limit);
        }

        $total = count($rows);

        if ($total === 0) {
            Log::warning('CRM Sync - No records returned from crm_details.');
            return $this->result(0, 0, 0, 0, 0, [
                ['bookingno' => null, 'error' => 'No records returned from crm_details']
            ]);
        }

        // ============================================================
        // STEP 3: Parse JSON and upsert into SQL Server
        //
        // For each row:
        //   - Validate the JSON from bookingdetailsjson
        //   - Build the column payload via buildPayload()
        //   - Check if bookingno already exists in Quote_QuotationInfo
        //   - INSERT (new) or UPDATE (existing) only CRM-owned columns
        // ============================================================
        $scmPdo = DB::connection(self::SCM_CONNECTION)->getPdo();

        foreach ($rows as $row) {
            $bno = $row->bookingno;

            try {
                // AFTER
                if (empty($row->bookingdetailsjson)) {
                    $skipped++;
                    // NEW — track skipped so UI can display it in the results table
                    $records[] = ['bookingno' => $bno, 'status' => 'skipped', 'error' => null];
                    Log::warning('CRM Sync - Empty JSON skipped', ['bookingno' => $bno]);
                    continue;
                }

                json_decode($row->bookingdetailsjson, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    $failed++;
                    $errorMsg = 'JSON parse error: ' . json_last_error_msg();
                    $errors[] = ['bookingno' => $bno, 'error' => $errorMsg];
                    // NEW — track failed with reason
                    $records[] = ['bookingno' => $bno, 'status' => 'failed', 'error' => $errorMsg];
                    Log::error('CRM Sync - JSON parse error', ['bookingno' => $bno]);
                    continue;
                }

                $existing = DB::connection(self::SCM_CONNECTION)
                    ->table('Quote_QuotationInfo')
                    ->where('bookingno', $bno)
                    ->exists();

                if ($dryRun) {
                    $action = $existing ? 'updated' : 'inserted';
                    $existing ? $updated++ : $inserted++;
                    $processedBookingNos[] = $bno;
                    // NEW — track dry-run outcome so preview table shows inserted/updated split
                    $records[] = ['bookingno' => $bno, 'status' => $action, 'error' => null];

                    Log::debug('CRM Sync - Dry run', [
                        'bookingno' => $bno,
                        'action' => 'would ' . $action,
                    ]);
                    continue;
                }

                $stmt = $scmPdo->prepare("EXEC dbo.sp_Quote_QuotationCreate ?");
                $stmt->execute([$row->bookingdetailsjson]);

                $action = $existing ? 'updated' : 'inserted';
                $existing ? $updated++ : $inserted++;
                $processedBookingNos[] = $bno;
                // NEW — track real outcome
                $records[] = ['bookingno' => $bno, 'status' => $action, 'error' => null];

                Log::info('CRM Sync - SP executed', [
                    'bookingno' => $bno,
                    'action' => $action,
                ]);
            } catch (\Exception $e) {
                $failed++;
                $errors[] = ['bookingno' => $bno, 'error' => $e->getMessage()];
                // NEW — track exception failures
                $records[] = ['bookingno' => $bno, 'status' => 'failed', 'error' => $e->getMessage()];

                Log::error('CRM Sync - Record failed', [
                    'bookingno' => $bno,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // ============================================================
        // STEP 4: Verify — only records touched in THIS run
        // Skipped on dry run since nothing was written
        // ============================================================
        $verification = null;
        if (!$dryRun) {
            $verification = $this->verify($bookingNo, $processedBookingNos);
        }

        Log::info('CRM Sync - Completed', compact('total', 'inserted', 'updated', 'skipped', 'failed'));

        // Persist this run to history (skipped on dry runs — nothing was written)
        if (!$dryRun) {
            $this->appendHistory([
                'run_at' => date('Y-m-d H:i:s'),
                'triggered_by' => $triggeredBy,
                'total' => $total,
                'inserted' => $inserted,
                'updated' => $updated,
                'skipped' => $skipped,
                'failed' => $failed,
                'booking_no' => $bookingNo,
                'limit' => $limit,
            ]);
        }

        return $this->result($total, $inserted, $updated, $skipped, $failed, $errors, $verification, $records);
    }

    // ============================================================
    // FIELD MAP: CRM JSON → Quote_QuotationInfo columns
    //
    // Source: bookingdetailsjson from CRM_Details (via sp_QuoteDetails)
    // Target: Quote_QuotationInfo (SQL Server)
    //
    // Columns intentionally NOT mapped (SCM-managed, not CRM-owned):
    //   QuoteNo, OfficeCode, OfficeID, QuotationTCTemplate,
    //   QuotationEmailTemplate, ServiceTemplateID, ServiceTemplate,
    //   BillToPartyCode, BillToParty, ForwarderCode, Forwarder,
    //   IncludeCountry, PlaceOfReceiptCode, PlaceOfReceipt,
    //   PlaceOfDeliveryCode, PlaceOfDelivery, ReasonCode, ReasonRemark,
    //   QuoteStatusDescription, INTCreatedOn, INTUpdatedOn,
    //   ClientCreated, ClientCodeINSCM, ClientIDINSCM, ClientIntegration,
    //   SCMBookingID, SCMBookingNo, SCMJobID, SCMJobNo, SCMINTStatus,
    //   SCMBookingXML, CreatedOnInSCM, UpdatedOninSCM, AgentTypeId,
    //   AgentType, CustomerType, CreatedBy
    // ============================================================
    private function buildPayload(array $d): array
    {
        return [
            // ── Identity / team ──────────────────────────────────────
            'clientid' => $d['clientid'] ?? null,
            'userid' => $d['userid'] ?? null,
            'userpartnerid' => $d['userpartnerid'] ?? null,
            'username' => $d['username'] ?? '',
            'teamid' => $d['teamid'] ?? null,
            'teamname' => $d['teamname'] ?? '',
            'SalesPersonCode' => $d['salespersoncode'] ?? '',
            'SalesPersonEmail' => $d['salespersonemail'] ?? '',

            // ── Company / contact ────────────────────────────────────
            'companyname' => $d['companyname'] ?? '',
            'companyname_crm' => $d['companyname_crm'] ?? '',
            'contact_name' => $d['contact_name'] ?? '',
            'partner_name' => $d['partner_name'] ?? '',
            'type' => $d['type'] ?? '',
            'function' => $d['function'] ?? '',
            'email_from' => $d['email_from'] ?? '',
            'phone' => $d['phone'] ?? '',
            'mobile' => $d['mobile'] ?? '',
            'street' => $d['street'] ?? '',
            'street2' => $d['street2'] ?? '',
            'zip' => $d['zip'] ?? '',
            'city' => $d['city'] ?? '',
            'phone_sanitized' => $d['phone_sanitized'] ?? '',
            'email_normalized' => $d['email_normalized'] ?? '',

            // ── Partner (company-level) ──────────────────────────────
            'partnermaster_name' => $d['partnermaster_name'] ?? '',
            'partner_stateid' => $d['partner_stateid'] ?? null,
            'partner_statename' => $d['partner_statename'] ?? '',
            'partner_countryid' => $d['partner_countryid'] ?? null,
            'partner_country_code' => $d['partner_country_code'] ?? '',
            'partner_countryname' => $d['partner_countryname'] ?? '',
            'industry_id' => $d['industry_id'] ?? null,
            'partner_industrttypecode' => $d['partner_industrttypecode'] ?? '',
            'partner_industrttype' => $d['partner_industrttype'] ?? '',
            'partner_function' => $d['partner_function'] ?? '',
            'partner_steet' => $d['partner_steet'] ?? '',
            'partner_steet2' => $d['partner_steet2'] ?? '',
            'partner_zip' => $d['partner_zip'] ?? '',
            'partner_city' => $d['partner_city'] ?? '',
            'partner_email' => $d['partner_email'] ?? '',
            'partner_phone' => $d['partner_phone'] ?? '',
            'partner_mobile' => $d['partner_mobile'] ?? '',

            // ── Primary partner ──────────────────────────────────────
            'pri_id' => $d['pri_id'] ?? null,
            'pri_partnermaster_name' => $d['pri_partnermaster_name'] ?? '',
            'pri_partner_stateid' => $d['pri_partner_stateid'] ?? null,
            'pri_partner_statename' => $d['pri_partner_statename'] ?? '',
            'pri_partner_countryid' => $d['pri_partner_countryid'] ?? null,
            'pri_partner_countrycode' => $d['pri_partner_countrycode'] ?? '',
            'pri_partner_countryname' => $d['pri_partner_countryname'] ?? '',
            'pri_rrp_industryid' => $d['pri_rrp_industryid'] ?? null,
            'pri_partner_industrttypecode' => $d['pri_partner_industrttypecode'] ?? '',
            'pri_partner_industrttype' => $d['pri_partner_industrttype'] ?? '',
            'pri_partner_function' => $d['pri_partner_function'] ?? '',
            'pri_partner_steet' => $d['pri_partner_steet'] ?? '',
            'pri_partner_steet2' => $d['pri_partner_steet2'] ?? '',
            'pri_partner_zip' => $d['pri_partner_zip'] ?? '',
            'pri_partner_city' => $d['pri_partner_city'] ?? '',
            'pri_partner_email' => $d['pri_partner_email'] ?? '',
            'pri_partner_phone' => $d['pri_partner_phone'] ?? '',
            'pri_partner_mobile' => $d['pri_partner_mobile'] ?? '',

            // ── Geography ────────────────────────────────────────────
            'origin_country_id' => $d['origin_country_id'] ?? null,
            'origin_country_code' => $d['origin_country_code'] ?? '',
            'origincountry' => $d['origincountry'] ?? '',
            'dest_country_id' => $d['dest_country_id'] ?? null,
            'destination_country_code' => $d['destination_country_code'] ?? '',
            'destinationcountry' => $d['destinationcountry'] ?? '',
            'stateid' => $d['stateid'] ?? null,
            'statename' => $d['statename'] ?? '',
            'countryid' => $d['countryid'] ?? null,
            'countrycode' => $d['countrycode'] ?? '',
            'countryname' => $d['countryname'] ?? '',

            // ── Industry ─────────────────────────────────────────────
            'industryid' => $d['industryid'] ?? null,
            'industryname' => $d['industryname'] ?? '',

            // ── Service details ──────────────────────────────────────
            'serviceverticalid' => $d['serviceverticalid'] ?? null,
            'serviceverticalname' => $d['svname'] ?? $d['serviceverticalname'] ?? '',
            'ServiceVertical' => $d['servicevertical'] ?? null,
            'SVName' => $d['svname'] ?? '',
            'TransportMode' => $d['transportmode'] ?? '',
            'ServiceClass' => $d['serviceclass'] ?? '',
            'shipmentmode' => $d['shipmentmode'] ?? '',
            'incoterm' => $d['incoterm'] ?? '',
            'process' => $d['process'] ?? '',
            'storage' => $d['storage'] ?? '',
            'other' => $d['other'] ?? '',

            // ── Cargo ────────────────────────────────────────────────
            'commodity' => $d['commodity'] ?? '',
            'scope_of_volume' => $d['scope_of_volume'] ?? '',
            'scopevolume' => $d['scopevolume'] ?? '',
            'cargo_volume' => ($d['cargo_volume'] ?? 0) > 0 ? (float) $d['cargo_volume'] : null,
            'cargovolume' => ($d['cargovolume'] ?? 0) > 0 ? (float) $d['cargovolume'] : null,
            'containerlist' => $d['containerlist'] ?? '',

            // ── Revenue ──────────────────────────────────────────────
            'quoted_revenue' => ($d['quoted_revenue'] ?? 0) > 0 ? (float) $d['quoted_revenue'] : null,
            'expected_revenue' => ($d['expected_revenue'] ?? 0) > 0 ? (float) $d['expected_revenue'] : null,
            'prorated_revenue' => ($d['prorated_revenue'] ?? 0) > 0 ? (float) $d['prorated_revenue'] : null,

            // ── CRM metadata ─────────────────────────────────────────
            'source_id' => $d['source_id'] ?? null,
            'source' => $d['source'] ?? '',
            'source_name' => $d['source_name'] ?? '',
            'medium_id' => $d['medium_id'] ?? null,
            'medium' => $d['medium'] ?? '',
            'stage_id' => $d['stage_id'] ?? null,
            'state' => $d['state'] ?? '',
            'quotationo' => $d['quotationo'] ?? '',
            'won_status' => $d['won_status'] ?? '',

            // ── Status ───────────────────────────────────────────────
            'status' => $d['status'] ?? '',
            'QuoteStatus' => $this->deriveQuoteStatus($d['won_status'] ?? null, $d['status'] ?? null),

            // ── Dates ────────────────────────────────────────────────
            'date_deadline' => $this->parseDate($d['date_deadline'] ?? null),
            'date_closed' => $this->parseDate($d['date_closed'] ?? null),
            'CreatedOn' => $this->parseDate($d['createdon'] ?? null),
            'Updatedon' => $this->parseDate($d['updatedon'] ?? null),

            // ── Audit ────────────────────────────────────────────────
            'LastModifiedBy' => 'crm_sync',
        ];
    }

    // ============================================================
    // VERIFICATION
    //
    // Only inspects rows touched in THIS sync run via $processedBookingNos.
    // This prevents the verification table from showing stale records
    // from previous runs.
    //
    // Single booking → returns the full row for spot-checking.
    // Full sync      → returns count + the records from this run only.
    // ============================================================
    private function verify(?string $bookingNo, array $processedBookingNos = []): array
    {
        try {
            $scm = DB::connection(self::SCM_CONNECTION);

            // Single booking path — return that one row in full
            if ($bookingNo) {
                $row = $scm->table('Quote_QuotationInfo')
                    ->where('bookingno', $bookingNo)
                    ->first([
                        'bookingno',
                        'companyname',
                        'teamname',
                        'username',
                        'origincountry',
                        'destinationcountry',
                        'serviceverticalname',
                        'QuoteStatus',
                        'status',
                        'Updatedon',
                        'LastModifiedBy',
                    ]);

                return $row ? ['record' => (array) $row] : ['record' => null];
            }

            // Full sync path — scope to only the booking numbers
            // processed in this run, not ALL crm_sync rows ever written
            $query = $scm->table('Quote_QuotationInfo')
                ->where('LastModifiedBy', 'crm_sync');

            if (!empty($processedBookingNos)) {
                $query->whereIn('bookingno', $processedBookingNos);
            }

            $total = $query->count();
            $recent = $query->orderByDesc('Updatedon')
                ->get(['bookingno', 'companyname', 'teamname', 'QuoteStatus', 'Updatedon'])
                ->toArray();

            return compact('total', 'recent');
        } catch (\Exception $e) {
            Log::error('CRM Sync - Verification failed', ['error' => $e->getMessage()]);
            return ['error' => $e->getMessage()];
        }
    }

    // ============================================================
    // HELPERS
    // ============================================================
    private function deriveQuoteStatus(?string $wonStatus, ?string $status): string
    {
        $won = strtolower(trim($wonStatus ?? ''));
        $st = strtolower(trim($status ?? ''));

        if ($won === 'won' || $st === 'won')
            return 'Won';
        if ($won === 'lost' || $st === 'lost')
            return 'Lost';
        if ($won === 'cancel')
            return 'Canceled';
        if ($won === 'pending')
            return 'In Progress';

        return 'In Progress'; // default
    }


    private function parseDate(?string $value): ?string
    {
        if (empty($value))
            return null;
        $ts = strtotime($value);
        return $ts !== false ? date('Y-m-d H:i:s', $ts) : null;
    }

    private function result(
        int $total,
        int $inserted,
        int $updated,
        int $skipped,
        int $failed,
        array $errors = [],
        ?array $verification = null,
        array $records = []   // NEW — per-record outcomes; defaults to [] so early-return callers are unaffected
    ): array {
        return compact('total', 'inserted', 'updated', 'skipped', 'failed', 'errors', 'verification', 'records');
    }

    // ============================================================
    // HISTORY
    //
    // Stored as a newline-delimited JSON file (one object per line).
    // Path: storage/app/crm_sync_history.jsonl
    //
    // Why a flat file?
    //   - No new tables (per requirement)
    //   - Append-only writes are O(1)
    //   - 20–100 lines is negligible storage
    //   - Zero schema dependency or migrations
    // ============================================================
    private const HISTORY_FILE = 'crm_sync_history.jsonl';
    private const HISTORY_MAX_LINES = 100;

    public function getHistory(int $limit = 20): array
    {
        $path = storage_path('app/' . self::HISTORY_FILE);

        if (!file_exists($path)) {
            return [];
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        if (empty($lines)) {
            return [];
        }

        // Reverse so most recent run is first
        $lines = array_reverse($lines);
        $lines = array_slice($lines, 0, $limit);
        $history = [];

        foreach ($lines as $line) {
            $decoded = json_decode(trim($line), true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $history[] = $decoded;
            }
        }

        return $history;
    }

    private function appendHistory(array $entry): void
    {
        $path = storage_path('app/' . self::HISTORY_FILE);

        try {
            file_put_contents(
                $path,
                json_encode($entry) . PHP_EOL,
                FILE_APPEND | LOCK_EX
            );

            // Rotate: keep only the last HISTORY_MAX_LINES entries
            $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            if (count($lines) > self::HISTORY_MAX_LINES) {
                $trimmed = array_slice($lines, -self::HISTORY_MAX_LINES);
                file_put_contents($path, implode(PHP_EOL, $trimmed) . PHP_EOL, LOCK_EX);
            }
        } catch (\Exception $e) {
            // Non-fatal — history failure must never break the sync
            Log::warning('CRM Sync - History write failed', [
                'error' => $e->getMessage(),
                'path' => $path,
            ]);
        }
    }
}
