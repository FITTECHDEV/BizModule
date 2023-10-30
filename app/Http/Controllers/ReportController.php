<?php

namespace App\Http\Controllers;

use App\Models\Office;
use App\Models\Report;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PdfReport;
use Carbon\Carbon;
use Illuminate\Http\Client\ResponseSequence;
use Chartisan\PHP\Chartisan;

class ReportController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        return view('reports.index');
    }


    public function displayPdfReport(Request $request)
    {
        $criteria = $request->input('criteria');
        $office = $request->input('office') == '*' ? 'a' : 'n';
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');
        // $sortBy = $request->input('sort_by');

        $title = 'Ticket Report'; // Report title

        $queryBuilder = DB::table($criteria)
            ->select('TicketNo', 'CreatedBy', 'OfficeID', 'CreatedOn', 'UserID')
            ->whereBetween('CreatedOn', [$fromDate, $toDate])
            ->orderBy('CreatedOn', 'DESC');
        if ($office == 'n') {
            $id = Office::where('ID', $request->input('office'))->first();
            $queryBuilder = $queryBuilder->where('OfficeID', $id->ID);
        }
        $meta = [ // For displaying filters description on header
            'From ' => $fromDate . ' To ' . $toDate,
            'Ticket Count' => $queryBuilder->count(),
            'Office' => $request->input('office') == '*' ? 'All' : Office::where('ID', $request->input('office'))->first()['Name']
        ];
        $columns = [ // Set Column to be displayed
            'Ticket No' => 'TicketNo',
            'Created By' => 'CreatedBy',
            'Office' => 'OfficeID',
            'Created On' => 'CreatedOn'
        ];

        // Generate Report with flexibility to manipulate column class even manipulate column value (using Carbon, etc).
        return PdfReport::of($title, $meta, $queryBuilder, $columns)
            // ->setOrientation('landscape')
            ->editColumn('Created On', [ // Change column class or manipulate its data for displaying to report
                'displayAs' => function ($result) {
                    return (date('d-m-Y H:i', strtotime($result->CreatedOn)));
                },
                'class' => 'left'
            ])
            ->editColumn('Office', [ // Change column class or manipulate its data for displaying to report
                'displayAs' => function ($result) {
                    // return dd($result);
                    $offid = $result->OfficeID == null ? User::where('ID', $result->UserID)->first()['OfficeID'] : $result->OfficeID;
                    return Office::where('ID', $offid)->first()['Name'];
                }
            ])
            ->editColumns(['Ticket Count'], [ // Mass edit column
                'class' => 'right bold'
            ])
            ->limit(20) // Limit record to be showed
            ->stream(); // other available method: store('path/to/file.pdf') to save to disk, download('filename') to download pdf / make() that will producing DomPDF / SnappyPdf instance so you could do any other DomPDF / snappyPdf method such as stream() or download()
    }

    /**
     *
     * filter reports
     */
    public function filterReport(Request $request)
    {
        $today = Carbon::now()->format('Y-m-d');

        // var
        $filter_date = $request->filter_date;
        $filter_solution = $request->filter_solution;
        $filter_country = $request->filter_country;


        // $query = new Ticket;
        $filter_query = null;
        $data = null;


        // $yesterday = Carbon::yesterday()->format('Y-m-d');

        //fetch the country for the tickets
        $query = User::join('Cust_Office', 'Cust_User.OfficeID', '=', 'Cust_Office.ID')
                ->join('TIC_TicketInfo', 'TIC_TicketInfo.UserID', '=', 'Cust_User.ID')
                ->select('TIC_TicketInfo.*','TIC_TicketInfo.CreatedOn', 'Cust_Office.ID AS OID','Cust_Office.Name as Name');
        // $query = new Ticket();

        //         ->whereDate('TIC_TicketInfo.CreatedOn', $yesterday)
        //         ->get();
        // dd($query);
        $firtsOfMonth= '';
        $lastOfMonth = '';
        // date
        if ($filter_date == 'Today') {
            //get todays tickets
            $today = Carbon::now()->format('Y-m-d');
            // dd($query->get());
            $filter_query = $query->whereDate('TIC_TicketInfo.CreatedOn', $today)
                            ->get();
        } elseif ($filter_date == 'Yesterday') {
            //get yesterday's tikcets
            $yesterday = Carbon::yesterday()->format('Y-m-d');
            $filter_query = $query->whereDate('TIC_TicketInfo.CreatedOn', $yesterday)->get();
            // dd($filter_query);
        }elseif ($filter_date == 'ThisMonth') {
            $this_month = Carbon::now()->month;
            $filter_query = $query->whereMonth('TIC_TicketInfo.CreatedOn', $this_month)->get();
        }elseif ($filter_date == 'LastMonth') {
            $last_month = Carbon::now()->subMonth()->month;
            $filter_query = $query->whereMonth('TIC_TicketInfo.CreatedOn', $last_month)->get();

        }elseif ($filter_date == 'PreviousMonth') {
            $previous_month = Carbon::now()->subMonth()->month;
            $filter_query = $query->whereMonth('TIC_TicketInfo.CreatedOn', $previous_month)->get();

        }elseif ($filter_date == 'ThisQuarter') {
            $date = Carbon::now();
            $firstOfQuarter = $date->firstOfQuarter()->format('Y-m-d');
            $lastOfQuarter = $date->lastOfQuarter()->format('Y-m-d');
            $filter_query = $query->whereBetween('TIC_TicketInfo.CreatedOn', [$firstOfQuarter,$lastOfQuarter])
                                    ->get();
        }elseif ($filter_date == 'Last3Months') {
            $firtsOfMonth = Carbon::now()->startOfMonth()->subMonth(3);
            $lastOfMonth = Carbon::now()->startOfMonth();
            $filter_query = $query->whereBetween('TIC_TicketInfo.CreatedOn', [$firtsOfMonth->toDateString(),$lastOfMonth->toDateString()])
                                    ->get();
        }elseif ($filter_date == 'Last6Months') {
            $firtsOfMonth = Carbon::now()->startOfMonth()->subMonth(6);
            $lastOfMonth = Carbon::now()->startOfMonth();
            $filter_query = $query->whereBetween('TIC_TicketInfo.CreatedOn', [$firtsOfMonth->toDateString(),$lastOfMonth->toDateString()])
                                    ->get();
        }elseif($filter_date == 'Range'){
            $filter_range_fromDate = $request->filter_range[0];
            $filter_range_toDate = $request->filter_range[1];
            // dd($filter_range_fromDate, $filter_range_toDate);
            $filter_query = $query->whereBetween('TIC_TicketInfo.CreatedOn', [$filter_range_fromDate, $filter_range_toDate])
                            ->get();
        }

        // dd($filter_query);

        // solution
        if ($request->filter_country != null) {
            // call the filter function
            $filter_query = $filter_query->where('OID', $filter_country);
            // dd($request->filter_country);
        }

        // Country
        if ($request->filter_solution != null) {
            // call to filter function
            $filter_query = $filter_query->where('SolutionID', $filter_solution);
        }

        $data = $this->filterFunc($filter_query);
        // dd($data);

        return response()->json([
            'data' => $data
        ]);
    }

    public function filterFunc($filter_query)
    {
        $data = [
            'open' => '',
            'solved' => '',
            'closed' => '',
        ];

        $data['open'] = $filter_query->where('Status', 1)->count();
        $data['solved'] = $filter_query->where('Status', 2)->count();
        $data['closed'] = $filter_query->where('Status', 3)->count();
        $data['re_opened'] = $filter_query->where('Status', 4)->count();
        $data['settled'] = $filter_query->where('Status', 5)->count();
        $data['pending'] = $filter_query->where('Status', 0)->count();
        $data['total'] = $filter_query->count();

        return $data;
    }



    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Report  $report
     * @return \Illuminate\Http\Response
     */
    public function show(Report $report)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Report  $report
     * @return \Illuminate\Http\Response
     */
    public function edit(Report $report)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Report  $report
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Report $report)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Report  $report
     * @return \Illuminate\Http\Response
     */
    public function destroy(Report $report)
    {
        //
    }
}
