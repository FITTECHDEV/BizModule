<?php

namespace App\Http\Controllers;

use App\Models\Efris;
use App\Models\KRABillingHeader;
use Illuminate\Http\Request;
use DB;
use Log;

class EfrisController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
         toast()->info('EFRIS Dashboard !!')->persistent(false, true);


        $invoice_creds  = DB::select('EXEC EFRIS_InvoiceRegister');

        $efris_data = DB::select('EXEC EFRIS_InvoiceStatus');

       $efris_data[count($efris_data)+1] = (object)[
        'EfrisStatus' => 'TOTAL',
        'NoOfInvoice'=>$this->total($efris_data)
        ];

        $efris_data = collect($efris_data);
        // $efris_stats = [];
        // foreach ($efris_data as $key => $value) {
        //     array_push($efris_stats, $value);
        // }

        // dd($efris_data);
        return view('efris.index',compact('efris_data'));
    }

    function total($data){
        $total = 0;
        foreach ($data as $key => $value) {
            // dd($value);
            $total += $value->NoOfInvoice;
        }
        return $total;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function reTrigger($docNo)
    {
        $feedback = DB::select("SET NOCOUNT ON;
                                    Declare
                                    @Status as Varchar(50),
                                    @Remark as Varchar(200)
                                Exec EFRIS_InvocieReTrigger
                                    @InvoiceNo = ?,
                                    @Status = @Status Output,
                                    @Remark= @Remark Output
                                    Select @Status as 'Status',
                                    @Remark as 'Status_Remark'
                                   ", [$docNo]);

        Log::notice(json_encode($feedback));

        return $feedback;
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
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
