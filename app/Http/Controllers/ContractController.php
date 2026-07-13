<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreContractRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

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
    public function create(Request $request)
    {
        $contractId = $request->query('draft', Str::uuid()->toString());
        $existing = Session::get($this->sessionKey($contractId));

        $stats = [
            'pending' => 0,
            'pending_approval' => 0,
            'authorized' => 0,
            'renewal_due' => 0,
            'total' => 0,
        ];

        return view('contracts.create', compact('contractId', 'existing', 'stats'));
    }

    /**
     * Persist the full Contract -> Services[] -> Charges[] tree for this draft.
     * Services/Charges arrive as JSON strings (client builds the nested state
     * in JS, same pattern as ContractTemplateController::store).
     */
    public function store(StoreContractRequest $request, string $contract): JsonResponse
    {
        $validated = $request->validated();

        $services = json_decode($request->input('services', '[]'), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Services payload was not valid JSON. Nothing was saved.',
                ],
                422,
            );
        }

        if (!is_array($services)) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Services payload must be an array. Nothing was saved.',
                ],
                422,
            );
        }

        Session::put($this->sessionKey($contract), [
            // 'contract_no' => $validated['contract_no'],
            'contract_type' => $validated['contract_type'],
            'status' => $validated['status'],
            'effective_from' => $validated['effective_from'],
            'effective_to' => $validated['effective_to'],
            'description' => $validated['description'] ?? null,
            'remark' => $validated['remark'] ?? null,
            'services' => $services,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Contract draft saved.',
            'data' => ['contract_id' => $contract],
        ]);
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

            return response()->json($rows);
        } catch (\Exception $e) {
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
        if (!$officeId) {
            return response()->json(['success' => false, 'message' => 'office_id required'], 400);
        }

        try {
            $pdo = DB::connection('sqlsrv')->getPdo();
            $stmt = $pdo->prepare('SET NOCOUNT ON; EXEC dbo.sp_CurrencyList ?, ?');
            $stmt->execute([$officeId, '']);

            $currencies = [];
            do {
                try {
                    $rows = $stmt->fetchAll(\PDO::FETCH_OBJ);
                    if (!empty($rows) && isset($rows[0]->CurrencyID)) {
                        $currencies = $rows;
                        break;
                    }
                } catch (\PDOException $e) {
                    continue;
                }
            } while ($stmt->nextRowset());

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
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
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
}
