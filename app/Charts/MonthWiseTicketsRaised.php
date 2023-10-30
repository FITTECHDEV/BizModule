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
use PHPUnit\TextUI\XmlConfiguration\Group;

class MonthWiseTicketsRaised extends BaseChart
{
    /**
     * Handles the HTTP request for the given chart.
     * It must always return an instance of Chartisan
     * and never a string or an array.
     */
    public function handler(Request $request): Chartisan
    {
        $year = '';

        if ($request->has('year')) {
            $year = $request->year;
        }else{
            $year = date('Y');
        }

        //ticket from countries

        $tickets = Ticket::whereYear('CreatedOn', $year)
            ->select(DB::raw('MONTH(CreatedOn) AS month'), DB::raw('count(Id) AS tickets'))
            ->groupBy(DB::raw('MONTH(CreatedOn)'))
            ->orderBy('month', 'asc')
            ->pluck('month', 'tickets')
            ->toArray();

        // beautify months for labels
        $labels = [];
        foreach ($tickets as $key => $ticket) {
            switch ($ticket) {
                case '1':
                    array_push($labels, 'Jan');
                    break;
                case '2':
                    array_push($labels, 'Feb');
                    break;
                case '3':
                    array_push($labels, 'March');
                    break;
                case '4':
                    array_push($labels, 'April');
                    break;
                case '5':
                    array_push($labels, 'May');
                    break;
                case '6':
                    array_push($labels, 'Jun');
                    break;
                case '7':
                    array_push($labels, 'Jul');
                    break;
                case '8':
                    array_push($labels, 'Aug');
                    break;
                case '9':
                    array_push($labels, 'Sept');
                    break;
                case '10':
                    array_push($labels, 'Oct');
                    break;
                case '11':
                    array_push($labels, 'Nov');
                    break;
                case '12':
                    array_push($labels, 'Dec');
                    break;
                default:

                    break;
            }
        }
        return Chartisan::build()
            ->labels(array_values($labels))
            ->dataset('Total Monthly Tickets', array_keys($tickets));
    }
}
