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

        return view('efris.index',compact('efris_data'));
    }

    function total($data){
        $total = 0;
        foreach ($data as $key => $value) {
            $total += $value->NoOfInvoice;
        }
        return $total;
    }

    /**
     * Re-trigger invoice submission
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
     * Update invoice TIN/PIN
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function updateInvoice(Request $request)
    {
        // Log the incoming request
        Log::info('EFRIS TIN/PIN Update Request', [
            'user' => auth()->user()->name ?? 'guest',
            'data' => $request->all()
        ]);

        $validated = $request->validate([
            'InvoiceId' => "required|uuid",
            'ChangeOption' => "required",
            'LevelOption' => "required|in:Master,Invoice",
            'OptionValue' => "required|string|max:50",
        ]);

        // Build the input JSON for the stored procedure
        $inputJson = json_encode([
            'OperationType' => 'TINPINUpdate',
            'SubmittedDatetime' => now()->format('YmdHis'),
            'SubmittedBy' => auth()->user()->name ?? 'system',
            'Action' => 'Update',
            'InvoiceID' => $validated['InvoiceId'],
            'UpdateType' => $validated['LevelOption'],
            'Value' => $validated['OptionValue']
        ]);

        // Log the JSON being sent to SP
        Log::info('EFRIS SP Input JSON', ['json' => $inputJson]);

        try {
            // Execute the stored procedure
            $result = DB::select("SET NOCOUNT ON;
                                    DECLARE @OutputJson as varchar(1000)
                                    EXEC sp_RevenueAuth_TINPINUpdate
                                        @InputPutJson = ?,
                                        @OutputJson = @OutputJson OUTPUT
                                    SELECT @OutputJson as OutputJson
                                   ", [$inputJson]);

            // Log raw SP output
            Log::info('EFRIS SP Raw Output', ['result' => $result]);

            // Parse the output JSON
            $output = json_decode($result[0]->OutputJson, true);

            // Log parsed output
            Log::info('EFRIS SP Parsed Output', ['output' => $output]);

            // Format response for frontend
            $response = [
                [
                    'Status' => $output['Status'] ?? 'Failed',
                    'Status_Remark' => isset($output['Remarks'])
                        ? implode(', ', array_column($output['Remarks'], 'Remark'))
                        : 'Unknown error occurred'
                ]
            ];

            // Log final response
            Log::info('EFRIS Final Response', ['response' => $response]);

            return response()->json($response, 200);

        } catch (\Exception $e) {
            Log::error('EFRIS TIN/PIN Update Exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                [
                    'Status' => 'Failed',
                    'Status_Remark' => 'System error: ' . $e->getMessage()
                ]
            ], 500);
        }
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
