<?php

declare(strict_types = 1);

namespace App\Charts;

use Chartisan\PHP\Chartisan;
use ConsoleTVs\Charts\BaseChart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DayWiseTicketsRaised extends BaseChart
{
    /**
     * Handles the HTTP request for the given chart.
     * It must always return an instance of Chartisan
     * and never a string or an array.
     */
    public function handler(Request $request): Chartisan
    {

        $today = date('d/M/Y');
        $fromdate = $request->all()  == null ? $today : $request->fromDate;
        $todate = $request->all()  == null ? $today : $request->fromDate;
        $status = $request->all()  == null ? null : $request->status;


        $datewise = DB::select("
            SET NOCOUNT ON;
            Declare @FromDate as varchar(60),
            @ToDate as varchar(60),
            @Status as Int
            Exec dbo.[sp_DatewiseStatusReport]
            @FromDate = ?,
            @ToDate = ?,
            @Status = ?",
            [$fromdate, $todate, $status]);

            $ow = []; $t_dates =[];
            $pending = [];
            $open = []; $solved =[];
            $closed = []; $re_opened = []; $settled = [];
            
        foreach ($datewise as $key => $value) {
            $ow[$key] = get_object_vars($value);
            $t_dates[$key] = $ow[$key]['TDate'];
            $pending[$key] = $ow[$key]['Pending'];
            $open[$key]= $ow[$key]['Open'];
            $solved[$key]= $ow[$key]['Solved'];
            $closed[$key]= $ow[$key]['Closed'];
            $re_opened[$key]= $ow[$key]['Re-Opened'];
            $settled[$key]= $ow[$key]['Settled'];
        }

        return Chartisan::build()
            ->labels($t_dates)
            ->dataset('Pending',  $pending)
            ->dataset('Open',$open)
            ->dataset('Solved',$solved)
            ->dataset('Closed',$closed)
            ->dataset('RE-Opened', $re_opened)
            ->dataset('Settled',$settled);
    }
}
