<?php

declare(strict_types=1);

namespace App\Charts;

use App\Models\Ticket;
use App\Models\User;
use Carbon\Carbon;
use Chartisan\PHP\Chartisan;
use ConsoleTVs\Charts\BaseChart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OverallTicketStatus extends BaseChart
{
    /**
     * Handles the HTTP request for the given chart.
     * It must always return an instance of Chartisan
     * and never a string or an array.
     */
    public function handler(Request $request): Chartisan
    {

        $today = date('Y-m-d');
        //ticket from countries today
        $tickets = Ticket::select('Status', DB::raw('count(Tic_TicketInfo.Id) AS counted'));

        $data = $request->all() == null ? null : $request->all();
        //split the input into three attributes
        if (!empty($data)) {
            $data_str = explode(',', $data['results']);
            //today
            if ($data_str[0] == "Today") {
                // get today's tickets
                $tickets =  $tickets->whereDate('TIC_TicketInfo.CreatedOn', $today);
            } elseif ($data_str[0] == "Yesterday") {
                // get yesterday's tickets
                $yesterday = Carbon::yesterday();
                $tickets =  $tickets->whereDate('TIC_TicketInfo.CreatedOn', $yesterday);
            } elseif ($data_str[0] == 'ThisMonth') {
                $this_month = Carbon::now()->month;
                $tickets =  $tickets->whereMonth('TIC_TicketInfo.CreatedOn', $this_month);
            } elseif ($data_str[0] == 'LastMonth') {
                $last_month = Carbon::now()->subMonth()->month;
                $tickets =  $tickets->whereMonth('TIC_TicketInfo.CreatedOn', $last_month);
            } elseif ($data_str[0] == 'PreviousMonth') {
                $previous_moth = Carbon::now()->subMonth()->month;
                $tickets =  $tickets->whereMonth('TIC_TicketInfo.CreatedOn', $previous_moth);
            } elseif ($data_str[0] == 'ThisQuarter') {
                $date = Carbon::now();
                $firstOfQuarter = $date->firstOfQuarter()->format('Y-m-d');
                $lastOfQuarter = $date->lastOfQuarter()->format('Y-m-d');
                $tickets =  $tickets->whereBetween('TIC_TicketInfo.CreatedOn',[$firstOfQuarter, $lastOfQuarter]);
            } elseif ($data_str[0] == 'Last3Months') {
                $firtsOfMonth = Carbon::now()->startOfMonth()->subMonth(3);
                $lastOfMonth = Carbon::now()->startOfMonth();
                $tickets =  $tickets->whereBetween('TIC_TicketInfo.CreatedOn',[$firtsOfMonth, $lastOfMonth]);
            } elseif ($data_str[0] == 'Last6Months') {
                $firtsOfMonth = Carbon::now()->startOfMonth()->subMonth(6);
                $lastOfMonth = Carbon::now()->startOfMonth();
                $tickets =  $tickets->whereBetween('TIC_TicketInfo.CreatedOn', [$firtsOfMonth->toDateString(), $lastOfMonth->toDateString()]);
            } elseif ($data_str[0] == 'Range') {
                $filter_range_fromDate = $request->fromDate;
                $filter_range_toDate = $request->toDate;
                $tickets =  $tickets->whereBetween('TIC_TicketInfo.CreatedOn',[$filter_range_fromDate, $filter_range_toDate]);
            }
             // solution
             if ($data_str[2] != "") {
                // call the filter function
                $tickets = $tickets->where('TIC_TicketInfo.SolutionID', $data_str[2]);
            }

            // Country
            if ($data_str[1] != "") {
                // call to filter function
                $tickets = $tickets->where('TIC_TicketInfo.SolutionID', $data_str[1]);
                // dd($data_str[1]);
            }
        }else{
            $tickets =  $tickets->whereDate('TIC_TicketInfo.CreatedOn', $today);

        }

        $tickets = $tickets->groupBy('Status')
                ->pluck('Status', 'counted')
                ->toArray();
        $labels = [];

        foreach (array_values($tickets) as $key => $ticket) {
            if ($ticket == 1) {
                array_push($labels, 'Open');
            } else if ($ticket == 2) {
                array_push($labels, 'Solved');
            } elseif ($ticket == 3) {
                array_push($labels, 'Closed');
            } elseif ($ticket == 4) {
                array_push($labels, 'Re-Opened');
            } elseif ($ticket == 5) {
                array_push($labels, 'Settled');
            } else {
                array_push($labels, 'Pending');
            }
        }

        return Chartisan::build()
            ->labels($labels)
            ->dataset('Total Tickets', array_keys($tickets));
    }
}
