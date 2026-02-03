<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class ScmProfitIntegrationController extends Controller
{
    public function index()
    {
        $stats = $this->getStats();
        $offices = $this->getOffices();

        return view('scm-profit-integration.index', compact('stats', 'offices'));
    }

    public function getData(Request $request)
    {
        $query = DB::table('CRM_SCMprofit_integration as CI')
            ->selectRaw("
                CI.bookingno,
                CAST(CI.createdon as date) as report_date,
                CI.teamname as OfficeName,
                CI.companyname as ClientName,
                CI.clientid as ClientID,
                CI.username as SalesPerson,
                CI.origincountry as OriginCountry,
                CI.destinationcountry as DestinationCountry,
                ISNULL(CI.ClientIntegration,'') as ClientStatus,
                CASE 
                    WHEN LEN(ISNULL(CI.SCMINTStatus,'')) <= 0 THEN 'Pending'
                    WHEN CHARINDEX('success', LOWER(CI.SCMINTStatus), 1) > 0 THEN 'Success'
                    ELSE 'Failed' 
                END as SCMStatus,
                CASE 
                    WHEN LEN(ISNULL(CI.SCMINTStatus,'')) <= 0 THEN ''
                    WHEN CHARINDEX('success', LOWER(CI.SCMINTStatus), 1) > 0 THEN ''
                    ELSE CI.SCMINTStatus 
                END as StatusRemark,
                ISNULL(CI.SCMBookingNo,'') as BookingNo
            ")
            ->whereNotNull('CI.clientid');

        // Date filter
        if ($request->filled('days') && $request->days !== 'custom') {
            $days = (int) $request->days;
            $query->whereRaw("CAST(CI.createdon as date) >= DATEADD(day, -{$days}, CAST(GETDATE() as date))");
        }

        // Custom date range
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween(DB::raw('CAST(CI.createdon as date)'), [$request->start_date, $request->end_date]);
        }

        // Office filter
        if ($request->filled('office')) {
            $query->where('CI.teamname', $request->office);
        }

        // Card filter
        if ($request->filled('card_filter') && $request->card_filter !== 'all') {
            switch ($request->card_filter) {
                case 'pending':
                    $query->whereRaw("LEN(ISNULL(CI.SCMINTStatus,'')) <= 0");
                    break;
                case 'success':
                    $query->whereRaw("CHARINDEX('success', LOWER(CI.SCMINTStatus), 1) > 0");
                    break;
                case 'failed':
                    $query->whereRaw("LEN(ISNULL(CI.SCMINTStatus,'')) > 0 AND CHARINDEX('success', LOWER(CI.SCMINTStatus), 1) = 0");
                    break;
            }
        }

        return DataTables::of($query)
            ->orderColumn('report_date', 'CI.createdon $1')
            ->make(true);
    }

    public function getStatsData(Request $request)
    {
        return response()->json($this->getStats($request));
    }

    private function getStats(Request $request = null)
    {
        $query = DB::table('CRM_SCMprofit_integration as CI')
            ->whereNotNull('CI.clientid');

        // Apply filters if request provided
        if ($request) {
            if ($request->filled('days') && $request->days !== 'custom') {
                $days = (int) $request->days;
                $query->whereRaw("CAST(CI.createdon as date) >= DATEADD(day, -{$days}, CAST(GETDATE() as date))");
            }

            if ($request->filled('start_date') && $request->filled('end_date')) {
                $query->whereBetween(DB::raw('CAST(CI.createdon as date)'), [$request->start_date, $request->end_date]);
            }

            if ($request->filled('office')) {
                $query->where('CI.teamname', $request->office);
            }
        } else {
            // Default: yesterday's data
            $query->whereRaw("CAST(CI.createdon as date) = CAST(DATEADD(day, -1, GETDATE()) as date)");
        }

        $stats = $query->selectRaw("
            COUNT(*) as total,
            SUM(CASE WHEN LEN(ISNULL(CI.SCMINTStatus,'')) <= 0 THEN 1 ELSE 0 END) as pending,
            SUM(CASE WHEN CHARINDEX('success', LOWER(CI.SCMINTStatus), 1) > 0 THEN 1 ELSE 0 END) as success,
            SUM(CASE WHEN LEN(ISNULL(CI.SCMINTStatus,'')) > 0 AND CHARINDEX('success', LOWER(CI.SCMINTStatus), 1) = 0 THEN 1 ELSE 0 END) as failed
        ")->first();

        return [
            'total' => $stats->total ?? 0,
            'pending' => $stats->pending ?? 0,
            'success' => $stats->success ?? 0,
            'failed' => $stats->failed ?? 0,
        ];
    }

    private function getOffices()
    {
        return DB::table('CRM_SCMprofit_integration')
            ->whereNotNull('clientid')
            ->whereNotNull('teamname')
            ->distinct()
            ->pluck('teamname')
            ->sort()
            ->values();
    }
}