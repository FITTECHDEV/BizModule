<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreContractTemplateRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Services\ContractListSpClient;
use App\Services\ContractListMapper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class ContractTemplateController extends Controller
{
    private function sessionKey($quotationId): string
    {
        return "contract_template.quotation.{$quotationId}";
    }

    public function listContracts(ContractListSpClient $client, $quotationId): JsonResponse
    {
        if (empty($quotationId)) {
            return response()->json(['success' => false, 'message' => 'Quotation ID is required'], 422);
        }

        $service = DB::connection('sqlsrv')
            ->table('Quote_QuotationInfo')
            ->where('ID', $quotationId)
            ->value('ServiceVertical');

        if (empty($service)) {
            return response()->json(['success' => false, 'message' => 'Quotation has no service selected'], 422);
        }

        // Same 'List' mode as ContractController::listContracts() — sp_Contract_List
        // takes no filter params in this mode; it returns all active, in-window
        // contracts. 'Parameter' (smart-match) mode isn't usable here since it
        // requires Process (Export/Import/Local) + COO/COD, which the quotation
        // query params (quote_no/service/origin/destination) don't supply.
        $payload = ContractListMapper::build('List', [], auth()->user()->name ?? '');

        try {
            $result = $client->call($payload);
        } catch (\RuntimeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }

        if (!$result['ok']) {
            return response()->json(['success' => false, 'message' => $result['message']], 422);
        }

        $contracts = array_map(function ($row) {
            return [
                'id' => $row[0] ?? null,
                'contractNo' => $row[1] ?? null,
                'contractName' => $row[2] ?? null,
                'effectiveFrom' => $row[3] ?? null,
                'effectiveTo' => $row[4] ?? null,
                'status' => $row[5] ?? null,
            ];
        }, $result['rows']);

        return response()->json(['success' => true, 'data' => $contracts]);
    }

    public function edit(Request $request, $quotationId)
    {
        $existing = Session::get($this->sessionKey($quotationId));

        $officeId = DB::connection('sqlsrv')
            ->table('Quote_QuotationInfo')
            ->where('ID', $quotationId)
            ->value('OfficeID');

        if (!$officeId) {
            $officeCode = session('active_office_code');

            if ($officeCode) {
                $office = DB::connection('sqlsrv')
                    ->table('Cust_Office')
                    ->whereRaw('RTRIM(Code) = ?', [strtoupper(trim($officeCode))])
                    ->first(['Id']);

                $officeId = $office->Id ?? '';
            }
        }
        $shipment = [
            'quote_no' => $request->query('quote_no', ''),
            'officeCode' => session('active_office_code', ''),
            'officeId' => $officeId ?? '',
            'service' => $request->query('service', ''),
            'origin' => $request->query('origin', ''),
            'destination' => $request->query('destination', ''),
        ];

        $view =
            $request->ajax() || $request->query('partial') === '1'
                ? 'quotation.contract-templates._content'
                : 'quotation.contract-templates.index';

        return view($view, compact('quotationId', 'existing', 'shipment'));
    }

    public function store(StoreContractTemplateRequest $request, $quotationId)
    {
        Log::info('Contract Template Save - Incoming JSON', [
            'quotation_id' => $quotationId,
            'payload' => $request->all(),
        ]);

        $validated = $request->validated();

        try {
            $inputJson = json_encode(
                [
                    'OperationType' => $validated['OperationType'],
                    'SubmittedDatetime' => $validated['SubmittedDatetime'],
                    'SubmittedBy' => $validated['SubmittedBy'],
                    'Action' => $validated['Action'],
                    'QuotationNo' => $validated['QuotationNo'],
                    'QuotationInfoID' => $validated['QuotationInfoID'],
                    'ContractID' => $validated['ContractID'],
                    'Charges' => array_map(function ($charge) {
                        return [
                            'ChargeCatagory' => $charge['ChargeCatagory'] ?? '',
                            'ChargeRowID' => $charge['ChargeRowID'] ?? '',
                            'ChargeCode' => $charge['ChargeCode'] ?? '',
                            'ChargeDescription' => $charge['ChargeDescription'] ?? '',
                            'PartyCode' => $charge['PartyCode'] ?? '',
                            'ChgCurr' => $charge['ChgCurr'] ?? '',
                            'BasedOnCode' => $charge['BasedOnCode'] ?? '',
                            'BasedOnUOM' => $charge['BasedOnUOM'] ?? '',
                            'Quantity' => (float) ($charge['Quantity'] ?? 0),
                            'CostRate' => (float) ($charge['CostRate'] ?? 0),
                            'CostAmount' => (float) ($charge['CostAmount'] ?? 0),
                            'IncreaseBy' => (float) ($charge['IncreaseBy'] ?? 0),
                            'IncreaseType' => $charge['IncreaseType'] ?? 'Percentage',
                            'SellRate' => (float) ($charge['SellRate'] ?? 0),
                            'SellAmount' => (float) ($charge['SellAmount'] ?? 0),
                            'ChargeRemark' => $charge['ChargeRemark'] ?? '',
                        ];
                    }, $validated['Charges']),
                ],
                JSON_THROW_ON_ERROR,
            );

            Log::info('Contract Template Save - SP JSON', [
                'quotation_id' => $quotationId,
                'input_json' => $inputJson,
            ]);

            $pdo = DB::connection('sqlsrv')->getPdo();

            $stmt = $pdo->prepare(
                "SET NOCOUNT ON;
             DECLARE @OutputJson VARCHAR(2000);

             EXEC dbo.sp_Quote_DetailsUpdate ?, @OutputJson OUTPUT;

             SELECT @OutputJson AS OutputJson;",
            );

            $stmt->execute([$inputJson]);

            $result = $stmt->fetch(\PDO::FETCH_ASSOC);

            $outputJson = $result['OutputJson'] ?? null;

            Log::info('Contract Template Save - SP Response', [
                'quotation_id' => $quotationId,
                'output_json' => $outputJson,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Contract charges saved successfully.',
                'data' => $outputJson,
            ]);
        } catch (\Throwable $e) {
            Log::error('Contract Template Save - Failed', [
                'quotation_id' => $quotationId,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(
                [
                    'success' => false,
                    'message' => $e->getMessage(),
                ],
                500,
            );
        }
    }

    public function charges($quotationId): JsonResponse
    {
        $link = Session::get($this->sessionKey($quotationId));

        if (!$link) {
            return response()->json(['success' => true, 'data' => null]);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'contract_template_name' => $link['contract_template_name'],
                'revenue_charges' => $link['revenue_charges'] ?? [],
                'cost_charges' => $link['cost_charges'] ?? [],
                'revenue_total' => $link['revenue_total'] ?? 0,
                'cost_total' => $link['cost_total'] ?? 0,
                'margin' => $link['margin'] ?? 0,
            ],
        ]);
    }
}
