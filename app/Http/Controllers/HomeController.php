<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Support\Facades\Log;
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

        try {
            $azureStats = Cache::remember('azure_user_stats', 300, function () {
                return [
                    'active'   => \App\Models\AzureUser::where('Status', 'active')->count(),
                    'pending'  => \App\Models\AzureUser::where('Status', 'pending')->count(),
                    'inactive' => \App\Models\AzureUser::where('Status', 'inactive')->count(),
                    'total'    => \App\Models\AzureUser::count(),
                ];
            });
        } catch (\Exception $e) {
            Log::error('Azure Stats Error: ' . $e->getMessage());
            $azureStats = ['active' => 0, 'pending' => 0, 'inactive' => 0, 'total' => 0];
        }

        $role = Auth::user()->Role;

        try {
            $efris_stats = DB::select('SET NOCOUNT ON; EXEC EFRIS_InvoiceStatus');
        } catch (\Exception $e) {
            Log::error('EFRIS Stats Error: ' . $e->getMessage());
            $efris_stats = [];
        }

        try {
            $traStatsTZ  = TRABillingHeader::getStatsByOfficeCode(['AR']);
            $traStatsADL = TRABillingHeader::getStatsByOfficeCode(['TZ']);
        } catch (\Exception $e) {
            Log::error('TRA Stats Error: ' . $e->getMessage());
            $traStatsTZ  = [];
            $traStatsADL = [];
        }

        try {
            $rra_stats = new RRABillingHeader();
            $rra_stats = $rra_stats->rraStats();
        } catch (\Exception $e) {
            Log::error('RRA Stats Error: ' . $e->getMessage());
            $rra_stats = [];
        }

        try {
            $loginTrackerStats = $this->getLoginTrackerStats();
        } catch (\Exception $e) {
            Log::error('Login Tracker Stats Error: ' . $e->getMessage());
            $loginTrackerStats = [
                'total_users'       => 0,
                'active_this_month' => 0,
                'active_last_month' => 0,
                'inactive_users'    => 0,
            ];
        }

        try {
            $tracker_stats = $this->getTrackerStats();
        } catch (\Exception $e) {
            Log::error('Tracker Stats Error: ' . $e->getMessage());
            $tracker_stats = (object)[
                'created'  => 0,
                'transit'  => 0,
                'complete' => 0,
                'total'    => 0,
            ];
        }

        $active_this_month = $loginTrackerStats['active_this_month'];
        $active_last_month = $loginTrackerStats['active_last_month'];
        $inactive_users    = $loginTrackerStats['inactive_users'];
        $total_users       = $loginTrackerStats['total_users'];

        $shipment_reports_stats = ShipmentReportController::getHomeStats();

        $scm_profit_stats_obj = DB::table('CRM_SCMprofit_integration')
            ->whereNotNull('clientid')
            ->whereRaw("CAST(createdon as date) = CAST(GETDATE() as date)")
            ->selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN LEN(ISNULL(SCMINTStatus,'')) <= 0 THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN CHARINDEX('success', LOWER(SCMINTStatus), 1) > 0 THEN 1 ELSE 0 END) as success,
                SUM(CASE WHEN LEN(ISNULL(SCMINTStatus,'')) > 0 AND CHARINDEX('success', LOWER(SCMINTStatus), 1) = 0 THEN 1 ELSE 0 END) as failed
            ")
            ->first();

        $scm_profit_stats = [
            'total'   => $scm_profit_stats_obj->total   ?? 0,
            'pending' => $scm_profit_stats_obj->pending  ?? 0,
            'success' => $scm_profit_stats_obj->success  ?? 0,
            'failed'  => $scm_profit_stats_obj->failed   ?? 0,
        ];

        try {
            $ra_metrics = \App\Http\Controllers\RAIntegrationController::getCardMetrics();
        } catch (\Exception $e) {
            Log::error('RA Integration Stats Error: ' . $e->getMessage());
            $ra_metrics = [
                'ra_pending_d365'  => 0,
                'ra_total_failed'  => 0,
                'ra_success_today' => 0,
            ];
        }

        // ---------------------------------------------------------------
        // Office switcher — resolve active office from session
        // ---------------------------------------------------------------
        $activeOfficeCode = session('active_office_code');

        // Always call with empty office first to get the full office list (base + otherOffices).
        // The SP only returns OtherOffices when @OfficeCode is empty.
        $baseOfficeData = $this->getAzureOfficeData(null);

        $baseOffice   = $baseOfficeData['baseOffice'];
        $otherOffices = $baseOfficeData['otherOffices'];
        $hasAzure     = $baseOfficeData['hasAzure'];

        // If no office selected yet, default to base office
        if (!$activeOfficeCode && !empty($baseOffice)) {
            $activeOfficeCode = $baseOffice;
            session(['active_office_code' => $activeOfficeCode]);
        }

        // Second call with active office to get the correct module list for that office
        $activeOfficeData = $this->getAzureOfficeData($activeOfficeCode);
        $activeModules    = $activeOfficeData['modules'];

        // Office-to-card gate map — drives card visibility in the blade.
        // Cards not listed here are visible to all (or gated by can() only).
        // 'ALL' means show for every office including ungated users.
        $officeGate = [
            'tims'    => ['KE', 'UPS'],   // KRA — Kenya offices only
            'efris'   => ['UG'],           // EFRIS — Uganda only
            'tra_tz'  => ['AR'],           // TRA FIT Tanzania
            'tra_adl' => ['TZ'],           // TRA ADL Tanzania
            'rra'     => ['RW'],           // RRA — Rwanda only
        ];

        // Resolve which office we're comparing against.
        // If the user has no Azure record, fall back to base office.
        $effectiveOffice = $activeOfficeCode ?? $baseOffice;

        // canSeeAll is true ONLY when the SP returns ALL or null module
        // for the currently selected office — not a permanent bypass
        $canSeeAll = $hasAzure && (
            in_array('ALL', $activeModules) ||
            in_array(null, $activeModules)
        );

        Log::info('Office Switcher State', [
            'user'             => Auth::user()->Email,
            'activeOfficeCode' => $activeOfficeCode,
            'baseOffice'       => $baseOffice,
            'activeModules'    => $activeModules,
            'canSeeAll'        => $canSeeAll,
            'otherOffices'     => $otherOffices,
        ]);
        // ---------------------------------------------------------------

        // $effectiveOffice is now defined — safe to use below
        $canViewQuotationCard = \Illuminate\Support\Facades\Gate::allows('view-quotation');

        try {
            $quotation_stats = \App\Http\Controllers\QuotationController::getHomeStats($effectiveOffice);
        } catch (\Exception $e) {
            Log::error('Quotation Stats Error: ' . $e->getMessage());
            $quotation_stats = ['total' => 0, 'in_progress' => 0, 'failed_int' => 0, 'failed_progress' => 0];
        }

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
                'ra_metrics',
                'canViewQuotationCard',
                'quotation_stats',
                'activeModules',
                'otherOffices',
                'baseOffice',
                'hasAzure',
                'canSeeAll',
                'activeOfficeCode',
                'officeGate',
                'effectiveOffice'
            )
        );
    } catch (\Exception $e) {
        Log::error('Home Controller Error: ' . $e->getMessage());
        return 'Controller Error: ' . $e->getMessage();
    }
}
    // ---------------------------------------------------------------
    // Office data from SP
    // ---------------------------------------------------------------
    private function getAzureOfficeData(?string $officeCode = null): array
    {
        try {
            $identifier = Auth::user()->Email ?? Auth::user()->LoginID;

            // REVERT TO THIS
            $azureUser = DB::table('Azure_Users')
                ->where('userPrincipalName', $identifier)
                ->whereNotNull('AzureId')
                ->first();

                if (!$azureUser) {
                    Log::warning('Azure user not found for identifier', ['identifier' => $identifier]);
                    return [
                        'modules'      => [],
                        'otherOffices' => [],
                        'baseOffice'   => null,
                        'hasAzure'     => false,
                    ];
                }


            $azureId    = $azureUser->AzureId;
            $office     = $officeCode ?? '';
            $outputJson = null;

            $pdo  = DB::connection()->getPdo();
            $stmt = $pdo->prepare("
                DECLARE @OutputJson NVARCHAR(MAX);
                EXEC sp_Azure_BIZOfficeandModuleList
                    @AzureID     = ?,
                    @OfficeCode  = ?,
                    @OutputJson  = @OutputJson OUTPUT;
                SELECT @OutputJson AS OutputJson;
            ");
            $stmt->bindParam(1, $azureId, \PDO::PARAM_STR);
            $stmt->bindParam(2, $office,  \PDO::PARAM_STR);
            $stmt->execute();

            $outputJson = null;
            do {
                $row = $stmt->fetch(\PDO::FETCH_OBJ);
                if ($row && isset($row->OutputJson)) {
                    $outputJson = $row->OutputJson;
                    break;
                }
            } while ($stmt->nextRowset());

            Log::info('SP Azure Office Data Raw Output', [
                'azureId'    => $azureId,
                'office'     => $office,
                'outputJson' => $outputJson,
            ]);

            if (empty($outputJson)) {
                Log::warning('sp_Azure_BIZOfficeandModuleList returned empty output', [
                    'azureId' => $azureId,
                    'office'  => $office,
                ]);
                return [
                    'modules'      => [],
                    'otherOffices' => [],
                    'baseOffice'   => $azureUser->BaseOfficeCode,
                    'hasAzure'     => true,
                ];
            }

            $data = json_decode($outputJson, true);

            // SP returned a failure response
            if (isset($data['Status']) && $data['Status'] === 'Failed') {
                Log::warning('sp_Azure_BIZOfficeandModuleList returned failure', [
                    'remarks' => $data['Remarks'] ?? 'No remarks',
                    'azureId' => $azureId,
                    'office'  => $office,
                ]);
                return [
                    'modules'      => [],
                    'otherOffices' => [],
                    'baseOffice'   => $azureUser->BaseOfficeCode,
                    'hasAzure'     => true,
                ];
            }

            $modules      = collect($data['Module'] ?? [])->pluck('ModuleName')->toArray();
            $otherOffices = $data['OtherOffices'] ?? [];
            $baseOffice   = $data['Basedoffice'] ?? $azureUser->BaseOfficeCode;

            return [
                'modules'      => $modules,
                'otherOffices' => $otherOffices,
                'baseOffice'   => $baseOffice,
                'hasAzure'     => true,
            ];
        } catch (\Exception $e) {
            Log::error('getAzureOfficeData failed: ' . $e->getMessage(), [
                'line'  => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            return [
                'modules'      => [],
                'otherOffices' => [],
                'baseOffice'   => null,
                'hasAzure'     => false,
            ];
        }
    }

    // ---------------------------------------------------------------
    // Switch office — store in session and redirect
    // ---------------------------------------------------------------
    public function switchOffice(Request $request)
    {
        $request->validate(['office_code' => 'required|string|max:5']);

        $officeCode = strtoupper(trim($request->office_code));

        session(['active_office_code' => $officeCode]);

        Log::info('Office switched', [
            'user'        => Auth::user()->Email,
            'office_code' => $officeCode,
        ]);

        return redirect()->route('home');
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
