<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;

class ShipmentReportController extends Controller
{
    /**
     * Display shipment email reports dashboard
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        try {
            // Get statistics for the cards (default: today)
            $stats = $this->getStatistics(0);

            // Get unique office names for the filter dropdown
            $offices = $this->getOfficeList();

            return view('shipment-reports.index', compact('stats', 'offices'));
        } catch (\Exception $e) {
            Log::error('Shipment Reports Dashboard Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->with('error', 'Failed to load shipment reports dashboard');
        }
    }

    /**
     * Get data for DataTables
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getData(Request $request)
    {
        try {
            $days = $request->input('days', 0);
            $search = $request->input('search.value', '');
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');
            $office = $request->input('office');
            $cardFilter = $request->input('card_filter', 'all');

            // Log the incoming request for debugging
            Log::info('DataTables Request', [
                'days' => $days,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'office' => $office,
                'card_filter' => $cardFilter,
                'search' => $search
            ]);

            // Build query
            $query = DB::connection('sqlsrv_auth')
                ->table('Email_EmailScheduler')
                ->select([
                    DB::raw('CAST(SubmittedOn as DATE) as report_date'),
                    'OfficeName',
                    'ClientName',
                    'ServiceName',
                    DB::raw('ISNULL(NoofShipment, 0) as shipment_count'),  // MODIFIED
                    'Status',
                    'SendRemark'
                ])
                ->whereNotNull('SubmittedOn');

            // Apply date filter
            if ($startDate && $endDate) {
                $query->whereBetween(DB::raw('CAST(SubmittedOn as DATE)'), [$startDate, $endDate]);
            } elseif (is_numeric($days)) {
                $query->where(DB::raw('CAST(SubmittedOn as DATE)'), '>=', Carbon::now()->subDays((int)$days)->toDateString());
            }

            // Apply office filter
            if ($office) {
                $query->where('OfficeName', $office);
            }

            // Apply card filter
            switch ($cardFilter) {
                case 'no_email':
                    $query->where('SendRemark', 'No Email ID Present');
                    break;
                case 'milestone_failures':
                    $query->where('SendRemark', 'Milestones Not Configured');
                    break;
                case 'failed':
                    $query->where('SendRemark', 'Milestones Not Updated');
                    break;
                case 'success':
                    $query->where('Status', 'Success');
                    break;
            }

            // Apply search (only on actual table columns, not computed columns)
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('ClientName', 'like', "%{$search}%")
                        ->orWhere('ServiceName', 'like', "%{$search}%")
                        ->orWhere('Status', 'like', "%{$search}%")
                        ->orWhere('OfficeName', 'like', "%{$search}%")
                        ->orWhere('SendRemark', 'like', "%{$search}%");
                });
            }

            // $query->groupBy([
            //     DB::raw('CAST(SubmittedOn as DATE)'),
            //     'OfficeName',
            //     'ClientName',
            //     'ServiceName',
            //     'Status',
            //     'SendRemark'
            // ]);

            return DataTables::of($query)
                ->editColumn('report_date', function ($row) {
                    return Carbon::parse($row->report_date)->format('d M Y');
                })
                ->editColumn('Status', function ($row) {
                    if ($row->Status === 'Success') {
                        return '<span class="badge badge-success">Success</span>';
                    } else {
                        return '<span class="badge badge-danger">Failed</span>';
                    }
                })
                ->editColumn('SendRemark', function ($row) {
                    $remark = $row->SendRemark ?? 'N/A';
                    if (strlen($remark) > 80) {
                        $truncated = substr($remark, 0, 80) . '...';
                        return '<span title="' . htmlspecialchars($remark) . '">' . htmlspecialchars($truncated) . '</span>';
                    }
                    return htmlspecialchars($remark);
                })
                ->editColumn('shipment_count', function ($row) {
                    return number_format($row->shipment_count);
                })
                ->filterColumn('report_date', function ($query, $keyword) {
                    // Don't filter on this computed column via DataTables
                    // The custom search above already handles filtering
                })
                ->filterColumn('shipment_count', function ($query, $keyword) {  // ADDED
                    // Don't filter on this computed column via DataTables
                })
                ->rawColumns(['Status', 'SendRemark'])
                ->make(true);
        } catch (\Exception $e) {
            Log::error('Shipment Reports DataTables Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);

            return response()->json([
                'error' => 'Failed to load data: ' . $e->getMessage(),
                'draw' => $request->input('draw', 0),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => []
            ], 500);
        }
    }

    /**
     * Get statistics for dashboard cards
     *
     * @param int $days Number of days to look back
     * @return array
     */
    private function getStatistics($days = 0)
    {
        try {
            $startDate = Carbon::now()->subDays($days)->toDateString();

            $stats = DB::connection('sqlsrv_auth')
                ->table('Email_EmailScheduler')
                ->select([
                    DB::raw("COUNT(*) as total_emails"),
                    DB::raw("SUM(CASE WHEN Status = 'Success' THEN 1 ELSE 0 END) as emails_sent"),
                    DB::raw("SUM(CASE WHEN SendRemark = 'No Email ID Present' THEN 1 ELSE 0 END) as no_email"),
                    DB::raw("SUM(CASE WHEN SendRemark = 'Milestones Not Updated' THEN 1 ELSE 0 END) as milestones_not_updated"),
                    DB::raw("SUM(CASE WHEN SendRemark = 'Milestones Not Configured' THEN 1 ELSE 0 END) as milestones_not_configured")
                ])
                ->where(DB::raw('CAST(SubmittedOn as DATE)'), '>=', $startDate)
                ->first();

            return [
                'total_emails' => $stats->total_emails ?? 0,
                'emails_sent' => $stats->emails_sent ?? 0,
                'no_email' => $stats->no_email ?? 0,
                'emails_failed' => $stats->milestones_not_updated ?? 0,
                'milestone_failures' => $stats->milestones_not_configured ?? 0,
            ];
        } catch (\Exception $e) {
            Log::error('Failed to get shipment reports statistics', [
                'error' => $e->getMessage(),
                'days' => $days
            ]);

            return [
                'total_emails' => 0,
                'emails_sent' => 0,
                'no_email' => 0,
                'emails_failed' => 0,
                'milestone_failures' => 0,
            ];
        }
    }

    /**
     * Get list of unique office names for filter dropdown
     *
     * @return \Illuminate\Support\Collection
     */
    private function getOfficeList()
    {
        try {
            $offices = DB::connection('sqlsrv_auth')
                ->table('Email_EmailScheduler')
                ->select('OfficeName')
                ->whereNotNull('OfficeName')
                ->where('OfficeName', '!=', '')
                ->distinct()
                ->orderBy('OfficeName')
                ->pluck('OfficeName');

            return $offices;
        } catch (\Exception $e) {
            Log::error('Failed to get office list', [
                'error' => $e->getMessage()
            ]);

            return collect([]);
        }
    }

    /**
     * Get statistics for home page card (last 7 days)
     *
     * @return array
     */
    public static function getHomeStats()
    {
        try {
            $startDate = Carbon::now()->subDays(7)->toDateString();

            $stats = DB::connection('sqlsrv_auth')
                ->table('Email_EmailScheduler')
                ->select([
                    DB::raw("COUNT(*) as total_emails"),
                    DB::raw("SUM(CASE WHEN Status = 'Success' THEN 1 ELSE 0 END) as emails_sent"),
                    DB::raw("SUM(CASE WHEN Status = 'Failed' THEN 1 ELSE 0 END) as failed")
                ])
                ->where(DB::raw('CAST(SubmittedOn as DATE)'), '>=', $startDate)
                ->first();

            $successRate = $stats->total_emails > 0
                ? round(($stats->emails_sent / $stats->total_emails) * 100, 0)
                : 0;

            return [
                'emails_sent' => $stats->emails_sent ?? 0,
                'success_rate' => $successRate,
                'failed' => $stats->failed ?? 0,
            ];
        } catch (\Exception $e) {
            Log::error('Failed to get shipment reports home stats', [
                'error' => $e->getMessage()
            ]);

            return [
                'emails_sent' => 0,
                'success_rate' => 0,
                'failed' => 0,
            ];
        }
    }

    /**
     * Get filtered statistics for dashboard cards
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getStats(Request $request)
    {
        try {
            $days = $request->input('days', 0);
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');
            $office = $request->input('office');

            // Build base query
            $query = DB::connection('sqlsrv_auth')
                ->table('Email_EmailScheduler')
                ->whereNotNull('SubmittedOn');

            // Apply date filter
            if ($startDate && $endDate) {
                $query->whereBetween(DB::raw('CAST(SubmittedOn as DATE)'), [$startDate, $endDate]);
            } elseif (is_numeric($days)) {
                $query->where(DB::raw('CAST(SubmittedOn as DATE)'), '>=', Carbon::now()->subDays((int)$days)->toDateString());
            }

            // Apply office filter
            if ($office) {
                $query->where('OfficeName', $office);
            }

            // Get statistics
            $stats = $query->select([
                DB::raw("COUNT(*) as total_emails"),
                DB::raw("SUM(CASE WHEN Status = 'Success' THEN 1 ELSE 0 END) as emails_sent"),
                DB::raw("SUM(CASE WHEN SendRemark = 'No Email ID Present' THEN 1 ELSE 0 END) as no_email"),
                DB::raw("SUM(CASE WHEN SendRemark = 'Milestones Not Updated' THEN 1 ELSE 0 END) as milestones_not_updated"),
                DB::raw("SUM(CASE WHEN SendRemark = 'Milestones Not Configured' THEN 1 ELSE 0 END) as milestones_not_configured")
            ])->first();

            return response()->json([
                'total_emails' => $stats->total_emails ?? 0,
                'emails_sent' => $stats->emails_sent ?? 0,
                'no_email' => $stats->no_email ?? 0,
                'emails_failed' => $stats->milestones_not_updated ?? 0,
                'milestone_failures' => $stats->milestones_not_configured ?? 0,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get filtered shipment reports statistics', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'total_emails' => 0,
                'emails_sent' => 0,
                'no_email' => 0,
                'emails_failed' => 0,
                'milestone_failures' => 0,
            ], 500);
        }
    }
}
