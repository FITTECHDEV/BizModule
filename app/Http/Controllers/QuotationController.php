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
use Illuminate\Support\Facades\Cache;

class QuotationController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth']);
    }

    // ============================================
    // OFFICE BASE CURRENCY MAP
    // Source: Cust_Office + Bill_ChargeCodeOfficeMap (dominant currency per office)
    // ============================================
    public const OFFICE_BASE_CURRENCY = [
        'DJ' => 'DJF',
        'ET' => 'ETB',
        'KE' => 'KES',
        'RW' => 'RWF',
        'SA' => 'KES',
        'SS' => 'SSP',
        'SO' => 'USD',
        'TZ' => 'TZS',
        'UG' => 'UGX',
        'ZM' => 'ZMW',
    ];

    public function index()
    {
        $resolved = $this->resolveUserOfficesViaSP();
        $offices = $resolved['offices'];
        $defaultOffice = $resolved['default']; // always BaseOfficeCode's name

        if ($offices->isEmpty() || !$defaultOffice) {
            return view('quotation.index', [
                'offices' => collect(),
                'defaultOffice' => null,
                'activeOfficeName' => null,
                'officeError' =>
                    'Your account is not mapped to any office in BIZ PRO. Please contact your administrator.',
                'stats' => $this->emptyStats(),
            ]);
        }

        // Resolve session office code → office name for pre-selection
        $sessionCode = session('active_office_code');
        $activeOfficeName = $defaultOffice; // fallback to base

        if ($sessionCode && $offices->isNotEmpty()) {
            $match = $offices->firstWhere('code', strtoupper(trim($sessionCode)));
            if ($match) {
                $activeOfficeName = $match['name'];
            }
        }

        Log::info('Quotation index - Offices resolved via SP', [
            'email' => Auth::user()->Email,
            'default_office' => $defaultOffice,
            'active_office' => $activeOfficeName,
            'session_code' => $sessionCode,
            'all_offices' => $offices->toArray(),
        ]);

        $stats = $this->emptyStats();

        return view('quotation.index', compact('offices', 'defaultOffice', 'activeOfficeName', 'stats'));
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
            ['from' => $spFromDate, 'to' => $spToDate] = $this->resolveDateRange($days, $startDate, $endDate);

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
            $lostCancelledRows = $this->callQuotationListSP('Lost', $spFromDate, $spToDate, $spOfficeCode);
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

            $spQuoteNos = array_column($allRows, 'QuoteNo');
            $dbQuoteNos = $scmLookup->keys()->toArray();

            Log::info('QuoteNo match check', [
                'sp_sample' => array_slice($spQuoteNos, 0, 3),
                'db_sample' => array_slice($dbQuoteNos, 0, 3),
                'sp_missing' => array_slice(array_diff($spQuoteNos, $dbQuoteNos), 0, 5),
            ]);

            // ── Compute stats ─────────────────────────────────────────────────
            $inProgress = $won = $completed = $failedInt = $failedProgress = 0;

            foreach ($allRows as $row) {
                $db = $scmLookup->get($row->QuoteNo);
                $quoteStatus = trim($db->QuoteStatus ?? ($row->Status ?? ''));
                $scmStatus = $db ? $db->SCMINTStatus : null;

                if ($quoteStatus === 'Completed') {
                    $completed++;
                } elseif ($quoteStatus === 'Won') {
                    $won++;
                } elseif ($quoteStatus === 'Failed Progress') {
                    $failedProgress++;
                } elseif ($quoteStatus === 'Failed Int.' || $quoteStatus === 'Failed Int') {
                    $failedInt++;
                } else {
                    $inProgress++;
                }
            }

            $lostCanceled = count($lostCancelledRows);

            // Count quotations that have at least one office request
            $allQuoteNosForStats = array_column($allRows, 'QuoteNo');
            $inProgressRequest = 0;
            if (!empty($allQuoteNosForStats)) {
                $quotationIds = DB::table('Quote_QuotationInfo')
                    ->whereIn('QuoteNo', $allQuoteNosForStats)
                    ->pluck('ID')
                    ->toArray();

                if (!empty($quotationIds)) {
                    $inProgressRequest = DB::table('Quote_Quotation_RequestedQuote')
                        ->whereIn('QuotationInfoID', $quotationIds)
                        ->where('IsActive', 1)
                        ->where('IsDeleted', 0)
                        ->distinct('QuotationInfoID')
                        ->count('QuotationInfoID');
                }
            }

            $total = $inProgress + $won + $completed + $failedInt + $failedProgress + $lostCanceled;
            $stats = [
                'total' => $total,
                'in_progress' => $inProgress,
                'in_progress_request' => $inProgressRequest,
                'won' => $won,
                'completed' => $completed,
                'lost_canceled' => $lostCanceled,
                'failed_int' => $failedInt,
                'failed_progress' => $failedProgress,
            ];

            // ── Select row set for active card ────────────────────────────────
            // Resolve QuoteNos that have office requests (only when needed)
            $requestQuoteNos = [];
            if ($cardFilter === 'in_progress_request') {
                $allRowQuoteNos = array_column($allRows, 'QuoteNo');
                $requestedIds = DB::table('Quote_Quotation_RequestedQuote')
                    ->whereIn(
                        'QuotationInfoID',
                        DB::table('Quote_QuotationInfo')->whereIn('QuoteNo', $allRowQuoteNos)->pluck('ID'),
                    )
                    ->where('IsActive', 1)
                    ->where('IsDeleted', 0)
                    ->distinct('QuotationInfoID')
                    ->pluck('QuotationInfoID')
                    ->toArray();

                $requestQuoteNos = DB::table('Quote_QuotationInfo')
                    ->whereIn('ID', $requestedIds)
                    ->pluck('QuoteNo')
                    ->toArray();
            }

            $activeRows = match ($cardFilter) {
                'lost_canceled' => $lostCancelledRows,
                'failed_progress' => $failedProgressRows,
                'in_progress_request' => array_values(
                    array_filter($allRows, fn($row) => in_array($row->QuoteNo, $requestQuoteNos)),
                ),
                default => $allRows,
            };

            // ── PHP filter for Total-branch sub-cards ─────────────────────────
            if (!in_array($cardFilter, ['lost_canceled', 'failed_progress', 'total', 'in_progress_request'])) {
                $activeRows = array_values(
                    array_filter($activeRows, function ($row) use ($cardFilter, $scmLookup) {
                        $db = $scmLookup->get($row->QuoteNo);
                        $quoteStatus = strtoupper(trim($db->QuoteStatus ?? ($row->Status ?? '')));
                        $scmStatus = $db
                            ? (is_string($db->SCMINTStatus)
                                ? strtoupper(trim($db->SCMINTStatus))
                                : null)
                            : null;

                        return match ($cardFilter) {
                            'in_progress' => !in_array($quoteStatus, [
                                'WON',
                                'COMPLETED',
                                'LOST',
                                'CANCELLED',
                                'CANCEL',
                                'FAILED PROGRESS',
                                'FAILED INT',
                                'FAILED INT.',
                            ]),
                            'won' => $quoteStatus === 'WON',
                            'completed' => $quoteStatus === 'COMPLETED',
                            'failed_int' => in_array($quoteStatus, ['FAILED INT', 'FAILED INT.']),
                            default => true,
                        };
                    }),
                );
            }

            // ── DataTables server-side ────────────────────────────────────────
            $draw = (int) $request->input('draw', 1);
            $start = (int) $request->input('start', 0);
            $length = (int) $request->input('length', 25);
            $search = trim($request->input('search.value', ''));

            if (!empty($search)) {
                $needle = strtolower($search);
                $activeRows = array_values(
                    array_filter($activeRows, function ($row) use ($needle) {
                        return str_contains(strtolower($row->CompanyName ?? ''), $needle) ||
                            str_contains(strtolower($row->OriginCountry ?? ''), $needle) ||
                            str_contains(strtolower($row->DestinationCountry ?? ''), $needle) ||
                            str_contains(strtolower($row->Status ?? ''), $needle) ||
                            str_contains(strtolower($row->QuoteNo ?? ''), $needle) ||
                            str_contains(strtolower($row->Service ?? ''), $needle) ||
                            str_contains(strtolower($row->Process ?? ''), $needle) ||
                            str_contains(strtolower($row->IncoTerm ?? ''), $needle);
                    }),
                );
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
                $quoteStatus = trim($db->QuoteStatus ?? ($row->Status ?? ''));
                $scmStatus = $db->SCMINTStatus ?? null;

                $displayStatus = match (true) {
                    strtolower($quoteStatus) === 'completed' => 'Completed',
                    strtolower($quoteStatus) === 'won' => 'Won',
                    in_array(strtolower($quoteStatus), ['failed int.', 'failed int']) => 'Failed Int.',
                    strtolower($quoteStatus) === 'failed progress' => 'Failed Progress',
                    in_array(strtolower($quoteStatus), ['lost', 'cancelled', 'cancel']) => 'Lost / Canceled',
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
                        $remark =
                            json_last_error() === JSON_ERROR_NONE && is_array($decoded)
                                ? implode(' | ', array_column($decoded, 'Remark'))
                                : $raw;
                    }
                }

                $remarksHtml = '<span class="text-muted">—</span>';
                if ($remark && $remark !== '-') {
                    $isError = in_array($displayStatus, ['Failed Int.', 'Failed Progress']);
                    $short = mb_strlen($remark) > 80 ? mb_substr($remark, 0, 80) . '...' : $remark;
                    $remarksHtml = $isError
                        ? '<span class="text-danger" title="' .
                            e($remark) .
                            '"><i class="fas fa-exclamation-circle mr-1"></i>' .
                            e($short) .
                            '</span>'
                        : '<span class="text-muted">' . e($short) . '</span>';
                }

                $actionHtml = '<div class="action-buttons">';
                if ($db) {
                    $isWon = $quoteStatus === 'Won';
                    $isFailedInt = in_array(strtolower($quoteStatus), ['failed int.', 'failed int']);

                    $actionHtml .=
                        '<a href="' .
                        route('quotation.view', $db->ID) .
                        '" class="btn btn-info btn-sm" title="View"><i class="fas fa-eye"></i></a>';

                    if ($cardFilter === 'in_progress') {
                        $actionHtml .=
                            ' <a href="' .
                            route('quotation.edit', $db->ID) .
                            '" class="btn btn-warning btn-sm" title="Edit"><i class="fas fa-edit"></i></a>';
                    }

                    if (($cardFilter === 'won' && $isWon) || ($cardFilter === 'failed_int' && $isFailedInt)) {
                        $actionHtml .=
                            ' <button class="btn btn-success btn-sm btn-create-booking"' .
                            ' data-id="' .
                            $db->ID .
                            '"' .
                            ' data-booking="' .
                            $db->bookingno .
                            '"' .
                            ' data-team="' .
                            $db->teamid .
                            '"' .
                            ' data-quoteno="' .
                            $db->QuoteNo .
                            '"' .
                            ' data-origin="' .
                            e($row->OriginCountry ?? '') .
                            '"' .
                            ' data-destination="' .
                            e($row->DestinationCountry ?? '') .
                            '"' .
                            ' data-status="' .
                            e($scmStatus ?? '') .
                            '"' .
                            ' title="Create Booking"><i class="fas fa-plus-circle"></i></button>';
                    }
                }
                $actionHtml .= '</div>';

                return [
                    'QuoteNo' => $row->QuoteNo ?? '-',
                    'Date' => $db?->CreatedOn ? date('Y-m-d', strtotime($db->CreatedOn)) : $row->Date ?? '-',
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
            return response()->json(
                [
                    'draw' => (int) $request->input('draw', 1),
                    'recordsTotal' => 0,
                    'recordsFiltered' => 0,
                    'data' => [],
                    'stats' => $this->emptyStats(),
                ],
                500,
            );
        }
    }

    /**
     * Resolves $spFromDate / $spToDate from the request's 'days' parameter.
     * Centralises logic used by portal(), dataFromSP(), and stats().
     */
    private function resolveDateRange(string $days, ?string $startDate, ?string $endDate): array
    {
        if ($days === 'custom' && $startDate && $endDate) {
            try {
                return [
                    'from' => \Carbon\Carbon::createFromFormat('Y-m-d', $startDate)->format('d/m/Y'),
                    'to' => \Carbon\Carbon::createFromFormat('Y-m-d', $endDate)->format('d/m/Y'),
                ];
            } catch (\Exception $e) {
                Log::warning('resolveDateRange - Invalid custom range', [
                    'start' => $startDate,
                    'end' => $endDate,
                ]);
                return ['from' => '', 'to' => ''];
            }
        }

        if ($days !== 'all' && is_numeric($days)) {
            $daysInt = (int) $days;

            if ($daysInt === 0) {
                $date = now()->format('d/m/Y');
                return ['from' => $date, 'to' => $date];
            }

            if ($daysInt === 1) {
                $date = now()->subDay()->format('d/m/Y');
                return ['from' => $date, 'to' => $date];
            }

            return [
                'from' => now()->subDays($daysInt)->format('d/m/Y'),
                'to' => now()->format('d/m/Y'),
            ];
        }

        return ['from' => '', 'to' => ''];
    }

    private function emptyStats(): array
    {
        return [
            'total' => 0,
            'in_progress' => 0,
            'in_progress_request' => 0,
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

        ['from' => $spFromDate, 'to' => $spToDate] = $this->resolveDateRange($days, $startDate, $endDate);

        // Office dropdown filter
        $filterCode = null;
        $requestedOffice = $request->input('office');
        if ($requestedOffice) {
            $code = DB::table('Cust_Office')
                ->where('Name', $requestedOffice)
                ->where('IsActive', 1)
                ->where('IsDeleted', 0)
                ->value('Code');
            if ($code) {
                $filterCode = strtoupper(trim($code));
            }
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
            $quoteStatus = $db->QuoteStatus ?? ($row->Status ?? '');
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

        // Count quotations that have at least one office request
        $inProgressRequest = 0;
        if (!empty($quoteNos)) {
            $quotationIds = DB::table('Quote_QuotationInfo')->whereIn('QuoteNo', $quoteNos)->pluck('ID')->toArray();

            if (!empty($quotationIds)) {
                $inProgressRequest = DB::table('Quote_Quotation_RequestedQuote')
                    ->whereIn('QuotationInfoID', $quotationIds)
                    ->where('IsActive', 1)
                    ->where('IsDeleted', 0)
                    ->distinct('QuotationInfoID')
                    ->count('QuotationInfoID');
            }
        }

        return response()->json([
            'total' => $total,
            'in_progress' => $inProgress,
            'in_progress_request' => $inProgressRequest,
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

            $allowed = $allowedOfficeCodes->map(fn($c) => strtoupper(trim($c)))->toArray();

            // ── Date range — day filters ignored, SP only supports FromDate/ToDate ──
            ['from' => $spFromDate, 'to' => $spToDate] = $this->resolveDateRange($days, $startDate, $endDate);

            $spStatus = match ($cardFilter) {
                'lost_canceled' => 'Lost', // SP returns both Lost+Cancelled in one call
                'failed_progress' => 'Failed Progress',
                default => 'Total', // covers in_progress, won, completed, failed_int, total
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
                $rows = array_values(
                    array_filter($rows, function ($row) use ($cardFilter, $dbRecords) {
                        $db = $dbRecords->get($row->QuoteNo);
                        $quoteStatus = $db->QuoteStatus ?? ($row->Status ?? '');
                        $scmStatus = $db->SCMINTStatus ?? null;

                        return match ($cardFilter) {
                            'in_progress' => $quoteStatus === 'In Progress',
                            'won' => $quoteStatus === 'Won' && is_null($scmStatus),
                            'completed' => $quoteStatus === 'Won' && $scmStatus === 'Success',
                            'failed_int' => $quoteStatus === 'Won' && !is_null($scmStatus) && $scmStatus !== 'Success',
                            default => true,
                        };
                    }),
                );
            }

            // ── DataTables server-side ────────────────────────────────────────
            $draw = (int) $request->input('draw', 1);
            $start = (int) $request->input('start', 0);
            $length = (int) $request->input('length', 25);
            $search = trim($request->input('search.value', ''));

            // Search
            if (!empty($search)) {
                $needle = strtolower($search);
                $rows = array_values(
                    array_filter($rows, function ($row) use ($needle, $dbRecords) {
                        $db = $dbRecords->get($row->QuoteNo);
                        return str_contains(strtolower($row->CompanyName ?? ''), $needle) ||
                            str_contains(strtolower($row->SalesPerson ?? ''), $needle) ||
                            str_contains(strtolower($row->OriginCountry ?? ''), $needle) ||
                            str_contains(strtolower($row->DestinationCountry ?? ''), $needle) ||
                            str_contains(strtolower($row->Status ?? ''), $needle) ||
                            str_contains(strtolower($row->QuoteNo ?? ''), $needle) ||
                            str_contains(strtolower($row->Service ?? ''), $needle) ||
                            str_contains(strtolower($row->Process ?? ''), $needle) ||
                            str_contains(strtolower($row->IncoTerm ?? ''), $needle);
                    }),
                );
            }

            $total = count($rows);
            $pageRows = array_slice($rows, $start, $length > 0 ? $length : $total);

            // ── Format rows for DataTable ─────────────────────────────────────
            $data = array_map(function ($row) use ($dbRecords, $cardFilter) {
                $db = $dbRecords->get($row->QuoteNo);
                $quoteStatus = $db->QuoteStatus ?? ($row->Status ?? 'In Progress');
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

                // For Failed Int., QuoteStatusDescription holds the actual pipeline failure
                $remark = trim($row->Remark ?? '');

                if ($db) {
                    $raw = trim($db->QuoteStatusDescription ?? '');
                    if ($raw) {
                        $decoded = json_decode('[' . $raw . ']', true);
                        $remark =
                            json_last_error() === JSON_ERROR_NONE && is_array($decoded)
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
                        ? '<span class="text-danger" title="' .
                            e($remark) .
                            '"><i class="fas fa-exclamation-circle mr-1"></i>' .
                            e($short) .
                            '</span>'
                        : '<span class="text-muted">' . e($short) . '</span>';
                }

                // Action buttons
                $actionHtml = '<div class="action-buttons">';
                if ($db) {
                    $isWon = $quoteStatus === 'Won' && is_null($scmStatus);
                    $isFailedInt = $quoteStatus === 'Won' && !is_null($scmStatus) && $scmStatus !== 'Success';
                    $isInProgress = $quoteStatus === 'In Progress';

                    $actionHtml .=
                        '<a href="' .
                        route('quotation.view', $db->ID) .
                        '" class="btn btn-info btn-sm" title="View"><i class="fas fa-eye"></i></a>';

                    if ($cardFilter === 'in_progress' && $isInProgress) {
                        $actionHtml .=
                            ' <a href="' .
                            route('quotation.edit', $db->ID) .
                            '" class="btn btn-warning btn-sm" title="Edit"><i class="fas fa-edit"></i></a>';
                    }

                    if (($cardFilter === 'won' && $isWon) || ($cardFilter === 'failed_int' && $isFailedInt)) {
                        $actionHtml .=
                            ' <button class="btn btn-success btn-sm btn-create-booking"' .
                            ' data-id="' .
                            $db->ID .
                            '"' .
                            ' data-booking="' .
                            $db->bookingno .
                            '"' .
                            ' data-team="' .
                            $db->teamid .
                            '"' .
                            ' data-quoteno="' .
                            $db->QuoteNo .
                            '"' .
                            ' data-origin="' .
                            e($row->OriginCountry ?? '') .
                            '"' .
                            ' data-destination="' .
                            e($row->DestinationCountry ?? '') .
                            '"' .
                            ' title="Create Booking"><i class="fas fa-plus-circle"></i></button>';
                    }
                }
                $actionHtml .= '</div>';

                return [
                    'QuoteNo' => $row->QuoteNo ?? '-',
                    'Date' => $db?->CreatedOn ? date('Y-m-d', strtotime($db->CreatedOn)) : $row->Date ?? '-',
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
            return response()->json(
                [
                    'draw' => (int) $request->input('draw', 1),
                    'recordsTotal' => 0,
                    'recordsFiltered' => 0,
                    'data' => [],
                ],
                500,
            );
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

        $allRows = $this->callSPForAllOffices('Total', $allowedOfficeCodes);
        $lostCancelledRows = $this->callSPForAllOffices('Lost', $allowedOfficeCodes);
        $failedProgressRows = $this->callSPForAllOffices('Failed Progress', $allowedOfficeCodes);

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
            $quoteStatus = $db->QuoteStatus ?? ($row->Status ?? '');
            $scmStatus = $db->SCMINTStatus ?? null;

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
        string $officeCode = '',
    ): array {
        $inputJson = json_encode(
            [
                'OfficeCode' => $officeCode, // ← was always ''
                'FromDate' => $fromDate,
                'ToDate' => $toDate,
                'Customer' => '',
                'Service' => '',
                'Process' => '',
                'OriginCountry' => '',
                'DestinationCountry' => '',
                'InCoTerm' => '',
                'Status' => $status,
            ],
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES,
        );

        try {
            $pdo = DB::connection()->getPdo();
            $stmt = $pdo->prepare('SET NOCOUNT ON; EXEC dbo.sp_Quote_QuotationList ?');
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
                    if (empty($fetched)) {
                        continue;
                    }

                    $firstVal = array_values((array) $fetched[0])[0] ?? '';
                    if (is_string($firstVal) && str_starts_with(trim($firstVal), 'Failed')) {
                        Log::error('callQuotationListSP - SP failure', [
                            'status' => $status,
                            'message' => $firstVal,
                        ]);
                        break;
                    }

                    if (!isset($fetched[0]->QuoteNo)) {
                        continue;
                    }

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
            ->whereRaw('RTRIM(LTRIM(Code)) IN (' . implode(',', array_fill(0, count($allowed), '?')) . ')', $allowed)
            ->where('IsActive', 1)
            ->where('IsDeleted', 0)
            ->pluck('TeamCode')
            ->map(fn($c) => (string) $c)
            ->toArray();

        if (empty($allowedTeamCodes)) {
            return [];
        }

        // ── Step 2: Extract QuoteNos from SP rows ─────────────────────────────
        $quoteNos = array_values(
            array_filter(
                array_map(function ($row) {
                    return $row->QuoteNo ?? null;
                }, $rows),
            ),
        );

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
        $scoped = array_values(
            array_filter($rows, function ($row) use ($validQuoteNos) {
                $qn = $row->QuoteNo ?? null;
                return $qn && isset($validQuoteNos[$qn]);
            }),
        );

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

                $scoped = array_values(
                    array_filter($scoped, function ($row) use ($filterQuoteNos) {
                        return isset($filterQuoteNos[$row->QuoteNo]);
                    }),
                );
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
                return DataTables::of(DB::table('Quote_QuotationInfo')->whereRaw('1=0'))->make(true);
            }

            $requestedOffice = $request->input('office');

            // Validate requested office is within the user's allowed teamnames
            $office = $requestedOffice && $allowedTeamNames->contains($requestedOffice) ? $requestedOffice : null;

            Log::info('Quotation data request', [
                'days' => $days,
                'requested_office' => $requestedOffice,
                'resolved_office' => $office,
                'allowed_teamnames' => $allowedTeamNames->toArray(),
                'card_filter' => $cardFilter,
            ]);

            $query = DB::table('Quote_QuotationInfo')->select([
                'ID',
                'QuoteNo',
                'bookingno',
                'pri_partnermaster_name',
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
                'pri_id',
                'countryname',
                'ServiceVertical',
                'process',
                'incoterm',
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
                    $query
                        ->where(function ($q) {
                            $q->whereNotIn('QuoteStatus', [
                                'Won',
                                'Lost',
                                'Cancel',
                                'Completed',
                                'Failed Progress',
                            ])->orWhereNull('QuoteStatus');
                        })
                        ->where(function ($q) {
                            $q->whereNotIn('status', [
                                'Won',
                                'Lost',
                                'Cancel',
                                'Completed',
                                'Failed Progress',
                            ])->orWhereNull('status');
                        });
                    break;

                case 'won':
                    $query->where('QuoteStatus', 'Won')->whereNull('SCMINTStatus');
                    break;

                case 'completed':
                    $query
                        ->where(function ($q) {
                            $q->where('QuoteStatus', 'Won')->orWhere('status', 'Won');
                        })
                        ->where('SCMINTStatus', 'Success');
                    break;

                case 'failed_int':
                    $query
                        ->where(function ($q) {
                            $q->where('QuoteStatus', 'Won')->orWhere('status', 'Won');
                        })
                        ->whereNotNull('SCMINTStatus')
                        ->where('SCMINTStatus', '!=', 'Success');
                    break;

                case 'lost_canceled':
                    $query->where(function ($q) {
                        $q->whereIn('QuoteStatus', ['Lost', 'Cancel'])->orWhereIn('status', ['Lost', 'Cancel']);
                    });
                    break;

                case 'failed_progress':
                    $query->where(function ($q) {
                        $q->where('QuoteStatus', 'Failed Progress')->orWhere('status', 'Failed Progress');
                    });
                    break;
            }

            // ── DataTables (unchanged below this line) ────────────────
            return DataTables::of($query)
                ->filterColumn('OfficeName', fn($q, $k) => $q->where('teamname', 'like', "%$k%"))
                ->filterColumn('ClientName', fn($q, $k) => $q->where('pri_partnermaster_name', 'like', "%$k%"))
                ->filterColumn('ClientID', fn($q, $k) => $q->where('pri_id', 'like', "%$k%"))
                ->filterColumn('SalesPerson', fn($q, $k) => $q->where('username', 'like', "%$k%"))
                ->filterColumn('OriginCountry', fn($q, $k) => $q->where('origincountry', 'like', "%$k%"))
                ->filterColumn('DestinationCountry', fn($q, $k) => $q->where('destinationcountry', 'like', "%$k%"))
                ->filterColumn('BookingNo', fn($q, $k) => $q->where('bookingno', 'like', "%$k%"))
                ->filterColumn('QuotationStatus', function ($query, $keyword) {
                    $kw = strtolower(trim($keyword));
                    $query->where(function ($q) use ($kw) {
                        if (
                            str_contains($kw, 'progress') ||
                            str_contains($kw, 'pending') ||
                            str_contains($kw, 'proposal')
                        ) {
                            $q->where(function ($sub) {
                                $sub->whereIn('QuoteStatus', ['Proposal', 'Pending', 'In Progress'])
                                    ->orWhereIn('status', ['Proposal', 'Pending'])
                                    ->orWhereNull('QuoteStatus')
                                    ->orWhereNull('status');
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
                            $q->where('QuoteStatus', 'Won')
                                ->where('status', 'Won')
                                ->whereNotNull('SCMINTStatus')
                                ->where('SCMINTStatus', '!=', 'Success');
                        } else {
                            $q->where('QuoteStatus', 'like', "%$kw%")->orWhere('status', 'like', "%$kw%");
                        }
                    });
                })
                ->addColumn('report_date', fn($r) => $r->CreatedOn ? date('Y-m-d H:i', strtotime($r->CreatedOn)) : '-')
                ->addColumn('OfficeName', fn($r) => $r->teamname ?? '-')
                ->addColumn('ClientName', fn($r) => $r->pri_partnermaster_name ?? '-')
                ->addColumn('ClientID', fn($r) => $r->pri_id ?? '-')
                ->addColumn('SalesPerson', fn($r) => $r->username ?? '-')
                ->addColumn('OriginCountry', fn($r) => $r->origincountry ?? '-')
                ->addColumn('DestinationCountry', fn($r) => $r->destinationcountry ?? '-')
                ->addColumn('Service', fn($r) => $r->ServiceVertical ?? '-')
                ->addColumn('CompanyName', fn($r) => $r->pri_partnermaster_name ?? '-')
                ->addColumn('Process', fn($r) => $r->process ?? '-')
                ->addColumn('IncoTerm', fn($r) => $r->incoterm ?? '-')
                ->addColumn('BookingNo', fn($r) => $r->bookingno ?? '-')
                ->addColumn('QuotationStatus', function ($row) {
                    $status = $row->QuoteStatus ?? ($row->status ?? 'Proposal');
                    $scmStatus = $row->SCMINTStatus;
                    if ($status === 'Won' && $scmStatus && $scmStatus !== 'Success') {
                        return 'Failed Int.';
                    }
                    if ($status === 'Won' && $scmStatus === 'Success') {
                        return 'Completed';
                    }
                    if ($status === 'Won') {
                        return 'Won';
                    }
                    if ($status === 'Failed Progress') {
                        return 'Failed Progress';
                    }
                    if (in_array($status, ['Proposal', 'Pending', 'In Progress']) || is_null($status)) {
                        return 'In Progress';
                    }
                    if (in_array($status, ['Lost', 'Cancel'])) {
                        return 'Lost / Canceled';
                    }
                    return $status;
                })
                ->addColumn('remarks_error', function ($row) {
                    $raw = $row->QuoteStatusDescription ?? null;
                    if (!$raw) {
                        return '-';
                    }
                    $decoded = json_decode('[' . $raw . ']', true);
                    if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
                        return $raw;
                    }
                    return implode(' | ', array_column($decoded, 'Remark'));
                })
                ->addColumn('action', function ($row) use ($cardFilter) {
                    $status = $row->QuoteStatus ?? ($row->status ?? 'Proposal');
                    $scmStatus = $row->SCMINTStatus;

                    $isInProgress =
                        in_array($status, ['Pending', 'Proposal', 'In Progress', 'Failed Progress']) ||
                        is_null($status);
                    $isWon = $status === 'Won' && is_null($scmStatus);
                    $isFailedInt = $status === 'Won' && $scmStatus && $scmStatus !== 'Success';
                    $isCompleted = $status === 'Won' && $scmStatus === 'Success';

                    $viewBtn =
                        '<a href="' .
                        route('quotation.view', $row->ID) .
                        '" class="btn btn-info btn-sm" title="View"><i class="fas fa-eye"></i></a>';

                    $editBtn = '';
                    if ($cardFilter === 'in_progress' && $isInProgress) {
                        $editBtn =
                            ' <a href="' .
                            route('quotation.edit', $row->ID) .
                            '" class="btn btn-warning btn-sm" title="Edit"><i class="fas fa-edit"></i></a>';
                    }

                    $createBookingBtn = '';
                    if (($cardFilter === 'won' && $isWon) || ($cardFilter === 'failed_int' && $isFailedInt)) {
                        $createBookingBtn =
                            ' <button class="btn btn-success btn-sm btn-create-booking"
                        data-id="' .
                            $row->ID .
                            '"
                        data-booking="' .
                            $row->bookingno .
                            '"
                        data-team="' .
                            $row->teamid .
                            '"
                        data-quoteno="' .
                            $row->QuoteNo .
                            '"
                        data-origin=""
                        data-destination=""
                        data-status="' .
                            e($scmStatus ?? '') .
                            '"
                        title="Create Booking"><i class="fas fa-plus-circle"></i></button>';
                    }
                    return $viewBtn . $editBtn . $createBookingBtn;
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
     *   OLD: Hard-coded array passed every column on every save — partial saves
     *        from the show page would null-out columns not in the request.
     *
     *   NEW: Builds the update payload conditionally using $request->has().
     *        Only columns present in the incoming request are written.
     *        Columns absent from the request are left exactly as they are.
     * ─────────────────────────────────────────────────────────────────────────────
     */

    public function update(Request $request, string $id): JsonResponse
    {
        try {
            $quotation = DB::table('Quote_QuotationInfo')->where('ID', $id)->first();

            if (!$quotation) {
                return response()->json(
                    [
                        'success' => false,
                        'message' => 'Quotation not found',
                    ],
                    404,
                );
            }

            $submittedBy = Auth::check() ? Auth::user()->displayName ?? (Auth::user()->Email ?? 'system') : 'system';

            Log::info('Update Quotation - Starting', [
                'id' => $id,
                'booking_no' => $quotation->bookingno,
                'updated_by' => $submittedBy,
            ]);

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
                'pri_partnermaster_name' => 'pri_partnermaster_name',
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
                return response()->json(
                    [
                        'success' => false,
                        'message' => 'No updatable fields were provided.',
                    ],
                    422,
                );
            }

            DB::table('Quote_QuotationInfo')->where('ID', $id)->update($updateData);

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

            return response()->json(
                [
                    'success' => false,
                    'message' => $e->getMessage(),
                ],
                500,
            );
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
                return redirect()->route('quotation.index')->with('error', 'Quotation not found');
            }

            $officeShortCode = null;

            // Priority 1: use OfficeCode column directly if it's a 2-char code
            if ($quotation->OfficeCode && preg_match('/^[A-Z]{2}$/', trim($quotation->OfficeCode))) {
                $officeShortCode = trim($quotation->OfficeCode);
            }

            // Priority 2: resolve from OfficeID (UUID stored in OfficeID column)
            if (!$officeShortCode && $quotation->OfficeID) {
                $officeShortCode = DB::table('Cust_Office')
                    ->where('Id', $quotation->OfficeID)
                    ->where('IsActive', 1)
                    ->where('IsDeleted', 0)
                    ->value('Code');
            }

            // Priority 3: resolve from teamid via TeamCode
            if (!$officeShortCode && $quotation->teamid) {
                $officeShortCode = DB::table('Cust_Office')
                    ->where('TeamCode', $quotation->teamid)
                    ->where('IsActive', 1)
                    ->where('IsDeleted', 0)
                    ->value('Code');
            }

            // Priority 4: check OFFICE_BASE_CURRENCY map as last resort
            if (!$officeShortCode && $quotation->OfficeCode) {
                $officeShortCode = array_key_exists($quotation->OfficeCode, self::OFFICE_BASE_CURRENCY)
                    ? $quotation->OfficeCode
                    : null;
            }

            Log::info('Office short code resolved', [
                'id' => $quotation->ID,
                'teamid' => $quotation->teamid,
                'office_code_field' => $quotation->OfficeCode,
                'office_short_code' => $officeShortCode,
            ]);

            Log::info('Quotation data loaded', [
                'id' => $quotation->ID,
                'bookingno' => $quotation->bookingno,
                'pri_partnermaster_name' => $quotation->pri_partnermaster_name,
            ]);

            return view('quotation.edit', compact('quotation', 'officeShortCode'));
        } catch (\Exception $e) {
            Log::error('Error loading quotation for edit: ' . $e->getMessage(), [
                'id' => $id,
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()
                ->route('quotation.index')
                ->with('error', 'Failed to load quotation: ' . $e->getMessage());
        }
    }

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
                    ->orWhereRaw('LOWER(LTRIM(RTRIM(userPrincipalName))) = ?', [
                        strtolower(trim($authUser->Email ?? '')),
                    ])
                    ->orWhereRaw('LOWER(LTRIM(RTRIM(mail))) = ?', [strtolower(trim($authUser->Email ?? ''))]);
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
                $q->whereRaw('BizUserID = ?', [$authUser->ID])->orWhereRaw(
                    'LOWER(LTRIM(RTRIM(userPrincipalName))) = ?',
                    [strtolower(trim($authUser->Email ?? ''))],
                );
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
            $defaultOffice = !empty($data['BasedOfficeName']) ? trim($data['BasedOfficeName']) : null;

            $offices = collect();

            $baseCode = $data['Basedoffice'] ?? null;

            if ($defaultOffice) {
                $offices->push([
                    'code' => $baseCode,
                    'name' => $defaultOffice,
                ]);
            }

            if (!empty($data['OtherOffices']) && is_array($data['OtherOffices'])) {
                foreach ($data['OtherOffices'] as $other) {
                    if (!empty($other['OfficeName'])) {
                        $offices->push([
                            'code' => isset($other['OfficeCode']) ? trim($other['OfficeCode']) : null,
                            'name' => trim($other['OfficeName']),
                        ]);
                    }
                }
            }

            $offices = $offices->unique('name')->values();

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

    public function getOfficeList(): JsonResponse
    {
        $result = $this->resolveUserOfficesViaSP();
        $offices = $result['offices'] ?? collect();

        return response()->json([
            'success' => true,
            'offices' => $offices->values(),
        ]);
    }

    public function resolveAllowedOfficeCodesViaSP(): \Illuminate\Support\Collection
    {
        $resolved = $this->resolveUserOfficesViaSP();
        $offices = $resolved['offices'];

        if ($offices->isEmpty()) {
            return collect();
        }

        $names = $offices->pluck('name')->filter()->values()->toArray();

        return DB::table('Cust_Office')
            ->whereIn('Name', $names)
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
            return response()->json(
                [
                    'success' => false,
                    'message' => 'booking_no and charge_id are required',
                ],
                400,
            );
        }

        try {
            $supplement = DB::selectOne(
                "
                    SELECT
                        QC.QuotationInfoID,
                        QC.IsRevenue,
                        QC.Quantity,
                        ISNULL(QC.IncrementBy,   0)         AS IncrementBy,
                        ISNULL(QC.IncrementType, '')        AS IncrementType,
                        ISNULL(QC.Remarks,       '')        AS ChargeRemark,
                        ISNULL(CP.Code,          '')        AS PartyCode,
                        ISNULL(QC.ChargeType,    'manual')  AS SourceType,
                        ISNULL(CB.Code,          '')        AS BasedOnCode,
                        ISNULL(EQ.Code,          '')        AS UOMCode_Equipment,
                        ISNULL(UOM.Code,         '')        AS UOMCode_UOM,
                        ISNULL(QC.ToBeAdded,     0)         AS ToBeAdded
                    
                    FROM Quote_Quotation_ChargeSheet QC WITH (NOLOCK)
                    INNER JOIN Quote_QuotationInfo QI WITH (NOLOCK) ON QI.id = QC.QuotationInfoID
                    LEFT JOIN Cust_ChargecodeBasedOn          CB  WITH (NOLOCK) ON CB.Id  = QC.BasedOnId
                    LEFT JOIN [FITSCM].dbo.Cust_Equipment  EQ  WITH (NOLOCK) ON EQ.Id  = QC.UOMId AND QC.UOMType = 'Equipment'
                    LEFT JOIN [FITSCM].dbo.Cust_Party      CP  WITH (NOLOCK) ON CP.Id  = QC.PartyID
                    LEFT JOIN [FITSCM].dbo.Sys_UOM         UOM WITH (NOLOCK) ON UOM.Id = QC.UOMId AND QC.UOMType != 'Equipment'
                    WHERE QC.Id = ? AND QC.IsDeleted = 0
                ",
                [$chargeId],
            );

            if (!$supplement) {
                return response()->json(['success' => false, 'message' => 'Charge not found'], 404);
            }

            $isRevenue = (int) $supplement->IsRevenue;
            $quotationInfoId = $supplement->QuotationInfoID; // guaranteed to match SP's FK check

            // Fetch the whole charge list via the correct 1-parameter SP call,
            // then locate this specific charge in PHP. The SP returns all charges
            // for the quotation — there's no per-charge parameter.
            $pdo = DB::connection()->getPdo();
            $spResult = $this->fetchChargesFromSP($pdo, $quotationInfoId);
            $allCharges = $spResult['charges'];

            $chargeRow = null;
            foreach ($allCharges as $c) {
                if (
                    (string) ($c['CostChargeRowID'] ?? '') === (string) $chargeId ||
                    (string) ($c['RevenueChargeRowID'] ?? '') === (string) $chargeId
                ) {
                    $chargeRow = $c;
                    break;
                }
            }

            if (!$chargeRow) {
                return response()->json(['success' => false, 'message' => 'Charge not found via SP'], 404);
            }

            $mapped = $this->mapChargeRow($chargeRow, $isRevenue ? 'revenue' : 'cost', [
                (string) $chargeId => [
                    'category' => $chargeRow['ChargeCatagory'] ?? '',
                    'baseAmount' => (float) ($chargeRow['CostAmount'] ?? ($chargeRow['SellAmount'] ?? 0)),
                    'exChangeRate' => 1.0,
                ],
            ]);

            return response()->json([
                'success' => true,
                'id' => $mapped['id'],
                'chargeCode' => $mapped['chargeCode'],
                'chargeName' => $mapped['chargeName'],
                'rate' => $mapped['rate'],
                'quantity' => (float) ($supplement->Quantity ?? $mapped['quantity']),
                'amtInChgCurr' => $mapped['amtInChgCurr'],
                'amtInBaseCurr' => $mapped['amtInBaseCurr'],
                'exChangeRate' => $mapped['exChangeRate'],
                'chgCurr' => $mapped['chgCurr'],
                'basedOnCode' => $supplement->BasedOnCode ?? $mapped['basedOnCode'],
                'basedOnAndUOM' => $mapped['basedOnAndUOM'],
                'uomCode' => $supplement->UOMCode_Equipment ?: ($supplement->UOMCode_UOM ?: $mapped['uomCode']),
                'chargeRemark' => $supplement->ChargeRemark ?? $mapped['chargeRemark'],
                'partyCode' => $supplement->PartyCode ?? $mapped['partyCode'],
                'revenueOrCost' => $mapped['revenueOrCost'],
                'revenueId' => $mapped['revenueId'],
                'category' => $mapped['category'],
                'sourceType' => $supplement->SourceType ?? $mapped['sourceType'],
                'increaseBy' => (float) ($supplement->IncrementBy ?? $mapped['increaseBy']),
                'increaseType' => $supplement->IncrementType ?: $mapped['increaseType'],
                'toBeAdded' => (int) ($supplement->ToBeAdded ?? 0),
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

    /**
     * Call sp_Quote_Quotation_ChargeList with the correct signature.
     * The SP returns { Status, Remark, Charges: [...] } as a single JSON blob
     * in @OutputJson — not a tabular rowset — so we use a DECLARE … SELECT
     * pattern (same approach as getTermsTemplates / getCommunicationDescription)
     * to avoid PDO OUTPUT-binding length limits on NVARCHAR(MAX).
     *
     * Each item in Charges may have:
     *   - both CostChargeRowID and RevenueChargeRowID  → paired Cost+Sell row
     *   - only RevenueChargeRowID                      → Sell-only row
     *   - only CostChargeRowID                         → Cost-only row
     *
     * @return array  Raw Charges array from the SP
     * @throws \RuntimeException  When the SP reports a failure
     */
    private function fetchChargesFromSP(\PDO $pdo, string $quotationId): array
    {
        $sql = "
        SET NOCOUNT ON;
        DECLARE @OutputJson NVARCHAR(MAX);
        EXEC dbo.sp_Quote_Quotation_ChargeList
            @QuoteInfoID = ?,
            @QuoteChargeID = ?, 
            @OutputJson  = @OutputJson OUTPUT;
        SELECT @OutputJson AS OutputJson;
    ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$quotationId, null]);
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
            throw new \RuntimeException('sp_Quote_Quotation_ChargeList: no output returned');
        }

        $result = json_decode($outputJson, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::error('fetchChargesFromSP - JSON decode failed', [
                'quotation_id' => $quotationId,
                'raw' => $outputJson,
            ]);
            throw new \RuntimeException('sp_Quote_Quotation_ChargeList: invalid JSON response');
        }

        $status = $result['Status'] ?? '';
        $remark = $result['Remark'] ?? '';

        // SP returns 'No Record Found' when the quotation has no charges.
        // That is a valid empty state, not a system failure — return [].
        // We also stash the SP's own message so the caller can surface it.
        if ($status === 'Failed' && $remark === 'No Record Found') {
            return ['charges' => [], 'message' => $remark];
        }

        if ($status !== 'Success') {
            throw new \RuntimeException('sp_Quote_Quotation_ChargeList: ' . ($remark ?: 'Unknown failure'));
        }

        return ['charges' => $result['Charges'] ?? [], 'message' => null];
    }

    /**
     * Persist Workbook charges for a quotation via sp_Quote_DetailsUpdate
     * (OperationType: WorkbookCharges, Action: Add), replacing the old
     * session-only ContractTemplateController::store() flow. Route
     * quotation.contract-template.store — {quotation} comes from the URL,
     * never from the request body.
     */
    public function saveWorkbookCharges(Request $request, $quotation)
    {
        $validator = \Validator::make($request->all(), [
            'ContractID' => 'required|string',
            'Charges' => 'required|array|min:1',

            'Charges.*.ChargeCatagory' => 'required|string',
            'Charges.*.ChargeCode' => 'required|string',
            'Charges.*.BasedOnCode' => 'required|string',
            'Charges.*.ChgCurr' => 'required|string',

            'Charges.*.ChargeRowID' => 'nullable|string',
            'Charges.*.ChargeDescription' => 'nullable|string',
            'Charges.*.PartyCode' => 'nullable|string',
            'Charges.*.BasedOnUOM' => 'nullable|string',
            // Quantity / amounts can legitimately be null when the source
            // charge has no matching container in cargo (e.g. 20GP rate row
            // exists but only 40GP containers were quoted). Coerce to 0
            // below so the SP receives a numeric value it can compute with.
            'Charges.*.Quantity' => 'nullable|numeric',
            'Charges.*.CostRate' => 'nullable|numeric',
            'Charges.*.CostAmount' => 'nullable|numeric',
            'Charges.*.IncreaseBy' => 'nullable|numeric',
            'Charges.*.IncreaseType' => 'nullable|string',
            'Charges.*.SellRate' => 'nullable|numeric',
            'Charges.*.SellAmount' => 'nullable|numeric',
            'Charges.*.ChargeRemark' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(
                [
                    'success' => false,
                    'message' => $validator->errors()->first(),
                ],
                422,
            );
        }

        /*
         * The quotation ID comes from the route:
         * /quotation/{quotation}/contract-template
         *
         * Therefore we use $quotation instead of expecting
         * quotation_id inside the request body.
         */
        $quotationRow = DB::table('Quote_QuotationInfo')
            ->where('ID', $quotation)
            ->first(['ID', 'bookingno']);

        if (!$quotationRow) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Quotation not found.',
                ],
                404,
            );
        }

        $charges = $request->input('Charges', []);
        $contractId = $request->input('ContractID');

        /*
         * Build the JSON expected by sp_Quote_DetailsUpdate.
         */
        $inputJson = json_encode([
            'OperationType' => 'WorkbookCharges',
            'SubmittedDatetime' => now()->format('YmdHis'),

            'SubmittedBy' => Auth::check() ? Auth::user()->displayName ?? (Auth::user()->Email ?? 'system') : 'system',

            'Action' => 'Add',

            'QuotationNo' => $quotationRow->bookingno ?? '',
            'QuotationInfoID' => $quotationRow->ID,
            'ContractID' => $contractId,

            'Charges' => array_map(function ($c) {
                $num = function ($v) {
                    return is_numeric($v) ? (float) $v : 0.0;
                };
                $str = function ($v) {
                    return is_string($v) ? $v : '';
                };

                return [
                    'ChargeCatagory' => $str($c['ChargeCatagory'] ?? ''),
                    'ChargeRowID' => $str($c['ChargeRowID'] ?? ''),
                    'ChargeCode' => $str($c['ChargeCode'] ?? ''),
                    'ChargeDescription' => $str($c['ChargeDescription'] ?? ''),
                    'PartyCode' => $str($c['PartyCode'] ?? ''),
                    'ChgCurr' => $str($c['ChgCurr'] ?? ''),
                    'BasedOnCode' => $str($c['BasedOnCode'] ?? ''),
                    'BasedOnUOM' => $str($c['BasedOnUOM'] ?? ''),

                    'Quantity' => $num($c['Quantity'] ?? null),
                    'CostRate' => $num($c['CostRate'] ?? null),
                    'CostAmount' => $num($c['CostAmount'] ?? null),
                    'IncreaseBy' => $num($c['IncreaseBy'] ?? null),
                    'IncreaseType' => $str($c['IncreaseType'] ?? 'Percentage') ?: 'Percentage',
                    'SellRate' => $num($c['SellRate'] ?? null),
                    'SellAmount' => $num($c['SellAmount'] ?? null),

                    'ChargeRemark' => $str($c['ChargeRemark'] ?? ''),
                ];
            }, $charges),
        ]);

        Log::info('Executing sp_Quote_DetailsUpdate for WorkbookCharges', [
            'quotation_id' => $quotationRow->ID,
            'contract_id' => $contractId,
            'charge_count' => count($charges),
            'input_json' => $inputJson,
        ]);

        try {
            $pdo = DB::connection()->getPdo();

            // sp_Quote_DetailsUpdate declares @InputJson VARCHAR(2000) — the
            // workbook payload with multiple charges overflows it and gets
            // silently truncated, which fails JSON parsing inside the SP with
            // "Unexpected character at position 19xx". Call the child SP
            // directly; it accepts varchar(max). Same downstream behaviour.
            $stmt = $pdo->prepare(
                "SET NOCOUNT ON;\n" .
                    "DECLARE @OutputJson VARCHAR(MAX);\n" .
                    "EXEC sp_Quote_WorkbookCharges_Add ?, @OutputRemarkJson = @OutputJson OUTPUT;\n" .
                    'SELECT @OutputJson AS OutputJson;',
            );

            $stmt->execute([$inputJson]);

            $outputJson = null;

            /*
             * The stored procedure can return multiple result sets.
             * Continue through them until we find OutputJson.
             */
            do {
                $rows = $stmt->fetchAll(\PDO::FETCH_OBJ);

                if (!empty($rows) && isset($rows[0]->OutputJson)) {
                    $outputJson = $rows[0]->OutputJson;
                    break;
                }
            } while ($stmt->nextRowset());
        } catch (\Exception $e) {
            Log::error('sp_Quote_DetailsUpdate execution failed', [
                'quotation_id' => $quotationRow->ID,
                'contract_id' => $contractId,
                'error' => $e->getMessage(),
            ]);

            return response()->json(
                [
                    'success' => false,
                    'message' => 'Failed to save workbook charges.',
                ],
                500,
            );
        }

        if (empty($outputJson)) {
            Log::error('sp_Quote_DetailsUpdate returned no output', [
                'quotation_id' => $quotationRow->ID,
                'contract_id' => $contractId,
            ]);

            return response()->json(
                [
                    'success' => false,
                    'message' => 'No response from stored procedure.',
                ],
                500,
            );
        }

        /*
         * Decode the stored procedure response.
         */
        $result = json_decode($outputJson, true);

        if (!is_array($result)) {
            Log::error('Invalid JSON returned by sp_Quote_DetailsUpdate', [
                'quotation_id' => $quotationRow->ID,
                'output_json' => $outputJson,
            ]);

            return response()->json(
                [
                    'success' => false,
                    'message' => 'Invalid response from stored procedure.',
                ],
                500,
            );
        }

        $status = $result['Status'] ?? 'Failed';

        $remarks = array_column($result['Remarks'] ?? [], 'Remark');

        if (empty($remarks)) {
            $remarks = array_column($result['Remarks'] ?? [], 'Remarks');
        }

        /*
         * Stored procedure returned a failure.
         */
        if (strcasecmp($status, 'success') !== 0) {
            Log::warning('Workbook charges save failed', [
                'quotation_id' => $quotationRow->ID,
                'contract_id' => $contractId,
                'status' => $status,
                'remarks' => $remarks,
            ]);

            return response()->json(
                [
                    'success' => false,
                    'message' => $remarks[0] ?? 'Failed to save workbook charges.',
                    'errors' => $remarks,
                ],
                422,
            );
        }

        Log::info('Workbook charges saved successfully', [
            'quotation_id' => $quotationRow->ID,
            'contract_id' => $contractId,
            'charge_count' => count($charges),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Workbook charges saved successfully.',
        ]);
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
                    ->first([
                        'ID',
                        'teamid',
                        'ServiceVertical',
                        'origincountry',
                        'destinationcountry',
                        'QuoteStatus',
                        'status',
                    ])
                : DB::table('Quote_QuotationInfo')
                    ->where('bookingno', $bookingNo)
                    ->when($officeCodeFromRequest, fn($q) => $q->where('OfficeCode', $officeCodeFromRequest))
                    ->first([
                        'ID',
                        'teamid',
                        'ServiceVertical',
                        'origincountry',
                        'destinationcountry',
                        'QuoteStatus',
                        'status',
                    ]);

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
                'user' => Auth::user()->displayName ?? (Auth::user()->Email ?? 'system'),
                'context' => [
                    'quotation_id' => $quotation->ID ?? 'N/A',
                    'office_code' => $officeCode,
                    'service_type' => $quotation->ServiceVertical ?? 'N/A',
                    'status' => $quotation->QuoteStatus ?? ($quotation->status ?? 'Unknown'),
                ],
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
                    'request_data' => $request->all(),
                ]);

                return response()->json(
                    [
                        'success' => false,
                        'message' => 'Validation failed: ' . $validator->errors()->first(),
                        'errors' => $validator->errors()->toArray(),
                    ],
                    422,
                );
            }

            // ============================================
            // VERIFY QUOTATION EXISTS
            // ============================================

            if (!$quotation) {
                Log::error('Save Charge - Quotation not found', [
                    'booking_no' => $bookingNo,
                    'action' => $request->input('action'),
                    'user' => Auth::user()->displayName ?? (Auth::user()->Email ?? 'system'),
                ]);

                return response()->json(
                    [
                        'success' => false,
                        'message' => 'Quotation not found for booking number: ' . $bookingNo,
                    ],
                    404,
                );
            }

            // Validate RevenueOrCost matches exactly what the SP expects
            $validChargeTypes = ['Revenue', 'Cost', 'Cost with Markup'];
            if (!in_array($request->input('revenue_or_cost'), $validChargeTypes)) {
                return response()->json(
                    [
                        'success' => false,
                        'message' => 'Invalid charge type. Must be Revenue, Cost, or Cost with Markup',
                    ],
                    422,
                );
            }

            // ============================================
            // DEBUG AUTH USER
            // ============================================

            $user = Auth::user();
            Log::info('Current authenticated user', [
                'user' => $user
                    ? [
                        'id' => $user->id ?? null,
                        'username' => $user->username ?? null,
                        'email' => $user->email ?? null,
                        'name' => $user->name ?? null,
                    ]
                    : 'No user authenticated',
            ]);

            // Amounts are server-owned: never trust a browser-supplied base amount or FX rate.
            $chgCurr = $request->input('chg_curr') ?: 'KES';
            $baseCurrency = self::OFFICE_BASE_CURRENCY[$officeCode] ?? null;

            if (!$baseCurrency) {
                Log::error('saveCharge - No base currency mapped for office', [
                    'office_code' => $officeCode,
                    'booking_no' => $bookingNo,
                ]);
                return response()->json(
                    [
                        'success' => false,
                        'message' => "No base currency configured for office '{$officeCode}'. Please contact your administrator.",
                    ],
                    422,
                );
            }
            $quantity = (float) $request->input('quantity', 0);
            $rate = (float) $request->input('rate', 0);

            // Amount is authoritative from the client — the modal already computed
            // Qty × Rate and displayed it to the user. Re-deriving it here duplicates
            // that logic and overwrites the value the user actually saw/approved.
            $amtInChgCurr = (float) $request->input('amt_in_chg_curr', 0);

            // Fallback only when the client sent nothing (e.g. legacy callers)
            if ($amtInChgCurr === 0.0 && $quantity > 0 && $rate > 0) {
                $amtInChgCurr = round($quantity * $rate, 2);
            }

            $exchangeRate = 1.0;
            $amtInBaseCurr = $amtInChgCurr;

            if ($request->input('action') !== 'Delete') {
                if ($chgCurr !== $baseCurrency) {
                    $resolvedRate = $this->resolveExchangeRate(
                        $officeCode,
                        $chgCurr,
                        $baseCurrency,
                        now()->format('d/M/Y'),
                    );

                    if (!$resolvedRate) {
                        return response()->json(
                            [
                                'success' => false,
                                'message' => "No exchange rate found for {$chgCurr} → {$baseCurrency}",
                            ],
                            422,
                        );
                    }
                    $exchangeRate = $resolvedRate;
                }

                // Amounts come from the request as-is — the client (or SP) is the source of truth.
                // Base amount = charge amount × FX rate only.
                // Do NOT re-derive from rate × quantity or apply markup — the client
                // already computed the marked-up amount and the SP stores what it receives.
                $amtInBaseCurr = round($amtInChgCurr * $exchangeRate, 2);
            }

            $inputJson = json_encode([
                'OperationType' => 'QuotationAddorEditCharge',
                'SubmittedDatetime' => now()->format('YmdHis'),
                'SubmittedBy' => Auth::check()
                    ? Auth::user()->displayName ?? (Auth::user()->Email ?? 'system')
                    : 'system',
                'Action' => $request->input('action', 'Add'),

                'QuotationID' => $quotation->ID ?? '',

                'QuoteChargeID' => $request->input('quote_charge_id', ''),
                'PartyCode' => $request->input('party_code', ''),
                'RevenueOrCost' => $request->input('revenue_or_cost'),
                'ChargeCode' => $request->input('charge_code'),
                'Quantity' => (float) $request->input('quantity', 0),
                'Rate' => (float) $request->input('rate', 0),
                'AmtInChgCurr' => $amtInChgCurr,
                'AmtInBaseCurr' => $amtInBaseCurr,
                'ExChangeRate' => $exchangeRate,
                'ChgCurr' => $chgCurr,
                'BasedOnCode' => $request->input('based_on_code'),
                'BasedOnUOM' => $request->input('based_on_uom', ''),
                'ChargeCategory' => $request->input('charge_category', $request->input('category', '')),
                'Category' => $request->input('category', $request->input('charge_category', '')),
                'ChargeCatagory' => $request->input('charge_category', $request->input('category', '')),
                'ChargeRemark' => $request->input('charge_remark', ''),
                'ToBeAdded' => (int) $request->input('to_be_added', 0),
                'RevenueChargeID' => $request->input('revenue_charge_id') ?: null,
                'IncreaseBy' => (float) $request->input('increase_by', 0),
                'IncreaseType' => $request->input('increase_type', 'Percentage'),
                'SourceType' => ucfirst(strtolower($request->input('source_type', 'manual'))),
            ]);

            Log::info('Stored Procedure Input JSON', [
                'booking_no' => $bookingNo,
                'action' => $request->input('action'),
                'json' => $inputJson,
                'json_decoded' => json_decode($inputJson, true),
            ]);

            // ============================================

            // EXECUTE STORED PROCEDURE WITH PDO

            // ============================================
            Log::info('Executing Stored Procedure: sp_Quote_Quotaiton_ChargesAddorEdit', [
                'booking_no' => $bookingNo,
                'office_code' => $officeCode,
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
                    'input_json' => $inputJson,
                ]);

                return response()->json(
                    [
                        'success' => false,
                        'message' => 'Failed to execute stored procedure',
                        'error_details' => config('app.debug') ? $errorInfo[2] : null,
                    ],
                    500,
                );
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
                        'first_row' => !empty($rows) ? (array) $rows[0] : null,
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
                                (is_string($value) &&
                                    (strpos($value, 'Error Message') !== false || strpos($value, 'Failed -') !== false))
                            ) {
                                Log::error('Stored procedure returned error object:', [
                                    'booking_no' => $bookingNo,
                                    'result_set' => $resultSetCount,
                                    'error_property_name' => $key,
                                    'error_message' => $value,
                                    'all_properties' => $properties,
                                    'context' => [
                                        'office_code' => $officeCode,
                                        'service_type' => $quotation->ServiceVertical ?? 'N/A',
                                        'action' => $request->input('action'),
                                    ],
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
                                'booking_no' => $bookingNo,
                            ]);
                            break;
                        }
                    }
                } catch (\PDOException $e) {
                    // Skip empty result sets
                    Log::debug("Result set #$resultSetCount error (skipping)", [
                        'booking_no' => $bookingNo,
                        'error' => $e->getMessage(),
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
                    'probable_cause' => 'Stored procedure may have failed silently or returned unexpected format',
                ]);

                return response()->json(
                    [
                        'success' => false,
                        'message' => 'Stored procedure did not return expected output',
                    ],
                    500,
                );
            }

            // ============================================
            // LOG RAW STORED PROCEDURE OUTPUT
            // ============================================

            Log::info('Stored Procedure Raw Output', [
                'booking_no' => $bookingNo,
                'result' => $result,
                'output_json' => $result[0]->OutputJson ?? null,
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
                    ],
                ]);

                return response()->json(
                    [
                        'success' => false,
                        'message' => 'Failed to parse stored procedure response: ' . json_last_error_msg(),
                        'error_details' => config('app.debug') ? $outputJson : null,
                    ],
                    500,
                );
            }

            Log::info('Stored Procedure Parsed Output', [
                'booking_no' => $bookingNo,
                'output' => $output,
                'status' => $output['Status'] ?? 'Unknown',
                'remarks' => $output['Remarks'] ?? [],
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
                    'amount' => $amtInBaseCurr,
                    'user' => Auth::user()->displayName ?? (Auth::user()->Email ?? 'system'),
                ]);

                return response()->json([
                    'success' => true,
                    'message' => $output['Remarks'][0]['Remark'] ?? 'Charge saved successfully',
                    'charge_id' => $output['ChargeID'] ?? null,
                    'data' => $output,
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
                ],
            ]);

            return response()->json(
                [
                    'success' => false,
                    'message' => $errorMessage,
                    'details' => $output['Remarks'] ?? [],
                ],
                422,
            );
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
                'request_data' => $request->all(),
            ]);

            return response()->json(
                [
                    'success' => false,
                    'message' => 'Database connection error while saving charge',
                    'error_code' => 'PDO_ERROR',
                    'details' => config('app.debug')
                        ? [
                            'message' => $e->getMessage(),
                            'sql_state' => $e->errorInfo[0] ?? null,
                        ]
                        : null,
                ],
                500,
            );
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
                'request_data' => $request->all(),
            ]);

            return response()->json(
                [
                    'success' => false,
                    'message' => 'Database query error while saving charge',
                    'error_code' => 'QUERY_ERROR',
                    'details' => config('app.debug')
                        ? [
                            'message' => $e->getMessage(),
                            'file' => $e->getFile(),
                            'line' => $e->getLine(),
                        ]
                        : null,
                ],
                500,
            );
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
                'request_data' => $request->all(),
            ]);

            return response()->json(
                [
                    'success' => false,
                    'message' => 'Failed to save charge: ' . $e->getMessage(),
                    'error_code' => 'GENERAL_ERROR',
                    'details' => config('app.debug')
                        ? [
                            'file' => $e->getFile(),
                            'line' => $e->getLine(),
                        ]
                        : null,
                ],
                500,
            );
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
                    'user' => Auth::user()->displayName ?? (Auth::user()->Email ?? 'system'),
                ]);

                return response()->json(
                    [
                        'success' => false,
                        'message' => !$bookingNo ? 'Booking number is required' : 'Charge ID is required',
                    ],
                    400,
                );
            }

            // ============================================
            // GET QUOTATION CONTEXT FOR DETAILED LOGGING
            // ============================================

            $officeCodeFromRequest = $request->input('office_code');

            $quotation = DB::table('Quote_QuotationInfo')
                ->where('bookingno', $bookingNo)
                ->when($officeCodeFromRequest, fn($q) => $q->where('OfficeCode', $officeCodeFromRequest))
                ->first([
                    'ID',
                    'teamid',
                    'ServiceVertical',
                    'origincountry',
                    'destinationcountry',
                    'QuoteStatus',
                    'status',
                ]);

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
                'user' => Auth::user()->displayName ?? (Auth::user()->Email ?? 'system'),
                'context' => [
                    'quotation_id' => $quotation->ID ?? 'N/A',
                    'office_code' => $officeCode,
                    'service_type' => $quotation->ServiceVertical ?? 'N/A',
                    'origin' => $quotation->origincountry ?? 'N/A',
                    'destination' => $quotation->destinationcountry ?? 'N/A',
                    'current_status' => $quotation->QuoteStatus ?? ($quotation->status ?? 'Unknown'),
                ],
            ]);

            // ============================================
            // VERIFY QUOTATION EXISTS
            // ============================================
            if (!$quotation) {
                Log::error('Delete Charge - Quotation not found', [
                    'booking_no' => $bookingNo,
                    'charge_id' => $chargeId,
                    'user' => Auth::user()->displayName ?? (Auth::user()->Email ?? 'system'),
                ]);

                return response()->json(
                    [
                        'success' => false,
                        'message' => 'Quotation not found for booking number: ' . $bookingNo,
                    ],
                    404,
                );
            }

            // ============================================
            // FETCH CHARGE DETAILS WITH ENHANCED VALIDATION
            // ============================================

            $charge = DB::selectOne(
                "
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
            LEFT JOIN [FITSCM].dbo.Bill_ChargeCode BC WITH (NOLOCK) ON BC.Id = QC.ChargeCodeId
            LEFT JOIN [FITSCM].dbo.Cust_Currency CC WITH (NOLOCK) ON CC.Id = QC.EstChargeCurrencyId
            LEFT JOIN Cust_ChargecodeBasedOn CB WITH (NOLOCK) ON CB.Id = QC.BasedOnId
            LEFT JOIN [FITSCM].dbo.Sys_UOM SU WITH (NOLOCK) ON SU.Id = QC.UOMId
            LEFT JOIN [FITSCM].dbo.Cust_Party CP WITH (NOLOCK) ON CP.Id = QC.PartyID
            WHERE QC.Id = ?
                AND QC.IsDeleted = 0
            ",
                [$chargeId],
            );

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
                        (is_string($value) &&
                            (strpos($value, 'Error Message') !== false || strpos($value, 'Failed -') !== false))
                    ) {
                        Log::error('Delete Charge - Charge lookup returned error object:', [
                            'booking_no' => $bookingNo,
                            'charge_id' => $chargeId,
                            'error_property_name' => $key,
                            'error_message' => $value,
                            'all_properties' => $properties,
                            'context' => [
                                'office_code' => $officeCode,
                                'service_type' => $quotation->ServiceVertical ?? 'N/A',
                            ],
                            'probable_cause' => 'Database schema mismatch or invalid charge ID',
                        ]);

                        return response()->json(
                            [
                                'success' => false,
                                'message' => 'Failed to retrieve charge details for deletion',
                                'error_details' => config('app.debug') ? $value : null,
                            ],
                            500,
                        );
                    }
                }
            }

            if (!$charge) {
                Log::warning('Delete Charge - Charge not found or already deleted', [
                    'charge_id' => $chargeId,
                    'booking_no' => $bookingNo,
                    'office_code' => $officeCode,
                    'user' => Auth::user()->displayName ?? (Auth::user()->Email ?? 'system'),
                ]);

                return response()->json(
                    [
                        'success' => false,
                        'message' => 'Charge not found or already deleted',
                    ],
                    404,
                );
            }

            // ============================================
            // PREPARE DELETE REQUEST
            // ============================================

            $submittedBy = Auth::check() ? Auth::user()->displayName ?? (Auth::user()->Email ?? 'system') : 'system';

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
                'SourceType' => $charge->SourceType ?? 'manual',
            ]);

            Log::info('Delete Charge - Stored Procedure Input', [
                'booking_no' => $bookingNo,
                'charge_id' => $chargeId,
                'charge_code' => $charge->ChargeCode,
                'is_revenue' => $charge->IsRevenue,
                'user' => $submittedBy,
                'office_code' => $officeCode,
                'json' => $inputJson,
            ]);

            // ============================================
            // EXECUTE STORED PROCEDURE WITH PDO
            // ============================================

            Log::info('Delete Charge - Executing Stored Procedure: sp_Quote_Quotaiton_ChargesAddorEdit', [
                'booking_no' => $bookingNo,
                'charge_id' => $chargeId,
                'office_code' => $officeCode,
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
                    ],
                ]);

                return response()->json(
                    [
                        'success' => false,
                        'message' => 'Failed to execute delete stored procedure',
                        'error_details' => config('app.debug') ? $errorInfo[2] : null,
                    ],
                    500,
                );
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
                        'first_row' => !empty($rows) ? (array) $rows[0] : null,
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
                                (is_string($value) &&
                                    (strpos($value, 'Error Message') !== false || strpos($value, 'Failed -') !== false))
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
                                    ],
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
                                'charge_id' => $chargeId,
                            ]);
                            break;
                        }
                    }
                } catch (\PDOException $e) {
                    Log::debug("Delete Charge - Result set #$resultSetCount error (skipping)", [
                        'booking_no' => $bookingNo,
                        'charge_id' => $chargeId,
                        'error' => $e->getMessage(),
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
                    'probable_cause' => 'Stored procedure may have failed silently or returned unexpected format',
                ]);

                return response()->json(
                    [
                        'success' => false,
                        'message' => 'Stored procedure did not return expected output',
                    ],
                    500,
                );
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
                    ],
                ]);

                return response()->json(
                    [
                        'success' => false,
                        'message' => 'Failed to parse stored procedure response: ' . json_last_error_msg(),
                        'error_details' => config('app.debug') ? $outputJson : null,
                    ],
                    500,
                );
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
                    'office_code' => $officeCode,
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Charge deleted successfully',
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
                    'user' => $submittedBy,
                ],
            ]);

            return response()->json(
                [
                    'success' => false,
                    'message' => $errorMessage,
                    'details' => $output['Remarks'] ?? [],
                ],
                422,
            );
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
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(
                [
                    'success' => false,
                    'message' => 'Database connection error while deleting charge',
                    'error_code' => 'PDO_ERROR',
                    'details' => config('app.debug')
                        ? [
                            'message' => $e->getMessage(),
                            'sql_state' => $e->errorInfo[0] ?? null,
                        ]
                        : null,
                ],
                500,
            );
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
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(
                [
                    'success' => false,
                    'message' => 'Database query error while deleting charge',
                    'error_code' => 'QUERY_ERROR',
                    'details' => config('app.debug')
                        ? [
                            'message' => $e->getMessage(),
                            'file' => $e->getFile(),
                            'line' => $e->getLine(),
                        ]
                        : null,
                ],
                500,
            );
        } catch (\Exception $e) {
            Log::error('Delete Charge - General exception', [
                'booking_no' => $request->input('booking_no'),
                'charge_id' => $request->input('quote_charge_id'),
                'error_message' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(
                [
                    'success' => false,
                    'message' => 'Failed to delete charge: ' . $e->getMessage(),
                ],
                500,
            );
        }
    }

    public function updateStatus(Request $request)
    {
        try {
            $bookingNo = $request->input('booking_no');
            $quotationId = $request->input('quotation_id');
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
                    'user' => Auth::user()->displayName ?? (Auth::user()->Email ?? 'system'),
                ]);

                return response()->json(
                    [
                        'success' => false,
                        'message' => !$bookingNo ? 'Booking number is required' : 'Status is required',
                    ],
                    400,
                );
            }

            if (!in_array($targetStatus, ['Won', 'Lost', 'Cancel'])) {
                Log::warning('Update Status - Invalid status value', [
                    'booking_no' => $bookingNo,
                    'target_status' => $targetStatus,
                    'valid_statuses' => ['Won', 'Lost', 'Cancel'],
                    'user' => Auth::user()->displayName ?? (Auth::user()->Email ?? 'system'),
                ]);

                return response()->json(
                    [
                        'success' => false,
                        'message' => 'Invalid status. Must be Won, Lost, or Cancel',
                    ],
                    400,
                );
            }

            $submittedBy = Auth::check() ? Auth::user()->displayName ?? (Auth::user()->Email ?? 'system') : 'system';

            // ============================================
            // GET QUOTATION WITH FULL CONTEXT
            // Prefer quotation_id (UUID) over bookingno — bookingno is not
            // unique across offices and causes multi-row matches.
            // ============================================

            $query = DB::table('Quote_QuotationInfo');

            if ($quotationId) {
                $query->where('ID', $quotationId);
            } else {
                $query->where('bookingno', $bookingNo);
            }

            $quotation = $query->first([
                'ID',
                'teamid',
                'QuoteStatus',
                'status',
                'ServiceVertical',
                'origincountry',
                'destinationcountry',
                'pri_partnermaster_name',
            ]);

            if (!$quotation) {
                Log::error('Update Status - Quotation not found', [
                    'booking_no' => $bookingNo,
                    'quotation_id' => $quotationId,
                    'target_status' => $targetStatus,
                    'user' => $submittedBy,
                ]);

                return response()->json(
                    [
                        'success' => false,
                        'message' => 'Quotation not found',
                    ],
                    404,
                );
            }

            $officeCode = 'Unknown';
            $office = DB::table('Cust_Office')
                ->where('TeamCode', $quotation->teamid)
                ->where('IsActive', 1)
                ->where('IsDeleted', 0)
                ->first(['Code']);

            if ($office) {
                $officeCode = trim($office->Code);
            }

            $currentStatus = $quotation->QuoteStatus ?? ($quotation->status ?? 'Proposal');

            Log::info('Update Status - Starting', [
                'booking_no' => $bookingNo,
                'current_status' => $currentStatus,
                'target_status' => $targetStatus,
                'user' => $submittedBy,
                'context' => [
                    'quotation_id' => $quotation->ID,
                    'office_code' => $officeCode,
                    'service_type' => $quotation->ServiceVertical ?? 'N/A',
                    'company' => $quotation->pri_partnermaster_name ?? 'N/A',
                    'origin' => $quotation->origincountry ?? 'N/A',
                    'destination' => $quotation->destinationcountry ?? 'N/A',
                    'reason_code' => $reasonCode ?: 'N/A',
                    'reason_remark' => $reasonRemark ?: 'N/A',
                ],
            ]);

            // ============================================
            // VALIDATE REASON CODE FOR LOST / CANCEL
            // ============================================

            if (empty($reasonCode) && $targetStatus !== 'Won') {
                return response()->json(
                    [
                        'success' => false,
                        'message' => 'Reason code is required for ' . $targetStatus . ' status',
                    ],
                    400,
                );
            }

            // ============================================
            // INTERMEDIATE STATUS TRANSITION IF NEEDED
            // ============================================

            $needsPendingFirst = false;

            if ($targetStatus === 'Lost' && $currentStatus !== 'In Progress') {
                $needsPendingFirst = true;
                Log::info('Update Status - Lost requires In Progress first', [
                    'booking_no' => $bookingNo,
                    'current_status' => $currentStatus,
                    'office_code' => $officeCode,
                ]);
            } elseif ($targetStatus === 'Cancel' && !in_array($currentStatus, ['In Progress', 'Won'])) {
                $needsPendingFirst = true;
                Log::info('Update Status - Cancel requires In Progress/Won first', [
                    'booking_no' => $bookingNo,
                    'current_status' => $currentStatus,
                    'office_code' => $officeCode,
                ]);
            }

            if ($needsPendingFirst) {
                try {
                    // Scope by ID to avoid partial bookingno matches
                    $pendingRows = DB::table('Quote_QuotationInfo')
                        ->where('ID', $quotation->ID)
                        ->update([
                            'QuoteStatus' => 'In Progress',
                            'status' => 'In Progress',
                            'LastModifiedBy' => $submittedBy,
                            'updatedon' => now(),
                        ]);

                    Log::info('Update Status - Intermediate In Progress update', [
                        'booking_no' => $bookingNo,
                        'previous_status' => $currentStatus,
                        'rows_updated' => $pendingRows,
                        'target_status' => $targetStatus,
                        'office_code' => $officeCode,
                    ]);
                } catch (\Exception $e) {
                    Log::error('Update Status - Failed to set intermediate In Progress status', [
                        'booking_no' => $bookingNo,
                        'error_message' => $e->getMessage(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                        'office_code' => $officeCode,
                    ]);

                    return response()->json(
                        [
                            'success' => false,
                            'message' => 'Failed to prepare status transition: ' . $e->getMessage(),
                        ],
                        500,
                    );
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
                'QuotationID' => $quotation->ID,
                'Status' => $targetStatus,
                'ReasonCode' => $reasonCode,
                'ReasonRemark' => $reasonRemark,
            ]);

            Log::info('Update Status - Executing SP', [
                'booking_no' => $bookingNo,
                'target_status' => $targetStatus,
                'office_code' => $officeCode,
                'json' => $inputJson,
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

                if ($needsPendingFirst) {
                    DB::table('Quote_QuotationInfo')
                        ->where('ID', $quotation->ID)
                        ->update([
                            'QuoteStatus' => $currentStatus,
                            'status' => $currentStatus,
                            'LastModifiedBy' => $submittedBy,
                            'updatedon' => now(),
                        ]);

                    Log::info('Update Status - Rolled back intermediate status after SP execute failure', [
                        'booking_no' => $bookingNo,
                        'restored_status' => $currentStatus,
                    ]);
                }

                Log::error('Update Status - SP execution failed', [
                    'booking_no' => $bookingNo,
                    'target_status' => $targetStatus,
                    'error_code' => $errorInfo[0] ?? 'Unknown',
                    'sql_state' => $errorInfo[1] ?? 'Unknown',
                    'error_message' => $errorInfo[2] ?? 'Unknown',
                    'office_code' => $officeCode,
                ]);

                return response()->json(
                    [
                        'success' => false,
                        'message' => 'Failed to execute status update procedure',
                        'error_details' => config('app.debug') ? $errorInfo[2] : null,
                    ],
                    500,
                );
            }

            // ============================================
            // PROCESS RESULT SETS
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
                        'first_row' => !empty($rows) ? (array) $rows[0] : null,
                    ]);

                    if (!empty($rows) && is_object($rows[0])) {
                        $properties = get_object_vars($rows[0]);
                        $hasError = false;

                        foreach ($properties as $key => $value) {
                            if (
                                empty($key) ||
                                (is_string($value) &&
                                    (strpos($value, 'Error Message') !== false || strpos($value, 'Failed -') !== false))
                            ) {
                                Log::error('Update Status - SP returned error object in result set', [
                                    'booking_no' => $bookingNo,
                                    'target_status' => $targetStatus,
                                    'result_set' => $resultSetCount,
                                    'error_property_name' => $key,
                                    'error_message' => $value,
                                    'all_properties' => $properties,
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
                            Log::info('Update Status - Found OutputJson in result set #' . $resultSetCount, [
                                'booking_no' => $bookingNo,
                            ]);
                            break;
                        }
                    }
                } catch (\PDOException $e) {
                    Log::debug("Update Status - Result set #$resultSetCount skipped", [
                        'booking_no' => $bookingNo,
                        'error' => $e->getMessage(),
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
                ]);

                return response()->json(
                    [
                        'success' => false,
                        'message' => 'Stored procedure did not return expected output',
                    ],
                    500,
                );
            }

            // ============================================
            // PARSE SP OUTPUT
            // ============================================

            $outputJson = $result[0]->OutputJson ?? '{}';
            $output = json_decode($outputJson, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('Update Status - Failed to decode SP output', [
                    'booking_no' => $bookingNo,
                    'target_status' => $targetStatus,
                    'json_error' => json_last_error_msg(),
                    'raw_output' => $outputJson,
                ]);

                return response()->json(
                    [
                        'success' => false,
                        'message' => 'Failed to parse stored procedure response: ' . json_last_error_msg(),
                        'error_details' => config('app.debug') ? $outputJson : null,
                    ],
                    500,
                );
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
                    'company' => $quotation->pri_partnermaster_name ?? 'N/A',
                ]);

                return response()->json([
                    'success' => true,
                    'message' => $output['Remark'] ?? 'Status updated successfully',
                ]);
            }

            // ============================================
            // HANDLE SP FAILURE — ROLLBACK IF NEEDED
            // ============================================

            $errorMessage = $output['Remark'] ?? 'Failed to update status';

            if ($needsPendingFirst) {
                try {
                    DB::table('Quote_QuotationInfo')
                        ->where('ID', $quotation->ID)
                        ->update([
                            'QuoteStatus' => $currentStatus,
                            'status' => $currentStatus,
                            'LastModifiedBy' => $submittedBy,
                            'updatedon' => now(),
                        ]);

                    Log::info('Update Status - Rolled back intermediate status after SP failure', [
                        'booking_no' => $bookingNo,
                        'restored_status' => $currentStatus,
                        'sp_error' => $errorMessage,
                    ]);
                } catch (\Exception $e) {
                    Log::error('Update Status - Failed to rollback intermediate status', [
                        'booking_no' => $bookingNo,
                        'error' => $e->getMessage(),
                        'original_status' => $currentStatus,
                    ]);
                }
            }

            Log::warning('Update Status - SP returned failure', [
                'booking_no' => $bookingNo,
                'target_status' => $targetStatus,
                'current_status' => $currentStatus,
                'sp_status' => $output['Status'] ?? 'Unknown',
                'message' => $errorMessage,
                'full_output' => $output,
                'office_code' => $officeCode,
                'reason_code' => $reasonCode,
                'user' => $submittedBy,
            ]);

            return response()->json(
                [
                    'success' => false,
                    'message' => $errorMessage,
                    'details' => $output['Remarks'] ?? [],
                ],
                422,
            );
        } catch (\PDOException $e) {
            Log::error('Update Status - PDO exception', [
                'booking_no' => $request->input('booking_no'),
                'target_status' => $request->input('status'),
                'error_message' => $e->getMessage(),
                'error_code' => $e->getCode(),
                'sql_state' => $e->errorInfo[0] ?? 'Unknown',
                'driver_error_code' => $e->errorInfo[1] ?? 'Unknown',
                'driver_error_message' => $e->errorInfo[2] ?? 'Unknown',
            ]);

            return response()->json(
                [
                    'success' => false,
                    'message' => 'Database connection error while updating status',
                    'error_code' => 'PDO_ERROR',
                    'details' => config('app.debug')
                        ? [
                            'message' => $e->getMessage(),
                            'sql_state' => $e->errorInfo[0] ?? null,
                        ]
                        : null,
                ],
                500,
            );
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('Update Status - Query exception', [
                'booking_no' => $request->input('booking_no'),
                'target_status' => $request->input('status'),
                'error_message' => $e->getMessage(),
                'sql' => $e->getSql() ?? 'N/A',
                'bindings' => $e->getBindings() ?? [],
            ]);

            return response()->json(
                [
                    'success' => false,
                    'message' => 'Database query error while updating status',
                    'error_code' => 'QUERY_ERROR',
                    'details' => config('app.debug')
                        ? [
                            'message' => $e->getMessage(),
                            'file' => $e->getFile(),
                            'line' => $e->getLine(),
                        ]
                        : null,
                ],
                500,
            );
        } catch (\Exception $e) {
            Log::error('Update Status - General exception', [
                'booking_no' => $request->input('booking_no'),
                'target_status' => $request->input('status'),
                'error_message' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
            ]);

            return response()->json(
                [
                    'success' => false,
                    'message' => 'Failed to update status: ' . $e->getMessage(),
                ],
                500,
            );
        }
    }

    public function processWon(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'quotation_id' => 'required|string',
            'reason_remark' => 'nullable|string',
            'source' => 'nullable|string',
        ]);

        $quotationId = $request->input('quotation_id');
        $reasonRemark = $request->input('reason_remark', '');
        $source = $request->input('source', 'dashboard');
        $submittedBy = Auth::user()->displayName ?? (Auth::user()->Email ?? 'system');

        $shouldMarkFailed = $source !== 'edit';

        $quotation = DB::table('Quote_QuotationInfo')
            ->where('ID', $quotationId)
            ->first(['ID', 'QuoteNo', 'QuoteStatus', 'status', 'pri_partnermaster_name', 'teamid']);

        if (!$quotation) {
            return response()->json(['success' => false, 'message' => 'Quotation not found'], 404);
        }

        $steps = [];

        // ── Step 1: Client Creation ───────────────────────────────────────────────
        $clientResult = $this->runBookingProcessMain($quotation->ID, 'Client');

        $steps[] = [
            'step' => 1,
            'label' => 'Client Creation',
            'status' => $clientResult['status'],
            'remark' => $clientResult['remark'],
        ];

        Log::info('processWon - Step 1 Client Creation', [
            'quotation_id' => $quotationId,
            'status' => $clientResult['status'],
            'remark' => $clientResult['remark'],
            'source' => $source,
        ]);

        if ($clientResult['status'] !== 'Success') {
            if ($shouldMarkFailed) {
                $this->markAsFailed($quotation->ID, 'Client Creation', $clientResult['remark'], $submittedBy);
            }
            return response()->json([
                'success' => false,
                'failed_step' => 1,
                'failed_label' => 'Client Creation',
                'message' => $clientResult['remark'] ?: 'Client creation failed.',
                'steps' => $steps,
            ]);
        }

        // ── Step 2: Mark as Won via SP ────────────────────────────────────────────
        try {
            $inputJson = json_encode([
                'OperationType' => 'QuotationStatusUpdate',
                'SubmittedDatetime' => now()->format('YmdHis'),
                'SubmittedBy' => $submittedBy,
                'Action' => 'Edit',
                'QuotationID' => $quotation->ID,
                'Status' => 'WON',
                'ReasonCode' => '',
                'ReasonRemark' => $reasonRemark,
            ]);

            $pdo = DB::connection()->getPdo();
            $stmt = $pdo->prepare("
            SET NOCOUNT ON;
            DECLARE @OutputJson VARCHAR(2000);
            EXEC sp_Quote_Quotaiton_StatusUpdate ?, @OutputJson OUTPUT;
            SELECT @OutputJson AS OutputJson
        ");
            $stmt->execute([$inputJson]);

            $spResult = null;
            do {
                try {
                    $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                    if (!empty($rows) && array_key_exists('OutputJson', $rows[0])) {
                        $spResult = $rows[0]['OutputJson'];
                    }
                } catch (\Exception $fetchEx) {
                    // non-fetchable result sets — skip
                }
            } while ($stmt->nextRowset());

            Log::info('processWon - Step 2 raw SP result', [
                'quotation_id' => $quotationId,
                'raw_result' => $spResult,
                'source' => $source,
            ]);

            if ($spResult === null) {
                $errorInfo = $stmt->errorInfo();
                $sqlMsg = !empty($errorInfo[2]) ? $errorInfo[2] : null;
                throw new \RuntimeException(
                    $sqlMsg ?:
                    'Status update failed. Please ensure the quotation has at least 1 revenue charge, a valid client, and all mandatory fields are filled.',
                );
            }

            $normalized = str_starts_with(trim($spResult), '{') ? '[' . $spResult . ']' : $spResult;
            $output = json_decode($normalized, true);

            $errors = [];
            if (is_array($output)) {
                foreach ($output as $item) {
                    if (($item['Status'] ?? '') === 'Failed' && isset($item['Remark'])) {
                        if (stripos($item['Remark'], 'has been updated to') === false) {
                            $errors[] = $item['Remark'];
                        }
                    }
                }
            }

            $overallSuccess = empty($errors) && is_array($output) && count($output) > 0;

            if (!$overallSuccess) {
                $errorMessage = !empty($errors) ? implode(' | ', $errors) : 'Failed to mark as Won';
                if ($shouldMarkFailed) {
                    $this->markAsFailed($quotation->ID, 'Mark as Won', $errorMessage, $submittedBy);
                }
                $steps[] = [
                    'step' => 2,
                    'label' => 'Mark as Won',
                    'status' => 'Failed',
                    'remark' => $errorMessage,
                ];
                return response()->json([
                    'success' => false,
                    'failed_step' => 2,
                    'failed_label' => 'Mark as Won',
                    'message' => $errorMessage,
                    'steps' => $steps,
                ]);
            }

            $successRemark = '';
            if (is_array($output)) {
                foreach ($output as $item) {
                    if (stripos($item['Remark'] ?? '', 'has been updated to') !== false) {
                        $successRemark = $item['Remark'];
                        break;
                    }
                }
            }

            $steps[] = [
                'step' => 2,
                'label' => 'Mark as Won',
                'status' => 'Success',
                'remark' => $successRemark ?: 'Quotation marked as Won successfully.',
            ];
        } catch (\Exception $e) {
            Log::error('processWon - Step 2 failed', [
                'quotation_id' => $quotationId,
                'error' => $e->getMessage(),
                'source' => $source,
            ]);
            if ($shouldMarkFailed) {
                $this->markAsFailed($quotation->ID, 'Mark as Won', $e->getMessage(), $submittedBy);
            }
            $steps[] = [
                'step' => 2,
                'label' => 'Mark as Won',
                'status' => 'Failed',
                'remark' => $e->getMessage(),
            ];
            return response()->json([
                'success' => false,
                'failed_step' => 2,
                'failed_label' => 'Mark as Won',
                'message' => $e->getMessage(),
                'steps' => $steps,
            ]);
        }

        // ── Step 3: Copy Revenue Charges to Cost ─────────────────────────────────
        $sendChargesResult = $this->runSendChargesSP($quotation->ID);

        Log::info('processWon - Step 3 SendCharges', [
            'quotation_id' => $quotationId,
            'status' => $sendChargesResult['status'],
            'remark' => $sendChargesResult['remark'],
            'source' => $source,
        ]);

        $steps[] = [
            'step' => 3,
            'label' => 'Copy Charges to Cost',
            'status' => $sendChargesResult['status'],
            'remark' => $sendChargesResult['remark'],
        ];

        if ($sendChargesResult['status'] !== 'Success') {
            if ($shouldMarkFailed) {
                $this->markAsFailed($quotation->ID, 'Copy Charges to Cost', $sendChargesResult['remark'], $submittedBy);
            }
            return response()->json([
                'success' => false,
                'failed_step' => 3,
                'failed_label' => 'Copy Charges to Cost',
                'message' => $sendChargesResult['remark'] ?: 'Failed to copy revenue charges to cost.',
                'steps' => $steps,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => $sendChargesResult['remark'] ?: 'Quotation marked as Won and charges copied successfully.',
            'steps' => $steps,
        ]);
    }

    private function runSendChargesSP(string $quotationId): array
    {
        try {
            $pdo = DB::connection()->getPdo();

            $stmt = $pdo->prepare("
            DECLARE @Status VARCHAR(20), @Remark VARCHAR(200);
            EXEC dbo.sp_Quote_Quotation_Request_SendCharges ?, @Status = @Status OUTPUT, @Remark = @Remark OUTPUT;
            SELECT @Status AS Status, @Remark AS Remark;
        ");

            $stmt->execute([$quotationId]);

            $result = null;
            do {
                try {
                    $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                    if (!empty($rows) && array_key_exists('Status', $rows[0])) {
                        $result = $rows[0];
                    }
                } catch (\Exception $e) {
                    // non-fetchable result sets — skip
                }
            } while ($stmt->nextRowset());

            if ($result === null) {
                return [
                    'status' => 'Failed',
                    'remark' => 'No output returned from sp_Quote_Quotation_Request_SendCharges.',
                ];
            }

            return [
                'status' => $result['Status'] ?? 'Failed',
                'remark' => $result['Remark'] ?? '',
            ];
        } catch (\Exception $e) {
            return ['status' => 'Failed', 'remark' => $e->getMessage()];
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
            $stmt = $pdo->prepare('SET NOCOUNT ON; EXEC sp_ReasonMasterList ?, ?, ?');
            $stmt->execute([$module, $reasonType, $filter]);

            $reasons = [];
            do {
                try {
                    $results = $stmt->fetchAll(\PDO::FETCH_OBJ);
                    if (empty($results)) {
                        continue;
                    }
                    $firstRow = $results[0];
                    $properties = array_keys((array) $firstRow);

                    // Skip SP status/error result sets — they return a single
                    // column whose value starts with 'Failed' or 'Error'

                    $firstVal = array_values((array) $firstRow)[0] ?? '';
                    if (
                        is_string($firstVal) &&
                        (str_starts_with($firstVal, 'Failed') || str_starts_with($firstVal, 'Error'))
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

            return response()->json(
                [
                    'success' => false,

                    'message' => 'Failed to load reasons: ' . $e->getMessage(),
                ],
                500,
            );
        }
    }

    public function saveTermsCondition(Request $request)
    {
        $bookingNo = $request->input('booking_no');
        $termsText = $request->input('terms_condition');
        $templateName = $request->input('template_name');
        $submittedBy = Auth::check() ? Auth::user()->displayName ?? (Auth::user()->Email ?? 'system') : 'system';

        if (!$bookingNo) {
            return response()->json(['success' => false, 'message' => 'Booking number is required'], 400);
        }

        // ── Normalize template name to exactly what Cust_CommunicationDetails stores ──
        // sp_Quote_QuotationFormat uses INNER JOIN On CC.Name = QI.QuotationTCTemplate
        // Any whitespace difference causes the join to fail → SP returns empty HTML.
        if ($templateName) {
            $exactName = DB::table('Cust_CommunicationDetails')
                ->whereRaw('LTRIM(RTRIM(Name)) = ?', [trim($templateName)])
                ->where('IsActive', 1)
                ->value('Name'); // returns the raw DB-stored string including trailing spaces

            if (!$exactName) {
                Log::error('saveTermsCondition - template not found in Cust_CommunicationDetails', [
                    'booking_no' => $bookingNo,
                    'template_name_raw' => $templateName,
                ]);
                return response()->json(
                    [
                        'success' => false,
                        'message' => "Terms & Conditions template '{$templateName}' was not found. Please re-select and try again.",
                    ],
                    422,
                );
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
            DB::statement(
                "
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
        ",
                [
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
                ],
            );

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

        $submittedBy = Auth::user()->displayName ?? (Auth::user()->Email ?? 'system');
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

            $pushResponse = json_decode($this->pushBookingToSCM($pushRequest)->getContent(), true);

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
                    $submittedBy,
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
                'QuoteStatus' => 'Completed',
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

        $json = json_encode(
            [
                'success' => true,
                'templates' => $templates,
            ],
            JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE,
        );

        return response($json, 200)->header('Content-Type', 'application/json');
    }

    public function getTermsTemplates(Request $request): JsonResponse
    {
        $quotationId = trim($request->input('quotation_id', ''));

        if (!$quotationId) {
            return response()->json(['success' => false, 'message' => 'quotation_id is required'], 400);
        }

        try {
            // Step 1: Run Eloquent UPDATE first
            DB::table('Quote_QuotationInfo')
                ->where('ID', $quotationId)
                ->update(['OfficeCode' => DB::raw('RTRIM(OfficeCode)')]);

            // Step 2: Force a fresh connection — the Eloquent UPDATE leaves the
            // shared PDO handle in a dirty state on pdo_sqlsrv. Disconnecting
            // forces Laravel to open a clean connection on the next getPdo() call.
            DB::disconnect();
            DB::reconnect();

            $pdo = DB::connection()->getPdo();

            // pdo_sqlsrv on IIS can return null on the first getPdo() after disconnect().
            // Retry once before giving up.
            if (!($pdo instanceof \PDO)) {
                Log::warning('getTermsTemplates - getPdo() returned null, retrying reconnect', [
                    'quotation_id' => $quotationId,
                ]);
                DB::disconnect();
                DB::reconnect();
                $pdo = DB::connection()->getPdo();
            }

            if (!($pdo instanceof \PDO)) {
                Log::error('getTermsTemplates - getPdo() returned null after retry', [
                    'quotation_id' => $quotationId,
                ]);
                return response()->json(
                    [
                        'success' => false,
                        'message' => 'Database connection unavailable. Please try again.',
                    ],
                    500,
                );
            }

            // Step 3: Raw PDO with SET NOCOUNT ON + columnCount() guard.
            $stmt = $pdo->prepare('SET NOCOUNT ON; EXEC dbo.sp_Quote_TermsandConditionList ?');
            $stmt->execute([$quotationId]);

            $rows = [];
            $resultSetIndex = 0;

            do {
                $resultSetIndex++;

                if ($stmt->columnCount() === 0) {
                    Log::debug('getTermsTemplates - Skipping no-column result set #' . $resultSetIndex);
                    continue;
                }

                try {
                    if ($stmt->columnCount() === 0) {
                        Log::debug('getTermsTemplates - columnCount() race, skipping set #' . $resultSetIndex);
                        continue;
                    }

                    $fetched = $stmt->fetchAll(\PDO::FETCH_OBJ);

                    if (empty($fetched)) {
                        continue;
                    }

                    $firstVal = array_values((array) $fetched[0])[0] ?? '';

                    if (is_string($firstVal) && str_starts_with(trim($firstVal), 'Failed')) {
                        Log::warning('getTermsTemplates - SP returned failure', [
                            'quotation_id' => $quotationId,
                            'message' => $firstVal,
                        ]);
                        return response()->json(
                            [
                                'success' => false,
                                'code' => 'NOT_CONFIGURED',
                                'message' => $firstVal,
                            ],
                            404,
                        );
                    }

                    $row = (array) $fetched[0];
                    $nameColumn = null;

                    foreach (['TemplateName', 'Name', 'TCName', 'TermsName'] as $candidate) {
                        if (array_key_exists($candidate, $row)) {
                            $nameColumn = $candidate;
                            break;
                        }
                    }

                    if (!$nameColumn) {
                        Log::debug('getTermsTemplates - No name column in result set #' . $resultSetIndex, [
                            'columns' => array_keys($row),
                        ]);
                        continue;
                    }

                    $descColumn = null;
                    foreach (['Description', 'TermsAndCondition', 'TCContent', 'Content', 'Body'] as $candidate) {
                        if (array_key_exists($candidate, $row)) {
                            $descColumn = $candidate;
                            break;
                        }
                    }

                    $rows = $fetched;

                    Log::info('getTermsTemplates - Templates found', [
                        'quotation_id' => $quotationId,
                        'count' => count($rows),
                        'name_column' => $nameColumn,
                        'desc_column' => $descColumn ?? 'not found',
                        'result_set' => $resultSetIndex,
                    ]);
                    break;
                } catch (\PDOException $e) {
                    Log::debug('getTermsTemplates - Skipping result set #' . $resultSetIndex, [
                        'error' => $e->getMessage(),
                    ]);
                    if (str_contains($e->getMessage(), 'no fields') || str_contains($e->getMessage(), 'no field')) {
                        break;
                    }
                    continue;
                }
            } while (
                (function () use ($stmt) {
                    try {
                        return $stmt->nextRowset();
                    } catch (\PDOException $e) {
                        Log::debug('getTermsTemplates - nextRowset() threw: ' . $e->getMessage());
                        return false;
                    }
                })()
            );

            if (empty($rows)) {
                Log::warning('getTermsTemplates - SP returned no usable templates', [
                    'quotation_id' => $quotationId,
                    'result_sets_seen' => $resultSetIndex,
                ]);
                return response()->json(
                    [
                        'success' => false,
                        'code' => 'NOT_CONFIGURED',
                        'message' => 'No T&C templates configured for this quotation.',
                    ],
                    404,
                );
            }

            $row = (array) $rows[0];
            $nameColumn = null;
            foreach (['TemplateName', 'Name', 'TCName', 'TermsName'] as $candidate) {
                if (array_key_exists($candidate, $row)) {
                    $nameColumn = $candidate;
                    break;
                }
            }

            $descColumn = null;
            foreach (['Description', 'TermsAndCondition', 'TCContent', 'Content', 'Body'] as $candidate) {
                if (array_key_exists($candidate, $row)) {
                    $descColumn = $candidate;
                    break;
                }
            }

            $templates = array_map(
                fn($r) => (object) [
                    'Name' => ((array) $r)[$nameColumn] ?? '',
                    'Description' => $descColumn ? base64_encode((string) (((array) $r)[$descColumn] ?? '')) : '',
                ],
                $rows,
            );

            return response()->json(['success' => true, 'templates' => $templates]);
        } catch (\Exception $e) {
            Log::error('getTermsTemplates - Failed', [
                'quotation_id' => $quotationId,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Failed to load T&C templates: ' . $e->getMessage(),
                ],
                500,
            );
        }
    }

    /**
     * Load T&C templates via sp_Quote_CommunicationDetail_List.
     * The SP filters by Service+Process+OfficeCode resolved from the QuoteNo internally,
     * so it returns templates appropriate for this specific quotation type and office.
     */
    private function getCommunicationTemplatesViaSP(
        string $module,
        string $subModule,
        string $bookingNo,
    ): \Illuminate\Http\JsonResponse {
        try {
            $quotationId = DB::table('Quote_QuotationInfo')->where('QuoteNo', $bookingNo)->value('ID');

            if (!$quotationId) {
                return response()->json(
                    [
                        'success' => false,
                        'message' => 'Quotation not found.',
                    ],
                    404,
                );
            }

            // ── Force-trim OfficeCode unconditionally ─────────────────────────────
            // whereRaw guard doesn't detect trailing spaces on all SQL Server
            // collations. Always RTRIM — no-op when already clean.
            DB::table('Quote_QuotationInfo')
                ->where('QuoteNo', $bookingNo)
                ->update(['OfficeCode' => DB::raw('RTRIM(OfficeCode)')]);

            // ── Log current record state after heal ───────────────────────────────
            $record = DB::table('Quote_QuotationInfo')
                ->where('QuoteNo', $bookingNo)
                ->first(['QuoteNo', 'ServiceVertical', 'process', 'OfficeCode']);

            Log::info('getCommunicationTemplatesViaSP - record state after heal', [
                'booking_no' => $bookingNo,
                'quote_no' => $record->QuoteNo ?? 'NULL',
                'svname' => $record->ServiceVertical ?? 'NULL',
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
            $stmt = $pdo->prepare('SET NOCOUNT ON; EXEC dbo.sp_Quote_CommunicationDetail_List ?, ?, ?');
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

                        return response()->json(
                            [
                                'success' => false,
                                'code' => $code,
                                'message' => $firstVal,
                            ],
                            404,
                        );
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
                        $templates = array_map(
                            fn($row) => (object) [
                                'Name' => $row->TemplateName,
                                'Description' => '',
                            ],
                            $rows,
                        );

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

                return response()->json(
                    [
                        'success' => false,
                        'code' => 'NOT_CONFIGURED',
                        'message' =>
                            "No T&C templates configured for this quotation's office and service type. Please contact your administrator.",
                    ],
                    404,
                );
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

            return response()->json(
                [
                    'success' => false,
                    'message' => 'Failed to load T&C templates: ' . $e->getMessage(),
                ],
                500,
            );
        }
    }

    public function getCommunicationDescription(Request $request)
    {
        $module = $request->input('module', 'Quotation');
        $subModule = $request->input('sub_module');
        $officeCode = trim($request->input('office_code', ''));
        $name = $request->input('name');

        if (!$subModule || !$officeCode || !$name) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'All parameters are required',
                ],
                400,
            );
        }

        try {
            $result = DB::select(
                '
            DECLARE @Status      VARCHAR(15);
            DECLARE @Remark      VARCHAR(1000);
            DECLARE @Description NVARCHAR(MAX);
            EXEC sp_CommunicationDetails_GetDescription ?, ?, ?, ?,
                @Status      = @Status      OUTPUT,
                @Remark      = @Remark      OUTPUT,
                @Description = @Description OUTPUT;
            SELECT @Status AS Status, @Remark AS Remark, @Description AS Description

        ',
                [$module, $subModule, $officeCode, $name],
            );
            if (empty($result)) {
                return response()->json(
                    [
                        'success' => false,
                        'message' => 'No response from stored procedure',
                    ],
                    500,
                );
            }

            if ($result[0]->Status === 'Success') {
                return response()->json([
                    'success' => true,
                    'description' => $result[0]->Description,
                ]);
            }

            return response()->json(
                [
                    'success' => false,
                    'message' => $result[0]->Remark ?? 'Failed to load description',
                ],
                422,
            );
        } catch (\Exception $e) {
            Log::error('getCommunicationDescription failed', [
                'office_code' => $officeCode,
                'name' => $name,
                'error' => $e->getMessage(),
            ]);

            return response()->json(
                [
                    'success' => false,
                    'message' => 'Failed to load description: ' . $e->getMessage(),
                ],
                500,
            );
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
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Quotation not found',
                ],
                404,
            );
        }

        try {
            // ============================================
            // SEND EMAIL
            // ============================================
            \Illuminate\Support\Facades\Mail::send([], [], function (\Illuminate\Mail\Message $message) use (
                $toEmail,
                $fromEmail,
                $subject,
                $body,
                $quotation,
            ) {
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
            });

            // ============================================
            // LOG VIA SP
            // ============================================
            $clientName =
                DB::table('Quote_QuotationInfo')->where('bookingno', $bookingNo)->value('pri_partnermaster_name') ?? '';

            $jsonData = json_encode([
                'SchedulerType' => 'QuotationEmail',
                'SubmittedOn' => now()->format('Y-m-d H:i:s'),
                'ClientName' => $clientName,
                'EmailFrom' => $fromEmail,
                'EmailTo' => $toEmail,
                'Subject' => $subject,
                'BodyMessage' => $body,
                'Status' => 'Sent',
                'SendRemark' =>
                    'Sent via Quotation Module' .
                    ($request->input('quote_version') ? ' | ' . $request->input('quote_version') : ''),
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
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Mail server error: ' . $e->getMessage(),
                ],
                500,
            );
        } catch (\Exception $e) {
            Log::error('sendEmailQuote - Failed', [
                'booking_no' => $bookingNo,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Failed to send email: ' . $e->getMessage(),
                ],
                500,
            );
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

            $quotation = \App\Models\QuotationInfo::where('ID', $id)->first();

            if (!$quotation) {
                Log::error('PDF Generation - Quotation not found', ['id' => $id]);
                return response()->json(['success' => false, 'message' => 'Quotation not found'], 404);
            }

            $submittedBy = substr(auth()->user()->displayName ?? (auth()->user()->Email ?? 'system'), 0, 50);
            if (empty($quotation->ID)) {
                Log::error('PDF Generation - QuotationID is empty', ['id' => $id]);
                return response()->json(
                    ['success' => false, 'message' => 'Quotation ID is not set. Cannot generate PDF.'],
                    422,
                );
            }

            // Force-trim OfficeCode — SP reads it directly via Left(QI.OfficeCode,2)
            DB::table('Quote_QuotationInfo')
                ->where('ID', $id)
                ->update(['OfficeCode' => DB::raw('RTRIM(OfficeCode)')]);

            // Persist the All-in-One remark, only for that quote type — the SP
            // (sp_Quote_QuotationFormat) does not read or write this column, it's
            // UI-side context saved alongside document generation.
            $quoteType = request('QuoteType', 'Std. PDF');

            // Persist the remark BEFORE the guard — must run regardless of which
            // engine renders the PDF.
            if ($quoteType === 'All in One PDF' && request()->filled('AllInOneRemark')) {
                DB::table('Quote_QuotationInfo')
                    ->where('ID', $id)
                    ->update(['AllInOneRemark' => substr(request('AllInOneRemark'), 0, 1000)]);
                $quotation->refresh();
            }

            // The Laravel renderer supports the standard layouts plus All in One
            // (rendered as the standard layout + VAT + remark block).
            if (
                request('Engine') !== 'legacy' &&
                !in_array($quoteType, ['Std. PDF', 'Std. With VAT', 'All in One PDF'], true)
            ) {
                return response()->json(
                    [
                        'success' => false,
                        'message' => "Quote type '{$quoteType}' is not supported by the current quotation format.",
                    ],
                    422,
                );
            }

            // ── Call sp_Quote_QuotationFormat ─────────────────────────────────────
            $html = null;
            $spError = null;

            // Opt-in Laravel-built HTML: ?Engine=laravel. Without it, the existing SP call below runs unchanged.
            if (request('Engine') !== 'legacy') {
                try {
                    $showVat = in_array($quoteType, ['Std. With VAT', 'All in One PDF'], true);
                    $allInOneRemark = $quoteType === 'All in One PDF' ? $quotation->AllInOneRemark ?? null : null;

                    $html = $this->buildQuotationHtml(
                        $this->getQuotationData($quotation->ID),
                        $showVat,
                        $allInOneRemark,
                    );
                } catch (\RuntimeException $e) {
                    return response()->json(
                        ['success' => false, 'message' => $e->getMessage(), 'dba_action_required' => true],
                        500,
                    );
                }
            } else {
                try {
                    $pdo = DB::getPdo();
                    $stmt = $pdo->prepare("
            DECLARE @QuoteFormat VARCHAR(MAX);
            EXEC dbo.sp_Quote_QuotationFormat ?, ?,
                @QuoteFormat = @QuoteFormat OUTPUT;
            SELECT @QuoteFormat AS QuoteFormat;
        ");
                    $stmt->execute([$quotation->ID, request('QuoteType', 'Std. PDF')]);

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
                            continue;
                        }
                    } while ($stmt->nextRowset());
                } catch (\Exception $e) {
                    return response()->json(
                        ['success' => false, 'message' => 'Failed to execute quotation format SP: ' . $e->getMessage()],
                        500,
                    );
                }
            }

            if (!empty($spError)) {
                Log::error('PDF Generation - sp_Quote_QuotationFormat failure (DBA action required)', [
                    'quotation_id' => $quotation->ID,
                    'quote_type' => request('QuoteType', 'Std. PDF'),
                    'sp_message' => $spError,
                ]);
                return response()->json(
                    [
                        'success' => false,
                        'message' => 'sp_Quote_QuotationFormat: ' . $spError,
                        'dba_action_required' => true,
                    ],
                    500,
                );
            }

            if (empty($html) || trim($html) === '') {
                return response()->json(
                    [
                        'success' => false,
                        'message' =>
                            'No quotation HTML returned from SP. Ensure a Terms & Conditions template is applied before generating.',
                    ],
                    422,
                );
            }

            Log::info('PDF Generation - HTML retrieved from SP', [
                'quotation_id' => $quotation->ID,
                'html_length' => strlen($html),
                'tc_template' => $quotation->QuotationTCTemplate ?? 'N/A',
            ]);

            if (request('Engine') === 'legacy') {
                $html = str_replace('public/img/logo.png', public_path('img/logo.png'), $html);
                $html = str_replace('style="height: 42px;"', 'style="height: 80px; width: auto;"', $html);
            }

            // ── Render PDF ────────────────────────────────────────────────────────
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
            } catch (\Exception $e) {
                return response()->json(
                    ['success' => false, 'message' => 'PDF render failed: ' . $e->getMessage()],
                    500,
                );
            }

            // ── Determine version number ──────────────────────────────────────────
            // Count existing non-deleted documents for this quotation.
            // SP has a duplicate-filename guard so the count must be accurate.
            $folder = 'quotations/' . $quotation->bookingno;

            try {
                \Storage::disk('public')->makeDirectory($folder);
            } catch (\Exception $e) {
                Log::warning('PDF Generation - Could not ensure directory exists', [
                    'folder' => $folder,
                    'error' => $e->getMessage(),
                ]);
            }

            // Tag VAT-quoted PDFs in the filename so the Versions list can
            // label them distinctly (e.g. "Version 2 (PDF - VAT)").
            $quoteType = request('QuoteType', 'Std. PDF');
            $fileName =
                'quote_' .
                now()->format('Ymd_His') .
                ($quoteType === 'Std. With VAT' ? '_vat' : '') .
                ($quoteType === 'All in One PDF' ? '_allinone' : '') .
                '.pdf';
            $storagePath = $folder . '/' . $fileName;
            $fullPath = \Storage::disk('public')->path($storagePath);

            // ── Write PDF to local disk ───────────────────────────────────────────
            // $azureWarning initialized here so it is always in scope,
            // including the early-return inside the SP catch block below.
            $azureWarning = null;

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
                return response()->json(
                    ['success' => false, 'message' => 'Failed to save PDF to disk: ' . $e->getMessage()],
                    500,
                );
            }

            // ── Step 1: Register document via SP ─────────────────────────────────
            // SP owns the INSERT. Must succeed before Azure upload to avoid orphaned blobs.
            $azureWarning = null;

            try {
                $pdoSp = DB::connection()->getPdo();
                $stmtSp = $pdoSp->prepare("
                SET NOCOUNT ON;
                DECLARE @Status VARCHAR(25), @Remark VARCHAR(200);
                EXEC dbo.sp_Quote_QuotationDocumentAdd ?, ?, ?,
                    @Status = @Status OUTPUT,
                    @Remark = @Remark OUTPUT;
                SELECT @Status AS Status, @Remark AS Remark;
            ");
                $stmtSp->execute([
                    $quotation->ID, // @QuotationID
                    $submittedBy, // @Username (already trimmed to 50)
                    $fileName, // @Filename — SP builds FilePath internally
                ]);

                $spResult = null;
                do {
                    try {
                        $spRows = $stmtSp->fetchAll(\PDO::FETCH_OBJ);
                        if (!empty($spRows) && isset($spRows[0]->Status)) {
                            $spResult = $spRows[0];
                            break;
                        }
                    } catch (\PDOException $e) {
                        continue;
                    }
                } while ($stmtSp->nextRowset());

                if (!$spResult) {
                    // SP returned nothing — clean up local file and fail
                    @unlink($fullPath);
                    Log::error('PDF Generation - SP returned no output, local file removed', [
                        'quotation_id' => $quotation->ID,
                        'file' => $fileName,
                    ]);
                    return response()->json(
                        [
                            'success' => false,
                            'message' => 'Document registration SP returned no output.',
                        ],
                        500,
                    );
                }

                if (strtolower(trim($spResult->Status)) !== 'success') {
                    // SP rejected — clean up local file and fail
                    @unlink($fullPath);
                    Log::error('PDF Generation - SP registration failed, local file removed', [
                        'quotation_id' => $quotation->ID,
                        'sp_status' => $spResult->Status,
                        'sp_remark' => $spResult->Remark,
                    ]);
                    return response()->json(
                        [
                            'success' => false,
                            'message' => 'Document registration failed: ' . $spResult->Remark,
                        ],
                        500,
                    );
                }

                Log::info('PDF Generation - SP registration succeeded', [
                    'quotation_id' => $quotation->ID,
                    'sp_remark' => $spResult->Remark,
                    'file' => $fileName,
                ]);
            } catch (\Exception $spEx) {
                @unlink($fullPath);
                Log::error('PDF Generation - SP call failed, local file removed', [
                    'quotation_id' => $quotation->ID,
                    'error' => $spEx->getMessage(),
                ]);
                return response()->json(
                    [
                        'success' => false,
                        'message' => 'Document registration failed: ' . $spEx->getMessage(),
                    ],
                    500,
                );
            }

            // ── Step 2: Upload to Azure ───────────────────────────────────────────
            // SP already wrote FilePath as 'Quotation/{officeCode}/{fileName}'.
            // Blob path must match exactly.
            $officeShortCode = DB::table('Cust_Office')
                ->where('TeamCode', $quotation->teamid)
                ->where('IsActive', 1)
                ->where('IsDeleted', 0)
                ->value('Code');

            $officeShortCode = $officeShortCode
                ? strtoupper(trim($officeShortCode))
                : strtoupper(substr($quotation->OfficeCode ?? 'XX', 0, 2)); // use already-loaded model

            $blobPath = 'Quotation/' . $officeShortCode . '/' . $fileName;

            try {
                \Storage::disk('azure')->put($blobPath, file_get_contents($fullPath));

                Log::info('PDF Generation - Uploaded to Azure', [
                    'quotation_id' => $quotation->ID,
                    'blob_path' => $blobPath,
                ]);
            } catch (\Exception $azureEx) {
                // Non-fatal: DB record exists, blob missing. Flag for manual recovery.
                Log::error('PDF Generation - Azure upload failed (non-fatal)', [
                    'quotation_id' => $quotation->ID,
                    'blob_path' => $blobPath,
                    'error' => $azureEx->getMessage(),
                ]);
                $azureWarning = 'PDF registered but Azure upload failed: ' . $azureEx->getMessage();
            }

            // ── Step 3: Cleanup local temp file ──────────────────────────────────
            @unlink($fullPath);
            Log::info('PDF Generation - Local temp file removed', ['path' => $fullPath]);
            // ── End Azure block ───────────────────────────────────────────────────

            // Version number is positional — count existing non-deleted docs after insert
            $versionNumber = DB::table('Quote_Quotation_Document')
                ->where('QuotationInfoID', $quotation->ID)
                ->where('DocumentType', 'QuotationPDF')
                ->where('IsDeleted', 0)
                ->count();

            return response()->json([
                'success' => true,
                'version' => $versionNumber,
                'label' => 'Version ' . $versionNumber,
                'url' => route('quotation.download-pdf', [$id, $versionNumber]),
                'warning' => $azureWarning,
            ]);
        } catch (\Exception $e) {
            Log::error('PDF Generation - Unexpected top-level exception', [
                'id' => $id,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(
                ['success' => false, 'message' => 'PDF generation failed unexpectedly: ' . $e->getMessage()],
                500,
            );
        }
    }

    /**
     * Generate and register an Excel (.xlsx) quotation document.
     * Route: POST /quotation/{id}/generate-excel
     *
     * Mirrors generatePdf() exactly: same SP for HTML, same document
     * registration SP, same Azure upload/cleanup flow. Only the render
     * step differs (PhpSpreadsheet HTML reader instead of Dompdf).
     *
     */
    public function generateExcel($id)
    {
        $tempFile = null;

        try {
            ini_set('memory_limit', '512M');
            ini_set('max_execution_time', '300');

            Log::info('Excel Generation - Starting', [
                'quotation_id' => $id,
            ]);

            $quotation = \App\Models\QuotationInfo::where('ID', $id)->first();

            if (!$quotation) {
                Log::error('Excel Generation - Quotation not found', [
                    'id' => $id,
                ]);

                return response()->json(
                    [
                        'success' => false,
                        'message' => 'Quotation not found',
                    ],
                    404,
                );
            }

            $submittedBy = substr(auth()->user()->displayName ?? (auth()->user()->Email ?? 'system'), 0, 50);

            /*
             * Generate quotation HTML.
             *
             * getQuotationHtml() now:
             * - returns the generated HTML when successful
             * - returns null when no HTML is available
             * - throws RuntimeException when the SP itself reports a failure
             */
            $quoteData = $this->getQuotationData($quotation->ID);
            $serviceText = trim((string) ($quoteData['Service'] ?? ''));
            $html = $this->buildExcelQuotationHtml(
                $quoteData,
                $this->getQuotationExcelTable($quotation->ID, $quoteData),
            );
            if (empty($html)) {
                Log::error('Excel Generation - Empty quotation HTML', [
                    'quotation_id' => $id,
                    'quote_type' => request('QuoteType', 'Cost & Sell EXCEL'),
                ]);

                throw new \RuntimeException('Unable to generate quotation HTML.');
            }

            /*
             * Remove HTML comments before DOM parsing.
             */
            $html = preg_replace('/<!--.*?-->/s', '', $html);

            /*
             * Remove XML declarations returned by the SP.
             *
             * The SP may already return:
             *
             * <?xml encoding="UTF-8">
             *
             * Remove existing declarations and add one clean declaration
             * before DOMDocument parsing.
             */
            $html = preg_replace('/<\?xml[^>]*\?>/i', '', $html);
            /*
             * Normalize the HTML using DOMDocument.
             */
            libxml_use_internal_errors(true);

            $dom = new \DOMDocument();

            $dom->loadHTML('<?xml encoding="UTF-8">' . $html, \LIBXML_HTML_NOIMPLIED | \LIBXML_HTML_NODEFDTD);

            $html = $dom->saveHTML();

            Log::info('CANARY: HTML after initial DOMDocument', [
                'html' => $html,
            ]);

            libxml_clear_errors();

            /*
             * Normalize the HTML for PhpSpreadsheet.
             */
            Log::info('CANARY: BEFORE normalizeHtmlForSpreadsheet', [
                'quotation_id' => $id,
                'html_length' => strlen($html),
            ]);

            $html = $this->normalizeHtmlForSpreadsheet($html);

            Log::info('CANARY: AFTER normalizeHtmlForSpreadsheet', [
                'quotation_id' => $id,
                'html_length' => strlen($html),
            ]);

            /*
             * Normalize image paths so PhpSpreadsheet can load the logo.
             */
            $html = $this->normalizeImagePathsForSpreadsheet($html);

            /*
             * TEMP DEBUG FILE
             *
             * Remove this after the Excel layout has been confirmed.
             */
            file_put_contents(storage_path('app/debug_quote_excel.html'), $html);

            /*
             * Read the normalized HTML into PhpSpreadsheet.
             */
            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Html();
            $spreadsheet = $reader->loadFromString($html);
            $sheet = $spreadsheet->getActiveSheet();

            // PhpSpreadsheet's Html reader does not reliably import <img> tags.
            // Inject the logo as a native drawing so it renders in Excel.
            $logoFile = public_path('img/logo.png');
            if (is_file($logoFile)) {
                $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
                $drawing->setName('Logo');
                $drawing->setDescription('Company logo');
                $drawing->setPath($logoFile);
                $drawing->setHeight(48);
                $drawing->setCoordinates('A1');
                $drawing->setOffsetX(5);
                $drawing->setOffsetY(5);
                $drawing->setWorksheet($sheet);
                Log::info('Excel Generation - logo drawing injected', [
                    'quotation_id' => $id,
                    'path' => $logoFile,
                ]);
            }

            /*
             * TEMP DEBUG: Check Terms & Conditions storage table rows.
             */
            for ($rowIdx = 1; $rowIdx <= $sheet->getHighestDataRow(); $rowIdx++) {
                $value = trim((string) $sheet->getCell("A{$rowIdx}")->getValue());

                if (stripos($value, '16th – 24th Day') !== false || stripos($value, 'Over 24 Days') !== false) {
                    Log::info('Storage table row check', [
                        'row' => $rowIdx,
                        'value' => $value,
                        'height' => $sheet->getRowDimension($rowIdx)->getRowHeight(),
                    ]);

                    for ($r = max(1, $rowIdx - 1); $r <= min($sheet->getHighestDataRow(), $rowIdx + 2); $r++) {
                        Log::info('Storage nearby row', [
                            'row' => $r,
                            'A' => $sheet->getCell("A{$r}")->getValue(),
                            'B' => $sheet->getCell("B{$r}")->getValue(),
                            'C' => $sheet->getCell("C{$r}")->getValue(),
                            'height' => $sheet->getRowDimension($r)->getRowHeight(),
                        ]);
                    }
                }
            }

            Log::info('Excel A2 customer cell', [
                'value' => $sheet->getCell('A2')->getValue(),
            ]);

            /*
             * PhpSpreadsheet's HTML reader is importing two blank rows before
             * the normalized quotation header.
             *
             * The imported structure is:
             *   Row 1-2 = blank
             *   Row 3   = quotation header row 1
             *   Row 4   = quotation header row 2
             *
             * Remove the two blank rows so the quotation header starts at Row 1.
             */
            $sheet->removeRow(1, 2);

            // Add a full blank row between the quotation header and GENERAL INFORMATION.
            $sheet->insertNewRowBefore(3, 1);

            Log::info('Excel T&C search', [
                'highest_row' => $sheet->getHighestDataRow(),
            ]);

            for ($rowIdx = 1; $rowIdx <= $sheet->getHighestDataRow(); $rowIdx++) {
                $value = trim((string) $sheet->getCell("A{$rowIdx}")->getValue());

                if (
                    stripos($value, 'Terms') !== false ||
                    stripos($value, 'Condition') !== false ||
                    stripos($value, 'All local charges') !== false ||
                    stripos($value, 'Port Storage') !== false
                ) {
                    Log::info('Excel T&C match', [
                        'row' => $rowIdx,
                        'value' => $value,
                    ]);
                }
            }

            Log::info('CANARY: Excel header cells after removing blank rows', [
                'A1' => $sheet->getCell('A1')->getValue(),
                'B1' => $sheet->getCell('B1')->getValue(),
                'C1' => $sheet->getCell('C1')->getValue(),
                'D1' => $sheet->getCell('D1')->getValue(),
                'E1' => $sheet->getCell('E1')->getValue(),
                'F1' => $sheet->getCell('F1')->getValue(),
                'G1' => $sheet->getCell('G1')->getValue(),
                'H1' => $sheet->getCell('H1')->getValue(),

                'A2' => $sheet->getCell('A2')->getValue(),
                'C2' => $sheet->getCell('C2')->getValue(),
                'E2' => $sheet->getCell('E2')->getValue(),
                'G2' => $sheet->getCell('G2')->getValue(),

                'merged_cells' => $sheet->getMergeCells(),
            ]);

            // Fix quotation header layout after HTML import.
            // The HTML reader does not reliably preserve the nested header table layout,
            // so explicitly control the header cells in Excel.

            $sheet->mergeCells('A1:B1');
            $sheet->mergeCells('C1:D1');
            $sheet->mergeCells('E1:H1');

            $sheet->mergeCells('A2:D2');
            $sheet
                ->getStyle('A2:D2')
                ->getAlignment()
                ->setWrapText(true)
                ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP);
            $sheet->mergeCells('E2:F2');
            $sheet->mergeCells('G2:H2');

            $sheet->getStyle('A2:D2')->getAlignment()->setWrapText(true);

            $sheet->getColumnDimension('A')->setWidth(10);
            $sheet->getColumnDimension('B')->setWidth(10);
            $sheet->getColumnDimension('C')->setWidth(24);
            $sheet->getColumnDimension('D')->setWidth(24);

            $sheet->getColumnDimension('E')->setWidth(12);
            $sheet->getColumnDimension('F')->setWidth(12);
            $sheet->getColumnDimension('G')->setWidth(12);
            $sheet->getColumnDimension('H')->setWidth(12);

            $sheet->getRowDimension(1)->setRowHeight(82);
            $sheet->getRowDimension(2)->setRowHeight(100);
            /*
             * ============================================================
             * GENERAL EXCEL FORMATTING
             * ============================================================
             */
            $sheet->setShowGridlines(false);

            /*
             * ============================================================
             * COLUMN WIDTHS
             * ============================================================
             *
             * A:B = Logo
             * C:D = Company information
             * E:H = Quotation information
             */
            $columnWidths = [
                'A' => 14,
                'B' => 14,
                'C' => 18,
                'D' => 18,
                'E' => 14,
                'F' => 14,
                'G' => 14,
                'H' => 14,
                'I' => 12,
                'J' => 12,
                'K' => 12,
                'L' => 12,
            ];

            foreach ($columnWidths as $column => $width) {
                $sheet->getColumnDimension($column)->setWidth($width);
            }
            /*
             * Header row heights.
             */
            $sheet->getRowDimension(1)->setRowHeight(80);
            $sheet->getRowDimension(2)->setRowHeight(78);
            $sheet->getRowDimension(3)->setRowHeight(20);
            $sheet->getRowDimension(4)->setRowHeight(18);

            /*
             * Format any images/drawings imported from the HTML.
             */
            foreach ($sheet->getDrawingCollection() as $drawing) {
                $drawing->setCoordinates('A1');
                $drawing->setOffsetX(5);
                $drawing->setOffsetY(5);
                $drawing->setHeight(48);
            }

            /*
             * Header alignment.
             *
             * Row 1:
             * A:D = company/logo
             * E:H = quotation title/number
             *
             * Row 2:
             * A:D = customer
             * E:F = date
             * G:H = currency
             */
            $sheet->getStyle('A1:D1')->applyFromArray([
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    'wrapText' => true,
                ],
            ]);

            $sheet->getStyle('E1:H1')->applyFromArray([
                'font' => ['bold' => true, 'size' => 14],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT,
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    'wrapText' => true,
                ],
            ]);

            $sheet->getStyle('A2:D2')->applyFromArray([
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP,
                    'wrapText' => true,
                ],
            ]);

            $sheet->getStyle('E2:F2')->applyFromArray([
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP,
                    'wrapText' => true,
                ],
            ]);

            $sheet->getStyle('G2:H2')->applyFromArray([
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT,
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP,
                    'wrapText' => true,
                ],
            ]);

            /*
             * Add a little visual breathing room around the header.
             */
            $sheet->getStyle('A1:H2')->applyFromArray([
                'font' => [
                    'name' => 'Arial',
                ],
            ]);

            /*
             * Content-based styling for charge tables only.
             */
            $navyFill = '1F3864';
            $headerFill = 'DCE6F1';
            $categoryFill = 'D9D9F0';
            $totalFill = 'E2EFDA';
            $borderColor = '9DB4CE';

            $reportTitleTexts = ['Equipment Based Charges', 'Non Equipment Based Charges'];

            $highestRow = $sheet->getHighestDataRow();
            $highestColumn = $sheet->getHighestDataColumn();

            $highestColIdx = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);

            $insideChargeSection = false;

            for ($rowIdx = 4; $rowIdx <= $highestRow; $rowIdx++) {
                $rowValues = [];

                for ($c = 1; $c <= $highestColIdx; $c++) {
                    $rowValues[] = trim((string) $sheet->getCellByColumnAndRow($c, $rowIdx)->getValue());
                }

                $firstCellValue = $rowValues[0] ?? '';

                $rowRange = "A{$rowIdx}:{$highestColumn}{$rowIdx}";

                /*
                 * Report title: enters a charge section.
                 */
                if (in_array($firstCellValue, $reportTitleTexts, true)) {
                    $insideChargeSection = true;

                    $sheet->getStyle($rowRange)->applyFromArray([
                        'font' => [
                            'bold' => true,
                            'color' => [
                                'rgb' => 'FFFFFF',
                            ],
                            'size' => 11,
                        ],
                        'fill' => [
                            'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                            'startColor' => [
                                'rgb' => $navyFill,
                            ],
                        ],
                        'alignment' => [
                            'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
                            'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                        ],
                    ]);

                    $sheet->getRowDimension($rowIdx)->setRowHeight(20);

                    continue;
                }

                /*
                 * Outside charge sections:
                 * leave General Information, Cargo Information,
                 * spacer rows, Terms & Conditions, etc. untouched.
                 */
                if (!$insideChargeSection) {
                    continue;
                }

                $hasRate = in_array('Rate', $rowValues, true);
                $hasAmount = in_array('Amount', $rowValues, true);

                /*
                 * Header rows.
                 */
                if ($hasRate || $hasAmount || $firstCellValue === 'Description') {
                    $sheet->getStyle($rowRange)->applyFromArray([
                        'font' => [
                            'bold' => true,
                            'size' => 9.5,
                        ],
                        'fill' => [
                            'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                            'startColor' => [
                                'rgb' => $headerFill,
                            ],
                        ],
                        'alignment' => [
                            'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                            'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                            'wrapText' => true,
                        ],
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                                'color' => [
                                    'rgb' => $borderColor,
                                ],
                            ],
                        ],
                    ]);

                    continue;
                }

                /*
                 * Total row.
                 */
                if (stripos($firstCellValue, 'total') === 0) {
                    $sheet->getStyle($rowRange)->applyFromArray([
                        'font' => [
                            'bold' => true,
                        ],
                        'fill' => [
                            'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                            'startColor' => [
                                'rgb' => $totalFill,
                            ],
                        ],
                        'borders' => [
                            'top' => [
                                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            ],
                        ],
                    ]);

                    $insideChargeSection = false;

                    continue;
                }

                /*
                 * Category row:
                 * single label, no Quantity value in column C.
                 */
                $quantityValue = $rowValues[2] ?? '';

                if ($firstCellValue !== '' && $quantityValue === '') {
                    $sheet->getStyle($rowRange)->applyFromArray([
                        'font' => [
                            'bold' => true,
                            'size' => 9.5,
                        ],
                        'fill' => [
                            'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                            'startColor' => [
                                'rgb' => $categoryFill,
                            ],
                        ],
                    ]);

                    continue;
                }

                /*
                 * Charge data row:
                 * has a Quantity value in column C.
                 */
                if ($quantityValue !== '') {
                    $sheet->getStyle("C{$rowIdx}:{$highestColumn}{$rowIdx}")->applyFromArray([
                        'alignment' => [
                            'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT,
                        ],
                        'borders' => [
                            'bottom' => [
                                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                                'color' => [
                                    'rgb' => 'DDDDDD',
                                ],
                            ],
                        ],
                    ]);
                }
            }

            /*
             * Unify section bars.
             */
            $blackBarTexts = ['GENERAL INFORMATION', 'CARGO INFORMATION', 'TERMS AND CONDITION'];

            for ($rowIdx = 4; $rowIdx <= $highestRow; $rowIdx++) {
                $cellValue = trim((string) $sheet->getCellByColumnAndRow(1, $rowIdx)->getValue());

                $rowRange = "A{$rowIdx}:{$highestColumn}{$rowIdx}";

                $plainValue = preg_replace('/^[\s\x{00A0}]+/u', '', $cellValue);
                if (
                    in_array($plainValue, $blackBarTexts, true) ||
                    ($serviceText !== '' && stripos($plainValue, $serviceText) === 0)
                ) {
                    $sheet->getStyle($rowRange)->applyFromArray([
                        'font' => [
                            'bold' => true,
                            'color' => [
                                'rgb' => 'FFFFFF',
                            ],
                            'size' => 10.5,
                        ],
                        'fill' => [
                            'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                            'startColor' => [
                                'rgb' => $navyFill,
                            ],
                        ],
                        'alignment' => [
                            'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
                            'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                        ],
                    ]);

                    $sheet->getRowDimension($rowIdx)->setRowHeight(18);
                }
            }

            /*
             * Create a unique XLSX filename.
             */
            $fileName = 'Quotation_' . now()->format('Ymd_His') . '_' . substr((string) $id, 0, 8) . '.xlsx';

            /*
             * Create temporary file.
             */
            $tempFile = tempnam(sys_get_temp_dir(), 'quotation_excel_');

            if ($tempFile === false) {
                throw new \Exception('Unable to create temporary Excel file.');
            }

            /*
             * Write XLSX file.
             */
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

            $writer->save($tempFile);

            if (!file_exists($tempFile)) {
                throw new \Exception('Excel file was not found after generation.');
            }

            Log::info('Excel Generation - XLSX created successfully', [
                'quotation_id' => $id,
                'file' => $tempFile,
                'size' => filesize($tempFile),
                'filename' => $fileName,
            ]);

            /*
             * Register the document through the existing SP.
             *
             * The existing SP inserts:
             * DocumentType = 'QuotationPDF'
             */
            try {
                $pdoSp = DB::connection()->getPdo();

                $stmtSp = $pdoSp->prepare("
                SET NOCOUNT ON;

                DECLARE @Status VARCHAR(25),
                        @Remark VARCHAR(200);

                EXEC dbo.sp_Quote_QuotationDocumentAdd
                    ?,
                    ?,
                    ?,
                    @Status = @Status OUTPUT,
                    @Remark = @Remark OUTPUT;

                SELECT
                    @Status AS Status,
                    @Remark AS Remark;
            ");

                $stmtSp->execute([$quotation->ID, $submittedBy, $fileName]);

                $spResult = null;

                do {
                    try {
                        $spRows = $stmtSp->fetchAll(\PDO::FETCH_OBJ);

                        if (!empty($spRows) && isset($spRows[0]->Status)) {
                            $spResult = $spRows[0];
                            break;
                        }
                    } catch (\PDOException $e) {
                        continue;
                    }
                } while ($stmtSp->nextRowset());

                if (!$spResult) {
                    @unlink($tempFile);
                    $tempFile = null;

                    Log::error('Excel Generation - SP returned no output', [
                        'quotation_id' => $id,
                        'file' => $fileName,
                    ]);

                    return response()->json(
                        [
                            'success' => false,
                            'message' => 'Document registration SP returned no output.',
                        ],
                        500,
                    );
                }

                if (strtolower(trim($spResult->Status)) !== 'success') {
                    @unlink($tempFile);
                    $tempFile = null;

                    Log::error('Excel Generation - SP registration failed', [
                        'quotation_id' => $id,
                        'sp_status' => $spResult->Status,
                        'sp_remark' => $spResult->Remark,
                    ]);

                    return response()->json(
                        [
                            'success' => false,
                            'message' => 'Document registration failed: ' . $spResult->Remark,
                        ],
                        500,
                    );
                }

                Log::info('Excel Generation - SP registration succeeded', [
                    'quotation_id' => $id,
                    'sp_remark' => $spResult->Remark,
                    'file' => $fileName,
                ]);
            } catch (\Exception $spEx) {
                @unlink($tempFile);
                $tempFile = null;

                Log::error('Excel Generation - SP call failed', [
                    'quotation_id' => $id,
                    'error' => $spEx->getMessage(),
                    'file' => $spEx->getFile(),
                    'line' => $spEx->getLine(),
                ]);

                return response()->json(
                    [
                        'success' => false,
                        'message' => 'Document registration failed: ' . $spEx->getMessage(),
                    ],
                    500,
                );
            }

            /*
             * Determine the office code using the same source
             * as sp_Quote_QuotationDocumentAdd:
             *
             * Left(QI.OfficeCode, 2)
             */
            $officeShortCode = strtoupper(substr(trim($quotation->OfficeCode ?? ''), 0, 2));

            if ($officeShortCode === '') {
                @unlink($tempFile);
                $tempFile = null;

                throw new \Exception('Unable to determine quotation office code.');
            }

            /*
             * The SP stores the document using:
             *
             * Quotation/{officeCode}/{filename}
             */
            $blobPath = 'Quotation/' . $officeShortCode . '/' . $fileName;

            $azureWarning = null;

            /*
             * Upload XLSX to Azure.
             */
            try {
                \Storage::disk('azure')->put($blobPath, file_get_contents($tempFile));

                Log::info('Excel Generation - Uploaded to Azure', [
                    'quotation_id' => $id,
                    'blob_path' => $blobPath,
                ]);
            } catch (\Exception $azureEx) {
                /*
                 * The document is already registered in the database,
                 * so Azure failure is non-fatal.
                 */
                Log::error('Excel Generation - Azure upload failed (non-fatal)', [
                    'quotation_id' => $id,
                    'blob_path' => $blobPath,
                    'error' => $azureEx->getMessage(),
                ]);

                $azureWarning = 'Excel registered but Azure upload failed: ' . $azureEx->getMessage();
            }

            /*
             * Remove local temporary XLSX.
             */
            @unlink($tempFile);
            $tempFile = null;

            Log::info('Excel Generation - Local temp file removed', [
                'path' => $tempFile,
            ]);

            /*
             * Determine version number.
             *
             * Excel currently uses the same DocumentType as PDF:
             * 'QuotationPDF'
             */
            $versionNumber = DB::table('Quote_Quotation_Document')
                ->where('QuotationInfoID', $quotation->ID)
                ->where('DocumentType', 'QuotationPDF')
                ->where('IsDeleted', 0)
                ->count();

            /*
             * Return JSON expected by the frontend.
             */
            return response()->json([
                'success' => true,
                'version' => $versionNumber,
                'label' => 'Version ' . $versionNumber,
                'url' => route('quotation.download-pdf', [$id, $versionNumber]),
                'warning' => $azureWarning,
            ]);
        } catch (\RuntimeException $e) {
            /*
             * RuntimeException is used by getQuotationHtml()
             * when the quotation-format SP itself reports a failure.
             *
             * Keep this separate so the frontend receives the actual
             * SP-related error instead of the generic Excel error.
             */
            if ($tempFile && file_exists($tempFile)) {
                @unlink($tempFile);
                $tempFile = null;
            }

            Log::error('Excel Generation - Quotation HTML/SP failure', [
                'id' => $id,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return response()->json(
                [
                    'success' => false,
                    'message' => $e->getMessage(),
                ],
                500,
            );
        } catch (\Throwable $e) {
            /*
             * Always clean up a temporary file if anything unexpected
             * happens after it has been created.
             */
            if ($tempFile && file_exists($tempFile)) {
                @unlink($tempFile);
                $tempFile = null;
            }

            Log::error('Excel Generation - Unexpected top-level exception', [
                'id' => $id,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(
                [
                    'success' => false,
                    'message' => 'Excel generation failed unexpectedly: ' . $e->getMessage(),
                ],
                500,
            );
        }
    }
    /**
     * Prepare the quotation header specifically for PhpSpreadsheet's HTML reader.
     *
     * The quotation HTML contains nested tables in the header. PhpSpreadsheet's
     * HTML reader can interpret those nested tables as additional spreadsheet
     * columns, which causes the header content to shift to the right.
     *
     * We flatten only the first two header rows into a simple 4-column structure.
     * The quotation charges and Terms & Conditions are left untouched.
     */
    private function prepareExcelHeader(string $html): string
    {
        libxml_use_internal_errors(true);

        $dom = new \DOMDocument();

        $loaded = $dom->loadHTML(
            '<?xml encoding="UTF-8">' . $html,
            LIBXML_NOERROR | LIBXML_NOWARNING | LIBXML_NOBLANKS,
        );

        if (!$loaded) {
            libxml_clear_errors();

            Log::warning('Excel Header Preparation - Could not parse HTML');

            return $html;
        }

        $xpath = new \DOMXPath($dom);

        $outerTable = $xpath->query('//body/table')->item(0);

        if (!$outerTable) {
            libxml_clear_errors();

            Log::warning('Excel Header Preparation - Outer quotation table not found');

            return $html;
        }

        $headerRows = $xpath->query('./tbody/tr', $outerTable);

        if (!$headerRows || $headerRows->length < 2) {
            libxml_clear_errors();

            return $html;
        }

        /*
         * Remove nested tables from ONLY the first two header rows.
         *
         * This is the actual cause of the upper section being shifted.
         * We deliberately do not touch the nested T&C/storage table.
         */
        for ($rowIndex = 0; $rowIndex < 2; $rowIndex++) {
            $row = $headerRows->item($rowIndex);

            if (!$row) {
                continue;
            }

            $nestedTables = [];

            foreach ($row->getElementsByTagName('table') as $nestedTable) {
                $nestedTables[] = $nestedTable;
            }

            foreach (array_reverse($nestedTables) as $nestedTable) {
                if (!$nestedTable->parentNode) {
                    continue;
                }

                /*
                 * Replace the nested table with its contents rather than
                 * allowing PhpSpreadsheet to interpret it as another table.
                 */
                while ($nestedTable->firstChild) {
                    $child = $nestedTable->firstChild;

                    $nestedTable->parentNode->insertBefore($child, $nestedTable);
                }

                $nestedTable->parentNode->removeChild($nestedTable);
            }
        }

        /*
         * The first row still has the company information and quotation
         * information in two cells. Keep it as a simple two-cell row.
         *
         * The important part is that the nested company table has already
         * been removed.
         */

        libxml_clear_errors();

        return $dom->saveHTML();
    }

    private function normalizeImagePathsForSpreadsheet(string $html): string
    {
        libxml_use_internal_errors(true);

        $dom = new \DOMDocument();

        $loaded = $dom->loadHTML(
            '<?xml encoding="UTF-8">' . $html,
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NOERROR | LIBXML_NOWARNING,
        );

        if (!$loaded) {
            libxml_clear_errors();

            return $html;
        }

        $logoPath = public_path('img/logo.png');

        \Log::info('Quotation Excel image normalization', [
            'logo_path' => $logoPath,
            'logo_exists' => file_exists($logoPath),
        ]);

        foreach (iterator_to_array($dom->getElementsByTagName('img')) as $image) {
            $src = trim($image->getAttribute('src'));

            if (
                $src === 'public/img/logo.png' ||
                $src === '/public/img/logo.png' ||
                $src === 'img/logo.png' ||
                $src === '/img/logo.png'
            ) {
                if (file_exists($logoPath)) {
                    $image->setAttribute('src', 'file:///' . str_replace('\\', '/', $logoPath));

                    \Log::info('Quotation Excel logo path replaced', [
                        'original_src' => $src,
                        'new_src' => $logoPath,
                    ]);
                } else {
                    \Log::error('Quotation Excel logo file not found', [
                        'expected_path' => $logoPath,
                        'original_src' => $src,
                    ]);
                }
            }
        }

        $result = $dom->saveHTML();

        libxml_clear_errors();

        return $result;
    }

    private function normalizeQuotationHeaderForSpreadsheet(string $html): string
    {
        libxml_use_internal_errors(true);

        $dom = new \DOMDocument('1.0', 'UTF-8');

        $loaded = $dom->loadHTML(
            '<?xml encoding="UTF-8">' . $html,
            LIBXML_NOERROR | LIBXML_NOWARNING | LIBXML_NOBLANKS,
        );

        if (!$loaded) {
            libxml_clear_errors();
            return $html;
        }

        $xpath = new \DOMXPath($dom);

        /*
         * Get the main quotation table.
         */
        $outerTable = $xpath->query('/html/body/table')->item(0);

        if (!$outerTable) {
            $outerTable = $xpath->query('//body/table')->item(0);
        }

        if (!$outerTable) {
            libxml_clear_errors();
            return $html;
        }

        /*
         * Only get rows directly belonging to the main table.
         * This prevents nested table rows from being included.
         */
        $rows = $xpath->query('./tbody/tr | ./tr', $outerTable);

        if ($rows->length < 2) {
            libxml_clear_errors();
            return $html;
        }

        $row1 = $rows->item(0);
        $row2 = $rows->item(1);

        $cells1 = $xpath->query('./td | ./th', $row1);

        $cells2 = $xpath->query('./td | ./th', $row2);

        /*
         * Expected current header:
         *
         * Row 1:
         *   Company/logo | Quotation
         *
         * Row 2:
         *   To | Date | Currency
         */
        if ($cells1->length < 2 || $cells2->length < 3) {
            libxml_clear_errors();
            return $html;
        }

        $companySource = $cells1->item(0);
        $quoteSource = $cells1->item(1);

        $toSource = $cells2->item(0);
        $dateSource = $cells2->item(1);
        $currSource = $cells2->item(2);

        /*
         * Helper for creating a new cell.
         */
        $makeCell = function (string $style = '', ?int $colspan = null) use ($dom) {
            $cell = $dom->createElement('td');

            if ($style !== '') {
                $cell->setAttribute('style', $style);
            }

            if ($colspan !== null) {
                $cell->setAttribute('colspan', (string) $colspan);
            }

            return $cell;
        };

        /*
         * Helper for copying the contents of an existing cell.
         */
        $copyChildren = function (\DOMElement $source, \DOMElement $target) {
            foreach (iterator_to_array($source->childNodes) as $child) {
                $target->appendChild($child->cloneNode(true));
            }
        };

        /*
         * ==========================================================
         * ROW 1
         * ==========================================================
         *
         * Convert:
         *
         *   [nested company table] [quotation]
         *
         * Into:
         *
         *   [Logo] [Company] [Quotation - colspan 2]
         */

        $newRow1 = $dom->createElement('tr');

        /*
         * Logo cell.
         */
        $imageCell = $makeCell('vertical-align:middle;');

        $image = $xpath->query('.//img', $companySource)->item(0);

        if ($image) {
            $imageCell->appendChild($image->cloneNode(true));
        }

        $newRow1->appendChild($imageCell);

        /*
         * Company details cell.
         */
        $companyCell = $makeCell('vertical-align:middle;');

        $companyClone = $companySource->cloneNode(true);

        /*
         * Remove the logo from the company-details copy.
         */
        while (($imageNode = $companyClone->getElementsByTagName('img')->item(0)) !== null) {
            $imageNode->parentNode->removeChild($imageNode);
        }

        $copyChildren($companyClone, $companyCell);

        $newRow1->appendChild($companyCell);

        /*
         * Quotation cell.
         *
         * It spans columns C and D so the quotation title
         * and quotation number remain together on the right.
         */
        $quoteCell = $makeCell('text-align:right;vertical-align:middle;', 2);

        $quoteDivs = $xpath->query('.//div', $quoteSource);

        if ($quoteDivs->length > 0) {
            $quoteCell->appendChild($quoteDivs->item(0)->cloneNode(true));

            if ($quoteDivs->length > 1) {
                $quoteCell->appendChild($quoteDivs->item(1)->cloneNode(true));
            }
        } else {
            $copyChildren($quoteSource, $quoteCell);
        }

        $newRow1->appendChild($quoteCell);

        /*
         * ==========================================================
         * ROW 2
         * ==========================================================
         *
         * Convert into:
         *
         *   [To - colspan 2] [Date] [Currency]
         */

        $newRow2 = $dom->createElement('tr');

        /*
         * To cell spans columns A and B.
         */
        $toCell = $makeCell('vertical-align:top;', 2);

        $copyChildren($toSource, $toCell);

        $newRow2->appendChild($toCell);

        /*
         * Date cell.
         */
        $dateCell = $makeCell('vertical-align:top;text-align:center;');

        $copyChildren($dateSource, $dateCell);

        $newRow2->appendChild($dateCell);

        /*
         * Currency cell.
         */
        $currCell = $makeCell('vertical-align:top;text-align:right;');

        $copyChildren($currSource, $currCell);

        $newRow2->appendChild($currCell);

        /*
         * Replace ONLY the first two rows.
         *
         * Everything below the header remains untouched.
         */
        $parent = $row1->parentNode;

        $parent->replaceChild($newRow1, $row1);

        $parent->replaceChild($newRow2, $row2);

        libxml_clear_errors();

        /*
         * Return the corrected HTML.
         */
        $htmlNode = $dom->getElementsByTagName('html')->item(0);

        return $htmlNode ? $dom->saveHTML($htmlNode) : $html;
    }

    /**
     * Normalize PDF-shaped quotation HTML for PhpSpreadsheet's HTML reader.
     *
     * Call this LAST, after comment-stripping and <html> dedup, on the final
     * single-document HTML — not on raw SP output.
     *
     * Why: PhpSpreadsheet's Html reader only imports <table> markup — <div>
     * sections (SP's General/Cargo Info blocks) get silently dropped, and
     * adjacent inline tags with no whitespace between them (e.g.
     * "</strong>undertakes") get concatenated once tags are stripped.
     */
    private function normalizeHtmlForSpreadsheet(string $html): string
    {
        libxml_use_internal_errors(true);

        $dom = new \DOMDocument();

        $dom->loadHTML('<?xml encoding="utf-8" ?>' . $html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

        libxml_clear_errors();

        $body = $dom->getElementsByTagName('body')->item(0);

        if (!$body) {
            return $html;
        }

        /*
         * Remove styles/scripts.
         */
        foreach (['style', 'script'] as $tagName) {
            foreach (iterator_to_array($body->getElementsByTagName($tagName)) as $node) {
                if ($node->parentNode) {
                    $node->parentNode->removeChild($node);
                }
            }
        }

        /*
         * Get only root-level tables.
         *
         * In the quotation HTML the header is actually made up of
         * two separate root-level tables:
         *
         *   1. Company/logo + quotation number
         *   2. Customer + date + currency
         *
         * Do not look for both rows inside the first table.
         */
        $rootTables = [];

        foreach (iterator_to_array($body->childNodes) as $node) {
            if ($node->nodeType === XML_ELEMENT_NODE && strtolower($node->nodeName) === 'table') {
                $rootTables[] = $node;
            }
        }

        /*
         * Normalize the quotation header.
         *
         * Desired Excel structure:
         *
         * Row 1:
         * A:D = Company / Logo
         * E:H = Quotation information
         *
         * Row 2:
         * A:D = Customer
         * E:F = Date
         * G:H = Currency
         */
        if (count($rootTables) >= 2) {
            $headerTable = $rootTables[0];
            $customerTable = $rootTables[1];

            /*
             * Get the table's direct row container.
             *
             * DOMDocument may insert <tbody>. If it does not exist,
             * use the table itself.
             */
            /*
             * Get only a DIRECT tbody child.
             *
             * This is important because the header table contains another
             * nested table for the logo/company information. We must not
             * accidentally select that nested table's tbody.
             */
            $getDirectTbody = static function (\DOMNode $table): ?\DOMElement {
                foreach (iterator_to_array($table->childNodes) as $child) {
                    if ($child->nodeType === XML_ELEMENT_NODE && strtolower($child->nodeName) === 'tbody') {
                        return $child;
                    }
                }

                return null;
            };

            $headerContainer = $getDirectTbody($headerTable) ?? $headerTable;
            $customerContainer = $getDirectTbody($customerTable) ?? $customerTable;

            /*
             * Get only direct <tr> children.
             */
            $getDirectRows = static function (\DOMNode $container): array {
                $rows = [];

                foreach (iterator_to_array($container->childNodes) as $node) {
                    if ($node->nodeType === XML_ELEMENT_NODE && strtolower($node->nodeName) === 'tr') {
                        $rows[] = $node;
                    }
                }

                return $rows;
            };

            /*
             * Get only direct td/th children from a row.
             */
            $getDirectCells = static function (\DOMNode $row): array {
                $cells = [];

                foreach (iterator_to_array($row->childNodes) as $node) {
                    if (
                        $node->nodeType === XML_ELEMENT_NODE &&
                        in_array(strtolower($node->nodeName), ['td', 'th'], true)
                    ) {
                        $cells[] = $node;
                    }
                }

                return $cells;
            };

            $headerRows = $getDirectRows($headerContainer);
            $customerRows = $getDirectRows($customerContainer);

            /*
             * The first table must contain:
             *
             *   [company] [quotation]
             */
            if (!empty($headerRows)) {
                $headerRow = $headerRows[0];
                $headerCells = $getDirectCells($headerRow);

                if (count($headerCells) >= 2) {
                    $companyCell = $headerCells[0];
                    $quotationCell = $headerCells[1];

                    /*
                     * ---------------------------------------------------------
                     * COMPANY AREA
                     * ---------------------------------------------------------
                     *
                     * Original structure:
                     *
                     * company td
                     *   └── nested table
                     *       └── logo td
                     *       └── company information td
                     *
                     * We create two NEW cells:
                     *
                     * A:B = Logo
                     * C:D = Company information
                     */

                    $logoCell = $dom->createElement('td');
                    $logoCell->setAttribute('colspan', '2');
                    $logoCell->setAttribute('style', 'vertical-align: middle;');

                    $companyInfoCell = $dom->createElement('td');
                    $companyInfoCell->setAttribute('colspan', '2');
                    $companyInfoCell->setAttribute('style', 'vertical-align: middle;');

                    /*
                     * Find the nested company table.
                     */
                    $companyNestedTable = null;

                    foreach (iterator_to_array($companyCell->childNodes) as $child) {
                        if ($child->nodeType === XML_ELEMENT_NODE && strtolower($child->nodeName) === 'table') {
                            $companyNestedTable = $child;
                            break;
                        }
                    }

                    if ($companyNestedTable) {
                        /*
                         * Find the DIRECT row inside the nested table.
                         */
                        $nestedRow = null;

                        foreach (iterator_to_array($companyNestedTable->childNodes) as $child) {
                            if ($child->nodeType === XML_ELEMENT_NODE && strtolower($child->nodeName) === 'tbody') {
                                foreach (iterator_to_array($child->childNodes) as $row) {
                                    if ($row->nodeType === XML_ELEMENT_NODE && strtolower($row->nodeName) === 'tr') {
                                        $nestedRow = $row;
                                        break;
                                    }
                                }

                                if ($nestedRow) {
                                    break;
                                }
                            } elseif ($child->nodeType === XML_ELEMENT_NODE && strtolower($child->nodeName) === 'tr') {
                                $nestedRow = $child;
                                break;
                            }
                        }

                        /*
                         * Extract logo + company information.
                         */
                        if ($nestedRow) {
                            $nestedCells = $getDirectCells($nestedRow);

                            if (count($nestedCells) >= 2) {
                                /*
                                 * Copy the CONTENT of the original cells into
                                 * newly-created cells.
                                 *
                                 * We intentionally do not move the original
                                 * DOM nodes.
                                 */
                                foreach (iterator_to_array($nestedCells[0]->childNodes) as $child) {
                                    $logoCell->appendChild($child->cloneNode(true));
                                }

                                foreach (iterator_to_array($nestedCells[1]->childNodes) as $child) {
                                    $companyInfoCell->appendChild($child->cloneNode(true));
                                }
                            }
                        }
                    }

                    /*
                     * ---------------------------------------------------------
                     * QUOTATION AREA
                     * ---------------------------------------------------------
                     *
                     * Copy the quotation cell content into a new cell.
                     */
                    $newQuotationCell = $dom->createElement('td');

                    $newQuotationCell->setAttribute('colspan', '4');
                    $newQuotationCell->setAttribute('style', 'text-align: right; vertical-align: middle;');

                    foreach (iterator_to_array($quotationCell->childNodes) as $child) {
                        $newQuotationCell->appendChild($child->cloneNode(true));
                    }

                    /*
                     * ---------------------------------------------------------
                     * BUILD NEW HEADER ROW
                     * ---------------------------------------------------------
                     */
                    $newHeaderRow = $dom->createElement('tr');

                    $newHeaderRow->appendChild($logoCell);
                    $newHeaderRow->appendChild($companyInfoCell);
                    $newHeaderRow->appendChild($newQuotationCell);

                    /*
                     * IMPORTANT:
                     *
                     * Instead of replaceChild(), insert the new row first,
                     * then remove the old row.
                     *
                     * This avoids the DOM "Not Found Error".
                     */
                    $headerContainer->insertBefore($newHeaderRow, $headerRow);

                    $headerContainer->removeChild($headerRow);
                }
            }

            /*
             * The second table must contain:
             *
             *   [customer] [date] [currency]
             */
            if (!empty($customerRows)) {
                $customerCells = $getDirectCells($customerRows[0]);

                if (count($customerCells) >= 3) {
                    $toCell = $customerCells[0];
                    $dateCell = $customerCells[1];
                    $currencyCell = $customerCells[2];

                    $toCell->setAttribute('colspan', '4');
                    $toCell->setAttribute('style', 'vertical-align: top;');

                    $dateCell->setAttribute('colspan', '2');
                    $dateCell->setAttribute('style', 'vertical-align: top; text-align: center;');

                    $currencyCell->setAttribute('colspan', '2');
                    $currencyCell->setAttribute('style', 'vertical-align: top; text-align: right;');

                    $newCustomerRow = $dom->createElement('tr');

                    $newCustomerRow->appendChild($toCell);
                    $newCustomerRow->appendChild($dateCell);
                    $newCustomerRow->appendChild($currencyCell);

                    /*
                     * Add the customer/date/currency row to the main
                     * header table after the quotation header row.
                     */
                    $headerContainer->appendChild($newCustomerRow);

                    /*
                     * The second table has now been transferred into
                     * the first table, so remove the empty second table.
                     */
                    if ($customerTable->parentNode) {
                        $customerTable->parentNode->removeChild($customerTable);
                    }
                }
            }
        }

        /*
         * Wrap non-table root-level elements into tables.
         *
         * This keeps the HTML structure compatible with
         * PhpSpreadsheet's HTML reader.
         */
        $children = iterator_to_array($body->childNodes);
        $pendingTbody = null;

        foreach ($children as $node) {
            if ($node->nodeType !== XML_ELEMENT_NODE) {
                continue;
            }

            if (strtolower($node->nodeName) === 'table') {
                $pendingTbody = null;
                continue;
            }

            if ($pendingTbody === null) {
                $wrapper = $dom->createElement('table');
                $pendingTbody = $dom->createElement('tbody');

                $wrapper->appendChild($pendingTbody);
                $body->insertBefore($wrapper, $node);
            }

            $tr = $dom->createElement('tr');
            $td = $dom->createElement('td');

            $td->appendChild($node);
            $tr->appendChild($td);
            $pendingTbody->appendChild($tr);
        }

        /*
         * Merge ONLY root-level tables.
         *
         * Nested tables such as the Terms & Conditions storage table
         * are intentionally left untouched.
         */
        $tables = [];

        foreach (iterator_to_array($body->childNodes) as $node) {
            if ($node->nodeType === XML_ELEMENT_NODE && strtolower($node->nodeName) === 'table') {
                $tables[] = $node;
            }
        }

        if (count($tables) > 1) {
            $mainTable = $tables[0];

            $mainTbody = $mainTable->getElementsByTagName('tbody')->item(0);

            if (!$mainTbody) {
                $mainTbody = $mainTable;
            }

            for ($i = 1; $i < count($tables); $i++) {
                $table = $tables[$i];

                foreach (iterator_to_array($table->childNodes) as $child) {
                    if ($child->nodeType === XML_ELEMENT_NODE && strtolower($child->nodeName) === 'tbody') {
                        foreach (iterator_to_array($child->childNodes) as $row) {
                            if ($row->nodeType === XML_ELEMENT_NODE && strtolower($row->nodeName) === 'tr') {
                                $mainTbody->appendChild($row);
                            }
                        }
                    } elseif ($child->nodeType === XML_ELEMENT_NODE && strtolower($child->nodeName) === 'tr') {
                        $mainTbody->appendChild($child);
                    }
                }

                if ($table->parentNode) {
                    $table->parentNode->removeChild($table);
                }
            }
        }
        /*
         * ---------------------------------------------------------
         * TERMS & CONDITIONS
         * ---------------------------------------------------------
         *
         * Convert the HTML Terms & Conditions list into
         * spreadsheet-friendly rows.
         */

        $termsDiv = null;

        foreach (iterator_to_array($body->getElementsByTagName('div')) as $div) {
            $hasHeading = false;
            $hasList = false;

            foreach (iterator_to_array($div->childNodes) as $child) {
                if ($child->nodeType !== XML_ELEMENT_NODE) {
                    continue;
                }

                $tag = strtolower($child->nodeName);

                if ($tag === 'h3') {
                    $headingText = trim($child->textContent);

                    if (stripos($headingText, 'Terms & Conditions') !== false) {
                        $hasHeading = true;
                    }
                }

                if ($tag === 'ol') {
                    $hasList = true;
                }
            }

            if ($hasHeading && $hasList) {
                $termsDiv = $div;
                break;
            }
        }

        if ($termsDiv) {
            $termsTable = $dom->createElement('table');
            $termsTable->setAttribute('width', '100%');
            $termsTable->setAttribute('cellpadding', '4');
            $termsTable->setAttribute('cellspacing', '0');

            $termsTbody = $dom->createElement('tbody');
            $termsTable->appendChild($termsTbody);

            /*
             * Terms & Conditions heading
             */
            $headingRow = $dom->createElement('tr');

            $headingCell = $dom->createElement('td');
            $headingCell->setAttribute('colspan', '8');
            $headingCell->setAttribute('style', 'font-weight:700; font-size:12px; padding:5px 7px;');

            $headingCell->appendChild($dom->createTextNode('Terms & Conditions'));

            $headingRow->appendChild($headingCell);
            $termsTbody->appendChild($headingRow);

            /*
             * Find the ordered list
             */
            $termsList = null;

            foreach (iterator_to_array($termsDiv->childNodes) as $child) {
                if ($child->nodeType === XML_ELEMENT_NODE && strtolower($child->nodeName) === 'ol') {
                    $termsList = $child;
                    break;
                }
            }

            /*
             * Convert every <li> into its own row.
             */
            if ($termsList) {
                $letter = 'a';

                foreach (iterator_to_array($termsList->childNodes) as $li) {
                    if ($li->nodeType !== XML_ELEMENT_NODE || strtolower($li->nodeName) !== 'li') {
                        continue;
                    }

                    /*
                     * Check for the storage table.
                     */
                    $nestedTable = null;

                    foreach (iterator_to_array($li->childNodes) as $child) {
                        if ($child->nodeType === XML_ELEMENT_NODE && strtolower($child->nodeName) === 'table') {
                            $nestedTable = $child;
                            break;
                        }
                    }

                    /*
                     * Main text row.
                     */
                    $textRow = $dom->createElement('tr');

                    $textCell = $dom->createElement('td');
                    $textCell->setAttribute('colspan', '8');
                    $textCell->setAttribute('style', 'padding:3px 7px; vertical-align:top;');

                    $textCell->appendChild($dom->createTextNode('(' . $letter . ') '));

                    /*
                     * Copy the list item content,
                     * excluding the nested table.
                     */
                    foreach (iterator_to_array($li->childNodes) as $child) {
                        if ($child->nodeType === XML_ELEMENT_NODE && strtolower($child->nodeName) === 'table') {
                            continue;
                        }

                        $textCell->appendChild($child->cloneNode(true));
                    }

                    $textRow->appendChild($textCell);
                    $termsTbody->appendChild($textRow);

                    /*
                     * Add the storage table separately.
                     */
                    if ($nestedTable) {
                        $tableRow = $dom->createElement('tr');

                        $tableCell = $dom->createElement('td');
                        $tableCell->setAttribute('colspan', '8');
                        $tableCell->setAttribute('style', 'padding:3px 7px;');

                        $storageTable = $nestedTable->cloneNode(true);

                        /*
                         * Remove HTML spacing that can cause oversized rows
                         * when PhpSpreadsheet imports the nested storage table.
                         */
                        $storageTable->removeAttribute('style');
                        $storageTable->setAttribute('cellpadding', '2');
                        $storageTable->setAttribute('cellspacing', '0');
                        $storageTable->setAttribute('border', '1');

                        foreach (iterator_to_array($storageTable->getElementsByTagName('tr')) as $storageRow) {
                            $storageRow->removeAttribute('height');
                        }

                        $tableCell->appendChild($storageTable);
                        $tableRow->appendChild($tableCell);
                        $termsTbody->appendChild($tableRow);
                    }

                    $letter++;
                }
            }

            /*
             * Replace the original Terms & Conditions div.
             */
            if ($termsDiv->parentNode) {
                $termsDiv->parentNode->replaceChild($termsTable, $termsDiv);
            }
        }

        /*
         * Keep the existing whitespace normalization.
         */
        return preg_replace('/(<\/?[a-zA-Z]+>)\s*(?=\w|<[a-zA-Z])/', '$1&nbsp;', $dom->saveHTML());
    }

    /**
     * Flatten any <table> descendants of $cell into plain markup so
     * PhpSpreadsheet's Html reader (which does not support nested
     * tables) reads $cell's content as ordinary text/inline elements.
     *
     * Each nested row becomes a line (<br>), each nested cell in that
     * row is separated by a space, images are preserved as-is.
     */
    private function unwrapNestedTables(\DOMDocument $dom, \DOMElement $cell): void
    {
        while ($nestedTable = $cell->getElementsByTagName('table')->item(0)) {
            $fragmentNodes = [];

            $rows = $nestedTable->getElementsByTagName('tr');

            foreach (iterator_to_array($rows) as $rowIndex => $row) {
                if ($rowIndex > 0) {
                    $fragmentNodes[] = $dom->createElement('br');
                }

                foreach (iterator_to_array($row->childNodes) as $cellIndex => $nestedCell) {
                    if (
                        $nestedCell->nodeType !== XML_ELEMENT_NODE ||
                        !in_array(strtolower($nestedCell->nodeName), ['td', 'th'], true)
                    ) {
                        continue;
                    }

                    if ($cellIndex > 0) {
                        $fragmentNodes[] = $dom->createTextNode(' ');
                    }

                    foreach (iterator_to_array($nestedCell->childNodes) as $child) {
                        $fragmentNodes[] = $child;
                    }
                }
            }

            foreach ($fragmentNodes as $node) {
                $nestedTable->parentNode->insertBefore($node, $nestedTable);
            }

            $nestedTable->parentNode->removeChild($nestedTable);
        }
    }
    //  *   @QuoteFormat VARCHAR(MAX) OUTPUT
    //  *
    //  * On internal error the SP sets @QuoteFormat = 'Failed (Reason) - <message>'
    //  * instead of raising an exception, so we must detect that string explicitly.
    //  *
    //  * @param  string $quoteNo  The QuoteNo value (e.g. '8270'), NOT the UUID
    //  * @return string|null      Complete HTML string, or null on failure
    //  */
    private function getQuotationHtml(string $quotationId, string $quoteType = 'Std. PDF'): ?string
    {
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
        EXEC dbo.sp_Quote_QuotationFormat ?, ?,
            @QuoteFormat = @QuoteFormat OUTPUT;
        SELECT @QuoteFormat AS QuoteFormat;
    ";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([trim($quotationId), $quoteType]);

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
                        break;
                    }
                } catch (\PDOException $e) {
                    continue;
                }
            } while ($stmt->nextRowset());

            // SP CATCH block sets @QuoteFormat = 'Failed (Reason) - <message>'
            // instead of raising — detect this and treat as a failure.
            if (!empty($html) && str_starts_with(trim($html), 'Failed (Reason)')) {
                Log::error('getQuotationHtml - sp_Quote_QuotationFormat failure (DBA action required)', [
                    'quotation_id' => $quotationId,
                    'quote_type' => $quoteType,
                    'sp_message' => $html,
                ]);
                throw new \RuntimeException('sp_Quote_QuotationFormat: ' . $html);
            }

            if (empty($html) || trim($html) === '') {
                return null;
            }
            return $html;
        } catch (\RuntimeException $e) {
            throw $e; // real SP failure — let the caller decide (DBA flag)
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Fetch the structured quotation JSON from sp_Quote_QuotationFormat.
     * The SP takes only @QuotationID and returns JSON in @QuoteFormat.
     *
     * @throws \RuntimeException when the SP reports a failure or returns unusable output
     */
    private function getQuotationData(string $quotationId, bool $withEquipmentRows = true): array
    {
        $quotationId = trim($quotationId);
        if ($quotationId === '') {
            throw new \RuntimeException('getQuotationData: quotationId is empty');
        }

        $stmt = DB::getPdo()->prepare("
            SET NOCOUNT ON;
            DECLARE @QuoteFormat VARCHAR(MAX);
            EXEC dbo.sp_Quote_QuotationFormat ?, @QuoteFormat = @QuoteFormat OUTPUT;
            SELECT @QuoteFormat AS QuoteFormat;
        ");
        $stmt->execute([$quotationId]);

        // The SP creates a temp table, which can produce empty result sets before the SELECT.
        $raw = null;
        do {
            try {
                $rows = $stmt->fetchAll(\PDO::FETCH_OBJ);
                if (!empty($rows) && isset($rows[0]->QuoteFormat)) {
                    $raw = (string) $rows[0]->QuoteFormat;
                    break;
                }
            } catch (\PDOException $e) {
                continue;
            }
        } while ($stmt->nextRowset());

        // NULL output means the SP's Terms join found no row: no T&C template applied.
        if ($raw === null || trim($raw) === '') {
            throw new \RuntimeException(
                'No quotation data returned from SP. Ensure a Terms & Conditions template is applied before generating.',
            );
        }

        Log::info('getQuotationData - raw SP output', [
            'quotation_id' => $quotationId,
            'json_length' => strlen($raw ?? ''),
            'json' => $raw,
        ]);

        if (str_starts_with(trim($raw), 'Failed (Reason)')) {
            Log::error('getQuotationData - sp_Quote_QuotationFormat failure (DBA action required)', [
                'quotation_id' => $quotationId,
                'sp_message' => $raw,
            ]);
            throw new \RuntimeException('sp_Quote_QuotationFormat: ' . $raw);
        }

        $data = json_decode($raw, true);
        if (!is_array($data)) {
            Log::error('getQuotationData - JSON decode failed', [
                'quotation_id' => $quotationId,
                'json_error' => json_last_error_msg(),
            ]);
            throw new \RuntimeException('sp_Quote_QuotationFormat: invalid JSON response');
        }

        // Workaround: the SP returns @OfficeJson as Customer and never uses @ContractJson.
        $data['Customer'] = $this->getQuotationCustomer($quotationId);

        // Workaround: add the VAT description the SP omits, matched on description, rate and amount.
        $vatDescriptions = $this->getQuotationVatDescriptions($quotationId);
        foreach ($data['ChargeDetails'] ?? [] as $index => $charge) {
            if (!isset($charge['VATRate'])) {
                continue;
            }
            $key = $this->vatKey($charge['ChargeDescription'] ?? '', $charge['Rate'] ?? 0, $charge['Amount'] ?? 0);
            if (isset($vatDescriptions[$key])) {
                $data['ChargeDetails'][$index]['VATDescription'] = $vatDescriptions[$key];
            }
        }
        // Equipment-based charges are not in the JSON (it only carries their count),        // so read them from sp_Quote_QuotationFormat_Data.
        if ($withEquipmentRows && (int) ($data['EquipmentBasedChargesNo'] ?? 0) > 0) {
            $data['EquipmentRows'] = $this->getQuotationEquipmentRows($quotationId);

            // The SP swallows its own errors and returns nothing, so an empty result here is a failure.
            if (empty($data['EquipmentRows'])) {
                Log::error('getQuotationData - no equipment rows returned (DBA action required)', [
                    'quotation_id' => $quotationId,
                    'equipment_charge_count' => $data['EquipmentBasedChargesNo'],
                ]);
                throw new \RuntimeException(
                    'sp_Quote_QuotationFormat_Data returned no rows for a quotation with equipment-based charges',
                );
            }
        }

        Log::info('getQuotationData - retrieved', [
            'quotation_id' => $quotationId,
            'charge_count' => count($data['ChargeDetails'] ?? []),
            'equipment_row_count' => count($data['EquipmentRows'] ?? []),
        ]);

        return $data;
    }

    /**
     * Returns the HTML charge table for the Excel export.
     *
     * sp_Quote_QuotationFormat_DataExcel is not deployed in production, so this
     * method falls back to building the table in PHP from the same JSON
     * sp_Quote_QuotationFormat returns. When the SP is later deployed, its
     * output is used directly and the PHP fallback is skipped.
     */
    private function getQuotationExcelTable(string $quotationId, ?array $preloadedData = null): string
    {
        // Try the SP first — if it exists and returns a table, use it.
        try {
            $stmt = DB::getPdo()->prepare("
            SET NOCOUNT ON;
            DECLARE @TableList VARCHAR(MAX);
            EXEC dbo.sp_Quote_QuotationFormat_DataExcel ?, @TableList = @TableList OUTPUT;
            SELECT @TableList AS TableList;
        ");
            $stmt->execute([trim($quotationId)]);

            do {
                try {
                    $rows = $stmt->fetchAll(\PDO::FETCH_OBJ);
                    if (!empty($rows) && isset($rows[0]->TableList) && trim((string) $rows[0]->TableList) !== '') {
                        return (string) $rows[0]->TableList;
                    }
                } catch (\PDOException $e) {
                    continue;
                }
            } while ($stmt->nextRowset());
        } catch (\PDOException $e) {
            Log::info('getQuotationExcelTable - SP unavailable, building table in PHP', [
                'quotation_id' => $quotationId,
                'error' => $e->getMessage(),
            ]);
        }

        // SP missing or returned nothing — build the charge table in PHP.
        return $this->buildChargeTableHtml($quotationId, $preloadedData);
    }

    /**
     * Build the charge table HTML from sp_Quote_QuotationFormat JSON.
     *
     * Renders categories, charges, and both SP total rows (Sub Total, Total With
     * VAT) as a single HTML table. The Excel HTML reader preserves table layout
     * reliably, unlike raw <div> / <p> markup.
     */
    private function buildChargeTableHtml(string $quotationId, ?array $preloadedData = null): string
    {
        try {
            $data = $preloadedData ?? $this->getQuotationData($quotationId, false);
        } catch (\Throwable $e) {
            Log::warning('buildChargeTableHtml - could not load quotation data', [
                'quotation_id' => $quotationId,
                'error' => $e->getMessage(),
            ]);
            return '';
        }

        $charges = $data['ChargeDetails'] ?? [];
        if (empty($charges)) {
            return '';
        }

        $esc = fn($v) => htmlspecialchars((string) $v, ENT_QUOTES);

        // Group by ChargeCatagory, preserving SP order.
        $groups = [];
        foreach ($charges as $charge) {
            $cat = trim((string) ($charge['ChargeCatagory'] ?? ''));
            $groups[$cat][] = $charge;
        }

        $spTotalCategories = ['Sub Total', 'Total With VAT', 'Total'];

        $html =
            '<table border="1" cellpadding="4" cellspacing="0" ' .
            'style="border-collapse:collapse; font-size:11px; width:100%;">';

        $html .=
            '<tr style="background:#DCE6F1; font-weight:bold;">' .
            '<td>Charge Description Activity</td>' .
            '<td>Based on &amp; UOM</td>' .
            '<td>Qty.</td>' .
            '<td>Rate</td>' .
            '<td>Quote Amt.</td>' .
            '<td>VAT</td>' .
            '</tr>';

        foreach ($groups as $cat => $rows) {
            $isTotal = in_array($cat, $spTotalCategories, true);

            if ($isTotal) {
                foreach ($rows as $charge) {
                    $html .=
                        '<tr style="background:#E2EFDA; font-weight:bold;">' .
                        '<td colspan="4" align="right">' .
                        $esc($cat) .
                        '</td>' .
                        '<td align="right">' .
                        number_format((float) ($charge['Amount'] ?? 0), 2) .
                        '</td>' .
                        '<td align="right">' .
                        number_format((float) ($charge['VATAmount'] ?? 0), 2) .
                        '</td>' .
                        '</tr>';
                }
                continue;
            }

            // Category band
            if ($cat !== '') {
                $html .=
                    '<tr style="background:#513D6C; color:#fff; font-weight:bold;">' .
                    '<td colspan="6">' .
                    $esc($cat) .
                    '</td>' .
                    '</tr>';
            }

            // Individual charge rows
            foreach ($rows as $charge) {
                $html .=
                    '<tr>' .
                    '<td>' .
                    $esc($charge['ChargeDescription'] ?? '') .
                    '</td>' .
                    '<td>' .
                    $esc($charge['BasedOn'] ?? '') .
                    '</td>' .
                    '<td align="right">' .
                    number_format((float) ($charge['Quantity'] ?? 0), 2) .
                    '</td>' .
                    '<td align="right">' .
                    number_format((float) ($charge['Rate'] ?? 0), 2) .
                    '</td>' .
                    '<td align="right">' .
                    number_format((float) ($charge['Amount'] ?? 0), 2) .
                    '</td>' .
                    '<td align="right">';

                if (isset($charge['VATRate'])) {
                    $html .= number_format((float) $charge['VATRate'], 2) . '%';
                }
                if (isset($charge['VATAmount'])) {
                    $html .= '<br>' . number_format((float) $charge['VATAmount'], 2);
                }

                $html .= '</td>' . '</tr>';
            }
        }

        $html .= '</table>';

        return $html;
    }
    private function buildExcelQuotationHtml(array $data, string $tableList): string
    {
        return view('quotation.excel_document', ['quote' => $data, 'tableList' => $tableList])->render();
    }

    // Reads the pivoted equipment charges. Columns: ChargeCatagories, Description, then per equipment
    // code "<Code> Rate", "<Code>" and "<Code> VAT". Values arrive already formatted as strings.
    private function getQuotationEquipmentRows(string $quotationId): array
    {
        // The SP builds the global temp table ##Result, so concurrent runs would overwrite each other.
        try {
            return Cache::lock('sp_quote_quotationformat_data', 30)->block(
                15,
                fn() => $this->fetchQuotationEquipmentRows($quotationId),
            );
        } catch (\Illuminate\Contracts\Cache\LockTimeoutException $e) {
            throw new \RuntimeException(
                'Equipment charges are being generated for another quotation. Please retry in a moment.',
            );
        }
    }

    private function fetchQuotationEquipmentRows(string $quotationId): array
    {
        $stmt = DB::getPdo()->prepare('SET NOCOUNT ON; EXEC dbo.sp_Quote_QuotationFormat_Data ?');
        $stmt->execute([trim($quotationId)]);

        do {
            try {
                $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

                if (empty($rows)) {
                    continue;
                }

                // SP emitted a real data set — the column we expect
                if (array_key_exists('ChargeCatagories', $rows[0])) {
                    return $rows;
                }

                // SP emitted a single-column response — either an error string
                // ('Error Message - ...') or a placeholder ('' / NULL).
                $firstValue = reset($rows[0]);
                $firstValue = is_string($firstValue) ? trim($firstValue) : $firstValue;

                if (is_string($firstValue) && str_starts_with($firstValue, 'Error Message - ')) {
                    throw new \RuntimeException($firstValue);
                }
                // Any other single-column response means "no equipment data" —
                // fall through and try the next rowset / eventually return [].
            } catch (\PDOException $e) {
                continue;
            }
        } while ($stmt->nextRowset());

        return [];
    }

    private function getQuotationCustomer(string $quotationId): array
    {
        $quote = DB::table('Quote_QuotationInfo')->where('ID', trim($quotationId))->first();
        if (!$quote) {
            return [];
        }
        $pad = fn($value) => trim((string) $value) === '' ? '' : ' ' . $value;

        return [
            'Name' => (string) ($quote->pri_partnermaster_name ?? ''),
            'Address1' => (string) ($quote->pri_partner_steet ?? ''),
            'Address2' => (string) ($quote->pri_partner_steet2 ?? ''),
            'City' => (string) ($quote->pri_partner_city ?? ''),
            'State' => $pad($quote->pri_partner_statename ?? ''),
            'Pin' => $pad($quote->pri_partner_zip ?? ''),
            'Country' => (string) ($quote->countryname ?? ''),
        ];
    }

    // Workaround: sp_Quote_QuotationFormat omits the VAT description. Read-only, cosmetic, so a failure only logs.
    // Workaround: sp_Quote_QuotationFormat omits the VAT description. Read-only, cosmetic, so a failure only logs.
    private function getQuotationVatDescriptions(string $quotationId): array
    {
        try {
            $rows = DB::select(
                "SELECT QS.Description, QS.QuoteChargeRate, QS.QuoteChargeAmount, MAX(BT.Description) AS VATDescription
             FROM Quote_Quotation_ChargeSheet QS WITH (NOLOCK)
             INNER JOIN Quote_QuotationInfo QI WITH (NOLOCK) ON QI.ID = QS.QuotationInfoID AND QI.ID = ?
             INNER JOIN [FITSCM].dbo.Bill_ChargeCodeOfficeMap BCO WITH (NOLOCK) ON BCO.ChargeCodeId = QS.ChargeCodeId AND BCO.IsActive = 1
             INNER JOIN [FITSCM].dbo.Bill_ChargeCodeOfficeTaxMap BCOT WITH (NOLOCK) ON BCOT.ChargeCodeOfficeId = BCO.Id AND BCOT.IsActive = 1 AND BCOT.ApplicableFor = 'Revenue'
             INNER JOIN [FITSCM].dbo.Bill_TaxCodeGroup BT WITH (NOLOCK) ON BT.Id = BCOT.TaxGroupId
             INNER JOIN [FITSCM].dbo.Bill_TaxCodeRate BTR WITH (NOLOCK) ON BTR.TaxCodeGroupId = BT.Id
                 AND CAST(GETDATE() AS date) BETWEEN CAST(BTR.FromDate AS date) AND CAST(BTR.ToDate AS date)
             INNER JOIN [FITSCM].dbo.Cust_Office CO WITH (NOLOCK) ON CO.Id = BCO.OfficeId AND CO.Code = QI.OfficeCode
             WHERE QS.IsDeleted = 0 AND QS.IsRevenue = 1
             GROUP BY QS.Description, QS.QuoteChargeRate, QS.QuoteChargeAmount",
                [trim($quotationId)],
            );
        } catch (\Throwable $e) {
            Log::warning('getQuotationVatDescriptions - lookup failed', ['error' => $e->getMessage()]);
            return [];
        }

        $map = [];
        foreach ($rows as $row) {
            $map[$this->vatKey($row->Description, $row->QuoteChargeRate, $row->QuoteChargeAmount)] =
                $row->VATDescription;
        }

        return $map;
    }

    private function vatKey($description, $rate, $amount): string
    {
        return trim((string) $description) .
            '|' .
            number_format((float) $rate, 2, '.', '') .
            '|' .
            number_format((float) $amount, 2, '.', '');
    }

    // The equipment codes are read from the "<Code> Rate" column names, in the SP's own column order.
    private function shapeQuotationEquipment(array $rows): array
    {
        $codes = [];
        foreach (array_keys($rows[0] ?? []) as $column) {
            if (str_ends_with($column, ' Rate')) {
                $codes[] = substr($column, 0, -5);
            }
        }

        // The Data SP sorts 'Custom Charges' but the main SP uses 'Customs Charges'; re-rank so customs rows stay before the totals.
        $rank = fn(array $row) => match (trim($row['ChargeCatagories'] ?? '')) {
            'No of Container' => 1,
            'Origin Charges' => 2,
            'Freight' => 3,
            'Custom Charges', 'Customs Charges' => 4,
            'Destination Charges' => 5,
            'Delivery Charges' => 6,
            'Sub Total' => 7,
            'Total' => 8,
            default => 9,
        };
        usort($rows, fn($a, $b) => $rank($a) <=> $rank($b)); // stable in PHP 8+, keeps the SP's Description order

        // Rows with a blank category sort after Total in the SP (Else 9); keep them ahead of the totals.
        $rank = fn(array $row) => match (trim($row['ChargeCatagories'] ?? '')) {
            'No of Container' => 1,
            'Sub Total' => 8,
            'Total' => 9,
            default => 5,
        };
        usort($rows, fn($a, $b) => $rank($a) <=> $rank($b)); // stable in PHP 8+, keeps the SP's order

        return ['codes' => $codes, 'rows' => $rows];
    }

    // Groups ChargeDetails by ChargeCatagory. The SP already orders the rows    // (Origin, Freight, Customs, Destination, Delivery, others) and groupBy keeps that order.
    private function groupQuotationCharges(array $data): array
    {
        return collect($data['ChargeDetails'] ?? [])
            ->groupBy(fn($charge) => trim((string) ($charge['ChargeCatagory'] ?? '')))
            ->map(fn($rows) => $rows->values()->all())
            ->all();
    }

    private function buildQuotationHtml(array $data, bool $showVat = false, ?string $allInOneRemark = null): string
    {
        return view('quotation.pdf_document', [
            'quote' => $data,
            'showVat' => $showVat,
            'allInOneRemark' => $allInOneRemark,
            'chargeGroups' => $this->groupQuotationCharges($data),
            'equipment' => $this->shapeQuotationEquipment($data['EquipmentRows'] ?? []),
            'logoPath' => public_path('img/logo.png'),
        ])->render();
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
            $versionCounter = 0;

            foreach ($documents as $doc) {
                $filePath = $doc->FilePath;
                $fileExists = false;

                // Check Azure if path starts with 'Quotation/'
                if (str_starts_with($filePath, 'Quotation/')) {
                    $fileExists = \Storage::disk('azure')->exists($filePath);
                } else {
                    // Legacy local disk check
                    $relativePath = $this->normalizeFilePath($filePath);
                    $fileExists = \Storage::disk('public')->exists($relativePath);
                }

                if (!$fileExists) {
                    Log::warning('getQuotationVersions - File missing, skipping', [
                        'quotation_id' => $id,
                        'file_path' => $filePath,
                    ]);
                    continue;
                }

                $ext = strtoupper(pathinfo($doc->Name, PATHINFO_EXTENSION));
                $isVat = $ext === 'PDF' && preg_match('/_vat\b/i', $doc->Name);
                $isAllInOne = $ext === 'PDF' && preg_match('/_allinone\b/i', $doc->Name);

                $versionCounter++;
                $versions[] = [
                    'version' => $versionCounter,
                    'label' => 'Version ' . $versionCounter,
                    'url' => route('quotation.download-pdf', [$id, $versionCounter]),
                    'created_on' => $doc->CreatedOn,
                    'file_name' => $doc->Name,
                    'file_type' => $isVat ? 'PDF - VAT' : ($isAllInOne ? 'PDF - All in One' : $ext),
                    'doc_id' => $doc->Id,
                ];
            }

            return response()->json([
                'success' => true,
                'versions' => $versions,
            ]);
        } catch (\Exception $e) {
            Log::error('getQuotationVersions - Failed', [
                'id' => $id,
                'error' => $e->getMessage(),
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
            // Fetch quotation to get pri_partnermaster_name for matching
            $quotation = DB::table('Quote_QuotationInfo')
                ->where('bookingno', $bookingNo)
                ->first(['pri_partnermaster_name']);
            if (!$quotation) {
                return response()->json(['success' => true, 'emails' => []]);
            }

            $emails = DB::table('email_emailscheduler')
                ->where('ClientName', $quotation->pri_partnermaster_name)
                ->where('SchedulerType', 'QuotationEmail')
                ->orderBy('SubmittedOn', 'desc')
                ->get([
                    'SubmittedOn as sent_on',
                    'EmailTo as to_email',
                    'Status as status',
                    'SendRemark as send_remark',
                ]);

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
            if (!$quotationId) {
                $quotationId = DB::table('Quote_QuotationInfo')->where('bookingno', $bookingNo)->value('ID');

                if (!$quotationId) {
                    return response()->json(['success' => false, 'message' => 'Quotation not found'], 404);
                }
            }

            $pdo = DB::connection()->getPdo();
            $spResult = $this->fetchChargesFromSP($pdo, $quotationId);

            // fetchChargesFromSP returns ['charges' => [...], 'message' => ...]
            // — unwrap before iterating.
            $allCharges = $spResult['charges'];
            $spMessage = $spResult['message'];

            // Supplementary read: the SP does not return ChargeCatagories /
            // EstBaseAmount / BaseExchRate, so fetch them directly for the same IDs.
            $chargeIds = [];
            foreach ($allCharges as $c) {
                if (!empty($c['CostChargeRowID'])) {
                    $chargeIds[] = $c['CostChargeRowID'];
                }
                if (!empty($c['RevenueChargeRowID'])) {
                    $chargeIds[] = $c['RevenueChargeRowID'];
                }
            }

            $supplement = [];
            if (!empty($chargeIds)) {
                $sheetRows = DB::table('Quote_Quotation_ChargeSheet')
                    ->whereIn('Id', $chargeIds)
                    ->get(['Id', 'ChargeCatagories', 'EstBaseAmount', 'BaseExchRate']);

                foreach ($sheetRows as $row) {
                    $supplement[(string) $row->Id] = [
                        'category' => $row->ChargeCatagories,
                        'baseAmount' => (float) $row->EstBaseAmount,
                        'exChangeRate' => (float) $row->BaseExchRate,
                    ];
                }
            }

            // Split each SP row into its cost and/or revenue representation.
            $revenue = [];
            $cost = [];

            foreach ($allCharges as $c) {
                if (!empty($c['RevenueChargeRowID'])) {
                    $revenue[] = $this->mapChargeRow($c, 'revenue', $supplement);
                }
                if (!empty($c['CostChargeRowID'])) {
                    $cost[] = $this->mapChargeRow($c, 'cost', $supplement);
                }
            }

            Log::info('getCharges - Success', [
                'booking_no' => $bookingNo,
                'quotation_id' => $quotationId,
                'revenue_count' => count($revenue),
                'cost_count' => count($cost),
            ]);

            // Pass through the SP's own message when it's present; otherwise
            // fall back to a generic string only if both arrays ended up empty.
            $isEmpty = empty($revenue) && empty($cost);

            Log::info('getCharges - counts', [
                'all_charges_raw' => is_array($allCharges) ? count($allCharges) : 'not-array',
                'charge_ids' => count($chargeIds),
                'supplement' => count($supplement),
                'revenue_count' => count($revenue),
                'cost_count' => count($cost),
                'sp_message' => $spMessage ?? null,
            ]);

            return response()->json([
                'success' => true,
                'revenue' => $revenue,
                'cost' => $cost,
                'message' => $spMessage ?? ($isEmpty ? 'No Record Found' : null),
            ]);
        } catch (\RuntimeException $e) {
            Log::error('getCharges - SP error (DBA action required)', [
                'booking_no' => $bookingNo,
                'sp_error' => $e->getMessage(),
            ]);
            return response()->json(
                ['success' => false, 'message' => $e->getMessage(), 'dba_action_required' => true],
                500,
            );
        } catch (\Exception $e) {
            Log::error('getCharges failed', ['booking_no' => $bookingNo, 'error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // private function fetchChargeListFromSP(\PDO $pdo, string $quotationId, int $isRevenue): array
    // {
    //     $stmt = $pdo->prepare('SET NOCOUNT ON; EXEC dbo.sp_Quote_Quotation_ChargeList ?, ?, ?');
    //     $stmt->bindValue(1, $quotationId, \PDO::PARAM_STR);
    //     $stmt->bindValue(2, $isRevenue, \PDO::PARAM_INT);
    //     $stmt->bindValue(3, null, \PDO::PARAM_NULL);
    //     $stmt->execute();

    //     $rows = [];

    //     do {
    //         try {
    //             $fetched = $stmt->fetchAll(\PDO::FETCH_OBJ);

    //             if (empty($fetched)) {
    //                 continue;
    //             }

    //             $firstVal = array_values((array) $fetched[0])[0] ?? '';

    //             if (is_string($firstVal) && str_starts_with($firstVal, 'Failed')) {
    //                 Log::error('fetchChargeListFromSP - SP failure', [
    //                     'quotation_id' => $quotationId,
    //                     'is_revenue' => $isRevenue,
    //                     'message' => $firstVal,
    //                 ]);
    //                 throw new \RuntimeException('sp_Quote_Quotation_ChargeList: ' . $firstVal);
    //             }

    //             $rows = $fetched;
    //             break;
    //         } catch (\PDOException $e) {
    //             continue;
    //         }
    //     } while ($stmt->nextRowset());

    //     // sp_Quote_Quotation_ChargeList does not select ChargeCatagories, so
    //     // grouping by category is not possible from its result alone. Read it
    //     // directly — read-only, does not duplicate or bypass any SP business
    //     // logic (same pattern as the PartyCode/BasedOnCode/Remarks supplement
    //     // query already used in getChargeDetails()).
    //     $chargeIds = array_column($rows, 'Id');
    //     $categoryById = [];
    //     $quantityById = [];
    //     $incrementById = [];
    //     $incrementTypeById = [];

    //     if (!empty($chargeIds)) {
    //         $sheetRows = DB::table('Quote_Quotation_ChargeSheet')
    //             ->whereIn('Id', $chargeIds)
    //             ->get(['Id', 'ChargeCatagories', 'Quantity', 'IncrementBy', 'IncrementType']);

    //         foreach ($sheetRows as $sheetRow) {
    //             $categoryById[$sheetRow->Id] = $sheetRow->ChargeCatagories;
    //             $quantityById[$sheetRow->Id] = (float) $sheetRow->Quantity;
    //             $incrementById[$sheetRow->Id] = (float) ($sheetRow->IncrementBy ?? 0);
    //             $incrementTypeById[$sheetRow->Id] = $sheetRow->IncrementType ?? 'Percentage';
    //         }
    //     }

    //     return array_map(function ($row) use (
    //         $isRevenue,
    //         $categoryById,
    //         $quantityById,
    //         $incrementById,
    //         $incrementTypeById,
    //     ) {
    //         $incrementBy = $incrementById[$row->Id] ?? 0;

    //         // Cost with markup = a cost-side row that carries a non-zero IncrementBy.
    //         // This mirrors how sp_Quote_WorkbookCharges_Display's classifyCharge()
    //         // determines the scenario — markup is a cost-only attribute.
    //         $isMarkup = !$isRevenue && $incrementBy > 0;

    //         return [
    //             'id' => $row->Id,
    //             'chargeCode' => $row->ChargeCode ?? '',
    //             'chargeName' => $row->ChargeDesp ?? '',
    //             'rate' => (float) ($row->EstChargeRate ?? 0),
    //             'quantity' => $quantityById[$row->Id] ?? 0,
    //             'amtInChgCurr' => (float) ($row->EstChargeAmount ?? 0),
    //             'amtInBaseCurr' => (float) ($row->EstBaseAmount ?? 0),
    //             'exChangeRate' => (float) ($row->BaseExchRate ?? 1),
    //             'chgCurr' => $row->EstChargeCurrCode ?? 'KES',
    //             'basedOnCode' => '', // not in list SP — fetched on edit
    //             'basedOnAndUOM' => $row->BasedOnAndUOM ?? '',
    //             'chargeRemark' => '', // not in list SP — fetched on edit
    //             'partyCode' => '', // not in list SP — fetched on edit
    //             'category' => $categoryById[$row->Id] ?? '',
    //             'increaseBy' => $incrementBy,
    //             'increaseType' => $incrementTypeById[$row->Id] ?? 'Percentage',
    //             'revenueOrCost' => $isMarkup ? 'Cost with Markup' : ($isRevenue ? 'Revenue' : 'Cost'),
    //             'sourceType' => 'manual',
    //         ];
    //     }, $rows);
    // }

    public function getCopyChargesSearch(Request $request): JsonResponse
    {
        $officeCode = trim($request->input('office_code', ''));
        $customer = trim($request->input('customer', ''));
        $origin = trim($request->input('origin', ''));
        $destination = trim($request->input('destination', ''));
        $por = trim($request->input('por', ''));
        $pod = trim($request->input('pod', ''));
        $equipment = trim($request->input('equipment', ''));
        $incoterm = trim($request->input('incoterm', ''));
        $quoteNo = trim($request->input('quote_no', ''));

        $hasFilter = $customer || $origin || $destination || $por || $pod || $equipment || $incoterm || $quoteNo;

        if (!$hasFilter) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Please enter at least one filter value',
                ],
                400,
            );
        }

        Log::info(
            'getCopyChargesSearch',
            compact(
                'officeCode',
                'customer',
                'origin',
                'destination',
                'por',
                'pod',
                'equipment',
                'incoterm',
                'quoteNo',
            ),
        );

        // Use only 'Total' — it covers all non-deleted quotations for this office.
        // 'Completed' is not a valid SP status and caused a PDO crash.
        $inputJson = json_encode(
            [
                'OfficeCode' => $officeCode,
                'FromDate' => '',
                'ToDate' => '',
                'Customer' => $customer,
                'Service' => '',
                'Process' => '',
                'OriginCountry' => $origin,
                'DestinationCountry' => $destination,
                'InCoTerm' => $incoterm,
                'PlaceOfReceipt' => $por,
                'PlaceOfDelivery' => $pod,
                'Equipment' => $equipment ? [$equipment] : [],
                'QuoteNo' => $quoteNo,
                'Status' => 'Total',
            ],
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES,
        );

        try {
            $pdo = DB::connection()->getPdo();
            $stmt = $pdo->prepare('SET NOCOUNT ON; EXEC dbo.sp_Quote_QuotationList ?');
            $stmt->execute([$inputJson]);

            $rows = [];
            do {
                try {
                    $fetched = $stmt->fetchAll(\PDO::FETCH_OBJ);
                    if (empty($fetched)) {
                        continue;
                    }
                    $firstVal = array_values((array) $fetched[0])[0] ?? '';
                    if (is_string($firstVal) && str_starts_with(trim($firstVal), 'Failed')) {
                        Log::error('getCopyChargesSearch - SP failure', ['message' => $firstVal]);
                        return response()->json(
                            [
                                'success' => false,
                                'message' => $firstVal,
                            ],
                            422,
                        );
                    }

                    if (!isset($fetched[0]->QuoteNo)) {
                        continue;
                    }

                    $rows = $fetched;
                    break;
                } catch (\PDOException $e) {
                    continue;
                }
            } while ($stmt->nextRowset());
        } catch (\Exception $e) {
            Log::error('getCopyChargesSearch failed', ['error' => $e->getMessage()]);
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Search failed: ' . $e->getMessage(),
                ],
                500,
            );
        }

        // Resolve DB IDs in one query
        $quoteNos = array_column((array) $rows, 'QuoteNo');
        $dbMap = collect();

        if (!empty($quoteNos)) {
            $dbMap = DB::table('Quote_QuotationInfo')->whereIn('QuoteNo', $quoteNos)->pluck('ID', 'QuoteNo');
        }

        $data = array_map(
            fn($row) => [
                'id' => $dbMap[$row->QuoteNo] ?? '',
                'QuoteNo' => $row->QuoteNo ?? '',
                'CompanyName' => $row->CompanyName ?? '',
                'Origin' => $row->OriginCountry ?? '',
                'Destination' => $row->DestinationCountry ?? '',
                'POR' => $row->PlaceOfReceipt ?? '',
                'POD' => $row->PlaceOfDelivery ?? '',
                'IncoTerm' => $row->InCoTerm ?? '',
            ],
            $rows,
        );

        return response()->json(['success' => true, 'data' => $data]);
    }

    public function copyChargesBulk(Request $request): JsonResponse
    {
        $request->validate([
            'source_quotation_id' => 'required|string',
            'target_quotation_id' => 'required|string',
            'charge_ids' => 'required|array',
            'charge_ids.*' => 'string',
        ]);

        $inputJson = json_encode([
            'CopyFromQuoteID' => $request->source_quotation_id,
            'CopyToQuoteID' => $request->target_quotation_id,
            'ChargeList' => $request->charge_ids,
        ]);

        Log::info('copyChargesBulk input', ['json' => $inputJson]);

        try {
            $pdo = DB::getPdo();
            $stmt = $pdo->prepare("
            SET NOCOUNT ON;
            DECLARE @OutputJson VARCHAR(2000);
            EXEC dbo.sp_Quote_QuotationCopyCharges ?, @OutputJson = @OutputJson OUTPUT;
            SELECT @OutputJson AS OutputJson
        ");
            $stmt->execute([$inputJson]);

            $outputJson = null;
            $transactionError = null;

            while (true) {
                try {
                    $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                    if (!empty($rows) && isset($rows[0]['OutputJson'])) {
                        $outputJson = $rows[0]['OutputJson'];
                        break;
                    }
                } catch (\PDOException $e) {
                    Log::warning('copyChargesBulk fetchAll PDOException', ['error' => $e->getMessage()]);
                }

                try {
                    if (!$stmt->nextRowset()) {
                        break;
                    }
                } catch (\PDOException $e) {
                    $transactionError = $e->getMessage();
                    Log::warning('copyChargesBulk nextRowset PDOException', ['error' => $e->getMessage()]);
                    break;
                }
            }

            Log::info('copyChargesBulk outputJson', ['output' => $outputJson]);

            if (!$outputJson) {
                $message = $transactionError
                    ? 'Stored procedure error: ' . $transactionError
                    : 'No response from stored procedure';

                Log::error('copyChargesBulk no output', [
                    'transaction_error' => $transactionError,
                    'input' => $inputJson,
                ]);

                return response()->json(['success' => false, 'message' => $message]);
            }

            $result = json_decode($outputJson, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('copyChargesBulk json_decode failed', ['raw' => $outputJson]);
                return response()->json(['success' => false, 'message' => 'Invalid SP response']);
            }

            $remark = collect($result['Remark'] ?? [])
                ->pluck('Remark')
                ->filter()
                ->implode(', ');

            if (($result['Status'] ?? '') === 'Success') {
                return response()->json([
                    'success' => true,
                    'message' => $remark ?: 'Charges copied successfully',
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => $remark ?: 'Copy failed',
            ]);
        } catch (\Exception $e) {
            Log::error('copyChargesBulk failed', [
                'error' => $e->getMessage(),
                'input' => $inputJson,
            ]);
            return response()->json(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
        }
    }

    public function getCopyCharges(Request $request): JsonResponse
    {
        $quotationId = trim($request->input('quotation_id', ''));

        if (!$quotationId) {
            return response()->json(['success' => false, 'message' => 'quotation_id is required'], 400);
        }

        try {
            $pdo = DB::connection()->getPdo();

            $revenueRows = $this->fetchCopyChargeRows($pdo, $quotationId, 1);
            $costRows = $this->fetchCopyChargeRows($pdo, $quotationId, 0);

            $map = fn($row, $isRevenue) => [
                'id' => $row['Id'],
                'chargeCode' => $row['ChargeCode'] ?? '',
                'chargeDesp' => $row['ChargeDesp'] ?? '',
                'basedOnCode' => $row['BasedOn'] ?? '',
                'basedOnAndUOM' => $row['BasedOnAndUOM'] ?? '',
                'uomCode' => $row['UOMCode'] ?? '',
                'quantity' => (float) ($row['Quantity'] ?? 0),
                'rate' => (float) ($row['EstChargeRate'] ?? 0),
                'currency' => $row['EstChargeCurrCode'] ?? '',
                'currencyId' => $row['EstChargeCurrencyId'] ?? '',
                'amount' => (float) ($row['EstChargeAmount'] ?? 0),
                'baseAmount' => (float) ($row['EstBaseAmount'] ?? 0),
                'exchangeRate' => (float) ($row['BaseExchRate'] ?? 1),
                'isRevenue' => $isRevenue,
            ];

            $jobInfo = DB::table('Quote_QuotationInfo')
                ->where('ID', $quotationId)
                ->select(['origincountry', 'destinationcountry', 'pri_partnermaster_name', 'SCMJobNo'])
                ->first();

            return response()->json([
                'success' => true,
                'revenue' => array_map(fn($r) => $map($r, true), $revenueRows),
                'cost' => array_map(fn($r) => $map($r, false), $costRows),
                'job_info' => $jobInfo
                    ? [
                        'client' => $jobInfo->pri_partnermaster_name ?? '',
                        'shipper' => $jobInfo->pri_partnermaster_name ?? '',
                        'consignee' => '',
                        'port_of_loading' => $jobInfo->origincountry ?? '',
                        'port_of_discharge' => $jobInfo->destinationcountry ?? '',
                    ]
                    : [],
            ]);
        } catch (\Exception $e) {
            Log::error('getCopyCharges failed', ['quotation_id' => $quotationId, 'error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    private function fetchCopyChargeRows(\PDO $pdo, string $quotationId, int $isRevenue): array
    {
        $spResult = $this->fetchChargesFromSP($pdo, $quotationId);
        $allCharges = $spResult['charges'];

        $rows = [];
        foreach ($allCharges as $c) {
            // Keep only the side the caller asked for
            if ($isRevenue && empty($c['RevenueChargeRowID'])) {
                continue;
            }
            if (!$isRevenue && empty($c['CostChargeRowID'])) {
                continue;
            }

            $mapped = $this->mapChargeRow($c, $isRevenue ? 'revenue' : 'cost');

            // getCopyCharges() reads these keys — map the mapper's output back
            // into the shape that consumer expects.
            $rows[] = [
                'Id' => $mapped['id'],
                'ChargeCode' => $mapped['chargeCode'],
                'ChargeDesp' => $mapped['chargeName'],
                'BasedOn' => $mapped['basedOnCode'],
                'BasedOnAndUOM' => $mapped['basedOnAndUOM'],
                'UOMCode' => $mapped['uomCode'],
                'Quantity' => $mapped['quantity'],
                'EstChargeRate' => $mapped['rate'],
                'EstChargeCurrCode' => $mapped['chgCurr'],
                'EstChargeCurrencyId' => '',
                'EstChargeAmount' => $mapped['amtInChgCurr'],
                'EstBaseAmount' => $mapped['amtInBaseCurr'],
                'BaseExchRate' => $mapped['exChangeRate'],
            ];
        }

        return $rows;
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
            $stmt = $pdo->prepare('SET NOCOUNT ON; EXEC sp_ChargeCode ?, ?, ?');
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

            return response()->json(
                [
                    'success' => false,
                    'message' => 'Failed to load charge codes: ' . $e->getMessage(),
                ],
                500,
            );
        }
    }

    /**
     * Execute sp_Quote_Quotation_Request
     * Copies a quotation to a requested office, generating a new quote number.
     *
     * SP inputs:  @QuotationID, @RequestedOfficeCode, @SubmittedBy
     * SP outputs: @Status (varchar 25), @Remark (varchar 200), @QuoteNo (varchar 50)
     */
    public function requestOfficeQuotation(Request $request): JsonResponse
    {
        $quotationId = $request->input('quotation_id');
        $officeCode = trim((string) $request->input('requested_office_code', ''));
        $submittedBy = Auth::user()->username ?? 'system';

        // ── Input validation ────────────────────────────────────────────────────
        if (empty($quotationId)) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Quotation ID is required.',
                    'data' => null,
                    'error' => ['source' => 'API', 'code' => 'MISSING_QUOTATION_ID', 'details' => ''],
                ],
                422,
            );
        }

        if (empty($officeCode)) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Requested Office Code is required.',
                    'data' => null,
                    'error' => ['source' => 'API', 'code' => 'MISSING_OFFICE_CODE', 'details' => ''],
                ],
                422,
            );
        }

        // ── DB call ──────────────────────────────────────────────────────────────
        try {
            \Log::info('sp_Quote_Quotation_Request INPUT', [
                'quotation_id' => $quotationId,
                'requested_office_code' => $officeCode,
                'submitted_by' => $submittedBy,
            ]);

            $pdo = DB::connection()->getPdo();
            $stmt = $pdo->prepare("
        SET NOCOUNT ON;
        DECLARE
            @Status  VARCHAR(25),
            @Remark  VARCHAR(200),
            @QuoteNo VARCHAR(50);

        EXEC sp_Quote_Quotation_Request
            @QuotationID         = ?,
            @RequestedOfficeCode = ?,
            @SubmittedBy         = ?,
            @Status              = @Status  OUTPUT,
            @Remark              = @Remark  OUTPUT,
            @QuoteNo             = @QuoteNo OUTPUT;

        SELECT
            @Status  AS [Status],
            @Remark  AS [Remark],
            @QuoteNo AS [QuoteNo];
    ");
            $stmt->execute([$quotationId, $officeCode, $submittedBy]);

            // Walk rowsets until we find the one with our output columns
            $row = null;
            do {
                try {
                    $rows = $stmt->fetchAll(\PDO::FETCH_OBJ);
                    if (!empty($rows) && isset($rows[0]->Status)) {
                        $row = $rows[0];
                        break;
                    }
                } catch (\PDOException $e) {
                    continue;
                }
            } while ($stmt->nextRowset());

            $status = $row->Status ?? 'Failed';
            $remark = $row->Remark ?? 'No response from stored procedure.';
            $quoteNo = $row->QuoteNo ?? '';

            \Log::info('sp_Quote_Quotation_Request OUTPUT', [
                'status' => $status,
                'remark' => $remark,
                'quote_no' => $quoteNo,
            ]);

            $success = strtolower(trim($status)) === 'success';

            return response()->json([
                'success' => $success,
                'message' => $remark,
                'data' => $success ? ['quote_no' => $quoteNo] : null,
                'error' => $success
                    ? null
                    : [
                        'source' => 'DB',
                        'code' => 'SP_FAILED',
                        'details' => $remark,
                    ],
            ]);
        } catch (\Throwable $e) {
            \Log::error('sp_Quote_Quotation_Request EXCEPTION', [
                'message' => $e->getMessage(),
                'quotation_id' => $quotationId,
                'office_code' => $officeCode,
            ]);

            return response()->json(
                [
                    'success' => false,
                    'message' => 'An error occurred while requesting the office quotation.',
                    'data' => null,
                    'error' => ['source' => 'DB', 'code' => 'EXCEPTION', 'details' => $e->getMessage()],
                ],
                500,
            );
        }
    }

    public function getRequestOfficeHistory(Request $request): JsonResponse
    {
        $quotationId = $request->input('quotation_id');

        if (empty($quotationId)) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Quotation ID is required.',
                    'data' => null,
                ],
                422,
            );
        }

        try {
            $rows = DB::select(
                "
            SELECT
                r.Id,
                r.RequestedByOfficeCode,
                r.RequestedByQuoteNo,
                r.RequestedToCode,
                r.RequestedToQuoteNo,
                r.CreatedBy,
                r.CreatedOn
            FROM Quote_Quotation_RequestedQuote r WITH (NOLOCK)
            WHERE r.QuotationInfoID = ?
                AND r.IsActive  = 1
                AND r.IsDeleted = 0
            ORDER BY r.CreatedOn ASC
        ",
                [$quotationId],
            );

            return response()->json([
                'success' => true,
                'message' => 'History loaded.',
                'data' => $rows,
            ]);
        } catch (\Throwable $e) {
            \Log::error('getRequestOfficeHistory EXCEPTION', [
                'message' => $e->getMessage(),
                'quotation_id' => $quotationId,
            ]);

            return response()->json(
                [
                    'success' => false,
                    'message' => 'Failed to load request history.',
                    'data' => null,
                ],
                500,
            );
        }
    }

    public function getOfficeCode(Request $request)
    {
        try {
            $teamId = $request->team_id;

            Log::info('getOfficeCode called', ['team_id' => $teamId]);

            if (!$teamId) {
                return response()->json(
                    [
                        'success' => false,
                        'message' => 'Team ID is required',
                    ],
                    400,
                );
            }

            // ✅ FIX: Use TeamCode (int column), not TeamId
            $office = DB::connection('sqlsrv')
                ->table('Cust_Office')
                ->where('TeamCode', $teamId)
                ->where('IsActive', 1)
                ->where('IsDeleted', 0)
                ->select('Code', 'TeamCode')
                ->first();

            Log::info('Office lookup result', [
                'team_id' => $teamId,
                'office_found' => $office ? 'yes' : 'no',
                'office_code' => $office ? $office->Code : null,
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
                        ->where('TeamCode', $quotation->teamid)
                        ->where('IsActive', 1)
                        ->where('IsDeleted', 0)
                        ->select('Code')
                        ->first();
                }
            }

            if (!$office) {
                return response()->json(
                    [
                        'success' => false,
                        'message' => 'Office not found for team ID: ' . $teamId,
                    ],
                    404,
                );
            }

            return response()->json([
                'success' => true,
                'office_code' => trim($office->Code),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get office code', [
                'team_id' => $request->team_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(
                [
                    'success' => false,
                    'message' => 'Failed to retrieve office code: ' . $e->getMessage(),
                ],
                500,
            );
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
                return response()->json(
                    [
                        'success' => false,
                        'message' => 'Office ID is required',
                    ],
                    400,
                );
            }

            $pdo = DB::connection()->getPdo();
            $stmt = $pdo->prepare('SET NOCOUNT ON; EXEC dbo.sp_CurrencyList ?, ?');
            $stmt->execute([$officeId, '']);

            $currencies = [];
            do {
                try {
                    $rows = $stmt->fetchAll(\PDO::FETCH_OBJ);
                    if (empty($rows)) {
                        continue;
                    }

                    $firstVal = array_values((array) $rows[0])[0] ?? '';
                    if (
                        is_string($firstVal) &&
                        (str_contains($firstVal, 'mandatory') || str_contains($firstVal, 'does not exist'))
                    ) {
                        Log::warning('getCurrencies - SP guard error', [
                            'office_id' => $officeId,
                            'message' => $firstVal,
                        ]);
                        return response()->json(
                            [
                                'success' => false,
                                'message' => $firstVal,
                            ],
                            404,
                        );
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
                'currencies' => array_map(
                    fn($c) => [
                        'CurrencyID' => $c->CurrencyID,
                        'CurrencyCode' => $c->CurrencyCode,
                    ],
                    $currencies,
                ),
            ]);
        } catch (\Exception $e) {
            Log::error('getCurrencies - exception', [
                'office_id' => $request->office_id ?? '',
                'error' => $e->getMessage(),
            ]);
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Failed to retrieve currencies: ' . $e->getMessage(),
                ],
                500,
            );
        }
    }

    /**
     * Get exchange rate for currency
     */

    public function getExchangeRate(Request $request)
    {
        $officeCode = trim($request->office_code);
        $fromCurrency = trim($request->from_currency);
        $toCurrency = trim($request->to_currency ?? '');
        if (!$toCurrency && $officeCode) {
            $toCurrency = self::OFFICE_BASE_CURRENCY[strtoupper(trim($officeCode))] ?? '';
        }
        if (!$toCurrency) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Could not resolve target currency for office ' . $officeCode,
                ],
                422,
            );
        }
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
                return response()->json(
                    [
                        'success' => false,
                        'message' => 'Invalid office ID',
                    ],
                    404,
                );
            }
        }

        // Accept any date format from frontend, normalize to '05/Mar/2026'
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
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Office code and from currency are required',
                ],
                400,
            );
        }

        if ($fromCurrency === $toCurrency) {
            return response()->json([
                'success' => true,
                'exchange_rate' => 1.0,
            ]);
        }

        try {
            $rate = $this->resolveExchangeRate($officeCode, $fromCurrency, $toCurrency, $dateFor);

            if ($rate === null) {
                return response()->json(
                    [
                        'success' => false,
                        'message' => "No exchange rate found for {$fromCurrency} → {$toCurrency} in office {$officeCode} on {$dateFor}",
                    ],
                    404,
                );
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

            return response()->json(
                [
                    'success' => false,
                    'message' => 'Failed to retrieve exchange rate: ' . $e->getMessage(),
                ],
                500,
            );
        }
    }

    /**
     * Looks up the current exchange rate via the SQL Server function.
     * $dateFor must be 'd/M/Y' (e.g. 05/Mar/2026), which is what the function expects.
     * Returns null when no positive rate is configured; DB exceptions bubble up to the caller.
     */
    private function resolveExchangeRate(string $officeCode, string $from, string $to, string $dateFor): ?float
    {
        $result = DB::selectOne('SELECT dbo.fn_GetCurrentExRateUsingCodeandPeriod(?, ?, ?, ?) AS ExchangeRate', [
            $officeCode,
            $from,
            $to,
            $dateFor,
        ]);

        $rate = $result ? (float) $result->ExchangeRate : null;

        Log::info('resolveExchangeRate - Result', [
            'office_code' => $officeCode,
            'from_currency' => $from,
            'to_currency' => $to,
            'date_for' => $dateFor,
            'rate' => $rate,
        ]);

        if (!$rate || $rate <= 0) {
            Log::warning('resolveExchangeRate - No rate found', [
                'office_code' => $officeCode,
                'from_currency' => $from,
                'to_currency' => $to,
                'date_for' => $dateFor,
            ]);

            return null;
        }

        return $rate;
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
                    'pri_id',
                    'ServiceVertical',
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
                'Service' => $quotation->ServiceVertical ?? '',
                'Process' => $quotation->process ?? '',
                'Client' => $quotation->pri_id ?? '',
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
            $stmt = $pdo->prepare('SET NOCOUNT ON; EXEC sp_LegalEntityList ?, ?, ?');
            $stmt->execute([$officeCode, $chargeType, $filter]);

            $parties = [];
            do {
                try {
                    $results = $stmt->fetchAll(\PDO::FETCH_OBJ);
                    if (empty($results)) {
                        continue;
                    }
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
                ORDER BY Name",
            );

            return response()->json([
                'success' => true,
                'based_on_list' => $basedOnList,
            ]);
        } catch (\Exception $e) {
            Log::error('Get based on list failed: ' . $e->getMessage());

            return response()->json(
                [
                    'success' => false,
                    'message' => 'Failed to load based on list: ' . $e->getMessage(),
                ],
                500,
            );
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
                return response()->json(
                    [
                        'success' => false,
                        'message' => 'Based On code is required',
                    ],
                    400,
                );
            }

            // Execute stored procedure
            $pdo = DB::connection('sqlsrv')->getPdo();
            $stmt = $pdo->prepare('EXEC sp_ChargeBasedOnUOM ?');
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
                                'message' => $firstColumn,
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
                    'message' => 'No UOM selection required for this Based On type',
                ]);
            }

            Log::info('UOM list fetched successfully', [
                'based_on_code' => $basedOnCode,
                'count' => count($uomList),
            ]);

            return response()->json([
                'success' => true,
                'uom_list' => $uomList,
            ]);
        } catch (\Exception $e) {
            Log::error('Get based on UOM failed', [
                'based_on_code' => $request->based_on_code,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(
                [
                    'success' => false,
                    'message' => 'Failed to get UOM list: ' . $e->getMessage(),
                ],
                500,
            );
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

        $submittedBy = auth()->user()->displayName ?? (auth()->user()->Email ?? 'system');
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
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Request payload too large for SP. Max 2000 characters.',
                ],
                400,
            );
        }

        try {
            $output = $this->executeDetailsUpdateSP($input);
            if (($output['Status'] ?? '') === 'Failed') {
                $remarks = collect($output['Remarks'] ?? [])
                    ->pluck('Remark')
                    ->implode(', ');
                return response()->json([
                    'success' => false,
                    'message' => $remarks ?: 'Booking update failed',
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Booking created successfully',
            ]);
        } catch (\Exception $e) {
            Log::error('createBooking error: ' . $e->getMessage());

            return response()->json(
                [
                    'success' => false,
                    'message' => $e->getMessage(),
                ],
                500,
            );
        }
    }

    /**
     * Resolve Cust_Office.Id (UUID) from Quote_QuotationInfo.teamid (bigint via TeamCode)
     * Shared helper used by Bill To Party and Forwarder lookups.
     */
    private function resolveOfficeId(string $teamId): ?string
    {
        $office = DB::selectOne(
            "
        SELECT SCMOfficeID
        FROM Cust_Office WITH (NOLOCK)
        WHERE TeamCode = ?
            AND IsActive = 1
            AND IsDeleted = 0
        ",
            [$teamId],
        );

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
            $stmt = $pdo->prepare('SET NOCOUNT ON; EXEC dbo.sp_Quote_ServiceTemplateList ?, ?');
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
                            $userMessage =
                                'Service type is not configured on this quotation (ServiceVertical is missing or unrecognised).';
                        } elseif (str_contains($firstVal, 'Process Details')) {
                            $userMessage =
                                'Process type is not configured on this quotation (Export/Import/Local required).';
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
                        return response()->json(
                            [
                                'success' => false,
                                'code' => 'SP_GUARD_FAILED',
                                'message' => $userMessage,
                            ],
                            422,
                        );
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
                return response()->json(
                    [
                        'success' => false,
                        'code' => 'NO_TEMPLATES_CONFIGURED',
                        'message' =>
                            'No service templates are configured for this quotation\'s service type and office. Please contact your SCM administrator.',
                    ],
                    404,
                );
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
            $results = DB::select("EXEC dbo.sp_PartyList ?, 'Debtor', ?", [$officeId, $filter]);

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
        $officeIdParam = $request->query('office_id');
        $filter = $request->query('filter', '');

        if (!$teamId && !$officeIdParam) {
            return response()->json(['success' => false, 'message' => 'team_id or office_id is required'], 422);
        }

        $officeId = $officeIdParam ?: $this->resolveOfficeId($teamId);

        if (!$officeId) {
            return response()->json(['success' => false, 'message' => 'Office not found'], 404);
        }

        try {
            $results = DB::select("EXEC dbo.sp_PartyList ?, 'Agent / Forwarder', ?", [$officeId, $filter]);

            return response()->json([
                'success' => true,
                'parties' => $results,
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
            $db = DB::getDatabaseName() === 'FITSYSUAT' ? '[FITSCMUAT].dbo.' : '[FITSCM].dbo.';

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

            $query .= ' WHERE CL.IsActive = 1 AND CL.IsDeleted = 0 ';
            if (!empty($filter)) {
                $query .= ' AND (CL.Name LIKE ? OR CL.Code LIKE ? OR CL.UNCode LIKE ?) ';
                $bindings[] = $filter . '%';
                $bindings[] = $filter . '%';
                $bindings[] = $filter . '%';
            }

            $query .= ' ORDER BY CL.Name ';

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
            $agentTypes = DB::connection('sqlsrv')->select('EXEC dbo.sp_ClientAgentList @Filter = ?', [$filter]);

            return response()->json([
                'success' => true,
                'data' => $agentTypes,
            ]);
        } catch (\Exception $e) {
            Log::error('getAgentTypes - Failed', [
                'error' => $e->getMessage(),
            ]);

            return response()->json(
                [
                    'success' => false,
                    'message' => 'Failed to load agent types: ' . $e->getMessage(),
                ],
                500,
            );
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
                return response()->json(
                    [
                        'success' => false,
                        'message' => 'input_json is required',
                    ],
                    400,
                );
            }

            // Log what we're sending
            \Log::info('saveCustomerDetails - Sending JSON', ['json' => $inputJson]);
            $jsonLength = strlen($inputJson);
            \Log::info('saveCustomerDetails - JSON Length', ['length' => $jsonLength]);

            if ($jsonLength > 2000) {
                \Log::warning('saveCustomerDetails - JSON exceeds SP VARCHAR(2000) limit', [
                    'length' => $jsonLength,
                ]);
                return response()->json(
                    [
                        'success' => false,
                        'message' =>
                            'Unable to save customer details. The data is too large for the current system limit. Please contact support. (Payload: ' .
                            $jsonLength .
                            ' chars / 2000 limit)',
                    ],
                    422,
                );
            }

            $pdo = DB::connection('sqlsrv')->getPdo();
            $stmt = $pdo->prepare(
                'SET NOCOUNT ON; DECLARE @OutputRemarkJson NVARCHAR(2000); EXEC dbo.sp_Quote_CustomerUpdate ?, @OutputRemarkJson OUTPUT; SELECT @OutputRemarkJson AS OutputRemarkJson',
            );
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
                return response()->json(
                    [
                        'success' => false,
                        'message' => 'No response from stored procedure',
                    ],
                    500,
                );
            }

            $result = json_decode($output, true);

            if ($result && isset($result['Status']) && $result['Status'] === 'Success') {
                // Patch columns the SP omits from its UPDATE
                $decoded = json_decode($request->input('input_json'), true);
                $patch = [];

                if (!empty($decoded['Industry'])) {
                    $patch['industryname'] = $decoded['Industry'];
                }

                // SP does not write these back to Quote_QuotationInfo
                if (!empty($decoded['CustomerType'])) {
                    $patch['CustomerType'] = $decoded['CustomerType'];
                }

                if (isset($decoded['AgentType'])) {
                    $patch['AgentType'] = $decoded['AgentType'];
                }

                if (!empty($patch)) {
                    DB::table('Quote_QuotationInfo')->where('ID', $decoded['QuotationID'])->update($patch);
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Customer details saved successfully',
                ]);
            }

            return response()->json(
                [
                    'success' => false,
                    'message' => $result['Remarks'][0]['Remark'] ?? 'Failed to save customer details',
                ],
                422,
            );
        } catch (\Exception $e) {
            Log::error('saveCustomerDetails - Failed', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return response()->json(
                [
                    'success' => false,
                    'message' => $e->getMessage(),
                ],
                500,
            );
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
            EXEC FITSYS.dbo.sp_Quote_CargoDetailsUpdate ?, @OutputRemarkJson = @OutputJson OUTPUT;
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
                ->where('DocumentType', 'QuotationPDF')
                ->where('IsDeleted', 0)
                ->where('IsVisible', 1)
                ->orderBy('CreatedOn', 'asc')
                ->skip($version - 1)
                ->take(1)
                ->first(['Name', 'FilePath']);

            if (!$document) {
                abort(404, 'Version ' . $version . ' not found');
            }

            $filePath = $document->FilePath;
            $fileName = $document->Name ?: "quote_{$quotation->bookingno}_v{$version}.pdf";

            // Derive Content-Type from the actual file extension — this route serves
            // both PDF and Excel versions (Excel reuses DocumentType = 'QuotationPDF'),
            // so hardcoding application/pdf mislabels .xlsx responses.
            $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            $contentType =
                $extension === 'xlsx'
                    ? 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
                    : 'application/pdf';

            // Determine disposition: inline for preview, attachment otherwise
            $disposition = request()->has('preview') ? 'inline' : 'attachment';

            // ── Azure storage path ──
            if (str_starts_with($filePath, 'Quotation/')) {
                if (!\Storage::disk('azure')->exists($filePath)) {
                    Log::error('downloadPdf - Azure blob not found', [
                        'id' => $id,
                        'version' => $version,
                        'blob_path' => $filePath,
                    ]);
                    abort(404, 'File not found in Azure storage');
                }

                Log::info('downloadPdf - Serving from Azure', [
                    'id' => $id,
                    'booking_no' => $quotation->bookingno,
                    'version' => $version,
                    'blob_path' => $filePath,
                    'disposition' => $disposition,
                ]);

                $contents = \Storage::disk('azure')->get($filePath);
                return response($contents, 200)
                    ->header('Content-Type', $contentType)
                    ->header('Content-Disposition', $disposition . '; filename="' . $fileName . '"');
            }

            // ── Local disk path ──
            $relativePath = $this->normalizeFilePath($filePath);

            if (!\Storage::disk('public')->exists($relativePath)) {
                Log::error('downloadPdf - File not found on disk', [
                    'id' => $id,
                    'version' => $version,
                    'raw_filepath' => $filePath,
                    'relative_path' => $relativePath,
                ]);
                abort(404, 'File not found on disk');
            }

            Log::info('downloadPdf - Serving from local disk', [
                'id' => $id,
                'booking_no' => $quotation->bookingno,
                'version' => $version,
                'file' => $fileName,
                'disposition' => $disposition,
            ]);

            // Read file contents to allow custom disposition header
            $contents = \Storage::disk('public')->get($relativePath);
            return response($contents, 200)
                ->header('Content-Type', $contentType)
                ->header('Content-Disposition', $disposition . '; filename="' . $fileName . '"');
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

    // ── Step 0: Save Booking Info ─────────────────────────────────────────────
    public function processBookingStep0(Request $request): JsonResponse
    {
        $request->validate([
            'id' => 'required|string',
            'service_template' => 'required|string',
            'bill_to_party' => 'required|string',
            'forwarder' => 'required|string',
            'place_of_receipt' => 'required|string',
            'place_of_delivery' => 'required|string',
        ]);

        $submittedBy = Auth::user()->displayName ?? (Auth::user()->Email ?? 'system');

        $quotation = DB::table('Quote_QuotationInfo')
            ->where('ID', $request->id)
            ->first(['ID', 'QuoteNo', 'bookingno', 'QuoteStatus', 'teamid', 'OfficeCode']);

        if (!$quotation) {
            return response()->json(
                [
                    'success' => false,
                    'step' => 0,
                    'status' => 'Failed',
                    'remark' => 'Quotation not found',
                ],
                404,
            );
        }

        // FIX 1: RTRIM OfficeCode before SP uses it for party validation
        DB::table('Quote_QuotationInfo')
            ->where('ID', $request->id)
            ->update(['OfficeCode' => DB::raw('RTRIM(OfficeCode)')]);

        Log::info('processBookingStep0 - Input', [
            'id' => $request->id,
            'quote_no' => $quotation->QuoteNo,
            'service_template' => $request->service_template,
            'bill_to_party' => $request->bill_to_party,
            'forwarder' => $request->forwarder,
            'place_of_receipt' => $request->place_of_receipt,
            'place_of_delivery' => $request->place_of_delivery,
            'include_country' => $request->input('include_country', 0),
        ]);

        $result = $this->runBookingInfoUpdate(
            $quotation->ID,
            $request->service_template,
            $request->bill_to_party,
            $request->forwarder,
            (int) $request->input('include_country', 0),
            $request->place_of_receipt,
            $request->place_of_delivery,
            $submittedBy,
        );

        Log::info('processBookingStep0 - Output', [
            'id' => $request->id,
            'status' => $result['status'],
            'remark' => $result['remark'],
        ]);

        if ($result['status'] !== 'Success') {
            $this->markAsFailed($request->id, 'Save Booking Info', $result['remark'], $submittedBy);
        }

        return response()->json([
            'success' => $result['status'] === 'Success',
            'step' => 0,
            'status' => $result['status'],
            'remark' => $result['remark'],
        ]);
    }

    // ── Step 2: Booking XML ───────────────────────────────────────────────────
    public function processBookingStep2(Request $request): JsonResponse
    {
        $request->validate(['id' => 'required|string']);

        $submittedBy = Auth::user()->displayName ?? (Auth::user()->Email ?? 'system');

        $quotation = DB::table('Quote_QuotationInfo')
            ->where('ID', $request->id)
            ->first(['ID', 'QuoteNo']);

        if (!$quotation) {
            return response()->json(
                [
                    'success' => false,
                    'step' => 2,
                    'status' => 'Failed',
                    'remark' => 'Quotation not found',
                ],
                404,
            );
        }

        Log::info('processBookingStep2 - Input', [
            'id' => $request->id,
            'quote_no' => $quotation->QuoteNo,
            'sp' => 'sp_Quote_BookingProcessMain',
            'type' => 'XML',
        ]);

        $result = $this->runBookingProcessMain($quotation->ID, 'XML');

        Log::info('processBookingStep2 - Output', [
            'id' => $request->id,
            'sp_status' => $result['status'],
            'sp_remark' => $result['remark'],
            'result_sets' => $result['result_sets'] ?? null,
        ]);

        if ($result['status'] !== 'Success') {
            $this->markAsFailed($quotation->ID, 'Booking XML', $result['remark'], $submittedBy);
        }

        return response()->json([
            'success' => $result['status'] === 'Success',
            'step' => 2,
            'status' => $result['status'],
            'remark' => $result['remark'],
        ]);
    }

    // ── Step 2b: SCM Push ─────────────────────────────────────────────────────
    public function processBookingStep2b(Request $request): JsonResponse
    {
        $request->validate(['id' => 'required|string']);

        $submittedBy = Auth::user()->displayName ?? (Auth::user()->Email ?? 'system');

        $quotation = DB::table('Quote_QuotationInfo')
            ->where('ID', $request->id)
            ->first(['ID', 'QuoteNo', 'SCMBookingXML', 'SCMBookingID']);

        if (!$quotation) {
            return response()->json(
                [
                    'success' => false,
                    'step' => '2b',
                    'status' => 'Failed',
                    'remark' => 'Quotation not found',
                ],
                404,
            );
        }

        Log::info('processBookingStep2b - Input', [
            'id' => $request->id,
            'quote_no' => $quotation->QuoteNo,
            'xml_length' => strlen($quotation->SCMBookingXML ?? ''),
        ]);

        $pushRequest = new \Illuminate\Http\Request();
        $pushRequest->merge(['quote_no' => $quotation->QuoteNo]);

        $pushResponse = json_decode($this->pushBookingToSCM($pushRequest)->getContent(), true);

        Log::info('processBookingStep2b - Output', [
            'id' => $request->id,
            'push_success' => $pushResponse['success'] ?? false,
            'scm_booking_id' => $pushResponse['scm_booking_id'] ?? null,
            'message' => $pushResponse['message'] ?? null,
        ]);

        if (!($pushResponse['success'] ?? false)) {
            $this->markAsFailed(
                $quotation->ID,
                'SCM Push',
                $pushResponse['message'] ?? 'SCM push failed',
                $submittedBy,
            );
        }

        return response()->json([
            'success' => $pushResponse['success'] ?? false,
            'step' => '2b',
            'status' => $pushResponse['success'] ?? false ? 'Success' : 'Failed',
            'remark' => $pushResponse['message'] ?? '',
            'scm_booking_id' => $pushResponse['scm_booking_id'] ?? null,
            'scm_booking_no' => $pushResponse['scm_booking_no'] ?? null,
        ]);
    }

    // ── Step 3: Charge Transfer ───────────────────────────────────────────────
    public function processBookingStep3(Request $request): JsonResponse
    {
        $request->validate(['id' => 'required|string']);

        $submittedBy = Auth::user()->displayName ?? (Auth::user()->Email ?? 'system');

        $quotation = DB::table('Quote_QuotationInfo')
            ->where('ID', $request->id)
            ->first(['ID', 'QuoteNo']);

        if (!$quotation) {
            return response()->json(
                [
                    'success' => false,
                    'step' => 3,
                    'status' => 'Failed',
                    'remark' => 'Quotation not found',
                ],
                404,
            );
        }

        Log::info('processBookingStep3 - Input', [
            'id' => $request->id,
            'quote_no' => $quotation->QuoteNo,
            'sp' => 'sp_Quote_BookingProcessMain',
            'type' => 'Charge',
        ]);

        $result = $this->runBookingProcessMain($quotation->ID, 'Charge');

        Log::info('processBookingStep3 - Output', [
            'id' => $request->id,
            'sp_status' => $result['status'],
            'sp_remark' => $result['remark'],
            'result_sets' => $result['result_sets'] ?? null,
        ]);

        if ($result['status'] !== 'Success') {
            $this->markAsFailed($quotation->ID, 'Charge Transfer', $result['remark'], $submittedBy);
        }

        return response()->json([
            'success' => $result['status'] === 'Success',
            'step' => 3,
            'status' => $result['status'],
            'remark' => $result['remark'],
        ]);
    }

    // ── Step 4: Document Transfer ─────────────────────────────────────────────
    public function processBookingStep4(Request $request): JsonResponse
    {
        $request->validate(['id' => 'required|string']);

        $submittedBy = Auth::user()->displayName ?? (Auth::user()->Email ?? 'system');

        $quotation = DB::table('Quote_QuotationInfo')
            ->where('ID', $request->id)
            ->first(['ID', 'QuoteNo']);

        if (!$quotation) {
            return response()->json(
                [
                    'success' => false,
                    'step' => 4,
                    'status' => 'Failed',
                    'remark' => 'Quotation not found',
                ],
                404,
            );
        }

        Log::info('processBookingStep4 - Input', [
            'id' => $request->id,
            'quote_no' => $quotation->QuoteNo,
            'sp' => 'sp_Quote_BookingProcessMain',
            'type' => 'Document',
        ]);

        $result = $this->runBookingProcessMain($quotation->ID, 'Document');

        Log::info('processBookingStep4 - Output', [
            'id' => $request->id,
            'sp_status' => $result['status'],
            'sp_remark' => $result['remark'],
            'result_sets' => $result['result_sets'] ?? null,
        ]);

        if ($result['status'] !== 'Success') {
            $this->markAsFailed($quotation->ID, 'Document Transfer', $result['remark'], $submittedBy);
        }

        // All steps done — mark as Completed
        if ($result['status'] === 'Success') {
            DB::table('Quote_QuotationInfo')
                ->where('ID', $quotation->ID)
                ->update([
                    'QuoteStatus' => 'Completed',
                    'SCMINTStatus' => 'Success',
                    'LastModifiedBy' => $submittedBy,
                    'updatedon' => now(),
                ]);

            Log::info('processBookingStep4 - Marked Completed', ['id' => $request->id]);
        }

        return response()->json([
            'success' => $result['status'] === 'Success',
            'step' => 4,
            'status' => $result['status'],
            'remark' => $result['remark'],
        ]);
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

        $submittedBy = Auth::user()->displayName ?? (Auth::user()->Email ?? 'system');

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
            return response()->json(
                [
                    'success' => false,
                    'message' => 'QuoteNo is not set on this quotation. Cannot proceed with booking process.',
                ],
                422,
            );
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
            $submittedBy,
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
                'message' => $step0Result['remark'] ?: 'Failed to save booking info.',
                'steps' => $steps,
            ]);
        }

        // ── Steps 1–4: sp_Quote_BookingProcessMain ────────────────────────────────
        $processSteps = [
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
                $this->markAsFailed($request->id, $stepDef['label'], $result['remark'], $submittedBy);

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
                    'message' => $result['remark'] ?: 'Step failed with no details returned.',
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

                $pushResponse = json_decode($this->pushBookingToSCM($pushRequest)->getContent(), true);

                Log::info('processBooking - pushBookingToSCM result', [
                    'quote_no' => $quoteNo,
                    'result' => $pushResponse,
                ]);

                if (!($pushResponse['success'] ?? false)) {
                    $this->markAsFailed(
                        $request->id,
                        'SCM Push',
                        $pushResponse['message'] ?? 'Failed to push XML to SCM',
                        $submittedBy,
                    );

                    // ← Add step 2b as Failed BEFORE returning
                    $steps[] = [
                        'step' => '2b',
                        'label' => 'SCM Push',
                        'status' => 'Failed',
                        'remark' => $pushResponse['message'] ?? 'Failed to push XML to SCM',
                    ];

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
                'QuoteStatus' => 'Completed',
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
            $existing = DB::table('Quote_QuotationInfo')->where('ID', $quotationId)->value('QuoteStatusDescription');

            $newEntry = json_encode(['Remark' => $stepLabel . ': ' . $remark]);
            $description = $existing ? $existing . ',' . $newEntry : $newEntry;

            DB::table('Quote_QuotationInfo')
                ->where('ID', $quotationId)
                ->update([
                    'QuoteStatus' => 'Failed Int.',
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
        string $submittedBy,
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

            Log::info('runBookingInfoUpdate - Input', [
                'quotation_id' => $quotationId, // was $quoteNo — not in scope here
                'input_json' => $inputJson,
            ]);

            $output = $this->executeDetailsUpdateSP($inputJson);

            Log::info('runBookingInfoUpdate - Output', [
                'quotation_id' => $quotationId,
                'output' => $output,
            ]);

            return [
                'status' => $output['Status'] ?? 'Failed',
                'remark' => $output['Remark'] ?? '[SP] No remark returned',
            ];
        } catch (\Exception $e) {
            Log::error('runBookingInfoUpdate - Exception', [
                'quotation_id' => $quotationId,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            return ['status' => 'Failed', 'remark' => $e->getMessage()];
        }
    }

    public function getBookingFormData(Request $request): \Illuminate\Http\JsonResponse
    {
        $quotationId = $request->input('quotation_id');

        if (empty($quotationId)) {
            return response()->json(['success' => false, 'data' => null]);
        }

        $row = DB::table('Quote_QuotationInfo')
            ->where('ID', $quotationId)
            ->first([
                'ServiceTemplate',
                'BillToPartyCode',
                'BillToParty',
                'ForwarderCode',
                'Forwarder',
                'IncludeCountry',
                'PlaceOfReceiptCode',
                'PlaceOfReceipt',
                'PlaceOfDeliveryCode',
                'PlaceOfDelivery',
            ]);

        $hasAnyBookingData =
            $row &&
            (!empty($row->BillToPartyCode) ||
                !empty($row->ForwarderCode) ||
                !empty($row->PlaceOfReceiptCode) ||
                !empty($row->PlaceOfDeliveryCode) ||
                !empty($row->ServiceTemplate));

        if (!$hasAnyBookingData) {
            // Nothing saved yet on any booking field — treat as fresh
            return response()->json(['success' => false, 'data' => null]);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'service_template' => $row->ServiceTemplate,
                'bill_to_party_code' => $row->BillToPartyCode,
                'bill_to_party_text' => $row->BillToParty
                    ? $row->BillToPartyCode . ' — ' . $row->BillToParty
                    : $row->BillToPartyCode,
                'forwarder_code' => $row->ForwarderCode,
                'forwarder_text' => $row->Forwarder
                    ? $row->ForwarderCode . ' — ' . $row->Forwarder
                    : $row->ForwarderCode,
                'include_country' => (bool) $row->IncludeCountry,
                'receipt_code' => $row->PlaceOfReceiptCode,
                'receipt_text' => $row->PlaceOfReceipt
                    ? $row->PlaceOfReceipt . ' (' . $row->PlaceOfReceiptCode . ')'
                    : $row->PlaceOfReceiptCode,
                'delivery_code' => $row->PlaceOfDeliveryCode,
                'delivery_text' => $row->PlaceOfDelivery
                    ? $row->PlaceOfDelivery . ' (' . $row->PlaceOfDeliveryCode . ')'
                    : $row->PlaceOfDeliveryCode,
            ],
        ]);
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
                    if (empty($rows)) {
                        continue;
                    }

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
                            Log::error('runBookingProcessMain - SP emitted error result set', [
                                'quotation_id' => $quotationId, // was $quoteNo — not in scope here
                                'process_type' => $processType,
                                'sp_error' => $trimmed,
                            ]);

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
                return count($s['columns'] ?? []) === 1 &&
                    is_string($val) &&
                    (stripos($val, 'Error') === 0 ||
                        stripos($val, 'Failed') === 0 ||
                        stripos($val, 'There is already') !== false);
            });

            if ($errorSet) {
                $spError = array_values($errorSet['first_row'])[0];
                Log::error('runBookingProcessMain - @Status=Success but error set detected', [
                    'quotation_id' => $quotationId,
                    'process_type' => $processType,
                    'sp_error' => $spError,
                ]);

                return [
                    'status' => 'Failed',
                    'remark' => $spError,
                    'result_sets' => $setsLogged,
                ];
            }

            return [
                'status' => $result->Status ?? 'Failed',
                'remark' =>
                    isset($result->Remark) && $result->Remark !== '' ? $result->Remark : '[SP] No remark returned',
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
            $stmt = $pdo->prepare('SET NOCOUNT ON; EXEC dbo.sp_ShipmentMode ?');
            $stmt->execute([$filter]);

            $results = [];
            do {
                try {
                    $rows = $stmt->fetchAll(\PDO::FETCH_OBJ);
                    if (empty($rows)) {
                        continue;
                    }
                    $firstVal = array_values((array) $rows[0])[0] ?? '';
                    if (is_string($firstVal) && str_starts_with($firstVal, 'Failed')) {
                        continue;
                    }
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
            $stmt = $pdo->prepare('SET NOCOUNT ON; EXEC dbo.sp_TransportMode ?');
            $stmt->execute([$filter]);

            $results = [];
            do {
                try {
                    $rows = $stmt->fetchAll(\PDO::FETCH_OBJ);
                    if (empty($rows)) {
                        continue;
                    }
                    $firstVal = array_values((array) $rows[0])[0] ?? '';
                    if (is_string($firstVal) && str_starts_with($firstVal, 'Failed')) {
                        continue;
                    }
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
            'pol_code' => 'nullable|string',
            'pod_code' => 'nullable|string',
            'place_of_receipt_code' => 'nullable|string',
            'place_of_delivery_code' => 'nullable|string',
            'forwarder_code' => 'nullable|string',
        ]);

        $submittedBy = auth()->user()->displayName ?? (auth()->user()->Email ?? 'system');

        $input = json_encode([
            'OperationType' => 'ShipmentDetails',
            'SubmittedDatetime' => now()->format('YmdHis'),
            'SubmittedBy' => $submittedBy,
            'Action' => 'Update',
            'QuotationID' => $request->quotation_id,
            'CountryOfOriginCode' => $request->origincountry ?? '',
            'CountryOfDestinationCode' => $request->destinationcountry ?? '',
            'POLCode' => $request->pol_code ?? '',
            'PODCode' => $request->pod_code ?? '',
            'PlaceOfReceiptCode' => $request->place_of_receipt_code ?? '',
            'PlaceOfDeliveryCode' => $request->place_of_delivery_code ?? '',
            'IncoTermCode' => $request->incoterm ?? '',
            'ShipmentModeCode' => $request->shipment_mode,
            'TransportModeCode' => $request->transport_mode,
            'ServiceClassCode' => $request->service_class ?? '',
            'ForwarderCode' => $request->forwarder_code ?? '',
        ]);

        if (strlen($input) > 2000) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Request payload too large for SP. Max 2000 characters.',
                ],
                400,
            );
        }

        try {
            $result = $this->executeDetailsUpdateSP($input);

            if (($result['Status'] ?? '') === 'Failed') {
                $message =
                    $result['Remark'] ?? implode(', ', array_column($result['Remarks'] ?? [], 'Remark')) ?:
                    'Shipment update failed';

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
            $stmt = $pdo->prepare('SET NOCOUNT ON; EXEC dbo.sp_EquipmentType ?');
            $stmt->execute([$filter]);

            $results = [];
            do {
                try {
                    $rows = $stmt->fetchAll(\PDO::FETCH_OBJ);
                    if (empty($rows)) {
                        continue;
                    }
                    $firstVal = array_values((array) $rows[0])[0] ?? '';
                    if (is_string($firstVal) && str_starts_with($firstVal, 'Failed')) {
                        continue;
                    }
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
        $quoteNo = trim($request->input('quote_no', '')); // ← add this

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
                'containerlist' => DB::raw('LEFT(RTRIM(containerlist), LEN(RTRIM(containerlist)) - 1)'),
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
                    if (empty($rows)) {
                        continue;
                    }

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

        $submittedBy = substr(auth()->user()->displayName ?? (auth()->user()->Email ?? 'system'), 0, 25);

        // Mirror saveInquiryDetails — verify QuoteNo exists in DB before calling SP.
        // This ensures the SP receives the exact string stored in the table,
        // eliminating any casing or whitespace mismatch from the request.
        $quoteNo = DB::connection('sqlsrv')
            ->table('Quote_QuotationInfo')
            ->where('ID', $request->quotation_id)
            ->value('QuoteNo');

        if (!$quoteNo) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Quotation not found',
                ],
                404,
            );
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
                if ($xPos === false) {
                    continue;
                }
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
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Payload too large for SP. Reduce number of containers.',
                ],
                400,
            );
        }

        try {
            $output = $this->executeCargoUpdateSP($input);

            Log::info('saveCargoDetails - SP response', [
                'quote_no' => $quoteNo,
                'output' => $output,
            ]);

            if (($output['Status'] ?? '') === 'Failed') {
                $remarks = collect($output['Remarks'] ?? [])
                    ->pluck('Remark')
                    ->implode(', ');
                return response()->json([
                    'success' => false,
                    'message' => $remarks ?: $output['Remark'] ?? 'Cargo update failed',
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Cargo details saved successfully',
            ]);
        } catch (\Exception $e) {
            Log::error('saveCargoDetails error', [
                'quote_no' => $quoteNo,
                'error' => $e->getMessage(),
            ]);
            return response()->json(
                [
                    'success' => false,
                    'message' => $e->getMessage(),
                ],
                500,
            );
        }
    }

    public function getSalesPersons(Request $request)
    {
        $filter = trim($request->input('filter', ''));
        $officeId = trim($request->input('office_id', ''));

        try {
            $query = DB::connection('sqlsrv')
                ->table('FITSCM.dbo.Cust_Resource AS CR')
                ->join('FITSCM.dbo.Cust_ResOfficeResFuncMap AS CRO', 'CRO.ResourceId', '=', 'CR.Id')
                ->join('FITSCM.dbo.Sys_ResourceFunction AS RF', 'RF.Id', '=', 'CRO.ResourceFunctionId')
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
            'quotation_currency' => 'required|string|size:3',
        ]);

        $submittedBy = auth()->user()->displayName ?? (auth()->user()->Email ?? 'system');

        $input = json_encode([
            'OperationType' => 'InquiryDetails',
            'SubmittedDatetime' => now()->format('YmdHis'),
            'SubmittedBy' => $submittedBy,
            'Action' => 'Update',
            'QuotationID' => $request->quotation_id,
            'SalesPersonCode' => $request->sales_person_code,
            'SalesPersonEmail' => $request->sales_person_email,
            'QuotationCurrency' => $request->quotation_currency,
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

    public function bulkDeleteCharges(Request $request): JsonResponse
    {
        $quotationId = $request->input('quotation_id');
        $chargeIds = $request->input('charge_ids', []);

        if (empty($quotationId) || empty($chargeIds)) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Missing required fields',
                ],
                400,
            );
        }

        $submittedBy = Auth::user()->displayName ?? (Auth::user()->Email ?? 'system');

        $inputJson = json_encode([
            'OperationType' => 'BulkDeleteCharges',
            'SubmittedDatetime' => now()->format('YmdHis'),
            'SubmittedBy' => $submittedBy,
            'Action' => 'Delete',
            'QuotationID' => $quotationId,
            'ChargeIDs' => array_values($chargeIds),
        ]);

        try {
            // Use the same pattern as saveCharge() - execute SP and fetch OutputJson
            $pdo = DB::connection()->getPdo();
            $stmt = $pdo->prepare("
                SET NOCOUNT ON;
                DECLARE @OutputJson VARCHAR(2000);
                EXEC dbo.sp_Quote_DetailsUpdate ?, @OutputJson = @OutputJson OUTPUT;
                SELECT @OutputJson AS OutputJson
            ");
            $stmt->execute([$inputJson]);

            $result = null;
            do {
                try {
                    $rows = $stmt->fetchAll(\PDO::FETCH_OBJ);
                    if (!empty($rows) && isset($rows[0]->OutputJson)) {
                        $outputJson = $rows[0]->OutputJson;
                        $result = json_decode($outputJson, true);
                        break;
                    }
                } catch (\PDOException $e) {
                    continue;
                }
            } while ($stmt->nextRowset());

            if (!$result) {
                return response()->json([
                    'success' => false,
                    'message' => 'No response from stored procedure',
                ]);
            }

            if (($result['Status'] ?? '') === 'Success') {
                Log::info('bulkDeleteCharges - Success', [
                    'quotation_id' => $quotationId,
                    'deleted_count' => count($chargeIds),
                    'charge_ids' => $chargeIds,
                    'user' => Auth::user()->email ?? 'system',
                ]);
                return response()->json([
                    'success' => true,
                    'message' => $result['Remarks'][0]['Remark'] ?? 'Charges deleted successfully',
                ]);
            }

            $remarks = collect($result['Remarks'] ?? [])
                ->pluck('Remark')
                ->implode(', ');

            return response()->json([
                'success' => false,
                'message' => $remarks ?: 'Failed to delete charges',
            ]);
        } catch (\Exception $e) {
            Log::error('bulkDeleteCharges failed', [
                'quotation_id' => $quotationId,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return response()->json(
                [
                    'success' => false,
                    'message' => 'Server error: ' . $e->getMessage(),
                ],
                500,
            );
        }
    }

    // ============================================================
    // LOOKUP METHODS — used by duplicate modal dropdowns
    // ============================================================
    public function lookupCountries(Request $request)
    {
        try {
            $pdo = DB::connection('sqlsrv')->getPdo();
            $stmt = $pdo->prepare('EXEC sp_CountryList @Filter = ?');
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
            $stmt = $pdo->prepare('EXEC sp_IncoTerms @Filter = ?');
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
            $stmt = $pdo->prepare('EXEC sp_ServiceClass @Filter = ?');
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
            $stmt = $pdo->prepare('EXEC sp_IndustryTypeList @Filter = ?');
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
            $stmt = $pdo->prepare('EXEC sp_PersonInCharge @OfficeID = ?, @PICType = ?, @Filter = ?');
            $stmt->execute([$request->get('office_id'), $request->get('pic_type', 'S'), $request->get('filter', '')]);
            return response()->json($stmt->fetchAll(\PDO::FETCH_ASSOC));
        } catch (\Exception $e) {
            return response()->json([]);
        }
    }

    public function lookupShipmentModes(Request $request)
    {
        try {
            $pdo = DB::getPdo();
            $stmt = $pdo->prepare('EXEC sp_ShipmentMode @Filter = ?');
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
            $stmt = $pdo->prepare('EXEC sp_TransportMode @Filter = ?');
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
            $stmt = $pdo->prepare('EXEC sp_EquipmentType @Filter = ?');
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
            $submittedBy = Auth::check() ? Auth::user()->displayName ?? (Auth::user()->Email ?? 'system') : 'system';

            // ── Call sp_Quote_QuotationCopy ───────────────────────────────────────
            // SP handles: header insert + charge sheet copy + equipment details internally.
            // SP generates QuoteNo as: LEFT(OfficeCode,2) + '-QT-' + yy + '-' + 00001
            //________________________________________________________________________
            $pdo = DB::getPdo();
            $quotationId = DB::table('Quote_QuotationInfo')->where('QuoteNo', $quoteNo)->value('ID');

            if (!$quotationId) {
                return response()->json(
                    [
                        'success' => false,
                        'message' => 'Quotation not found for quote number: ' . $quoteNo,
                    ],
                    404,
                );
            }

            $pdo = DB::getPdo();
            $sql = "
                DECLARE @Status  VARCHAR(25)
                DECLARE @Remark  VARCHAR(200)
                DECLARE @QuoteNo VARCHAR(50)
                EXEC dbo.sp_Quote_QuotationCopy ?, ?,
                    @Status  = @Status  OUTPUT,
                    @Remark  = @Remark  OUTPUT,
                    @QuoteNo = @QuoteNo OUTPUT
                SELECT @Status AS Status, @Remark AS Remark, @QuoteNo AS QuoteNo
                ";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([$quotationId, $submittedBy]);

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
                return response()->json(
                    [
                        'success' => false,
                        'message' => 'No response from copy stored procedure',
                    ],
                    500,
                );
            }

            $spStatus = strtolower(trim($result['Status'] ?? ''));
            if ($spStatus !== 'success') {
                Log::error('processDuplicate - SP failed', [
                    'sp_status' => $result['Status'],
                    'sp_remark' => $result['Remark'],
                ]);
                return response()->json(
                    [
                        'success' => false,
                        'message' => $result['Remark'] ?? 'Copy stored procedure failed',
                    ],
                    500,
                );
            }

            $newQuoteNo = trim($result['QuoteNo'] ?? '');

            if (empty($newQuoteNo)) {
                Log::error('processDuplicate - SP succeeded but QuoteNo is empty', [
                    // 'original_id' => $original->ID,
                    'sp_remark' => $result['Remark'],
                ]);
                return response()->json(
                    [
                        'success' => false,
                        'message' => 'Copy succeeded but no QuoteNo was returned',
                    ],
                    500,
                );
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
                return response()->json(
                    [
                        'success' => false,
                        'message' =>
                            'Copy succeeded but new record could not be located (QuoteNo: ' . $newQuoteNo . ')',
                    ],
                    500,
                );
            }

            Log::info('processDuplicate - Success', [
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

            return response()->json(
                [
                    'success' => false,
                    'message' => $e->getMessage(),
                ],
                500,
            );
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

            $xml =
                "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
                <ArrayOfTransaction>
                    <Transaction>
                        <ID>{$request->quote_no}</ID>
                        <Status>{$request->status}</Status>
                        <CutomerRefNo>" .
                htmlspecialchars($request->customer_ref_no ?? '', ENT_XML1) .
                "</CutomerRefNo>
                        <TransactionId>" .
                htmlspecialchars($request->transaction_id ?? '', ENT_XML1) .
                "</TransactionId>
                        <Errors>{$errorsXml}</Errors>
                    </Transaction>
                </ArrayOfTransaction>";

            $pdo = DB::getPdo();
            $stmt = $pdo->prepare('EXEC dbo.sp_Quote_BookingCreateInfoUpdate @InputXML = ?');
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
            $stmt = $pdo->prepare('SET NOCOUNT ON; EXEC dbo.sp_IncoTerms @Filter = ?');
            $stmt->execute([$filter]);

            $results = [];
            do {
                try {
                    $rows = $stmt->fetchAll(\PDO::FETCH_OBJ);

                    if (empty($rows)) {
                        continue;
                    }

                    // Skip error rowsets from SP CATCH block
                    if (isset($rows[0]->Err_Message)) {
                        continue;
                    }

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
            return response()->json(
                [
                    'success' => false,
                    'data' => [],
                    'message' => $e->getMessage(),
                ],
                500,
            );
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
            $stmt = $pdo->prepare('SET NOCOUNT ON; EXEC dbo.sp_ClientList @Filter = ?');
            $stmt->execute([$filter]);

            $clients = [];
            do {
                try {
                    $rows = $stmt->fetchAll(\PDO::FETCH_OBJ);

                    if (empty($rows)) {
                        continue;
                    }

                    // Skip SP error result sets
                    $firstVal = array_values((array) $rows[0])[0] ?? '';
                    if (is_string($firstVal) && str_starts_with($firstVal, 'Error')) {
                        continue;
                    }

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
        $customerCode = trim($request->input('customer_code', '')); // This should be the Client Code like "KEAA"

        if (!$customerCode) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'customer_code is required',
                ],
                400,
            );
        }

        try {
            $pdo = DB::connection('sqlsrv')->getPdo();

            // Call with single parameter - the Customer Code
            $stmt = $pdo->prepare('SET NOCOUNT ON; EXEC dbo.sp_Quote_CustomerDetails ?');
            $stmt->execute([$customerCode]);

            $result = null;
            do {
                try {
                    $rows = $stmt->fetchAll(\PDO::FETCH_OBJ);
                    if (empty($rows)) {
                        continue;
                    }

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
                'company' => urldecode($result->Name ?? ''),
                'address1' => urldecode($result->Address1 ?? ''),
                'address2' => urldecode($result->Address2 ?? ''),
                'city' => urldecode($result->City ?? ''),
                'zip' => urldecode($result->Zip ?? ''),
                'state' => urldecode($result->State ?? ''),
                'country_id' => $result->Address_CountryId ?? '',
                'country_code' => $result->CountryCode ?? '',
                'country_name' => urldecode($result->CountryName ?? ''),
                'contact_person' => urldecode($result->ContactPerson ?? ''),
                'phone' => urldecode($result->PhoneNo ?? ''),
                'email' => urldecode($result->Email ?? ''),
                'industry_code' => $result->IndustryTypeCode ?? '',
                'industry_name' => urldecode($result->IndustryType ?? ''),
                'agent_type_code' => $result->AgentTypeCode ?? '',
                'agent_type_name' => urldecode($result->AgentType ?? ''),
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
            $validStatuses = ['Total', 'In Progress', 'Won', 'Cancelled', 'Lost', 'Failed Progress', 'Failed Int.'];

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
                        return response()->json(
                            [
                                'draw' => (int) $request->input('draw', 1),
                                'recordsTotal' => 0,
                                'recordsFiltered' => 0,
                                'data' => [],
                                'error' => 'End date must be after start date.',
                            ],
                            422,
                        );
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
            $stmt = $pdo->prepare('SET NOCOUNT ON; EXEC dbo.sp_Quote_QuotationList ?');
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

                        return response()->json(
                            [
                                'draw' => (int) $request->input('draw', 1),
                                'recordsTotal' => 0,
                                'recordsFiltered' => 0,
                                'data' => [],
                                'error' => 'Report query failed. Please contact your administrator.',
                            ],
                            500,
                        );
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

            return response()->json(
                [
                    'draw' => (int) $request->input('draw', 1),
                    'recordsTotal' => 0,
                    'recordsFiltered' => 0,
                    'data' => [],
                    'error' => 'An unexpected error occurred.',
                ],
                500,
            );
        }
    }

    /**
     * Returns quotation stats for the dashboard home card.
     * All-time scope (no date filter), scoped to the calling user's offices.
     * Keys match the dashboard card: total, in_progress, failed_int, failed_progress.view
     */
    public static function getHomeStats(?string $officeCode = null): array
    {
        $blank = [
            'total' => 0,
            'in_progress' => 0,
            'failed_int' => 0,
            'failed_progress' => 0,
        ];

        try {
            $instance = new self();

            $allowedOfficeCodes = $officeCode
                ? collect([strtoupper(trim($officeCode))])
                : $instance->resolveAllowedOfficeCodesViaSP();

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

    private function callSPForAllOffices(string $status, \Illuminate\Support\Collection $officeCodes): array
    {
        $merged = [];
        $seen = [];

        foreach ($officeCodes as $code) {
            $rows = $this->callQuotationListSP($status, '', '', $code);
            foreach ($rows as $row) {
                $key = $row->QuoteNo ?? null;
                if ($key === null || isset($seen[$key])) {
                    continue;
                }
                $seen[$key] = true;
                $merged[] = $row;
            }
        }

        return $merged;
    }

    public function pushBookingToSCM(Request $request): JsonResponse
    {
        $quoteNo = $request->input('quote_no');
        $bookingNo = $request->input('booking_no');

        if (!$quoteNo && !$bookingNo) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'quote_no or booking_no is required',
                ],
                400,
            );
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
                return response()->json(
                    [
                        'success' => false,
                        'message' => 'SCMBookingXML is empty. Run the XML generation step first.',
                    ],
                    422,
                );
            }

            Log::info('pushBookingToSCM - Starting', [
                'quote_no' => $quotation->QuoteNo,
                'booking_no' => $quotation->bookingno,
                'xml_length' => strlen($quotation->SCMBookingXML),
            ]);

            // ── Step 1: Generate Token ────────────────────────────────────────────
            $tokenUrl = env('SCM_TOKEN_URL', 'https://hhtapi.freightintime.com/token');
            $authString =
                'grant_type=password' .
                '&username=' .
                urlencode(env('SCM_USERNAME', 'fitexpress')) .
                '&password=' .
                urlencode(env('SCM_PASSWORD', 'Forwarder@8082'));

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
                return response()->json(
                    [
                        'success' => false,
                        'message' => 'Failed to generate SCM token: ' . $tokenError,
                    ],
                    500,
                );
            }

            $tokenData = json_decode($tokenResponse);
            if (json_last_error() !== JSON_ERROR_NONE || !isset($tokenData->access_token)) {
                Log::error('pushBookingToSCM - Token parse failed', [
                    'quote_no' => $quotation->QuoteNo,
                    'response' => $tokenResponse,
                ]);
                return response()->json(
                    [
                        'success' => false,
                        'message' => 'Failed to parse SCM token response',
                    ],
                    500,
                );
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
                return response()->json(
                    [
                        'success' => false,
                        'message' => 'Failed to push XML to SCM: ' . $curlError,
                    ],
                    500,
                );
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
                return response()->json(
                    [
                        'success' => false,
                        'message' => 'SCM returned an invalid XML response: ' . $scmResponse,
                    ],
                    500,
                );
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
            $stmt = $pdo->prepare('SET NOCOUNT ON; EXEC dbo.sp_Quote_BookingCreateInfoUpdate ?');
            $stmt->execute([$inputXml]);

            $spError = null;
            do {
                try {
                    $rows = $stmt->fetchAll(\PDO::FETCH_OBJ);
                    if (empty($rows)) {
                        continue;
                    }
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
                return response()->json(
                    [
                        'success' => false,
                        'message' => $spError,
                    ],
                    422,
                );
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
                'message' =>
                    $status === 'Success'
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
            return response()->json(
                [
                    'success' => false,
                    'message' => $e->getMessage(),
                ],
                500,
            );
        }
    }

    public function salesIntelligence(Request $request)
    {
        $officeCode = $request->input('office_code');
        $customer = $request->input('customer');

        $inputJson = json_encode([
            'OfficeCode' => $officeCode,
            'Status' => 'Total',
            'Customer' => $customer,
            'FromDate' => '',
            'ToDate' => '',
            'Service' => '',
            'Process' => '',
        ]);

        try {
            $results = DB::select('EXEC sp_Quote_QuotationList @InputJson = ?', [$inputJson]);

            // Exact match guard — SP uses LIKE, we enforce exact match here
            $company = $customer;
            $filtered = array_filter($results, fn($r) => trim($r->CompanyName) === trim($company));

            return response()->json([
                'success' => true,
                'data' => array_values($filtered),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function showStatusResetForm()
    {
        return view('quotation.status-reset');
    }

    public function resetStatus(Request $request)
    {
        $request->validate([
            'quote_no' => 'required|string|max:50',
            'current_status' => 'required|in:Won,Failed Int.',
        ]);

        $quoteNo = trim($request->quote_no);
        $currentStatus = $request->current_status;

        // Check if quotation exists and has the given status
        $quotation = DB::table('Quote_QuotationInfo')
            ->where('QuoteNo', $quoteNo)
            ->first(['ID', 'QuoteNo', 'QuoteStatus', 'status']);

        if (!$quotation) {
            return back()
                ->withErrors(['quote_no' => 'Quotation not found.'])
                ->withInput();
        }

        // Verify the current status matches the user's selection
        $actualStatus = $quotation->QuoteStatus ?? $quotation->status;
        if ($actualStatus !== $currentStatus) {
            return back()
                ->withErrors(['current_status' => "The quote is currently '{$actualStatus}', not '{$currentStatus}'."])
                ->withInput();
        }

        // Perform the update
        $updated = DB::table('Quote_QuotationInfo')
            ->where('QuoteNo', $quoteNo)
            ->update([
                'QuoteStatus' => 'In Progress',
                'status' => 'In Progress',
                'LastModifiedBy' => auth()->user()->displayName ?? (auth()->user()->Email ?? 'system'),
                'updatedon' => now(),
            ]);

        // Optional: clear any failure remarks if it was Failed Int.
        if ($currentStatus === 'Failed Int.') {
            DB::table('Quote_QuotationInfo')
                ->where('QuoteNo', $quoteNo)
                ->update(['QuoteStatusDescription' => null]); // clear error history
        }

        Log::info('Status reset', [
            'quote_no' => $quoteNo,
            'from' => $currentStatus,
            'to' => 'In Progress',
            'by' => auth()->user()->Email ?? 'system',
        ]);

        return redirect()
            ->route('quotation.status-reset.form')
            ->with('success', "Quote {$quoteNo} has been moved from '{$currentStatus}' back to 'In Progress'.");
    }

    /**
     * Returns the current approval status for a quotation.
     *
     * TODO: Replace with sp_Quote_ApprovalStatus once the DBA delivers the SP.
     * Expected SP output shape (agreed contract):
     *   status, required, approvedCount, approvers[] = {userId, name, status, date, comments}
     */
    public function getApprovalStatus($id)
    {
        // Once the SP exists, this becomes:
        // $result = DB::select('EXEC sp_Quote_ApprovalStatus @QuotationID = ?', [$id]);
        // ...map and return real data.

        return response()->json(
            [
                'success' => false,
                'message' => 'Approval status is not available yet — backend implementation pending.',
                'data' => null,
            ],
            200,
        );
    }

    /**
     * Submits a quotation for approval.
     *
     * TODO: Replace with sp_Quote_ApprovalRequest once the DBA delivers the SP.
     * Expected inputs: QuotationID, SubmittedBy (Auth::id()).
     */
    public function sendForApproval(Request $request, int $id)
    {
        Log::info('sendForApproval: triggered', ['quotationId' => $id]);

        // TODO: replace with sp_ApprovalRequest call
        // $approvers = DB::select('EXEC sp_ApprovalRequest @QuotationID = ?', [$id]);

        $mockApprovers = [
            [
                'userId' => 1,
                'name' => 'Imran',
                'email' => 'imran@freight-in-time.com',
            ],
        ];

        // TODO: replace mock quotation data with real lookup
        $quotation = [
            'quotationId' => $id,
            'quoteNo' => 'QT-2026-' . str_pad($id, 4, '0', STR_PAD_LEFT),
            'customer' => 'Pending SP',
            'revenue' => 0,
            'cost' => 0,
            'profit' => 0,
            'margin' => 0,
            'currency' => 'USD',
        ];

        // TODO: resolve real PDF path from quotation record
        $pdfPath = '';

        $emailService = new \App\Services\ApprovalEmailService();
        $results = $emailService->sendApprovalEmails($quotation, $mockApprovers, $pdfPath);

        $allSent = collect($results)->every(fn($r) => $r['status'] === 'Sent');

        return response()->json([
            'success' => $allSent,
            'message' => $allSent
                ? 'Approval emails sent successfully.'
                : 'Some approval emails failed. Check logs for details.',
            'data' => $results,
        ]);
    }

    /**
     * Approvals Dashboard — lists all open/closed approval requests.
     *
     * TODO: Replace mock data with sp_Quote_ApprovalSummary once approved (see progress table #6).
     * Expected SP output per row: requestId, quoteNo, customer, submittedBy,
     * submittedDate, required, approvedCount, status, officeCode.
     */
    public function approvalsDashboard(Request $request)
    {
        // Once the SP exists, replace mock with sp_Quote_ApprovalSummary results
        $mockRows = [
            [
                'requestId' => 1,
                'quoteNo' => 'QT-2026-0001',
                'customer' => 'Acme Logistics',
                'submittedBy' => 'jane.doe',
                'submittedDate' => '2026-06-25',
                'required' => 2,
                'approvedCount' => 1,
                'status' => 'Pending Approval',
                'officeCode' => 'NBO',
            ],
            [
                'requestId' => 2,
                'quoteNo' => 'QT-2026-0002',
                'customer' => 'Globex Freight',
                'submittedBy' => 'john.smith',
                'submittedDate' => '2026-06-20',
                'required' => 2,
                'approvedCount' => 2,
                'status' => 'Approved',
                'officeCode' => 'MBA',
            ],
        ];

        // fix: counts derived from the same rows — once SP lands, SP should
        // ideally return these counts directly rather than recomputing client-side
        $counts = [
            'pending' => collect($mockRows)->where('status', 'Pending Approval')->count(),
            'approved' => collect($mockRows)->where('status', 'Approved')->count(),
            'rejected' => collect($mockRows)->where('status', 'Rejected')->count(),
            'total' => count($mockRows),
        ];

        return view('quotation.approvals-dashboard', [
            'rows' => $mockRows,
            'counts' => $counts,
        ]);
    }

    /**
     * Submits an Approve/Reject decision from the dashboard.
     *
     * TODO: Replace with sp_Quote_ApprovalDecision once approved (see progress table #6).
     * Server-side SP must validate the logged-in user is a configured approver
     * for this request — UI-side disabling is not a security control.
     */
    public function submitApprovalDecision(Request $request, $requestId)
    {
        $request->validate([
            'decision' => 'required|in:Approved,Rejected',
            'comments' => 'nullable|string|max:1000',
        ]);

        // Once the SP exists:
        // DB::statement('EXEC sp_Quote_ApprovalDecision @ApprovalRequestID = ?, @ApproverUserID = ?, @Decision = ?, @Comments = ?', [
        //     $requestId, Auth::id(), $request->decision, $request->comments,
        // ]);

        return response()->json(
            [
                'success' => false,
                'message' => 'Approval decision is not available yet — backend implementation pending.',
            ],
            200,
        );
    }

    /**
     * Show the approval decision page.
     * Reached via email link — action and approver pre-filled from query params.
     * Auth middleware ensures the approver is logged in before they can act.
     */
    public function approvePage(Request $request, int $id)
    {
        // TODO: Replace mock with real quotation lookup once SP contract is confirmed
        $quotation = [
            'quotationId' => $id,
            'quoteNo' => 'QT-2026-' . str_pad($id, 4, '0', STR_PAD_LEFT),
            'customer' => 'Pending SP',
            'revenue' => 0,
            'cost' => 0,
            'profit' => 0,
            'margin' => 0,
            'currency' => 'USD',
        ];

        return view('quotation.approve', [
            'quotation' => $quotation,
            'action' => $request->query('action', 'approve'),
            'approverId' => $request->query('approver', auth()->id()),
        ]);
    }

    /**
     * Map one entry from the SP's Charges array into the flat structure the
     * frontend consumes. $side is 'revenue' or 'cost' — a paired row is emitted
     * once for each side so a markup charge appears in both the revenue table
     * (at SellRate) and the cost table (at CostRate).
     */
    private function mapChargeRow(array $c, string $side, array $supplement = []): array
    {
        $isRevenue = $side === 'revenue';

        // Row IDs — paired rows carry both; single-sided rows carry one.
        $id = $isRevenue ? (string) ($c['RevenueChargeRowID'] ?? '') : (string) ($c['CostChargeRowID'] ?? '');

        $rate = (float) ($isRevenue ? $c['SellRate'] ?? 0 : $c['CostRate'] ?? 0);
        $amount = (float) ($isRevenue ? $c['SellAmount'] ?? 0 : $c['CostAmount'] ?? 0);
        $quantity = (float) ($c['Quantity'] ?? 0);
        $increaseBy = (float) ($c['IncreaseBy'] ?? 0);
        $increaseType = $c['IncreaseType'] ?? '' ?: 'Percentage';

        // Amount is what the SP returned — the client already computed it at save time.
        // The supplement only carries category metadata; do NOT use its baseAmount
        // as the display value, because EstBaseAmount in the sheet is the SP-owned
        // figure and may drift from what the user actually saved.
        $supp = $supplement[$id] ?? [];
        $baseAmount = $amount;
        $exchangeRate = (float) ($supp['exChangeRate'] ?? 1.0);
        $category = (string) ($supp['category'] ?? ($c['ChargeCatagory'] ?? ''));

        // Markup is a cost-side attribute — cost row with non-zero IncrementBy.
        $isMarkup = !$isRevenue && $increaseBy > 0;

        $basedOnCode = (string) ($c['BasedOnCode'] ?? '');
        $basedOnUOM = (string) ($c['BasedOnUOM'] ?? '');

        return [
            'costId' => (string) ($c['CostChargeRowID'] ?? ''),
            'revenueId' => (string) ($c['RevenueChargeRowID'] ?? ''),
            'pairKey' => (string) ($c['CostChargeRowID'] ?? '') . '|' . (string) ($c['RevenueChargeRowID'] ?? ''),
            'id' => $id,
            'chargeCode' => (string) ($c['ChargeCode'] ?? ''),
            'chargeName' => (string) ($c['ChargeDescription'] ?? ''),
            'costRate' => (float) ($c['CostRate'] ?? 0),
            'costAmount' => (float) ($c['CostAmount'] ?? 0),
            'sellRate' => (float) ($c['SellRate'] ?? 0),
            'sellAmount' => (float) ($c['SellAmount'] ?? 0),
            'rate' => $rate,
            'quantity' => $quantity,
            'amtInChgCurr' => $amount,
            'amtInBaseCurr' => $baseAmount,
            'exChangeRate' => $exchangeRate,
            'chgCurr' => (string) ($c['ChgCurr'] ?? 'KES'),
            'basedOnCode' => $basedOnCode,
            'basedOnAndUOM' => trim($basedOnCode . ' / ' . $basedOnUOM, ' /'),
            'uomCode' => $basedOnUOM,
            'chargeRemark' => (string) ($c['ChargeRemark'] ?? ''),
            'margin' => (float) ($c['Margin'] ?? 0),
            'partyCode' => (string) ($c['PartyCode'] ?? ''),
            'category' => $category,
            'increaseBy' => $increaseBy,
            'increaseType' => $increaseType,
            'revenueOrCost' => $isMarkup ? 'Cost with Markup' : ($isRevenue ? 'Revenue' : 'Cost'),
            'sourceType' => 'manual',
        ];
    }
    public function bulkSaveCharges(Request $request)
    {
        $request->validate([
            'quotation_id' => 'required',
            'booking_no' => 'required',
            'office_code' => 'required',
            'charges' => 'required|array',
        ]);

        $results = [];
        $errors = [];

        foreach ($request->input('charges') as $idx => $charge) {
            try {
                $payload = new Request([
                    'action' => $charge['action'] ?? 'Add',
                    'quotation_id' => $request->input('quotation_id'),
                    'booking_no' => $request->input('booking_no'),
                    'office_code' => $request->input('office_code'),
                    'quote_charge_id' => $charge['quote_charge_id'] ?? '',
                    'party_code' => $charge['party_code'] ?? '',
                    'revenue_or_cost' => $charge['revenue_or_cost'] ?? 'Cost',
                    'charge_code' => $charge['charge_code'] ?? '',
                    'quantity' => $charge['quantity'] ?? 0,
                    'rate' => $charge['rate'] ?? 0,
                    'amt_in_chg_curr' => $charge['amt_in_chg_curr'] ?? 0,
                    'amt_in_base_curr' => $charge['amt_in_base_curr'] ?? 0,
                    'exchange_rate' => $charge['exchange_rate'] ?? 1,
                    'chg_curr' => $charge['chg_curr'] ?? '',
                    'based_on_code' => $charge['based_on_code'] ?? '',
                    'based_on_uom' => $charge['based_on_uom'] ?? '',
                    'charge_remark' => $charge['charge_remark'] ?? '',
                    'to_be_added' => $charge['to_be_added'] ?? 0,
                    'increase_by' => $charge['increase_by'] ?? 0,
                    'increase_type' => $charge['increase_type'] ?? 'Percentage',
                    'source_type' => $charge['source_type'] ?? 'manual',
                ]);

                $response = $this->saveCharge($payload);
                $body = json_decode($response->getContent(), true);

                if (!($body['success'] ?? false)) {
                    $errors[] = "Row {$idx}: " . ($body['message'] ?? 'Unknown error');
                }
                $results[] = $body;
            } catch (\Throwable $e) {
                $errors[] = "Row {$idx}: " . $e->getMessage();
            }
        }

        if ($errors) {
            return response()->json(
                [
                    'success' => false,
                    'message' => implode(' | ', $errors),
                    'results' => $results,
                ],
                422,
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'All charges saved successfully',
            'results' => $results,
        ]);
    }

    /**
     * Handle Approve/Reject decision from the approval page.
     *
     * TODO: Replace stub with sp_ApprovalStatus call once DBA delivers SP.
     * Expected inputs to SP: QuotationID, ApproverUserID, Decision, Comments.
     */
    public function approvalDecision(Request $request, int $id)
    {
        $request->validate([
            'decision' => 'required|in:Approved,Rejected',
            'approver_id' => 'required|integer',
            'comments' => 'nullable|string|max:1000',
        ]);

        Log::info('approvalDecision: decision received', [
            'quotationId' => $id,
            'approverId' => $request->approver_id,
            'decision' => $request->decision,
        ]);

        // TODO: wire to SP once delivered
        // DB::statement('EXEC sp_ApprovalStatus @QuotationID = ?, @ApproverUserID = ?, @Decision = ?, @Comments = ?', [
        //     $id, $request->approver_id, $request->decision, $request->comments,
        // ]);

        return response()->json([
            'success' => false,
            'message' => 'Approval decision is not available yet — SP pending from DBA.',
            'data' => null,
        ]);
    }
}
