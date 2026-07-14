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

    /**
     * Pulls real Authorized-contract data via sp_Contract_List (UsedFor=List).
     *
     * KNOWN LIMITATION: sp_Contract_List's List-mode query is hardcoded to
     * Status = 'Authorized' — it cannot return Pending or Pending Approval
     * contracts under any input. Until a status parameter is added to the SP
     * (or a separate endpoint exists for non-Authorized statuses), those two
     * cards — and "Total" as a true cross-status count — remain at 0 / equal
     * to the Authorized count. This is not a bug in this method; it's a
     * ceiling imposed by the SP as currently written.
     */
    private function computeDashboardStats(ContractListClient $client): array
    {
        $stats = [
            'pending' => 0, // SP cannot return this status — see docblock
            'pending_approval' => 0, // SP cannot return this status — see docblock
            'authorized' => 0,
            'renewal_due' => 0,
            'total' => 0, // == authorized count only, not a true cross-status total
        ];

        $payload = ContractListMapper::build('List', [], auth()->user()->name ?? '');

        try {
            $result = $client->call($payload);
        } catch (\RuntimeException $e) {
            Log::error('Dashboard stats - sp_Contract_List call failed', ['error' => $e->getMessage()]);
            return $stats; // fail safe to zeros rather than breaking the page
        }

        if (!$result['ok']) {
            Log::warning('Dashboard stats - sp_Contract_List returned failure', ['message' => $result['message']]);
            return $stats;
        }

        $authorizedCount = count($result['rows']);
        $renewalDueCount = 0;
        $today = Carbon::today();

        foreach ($result['rows'] as $row) {
            $effectiveTo = $row[4] ?? null; // index 4 = EffectiveTo, per List-mode column order
            if (!$effectiveTo) {
                continue;
            }

            $daysUntilExpiry = $today->diffInDays(Carbon::parse($effectiveTo), false);
            if ($daysUntilExpiry >= 0 && $daysUntilExpiry <= 10) {
                $renewalDueCount++;
            }
        }

        $stats['authorized'] = $authorizedCount;
        $stats['renewal_due'] = $renewalDueCount;
        $stats['total'] = $authorizedCount;

        return $stats;
    }

    public function store(StoreContractRequest $request, string $contract): JsonResponse
    {
        $validated = $request->validated();
        $services = json_decode($request->input('services', '[]'), true);

        if (json_last_error() !== JSON_ERROR_NONE || !is_array($services)) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Services payload must be valid JSON array.',
                ],
                422,
            );
        }

        $draft = Session::get($this->sessionKey($contract));
        $action = isset($draft['contract_id']) ? 'Update' : 'Add';

        $headerPayload = [
            'OperationType' => 'ContractInfo',
            'SubmittedDatetime' => now()->format('Y-m-d H:i:s'),
            'SubmittedBy' => auth()->user()->name ?? 'system',
            'Action' => $action,
            'ContractID' => $draft['contract_id'] ?? null,
            'ContractDate' => now()->format('d/m/Y'),
            'ContactType' => $validated['contract_type'],
            'OfficeCode' => $this->getUserOfficeCode(),
            'ClientCode' => '',
            'EffectiveFrom' => \Carbon\Carbon::parse($validated['effective_from'])->format('d/m/Y'),
            'EffectiveTo' => \Carbon\Carbon::parse($validated['effective_to'])->format('d/m/Y'),
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

        DB::beginTransaction();

        try {
            foreach ($services as $service) {
                $servicePayload = [
                    'OperationType' => 'ContractServiceDetails',
                    'Action' => 'Add',
                    'SubmittedBy' => auth()->user()->name ?? 'system',
                    'SubmittedDatetime' => now()->format('Y-m-d H:i:s'),
                    'ContractInfoID' => $contractId, // ✅ changed
                    'ServiceAndFilterID' => null, // ✅ changed
                    'Service' => $service['service'],
                    'Process' => $service['process'],
                    'IncoTerm' => $service['incoterm'] ?? '',
                    'COO' => $service['countryOrigin'],
                    'COD' => $service['countryDestination'],
                    'POR' => $service['placeOfReceipt'] ?? '',
                    'POE' => $service['placeOfDelivery'] ?? '',
                    'POL' => $service['pol'] ?? '',
                    'POD' => $service['pod'] ?? '',
                    'ServiceClass' => $service['serviceClass'] ?? '',
                    'ShipmentMode' => $service['shipmentMode'],
                    'ShipmentType' => $service['shipmentType'],
                    'MovementType' => $service['movementType'] ?? '',
                    // Add any other fields the SP expects (check its WITH clause)
                ];

                $serviceResult = $this->callContractSP('sp_Contractinfo_ServiceDetails', $servicePayload);

                if (!$serviceResult['success']) {
                    throw new \Exception('Failed to save service: ' . ($serviceResult['message'] ?? 'Unknown error'));
                }

                $serviceFilterId = $serviceResult['service_filter_id'];

                if (!empty($service['charges'])) {
                    foreach ($service['charges'] as $charge) {
                        $chargePayload = [
                            'OperationType' => 'ServiceBasedCharges',
                            'SubmittedDatetime' => now()->format('Y-m-d H:i:s'),
                            'SubmittedBy' => auth()->user()->name ?? 'system',
                            'Action' => 'Add',
                            'ContractInfoID' => $contractId,
                            'ServiceAndFilterID' => $serviceFilterId,
                            'ServiceChargeID' => null, // null for Add
                            'ChargeCode' => $charge['chargeCode'],
                            'ChargeBasedon' => $charge['chargeBasedOn'], // note lowercase 'o'
                            'ChargeBasedOnUOM' => $charge['chargeBasedOnUOM'] ?? '',
                            'ChargeCurr' => $charge['chargeCurr'],
                            'MinAmount' => $charge['minAmount'] ?? 0.0,
                            'MaxAmount' => $charge['maxAmount'] ?? 0.0,
                            'RateType' => $charge['rateType'],
                            'Rate' => $charge['rate'] ?? 0.0,
                            'Formula' => $charge['formula'] ?? '',
                            'RateSlabJson' => $charge['rateSlab'] ? json_encode($charge['rateSlab']) : '',
                            'Comments' => '',
                        ];

                        $chargeResult = $this->callContractSP('sp_Contractinfo_ServiceCharges', $chargePayload);

                        if (!$chargeResult['success']) {
                            throw new \Exception(
                                'Failed to save charge: ' . ($chargeResult['message'] ?? 'Unknown error'),
                            );
                        }
                    }
                }
            }

            DB::commit();

            Session::forget($this->sessionKey($contract));

            return response()->json([
                'success' => true,
                'message' => "Contract {$contractNo} saved successfully.",
                'data' => [
                    'contract_no' => $contractNo,
                    'contract_id' => $contractId,
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Contract save failed: ' . $e->getMessage());
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Failed to save contract: ' . $e->getMessage(),
                ],
                500,
            );
        }
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
                "SELECT Id, Code, Name, BasedOn
                 FROM Cust_ChargecodeBasedOn WITH (NOLOCK)
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
                            $uomList = $rows;
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
    public function getServices(Request $request): JsonResponse
    {
        $list = [
            ['Code' => 'Air', 'Name' => 'Air'],
            ['Code' => 'Sea', 'Name' => 'Sea'],
            ['Code' => 'Road', 'Name' => 'Road'],
        ];
        return response()->json(['success' => true, 'data' => $list]);
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
        try {
            $list = DB::connection('sqlsrv')
                ->table('Sys_ServiceProcess')
                ->where('IsActive', 1)
                ->where('IsDeleted', 0)
                ->select('ProcessCode as Code', 'ProcessName as Name')
                ->distinct()
                ->orderBy('ProcessName')
                ->get();

            // If no data, fallback to hardcoded values (mirror SP validation)
            if ($list->isEmpty()) {
                return $this->fallbackProcesses();
            }

            return response()->json(['success' => true, 'data' => $list]);
        } catch (\Exception $e) {
            \Log::error('getProcesses failed: ' . $e->getMessage());
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
            $list = DB::connection('sqlsrv')
                ->table('FITSCMUAT.dbo.Sys_ShipmentType')
                ->where('IsActive', 1)
                ->select('Code', 'Name')
                ->orderBy('Name')
                ->get();
            return response()->json(['success' => true, 'data' => $list]);
        } catch (\Exception $e) {
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
                ->orderBy('Name')
                ->get();
            return response()->json(['success' => true, 'data' => $list]);
        } catch (\Exception $e) {
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
        // Try common table names
        $tables = ['Cust_ChargeCategory', 'Sys_ChargeCategory', 'ChargeCategory'];
        $data = null;

        foreach ($tables as $table) {
            try {
                $data = DB::connection('sqlsrv')
                    ->table('FITSCMUAT.dbo.' . $table)
                    ->where('IsActive', 1)
                    ->select('Code', 'Name')
                    ->orderBy('Name')
                    ->get();
                if ($data->isNotEmpty()) {
                    break;
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        if ($data && $data->isNotEmpty()) {
            return response()->json(['success' => true, 'data' => $data]);
        }

        // Fallback if no table found
        $list = [
            ['Code' => 'Freight', 'Name' => 'Freight'],
            ['Code' => 'Handling', 'Name' => 'Handling'],
            ['Code' => 'Documentation', 'Name' => 'Documentation'],
            ['Code' => 'Insurance', 'Name' => 'Insurance'],
            ['Code' => 'Other', 'Name' => 'Other'],
        ];
        return response()->json(['success' => true, 'data' => $list]);
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
}
