<?php

namespace App\Http\Controllers;

use App\Models\QuotationInfo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;
use Symfony\Component\Mailer\Exception\TransportException;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;

class QuotationController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth']);
    }

    // ============================================
    // OFFICE BASE CURRENCY MAP
    // Source: Cust_Office + Bill_ChargeCodeOfficeMap (dominant currency per office)
    // SA has no operational currency — defaults to KES
    // ============================================
    const OFFICE_BASE_CURRENCY = [
        'DJ' => 'DJF',
        'ET' => 'ETB',
        'KE' => 'KES',
        'RW' => 'RWF',
        'SA' => 'KES', // Sales office — no dedicated currency, falls back to KES
        'SS' => 'SSP',
        'TZ' => 'TZS',
        'UG' => 'UGX',
        'ZM' => 'ZMW',
    ];

    // ============================================================
    // UPDATED index()
    // Uses resolveUserOfficeCodes() instead of inline Azure_Users query
    // ============================================================
    // public function index()
    // {
    //     $userOfficeCodes = $this->resolveUserOfficeCodes();

    //     if ($userOfficeCodes->isEmpty()) {
    //         return view('quotation.index', [
    //             'offices'       => collect(),
    //             'defaultOffice' => null,
    //             'officeError'   => 'Your account is not mapped to any office. Please contact your administrator.',
    //             'stats'         => $this->emptyStats(),
    //         ]);
    //     }

    //     // Resolve BaseOfficeCodes → teamnames used in Quote_QuotationInfo
    //     $offices = DB::table('Quote_QuotationInfo')
    //         ->whereNotNull('teamname')
    //         ->whereIn('teamname', $userOfficeCodes->toArray())
    //         ->distinct()
    //         ->orderBy('teamname')
    //         ->pluck('teamname');

    //     // Fallback: try matching through Cust_Office.TeamCode
    //     if ($offices->isEmpty()) {
    //         $teamCodes = DB::table('Cust_Office')
    //             ->whereIn('Code', $userOfficeCodes->toArray())
    //             ->where('IsActive', 1)
    //             ->where('IsDeleted', 0)
    //             ->pluck('TeamCode');

    //         if ($teamCodes->isNotEmpty()) {
    //             $offices = DB::table('Cust_Office')
    //                 ->whereIn('TeamCode', $teamCodes->toArray())
    //                 ->where('IsActive', 1)
    //                 ->where('IsDeleted', 0)
    //                 ->orderBy('Name')
    //                 ->pluck('Name');
    //         }
    //     }

    //     if ($offices->isEmpty()) {
    //         Log::warning('Quotation index - Office codes could not be resolved to any teamname', [
    //             'email'             => Auth::user()->Email,
    //             'base_office_codes' => $userOfficeCodes->toArray(),
    //         ]);

    //         return view('quotation.index', [
    //             'offices'       => collect(),
    //             'defaultOffice' => null,
    //             'officeError'   => 'Your office mapping could not be resolved. Please contact your administrator.',
    //             'stats'         => $this->emptyStats(),
    //         ]);
    //     }

    //     $defaultOffice = $offices->first();

    //     Log::info('Quotation index - Resolved offices', [
    //         'email'             => Auth::user()->Email,
    //         'base_office_codes' => $userOfficeCodes->toArray(),
    //         'resolved_offices'  => $offices->toArray(),
    //         'default_office'    => $defaultOffice,
    //     ]);

    //     // No $stats passed — portal() delivers stats with the first DataTable load
    //     $stats = $this->emptyStats();
    //     return view('quotation.index', compact('offices', 'defaultOffice', 'stats'));
    // }


    public function index()
    {
        $resolved = $this->resolveUserOfficesViaSP();
        $offices = $resolved['offices'];
        $defaultOffice = $resolved['default']; // always BaseOfficeCode's name

        if ($offices->isEmpty() || !$defaultOffice) {
            return view('quotation.index', [
                'offices' => collect(),
                'defaultOffice' => null,
                'officeError' => 'Your account is not mapped to any office in BIZ PRO. Please contact your administrator.',
                'stats' => $this->emptyStats(),
            ]);
        }

        Log::info('Quotation index - Offices resolved via SP', [
            'email' => Auth::user()->Email,
            'default_office' => $defaultOffice,
            'all_offices' => $offices->toArray(),
        ]);

        $stats = $this->emptyStats();
        return view('quotation.index', compact('offices', 'defaultOffice', 'stats'));
    }


    /**
     * portal()
     * ─────────────────────────────────────────────────────────────────────────────
     * Single endpoint for both DataTable rows and stats cards.
     *
     * SP BRANCH REALITY (from reading the actual SP body):
     *
     *   'In Progress' | 'Won' | 'Total'
     *     → Same SQL: returns ALL rows, no QuoteStatus filter
     *     → PHP must split into: in_progress / won / completed / failed_int
     *       using QuoteStatus + SCMINTStatus from DB
     *
     *   'Lost' | 'Cancelled'
     *     → Same SQL: WHERE QuoteStatus IN ('Lost','Cancelled')
     *     → Calling SP with 'Lost' already returns both Lost + Cancelled rows
     *
     *   'Failed Progress'
     *     → WHERE QuoteStatus = 'Failed Progress'
     *
     *   'Failed Int.'
     *     → WHERE QuoteStatus = 'Failed Int.'
     *
     *   'Completed'
     *     → WHERE QuoteStatus = 'Completed' — returns ZERO rows in practice
     *       because Completed is derived (Won + SCMINTStatus='Success'),
     *       never written to QuoteStatus column.
     *       Must use the 'Total' branch + PHP filter instead.
     *
     * STATS: Computed from the same SP calls used for rows — same PHP split logic.
     * NO separate DB aggregation queries.
     * ─────────────────────────────────────────────────────────────────────────────
     */
    public function portal(Request $request)
    {
        try {
            $cardFilter = $request->input('card_filter', 'in_progress');
            $days = $request->input('days', 'all');
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');

            // ── Resolve allowed office codes (security check) ─────────────────
            $allowedOfficeCodes = $this->resolveAllowedOfficeCodesViaSP();

            if ($allowedOfficeCodes->isEmpty()) {
                return response()->json([
                    'draw' => (int) $request->input('draw', 1),
                    'recordsTotal' => 0,
                    'recordsFiltered' => 0,
                    'data' => [],
                    'stats' => $this->emptyStats(),
                ]);
            }

            // ── Date formatting ───────────────────────────────────────────────
            $spFromDate = '';
            $spToDate = '';
            if ($days === 'custom' && $startDate && $endDate) {
                try {
                    $spFromDate = \Carbon\Carbon::createFromFormat('Y-m-d', $startDate)->format('d/m/Y');
                    $spToDate = \Carbon\Carbon::createFromFormat('Y-m-d', $endDate)->format('d/m/Y');
                } catch (\Exception $e) {
                    Log::warning('portal - Invalid date range', [
                        'start' => $startDate,
                        'end' => $endDate,
                    ]);
                }
            }

            // ── Resolve office code for SP ────────────────────────────────────
            // Requested office must be within the user's allowed codes.
            // If none selected, default to the user's base office code.
            $requestedOffice = $request->input('office');
            $spOfficeCode = '';

            if ($requestedOffice) {
                $code = DB::table('Cust_Office')
                    ->where('Name', $requestedOffice)
                    ->where('IsActive', 1)
                    ->where('IsDeleted', 0)
                    ->value('Code');

                if ($code && $allowedOfficeCodes->contains(strtoupper(trim($code)))) {
                    $spOfficeCode = strtoupper(trim($code));
                }
            }

            // Fallback to base office if no valid office resolved
            if (empty($spOfficeCode)) {
                $resolved = $this->resolveUserOfficesViaSP();
                $defaultName = $resolved['default'] ?? null;
                if ($defaultName) {
                    $code = DB::table('Cust_Office')
                        ->where('Name', $defaultName)
                        ->where('IsActive', 1)
                        ->where('IsDeleted', 0)
                        ->value('Code');
                    $spOfficeCode = $code ? strtoupper(trim($code)) : '';
                }
            }

            // ── Fetch SP result sets — office filtering done by SP ────────────
            $allRows = $this->callQuotationListSP('Total', $spFromDate, $spToDate, $spOfficeCode);
            $lostCancelledRows = $this->callQuotationListSP('Cancelled', $spFromDate, $spToDate, $spOfficeCode);
            $failedProgressRows = $this->callQuotationListSP('Failed Progress', $spFromDate, $spToDate, $spOfficeCode);

            // ── Load SCMINTStatus for Total branch rows ───────────────────────
            $allQuoteNos = array_column($allRows, 'QuoteNo');
            $scmLookup = collect();
            if (!empty($allQuoteNos)) {
                $scmLookup = DB::table('Quote_QuotationInfo')
                    ->whereIn('QuoteNo', $allQuoteNos)
                    ->get(['QuoteNo', 'QuoteStatus', 'SCMINTStatus'])
                    ->keyBy('QuoteNo');
            }

            // ── Compute stats ─────────────────────────────────────────────────
            $inProgress = $won = $completed = $failedInt = $failedProgress = 0;

            foreach ($allRows as $row) {
                $db = $scmLookup->get($row->QuoteNo);
                $quoteStatus = strtoupper(trim($db->QuoteStatus ?? $row->Status ?? ''));
                $scmStatus = $db ? (is_string($db->SCMINTStatus) ? strtoupper(trim($db->SCMINTStatus)) : null) : null;

                if ($quoteStatus === 'COMPLETED') {
                    $completed++;
                } elseif ($quoteStatus === 'WON' && $scmStatus === 'SUCCESS') {
                    $completed++;
                } elseif ($quoteStatus === 'WON' && !is_null($scmStatus)) {
                    $failedInt++;
                } elseif ($quoteStatus === 'WON') {
                    $won++;
                } elseif ($quoteStatus === 'FAILED PROGRESS') {
                    $failedProgress++;
                } else {
                    $inProgress++;
                }
            }

            $lostCanceled = count($lostCancelledRows);
            $total = $inProgress + $won + $completed + $failedInt + $failedProgress + $lostCanceled;

            $stats = [
                'total' => $total,
                'in_progress' => $inProgress,
                'won' => $won,
                'completed' => $completed,
                'lost_canceled' => $lostCanceled,
                'failed_int' => $failedInt,
                'failed_progress' => $failedProgress,
            ];

            // ── Select row set for active card ────────────────────────────────
            $activeRows = match ($cardFilter) {
                'lost_canceled' => $lostCancelledRows,
                'failed_progress' => $failedProgressRows,
                default => $allRows,
            };

            // ── PHP filter for Total-branch sub-cards ─────────────────────────
            if (!in_array($cardFilter, ['lost_canceled', 'failed_progress', 'total'])) {
                $activeRows = array_values(array_filter($activeRows, function ($row) use ($cardFilter, $scmLookup) {
                    $db = $scmLookup->get($row->QuoteNo);
                    $quoteStatus = strtoupper(trim($db->QuoteStatus ?? $row->Status ?? ''));
                    $scmStatus = $db ? (is_string($db->SCMINTStatus) ? strtoupper(trim($db->SCMINTStatus)) : null) : null;

                    return match ($cardFilter) {
                        'in_progress' => !in_array($quoteStatus, ['WON', 'COMPLETED', 'LOST', 'CANCELLED', 'CANCEL', 'FAILED PROGRESS']),
                        'won' => $quoteStatus === 'WON' && is_null($scmStatus),
                        'completed' => $quoteStatus === 'COMPLETED' || ($quoteStatus === 'WON' && $scmStatus === 'SUCCESS'),
                        'failed_int' => $quoteStatus === 'WON' && !is_null($scmStatus) && $scmStatus !== 'SUCCESS',
                        default => true,
                    };
                }));
            }

            // ── DataTables server-side ────────────────────────────────────────
            $draw = (int) $request->input('draw', 1);
            $start = (int) $request->input('start', 0);
            $length = (int) $request->input('length', 25);
            $search = trim($request->input('search.value', ''));

            if (!empty($search)) {
                $needle = strtolower($search);
                $activeRows = array_values(array_filter($activeRows, function ($row) use ($needle) {
                    return str_contains(strtolower($row->CompanyName ?? ''), $needle)
                        || str_contains(strtolower($row->OriginCountry ?? ''), $needle)
                        || str_contains(strtolower($row->DestinationCountry ?? ''), $needle)
                        || str_contains(strtolower($row->Status ?? ''), $needle)
                        || str_contains(strtolower($row->QuoteNo ?? ''), $needle)
                        || str_contains(strtolower($row->Service ?? ''), $needle)
                        || str_contains(strtolower($row->Process ?? ''), $needle)
                        || str_contains(strtolower($row->IncoTerm ?? ''), $needle);
                }));
            }

            $rowTotal = count($activeRows);
            $pageRows = array_slice($activeRows, $start, $length > 0 ? $length : $rowTotal);

            // ── Fetch full DB records for current page only ───────────────────
            $pageQuoteNos = array_column($pageRows, 'QuoteNo');
            $dbRecords = collect();
            if (!empty($pageQuoteNos)) {
                $dbRecords = DB::table('Quote_QuotationInfo')
                    ->whereIn('QuoteNo', $pageQuoteNos)
                    ->get([
                        'ID',
                        'QuoteNo',
                        'bookingno',
                        'teamname',
                        'teamid',
                        'clientid',
                        'username',
                        'QuoteStatus',
                        'SCMINTStatus',
                        'QuoteStatusDescription',
                        'CreatedOn',
                    ])
                    ->keyBy('QuoteNo');
            }

            // ── Format rows ───────────────────────────────────────────────────
            $data = array_map(function ($row) use ($dbRecords, $cardFilter) {
                $db = $dbRecords->get($row->QuoteNo);
                $quoteStatus = $db->QuoteStatus ?? $row->Status ?? 'In Progress';
                $scmStatus = $db->SCMINTStatus ?? null;

                $displayStatus = match (true) {
                    $quoteStatus === 'Completed' => 'Completed',
                    $quoteStatus === 'Won' && $scmStatus === 'Success' => 'Completed',
                    $quoteStatus === 'Won' && !is_null($scmStatus) && $scmStatus !== 'Success' => 'Failed Int.',
                    $quoteStatus === 'Won' => 'Won',
                    $quoteStatus === 'Failed Progress' => 'Failed Progress',
                    in_array($quoteStatus, ['Lost', 'Cancelled', 'Cancel']) => 'Lost / Canceled',
                    default => 'In Progress',
                };

                $badgeClass = match ($displayStatus) {
                    'Won' => 'badge-success',
                    'Completed' => 'badge-teal',
                    'Lost / Canceled' => 'badge-warning',
                    'Failed Int.' => 'badge-danger',
                    'Failed Progress' => 'badge-secondary',
                    default => 'badge-info',
                };

                $remark = trim($row->Remark ?? '');

                if ($db && in_array($displayStatus, ['Failed Int.', 'Failed Progress'])) {
                    $raw = trim($db->QuoteStatusDescription ?? '');
                    if ($raw) {
                        $decoded = json_decode('[' . $raw . ']', true);
                        $remark = (json_last_error() === JSON_ERROR_NONE && is_array($decoded))
                            ? implode(' | ', array_column($decoded, 'Remark'))
                            : $raw;
                    }
                }

                $remarksHtml = '<span class="text-muted">—</span>';
                if ($remark && $remark !== '-') {
                    $isError = in_array($displayStatus, ['Failed Int.', 'Failed Progress']);
                    $short = mb_strlen($remark) > 80 ? mb_substr($remark, 0, 80) . '...' : $remark;
                    $remarksHtml = $isError
                        ? '<span class="text-danger" title="' . e($remark) . '"><i class="fas fa-exclamation-circle mr-1"></i>' . e($short) . '</span>'
                        : '<span class="text-muted">' . e($short) . '</span>';
                }

                $actionHtml = '<div class="action-buttons">';
                if ($db) {
                    $isWon = $quoteStatus === 'Won' && is_null($scmStatus);
                    $isFailedInt = $quoteStatus === 'Won' && !is_null($scmStatus) && $scmStatus !== 'Success';

                    $actionHtml .= '<a href="' . route('quotation.view', $db->ID) . '" class="btn btn-info btn-sm" title="View"><i class="fas fa-eye"></i></a>';

                    if ($cardFilter === 'in_progress') {
                        $actionHtml .= ' <a href="' . route('quotation.edit', $db->ID) . '" class="btn btn-warning btn-sm" title="Edit"><i class="fas fa-edit"></i></a>';
                    }

                    if (($cardFilter === 'won' && $isWon) || ($cardFilter === 'failed_int' && $isFailedInt)) {
                        $actionHtml .= ' <button class="btn btn-success btn-sm btn-create-booking"'
                            . ' data-id="' . $db->ID . '"'
                            . ' data-booking="' . $db->bookingno . '"'
                            . ' data-team="' . $db->teamid . '"'
                            . ' data-quoteno="' . $db->QuoteNo . '"'
                            . ' data-origin="' . e($row->OriginCountry ?? '') . '"'
                            . ' data-destination="' . e($row->DestinationCountry ?? '') . '"'
                            . ' title="Create Booking"><i class="fas fa-plus-circle"></i></button>';
                    }

                    if ($cardFilter === 'failed_int' && $isFailedInt) {
                        $actionHtml .= ' <button class="btn btn-secondary btn-sm btn-retrigger"'
                            . ' data-booking="' . $db->bookingno . '"'
                            . ' data-id="' . $db->ID . '"'
                            . ' title="Retrigger"><i class="fas fa-sync-alt"></i></button>';
                    }
                }
                $actionHtml .= '</div>';

                return [
                    'QuoteNo' => $row->QuoteNo ?? '-',
                    'Date' => $db?->CreatedOn ? date('Y-m-d', strtotime($db->CreatedOn)) : ($row->Date ?? '-'),
                    'CompanyName' => $row->CompanyName ?? '-',
                    'SalesPerson' => $row->SalesPerson ?? '-',
                    'Service' => $row->Service ?? '-',
                    'Process' => $row->Process ?? '-',
                    'OriginCountry' => $row->OriginCountry ?? '-',
                    'DestinationCountry' => $row->DestinationCountry ?? '-',
                    'IncoTerm' => $row->IncoTerm ?? '-',
                    'ScopeOfVolume' => $row->ScopeOfVolume ?? '-',
                    'CargoVolume' => $row->CargoVolume ?? null,
                    'ContainerList' => $row->ContainerList ?? '-',
                    'Status' => '<span class="badge ' . $badgeClass . '">' . e($displayStatus) . '</span>',
                    'Remark' => $remarksHtml,
                    'action' => $actionHtml,
                ];
            }, $pageRows);

            Log::info('portal - Response', [
                'user' => Auth::user()->Email,
                'office_code' => $spOfficeCode,
                'card_filter' => $cardFilter,
                'row_total' => $rowTotal,
                'stats' => $stats,
            ]);

            return response()->json([
                'draw' => $draw,
                'recordsTotal' => $rowTotal,
                'recordsFiltered' => $rowTotal,
                'data' => $data,
                'stats' => $stats,
            ]);
        } catch (\Throwable $e) {
            Log::error('portal - Exception', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            return response()->json([
                'draw' => (int) $request->input('draw', 1),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'stats' => $this->emptyStats(),
            ], 500);
        }
    }

    private function emptyStats(): array
    {
        return [
            'total' => 0,
            'in_progress' => 0,
            'won' => 0,
            'completed' => 0,
            'lost_canceled' => 0,
            'failed_int' => 0,
            'failed_progress' => 0,
        ];
    }

    // ============================================================
    // UPDATED stats()
    // Validates the requested office against the user's allowed
    // offices — prevents users bypassing the dropdown restriction
    // ============================================================
    public function stats(Request $request)
    {
        $days = $request->input('days', 'all');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $allowedOfficeCodes = $this->resolveAllowedOfficeCodesViaSP();
        if ($allowedOfficeCodes->isEmpty()) {
            return response()->json([
                'total' => 0,
                'in_progress' => 0,
                'won' => 0,
                'completed' => 0,
                'lost_canceled' => 0,
                'failed_int' => 0,
                'failed_progress' => 0,
            ]);
        }

        $allowed = $allowedOfficeCodes->map(fn($c) => strtoupper(trim($c)))->toArray();

        $spFromDate = '';
        $spToDate = '';
        if ($days === 'custom' && $startDate && $endDate) {
            try {
                $spFromDate = \Carbon\Carbon::createFromFormat('Y-m-d', $startDate)->format('d/m/Y');
                $spToDate = \Carbon\Carbon::createFromFormat('Y-m-d', $endDate)->format('d/m/Y');
            } catch (\Exception $e) {
            }
        }

        // Office dropdown filter
        $filterCode = null;
        $requestedOffice = $request->input('office');
        if ($requestedOffice) {
            $code = DB::table('Cust_Office')
                ->where('Name', $requestedOffice)
                ->where('IsActive', 1)->where('IsDeleted', 0)
                ->value('Code');
            if ($code)
                $filterCode = strtoupper(trim($code));
        }

        // Scope helper — handles both formatted and numeric QuoteNos
        $scope = fn(array $rows) => $this->scopeRowsByOffice($rows, $allowed, $filterCode);

        // One SP call for Total (covers In Progress + Won + Completed + Failed Int.)
        $allRows = $scope($this->callQuotationListSP('Total', $spFromDate, $spToDate));
        $lostCancelledRows = $scope($this->callQuotationListSP('Lost', $spFromDate, $spToDate));
        $failedProgressRows = $scope($this->callQuotationListSP('Failed Progress', $spFromDate, $spToDate));

        // Look up SCMINTStatus for Won-branch splitting
        $quoteNos = array_column($allRows, 'QuoteNo');
        $scmLookup = collect();
        if (!empty($quoteNos)) {
            $scmLookup = DB::table('Quote_QuotationInfo')
                ->whereIn('QuoteNo', $quoteNos)
                ->get(['QuoteNo', 'QuoteStatus', 'SCMINTStatus'])
                ->keyBy('QuoteNo');
        }

        $inProgress = $won = $completed = $failedInt = 0;

        foreach ($allRows as $row) {
            $db = $scmLookup->get($row->QuoteNo);
            $quoteStatus = $db->QuoteStatus ?? $row->Status ?? '';
            $scmStatus = $db->SCMINTStatus ?? null;

            // REPLACE with:
            if ($quoteStatus === 'Won' && $scmStatus === 'Success') {
                $completed++;
            } elseif ($quoteStatus === 'Won' && !is_null($scmStatus) && $scmStatus !== 'Success') {
                $failedInt++;
            } elseif ($quoteStatus === 'Won') {
                $won++;
            } else {
                $inProgress++;
            }
        }

        $lostCanceled = count($lostCancelledRows);
        $failedProgress = count($failedProgressRows);
        $total = $inProgress + $won + $completed + $failedInt + $failedProgress + $lostCanceled;

        return response()->json([
            'total' => $total,
            'in_progress' => $inProgress,
            'won' => $won,
            'completed' => $completed,
            'lost_canceled' => $lostCanceled,
            'failed_int' => $failedInt,
            'failed_progress' => $failedProgress,
        ]);
    }

    // ============================================================
    // dataFromSP()
    // Powers the main quotation table. Calls sp_Quote_QuotationList
    // with the card-filter status, then looks up DB records for
    // IDs needed by action buttons.
    //
    // SP Status mapping:
    //   in_progress   → 'In Progress'
    //   won           → 'Won'        (QuoteStatus=Won, SCMINTStatus IS NULL)
    //   completed     → 'Completed'  (QuoteStatus=Won, SCMINTStatus=Success)
    //   failed_int    → 'Failed Int.'
    //   failed_progress → 'Failed Progress'
    //   lost_canceled → 'Lost' + separate 'Cancelled' call merged
    //   total         → 'Total'      (all records, no status filter)
    //
    // Day-based filters (Today, Last 15 days) are ignored — SP only
    // supports FromDate/ToDate pairs. Custom date range is converted.
    // Office scoping is applied in PHP via QuoteNo prefix matching.
    // ============================================================
    public function dataFromSP(Request $request)
    {
        try {
            $cardFilter = $request->input('card_filter', 'in_progress');
            $days = $request->input('days', 'all');
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');

            // ── Resolve allowed office codes ──────────────────────────────────
            $allowedOfficeCodes = $this->resolveAllowedOfficeCodesViaSP();

            if ($allowedOfficeCodes->isEmpty()) {
                return response()->json([
                    'draw' => (int) $request->input('draw', 1),
                    'recordsTotal' => 0,
                    'recordsFiltered' => 0,
                    'data' => [],
                ]);
            }

            $allowed = $allowedOfficeCodes
                ->map(fn($c) => strtoupper(trim($c)))
                ->toArray();

            // ── Date range — day filters ignored, SP only supports FromDate/ToDate ──
            $spFromDate = '';
            $spToDate = '';
            if ($days === 'custom' && $startDate && $endDate) {
                try {
                    $spFromDate = \Carbon\Carbon::createFromFormat('Y-m-d', $startDate)->format('d/m/Y');
                    $spToDate = \Carbon\Carbon::createFromFormat('Y-m-d', $endDate)->format('d/m/Y');
                } catch (\Exception $e) {
                    Log::warning('dataFromSP - Invalid date', ['start' => $startDate, 'end' => $endDate]);
                }
            }

            // ── Determine which SP call(s) to make per card ───────────────────
            // SP branches:
            //   'Total'          → all records (no status filter)
            //   'In Progress'    → all records (same branch as Total — filter in PHP)
            //   'Won'            → all records (same branch — filter in PHP by SCMINTStatus IS NULL)
            //   'Completed'      → all records (same branch — filter in PHP by SCMINTStatus=Success)
            //   'Failed Int.'    → all records (same branch — filter in PHP by SCMINTStatus=Failed)
            //   'Lost','Cancelled' → WHERE QuoteStatus IN ('Lost','Cancelled')
            //   'Failed Progress'  → WHERE QuoteStatus = 'Failed Progress'
            //
            // We call SP with 'Total' for in_progress/won/completed/failed_int/total
            // and with the exact status for lost_canceled/failed_progress

            $spStatus = match ($cardFilter) {
                'lost_canceled' => 'Lost',       // SP returns both Lost+Cancelled in one call
                'failed_progress' => 'Failed Progress',
                default => 'Total',      // covers in_progress, won, completed, failed_int, total
            };

            // ── Call SP ───────────────────────────────────────────────────────
            $rows = $this->callQuotationListSP($spStatus, $spFromDate, $spToDate);

            // ── Resolve office dropdown filter code if selected ───────────────────
            $filterCode = null;
            $requestedOffice = $request->input('office');
            if ($requestedOffice) {
                $officeCode = DB::table('Cust_Office')
                    ->where('Name', $requestedOffice)
                    ->where('IsActive', 1)
                    ->where('IsDeleted', 0)
                    ->value('Code');

                if ($officeCode) {
                    $filterCode = strtoupper(trim($officeCode));
                }
            }

            // ── Apply office scope (handles both KE-QT-XX format AND numeric QuoteNos) ──
            $rows = $this->scopeRowsByOffice($rows, $allowed, $filterCode);

            // ── Fetch SCMINTStatus from DB for Won-branch filtering ───────────
            // SP 'Total' branch returns ALL records including Won rows.
            // We need SCMINTStatus to split Won into Won/Completed/Failed Int.
            $quoteNos = array_column($rows, 'QuoteNo');
            $dbRecords = collect();

            if (!empty($quoteNos)) {
                $dbRecords = DB::table('Quote_QuotationInfo')
                    ->whereIn('QuoteNo', $quoteNos)
                    ->get([
                        'ID',
                        'QuoteNo',
                        'bookingno',
                        'teamname',
                        'teamid',
                        'clientid',
                        'username',
                        'QuoteStatus',
                        'SCMINTStatus',
                        'QuoteStatusDescription',
                        'CreatedOn',
                    ])
                    ->keyBy('QuoteNo');
            }

            // ── Filter rows by card ───────────────────────────────────────────
            // For SP 'Total' branch, all statuses come back — filter in PHP
            if (!in_array($cardFilter, ['lost_canceled', 'failed_progress', 'total'])) {
                $rows = array_values(array_filter($rows, function ($row) use ($cardFilter, $dbRecords) {
                    $db = $dbRecords->get($row->QuoteNo);
                    $quoteStatus = $db->QuoteStatus ?? $row->Status ?? '';
                    $scmStatus = $db->SCMINTStatus ?? null;

                    return match ($cardFilter) {
                        'in_progress' => $quoteStatus === 'In Progress',
                        'won' => $quoteStatus === 'Won' && is_null($scmStatus),
                        'completed' => $quoteStatus === 'Won' && $scmStatus === 'Success',
                        'failed_int' => $quoteStatus === 'Won' && !is_null($scmStatus) && $scmStatus !== 'Success',
                        default => true,
                    };
                }));
            }

            // ── DataTables server-side ────────────────────────────────────────
            $draw = (int) $request->input('draw', 1);
            $start = (int) $request->input('start', 0);
            $length = (int) $request->input('length', 25);
            $search = trim($request->input('search.value', ''));

            // Search
            if (!empty($search)) {
                $needle = strtolower($search);
                $rows = array_values(array_filter($rows, function ($row) use ($needle, $dbRecords) {
                    $db = $dbRecords->get($row->QuoteNo);
                    return str_contains(strtolower($row->CompanyName ?? ''), $needle)
                        || str_contains(strtolower($row->SalesPerson ?? ''), $needle)
                        || str_contains(strtolower($row->OriginCountry ?? ''), $needle)
                        || str_contains(strtolower($row->DestinationCountry ?? ''), $needle)
                        || str_contains(strtolower($row->Status ?? ''), $needle)
                        || str_contains(strtolower($row->QuoteNo ?? ''), $needle)
                        || str_contains(strtolower($row->Service ?? ''), $needle)
                        || str_contains(strtolower($row->Process ?? ''), $needle)
                        || str_contains(strtolower($row->IncoTerm ?? ''), $needle);
                }));
            }

            $total = count($rows);
            $pageRows = array_slice($rows, $start, $length > 0 ? $length : $total);

            // ── Format rows for DataTable ─────────────────────────────────────
            $data = array_map(function ($row) use ($dbRecords, $cardFilter) {
                $db = $dbRecords->get($row->QuoteNo);
                $quoteStatus = $db->QuoteStatus ?? $row->Status ?? 'In Progress';
                $scmStatus = $db->SCMINTStatus ?? null;

                // Resolve display status
                $displayStatus = match (true) {
                    $quoteStatus === 'Won' && $scmStatus === 'Success' => 'Completed',
                    $quoteStatus === 'Won' && !is_null($scmStatus) && $scmStatus !== 'Success' => 'Failed Int.',
                    $quoteStatus === 'Won' => 'Won',
                    $quoteStatus === 'Failed Progress' => 'Failed Progress',
                    in_array($quoteStatus, ['Lost', 'Cancelled']) => 'Lost / Canceled',
                    default => 'In Progress',
                };

                $badgeClass = match ($displayStatus) {
                    'Won' => 'badge-success',
                    'Completed' => 'badge-teal',
                    'Lost / Canceled' => 'badge-warning',
                    'Failed Int.' => 'badge-danger',
                    'Failed Progress' => 'badge-secondary',
                    default => 'badge-info', // In Progress
                };

                // Remarks
                // For Failed Int., QuoteStatusDescription holds the actual pipeline failure
                // message written by markAsFailed(). The SP's Remark field contains the CRM
                // reference number, which is not meaningful to the user.
                $remark = trim($row->Remark ?? '');

                if ($db) {
                    $raw = trim($db->QuoteStatusDescription ?? '');
                    if ($raw) {
                        $decoded = json_decode('[' . $raw . ']', true);
                        $remark = (json_last_error() === JSON_ERROR_NONE && is_array($decoded))
                            ? implode(' | ', array_column($decoded, 'Remark'))
                            : $raw;
                    } else {
                        // No QuoteStatusDescription — suppress the CRM reference number
                        $remark = '';
                    }
                }

                $remarksHtml = '<span class="text-muted">—</span>';
                if ($remark && $remark !== '-') {
                    $isError = in_array($displayStatus, ['Failed Int.', 'Failed Progress']);
                    $short = mb_strlen($remark) > 80 ? mb_substr($remark, 0, 80) . '...' : $remark;
                    $remarksHtml = $isError
                        ? '<span class="text-danger" title="' . e($remark) . '"><i class="fas fa-exclamation-circle mr-1"></i>' . e($short) . '</span>'
                        : '<span class="text-muted">' . e($short) . '</span>';
                }

                // Action buttons
                $actionHtml = '<div class="action-buttons">';
                if ($db) {
                    $isWon = $quoteStatus === 'Won' && is_null($scmStatus);
                    $isFailedInt = $quoteStatus === 'Won' && !is_null($scmStatus) && $scmStatus !== 'Success';
                    $isInProgress = $quoteStatus === 'In Progress';

                    $actionHtml .= '<a href="' . route('quotation.view', $db->ID) . '" class="btn btn-info btn-sm" title="View"><i class="fas fa-eye"></i></a>';

                    if ($cardFilter === 'in_progress' && $isInProgress) {
                        $actionHtml .= ' <a href="' . route('quotation.edit', $db->ID) . '" class="btn btn-warning btn-sm" title="Edit"><i class="fas fa-edit"></i></a>';
                    }

                    if (($cardFilter === 'won' && $isWon) || ($cardFilter === 'failed_int' && $isFailedInt)) {
                        $actionHtml .= ' <button class="btn btn-success btn-sm btn-create-booking"'
                            . ' data-id="' . $db->ID . '"'
                            . ' data-booking="' . $db->bookingno . '"'
                            . ' data-team="' . $db->teamid . '"'
                            . ' data-quoteno="' . $db->QuoteNo . '"'
                            . ' data-origin="' . e($row->OriginCountry ?? '') . '"'
                            . ' data-destination="' . e($row->DestinationCountry ?? '') . '"'
                            . ' title="Create Booking"><i class="fas fa-plus-circle"></i></button>';
                    }

                    if ($cardFilter === 'failed_int' && $isFailedInt) {
                        $actionHtml .= ' <button class="btn btn-secondary btn-sm btn-retrigger"'
                            . ' data-booking="' . $db->bookingno . '"'
                            . ' data-id="' . $db->ID . '"'
                            . ' title="Retrigger"><i class="fas fa-sync-alt"></i></button>';
                    }
                }
                $actionHtml .= '</div>';

                return [
                    'QuoteNo' => $row->QuoteNo ?? '-',
                    'Date' => $db?->CreatedOn ? date('Y-m-d', strtotime($db->CreatedOn)) : ($row->Date ?? '-'),
                    'ClientName' => $row->CompanyName ?? '-',
                    'SalesPerson' => $row->SalesPerson ?? '-',
                    'Service' => $row->Service ?? '-',
                    'Service' => $row->Service ?? '-',
                    'Process' => $row->Process ?? '-',
                    'OriginCountry' => $row->OriginCountry ?? '-',
                    'DestinationCountry' => $row->DestinationCountry ?? '-',
                    'IncoTerm' => $row->IncoTerm ?? '-',
                    'ScopeOfVolume' => $row->ScopeOfVolume ?? '-',
                    'CargoVolume' => $row->CargoVolume ?? null,
                    'ContainerList' => $row->ContainerList ?? '-',
                    'Status' => '<span class="badge ' . $badgeClass . '">' . e($displayStatus) . '</span>',
                    'Remark' => $remarksHtml,
                    'action' => $actionHtml,
                ];
            }, $pageRows);

            return response()->json([
                'draw' => $draw,
                'recordsTotal' => $total,
                'recordsFiltered' => $total,
                'data' => $data,
            ]);
        } catch (\Throwable $e) {
            Log::error('dataFromSP - Exception', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            return response()->json([
                'draw' => (int) $request->input('draw', 1),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
            ], 500);
        }
    }

    // ============================================================
    // NEW PRIVATE METHOD — resolves BaseOfficeCodes → teamnames
    // that actually exist in Quote_QuotationInfo.
    // This is what the office dropdown displays and what teamname
    // column stores (e.g. "Kenya" not "KE").
    // ============================================================
    private function resolveUserTeamNames(): \Illuminate\Support\Collection
    {
        $officeCodes = $this->resolveUserOfficeCodes();

        if ($officeCodes->isEmpty()) {
            return collect();
        }

        // Resolve Cust_Office.Name values for the user's office codes.
        // These are clean office display names (e.g. "FIT - Kenya")
        // used as the reliable identifier instead of teamname,
        // which stores mixed values (office names AND person names).
        $officeNames = DB::table('Cust_Office')
            ->whereIn('Code', $officeCodes->map(fn($c) => trim($c))->toArray())
            ->where('IsActive', 1)
            ->where('IsDeleted', 0)
            ->orderBy('Name')
            ->pluck('Name');

        Log::info('resolveUserTeamNames result', [
            'email' => Auth::user()->Email,
            'office_codes' => $officeCodes->toArray(),
            'office_names' => $officeNames->toArray(),
        ]);

        return $officeNames;
    }

    private function getStatsForIndex(\Illuminate\Support\Collection $allowedOfficeCodes): array
    {
        if ($allowedOfficeCodes->isEmpty()) {
            return [
                'total' => 0,
                'in_progress' => 0,
                'won' => 0,
                'completed' => 0,
                'lost_canceled' => 0,
                'failed_int' => 0,
                'failed_progress' => 0,
            ];
        }

        $allowed = $allowedOfficeCodes->map(fn($c) => strtoupper(trim($c)))->toArray();

        $scope = fn(array $rows) => $this->scopeRowsByOffice($rows, $allowed);


        $allRows = $scope($this->callQuotationListSP('Total'));
        $lostCancelledRows = $scope($this->callQuotationListSP('Lost'));
        $failedProgressRows = $scope($this->callQuotationListSP('Failed Progress'));

        $quoteNos = array_column($allRows, 'QuoteNo');
        $scmLookup = collect();
        if (!empty($quoteNos)) {
            $scmLookup = DB::table('Quote_QuotationInfo')
                ->whereIn('QuoteNo', $quoteNos)
                ->get(['QuoteNo', 'QuoteStatus', 'SCMINTStatus'])
                ->keyBy('QuoteNo');
        }

        $inProgress = $won = $completed = $failedInt = 0;

        foreach ($allRows as $row) {
            $db = $scmLookup->get($row->QuoteNo);
            $quoteStatus = $db->QuoteStatus ?? $row->Status ?? '';
            $scmStatus = $db->SCMINTStatus ?? null;

            // REPLACE with:
            if ($quoteStatus === 'Won' && $scmStatus === 'Success') {
                $completed++;
            } elseif ($quoteStatus === 'Won' && !is_null($scmStatus) && $scmStatus !== 'Success') {
                $failedInt++;
            } elseif ($quoteStatus === 'Won') {
                $won++;
            } else {
                $inProgress++;
            }
        }

        $lostCanceled = count($lostCancelledRows);
        $failedProgress = count($failedProgressRows);
        $total = $inProgress + $won + $completed + $failedInt + $failedProgress + $lostCanceled;

        return [
            'total' => $total,
            'in_progress' => $inProgress,
            'won' => $won,
            'completed' => $completed,
            'lost_canceled' => $lostCanceled,
            'failed_int' => $failedInt,
            'failed_progress' => $failedProgress,
        ];
    }

    private function callQuotationListSP(
        string $status,
        string $fromDate = '',
        string $toDate = '',
        string $officeCode = ''
    ): array {
        $inputJson = json_encode([
            'OfficeCode' => $officeCode,   // ← was always ''
            'FromDate' => $fromDate,
            'ToDate' => $toDate,
            'Customer' => '',
            'Service' => '',
            'Process' => '',
            'OriginCountry' => '',
            'DestinationCountry' => '',
            'InCoTerm' => '',
            'Status' => $status,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        try {
            $pdo = DB::connection()->getPdo();
            $stmt = $pdo->prepare("SET NOCOUNT ON; EXEC dbo.sp_Quote_QuotationList ?");
            $stmt->execute([$inputJson]);

            Log::info('callQuotationListSP - executed', [
                'status' => $status,
                'office_code' => $officeCode ?: 'ALL',
                'input_json' => $inputJson,
            ]);

            $rows = [];
            do {
                try {
                    $fetched = $stmt->fetchAll(\PDO::FETCH_OBJ);
                    if (empty($fetched))
                        continue;

                    $firstVal = array_values((array) $fetched[0])[0] ?? '';
                    if (is_string($firstVal) && str_starts_with(trim($firstVal), 'Failed')) {
                        Log::error('callQuotationListSP - SP failure', [
                            'status' => $status,
                            'message' => $firstVal,
                        ]);
                        break;
                    }

                    if (!isset($fetched[0]->QuoteNo))
                        continue;

                    $rows = $fetched;
                    break;
                } catch (\PDOException $e) {
                    continue;
                }
            } while ($stmt->nextRowset());

            return $rows;
        } catch (\Exception $e) {
            Log::error('callQuotationListSP - Exception', [
                'status' => $status,
                'error' => $e->getMessage(),
            ]);
            return [];
        }
    }

    private function scopeRowsByOffice(array $rows, array $allowed, ?string $filterCode = null): array
    {
        if (empty($rows) || empty($allowed)) {
            return [];
        }

        // ── Step 1: Resolve allowed TeamCodes from office codes ───────────────
        $allowedTeamCodes = DB::table('Cust_Office')
            ->whereRaw(
                'RTRIM(LTRIM(Code)) IN (' . implode(',', array_fill(0, count($allowed), '?')) . ')',
                $allowed
            )
            ->where('IsActive', 1)
            ->where('IsDeleted', 0)
            ->pluck('TeamCode')
            ->map(fn($c) => (string) $c)
            ->toArray();

        if (empty($allowedTeamCodes)) {
            return [];
        }

        // ── Step 2: Extract QuoteNos from SP rows ─────────────────────────────
        $quoteNos = array_values(array_filter(array_map(function ($row) {
            return $row->QuoteNo ?? null;
        }, $rows)));

        if (empty($quoteNos)) {
            return [];
        }

        // ── Step 3: Find valid QuoteNos using DB (authoritative mapping) ──────
        $validQuoteNos = DB::table('Quote_QuotationInfo')
            ->whereIn('QuoteNo', $quoteNos)
            ->whereIn('teamid', $allowedTeamCodes)
            ->pluck('QuoteNo')
            ->flip()
            ->toArray();

        // ── Step 4: Filter SP rows based on DB result ─────────────────────────
        $scoped = array_values(array_filter($rows, function ($row) use ($validQuoteNos) {
            $qn = $row->QuoteNo ?? null;
            return $qn && isset($validQuoteNos[$qn]);
        }));

        // ── Step 5: Apply single office dropdown filter (if selected) ─────────
        if ($filterCode) {
            $filterCode = strtoupper(trim($filterCode));

            $filterTeamCode = DB::table('Cust_Office')
                ->whereRaw('RTRIM(LTRIM(Code)) = ?', [$filterCode])
                ->where('IsActive', 1)
                ->where('IsDeleted', 0)
                ->value('TeamCode');

            if ($filterTeamCode) {
                $filterQuoteNos = DB::table('Quote_QuotationInfo')
                    ->whereIn('QuoteNo', array_column($scoped, 'QuoteNo'))
                    ->where('teamid', $filterTeamCode)
                    ->pluck('QuoteNo')
                    ->flip()
                    ->toArray();

                $scoped = array_values(array_filter($scoped, function ($row) use ($filterQuoteNos) {
                    return isset($filterQuoteNos[$row->QuoteNo]);
                }));
            }
        }

        Log::info('scopeRowsByOffice - Result', [
            'input_rows' => count($rows),
            'output_rows' => count($scoped),
            'allowed_offices' => $allowed,
        ]);

        return $scoped;
    }

    // ============================================================
    // UPDATED data()
    // Same office validation pattern as stats()
    // ============================================================
    public function data(Request $request)
    {
        try {
            $days = $request->input('days', '0');
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');
            $cardFilter = $request->input('card_filter', 'in_progress');

            // Use resolved teamnames — matches what teamname column actually stores
            $allowedTeamNames = $this->resolveUserTeamNames();

            if ($allowedTeamNames->isEmpty()) {
                return DataTables::of(DB::table('Quote_QuotationInfo')->whereRaw('1=0'))
                    ->make(true);
            }

            $requestedOffice = $request->input('office');

            // Validate requested office is within the user's allowed teamnames
            $office = ($requestedOffice && $allowedTeamNames->contains($requestedOffice))
                ? $requestedOffice
                : null;

            Log::info('Quotation data request', [
                'days' => $days,
                'requested_office' => $requestedOffice,
                'resolved_office' => $office,
                'allowed_teamnames' => $allowedTeamNames->toArray(),
                'card_filter' => $cardFilter,
            ]);

            $query = DB::table('Quote_QuotationInfo')
                ->select([
                    'ID',
                    'QuoteNo',
                    'bookingno',
                    'companyname',
                    'clientid',
                    'username',
                    'teamname',
                    'teamid',
                    'origincountry',
                    'destinationcountry',
                    'status',
                    'QuoteStatus',
                    'SCMINTStatus',
                    'QuoteStatusDescription',
                    'CreatedOn',
                    'Updatedon',
                ]);

            // ── Date filter ──────────────────────────────────────────────
            if ($days === 'custom' && $startDate && $endDate) {
                $query->whereBetween('createdon', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
            } elseif ($days === '1') {
                $query->whereDate('createdon', now()->subDay());
            } elseif ($days === '15') {
                $query->where('createdon', '>=', now()->subDays(15));
            } elseif ($days === '30') {
                $query->where('createdon', '>=', now()->subDays(30));
            } elseif ($days === '0') {
                $query->whereDate('createdon', today());
            }

            // ── Office filter ────────────────────────────────────────────
            if ($office) {
                // Resolve teamid from Cust_Office — avoids matching person names in teamname
                $teamCode = DB::table('Cust_Office')
                    ->where('Name', $office)
                    ->where('IsActive', 1)
                    ->where('IsDeleted', 0)
                    ->value('TeamCode');

                if ($teamCode) {
                    $query->where('teamid', $teamCode);
                } else {
                    $query->where('teamname', $office); // fallback
                }
            } else {
                // No specific office — scope to all allowed teamids
                $allowedTeamCodes = DB::table('Cust_Office')
                    ->whereIn('Name', $allowedTeamNames->toArray())
                    ->where('IsActive', 1)
                    ->where('IsDeleted', 0)
                    ->pluck('TeamCode');

                if ($allowedTeamCodes->isNotEmpty()) {
                    $query->whereIn('teamid', $allowedTeamCodes->toArray());
                } else {
                    $query->whereIn('teamname', $allowedTeamNames->toArray()); // fallback
                }
            }

            // ── Card filter ───────────────────────────────────────────────
            switch ($cardFilter) {
                case 'total':
                    // No status filter — return all records scoped to office/date only
                    break;

                case 'in_progress':
                    $query->where(function ($q) {
                        $q->whereNotIn('QuoteStatus', ['Won', 'Lost', 'Cancel', 'Completed', 'Failed Progress'])
                            ->orWhereNull('QuoteStatus');
                    })->where(function ($q) {
                        $q->whereNotIn('status', ['Won', 'Lost', 'Cancel', 'Completed', 'Failed Progress'])
                            ->orWhereNull('status');
                    });
                    break;

                case 'won':
                    $query->where('QuoteStatus', 'Won')
                        ->whereNull('SCMINTStatus');
                    break;

                case 'completed':
                    $query->where(function ($q) {
                        $q->where('QuoteStatus', 'Won')->orWhere('status', 'Won');
                    })->where('SCMINTStatus', 'Success');
                    break;

                case 'failed_int':
                    $query->where(function ($q) {
                        $q->where('QuoteStatus', 'Won')->orWhere('status', 'Won');
                    })->whereNotNull('SCMINTStatus')
                        ->where('SCMINTStatus', '!=', 'Success');
                    break;

                case 'lost_canceled':
                    $query->where(function ($q) {
                        $q->whereIn('QuoteStatus', ['Lost', 'Cancel'])
                            ->orWhereIn('status', ['Lost', 'Cancel']);
                    });
                    break;

                case 'failed_progress':
                    $query->where(function ($q) {
                        $q->where('QuoteStatus', 'Failed Progress')
                            ->orWhere('status', 'Failed Progress');
                    });
                    break;
            }

            // ── DataTables (unchanged below this line) ────────────────
            return DataTables::of($query)
                ->filterColumn('OfficeName', fn($q, $k) => $q->where('teamname', 'like', "%$k%"))
                ->filterColumn('ClientName', fn($q, $k) => $q->where('companyname', 'like', "%$k%"))
                ->filterColumn('ClientID', fn($q, $k) => $q->where('clientid', 'like', "%$k%"))
                ->filterColumn('SalesPerson', fn($q, $k) => $q->where('username', 'like', "%$k%"))
                ->filterColumn('OriginCountry', fn($q, $k) => $q->where('origincountry', 'like', "%$k%"))
                ->filterColumn('DestinationCountry', fn($q, $k) => $q->where('destinationcountry', 'like', "%$k%"))
                ->filterColumn('BookingNo', fn($q, $k) => $q->where('bookingno', 'like', "%$k%"))
                ->filterColumn('QuotationStatus', function ($query, $keyword) {
                    $kw = strtolower(trim($keyword));
                    $query->where(function ($q) use ($kw) {
                        if (str_contains($kw, 'progress') || str_contains($kw, 'pending') || str_contains($kw, 'proposal')) {
                            $q->where(function ($sub) {
                                $sub->whereIn('QuoteStatus', ['Proposal', 'Pending', 'In Progress'])
                                    ->orWhereIn('status', ['Proposal', 'Pending'])
                                    ->orWhereNull('QuoteStatus')->orWhereNull('status');
                            });
                        } elseif (str_contains($kw, 'complete')) {
                            $q->where('QuoteStatus', 'Won')->where('status', 'Won')->where('SCMINTStatus', 'Success');
                        } elseif (str_contains($kw, 'won')) {
                            $q->where('QuoteStatus', 'Won')->where('status', 'Won')->whereNull('SCMINTStatus');
                        } elseif (str_contains($kw, 'lost')) {
                            $q->whereIn('QuoteStatus', ['Lost', 'Cancel'])->whereIn('status', ['Lost', 'Cancel']);
                        } elseif (str_contains($kw, 'cancel')) {
                            $q->where('QuoteStatus', 'Cancel')->where('status', 'Cancel');
                        } elseif (str_contains($kw, 'failed') && str_contains($kw, 'progress')) {
                            $q->where('QuoteStatus', 'Failed Progress')->where('status', 'Failed Progress');
                        } elseif (str_contains($kw, 'failed') || str_contains($kw, 'int')) {
                            $q->where('QuoteStatus', 'Won')->where('status', 'Won')
                                ->whereNotNull('SCMINTStatus')->where('SCMINTStatus', '!=', 'Success');
                        } else {
                            $q->where('QuoteStatus', 'like', "%$kw%")->orWhere('status', 'like', "%$kw%");
                        }
                    });
                })
                ->addColumn('report_date', fn($r) => $r->CreatedOn ? date('Y-m-d H:i', strtotime($r->CreatedOn)) : '-')
                ->addColumn('OfficeName', fn($r) => $r->teamname ?? '-')
                ->addColumn('ClientName', fn($r) => $r->companyname ?? '-')
                ->addColumn('ClientID', fn($r) => $r->clientid ?? '-')
                ->addColumn('SalesPerson', fn($r) => $r->username ?? '-')
                ->addColumn('OriginCountry', fn($r) => $r->origincountry ?? '-')
                ->addColumn('DestinationCountry', fn($r) => $r->destinationcountry ?? '-')
                ->addColumn('BookingNo', fn($r) => $r->bookingno ?? '-')
                ->addColumn('QuotationStatus', function ($row) {
                    $status = $row->QuoteStatus ?? $row->status ?? 'Proposal';
                    $scmStatus = $row->SCMINTStatus;
                    if ($status === 'Won' && $scmStatus && $scmStatus !== 'Success')
                        return 'Failed Int.';
                    if ($status === 'Won' && $scmStatus === 'Success')
                        return 'Completed';
                    if ($status === 'Won')
                        return 'Won';
                    if ($status === 'Failed Progress')
                        return 'Failed Progress';
                    if (in_array($status, ['Proposal', 'Pending', 'In Progress']) || is_null($status))
                        return 'In Progress';
                    if (in_array($status, ['Lost', 'Cancel']))
                        return 'Lost / Canceled';
                    return $status;
                })
                ->addColumn('remarks_error', function ($row) {
                    $raw = $row->QuoteStatusDescription ?? null;
                    if (!$raw)
                        return '-';
                    $decoded = json_decode('[' . $raw . ']', true);
                    if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded))
                        return $raw;
                    return implode(' | ', array_column($decoded, 'Remark'));
                })
                ->addColumn('action', function ($row) use ($cardFilter) {
                    $status = $row->QuoteStatus ?? $row->status ?? 'Proposal';
                    $scmStatus = $row->SCMINTStatus;

                    $isInProgress = in_array($status, ['Pending', 'Proposal', 'In Progress', 'Failed Progress']) || is_null($status);
                    $isWon = ($status === 'Won' && is_null($scmStatus));
                    $isFailedInt = ($status === 'Won' && $scmStatus && $scmStatus !== 'Success');
                    $isCompleted = ($status === 'Won' && $scmStatus === 'Success');

                    $viewBtn = '<a href="' . route('quotation.view', $row->ID) . '" class="btn btn-info btn-sm" title="View"><i class="fas fa-eye"></i></a>';

                    $editBtn = '';
                    if ($cardFilter === 'in_progress' && $isInProgress) {
                        $editBtn = ' <a href="' . route('quotation.edit', $row->ID) . '" class="btn btn-warning btn-sm" title="Edit"><i class="fas fa-edit"></i></a>';
                    }

                    $createBookingBtn = '';
                    if (
                        ($cardFilter === 'won' && $isWon) ||
                        ($cardFilter === 'failed_int' && $isFailedInt)
                    ) {
                        $createBookingBtn = ' <button class="btn btn-success btn-sm btn-create-booking"
                            data-id="' . $row->ID . '"
                            data-booking="' . $row->bookingno . '"
                            data-team="' . $row->teamid . '"
                            data-quoteno="' . $row->QuoteNo . '"
                            title="Create Booking"><i class="fas fa-plus-circle"></i></button>';
                    }

                    // Retrigger button — only for Failed Int. rows
                    $retriggerBtn = '';
                    if ($cardFilter === 'failed_int' && $isFailedInt) {
                        $retriggerBtn = ' <button class="btn btn-secondary btn-sm btn-retrigger"
                        data-booking="' . $row->bookingno . '"
                        data-id="' . $row->ID . '"
                        title="Retrigger"><i class="fas fa-sync-alt"></i></button>';
                    }

                    return $viewBtn . $editBtn . $createBookingBtn . $retriggerBtn;
                })
                ->rawColumns(['action', 'remarks_error'])
                ->make(true);
        } catch (\Throwable $e) {
            Log::error('QuotationData 500', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * QuotationController::update()
     * ─────────────────────────────────────────────────────────────────────────────
     * WHAT CHANGED vs. the original:
     *
     *   OLD: Hard-coded array passed every column on every save — partial saves
     *        from the show page would null-out columns not in the request.
     *
     *   NEW: Builds the update payload conditionally using $request->has().
     *        Only columns present in the incoming request are written.
     *        Columns absent from the request are left exactly as they are.
     *
     * This is fully backward-compatible:
     *   • The full edit-page form sends all fields  → behaviour identical to before.
     *   • Per-card saves send only that card's fields → only those columns change.
     *
     * Nothing else in the method was touched (try/catch, logging, response shape).
     * ─────────────────────────────────────────────────────────────────────────────
     */

    public function update(Request $request, string $id): JsonResponse
    {
        try {
            $quotation = DB::table('Quote_QuotationInfo')
                ->where('ID', $id)
                ->first();

            if (!$quotation) {
                return response()->json([
                    'success' => false,
                    'message' => 'Quotation not found',
                ], 404);
            }

            $submittedBy = Auth::check()
                ? (Auth::user()->displayName ?? Auth::user()->Email ?? 'system')
                : 'system';

            Log::info('Update Quotation - Starting', [
                'id' => $id,
                'booking_no' => $quotation->bookingno,
                'updated_by' => $submittedBy,
            ]);

            // ── Whitelist every column that may ever be written ──────────────────
            // Map: request key → DB column name (they happen to match here, but
            // keeping them explicit makes future renames safe).
            $allowed = [
                // Shipment
                'origincountry' => 'origincountry',
                'destinationcountry' => 'destinationcountry',
                'incoterm' => 'incoterm',
                'shipmentmode' => 'shipmentmode',
                'TransportMode' => 'TransportMode',
                'ServiceClass' => 'ServiceClass',
                'serviceverticalname' => 'serviceverticalname',
                'process' => 'process',

                // Cargo
                'commodity' => 'commodity',
                'scopevolume' => 'scopevolume',
                'cargovolume' => 'cargovolume',
                'containerlist' => 'containerlist',

                // Customer
                'companyname' => 'companyname',
                'pri_partner_steet' => 'pri_partner_steet',
                'pri_partner_steet2' => 'pri_partner_steet2',
                'pri_partner_city' => 'pri_partner_city',
                'pri_partner_statename' => 'pri_partner_statename',
                'pri_partner_zip' => 'pri_partner_zip',
                'pri_partner_countryname' => 'pri_partner_countryname',
                'pri_partner_email' => 'pri_partner_email',
                'pri_partner_phone' => 'pri_partner_phone',
                'pri_partnermaster_name' => 'pri_partnermaster_name',
                'clientid' => 'pri_id',
                'industryname' => 'industryname',

                // Inquiry
                'source' => 'source',
                'status' => 'status',
                'username' => 'username',
                'teamname' => 'teamname',

                // Summary
                'expected_revenue' => 'expected_revenue',
                'quoted_revenue' => 'quoted_revenue',
                'quoted_margin' => 'quoted_margin',
                'terms_condition' => 'terms_condition',
            ];

            // ── Build payload: only include fields present in this request ────────
            $updateData = [
                'LastModifiedBy' => $submittedBy,
                'updatedon' => now(),
            ];

            foreach ($allowed as $requestKey => $dbColumn) {
                if ($request->has($requestKey)) {
                    $updateData[$dbColumn] = $request->input($requestKey);
                }
            }

            // Nothing meaningful to write (just audit columns) — bail early.
            if (count($updateData) <= 2) {
                return response()->json([
                    'success' => false,
                    'message' => 'No updatable fields were provided.',
                ], 422);
            }

            DB::table('Quote_QuotationInfo')
                ->where('ID', $id)
                ->update($updateData);

            Log::info('Update Quotation - Success', [
                'id' => $id,
                'booking_no' => $quotation->bookingno,
                'updated_by' => $submittedBy,
                'fields' => array_keys($updateData),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Booking updated successfully',
            ]);
        } catch (\Exception $e) {
            Log::error('Update Quotation - Failed', [
                'id' => $id,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function view($id)
    {
        $quotation = QuotationInfo::where('ID', $id)->firstOrFail();
        return view('quotation.view', compact('quotation'));
    }

    public function edit($id)
    {
        try {
            Log::info('Attempting to load quotation with ID: ' . $id);

            $quotation = QuotationInfo::where('ID', $id)->first();

            if (!$quotation) {
                Log::error('Quotation not found with ID: ' . $id);
                return redirect()
                    ->route('quotation.index')
                    ->with('error', 'Quotation not found');
            }

            // ============================================
            // ADD THIS: Resolve the short office code
            // ============================================
            $officeShortCode = null;

            // Try to get from teamid first (most reliable)
            if ($quotation->teamid) {
                $officeShortCode = DB::table('Cust_Office')
                    ->where('TeamCode', $quotation->teamid)
                    ->where('IsActive', 1)
                    ->where('IsDeleted', 0)
                    ->value('Code');
            }

            // Fallback: try using the OfficeCode field if it's a short code, not a UUID
            if (!$officeShortCode && $quotation->OfficeCode) {
                // Check if OfficeCode is a short code (2 characters) or a UUID
                $isShortCode = preg_match('/^[A-Z]{2}$/', $quotation->OfficeCode);
                if ($isShortCode) {
                    $officeShortCode = $quotation->OfficeCode;
                } else {
                    // It's a UUID, try to look it up
                    $officeShortCode = DB::table('Cust_Office')
                        ->where('Id', $quotation->OfficeCode)
                        ->where('IsActive', 1)
                        ->where('IsDeleted', 0)
                        ->value('Code');
                }
            }

            // Final fallback: use the office base currency mapping if available
            if (!$officeShortCode && $quotation->OfficeCode) {
                // Check if the OfficeCode exists in our base currency map as a key
                $officeShortCode = array_key_exists($quotation->OfficeCode, self::OFFICE_BASE_CURRENCY)
                    ? $quotation->OfficeCode
                    : null;
            }

            Log::info('Office short code resolved', [
                'id' => $quotation->ID,
                'teamid' => $quotation->teamid,
                'office_code_field' => $quotation->OfficeCode,
                'office_short_code' => $officeShortCode
            ]);

            Log::info('Quotation data loaded', [
                'id' => $quotation->ID,
                'bookingno' => $quotation->bookingno,
                'companyname' => $quotation->companyname
            ]);

            return view('quotation.edit', compact('quotation', 'officeShortCode'));
        } catch (\Exception $e) {
            Log::error('Error loading quotation for edit: ' . $e->getMessage(), [
                'id' => $id,
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()
                ->route('quotation.index')
                ->with('error', 'Failed to load quotation: ' . $e->getMessage());
        }
    }

    // public function copy($bookingno)
    // {
    //     try {
    //         // ============================================
    //         // FIND ORIGINAL QUOTATION UUID BY BOOKING NUMBER
    //         // ============================================
    //         $original = DB::table('Quote_QuotationInfo')
    //             ->where('bookingno', $bookingno)
    //             ->select('ID', 'bookingno')
    //             ->first();

    //         if (!$original) {
    //             Log::error('Copy Quotation - Original not found', [
    //                 'bookingno' => $bookingno
    //             ]);

    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'Quotation not found for booking number: ' . $bookingno
    //             ], 404);
    //         }

    //         $submittedBy = Auth::check()
    //             ? (Auth::user()->displayName ?? Auth::user()->Email ?? 'system')
    //             : 'system';

    //         Log::info('Copy Quotation - Starting', [
    //             'original_booking_no' => $original->bookingno,
    //             'original_id'         => $original->ID,
    //             'copied_by'           => $submittedBy,
    //         ]);

    //         // ============================================
    //         // CALL COPY SP
    //         // Handles header + charges internally
    //         //EXEC dbo.sp_Quote_QuotationCopy ?, (integer primary key of the original quotation)

    //         // ============================================
    //         $pdo = DB::getPdo();
    //         $sql = "
    //         DECLARE @Status  VARCHAR(25)
    //         DECLARE @Remark  VARCHAR(200)
    //         DECLARE @QuoteNo VARCHAR(50)
    //         EXEC dbo.sp_Quote_QuotationCopy ?,
    //             @Status  = @Status  OUTPUT,
    //             @Remark  = @Remark  OUTPUT,
    //             @QuoteNo = @QuoteNo OUTPUT
    //         SELECT @Status AS Status, @Remark AS Remark, @QuoteNo AS QuoteNo
    //     ";

    //         $stmt = $pdo->prepare($sql);
    //         $stmt->execute([$original->QuoteNo]);

    //         // SP emits an intermediate result set before the final OUTPUT select
    //         // Skip result sets until we find the one with Status/Remark/QuoteNo
    //         $result = null;
    //         do {
    //             try {
    //                 $row = $stmt->fetch(\PDO::FETCH_ASSOC);
    //                 if ($row && isset($row['Status'])) {
    //                     $result = $row;
    //                     break;
    //                 }
    //             } catch (\Exception $e) {
    //                 // empty result set — skip and advance
    //             }
    //         } while ($stmt->nextRowset());

    //         // FIX 1: SP has a known typo — 'Sucess' instead of 'Success' — accept both
    //         if (!$result || strtolower(trim($result['Status'] ?? '')) !== 'success') {
    //             $remark = $result['Remark'] ?? 'Unknown error from copy SP';

    //             Log::error('Copy Quotation - SP Failed', [
    //                 'original_booking_no' => $original->bookingno,
    //                 'sp_status'           => $result['Status'] ?? null,
    //                 'sp_remark'           => $remark,
    //             ]);

    //             return response()->json([
    //                 'success' => false,
    //                 'message' => $remark
    //             ], 500);
    //         }

    //         $newBookingNo = trim($result['QuoteNo']);

    //         // ============================================
    //         // FETCH NEW QUOTATION ID FOR REDIRECT
    //         // FIX 2: $newBookingNo is the formatted QuoteNo string (e.g. 'KE-QT-25-00002'),
    //         // NOT the integer bookingno — must query by QuoteNo column.
    //         // ============================================
    //         $newQuotation = DB::table('Quote_QuotationInfo')
    //             ->where('QuoteNo', $newBookingNo)
    //             ->select('ID', 'bookingno')
    //             ->first();

    //         if (!$newQuotation) {
    //             Log::error('Copy Quotation - New record not found after SP', [
    //                 'new_quote_no' => $newBookingNo,
    //             ]);

    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'Copy succeeded but new quotation record could not be located.'
    //             ], 500);
    //         }

    //         Log::info('Copy Quotation - Success', [
    //             'original_booking_no' => $original->bookingno,
    //             'new_quote_no'        => $newBookingNo,
    //             'new_bookingno'       => $newQuotation->bookingno,
    //             'new_id'              => $newQuotation->ID,
    //             'copied_by'           => $submittedBy,
    //         ]);

    //         return response()->json([
    //             'success'        => true,
    //             'new_booking_no' => $newBookingNo,
    //             'new_id'         => $newQuotation->ID,
    //             'redirect_url'   => route('quotation.edit', $newQuotation->ID) . '?copied=1',
    //         ]);
    //     } catch (\Exception $e) {

    //         Log::error('Copy Quotation - Failed', [
    //             'original_bookingno' => $bookingno,
    //             'error'              => $e->getMessage(),
    //             'file'               => $e->getFile(),
    //             'line'               => $e->getLine(),
    //         ]);

    //         return response()->json([
    //             'success' => false,
    //             'message' => $e->getMessage()
    //         ], 500);
    //     }
    // }

    // ============================================================
    // NEW PRIVATE METHOD — single source of truth for office resolution
    // Matching priority:
    //   1. Azure_Users.userPrincipalName = Auth::user()->Email
    //   2. Azure_Users.mail              = Auth::user()->Email (fallback)
    // Returns empty collection if no BaseOfficeCode found.
    // ============================================================
    private function resolveUserOfficeCodes(): \Illuminate\Support\Collection
    {
        $authUser = Auth::user();

        $azureUser = DB::table('Azure_Users')
            ->where(function ($q) use ($authUser) {
                $q->whereRaw('BizUserID = ?', [$authUser->ID])
                    ->orWhereRaw(
                        'LOWER(LTRIM(RTRIM(userPrincipalName))) = ?',
                        [strtolower(trim($authUser->Email ?? ''))]
                    )
                    ->orWhereRaw(
                        'LOWER(LTRIM(RTRIM(mail))) = ?',
                        [strtolower(trim($authUser->Email ?? ''))]
                    );
            })
            ->whereNotNull('BaseOfficeCode')
            ->where('BaseOfficeCode', '!=', '')
            ->whereRaw("LTRIM(RTRIM(BaseOfficeCode)) != ''")
            ->select('userPrincipalName', 'BaseOfficeCode')
            ->first();

        if (!$azureUser) {
            Log::warning('resolveUserOfficeCodes - No Azure user or BaseOfficeCode found', [
                'biz_user_id' => $authUser->ID,
                'email' => $authUser->Email,
            ]);
            return collect();
        }

        Log::info('resolveUserOfficeCodes - Raw BaseOfficeCode', [
            'raw' => $azureUser->BaseOfficeCode,
            'email' => $authUser->Email,
        ]);

        return collect(explode(',', $azureUser->BaseOfficeCode))
            ->map(function (string $segment) {
                // trim() handles trailing spaces: 'KE ' → 'KE'
                // regex handles extra text:       'RW- A014' → 'RW'
                $segment = trim($segment);

                if (preg_match('/^([A-Za-z]{2})\b/', $segment, $matches)) {
                    return strtoupper($matches[1]);
                }

                return strtoupper($segment);
            })
            ->filter(fn($code) => preg_match('/^[A-Z]{2}$/', $code))
            ->unique()
            ->values();
    }

    private function resolveUserOfficesViaSP(): array
    {
        $authUser = Auth::user();

        $azureUser = DB::table('Azure_Users')
            ->where(function ($q) use ($authUser) {
                $q->whereRaw('BizUserID = ?', [$authUser->ID])
                    ->orWhereRaw('LOWER(LTRIM(RTRIM(userPrincipalName))) = ?', [strtolower(trim($authUser->Email ?? ''))])
                    ->orWhereRaw('LOWER(LTRIM(RTRIM(mail))) = ?', [strtolower(trim($authUser->Email ?? ''))]);
            })
            ->whereNotNull('AzureId')
            ->select('AzureId')
            ->first();

        $empty = ['default' => null, 'offices' => collect()];

        if (!$azureUser || empty($azureUser->AzureId)) {
            Log::warning('resolveUserOfficesViaSP - No AzureId found', [
                'email' => $authUser->Email,
            ]);
            return $empty;
        }

        try {
            $pdo = DB::connection()->getPdo();
            $stmt = $pdo->prepare("
            SET NOCOUNT ON;
            DECLARE @OutputJson VARCHAR(2000);
            EXEC dbo.sp_Azure_BIZOfficeandModuleList ?, '', @OutputJson OUTPUT;
            SELECT @OutputJson AS OutputJson;
        ");
            $stmt->execute([$azureUser->AzureId]);

            $outputJson = null;
            do {
                try {
                    $rows = $stmt->fetchAll(\PDO::FETCH_OBJ);
                    if (!empty($rows) && isset($rows[0]->OutputJson)) {
                        $outputJson = $rows[0]->OutputJson;
                        break;
                    }
                } catch (\PDOException $e) {
                    continue;
                }
            } while ($stmt->nextRowset());

            if (empty($outputJson)) {
                Log::warning('resolveUserOfficesViaSP - SP returned no output', [
                    'email' => $authUser->Email,
                    'azure_id' => $azureUser->AzureId,
                ]);
                return $empty;
            }

            $data = json_decode($outputJson, true);

            if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
                Log::warning('resolveUserOfficesViaSP - JSON decode failed', [
                    'email' => $authUser->Email,
                    'output' => $outputJson,
                ]);
                return $empty;
            }

            if (isset($data['Status']) && $data['Status'] === 'Failed') {
                Log::warning('resolveUserOfficesViaSP - SP guard failed', [
                    'email' => $authUser->Email,
                    'remarks' => $data['Remarks'] ?? 'unknown',
                ]);
                return $empty;
            }

            // BasedOfficeName is the user's BaseOfficeCode resolved to its full name.
            // This is always the default — it comes from Azure_Users.BaseOfficeCode.
            $defaultOffice = !empty($data['BasedOfficeName'])
                ? trim($data['BasedOfficeName'])
                : null;

            $offices = collect();

            if ($defaultOffice) {
                $offices->push($defaultOffice);
            }

            if (!empty($data['OtherOffices']) && is_array($data['OtherOffices'])) {
                foreach ($data['OtherOffices'] as $other) {
                    if (!empty($other['OfficeName'])) {
                        $offices->push(trim($other['OfficeName']));
                    }
                }
            }

            $offices = $offices->unique()->values();

            Log::info('resolveUserOfficesViaSP - Resolved', [
                'email' => $authUser->Email,
                'default_office' => $defaultOffice,
                'all_offices' => $offices->toArray(),
            ]);

            return [
                'default' => $defaultOffice,
                'offices' => $offices,
            ];
        } catch (\Exception $e) {
            Log::error('resolveUserOfficesViaSP - Exception', [
                'email' => $authUser->Email,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            return $empty;
        }
    }

    private function resolveAllowedOfficeCodesViaSP(): \Illuminate\Support\Collection
    {
        $resolved = $this->resolveUserOfficesViaSP();
        $officeNames = $resolved['offices'];

        if ($officeNames->isEmpty()) {
            return collect();
        }

        return DB::table('Cust_Office')
            ->whereIn('Name', $officeNames->toArray())
            ->where('IsActive', 1)
            ->where('IsDeleted', 0)
            ->pluck('Code')
            ->map(fn($c) => strtoupper(trim($c)))
            ->unique()
            ->values();
    }

    public function getChargeDetails(Request $request)
    {
        $bookingNo = $request->input('booking_no');
        $chargeId = $request->input('charge_id');
        $quotationId = trim($request->input('quotation_id', ''));

        if (!$bookingNo || !$chargeId) {
            return response()->json([
                'success' => false,
                'message' => 'booking_no and charge_id are required'
            ], 400);
        }

        try {
            // ---------------------------------------------------------------
            // FIX: Derive QuotationInfoID directly from the charge row itself.
            // Previously we looked up Quote_QuotationInfo by bookingno, which
            // returns the first match and fails when a booking has multiple
            // quotation revisions — the charge may belong to a different one.
            // ---------------------------------------------------------------
            $supplement = DB::selectOne("
            SELECT
                QC.QuotationInfoID,
                QC.IsRevenue,
                QC.Quantity,
                ISNULL(QC.Remarks,        '')       AS ChargeRemark,
                ISNULL(CP.Code,           '')       AS PartyCode,
                ISNULL(QC.ChargeType,     'manual') AS SourceType,
                ISNULL(CB.Code,           '')       AS BasedOnCode,
                ISNULL(EQ.Code,           '')       AS UOMCode_Equipment,
                ISNULL(UOM.Code,          '')       AS UOMCode_UOM
            FROM Quote_Quotation_ChargeSheet QC WITH (NOLOCK)
            LEFT JOIN Cust_ChargecodeBasedOn          CB  WITH (NOLOCK) ON CB.Id  = QC.BasedOnId
            LEFT JOIN [FITSCMUAT].dbo.Cust_Party      CP  WITH (NOLOCK) ON CP.Id  = QC.PartyID
            LEFT JOIN [FITSCMUAT].dbo.Cust_Equipment  EQ  WITH (NOLOCK) ON EQ.Id  = QC.UOMId AND QC.UOMType = 'Equipment'
            LEFT JOIN [FITSCMUAT].dbo.Sys_UOM         UOM WITH (NOLOCK) ON UOM.Id = QC.UOMId AND QC.UOMType != 'Equipment'
            WHERE QC.Id = ? AND QC.IsDeleted = 0
        ", [$chargeId]);

            if (!$supplement) {
                return response()->json(['success' => false, 'message' => 'Charge not found'], 404);
            }

            $isRevenue = (int) $supplement->IsRevenue;
            $quotationInfoId = $supplement->QuotationInfoID; // guaranteed to match SP's FK check

            // Call SP — now passing the QuotationInfoID that owns this charge
            $pdo = DB::connection()->getPdo();
            $stmt = $pdo->prepare("SET NOCOUNT ON; EXEC dbo.sp_Quote_Quotation_ChargeList ?, ?, ?");
            $stmt->bindValue(1, $quotationInfoId, \PDO::PARAM_STR);
            $stmt->bindValue(2, $isRevenue, \PDO::PARAM_INT);
            $stmt->bindValue(3, $chargeId, \PDO::PARAM_STR);
            $stmt->execute();

            $spRow = null;
            do {
                try {
                    $rows = $stmt->fetchAll(\PDO::FETCH_OBJ);
                    if (empty($rows))
                        continue;

                    $firstVal = array_values((array) $rows[0])[0] ?? '';
                    if (is_string($firstVal) && str_starts_with($firstVal, 'Failed')) {
                        Log::error('getChargeDetails - SP failure (DBA action required)', [
                            'charge_id' => $chargeId,
                            'quotation_info_id' => $quotationInfoId,
                            'message' => $firstVal,
                        ]);
                        return response()->json([
                            'success' => false,
                            'message' => 'sp_Quote_Quotation_ChargeList: ' . $firstVal,
                            'dba_action_required' => true,
                        ], 500);
                    }

                    if (isset($rows[0]->Id)) {
                        $spRow = $rows[0];
                        break;
                    }
                } catch (\PDOException $e) {
                    continue;
                }
            } while ($stmt->nextRowset());

            if (!$spRow) {
                return response()->json(['success' => false, 'message' => 'Charge not found via SP'], 404);
            }

            return response()->json([
                'success' => true,
                'id' => $spRow->Id,
                'chargeCode' => $spRow->ChargeCode ?? '',
                'chargeName' => $spRow->ChargeDesp ?? '',
                'rate' => (float) ($spRow->EstChargeRate ?? 0),
                'quantity' => (float) ($supplement->Quantity ?? 0),
                'amtInChgCurr' => (float) ($spRow->EstChargeAmount ?? 0),
                'amtInBaseCurr' => (float) ($spRow->EstBaseAmount ?? 0),
                'exChangeRate' => (float) ($spRow->BaseExchRate ?? 1),
                'chgCurr' => $spRow->EstChargeCurrCode ?? 'KES',
                'basedOnCode' => $supplement->BasedOnCode ?? '',
                'basedOnAndUOM' => $spRow->BasedOnAndUOM ?? '',
                'uomCode' => (!empty($supplement->UOMCode_Equipment)
                    ? $supplement->UOMCode_Equipment
                    : $supplement->UOMCode_UOM) ?? '',
                'chargeRemark' => $supplement->ChargeRemark ?? '',
                'partyCode' => $supplement->PartyCode ?? '',
                'revenueOrCost' => $isRevenue ? 'Revenue' : 'Cost',
                'sourceType' => $supplement->SourceType ?? 'manual',
                'increaseBy' => (float) ($supplement->IncreaseBy ?? 0),
                'increaseType' => $supplement->IncreaseType ?? 'Percentage',
            ]);
        } catch (\Exception $e) {
            Log::error('getChargeDetails failed', [
                'booking_no' => $bookingNo,
                'charge_id' => $chargeId,
                'error' => $e->getMessage(),
            ]);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function saveCharge(Request $request)
    {
        try {
            // ============================================
            // GET QUOTATION CONTEXT FOR DETAILED LOGGING
            // ============================================

            $bookingNo = $request->input('booking_no');
            $officeCodeFromRequest = $request->input('office_code');

            $quotationIdFromRequest = trim($request->input('quotation_id', ''));

            $quotation = $quotationIdFromRequest
                ? DB::table('Quote_QuotationInfo')
                ->where('ID', $quotationIdFromRequest)
                ->first(['ID', 'teamid', 'ServiceVerticalname', 'origincountry', 'destinationcountry', 'QuoteStatus', 'status'])
                : DB::table('Quote_QuotationInfo')
                ->where('bookingno', $bookingNo)
                ->when($officeCodeFromRequest, fn($q) => $q->where('OfficeCode', $officeCodeFromRequest))
                ->first(['ID', 'teamid', 'ServiceVerticalname', 'origincountry', 'destinationcountry', 'QuoteStatus', 'status']);

            $officeCode = 'Unknown';
            if ($quotation) {
                $office = DB::table('Cust_Office')
                    ->where('TeamCode', $quotation->teamid)
                    ->where('IsActive', 1)
                    ->where('IsDeleted', 0)
                    ->first(['Code']);
                $officeCode = $office ? trim($office->Code) : 'Unknown';
            }

            // ============================================
            // LOG INCOMING REQUEST WITH CONTEXT
            // ============================================
            Log::info('Save Charge Request - Start', [
                'booking_no' => $bookingNo,
                'charge_code' => $request->input('charge_code'),
                'based_on_code' => $request->input('based_on_code'),
                'based_on_uom' => $request->input('based_on_uom'),
                'action' => $request->input('action'),
                'revenue_or_cost' => $request->input('revenue_or_cost'),
                'user' => Auth::user()->displayName ?? Auth::user()->Email ?? 'system',
                'context' => [
                    'quotation_id' => $quotation->ID ?? 'N/A',
                    'office_code' => $officeCode,
                    'service_type' => $quotation->ServiceVerticalname ?? 'N/A',
                    'status' => $quotation->QuoteStatus ?? $quotation->status ?? 'Unknown',
                ]
            ]);

            // ============================================
            // VALIDATE REQUIRED FIELDS
            // ============================================
            $validator = \Validator::make($request->all(), [
                'booking_no' => 'required|string',
                'office_code' => 'required|string',
                'charge_code' => 'required|string',
                'based_on_code' => 'required|string',
                'revenue_or_cost' => 'required|string',
                'action' => 'required|in:Add,Edit,Delete',
            ]);

            if ($validator->fails()) {
                Log::warning('Save Charge Validation Failed', [
                    'booking_no' => $bookingNo,
                    'errors' => $validator->errors()->toArray(),
                    'request_data' => $request->all()
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed: ' . $validator->errors()->first(),
                    'errors' => $validator->errors()->toArray()
                ], 422);
            }

            // ============================================
            // VERIFY QUOTATION EXISTS
            // ============================================

            if (!$quotation) {
                Log::error('Save Charge - Quotation not found', [
                    'booking_no' => $bookingNo,
                    'action' => $request->input('action'),
                    'user' => Auth::user()->displayName ?? Auth::user()->Email ?? 'system'
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Quotation not found for booking number: ' . $bookingNo
                ], 404);
            }

            // Validate RevenueOrCost matches exactly what the SP expects
            $validChargeTypes = ['Revenue', 'Cost', 'Cost with Markup'];
            if (!in_array($request->input('revenue_or_cost'), $validChargeTypes)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid charge type. Must be Revenue, Cost, or Cost with Markup'
                ], 422);
            }

            // ============================================
            // DEBUG AUTH USER
            // ============================================

            $user = Auth::user();
            Log::info('Current authenticated user', [
                'user' => $user ? [
                    'id' => $user->id ?? null,
                    'username' => $user->username ?? null,
                    'email' => $user->email ?? null,
                    'name' => $user->name ?? null,
                ] : 'No user authenticated'
            ]);

            // ============================================
            // PREPARE STORED PROCEDURE INPUT
            // ============================================
            // $quoteNo = DB::table('Quote_QuotationInfo')
            //     ->where('bookingno', $bookingNo)
            //     ->where('OfficeCode', $request->input('office_code'))
            //     ->value('QuoteNo');

            // if (!$quoteNo) {
            //     return response()->json([
            //         'success' => false,
            //         'message' => 'QuoteNo not found for booking: ' . $bookingNo,
            //     ], 404);
            // }

            $inputJson = json_encode([
                'OperationType' => 'QuotationAddorEditCharge',
                'SubmittedDatetime' => now()->format('YmdHis'),
                'SubmittedBy' => Auth::check()
                    ? (Auth::user()->displayName ?? Auth::user()->Email ?? 'system')
                    : 'system',
                'Action' => $request->input('action', 'Add'),

                'QuotationID' => $quotation->ID ?? '',           // ←←← THIS IS THE FIX

                'QuoteChargeID' => $request->input('quote_charge_id', ''),
                'PartyCode' => $request->input('party_code', ''),
                'RevenueOrCost' => $request->input('revenue_or_cost'),
                'ChargeCode' => $request->input('charge_code'),
                'Quantity' => (float) $request->input('quantity', 0),
                'Rate' => (float) $request->input('rate', 0),
                'AmtInChgCurr' => (float) $request->input('amt_in_chg_curr', 0),
                'AmtInBaseCurr' => (float) $request->input('amt_in_base_curr', 0),
                'ExChangeRate' => (float) $request->input('exchange_rate', 1),
                'ChgCurr' => $request->input('chg_curr', 'KES'),
                'BasedOnCode' => $request->input('based_on_code'),
                'BasedOnUOM' => $request->input('based_on_uom', ''),
                'ChargeRemark' => $request->input('charge_remark', ''),
                'ToBeAdded' => (int) $request->input('to_be_added', 0),
                'IncreaseBy' => (float) $request->input('increase_by', 0),
                'IncreaseType' => $request->input('increase_type', 'Percentage'),
                'SourceType' => ucfirst(strtolower($request->input('source_type', 'manual')))
            ]);

            Log::info('Stored Procedure Input JSON', [
                'booking_no' => $bookingNo,
                'action' => $request->input('action'),
                'json' => $inputJson,
                'json_decoded' => json_decode($inputJson, true)
            ]);

            // ============================================

            // EXECUTE STORED PROCEDURE WITH PDO

            // ============================================
            Log::info('Executing Stored Procedure: sp_Quote_Quotaiton_ChargesAddorEdit', [
                'booking_no' => $bookingNo,
                'office_code' => $officeCode
            ]);

            $pdo = DB::connection()->getPdo();
            $stmt = $pdo->prepare("
                SET NOCOUNT ON;
                DECLARE @OutputJson VARCHAR(2000);
                EXEC sp_Quote_Quotaiton_ChargesAddorEdit ?, @OutputJson OUTPUT;
                SELECT @OutputJson AS OutputJson

                ");

            $executeSuccess = $stmt->execute([$inputJson]);
            if (!$executeSuccess) {
                $errorInfo = $stmt->errorInfo();
                Log::error('Stored procedure execution failed', [
                    'booking_no' => $bookingNo,
                    'error_code' => $errorInfo[0] ?? 'Unknown',
                    'sql_state' => $errorInfo[1] ?? 'Unknown',
                    'error_message' => $errorInfo[2] ?? 'Unknown',
                    'input_json' => $inputJson
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Failed to execute stored procedure',
                    'error_details' => config('app.debug') ? $errorInfo[2] : null
                ], 500);
            }

            // ============================================
            // ENHANCED RESULT SET PROCESSING
            // ============================================

            $result = null;
            $resultSetCount = 0;

            do {
                $resultSetCount++;
                try {
                    $rows = $stmt->fetchAll(\PDO::FETCH_OBJ);
                    Log::debug("Result set #$resultSetCount", [
                        'booking_no' => $bookingNo,
                        'count' => count($rows),
                        'first_row' => !empty($rows) ? (array) $rows[0] : null
                    ]);

                    // ============================================
                    // ENHANCED ERROR DETECTION IN RESULT SET
                    // ============================================

                    if (!empty($rows) && is_object($rows[0])) {
                        $properties = get_object_vars($rows[0]);

                        // Check for error indicators
                        $hasError = false;
                        foreach ($properties as $key => $value) {
                            if (
                                empty($key) ||
                                $key === '' ||
                                (is_string($value) && (
                                    strpos($value, 'Error Message') !== false ||
                                    strpos($value, 'Failed -') !== false
                                ))
                            ) {
                                Log::error('Stored procedure returned error object:', [
                                    'booking_no' => $bookingNo,
                                    'result_set' => $resultSetCount,
                                    'error_property_name' => $key,
                                    'error_message' => $value,
                                    'all_properties' => $properties,
                                    'context' => [
                                        'office_code' => $officeCode,
                                        'service_type' => $quotation->ServiceVerticalname ?? 'N/A',
                                        'action' => $request->input('action'),
                                    ]
                                ]);
                                $hasError = true;
                                break;
                            }
                        }

                        if ($hasError) {
                            continue; // Skip this result set
                        }

                        // Valid result set with OutputJson
                        if (isset($rows[0]->OutputJson)) {
                            $result = $rows;
                            Log::info('✓ Found OutputJson in result set #' . $resultSetCount, [
                                'booking_no' => $bookingNo
                            ]);
                            break;
                        }
                    }
                } catch (\PDOException $e) {

                    // Skip empty result sets
                    Log::debug("Result set #$resultSetCount error (skipping)", [
                        'booking_no' => $bookingNo,
                        'error' => $e->getMessage()
                    ]);
                    continue;
                }
            } while ($stmt->nextRowset());

            if (!$result) {
                Log::error('Stored procedure did not return OutputJson', [
                    'booking_no' => $bookingNo,
                    'total_result_sets' => $resultSetCount,
                    'action' => $request->input('action'),
                    'office_code' => $officeCode,
                    'probable_cause' => 'Stored procedure may have failed silently or returned unexpected format'
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Stored procedure did not return expected output'
                ], 500);
            }

            // ============================================
            // LOG RAW STORED PROCEDURE OUTPUT
            // ============================================

            Log::info('Stored Procedure Raw Output', [
                'booking_no' => $bookingNo,
                'result' => $result,
                'output_json' => $result[0]->OutputJson ?? null
            ]);

            // ============================================
            // PARSE OUTPUT WITH ENHANCED ERROR HANDLING
            // ============================================

            $outputJson = $result[0]->OutputJson ?? '{}';
            $output = json_decode($outputJson, true);

            // Check if JSON decode failed

            if (json_last_error() !== JSON_ERROR_NONE) {

                Log::error('Failed to decode stored procedure output', [
                    'booking_no' => $bookingNo,
                    'json_error' => json_last_error_msg(),
                    'json_error_code' => json_last_error(),
                    'raw_output' => $outputJson,
                    'context' => [
                        'office_code' => $officeCode,
                        'action' => $request->input('action'),
                    ]
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Failed to parse stored procedure response: ' . json_last_error_msg(),
                    'error_details' => config('app.debug') ? $outputJson : null
                ], 500);
            }

            Log::info('Stored Procedure Parsed Output', [
                'booking_no' => $bookingNo,
                'output' => $output,
                'status' => $output['Status'] ?? 'Unknown',
                'remarks' => $output['Remarks'] ?? []
            ]);

            // ============================================
            // HANDLE SUCCESS RESPONSE
            // ============================================

            if (($output['Status'] ?? '') === 'Success') {
                Log::info('Charge saved successfully', [
                    'booking_no' => $bookingNo,
                    'charge_id' => $output['ChargeID'] ?? null,
                    'action' => $request->input('action'),
                    'revenue_or_cost' => $request->input('revenue_or_cost'),
                    'charge_code' => $request->input('charge_code'),
                    'amount' => $request->input('amt_in_base_curr'),
                    'user' => Auth::user()->displayName ?? Auth::user()->Email ?? 'system'
                ]);

                return response()->json([
                    'success' => true,
                    'message' => $output['Remarks'][0]['Remark'] ?? 'Charge saved successfully',
                    'charge_id' => $output['ChargeID'] ?? null,
                    'data' => $output
                ]);
            }

            // ============================================
            // HANDLE FAILURE RESPONSE
            // ============================================

            $errorMessage = $output['Remarks'][0]['Remark'] ?? 'Failed to save charge';
            Log::warning('Stored Procedure returned failure', [
                'booking_no' => $bookingNo,
                'status' => $output['Status'] ?? 'Unknown',
                'message' => $errorMessage,
                'full_output' => $output,
                'context' => [
                    'office_code' => $officeCode,
                    'action' => $request->input('action'),
                    'charge_code' => $request->input('charge_code'),
                    'revenue_or_cost' => $request->input('revenue_or_cost'),
                ]
            ]);

            return response()->json([
                'success' => false,
                'message' => $errorMessage,
                'details' => $output['Remarks'] ?? []
            ], 422);
        } catch (\PDOException $e) {

            // ============================================
            // PDO-SPECIFIC ERRORS
            // ============================================

            Log::error('Save charge failed with PDO exception', [
                'booking_no' => $request->input('booking_no'),
                'error_message' => $e->getMessage(),
                'error_code' => $e->getCode(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'sql_state' => $e->errorInfo[0] ?? 'Unknown',
                'driver_error_code' => $e->errorInfo[1] ?? 'Unknown',
                'driver_error_message' => $e->errorInfo[2] ?? 'Unknown',
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Database connection error while saving charge',
                'error_code' => 'PDO_ERROR',
                'details' => config('app.debug') ? [
                    'message' => $e->getMessage(),
                    'sql_state' => $e->errorInfo[0] ?? null,
                ] : null

            ], 500);
        } catch (\Illuminate\Database\QueryException $e) {

            // ============================================
            // LARAVEL QUERY ERRORS
            // ============================================

            Log::error('Save charge failed with query exception', [
                'booking_no' => $request->input('booking_no'),
                'error_message' => $e->getMessage(),
                'error_code' => $e->getCode(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'sql' => $e->getSql() ?? 'N/A',
                'bindings' => $e->getBindings() ?? [],
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Database query error while saving charge',
                'error_code' => 'QUERY_ERROR',
                'details' => config('app.debug') ? [
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ] : null

            ], 500);
        } catch (\Exception $e) {

            // ============================================
            // GENERAL ERRORS
            // ============================================

            Log::error('Save charge failed with general exception', [
                'booking_no' => $request->input('booking_no'),
                'error_message' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to save charge: ' . $e->getMessage(),
                'error_code' => 'GENERAL_ERROR',
                'details' => config('app.debug') ? [
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ] : null

            ], 500);
        }
    }

    public function deleteCharge(Request $request)
    {
        try {
            $bookingNo = $request->input('booking_no');
            $chargeId = $request->input('quote_charge_id');

            // ============================================
            // VALIDATE REQUIRED FIELDS
            // ============================================

            if (!$bookingNo || !$chargeId) {
                Log::warning('Delete Charge - Missing required fields', [
                    'booking_no' => $bookingNo,
                    'charge_id' => $chargeId,
                    'user' => Auth::user()->displayName ?? Auth::user()->Email ?? 'system'
                ]);

                return response()->json([
                    'success' => false,
                    'message' => !$bookingNo ? 'Booking number is required' : 'Charge ID is required'
                ], 400);
            }

            // ============================================
            // GET QUOTATION CONTEXT FOR DETAILED LOGGING
            // ============================================

            $officeCodeFromRequest = $request->input('office_code');

            $quotation = DB::table('Quote_QuotationInfo')
                ->where('bookingno', $bookingNo)
                ->when($officeCodeFromRequest, fn($q) => $q->where('OfficeCode', $officeCodeFromRequest))
                ->first(['ID', 'teamid', 'ServiceVerticalname', 'origincountry', 'destinationcountry', 'QuoteStatus', 'status']);

            $officeCode = 'Unknown';

            if ($quotation) {
                $office = DB::table('Cust_Office')
                    ->where('TeamCode', $quotation->teamid)
                    ->where('IsActive', 1)
                    ->where('IsDeleted', 0)
                    ->first(['Code']);
                $officeCode = $office ? trim($office->Code) : 'Unknown';
            }

            Log::info('Delete Charge - Starting', [
                'booking_no' => $bookingNo,
                'charge_id' => $chargeId,
                'user' => Auth::user()->displayName ?? Auth::user()->Email ?? 'system',
                'context' => [
                    'quotation_id' => $quotation->ID ?? 'N/A',
                    'office_code' => $officeCode,
                    'service_type' => $quotation->ServiceVerticalname ?? 'N/A',
                    'origin' => $quotation->origincountry ?? 'N/A',
                    'destination' => $quotation->destinationcountry ?? 'N/A',
                    'current_status' => $quotation->QuoteStatus ?? $quotation->status ?? 'Unknown',
                ]
            ]);

            // ============================================
            // VERIFY QUOTATION EXISTS
            // ============================================
            if (!$quotation) {
                Log::error('Delete Charge - Quotation not found', [
                    'booking_no' => $bookingNo,
                    'charge_id' => $chargeId,
                    'user' => Auth::user()->displayName ?? Auth::user()->Email ?? 'system'
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Quotation not found for booking number: ' . $bookingNo
                ], 404);
            }

            // ============================================
            // FETCH CHARGE DETAILS WITH ENHANCED VALIDATION
            // ============================================

            $charge = DB::selectOne("
            SELECT
                QC.Id as QuoteChargeID,
                BC.Code as ChargeCode,
                BC.Name as ChargeName,
                QC.Quantity,
                QC.EstChargeRate as Rate,
                QC.EstChargeAmount as AmtInChgCurr,
                QC.EstBaseAmount as AmtInBaseCurr,
                QC.BaseExchRate as ExChangeRate,
                CC.Code as ChgCurr,
                CB.Code as BasedOnCode,
                ISNULL(SU.Code, '') as BasedOnUOM,
                ISNULL(QC.Remarks, '') as ChargeRemark,
                QC.IsRevenue,
                ISNULL(CP.Code, '') as PartyCode,
                ISNULL(QC.ChargeType, 'manual') as SourceType
            FROM Quote_Quotation_ChargeSheet QC WITH (NOLOCK)
            INNER JOIN Quote_QuotationInfo QI WITH (NOLOCK) ON QI.id = QC.QuotationInfoID
            LEFT JOIN [FITSCMUAT].dbo.Bill_ChargeCode BC WITH (NOLOCK) ON BC.Id = QC.ChargeCodeId
            LEFT JOIN [FITSCMUAT].dbo.Cust_Currency CC WITH (NOLOCK) ON CC.Id = QC.EstChargeCurrencyId
            LEFT JOIN Cust_ChargecodeBasedOn CB WITH (NOLOCK) ON CB.Id = QC.BasedOnId
            LEFT JOIN [FITSCMUAT].dbo.Sys_UOM SU WITH (NOLOCK) ON SU.Id = QC.UOMId
            LEFT JOIN [FITSCMUAT].dbo.Cust_Party CP WITH (NOLOCK) ON CP.Id = QC.PartyID
            WHERE QC.Id = ?
                AND QC.IsDeleted = 0
            ", [$chargeId]);

            Log::info('Delete Charge - Charge lookup result', [
                'booking_no' => $bookingNo,
                'charge_id' => $chargeId,
                'charge_found' => $charge ? 'yes' : 'no',
                'charge_code' => $charge->ChargeCode ?? 'N/A',
                'charge_name' => $charge->ChargeName ?? 'N/A',
                'is_revenue' => $charge->IsRevenue ?? 'N/A',
            ]);

            // ============================================
            // ENHANCED ERROR DETECTION ON CHARGE LOOKUP
            // ============================================

            if (!empty($charge) && is_object($charge)) {
                $properties = get_object_vars($charge);

                foreach ($properties as $key => $value) {
                    if (
                        empty($key) ||
                        $key === '' ||

                        (is_string($value) && (
                            strpos($value, 'Error Message') !== false ||
                            strpos($value, 'Failed -') !== false
                        ))
                    ) {
                        Log::error('Delete Charge - Charge lookup returned error object:', [
                            'booking_no' => $bookingNo,
                            'charge_id' => $chargeId,
                            'error_property_name' => $key,
                            'error_message' => $value,
                            'all_properties' => $properties,
                            'context' => [
                                'office_code' => $officeCode,
                                'service_type' => $quotation->ServiceVerticalname ?? 'N/A',
                            ],
                            'probable_cause' => 'Database schema mismatch or invalid charge ID'
                        ]);

                        return response()->json([
                            'success' => false,
                            'message' => 'Failed to retrieve charge details for deletion',
                            'error_details' => config('app.debug') ? $value : null
                        ], 500);
                    }
                }
            }

            if (!$charge) {
                Log::warning('Delete Charge - Charge not found or already deleted', [
                    'charge_id' => $chargeId,
                    'booking_no' => $bookingNo,
                    'office_code' => $officeCode,
                    'user' => Auth::user()->displayName ?? Auth::user()->Email ?? 'system'
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Charge not found or already deleted'
                ], 404);
            }

            // ============================================
            // PREPARE DELETE REQUEST
            // ============================================

            $submittedBy = Auth::check()
                ? (Auth::user()->displayName ?? Auth::user()->Email ?? 'system')
                : 'system';

            // $quoteNo = DB::table('Quote_QuotationInfo')
            //     ->where('bookingno', $bookingNo)
            //     ->when($officeCodeFromRequest, fn($q) => $q->where('OfficeCode', $officeCodeFromRequest))
            //     ->value('QuoteNo');

            // if (!$quoteNo) {
            //     return response()->json([
            //         'success' => false,
            //         'message' => 'QuoteNo not found for booking: ' . $bookingNo,
            //     ], 404);
            // }public function getChargeBasedOnDefaults(Request $request)


            $inputJson = json_encode([
                'OperationType' => 'QuotationAddorEditCharge',
                'SubmittedDatetime' => now()->format('YmdHis'),
                'SubmittedBy' => $submittedBy,
                'Action' => 'Delete',
                'QuotationID' => $quotation->ID,
                'QuoteChargeID' => $chargeId,
                'PartyCode' => $charge->PartyCode ?? '',
                'RevenueOrCost' => $charge->IsRevenue ? 'Revenue' : 'Cost',
                'ChargeCode' => $charge->ChargeCode,
                'Quantity' => (float) $charge->Quantity,
                'Rate' => (float) $charge->Rate,
                'AmtInChgCurr' => (float) $charge->AmtInChgCurr,
                'AmtInBaseCurr' => (float) $charge->AmtInBaseCurr,
                'ExChangeRate' => (float) $charge->ExChangeRate,
                'ChgCurr' => $charge->ChgCurr ?? 'KES',
                'BasedOnCode' => $charge->BasedOnCode,
                'BasedOnUOM' => $charge->BasedOnUOM ?? '',
                'ChargeRemark' => $charge->ChargeRemark ?? '',
                'ToBeAdded' => 0,
                'IncreaseBy' => 0,
                'IncreaseType' => 'Percentage',
                'SourceType' => $charge->SourceType ?? 'manual'
            ]);

            Log::info('Delete Charge - Stored Procedure Input', [
                'booking_no' => $bookingNo,
                'charge_id' => $chargeId,
                'charge_code' => $charge->ChargeCode,
                'is_revenue' => $charge->IsRevenue,
                'user' => $submittedBy,
                'office_code' => $officeCode,
                'json' => $inputJson
            ]);

            // ============================================
            // EXECUTE STORED PROCEDURE WITH PDO
            // ============================================

            Log::info('Delete Charge - Executing Stored Procedure: sp_Quote_Quotaiton_ChargesAddorEdit', [
                'booking_no' => $bookingNo,
                'charge_id' => $chargeId,
                'office_code' => $officeCode
            ]);

            $pdo = DB::connection()->getPdo();
            $stmt = $pdo->prepare("
            SET NOCOUNT ON;
            DECLARE @OutputJson VARCHAR(2000);
            EXEC sp_Quote_Quotaiton_ChargesAddorEdit ?, @OutputJson OUTPUT;
            SELECT @OutputJson AS OutputJson
                ");

            $executeSuccess = $stmt->execute([$inputJson]);

            if (!$executeSuccess) {
                $errorInfo = $stmt->errorInfo();

                Log::error('Delete Charge - Stored procedure execution failed', [
                    'booking_no' => $bookingNo,
                    'charge_id' => $chargeId,
                    'error_code' => $errorInfo[0] ?? 'Unknown',
                    'sql_state' => $errorInfo[1] ?? 'Unknown',
                    'error_message' => $errorInfo[2] ?? 'Unknown',
                    'input_json' => $inputJson,
                    'context' => [
                        'office_code' => $officeCode,
                        'charge_code' => $charge->ChargeCode,
                    ]
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Failed to execute delete stored procedure',
                    'error_details' => config('app.debug') ? $errorInfo[2] : null
                ], 500);
            }

            // ============================================
            // ENHANCED RESULT SET PROCESSING
            // ============================================

            $result = null;

            $resultSetCount = 0;

            do {
                $resultSetCount++;
                try {
                    $rows = $stmt->fetchAll(\PDO::FETCH_OBJ);

                    Log::debug("Delete Charge - Result set #$resultSetCount", [
                        'booking_no' => $bookingNo,
                        'charge_id' => $chargeId,
                        'count' => count($rows),
                        'first_row' => !empty($rows) ? (array) $rows[0] : null
                    ]);

                    // ============================================
                    // ENHANCED ERROR DETECTION IN RESULT SET
                    // ============================================

                    if (!empty($rows) && is_object($rows[0])) {
                        $properties = get_object_vars($rows[0]);
                        $hasError = false;

                        foreach ($properties as $key => $value) {
                            if (
                                empty($key) ||
                                $key === '' ||
                                (is_string($value) && (
                                    strpos($value, 'Error Message') !== false ||
                                    strpos($value, 'Failed -') !== false
                                ))

                            ) {

                                Log::error('Delete Charge - SP returned error object in result set:', [
                                    'booking_no' => $bookingNo,
                                    'charge_id' => $chargeId,
                                    'result_set' => $resultSetCount,
                                    'error_property_name' => $key,
                                    'error_message' => $value,
                                    'all_properties' => $properties,
                                    'context' => [
                                        'office_code' => $officeCode,
                                        'charge_code' => $charge->ChargeCode,
                                        'is_revenue' => $charge->IsRevenue,
                                    ]
                                ]);
                                $hasError = true;
                                break;
                            }
                        }

                        if ($hasError) {
                            continue;
                        }

                        if (isset($rows[0]->OutputJson)) {
                            $result = $rows;

                            Log::info('Delete Charge - ✓ Found OutputJson in result set #' . $resultSetCount, [
                                'booking_no' => $bookingNo,
                                'charge_id' => $chargeId
                            ]);
                            break;
                        }
                    }
                } catch (\PDOException $e) {

                    Log::debug("Delete Charge - Result set #$resultSetCount error (skipping)", [
                        'booking_no' => $bookingNo,
                        'charge_id' => $chargeId,
                        'error' => $e->getMessage()
                    ]);

                    continue;
                }
            } while ($stmt->nextRowset());

            if (!$result) {

                Log::error('Delete Charge - SP did not return OutputJson', [
                    'booking_no' => $bookingNo,
                    'charge_id' => $chargeId,
                    'total_result_sets' => $resultSetCount,
                    'office_code' => $officeCode,
                    'charge_code' => $charge->ChargeCode,
                    'probable_cause' => 'Stored procedure may have failed silently or returned unexpected format'
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Stored procedure did not return expected output'
                ], 500);
            }

            // ============================================
            // PARSE OUTPUT WITH ENHANCED ERROR HANDLING
            // ============================================

            $outputJson = $result[0]->OutputJson ?? '{}';
            $output = json_decode($outputJson, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('Delete Charge - Failed to decode SP output', [
                    'booking_no' => $bookingNo,
                    'charge_id' => $chargeId,
                    'json_error' => json_last_error_msg(),
                    'json_error_code' => json_last_error(),
                    'raw_output' => $outputJson,
                    'context' => [
                        'office_code' => $officeCode,
                        'charge_code' => $charge->ChargeCode,
                    ]
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Failed to parse stored procedure response: ' . json_last_error_msg(),
                    'error_details' => config('app.debug') ? $outputJson : null
                ], 500);
            }

            // ============================================
            // HANDLE SUCCESS
            // ============================================

            if (($output['Status'] ?? '') === 'Success') {
                Log::info('Delete Charge - Charge deleted successfully', [
                    'booking_no' => $bookingNo,
                    'charge_id' => $chargeId,
                    'charge_code' => $charge->ChargeCode,
                    'charge_name' => $charge->ChargeName,
                    'was_revenue' => (bool) $charge->IsRevenue,
                    'base_amount' => $charge->AmtInBaseCurr,
                    'user' => $submittedBy,
                    'office_code' => $officeCode
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Charge deleted successfully'
                ]);
            }

            // ============================================
            // HANDLE FAILURE
            // ============================================

            $errorMessage = $output['Remarks'][0]['Remark'] ?? 'Failed to delete charge';

            Log::warning('Delete Charge - SP returned failure', [
                'booking_no' => $bookingNo,
                'charge_id' => $chargeId,
                'charge_code' => $charge->ChargeCode,
                'status' => $output['Status'] ?? 'Unknown',
                'message' => $errorMessage,
                'full_output' => $output,
                'context' => [
                    'office_code' => $officeCode,
                    'is_revenue' => $charge->IsRevenue,
                    'user' => $submittedBy
                ]
            ]);

            return response()->json([
                'success' => false,
                'message' => $errorMessage,
                'details' => $output['Remarks'] ?? []
            ], 422);
        } catch (\PDOException $e) {

            Log::error('Delete Charge - PDO exception', [
                'booking_no' => $request->input('booking_no'),
                'charge_id' => $request->input('quote_charge_id'),
                'error_message' => $e->getMessage(),
                'error_code' => $e->getCode(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'sql_state' => $e->errorInfo[0] ?? 'Unknown',
                'driver_error_code' => $e->errorInfo[1] ?? 'Unknown',
                'driver_error_message' => $e->errorInfo[2] ?? 'Unknown',
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Database connection error while deleting charge',
                'error_code' => 'PDO_ERROR',
                'details' => config('app.debug') ? [
                    'message' => $e->getMessage(),
                    'sql_state' => $e->errorInfo[0] ?? null,
                ] : null

            ], 500);
        } catch (\Illuminate\Database\QueryException $e) {

            Log::error('Delete Charge - Query exception', [
                'booking_no' => $request->input('booking_no'),
                'charge_id' => $request->input('quote_charge_id'),
                'error_message' => $e->getMessage(),
                'error_code' => $e->getCode(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'sql' => $e->getSql() ?? 'N/A',
                'bindings' => $e->getBindings() ?? [],
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Database query error while deleting charge',
                'error_code' => 'QUERY_ERROR',
                'details' => config('app.debug') ? [
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ] : null

            ], 500);
        } catch (\Exception $e) {

            Log::error('Delete Charge - General exception', [
                'booking_no' => $request->input('booking_no'),
                'charge_id' => $request->input('quote_charge_id'),
                'error_message' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete charge: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateStatus(Request $request)
    {
        try {
            $bookingNo = $request->input('booking_no');
            $targetStatus = $request->input('status');
            $reasonCode = $request->input('reason_code', '');
            $reasonRemark = $request->input('reason_remark', '');

            // ============================================
            // VALIDATE REQUIRED FIELDS
            // ============================================

            if (!$bookingNo || !$targetStatus) {
                Log::warning('Update Status - Missing required fields', [
                    'booking_no' => $bookingNo,
                    'target_status' => $targetStatus,
                    'user' => Auth::user()->displayName ?? Auth::user()->Email ?? 'system'

                ]);

                return response()->json([
                    'success' => false,
                    'message' => !$bookingNo ? 'Booking number is required' : 'Status is required'
                ], 400);
            }

            // Validate status value
            if (!in_array($targetStatus, ['Won', 'Lost', 'Cancel'])) {
                Log::warning('Update Status - Invalid status value', [
                    'booking_no' => $bookingNo,
                    'target_status' => $targetStatus,
                    'valid_statuses' => ['Won', 'Lost', 'Cancel'],
                    'user' => Auth::user()->displayName ?? Auth::user()->Email ?? 'system'
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Invalid status. Must be Won, Lost, or Cancel'
                ], 400);
            }

            $submittedBy = Auth::check()
                ? (Auth::user()->displayName ?? Auth::user()->Email ?? 'system')
                : 'system';

            // ============================================
            // GET QUOTATION WITH FULL CONTEXT
            // ============================================

            $quotation = DB::table('Quote_QuotationInfo')
                ->where('bookingno', $bookingNo)
                ->first([
                    'ID',
                    'teamid',
                    'QuoteStatus',
                    'status',
                    'ServiceVerticalname',
                    'origincountry',
                    'destinationcountry',
                    'companyname'
                ]);

            if (!$quotation) {
                Log::error('Update Status - Quotation not found', [
                    'booking_no' => $bookingNo,
                    'target_status' => $targetStatus,
                    'user' => $submittedBy

                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Quotation not found'
                ], 404);
            }

            // Get office code for context
            $officeCode = 'Unknown';
            $office = DB::table('Cust_Office')
                ->where('TeamCode', $quotation->teamid)
                ->where('IsActive', 1)
                ->where('IsDeleted', 0)
                ->first(['Code']);

            if ($office) {
                $officeCode = trim($office->Code);
            }

            $currentStatus = $quotation->QuoteStatus ?? $quotation->status ?? 'Proposal';

            Log::info('Update Status - Starting', [
                'booking_no' => $bookingNo,
                'current_status' => $currentStatus,
                'target_status' => $targetStatus,
                'user' => $submittedBy,
                'context' => [
                    'quotation_id' => $quotation->ID,
                    'office_code' => $officeCode,
                    'service_type' => $quotation->ServiceVerticalname ?? 'N/A',
                    'company' => $quotation->companyname ?? 'N/A',
                    'origin' => $quotation->origincountry ?? 'N/A',
                    'destination' => $quotation->destinationcountry ?? 'N/A',
                    'reason_code' => $reasonCode ?: 'N/A',
                    'reason_remark' => $reasonRemark ?: 'N/A',

                ]
            ]);

            // ============================================
            // HANDLE "WON" STATUS SEPARATELY (NOT IN SP)
            // ============================================

            if ($targetStatus === 'Won') {
                Log::info('Update Status - Handling Won status directly', [
                    'booking_no' => $bookingNo,
                    'current_status' => $currentStatus,
                    'user' => $submittedBy,
                    'office_code' => $officeCode
                ]);

                DB::beginTransaction();

                try {
                    // Update quotation status
                    $updatedRows = DB::table('Quote_QuotationInfo')
                        ->where('bookingno', $bookingNo)
                        ->update([
                            'QuoteStatus' => 'Won',
                            'status' => 'Won',
                            'SCMINTStatus' => null,
                            'ReasonRemark' => $reasonRemark ?: null,
                            'LastModifiedBy' => $submittedBy,
                            'updatedon' => now()
                        ]);
                    if ($updatedRows === 0) {
                        DB::rollBack();
                        Log::error('Update Status - Won update affected no rows', [
                            'booking_no' => $bookingNo,
                            'quotation_id' => $quotation->ID,
                            'user' => $submittedBy
                        ]);

                        return response()->json([
                            'success' => false,
                            'message' => 'Failed to update status: No records were updated'
                        ], 500);
                    }

                    // Insert history record

                    DB::table('Cust_EntityHistory')->insert([
                        'ID' => DB::raw('NEWID()'),
                        'CustEntityID' => $quotation->ID,
                        'RequestNo' => (string) $bookingNo,
                        'Comment' => $reasonRemark ?: 'Status updated to Won',
                        'Type' => 'Quotation Status Update',
                        'PreviousValue' => $currentStatus,
                        'NewValue' => 'Won',
                        'CreatedBy' => $submittedBy,
                        'CreatedOn' => now(),
                        'LastModifiedBy' => $submittedBy,
                        'LastModifiedOn' => now(),
                        'IsActive' => 1,
                        'IsDeleted' => 0
                    ]);

                    DB::commit();

                    Log::info('Update Status - Won status set successfully', [
                        'booking_no' => $bookingNo,
                        'quotation_id' => $quotation->ID,
                        'previous_status' => $currentStatus,
                        'new_status' => 'Won',
                        'rows_updated' => $updatedRows,
                        'user' => $submittedBy,
                        'office_code' => $officeCode,
                        'company' => $quotation->companyname ?? 'N/A'
                    ]);

                    return response()->json([
                        'success' => true,
                        'message' => 'Quotation marked as WON successfully'
                    ]);
                } catch (\Illuminate\Database\QueryException $e) {

                    DB::rollBack();

                    Log::error('Update Status - Won query exception', [
                        'booking_no' => $bookingNo,
                        'quotation_id' => $quotation->ID,
                        'error_message' => $e->getMessage(),
                        'error_code' => $e->getCode(),
                        'sql' => $e->getSql() ?? 'N/A',
                        'bindings' => $e->getBindings() ?? [],
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                        'office_code' => $officeCode
                    ]);

                    return response()->json([
                        'success' => false,
                        'message' => 'Database error while updating status to Won',
                        'error_details' => config('app.debug') ? $e->getMessage() : null
                    ], 500);
                } catch (\Exception $e) {

                    DB::rollBack();

                    Log::error('Update Status - Won general exception', [
                        'booking_no' => $bookingNo,
                        'quotation_id' => $quotation->ID,
                        'error_message' => $e->getMessage(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                        'office_code' => $officeCode
                    ]);

                    return response()->json([
                        'success' => false,
                        'message' => 'Failed to update status to Won: ' . $e->getMessage()
                    ], 500);
                }
            }

            // ============================================
            // HANDLE "LOST" AND "CANCEL" VIA STORED PROCEDURE
            // ============================================

            // Validate reason code is provided for Lost/Cancel
            if (empty($reasonCode)) {
                Log::warning('Update Status - Reason code missing for Lost/Cancel', [
                    'booking_no' => $bookingNo,
                    'target_status' => $targetStatus,
                    'user' => $submittedBy
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Reason code is required for ' . $targetStatus . ' status'
                ], 400);
            }

            // Handle status transition requirements
            $needsPendingFirst = false;

            if ($targetStatus === 'Lost' && $currentStatus !== 'Pending') {
                $needsPendingFirst = true;
                Log::info('Update Status - Lost requires Pending first', [
                    'booking_no' => $bookingNo,
                    'current_status' => $currentStatus,
                    'office_code' => $officeCode
                ]);
            } elseif ($targetStatus === 'Cancel' && !in_array($currentStatus, ['Pending', 'Won'])) {

                $needsPendingFirst = true;

                Log::info('Update Status - Cancel requires Pending/Won first', [
                    'booking_no' => $bookingNo,
                    'current_status' => $currentStatus,
                    'office_code' => $officeCode
                ]);
            }

            // Update to Pending first if needed

            if ($needsPendingFirst) {
                try {
                    $pendingRows = DB::table('Quote_QuotationInfo')
                        ->where('bookingno', $bookingNo)
                        ->update([
                            'QuoteStatus' => 'Pending',
                            'status' => 'Pending',
                            'LastModifiedBy' => $submittedBy,
                            'updatedon' => now()
                        ]);

                    Log::info('Update Status - Intermediate Pending update', [
                        'booking_no' => $bookingNo,
                        'previous_status' => $currentStatus,
                        'rows_updated' => $pendingRows,
                        'target_status' => $targetStatus,
                        'office_code' => $officeCode

                    ]);
                } catch (\Exception $e) {

                    Log::error('Update Status - Failed to set intermediate Pending status', [
                        'booking_no' => $bookingNo,
                        'error_message' => $e->getMessage(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                        'office_code' => $officeCode
                    ]);

                    return response()->json([
                        'success' => false,
                        'message' => 'Failed to prepare status transition: ' . $e->getMessage()
                    ], 500);
                }
            }

            // ============================================
            // EXECUTE STATUS UPDATE STORED PROCEDURE
            // ============================================

            $inputJson = json_encode([
                'OperationType' => 'QuotationStatusUpdate',
                'SubmittedDatetime' => now()->format('YmdHis'),
                'SubmittedBy' => $submittedBy,
                'Action' => 'Edit',
                'QuotationID' => $quotation->ID,  // ← REPLACE BookingNo with QuotationID
                'Status' => $targetStatus,
                'ReasonCode' => $reasonCode,
                'ReasonRemark' => $reasonRemark
            ]);
            Log::info('Update Status - Executing SP', [
                'booking_no' => $bookingNo,
                'target_status' => $targetStatus,
                'office_code' => $officeCode,
                'json' => $inputJson
            ]);

            $pdo = DB::connection()->getPdo();
            $stmt = $pdo->prepare("
            SET NOCOUNT ON;
            DECLARE @OutputJson VARCHAR(2000);
            EXEC sp_Quote_Quotaiton_StatusUpdate ?, @OutputJson OUTPUT;
                SELECT @OutputJson AS OutputJson
            ");

            $executeSuccess = $stmt->execute([$inputJson]);
            if (!$executeSuccess) {
                $errorInfo = $stmt->errorInfo();

                // Rollback pending status if we changed it
                if ($needsPendingFirst) {
                    DB::table('Quote_QuotationInfo')
                        ->where('bookingno', $bookingNo)
                        ->update([

                            'QuoteStatus' => $currentStatus,
                            'status' => $currentStatus,
                            'LastModifiedBy' => $submittedBy,
                            'updatedon' => now()
                        ]);

                    Log::info('Update Status - Rolled back intermediate Pending status', [
                        'booking_no' => $bookingNo,
                        'restored_status' => $currentStatus
                    ]);
                }

                Log::error('Update Status - SP execution failed', [
                    'booking_no' => $bookingNo,
                    'target_status' => $targetStatus,
                    'error_code' => $errorInfo[0] ?? 'Unknown',
                    'sql_state' => $errorInfo[1] ?? 'Unknown',
                    'error_message' => $errorInfo[2] ?? 'Unknown',
                    'office_code' => $officeCode
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Failed to execute status update procedure',
                    'error_details' => config('app.debug') ? $errorInfo[2] : null
                ], 500);
            }

            // ============================================
            // ENHANCED RESULT SET PROCESSING
            // ============================================

            $result = null;
            $resultSetCount = 0;

            do {
                $resultSetCount++;
                try {
                    $rows = $stmt->fetchAll(\PDO::FETCH_OBJ);

                    Log::debug("Update Status - Result set #$resultSetCount", [
                        'booking_no' => $bookingNo,
                        'count' => count($rows),
                        'first_row' => !empty($rows) ? (array) $rows[0] : null
                    ]);

                    // ============================================
                    // ENHANCED ERROR DETECTION IN RESULT SET
                    // ============================================

                    if (!empty($rows) && is_object($rows[0])) {
                        $properties = get_object_vars($rows[0]);
                        $hasError = false;

                        foreach ($properties as $key => $value) {
                            if (
                                empty($key) ||
                                $key === '' ||
                                (is_string($value) && (
                                    strpos($value, 'Error Message') !== false ||
                                    strpos($value, 'Failed -') !== false
                                ))
                            ) {

                                Log::error('Update Status - SP returned error object in result set:', [
                                    'booking_no' => $bookingNo,
                                    'target_status' => $targetStatus,
                                    'result_set' => $resultSetCount,
                                    'error_property_name' => $key,
                                    'error_message' => $value,
                                    'all_properties' => $properties,
                                    'context' => [
                                        'office_code' => $officeCode,
                                        'current_status' => $currentStatus,
                                        'reason_code' => $reasonCode,
                                    ]
                                ]);
                                $hasError = true;
                                break;
                            }
                        }

                        if ($hasError) {
                            continue;
                        }

                        if (isset($rows[0]->OutputJson)) {
                            $result = $rows;
                            Log::info('Update Status - ✓ Found OutputJson in result set #' . $resultSetCount, [
                                'booking_no' => $bookingNo
                            ]);
                            break;
                        }
                    }
                } catch (\PDOException $e) {

                    Log::debug("Update Status - Result set #$resultSetCount error (skipping)", [
                        'booking_no' => $bookingNo,
                        'error' => $e->getMessage()
                    ]);
                    continue;
                }
            } while ($stmt->nextRowset());

            if (!$result) {
                Log::error('Update Status - SP did not return OutputJson', [
                    'booking_no' => $bookingNo,
                    'target_status' => $targetStatus,
                    'total_result_sets' => $resultSetCount,
                    'office_code' => $officeCode,
                    'probable_cause' => 'Stored procedure may have failed silently or returned unexpected format'
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Stored procedure did not return expected output'
                ], 500);
            }

            // ============================================
            // PARSE OUTPUT WITH ENHANCED ERROR HANDLING
            // ============================================

            $outputJson = $result[0]->OutputJson ?? '{}';
            $output = json_decode($outputJson, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('Update Status - Failed to decode SP output', [
                    'booking_no' => $bookingNo,
                    'target_status' => $targetStatus,
                    'json_error' => json_last_error_msg(),
                    'json_error_code' => json_last_error(),
                    'raw_output' => $outputJson,
                    'context' => [
                        'office_code' => $officeCode,
                        'current_status' => $currentStatus,
                    ]
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Failed to parse stored procedure response: ' . json_last_error_msg(),
                    'error_details' => config('app.debug') ? $outputJson : null
                ], 500);
            }

            // ============================================
            // HANDLE SUCCESS
            // ============================================

            if (($output['Status'] ?? '') === 'Success') {
                Log::info('Update Status - Status updated successfully', [
                    'booking_no' => $bookingNo,
                    'quotation_id' => $quotation->ID,
                    'previous_status' => $currentStatus,
                    'new_status' => $targetStatus,
                    'reason_code' => $reasonCode,
                    'user' => $submittedBy,
                    'office_code' => $officeCode,
                    'company' => $quotation->companyname ?? 'N/A'
                ]);

                return response()->json([
                    'success' => true,
                    'message' => $output['Remarks'][0]['Remark'] ?? 'Status updated successfully'
                ]);
            }

            // ============================================
            // HANDLE FAILURE - ROLLBACK PENDING IF NEEDED
            // ============================================

            $errorMessage = $output['Remarks'][0]['Remark'] ?? 'Failed to update status';

            // If SP failed and we had changed to Pending, restore original status
            if ($needsPendingFirst) {
                try {
                    DB::table('Quote_QuotationInfo')
                        ->where('bookingno', $bookingNo)
                        ->update([
                            'QuoteStatus' => $currentStatus,
                            'status' => $currentStatus,
                            'LastModifiedBy' => $submittedBy,
                            'updatedon' => now()
                        ]);

                    Log::info('Update Status - Rolled back intermediate Pending after SP failure', [
                        'booking_no' => $bookingNo,
                        'restored_status' => $currentStatus,
                        'sp_error' => $errorMessage
                    ]);
                } catch (\Exception $e) {
                    Log::error('Update Status - Failed to rollback intermediate Pending status', [
                        'booking_no' => $bookingNo,
                        'error' => $e->getMessage(),
                        'original_status' => $currentStatus
                    ]);
                }
            }

            Log::warning('Update Status - SP returned failure', [
                'booking_no' => $bookingNo,
                'target_status' => $targetStatus,
                'current_status' => $currentStatus,
                'status' => $output['Status'] ?? 'Unknown',
                'message' => $errorMessage,
                'full_output' => $output,
                'context' => [
                    'office_code' => $officeCode,
                    'reason_code' => $reasonCode,
                    'user' => $submittedBy,
                    'company' => $quotation->companyname ?? 'N/A'
                ]
            ]);

            return response()->json([
                'success' => false,
                'message' => $errorMessage,
                'details' => $output['Remarks'] ?? []
            ], 422);
        } catch (\PDOException $e) {

            Log::error('Update Status - PDO exception', [
                'booking_no' => $request->input('booking_no'),
                'target_status' => $request->input('status'),
                'error_message' => $e->getMessage(),
                'error_code' => $e->getCode(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'sql_state' => $e->errorInfo[0] ?? 'Unknown',
                'driver_error_code' => $e->errorInfo[1] ?? 'Unknown',
                'driver_error_message' => $e->errorInfo[2] ?? 'Unknown',
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Database connection error while updating status',
                'error_code' => 'PDO_ERROR',
                'details' => config('app.debug') ? [
                    'message' => $e->getMessage(),
                    'sql_state' => $e->errorInfo[0] ?? null,
                ] : null

            ], 500);
        } catch (\Illuminate\Database\QueryException $e) {

            Log::error('Update Status - Query exception', [
                'booking_no' => $request->input('booking_no'),
                'target_status' => $request->input('status'),
                'error_message' => $e->getMessage(),
                'error_code' => $e->getCode(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'sql' => $e->getSql() ?? 'N/A',
                'bindings' => $e->getBindings() ?? [],
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Database query error while updating status',
                'error_code' => 'QUERY_ERROR',
                'details' => config('app.debug') ? [
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ] : null

            ], 500);
        } catch (\Exception $e) {

            Log::error('Update Status - General exception', [
                'booking_no' => $request->input('booking_no'),
                'target_status' => $request->input('status'),
                'error_message' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update status: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getReasons(Request $request)
    {
        $module = $request->input('module', 'Quotation');
        $reasonType = $request->input('reason_type');
        $filter = $request->input('filter', '');

        if (!$reasonType) {
            return response()->json(['success' => false, 'message' => 'Reason type is required'], 400);
        }

        try {
            $pdo = DB::connection()->getPdo();
            $stmt = $pdo->prepare("SET NOCOUNT ON; EXEC sp_ReasonMasterList ?, ?, ?");
            $stmt->execute([$module, $reasonType, $filter]);

            $reasons = [];
            do {
                try {
                    $results = $stmt->fetchAll(\PDO::FETCH_OBJ);
                    if (empty($results))
                        continue;
                    $firstRow = $results[0];
                    $properties = array_keys((array) $firstRow);

                    // Skip SP status/error result sets — they return a single
                    // column whose value starts with 'Failed' or 'Error'

                    $firstVal = array_values((array) $firstRow)[0] ?? '';
                    if (
                        is_string($firstVal) && (
                            str_starts_with($firstVal, 'Failed') ||
                            str_starts_with($firstVal, 'Error')
                        )
                    ) {

                        Log::warning('getReasons - SP returned failure', [
                            'reason_type' => $reasonType,
                            'message' => $firstVal,
                        ]);
                        continue;
                    }

                    // Accept any result set that has at least a Code column
                    if (in_array('Code', $properties)) {
                        $reasons = $results;
                        Log::info('getReasons - Found reasons', [
                            'reason_type' => $reasonType,
                            'count' => count($results),
                            'columns' => $properties,
                        ]);

                        break;
                    }
                } catch (\Exception $e) {

                    continue;
                }
            } while ($stmt->nextRowset());

            return response()->json(['success' => true, 'reasons' => $reasons]);
        } catch (\Exception $e) {

            Log::error('getReasons failed', [

                'reason_type' => $reasonType,

                'error' => $e->getMessage(),

            ]);

            return response()->json([

                'success' => false,

                'message' => 'Failed to load reasons: ' . $e->getMessage()

            ], 500);
        }
    }

    public function saveTermsCondition(Request $request)
    {
        $bookingNo = $request->input('booking_no');
        $termsText = $request->input('terms_condition');
        $templateName = $request->input('template_name');
        $submittedBy = Auth::check()
            ? (Auth::user()->displayName ?? Auth::user()->Email ?? 'system')
            : 'system';

        if (!$bookingNo) {
            return response()->json(['success' => false, 'message' => 'Booking number is required'], 400);
        }

        // ── Normalize template name to exactly what Cust_CommunicationDetails stores ──
        // sp_Quote_QuotationFormat uses INNER JOIN On CC.Name = QI.QuotationTCTemplate
        // Any whitespace difference causes the join to fail → SP returns empty HTML.
        if ($templateName) {
            $exactName = DB::table('Cust_CommunicationDetails')
                ->whereRaw("LTRIM(RTRIM(Name)) = ?", [trim($templateName)])
                ->where('IsActive', 1)
                ->value('Name'); // returns the raw DB-stored string including trailing spaces

            if (!$exactName) {
                Log::error('saveTermsCondition - template not found in Cust_CommunicationDetails', [
                    'booking_no' => $bookingNo,
                    'template_name_raw' => $templateName,
                ]);
                return response()->json([
                    'success' => false,
                    'message' => "Terms & Conditions template '{$templateName}' was not found. Please re-select and try again.",
                ], 422);
            }

            if ($exactName !== $templateName) {
                Log::info('saveTermsCondition - template name normalized', [
                    'booking_no' => $bookingNo,
                    'original' => $templateName,
                    'normalized' => $exactName,
                ]);
            }

            $templateName = $exactName;
        }

        try {
            // ── Write QuotationTCTemplate only — no other column stores T&C text ──
            $updated = DB::table('Quote_QuotationInfo')
                ->where('bookingno', $bookingNo)
                ->update([
                    'QuotationTCTemplate' => $templateName,
                    'LastModifiedBy' => $submittedBy,
                    'updatedon' => now(),
                ]);

            if ($updated === 0) {
                return response()->json(['success' => false, 'message' => 'No matching quotation found'], 404);
            }

            $quotation = DB::table('Quote_QuotationInfo')
                ->where('bookingno', $bookingNo)
                ->first(['ID']);

            // ── Save T&C HTML to disk ──
            $folder = 'quotations/' . $bookingNo . '/tc';
            $fileName = 'TC_' . $bookingNo . '_' . now()->format('Ymd_His') . '.html';
            $filePath = $folder . '/' . $fileName;
            \Storage::disk('public')->put($filePath, $termsText ?? '');

            $docName = $templateName ?? $fileName;

            // ── Upsert into Quote_Quotation_Document ──
            DB::statement("
            MERGE dbo.Quote_Quotation_Document AS target
            USING (
                SELECT :quotationId AS QuotationInfoID,
                    'TermsCondition' AS DocumentType,
                    :name AS Name
            ) AS src
            ON  target.QuotationInfoID = src.QuotationInfoID
            AND target.DocumentType    = src.DocumentType
            AND target.Name            = src.Name
            WHEN MATCHED THEN UPDATE SET
                FilePath        = :filePath,
                LastModifiedBy  = :modifiedBy,
                LastModifiedOn  = :modifiedOn,
                IsDeleted       = 0
            WHEN NOT MATCHED THEN INSERT
                (Id, QuotationInfoID, DocumentType, Name, IsVisible, IsPrinted, FilePath,
                CreatedBy, CreatedOn, LastModifiedBy, LastModifiedOn, IsDeleted)
            VALUES
                (NEWID(), :quotationId2, 'TermsCondition', :name2, 1, 0, :filePath2,
                :createdBy, :createdOn, :modifiedBy2, :modifiedOn2, 0);
        ", [
                'quotationId' => $quotation->ID,
                'name' => $docName,
                'filePath' => $filePath,
                'modifiedBy' => $submittedBy,
                'modifiedOn' => now(),
                'quotationId2' => $quotation->ID,
                'name2' => $docName,
                'filePath2' => $filePath,
                'createdBy' => $submittedBy,
                'createdOn' => now(),
                'modifiedBy2' => $submittedBy,
                'modifiedOn2' => now(),
            ]);

            // ── Insert version record — only columns that actually exist in Cust_DocumentDeails ──
            // Actual schema: Id, DocumentType, ReferenceID, ReferenceNo,
            //                SubmittedDate, VersionNo, FileName, Path, CreatedOn
            // 'ReferenceType' does NOT exist — removed.
            $nextVersion = DB::table('Cust_DocumentDeails')
                ->where('ReferenceID', $quotation->ID)
                ->where('DocumentType', 'TermsCondition')
                ->max('VersionNo') ?? 0;

            DB::table('Cust_DocumentDeails')->insert([
                'Id' => DB::raw('NEWID()'),
                'DocumentType' => 'TermsCondition',
                'ReferenceID' => $quotation->ID,
                'ReferenceNo' => (string) $bookingNo,
                'SubmittedDate' => now(),
                'VersionNo' => $nextVersion + 1,
                'FileName' => $fileName,
                'Path' => $filePath,
                'CreatedOn' => now(),
            ]);

            Log::info('saveTermsCondition - Success', [
                'booking_no' => $bookingNo,
                'template' => $templateName,
                'file' => $filePath,
                'user' => $submittedBy,
            ]);

            return response()->json(['success' => true, 'message' => 'Terms & Conditions saved successfully']);
        } catch (\Exception $e) {
            Log::error('saveTermsCondition - Failed', [
                'booking_no' => $bookingNo,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            return response()->json(['success' => false, 'message' => 'Failed to save: ' . $e->getMessage()], 500);
        }
    }

    // ============================================================
    // PRODUCTION FIX — retrigger() method
    //
    // ROOT CAUSE (two bugs):
    //
    // 1. In the "Steps 3 and 4" loop, the code calls:
    //      $step2 = $this->runBookingProcessMain($quotation->ID, 'XML');
    //    then reads from $result (which was never assigned in this branch).
    //    The local variable is wrong on both sides — the assignment name is
    //    $step2 (stale from the outer branch) and the read is $result
    //    (undefined here). Fix: use $result = $this->runBookingProcessMain(...)
    //    with the correct $stepDef['type'] argument.
    //
    // 2. Inside the "SCMBookingID is NULL" branch (Step 2 re-run), the same
    //    pattern repeats: calls runBookingProcessMain but stores in $step2
    //    and never reads from it properly. Fix: same — use $result and pass
    //    $stepDef['type'] is not in scope here, so pass 'XML' explicitly.
    //
    // CHANGE SCOPE: Only the two assignment+read pairs inside retrigger().
    // No other logic touched.
    // ============================================================

    public function retrigger(Request $request)
    {
        $bookingNo = $request->input('booking_no');
        $quotationId = $request->input('quotation_id'); // UUID from data-id

        if (!$bookingNo) {
            return response()->json(['success' => false, 'message' => 'Booking number is required'], 400);
        }

        $quotation = DB::table('Quote_QuotationInfo')
            ->when($quotationId, fn($q) => $q->where('ID', $quotationId))
            ->when(!$quotationId, fn($q) => $q->where('bookingno', $bookingNo))
            ->first(['ID', 'QuoteNo', 'SCMBookingID', 'SCMBookingXML', 'ClientCreated', 'OfficeCode']);

        if (!$quotation) {
            return response()->json(['success' => false, 'message' => 'Quotation not found'], 404);
        }

        if (empty($quotation->QuoteNo)) {
            return response()->json(['success' => false, 'message' => 'QuoteNo is not set. Cannot retrigger.'], 422);
        }

        $submittedBy = Auth::user()->displayName ?? Auth::user()->Email ?? 'system';
        $quoteNo = $quotation->QuoteNo;
        $steps = [];

        Log::info('retrigger - Starting', [
            'booking_no' => $bookingNo,
            'quote_no' => $quoteNo,
            'scm_booking_id' => $quotation->SCMBookingID ?? 'NULL',
            'xml_present' => !empty($quotation->SCMBookingXML)
                ? 'YES (' . strlen($quotation->SCMBookingXML) . ' chars)'
                : 'NO',
            'client_created' => $quotation->ClientCreated ?? 'NULL',
            'office_code' => $quotation->OfficeCode ?? 'NULL',
            'user' => $submittedBy,
        ]);

        // ── Step 2: Re-run if SCMBookingID is not set ─────────────────────────────
        if (empty($quotation->SCMBookingID)) {

            Log::warning('retrigger - SCMBookingID is NULL, re-running Step 2', [
                'booking_no' => $bookingNo,
                'quote_no' => $quoteNo,
            ]);

            // FIX 1: was "$step2 = $this->runBookingProcessMain($quotation->ID, $stepDef['type'])"
            // $stepDef is not in scope here. The correct process type for this branch is 'XML'.
            // Also renamed to $result to be consistent with the read below.
            $result = $this->runBookingProcessMain($quotation->ID, 'XML');

            $steps[] = [
                'step' => 2,
                'label' => 'Booking XML',
                'status' => $result['status'],
                'remark' => $result['remark'],
            ];

            Log::info('retrigger - Step 2 result', [
                'booking_no' => $bookingNo,
                'quote_no' => $quoteNo,
                'sp_called' => 'sp_Quote_BookingProcessMain',
                'sp_params' => ['QuotationNo' => $quoteNo, 'ProcessType' => 'XML'],
                'sp_status' => $result['status'],
                'sp_remark' => $result['remark'],
                'sql_error' => $result['sql_error'] ?? null,
                'sql_state' => $result['sql_state'] ?? null,
                'result_sets' => $result['result_sets'] ?? null,
            ]);

            if ($result['status'] !== 'Success') {
                Log::error('retrigger - Step 2 failed', [
                    'booking_no' => $bookingNo,
                    'quote_no' => $quoteNo,
                    'sp_remark' => $result['remark'],
                ]);

                $this->markAsFailed($quotation->ID, 'Booking XML', $result['remark'], $submittedBy);

                return response()->json([
                    'success' => false,
                    'failed_step' => 2,
                    'failed_label' => 'Booking XML',
                    'steps' => $steps,
                    'message' => $result['remark'],
                ]);
            }

            // ── Push XML to SCM ───────────────────────────────────────────────────────
            Log::info('retrigger - XML saved, pushing to SCM', [
                'booking_no' => $bookingNo,
                'quote_no' => $quoteNo,
            ]);

            $pushRequest = new \Illuminate\Http\Request();
            $pushRequest->merge(['quote_no' => $quoteNo]);

            $pushResponse = json_decode(
                $this->pushBookingToSCM($pushRequest)->getContent(),
                true
            );

            Log::info('retrigger - pushBookingToSCM result', [
                'booking_no' => $bookingNo,
                'quote_no' => $quoteNo,
                'result' => $pushResponse,
            ]);

            if (!($pushResponse['success'] ?? false)) {
                $this->markAsFailed(
                    $quotation->ID,
                    'SCM Push',
                    $pushResponse['message'] ?? 'SCM push failed',
                    $submittedBy
                );

                return response()->json([
                    'success' => false,
                    'failed_step' => 2,
                    'failed_label' => 'SCM Push',
                    'steps' => $steps,
                    'message' => $pushResponse['message'] ?? 'SCM push failed',
                ]);
            }

            $steps[] = [
                'step' => '2b',
                'label' => 'SCM Push',
                'status' => 'Success',
                'remark' => $pushResponse['message'] ?? 'Pushed to SCM successfully',
            ];

            // Re-fetch after push
            // AFTER:
            $quotation = DB::table('Quote_QuotationInfo')
                ->where('ID', $quotation->ID)
                ->first(['ID', 'QuoteNo', 'SCMBookingID', 'SCMBookingXML', 'ClientCreated', 'OfficeCode']);

            Log::info('retrigger - SCMBookingID after push', [
                'booking_no' => $bookingNo,
                'scm_booking_id' => $quotation->SCMBookingID ?? 'STILL NULL',
            ]);

            if (empty($quotation->SCMBookingID)) {
                Log::error('retrigger - SCMBookingID still NULL after push', [
                    'booking_no' => $bookingNo,
                    'quote_no' => $quoteNo,
                ]);

                return response()->json([
                    'success' => false,
                    'failed_step' => 2,
                    'failed_label' => 'SCM Push',
                    'steps' => $steps,
                    'message' => 'SCMBookingID is NULL — SCM did not confirm the booking for QuoteNo: ' . $quoteNo,
                ]);
            }
        }

        // ── Steps 3 and 4: Charge and Document Transfer ───────────────────────────
        $retriggerSteps = [
            3 => ['type' => 'Charge', 'label' => 'Charge Transfer'],
            4 => ['type' => 'Document', 'label' => 'Document Transfer'],
        ];

        foreach ($retriggerSteps as $stepNo => $stepDef) {
            // FIX 2: was "$step2 = $this->runBookingProcessMain($quotation->ID, 'XML')"
            // Two problems: used $step2 (wrong name, leftover from outer branch) and
            // hardcoded 'XML' instead of $stepDef['type'] (Charge / Document).
            $result = $this->runBookingProcessMain($quotation->ID, $stepDef['type']);

            $steps[] = [
                'step' => $stepNo,
                'label' => $stepDef['label'],
                'status' => $result['status'],
                'remark' => $result['remark'],
            ];

            Log::info('retrigger - Step result', [
                'booking_no' => $bookingNo,
                'quote_no' => $quoteNo,
                'step' => $stepNo,
                'sp_called' => 'sp_Quote_BookingProcessMain',
                'sp_params' => ['QuotationNo' => $quoteNo, 'ProcessType' => $stepDef['type']],
                'sp_status' => $result['status'],
                'sp_remark' => $result['remark'],
                'sql_error' => $result['sql_error'] ?? null,
                'sql_state' => $result['sql_state'] ?? null,
                'result_sets' => $result['result_sets'] ?? null,
            ]);

            if ($result['status'] !== 'Success') {
                Log::error('retrigger - Step failed', [
                    'booking_no' => $bookingNo,
                    'quote_no' => $quoteNo,
                    'failed_step' => $stepNo,
                    'failed_label' => $stepDef['label'],
                    'sp_remark' => $result['remark'],
                ]);

                $this->markAsFailed($quotation->ID, $stepDef['label'], $result['remark'], $submittedBy);

                foreach ($retriggerSteps as $remainingNo => $remainingDef) {
                    if ($remainingNo > $stepNo) {
                        $steps[] = [
                            'step' => $remainingNo,
                            'label' => $remainingDef['label'],
                            'status' => 'Skipped',
                            'remark' => 'Skipped due to previous step failure',
                        ];
                    }
                }

                return response()->json([
                    'success' => false,
                    'failed_step' => $stepNo,
                    'failed_label' => $stepDef['label'],
                    'steps' => $steps,
                    'message' => $result['remark'],
                ]);
            }
        }

        // ── All steps passed ──────────────────────────────────────────────────────
        DB::table('Quote_QuotationInfo')
            ->where('ID', $quotation->ID)
            ->update([
                'SCMINTStatus' => 'Success',
                'LastModifiedBy' => $submittedBy,
                'updatedon' => now(),
            ]);

        Log::info('retrigger - Completed successfully', [
            'booking_no' => $bookingNo,
            'quote_no' => $quoteNo,
            'user' => $submittedBy,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Retrigger completed successfully',
            'steps' => $steps,
        ]);
    }

    public function getCommunicationTemplates(Request $request)
    {
        $module = $request->input('module');
        $subModule = $request->input('sub_module');
        $officeCode = $request->input('office_code');

        // This likely queries Cust_CommunicationDetails table
        $templates = DB::table('Cust_CommunicationDetails')
            ->where('Module', $module)
            ->where('SubModule', $subModule)
            ->where('OfficeCode', $officeCode)
            ->where('IsActive', 1)
            ->select('Name', 'Description')
            ->get();

        return response()->json([
            'success' => true,
            'templates' => $templates
        ]);
    }



    public function getTermsTemplates(Request $request): JsonResponse
    {
        $quotationId = trim($request->input('quotation_id', ''));

        if (!$quotationId) {
            return response()->json(['success' => false, 'message' => 'quotation_id is required'], 400);
        }

        try {
            $pdo = DB::connection()->getPdo();
            DB::table('Quote_QuotationInfo')
                ->where('ID', $quotationId)
                ->update(['OfficeCode' => DB::raw("RTRIM(OfficeCode)")]);

            $stmt = $pdo->prepare("SET NOCOUNT ON; EXEC dbo.sp_Quote_TermsandConditionList ?");
            $stmt->execute([$quotationId]);

            $templates = [];
            $resultSetIndex = 0;

            do {
                $resultSetIndex++;
                try {
                    $rows = $stmt->fetchAll(\PDO::FETCH_OBJ);

                    if (empty($rows))
                        continue;

                    $firstVal = array_values((array) $rows[0])[0] ?? '';

                    // SP CATCH block returns 'Failed - ...'
                    if (is_string($firstVal) && str_starts_with(trim($firstVal), 'Failed')) {
                        Log::warning('getTermsTemplates - SP returned failure', [
                            'quotation_id' => $quotationId,
                            'message' => $firstVal,
                        ]);
                        return response()->json([
                            'success' => false,
                            'code' => 'NOT_CONFIGURED',
                            'message' => $firstVal,
                        ], 404);
                    }

                    // Accept first result set that has a recognisable name column
                    $row = (array) $rows[0];
                    $nameColumn = null;
                    foreach (['TemplateName', 'Name', 'TCName', 'TermsName'] as $candidate) {
                        if (array_key_exists($candidate, $row)) {
                            $nameColumn = $candidate;
                            break;
                        }
                    }

                    if (!$nameColumn)
                        continue;

                    // Normalise to { Name, Description } shape expected by frontend
                    $descColumn = null;
                    foreach (['Description', 'TermsAndCondition', 'TCContent', 'Content', 'Body'] as $candidate) {
                        if (array_key_exists($candidate, $row)) {
                            $descColumn = $candidate;
                            break;
                        }
                    }

                    $templates = array_map(fn($r) => (object) [
                        'Name' => ((array) $r)[$nameColumn] ?? '',
                        'Description' => $descColumn ? (((array) $r)[$descColumn] ?? '') : '',
                    ], $rows);

                    Log::info('getTermsTemplates - Templates found', [
                        'quotation_id' => $quotationId,
                        'count' => count($templates),
                        'name_column' => $nameColumn,
                        'desc_column' => $descColumn ?? 'not found',
                        'result_set' => $resultSetIndex,
                    ]);
                    break;
                } catch (\PDOException $e) {
                    Log::debug('getTermsTemplates - Skipping result set #' . $resultSetIndex, [
                        'error' => $e->getMessage(),
                    ]);
                    continue;
                }
            } while ($stmt->nextRowset());

            if (empty($templates)) {
                Log::warning('getTermsTemplates - SP returned no templates', [
                    'quotation_id' => $quotationId,
                    'result_sets_seen' => $resultSetIndex,
                ]);
                return response()->json([
                    'success' => false,
                    'code' => 'NOT_CONFIGURED',
                    'message' => 'No T&C templates configured for this quotation.',
                ], 404);
            }

            return response()->json(['success' => true, 'templates' => $templates]);
        } catch (\Exception $e) {
            Log::error('getTermsTemplates - Failed', [
                'quotation_id' => $quotationId,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to load T&C templates: ' . $e->getMessage(),
            ], 500);
        }
    }


    /**
     * Load T&C templates via sp_Quote_CommunicationDetail_List.
     * The SP filters by Service+Process+OfficeCode resolved from the QuoteNo internally,
     * so it returns templates appropriate for this specific quotation type and office.
     */
    private function getCommunicationTemplatesViaSP(string $module, string $subModule, string $bookingNo): \Illuminate\Http\JsonResponse
    {
        try {
            $quotationId = DB::table('Quote_QuotationInfo')
                ->where('QuoteNo', $bookingNo)
                ->value('ID');

            if (!$quotationId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Quotation not found.',
                ], 404);
            }

            // ── Force-trim OfficeCode unconditionally ─────────────────────────────
            // whereRaw guard doesn't detect trailing spaces on all SQL Server
            // collations. Always RTRIM — no-op when already clean.
            DB::table('Quote_QuotationInfo')
                ->where('QuoteNo', $bookingNo)
                ->update(['OfficeCode' => DB::raw("RTRIM(OfficeCode)")]);

            // ── Log current record state after heal ───────────────────────────────
            $record = DB::table('Quote_QuotationInfo')
                ->where('QuoteNo', $bookingNo)
                ->first(['QuoteNo', 'SVName', 'process', 'OfficeCode']);

            Log::info('getCommunicationTemplatesViaSP - record state after heal', [
                'booking_no' => $bookingNo,
                'quote_no' => $record->QuoteNo ?? 'NULL',
                'svname' => $record->SVName ?? 'NULL',
                'process' => $record->process ?? 'NULL',
                'office_code' => $record->OfficeCode ?? 'NULL',
                'office_bracketed' => '[' . ($record->OfficeCode ?? '') . ']',
            ]);

            Log::info('getCommunicationTemplatesViaSP - params', [
                'booking_no' => $bookingNo,
                'module' => $module,
                'sub_module' => $subModule,
            ]);

            $pdo = DB::connection()->getPdo();
            $stmt = $pdo->prepare("SET NOCOUNT ON; EXEC dbo.sp_Quote_CommunicationDetail_List ?, ?, ?");
            $stmt->execute([$quotationId, $module, $subModule]);

            $templates = [];
            $resultSetIndex = 0;

            do {
                $resultSetIndex++;
                try {
                    $rows = $stmt->fetchAll(\PDO::FETCH_OBJ);

                    if (empty($rows)) {
                        continue;
                    }

                    $firstVal = array_values((array) $rows[0])[0] ?? '';

                    if (is_string($firstVal) && str_starts_with($firstVal, 'Failed')) {
                        Log::warning('getCommunicationTemplatesViaSP - SP guard failed', [
                            'booking_no' => $bookingNo,
                            'sub_module' => $subModule,
                            'message' => $firstVal,
                        ]);

                        $code = str_contains($firstVal, 'Office') ? 'OFFICE_NOT_FOUND' : 'NOT_CONFIGURED';

                        return response()->json([
                            'success' => false,
                            'code' => $code,
                            'message' => $firstVal,
                        ], 404);
                    }

                    if (!isset($rows[0]->TemplateID) && !isset($rows[0]->TemplateName)) {
                        Log::debug('getCommunicationTemplatesViaSP - Skipping diagnostic result set', [
                            'booking_no' => $bookingNo,
                            'result_set' => $resultSetIndex,
                            'values' => (array) $rows[0],
                        ]);
                        continue;
                    }

                    if (isset($rows[0]->TemplateID) && isset($rows[0]->TemplateName)) {
                        $templates = array_map(fn($row) => (object) [
                            'Name' => $row->TemplateName,
                            'Description' => '',
                        ], $rows);

                        Log::info('getCommunicationTemplatesViaSP - Templates found', [
                            'booking_no' => $bookingNo,
                            'sub_module' => $subModule,
                            'template_count' => count($templates),
                            'result_set' => $resultSetIndex,
                        ]);
                        break;
                    }
                } catch (\PDOException $e) {
                    Log::debug('getCommunicationTemplatesViaSP - Skipping result set', [
                        'booking_no' => $bookingNo,
                        'result_set' => $resultSetIndex,
                        'error' => $e->getMessage(),
                    ]);
                    continue;
                }
            } while ($stmt->nextRowset());

            if (empty($templates)) {
                Log::warning('getCommunicationTemplatesViaSP - No templates returned', [
                    'booking_no' => $bookingNo,
                    'sub_module' => $subModule,
                    'result_sets_seen' => $resultSetIndex,
                ]);

                return response()->json([
                    'success' => false,
                    'code' => 'NOT_CONFIGURED',
                    'message' => "No T&C templates configured for this quotation's office and service type. Please contact your administrator.",
                ], 404);
            }

            return response()->json([
                'success' => true,
                'templates' => $templates,
            ]);
        } catch (\Exception $e) {
            Log::error('getCommunicationTemplatesViaSP failed', [
                'booking_no' => $bookingNo,
                'sub_module' => $subModule,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to load T&C templates: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function getCommunicationDescription(Request $request)
    {
        $module = $request->input('module', 'Quotation');
        $subModule = $request->input('sub_module');
        $officeCode = trim($request->input('office_code', ''));
        $name = $request->input('name');

        if (!$subModule || !$officeCode || !$name) {
            return response()->json([
                'success' => false,
                'message' => 'All parameters are required'
            ], 400);
        }

        try {
            $result = DB::select('
            DECLARE @Status      VARCHAR(15);
            DECLARE @Remark      VARCHAR(1000);
            DECLARE @Description NVARCHAR(MAX);
            EXEC sp_CommunicationDetails_GetDescription ?, ?, ?, ?,
                @Status      = @Status      OUTPUT,
                @Remark      = @Remark      OUTPUT,
                @Description = @Description OUTPUT;
            SELECT @Status AS Status, @Remark AS Remark, @Description AS Description

        ', [$module, $subModule, $officeCode, $name]);
            if (empty($result)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No response from stored procedure'
                ], 500);
            }

            if ($result[0]->Status === 'Success') {
                return response()->json([
                    'success' => true,
                    'description' => $result[0]->Description
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => $result[0]->Remark ?? 'Failed to load description'
            ], 422);
        } catch (\Exception $e) {

            Log::error('getCommunicationDescription failed', [
                'office_code' => $officeCode,
                'name' => $name,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to load description: ' . $e->getMessage()
            ], 500);
        }
    }

    public function sendEmailQuote(Request $request)
    {
        $request->validate([
            'booking_no' => 'required|string',
            'quote_id' => 'required|string',
            'template_id' => 'required|string',
            'to_email' => 'required|email',
            'from_email' => 'required|email',
            'subject' => 'required|string',
            'body' => 'required|string',
        ]);

        $bookingNo = $request->booking_no;
        $toEmail = $request->to_email;
        $fromEmail = $request->from_email;
        $subject = $request->subject;
        $body = $request->body;
        $templateId = $request->template_id;

        // Fetch quotation so $quotation->ID is available
        $quotation = DB::table('Quote_QuotationInfo')
            ->where('ID', $request->quote_id)
            ->first(['ID', 'bookingno']);

        if (!$quotation) {
            return response()->json([
                'success' => false,
                'message' => 'Quotation not found',
            ], 404);
        }

        try {
            // ============================================
            // SEND EMAIL
            // ============================================
            \Illuminate\Support\Facades\Mail::send(
                [],
                [],
                function (\Illuminate\Mail\Message $message) use ($toEmail, $fromEmail, $subject, $body, $quotation) {
                    $message
                        ->to([$toEmail, 'amay@freight-in-time.com'])
                        ->cc('nashonmwendwa04@gmail.com')
                        ->replyTo($fromEmail)
                        ->subject($subject)
                        ->setBody($body, 'text/html');

                    // ============================================
                    // ATTACH PDF — prefer saved file, fall back to
                    // in-memory generation from SP if none exists.
                    // ============================================
                    $attached = false;

                    // Path 1: latest saved PDF on disk (existing behaviour)
                    $latest = DB::table('Quote_Quotation_Document')
                        ->where('QuotationInfoID', $quotation->ID)
                        ->where('DocumentType', 'QuotationPDF')
                        ->where('IsDeleted', 0)
                        ->orderByDesc('CreatedOn')
                        ->first(['Name', 'FilePath']);

                    if ($latest && $latest->FilePath) {
                        $relativePath = $this->normalizeFilePath($latest->FilePath);
                        $fullPath = \Storage::disk('public')->path($relativePath);

                        if (file_exists($fullPath)) {
                            $attachName = $latest->Name ?: "Quotation_{$quotation->bookingno}.pdf";
                            $message->attach($fullPath, ['as' => $attachName, 'mime' => 'application/pdf']);
                            $attached = true;

                            Log::info('sendEmailQuote - Attached saved PDF', [
                                'booking_no' => $quotation->bookingno,
                                'file' => $attachName,
                            ]);
                        }
                    }

                    // Path 2: no saved PDF found — generate in-memory from SP and attach directly.
                    // This avoids writing a new version to disk just to send an email.
                    if (!$attached) {
                        Log::warning('sendEmailQuote - No saved PDF found, generating in-memory from SP', [
                            'booking_no' => $quotation->bookingno,
                            'quote_no' => $quotation->QuoteNo,
                        ]);

                        $html = $this->getQuotationHtml($quotation->ID ?? '');

                        if (!empty($html)) {
                            try {
                                $options = new \Dompdf\Options();
                                $options->set('isHtml5ParserEnabled', true);
                                $options->set('isPhpEnabled', false);
                                $options->set('isRemoteEnabled', true);
                                $options->set('defaultFont', 'Arial');

                                $pdf = new \Dompdf\Dompdf($options);
                                $pdf->loadHtml($html, 'UTF-8');
                                $pdf->setPaper('A4', 'portrait');
                                $pdf->render();

                                // Attach from memory — no temp file needed
                                $attachName = "Quotation_{$quotation->bookingno}.pdf";
                                $message->attachData($pdf->output(), $attachName, ['mime' => 'application/pdf']);

                                Log::info('sendEmailQuote - Attached in-memory PDF from SP', [
                                    'booking_no' => $quotation->bookingno,
                                ]);
                            } catch (\Exception $e) {
                                Log::error('sendEmailQuote - In-memory PDF generation failed', [
                                    'booking_no' => $quotation->bookingno,
                                    'error' => $e->getMessage(),
                                ]);
                                // Email still sends — just without attachment
                            }
                        } else {
                            Log::error('sendEmailQuote - SP returned no HTML, email sent without PDF', [
                                'booking_no' => $quotation->bookingno,
                                'quote_no' => $quotation->QuoteNo ?? 'N/A',
                            ]);
                        }
                    }
                }
            );

            // ============================================
            // LOG VIA SP
            // ============================================
            $clientName = DB::table('Quote_QuotationInfo')
                ->where('bookingno', $bookingNo)
                ->value('companyname') ?? '';

            $jsonData = json_encode([
                'SchedulerType' => 'QuotationEmail',
                'SubmittedOn' => now()->format('Y-m-d H:i:s'),
                'ClientName' => $clientName,
                'EmailFrom' => $fromEmail,
                'EmailTo' => $toEmail,
                'Subject' => $subject,
                'BodyMessage' => $body,
                'Status' => 'Sent',
                'SendRemark' => 'Sent via Quotation Module' . ($request->input('quote_version') ? ' | ' . $request->input('quote_version') : ''),
            ]);

            $pdo = DB::getPdo();
            $stmt = $pdo->prepare('EXEC sp_EmailSchedulerDetailsUpdate ?, ?, ?');
            $spStatus = null;
            $spRemark = null;
            $stmt->bindParam(1, $jsonData);
            $stmt->bindParam(2, $spStatus, \PDO::PARAM_STR | \PDO::PARAM_INPUT_OUTPUT, 20);
            $stmt->bindParam(3, $spRemark, \PDO::PARAM_STR | \PDO::PARAM_INPUT_OUTPUT, 2000);
            $stmt->execute();

            if ($spStatus === 'Failed') {
                Log::error('sendEmailQuote - SP log failed', [
                    'booking_no' => $bookingNo,
                    'remark' => $spRemark,
                ]);
            }

            // ============================================
            // SAVE TEMPLATE USED AGAINST QUOTATION
            // ============================================
            DB::table('Quote_QuotationInfo')
                ->where('bookingno', $bookingNo)
                ->update(['QuotationEmailTemplate' => $templateId]);

            Log::info('sendEmailQuote - Success', [
                'booking_no' => $bookingNo,
                'to' => $toEmail,
                'subject' => $subject,
                'template' => $templateId,
                'sp_status' => $spStatus,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Email sent successfully',
            ]);
        } catch (\Symfony\Component\Mailer\Exception\TransportException $e) {
            Log::error('sendEmailQuote - SMTP error', [
                'booking_no' => $bookingNo,
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Mail server error: ' . $e->getMessage(),
            ], 500);
        } catch (\Exception $e) {
            Log::error('sendEmailQuote - Failed', [
                'booking_no' => $bookingNo,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to send email: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Generate and stream a PDF quotation document.
     * Route: GET /quotation/{id}/generate-pdf
     */
    public function generatePdf($id)
    {
        try {
            ini_set('memory_limit', '512M');
            ini_set('max_execution_time', '300');

            Log::info('PDF Generation - Starting', ['quotation_id' => $id]);

            // ============================================
            // LOAD QUOTATION
            // ============================================
            $quotation = \App\Models\QuotationInfo::where('ID', $id)->first();

            if (!$quotation) {
                Log::error('PDF Generation - Quotation not found', ['id' => $id]);
                return response()->json([
                    'success' => false,
                    'message' => 'Quotation not found',
                ], 404);
            }

            $submittedBy = auth()->user()->displayName ?? auth()->user()->Email ?? 'system';

            // ============================================
            // GUARD: QuoteNo must be set
            // ============================================
            // $quoteNo = trim((string) ($quotation->QuoteNo ?? ''));

            // if (empty($quoteNo)) {
            //     Log::error('PDF Generation - QuoteNo is empty', ['id' => $id]);
            //     return response()->json([
            //         'success' => false,
            //         'message' => 'QuoteNo is not set on this quotation. Cannot generate PDF.',
            //     ], 422);
            // }

            if (empty($quotation->ID)) {
                Log::error('PDF Generation - QuotationID is empty', ['id' => $id]);
                return response()->json([
                    'success' => false,
                    'message' => 'Quotation ID is not set. Cannot generate PDF.',
                ], 422);
            }

            // ── Force-trim OfficeCode unconditionally ─────────────────────────────
            // whereRaw guard ("OfficeCode != RTRIM(OfficeCode)") does not reliably
            // detect trailing spaces on all SQL Server collations — the update
            // silently never fires. Always RTRIM so sp_Quote_QuotationFormat's
            // office name lookup (where CO.Code = @OfficeCode) finds a match
            // and the header block renders correctly.
            DB::table('Quote_QuotationInfo')
                ->where('ID', $id)
                ->update(['OfficeCode' => DB::raw("RTRIM(OfficeCode)")]);

            // Log::info('PDF Generation - OfficeCode after heal', [
            //     'quote_no'    => $quoteNo,
            //     'office_code' => DB::table('Quote_QuotationInfo')->where('ID', $id)->value('OfficeCode'),
            // ]);

            // ============================================
            // CALL sp_Quote_QuotationFormat
            // ============================================
            $html = null;
            $spError = null;

            try {
                $pdo = DB::getPdo();
                $stmt = $pdo->prepare("
                DECLARE @QuoteFormat VARCHAR(MAX);
                EXEC dbo.sp_Quote_QuotationFormat ?,
                    @QuoteFormat = @QuoteFormat OUTPUT;
                SELECT @QuoteFormat AS QuoteFormat;
            ");

                $stmt->execute([$quotation->ID]);

                $setIndex = 0;
                do {
                    $setIndex++;
                    try {
                        $rows = $stmt->fetchAll(\PDO::FETCH_OBJ);

                        if (empty($rows)) {
                            continue;
                        }

                        if (isset($rows[0]->QuoteFormat)) {
                            $raw = (string) $rows[0]->QuoteFormat;

                            if (str_starts_with(trim($raw), 'Failed (Reason)')) {
                                $spError = $raw;
                            } else {
                                $html = $raw;
                            }
                            break;
                        }
                    } catch (\PDOException $e) {
                        // Log::debug('PDF Generation - Skipping intermediate result set #' . $setIndex, [
                        //     'quote_no' => $quoteNo,
                        //     'error'    => $e->getMessage(),
                        // ]);
                        continue;
                    }
                } while ($stmt->nextRowset());
            } catch (\Exception $e) {
                // Log::error('PDF Generation - SP execution threw exception', [
                //     // 'quote_no' => $quoteNo,
                //     'error'    => $e->getMessage(),
                //     'file'     => $e->getFile(),
                //     'line'     => $e->getLine(),
                // ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to execute quotation format SP: ' . $e->getMessage(),
                ], 500);
            }

            if (!empty($spError)) {
                // Log::error('PDF Generation - SP returned failure string', [
                //     // 'quote_no' => $quoteNo,
                //     'sp_error' => $spError,
                // ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Quotation format SP failed: ' . $spError,
                ], 500);
            }

            if (empty($html) || trim($html) === '') {
                // Log::error('PDF Generation - SP returned empty HTML', [
                //     'quote_no'          => $quoteNo,
                //     'tc_template_set'   => !empty($quotation->QuotationTCTemplate),
                //     'tc_template_value' => $quotation->QuotationTCTemplate ?? 'NULL',
                // ]);
                return response()->json([
                    'success' => false,
                    'message' => 'No quotation HTML returned from SP. '
                        . 'Ensure a Terms & Conditions template is applied before generating.',
                ], 422);
            }

            Log::info('PDF Generation - HTML retrieved from SP', [
                'quotation_id' => $quotation->ID,
                'html_length' => strlen($html),
                'tc_template' => $quotation->QuotationTCTemplate ?? 'N/A',
            ]);

            // ============================================
            // FIX LOGO PATH
            // ============================================
            $html = str_replace(
                'public/img/logo.png',
                public_path('img/logo.png'),
                $html
            );

            // ============================================
            // ADJUST LOGO SIZE
            // ============================================
            $html = str_replace(
                'style="height: 42px;"',
                'style="height: 80px; width: auto;"',
                $html
            );

            // ============================================
            // RENDER PDF WITH DOMPDF
            // ============================================
            try {
                $options = new \Dompdf\Options();
                $options->set('isHtml5ParserEnabled', true);
                $options->set('isPhpEnabled', false);
                $options->set('isRemoteEnabled', true);
                $options->set('defaultFont', 'Arial');
                $options->set('chroot', public_path());

                $pdf = new \Dompdf\Dompdf($options);
                $pdf->loadHtml($html, 'UTF-8');
                $pdf->setPaper('A4', 'portrait');
                $pdf->render();

                // Log::info('PDF Generation - DomPDF rendered successfully', [
                //     'quote_no' => $quoteNo,
                // ]);
            } catch (\Exception $e) {
                // Log::error('PDF Generation - DomPDF render failed', [
                //     'quote_no' => $quoteNo,
                //     'error'    => $e->getMessage(),
                //     'file'     => $e->getFile(),
                //     'line'     => $e->getLine(),
                // ]);
                return response()->json([
                    'success' => false,
                    'message' => 'PDF render failed: ' . $e->getMessage(),
                ], 500);
            }

            // ============================================
            // DETERMINE VERSION NUMBER
            // ============================================
            $folder = 'quotations/' . $quotation->bookingno;

            try {
                \Storage::disk('public')->makeDirectory($folder);
            } catch (\Exception $e) {
                Log::warning('PDF Generation - Could not ensure directory exists', [
                    'folder' => $folder,
                    'error' => $e->getMessage(),
                ]);
            }

            $existingVersions = DB::table('Quote_Quotation_Document')
                ->where('QuotationInfoID', $quotation->ID)
                ->where('DocumentType', 'QuotationPDF')
                ->where('IsDeleted', 0)
                ->count();

            $nextVersion = $existingVersions + 1;
            $fileName = 'Quotation_' . $quotation->bookingno
                . '_V' . $nextVersion
                . '_' . now()->format('Ymd_His')
                . '.pdf';
            $storagePath = $folder . '/' . $fileName;
            $fullPath = \Storage::disk('public')->path($storagePath);

            Log::info('PDF Generation - Paths resolved', [
                'storage_path' => $storagePath,
                'version' => $nextVersion,
                'existing_versions' => $existingVersions,
            ]);

            // ============================================
            // WRITE PDF TO DISK
            // ============================================
            try {
                $dir = dirname($fullPath);
                if (!file_exists($dir)) {
                    mkdir($dir, 0755, true);
                    Log::info('PDF Generation - Created directory', ['dir' => $dir]);
                }

                file_put_contents($fullPath, $pdf->output());

                if (!file_exists($fullPath)) {
                    throw new \Exception('File was not found on disk after write: ' . $fullPath);
                }

                Log::info('PDF Generation - File written to disk', [
                    'path' => $fullPath,
                    'size' => filesize($fullPath),
                ]);
            } catch (\Exception $e) {
                Log::error('PDF Generation - File write failed', [
                    'path' => $fullPath,
                    'error' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to save PDF to disk: ' . $e->getMessage(),
                ], 500);
            }

            // ============================================
            // RECORD IN DATABASE
            // ============================================
            try {
                DB::table('Quote_Quotation_Document')->insert([
                    'Id' => DB::raw('NEWID()'),
                    'QuotationInfoID' => $quotation->ID,
                    'DocumentType' => 'QuotationPDF',
                    'Name' => $fileName,
                    'IsVisible' => 1,
                    'IsPrinted' => 1,
                    'PrintedOn' => now(),
                    'FilePath' => $storagePath,
                    'CreatedBy' => $submittedBy,
                    'CreatedOn' => now(),
                    'LastModifiedBy' => $submittedBy,
                    'LastModifiedOn' => now(),
                    'IsDeleted' => 0,
                ]);

                // Log::info('PDF Generation - Document record inserted', [
                //     'quote_no' => $quoteNo,
                //     'version'  => $nextVersion,
                //     'file'     => $fileName,
                // ]);
            } catch (\Exception $e) {
                // Non-fatal: PDF is on disk and can be served.
                // Log::error('PDF Generation - DB insert failed (non-fatal, PDF is on disk)', [
                //     'quote_no' => $quoteNo,
                //     'error'    => $e->getMessage(),
                //     'file'     => $e->getFile(),
                //     'line'     => $e->getLine(),
                // ]);

                return response()->json([
                    'success' => true,
                    'version' => $nextVersion,
                    'label' => 'Version ' . $nextVersion,
                    'url' => route('quotation.download-pdf', [$id, $nextVersion]),
                    'warning' => 'PDF generated but version record could not be saved. Please refresh if the version does not appear.',
                ]);
            }

            // Log::info('PDF Generation - Completed successfully', [
            //     'booking_no'  => $quotation->bookingno,
            //     'quote_no'    => $quoteNo,
            //     'version'     => $nextVersion,
            //     'path'        => $storagePath,
            //     'tc_template' => $quotation->QuotationTCTemplate ?? 'N/A',
            // ]);

            return response()->json([
                'success' => true,
                'version' => $nextVersion,
                'label' => 'Version ' . $nextVersion,
                'url' => route('quotation.download-pdf', [$id, $nextVersion]),
            ]);
        } catch (\Exception $e) {
            Log::error('PDF Generation - Unexpected top-level exception', [
                'id' => $id,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'PDF generation failed unexpectedly: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Execute sp_Quote_QuotationFormat and return the HTML string.
     *
     * The SP signature:
     *   @QuotationNo VARCHAR(25) INPUT
     *   @QuoteFormat VARCHAR(MAX) OUTPUT
     *
     * On internal error the SP sets @QuoteFormat = 'Failed (Reason) - <message>'
     * instead of raising an exception, so we must detect that string explicitly.
     *
     * @param  string $quoteNo  The QuoteNo value (e.g. '8270'), NOT the UUID
     * @return string|null      Complete HTML string, or null on failure
     */
    private function getQuotationHtml(string $quotationId): ?string
    {
        // if (empty(trim($quoteNo))) {
        //     Log::error('getQuotationHtml - quoteNo is empty');
        //     return null;
        // }

        if (empty(trim($quotationId))) {
            Log::error('getQuotationHtml - quotationId is empty');
            return null;
        }

        try {
            $pdo = DB::getPdo();

            // IMPORTANT: DECLARE uses VARCHAR(MAX) to match the SP OUTPUT param type.
            // Using VARCHAR(N) here would silently truncate large HTML documents.
            $sql = "
            DECLARE @QuoteFormat VARCHAR(MAX);
            EXEC dbo.sp_Quote_QuotationFormat ?,
                @QuoteFormat = @QuoteFormat OUTPUT;
            SELECT @QuoteFormat AS QuoteFormat;
        ";



            $stmt = $pdo->prepare($sql);
            $stmt->execute([trim($quotationId)]);

            // Walk result sets — the SP creates/drops a temp table (#VATDetails)
            // which can produce intermediate empty result sets before the SELECT.
            $html = null;
            $setIndex = 0;

            do {
                $setIndex++;
                try {
                    $rows = $stmt->fetchAll(\PDO::FETCH_OBJ);

                    if (empty($rows)) {
                        continue;
                    }

                    if (isset($rows[0]->QuoteFormat)) {
                        $html = $rows[0]->QuoteFormat;
                        // Log::info('getQuotationHtml - Found QuoteFormat in result set #' . $setIndex, [
                        //     'quote_no' => $quoteNo,
                        //     'length'   => strlen($html ?? ''),
                        // ]);
                        break;
                    }
                } catch (\PDOException $e) {
                    // Skip unfetchable intermediate result sets (normal for temp table SPs)
                    // Log::debug('getQuotationHtml - Skipping result set #' . $setIndex, [
                    //     'quote_no' => $quoteNo,
                    //     'error'    => $e->getMessage(),
                    // ]);
                    continue;
                }
            } while ($stmt->nextRowset());

            // SP CATCH block sets @QuoteFormat = 'Failed (Reason) - <message>'
            // instead of raising — detect this and treat as a failure.
            if (!empty($html) && str_starts_with(trim($html), 'Failed (Reason)')) {
                // Log::error('getQuotationHtml - SP returned failure message', [
                //     'quote_no' => $quoteNo,
                //     'message'  => $html,
                // ]);
                return null;
            }

            if (empty($html) || trim($html) === '') {
                // Log::warning('getQuotationHtml - SP returned empty HTML', [
                //     'quote_no'         => $quoteNo,
                //     'result_sets_seen' => $setIndex,
                // ]);
                return null;
            }

            return $html;
        } catch (\Exception $e) {
            // Log::error('getQuotationHtml - Exception', [
            //     'quote_no' => $quoteNo,
            //     'error'    => $e->getMessage(),
            //     'file'     => $e->getFile(),
            //     'line'     => $e->getLine(),
            // ]);
            return null;
        }
    }

    public function getQuotationVersions($id)
    {
        try {
            $quotation = \App\Models\QuotationInfo::where('ID', $id)->first();

            if (!$quotation) {
                return response()->json(['success' => false, 'message' => 'Quotation not found'], 404);
            }

            $documents = DB::table('Quote_Quotation_Document')
                ->where('QuotationInfoID', $quotation->ID)
                ->where('DocumentType', 'QuotationPDF')
                ->where('IsDeleted', 0)
                ->orderBy('CreatedOn', 'asc')
                ->get(['Id', 'Name', 'FilePath', 'CreatedOn']);

            $versions = [];

            $versionCounter = 0; // sequential counter — only incremented for files that exist on disk

            foreach ($documents as $doc) {
                $relativePath = $this->normalizeFilePath($doc->FilePath);
                $fileExists = \Storage::disk('public')->exists($relativePath);

                // Skip versions whose file no longer exists on disk.
                // Do NOT increment versionCounter here — the counter must only reflect
                // downloadable versions so that downloadPdf()->skip($version - 1) resolves correctly.
                if (!$fileExists) {
                    Log::warning('getQuotationVersions - File missing on disk, skipping', [
                        'quotation_id' => $id,
                        'file_name' => $doc->Name,
                        'raw_path' => $doc->FilePath,
                        'relative' => $relativePath,
                    ]);
                    continue;
                }

                $versionCounter++;

                Log::info('getQuotationVersions - Version entry', [
                    'quotation_id' => $id,
                    'version_no' => $versionCounter,
                    'file_name' => $doc->Name,
                    'file_path' => $relativePath,
                ]);

                $versions[] = [
                    'version' => $versionCounter,
                    'label' => 'Version ' . $versionCounter,
                    'url' => route('quotation.download-pdf', [$id, $versionCounter]),
                    'created_on' => $doc->CreatedOn,
                    'file_name' => $doc->Name,
                    'doc_id' => $doc->Id,
                ];
            }
            Log::info('getQuotationVersions - Completed', [
                'quotation_id' => $id,
                'total_db_docs' => $documents->count(),
                'versions_found' => count($versions),
            ]);

            return response()->json([
                'success' => true,
                'versions' => $versions,
            ]);
        } catch (\Exception $e) {
            Log::error('getQuotationVersions - Failed', [
                'id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(['success' => false, 'message' => 'Failed to load versions'], 500);
        }
    }

    public function getEmailHistory(Request $request)
    {
        $bookingNo = $request->input('booking_no');

        if (!$bookingNo) {
            return response()->json(['success' => false, 'message' => 'Booking number is required'], 400);
        }

        try {
            // Fetch quotation to get companyname for matching
            $quotation = DB::table('Quote_QuotationInfo')
                ->where('bookingno', $bookingNo)
                ->first(['companyname']);
            if (!$quotation) {
                return response()->json(['success' => true, 'emails' => []]);
            }

            $emails = DB::table('email_emailscheduler')
                ->where('ClientName', $quotation->companyname)
                ->where('SchedulerType', 'QuotationEmail')
                ->orderBy('SubmittedOn', 'desc')
                ->get(['SubmittedOn as sent_on', 'EmailTo as to_email', 'Status as status', 'SendRemark as send_remark']);

            // Extract version from SendRemark — stored as "Sent via Quotation Module | Version N"
            $emails = $emails->map(function ($email) {
                $version = null;
                if (!empty($email->send_remark) && str_contains($email->send_remark, '|')) {
                    $version = trim(explode('|', $email->send_remark, 2)[1]);
                }
                $email->quote_version = $version ?: 'N/A';
                unset($email->send_remark);
                return $email;
            });

            return response()->json(['success' => true, 'emails' => $emails]);
        } catch (\Exception $e) {

            Log::error('getEmailHistory - Failed', [
                'booking_no' => $bookingNo,
                'error' => $e->getMessage(),
            ]);

            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function getCharges(Request $request)
    {
        $bookingNo = $request->input('booking_no');
        $quotationId = trim($request->input('quotation_id', ''));

        if (!$bookingNo) {
            return response()->json(['success' => false, 'message' => 'Booking number is required'], 400);
        }

        try {
            // Use quotation_id directly if passed — avoids hitting the wrong row
            // when multiple quotations share the same bookingno across offices.
            if (!$quotationId) {
                $quotation = DB::table('Quote_QuotationInfo')
                    ->where('bookingno', $bookingNo)
                    ->first(['ID']);

                if (!$quotation) {
                    return response()->json(['success' => false, 'message' => 'Quotation not found'], 404);
                }

                $quotationId = $quotation->ID;
            }

            $pdo = DB::connection()->getPdo();
            $revenue = $this->fetchChargeListFromSP($pdo, $quotationId, 1);
            $cost = $this->fetchChargeListFromSP($pdo, $quotationId, 0);

            Log::info('getCharges - Success', [
                'booking_no' => $bookingNo,
                'quotation_id' => $quotationId,
                'quotation_source' => $request->has('quotation_id') ? 'request' : 'db_lookup',
                'revenue_count' => count($revenue),
                'cost_count' => count($cost),
            ]);

            return response()->json(['success' => true, 'revenue' => $revenue, 'cost' => $cost]);
        } catch (\RuntimeException $e) {
            Log::error('getCharges - SP error (DBA action required)', [
                'booking_no' => $bookingNo,
                'sp_error' => $e->getMessage(),
            ]);
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'dba_action_required' => true,
            ], 500);
        } catch (\Exception $e) {
            Log::error('getCharges failed', [
                'booking_no' => $bookingNo,
                'error' => $e->getMessage(),
            ]);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    private function fetchChargeListFromSP(\PDO $pdo, string $quotationId, int $isRevenue): array
    {
        $stmt = $pdo->prepare("SET NOCOUNT ON; EXEC dbo.sp_Quote_Quotation_ChargeList ?, ?, NULL");
        $stmt->execute([$quotationId, $isRevenue]);

        $rows = [];

        do {
            try {
                $fetched = $stmt->fetchAll(\PDO::FETCH_OBJ);

                if (empty($fetched))
                    continue;

                $firstVal = array_values((array) $fetched[0])[0] ?? '';

                if (is_string($firstVal) && str_starts_with($firstVal, 'Failed')) {
                    Log::error('fetchChargeListFromSP - SP failure', [
                        'quotation_id' => $quotationId,
                        'is_revenue' => $isRevenue,
                        'message' => $firstVal,
                    ]);
                    throw new \RuntimeException('sp_Quote_Quotation_ChargeList: ' . $firstVal);
                }

                // if (isset($fetched[0]->Id)) {
                //     $rows = $fetched;
                //     break;
                // }
                $rows = $fetched;
                break;
            } catch (\PDOException $e) {
                continue;
            }
        } while ($stmt->nextRowset());

        return array_map(fn($row) => [
            'id' => $row->Id,
            'chargeCode' => $row->ChargeCode ?? '',
            'chargeName' => $row->ChargeDesp ?? '',
            'rate' => (float) ($row->EstChargeRate ?? 0),
            'quantity' => (float) ($row->Quantity ?? 0),
            'amtInChgCurr' => (float) ($row->EstChargeAmount ?? 0),
            'amtInBaseCurr' => (float) ($row->EstBaseAmount ?? 0),
            'exChangeRate' => (float) ($row->BaseExchRate ?? 1),
            'chgCurr' => $row->EstChargeCurrCode ?? 'KES',
            'basedOnCode' => '',   // not in list SP — fetched on edit
            'basedOnAndUOM' => $row->BasedOnAndUOM ?? '',
            'chargeRemark' => '',   // not in list SP — fetched on edit
            'partyCode' => '',   // not in list SP — fetched on edit
            'revenueOrCost' => $isRevenue ? 'Revenue' : 'Cost',
            'sourceType' => 'manual',
        ], $rows);
    }

    public function getChargeCodes(Request $request)
    {
        $bookingNo = $request->input('booking_no');
        $chargeType = $request->input('charge_type', 'Revenue');
        $filter = $request->input('filter', '');
        $officeCode = trim($request->input('office_code', ''));

        Log::info('=== GET CHARGE CODES CALLED ===', [
            'booking_no' => $bookingNo,
            'charge_type' => $chargeType,
            'filter' => $filter,
            'filter_length' => strlen($filter),
            'office_code_from_request' => $officeCode ?: null,
        ]);

        if (!$bookingNo) {
            return response()->json(['success' => false, 'message' => 'Booking number required'], 400);
        }

        try {
            // Only hit the DB if office_code was not passed from the frontend.
            // Prefer the passed value — it comes from $quotation->OfficeCode which
            // is the same value used for save, exchange rate, and all other SP calls.
            if (!$officeCode) {
                $quotation = DB::table('Quote_QuotationInfo')
                    ->where('bookingno', $bookingNo)
                    ->first(['teamid']);

                if (!$quotation) {
                    Log::warning('Quotation not found', ['booking_no' => $bookingNo]);
                    return response()->json(['success' => false, 'message' => 'Quotation not found'], 404);
                }

                Log::info('Quotation found', ['team_id' => $quotation->teamid]);

                $office = DB::table('Cust_Office')
                    ->where('TeamCode', $quotation->teamid)
                    ->where('IsActive', 1)
                    ->where('IsDeleted', 0)
                    ->first(['Code']);

                if (!$office) {
                    Log::warning('Office not found', ['team_id' => $quotation->teamid]);
                    return response()->json(['success' => false, 'message' => 'Office not found'], 404);
                }

                $officeCode = trim($office->Code);
            }

            Log::info('=== EXECUTING SP_CHARGECODE ===', [
                'office_code' => $officeCode,
                'charge_type' => $chargeType,
                'filter' => $filter,
                'office_source' => $request->has('office_code') ? 'request' : 'db_lookup',
            ]);

            $pdo = DB::connection()->getPdo();
            $stmt = $pdo->prepare("SET NOCOUNT ON; EXEC sp_ChargeCode ?, ?, ?");
            $stmt->execute([$officeCode, $chargeType, $filter]);

            $chargeCodes = [];
            $resultSetCount = 0;

            do {
                $resultSetCount++;
                try {
                    $results = $stmt->fetchAll(\PDO::FETCH_OBJ);

                    Log::info("Result set #$resultSetCount", [
                        'count' => count($results),
                        'first_row' => !empty($results) ? (array) $results[0] : null,
                    ]);

                    if (!empty($results)) {
                        $firstRow = $results[0];
                        if (isset($firstRow->Id) && isset($firstRow->Code)) {
                            $chargeCodes = $results;
                            Log::info('✓ Found charge codes result set', ['count' => count($chargeCodes)]);
                            break;
                        }
                    }
                } catch (\Exception $e) {
                    Log::debug("Result set #$resultSetCount error", ['error' => $e->getMessage()]);
                    continue;
                }
            } while ($stmt->nextRowset());

            Log::info('=== FINAL RESULT ===', [
                'charge_codes_found' => count($chargeCodes),
                'total_result_sets' => $resultSetCount,
            ]);

            return response()->json([
                'success' => true,
                'charge_codes' => $chargeCodes,
            ]);
        } catch (\Exception $e) {
            Log::error('Charge codes error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to load charge codes: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function getOfficeCode(Request $request)
    {
        try {
            $teamId = $request->team_id;

            Log::info('getOfficeCode called', ['team_id' => $teamId]);

            if (!$teamId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Team ID is required'
                ], 400);
            }

            // ✅ FIX: Use TeamCode (int column), not TeamId
            $office = DB::connection('sqlsrv')
                ->table('Cust_Office')
                ->where('TeamCode', $teamId)           // ✅ Changed from TeamId
                ->where('IsActive', 1)                 // ✅ Added active filter
                ->where('IsDeleted', 0)                // ✅ Added deleted filter
                ->select('Code', 'TeamCode')           // ✅ Changed from TeamId
                ->first();

            Log::info('Office lookup result', [
                'team_id' => $teamId,
                'office_found' => $office ? 'yes' : 'no',
                'office_code' => $office ? $office->Code : null
            ]);

            if (!$office) {

                // Fallback: Try to get from quotation
                $quotation = DB::connection('sqlsrv')
                    ->table('Quote_QuotationInfo')
                    ->where('teamid', $teamId)
                    ->select('teamid')
                    ->first();

                if ($quotation) {
                    $office = DB::connection('sqlsrv')
                        ->table('Cust_Office')
                        ->where('TeamCode', $quotation->teamid)  // ✅ Changed from TeamId
                        ->where('IsActive', 1)
                        ->where('IsDeleted', 0)
                        ->select('Code')
                        ->first();
                }
            }

            if (!$office) {
                return response()->json([
                    'success' => false,
                    'message' => 'Office not found for team ID: ' . $teamId
                ], 404);
            }

            return response()->json([
                'success' => true,
                'office_code' => trim($office->Code)
            ]);
        } catch (\Exception $e) {

            Log::error('Failed to get office code', [
                'team_id' => $request->team_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve office code: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get currencies for office
     */

    public function getCurrencies(Request $request)
    {
        try {
            $officeId = trim($request->office_id);

            if (!$officeId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Office ID is required'
                ], 400);
            }

            $pdo = DB::connection()->getPdo();
            $stmt = $pdo->prepare("SET NOCOUNT ON; EXEC dbo.sp_CurrencyList ?, ?");
            $stmt->execute([$officeId, '']);

            $currencies = [];
            do {
                try {
                    $rows = $stmt->fetchAll(\PDO::FETCH_OBJ);
                    if (empty($rows))
                        continue;

                    $firstVal = array_values((array) $rows[0])[0] ?? '';
                    if (
                        is_string($firstVal) && (
                            str_contains($firstVal, 'mandatory') ||
                            str_contains($firstVal, 'does not exist')
                        )
                    ) {
                        Log::warning('getCurrencies - SP guard error', [
                            'office_id' => $officeId,
                            'message' => $firstVal,
                        ]);
                        return response()->json([
                            'success' => false,
                            'message' => $firstVal
                        ], 404);
                    }

                    if (isset($rows[0]->CurrencyID, $rows[0]->CurrencyCode)) {
                        $currencies = $rows;
                        break;
                    }
                } catch (\PDOException $e) {
                    continue;
                }
            } while ($stmt->nextRowset());

            return response()->json([
                'success' => true,
                'currencies' => array_map(fn($c) => [
                    'CurrencyID' => $c->CurrencyID,
                    'CurrencyCode' => $c->CurrencyCode,
                ], $currencies),
            ]);
        } catch (\Exception $e) {
            Log::error('getCurrencies - exception', [
                'office_id' => $request->office_id ?? '',
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve currencies: ' . $e->getMessage()
            ], 500);
        }
    }
    /**
     * Get exchange rate for currency
     */

    public function getExchangeRate(Request $request)
    {
        $officeCode = trim($request->office_code);
        $fromCurrency = trim($request->from_currency);
        $toCurrency = trim($request->to_currency ?? 'KES');

        // ============================================
        // SIMPLE FIX: Convert UUID to short code if needed
        // ============================================
        // Check if it's a UUID (has hyphens)
        if (str_contains($officeCode, '-')) {
            $officeCode = DB::table('Cust_Office')
                ->where('Id', $officeCode)
                ->where('IsActive', 1)
                ->where('IsDeleted', 0)
                ->value('Code');

            if (!$officeCode) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid office ID'
                ], 404);
            }
        }

        // Accept any date format from frontend, normalize to '05/Mar/2026'
        // AFTER
        try {
            $dateFor = $request->date_for
                ? \Carbon\Carbon::parse($request->date_for)->format('d/M/Y')
                : now()->format('d/M/Y');
        } catch (\Exception $e) {
            Log::warning('getExchangeRate - Invalid date_for value, falling back to today', [
                'date_for_raw' => $request->date_for,
                'error' => $e->getMessage(),
            ]);
            $dateFor = now()->format('d/M/Y');
        }

        if (!$officeCode || !$fromCurrency) {
            return response()->json([
                'success' => false,
                'message' => 'Office code and from currency are required'
            ], 400);
        }

        if ($fromCurrency === $toCurrency) {
            return response()->json([
                'success' => true,
                'exchange_rate' => 1.000000
            ]);
        }

        try {
            $result = DB::selectOne("
            SELECT dbo.fn_GetCurrentExRateUsingCodeandPeriod(?, ?, ?, ?) AS ExchangeRate
        ", [$officeCode, $fromCurrency, $toCurrency, $dateFor]);

            $rate = $result ? (float) $result->ExchangeRate : null;

            Log::info('getExchangeRate - Result', [
                'office_code' => $officeCode,
                'from_currency' => $fromCurrency,
                'to_currency' => $toCurrency,
                'date_for' => $dateFor,
                'rate' => $rate,
            ]);

            if (!$rate || $rate <= 0) {
                Log::warning('getExchangeRate - No rate found', [
                    'office_code' => $officeCode,
                    'from_currency' => $fromCurrency,
                    'to_currency' => $toCurrency,
                    'date_for' => $dateFor,
                ]);

                return response()->json([
                    'success' => false,
                    'message' => "No exchange rate found for {$fromCurrency} → {$toCurrency} in office {$officeCode} on {$dateFor}",
                ], 404);
            }

            return response()->json([
                'success' => true,
                'exchange_rate' => $rate,
            ]);
        } catch (\Exception $e) {
            Log::error('getExchangeRate - Failed', [
                'office_code' => $officeCode,
                'from_currency' => $fromCurrency,
                'to_currency' => $toCurrency,
                'date_for' => $dateFor,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve exchange rate: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getContractCharges(Request $request)
    {
        $bookingNo = $request->input('booking_no');

        if (!$bookingNo) {
            return response()->json(['success' => false, 'message' => 'Booking number is required'], 400);
        }

        try {
            $quotation = DB::table('Quote_QuotationInfo')
                ->where('bookingno', $bookingNo)
                ->first([
                    'clientid',
                    'ServiceVerticalname',
                    'process',
                    'origincountry',
                    'destinationcountry',
                    'incoterm',
                    'shipmentmode',
                    'TransportMode',
                    'ServiceClass',
                    'commodity',
                    'containerlist',
                    'scopevolume',
                    'cargovolume',
                ]);

            if (!$quotation) {
                return response()->json(['success' => false, 'message' => 'Quotation not found'], 404);
            }

            // Parse containerlist — try JSON first, otherwise pass empty array
            $equipment = [];

            if (!empty($quotation->containerlist)) {
                $decoded = json_decode($quotation->containerlist, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $equipment = $decoded;
                }
            }

            $inputJson = json_encode([
                'Service' => $quotation->ServiceVerticalname ?? '',
                'Process' => $quotation->process ?? '',
                'Client' => $quotation->clientid ?? '',
                'Weight' => (float) ($quotation->scopevolume ?? 0),
                'Volume' => (float) ($quotation->cargovolume ?? 0),
                'Filter' => [
                    ['Code' => 'Inco Terms', 'Value' => $quotation->incoterm ?? ''],
                    ['Code' => 'Country Of Origin', 'Value' => $quotation->origincountry ?? ''],
                    ['Code' => 'Country Of Destination', 'Value' => $quotation->destinationcountry ?? ''],
                    ['Code' => 'Place Of Reciept', 'Value' => ''],
                    ['Code' => 'Place Of Delivery', 'Value' => ''],
                    ['Code' => 'Port Of Loading', 'Value' => ''],
                    ['Code' => 'Port Of Discharge', 'Value' => ''],
                    ['Code' => 'Service Class', 'Value' => $quotation->ServiceClass ?? ''],
                    ['Code' => 'Commodity', 'Value' => $quotation->commodity ?? ''],
                    ['Code' => 'Shipment Mode', 'Value' => $quotation->shipmentmode ?? ''],
                    ['Code' => 'Shipment Type', 'Value' => ''],
                    ['Code' => 'Movement Type', 'Value' => $quotation->TransportMode ?? ''],
                    ['Code' => 'Forwarder', 'Value' => ''],
                    ['Code' => 'Shipping Line', 'Value' => ''],
                    ['Code' => 'Air Line', 'Value' => ''],
                    ['Code' => 'Trucker', 'Value' => ''],
                ],

                'Equipment' => $equipment,
            ]);

            Log::info('getContractCharges - Input JSON', [
                'booking_no' => $bookingNo,
                'input_json' => $inputJson,
            ]);

            $pdo = DB::connection()->getPdo();
            $stmt = $pdo->prepare("

            DECLARE @Output VARCHAR(MAX);
            EXEC dbo.sp_Quote_QuotationCharges ?, @Output = @Output OUTPUT;
            SELECT @Output AS OutputJson
        ");

            $stmt->execute([$inputJson]);

            // Walk result sets to find the OutputJson row
            $outputJson = null;
            do {
                try {
                    $rows = $stmt->fetchAll(\PDO::FETCH_OBJ);
                    if (!empty($rows) && isset($rows[0]->OutputJson)) {
                        $outputJson = $rows[0]->OutputJson;
                        break;
                    }
                } catch (\PDOException $e) {

                    continue;
                }
            } while ($stmt->nextRowset());

            if ($outputJson === null) {
                Log::warning('getContractCharges - SP returned no output', ['booking_no' => $bookingNo]);
                return response()->json(['success' => true, 'charges' => []]);
            }

            $output = json_decode($outputJson, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('getContractCharges - Failed to decode SP output', [
                    'booking_no' => $bookingNo,
                    'raw' => $outputJson,
                ]);

                return response()->json(['success' => false, 'message' => 'Failed to parse contract charges'], 500);
            }

            // SP may return {"Charges": [...]} or a raw array

            $charges = $output['Charges'] ?? (isset($output[0]) ? $output : []);

            Log::info('getContractCharges - Success', [
                'booking_no' => $bookingNo,
                'charge_count' => count($charges),
            ]);

            return response()->json(['success' => true, 'charges' => $charges]);
        } catch (\Exception $e) {

            Log::error('getContractCharges - Failed', [
                'booking_no' => $bookingNo,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function getLegalEntities(Request $request)
    {
        $officeCode = trim($request->input('office_code', ''));
        $chargeType = $request->input('charge_type', 'Cost');
        $filter = $request->input('filter', '');

        if (!$officeCode) {
            return response()->json(['success' => false, 'message' => 'Office code is required'], 400);
        }

        if (!in_array($chargeType, ['Revenue', 'Cost'])) {
            $chargeType = 'Cost';
        }

        try {
            $pdo = DB::connection()->getPdo();
            $stmt = $pdo->prepare("SET NOCOUNT ON; EXEC sp_LegalEntityList ?, ?, ?");
            $stmt->execute([$officeCode, $chargeType, $filter]);

            $parties = [];
            do {
                try {
                    $results = $stmt->fetchAll(\PDO::FETCH_OBJ);
                    if (empty($results))
                        continue;
                    $firstRow = (array) $results[0];
                    $firstVal = array_values($firstRow)[0] ?? '';

                    // Skip SP error/status result sets
                    if (is_string($firstVal) && str_starts_with((string) $firstVal, 'Failed')) {
                        Log::warning('sp_LegalEntityList returned failure', [
                            'office_code' => $officeCode,
                            'charge_type' => $chargeType,
                            'message' => $firstVal,
                        ]);
                        continue;
                    }

                    // Valid data result set — must have Id and Code

                    if (isset($results[0]->Id) && isset($results[0]->Code)) {
                        $parties = $results;
                        break;
                    }
                } catch (\Exception $e) {
                    continue;
                }
            } while ($stmt->nextRowset());

            return response()->json(['success' => true, 'parties' => $parties]);
        } catch (\Exception $e) {

            Log::error('getLegalEntities failed', [
                'office_code' => $officeCode,
                'error' => $e->getMessage(),
            ]);

            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function getBasedOnList(Request $request)
    {
        try {
            $basedOnList = DB::select(
                "SELECT Id, Code, Name, BasedOn
                    FROM Cust_ChargecodeBasedOn WITH (NOLOCK)
                    WHERE IsActive = 1
                ORDER BY Name"
            );

            return response()->json([
                'success' => true,
                'based_on_list' => $basedOnList
            ]);
        } catch (\Exception $e) {

            Log::error('Get based on list failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to load based on list: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get UOM list based on Based On code
     */

    public function getBasedOnUOM(Request $request)
    {
        try {
            $basedOnCode = $request->based_on_code;

            Log::info('Get based on UOM called', ['based_on_code' => $basedOnCode]);

            if (!$basedOnCode) {
                return response()->json([
                    'success' => false,
                    'message' => 'Based On code is required'
                ], 400);
            }

            // Execute stored procedure
            $pdo = DB::connection('sqlsrv')->getPdo();
            $stmt = $pdo->prepare("EXEC sp_ChargeBasedOnUOM ?");
            $stmt->execute([$basedOnCode]);

            $uomList = [];
            $hasResults = false;

            // Handle multiple result sets
            do {
                try {
                    $results = $stmt->fetchAll(\PDO::FETCH_OBJ);

                    if (!empty($results)) {
                        // Check if it's an error message from the SP
                        $firstRow = $results[0];
                        $firstColumn = current((array) $firstRow);

                        // If the SP returns "Failed - ..." message

                        if (is_string($firstColumn) && strpos($firstColumn, 'Failed') === 0) {
                            Log::warning('SP returned failure message', [
                                'based_on_code' => $basedOnCode,
                                'message' => $firstColumn
                            ]);
                            continue; // Skip this result set
                        }

                        // Valid UOM data
                        $uomList = array_merge($uomList, $results);
                        $hasResults = true;
                        Log::info('UOM result set fetched', ['count' => count($results)]);
                    }
                } catch (\PDOException $e) {

                    // Skip empty result sets (this is normal for this SP)
                    if (strpos($e->getMessage(), 'no fields') === false) {
                        throw $e; // Re-throw if it's a real error
                    }

                    Log::debug('Empty result set skipped', ['error' => $e->getMessage()]);
                }
            } while ($stmt->nextRowset());

            // If no UOM found, it means this Based On doesn't have UOM requirements
            // This is valid for things like AssessableValue, CommercialInvoiceVal, etc.
            if (empty($uomList)) {

                Log::info('No UOM required for this Based On', ['based_on_code' => $basedOnCode]);
                return response()->json([
                    'success' => true,
                    'uom_list' => [],
                    'message' => 'No UOM selection required for this Based On type'
                ]);
            }

            Log::info('UOM list fetched successfully', [
                'based_on_code' => $basedOnCode,
                'count' => count($uomList)
            ]);

            return response()->json([
                'success' => true,
                'uom_list' => $uomList
            ]);
        } catch (\Exception $e) {

            Log::error('Get based on UOM failed', [
                'based_on_code' => $request->based_on_code,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to get UOM list: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create a booking from a won quotation.
     * TODO: Implement full booking creation logic.
     */
    public function createBooking(Request $request)
    {
        $request->validate([
            'booking_no' => 'required|string',
            'service_template' => 'required|string',
            'bill_to_party' => 'required|string',
            'forwarder' => 'required|string',
            'place_of_receipt' => 'required|string',
            'place_of_delivery' => 'required|string',
        ]);

        $submittedBy = auth()->user()->displayName ?? auth()->user()->Email ?? 'system';
        $input = json_encode([
            'OperationType' => 'BookingInfoUpdate',
            'SubmittedDatetime' => now()->format('YmdHis'),
            'SubmittedBy' => $submittedBy,
            'Action' => 'Update',
            'QuotationNo' => $request->booking_no,
            'ServiceTemplate' => $request->service_template,
            'BillToParty' => $request->bill_to_party,
            'Forwarder' => $request->forwarder,
            'CountryIncluded' => $request->include_country ? 1 : 0,
            'PlaceOfReceipt' => $request->place_of_receipt,
            'PlaceOfDelivery' => $request->place_of_delivery,
        ]);

        if (strlen($input) > 2000) {
            return response()->json([
                'success' => false,
                'message' => 'Request payload too large for SP. Max 2000 characters.'
            ], 400);
        }

        try {
            $output = $this->executeDetailsUpdateSP($input);
            if (($output['Status'] ?? '') === 'Failed') {
                $remarks = collect($output['Remarks'] ?? [])->pluck('Remark')->implode(', ');
                return response()->json([
                    'success' => false,
                    'message' => $remarks ?: 'Booking update failed'
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Booking created successfully'
            ]);
        } catch (\Exception $e) {

            Log::error('createBooking error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Resolve Cust_Office.Id (UUID) from Quote_QuotationInfo.teamid (bigint via TeamCode)
     * Shared helper used by Bill To Party and Forwarder lookups.
     */
    private function resolveOfficeId(string $teamId): ?string
    {
        $office = DB::selectOne("
        SELECT SCMOfficeID
        FROM Cust_Office WITH (NOLOCK)
        WHERE TeamCode = ?
            AND IsActive = 1
            AND IsDeleted = 0
        ", [$teamId]);

        return $office ? $office->SCMOfficeID : null;
    }

    /**
     * GET /quotation/create-booking/service-templates
     * Calls sp_Quote_ServiceTemplateList with the QuotationID.
     * Returns: [{ Id, Name }]
     */

    public function getServiceTemplates(Request $request)
    {
        $quotationId = $request->query('quotation_id');
        $filter = $request->query('filter', '');

        Log::info('getServiceTemplates - params', [
            'quotation_id' => $quotationId,
            'filter' => $filter,
        ]);

        if (!$quotationId) {
            return response()->json(['success' => false, 'message' => 'quotation_id is required'], 422);
        }

        try {
            $pdo = DB::getPdo();
            $stmt = $pdo->prepare("SET NOCOUNT ON; EXEC dbo.sp_Quote_ServiceTemplateList ?, ?");
            $stmt->execute([$quotationId, $filter]);

            $results = [];
            $resultSetIndex = 0;

            do {
                $resultSetIndex++;
                try {
                    $rows = $stmt->fetchAll(\PDO::FETCH_OBJ);

                    Log::debug('getServiceTemplates - row keys', [
                        'keys' => !empty($rows) ? array_keys((array) $rows[0]) : [],
                    ]);

                    Log::debug('getServiceTemplates - Result set #' . $resultSetIndex, [
                        'quotation_id' => $quotationId,
                        'row_count' => count($rows),
                        'first_row' => !empty($rows) ? (array) $rows[0] : null,
                    ]);

                    if (empty($rows)) {
                        continue;
                    }

                    $firstVal = array_values((array) $rows[0])[0] ?? null;

                    // Skip diagnostic UUID result sets
                    if (count($rows) === 1 && count((array) $rows[0]) === 1 && is_string($firstVal)) {
                        if (preg_match('/^[0-9a-f\-]{36}$/i', trim($firstVal))) {
                            Log::debug('getServiceTemplates - Skipping diagnostic UUID result set');
                            continue;
                        }
                    }

                    // SP failure message
                    if (is_string($firstVal) && str_starts_with($firstVal, 'Failed')) {
                        // Map SP failure messages to user-friendly explanations
                        $userMessage = $firstVal;
                        if (str_contains($firstVal, 'Service Details')) {
                            $userMessage = 'Service type is not configured on this quotation (SVName is missing or unrecognised).';
                        } elseif (str_contains($firstVal, 'Process Details')) {
                            $userMessage = 'Process type is not configured on this quotation (Export/Import/Local required).';
                        } elseif (str_contains($firstVal, 'Office Details')) {
                            $userMessage = 'Office could not be resolved for this quotation.';
                        } elseif (str_contains($firstVal, 'does not exists')) {
                            $userMessage = 'Quotation not found in the system.';
                        }

                        Log::warning('getServiceTemplates - SP returned failure', [
                            'quotation_id' => $quotationId,
                            'sp_message' => $firstVal,
                            'user_message' => $userMessage,
                        ]);
                        return response()->json([
                            'success' => false,
                            'code' => 'SP_GUARD_FAILED',
                            'message' => $userMessage,
                        ], 422);
                    }

                    // Actual template data — SP returns lowercase 'id' and 'Name'
                    if (isset($rows[0]->id) && isset($rows[0]->Name)) {
                        $results = $rows;
                        Log::info('getServiceTemplates - Found templates in result set #' . $resultSetIndex, [
                            'quotation_id' => $quotationId,
                            'template_count' => count($results),
                        ]);
                        break;
                    }
                } catch (\PDOException $e) {
                    Log::debug('getServiceTemplates - Skipping unfetchable result set #' . $resultSetIndex, [
                        'error' => $e->getMessage(),
                    ]);
                    continue;
                }
            } while ($stmt->nextRowset());

            Log::info('getServiceTemplates - Completed', [
                'quotation_id' => $quotationId,
                'template_count' => count($results),
                'result_sets_seen' => $resultSetIndex,
            ]);

            // Empty result set means no templates configured in SCM for this
            // service type + process + office combination — not an SP error.
            // Return a specific code so the frontend can show an actionable message.
            if (empty($results)) {
                Log::warning('getServiceTemplates - No templates found in SCM', [
                    'quotation_id' => $quotationId,
                ]);
                return response()->json([
                    'success' => false,
                    'code' => 'NO_TEMPLATES_CONFIGURED',
                    'message' => 'No service templates are configured for this quotation\'s service type and office. Please contact your SCM administrator.',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'templates' => $results,
            ]);
        } catch (\Exception $e) {
            Log::error('getServiceTemplates failed', [
                'quotation_id' => $quotationId,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * GET /quotation/create-booking/bill-to-parties
     * Calls sp_PartyList with PartyType = 'Debtor'
     * Returns: [{ PartyID, PartyCode, PartyName }]
     */

    public function getBillToParties(Request $request)
    {
        $teamId = $request->query('team_id');
        $filter = $request->query('filter', '');

        if (!$teamId) {
            return response()->json(['success' => false, 'message' => 'team_id is required'], 422);
        }

        $officeId = $this->resolveOfficeId($teamId);

        if (!$officeId) {
            return response()->json(['success' => false, 'message' => 'Office not found for team_id: ' . $teamId], 404);
        }

        try {
            $results = DB::select("EXEC dbo.sp_PartyList ?, 'Debtor', ?", [
                $officeId,
                $filter,
            ]);

            return response()->json([
                'success' => true,
                'parties' => $results, // each row has PartyID, PartyCode, PartyName
            ]);
        } catch (\Exception $e) {

            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * GET /quotation/create-booking/forwarders
     * Calls sp_PartyList with PartyType = 'Agent / Forwarder'
     * Returns: [{ PartyID, PartyCode, PartyName }]
     */

    public function getForwarders(Request $request)
    {
        $teamId = $request->query('team_id');
        $filter = $request->query('filter', '');

        if (!$teamId) {
            return response()->json(['success' => false, 'message' => 'team_id is required'], 422);
        }

        $officeId = $this->resolveOfficeId($teamId);

        if (!$officeId) {
            return response()->json(['success' => false, 'message' => 'Office not found for team_id: ' . $teamId], 404);
        }

        try {
            $results = DB::select("EXEC dbo.sp_PartyList ?, 'Agent / Forwarder', ?", [
                $officeId,
                $filter,
            ]);

            return response()->json([
                'success' => true,
                'parties' => $results, // each row has PartyID, PartyCode, PartyName
            ]);
        } catch (\Exception $e) {

            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }



    /**
     * GET /quotation/create-booking/locations
     * Calls sp_LocationList with LocationType = 'Road' (always static per spec)
     * @IncludeCountry is driven by the checkbox value passed from the frontend
     * Returns: [{ LocationID, LocationCode, LocationName }]
     */
    public function getLocations(Request $request)
    {
        $filter = $request->query('filter', '');
        $includeCountry = (int) $request->query('include_country', 0);
        $countryCode = $request->query('country_code', '');

        Log::info('getLocations - params', [
            'filter' => $filter,
            'include_country' => $includeCountry,
            'country_code' => $countryCode,
            'db_name' => DB::getDatabaseName(),
            'db_prefix' => DB::getDatabaseName() === 'FITSYSUAT' ? '[FITSCMUAT].dbo.' : '[FITSCM].dbo.',
        ]);

        try {
            $db = DB::getDatabaseName() === 'FITSYSUAT'
                ? '[FITSCMUAT].dbo.'
                : '[FITSCM].dbo.';

            $locationFilter = "('Station','Location','Airport','Seaport','InlandPort')";
            $query = "
            SELECT
                CL.Id   AS LocationID,
                CL.Code AS LocationCode,
                CL.Name AS LocationName,
                SL.Code AS LocationType
            FROM {$db}Cust_Location CL WITH (NOLOCK)
            INNER JOIN {$db}sys_locationType SL WITH (NOLOCK)
                ON SL.Id = CL.LocationTypeId
                AND SL.Code IN {$locationFilter}
        ";

            $bindings = [];
            if ($includeCountry === 1 && !empty($countryCode)) {
                $query .= " INNER JOIN {$db}Cust_Country CC WITH (NOLOCK)
                    ON CC.Id = CL.CountryId
                    AND (CC.Code = ? OR CC.Name = ?) ";
                $bindings[] = strtoupper(trim($countryCode));
                $bindings[] = trim($countryCode);
            }

            $query .= " WHERE CL.IsActive = 1 AND CL.IsDeleted = 0 ";
            if (!empty($filter)) {
                $query .= " AND (CL.Name LIKE ? OR CL.Code LIKE ? OR CL.UNCode LIKE ?) ";
                $bindings[] = $filter . '%';
                $bindings[] = $filter . '%';
                $bindings[] = $filter . '%';
            }

            $query .= " ORDER BY CL.Name ";

            Log::info('getLocations - query', [
                'sql' => $query,
                'bindings' => $bindings,
            ]);

            $results = DB::select($query, $bindings);

            return response()->json([
                'success' => true,
                'locations' => $results,
            ]);
        } catch (\Exception $e) {

            Log::error('getLocations failed', ['error' => $e->getMessage()]);

            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function getAgentTypes(Request $request)
    {
        try {
            $filter = $request->input('filter', '');
            $agentTypes = DB::connection('sqlsrv')
                ->select('EXEC dbo.sp_ClientAgentList @Filter = ?', [$filter]);

            return response()->json([
                'success' => true,
                'data' => $agentTypes,

            ]);
        } catch (\Exception $e) {
            Log::error('getAgentTypes - Failed', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to load agent types: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function getCustomerTypes()
    {
        try {
            $results = DB::select('EXEC sp_Quote_GetCustomerType');
            return response()->json(['success' => true, 'data' => $results]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'data' => [], 'message' => $e->getMessage()]);
        }
    }


    public function saveCustomerDetails(Request $request)
    {
        try {
            $inputJson = $request->input('input_json');

            if (!$inputJson) {
                return response()->json([
                    'success' => false,
                    'message' => 'input_json is required'
                ], 400);
            }

            // Log what we're sending
            \Log::info('saveCustomerDetails - Sending JSON', ['json' => $inputJson]);

            $pdo = DB::connection('sqlsrv')->getPdo();
            $stmt = $pdo->prepare("SET NOCOUNT ON; DECLARE @OutputRemarkJson NVARCHAR(2000); EXEC dbo.sp_Quote_CustomerUpdate ?, @OutputRemarkJson OUTPUT; SELECT @OutputRemarkJson AS OutputRemarkJson");
            $stmt->execute([$inputJson]);

            $output = null;
            do {
                try {
                    $rows = $stmt->fetchAll(\PDO::FETCH_OBJ);
                    if (!empty($rows) && isset($rows[0]->OutputRemarkJson)) {
                        $output = $rows[0]->OutputRemarkJson;
                        break;
                    }
                } catch (\PDOException $e) {
                    continue;
                }
            } while ($stmt->nextRowset());

            if (!$output) {
                return response()->json([
                    'success' => false,
                    'message' => 'No response from stored procedure'
                ], 500);
            }

            $result = json_decode($output, true);

            if ($result && isset($result['Status']) && $result['Status'] === 'Success') {
                // Patch columns the SP omits from its UPDATE
                $decoded = json_decode($request->input('input_json'), true);
                $patch = [];

                if (!empty($decoded['Industry'])) {
                    $patch['industryname'] = $decoded['Industry'];
                }

                if (!empty($patch)) {
                    DB::table('Quote_QuotationInfo')
                        ->where('ID', $decoded['QuotationID'])
                        ->update($patch);
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Customer details saved successfully',
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => $result['Remarks'][0]['Remark'] ?? 'Failed to save customer details',
            ], 422);
        } catch (\Exception $e) {
            Log::error('saveCustomerDetails - Failed', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Shared PDO helper for sp_Quote_DetailsUpdate.
     * Uses nextRowset() to skip intermediate result sets produced by
     * DROP/CREATE TABLE inside the SP — same pattern as saveCharge().
     */
    private function executeCargoUpdateSP(string $inputJson): array
    {
        if (strlen($inputJson) > 2000) {
            Log::error('executeCargoUpdateSP - payload exceeds SP limit', [
                'length' => strlen($inputJson),
                'preview' => substr($inputJson, 0, 300),
            ]);
            return [
                'Status' => 'Failed',
                'Remark' => '[PHP] Input payload too large (' . strlen($inputJson) . ' chars). SP limit is 2000.',
            ];
        }

        $pdo = DB::connection('sqlsrv')->getPdo();
        $stmt = $pdo->prepare("
            SET NOCOUNT ON;
            DECLARE @OutputJson VARCHAR(2000);
            EXEC FITSYSUAT.dbo.sp_Quote_CargoDetailsUpdate ?, @OutputRemarkJson = @OutputJson OUTPUT;
            SELECT @OutputJson AS output
        ");
        $stmt->execute([$inputJson]);

        do {
            try {
                $rows = $stmt->fetchAll(\PDO::FETCH_OBJ);
                if (!empty($rows) && isset($rows[0]->output)) {
                    $raw = trim($rows[0]->output);
                    $toDecode = str_starts_with($raw, '[') ? $raw : '[' . $raw . ']';
                    $decoded = json_decode($toDecode, true);

                    if (json_last_error() !== JSON_ERROR_NONE) {
                        Log::error('executeCargoUpdateSP - JSON decode failed', [
                            'raw' => $raw,
                            'input' => $inputJson,
                        ]);
                        return ['Status' => 'Failed', 'Remark' => '[SP] Response could not be parsed'];
                    }

                    $failures = array_filter($decoded, fn($r) => ($r['Status'] ?? '') === 'Failed');

                    if (!empty($failures)) {
                        $remarks = implode(', ', array_column($failures, 'Remark'));
                        return [
                            'Status' => 'Failed',
                            'Remark' => $remarks,
                            'Remarks' => array_values($failures),
                        ];
                    }

                    return $decoded[0] ?? ['Status' => 'Success'];
                }
            } catch (\PDOException $e) {
                // skip intermediate result sets
            }
        } while ($stmt->nextRowset());

        Log::error('executeCargoUpdateSP - no output returned', ['input' => $inputJson]);
        return [
            'Status' => 'Failed',
            'Remark' => '[SP] No response returned from stored procedure',
        ];
    }

    /**
     * Shared PDO helper for sp_Quote_DetailsUpdate.
     * Used by: saveShipmentDetails, saveInquiryDetails, createBooking, runBookingInfoUpdate.
     *
     * The SP wraps results in a JSON array — e.g:
     *   [{"Status":"Success","Remark":"..."}]
     *
     * Returns the first element as an associative array.
     */
    private function executeDetailsUpdateSP(string $inputJson): array
    {
        if (strlen($inputJson) > 2000) {
            Log::error('executeDetailsUpdateSP - payload exceeds SP limit', [
                'length' => strlen($inputJson),
                'preview' => substr($inputJson, 0, 300),
            ]);
            return [
                'Status' => 'Failed',
                'Remark' => '[PHP] Input payload too large (' . strlen($inputJson) . ' chars). SP limit is 2000.',
            ];
        }

        $pdo = DB::connection('sqlsrv')->getPdo();
        $stmt = $pdo->prepare("
        SET NOCOUNT ON;
        DECLARE @OutputJson VARCHAR(2000);
        EXEC dbo.sp_Quote_DetailsUpdate ?, @OutputJson = @OutputJson OUTPUT;
        SELECT @OutputJson AS output
            ");
        $stmt->execute([$inputJson]);

        do {
            try {
                $rows = $stmt->fetchAll(\PDO::FETCH_OBJ);

                if (empty($rows)) {
                    continue;
                }

                if (isset($rows[0]->output)) {
                    $raw = trim($rows[0]->output);

                    // SP may return a bare object or an array — normalize to array
                    $toDecode = str_starts_with($raw, '[') ? $raw : '[' . $raw . ']';
                    $decoded = json_decode($toDecode, true);

                    if (json_last_error() !== JSON_ERROR_NONE) {
                        Log::error('executeDetailsUpdateSP - JSON decode failed', [
                            'raw' => $raw,
                            'input' => $inputJson,
                        ]);
                        return ['Status' => 'Failed', 'Remark' => '[SP] Response could not be parsed'];
                    }

                    // Check for any failure across all returned status objects
                    $failures = array_filter($decoded, fn($r) => ($r['Status'] ?? '') === 'Failed');

                    if (!empty($failures)) {
                        $remarks = implode(', ', array_column($failures, 'Remark'));
                        return [
                            'Status' => 'Failed',
                            'Remark' => $remarks,
                            'Remarks' => array_values($failures),
                        ];
                    }

                    return $decoded[0] ?? ['Status' => 'Success'];
                }
            } catch (\PDOException $e) {
                // Skip intermediate result sets from temp table drops inside SP
                Log::debug('executeDetailsUpdateSP - Skipping result set', [
                    'error' => $e->getMessage(),
                ]);
                continue;
            }
        } while ($stmt->nextRowset());

        Log::error('executeDetailsUpdateSP - no output returned', ['input' => $inputJson]);

        return [
            'Status' => 'Failed',
            'Remark' => '[SP] No response returned from stored procedure',
        ];
    }

    public function downloadPdf($id, $version)
    {
        try {
            $quotation = QuotationInfo::where('ID', $id)->first(['ID', 'bookingno']);

            if (!$quotation) {

                abort(404, 'Quotation not found');
            }

            $document = DB::table('Quote_Quotation_Document')
                ->where('QuotationInfoID', $quotation->ID)
                ->where('DocumentType', 'QuotationPDF') // FIX: was 'Quotation'
                ->where('IsDeleted', 0)
                ->where('IsVisible', 1)
                ->orderBy('CreatedOn', 'asc')
                ->skip($version - 1)
                ->take(1)
                ->first(['Name', 'FilePath']);
            if (!$document) {
                abort(404, 'Version ' . $version . ' not found');
            }

            $relativePath = $this->normalizeFilePath($document->FilePath);
            Log::info('downloadPdf - Path diagnostic', [
                'raw_filepath' => $document->FilePath,
                'relative_path' => $relativePath,
                'storage_root' => \Storage::disk('public')->path(''),
                'full_resolved' => \Storage::disk('public')->path($relativePath),
                'exists' => \Storage::disk('public')->exists($relativePath),
                'file_exists_raw' => file_exists($document->FilePath),
                'file_exists_full' => file_exists(\Storage::disk('public')->path($relativePath)),

            ]);

            if (!\Storage::disk('public')->exists($relativePath)) {
                Log::error('downloadPdf - File not found on disk', [
                    'id' => $id,
                    'version' => $version,
                    'raw_filepath' => $document->FilePath,
                    'relative_path' => $relativePath,
                ]);
                abort(404, 'File not found on disk');
            }

            Log::info('downloadPdf - Downloaded', [
                'id' => $id,
                'booking_no' => $quotation->bookingno,
                'version' => $version,
                'file' => $document->Name,
                'user' => Auth::user()->username ?? Auth::user()->email ?? 'system'

            ]);

            return \Storage::disk('public')->download($relativePath, $document->Name);
        } catch (\Exception $e) {
            Log::error('downloadPdf - Failed', [
                'id' => $id,
                'version' => $version,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            abort(500, 'Download failed: ' . $e->getMessage());
        }
    }

    /**
     * Normalize a FilePath that may be an absolute Windows path or already relative.
     * generatePdf saves the full path (e.g. C:\inetpub\...\storage\app/public\quotations/...)
     * Storage::disk('public') expects just the relative path (e.g. quotations/5238/...)
     */

    private function normalizeFilePath(string $filePath): string
    {
        // Normalize all slashes to forward
        $normalized = str_replace('\\', '/', $filePath);

        // Try to extract relative path after 'storage/app/public/'
        $marker = 'storage/app/public/';
        $pos = strpos($normalized, $marker);

        if ($pos !== false) {
            return substr($normalized, $pos + strlen($marker));
        }

        // Fallback: try 'public/' in case path was already partially normalized
        $marker2 = '/public/';
        $pos2 = strrpos($normalized, $marker2); // strrpos = last occurrence

        if ($pos2 !== false) {
            return substr($normalized, $pos2 + strlen($marker2));
        }
        return ltrim($normalized, '/');
    }

    /**
     * Process a won quotation through the full booking pipeline.
     * Step 0: Save booking info (ServiceTemplate, BillToParty, Forwarder, PoR, PoD)
     * Step 1: Client Creation   → sp_Quote_BookingProcessMain 'Client'
     * Step 2: Booking XML       → sp_Quote_BookingProcessMain 'XML'
     *         ↳ After success: verify SCMBookingID was populated (required by Steps 3 & 4)
     * Step 3: Charge Transfer   → sp_Quote_BookingProcessMain 'Charge'
     * Step 4: Document Transfer → sp_Quote_BookingProcessMain 'Document'
     *
     * Route: POST /quotation/process-booking
     */
    public function processBooking(Request $request)
    {
        $request->validate([
            'id' => 'required|string',
            'booking_no' => 'required|string',
            'service_template' => 'required|string',
            'bill_to_party' => 'required|string',
            'forwarder' => 'required|string',
            'place_of_receipt' => 'required|string',
            'place_of_delivery' => 'required|string',
        ]);

        $submittedBy = Auth::user()->displayName ?? Auth::user()->Email ?? 'system';

        $quotation = DB::table('Quote_QuotationInfo')
            ->where('ID', $request->id)
            ->first(['ID', 'QuoteNo', 'bookingno', 'QuoteStatus', 'teamid', 'OfficeCode']);

        if (!$quotation) {
            Log::error('processBooking - Quotation not found', [
                'id' => $request->id,
                'user' => $submittedBy,
            ]);
            return response()->json(['success' => false, 'message' => 'Quotation not found'], 404);
        }

        Log::info('processBooking - ID debug', [
            'request_id' => $request->id,
            'db_id' => $quotation->ID,
            'json_encoded' => json_encode(['QuotationID' => $quotation->ID]),
        ]);

        $quoteNo = $quotation->QuoteNo;

        if (empty($quoteNo)) {
            return response()->json([
                'success' => false,
                'message' => 'QuoteNo is not set on this quotation. Cannot proceed with booking process.',
            ], 422);
        }

        Log::info('processBooking - Starting', [
            'id' => $request->id,
            'booking_no' => $request->booking_no,
            'quote_no' => $quoteNo,
            'user' => $submittedBy,
        ]);

        $steps = [];

        // ── Step 0: Save Booking Info ─────────────────────────────────────────────
        $step0Result = $this->runBookingInfoUpdate(
            $quotation->ID,
            $request->service_template,
            $request->bill_to_party,
            $request->forwarder,
            (int) $request->input('include_country', 0),
            $request->place_of_receipt,
            $request->place_of_delivery,
            $submittedBy
        );

        $steps[] = [
            'step' => 0,
            'label' => 'Save Booking Info',
            'status' => $step0Result['status'],
            'remark' => $step0Result['remark'],
        ];

        if ($step0Result['status'] !== 'Success') {
            $this->markAsFailed($request->id, 'Save Booking Info', $step0Result['remark'], $submittedBy);

            return response()->json([
                'success' => false,
                'failed_step' => 0,
                'failed_label' => 'Save Booking Info',
                'steps' => $steps,
            ]);
        }

        // ── Steps 1–4: sp_Quote_BookingProcessMain ────────────────────────────────
        $processSteps = [
            1 => ['type' => 'Client', 'label' => 'Client Creation'],
            2 => ['type' => 'XML', 'label' => 'Booking XML'],
            3 => ['type' => 'Charge', 'label' => 'Charge Transfer'],
            4 => ['type' => 'Document', 'label' => 'Document Transfer'],
        ];

        foreach ($processSteps as $stepNo => $stepDef) {
            $result = $this->runBookingProcessMain($quotation->ID, $stepDef['type']);

            $steps[] = [
                'step' => $stepNo,
                'label' => $stepDef['label'],
                'status' => $result['status'],
                'remark' => $result['remark'],
            ];

            Log::info('processBooking - Step result', [
                'quote_no' => $quoteNo,
                'step' => $stepNo,
                'type' => $stepDef['type'],
                'status' => $result['status'],
                'remark' => $result['remark'],
            ]);

            if ($result['status'] !== 'Success') {
                $this->markAsFailed(
                    $request->id,
                    $stepDef['label'],
                    $result['remark'],
                    $submittedBy
                );

                foreach ($processSteps as $remainingNo => $remainingDef) {
                    if ($remainingNo > $stepNo) {
                        $steps[] = [
                            'step' => $remainingNo,
                            'label' => $remainingDef['label'],
                            'status' => 'Skipped',
                            'remark' => 'Skipped due to previous step failure',
                        ];
                    }
                }

                return response()->json([
                    'success' => false,
                    'failed_step' => $stepNo,
                    'failed_label' => $stepDef['label'],
                    'steps' => $steps,
                ]);
            }

            // ── After Step 2: Push XML to SCM ─────────────────────────────────────
            if ($stepNo === 2) {
                Log::info('processBooking - XML saved, pushing to SCM', [
                    'quote_no' => $quoteNo,
                ]);

                $pushRequest = new \Illuminate\Http\Request();
                $pushRequest->merge(['quote_no' => $quoteNo]);

                $pushResponse = json_decode(
                    $this->pushBookingToSCM($pushRequest)->getContent(),
                    true
                );

                Log::info('processBooking - pushBookingToSCM result', [
                    'quote_no' => $quoteNo,
                    'result' => $pushResponse,
                ]);

                if (!($pushResponse['success'] ?? false)) {
                    $this->markAsFailed(
                        $request->id,
                        'SCM Push',
                        $pushResponse['message'] ?? 'Failed to push XML to SCM',
                        $submittedBy
                    );

                    foreach ($processSteps as $remainingNo => $remainingDef) {
                        if ($remainingNo > $stepNo) {
                            $steps[] = [
                                'step' => $remainingNo,
                                'label' => $remainingDef['label'],
                                'status' => 'Skipped',
                                'remark' => 'Skipped due to SCM push failure',
                            ];
                        }
                    }

                    return response()->json([
                        'success' => false,
                        'failed_step' => $stepNo,
                        'failed_label' => 'SCM Push',
                        'steps' => $steps,
                        'message' => $pushResponse['message'] ?? 'Failed to push XML to SCM',
                    ]);
                }

                // Add SCM push as a step in the response
                $steps[] = [
                    'step' => '2b',
                    'label' => 'SCM Push',
                    'status' => 'Success',
                    'remark' => $pushResponse['message'] ?? 'Pushed to SCM successfully',
                ];

                Log::info('processBooking - SCM Push successful', [
                    'quote_no' => $quoteNo,
                    'scm_booking_id' => $pushResponse['scm_booking_id'] ?? 'NULL',
                    'scm_booking_no' => $pushResponse['scm_booking_no'] ?? 'NULL',
                ]);
            }
        }

        // ── All steps passed: mark SCMINTStatus = 'Success' ──────────────────────
        DB::table('Quote_QuotationInfo')
            ->where('ID', $request->id)
            ->update([
                'SCMINTStatus' => 'Success',
                'LastModifiedBy' => $submittedBy,
                'updatedon' => now(),
            ]);

        Log::info('processBooking - All steps completed successfully', [
            'quote_no' => $quoteNo,
            'user' => $submittedBy,
        ]);

        $saved = DB::table('Quote_QuotationInfo')
            ->where('ID', $request->id)
            ->first(['SCMBookingID', 'SCMBookingNo']);

        return response()->json([
            'success' => true,
            'message' => 'Booking process completed successfully',
            'steps' => $steps,
            'scm_booking_no' => $saved->SCMBookingNo ?? null,
            'scm_booking_id' => $saved->SCMBookingID ?? null,
        ]);
    }



    /**
     * Writes SCMINTStatus = 'Failed' and appends the step + error message to
     * QuoteStatusDescription so the row appears in the Failed Int. card and the
     * Remarks column shows the specific failure reason.
     *
     * QuoteStatusDescription stores concatenated JSON objects:
     *   {"Remark":"msg1"},{"Remark":"msg2"}
     * so we append a new object in the same format.
     */
    private function markAsFailed(string $quotationId, string $stepLabel, string $remark, string $submittedBy): void
    {
        try {
            $existing = DB::table('Quote_QuotationInfo')
                ->where('ID', $quotationId)
                ->value('QuoteStatusDescription');

            $newEntry = json_encode(['Remark' => $stepLabel . ': ' . $remark]);
            $description = $existing ? $existing . ',' . $newEntry : $newEntry;

            DB::table('Quote_QuotationInfo')
                ->where('ID', $quotationId)
                ->update([
                    'SCMINTStatus' => 'Failed',
                    'QuoteStatusDescription' => $description,
                    'LastModifiedBy' => $submittedBy,
                    'updatedon' => now(),
                ]);

            Log::info('markAsFailed - Updated record', [
                'quotation_id' => $quotationId,
                'step_label' => $stepLabel,
                'remark' => $remark,
            ]);
        } catch (\Exception $e) {
            Log::error('markAsFailed - Could not update record', [
                'quotation_id' => $quotationId,
                'error' => $e->getMessage(),
            ]);
        }
    }


    /**
     * Execute sp_Quote_BookingInfoUpdate (Step 0).
     * Saves ServiceTemplate, BillToParty, Forwarder, PlaceOfReceipt, PlaceOfDelivery
     * onto the quotation record before the SCM process steps run.
     */
    private function runBookingInfoUpdate(
        string $quotationId,
        string $serviceTemplate,
        string $billToParty,
        string $forwarder,
        int $includeCountry,
        string $placeOfReceipt,
        string $placeOfDelivery,
        string $submittedBy
    ): array {
        try {
            $submittedBy = substr($submittedBy, 0, 25);

            $inputJson = json_encode([
                'OperationType' => 'BookingInfoUpdate',
                'SubmittedDatetime' => now()->format('YmdHis'),
                'SubmittedBy' => $submittedBy,
                'Action' => 'Update',
                'QuotationID' => $quotationId,
                'ServiceTemplate' => $serviceTemplate,
                'BillToParty' => $billToParty,
                'Forwarder' => $forwarder,
                'CountryIncluded' => $includeCountry,
                'PlaceOfReceipt' => $placeOfReceipt,
                'PlaceOfDelivery' => $placeOfDelivery,
            ]);

            // Log::info('runBookingInfoUpdate - Input', [
            //     'quote_no'   => $quoteNo,
            //     'input_json' => $inputJson,
            // ]);

            $output = $this->executeDetailsUpdateSP($inputJson);

            // Log::info('runBookingInfoUpdate - Output', [
            //     'quote_no' => $quoteNo,
            //     'output'   => $output,
            // ]);

            return [
                'status' => $output['Status'] ?? 'Failed',
                'remark' => $output['Remark'] ?? '[SP] No remark returned',
            ];
        } catch (\Exception $e) {
            // Log::error('runBookingInfoUpdate - Exception', [
            //     'quote_no' => $quoteNo,
            //     'error'    => $e->getMessage(),
            //     'file'     => $e->getFile(),
            //     'line'     => $e->getLine(),
            // ]);
            return ['status' => 'Failed', 'remark' => $e->getMessage()];
        }
    }

    /**
     * Execute sp_Quote_BookingProcessMain for a single process type.
     * Returns ['status' => 'Success'|'Failed', 'remark' => '...']
     */
    private function runBookingProcessMain(string $quotationId, string $processType): array
    {
        try {
            $pdo = DB::connection()->getPdo();
            $sql = "
                SET NOCOUNT ON;
                DECLARE @Status VARCHAR(20), @Remark VARCHAR(200);
                EXEC dbo.sp_Quote_BookingProcessMain ?, ?,
                    @Status = @Status OUTPUT,
                    @Remark = @Remark OUTPUT;
                SELECT @Status AS Status, @Remark AS Remark;
            ";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([$quotationId, $processType]);

            $result = null;
            $setIndex = 0;
            $setsLogged = [];

            do {
                $setIndex++;
                try {
                    $rows = $stmt->fetchAll(\PDO::FETCH_OBJ);
                    if (empty($rows))
                        continue;

                    $cols = array_keys((array) $rows[0]);
                    $firstVal = array_values((array) $rows[0])[0] ?? '';

                    $setsLogged[] = [
                        'set' => $setIndex,
                        'columns' => $cols,
                        'first_row' => (array) $rows[0],
                    ];

                    // Detect SP-level error result sets
                    if (count($cols) === 1 && is_string($firstVal)) {
                        $trimmed = trim($firstVal);
                        if (
                            stripos($trimmed, 'Error') === 0 ||
                            stripos($trimmed, 'Failed') === 0 ||
                            stripos($trimmed, 'There is already') !== false
                        ) {
                            // Log::error('runBookingProcessMain - SP emitted error result set', [
                            //     'quote_no'     => $quoteNo,
                            //     'process_type' => $processType,
                            //     'sp_error'     => $trimmed,
                            // ]);

                            return [
                                'status' => 'Failed',
                                'remark' => $trimmed,
                                'result_sets' => $setsLogged,
                            ];
                        }
                    }

                    // Accept the final OUTPUT SELECT
                    if ($cols === ['Status', 'Remark']) {
                        $result = $rows[0];
                        break;
                    }
                } catch (\PDOException $e) {
                    $setsLogged[] = [
                        'set' => $setIndex,
                        'fetch_error' => $e->getMessage(),
                        'sql_state' => $e->errorInfo[0] ?? null,
                        'sql_code' => $e->errorInfo[1] ?? null,
                    ];
                    continue;
                }
            } while ($stmt->nextRowset());

            if (!$result) {
                return [
                    'status' => 'Failed',
                    'remark' => 'No Status/Remark output returned from SP',
                    'result_sets' => $setsLogged,
                ];
            }

            // Final guard: @Status says Success but an error result set was emitted earlier
            $errorSet = collect($setsLogged)->first(function ($s) {
                $val = $s['first_row'] ? array_values($s['first_row'])[0] ?? '' : '';
                return count($s['columns'] ?? []) === 1
                    && is_string($val)
                    && (
                        stripos($val, 'Error') === 0 ||
                        stripos($val, 'Failed') === 0 ||
                        stripos($val, 'There is already') !== false
                    );
            });

            if ($errorSet) {
                $spError = array_values($errorSet['first_row'])[0];
                // Log::error('runBookingProcessMain - @Status=Success but error set detected', [
                //     'quote_no'     => $quoteNo,
                //     'process_type' => $processType,
                //     'sp_error'     => $spError,
                // ]);

                return [
                    'status' => 'Failed',
                    'remark' => $spError,
                    'result_sets' => $setsLogged,
                ];
            }

            return [
                'status' => $result->Status ?? 'Failed',
                'remark' => $result->Remark ?? '',
                'result_sets' => $setsLogged,
            ];
        } catch (\PDOException $e) {
            return [
                'status' => 'Failed',
                'remark' => $e->getMessage(),
                'sql_error' => $e->errorInfo[1] ?? null,
                'sql_state' => $e->errorInfo[0] ?? null,
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'Failed',
                'remark' => $e->getMessage(),
            ];
        }
    }

    // ============================================
    // SHIPMENT MODE DROPDOWN
    // sp_ShipmentMode returns: Id, Code
    // ============================================
    public function getShipmentModes(Request $request)
    {
        $filter = $request->input('filter', '');
        try {
            $pdo = DB::connection()->getPdo();
            $stmt = $pdo->prepare("SET NOCOUNT ON; EXEC dbo.sp_ShipmentMode ?");
            $stmt->execute([$filter]);

            $results = [];
            do {
                try {
                    $rows = $stmt->fetchAll(\PDO::FETCH_OBJ);
                    if (empty($rows))
                        continue;
                    $firstVal = array_values((array) $rows[0])[0] ?? '';
                    if (is_string($firstVal) && str_starts_with($firstVal, 'Failed'))
                        continue;
                    if (isset($rows[0]->Id) && isset($rows[0]->Code)) {
                        $results = $rows;
                        break;
                    }
                } catch (\PDOException $e) {
                    continue;
                }
            } while ($stmt->nextRowset());

            return response()->json(['success' => true, 'data' => $results]);
        } catch (\Exception $e) {
            Log::error('getShipmentModes - Failed', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ============================================
    // TRANSPORT MODE DROPDOWN
    // sp_TransportMode returns: Id, Code, Name
    // ============================================
    public function getTransportModes(Request $request)
    {
        $filter = $request->input('filter', '');
        try {
            $pdo = DB::connection()->getPdo();
            $stmt = $pdo->prepare("SET NOCOUNT ON; EXEC dbo.sp_TransportMode ?");
            $stmt->execute([$filter]);

            $results = [];
            do {
                try {
                    $rows = $stmt->fetchAll(\PDO::FETCH_OBJ);
                    if (empty($rows))
                        continue;
                    $firstVal = array_values((array) $rows[0])[0] ?? '';
                    if (is_string($firstVal) && str_starts_with($firstVal, 'Failed'))
                        continue;
                    if (isset($rows[0]->Id) && isset($rows[0]->Code)) {
                        $results = $rows;
                        break;
                    }
                } catch (\PDOException $e) {
                    continue;
                }
            } while ($stmt->nextRowset());

            return response()->json(['success' => true, 'data' => $results]);
        } catch (\Exception $e) {
            Log::error('getTransportModes - Failed', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function saveShipmentDetails(Request $request)
    {
        $request->validate([
            'quotation_id' => 'required|string',
            'shipment_mode' => 'required|string',
            'transport_mode' => 'required|string',
        ]);

        $submittedBy = auth()->user()->displayName
            ?? auth()->user()->Email
            ?? 'system';

        $input = json_encode([
            'OperationType' => 'ShipmentDetails',
            'SubmittedDatetime' => now()->format('YmdHis'),
            'SubmittedBy' => $submittedBy,
            'Action' => 'Update',
            'QuotationID' => $request->quotation_id,
            'CountryOfOriginCode' => $request->origincountry ?? '',
            'CountryOfDestinationCode' => $request->destinationcountry ?? '',
            'IncoTermCode' => $request->incoterm ?? '',
            'ShipmentModeCode' => $request->shipment_mode,
            'TransportModeCode' => $request->transport_mode,
            'ServiceClassCode' => $request->service_class ?? '',
        ]);

        if (strlen($input) > 2000) {
            return response()->json([
                'success' => false,
                'message' => 'Request payload too large for SP. Max 2000 characters.',
            ], 400);
        }

        try {
            $result = $this->executeDetailsUpdateSP($input);

            if (($result['Status'] ?? '') === 'Failed') {
                $message = $result['Remark']
                    ?? implode(', ', array_column($result['Remarks'] ?? [], 'Remark'))
                    ?: 'Shipment update failed';

                return response()->json(['success' => false, 'message' => $message]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Shipment details saved successfully',
            ]);
        } catch (\Exception $e) {
            Log::error('saveShipmentDetails - Exception', [
                'quotation_id' => $request->quotation_id,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function getEquipmentTypes(Request $request)
    {
        $filter = $request->input('filter', '');
        try {
            $pdo = DB::connection('sqlsrv')->getPdo();
            $stmt = $pdo->prepare("SET NOCOUNT ON; EXEC dbo.sp_EquipmentType ?");
            $stmt->execute([$filter]);

            $results = [];
            do {
                try {
                    $rows = $stmt->fetchAll(\PDO::FETCH_OBJ);
                    if (empty($rows))
                        continue;
                    $firstVal = array_values((array) $rows[0])[0] ?? '';
                    if (is_string($firstVal) && str_starts_with($firstVal, 'Failed'))
                        continue;
                    if (isset($rows[0]->Code)) {
                        $results = $rows;
                        break;
                    }
                } catch (\PDOException $e) {
                    continue;
                }
            } while ($stmt->nextRowset());

            return response()->json(['success' => true, 'data' => $results]);
        } catch (\Exception $e) {
            Log::error('getEquipmentTypes - Failed', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function getChargeBasedOnDefaults(Request $request)
    {
        $bookingNo = $request->input('booking_no');
        $basedOn = $request->input('based_on');
        $equipment = $request->input('equipment', '');
        $quoteNo = trim($request->input('quote_no', ''));  // ← add this


        // REPLACE WITH:
        if (!$bookingNo || !$basedOn) {
            return response()->json(['success' => false, 'message' => 'booking_no and based_on are required'], 400);
        }

        // Use the UUID passed directly from the frontend (quote_no = quotationId in JS).
        // Falling back to bookingno lookup is unsafe — multiple quotations can share
        // the same bookingno integer and ->first() returns an unpredictable row.
        $resolvedId = $quoteNo ?: null;

        if (!$resolvedId) {
            $quotation = DB::table('Quote_QuotationInfo')
                ->where('bookingno', $bookingNo)
                ->first(['ID']);

            if (!$quotation || empty($quotation->ID)) {
                Log::warning('getChargeBasedOnDefaults - QuotationID not found', ['booking_no' => $bookingNo]);
                return response()->json(['success' => false, 'message' => 'Quotation not found for this booking'], 404);
            }

            $resolvedId = $quotation->ID;
        }

        // ── Strip trailing comma from containerlist before SP reads it ──
        DB::table('Quote_QuotationInfo')
            ->where('ID', $resolvedId)
            ->whereRaw("RIGHT(RTRIM(containerlist), 1) = ','")
            ->update([
                'containerlist' => DB::raw("LEFT(RTRIM(containerlist), LEN(RTRIM(containerlist)) - 1)")
            ]);

        Log::info('getChargeBasedOnDefaults - Calling SP', [
            'booking_no' => $bookingNo,
            'quote_no' => $quoteNo,
            'based_on' => $basedOn,
            'equipment' => $equipment ?: 'N/A',
        ]);

        try {
            $pdo = DB::getPdo();
            $stmt = $pdo->prepare("
            DECLARE @BasedOnUOMCode VARCHAR(50),
                    @BasedOnUOM     VARCHAR(100),
                    @Quantity       DECIMAL(18,3);

            EXEC dbo.sp_Quote_ChargeBasedOnDefaultDetails ?, ?, ?,
                @BasedOnUOMCode = @BasedOnUOMCode OUTPUT,
                @BasedOnUOM     = @BasedOnUOM     OUTPUT,
                @Quantity       = @Quantity       OUTPUT;

            SELECT @BasedOnUOMCode AS BasedOnUOMCode,
                @BasedOnUOM     AS BasedOnUOM,
                @Quantity       AS Quantity;
        ");

            $stmt->execute([$resolvedId, $basedOn, $equipment]);

            $result = null;
            do {
                try {
                    $rows = $stmt->fetchAll(\PDO::FETCH_OBJ);
                    if (empty($rows))
                        continue;

                    $firstVal = array_values((array) $rows[0])[0] ?? '';

                    // SP CATCH block returns 'Failed - ...' as a result row
                    if (is_string($firstVal) && str_starts_with(trim($firstVal), 'Failed')) {
                        Log::error('getChargeBasedOnDefaults - SP returned failure', [
                            'booking_no' => $bookingNo,
                            'quote_no' => $quoteNo,
                            'message' => $firstVal,
                        ]);
                        return response()->json(['success' => false, 'message' => $firstVal], 422);
                    }

                    if (isset($rows[0]->BasedOnUOMCode)) {
                        $result = $rows[0];
                        break;
                    }
                } catch (\PDOException $e) {
                    continue;
                }
            } while ($stmt->nextRowset());

            if (!$result) {
                Log::warning('getChargeBasedOnDefaults - SP returned no output', [
                    'booking_no' => $bookingNo,
                    'quote_no' => $quoteNo,
                    'based_on' => $basedOn,
                ]);
                return response()->json(['success' => false, 'message' => 'No defaults returned from SP'], 500);
            }

            Log::info('getChargeBasedOnDefaults - Success', [
                'booking_no' => $bookingNo,
                'quote_no' => $quoteNo,
                'based_on' => $basedOn,
                'based_on_uom' => $result->BasedOnUOM,
                'quantity' => $result->Quantity,
            ]);

            return response()->json([
                'success' => true,
                'based_on_uom_code' => trim($result->BasedOnUOMCode ?? ''),
                'based_on_uom' => trim($result->BasedOnUOM ?? ''),
                'quantity' => (float) ($result->Quantity ?? 0),
            ]);
        } catch (\Exception $e) {
            Log::error('getChargeBasedOnDefaults - Exception', [
                'booking_no' => $bookingNo,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ============================================
    // GET CONTAINERS
    // Loads from Quote_Quotation_EquipmentDetails for a given QuoteNo.
    // Used to populate the container list table on page load.
    // ============================================
    public function getContainers(Request $request)
    {
        $quotationId = $request->input('quotation_id', '');

        if (!$quotationId) {
            return response()->json(['success' => false, 'message' => 'Quotation ID required']);
        }

        try {
            $containers = DB::connection('sqlsrv')
                ->table('Quote_Quotation_EquipmentDetails as QE')
                ->where('QE.QuotationInfoID', $quotationId)
                ->where('QE.IsActive', 1)
                ->where('QE.IsDeleted', 0)
                ->select('QE.EquipmentType', 'QE.Quantity')
                ->get();

            return response()->json(['success' => true, 'containers' => $containers]);
        } catch (\Exception $e) {
            Log::error('getContainers - Failed', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ============================================
    // SAVE CARGO DETAILS
    // Calls sp_Quote_CargoDetailsUpdate via OUTPUT parameter pattern.
    // Containers array uses "Qunatity" (SP typo — must match exactly).
    // ============================================
    public function saveCargoDetails(Request $request)
    {
        $request->validate([
            'quotation_id' => 'required|string',
            'commodity' => 'nullable|string',
            'cargo_volume' => 'nullable|numeric|min:0',
            'containers' => 'nullable|string',
        ]);

        $submittedBy = substr(auth()->user()->displayName ?? auth()->user()->Email ?? 'system', 0, 25);

        // Mirror saveInquiryDetails — verify QuoteNo exists in DB before calling SP.
        // This ensures the SP receives the exact string stored in the table,
        // eliminating any casing or whitespace mismatch from the request.
        $quoteNo = DB::connection('sqlsrv')
            ->table('Quote_QuotationInfo')
            ->where('ID', $request->quotation_id)
            ->value('QuoteNo');

        if (!$quoteNo) {
            return response()->json([
                'success' => false,
                'message' => 'Quotation not found'
            ], 404);
        }

        $scopeVolume = $request->input('scope_volume', '');
        $commodity = $request->input('commodity', '');

        $directUpdate = [];
        if (!empty($scopeVolume)) {
            $directUpdate['scopevolume'] = $scopeVolume;
        }
        if (!empty($commodity)) {
            $directUpdate['commodity'] = $commodity;
        }

        if (!empty($directUpdate)) {
            $directUpdate['updatedon'] = now();
            $directUpdate['LastModifiedBy'] = substr($submittedBy, 0, 25);
            DB::connection('sqlsrv')
                ->table('Quote_QuotationInfo')
                ->where('ID', $request->quotation_id)
                ->update($directUpdate);

            Log::info('saveCargoDetails - direct column update', [
                'quotation_id' => $request->quotation_id,
                'quote_no' => $quoteNo,
                'fields' => array_keys($directUpdate),
            ]);
        }

        // Build containers array
        $containers = [];
        if ($request->containers) {
            foreach (explode(',', $request->containers) as $segment) {
                $segment = trim($segment);
                $xPos = strrpos($segment, 'X');
                if ($xPos === false)
                    continue;
                $type = trim(substr($segment, 0, $xPos));
                $qty = (int) trim(substr($segment, $xPos + 1));
                if ($type && $qty > 0) {
                    $containers[] = [
                        'ContainerType' => $type,
                        'Quantity' => $qty,
                    ];
                }
            }
        }

        $input = json_encode([
            'OperationType' => 'CargoDetails',
            'SubmittedDatetime' => now()->format('YmdHis'),
            'SubmittedBy' => $submittedBy,
            'Action' => 'Update',
            'QuotationID' => $request->quotation_id,
            'Commodity' => $request->commodity ?? '',
            'CargoVolume' => (float) ($request->cargo_volume ?? 0),
            'Containers' => $containers,
        ]);

        Log::info('saveCargoDetails - payload', [
            'quotation_id' => $request->quotation_id,
            'input_json' => $input,
            'length' => strlen($input),
        ]);

        if (strlen($input) > 2000) {
            return response()->json([
                'success' => false,
                'message' => 'Payload too large for SP. Reduce number of containers.'
            ], 400);
        }

        try {
            $output = $this->executeCargoUpdateSP($input);

            Log::info('saveCargoDetails - SP response', [
                'quote_no' => $quoteNo,
                'output' => $output,
            ]);

            if (($output['Status'] ?? '') === 'Failed') {
                $remarks = collect($output['Remarks'] ?? [])->pluck('Remark')->implode(', ');
                return response()->json([
                    'success' => false,
                    'message' => $remarks ?: ($output['Remark'] ?? 'Cargo update failed')
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Cargo details saved successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('saveCargoDetails error', [
                'quote_no' => $quoteNo,
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getSalesPersons(Request $request)
    {
        $filter = trim($request->input('filter', ''));
        $officeId = trim($request->input('office_id', ''));

        try {
            $query = DB::connection('sqlsrv')
                ->table('FITSCMUAT.dbo.Cust_Resource AS CR')
                ->join('FITSCMUAT.dbo.Cust_ResOfficeResFuncMap AS CRO', 'CRO.ResourceId', '=', 'CR.Id')
                ->join('FITSCMUAT.dbo.Sys_ResourceFunction AS RF', 'RF.Id', '=', 'CRO.ResourceFunctionId')
                ->where('CR.IsActive', 1)
                ->where('CR.IsDeleted', 0)
                ->where('CRO.IsActive', 1)
                ->where('RF.Code', 'SalesMgr')
                ->where(function ($q) use ($filter) {
                    $q->where('CR.Contact_PersonName_FirstName', 'like', "%{$filter}%")
                        ->orWhere('CR.Contact_PersonName_LastName', 'like', "%{$filter}%")
                        ->orWhere('CR.Contact_Email', 'like', "%{$filter}%")
                        ->orWhere('CR.Code', 'like', "%{$filter}%");
                })
                // FIX 1: Add Contact_PersonName_FirstName to the SELECT so DISTINCT
                // allows ORDER BY on it. SQL Server requires every ORDER BY column
                // to appear in the SELECT list when DISTINCT is used.
                ->select([
                    'CR.Code',
                    'CR.Contact_PersonName_FirstName', // ← ADDED: needed by ORDER BY + DISTINCT
                    DB::raw("LTRIM(RTRIM(CR.Contact_PersonName_FirstName))
                    + IIF(
                        CR.Contact_PersonName_LastName IS NULL
                        OR LTRIM(RTRIM(CR.Contact_PersonName_LastName)) IN ('', '-'),
                        '',
                        ' ' + LTRIM(RTRIM(CR.Contact_PersonName_LastName))
                    ) AS FullName"),
                    DB::raw("ISNULL(CR.Contact_Email, '') AS Email"),
                    'CRO.OfficeId',
                ])
                // FIX 2: Keep DISTINCT — it now works because the ORDER BY column
                // (Contact_PersonName_FirstName) is explicitly in the SELECT list above.
                ->distinct()
                ->orderBy('CR.Contact_PersonName_FirstName')
                ->limit(20);

            if ($officeId) {
                $query->where('CRO.OfficeId', $officeId);
            }

            $persons = $query->get();

            Log::info('getSalesPersons success', [
                'filter' => $filter,
                'officeId' => $officeId ?: 'all',
                'count' => $persons->count(),
            ]);

            return response()->json(['success' => true, 'persons' => $persons]);
        } catch (\Throwable $e) {
            Log::error('getSalesPersons failed', [
                'filter' => $filter,
                'officeId' => $officeId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'persons' => [],
                'error' => $e->getMessage(),
            ]);
        }
    }


    public function saveInquiryDetails(Request $request)
    {
        $request->validate([
            'quotation_id' => 'required|string',
            'sales_person_code' => 'required|string',
            'sales_person_email' => 'required|email',
        ]);

        $submittedBy = auth()->user()->displayName
            ?? auth()->user()->Email
            ?? 'system';

        $input = json_encode([
            'OperationType' => 'InquiryDetails',
            'SubmittedDatetime' => now()->format('YmdHis'),
            'SubmittedBy' => $submittedBy,
            'Action' => 'Update',
            'QuotationID' => $request->quotation_id, // ← GUID directly
            'SalesPersonCode' => $request->sales_person_code,
            'SalesPersonEmail' => $request->sales_person_email,
        ]);

        try {
            $output = $this->executeDetailsUpdateSP($input);

            if (($output['Status'] ?? '') === 'Failed') {
                return response()->json([
                    'success' => false,
                    'message' => $output['Remark'] ?? 'Update failed',
                ]);
            }

            return response()->json(['success' => true, 'message' => 'Inquiry details updated']);
        } catch (\Throwable $e) {
            Log::error('saveInquiryDetails error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ============================================================
    // LOOKUP METHODS — used by duplicate modal dropdowns
    // ============================================================
    public function lookupCountries(Request $request)
    {
        try {
            $pdo = DB::connection('sqlsrv')->getPdo();
            $stmt = $pdo->prepare("EXEC sp_CountryList @Filter = ?");
            $stmt->execute([(string) ($request->get('filter') ?? '')]);

            $rows = [];
            do {
                try {
                    $fetched = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                    if (!empty($fetched) && empty($rows)) {
                        $rows = $fetched;
                    }
                } catch (\Exception $e) {
                    // skip unfetchable result sets (temp table drops)
                }
            } while ($stmt->nextRowset());

            if (empty($rows)) {
                Log::warning('lookupCountries — SP returned no rows');
                return response()->json([]);
            }

            // SP confirmed columns: CountryID (UUID = CC.id), CountCode (ISO), CountryName
            $normalised = array_map(function ($row) {
                return [
                    'CountryID' => trim($row['CountryID'] ?? ''), // UUID — sent to SP as CountryCode
                    'CountryCode' => trim($row['CountCode'] ?? ''), // ISO e.g. "KE" — used for pre-selection
                    'CountryName' => trim($row['CountryName'] ?? ''), // display label
                ];
            }, $rows);

            return response()->json($normalised);
        } catch (\Exception $e) {
            Log::error('lookupCountries error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            return response()->json([]);
        }
    }


    public function lookupIncoTerms(Request $request)
    {
        try {
            $pdo = DB::getPdo();
            $stmt = $pdo->prepare("EXEC sp_IncoTerms @Filter = ?");
            $stmt->execute([(string) ($request->get('filter') ?? '')]);
            return response()->json($stmt->fetchAll(\PDO::FETCH_ASSOC));
        } catch (\Exception $e) {
            return response()->json([]);
        }
    }

    public function lookupServiceClasses(Request $request)
    {
        try {
            $pdo = DB::getPdo();
            $stmt = $pdo->prepare("EXEC sp_ServiceClass @Filter = ?");
            $stmt->execute([(string) ($request->get('filter') ?? '')]);
            return response()->json($stmt->fetchAll(\PDO::FETCH_ASSOC));
        } catch (\Exception $e) {
            return response()->json([]);
        }
    }

    public function lookupIndustryTypes(Request $request)
    {
        try {
            $pdo = DB::connection('sqlsrv')->getPdo();
            $stmt = $pdo->prepare("EXEC sp_IndustryTypeList @Filter = ?");
            $stmt->execute([(string) ($request->get('filter') ?? '')]);

            // sp_IndustryTypeList uses EXEC sp_executesql internally (dynamic SQL).
            // Same multi-result-set problem — loop until we find the data rows.
            $rows = [];
            do {
                try {
                    $fetched = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                    if (!empty($fetched)) {
                        $rows = $fetched;
                        break;
                    }
                } catch (\Exception $fetchEx) {
                    // This result set has no fields — advance to next
                }
            } while ($stmt->nextRowset());

            Log::info('lookupIndustryTypes success', ['count' => count($rows)]);
            return response()->json($rows);
        } catch (\Exception $e) {
            Log::error('lookupIndustryTypes error: ' . $e->getMessage());
            return response()->json([]);
        }
    }

    public function lookupPersonsInCharge(Request $request)
    {
        try {
            $pdo = DB::getPdo();
            $stmt = $pdo->prepare("EXEC sp_PersonInCharge @OfficeID = ?, @PICType = ?, @Filter = ?");
            $stmt->execute([
                $request->get('office_id'),
                $request->get('pic_type', 'S'),
                $request->get('filter', ''),
            ]);
            return response()->json($stmt->fetchAll(\PDO::FETCH_ASSOC));
        } catch (\Exception $e) {
            return response()->json([]);
        }
    }

    public function lookupShipmentModes(Request $request)
    {
        try {
            $pdo = DB::getPdo();
            $stmt = $pdo->prepare("EXEC sp_ShipmentMode @Filter = ?");
            $stmt->execute([(string) ($request->get('filter') ?? '')]);
            return response()->json($stmt->fetchAll(\PDO::FETCH_ASSOC));
        } catch (\Exception $e) {
            return response()->json([]);
        }
    }

    public function lookupTransportModes(Request $request)
    {
        try {
            $pdo = DB::getPdo();
            $stmt = $pdo->prepare("EXEC sp_TransportMode @Filter = ?");
            $stmt->execute([(string) ($request->get('filter') ?? '')]);
            return response()->json($stmt->fetchAll(\PDO::FETCH_ASSOC));
        } catch (\Exception $e) {
            return response()->json([]);
        }
    }

    public function lookupEquipmentTypes(Request $request)
    {
        try {
            $pdo = DB::getPdo();
            $stmt = $pdo->prepare("EXEC sp_EquipmentType @Filter = ?");
            $stmt->execute([(string) ($request->get('filter') ?? '')]);
            return response()->json($stmt->fetchAll(\PDO::FETCH_ASSOC));
        } catch (\Exception $e) {
            return response()->json([]);
        }
    }

    // ============================================================
    // PROCESS DUPLICATE
    // 1. Calls sp_Quote_QuotationCopy
    // 2. Updates new record with modal form data
    // 3. Inserts equipment rows if Containerized
    // ============================================================
    public function processDuplicate($quoteNo)
    {
        Log::info('processDuplicate - Starting', ['quoteNo' => $quoteNo]);
        Log::info('processDuplicate - param received', [
            'quoteNo' => $quoteNo,
            'type' => gettype($quoteNo),
        ]);

        try {
            // ── Find original record ──────────────────────────────────────────────
            // $original = DB::table('Quote_QuotationInfo')
            //     ->where('BookingNo', $BookingNo, 'officeCode', $officeCode)
            //     ->select('ID', 'bookingno', 'QuoteNo', 'officeCode')
            //     ->first();

            // if (!$original) {
            //     return response()->json([
            //         'success' => false,
            //         'message' => 'Quotation not found for quote number: ' .   $BookingNo,
            //     ], 404);
            // }

            $submittedBy = Auth::check()
                ? (Auth::user()->displayName ?? Auth::user()->Email ?? 'system')
                : 'system';

            // Log::info('processDuplicate - Original found', [
            //     'original_bookingno' => $original->bookingno,
            //     'original_id'        => $original->ID,
            //     'original_quote_no'  => $original->QuoteNo,
            //     'duplicated_by'      => $submittedBy,
            // ]);

            // ── Call sp_Quote_QuotationCopy ───────────────────────────────────────
            // SP handles: header insert + charge sheet copy + equipment details internally.
            // SP generates QuoteNo as: LEFT(OfficeCode,2) + '-QT-' + yy + '-' + 00001
            //________________________________________________________________________
            $pdo = DB::getPdo();
            $quotationId = DB::table('Quote_QuotationInfo')
                ->where('QuoteNo', $quoteNo)
                ->value('ID');

            if (!$quotationId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Quotation not found for quote number: ' . $quoteNo,
                ], 404);
            }

            $pdo = DB::getPdo();
            $sql = "
            DECLARE @Status  VARCHAR(25)
            DECLARE @Remark  VARCHAR(200)
            DECLARE @QuoteNo VARCHAR(50)
            EXEC dbo.sp_Quote_QuotationCopy ?,
                @Status  = @Status  OUTPUT,
                @Remark  = @Remark  OUTPUT,
                @QuoteNo = @QuoteNo OUTPUT
            SELECT @Status AS Status, @Remark AS Remark, @QuoteNo AS QuoteNo
            ";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([$quotationId]);

            $result = null;
            do {
                try {
                    $row = $stmt->fetch(\PDO::FETCH_ASSOC);
                    if ($row && isset($row['Status'])) {
                        $result = $row;
                        break;
                    }
                } catch (\Exception $e) {
                    // empty result set — skip
                }
            } while ($stmt->nextRowset());

            if (!$result) {
                // Log::error('processDuplicate - SP returned no output', [
                //     'original_id' => $original->ID,
                // ]);
                return response()->json([
                    'success' => false,
                    'message' => 'No response from copy stored procedure',
                ], 500);
            }

            $spStatus = strtolower(trim($result['Status'] ?? ''));
            if ($spStatus !== 'success') {
                Log::error('processDuplicate - SP failed', [
                    // 'original_id' => $original->ID,
                    'sp_status' => $result['Status'],
                    'sp_remark' => $result['Remark'],
                ]);
                return response()->json([
                    'success' => false,
                    'message' => $result['Remark'] ?? 'Copy stored procedure failed',
                ], 500);
            }

            $newQuoteNo = trim($result['QuoteNo'] ?? '');

            if (empty($newQuoteNo)) {
                Log::error('processDuplicate - SP succeeded but QuoteNo is empty', [
                    // 'original_id' => $original->ID,
                    'sp_remark' => $result['Remark'],
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Copy succeeded but no QuoteNo was returned',
                ], 500);
            }

            // ── Fetch new record by QuoteNo ───────────────────────────────────────
            // Must use QuoteNo column — NOT bookingno — because $newQuoteNo is the
            // formatted string (e.g. 'KE-QT-25-00002'), not the integer bookingno.
            $newQuotation = DB::table('Quote_QuotationInfo')
                ->where('QuoteNo', $newQuoteNo)
                ->select('ID', 'bookingno', 'QuoteNo')
                ->first();

            if (!$newQuotation) {
                Log::error('processDuplicate - New record not found after SP', [
                    'new_quote_no' => $newQuoteNo,
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Copy succeeded but new record could not be located (QuoteNo: ' . $newQuoteNo . ')',
                ], 500);
            }

            Log::info('processDuplicate - Success', [
                // 'original_bookingno' => $original->bookingno,
                // 'original_quote_no'  => $original->QuoteNo,
                'new_quote_no' => $newQuoteNo,
                'new_bookingno' => $newQuotation->bookingno,
                'new_id' => $newQuotation->ID,
                'duplicated_by' => $submittedBy,
            ]);

            return response()->json([
                'success' => true,
                'new_booking_no' => $newQuoteNo,
                'new_id' => $newQuotation->ID,
                'redirect_url' => route('quotation.edit', $newQuotation->ID),
            ]);
        } catch (\Exception $e) {
            Log::error('processDuplicate - Failed', [
                'quoteNo' => $quoteNo,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    // ============================================================
    // HANDLE SCM BOOKING INTEGRATION RESPONSE
    // Called by SCM after booking creation — updates QuoteStatus,
    // SCMBookingNo, SCMBookingID, SCMJobID, SCMJobNo based on XML response
    // ============================================================
    public function handleBookingIntegrationResponse(Request $request)
    {
        $request->validate([
            'quote_no' => 'required|string',
            'status' => 'required|string|in:Success,Failed',
            'customer_ref_no' => 'nullable|string',
            'transaction_id' => 'nullable|string',
            'errors' => 'nullable|array',
        ]);

        try {
            // Build XML matching SP's expected structure:
            // /ArrayOfTransaction/Transaction with ID, Status, CutomerRefNo, TransactionId, Errors/Error
            $errorsXml = '';
            if (!empty($request->errors)) {
                foreach ($request->errors as $err) {
                    $code = htmlspecialchars($err['code'] ?? '', ENT_XML1);
                    $description = htmlspecialchars($err['description'] ?? '', ENT_XML1);
                    $severity = (int) ($err['severity'] ?? 1);
                    $errorsXml .= "<Error><Code>{$code}</Code><Description>{$description}</Description><Severity>{$severity}</Severity></Error>";
                }
            }

            $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
        <ArrayOfTransaction>
            <Transaction>
                <ID>{$request->quote_no}</ID>
                <Status>{$request->status}</Status>
                <CutomerRefNo>" . htmlspecialchars($request->customer_ref_no ?? '', ENT_XML1) . "</CutomerRefNo>
                <TransactionId>" . htmlspecialchars($request->transaction_id ?? '', ENT_XML1) . "</TransactionId>
                <Errors>{$errorsXml}</Errors>
            </Transaction>
        </ArrayOfTransaction>";

            $pdo = DB::getPdo();
            $stmt = $pdo->prepare("EXEC dbo.sp_Quote_BookingCreateInfoUpdate @InputXML = ?");
            $stmt->execute([$xml]);

            // SP returns a SELECT only on failure — check for error message
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            if ($result) {
                $msg = array_values($result)[0] ?? '';
                if (str_starts_with(trim($msg), 'Failed')) {
                    Log::error('handleBookingIntegrationResponse - SP failed', [
                        'quote_no' => $request->quote_no,
                        'sp_msg' => $msg,
                    ]);
                    return response()->json(['success' => false, 'message' => $msg], 500);
                }
            }

            Log::info('handleBookingIntegrationResponse - Success', [
                'quote_no' => $request->quote_no,
                'status' => $request->status,
                'booking_no' => $request->customer_ref_no,
            ]);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('handleBookingIntegrationResponse error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function getIncoTerms(Request $request)
    {
        $filter = $request->input('filter', ''); // default to '' not null — some SPs reject NULL on VARCHAR params

        try {
            // FIX: was DB::connection()->getPdo() — wrong connection
            $pdo = DB::connection('sqlsrv')->getPdo();
            $stmt = $pdo->prepare("SET NOCOUNT ON; EXEC dbo.sp_IncoTerms @Filter = ?");
            $stmt->execute([$filter]);

            $results = [];
            do {
                try {
                    $rows = $stmt->fetchAll(\PDO::FETCH_OBJ);

                    if (empty($rows))
                        continue;

                    // Skip error rowsets from SP CATCH block
                    if (isset($rows[0]->Err_Message))
                        continue;

                    // First rowset containing IncoTermCode is the one we want
                    if (isset($rows[0]->IncoTermCode)) {
                        $results = $rows;
                        break;
                    }
                } catch (\PDOException $e) {
                    continue;
                }
            } while ($stmt->nextRowset());

            return response()->json(['success' => true, 'data' => $results]);
        } catch (\Exception $e) {
            Log::error('getIncoTerms - Failed', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'data' => [],
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getClientList(Request $request)
    {
        $filter = trim($request->input('filter', ''));

        if (strlen($filter) < 2) {
            return response()->json(['success' => true, 'clients' => []]);
        }

        try {
            $pdo = DB::connection('sqlsrv')->getPdo();
            $stmt = $pdo->prepare("SET NOCOUNT ON; EXEC dbo.sp_ClientList @Filter = ?");
            $stmt->execute([$filter]);

            $clients = [];
            do {
                try {
                    $rows = $stmt->fetchAll(\PDO::FETCH_OBJ);

                    if (empty($rows))
                        continue;

                    // Skip SP error result sets
                    $firstVal = array_values((array) $rows[0])[0] ?? '';
                    if (is_string($firstVal) && str_starts_with($firstVal, 'Error'))
                        continue;

                    if (isset($rows[0]->ClientID)) {
                        $clients = $rows;
                        break;
                    }
                } catch (\PDOException $e) {
                    continue;
                }
            } while ($stmt->nextRowset());

            Log::info('getClientList', ['filter' => $filter, 'count' => count($clients)]);

            return response()->json(['success' => true, 'clients' => $clients]);
        } catch (\Exception $e) {
            Log::error('getClientList failed', ['filter' => $filter, 'error' => $e->getMessage()]);
            return response()->json(['success' => false, 'clients' => [], 'message' => $e->getMessage()], 500);
        }
    }

    public function getClientDetails(Request $request)
    {
        $customerCode = trim($request->input('customer_code', ''));  // This should be the Client Code like "KEAA"

        if (!$customerCode) {
            return response()->json([
                'success' => false,
                'message' => 'customer_code is required'
            ], 400);
        }

        try {
            $pdo = DB::connection('sqlsrv')->getPdo();

            // Call with single parameter - the Customer Code
            $stmt = $pdo->prepare("SET NOCOUNT ON; EXEC dbo.sp_Quote_CustomerDetails ?");
            $stmt->execute([$customerCode]);

            $result = null;
            do {
                try {
                    $rows = $stmt->fetchAll(\PDO::FETCH_OBJ);
                    if (empty($rows))
                        continue;

                    $firstVal = array_values((array) $rows[0])[0] ?? '';
                    if (is_string($firstVal) && str_starts_with($firstVal, 'Failed')) {
                        return response()->json(['success' => false, 'message' => $firstVal], 422);
                    }

                    if (isset($rows[0]->Id)) {
                        $result = $rows[0];
                        break;
                    }
                } catch (\PDOException $e) {
                    continue;
                }
            } while ($stmt->nextRowset());

            if (!$result) {
                return response()->json(['success' => false, 'message' => 'No data returned for this customer'], 404);
            }

            return response()->json([
                'success' => true,
                'client_id' => $result->Id ?? '',
                'client_code' => $result->Code ?? '',
                'company' => $result->Name ?? '',
                'address1' => $result->Address1 ?? '',
                'address2' => $result->Address2 ?? '',
                'city' => $result->City ?? '',
                'zip' => $result->Zip ?? '',
                'state' => $result->State ?? '',
                'country_id' => $result->Address_CountryId ?? '',
                'country_code' => $result->CountryCode ?? '',
                'country_name' => $result->CountryName ?? '',
                'contact_person' => $result->ContactPerson ?? '',
                'phone' => $result->PhoneNo ?? '',
                'email' => $result->Email ?? '',
                'industry_code' => $result->IndustryTypeCode ?? '',
                'industry_name' => $result->IndustryType ?? '',
                'agent_type_code' => $result->AgentTypeCode ?? '',
                'agent_type_name' => $result->AgentType ?? '',
            ]);
        } catch (\Exception $e) {
            \Log::error('getClientDetails - Failed', [
                'customer_code' => $customerCode,
                'error' => $e->getMessage(),
            ]);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ============================================================
    // reportData()
    // Powers the Report modal on the quotation index page.
    // Calls sp_Quote_QuotationList with the user's filter inputs
    // and returns JSON for the modal DataTable.
    //
    // Office scoping: same as the main table — results are filtered
    // to only include records whose QuoteNo prefix matches the
    // user's allowed office codes resolved via resolveUserOfficeCodes().
    //
    // Status='Total' intentionally left as the default so the SP
    // returns the broadest result set (In Progress + Won branch).
    // Completed is not supported by the SP and is excluded.
    // ============================================================
    public function reportData(Request $request): JsonResponse
    {
        try {
            // ── Resolve allowed office codes for this user ───────────────────
            $allowedTeamNames = $this->resolveUserTeamNames();

            if ($allowedTeamNames->isEmpty()) {
                return response()->json([
                    'draw' => (int) $request->input('draw', 1),
                    'recordsTotal' => 0,
                    'recordsFiltered' => 0,
                    'data' => [],
                ]);
            }

            // ── Resolve allowed TeamCodes for office scoping ─────────────────
            $allowedTeamCodes = DB::table('Cust_Office')
                ->whereIn('Name', $allowedTeamNames->toArray())
                ->where('IsActive', 1)
                ->where('IsDeleted', 0)
                ->pluck('TeamCode')
                ->toArray();

            // ── Validate and sanitize inputs ─────────────────────────────────
            $fromDate = $request->input('from_date', '');
            $toDate = $request->input('to_date', '');
            $customer = $request->input('customer', '');
            $service = $request->input('service', '');
            $process = $request->input('process', '');
            $incoTerm = $request->input('incoterm', '');

            // Whitelist status values the SP actually supports.
            // 'Completed' is excluded — SP has no branch for it.
            $validStatuses = [
                'Total',
                'In Progress',
                'Won',
                'Cancelled',
                'Lost',
                'Failed Progress',
                'Failed Int.',
            ];

            $status = $request->input('status', 'Total');
            if (!in_array($status, $validStatuses, true)) {
                $status = 'Total';
            }

            // ── Validate date range if provided ──────────────────────────────
            if (!empty($fromDate) && !empty($toDate)) {
                try {
                    $from = \Carbon\Carbon::createFromFormat('Y-m-d', $fromDate);
                    $to = \Carbon\Carbon::createFromFormat('Y-m-d', $toDate);

                    if ($to->lt($from)) {
                        return response()->json([
                            'draw' => (int) $request->input('draw', 1),
                            'recordsTotal' => 0,
                            'recordsFiltered' => 0,
                            'data' => [],
                            'error' => 'End date must be after start date.',
                        ], 422);
                    }

                    // SP expects dd/MM/yyyy format (British date style, format 103)
                    $fromDate = $from->format('d/m/Y');
                    $toDate = $to->format('d/m/Y');
                } catch (\Exception $e) {
                    // Invalid date format — clear both to avoid SP errors
                    $fromDate = '';
                    $toDate = '';

                    Log::warning('reportData - Invalid date input, cleared', [
                        'from_date_raw' => $request->input('from_date'),
                        'to_date_raw' => $request->input('to_date'),
                    ]);
                }
            } else {
                // If only one date is provided, clear both — SP requires a pair
                $fromDate = '';
                $toDate = '';
            }

            // ── Build SP input JSON ───────────────────────────────────────────
            $inputJson = json_encode([
                'FromDate' => $fromDate,
                'ToDate' => $toDate,
                'Customer' => $customer,
                'Service' => $service,
                'Process' => $process,
                'OriginCountry' => '',
                'DestinationCountry' => '',
                'InCoTerm' => $incoTerm,
                'Status' => $status,
            ]);

            Log::info('reportData - Calling SP', [
                'user' => Auth::user()->Email ?? 'unknown',
                'input_json' => $inputJson,
                'office_scope' => $allowedTeamNames->toArray(),
            ]);

            // ── Execute SP ────────────────────────────────────────────────────
            // The SP emits a diagnostic SELECT of the parsed params as its
            // first result set before the actual data. We skip it using
            // nextRowset() — same pattern used throughout this controller.
            $pdo = DB::connection()->getPdo();
            $stmt = $pdo->prepare("SET NOCOUNT ON; EXEC dbo.sp_Quote_QuotationList ?");
            $stmt->execute([$inputJson]);

            $rows = [];
            $resultIndex = 0;

            do {
                $resultIndex++;
                try {
                    $fetched = $stmt->fetchAll(\PDO::FETCH_OBJ);

                    if (empty($fetched)) {
                        continue;
                    }

                    // ── Detect SP CATCH block failure string ──────────────────
                    // SP returns: SELECT 'Failed - (Reason) ' + ERROR_MESSAGE()
                    // This comes back as a single-column, single-row result set.
                    $firstVal = array_values((array) $fetched[0])[0] ?? '';
                    if (is_string($firstVal) && str_starts_with(trim($firstVal), 'Failed')) {
                        Log::error('reportData - SP returned failure', [
                            'message' => $firstVal,
                            'input_json' => $inputJson,
                        ]);

                        return response()->json([
                            'draw' => (int) $request->input('draw', 1),
                            'recordsTotal' => 0,
                            'recordsFiltered' => 0,
                            'data' => [],
                            'error' => 'Report query failed. Please contact your administrator.',
                        ], 500);
                    }

                    // ── Skip the diagnostic result set ────────────────────────
                    // The SP always emits SELECT @FromDate,@ToDate,... as result
                    // set #1. We detect it by checking for the QuoteNo column
                    // which only exists on the actual data result set.
                    if (!isset($fetched[0]->QuoteNo)) {
                        continue;
                    }

                    // ── This is the data result set ───────────────────────────
                    $rows = $fetched;
                    break;
                } catch (\PDOException $e) {
                    // Skip unfetchable intermediate result sets — normal for
                    // SPs that use dynamic SQL or temp tables internally.
                    Log::debug('reportData - Skipping result set #' . $resultIndex, [
                        'error' => $e->getMessage(),
                    ]);
                    continue;
                }
            } while ($stmt->nextRowset());

            Log::info('reportData - SP returned rows', [
                'row_count' => count($rows),
                'result_sets' => $resultIndex,
                'status_filter' => $status,
            ]);

            // ── Apply office scoping in PHP ───────────────────────────────────
            // The SP does not accept an office filter parameter, so we scope
            // the results here by matching QuoteNo prefix against the user's
            // allowed office codes (e.g. QuoteNo 'KE-QT-25-00001' → office 'KE').
            // This mirrors how resolveUserOfficeCodes() works.
            $allowedOfficeCodes = $this->resolveUserOfficeCodes()->toArray();

            if (!empty($allowedOfficeCodes)) {
                $rows = array_filter($rows, function ($row) use ($allowedOfficeCodes) {
                    $quoteNo = $row->QuoteNo ?? '';
                    if (empty($quoteNo)) {
                        return false;
                    }

                    // QuoteNo format: {OFFICE_CODE}-QT-{YY}-{NNNNN}
                    // e.g. KE-QT-25-00001 → office code is 'KE'
                    $parts = explode('-', $quoteNo);
                    $officeCode = strtoupper(trim($parts[0] ?? ''));

                    return in_array($officeCode, array_map('strtoupper', $allowedOfficeCodes), true);
                });

                $rows = array_values($rows); // re-index after filter
            }

            // ── Format rows for DataTables ────────────────────────────────────
            $formatted = array_map(function ($row) {
                return [
                    'QuoteNo' => $row->QuoteNo ?? '-',
                    'Date' => $row->Date ?? '-',
                    'ClientName' => $row->CompanyName ?? '-',
                    'SalesPerson' => $row->SalesPerson ?? '-',
                    'Process' => $row->Process ?? '-',
                    'Origin' => $row->OriginCountry ?? '-',
                    'Destination' => $row->DestinationCountry ?? '-',
                    'IncoTerm' => $row->IncoTerm ?? '-',
                    'Status' => $row->Status ?? '-',
                    'Remark' => $row->Remark ?? '',
                ];
            }, $rows);

            $total = count($formatted);

            return response()->json([
                'draw' => (int) $request->input('draw', 1),
                'recordsTotal' => $total,
                'recordsFiltered' => $total,
                'data' => $formatted,
            ]);
        } catch (\Exception $e) {
            Log::error('reportData - Failed', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return response()->json([
                'draw' => (int) $request->input('draw', 1),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => 'An unexpected error occurred.',
            ], 500);
        }
    }

    /**
     * Returns quotation stats for the dashboard home card.
     * All-time scope (no date filter), scoped to the calling user's offices.
     * Keys match the dashboard card: total, in_progress, failed_int, failed_progress.
     */
    public static function getHomeStats(): array
    {
        $blank = [
            'total' => 0,
            'in_progress' => 0,
            'failed_int' => 0,
            'failed_progress' => 0,
        ];

        try {
            $instance = new self();
            $allowedOfficeCodes = $instance->resolveUserOfficeCodes();

            \Log::info('QuotationController::getHomeStats - office codes resolved', [
                'user_email' => \Auth::user()->Email ?? 'null',
                'allowed_office_codes' => $allowedOfficeCodes->toArray(),
            ]);

            if ($allowedOfficeCodes->isEmpty()) {
                \Log::warning('QuotationController::getHomeStats - no office codes, returning zeros');
                return $blank;
            }

            $stats = $instance->getStatsForIndex($allowedOfficeCodes);

            \Log::info('QuotationController::getHomeStats - stats returned', $stats);

            return [
                'total' => $stats['total'] ?? 0,
                'in_progress' => $stats['in_progress'] ?? 0,
                'failed_int' => $stats['failed_int'] ?? 0,
                'failed_progress' => $stats['failed_progress'] ?? 0,
            ];
        } catch (\Exception $e) {
            \Log::error('QuotationController::getHomeStats - Failed', [
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);

            return $blank;
        }
    }

    public function pushBookingToSCM(Request $request): JsonResponse
    {
        $quoteNo = $request->input('quote_no');
        $bookingNo = $request->input('booking_no');

        if (!$quoteNo && !$bookingNo) {
            return response()->json([
                'success' => false,
                'message' => 'quote_no or booking_no is required'
            ], 400);
        }

        try {
            // ── Fetch record ──────────────────────────────────────────────────────
            $quotation = DB::table('Quote_QuotationInfo')
                ->when($quoteNo, fn($q) => $q->where('QuoteNo', $quoteNo))
                ->when(!$quoteNo, fn($q) => $q->where('bookingno', $bookingNo))
                ->first(['ID', 'QuoteNo', 'bookingno', 'SCMBookingXML', 'SCMBookingID']);

            if (!$quotation) {
                return response()->json(['success' => false, 'message' => 'Quotation not found'], 404);
            }

            if (empty($quotation->SCMBookingXML)) {
                return response()->json([
                    'success' => false,
                    'message' => 'SCMBookingXML is empty. Run the XML generation step first.'
                ], 422);
            }

            Log::info('pushBookingToSCM - Starting', [
                'quote_no' => $quotation->QuoteNo,
                'booking_no' => $quotation->bookingno,
                'xml_length' => strlen($quotation->SCMBookingXML),
            ]);

            // ── Step 1: Generate Token ────────────────────────────────────────────
            $tokenUrl = env('SCM_TOKEN_URL', 'https://hhtapi.freightintime.com/token');
            $authString = 'grant_type=password'
                . '&username=' . urlencode(env('SCM_USERNAME', 'fitexpress'))
                . '&password=' . urlencode(env('SCM_PASSWORD', 'Forwarder@8082'));

            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL => $tokenUrl,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => $authString,
                CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
            ]);

            $tokenResponse = curl_exec($curl);
            $tokenError = curl_error($curl);
            curl_close($curl);

            if ($tokenError) {
                Log::error('pushBookingToSCM - Token fetch failed', [
                    'quote_no' => $quotation->QuoteNo,
                    'error' => $tokenError,
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to generate SCM token: ' . $tokenError
                ], 500);
            }

            $tokenData = json_decode($tokenResponse);
            if (json_last_error() !== JSON_ERROR_NONE || !isset($tokenData->access_token)) {
                Log::error('pushBookingToSCM - Token parse failed', [
                    'quote_no' => $quotation->QuoteNo,
                    'response' => $tokenResponse,
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to parse SCM token response'
                ], 500);
            }

            $token = $tokenData->access_token;

            Log::info('pushBookingToSCM - Token generated successfully', [
                'quote_no' => $quotation->QuoteNo,
            ]);

            // ── Step 2: Push XML to SCM ───────────────────────────────────────────
            $pushUrl = env('SCM_BOOKING_URL', 'https://hhtapi.freightintime.com/api/v1/Booking/ProcessBooking');
            $xml = $quotation->SCMBookingXML;

            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL => $pushUrl,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 60,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => $xml,
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/xml; charset=utf-8',
                    'Authorization: Bearer ' . $token,
                    'Content-Length: ' . strlen($xml),
                    'Connection: keep-alive',
                    'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                ],
            ]);

            $scmResponse = curl_exec($curl);
            $curlError = curl_error($curl);
            curl_close($curl);

            if ($curlError) {
                Log::error('pushBookingToSCM - Curl push failed', [
                    'quote_no' => $quotation->QuoteNo,
                    'error' => $curlError,
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to push XML to SCM: ' . $curlError
                ], 500);
            }

            Log::info('pushBookingToSCM - SCM raw response', [
                'quote_no' => $quotation->QuoteNo,
                'response' => $scmResponse,
            ]);

            // ── Step 3: Parse SCM XML response ────────────────────────────────────
            libxml_use_internal_errors(true);
            $xmlObj = simplexml_load_string($scmResponse, null, LIBXML_NOCDATA);

            if ($xmlObj === false) {
                Log::error('pushBookingToSCM - Invalid SCM response XML', [
                    'quote_no' => $quotation->QuoteNo,
                    'response' => $scmResponse,
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'SCM returned an invalid XML response: ' . $scmResponse
                ], 500);
            }

            $json = json_encode($xmlObj);
            $response = json_decode($json, true);

            Log::info('pushBookingToSCM - SCM response parsed', [
                'quote_no' => $quotation->QuoteNo,
                'parsed_response' => $response,
            ]);

            // ── Step 4: Build XML for sp_Quote_BookingCreateInfoUpdate ────────────
            $status = $response['Transaction']['Status'] ?? 'Fail';
            $transactionId = $response['Transaction']['TransactionId'] ?? '';
            $customerRefNo = $response['Transaction']['ID'] ?? $quotation->QuoteNo;

            $errors = $response['Transaction']['Errors']['Error'] ?? [];
            $errorXml = '';

            if (!empty($errors)) {
                if (isset($errors['Code'])) {
                    $errors = [$errors];
                }
                foreach ($errors as $error) {
                    $code = htmlspecialchars($error['Code'] ?? '');
                    $description = htmlspecialchars($error['Description'] ?? '');
                    $severity = htmlspecialchars((string) ($error['Severity'] ?? '0'));
                    $errorXml .= "<Error>
                    <Code>{$code}</Code>
                    <Description>{$description}</Description>
                    <Severity>{$severity}</Severity>
                </Error>";
                }
            }

            $inputXml = "
        <ArrayOfTransaction>
            <Transaction>
                <ID>{$quotation->QuoteNo}</ID>
                <Status>{$status}</Status>
                <CutomerRefNo>{$customerRefNo}</CutomerRefNo>
                <TransactionId>{$transactionId}</TransactionId>
                <Errors>{$errorXml}</Errors>
            </Transaction>
        </ArrayOfTransaction>";

            Log::info('pushBookingToSCM - Passing to sp_Quote_BookingCreateInfoUpdate', [
                'quote_no' => $quotation->QuoteNo,
                'status' => $status,
                'input_xml' => $inputXml,
            ]);

            // ── Step 5: Call sp_Quote_BookingCreateInfoUpdate ─────────────────────
            $pdo = DB::connection()->getPdo();
            $stmt = $pdo->prepare("SET NOCOUNT ON; EXEC dbo.sp_Quote_BookingCreateInfoUpdate ?");
            $stmt->execute([$inputXml]);

            $spError = null;
            do {
                try {
                    $rows = $stmt->fetchAll(\PDO::FETCH_OBJ);
                    if (empty($rows))
                        continue;
                    $firstVal = array_values((array) $rows[0])[0] ?? '';
                    if (is_string($firstVal) && str_starts_with($firstVal, 'Failed')) {
                        $spError = $firstVal;
                        break;
                    }
                } catch (\PDOException $e) {
                    continue;
                }
            } while ($stmt->nextRowset());

            if ($spError) {
                Log::error('pushBookingToSCM - SP returned error', [
                    'quote_no' => $quotation->QuoteNo,
                    'sp_error' => $spError,
                ]);
                return response()->json([
                    'success' => false,
                    'message' => $spError
                ], 422);
            }

            // ── Step 6: Verify SCMBookingID was written ───────────────────────────
            $updated = DB::table('Quote_QuotationInfo')
                ->where('QuoteNo', $quotation->QuoteNo)
                ->first(['SCMBookingID', 'SCMBookingNo', 'QuoteStatus']);

            Log::info('pushBookingToSCM - Completed', [
                'quote_no' => $quotation->QuoteNo,
                'scm_booking_id' => $updated->SCMBookingID ?? 'NULL',
                'scm_booking_no' => $updated->SCMBookingNo ?? 'NULL',
                'quote_status' => $updated->QuoteStatus ?? 'NULL',
                'scm_status' => $status,
            ]);

            return response()->json([
                'success' => $status === 'Success',
                'message' => $status === 'Success'
                    ? 'Booking pushed to SCM successfully'
                    : 'SCM returned failure — check errors',
                'scm_booking_id' => $updated->SCMBookingID ?? null,
                'scm_booking_no' => $updated->SCMBookingNo ?? null,
                'quote_status' => $updated->QuoteStatus ?? null,
                'scm_status' => $status,
                'errors' => $errors,
            ]);
        } catch (\Exception $e) {
            Log::error('pushBookingToSCM - Exception', [
                'quote_no' => $quoteNo ?? $bookingNo,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
