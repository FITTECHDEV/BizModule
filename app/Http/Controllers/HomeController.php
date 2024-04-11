<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\ClientParty;
use App\Models\KRABillingHeader;
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
        $tickets = new Ticket();
        $ticketStatistics = $tickets->ticketStatistics();

        $entities = new ClientParty();
        $entityStatistics = $entities->entityStats();

        $tims_stats = new KRABillingHeader();
        $tims_stats = $tims_stats->timsStats();

        $role = Auth::user()->Role;

        $efris_stats = DB::select('SET NOCOUNT ON; EXEC EFRIS_InvoiceStatus');

        return view(
            'dashboard.ticket',
            compact('ticketStatistics', 'entityStatistics','tims_stats','efris_stats')
        );
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
