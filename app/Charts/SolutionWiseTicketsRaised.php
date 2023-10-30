<?php

declare(strict_types = 1);

namespace App\Charts;

use App\Models\Category;
use App\Models\Ticket;
use Carbon\Carbon;
use Chartisan\PHP\Chartisan;
use ConsoleTVs\Charts\BaseChart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SolutionWiseTicketsRaised extends BaseChart
{
    /**
     * Handles the HTTP request for the given chart.
     * It must always return an instance of Chartisan
     * and never a string or an array.
     */
    public function handler(Request $request): Chartisan
    {

        $ticket = new Ticket();

        // $today = date('d/M/Y');
        // $fromdate = $request->all()  == null ? $today : $request->fromDate;
        // $todate = $request->all()  == null ? $today : $request->fromDate;
        // $status = $request->all()  == null ? null : $request->status;
        $officename = $request->all()  == null ? null : $request->officeName;
        $solutionname = $request->all()  == null ? null : $request->solutionname;

        $today = Carbon::now();
        $date = [$today, $today];

        $data = $request->all() == null ? null : $request->all();
        //split the input into three attributes
        if (!empty($data)) {
            $data_str = explode(',', $data['results']);
            if ($data_str[0] != 'Range') {
               $date =  $ticket->getDateFilter($data_str[0]);
            }else{
                $date =[$request->fromDate, $request->toDate];
            }
        }


        $solutionwise = DB::select("
            SET NOCOUNT ON;
            Declare @FromDate as varchar(60),
            @ToDate as varchar(60),
            @SolutionName as Varchar(60),
            @OfficeName as varchar(60)
            Exec dbo.sp_SolutionwiseReport
            @FromDate = ?,
            @ToDate = ?,
            @SolutionName = ?,
            @OfficeName = ?",
            [$date[0], $date[1], null, $solutionname, $officename]);

            $ow = [];
            $office_names =[];
            $pending = [];
            $open = [];
            $solved =[];
            $closed = [];
            $re_opened = [];
            $settled = [];
            //todo get dynamic categories $categories
            $categories = Category::where('CategoryType','Solution')->pluck('CategoryName');
            foreach ($solutionwise as $key => $value) {
                $ow[$key] = get_object_vars($value);
                $office_names[$key] = $ow[$key]['Name'];
                // $pending[$key] = $ow[$key]['Name'];
                $open[$key]= $ow[$key]['e-Ticketing'];
                $solved[$key]= $ow[$key]['Hardware / Software'];
                $closed[$key]= $ow[$key]['LMD - ET'];
                $re_opened[$key]= isset($ow[$key]['SCMProFit ']) ? $ow[$key]['SCMProFit '] : 0;
                $settled[$key]= $ow[$key]['UPS OPS'];
            }

        return Chartisan::build()
            ->labels($office_names)
            // ->dataset('Pending',  $pending)
            ->dataset('E-Ticket',$open)
            ->dataset('Hardware / Software',$solved)
            ->dataset('LMD - ET',$closed)
            ->dataset('SCMProFit', $re_opened)
            ->dataset('UPS OPS',$settled);
    }
}
