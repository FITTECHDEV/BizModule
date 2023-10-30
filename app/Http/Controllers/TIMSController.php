<?php

namespace App\Http\Controllers;

use App\Models\KRABillingHeader;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use PDF;
use RealRashid\SweetAlert\Facades\Alert;

class TIMSController extends Controller
{
    private $header;

    public function __construct()
    {
        $this->middleware(['auth']);
        $this->header = new KRABillingHeader();
    }
    public function index()
    {
         toast()->info('TIMS Dashboard !!')->persistent(false, true);

        $kraBillingHeader = $this->header;

        return view('tims.index', compact('kraBillingHeader'));
    }

      public function getStats()
    {
        
        $data = [];

        array_push($data, ['Failed' => $this->header->invCredCount('DocStatus','Failed'),
                            'Pending' => $this->header->invCredCount('DocStatus','Pending'),
                            'Submitted' => $this->header->invCredCount('DocStatus','Submitted'),
                            'Total' => $this->header->invCredCount('DocStatus','*')
                        ]);
        
        return response()->json($data, 200);
    }

    public function reTrigger($docNo)
    {
        $feedback = DB::select("SET NOCOUNT ON;
                                    Declare
                                    @Status as Varchar(50),
                                    @Remark as Varchar(200)
                                Exec KRA_RetriggerInvoice
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
        $json_string = KRABillingHeader::where('ID', $id)->select('DocNo', 'KRAJsonFile')->first();
        Storage::put("invoice_data/" . $json_string->DocNo . "-Invoice-data.json", $json_string->KRAJsonFile);
        return response()->download(storage_path("app/invoice_data/" . $json_string->DocNo . "-Invoice-data.json"));
    }

    // public function manual()
    // {
    //     $entries = [
    //         // 'BDF352A4-8441-4C51-885F-AF6A0099D01D',
    //         // 'DB3FFF3C-05B9-40D3-AB5D-AF6A0099D785',
    //         // 'C7C78597-7FD7-4E27-AC07-AF6A0099DC41',
    //         // 'EAD7649B-EC17-43A1-8933-AF6A0099DCEA',
    //         // '782FACAE-DAE7-42F8-A813-AF6A0099E174',
    //         // 'C705B511-670A-455A-B731-AF6A0099E926',
    //         // '63B993C1-14B4-42C5-83B0-AF6A0099F2E8',
    //         // '99ACDD49-8F0C-424B-8F0D-AF6A0099F9C8',
    //         // '0ED6E4E9-4F70-43D2-97C9-AF6D010C9F0E',
    //         // '02C4F69A-BC8D-42F0-A7FA-AF6D010C9FEE',
    //         // '05C2035E-BF3A-4119-93FB-AF6D010CA099',
    //         // 'FBCA6657-85B6-41C3-8449-AF6D010CA112',
    //         // '6DE55D0F-FB9F-4CB8-AF3A-AF6D010CA202',
    //         // '30C14DD8-2820-45AC-8E13-AF6D010CA2AB',
    //         // '152A00ED-537F-4D26-B05B-AF6D010CA360',
    //         // '77327395-B698-4A8A-831A-AF6D010CA43A',
    //         // '22FE5167-4CE4-4298-B2B1-AF6D010CA4BC',
    //         // '40BD5D54-CFAE-4465-870E-AF6D010CA54B',
    //         // '63075556-B645-4363-ABA3-AF6D010CA5F5',
    //         // 'C67FEBC6-C77C-4865-B815-AF6D010CA67B',
    //         // 'F4563FA4-BD04-4EB7-AE66-AF6D010CA6F9',
    //         // '4BC23FC2-607C-4399-B6A9-AF6D010CA788',
    //         // '28297108-955B-4445-BEAF-AF6D010CA834',
    //         // '1D6E22BC-BB8C-494C-8830-AF6D010CA8BE',
    //         // '43C207B7-246D-4B8D-BFA9-AF6D010CA945',
    //         // '936FE313-47DE-460F-B555-AF6D010CA9D7',
    //         // 'E2ECA852-1AE5-4B93-942A-AF6D010CABAE',
    //         // '02C69BF6-7AEC-4DD8-8488-AF6D010CB471',
    //         // '2FA045FD-95BA-4DEA-8954-AF6D010CB932',
    //         // 'B8154D1E-87CE-4832-8902-AF6D010CBA87',
    //         // '9A8BC6D4-46A6-40BD-8A58-AF6D010CBB92',
    //         // 'B85705AB-C0C1-40EF-B91A-AF6D010CBC97',
    //         // '99439F47-16F6-43A9-B369-AF6D010CBD7F',
    //         // '2B652F3E-35A6-409A-9DA7-AF6D010CBE9F',
    //         // 'D52ABC14-F942-4DFD-9E97-AF6D010CBFAF',
    //         // 'AC37712C-E3B1-4A89-BFEF-AF6D010CC103',
    //         // '3218D665-DAAE-40EA-9FB6-AF6D010CC1FF',
    //         // '52750205-39C7-4B6E-BBFD-AF6D010CC301',
    //         // '07EAA2B2-5687-4710-BFF7-AF6D010CC41B',
    //         // '30E298F3-6C9C-447F-A97E-AF6D010CC53B',
    //         // 'ED1D9849-2964-4B68-816F-AF6D010CC650',
    //         // 'F5F3AD3F-0495-4DB3-82B0-AF6D010CC751',
    //         // 'FF8C5C17-5369-45D7-A505-AF6D010CC868',
    //         // '13E67AF3-DA5C-4885-865A-AF6D010CC98E',
    //         // 'A9A582CB-A747-4782-9F38-AF6D010CCAC4',
    //         // '29C26498-6227-4A3E-9E6C-AF6D010CCBA2',
    //         // '8320103B-DBBC-4477-A483-AF6D010CCC80',
    //         // '9A83AB18-D347-4080-A87E-AF6D010CCD70',
    //         // '9F0CC832-2D4A-40AE-AB33-AF6D010CCE77',
    //         // '71F0675E-F8E2-489C-AC02-AF6D010CCF68',
    //         // '11853DD2-4F9F-47DE-A76E-AF6D010CD05E',
    //         // 'D8CD3A12-63D4-4B91-83A9-AF6D010CD149',
    //         // '7AE2FAA8-2D74-4690-8361-AF6D010CD25A',
    //         // 'C4C8F810-3E18-4445-A346-AF6D010CD346',
    //         // 'A0D80469-E09C-4CF6-B209-AF6D010CD43B',
    //         // '714C070B-40DC-4CF9-80B9-AF6D010CD53A',
    //         // 'C403C666-8689-40A6-B74F-AF6D010CD7BF',
    //         // 'DAAF8A35-E503-4B71-9326-AF6D010CD8C8',
    //         // 'E99D5977-E7C7-4A5C-94D4-AF6D010CD9DA',
    //         // 'ECB652FA-8838-4B9A-9767-AF6D010CDAEC',
    //         // '380274CC-D120-4E1F-A2ED-AF6D010CDBFE',
    //         // '8A9D55C7-D292-4E73-9520-AF6D010CDD12',
    //         // '43AB4622-CE43-4194-9DA5-AF6D010CDE46',
    //         // 'E05E803B-178F-4B84-873A-AF6D010CDF5C',
    //         // '8805FEFD-CE1C-4352-826B-AF6D010CE06D',
    //         // '85D9B99E-AD21-4404-A1A5-AF6D010CE187',
    //         // 'CEC38958-CDD9-466E-A6D6-AF6D010CE29F',
    //         // '0AE01411-EED9-4669-8527-AF6D010CE3B2',
    //         // '6F82C698-8391-4244-A54B-AF6D010CE549',
    //         // 'DFDC940B-EF2A-4587-BC78-AF6D010CE6A0',
    //         // '1A61F9C8-C0FF-4DC3-B85C-AF6D010CE7BE',
    //         // '6CA1B5CE-F1EC-4EBA-BFC7-AF6D010CE8DA',
    //         // '938F1E17-F9FD-4A0A-889E-AF6D010CE9FD',
    //         // 'AE78E278-BC15-4C20-B997-AF6D010CEB74',
    //         // 'AB0DE0E5-07E0-4020-A53A-AF6D010CEC91',
    //         // 'F396DF0A-8B80-494B-82A9-AF6D010CEDB1',
    //         // '304F4900-374C-4390-A5D6-AF6D010CEEDB',
    //         // 'D0BB3918-B619-4242-A735-AF6D010CF001',
    //         // '5FCE7885-97FC-4571-8936-AF6D010CF12A',
    //         // '503536F6-31F7-4707-9015-AF6D010CF250',
    //         // '30BD03B6-6CE2-4D59-B756-AF6D010CF368',
    //         // 'D3F82379-B4FF-450D-98C1-AF6D010CF492',
    //         // '8407420F-5D99-4069-B811-AF6D010CF5AF',
    //         // '7BC24261-EB6C-4082-92D1-AF6D010CF6F3',
    //         // 'C3C628CA-BA53-4546-BA6E-AF6D010CF821',
    //         // '2AFFE67E-8BEF-47AC-AE36-AF6D010CF94A',
    //         // 'D17D079A-0F2D-4CF6-B149-AF6D010CFA67',
    //         // '838E4B17-1F68-42F7-A3A8-AF6D010CFB8C',
    //         // 'B03790B8-1497-4FD3-B859-AF6D010CFCAD',
    //         // '13DEE56F-B3C1-4687-9C53-AF6D010CFDDC',
    //         // '5D7D6862-F6EC-4A1A-AE04-AF6D010CFF08',
    //         // 'F8C2B8D7-A08F-4AA1-AA2E-AF6D010D0038',
    //         // 'E5F549EE-2023-4770-9390-AF6D010D016D',
    //         // '3BB5BBC3-6577-4462-B8E8-AF6D010D02A8',
    //         // '4A4B310B-437D-495F-B051-AF6D010D03F5',
    //         // '14150D20-083C-476D-9475-AF6D010D0523',
    //         // 'EED73527-6D17-41C6-AA73-AF6D010D064C',
    //         // '010392D9-197E-46D6-A039-AF6D010D0778',
    //         // 'AAD683F2-A68E-4ED9-99D4-AF6D010D08A9',
    //         // 'E5222506-E34F-42BD-B59E-AF6D010D09DA',
    //         // 'B55002D4-7B2F-4F11-A283-AF6D010D0B07',
    //         // 'D7D25210-028A-4482-AC23-AF6D010D0C4A',
    //         // 'B235A197-2F09-4BD3-BF75-AF6D010D0D73',
    //         // 'E266E016-30A6-47AE-A1DF-AF6D010D0E95',
    //         // 'A6CC158F-AD50-45F5-837D-AF6D010D0FEB',
    //         // '48672A20-2D69-452E-97E0-AF6D010D1122',
    //         // 'B8699B0E-B61F-459F-880A-AF6D010D1253',
    //         // 'DC1C2CC1-23A9-4CBD-BE3D-AF6D010D1391',
    //         // '500256DE-3AC4-4214-9CE2-AF6D010D14F1',
    //         // '990A9C59-5D90-41BA-A6DB-AF6D010D1623',
    //         // '8086F1BF-FDDA-4DD2-A0BD-AF6D010D1767',
    //         // '33689B92-753A-4DD0-9D97-AF6D010D1987',
    //         // 'D3DA2D20-9CF0-44C7-9390-AF6D010D1AD7',
    //         // '23AE43FA-8148-41A3-A164-AF6D010D1C54',
    //         // '15D334B6-7D32-4603-88A4-AF6D010D1DA9',
    //         // 'AFA6A0B3-3FAA-4D16-B79D-AF6D010D1F06',
    //         // 'CC0CB94A-BDB3-4206-8198-AF6D010D2073',
    //         // '54C3B5A3-D75C-40E2-9784-AF6D010D21D6',
    //         // '630495BC-C173-46AB-989D-AF6D010D232C',
    //         // '4659F179-43B9-47FA-8207-AF6D010D2478',
    //         // '1E408657-5F02-4D87-AC52-AF6D010D25DE',
    //         // '267E9232-4EB1-4737-9EF7-AF6D010D2734',
    //         // '2D3AF4B7-8B68-4EAD-88CD-AF6D010D28BD',
    //         // '9B27B050-3766-40AA-8140-AF6D010D2A0E',
    //         // '1838DC28-8E3B-4DD4-B5DA-AF6D010D2CE7',
    //         // 'F98FF315-4511-456A-AB78-AF6D010D3086',
    //         // 'E98D5247-A858-45AF-A118-AF6D010D3248',
    //         // 'E2B62CBA-7B70-4BEA-BE26-AF6D010D340A',
    //         // '88139773-BCE6-4077-ABC0-AF6D010D35CE',
    //         // '35B7A387-8412-45EF-B05C-AF6D010D372F',
    //         // 'BD8D1091-6EC7-48C1-8D7F-AF6D010D3886',
    //         // '760F4EA2-49B8-48FA-AC2B-AF6D010D39DB',
    //         // '27131A22-9BB2-40DE-9389-AF6D010D3B32',
    //         // 'F0C3914F-A44C-4D7C-ADA7-AF6D010D3C92',
    //         // '0828CB52-53E3-4E22-B5FC-AF6D010D3DFC',
    //         // 'F373236C-0EF7-49FD-AD92-AF6D010D3F5E',
    //         // '4260F9D4-667A-4231-BB8D-AF6D010D40C9',
    //         // '6A38C78D-2EBC-4E69-B777-AF6D010D4247',
    //         // '3B6791DF-5D8D-41CB-9075-AF6D010D43AE',
    //         // '325BEB16-E586-4E5F-8CFC-AF6D010D4515',
    //         // 'F392AC00-BB55-4684-A8D0-AF6D010D467E',
    //         // '2E0222B6-15D1-4F76-A910-AF6D010D47FE',
    //         // '7265CBE5-0A5A-488B-8B61-AF6D010D4972',
    //         // '90465A36-C85D-4368-8961-AF6D010D4AE4',
    //         // '2DB564B5-1EDD-42E5-AF05-AF6D010D4C4E',
    //         // '8769D049-47FD-4503-BAF8-AF6D010D4DE8',
    //         // '3BF77FB8-D7C2-4557-8593-AF6D010D4F54',
    //         // '095FDEA3-0586-4D03-9FA8-AF6D010D50C0',
    //         // '2009ABFC-4C0C-4E05-9900-AF6D010D5239',
    //         // '59A5C8CE-9FD6-4E00-9B56-AF6D010D53BC',
    //         // 'B3DDC94E-CEAA-486B-90DA-AF6D010D5534',
    //         // 'E075A546-4E21-4516-983A-AF6D010D56A9',
    //     ];
    //     foreach ($entries as $entry) {
    //         $this->invoiceDownload($entry);
    //     }
    // }

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
                    FREIGHT IN TIME LIMITED
                    Nairobi, Kenya
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

            $data = "$bookingNo DT $dt<br>".ucwords($consignee)."<br>$shipperRef";
            PDF::writeHTMLCell(0, '', 16, $txt_pos, $data, 0, 0, 0, true, 'J', true); //Shipment No & Date, Consignee Name, Reference Number
            PDF::writeHTMLCell(11, '', 48, $txt_pos, $details[$i]->ShipmentType, 0, 0, 0, true, 'J', true); //L, N, D

            $title = $details[$i]->Packages . " <br>" . $details[$i]->ClearingAgent . "<br>" . $bookingNo;
            PDF::writeHTMLCell(0, '', 57, $txt_pos, $title, 0, 0, 0, true, 'J', true); // pkgs, Consignorname, jobno

            PDF::writeHTMLCell(0, '', 75, $txt_pos, number_format($details[$i]->WeightinKG, 2), 0, 0, 0, true, 'J', true); // wt
            PDF::writeHTMLCell(0, '', 85, $txt_pos, $details[$i]->ZoneName, 0, 0, 0, true, 'J', true); // zone
            PDF::writeHTMLCell(0, '', 108, $txt_pos, number_format($details[$i]->Tariff, 2), 0, 0, 0, true, 'J', true); // tarrif
            PDF::writeHTMLCell(0, '', 95, $txt_pos, number_format($details[$i]->Discount, 2), 0, 0, 0, true, 'J', true); // discount

            $title =  number_format($details[$i]->Freight, 2) . "<br>" . $details[$i]->CountryOfDestination . "<br>" . $consigneeRef;
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
                    KRA Serial No. $header->KRASerialNo
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
        if (ob_get_contents()) ob_end_clean();
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
                from KRA_BillingDetails KD With (Nolock)
                Where KD.TotalTax <= 0 and KD.HSCode is Null and KD.DocumentHeaderId = ?";

        $results = DB::select($sql, [$id]);

        return response()->json($results, 200);
    }

    public function updateInvoice(Request $request)
    {
        $validated = $request->validate([
            'InvoiceId' => "required",
            'ChangeOption' => "required", // "Customer ID" & "HS Code"
            'HSCodeOptions' => "required_if:ChangeOption,==,HS_Code", //charge code
            'LevelOption' => "required", // $validated['ChangeOption']
            'OptionValue' => "required", // value
        ]);

        $code = $validated['HSCodeOptions'] ?? null;

        //exec sp
        $feedback = DB::select("SET NOCOUNT ON;
                                    Declare
                                    @Status as Varchar(50),
                                    @Remark as Varchar(200)
                                        Exec KRA_InfomationUpdate
                                    @ChangeType  = ?,
                                    @Appliable   = ?,
                                    @ChargeCode  = ?,
                                    @Value = ?,
                                    @InvoiceID = ?,
                                    @Status = @Status Output,
                                    @Remark= @Remark Output
                                    Select @Status as 'Status',
                                    @Remark as 'Status_Remark'
                                   ", [
                                        $validated['ChangeOption'],
                                        $validated['LevelOption'],
                                        $code,
                                        $validated['OptionValue'],
                                        $validated['InvoiceId'],
                                    ]);

            return response()->json($feedback, 200);

    }
}