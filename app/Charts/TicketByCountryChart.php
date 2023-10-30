<?php

declare(strict_types=1);

namespace App\Charts;

use App\Models\Category;
use App\Models\Office;
use App\Models\Ticket;
use App\Models\User;
use Carbon\Carbon;
use Chartisan\PHP\Chartisan;
use ConsoleTVs\Charts\BaseChart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TicketByCountryChart extends BaseChart
{
    /**
     * Determines the chart name to be used on the
     * route. If null, the name will be a snake_case
     * version of the class name.
     */
    // public ?string $name = 'TicketByCountryChart';

    /**
     * Determines the name suffix of the chart route.
     * This will also be used to get the chart URL
     * from the blade directrive. If null, the chart
     * name will be used.
     */
    // public ?string $routeName = 'chart_route_name';

    /**
     * Determines the prefix that will be used by the chart
     * endpoint.
     */
    // public ?string $prefix = 'some_prefix';

    /**
     * Determines the middlewares that will be applied
     * to the chart endpoint.
     */
    public ?array $middlewares = ['auth'];

    /**
     * Handles the HTTP request for the given chart.
     * It must always return an instance of Chartisan
     * and never a string or an array.
     */
    public function handler(Request $request): Chartisan
    {
        $today = date('Y-m-d');
        //ticket from countries
        $tickets = User::join('Cust_Office', 'Cust_User.OfficeID', '=', 'Cust_Office.ID')
            ->join('TIC_TicketInfo', 'TIC_TicketInfo.UserID', '=', 'Cust_User.ID');

        $data = $request->all() == null ? null : $request->all();
        // $data_str = explode(',', $data['results']);

        // dd(!empty($data), $data,( $data_str[2] != ""));
        //split the input into three attributes
        if (!empty($data)) {
            $data_str = explode(',', $data['results']);
            //today
            if ($data_str[0] == "Today") {
                $tickets = $tickets->whereDate('TIC_TicketInfo.CreatedOn', $today);
            } elseif ($data_str[0] == "Yesterday") {
                // get yesterday's tickets
                $yesterday = Carbon::yesterday()->format('Y-m-d');
                $tickets = $tickets->whereDate('TIC_TicketInfo.CreatedOn', $yesterday);
            } elseif ($data_str[0] == 'ThisMonth') {
                $this_month = Carbon::now()->month;
                $tickets = $tickets->whereMonth('TIC_TicketInfo.CreatedOn', $this_month);
            } elseif ($data_str[0] == 'LastMonth') {
                $last_month = Carbon::now()->subMonth()->month;
                $tickets = $tickets->whereMonth('TIC_TicketInfo.CreatedOn', $last_month);
            } elseif ($data_str[0] == 'PreviousMonth') {
                $previous_moth = Carbon::now()->subMonth()->month;
                $tickets = $tickets->whereMonth('TIC_TicketInfo.CreatedOn', $previous_moth);
            } elseif ($data_str[0] == 'ThisQuarter') {
                $date = Carbon::now();
                $firstOfQuarter = $date->firstOfQuarter()->format('Y-m-d');
                $lastOfQuarter = $date->lastOfQuarter()->format('Y-m-d');
                $tickets = $tickets->whereBetween('TIC_TicketInfo.CreatedOn', [$firstOfQuarter, $lastOfQuarter]);
            } elseif ($data_str[0] == 'Last3Months') {
                $firtsOfMonth = Carbon::now()->startOfMonth()->subMonth(3);
                $lastOfMonth = Carbon::now()->startOfMonth();
                $tickets = $tickets->whereBetween('TIC_TicketInfo.CreatedOn', [$firtsOfMonth->toDateString(), $lastOfMonth->toDateString()]);
            } elseif ($data_str[0] == 'Last6Months') {
                $firtsOfMonth = Carbon::now()->startOfMonth()->subMonth(6);
                $lastOfMonth = Carbon::now()->startOfMonth();
                $tickets = $tickets->whereBetween('TIC_TicketInfo.CreatedOn', [$firtsOfMonth->toDateString(), $lastOfMonth->toDateString()]);
            } elseif ($data_str[0] == 'Range') {
                $filter_range_fromDate = $request->fromDate;
                $filter_range_toDate = $request->toDate;
                $tickets = $tickets->whereBetween('TIC_TicketInfo.CreatedOn', [$filter_range_fromDate, $filter_range_toDate]);
            }

            // solution
            if ($data_str[2] != "") {
                // call the filter function
                $tickets = $tickets->where('TIC_TicketInfo.SolutionID', $data_str[2]);
            }

            // Country
            if ($data_str[1] != "") {
                // call to filter function
                $tickets = $tickets->where('Cust_Office.ID', $data_str[1]);
                // dd($data_str[1]);
            }
        } else {
            $tickets = $tickets->whereDate('TIC_TicketInfo.CreatedOn', $today);

        }

        $tickets = $tickets->select('Cust_Office.Name AS name', DB::raw('count(Cust_Office.ID) AS counted'))
                    ->groupBy('name')
                    ->pluck('name','counted')
                    ->toArray();

        return Chartisan::build()
            ->labels(array_values($tickets))
            ->dataset('Total Tickets By Country', array_keys($tickets));
    }
}
