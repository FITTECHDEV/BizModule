<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\ClientParty;
use App\Models\KRABillingHeader;
use App\Models\TRABillingHeader;
use App\Models\RRABillingHeader;
use App\Models\Ticket;
use App\Models\Office;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        try {
            $tickets = new Ticket();
            $ticketStatistics = $tickets->ticketStatistics();

            $entities = new ClientParty();
            $entityStatistics = $entities->entityStats();

            $tims_stats = new KRABillingHeader();
            $tims_stats = $tims_stats->timsStats();

            // Azure User Statistics with error handling
            try {
                $azureStats = Cache::remember('azure_user_stats', 300, function () {
                    return [
                        'active' => \App\Models\AzureUser::where('Status', 'active')->count(),
                        'pending' => \App\Models\AzureUser::where('Status', 'pending')->count(),
                        'inactive' => \App\Models\AzureUser::where('Status', 'inactive')->count(),
                        'total' => \App\Models\AzureUser::count(),
                    ];
                });
            } catch (\Exception $e) {
                \Log::error('Azure Stats Error: ' . $e->getMessage());
                $azureStats = ['active' => 0, 'pending' => 0, 'inactive' => 0, 'total' => 0];
            }

            $role = Auth::user()->Role;

            try {
                $efris_stats = DB::select('SET NOCOUNT ON; EXEC EFRIS_InvoiceStatus');
            } catch (\Exception $e) {
                \Log::error('EFRIS Stats Error: ' . $e->getMessage());
                $efris_stats = [];
            }

            try {
                $traStatsTZ = TRABillingHeader::getStatsByOfficeCode(['AR']);
                $traStatsADL = TRABillingHeader::getStatsByOfficeCode(['TZ']);
            } catch (\Exception $e) {
                \Log::error('TRA Stats Error: ' . $e->getMessage());
                $traStatsTZ = [];
                $traStatsADL = [];
            }

            try {
                $rra_stats = new RRABillingHeader();
                $rra_stats = $rra_stats->rraStats();
            } catch (\Exception $e) {
                \Log::error('RRA Stats Error: ' . $e->getMessage());
                $rra_stats = [];
            }

            // Get Login Tracker Statistics
            try {
                $loginTrackerStats = $this->getLoginTrackerStats();
            } catch (\Exception $e) {
                \Log::error('Login Tracker Stats Error: ' . $e->getMessage());
                $loginTrackerStats = [
                    'total_users' => 0,
                    'active_this_month' => 0,
                    'active_last_month' => 0,
                    'inactive_users' => 0,
                ];
            }

            // Get GPS Tracker Statistics
            try {
                $tracker_stats = $this->getTrackerStats();
            } catch (\Exception $e) {
                \Log::error('Tracker Stats Error: ' . $e->getMessage());
                $tracker_stats = (object)[
                    'created' => 0,
                    'transit' => 0,
                    'complete' => 0,
                    'total' => 0,
                ];
            }

            $active_this_month = $loginTrackerStats['active_this_month'];
            $active_last_month = $loginTrackerStats['active_last_month'];
            $inactive_users = $loginTrackerStats['inactive_users'];
            $total_users = $loginTrackerStats['total_users'];

            $shipment_reports_stats = ShipmentReportController::getHomeStats();

            // Add SCM Profit Integration stats
            $scm_profit_stats_obj = DB::table('CRM_SCMprofit_integration')
                ->whereNotNull('clientid')
                ->whereRaw("CAST(createdon as date) = CAST(GETDATE() as date)")->selectRaw("
                    COUNT(*) as total,
                        SUM(CASE WHEN LEN(ISNULL(SCMINTStatus,'')) <= 0 THEN 1 ELSE 0 END) as pending,
                        SUM(CASE WHEN CHARINDEX('success', LOWER(SCMINTStatus), 1) > 0 THEN 1 ELSE 0 END) as success,
                        SUM(CASE WHEN LEN(ISNULL(SCMINTStatus,'')) > 0 AND CHARINDEX('success', LOWER(SCMINTStatus), 1) = 0 THEN 1 ELSE 0 END) as failed
                ")
                ->first();

            $scm_profit_stats = [
                'total' => $scm_profit_stats_obj->total ?? 0,
                'pending' => $scm_profit_stats_obj->pending ?? 0,
                'success' => $scm_profit_stats_obj->success ?? 0,
                'failed' => $scm_profit_stats_obj->failed ?? 0,
            ];

            // Add RA Integration metrics
            try {
                $ra_metrics = \App\Http\Controllers\RAIntegrationController::getCardMetrics();
            } catch (\Exception $e) {
                \Log::error('RA Integration Stats Error: ' . $e->getMessage());
                $ra_metrics = [
                    'ra_pending_d365' => 0,
                    'ra_total_failed' => 0,
                    'ra_success_today' => 0,
                ];
            }


            // LOG BEFORE RETURNING VIEW
            \Log::info('About to return view with data', [
                'ticketStatistics' => $ticketStatistics,
                'efris_stats' => $efris_stats,
                'traStatsTZ' => $traStatsTZ,
                'azureStats' => $azureStats,
                'tracker_stats' => $tracker_stats,
            ]);

            return view(
                'dashboard.ticket',
                compact(
                    'ticketStatistics',
                    'entityStatistics',
                    'tims_stats',
                    'efris_stats',
                    'traStatsTZ',
                    'traStatsADL',
                    'rra_stats',
                    'active_this_month',
                    'active_last_month',
                    'inactive_users',
                    'total_users',
                    'tracker_stats',
                    'azureStats',
                    'shipment_reports_stats',
                    'scm_profit_stats',
                    'ra_metrics'
                )
            );
        } catch (\Exception $e) {
            \Log::error('Home Controller Error: ' . $e->getMessage());
            return 'Controller Error: ' . $e->getMessage();
        }
    }

    /**
     * Get GPS Tracker statistics
     * 
     * @return object
     */
    private function getTrackerStats()
    {
        try {
            $baseQuery = function () {
                return DB::table('Trip_TripInfomation')
                    ->where('IsActive', true)
                    ->where('IsDeleted', false)
                    ->where('OfficeCode', 'KE');
            };

            $stats = (object) [
                'created' => $baseQuery()->whereNull('StartDate')->count(),
                'transit' => $baseQuery()->whereNotNull('StartDate')->whereNull('EndDate')->count(),
                'complete' => $baseQuery()->whereNotNull('EndDate')->count(),
                'total' => $baseQuery()->count(),
            ];

            \Log::info('Tracker Stats Retrieved', [
                'created' => $stats->created,
                'transit' => $stats->transit,
                'complete' => $stats->complete,
                'total' => $stats->total,
            ]);

            return $stats;
        } catch (\Exception $e) {
            \Log::error('Failed to get tracker stats in HomeController', [
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
            ]);

            return (object)[
                'created' => 0,
                'transit' => 0,
                'complete' => 0,
                'total' => 0,
            ];
        }
    }

    /**
     * Get Login Tracker statistics from stored procedure
     * 
     * @return array
     */
    private function getLoginTrackerStats()
    {
        try {
            $pdo = DB::connection()->getPdo();
            $stmt = $pdo->prepare("SET NOCOUNT ON; EXEC dbo.sp_Azure_UserSummary;");
            $stmt->execute();

            $results = $stmt->fetchAll(\PDO::FETCH_OBJ);

            // Clear any additional result sets
            while ($stmt->nextRowset()) {
                // Clear buffer
            }

            \Log::info('Login Tracker Stats Retrieved', [
                'count' => count($results),
                'first_result' => !empty($results) ? $results[0] : null,
            ]);

            if (!empty($results) && is_object($results[0])) {
                $firstRow = $results[0];

                // Use actual column names from stored procedure
                return [
                    'total_users' => $firstRow->Totaluser ?? 0,
                    'active_this_month' => $firstRow->ActiveThisMonth ?? 0,
                    'active_last_month' => $firstRow->ActiveLastMonth ?? 0,
                    'inactive_users' => $firstRow->InActiveUser ?? 0,
                ];
            }

            \Log::warning('SP UserSummary returned no data');

            return [
                'total_users' => 0,
                'active_this_month' => 0,
                'active_last_month' => 0,
                'inactive_users' => 0,
            ];
        } catch (\Exception $e) {
            \Log::error('Failed to get login tracker stats in HomeController', [
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
            ]);

            // Return zero values on error
            return [
                'total_users' => 0,
                'active_this_month' => 0,
                'active_last_month' => 0,
                'inactive_users' => 0,
            ];
        }
    }

    public function archive()
    {
        //priority
        $priorities = Category::select('ID', 'CategoryCode', 'CategoryName')
            ->where('CategoryType', 'Priority')->orderBy('CategorySeq', 'desc')->get();

        return view('archive.index', compact('priorities'));
    }

    public function reportsFilter(Request $request)
    {
        $date_query = $this->dateFilter($request->date, $request->fromDate, $request->toDate, $request->month);
        // dd($date_query);
        //add office
        if (isset($request->office)) {
            $date_query = $date_query . " and TI.OfficeID = '" . $request->office . "'";
        }
        //add office
        if (isset($request->status)) {
            $date_query = $date_query . " and TI.Status = '" . $request->status . "'";
        }
        //add priority
        if (isset($request->priority)) {
            $date_query = $date_query . " and TI.PriorityID = '" . $request->priority . "'";
        }

        //add assigned to
        if (isset($request->assignedTo)) {
            $date_query = $date_query . " and TI.AssignedTo = '" . $request->assignedTo . "'";
        }

        //add assigned to
        if (isset($request->assignedBy)) {
            $date_query = $date_query . " and TI.AssignedBy = '" . $request->assignedBy . "'";
        }
        //add solution
        if (isset($request->solution)) {
            $date_query = $date_query . " and TI.SolutionID = '" . $request->solution . "'";
        }

        //add module
        if (isset($request->module)) {
            $date_query = $date_query . " and TI.ModuleID = '" . $request->module . "'";
        }
        //add module
        if (isset($request->sub_module)) {
            $date_query = $date_query . " and TI.SubModuleID = '" . $request->sub_module . "'";
        }
        //add module
        if (isset($request->tat_assign)) {
            if (($request->tat_assign) == 'tat_assign_range') {
                $date_query = $date_query . " and isnull(DATEDIFF(HOUR,TI.AssignedOn,TI.CreatedOn),0) between '" . $request->tat_assign_v1 . "' and '" . $request->tat_assign_v2 . "'";
            } else {
                $date_query = $date_query . " and isnull(DATEDIFF(HOUR,TI.AssignedOn,TI.CreatedOn),0) " . $request->tat_assign . " '" . $request->tat_assign_v2 . "' ";
            }
        }

        if (isset($request->tat_solve)) {
            if (($request->tat_solve) == 'tat_solve_range') {
                $date_query = $date_query . " and isnull(DATEDIFF(HOUR,TI.SolvedOn,TI.CreatedOn),0) between '" . $request->tat_solve_v1 . "' and '" . $request->tat_solve_v2 . "'";
            } else {
                $date_query = $date_query . " and isnull(DATEDIFF(HOUR,TI.SolvedOn,TI.CreatedOn),0) " . $request->tat_solve . " '" . $request->tat_solve_v2 . "' ";
            }
        }
        if (isset($request->tat_close)) {
            if (($request->tat_close) == 'tat_close_range') {
                $date_query = $date_query . " and isnull(DATEDIFF(HOUR,TI.ClosedOn,TI.CreatedOn),0) between '" . $request->tat_close_v1 . "' and '" . $request->tat_close_v2 . "'";
            } else {
                $date_query = $date_query . " and isnull(DATEDIFF(HOUR,TI.ClosedOn,TI.CreatedOn),0) " . $request->tat_close . " '" . $request->tat_close_v2 . "' ";
            }
        }

        // dd($date_query);

        $data = DB::select(
            "
                SET NOCOUNT ON;
                Declare @Filter as nvarchar(max)
                Exec sp_TicketReport
                @Filter = ?",
            ["$date_query"]
        );

        return response()->json($data);
    }

    public function dateFilter($val, $from = null, $to = null, $month = null)
    {
        $output = '';

        if ($val == "Today") {
            $output = "cast(TicketDate as Date) = '" . Carbon::now()->format('d/M/Y') . "'";
        } elseif ($val == "ThisMonth") {
            $output = "Datepart(Month,TI.CreatedOn) = '" . Carbon::now()->month . "'";
        } elseif ($val == "LastMonth") {
            $output = "Datepart(Month,TI.CreatedOn) = '" . Carbon::now()->subMonth()->month . "'";
        } elseif ($val == "SpecificMonth") {
            $output = "Datepart(Month,TI.CreatedOn) = '" . $month . "' ";
        } elseif ($val == "Range") {
            $from = ($from == '') ? '' : $from;
            $to = ($to == '') ? '' : $to;
            $output = "cast(TicketDate as Date) between '" . $from . "' and '" . $to . "'";
        }

        return $output;
    }

    public function getMasterData()
    {
        $tickets = Ticket::select(['TicketNo', 'Title', 'CreatedOn', 'CreatedBy']);

        return Datatables::of($tickets)
            ->editColumn('Title', function ($row) {
                return ucwords($row->Title);
            })
            ->editColumn('CreatedOn', function ($row) {
                return $row->CreatedOn->format('Y-m-d H:m:s');
            })->make();
    }
}
