<?php

namespace App\Http\Controllers;

use App\Models\TRABillingHeader;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use PDF;
use RealRashid\SweetAlert\Facades\Alert;

class TraADLController extends Controller
{
    private $header;

    public function __construct()
    {
        $this->middleware(['auth']);
        $this->header = new TRABillingHeader();

    }

    public function index()
    {
        toast()->info('TRA ADL Dashboard !!')->persistent(false, true);

        $traADLBillingHeader = $this->header;

        // Filter the data for the TZ office
        $traADLBillingHeader = $this->header->filterByOfficeCode(['TZ'])->get();
        // dd($traTZBillingHeader);
        // Count the status for the TZ office
        $traStatsADL = TRABillingHeader::getStatsByOfficeCode(['TZ']);

        return view('tra.ADL.index', compact('traADLBillingHeader', 'traStatsADL'));

    }

    public function getStats()
    {
        $data = [];

        array_push($data, [
            'Failed' => $this->header->invCredCount('DocStatus', 'Failed', null, 'TZ'),
            'Pending' => $this->header->invCredCount('DocStatus', 'Pending', null, 'TZ'),
            'Submitted' => $this->header->invCredCount('DocStatus', 'Submitted', null, 'TZ'),
            'Total' => $this->header->invCredCount('DocStatus', '*', null, 'TZ')
        ]);

        return response()->json($data, 200);
    }


    public function reTrigger($docNo)
    {
        $feedback = DB::select("SET NOCOUNT ON;
                                    Declare
                                    @Status as Varchar(50),
                                    @Remark as Varchar(200)
                                Exec TRA_RetriggerInvoice
                                    @InvoiceNo = ?,
                                    @Status = @Status Output,
                                    @Remark= @Remark Output
                                    Select @Status as 'Status',
                                    @Remark as 'Status_Remark'
                                   ", [$docNo]);

        Log::notice(json_encode($feedback));

        return $feedback;
    }

    public function jsonGenerate($id)
    {
        $json_string = TRABillingHeader::where('ID', $id)->select('DocNo', 'TRAJsonFile')->first();
        Storage::put("invoice_data/" . $json_string->DocNo . "-Invoice-data.json", $json_string->TRAJsonFile);
        return response()->download(storage_path("app/invoice_data/" . $json_string->DocNo . "-Invoice-data.json"));
    }


    public function invoiceDownload($invoiceHeaderID)
    {
        // $invoiceHeaderID = 'DB3FFF3C-05B9-40D3-AB5D-AF6A0099D785';
        $header = $this->invoiceHeader($invoiceHeaderID);
        // dd($header);
        $details = $this->invoiceDetails($invoiceHeaderID);
        // dd($details);
        if ($header == null) {
            Alert::info('Invoice Header', 'No Invoice Header Data !!');
            return redirect()->back();
        }

        if ($details == null) {
            Alert::info('Invoice Details', 'No Invoice Details Data !!');
            return redirect()->back();
        }

        $header = $header[0];

        PDF::SetTitle('Export Invoice');
        PDF::SetAuthor('Freight In Time Group');
        PDF::SetSubject('Export Invoice');
        PDF::SetKeywords('PDF, Invoice');
        PDF::SetFont('times', '', 8);


        // define 1Dbarcode style
        $style = array(
            'position' => 'R',
            'align' => 'C',
            'stretch' => false,
            'fitwidth' => true,
            'cellfitalign' => '',
            'border' => false,
            'hpadding' => 'auto',
            'vpadding' => 'auto',
            'fgcolor' => array(0, 0, 0),
            'bgcolor' => false, //array(255,255,255),
            'text' => true,
            'font' => 'times',
            'fontsize' => 8,
            'stretchtext' => 4
        );
        // define 2Dbarcode style
        $qr_code_Style = array(
            'border' => false,
            'vpadding' => 'auto',
            'hpadding' => 'auto',
            'fgcolor' => array(0, 0, 0),
            'bgcolor' => false, //array(255,255,255)
            'module_width' => 1, // width of a single module in points
            'module_height' => 1 // height of a single module in points
        );
        $line = array('width' => 0.3, 'cap' => 'butt', 'join' => 'miter', 'dash' => '', 'phase' => 5, 'color' => array(0, 0, 0));

        //header
        PDF::setHeaderCallback(function ($pdf) use ($style, $line, $qr_code_Style, $header) {
            // Logo
            // $image_file = K_PATH_IMAGES . 'logo_example.jpg';
            $image_file = public_path('img/logo.png');
            $pdf->Image($image_file, 9, 10, 40, '', 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);
            // Set font
            $pdf->SetFont('times', 'N', 12);
            $txt = <<<EOD
                    Africa Direct line LIMITED
                    Dar es Salaam, Tanzania
                    Main: +254-720-651313
                    Email: ups.nbo@freight-in-time.com
                    EOD;
            $pdf->MultiCell(70, 20, $txt, 0, 'L', 0, 1, 55, '', true);

            $pdf->write1DBarcode($header->DocNo, 'C128', '', 10, '', 19, 0.3, $style, 'N');
            $pdf->ln(8);
            $pdf->SetFont('times', 'B', 16);
            $pdf->Cell(0, 0, 'Export Invoice', 0, 0, 'C', 0, '', 0, false, 'T', 'M');
            $pdf->SetFont('times', '', 9);
            $txt = <<<EOD
                TO
                $header->PartyName
                $header->Address1
                $header->Address2
                $header->AddressCity
                $header->CountryName
                $header->CustomerPINNo
                EOD;

            $pdf->MultiCell(0, 10, $txt, 0, 'L', 0, 1, 10, 42, true);
            $pdf->setCellHeightRatio(1.5);
            $ship_data = "<table cellspacing='0' cellpadding='1'>
                            <tr>
                                <th><b>Invoice No </b></th>
                                <td>: " . $header->DocNo . "</td>
                            </tr>
                            <tr>
                                <th><b>Invoice Date </b></th>
                                <td>: " . Carbon::parse($header->DocDate)->format('d-m-Y') . "</td>
                            </tr>
                            <tr>
                                <th><b>Invoice Due Date </b></th>
                                <td>: " . Carbon::parse($header->DocDueDate)->format('d-m-Y') . "</td>
                            </tr>
                            <tr>
                                <th><b>UPS Account No </b></th>
                                <td>: " . $header->UPSAccount . "</td>
                            </tr>
                            <tr>
                                <th><b>FIT Account No </b></th>
                                <td>: " . $header->Code . "</td>
                            </tr>
                        </table>";
            $pdf->writeHTMLCell(90, 0, 90, 44, $ship_data, 0, 1, 0, true, 'J', true);
            $pdf->SetFont('times', 'B', 8);
            $pdf->ln(1);
            $pdf->Cell(0, 0, 'Total No of Shipment :43', 0, false, 'L', 0, '', 0, false, 'T', 'M'); // no of shipments
            $pdf->SetFont('times', '', 8);
            $pdf->Line(200, 74, 10, 74, $line);
            $pdf->ln(7);
            $pdf->SetFont('times', 'B', 8);
            $pdf->Cell(0, 0, '#', 0, false, 'L', 0, '', 0, false, 'T', 'M');
            $title = "Shipment No & Date <br>Consignee Name <br>Reference Number";
            $pdf->setCellHeightRatio(1.5);
            $pdf->writeHTMLCell(28, '', 16, 75, $title, 0, 0, 0, true, 'J', true);
            $pdf->writeHTMLCell(11, '', 45, 75, "Product", 0, 0, 0, true, 'J', true);
            $title = "Pkgs <br>Consignee Name <br>Job No";
            $pdf->writeHTMLCell(22, '', 57, 75, $title, 0, 0, 0, true, 'J', true);
            $pdf->writeHTMLCell(6, '', 75, 75, "Wt.", 0, 0, 0, true, 'J', true);
            $pdf->writeHTMLCell(7, '', 85, 75, "Zone", 0, 0, 0, true, 'J', true);
            $pdf->writeHTMLCell(10, '', 108, 75, "Tariff", 0, 0, 0, true, 'J', true);
            $pdf->writeHTMLCell(12, '', 95, 75, "Discount", 0, 0, 0, true, 'J', true);
            $title = "Freight <br>Destination <br>Customer Cost Center";
            $pdf->writeHTMLCell(28, '', 119, 75, $title, 0, 0, 0, true, 'J', true);
            $pdf->writeHTMLCell(5, '', 140, 75, "Ins", 0, 0, 0, true, 'J', true);
            $title = "Surcharge <br>Origin";
            $pdf->writeHTMLCell(15, '', 154, 75, $title, 0, 0, 0, true, 'J', true);
            $pdf->writeHTMLCell(15, '', 170, 75, "VAT", 0, 0, 0, true, 'J', true);
            $pdf->writeHTMLCell(15, '', 185, 75, "Total", 0, 0, 0, true, 'R', true);
            $pdf->Line(200, 88, 10, 88, $line);
        });

        PDF::SetFont('times', '', 6.5);
        PDF::AddPage();

        $txt_pos = 88;
        $txt_pos_line = 97;
        $per_page = 15;
        $loop_count = count($details);

        for ($i = 0; $i <= ($loop_count - 1); $i++) {
            $dt = Carbon::parse($details[$i]->ProcessNumberGeneratedDate)->format('d/m/Y');
            $bookingNo = $details[$i]->BookingNumber;
            $consignee = $details[$i]->Consignee;
            $consigneeRef = $details[$i]->ConsigneeRef;
            $shipperRef = $details[$i]->ShipperRef;
            $title = $i + 1;
            PDF::writeHTMLCell(0, '', 10, $txt_pos, $title, 0, 0, 0, true, 'J', true); // numbers 1,2,3

            $data = "$bookingNo DT $dt<br>" . ucwords($consignee) . "<br>$shipperRef";
            PDF::writeHTMLCell(0, '', 16, $txt_pos, $data, 0, 0, 0, true, 'J', true); //Shipment No & Date, Consignee Name, Reference Number
            PDF::writeHTMLCell(11, '', 48, $txt_pos, $details[$i]->ShipmentType, 0, 0, 0, true, 'J', true); //L, N, D

            $title = $details[$i]->Packages . " <br>" . $details[$i]->ClearingAgent . "<br>" . $bookingNo;
            PDF::writeHTMLCell(0, '', 57, $txt_pos, $title, 0, 0, 0, true, 'J', true); // pkgs, Consignorname, jobno

            PDF::writeHTMLCell(0, '', 75, $txt_pos, number_format($details[$i]->WeightinKG, 2), 0, 0, 0, true, 'J', true); // wt
            PDF::writeHTMLCell(0, '', 85, $txt_pos, $details[$i]->ZoneName, 0, 0, 0, true, 'J', true); // zone
            PDF::writeHTMLCell(0, '', 108, $txt_pos, number_format($details[$i]->Tariff, 2), 0, 0, 0, true, 'J', true); // tarrif
            PDF::writeHTMLCell(0, '', 95, $txt_pos, number_format($details[$i]->Discount, 2), 0, 0, 0, true, 'J', true); // discount

            $title = number_format($details[$i]->Freight, 2) . "<br>" . $details[$i]->CountryOfDestination . "<br>" . $consigneeRef;
            PDF::writeHTMLCell(0, '', 119, $txt_pos, $title, 0, 0, 0, true, 'J', true); // freight, destination, customer cost center

            PDF::writeHTMLCell(0, '', 140, $txt_pos, "", 0, 0, 0, true, 'J', true); //ins

            $title = number_format($details[$i]->FSCAmt, 2) . "<br>" . $details[$i]->CountryOfOrigin;
            PDF::writeHTMLCell(0, '', 154, $txt_pos, $title, 0, 0, 0, true, 'J', true); // surcharge

            PDF::writeHTMLCell(0, '', 170, $txt_pos, number_format($details[$i]->TAX, 2), 0, 0, 0, true, 'J', true); // vat
            PDF::writeHTMLCell(0, '', 185, $txt_pos, number_format($details[$i]->TotalinBillAmt, 2), 0, 0, 0, true, 'R', true); // total
            PDF::Line(200, $txt_pos_line, 10, $txt_pos_line, $line);

            $txt_pos += 10;
            $txt_pos_line += 10;

            if ($i == $per_page) {
                PDF::AddPage();
                PDF::setFooterCallback(function ($pdf) use ($txt_pos) {
                    $pdf->SetY(-15);
                    // Set font
                    $pdf->SetFont('times', 'I', 8);
                    // Page number
                    $pdf->Cell(0, 10, 'Page ' . $pdf->getAliasNumPage() . ' of ' . $pdf->getAliasNbPages(), 0, false, 'R', 0, '', 0, false, 'T', 'M');
                });
                $txt_pos = 88;
                $txt_pos_line = 97;
                $per_page += 19;
            }
        }


        PDF::SetFont('times', 'B', 9);

        PDF::writeHTMLCell(0, 0, 10, $txt_pos, "Amount in word", 0, 0, 0, true, 'J', true);

        PDF::writeHTMLCell(0, 12, 140, $txt_pos, "<b>Total Amount</b>", 0, 0, 0, true, 'J', true);
        $tot_val = number_format(($header->TotChg + $header->TotDiscount), 2);
        PDF::writeHTMLCell(0, 12, 180, $txt_pos, $tot_val, 0, 0, 0, true, 'R', true);

        PDF::writeHTMLCell(0, 12, 140, $txt_pos + 5, "<b>Discount</b>", 0, 0, 0, true, 'J', true);
        PDF::writeHTMLCell(0, 12, 180, $txt_pos + 5, number_format($header->TotDiscount, 2), 0, 0, 0, true, 'R', true);

        PDF::Line(140, $txt_pos + 10, 200, $txt_pos + 10, $line);
        PDF::writeHTMLCell(0, 12, 140, $txt_pos + 10, "<b>Sub Total</b>", 0, 0, 0, true, 'J', true);
        PDF::writeHTMLCell(0, 12, 180, $txt_pos + 10, number_format($header->TotChg, 2), 0, 0, 0, true, 'R', true);

        PDF::writeHTMLCell(0, 12, 140, $txt_pos + 15, "<b>VAT Total </b>", 0, 0, 0, true, 'J', true);
        PDF::writeHTMLCell(0, 12, 180, $txt_pos + 15, number_format($header->TotTax, 2), 0, 0, 0, true, 'R', true);

        PDF::Line(140, $txt_pos + 20, 200, $txt_pos + 20, $line);
        PDF::writeHTMLCell(0, 12, 140, $txt_pos + 20, "<b>Amount Payable in " . $header->Currency . "</b>", 0, 0, 0, true, 'J', true);
        PDF::writeHTMLCell(0, 12, 180, $txt_pos + 20, number_format($header->TotBill, 2), 0, 0, 0, true, 'R', true);
        PDF::Line(140, $txt_pos + 25, 200, $txt_pos + 25, $line);

        PDF::ln(28);

        // 2d barcode
        if ($header->KRAInvoice != null) {
            PDF::write2DBarcode($header->KRAQRCode, 'QRCODE,H', 10, 220, 20, 20, $qr_code_Style, 'N');
            $codes = <<<EOD
                    Invoice No. $header->KRAInvoice
                    TRA Serial No. $header->KRASerialNo
                    EOD;
            PDF::MultiCell(0, 10, $codes, 0, 'L', 0, 1, 28, 230, true);
        }

        PDF::SetFont('times', '', 8);
        $txt = 'Terms & Conditions<br>1. PIN NO: P051124154Q<br>2. Payments are due on demand<br>3. All Cheque payable to: FREIGHT IN TIME LTD - UPS<br>4. Without prejudice all overdue accounts will attract an interest of 2% P.M. from the date of default until the account is fully settled.<br>5. All transactions accepted without obligation and are subject to our standard trading conditions (Available on request).<br>6. Invoice disputes must be communicated, in writing, within (14) days of receipt<br>7. E. & O. E.<br>';
        PDF::writeHTMLCell(0, 0, 10, 240, $txt, 0, 0, 0, true, 'J', true);
        PDF::ln(28);
        PDF::SetFont('times', 'I', 8);
        PDF::Cell(0, 0, 'This is a Computer generated invoice which does not require any authorized signature', 0, 0, 'C', 0, '', 0, false, 'T', 'M');

        PDF::setFooterCallback(function ($pdf) use ($txt_pos) {
            $pdf->SetY(-15);
            // Set font
            $pdf->SetFont('times', 'I', 8);
            // Page number
            $pdf->Cell(0, 10, 'Page ' . $pdf->getAliasNumPage() . ' of ' . $pdf->getAliasNbPages(), 0, false, 'R', 0, '', 0, false, 'T', 'M');
        });
        if (ob_get_contents())
            ob_end_clean();
        // PDF::Output(storage_path('/invoices/' . $header->DocNo . '-Invoice.pdf'), "F");
        PDF::Output($header->DocNo . '-Invoice.pdf', "I");
        PDF::reset();
    }

    public function invoiceHeader($id = null)
    {
        $header = DB::select('Set NoCount On;
                            Exec sp_Invoice_Header
                            @InvoiceHeaderID = ?', [$id]);
        return $header;
    }

    public function invoiceDetails($id = null)
    {
        $details = DB::select('Set NoCount On;
                            Exec sp_Invoice_JobDetails
                            @InvoiceHeaderID = ?', [$id]);
        return $details;
    }

    public function hsCodes($id)
    {
        $sql = "Select Distinct KD.ChargeCode,KD.Description
                from TRA_BillingDetails KD With (Nolock)
                Where KD.TotalTax <= 0 and KD.HSCode is Null and KD.DocumentHeaderId = ?";

        $results = DB::select($sql, [$id]);

        return response()->json($results, 200);
    }

   public function updateInvoice(Request $request)
{
    // Log the incoming request
    Log::info('TRA ADL TIN/PIN Update Request', [
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
    Log::info('TRA ADL SP Input JSON', ['json' => $inputJson]);

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
        Log::info('TRA ADL SP Raw Output', ['result' => $result]);

        // Parse the output JSON
        $output = json_decode($result[0]->OutputJson, true);

        // Log parsed output
        Log::info('TRA ADL SP Parsed Output', ['output' => $output]);

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
        Log::info('TRA ADL Final Response', ['response' => $response]);

        return response()->json($response, 200);

    } catch (\Exception $e) {
        Log::error('TRA ADL TIN/PIN Update Exception', [
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
}
