<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CrmSyncService
{
    private const CRM_CONNECTION = 'pgsql';
    private const SCM_CONNECTION = 'sqlsrv';

    private const STATUS_MAP = [
        'won'      => 'Won',
        'lost'     => 'Lost',
        'cancel'   => 'Cancel',
        'proposal' => 'Proposal',
        'pending'  => 'Pending',
    ];

    /**
     * Run the full sync and return a result summary array.
     *
     * @param  bool        $dryRun      Preview only — no writes
     * @param  int|null    $limit       Max records to process
     * @param  string|null $bookingNo   Sync a single booking
     * @return array{
     *   total: int,
     *   inserted: int,
     *   updated: int,
     *   skipped: int,
     *   failed: int,
     *   errors: array,
     *   verification: array|null
     * }
     */
    public function run(bool $dryRun = false, ?int $limit = null, ?string $bookingNo = null): array
    {
        $inserted = 0;
        $updated  = 0;
        $skipped  = 0;
        $failed   = 0;
        $errors   = [];

        // ── Step 1: Call sp_QuoteDetails() to populate temp table ──
        try {
            DB::connection(self::CRM_CONNECTION)->statement('CALL sp_QuoteDetails()');
            Log::info('CRM Sync - sp_QuoteDetails() completed');
        } catch (\Exception $e) {
            Log::error('CRM Sync - sp_QuoteDetails failed', ['error' => $e->getMessage()]);
            return $this->result(0, 0, 0, 0, 0, [
                ['bookingno' => null, 'error' => 'sp_QuoteDetails() failed: ' . $e->getMessage()]
            ]);
        }

        // ── Step 2: Fetch from crm_details temp table ───────────────
        try {
            $sql      = 'SELECT bookingno, bookingdetailsjson FROM crm_details ORDER BY bookingno ASC';
            $bindings = [];

            if ($bookingNo) {
                $sql      = 'SELECT bookingno, bookingdetailsjson FROM crm_details WHERE bookingno = ? ORDER BY bookingno ASC';
                $bindings = [$bookingNo];
            }

            $rows = DB::connection(self::CRM_CONNECTION)->select($sql, $bindings);

            if ($limit && !$bookingNo) {
                $rows = array_slice($rows, 0, $limit);
            }
        } catch (\Exception $e) {
            Log::error('CRM Sync - crm_details fetch failed', ['error' => $e->getMessage()]);
            return $this->result(0, 0, 0, 0, 0, [
                ['bookingno' => null, 'error' => 'crm_details read failed: ' . $e->getMessage()]
            ]);
        }

        $total = count($rows);

        // ── Step 3: Upsert each record into SQL Server ──────────────
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

                $payload = $this->buildPayload($data);

                $existing = DB::connection(self::SCM_CONNECTION)
                    ->table('Quote_QuotationInfo')
                    ->where('bookingno', $bno)
                    ->first(['ID']);

                if ($dryRun) {
                    $existing ? $updated++ : $inserted++;
                    continue;
                }

                if ($existing) {
                    DB::connection(self::SCM_CONNECTION)
                        ->table('Quote_QuotationInfo')
                        ->where('bookingno', $bno)
                        ->update($payload);

                    $updated++;
                    Log::info('CRM Sync - Updated', ['bookingno' => $bno]);
                } else {
                    DB::connection(self::SCM_CONNECTION)
                        ->table('Quote_QuotationInfo')
                        ->insert(array_merge($payload, [
                            'ID'        => DB::connection(self::SCM_CONNECTION)->raw('NEWID()'),
                            'bookingno' => $bno,
                        ]));

                    $inserted++;
                    Log::info('CRM Sync - Inserted', ['bookingno' => $bno]);
                }
            } catch (\Exception $e) {
                $failed++;
                $errors[] = ['bookingno' => $bno, 'error' => $e->getMessage()];
                Log::error('CRM Sync - Record failed', [
                    'bookingno' => $bno,
                    'error'     => $e->getMessage(),
                    'file'      => $e->getFile(),
                    'line'      => $e->getLine(),
                ]);
            }
        }

        // ── Step 4: Verification (live run only) ────────────────────
        $verification = null;
        if (!$dryRun) {
            $verification = $this->verify($bookingNo);
        }

        Log::info('CRM Sync - Completed', compact('total', 'inserted', 'updated', 'skipped', 'failed'));

        return $this->result($total, $inserted, $updated, $skipped, $failed, $errors, $verification);
    }

    // ============================================================
    // FIELD MAP: CRM JSON → Quote_QuotationInfo columns
    // ============================================================
    private function buildPayload(array $d): array
    {
        return [
            'clientid'                => $d['clientid']              ?? null,
            'userid'                  => $d['userid']                ?? null,
            'userpartnerid'           => $d['userpartnerid']         ?? null,
            'username'                => $d['username']              ?? null,
            'teamid'                  => $d['teamid']                ?? null,
            'teamname'                => $d['teamname']              ?? null,
            'salespersoncode'         => $d['salespersoncode']       ?? null,
            'companyname'             => $d['companyname']           ?? null,
            'contact_name'            => $d['contact_name']         ?? null,
            'type'                    => $d['type']                  ?? null,
            'email_from'              => $d['email_from']            ?? null,
            'phone'                   => $d['phone']                 ?? null,
            'mobile'                  => $d['mobile']                ?? null,
            'pri_partner_steet'       => $d['pri_partner_steet']        ?? null,
            'pri_partner_steet2'      => $d['pri_partner_steet2']       ?? null,
            'pri_partner_city'        => $d['pri_partner_city']         ?? null,
            'pri_partner_zip'         => $d['pri_partner_zip']          ?? null,
            'pri_partner_countryname' => $d['pri_partner_countryname']  ?? null,
            'pri_partner_email'       => $d['pri_partner_email']        ?? null,
            'pri_partner_phone'       => $d['pri_partner_phone']        ?? null,
            'origincountry'           => $d['origincountry']         ?? null,
            'destinationcountry'      => $d['destinationcountry']    ?? null,
            'countrycode'             => $d['countrycode']           ?? null,
            'countryname'             => $d['countryname']           ?? null,
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
            'expected_revenue'        => isset($d['expected_revenue']) ? (float) $d['expected_revenue'] : null,
            'prorated_revenue'        => isset($d['prorated_revenue']) ? (float) $d['prorated_revenue'] : null,
            'quoted_revenue'          => isset($d['quoted_revenue'])   ? (float) $d['quoted_revenue']   : null,
            'source_id'               => $d['source_id']             ?? null,
            'source_name'             => $d['source_name']           ?? null,
            'medium_id'               => $d['medium_id']             ?? null,
            'stage_id'                => $d['stage_id']              ?? null,
            'industryname'            => $d['industryname']          ?? null,
            'state'                   => $d['state']                 ?? null,
            'status'                  => $this->mapStatus($d['status']     ?? null),
            'QuoteStatus'             => $this->mapStatus($d['won_status'] ?? $d['status'] ?? null),
            'date_deadline'           => $this->parseDate($d['date_deadline'] ?? null),
            'date_closed'             => $this->parseDate($d['date_closed']   ?? null),
            'createdon'               => $this->parseDate($d['createdon']     ?? null),
            'updatedon'               => $this->parseDate($d['updatedon']     ?? null),
            'LastModifiedBy'          => 'crm_sync',
        ];
    }

    // ── Verification ────────────────────────────────────────────
    private function verify(?string $bookingNo): array
    {
        try {
            $scm = DB::connection(self::SCM_CONNECTION);

            if ($bookingNo) {
                $row = $scm->table('Quote_QuotationInfo')
                    ->where('bookingno', $bookingNo)
                    ->first([
                        'bookingno', 'companyname', 'teamname', 'username',
                        'origincountry', 'destinationcountry', 'serviceverticalname',
                        'QuoteStatus', 'status', 'updatedon', 'LastModifiedBy',
                    ]);

                return $row ? ['record' => (array) $row] : ['record' => null];
            }

            $total = $scm->table('Quote_QuotationInfo')
                ->where('LastModifiedBy', 'crm_sync')
                ->count();

            $statusBreakdown = $scm->table('Quote_QuotationInfo')
                ->where('LastModifiedBy', 'crm_sync')
                ->selectRaw("ISNULL(QuoteStatus, '(null)') AS QuoteStatus, COUNT(*) as cnt")
                ->groupBy('QuoteStatus')
                ->orderByDesc('cnt')
                ->get()
                ->toArray();

            $recent = $scm->table('Quote_QuotationInfo')
                ->where('LastModifiedBy', 'crm_sync')
                ->orderByDesc('updatedon')
                ->limit(5)
                ->get(['bookingno', 'companyname', 'teamname', 'QuoteStatus', 'updatedon'])
                ->toArray();

            return compact('total', 'statusBreakdown', 'recent');
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    // ── Helpers ─────────────────────────────────────────────────
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

    private function result(
        int $total, int $inserted, int $updated,
        int $skipped, int $failed, array $errors = [],
        ?array $verification = null
    ): array {
        return compact('total', 'inserted', 'updated', 'skipped', 'failed', 'errors', 'verification');
    }
}