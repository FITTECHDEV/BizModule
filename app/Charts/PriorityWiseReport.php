<?php

declare(strict_types = 1);

namespace App\Charts;

use Chartisan\PHP\Chartisan;
use ConsoleTVs\Charts\BaseChart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PriorityWiseReport extends BaseChart
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
        $todate = $request->all()  == null ? $today : $request->toDate;
        $priority = $request->all()  == null ? null : $request->priority;
        $officename = $request->all()  == null ? null : $request->officeName;

        $officewisepriority = DB::select("
            SET NOCOUNT ON;
            Declare @FromDate as varchar(60),
            @ToDate as varchar(60),
            @Priority as varchar(60),
            @OfficeName as varchar(60)
            Exec dbo.sp_OfficewisePriorityReport
            @FromDate = ?,
            @ToDate = ?,
            @Priority = ?,
            @OfficeName = ?",
            [$fromdate, $todate, $priority,$officename]);

            $ow = [];
            $office_names =[];
            $high = [];
            $low = [];
            $medium =[];
            $showstopper = [];
            foreach ($officewisepriority as $key => $value) {
                $ow[$key] = get_object_vars($value);
                $office_names[$key] = $ow[$key]['Name'];
                $high[$key] = $ow[$key]['High'];
                $low[$key]= $ow[$key]['Low'];
                $medium[$key]= $ow[$key]['Medium'];
                $showstopper[$key]= $ow[$key]['ShowStopper'];
            }

        return Chartisan::build()
            ->labels($office_names)
            ->dataset('High',  $high)
            ->dataset('Low',$low)
            ->dataset('Medium',$medium)
            ->dataset('ShowStopper',$showstopper);
    }
}
