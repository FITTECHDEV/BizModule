<?php

declare(strict_types = 1);

namespace App\Charts;

use App\Models\Ticket;
use Carbon\Carbon;
use Chartisan\PHP\Chartisan;
use ConsoleTVs\Charts\BaseChart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TicketStatusForEachCountry extends BaseChart
{
    /**
     * Handles the HTTP request for the given chart.
     * It must always return an instance of Chartisan
     * and never a string or an array.
     */
    public function handler(Request $request): Chartisan
    {

        $today = Carbon::now();
        $date = [$today->format('d/M/Y'), $today->format('d/M/Y')];

        $data = $request->all() == null ? null : $request->all();
        //split the input into three attributes
        if (!empty($data)) {
            $data_str = explode(',', $data['results']);
            if ($data_str[0] != 'Range') {
               $ticket = new Ticket();
               $date =  $ticket->getDateFilter($data_str[0]);
// dd($date);
            }else{
                $date =[$request->fromDate, $request->toDate];

            }
        }

        // dd($date[0]);

        $officewisestatus = DB::select("
            SET NOCOUNT ON;
            Declare @FromDate as varchar(60),
            @ToDate as varchar(60),
            @Status as Int,
            @OfficeName as varchar(60)
            Exec dbo.sp_OfficewiseStatusReport
            @FromDate = ?,
            @ToDate = ?,
            @Status = ?,
            @OfficeName = ?",
            [$date[0], $date[1], null, null]);

            $ow = []; $office_names =[];
            $pending = [];
            $open = []; $solved =[];
            $closed = []; $re_opened = []; $settled = [];
            // dd($date,$officewisestatus);
            foreach ($officewisestatus as $key => $value) {
                $ow[$key] = get_object_vars($value);
                $office_names[$key] = $ow[$key]['Name'];
                $pending[$key] = $ow[$key]['Pending'];
                $open[$key]= $ow[$key]['Open'];
                $solved[$key]= $ow[$key]['Solved'];
                $closed[$key]= $ow[$key]['Closed'];
                $re_opened[$key]= $ow[$key]['Re-Opened'];
                $settled[$key]= $ow[$key]['Settled'];
            }

        return Chartisan::build()
            ->labels($office_names)
            ->dataset('Pending',  $pending)
            ->dataset('Open',$open)
            ->dataset('Solved',$solved)
            ->dataset('Closed',$closed)
            ->dataset('RE-Opened', $re_opened)
            ->dataset('Settled',$settled);
    }
}
