<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class WebsiteQuotationController extends Controller
{
    protected $quotationController;

    public function __construct(QuotationController $quotationController)
    {
        $this->quotationController = $quotationController;
    }

    public function index(Request $request)
    {
        $parentResponse = $this->quotationController->index($request);
        $data = $parentResponse->getData();
        return view('quotation.website-requests.index', $data);
    }

    public function portal(Request $request)
    {
        try {
            $cardFilter = $request->input('card_filter', 'in_progress');
            $days = $request->input('days', 'all');
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');
            $draw = (int) $request->input('draw', 1);
            $start = (int) $request->input('start', 0);
            $length = (int) $request->input('length', 25);
            $search = trim($request->input('search.value', ''));

            // ── Resolve allowed office codes ──────────────────────────────────
            $allowedOfficeCodes = $this->quotationController->resolveAllowedOfficeCodesViaSP();

            if ($allowedOfficeCodes->isEmpty()) {
                return $this->emptyResponse($draw);
            }

            // ── Base query — website submissions only ─────────────────────────
            // Web quotes have NULL OfficeCode and QUOT-XX-XXXXXX QuoteNo format
            // with no office prefix. source='Web' is the only reliable scope.
            $query = DB::table('Quote_QuotationInfo')->where('source', 'Web');

            // ── Office dropdown filter ────────────────────────────────────────
            $requestedOffice = $request->input('office');
            if ($requestedOffice) {
                $filterCode = strtoupper(
                    trim(
                        DB::table('Cust_Office')
                            ->where('Name', $requestedOffice)
                            ->where('IsActive', 1)
                            ->where('IsDeleted', 0)
                            ->value('Code') ?? '',
                    ),
                );

                if ($filterCode) {
                    $query->where(function ($q) use ($filterCode) {
                        $q->whereRaw("LEFT(RTRIM(ISNULL(OfficeCode,'')), 2) = ?", [$filterCode])->orWhereNull(
                            'OfficeCode',
                        );
                    });
                }
            }

            // ── Date filter ───────────────────────────────────────────────────
            if ($days === 'custom' && $startDate && $endDate) {
                $query->whereBetween('CreatedOn', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
            } elseif ($days === '0') {
                $query->whereDate('CreatedOn', today());
            } elseif ($days === '1') {
                $query->whereDate('CreatedOn', now()->subDay());
            } elseif (is_numeric($days)) {
                $query->where('CreatedOn', '>=', now()->subDays((int) $days));
            }

            // ── Fetch all matching records ────────────────────────────────────
            $allRecords = $query->get([
                'ID',
                'QuoteNo',
                'bookingno',
                'teamid',
                'pri_partnermaster_name',
                'username',
                'origincountry',
                'destinationcountry',
                'ServiceVertical',
                'process',
                'incoterm',
                'scopevolume',
                'cargovolume',
                'containerlist',
                'QuoteStatus',
                'SCMINTStatus',
                'QuoteStatusDescription',
                'CreatedOn',
                'OfficeCode',
            ]);

            Log::info('WebsiteQuotation records found', ['count' => $allRecords->count()]);

            // ── Compute stats ─────────────────────────────────────────────────
            $stats = $this->computeStats($allRecords);
            $filtered = $this->applyCardFilter($allRecords, $cardFilter);

            // ── Search ────────────────────────────────────────────────────────
            if (!empty($search)) {
                $needle = strtolower($search);
                $filtered = $filtered
                    ->filter(function ($row) use ($needle) {
                        return str_contains(strtolower($row->pri_partnermaster_name ?? ''), $needle) ||
                            str_contains(strtolower($row->QuoteNo ?? ''), $needle) ||
                            str_contains(strtolower($row->origincountry ?? ''), $needle) ||
                            str_contains(strtolower($row->destinationcountry ?? ''), $needle) ||
                            str_contains(strtolower($row->ServiceVertical ?? ''), $needle) ||
                            str_contains(strtolower($row->process ?? ''), $needle) ||
                            str_contains(strtolower($row->incoterm ?? ''), $needle);
                    })
                    ->values();
            }

            $total = $filtered->count();
            $pageRows = $filtered->slice($start, $length > 0 ? $length : $total)->values();

            $data = $pageRows->map(fn($row) => $this->formatRow($row, $cardFilter))->values()->toArray();

            return response()->json([
                'draw' => $draw,
                'recordsTotal' => $total,
                'recordsFiltered' => $total,
                'data' => $data,
                'stats' => $stats,
            ]);
        } catch (\Throwable $e) {
            Log::error('WebsiteQuotationController::portal - Exception', [
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

    // ── Private helpers ───────────────────────────────────────────────────────

    private function resolveDisplayStatus(object $row): string
    {
        $quoteStatus = trim($row->QuoteStatus ?? '');
        $scmStatus = $row->SCMINTStatus ?? null;

        return match (true) {
            strtolower($quoteStatus) === 'completed' => 'Completed',
            strtolower($quoteStatus) === 'won' && $scmStatus === 'Success' => 'Completed',
            strtolower($quoteStatus) === 'won' && !is_null($scmStatus) && $scmStatus !== 'Success' => 'Failed Int.',
            strtolower($quoteStatus) === 'won' => 'Won',
            strtolower($quoteStatus) === 'failed progress' => 'Failed Progress',
            in_array(strtolower($quoteStatus), ['failed int.', 'failed int']) => 'Failed Int.',
            in_array(strtolower($quoteStatus), ['lost', 'cancelled', 'cancel']) => 'Lost / Canceled',
            default => 'In Progress',
        };
    }

    private function computeStats(\Illuminate\Support\Collection $records): array
    {
        $inProgress = $won = $completed = $failedInt = $failedProgress = $lostCanceled = 0;

        foreach ($records as $row) {
            $status = $this->resolveDisplayStatus($row);
            match ($status) {
                'In Progress' => $inProgress++,
                'Won' => $won++,
                'Completed' => $completed++,
                'Failed Int.' => $failedInt++,
                'Failed Progress' => $failedProgress++,
                'Lost / Canceled' => $lostCanceled++,
                default => $inProgress++,
            };
        }

        $total = $inProgress + $won + $completed + $failedInt + $failedProgress + $lostCanceled;

        return [
            'total' => $total,
            'in_progress' => $inProgress,
            'in_progress_request' => 0,
            'won' => $won,
            'completed' => $completed,
            'lost_canceled' => $lostCanceled,
            'failed_int' => $failedInt,
            'failed_progress' => $failedProgress,
        ];
    }

    private function applyCardFilter(
        \Illuminate\Support\Collection $records,
        string $cardFilter,
    ): \Illuminate\Support\Collection {
        if ($cardFilter === 'total') {
            return $records;
        }

        return $records
            ->filter(function ($row) use ($cardFilter) {
                $status = $this->resolveDisplayStatus($row);

                return match ($cardFilter) {
                    'in_progress' => $status === 'In Progress',
                    'won' => $status === 'Won',
                    'completed' => $status === 'Completed',
                    'failed_int' => $status === 'Failed Int.',
                    'failed_progress' => $status === 'Failed Progress',
                    'lost_canceled' => $status === 'Lost / Canceled',
                    default => true,
                };
            })
            ->values();
    }

    private function formatRow(object $row, string $cardFilter): array
    {
        $displayStatus = $this->resolveDisplayStatus($row);

        $badgeClass = match ($displayStatus) {
            'Won' => 'badge-success',
            'Completed' => 'badge-teal',
            'Lost / Canceled' => 'badge-warning',
            'Failed Int.' => 'badge-danger',
            'Failed Progress' => 'badge-secondary',
            default => 'badge-info',
        };

        // ── Remark ────────────────────────────────────────────────────────────
        $remark = '';
        if (in_array($displayStatus, ['Failed Int.', 'Failed Progress'])) {
            $raw = trim($row->QuoteStatusDescription ?? '');
            if ($raw) {
                $decoded = json_decode('[' . $raw . ']', true);
                $remark =
                    json_last_error() === JSON_ERROR_NONE && is_array($decoded)
                        ? implode(' | ', array_column($decoded, 'Remark'))
                        : $raw;
            }
        }

        $remarksHtml = '<span class="text-muted">—</span>';
        if ($remark) {
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

        // ── Action buttons ────────────────────────────────────────────────────
        $actionHtml = '<div class="action-buttons">';
        $actionHtml .=
            '<a href="' .
            route('quotation.view', $row->ID) .
            '" class="btn btn-info btn-sm" title="View"><i class="fas fa-eye"></i></a>';

        if ($cardFilter === 'in_progress' && $displayStatus === 'In Progress') {
            $actionHtml .=
                ' <a href="' .
                route('quotation.edit', $row->ID) .
                '" class="btn btn-warning btn-sm" title="Edit"><i class="fas fa-edit"></i></a>';
        }

        if (in_array($cardFilter, ['won', 'failed_int']) && in_array($displayStatus, ['Won', 'Failed Int.'])) {
            $actionHtml .=
                ' <button class="btn btn-success btn-sm btn-create-booking"' .
                ' data-id="' .
                $row->ID .
                '"' .
                ' data-booking="' .
                $row->bookingno .
                '"' .
                ' data-team="' .
                $row->teamid .
                '"' .
                ' data-quoteno="' .
                $row->QuoteNo .
                '"' .
                ' data-origin="' .
                e($row->origincountry ?? '') .
                '"' .
                ' data-destination="' .
                e($row->destinationcountry ?? '') .
                '"' .
                ' data-status="' .
                e($row->SCMINTStatus ?? '') .
                '"' .
                ' title="Create Booking"><i class="fas fa-plus-circle"></i></button>';
        }

        $actionHtml .= '</div>';

        return [
            'QuoteNo' => $row->QuoteNo ?? '-',
            'Date' => $row->CreatedOn ? date('Y-m-d', strtotime($row->CreatedOn)) : '-',
            'CompanyName' => $row->pri_partnermaster_name ?? '-',
            'SalesPerson' => $row->username ?? '-',
            'Service' => $row->ServiceVertical ?? '-',
            'Process' => $row->process ?? '-',
            'OriginCountry' => $row->origincountry ?? '-',
            'DestinationCountry' => $row->destinationcountry ?? '-',
            'IncoTerm' => $row->incoterm ?? '-',
            'ScopeOfVolume' => $row->scopevolume ?? '-',
            'CargoVolume' => $row->cargovolume ?? null,
            'ContainerList' => $row->containerlist ?? '-',
            'Status' => '<span class="badge ' . $badgeClass . '">' . e($displayStatus) . '</span>',
            'Remark' => $remarksHtml,
            'action' => $actionHtml,
        ];
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

    private function emptyResponse(int $draw): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'draw' => $draw,
            'recordsTotal' => 0,
            'recordsFiltered' => 0,
            'data' => [],
            'stats' => $this->emptyStats(),
        ]);
    }
}
