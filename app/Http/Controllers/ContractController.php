<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreContractRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Services\ContractListMapper;
use App\Services\ContractListClient;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ContractController extends Controller
{
    private function sessionKey(string $contractId): string
    {
        return "contract.draft.{$contractId}";
    }

    private function submittedDatetime(): string
    {
        // sp_Contractinfo, sp_Contractinfo_ServiceDetails, and
        // sp_Contractinfo_ServiceCharges all parse SubmittedDatetime via a
        // positional STUFF() chain built for yyyyMMddHHmmss. Do NOT switch
        // this to a separator-formatted string — TRY_CONVERT silently
        // returns NULL if you do.
        return now()->format('YmdHis');
    }

    /**
     * Show the Create Contract screen. Generates a draft ID if none supplied,
     * so Add Service / Add Charges can be saved incrementally into the same
     * session slot before the contract is finalised.
     */

    public function create(Request $request, ContractListClient $client)
    {
        $contractId = $request->query('draft', Str::uuid()->toString());
        $existing = Session::get($this->sessionKey($contractId));

        $stats = $this->computeDashboardStats($client);
        $officeCode = $this->getUserOfficeCode();

        return view('contracts.create', compact('contractId', 'existing', 'stats', 'officeCode'));
    }

    public function showDetails(string $contract)
    {
        $contractInfo = DB::connection('sqlsrv')
            ->table('Contract_ContractInfo')
            ->where('Id', $contract)
            ->where('IsDeleted', 0)
            ->first();

        if (!$contractInfo) {
            abort(404, 'Contract not found');
        }

        $services = DB::connection('sqlsrv')
            ->table('Contract_ContractServiceAndFilter')
            ->where('ContractInfoID', $contract)
            ->where('IsDeleted', 0)
            ->get();

        $serviceIds = $services->pluck('Id')->toArray();
        $charges = [];
        if (!empty($serviceIds)) {
            $charges = DB::connection('sqlsrv')
                ->table('Contract_ContractCharges')
                ->whereIn('ServiceAndFilterID', $serviceIds)
                ->where('IsDeleted', 0)
                ->get()
                ->groupBy('ServiceAndFilterID');
        }

        return view('contracts.show', compact('contractInfo', 'services', 'charges'));
    }

    public function edit(string $contract)
    {
        $contractInfo = DB::connection('sqlsrv')
            ->table('Contract_ContractInfo')
            ->where('Id', $contract)
            ->where('IsDeleted', 0)
            ->first();

        if (!$contractInfo) {
            abort(404, 'Contract not found');
        }

        $services = DB::connection('sqlsrv')
            ->table('Contract_ContractServiceAndFilter')
            ->where('ContractInfoID', $contract)
            ->where('IsDeleted', 0)
            ->get();

        $serviceIds = $services->pluck('Id')->toArray();
        $charges = [];
        if (!empty($serviceIds)) {
            $charges = DB::connection('sqlsrv')
                ->table('Contract_ContractCharges')
                ->whereIn('ServiceAndFilterID', $serviceIds)
                ->where('IsDeleted', 0)
                ->get()
                ->groupBy('ServiceAndFilterID');
        }

        $officeCode = $this->getUserOfficeCode();

        $officeId = DB::connection('sqlsrv')
            ->table('Cust_Office')
            ->whereRaw('RTRIM(Code) = ?', [strtoupper(trim($officeCode))])
            ->where('IsActive', 1)
            ->where('IsDeleted', 0)
            ->value('Id');

        return view('contracts.edit', compact('contractInfo', 'services', 'charges', 'officeCode'));
    }

    public function newDraft(): JsonResponse
    {
        $contractId = Str::uuid()->toString();

        return response()->json([
            'success' => true,
            'data' => [
                'contract_id' => $contractId,
                'store_url' => route('contracts.store', $contractId),
                'services_store_url' => route('contracts.services.store', $contractId),
            ],
        ]);
    }

    /**
     * GET /contracts/list
     * Row-level data for the Contract Management landing table — same SP
     * and mode as computeDashboardStats(), but returns actual rows instead
     * of aggregate counts. Only Authorized contracts are returned; this is
     * an sp_Contract_List ceiling, not a filter applied here.
     */
    public function listContracts(ContractListClient $client): JsonResponse
    {
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
                'id' => $row[0],
                'contractNo' => $row[1],
                'contractName' => $row[2],
                'effectiveFrom' => $row[3],
                'effectiveTo' => $row[4],
                'status' => $row[5],
            ];
        }, $result['rows']);

        Log::info('listContracts - result', ['count' => count($contracts), 'sample' => $contracts[0] ?? null]);

        return response()->json(['success' => true, 'data' => $contracts]);
    }

    /**
     * Pulls contract data via sp_Contract_List (UsedFor=List).
     * The SP returns all active, non-deleted contracts currently within
     * their effective date range, regardless of status — counts are
     * bucketed here by the Status column (row index 5).
     */
    private function computeDashboardStats(ContractListClient $client): array
    {
        $stats = [
            'pending' => 0,
            'pending_approval' => 0,
            'authorized' => 0,
            'renewal_due' => 0,
            'total' => 0,
        ];

        $payload = ContractListMapper::build('List', [], auth()->user()->name ?? '');

        try {
            $result = $client->call($payload);
        } catch (\RuntimeException $e) {
            Log::error('Dashboard stats - sp_Contract_List call failed', ['error' => $e->getMessage()]);
            return $stats;
        }

        if (!$result['ok']) {
            Log::warning('Dashboard stats - sp_Contract_List returned failure', ['message' => $result['message']]);
            return $stats;
        }

        $today = Carbon::today();

        foreach ($result['rows'] as $row) {
            $effectiveTo = $row[4] ?? null;
            $status = $row[5] ?? null;

            $stats['total']++;

            switch ($status) {
                case 'Pending':
                    $stats['pending']++;
                    break;
                case 'Pending Approval':
                    $stats['pending_approval']++;
                    break;
                case 'Authorized':
                    $stats['authorized']++;
                    break;
            }

            if ($effectiveTo) {
                $daysUntilExpiry = $today->diffInDays(Carbon::parse($effectiveTo), false);
                if ($daysUntilExpiry >= 0 && $daysUntilExpiry <= 10) {
                    $stats['renewal_due']++;
                }
            }
        }

        return $stats;
    }

    public function store(StoreContractRequest $request, string $contract): JsonResponse
    {
        $validated = $request->validated();

        $draft = Session::get($this->sessionKey($contract));
        $action = isset($draft['contract_id']) ? 'Update' : 'Add';

        $headerPayload = [
            'OperationType' => 'ContractInfo',
            'SubmittedDatetime' => $this->submittedDatetime(),
            'SubmittedBy' => auth()->user()->name ?? 'system',
            'Action' => $action,
            'ContractID' => $draft['contract_id'] ?? null,
            'ContractDate' => now()->format('d/m/Y'),
            'ContactType' => $validated['contract_type'],
            'OfficeCode' => $this->getUserOfficeCode(),
            'ClientCode' => '',
            'EffectiveFrom' => \Carbon\Carbon::parse($validated['effective_from'])->format('Ymd'),
            'EffectiveTo' => \Carbon\Carbon::parse($validated['effective_to'])->format('Ymd'),
            'ContractName' => $validated['contract_name'],
            'Remark' => $validated['remark'] ?? '',
        ];

        $headerResult = $this->callContractSP('sp_Contractinfo', $headerPayload);

        if (!$headerResult['success']) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Failed to save contract header: ' . ($headerResult['message'] ?? 'Unknown error'),
                ],
                422,
            );
        }

        $contractId = $headerResult['contract_id'];
        $contractNo = $headerResult['contract_no'];

        $draft['contract_id'] = $contractId;
        $draft['contract_no'] = $contractNo;
        Session::put($this->sessionKey($contract), $draft);

        return response()->json([
            'success' => true,
            'message' => "Contract {$contractNo} saved successfully.",
            'data' => [
                'contract_no' => $contractNo,
                'contract_id' => $contractId,
            ],
        ]);
    }

    public function storeService(Request $request, string $contract): JsonResponse
    {
        $draft = Session::get($this->sessionKey($contract));

        if (empty($draft['contract_id'])) {
            return response()->json(
                ['success' => false, 'message' => 'Save Contract Info before adding services.'],
                422,
            );
        }

        $service = $request->all();

        $servicePayload = [
            'OperationType' => 'ContractServiceDetails',
            'Action' => 'Add',
            'SubmittedBy' => auth()->user()->name ?? 'system',
            'SubmittedDatetime' => $this->submittedDatetime(),
            'ContractInfoID' => $draft['contract_id'],
            'ServiceAndFilterID' => null,
            'Service' => $service['service'] ?? '',
            'Process' => $service['process'] ?? '',
            'IncoTerm' => $service['incoterm'] ?? '',
            'COO' => $service['countryOrigin'] ?? '',
            'COD' => $service['countryDestination'] ?? '',
            'POR' => $service['placeOfReceipt'] ?? '',
            'POE' => $service['placeOfDelivery'] ?? '',
            'POL' => $service['pol'] ?? '',
            'POD' => $service['pod'] ?? '',
            'ServiceClass' => $service['serviceClass'] ?? '',
            'ShipmentMode' => $service['shipmentMode'] ?? '',
            'ShipmentType' => $service['shipmentType'] ?? '',
            'MovementType' => $service['movementType'] ?? '',
        ];

        $result = $this->callContractSP('sp_Contractinfo_ServiceDetails', $servicePayload);

        if (!$result['success']) {
            return response()->json(
                ['success' => false, 'message' => 'Failed to save service: ' . ($result['message'] ?? 'Unknown error')],
                422,
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Service saved.',
            'data' => ['service_filter_id' => $result['service_filter_id']],
        ]);
    }

    public function storeCharge(Request $request, string $contract, string $serviceFilterId): JsonResponse
    {
        $draft = Session::get($this->sessionKey($contract));
        $contractInfoId = $draft['contract_id'] ?? null;

        if (empty($contractInfoId)) {
            // No session draft (Edit flow) — treat $contract as the real ContractInfoID.
            $exists = DB::connection('sqlsrv')
                ->table('Contract_ContractInfo')
                ->where('Id', $contract)
                ->where('IsDeleted', 0)
                ->exists();

            if (!$exists) {
                return response()->json(
                    ['success' => false, 'message' => 'Save Contract Info before adding charges.'],
                    422,
                );
            }
            $contractInfoId = $contract;
        }

        $charge = $request->all();

        $chargePayload = [
            'OperationType' => 'ServiceBasedCharges',
            'SubmittedDatetime' => $this->submittedDatetime(),
            'SubmittedBy' => auth()->user()->name ?? 'system',
            'Action' => 'Add',
            'ContractInfoID' => $contractInfoId,
            'ServiceAndFilterID' => $serviceFilterId,
            'ServiceChargeID' => null,
            'ChargeCode' => $charge['chargeCode'] ?? '',
            'ChargeBasedon' => $charge['chargeBasedOn'] ?? '',
            'ChargeBasedOnUOM' => $charge['chargeBasedOnUOM'] ?? '',
            'ChargeCurr' => $charge['chargeCurr'] ?? '',
            'MinAmount' => $charge['minAmount'] ?? 0.0,
            'MaxAmount' => $charge['maxAmount'] ?? 0.0,
            'RateType' => $charge['rateType'] ?? '',
            'Rate' => $charge['rate'] ?? 0.0,
            'Formula' => $charge['formula'] ?? '',
            'RateSlabJson' => !empty($charge['rateSlab']) ? json_encode($charge['rateSlab']) : '',
            'Comments' => '',
            'ChargeAction' => $charge['chargeAction'] ?? 'R',
            'MarkupRate' => $charge['markupRate'] ?? 0.0,
            'MarkFormula' => $charge['markupFormula'] ?? '',
            'MarkRateSlabJson' => !empty($charge['markupRateSlab']) ? json_encode($charge['markupRateSlab']) : '',
            // Not yet read by sp_Contractinfo_ServiceCharges (missing from its
            // OPENJSON WITH clause and INSERT column list) — sent ahead of time
            // so no PHP change is needed once the SP is extended to accept them.
            'IsRevenue' => ($charge['chargeNature'] ?? '') === 'Cost' ? 0 : 1,
            'Category' => $charge['chargeCategory'] ?? '',
            'ChargeDescription' => $charge['chargeDescription'] ?? '',
            'ToBeAdded' => $charge['to_be_added'] ?? 0,
        ];

        $result = $this->callContractSP('sp_Contractinfo_ServiceCharges', $chargePayload);

        if (!$result['success']) {
            return response()->json(
                ['success' => false, 'message' => 'Failed to save charge: ' . ($result['message'] ?? 'Unknown error')],
                422,
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Charge saved.',
            'data' => ['charge_id' => $result['charge_id']],
        ]);
    }

    public function updateCharge(
        Request $request,
        string $contract,
        string $serviceFilterId,
        string $chargeId,
    ): JsonResponse {
        $charge = $request->all();

        $chargePayload = [
            'OperationType' => 'ServiceBasedCharges',
            'SubmittedDatetime' => $this->submittedDatetime(),
            'SubmittedBy' => auth()->user()->name ?? 'system',
            'Action' => 'Update',
            'ContractInfoID' => $contract,
            'ServiceAndFilterID' => $serviceFilterId,
            'ServiceChargeID' => $chargeId,
            'ChargeCode' => $charge['chargeCode'] ?? '',
            'ChargeBasedon' => $charge['chargeBasedOn'] ?? '',
            'ChargeBasedOnUOM' => $charge['chargeBasedOnUOM'] ?? '',
            'ChargeCurr' => $charge['chargeCurr'] ?? '',
            'MinAmount' => $charge['minAmount'] ?? 0.0,
            'MaxAmount' => $charge['maxAmount'] ?? 0.0,
            'RateType' => $charge['rateType'] ?? '',
            'Rate' => $charge['rate'] ?? 0.0,
            'Formula' => $charge['formula'] ?? '',
            'RateSlabJson' => !empty($charge['rateSlab']) ? json_encode($charge['rateSlab']) : '',
            'Comments' => '',
            'ChargeAction' => $charge['chargeAction'] ?? 'R',
            'MarkupRate' => $charge['markupRate'] ?? 0.0,
            'MarkFormula' => $charge['markupFormula'] ?? '',
            'MarkRateSlabJson' => !empty($charge['markupRateSlab']) ? json_encode($charge['markupRateSlab']) : '',
            // IsRevenue/Category not yet read by sp_Contractinfo_ServiceCharges
            // (commented out server-side, missing from OPENJSON WITH clause and
            // INSERT/UPDATE column lists) — sent ahead of time using the SP's
            // documented contract so no PHP change is needed once the DBA
            // enables them.
            'IsRevenue' => ($charge['chargeNature'] ?? '') === 'Cost' ? 0 : 1,
            'Category' => $charge['chargeCategory'] ?? '',
            'ChargeDescription' => $charge['chargeDescription'] ?? '',
            'ToBeAdded' => $charge['to_be_added'] ?? 0,
        ];

        $result = $this->callContractSP('sp_Contractinfo_ServiceCharges', $chargePayload);

        if (!$result['success']) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Failed to update charge: ' . ($result['message'] ?? 'Unknown error'),
                ],
                422,
            );
        }

        return response()->json(['success' => true, 'message' => 'Charge updated.']);
    }

    public function destroyCharge(string $contract, string $serviceFilterId, string $chargeId): JsonResponse
    {
        $chargePayload = [
            'OperationType' => 'ServiceBasedCharges',
            'SubmittedDatetime' => $this->submittedDatetime(),
            'SubmittedBy' => auth()->user()->name ?? 'system',
            'Action' => 'Delete',
            'ContractInfoID' => $contract,
            'ServiceAndFilterID' => $serviceFilterId,
            'ServiceChargeID' => $chargeId,
        ];

        $result = $this->callContractSP('sp_Contractinfo_ServiceCharges', $chargePayload);

        if (!$result['success']) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Failed to delete charge: ' . ($result['message'] ?? 'Unknown error'),
                ],
                422,
            );
        }

        return response()->json(['success' => true, 'message' => 'Charge deleted.']);
    }

    /**
     * Deletes a service and cascades to all its charges first, since
     * sp_Contractinfo_ServiceDetails' Delete action only soft-deletes the
     * service row itself — it does not touch Contract_ContractCharges.
     * Charges are fetched and deleted individually via
     * sp_Contractinfo_ServiceCharges before the service delete runs, so a
     * charge-delete failure aborts before the service is touched.
     */
    public function destroyService(string $contract, string $serviceFilterId): JsonResponse
    {
        $chargeIds = DB::connection('sqlsrv')
            ->table('Contract_ContractCharges')
            ->where('ServiceAndFilterID', $serviceFilterId)
            ->where('ContractInfoID', $contract)
            ->where('IsDeleted', 0)
            ->pluck('Id');

        foreach ($chargeIds as $chargeId) {
            $chargeResult = $this->callContractSP('sp_Contractinfo_ServiceCharges', [
                'OperationType' => 'ServiceBasedCharges',
                'SubmittedDatetime' => $this->submittedDatetime(),
                'SubmittedBy' => auth()->user()->name ?? 'system',
                'Action' => 'Delete',
                'ContractInfoID' => $contract,
                'ServiceAndFilterID' => $serviceFilterId,
                'ServiceChargeID' => $chargeId,
            ]);

            if (!$chargeResult['success']) {
                Log::error('destroyService - failed to delete charge, aborting service delete', [
                    'contract' => $contract,
                    'service_filter_id' => $serviceFilterId,
                    'charge_id' => $chargeId,
                    'message' => $chargeResult['message'] ?? null,
                ]);
                return response()->json(
                    [
                        'success' => false,
                        'message' =>
                            'Failed to delete charge ' .
                            $chargeId .
                            ' while removing service: ' .
                            ($chargeResult['message'] ?? 'Unknown error'),
                    ],
                    422,
                );
            }
        }

        $servicePayload = [
            'OperationType' => 'ContractServiceDetails',
            'SubmittedDatetime' => $this->submittedDatetime(),
            'SubmittedBy' => auth()->user()->name ?? 'system',
            'Action' => 'Delete',
            'ContractInfoID' => $contract,
            'ServiceAndFilterID' => $serviceFilterId,
        ];

        $result = $this->callContractSP('sp_Contractinfo_ServiceDetails', $servicePayload);

        if (!$result['success']) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Failed to delete service: ' . ($result['message'] ?? 'Unknown error'),
                ],
                422,
            );
        }

        return response()->json(['success' => true, 'message' => 'Service and its charges deleted.']);
    }

    private function getUserOfficeCode(): string
    {
        $officeCode = session('active_office_code');
        if ($officeCode) {
            return $officeCode;
        }

        $user = auth()->user();
        if ($user) {
            $azure = DB::connection('sqlsrv')
                ->table('Azure_Users')
                ->where('BizUserID', $user->id)
                ->first(['BaseOfficeCode']);
            if ($azure && $azure->BaseOfficeCode) {
                return $azure->BaseOfficeCode;
            }
        }
        return 'KE';
    }

    private function callContractSP(string $spName, array $payload): array
    {
        $inputJson = json_encode($payload);
        try {
            $pdo = DB::connection('sqlsrv')->getPdo();
            $stmt = $pdo->prepare(
                "SET NOCOUNT ON; DECLARE @OutputJson NVARCHAR(MAX); EXEC dbo.{$spName} ?, @OutputJson = @OutputJson OUTPUT; SELECT @OutputJson AS OutputJson",
            );
            $stmt->execute([$inputJson]);

            $outputJson = null;
            do {
                $rows = $stmt->fetchAll(\PDO::FETCH_OBJ);
                if (!empty($rows) && isset($rows[0]->OutputJson)) {
                    $outputJson = $rows[0]->OutputJson;
                    break;
                }
            } while ($stmt->nextRowset());

            if (!$outputJson) {
                Log::error("SP {$spName} returned no output", ['input' => $inputJson]);
                return ['success' => false, 'message' => 'No output from stored procedure'];
            }

            $result = json_decode($outputJson, true);
            if (!$result || ($result['Status'] ?? '') === 'Failed') {
                $remarks = $result['Remark'] ?? [];
                $msg = !empty($remarks) ? implode('; ', array_column($remarks, 'Remark')) : 'Unknown error';
                Log::warning("SP {$spName} returned failure", ['input' => $inputJson, 'output' => $outputJson]);
                return ['success' => false, 'message' => $msg];
            }

            return [
                'success' => true,
                'contract_id' => $result['ContractID'] ?? null,
                'contract_no' => $result['ContractNo'] ?? null,
                'service_filter_id' => $result['ServiceFilterID'] ?? null,
                'charge_id' => $result['ServiceChargeID'] ?? null,
            ];
        } catch (\Exception $e) {
            Log::error("SP {$spName} call failed: " . $e->getMessage(), ['input' => $inputJson]);
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Helper to call sp_Contract_Main with a given payload and parse output.
     * Returns ['status' => 'Success'|'Failed', 'remark' => string, 'contractId' => ..., 'contractNo' => ..., 'serviceFilterId' => ...]
     */
    private function callContractMain(array $payload): array
    {
        $inputJson = json_encode($payload);

        try {
            $pdo = DB::connection('sqlsrv')->getPdo();
            $stmt = $pdo->prepare(
                'SET NOCOUNT ON; DECLARE @Output NVARCHAR(MAX); EXEC dbo.sp_Contract_Main ?, @OutputJson = @Output OUTPUT; SELECT @Output AS OutputJson',
            );
            $stmt->execute([$inputJson]);

            $outputJson = null;
            do {
                $rows = $stmt->fetchAll(\PDO::FETCH_OBJ);
                if (!empty($rows) && isset($rows[0]->OutputJson)) {
                    $outputJson = $rows[0]->OutputJson;
                    break;
                }
            } while ($stmt->nextRowset());

            if (!$outputJson) {
                return ['status' => 'Failed', 'remark' => 'No output from stored procedure.'];
            }

            $result = json_decode($outputJson, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return ['status' => 'Failed', 'remark' => 'Invalid JSON response from SP.'];
            }

            $status = $result['Status'] ?? 'Failed';
            $remark = $result['Remarks'][0]['Remark'] ?? '';

            return [
                'status' => $status,
                'remark' => $remark,
                'contractId' => $result['ContractID'] ?? null,
                'contractNo' => $result['ContractNo'] ?? null,
                'serviceFilterId' => $result['ServiceFilterID'] ?? null,
            ];
        } catch (\Exception $e) {
            Log::error('Contract SP call failed: ' . $e->getMessage());
            return ['status' => 'Failed', 'remark' => 'Database error: ' . $e->getMessage()];
        }
    }

    public function show(string $contract): JsonResponse
    {
        $draft = Session::get($this->sessionKey($contract));

        if (!$draft) {
            return response()->json(['success' => true, 'data' => null]);
        }

        return response()->json(['success' => true, 'data' => $draft]);
    }

    /**
     * GET /contract/masters/inco-terms
     */
    public function getIncoTerms(Request $request): JsonResponse
    {
        $filter = $request->input('filter', '');
        try {
            $pdo = DB::connection('sqlsrv')->getPdo();
            $stmt = $pdo->prepare('SET NOCOUNT ON; EXEC dbo.sp_IncoTerms @Filter = ?');
            $stmt->execute([$filter]);

            $results = [];
            do {
                try {
                    $rows = $stmt->fetchAll(\PDO::FETCH_OBJ);
                    if (empty($rows)) {
                        continue;
                    }
                    if (isset($rows[0]->IncoTermCode)) {
                        $results = $rows;
                        break;
                    }
                } catch (\PDOException $e) {
                    continue;
                }
            } while ($stmt->nextRowset());

            return response()->json(['success' => true, 'data' => $results]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * GET /contract/masters/countries
     */
    public function getCountries(Request $request): JsonResponse
    {
        $filter = $request->input('filter', '');
        try {
            $pdo = DB::connection('sqlsrv')->getPdo();
            $stmt = $pdo->prepare('SET NOCOUNT ON; EXEC dbo.sp_CountryList @Filter = ?');
            $stmt->execute([$filter]);

            $rows = [];
            do {
                try {
                    $fetched = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                    if (!empty($fetched)) {
                        $rows = $fetched;
                        break;
                    }
                } catch (\PDOException $e) {
                    continue;
                }
            } while ($stmt->nextRowset());

            Log::info('getCountries - result', ['count' => count($rows), 'sample' => $rows[0] ?? null]);

            return response()->json($rows);
        } catch (\Exception $e) {
            Log::error('getCountries failed', ['error' => $e->getMessage()]);
            return response()->json([]);
        }
    }

    /**
     * GET /contract/masters/shipment-modes
     */
    public function getShipmentModes(Request $request): JsonResponse
    {
        $filter = $request->input('filter', '');
        try {
            $pdo = DB::connection('sqlsrv')->getPdo();
            $stmt = $pdo->prepare('SET NOCOUNT ON; EXEC dbo.sp_ShipmentMode ?');
            $stmt->execute([$filter]);

            $results = [];
            do {
                try {
                    $rows = $stmt->fetchAll(\PDO::FETCH_OBJ);
                    if (!empty($rows) && isset($rows[0]->Id)) {
                        $results = $rows;
                        break;
                    }
                } catch (\PDOException $e) {
                    continue;
                }
            } while ($stmt->nextRowset());

            return response()->json(['success' => true, 'data' => $results]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * GET /contract/masters/transport-modes
     */
    public function getTransportModes(Request $request): JsonResponse
    {
        $filter = $request->input('filter', '');
        try {
            $pdo = DB::connection('sqlsrv')->getPdo();
            $stmt = $pdo->prepare('SET NOCOUNT ON; EXEC dbo.sp_TransportMode ?');
            $stmt->execute([$filter]);

            $results = [];
            do {
                try {
                    $rows = $stmt->fetchAll(\PDO::FETCH_OBJ);
                    if (!empty($rows) && isset($rows[0]->Id)) {
                        $results = $rows;
                        break;
                    }
                } catch (\PDOException $e) {
                    continue;
                }
            } while ($stmt->nextRowset());

            return response()->json(['success' => true, 'data' => $results]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * GET /contract/masters/equipment-types
     */
    public function getEquipmentTypes(Request $request): JsonResponse
    {
        $filter = $request->input('filter', '');
        try {
            $pdo = DB::connection('sqlsrv')->getPdo();
            $stmt = $pdo->prepare('SET NOCOUNT ON; EXEC dbo.sp_EquipmentType ?');
            $stmt->execute([$filter]);

            $results = [];
            do {
                try {
                    $rows = $stmt->fetchAll(\PDO::FETCH_OBJ);
                    if (!empty($rows) && isset($rows[0]->Code)) {
                        $results = $rows;
                        break;
                    }
                } catch (\PDOException $e) {
                    continue;
                }
            } while ($stmt->nextRowset());

            return response()->json(['success' => true, 'data' => $results]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * GET /contract/masters/charge-codes
     */
    public function getChargeCodes(Request $request): JsonResponse
    {
        $officeCode = $request->input('office_code', '');
        $chargeType = $request->input('charge_type', 'Revenue');
        $filter = $request->input('filter', '');

        if (!$officeCode) {
            return response()->json(['success' => false, 'message' => 'office_code required'], 400);
        }

        try {
            $pdo = DB::connection('sqlsrv')->getPdo();
            $stmt = $pdo->prepare('SET NOCOUNT ON; EXEC dbo.sp_ChargeCode ?, ?, ?');
            $stmt->execute([$officeCode, $chargeType, $filter]);

            $chargeCodes = [];
            do {
                try {
                    $results = $stmt->fetchAll(\PDO::FETCH_OBJ);
                    if (!empty($results) && isset($results[0]->Id)) {
                        $chargeCodes = $results;
                        break;
                    }
                } catch (\PDOException $e) {
                    continue;
                }
            } while ($stmt->nextRowset());

            return response()->json(['success' => true, 'charge_codes' => $chargeCodes]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * GET /contract/masters/based-on
     */
    public function getBasedOnList(): JsonResponse
    {
        try {
            $list = DB::select(
                "SELECT Id, Code, Name
                FROM FITSCMUAT.dbo.Sys_ChargeBasedOn WITH (NOLOCK)
                WHERE IsActive = 1
                ORDER BY Name",
            );
            return response()->json(['success' => true, 'based_on_list' => $list]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * GET /contract/masters/based-on-uom
     */
    public function getBasedOnUOM(Request $request): JsonResponse
    {
        $basedOnCode = $request->input('based_on_code');
        if (!$basedOnCode) {
            return response()->json(['success' => false, 'message' => 'based_on_code required'], 400);
        }

        try {
            $pdo = DB::connection('sqlsrv')->getPdo();
            $stmt = $pdo->prepare('SET NOCOUNT ON; EXEC dbo.sp_ChargeBasedOnUOM ?');
            $stmt->execute([$basedOnCode]);

            $uomList = [];
            do {
                try {
                    $rows = $stmt->fetchAll(\PDO::FETCH_OBJ);
                    if (!empty($rows)) {
                        $firstRow = (array) $rows[0];
                        $firstVal = array_values($firstRow)[0] ?? '';
                        if (is_string($firstVal) && str_starts_with($firstVal, 'Failed')) {
                            continue;
                        }
                        if (isset($rows[0]->Code)) {
                            // Map each row to ensure Code and Name exist
                            $uomList = array_map(function ($row) {
                                // Try common field names for the description
                                $name = $row->Name ?? ($row->UOMName ?? ($row->Description ?? $row->Code));
                                return (object) [
                                    'Code' => $row->Code,
                                    'Name' => $name,
                                ];
                            }, $rows);
                            break;
                        }
                    }
                } catch (\PDOException $e) {
                    continue;
                }
            } while ($stmt->nextRowset());

            return response()->json(['success' => true, 'uom_list' => $uomList]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * GET /contract/masters/currencies
     */
    public function getCurrencies(Request $request): JsonResponse
    {
        $officeId = $request->input('office_id');

        // If no office_id, try to resolve from the authenticated user
        if (!$officeId) {
            $userOfficeCode = $this->getUserOfficeCode();
            $officeId = $this->resolveOfficeIdFromCode($userOfficeCode);
            if (!$officeId) {
                return response()->json(
                    [
                        'success' => false,
                        'message' => 'Could not determine office. Please select an office.',
                    ],
                    400,
                );
            }
        }

        // Convert short code to UUID if needed
        if (preg_match('/^[A-Z]{2}$/', $officeId)) {
            $officeId = $this->resolveOfficeIdFromCode($officeId);
            if (!$officeId) {
                return response()->json(
                    [
                        'success' => false,
                        'message' => "Office code '{$officeId}' not found.",
                    ],
                    404,
                );
            }
        }

        try {
            $pdo = DB::connection('sqlsrv')->getPdo();
            $stmt = $pdo->prepare('SET NOCOUNT ON; EXEC dbo.sp_CurrencyList ?, ?');
            $stmt->execute([$officeId, '']);

            $currencies = [];
            $resultSetCount = 0;

            do {
                $resultSetCount++;
                try {
                    $rows = $stmt->fetchAll(\PDO::FETCH_OBJ);
                    if (empty($rows)) {
                        continue;
                    }

                    $firstVal = array_values((array) $rows[0])[0] ?? '';
                    if (is_string($firstVal) && str_starts_with($firstVal, 'Failed')) {
                        Log::warning('sp_CurrencyList returned failure', [
                            'office_id' => $officeId,
                            'message' => $firstVal,
                        ]);
                        continue;
                    }

                    if (isset($rows[0]->CurrencyID) && isset($rows[0]->CurrencyCode)) {
                        $currencies = $rows;
                        Log::info('sp_CurrencyList returned currencies', [
                            'office_id' => $officeId,
                            'count' => count($currencies),
                        ]);
                        break;
                    }
                } catch (\PDOException $e) {
                    continue;
                }
            } while ($stmt->nextRowset());

            // Fallback if SP returns nothing
            if (empty($currencies)) {
                Log::warning('No currencies returned from sp_CurrencyList, using fallback', [
                    'office_id' => $officeId,
                ]);
                $currencies = [
                    (object) ['CurrencyID' => 'USD', 'CurrencyCode' => 'USD'],
                    (object) ['CurrencyID' => 'KES', 'CurrencyCode' => 'KES'],
                    (object) ['CurrencyID' => 'EUR', 'CurrencyCode' => 'EUR'],
                    (object) ['CurrencyID' => 'GBP', 'CurrencyCode' => 'GBP'],
                ];
            }

            return response()->json([
                'success' => true,
                'currencies' => array_map(
                    fn($c) => [
                        'CurrencyID' => $c->CurrencyID,
                        'CurrencyCode' => $c->CurrencyCode,
                    ],
                    $currencies,
                ),
            ]);
        } catch (\Exception $e) {
            Log::error('getCurrencies failed: ' . $e->getMessage(), ['office_id' => $officeId]);
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Failed to load currencies: ' . $e->getMessage(),
                ],
                500,
            );
        }
    }

    /**
     * Resolve a short office code (e.g., 'KE') to its UUID (Id).
     */
    private function resolveOfficeIdFromCode(string $code): ?string
    {
        $office = DB::connection('sqlsrv')
            ->table('Cust_Office')
            ->whereRaw('RTRIM(Code) = ?', [strtoupper(trim($code))])
            ->where('IsActive', 1)
            ->where('IsDeleted', 0)
            ->select('Id')
            ->first();

        return $office ? $office->Id : null;
    }

    /**
     * GET /contract/masters/exchange-rate
     */
    public function getExchangeRate(Request $request): JsonResponse
    {
        $officeCode = $request->input('office_code');
        $fromCurrency = $request->input('from_currency');
        $toCurrency = $request->input('to_currency', 'KES');
        $dateFor = $request->input('date_for', now()->format('d/M/Y'));

        if (!$officeCode || !$fromCurrency) {
            return response()->json(['success' => false, 'message' => 'office_code and from_currency required'], 400);
        }

        try {
            $result = DB::selectOne('SELECT dbo.fn_GetCurrentExRateUsingCodeandPeriod(?, ?, ?, ?) AS ExchangeRate', [
                $officeCode,
                $fromCurrency,
                $toCurrency,
                $dateFor,
            ]);
            $rate = $result ? (float) $result->ExchangeRate : null;

            if (!$rate || $rate <= 0) {
                return response()->json(
                    [
                        'success' => false,
                        'message' => "No exchange rate found for {$fromCurrency} → {$toCurrency} on {$dateFor}",
                    ],
                    404,
                );
            }

            return response()->json(['success' => true, 'exchange_rate' => $rate]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * GET /contract/masters/locations
     * For port / place of receipt / delivery fields.
     */
    public function getLocations(Request $request): JsonResponse
    {
        $filter = $request->input('filter', '');
        $includeCountry = (int) $request->input('include_country', 0);
        $countryCode = $request->input('country_code', '');

        try {
            $db = DB::getDatabaseName() === 'FITSYSUAT' ? '[FITSCMUAT].dbo.' : '[FITSCM].dbo.';
            $locationFilter = "('Station','Location','Airport','Seaport','InlandPort')";
            $query = "
                SELECT CL.Id AS LocationID, CL.Code AS LocationCode, CL.Name AS LocationName
                FROM {$db}Cust_Location CL WITH (NOLOCK)
                INNER JOIN {$db}sys_locationType SL WITH (NOLOCK)
                    ON SL.Id = CL.LocationTypeId AND SL.Code IN {$locationFilter}
            ";
            $bindings = [];

            if ($includeCountry && $countryCode) {
                $query .= " INNER JOIN {$db}Cust_Country CC WITH (NOLOCK)
                    ON CC.Id = CL.CountryId AND (CC.Code = ? OR CC.Name = ?) ";
                $bindings[] = strtoupper(trim($countryCode));
                $bindings[] = trim($countryCode);
            }

            $query .= ' WHERE CL.IsActive = 1 AND CL.IsDeleted = 0 ';
            if (!empty($filter)) {
                $query .= ' AND (CL.Name LIKE ? OR CL.Code LIKE ? OR CL.UNCode LIKE ?) ';
                $bindings[] = $filter . '%';
                $bindings[] = $filter . '%';
                $bindings[] = $filter . '%';
            }
            $query .= ' ORDER BY CL.Name ';

            $results = DB::select($query, $bindings);
            return response()->json(['success' => true, 'locations' => $results]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // -----------------------------------------------------------------
    // Optional: Service / Process / ShipmentType / MovementType lists
    // (If the SPs exist, add them similarly. I’ll include stubs.)
    // -----------------------------------------------------------------

    /**
     * GET /contract/masters/services
     * Assumes a stored procedure dbo.sp_ServiceList exists.
     */
    // public function getServices(Request $request): JsonResponse
    // {
    //     $list = [
    //         ['Code' => 'Air', 'Name' => 'Air'],
    //         ['Code' => 'Sea', 'Name' => 'Sea'],
    //         ['Code' => 'Road', 'Name' => 'Road'],
    //     ];
    //     return response()->json(['success' => true, 'data' => $list]);
    // }
    public function getServices(Request $request): JsonResponse
    {
        try {
            $list = DB::connection('sqlsrv')
                ->table('Sys_ServiceProcess')
                ->where('IsActive', 1)
                ->where('IsDeleted', 0)
                ->select('ServiceName as Code', 'ServiceName as Name')
                ->distinct()
                ->orderBy('ServiceName')
                ->get();

            return response()->json(['success' => true, 'data' => $list]);
        } catch (\Exception $e) {
            Log::error('getServices failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * GET /contract/masters/processes
     * Query Sys_ServiceProcess table (found in the list)
     */
    /**
     * GET /contract/masters/processes
     * Query distinct active processes from Sys_ServiceProcess
     */
    public function getProcesses(Request $request): JsonResponse
    {
        $serviceCode = $request->input('service');

        try {
            $query = DB::connection('sqlsrv')->table('Sys_ServiceProcess')->where('IsActive', 1)->where('IsDeleted', 0);

            if ($serviceCode) {
                $query->where('ServiceCode', $serviceCode);
            }

            $list = $query
                ->select('ProcessCode as Code', 'ProcessName as Name')
                ->distinct()
                ->orderBy('ProcessName')
                ->get();

            // If no data, fallback to hardcoded values (mirror SP validation).
            // Note: fallback is unfiltered — only hit if the real table returns
            // nothing, so this never silently drops a valid service's processes.
            if ($list->isEmpty()) {
                return $this->fallbackProcesses();
            }

            return response()->json(['success' => true, 'data' => $list]);
        } catch (\Exception $e) {
            \Log::error('getProcesses failed: ' . $e->getMessage(), ['service' => $serviceCode]);
            return $this->fallbackProcesses();
        }
    }

    /**
     * Fallback hardcoded process list (mirrors SP validation)
     */
    private function fallbackProcesses(): JsonResponse
    {
        $list = [
            ['Code' => 'Export', 'Name' => 'Export'],
            ['Code' => 'Import', 'Name' => 'Import'],
            ['Code' => 'Local', 'Name' => 'Local'],
        ];
        return response()->json(['success' => true, 'data' => $list]);
    }

    /**
     * GET /contract/masters/shipment-types
     * Query Sys_ShipmentType table (likely exists)
     */
    public function getShipmentTypes(Request $request): JsonResponse
    {
        try {
            // Sys_ShipmentType has multiple rows sharing the same Code/Name
            // (differentiated only by BookingType, which this dropdown doesn't
            // use) — confirmed via direct query, not a query-construction bug.
            // ->distinct() on the two selected columns collapses those to one
            // row per Code/Name pair.
            $list = DB::connection('sqlsrv')
                ->table('FITSCMUAT.dbo.Sys_ShipmentType')
                ->where('IsActive', 1)
                ->select('Code', 'Name')
                ->distinct()
                ->orderBy('Name')
                ->get();
            return response()->json(['success' => true, 'data' => $list]);
        } catch (\Exception $e) {
            \Log::error('getShipmentTypes failed: ' . $e->getMessage());
            // Fallback if table doesn't exist (unlikely)
            $list = [
                ['Code' => 'FCL', 'Name' => 'FCL'],
                ['Code' => 'LCL', 'Name' => 'LCL'],
                ['Code' => 'Air', 'Name' => 'Air'],
                ['Code' => 'Bulk', 'Name' => 'Bulk'],
            ];
            return response()->json(['success' => true, 'data' => $list]);
        }
    }

    /**
     * GET /contract/masters/movement-types
     * Query Sys_MovementType table (likely exists)
     */
    public function getMovementTypes(Request $request): JsonResponse
    {
        try {
            $list = DB::connection('sqlsrv')
                ->table('FITSCMUAT.dbo.Sys_MovementType')
                ->where('IsActive', 1)
                ->select('Code', 'Name')
                ->distinct()
                ->orderBy('Name')
                ->get();
            return response()->json(['success' => true, 'data' => $list]);
        } catch (\Exception $e) {
            \Log::error('getMovementTypes failed: ' . $e->getMessage());
            $list = [
                ['Code' => 'Port to Port', 'Name' => 'Port to Port'],
                ['Code' => 'Door to Door', 'Name' => 'Door to Door'],
                ['Code' => 'Port to Door', 'Name' => 'Port to Door'],
                ['Code' => 'Door to Port', 'Name' => 'Door to Port'],
            ];
            return response()->json(['success' => true, 'data' => $list]);
        }
    }

    /**
     * GET /contract/masters/charge-categories
     * Query the ChargeCategory table (adjust name if different)
     */
    public function getChargeCategories(Request $request): JsonResponse
    {
        try {
            $pdo = DB::connection('sqlsrv')->getPdo();
            $stmt = $pdo->prepare('SET NOCOUNT ON; EXEC dbo.sp_Contract_ChargeCategoryList');
            $stmt->execute();

            $rows = $stmt->fetchAll(\PDO::FETCH_NUM);
            $list = array_map(fn($r) => ['Code' => $r[0], 'Name' => $r[0]], $rows);

            return response()->json(['success' => true, 'data' => $list]);
        } catch (\Exception $e) {
            Log::error('getChargeCategories failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * GET /contract/masters/service-classes
     */
    public function getServiceClasses(Request $request): JsonResponse
    {
        try {
            $list = DB::connection('sqlsrv')
                ->table('FITSCMUAT.dbo.Cust_ServiceClass')
                ->where('IsActive', 1)
                ->select('Code', 'Description as Name')
                ->distinct()
                ->orderBy('Description')
                ->get();
            return response()->json(['success' => true, 'data' => $list]);
        } catch (\Exception $e) {
            Log::error('getServiceClasses failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * GET /contracts/search
     *
     * UsedFor=List   -> query params: none required, returns all Authorized
     *                   contracts currently within their effective date range.
     * UsedFor=Parameter -> query params: service, process (required), plus
     *                   coo/cod (conditionally required per SP rules — see
     *                   ContractListMapper docblock) and any optional filters.
     *                   Returns results bifurcated into exact/nearest based on
     *                   MatchScore vs. the max possible score for the filters
     *                   actually supplied.
     */

    public function searchServices(Request $request, ContractListClient $client): JsonResponse
    {
        $service = $request->input('service');
        $process = $request->input('process');
        $origin = $request->input('origin'); // maps to COO
        $destination = $request->input('destination'); // maps to COD
        $incoterm = $request->input('incoterm', '');
        $pol = $request->input('pol', '');
        $pod = $request->input('pod', '');
        $por = $request->input('por', '');
        $podel = $request->input('podel', ''); // maps to POE (Place Of Delivery)

        if (!$service || !$process || !$origin || !$destination) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'service, process, origin and destination are required.',
                ],
                422,
            );
        }

        $mapperData = [
            'service' => $service,
            'process' => $process,
            'coo' => $origin,
            'cod' => $destination,
            'inco_term' => $incoterm,
            'pol' => $pol,
            'pod' => $pod,
            'por' => $por,
            'poe' => $podel,
        ];

        $payload = ContractListMapper::build('Parameter', $mapperData, auth()->user()->name ?? '');

        try {
            $result = $client->call($payload);
        } catch (\RuntimeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }

        if (!$result['ok']) {
            return response()->json(['success' => false, 'message' => $result['message']], 422);
        }

        $maxScore = ContractListMapper::maxPossibleScore($mapperData);

        // sp_Contract_List's Parameter-mode SELECT only returns
        // (Id, ContractNo, ContractName, ServiceFilterID, MatchScore) — it
        // does not return Service/Process/COO/COD/IncoTerm/POL/POD/POR/POE,
        // which the frontend needs for auto-fill on selection. Supplemental
        // lookup fills that gap using the same table/columns confirmed in
        // sp_Contractinfo_ServiceDetails. If the SP is later extended to
        // return these columns directly, this supplemental query can be
        // removed and the SP's own row data used instead.
        $serviceFilterIds = array_column($result['rows'], 3); // index 3 = ServiceFilterID

        $detailRows = empty($serviceFilterIds)
            ? collect()
            : DB::connection('sqlsrv')
                ->table('Contract_ContractServiceAndFilter')
                ->whereIn('Id', $serviceFilterIds)
                ->get()
                ->keyBy('Id');

        $exact = [];
        $other = [];

        foreach ($result['rows'] as $row) {
            $serviceFilterId = $row[3] ?? null;
            $matchScore = isset($row[4]) ? (int) $row[4] : 0;
            $detail = $detailRows->get($serviceFilterId);

            $item = [
                'ServiceFilterId' => $serviceFilterId,
                'ContractNo' => $row[1] ?? null,
                'ContractName' => $row[2] ?? null,
                'Service' => $detail->Service ?? null,
                'Process' => $detail->Process ?? null,
                'COO' => $detail->COO ?? null,
                'COD' => $detail->COD ?? null,
                'IncoTerm' => $detail->IncoTerm ?? null,
                'POL' => $detail->POL ?? null,
                'POD' => $detail->POD ?? null,
                'POR' => $detail->POR ?? null,
                'PlaceOfDelivery' => $detail->POE ?? null,
            ];

            if ($matchScore === $maxScore) {
                $exact[] = $item;
            } else {
                $other[] = $item;
            }
        }

        return response()->json([
            'success' => true,
            'exact_matches' => $exact,
            'other_matches' => $other,
        ]);
    }

    public function updateHeader(StoreContractRequest $request, string $contract): JsonResponse
    {
        $validated = $request->validated();

        $headerPayload = [
            'OperationType' => 'ContractInfo',
            'SubmittedDatetime' => $this->submittedDatetime(),
            'SubmittedBy' => auth()->user()->name ?? 'system',
            'Action' => 'Update',
            'ContractID' => $contract,
            'ContractDate' => now()->format('d/m/Y'),
            'ContactType' => $validated['contract_type'],
            'OfficeCode' => $this->getUserOfficeCode(),
            'ClientCode' => '',
            'EffectiveFrom' => \Carbon\Carbon::parse($validated['effective_from'])->format('Ymd'),
            'EffectiveTo' => \Carbon\Carbon::parse($validated['effective_to'])->format('Ymd'),
            'ContractName' => $validated['contract_name'],
            'Remark' => $validated['remark'] ?? '',
        ];

        $result = $this->callContractSP('sp_Contractinfo', $headerPayload);

        if (!$result['success']) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Failed to update contract: ' . ($result['message'] ?? 'Unknown error'),
                ],
                422,
            );
        }

        return response()->json(['success' => true, 'message' => $result['message'] ?? 'Contract updated.']);
    }

    public function updateService(Request $request, string $contract, string $serviceFilterId): JsonResponse
    {
        $service = $request->all();

        $servicePayload = [
            'OperationType' => 'ContractServiceDetails',
            'Action' => 'Update',
            'SubmittedBy' => auth()->user()->name ?? 'system',
            'SubmittedDatetime' => $this->submittedDatetime(),
            'ContractInfoID' => $contract,
            'ServiceAndFilterID' => $serviceFilterId,
            'Service' => $service['service'] ?? '',
            'Process' => $service['process'] ?? '',
            'IncoTerm' => $service['incoterm'] ?? '',
            'COO' => $service['countryOrigin'] ?? '',
            'COD' => $service['countryDestination'] ?? '',
            'POR' => $service['placeOfReceipt'] ?? '',
            'POE' => $service['placeOfDelivery'] ?? '',
            'POL' => $service['pol'] ?? '',
            'POD' => $service['pod'] ?? '',
            'ServiceClass' => $service['serviceClass'] ?? '',
            'ShipmentMode' => $service['shipmentMode'] ?? '',
            'ShipmentType' => $service['shipmentType'] ?? '',
            'MovementType' => $service['movementType'] ?? '',
        ];

        $result = $this->callContractSP('sp_Contractinfo_ServiceDetails', $servicePayload);

        if (!$result['success']) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Failed to update service: ' . ($result['message'] ?? 'Unknown error'),
                ],
                422,
            );
        }

        return response()->json(['success' => true, 'message' => 'Service updated.']);
    }

    /**
     * GET /contracts/{contract}/services/{serviceFilterId}/formula-fields?is_revenue=1
     *
     * sp_Contract_FormulaList has no @OutputJson param — it returns a plain
     * SELECT. On validation failure it returns a single row/column containing
     * an error message instead of the field list; both shapes are one column,
     * so success/failure is distinguished by matching the SP's known error
     * phrases rather than assuming any non-empty result is success.
     */
    public function getFormulaFields(Request $request, string $contract, string $serviceFilterId): JsonResponse
    {
        $isRevenue = $request->input('is_revenue');

        if ($isRevenue === null) {
            return response()->json(['success' => false, 'message' => 'is_revenue is required'], 400);
        }

        try {
            $pdo = DB::connection('sqlsrv')->getPdo();
            $stmt = $pdo->prepare(
                'SET NOCOUNT ON; EXEC dbo.sp_Contract_FormulaList @IsRevenue = ?, @ContractID = ?, @ServiceFilterID = ?',
            );
            $stmt->execute([(int) $isRevenue, $contract, $serviceFilterId]);

            $rows = $stmt->fetchAll(\PDO::FETCH_NUM);

            $isErrorRow =
                count($rows) === 1 &&
                is_string($rows[0][0] ?? null) &&
                (str_starts_with($rows[0][0], 'Error Occured') ||
                    str_contains($rows[0][0], 'mandatory') ||
                    str_contains($rows[0][0], 'not present in the system'));

            if ($isErrorRow) {
                return response()->json(['success' => false, 'message' => $rows[0][0]], 422);
            }

            $fields = array_map(fn($r) => $r[0], $rows);

            return response()->json(['success' => true, 'data' => $fields]);
        } catch (\Exception $e) {
            Log::error('getFormulaFields failed: ' . $e->getMessage(), [
                'contract' => $contract,
                'service_filter_id' => $serviceFilterId,
            ]);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
