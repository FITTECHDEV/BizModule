<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class CrmSyncService
{
    private const CRM_CONNECTION     = 'pgsql';
    private const SCM_CONNECTION     = 'sqlsrv';
    private const HISTORY_FILE       = 'crm_sync_history.json';
    private const HISTORY_MAX_ENTRIES = 50;

    // Date fields in the CRM JSON that need microsecond truncation
    // before being passed to SQL Server's TRY_CAST(... AS DATETIME)
    private const DATE_FIELDS = ['createdon', 'updatedon', 'date_deadline', 'date_closed'];

    /**
     * Run the full sync and return a result summary array.
     *
     * Flow:
     *   1. Get one persistent PDO connection to Postgres
     *   2. Call sp_QuoteDetails() — creates CRM_Details temp table in THIS session
     *   3. SELECT * FROM CRM_Details in the SAME session (temp table would be lost otherwise)
     *   4. Loop each row, normalize date fields in JSON, upsert into SQL Server via SP
     *   5. Verify only the records touched in this run
     *   6. Append run summary to history file
     *
     * @param  bool        $dryRun       Preview only — no writes
     * @param  int|null    $limit        Max records to process
     * @param  string|null $bookingNo    Sync a single booking number
     * @param  string      $triggeredBy  User who triggered the sync (for history log)
     */
    public function run(
        bool    $dryRun      = false,
        ?int    $limit       = null,
        ?string $bookingNo   = null,
        string  $triggeredBy = 'system'
    ): array {
        $inserted            = 0;
        $updated             = 0;
        $skipped             = 0;
        $failed              = 0;
        $errors              = [];
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

            Log::info('CRM Sync - Calling sp_QuoteDetails()');
            $pdo->exec('CALL sp_QuoteDetails()');
            Log::info('CRM Sync - sp_QuoteDetails() completed');

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
                'file'  => $e->getFile(),
                'line'  => $e->getLine(),
            ]);

            return $this->result(0, 0, 0, 0, 0, [
                ['bookingno' => null, 'error' => 'CRM setup failed: ' . $e->getMessage()]
            ]);
        }

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
        // STEP 3: Parse JSON, normalize dates, upsert into SQL Server
        //
        // Root cause of NULL dates: Postgres sends microsecond precision
        // timestamps e.g. "2026-02-13T14:19:54.235641" which SQL Server's
        // TRY_CAST(... AS DATETIME) silently returns NULL for because
        // DATETIME only supports 3-decimal millisecond precision.
        //
        // Fix: decode JSON, truncate each date field to milliseconds,
        // re-encode before passing to the stored procedure.
        // ============================================================
        $scmPdo = DB::connection(self::SCM_CONNECTION)->getPdo();

        foreach ($rows as $row) {
            $bno = $row->bookingno;

            try {
                if (empty($row->bookingdetailsjson)) {
                    $skipped++;
                    Log::warning('CRM Sync - Empty JSON skipped', ['bookingno' => $bno]);
                    continue;
                }

                $data = json_decode($row->bookingdetailsjson, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    $failed++;
                    $errors[] = ['bookingno' => $bno, 'error' => 'JSON parse error: ' . json_last_error_msg()];
                    Log::error('CRM Sync - JSON parse error', ['bookingno' => $bno]);
                    continue;
                }

                // Normalize microsecond timestamps → SQL Server-compatible format
                foreach (self::DATE_FIELDS as $field) {
                    if (!empty($data[$field])) {
                        $data[$field] = $this->normalizeDateTime($data[$field]);
                    }
                }

                $normalizedJson = json_encode($data);

                $existing = DB::connection(self::SCM_CONNECTION)
                    ->table('Quote_QuotationInfo')
                    ->where('bookingno', $bno)
                    ->exists();

                if ($dryRun) {
                    $existing ? $updated++ : $inserted++;
                    $processedBookingNos[] = $bno;
                    Log::debug('CRM Sync - Dry run', [
                        'bookingno' => $bno,
                        'action'    => $existing ? 'would update' : 'would insert',
                    ]);
                    continue;
                }

                // Pass normalized JSON (dates truncated to ms) to the SP
                $stmt = $scmPdo->prepare("EXEC dbo.sp_Quote_QuotationCreate ?");
                $stmt->execute([$normalizedJson]);

                $existing ? $updated++ : $inserted++;
                $processedBookingNos[] = $bno;

                Log::info('CRM Sync - SP executed', [
                    'bookingno' => $bno,
                    'action'    => $existing ? 'updated' : 'inserted',
                ]);
            } catch (\Exception $e) {
                $failed++;
                $errors[] = ['bookingno' => $bno, 'error' => $e->getMessage()];
                Log::error('CRM Sync - Record failed', [
                    'bookingno' => $bno,
                    'error'     => $e->getMessage(),
                ]);
            }
        }

        // ============================================================
        // STEP 4: Verify — only records touched in THIS run
        // ============================================================
        $verification = null;
        if (!$dryRun) {
            $verification = $this->verify($bookingNo, $processedBookingNos);
        }

        Log::info('CRM Sync - Completed', compact('total', 'inserted', 'updated', 'skipped', 'failed'));

        $result = $this->result($total, $inserted, $updated, $skipped, $failed, $errors, $verification);

        // ============================================================
        // STEP 5: Persist run summary to history file (skip dry runs)
        // ============================================================
        if (!$dryRun) {
            $this->appendHistory($result, $triggeredBy, $bookingNo, $limit);
        }

        return $result;
    }

    // ============================================================
    // DATE NORMALIZATION
    //
    // SQL Server DATETIME supports milliseconds (3 decimal places).
    // Postgres sends microseconds (6 decimal places) which causes
    // TRY_CAST to silently return NULL.
    //
    // Input:  "2026-02-13T14:19:54.235641"
    // Output: "2026-02-13 14:19:54.235"
    // ============================================================
    private function normalizeDateTime(string $value): ?string
    {
        if (empty($value)) {
            return null;
        }

        // Remove the T separator so SQL Server DATETIME parsing is unambiguous
        $value = str_replace('T', ' ', $value);

        // Truncate to 3 decimal places (milliseconds) if more are present
        // Matches: "2026-02-13 14:19:54.235641" → "2026-02-13 14:19:54.235"
        if (preg_match('/^(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\.\d{1,3})\d*$/', $value, $m)) {
            return $m[1];
        }

        // Already clean (no decimals or exactly 1-3 decimal places)
        return $value;
    }

    // ============================================================
    // HISTORY FILE
    //
    // Appends a compact summary of each real (non-dry-run) sync
    // to storage/app/crm_sync_history.json.
    // Keeps the last HISTORY_MAX_ENTRIES entries only.
    // ============================================================
    private function appendHistory(array $result, string $triggeredBy, ?string $bookingNo, ?int $limit): void
    {
        try {
            $entry = [
                'run_at'       => now()->toDateTimeString(),
                'triggered_by' => $triggeredBy,
                'booking_no'   => $bookingNo,
                'limit'        => $limit,
                'total'        => $result['total'],
                'inserted'     => $result['inserted'],
                'updated'      => $result['updated'],
                'skipped'      => $result['skipped'],
                'failed'       => $result['failed'],
                'has_errors'   => !empty($result['errors']),
            ];

            $history = [];

            if (Storage::exists(self::HISTORY_FILE)) {
                $raw = Storage::get(self::HISTORY_FILE);
                $history = json_decode($raw, true) ?? [];
            }

            // Prepend newest entry so the list is newest-first
            array_unshift($history, $entry);

            // Trim to max allowed entries
            $history = array_slice($history, 0, self::HISTORY_MAX_ENTRIES);

            Storage::put(self::HISTORY_FILE, json_encode($history, JSON_PRETTY_PRINT));
        } catch (\Exception $e) {
            Log::error('CRM Sync - Failed to write history file', ['error' => $e->getMessage()]);
        }
    }

    // ============================================================
    // HISTORY READ
    // Called by the controller to serve the history endpoint.
    // ============================================================
    public function getHistory(int $limit = 20): array
    {
        try {
            if (!Storage::exists(self::HISTORY_FILE)) {
                return [];
            }

            $history = json_decode(Storage::get(self::HISTORY_FILE), true) ?? [];

            return array_slice($history, 0, $limit);
        } catch (\Exception $e) {
            Log::error('CRM Sync - Failed to read history file', ['error' => $e->getMessage()]);
            return [];
        }
    }

    // ============================================================
    // FIELD MAP: CRM JSON → Quote_QuotationInfo columns
    // (kept for reference — not used in the sync flow directly)
    // ============================================================
    private function buildPayload(array $d): array
    {
        return [
            'clientid'                     => $d['clientid']          ?? null,
            'userid'                       => $d['userid']            ?? null,
            'userpartnerid'                => $d['userpartnerid']     ?? null,
            'username'                     => $d['username']          ?? '',
            'teamid'                       => $d['teamid']            ?? null,
            'teamname'                     => $d['teamname']          ?? '',
            'SalesPersonCode'              => $d['salespersoncode']   ?? '',
            'SalesPersonEmail'             => $d['salespersonemail']  ?? '',
            'companyname'                  => $d['companyname']       ?? '',
            'companyname_crm'              => $d['companyname_crm']   ?? '',
            'contact_name'                 => $d['contact_name']      ?? '',
            'partner_name'                 => $d['partner_name']      ?? '',
            'type'                         => $d['type']              ?? '',
            'function'                     => $d['function']          ?? '',
            'email_from'                   => $d['email_from']        ?? '',
            'phone'                        => $d['phone']             ?? '',
            'mobile'                       => $d['mobile']            ?? '',
            'street'                       => $d['street']            ?? '',
            'street2'                      => $d['street2']           ?? '',
            'zip'                          => $d['zip']               ?? '',
            'city'                         => $d['city']              ?? '',
            'phone_sanitized'              => $d['phone_sanitized']   ?? '',
            'email_normalized'             => $d['email_normalized']  ?? '',
            'partnermaster_name'           => $d['partnermaster_name']       ?? '',
            'partner_stateid'              => $d['partner_stateid']          ?? null,
            'partner_statename'            => $d['partner_statename']        ?? '',
            'partner_countryid'            => $d['partner_countryid']        ?? null,
            'partner_country_code'         => $d['partner_country_code']     ?? '',
            'partner_countryname'          => $d['partner_countryname']      ?? '',
            'industry_id'                  => $d['industry_id']              ?? null,
            'partner_industrttypecode'     => $d['partner_industrttypecode'] ?? '',
            'partner_industrttype'         => $d['partner_industrttype']     ?? '',
            'partner_function'             => $d['partner_function']         ?? '',
            'partner_steet'                => $d['partner_steet']            ?? '',
            'partner_steet2'               => $d['partner_steet2']           ?? '',
            'partner_zip'                  => $d['partner_zip']              ?? '',
            'partner_city'                 => $d['partner_city']             ?? '',
            'partner_email'                => $d['partner_email']            ?? '',
            'partner_phone'                => $d['partner_phone']            ?? '',
            'partner_mobile'               => $d['partner_mobile']           ?? '',
            'pri_id'                       => $d['pri_id']                          ?? null,
            'pri_partnermaster_name'       => $d['pri_partnermaster_name']          ?? '',
            'pri_partner_stateid'          => $d['pri_partner_stateid']             ?? null,
            'pri_partner_statename'        => $d['pri_partner_statename']           ?? '',
            'pri_partner_countryid'        => $d['pri_partner_countryid']           ?? null,
            'pri_partner_countrycode'      => $d['pri_partner_countrycode']         ?? '',
            'pri_partner_countryname'      => $d['pri_partner_countryname']         ?? '',
            'pri_rrp_industryid'           => $d['pri_rrp_industryid']              ?? null,
            'pri_partner_industrttypecode' => $d['pri_partner_industrttypecode']    ?? '',
            'pri_partner_industrttype'     => $d['pri_partner_industrttype']        ?? '',
            'pri_partner_function'         => $d['pri_partner_function']            ?? '',
            'pri_partner_steet'            => $d['pri_partner_steet']               ?? '',
            'pri_partner_steet2'           => $d['pri_partner_steet2']              ?? '',
            'pri_partner_zip'              => $d['pri_partner_zip']                 ?? '',
            'pri_partner_city'             => $d['pri_partner_city']                ?? '',
            'pri_partner_email'            => $d['pri_partner_email']               ?? '',
            'pri_partner_phone'            => $d['pri_partner_phone']               ?? '',
            'pri_partner_mobile'           => $d['pri_partner_mobile']              ?? '',
            'origin_country_id'            => $d['origin_country_id']           ?? null,
            'origin_country_code'          => $d['origin_country_code']         ?? '',
            'origincountry'                => $d['origincountry']               ?? '',
            'dest_country_id'              => $d['dest_country_id']             ?? null,
            'destination_country_code'     => $d['destination_country_code']    ?? '',
            'destinationcountry'           => $d['destinationcountry']          ?? '',
            'stateid'                      => $d['stateid']                     ?? null,
            'statename'                    => $d['statename']                   ?? '',
            'countryid'                    => $d['countryid']                   ?? null,
            'countrycode'                  => $d['countrycode']                 ?? '',
            'countryname'                  => $d['countryname']                 ?? '',
            'industryid'                   => $d['industryid']                  ?? null,
            'industryname'                 => $d['industryname']                ?? '',
            'serviceverticalid'            => $d['serviceverticalid']           ?? null,
            'serviceverticalname'          => $d['svname'] ?? $d['serviceverticalname'] ?? '',
            'ServiceVertical'              => $d['servicevertical']             ?? null,
            'SVName'                       => $d['svname']                      ?? '',
            'TransportMode'                => $d['transportmode']               ?? '',
            'ServiceClass'                 => $d['serviceclass']                ?? '',
            'shipmentmode'                 => $d['shipmentmode']                ?? '',
            'incoterm'                     => $d['incoterm']                    ?? '',
            'process'                      => $d['process']                     ?? '',
            'storage'                      => $d['storage']                     ?? '',
            'other'                        => $d['other']                       ?? '',
            'commodity'                    => $d['commodity']                   ?? '',
            'scope_of_volume'              => $d['scope_of_volume']             ?? '',
            'scopevolume'                  => $d['scopevolume']                 ?? '',
            'cargo_volume'                 => ($d['cargo_volume']  ?? 0) > 0 ? (float) $d['cargo_volume']  : null,
            'cargovolume'                  => ($d['cargovolume']   ?? 0) > 0 ? (float) $d['cargovolume']   : null,
            'containerlist'                => $d['containerlist']               ?? '',
            'quoted_revenue'               => ($d['quoted_revenue']   ?? 0) > 0 ? (float) $d['quoted_revenue']   : null,
            'expected_revenue'             => ($d['expected_revenue']  ?? 0) > 0 ? (float) $d['expected_revenue']  : null,
            'prorated_revenue'             => ($d['prorated_revenue']  ?? 0) > 0 ? (float) $d['prorated_revenue']  : null,
            'source_id'                    => $d['source_id']                   ?? null,
            'source'                       => $d['source']                      ?? '',
            'source_name'                  => $d['source_name']                 ?? '',
            'medium_id'                    => $d['medium_id']                   ?? null,
            'medium'                       => $d['medium']                      ?? '',
            'stage_id'                     => $d['stage_id']                    ?? null,
            'state'                        => $d['state']                       ?? '',
            'quotationo'                   => $d['quotationo']                  ?? '',
            'won_status'                   => $d['won_status']                  ?? '',
            'status'                       => $d['status']                      ?? '',
            'QuoteStatus'                  => $this->deriveQuoteStatus($d['won_status'] ?? null, $d['status'] ?? null),
            'date_deadline'                => $this->normalizeDateTime($d['date_deadline'] ?? null),
            'date_closed'                  => $this->normalizeDateTime($d['date_closed']   ?? null),
            'CreatedOn'                    => $this->normalizeDateTime($d['createdon']     ?? null),
            'Updatedon'                    => $this->normalizeDateTime($d['updatedon']     ?? null),
            'LastModifiedBy'               => 'crm_sync',
        ];
    }

    // ============================================================
    // VERIFICATION
    // ============================================================
    private function verify(?string $bookingNo, array $processedBookingNos = []): array
    {
        try {
            $scm = DB::connection(self::SCM_CONNECTION);

            if ($bookingNo) {
                $row = $scm->table('Quote_QuotationInfo')
                    ->where('bookingno', $bookingNo)
                    ->first([
                        'bookingno', 'companyname', 'teamname', 'username',
                        'origincountry', 'destinationcountry', 'serviceverticalname',
                        'QuoteStatus', 'status', 'CreatedOn', 'Updatedon', 'LastModifiedBy',
                    ]);

                return $row ? ['record' => (array) $row] : ['record' => null];
            }

            $query = $scm->table('Quote_QuotationInfo')
                ->where('LastModifiedBy', 'crm_sync');

            if (!empty($processedBookingNos)) {
                $query->whereIn('bookingno', $processedBookingNos);
            }

            $total  = $query->count();
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
        $st  = strtolower(trim($status   ?? ''));

        if ($won === 'won'    || $st === 'won')  return 'Won';
        if ($won === 'lost'   || $st === 'lost') return 'Lost';
        if ($won === 'cancel')                   return 'Canceled';
        if ($won === 'pending')                  return 'In Progress';

        return 'In Progress';
    }

    private function parseDate(?string $value): ?string
    {
        if (empty($value)) return null;
        $ts = strtotime($value);
        return $ts !== false ? date('Y-m-d H:i:s', $ts) : null;
    }

    private function result(
        int    $total,
        int    $inserted,
        int    $updated,
        int    $skipped,
        int    $failed,
        array  $errors       = [],
        ?array $verification = null
    ): array {
        return compact('total', 'inserted', 'updated', 'skipped', 'failed', 'errors', 'verification');
    }
}