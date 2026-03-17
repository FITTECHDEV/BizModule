<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreClientPartyRequest;
use App\Http\Requests\UpdateClientPartyRequest;
use App\Mail\RejectEntity;
use App\Models\ClientParty;
use App\Models\ClientPartyAttahment;
use App\Models\EntityHistory;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
// Correct import

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use JeroenNoten\LaravelAdminLte\Components\Form\Select;
use RealRashid\SweetAlert\Facades\Alert;

class ClientPartyController extends Controller
{
    public $clientParty;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->clientParty = new ClientParty;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $title = 'My Client Party Requests';
        $user = Auth::user();
        // authorization
        if (! \request()->user()->can('view-client-party')) {
            abort(403, 'You are not allowed to view this page.');
        }

        $clientParty = $this->clientParty;

        return view('client-party.index', compact('clientParty', 'title'));
    }

    public function create()
    {
        if (! \request()->user()->hasPermissionTo('request-client-party')) {
            abort(403, 'You are not allowed to view this page.');
        }

        $countries = $this->getCountries();
        $offices = $this->getOffices();
        $currencyList = $this->getCurrencyList();
        $licenceTypes = $this->getLicenceType();
        $tarrifs = $this->getTarrif();
        $incoterms = $this->getIncoTerms();

        $payTerms = [];
        $user = Auth::user();
        $officeCode = $user->OfficeCode ?? null;
        Log::info('User OfficeCode in create:', ['userID' => $user->id, 'officeCode' => $officeCode]);

        if (! empty($officeCode)) {
            try {
                $response = DB::select(
                    'SET NOCOUNT ON;
                 DECLARE @OfficeCode AS VARCHAR(5);
                 EXEC [dbo].[sp_PaymentTerms] @OfficeCode = ?',
                    [$officeCode]
                );
                if (empty($response) || (isset($response[0]->Failed))) {
                    Log::warning('sp_PaymentTerms returned no data or error', ['response' => $response, 'officeCode' => $officeCode]);
                } else {
                    $payTerms = array_map(function ($term) {
                        return [
                            'Id' => $term->Id,
                            'CreditDays' => $term->Code, // Use Code as CreditDays
                        ];
                    }, $response);
                }
                Log::info('Fetched payTerms in create:', ['payTerms' => $payTerms, 'officeCode' => $officeCode]);
            } catch (\Illuminate\Database\QueryException $e) {
                Log::error('sp_PaymentTerms Error in create: ' . $e->getMessage(), [
                    'officeCode' => $officeCode,
                    'sql' => $e->getSql(),
                ]);
            }
        } else {
            Log::warning('No OfficeCode for user in create', ['userID' => $user->id]);
        }

        Log::info('currencyList in create:', ['currencyList' => $currencyList]);

        $debtors = '';
        $vendors = '';

        return view(
            'client-party.create',
            compact('payTerms', 'incoterms', 'countries', 'offices', 'currencyList', 'licenceTypes', 'tarrifs', 'debtors', 'vendors')
        );
    }

    public function getDebtorVendor($type = null)
    {
        if (\request()->ajax()) {
            $query = \request()->all();
            $response = DB::select(
                'SET NOCOUNT ON;
                Declare  @OfficeID as Uniqueidentifier,
                @PartyType as Varchar(60),
                @Filter as Varchar(50)
                Exec sp_PartyList
                @OfficeID = ?, @PartyType = ?, @Filter = ?',
                [$query['officeID'], $type, $query['query']]
            );
            $debtorVendors = [];
            if (count($response) > 0) {
                foreach ($response as $resp) {
                    if (isset($resp->PartyID)) {
                        $debtorVendors[] = [
                            'value' => $resp->PartyID,
                            'label' => $resp->PartyName,
                            'code' => $resp->PartyCode,
                        ];
                    }
                }
            }

            return response()->json($debtorVendors);
        }
    }

    /**
     * Get country listing
     *
     * @param  \App\Http\Requests\getDataClientPartyRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function getCountries()
    {
        $response = DB::select(
            'SET NOCOUNT ON;
            Declare  @Filter as Varchar(50)
            Exec sp_CountryList @Filter = ?',
            ['']
        );
        if (\request()->ajax()) {
            return response()->json($response);
        } else {
            return $response;
        }
    }

    public function getCities()
    {
        if (\request()->ajax()) {
            $request = \request()->all();
            $response = DB::select(
                'SET NOCOUNT ON;
                Declare  @Country as char(2),
                @Filter as Varchar(50)
                Exec sp_StateList
                @country = ?, @Filter = ?',
                [$request['country_code'], $request['query']]
            );
            $cities = [];
            if (count($response) > 0) {
                foreach ($response as $resp) {
                    if (isset($resp->CountryID)) {
                        $cities[] = [
                            'value' => $resp->CountryID,
                            'label' => $resp->CountryName,
                            'code' => $resp->CountCode,
                        ];
                    }
                }
            }

            return $cities;
        }
    }

    public function getProvinces()
    {
        $request = \request()->all();
        $response = DB::select(
            'SET NOCOUNT ON;
            Declare  @Country as char(2)
            Declare  @Filter as Varchar(50)
            Exec sp_StateList
            @country = ?,
            @Filter = ?',
            [$request['country_code'], $request['query']]
        );
        $provinces = [];
        if (count($response) > 0) {
            foreach ($response as $resp) {
                if (isset($resp->CountryID)) {
                    $provinces[] = [
                        'value' => $resp->CountryID,
                        'label' => $resp->CountryName,
                        'code' => $resp->CountCode,
                    ];
                }
            }
        }

        return response()->json($provinces);
    }

    public function getTarrif($var = null)
    {
        $query = '';
        if (\request()->ajax()) {
            $query = \request()->all()['query'];
        } else {
            $query = $var;
        }
        $response = DB::select(
            'SET NOCOUNT ON;
            Declare @office as varchar(60),
            @officeID as  Uniqueidentifier
            Exec sp_TariffList
            @office = ?,
            @officeID = ?',
            ['Kenya - UPS', $query]
        );
        if (\request()->ajax()) {
            return response()->json($response);
        } else {
            return $response;
        }
    }

    public function getPOLPOD($locationType = null)
    {
        $request = \request()->all();
        $response = DB::select(
            'SET NOCOUNT ON;
            Declare @LocationType as Char(3),
            @IncludeCountry as Bit,
            @CountryCode as Char(2),
            @Filter as Varchar(50)
            Exec [dbo].[sp_LocationList]
            @LocationType = ?,
            @IncludeCountry = ?,
            @CountryCode = ?,
            @Filter = ?',
            [$locationType, $request['includeCountry'], $request['includeCode'], $request['query']]
        );
        $locations = [];
        if (count($response) > 0) {
            foreach ($response as $location) {
                if (isset($location->LocationID)) {
                    $locations[] = [
                        'value' => $location->LocationID,
                        'label' => $location->LocationName,
                    ];
                }
            }
        }

        return response()->json($locations);
    }

    public function getBaseCurrency($officeID = null)
    {
        $query = '';
        if (\request()->ajax()) {
            $query = \request()->all()['query'];
        } else {
            $query = $officeID;
        }
        $response = DB::select(
            'SET NOCOUNT ON;
                Declare @OfficeID as Uniqueidentifier
                Exec [dbo].[sp_BaseOfficeCurrency]
                @officeID = ?',
            [$query]
        );
        $baseCurrency = [];
        if (count($response) > 0) {
            foreach ($response as $location) {
                if (isset($location->BaseCurrencyID)) {
                    $baseCurrency[] = [
                        'value' => $location->BaseCurrencyID,
                        'label' => $location->BaseCurrencyName,
                        'code' => $location->BaseCurrencyCode,
                    ];
                }
            }
        }

        return response()->json($baseCurrency);
    }

    public function getDebtorVendorList($officeCode = null)
    {
        $query = '';
        if (\request()->ajax()) {
            $query = \request()->all()['query'];
        } else {
            $query = $officeCode;
        }
        $response = DB::select(
            'SET NOCOUNT ON;
                Declare @OfficeID as Uniqueidentifier
                Exec [dbo].[sp_EntityType]
                @OfficeCode = ?',
            [$query]
        );
        $v = [];
        $d = [];
        if (count($response) > 0) {
            foreach ($response as $key => $row) {
                ($row->Type == 'Vendor') ? array_push($v, $row->Code) : array_push($d, $row->Code);
            }
        }

        return response()->json(['Vendors' => $v, 'Debtors' => $d]);
    }

    public function autocompleteClient(Request $request)
    {
        $search = \request()->all()['query'];
        $results = ClientParty::where('OperationalEntityType', 'Client without Debtor or Vendor')
            ->where('Name', 'LIKE', "%{$search}%")->pluck('Name');

        return response()->json($results);
    }

    public function fetchClientDetails($companyName)
    {
        $client = ClientParty::where('OperationalEntityType', 'Client without Debtor or Vendor')->where('Name', $companyName)->first();
        if ($client) {
            $clientData = $client->only(['Name', 'Address_Line1', 'Address_CountryId', 'Address_CountryCode', 'Cont_Bch_PersonName_FirstName', 'Cont_Bch_PersonName_LastName', 'Cont_Bch_PhoneNumbers', 'Office', 'SalesMgrOfficeFunMapName', 'AccountMgrOfficeFunMapName', 'CreditLimitCurrencyCode', 'CreditDays', 'CreditLimit', 'LicenceTypeValue', 'LicenceTypeID']);

            return response()->json($clientData);
        }

        return response()->json(['message' => 'Client not found'], 404);
    }

    public function getClients($type = null)
    {
        $clients = [];
        $query = \request()->all()['query'];
        if ($type == 'master') {
            $response = DB::select(
                'SET NOCOUNT ON;
                Declare @filter varchar(60)
                Exec sp_ClientList
                @filter = ?',
                [$query]
            );
            if (count($response) > 0) {
                foreach ($response as $resp) {
                    if (isset($resp->ClientID)) {
                        $clients[] = [
                            'value' => $resp->ClientID,
                            'label' => $resp->ClientName,
                            'code' => $resp->ClientCode,
                        ];
                    }
                }
            }
        } elseif ($type == 'local') {
            $results = ClientParty::select('name', 'Id', 'code')
                ->where('OperationalEntityType', 'Client + Shipper + Consignee')
                ->where('Name', 'LIKE', '%' . $query . '%')->get();
            if (count($results) > 0) {
                foreach ($results as $resp) {
                    if (isset($resp->Id)) {
                        $clients[] = [
                            'value' => $resp->Id,
                            'label' => $resp->name,
                            'code' => $resp->Code,
                        ];
                    }
                }
            }
        }

        return $clients;
    }

    public function getOffices()
    {
        $response = DB::select(
            'SET NOCOUNT ON;
                Exec sp_OfficeList'
        );
        if (\request()->ajax()) {
            return response()->json($response);
        }

        return $response;
    }

    public function getLicenceType()
    {
        $response = DB::select(
            'SET NOCOUNT ON;
                Exec [dbo].[sp_LicenseType]'
        );
        if (\request()->ajax()) {
            return response()->json($response);
        }

        return $response;
    }

    public function getPartyEntity()
    {
        $request = \request()->all();
        $response = DB::select(
            'SET NOCOUNT ON;
            Declare @OfficeID as Uniqueidentifier,
            @PartyType as Varchar(60),
            @Filter as Varchar(50)
            Exec [dbo].[sp_PartyList]
            @OfficeID = ?,
            @PartyType = ?,
            @Filter = ?',
            [$request['officeID'], $request['partyType'], $request['query']]
        );
        $partyList = [];
        if (count($response) > 0) {
            foreach ($response as $resp) {
                if (isset($resp->PartyID)) {
                    $partyList[] = [
                        'value' => $resp->PartyID,
                        'label' => $resp->PartyName,
                        'code' => $resp->PartyCode,
                    ];
                }
            }
        }

        return response()->json($partyList);
    }

    public function getIncoTerms()
    {
        $response = DB::select(
            'SET NOCOUNT ON;
            Declare @Filter as Varchar(50)
            Exec [dbo].[sp_IncoTerms]
            @Filter = ?',
            ['']
        );

        return $response;
    }

    //     public function getPaymentTermList(Request $request)
    // {
    //     try {
    //         $officeCode = $request->input('office_code');

    //         Log::info('getPaymentTermList called:', ['officeCode' => $officeCode]);

    //         if (empty($officeCode)) {
    //             Log::warning('Office code not provided for sp_PaymentTerms');
    //             return response()->json(['error' => 'Office code is required'], 400);
    //         }

    //         $response = DB::select(
    //             'SET NOCOUNT ON;
    //              DECLARE @OfficeCode AS VARCHAR(5);
    //              EXEC [dbo].[sp_PaymentTerms] @OfficeCode = ?',
    //             [$officeCode]
    //         );

    //         $payTerms = empty($response) || (isset($response[0]->Failed)) ? [] : array_map(function ($term) {
    //             return [
    //                 'Id' => $term->Id,
    //                 'CreditDays' => $term->Code // Use Code as CreditDays
    //             ];
    //         }, $response);

    //         Log::info('sp_PaymentTerms response:', ['payTerms' => $payTerms, 'officeCode' => $officeCode]);
    //         return response()->json($payTerms);
    //     } catch (\Illuminate\Database\QueryException $e) {
    //         Log::error('sp_PaymentTerms Error in getPaymentTermList: ' . $e->getMessage(), [
    //             'officeCode' => $officeCode,
    //             'sql' => $e->getSql()
    //         ]);
    //         return response()->json(['error' => 'Failed to retrieve payment terms'], 500);
    //     }
    // }
    public function getPaymentTermList($officeCode = null, ?Request $request = null)
    {
        try {
            // If called via AJAX, get officeCode from request
            if ($request && \request()->ajax()) {
                $officeCode = $request->input('office_code');
            }

            Log::info('getPaymentTermList called:', ['officeCode' => $officeCode]);

            if (empty($officeCode)) {
                Log::warning('Office code not provided for sp_PaymentTerms');
                if ($request && \request()->ajax()) {
                    return response()->json(['error' => 'Office code is required'], 400);
                }

                return []; // Return empty array for non-AJAX calls
            }

            $response = DB::select(
                'SET NOCOUNT ON;
             DECLARE @OfficeCode AS VARCHAR(5);
             EXEC [dbo].[sp_PaymentTerms] @OfficeCode = ?',
                [$officeCode]
            );

            $payTerms = empty($response) || (isset($response[0]->Failed)) ? [] : array_map(function ($term) {
                return [
                    'Id' => $term->Id,
                    'CreditDays' => $term->Code, // Use Code as CreditDays
                ];
            }, $response);

            Log::info('sp_PaymentTerms response:', ['payTerms' => $payTerms, 'officeCode' => $officeCode]);

            if ($request && \request()->ajax()) {
                return response()->json($payTerms);
            }

            return $payTerms; // Return array for non-AJAX calls
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('sp_PaymentTerms Error in getPaymentTermList: ' . $e->getMessage(), [
                'officeCode' => $officeCode,
                'sql' => $e->getSql(),
            ]);
            if ($request && \request()->ajax()) {
                return response()->json(['error' => 'Failed to retrieve payment terms'], 500);
            }

            return []; // Return empty array for non-AJAX calls
        }
    }

    public function getCurrencyList($var = null)
    {
        $query = '';
        if (\request()->ajax()) {
            $query = \request()['query'];
        } else {
            $query = $var;
        }
        if (! empty($query)) {
            $response = DB::select(
                'SET NOCOUNT ON;
                Declare
                @OfficeID as Uniqueidentifier,
                @Filter as Varchar(50)
                Exec [dbo].[sp_CurrencyList]
                @OfficeID = ?,
                @Filter = ?',
                [$query, '']
            );
        } else {
            $response = [];
        }

        return $response;
    }

    public function salesAccountPCI($var = null)
    {
        if (\request()->ajax()) {
            $query = \request()->all();
            $response = DB::select(
                'SET NOCOUNT ON;
                Declare @OfficeID as Uniqueidentifier,
                @PICType as Char(1),
                @Filter as Varchar(50)
                Exec [dbo].[sp_PersonInCharge]
                @OfficeID = ?,
                @PICType = ?,
                @Filter = ?',
                [$query['officeID'], $var, $query['query']]
            );
            $infos = [];
            if (count($response) > 0) {
                foreach ($response as $resp) {
                    if (isset($resp->PersonName)) {
                        $infos[] = [
                            'value' => $resp->Id,
                            'label' => $resp->PersonName,
                        ];
                    }
                }
            }

            return $infos;
        }
    }

    public function getNotifyPartyList($var = null)
    {
        $query = \request()->all();
        $response = DB::select(
            'SET NOCOUNT ON;
            Declare @OfficeID as Uniqueidentifier,
            @PICType as Char(1),
            @Filter as Varchar(50)
            Exec [dbo].[sp_PartyList]
            @OfficeID = ?,
            @PartyType = ?,
            @Filter = ?',
            [$query['officeID'], $var, $query['query']]
        );
        $list = [];
        if (count($response) > 0) {
            foreach ($response as $location) {
                if (isset($location->PartyID)) {
                    $list[] = [
                        'value' => $location->PartyID,
                        'label' => $location->PartyName,
                        'code' => $location->PartyCode,
                    ];
                }
            }
        }

        return $list;
    }
    // /NEW OPERATIONAL ENTITY STORE FUNCTION

    /**
     * Centralized call to the new JSON-based entity procedure
     *
     * @return array Decoded JSON response {Status: "Success"|"Failed", Remarks: [...]}
     *
     * @throws \Exception on failure or invalid response
     */

    private function executeJsonEntityProcedure(array $payload): array
    {
        try {
            $jsonInput = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            \Log::error('Failed to encode payload to JSON', [
                'payload' => $payload,
                'error' => $e->getMessage(),
            ]);
            throw new \Exception('Failed to prepare JSON input for stored procedure');
        }

        \Log::info('sp_EntityMain payload (JSON)', [
            'operation' => $payload['OperationType'] ?? 'unknown',
            'RequestNo' => $payload['RequestNo'] ?? '(new)',
            'submitted_by' => $payload['SubmittedBy'] ?? '—',
            'payload_json' => $jsonInput,
            'payload_length' => strlen($jsonInput),
        ]);

        // ── FIX: Handle transaction mismatch ────────────────────────────────
        // The SP manages its own transactions, so we need to call it outside
        // of any Laravel-managed transaction to avoid conflicts

        $hadOpenTransaction = DB::transactionLevel() > 0;

        if ($hadOpenTransaction) {
            // Temporarily commit any open transactions
            while (DB::transactionLevel() > 0) {
                DB::commit();
            }
            \Log::debug('Committed open transactions before SP call', [
                'operation' => $payload['OperationType'] ?? 'unknown',
            ]);
        }

        try {
            $results = DB::select(
                'SET NOCOUNT ON;
             DECLARE @OutputJson NVARCHAR(MAX);
             EXEC [dbo].[sp_EntityMain] @InputJson = ?, @OutputJson = @OutputJson OUTPUT;
             SELECT @OutputJson AS OutputJson',
                [$jsonInput]
            );

            if (empty($results) || ! isset($results[0]->OutputJson) || trim($results[0]->OutputJson) === '') {
                \Log::error('sp_EntityMain returned empty or null OutputJson', [
                    'sent_payload' => $jsonInput,
                ]);
                throw new \Exception('No valid response from entity procedure');
            }

            $rawOutput = $results[0]->OutputJson;
            $output = json_decode($rawOutput, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                \Log::error('Invalid JSON from sp_EntityMain', [
                    'raw_output' => $rawOutput,
                    'json_error' => json_last_error_msg(),
                    'sent_payload' => $jsonInput,
                ]);
                throw new \Exception('Invalid JSON response from stored procedure');
            }

            if (! isset($output['Status'])) {
                \Log::error('Response missing "Status" field', [
                    'raw_output' => $rawOutput,
                    'sent_payload' => $jsonInput,
                ]);
                throw new \Exception('Response missing "Status" field');
            }

            \Log::info('sp_EntityMain response received', [
                'status' => $output['Status'],
                'remarks' => $output['Remarks'] ?? null,
                'sent_operation' => $payload['OperationType'] ?? 'unknown',
            ]);

            if ($output['Status'] !== 'Success') {
                $firstRemark = $output['Remarks'][0]['Remark'] ?? 'Unknown error from database';
                \Log::warning('sp_EntityMain failed', [
                    'status' => $output['Status'],
                    'remark' => $firstRemark,
                    'sent_payload' => $jsonInput,
                ]);

                throw new \Exception("Procedure failed: $firstRemark");
            }

            return $output;
        } catch (\Exception $e) {
            // Re-throw the exception, let the calling code handle it
            throw $e;
        } finally {
            // Restart transaction if one was open before
            if ($hadOpenTransaction) {
                DB::beginTransaction();
                \Log::debug('Restarted transaction after SP call', [
                    'operation' => $payload['OperationType'] ?? 'unknown',
                ]);
            }
        }
    }

    public function store(StoreClientPartyRequest $request)
    {
       $user = $request->user();

        if (! $user->can('request-client-party')) {
            abort(403, 'You are not allowed to request client parties.');
        }

        // ── Determine Operation Type (smart detection) ─────────────────────
        $operationType = $request->input('OperationType', 'OperationalEntity');

        // FORCE AccountingEntity whenever legal/accounting fields are present
        // This fixes the "form sends OperationalEntity but we have Debtor/Vendor" bug
        if ($request->has('legalEntityType')
            && !empty(trim($request->legalEntityType ?? ''))
            && in_array(trim($request->legalEntityType), ['Debtor', 'Vendor', 'Debtor & Vendor', 'Only in D365 Debtor', 'Only in D365 Vendor'])) {

            $operationType = 'AccountingEntity';
        }

        $partyEntityType = $request->partyEntityType ?? '';

        \Log::info('Incoming entity submission', [
            'operation_type' => $operationType,
            'party_entity_type' => $partyEntityType,
            'legal_entity_type' => $request->legalEntityType,
            'has_accounting_fields' => $request->has('creditDays') || $request->has('maxCreditLimit') || $request->has('debtorType'),
            'request_data' => $request->except(['_token', 'attachmentNames', 'attachmentDescription']),
        ]);

               // ── Determine ClientCode vs OtherPartyCode based on entity type ─────
        // SMART FALLBACKS (this fixes the empty ClientCode bug)
        $clientCode = $request->clientCode
            ?? $request->companyCode
            ?? $request->otherCode
            ?? '';

        $otherPartyCode = $request->otherCode
            ?? $request->companyCode
            ?? $request->clientCode
            ?? '';

        $payload = [
            'OperationType' => $operationType,
            'Action' => 'Add',
            'SubmittedDatetime' => now()->format('YmdHis'),
            'SubmittedBy' => $user->LoginID ?? $user->email ?? $user->full_name ?? 'system',
            'Name' => $request->companyName ?? trim($request->input('name', '')),
            'Address_Line1' => $request->addressLine1 ?? '',
            'Address_Line2' => $request->addressLine2 ?? '',
            'Address_City' => $request->cityName ?? '',
            'Address_CityCode' => $request->cityCode ?? '',
            'Address_State' => $request->provinceName ?? '',
            'Address_StateCode' => $request->provinceCode ?? '',
            'Address_CountryCode' => $request->countryCode ?? '',
            'Address_Pin' => $request->zipCode ?? '',
            'Cont_Bch_Dept' => $request->department ?? '',
            'Cont_Bch_PersonName_FirstName' => $request->firstName ?? '',
            'Cont_Bch_PersonName_LastName' => $request->lastName ?? '',
            'Cont_Bch_PhoneNumbers' => $request->phoneNo ?? '',
            'Cont_Bch_Email' => $request->contractEmail ?? '',
            'OfficeCode' => $request->officeCode ?? $user->OfficeCode ?? '',
            'OperationalEntityType' => $partyEntityType,
            'ClientCode' => $clientCode,
            'OtherPartyCode' => $otherPartyCode,
        ];

        if ($operationType === 'AccountingEntity' || $request->has('legalEntityType')) {
            $creditDaysValue = $request->creditDays !== '' && is_numeric($request->creditDays) ? $request->creditDays : '0';
            $creditLimitValue = $request->maxCreditLimit !== '' && is_numeric($request->maxCreditLimit) ? $request->maxCreditLimit : '0';

            $payload = array_merge($payload, [
                'LegalEntityType' => $request->legalEntityType ?? '',
                'CreditDays' => $creditDaysValue,
                'CreditLimit' => $creditLimitValue,
                'CreditLimitCurrencyCode' => $request->creditLimitCurrencyCode ?? '',
                'BillingCurrencyCode' => $request->billingCurrencyCode ?? '',
                'BaseCurrencyCode' => $request->baseCurrencyCode ?? '',
                'LicenceTypeCode' => $request->licenceTypeCode ?? '',
                'LicenceTypeValue' => $request->licenceTypeValue ?? '',
                'DebtorType' => $request->debtorType ?? '',
                'VendorType' => $request->vendorType ?? '',
            ]);
        }

        \Log::info('Payload prepared for SP', [
            'operation_type' => $operationType,
            'party_entity_type' => $partyEntityType,
            'client_code' => $clientCode,
            'other_party_code' => $otherPartyCode,
        ]);

        try {
            // Call the stored procedure
            $response = $this->executeJsonEntityProcedure($payload);

            // Parse RequestNo from remarks
            $requestNo = null;
            if (isset($response['Remarks']) && is_array($response['Remarks'])) {
                foreach ($response['Remarks'] as $remark) {
                    if (isset($remark['Remark']) && preg_match('/<< (.+?) >>/', $remark['Remark'], $matches)) {
                        $requestNo = trim($matches[1]);
                        break;
                    }
                }
            }

            if (! $requestNo) {
                \Log::error('Could not parse RequestNo from SP response', [
                    'remarks' => $response['Remarks'] ?? null,
                    'response' => $response,
                ]);
                throw new \Exception('Request submitted but could not retrieve request number. Please contact support.');
            }

            \Log::info('Entity request submitted successfully', [
                'request_no' => $requestNo,
                'operation_type' => $operationType,
                'remarks' => $response['Remarks'],
            ]);

            // ── Query Cust_Entity table directly ─────────────────────────────
            $entityRecord = DB::table('Cust_Entity')
                ->where('Code', $requestNo)
                ->whereIn('Status', ['PendingCountryLevelApproval', 'Submitted'])
                ->first();

            if (! $entityRecord) {
                \Log::error('Request submitted but record not found in Cust_Entity table', [
                    'request_no' => $requestNo,
                    'office_code' => $payload['OfficeCode'],
                    'operation_type' => $operationType,
                    'client_code' => $clientCode,
                    'other_party_code' => $otherPartyCode,
                ]);
                throw new \Exception("Request submitted successfully ({$requestNo}) but record not found in database. Please contact support.");
            }

            \Log::info('Found entity record in Cust_Entity', [
                'request_no' => $requestNo,
                'entity_id' => $entityRecord->Id,
                'status' => $entityRecord->Status,
            ]);

            // ── Handle file attachments ──────────────────────────────────────
            if ($request->hasFile('attachmentNames')) {
                $uploadedCount = 0;

                foreach ($request->file('attachmentNames') as $index => $file) {
                    if (! $file || ! $file->isValid()) {
                        \Log::warning('Invalid file upload skipped', ['index' => $index]);

                        continue;
                    }

                    try {
                        $originalName = $file->getClientOriginalName();
                        $extension = $file->getClientOriginalExtension();
                        $filename = pathinfo($originalName, PATHINFO_FILENAME);
                        $newName = $requestNo . '_' . $filename . '.' . $extension;

                        // Store locally first
                        $localPath = $file->storeAs('client-party', $newName, 'local');

                        // Upload to Azure
                        $azurePath = "Kenya/ClientParty/{$newName}";
                        Storage::disk('azure')->put(
                            $azurePath,
                            file_get_contents(storage_path("app/{$localPath}"))
                        );

                        // Create attachment record
                        DB::table('Cust_EntityAttachment')->insert([
                            'ID' => (string) Str::uuid(),
                            'CustEntityID' => $entityRecord->Id,
                            'RequestNo' => $requestNo,
                            'Title' => $request->attachmentDescription[$index] ?? 'Attachment',
                            'FileName' => $newName,
                            'FilePath' => $azurePath,
                            'CreatedBy' => $user->ID ?? 'system',
                            'CreatedOn' => now(),
                            'LastModifiedBy' => $user->ID ?? 'system',
                            'LastModifiedOn' => now(),
                        ]);

                        // Clean up local file
                        @unlink(storage_path("app/{$localPath}"));

                        $uploadedCount++;
                    } catch (\Exception $fileEx) {
                        \Log::error('Failed to upload attachment', [
                            'request_no' => $requestNo,
                            'file_index' => $index,
                            'filename' => $originalName ?? 'unknown',
                            'error' => $fileEx->getMessage(),
                        ]);
                        // Continue with other files
                    }
                }

                if ($uploadedCount > 0) {
                    \Log::info('Uploaded attachments for request', [
                        'request_no' => $requestNo,
                        'count' => $uploadedCount,
                    ]);
                }
            }

            // Success response
            Alert::success('Success', "Entity Request No << {$requestNo} >> Successfully Submitted")
                ->persistent(true, false);

            return redirect()->route('client-party.index');
        } catch (\Exception $e) {
            \Log::error('Entity creation failed', [
                'payload' => $payload,
                'operation_type' => $operationType,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            Alert::error('Error', $e->getMessage() ?: 'Failed to submit entity request. Please try again.')
                ->persistent(true, false);

            return redirect()->back()->withInput();
        }
    }

//     public function store(StoreClientPartyRequest $request)
// {
//     $user = $request->user();

//     if (! $user->can('request-client-party')) {
//         abort(403, 'You are not allowed to request client parties.');
//     }

//     $operationType = $request->input('OperationType', 'OperationalEntity');
//     $partyEntityType = $request->partyEntityType ?? '';

//     \Log::info('Incoming entity submission', [
//         'operation_type' => $operationType,
//         'party_entity_type' => $partyEntityType,
//         'legal_entity_type' => $request->legalEntityType,
//         'has_accounting_fields' => $request->has('creditDays') || $request->has('maxCreditLimit') || $request->has('debtorType'),
//         'request_data' => $request->except(['_token', 'attachmentNames', 'attachmentDescription']),
//     ]);

//     // ── Determine ClientCode vs OtherPartyCode based on entity type ─────
//         // SMART FALLBACKS (this fixes the empty ClientCode bug)
//         $clientCode = $request->clientCode
//             ?? $request->companyCode
//             ?? $request->otherCode
//             ?? '';

//         $otherPartyCode = $request->otherCode
//             ?? $request->companyCode
//             ?? $request->clientCode
//             ?? '';

//     $payload = [
//         'OperationType' => $operationType,
//         'Action' => 'Add',
//         'SubmittedDatetime' => now()->format('YmdHis'),
//         'SubmittedBy' => $user->LoginID ?? $user->email ?? $user->full_name ?? 'system',
//         'Name' => $request->companyName ?? trim($request->input('name', '')),
//         'Address_Line1' => $request->addressLine1 ?? '',
//         'Address_Line2' => $request->addressLine2 ?? '',
//         'Address_City' => $request->cityName ?? '',
//         'Address_CityCode' => $request->cityCode ?? '',
//         'Address_State' => $request->provinceName ?? '',
//         'Address_StateCode' => $request->provinceCode ?? '',
//         'Address_CountryCode' => $request->countryCode ?? '',
//         'Address_Pin' => $request->zipCode ?? '',
//         'Cont_Bch_Dept' => $request->department ?? '',
//         'Cont_Bch_PersonName_FirstName' => $request->firstName ?? '',
//         'Cont_Bch_PersonName_LastName' => $request->lastName ?? '',
//         'Cont_Bch_PhoneNumbers' => $request->phoneNo ?? '',
//         'Cont_Bch_Email' => $request->contractEmail ?? '',
//         'OfficeCode' => $request->officeCode ?? $user->OfficeCode ?? '',
//         'OperationalEntityType' => $partyEntityType,
//         'ClientCode' => $clientCode,
//         'OtherPartyCode' => $otherPartyCode,
//     ];

//     if ($operationType === 'AccountingEntity' || $request->has('legalEntityType')) {
//         $creditDaysValue = $request->creditDays !== '' && is_numeric($request->creditDays) ? $request->creditDays : '0';
//         $creditLimitValue = $request->maxCreditLimit !== '' && is_numeric($request->maxCreditLimit) ? $request->maxCreditLimit : '0';

//         $payload = array_merge($payload, [
//             'LegalEntityType' => $request->legalEntityType ?? '',
//             'CreditDays' => $creditDaysValue,
//             'CreditLimit' => $creditLimitValue,
//             'CreditLimitCurrencyCode' => $request->creditLimitCurrencyCode ?? '',
//             'BillingCurrencyCode' => $request->billingCurrencyCode ?? '',
//             'BaseCurrencyCode' => $request->baseCurrencyCode ?? '',
//             'LicenceTypeCode' => $request->licenceTypeCode ?? '',
//             'LicenceTypeValue' => $request->licenceTypeValue ?? '',
//             'DebtorType' => $request->debtorType ?? '',
//             'VendorType' => $request->vendorType ?? '',
//         ]);
//     }

//     \Log::info('Payload prepared for SP', [
//         'operation_type' => $operationType,
//         'party_entity_type' => $partyEntityType,
//         'client_code' => $clientCode,
//         'other_party_code' => $otherPartyCode,
//     ]);

//     try {
//         // Call the stored procedure
//         $response = $this->executeJsonEntityProcedure($payload);

//         // Parse RequestNo from remarks
//         $requestNo = null;
//         if (isset($response['Remarks']) && is_array($response['Remarks'])) {
//             foreach ($response['Remarks'] as $remark) {
//                 if (isset($remark['Remark']) && preg_match('/<< (.+?) >>/', $remark['Remark'], $matches)) {
//                     $requestNo = trim($matches[1]);
//                     break;
//                 }
//             }
//         }

//         if (! $requestNo) {
//             \Log::error('Could not parse RequestNo from SP response', [
//                 'remarks' => $response['Remarks'] ?? null,
//                 'response' => $response,
//             ]);
//             throw new \Exception('Request submitted but could not retrieve request number. Please contact support.');
//         }

//         \Log::info('Entity request submitted successfully', [
//             'request_no' => $requestNo,
//             'operation_type' => $operationType,
//             'remarks' => $response['Remarks'],
//         ]);

//         // ── NEW: Trust SP success (no more hard check that was causing the error) ──
//         // For AccountingEntity the final record appears only after approval.
//         // We only try to find it for attachments.
//         $entityRecord = DB::table('Cust_Entity')
//             ->where('Code', $requestNo)
//             ->first();

//         if (! $entityRecord) {
//             \Log::warning('Entity record not found in Cust_Entity yet (normal for AccountingEntity initial submission)', [
//                 'request_no' => $requestNo,
//                 'operation_type' => $operationType,
//             ]);
//         } else {
//             \Log::info('Found entity record in Cust_Entity', [
//                 'request_no' => $requestNo,
//                 'entity_id' => $entityRecord->Id,
//                 'status' => $entityRecord->Status,
//             ]);
//         }

//         // ── Handle file attachments (only if we have the entity record) ──────────────────────────────────────
//         if ($request->hasFile('attachmentNames') && $entityRecord) {
//             $uploadedCount = 0;

//             foreach ($request->file('attachmentNames') as $index => $file) {
//                 if (! $file || ! $file->isValid()) {
//                     \Log::warning('Invalid file upload skipped', ['index' => $index]);
//                     continue;
//                 }

//                 try {
//                     $originalName = $file->getClientOriginalName();
//                     $extension = $file->getClientOriginalExtension();
//                     $filename = pathinfo($originalName, PATHINFO_FILENAME);
//                     $newName = $requestNo . '_' . $filename . '.' . $extension;

//                     // Store locally first
//                     $localPath = $file->storeAs('client-party', $newName, 'local');

//                     // Upload to Azure
//                     $azurePath = "Kenya/ClientParty/{$newName}";
//                     Storage::disk('azure')->put(
//                         $azurePath,
//                         file_get_contents(storage_path("app/{$localPath}"))
//                     );

//                     // Create attachment record
//                     DB::table('Cust_EntityAttachment')->insert([
//                         'ID' => (string) Str::uuid(),
//                         'CustEntityID' => $entityRecord->Id,
//                         'RequestNo' => $requestNo,
//                         'Title' => $request->attachmentDescription[$index] ?? 'Attachment',
//                         'FileName' => $newName,
//                         'FilePath' => $azurePath,
//                         'CreatedBy' => $user->ID ?? 'system',
//                         'CreatedOn' => now(),
//                         'LastModifiedBy' => $user->ID ?? 'system',
//                         'LastModifiedOn' => now(),
//                     ]);

//                     // Clean up local file
//                     @unlink(storage_path("app/{$localPath}"));

//                     $uploadedCount++;
//                 } catch (\Exception $fileEx) {
//                     \Log::error('Failed to upload attachment', [
//                         'request_no' => $requestNo,
//                         'file_index' => $index,
//                         'filename' => $originalName ?? 'unknown',
//                         'error' => $fileEx->getMessage(),
//                     ]);
//                 }
//             }

//             if ($uploadedCount > 0) {
//                 \Log::info('Uploaded attachments for request', [
//                     'request_no' => $requestNo,
//                     'count' => $uploadedCount,
//                 ]);
//             }
//         } elseif ($request->hasFile('attachmentNames')) {
//             \Log::warning('Attachments skipped - Cust_Entity record not available yet (will be attachable after approval)', [
//                 'request_no' => $requestNo,
//             ]);
//         }

//         // Success response
//         Alert::success('Success', "Entity Request No << {$requestNo} >> Successfully Submitted")
//             ->persistent(true, false);

//         return redirect()->route('client-party.index');
//     } catch (\Exception $e) {
//         \Log::error('Entity creation failed', [
//             'payload' => $payload,
//             'operation_type' => $operationType,
//             'error' => $e->getMessage(),
//             'trace' => $e->getTraceAsString(),
//         ]);

//         Alert::error('Error', $e->getMessage() ?: 'Failed to submit entity request. Please try again.')
//             ->persistent(true, false);

//         return redirect()->back()->withInput();
//     }
// }

    public function getOfficeNumberSeries($officeCode = null, $entityType = null)
    {

        try {
            // Add validation for required parameters
            if (! $officeCode || ! $entityType) {
                Log::error('getOfficeNumberSeries: Missing required parameters', [
                    'officeCode' => $officeCode,
                    'entityType' => $entityType,
                ]);

                return null;
            }

            Log::info('getOfficeNumberSeries called', [
                'officeCode' => $officeCode,
                'entityType' => $entityType,
            ]);

            $numSeries = DB::table('Cust_OfficeSeriesMapping')
                ->where('EntityType', $entityType)
                ->where('OfficeCode', trim($officeCode))
                ->pluck('SeriesNo');

            // Check if the collection is empty before accessing index 0
            if ($numSeries->isEmpty()) {
                Log::warning('getOfficeNumberSeries: No series found', [
                    'officeCode' => $officeCode,
                    'entityType' => $entityType,
                    'query_result' => $numSeries->toArray(),
                ]);

                return null;
            }

            $seriesNo = $numSeries->first(); // Use first() instead of [0] for better error handling

            Log::info('getOfficeNumberSeries result', [
                'officeCode' => $officeCode,
                'entityType' => $entityType,
                'seriesNo' => $seriesNo,
                'found_count' => $numSeries->count(),
            ]);

            return $seriesNo;
        } catch (Exception $e) {
            Log::error('getOfficeNumberSeries exception', [
                'officeCode' => $officeCode,
                'entityType' => $entityType,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return null;
        }
    }

    // public function preview(ClientParty $path)
    // {
    //     $attachment = $path->attachments;
    //     $filePath = $attachment[0]->FilePath;

    //     return Storage::disk('azure')->download($filePath, basename($attachment[0]->FilePath), [
    //         'Content-Type' => 'application/octet-stream',
    //         'Content-Disposition' => 'inline; filename="' . basename($attachment[0]->FilePath) . '"'
    //     ]);
    // }

    /**
     * Preview/Download a specific attachment file
     *
     * @param  string  $attachmentId  - The unique ID of the attachment
     * @return \Illuminate\Http\Response
     */
    public function preview($attachmentId)
    {
        try {
            // Fetch the specific attachment by its ID (not entity ID)
            $attachment = ClientPartyAttahment::where('ID', $attachmentId)->firstOrFail();

            Log::info('Preview attachment request:', [
                'attachmentId' => $attachmentId,
                'fileName' => $attachment->FileName,
                'filePath' => $attachment->FilePath,
                'entityId' => $attachment->CustEntityID,
            ]);

            // Check if file exists in Azure storage
            if (! Storage::disk('azure')->exists($attachment->FilePath)) {
                Log::error('File not found in Azure storage:', [
                    'filePath' => $attachment->FilePath,
                    'attachmentId' => $attachmentId,
                ]);
                abort(404, 'File not found in storage');
            }

            // Get file extension to determine content type
            $extension = pathinfo($attachment->FileName, PATHINFO_EXTENSION);
            $mimeType = $extension === 'pdf' ? 'application/pdf' : 'application/octet-stream';

            Log::info('File retrieved successfully:', [
                'attachmentId' => $attachmentId,
                'fileName' => $attachment->FileName,
                'mimeType' => $mimeType,
            ]);

            // Download file from Azure and return as inline (for preview)
            return Storage::disk('azure')->download(
                $attachment->FilePath,
                $attachment->FileName,
                [
                    'Content-Type' => $mimeType,
                    'Content-Disposition' => 'inline; filename="' . $attachment->FileName . '"',
                ]
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error('Attachment not found in database:', [
                'attachmentId' => $attachmentId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'Attachment not found',
            ], 404);
        } catch (\Exception $e) {
            Log::error('Preview failed with exception:', [
                'attachmentId' => $attachmentId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'error' => 'Error retrieving file: ' . $e->getMessage(),
            ], 500);
        }
    }

    // public function createLegalEntityType(string $type, array $post_data)
    // {
    //     $debtor_url = env('DEBTOR_URL');
    //     $vendor_url = env('VENDER_URL');

    //     // Handle both 'debtor' and 'Customer' type names
    //     $request_url = (strtolower($type) == 'vendor') ? $vendor_url : $debtor_url;

    //     Log::info('D365 API Request', [
    //         'type' => $type,
    //         'url' => $request_url,
    //         'payload' => $post_data,
    //     ]);

    //     // Get authentication token
    //     $entityToken = $this->entityToken();
    //     if (! $entityToken['status']) {
    //         Log::error('Failed to get D365 token');

    //         return [
    //             'status' => false,
    //             'error' => 'Failed to authenticate with D365',
    //         ];
    //     }

    //     Log::info('Entity Token Retrieved', [
    //         'status' => $entityToken['status'],
    //         'token_preview' => substr($entityToken['token'], 0, 20) . '...',
    //     ]);

    //     try {
    //         $response = Http::withToken($entityToken['token'])
    //             ->timeout(30) // Add timeout
    //             ->post($request_url, $post_data);

    //         Log::info('Legal Entity Creation Response', [
    //             'status' => $response->status(),
    //             'body' => $response->body(),
    //             'type' => $type,
    //             'post_data' => $post_data,
    //         ]);

    //         // Decode response
    //         $responseBody = json_decode($response->body());

    //         // Handle HTTP errors (4xx, 5xx)
    //         if (! $response->successful()) {
    //             $errorMessage = 'HTTP Error ' . $response->status();

    //             if (is_object($responseBody)) {
    //                 if (property_exists($responseBody, 'title')) {
    //                     $errorMessage = $responseBody->title;
    //                 } elseif (property_exists($responseBody, 'message')) {
    //                     $errorMessage = $responseBody->message;
    //                 } elseif (property_exists($responseBody, 'responseDescription')) {
    //                     $errorMessage = $responseBody->responseDescription;
    //                 }

    //                 // Handle validation errors
    //                 if (property_exists($responseBody, 'errors')) {
    //                     $validationErrors = [];
    //                     foreach ($responseBody->errors as $field => $messages) {
    //                         if (is_array($messages)) {
    //                             $validationErrors[] = $field . ': ' . implode(', ', $messages);
    //                         }
    //                     }
    //                     if (! empty($validationErrors)) {
    //                         $errorMessage .= ' - ' . implode('; ', $validationErrors);
    //                     }
    //                 }
    //             }

    //             Log::error('D365 API HTTP Error', [
    //                 'type' => $type,
    //                 'status' => $response->status(),
    //                 'error' => $errorMessage,
    //             ]);

    //             return [
    //                 'status' => false,
    //                 'error' => $errorMessage,
    //             ];
    //         }

    //         // Parse successful response
    //         if (is_object($responseBody)) {
    //             // Success case: status === true and responseCode exists
    //             if (property_exists($responseBody, 'status') && $responseBody->status === true) {
    //                 if (property_exists($responseBody, 'responseCode') && ! empty($responseBody->responseCode)) {
    //                     Log::info('D365 Success', [
    //                         'type' => $type,
    //                         'responseCode' => $responseBody->responseCode,
    //                     ]);

    //                     return [
    //                         'status' => true,
    //                         'responseCode' => $responseBody->responseCode,
    //                         'responseDescription' => $responseBody->responseDescription ?? 'Success',
    //                     ];
    //                 } else {
    //                     Log::error('D365 Success but no responseCode', [
    //                         'type' => $type,
    //                         'response' => $responseBody,
    //                     ]);

    //                     return [
    //                         'status' => false,
    //                         'error' => 'D365 returned success but no entity code',
    //                     ];
    //                 }
    //             }
    //             // Failure case: status === false
    //             elseif (property_exists($responseBody, 'status') && $responseBody->status === false) {
    //                 $errorMessage = property_exists($responseBody, 'responseDescription')
    //                     ? $responseBody->responseDescription
    //                     : 'D365 returned status: false';

    //                 Log::error('D365 Failed', [
    //                     'type' => $type,
    //                     'error' => $errorMessage,
    //                 ]);

    //                 return [
    //                     'status' => false,
    //                     'error' => $errorMessage,
    //                 ];
    //             }
    //             // Fallback: check if responseCode exists directly (some APIs return this without status field)
    //             elseif (property_exists($responseBody, 'responseCode') && ! empty($responseBody->responseCode)) {
    //                 Log::info('D365 Success (fallback)', [
    //                     'type' => $type,
    //                     'responseCode' => $responseBody->responseCode,
    //                 ]);

    //                 return [
    //                     'status' => true,
    //                     'responseCode' => $responseBody->responseCode,
    //                     'responseDescription' => $responseBody->responseDescription ?? 'Success',
    //                 ];
    //             }
    //         }

    //         // If we get here, the response format is unexpected
    //         Log::error('D365 Unexpected Response Format', [
    //             'type' => $type,
    //             'response' => $response->body(),
    //         ]);

    //         return [
    //             'status' => false,
    //             'error' => 'Unexpected response format from D365',
    //         ];
    //     } catch (\Illuminate\Http\Client\RequestException $e) {
    //         Log::error('D365 Request Exception', [
    //             'type' => $type,
    //             'error' => $e->getMessage(),
    //             'response' => $e->response ? $e->response->body() : 'No response',
    //         ]);

    //         return [
    //             'status' => false,
    //             'error' => 'D365 API request failed: ' . $e->getMessage(),
    //         ];
    //     } catch (\Exception $e) {
    //         Log::error('D365 Exception', [
    //             'type' => $type,
    //             'error' => $e->getMessage(),
    //             'trace' => $e->getTraceAsString(),
    //         ]);

    //         return [
    //             'status' => false,
    //             'error' => 'D365 API call failed: ' . $e->getMessage(),
    //         ];
    //     }
    // }
    public function createLegalEntityType(string $type, array $post_data)
    {
        $debtor_url = env('DEBTOR_URL');
        $vendor_url = env('VENDER_URL');

        $request_url = (strtolower($type) == 'vendor') ? $vendor_url : $debtor_url;

        Log::info('D365 API Request', [
            'type' => $type,
            'url' => $request_url,
            'payload' => $post_data,
        ]);

        $entityToken = $this->entityToken();
        if (! $entityToken['status']) {
            Log::error('Failed to get D365 token');

            return [
                'status' => false,
                'error' => 'Failed to authenticate with D365',
            ];
        }

        Log::info('Entity Token Retrieved', [
            'status' => $entityToken['status'],
            'token_preview' => substr($entityToken['token'], 0, 20) . '...',
        ]);

        try {
            $response = Http::withToken($entityToken['token'])
                ->timeout(30)
                ->post($request_url, $post_data);

            Log::info('Legal Entity Creation Response', [
                'status' => $response->status(),
                'body' => $response->body(),
                'type' => $type,
                'post_data' => $post_data,
            ]);

            $responseBody = json_decode($response->body());

            if (! $response->successful()) {
                $errorMessage = 'HTTP Error ' . $response->status();

                if (is_object($responseBody)) {
                    if (property_exists($responseBody, 'responseDescription') && ! empty($responseBody->responseDescription)) {
                        $errorMessage = $responseBody->responseDescription; // ← moved first
                    } elseif (property_exists($responseBody, 'title')) {
                        $errorMessage = $responseBody->title;
                    } elseif (property_exists($responseBody, 'message')) {
                        $errorMessage = $responseBody->message;
                    }

                    if (property_exists($responseBody, 'errors')) {
                        $validationErrors = [];
                        foreach ($responseBody->errors as $field => $messages) {
                            if (is_array($messages)) {
                                $validationErrors[] = $field . ': ' . implode(', ', $messages);
                            }
                        }
                        if (! empty($validationErrors)) {
                            $errorMessage .= ' - ' . implode('; ', $validationErrors);
                        }
                    }
                }

                Log::error('D365 API HTTP Error', [
                    'type' => $type,
                    'status' => $response->status(),
                    'error' => $errorMessage,
                ]);

                return [
                    'status' => false,
                    'error' => $errorMessage,
                ];
            }

            if (is_object($responseBody)) {
                if (property_exists($responseBody, 'status') && $responseBody->status === true) {
                    if (property_exists($responseBody, 'responseCode') && ! empty($responseBody->responseCode)) {
                        Log::info('D365 Success', [
                            'type' => $type,
                            'responseCode' => $responseBody->responseCode,
                        ]);

                        return [
                            'status' => true,
                            'responseCode' => $responseBody->responseCode,
                            'responseDescription' => $responseBody->responseDescription ?? 'Success',
                        ];
                    } else {
                        Log::error('D365 Success but no responseCode', [
                            'type' => $type,
                            'response' => $responseBody,
                        ]);

                        return [
                            'status' => false,
                            'error' => 'D365 returned success but no entity code',
                        ];
                    }
                } elseif (property_exists($responseBody, 'status') && $responseBody->status === false) {
                    $errorMessage = property_exists($responseBody, 'responseDescription')
                        ? $responseBody->responseDescription
                        : 'D365 returned status: false';

                    Log::error('D365 Failed', [
                        'type' => $type,
                        'error' => $errorMessage,
                    ]);

                    return [
                        'status' => false,
                        'error' => $errorMessage,
                    ];
                } elseif (property_exists($responseBody, 'responseCode') && ! empty($responseBody->responseCode)) {
                    Log::info('D365 Success (fallback)', [
                        'type' => $type,
                        'responseCode' => $responseBody->responseCode,
                    ]);

                    return [
                        'status' => true,
                        'responseCode' => $responseBody->responseCode,
                        'responseDescription' => $responseBody->responseDescription ?? 'Success',
                    ];
                }
            }

            Log::error('D365 Unexpected Response Format', [
                'type' => $type,
                'response' => $response->body(),
            ]);

            return [
                'status' => false,
                'error' => 'Unexpected response format from D365',
            ];
        } catch (\Illuminate\Http\Client\RequestException $e) {
            Log::error('D365 Request Exception', [
                'type' => $type,
                'error' => $e->getMessage(),
                'response' => $e->response ? $e->response->body() : 'No response',
            ]);

            return [
                'status' => false,
                'error' => 'D365 API request failed: ' . $e->getMessage(),
            ];
        } catch (\Exception $e) {
            Log::error('D365 Exception', [
                'type' => $type,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'status' => false,
                'error' => 'D365 API call failed: ' . $e->getMessage(),
            ];
        }
    }

    public function entityToken()
    {
        $token_url = env('D365_TOKEN_URL');

        $response = Http::post($token_url, [
            'username' => env('D365_USERNAME'),
            'password' => env('D365_PASSWORD'),
        ]);

        return ['status' => $response->successful(), 'token' => $response->body()];
    }

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */

    // 2

    public function show(ClientParty $clientParty)
    {
        $user = request()->user();

        if (! $user->can('view-client-party')) {
            abort(403, 'You are not allowed to view this page.');
        }

        $type = $clientParty->LegalEntityType ? 'accounting' : 'operational';
        $approve = false;
        $editForm = true;

        if (($clientParty->Status == 'PendingCountryLevelApproval' || $clientParty->Status == 'PendingGroupLevelApproval') && ! $user->can('approve-entity-request')) {
            alert()->warning('Warning !', 'Entity Pending Approval')->showConfirmButton();

            return redirect()->back();
        }

        if ($clientParty->Status == 'Created') {
            $editForm = false;
        }

        if ((Auth::user()->Role != 1 && ($clientParty->Status == 'Not Submitted' || $clientParty->Status == 'Submitted')) && ! Auth::user()->can('create-client-party')) {
            $editForm = false;
        }

        if (Auth::user()->Role == 1 && ($clientParty->Status != 'Created')) {
            $editForm = true;
        }

        if (url()->previous() == url('client-party/approve') || ($clientParty->Status == 'PendingCountryLevelApproval' || $clientParty->Status == 'PendingGroupLevelApproval' || $clientParty->Status == 'Submitted')) {
            $editForm = false;
            $approve = true;
        }

        $countries = $this->getCountries();
        $offices = $this->getOffices();
        $currencyList = $this->getCurrencyList($clientParty->OfficeID) ?: ['does not exist'];

        if ($clientParty->OfficeID && str_contains(json_encode($currencyList[0]), 'does not exist')) {
            $scm_office_id = DB::select(DB::raw('SELECT SCMOfficeID FROM Cust_Office WHERE ID = ?', [$clientParty->OfficeID]));
            $currencyList = $this->getCurrencyList($scm_office_id[0]->SCMOfficeID ?? null);
        } else {
            $idX = Auth::user()->office->SCMOfficeID ?? null;
            $currencyList = $this->getCurrencyList($idX);
        }

        $licenceTypes = $this->getLicenceType();
        $tarrifs = ($clientParty->Office == 'FIT - Kenya - UPS') ? $this->getTarrif($clientParty->OfficeID) : [];
        $incoterms = $this->getIncoTerms();
        // Pass the officeCode from clientParty
        $payTerms = $this->getPaymentTermList($clientParty->OfficeCode);

        return view('client-party.show', compact(
            'approve',
            'editForm',
            'payTerms',
            'incoterms',
            'clientParty',
            'countries',
            'offices',
            'currencyList',
            'licenceTypes',
            'tarrifs',
            'type'
        ));
    }
    // 3

    /**
     * Show the form for editing the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function edit(ClientParty $clientParty)
    {
        $user = Auth::user();
        if (! $user->can('edit-client-party') && ! $user->can('approve-my-office-entity-request')) {
            alert()->warning('Warning!', 'Unauthorized.')->showConfirmButton();

            return redirect()->back();
        }

        try {
            Log::info('Edit Client Party Data:', [
                'id' => $clientParty->Id,
                'firstName' => $clientParty->Cont_Bch_PersonName_FirstName,
                'lastName' => $clientParty->Cont_Bch_PersonName_LastName,
                'status' => $clientParty->Status,
                'clientID' => $clientParty->ClientID,
                'OperationalEntityType' => $clientParty->OperationalEntityType,
            ]);

            return view('client-party.edit', [
                'clientParty' => $clientParty,
                'countries' => DB::table('Countries')->get(),
                'offices' => DB::table('Offices')->get(),
                'currencies' => DB::table('Currencies')->get(),
                'incoterms' => DB::table('Incoterms')->get(),
                'payTerms' => DB::table('PayTerms')->get(),
                'licenceTypes' => DB::table('LicenceTypes')->get(),
                'tarrifs' => DB::table('Tarrifs')->get(),
                'currencyList' => DB::table('Currencies')->get(),
            ]);
        } catch (Exception $e) {
            Log::error('Failed to load edit form:', [
                'id' => $clientParty->Id,
                'error' => $e->getMessage(),
            ]);
            alert()->error('Error', 'Failed to load entity for editing.')->showConfirmButton();

            return redirect()->route('client-party.index');
        }
    }




    public function update(UpdateClientPartyRequest $request, $id)
    {
        $user = Auth::user();

        Log::info('=== UPDATE CONTROLLER START ===', [
            'id' => $id,
            'user_id' => $user?->Id,
            'method' => $request->method(),
            'createBtn' => $request->input('createBtn'),
            'all_input' => $request->all(),
            'ip' => $request->ip(),
        ]);

        if (!$user->can('update-client-party')) {
            Log::warning('Unauthorized access attempt', ['user_id' => $user?->Id, 'id' => $id]);
            Alert::warning('Warning', 'Unauthorized.')->persistent(true, false);
            return redirect()->back();
        }

        $entity = ClientParty::findOrFail($id);
        $currentStatus = $entity->Status;

        Log::info('Entity loaded successfully', [
            'id' => $id,
            'code' => $entity->Code,
            'status' => $currentStatus,
            'legal_entity_type' => $entity->LegalEntityType,
            'request_debtor' => $entity->RequestforDebtor,
            'request_vendor' => $entity->RequestforVendor,
        ]);

        if (!isset($request->createBtn)) {
            Log::info('No createBtn present - redirecting to show');
            Alert::info('Info', 'Use Create/Approve for status changes.')->persistent(true, false);
            return redirect()->route('client-party.show', $id);
        }

        // Determine target status
        $targetStatus = 'Created';
        if ($currentStatus === 'PendingGroupLevelApproval') {
            $targetStatus = 'PendingITVerification';
        }

        Log::info('Target status determined', ['from' => $currentStatus, 'to' => $targetStatus]);

        // Validate transition
        $allowedTransitions = [
            'PendingGroupLevelApproval' => ['PendingITVerification'],
            'PendingITVerification'     => ['Created'],
        ];

        if (!isset($allowedTransitions[$currentStatus]) || !in_array($targetStatus, $allowedTransitions[$currentStatus])) {
            Log::warning('Invalid status transition attempted', [
                'current' => $currentStatus,
                'target'  => $targetStatus,
            ]);
            Alert::error('Error', 'Invalid status transition from ' . $currentStatus)->persistent(true, false);
            return redirect()->back();
        }

        // STEP 1: PendingGroupLevelApproval → PendingITVerification
        if ($targetStatus === 'PendingITVerification') {
            Log::info('Entering Group → IT Verification transition');

            $payload = [
                'OperationType'     => 'AccountingEntityUpdate',
                'Action'            => 'Add',
                'SubmittedDatetime' => now()->format('YmdHis'),
                'SubmittedBy'       => $user->LoginID ?? $user->full_name ?? 'system',
                'RequestNo'         => $entity->Code,
                'Status'            => $targetStatus,
                'Remark'            => $request->input('comment', 'Group level approved - pending IT verification'),
            ];

            Log::info('Sending status update payload to SP', ['payload' => $payload]);

            try {
                $response = $this->executeJsonEntityProcedure($payload);

                Log::info('Status update SP response', [
                    'status' => $response['Status'] ?? 'missing',
                    'remarks' => $response['Remarks'] ?? [],
                ]);

                if ($response['Status'] !== 'Success') {
                    $remark = $response['Remarks'][0]['Remark'] ?? 'Unknown error';
                    Log::error('Status update failed', ['remark' => $remark]);
                    throw new Exception('SP failed: ' . $remark);
                }

                Log::info('Group → IT transition completed successfully');
                Alert::success('Success', 'Entity moved to Pending IT Verification')->showConfirmButton();
                return redirect()->route('client-party.show', $id);
            } catch (\Exception $e) {
                Log::error('Group → IT transition failed', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                Alert::error('Error', $e->getMessage())->persistent(true, false);
                return redirect()->back();
            }
        }

        // STEP 2: PendingITVerification → Created (final creation)
        if ($currentStatus !== 'PendingITVerification') {
            Log::warning('Invalid final transition - not in PendingITVerification');
            Alert::error('Error', 'Can only finalize from Pending IT Verification')->persistent(true, false);
            return redirect()->back();
        }

        Log::info('Entering FINAL CREATION STEP (PendingITVerification → Created)');

        $isAccounting = !empty($entity->LegalEntityType) || $entity->RequestforDebtor || $entity->RequestforVendor;
        if (!$isAccounting) {
            Log::warning('Entity is not accounting type - skipping creation');
            Alert::error('Error', 'This is not an accounting entity')->persistent(true, false);
            return redirect()->back();
        }

        $d365Results = [];

        // Debtor creation (now using dynamic payload)
        if ($entity->RequestforDebtor || stripos($entity->LegalEntityType, 'debtor') !== false) {
            Log::info('Starting Debtor D365 creation (dynamic payload)');

            try {
                $debtorPayload = $this->buildD365Payload($entity, 'debtor');

                Log::info('Debtor payload built successfully - sending to D365', [
                    'payload_summary' => array_keys($debtorPayload),
                    'no_series'       => $debtorPayload['NoSeries'] ?? 'MISSING',
                    'office'          => $debtorPayload['office'] ?? 'unknown',
                ]);

                $d365Response = $this->createLegalEntityType('debtor', $debtorPayload);

                Log::info('Debtor D365 API response', [
                    'status'       => $d365Response['status'] ?? 'missing',
                    'response_code' => $d365Response['responseCode'] ?? null,
                    'full_response' => $d365Response,
                ]);
                // Debtor block
                if (!$d365Response['status'] || empty($d365Response['responseCode'])) {
                    Log::error('Debtor D365 creation failed', ['error' => $d365Response['error'] ?? '']);
                    $this->d365ErrorAlert('debtor', $d365Response);
                    return redirect()->back()->withInput();
                }

                $d365Results['debtor'] = $d365Response['responseCode'];
                Log::info('Debtor successfully created in D365', ['code' => $d365Results['debtor']]);
            } catch (\Exception $e) {
                Log::error('Debtor D365 exception', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                Alert::error('D365 Exception (Debtor)', $e->getMessage())->persistent(true, false);
                return redirect()->back();
            }
        } else {
            Log::info('Debtor processing skipped (not requested)');
        }

        // Vendor creation (now using dynamic payload)
        if ($entity->RequestforVendor || stripos($entity->LegalEntityType, 'vendor') !== false) {
            Log::info('Starting Vendor D365 creation (dynamic payload)');

            try {
                $vendorPayload = $this->buildD365Payload($entity, 'vendor');

                Log::info('Vendor payload built successfully - sending to D365', [
                    'payload_summary' => array_keys($vendorPayload),
                    'no_series'       => $vendorPayload['NoSeries'] ?? 'MISSING',
                    'office'          => $vendorPayload['office'] ?? 'unknown',
                ]);

                $d365Response = $this->createLegalEntityType('vendor', $vendorPayload);

                Log::info('Vendor D365 API response', [
                    'status'       => $d365Response['status'] ?? 'missing',
                    'response_code' => $d365Response['responseCode'] ?? null,
                    'full_response' => $d365Response,
                ]);

                // Vendor block
                if (!$d365Response['status'] || empty($d365Response['responseCode'])) {
                    Log::error('Vendor D365 creation failed', ['error' => $d365Response['error'] ?? '']);
                    $this->d365ErrorAlert('vendor', $d365Response);
                    return redirect()->back()->withInput();
                }

                $d365Results['vendor'] = $d365Response['responseCode'];
                Log::info('Vendor successfully created in D365', ['code' => $d365Results['vendor']]);
            } catch (\Exception $e) {
                Log::error('Vendor D365 exception', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                Alert::error('D365 Exception (Vendor)', $e->getMessage())->persistent(true, false);
                return redirect()->back();
            }
        } else {
            Log::info('Vendor processing skipped (not requested)');
        }

        // Final SP payload
        $payload = [
            'OperationType'     => 'AccountingEntityCreation',
            'Action'            => 'Add',
            'SubmittedDatetime' => now()->format('YmdHis'),
            'SubmittedBy'       => $user->LoginID ?? $user->full_name ?? 'system',
            'RequestNo'         => $entity->Code,
            'Status'            => 'Create',
            'Remark'            => $request->input('comment', 'Final creation from update'),
        ];

        if (isset($d365Results['debtor'])) $payload['DebtorNo'] = $d365Results['debtor'];
        if (isset($d365Results['vendor'])) $payload['VendorNo'] = $d365Results['vendor'];

        Log::info('Final SP payload prepared', ['payload' => $payload]);

        try {
            Log::info('Calling main SP (sp_EntityMain) for final creation');
            $response = $this->executeJsonEntityProcedure($payload);

            Log::info('Main SP response received', [
                'status'  => $response['Status'] ?? 'missing',
                'remarks' => $response['Remarks'] ?? [],
            ]);

            if ($response['Status'] !== 'Success') {
                $remark = $response['Remarks'][0]['Remark'] ?? 'Unknown error';
                Log::error('Main SP returned failure', ['remark' => $remark]);
                throw new Exception('SP failed: ' . $remark);
            }

            Log::info('FINAL CREATION SUCCESS - entity created');
            Alert::success('Success', 'Entity created successfully')->showConfirmButton();
            return redirect()->route('client-party.index')->with('entity_type', $type ?? 'operational');
        } catch (\Exception $e) {
            Log::error('Final creation FAILED - exception caught', [
                'error_message' => $e->getMessage(),
                'file'          => $e->getFile(),
                'line'          => $e->getLine(),
                'trace'         => $e->getTraceAsString(),
            ]);
            Alert::error('Error', $e->getMessage())->persistent(true, false);
            return redirect()->back();
        }
    }


    // In your update() controller, replace both D365 error blocks with this helper approach
    // Add this private method to the controller:

    private function d365ErrorAlert(string $type, array $response): void
    {
        $error = $response['error'] ?? $response['responseDescription'] ?? 'Unknown D365 error';

        $hints = [
            'VAT Registration'  => '⚠ A vendor/debtor with this VAT/TIN already exists in D365. Verify the registration number before proceeding.',
            'already exists'    => '⚠ A duplicate record was detected in D365.',
            'authenticate'      => '⚠ D365 authentication failed. Contact IT to check the API credentials.',
            'Unexpected'        => '⚠ D365 returned an unrecognised response. Contact IT to investigate.',
        ];

        $hint = '';
        foreach ($hints as $keyword => $message) {
            if (str_contains($error, $keyword)) {
                $hint = '<p style="color:#e67e22;font-size:12px;margin-top:6px;">' . $message . '</p>';
                break;
            }
        }

        $label = ucfirst($type); // "Vendor" or "Debtor"

        $html = '
        <div style="text-align:left;font-size:14px;line-height:1.5">
            <p><strong>' . $label . ' could not be created in D365.</strong></p>
            <p style="color:#e74c3c;margin-top:4px;">' . e($error) . '</p>'
            . $hint .
            '</div>';

        Alert::error('D365 ' . $label . ' Error', $html)
            ->html()
            ->persistent(true, false);
    }



    /**
     * Build D365 payload for Customer/Vendor creation
     *
     * @param  ClientParty  $entity
     * @param  string  $type  ('debtor' or 'vendor')
     * @return array
     */
    private function buildD365Payload($entity, $type)
    {
        // Get office code
        $officeCode = $entity->OfficeCode ?? 'KE';

        // Get number series from database
        $noSeries = $this->getOfficeNumberSeries(
            $officeCode,
            $type === 'debtor' ? 'Debtor' : 'Vendor'
        );

        if ($noSeries === null) {
            throw new \Exception('No series number configured for ' . ($type === 'debtor' ? 'Debtor' : 'Vendor') . ' in office ' . $officeCode);
        }

        // Determine party type based on environment
        $partyType = $type === 'debtor' ? 'Customer' : 'Vendor';
        if (config('app.env') !== 'production') {
            $partyType = 'TST'; // Test party type for non-production
        }

        $vat = strtoupper(trim($entity->LicenceTypeValue ?? ''));

        return [
            'office' => ($officeCode === 'UPS') ? 'KE' : $officeCode,
            'partyType' => $partyType,
            'no' => '',
            'name' => $entity->Name ?? 'Unknown',
            'postingGroup' => $type === 'debtor'
                ? ($entity->DebtorType ?? 'CASHCUSTOM')
                : ($entity->VendorType ?? 'DOMESTIC'),
            'vatRegistrationNo' => in_array($vat, ['NILL', 'NIL', 'N/A', 'NONE']) ? '-' : ($vat ?: '-'),
            'postCode' => $entity->Address_Pin ?? '',
            'address' => $entity->Address_Line1 ?? 'Nairobi',
            'address2' => $entity->Address_Line2 ?? '',
            'city' => $entity->Address_City ?? '',
            'ClientID' => $entity->ClientID,
            'ClientCode' => $entity->ClientCode,
            'countryCode' => $entity->Address_CountryCode ?? 'KE',
            'emailAddress' => $entity->Cont_Bch_Email ?? '',
            'phoneNo' => $entity->Cont_Bch_PhoneNumbers ?? '',
            'phoneNo2' => '',
            'billingCurrency' => $entity->BillingCurrencyCode ?? 'KES',
            'paymentTermsCode' => (string) ($entity->CreditDays ?? '0'),
            'creditLimitLCY' => max(1, intval($entity->CreditLimit ?? 0)),
            'contactPersonName' => trim(
                ($entity->Cont_Bch_PersonName_FirstName ?? '') . ' ' .
                    ($entity->Cont_Bch_PersonName_LastName ?? '')
            ),
            'contactPersonEmailAddress' => $entity->Cont_Bch_Email ?? '',
            'contactPersonPhoneNo' => $entity->Cont_Bch_PhoneNumbers ?? '',
            'contactPersonPhoneNo2' => '',
            'relatedParty' => '',
            'blocked' => false,
            'NoSeries' => $noSeries,
        ];
    }

    public function CreateClientPartySCM(HttpRequest $request)
    {
        $requestID = $request->input('requestID');

        // Validate requestID format (UUID)
        if (! preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $requestID)) {
            Log::warning('Invalid RequestID format:', ['RequestID' => $requestID]);

            return ['success' => false, 'error' => 'Invalid Request ID format'];
        }

        // Check permissions and OperatioEntityType
        $entity = DB::table('Cust_entity')->where('Id', $requestID)->first();
        if (! $entity) {
            Log::error('❌ STEP 3: Entity Not Found', ['requestID' => $requestID]);

            return [
                'success' => false,
                'data' => [
                    'Status' => 'Failed',
                    'Remark' => 'Entity not found with ID: ' . $requestID,
                ],
            ];
        }

        if (! Auth::user()->can('create-client-party-SCM') && $entity->OperatioEntityType !== 'Client without Debtor or Vendor') {
            Log::warning('SCM Permission Denied:', [
                'UserID' => Auth::user()->Id,
                'Permissions' => Auth::user()->getAllPermissions()->pluck('name')->toArray(),
                'OperatioEntityType' => $entity->OperatioEntityType,
            ]);

            return ['success' => false, 'error' => 'Unauthorized SCM Access'];
        }

        try {
            // Execute the stored procedure
            $results = DB::select(
                'SET NOCOUNT ON;
             DECLARE @Status VARCHAR(30), @Remark VARCHAR(1000), @ClientCode VARCHAR(60),
                     @ClientID UNIQUEIDENTIFIER, @OtherCode VARCHAR(60), @OtherID UNIQUEIDENTIFIER;
             EXEC [dbo].[Sp_OperationEntityCreation]
                 @RequestID = ?,
                 @Status = @Status OUTPUT,
                 @Remark = @Remark OUTPUT,
                 @ClientCode = @ClientCode OUTPUT,
                 @ClientID = @ClientID OUTPUT,
                 @OtherCode = @OtherCode OUTPUT,
                 @OtherID = @OtherID OUTPUT;
             SELECT @Status AS Status, @Remark AS Remark, @ClientCode AS ClientCode,
                    @ClientID AS ClientID, @OtherCode AS OtherCode, @OtherID AS OtherID',
                [$requestID]
            );

            // Check if results are valid
            if (! empty($results) && isset($results[0])) {
                $result = (array) $results[0];

                // Log success
                Log::info('SCM Creation Success:', [
                    'RequestID' => $requestID,
                    'Result' => $result,
                    'OperatioEntityType' => $entity->OperatioEntityType,
                ]);

                // Check stored procedure status
                if ($result['Status'] === 'Success') {
                    return ['success' => true, 'data' => $result];
                } else {
                    Log::warning('Stored Procedure Failed:', [
                        'RequestID' => $requestID,
                        'Remark' => $result['Remark'],
                        'OperatioEntityType' => $entity->OperatioEntityType,
                    ]);

                    return ['success' => false, 'error' => $result['Remark']];
                }
            } else {
                Log::warning('⚠️ STEP 14a: No Cust_Clients Entry Found', [
                    'requestID' => $requestID,
                    'searchedCode' => $restoreData['ClientCode'] ?? $entity->Code,
                ]);
            }

            // Check Cust_Party
            $scmParty = DB::connection('sqlsrv2')
                ->table('Cust_Party')
                ->where('PartyCode', $restoreData['OtherPartyCode'] ?? $entity->Code)
                ->first();

            if ($scmParty) {
                Log::info('✅ STEP 14b: Cust_Party Entry Found', [
                    'requestID' => $requestID,
                    'partyCode' => $scmParty->PartyCode,
                    'partyID' => $scmParty->PartyID,
                    'partyName' => $scmParty->PartyName,
                    'partyType' => $scmParty->PartyType,
                ]);
            } else {
                Log::warning('⚠️ STEP 14b: No Cust_Party Entry Found', [
                    'requestID' => $requestID,
                    'searchedCode' => $restoreData['OtherPartyCode'] ?? $entity->Code,
                ]);
            }
        } catch (QueryException $e) {
            Log::error('Error executing Sp_OperationEntityCreation:', [
                'RequestID' => $requestID,
                'Error' => $e->getMessage(),
                'OperatioEntityType' => $entity->OperatioEntityType,
            ]);

            return ['success' => false, 'error' => 'Entity Creation Failed: ' . $e->getMessage()];
        }

        Log::info('═══════════════════════════════════════════════════════════════════════');

        return [
            'success' => false,
            'data' => [
                'Status' => $status,
                'Remark' => $remarks,
            ],
        ];
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ClientParty  $clientParty
     * @return \Illuminate\Http\Response
     */
    public function destroy($clientParty)
    {
        if (! Auth::user()->can('delete-entity-request')) {
            alert()->warning('Warning !', 'Unauthorised.')->showConfirmButton();

            return redirect()->back();
        }
    }

    public function reject()
    {
        $user = Auth::user();

        if (! $user->can('approve-entity-request') && ! $user->can('create-client-party')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $id = request()->input('id');
        $level = request()->input('level');   // usually 'Rejected'
        $reason = request()->input('reason', 'No reason provided');

        $entity = ClientParty::findOrFail($id);

        $payload = [
            'OperationType' => 'OperationalEntityUpdate',
            'Action' => 'Add',
            'SubmittedDatetime' => now()->format('YmdHis'),
            'SubmittedBy' => $user->LoginID ?? $user->email ?? $user->full_name ?? 'system',
            'RequestNo' => $entity->Code,
            'Status' => $level,
            'Remark' => $reason,
        ];

        try {
            $response = $this->executeJsonEntityProcedure($payload);

            // Optional: send email (keep your existing logic)
            try {
                $creator = User::where('full_name', $entity->CreatedBy)
                    ->orWhere('LoginID', $entity->CreatedBy)
                    ->first();

                if ($creator && $creator->Email) {
                    Mail::to($creator->Email)->send(new RejectEntity($entity));
                }
            } catch (\Throwable $e) {
                \Log::warning('Rejection email failed', ['error' => $e->getMessage()]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Entity rejected at ' . $level,
                'remarks' => $response['Remarks'] ?? [],
            ]);
        } catch (\Exception $e) {
            \Log::error('Reject via JSON failed', [
                'id' => $id,
                'level' => $level,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage() ?: 'Failed to reject entity',
            ], 422);
        }
    }

    public function approve(HttpRequest $request)
    {
        $user = Auth::user();

        if (! $user->can('approve-entity-request')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $id = $request->input('id');
        $entity = ClientParty::findOrFail($id);

        // Allowed statuses for approval
        $allowedStatuses = [
            'Submitted',                        // Operational
            'PendingCountryLevelApproval',      // Accounting initial
            'PendingGroupLevelApproval',        // Accounting after country
            'PendingITVerification',            // Accounting final pending → ready for creation
        ];

        if (! in_array($entity->Status, $allowedStatuses)) {
            \Log::warning('Attempt to approve entity in invalid status', [
                'entity_id' => $id,
                'code' => $entity->Code ?? 'unknown',
                'current_status' => $entity->Status,
            ]);

            return response()->json([
                'success' => false,
                'message' => "This entity is not ready for approval. Current: {$entity->Status}",
            ], 422);
        }

        // Detect accounting entity
        $accountingIndicators = [
            'LegalEntityType',
            'LicenceTypeCode',
            'LicenceTypeValue',
            'CreditDays',
            'CreditLimit',
            'CreditLimitCurrencyCode',
            'BillingCurrencyCode',
            'BaseCurrencyCode',
            'DebtorType',
            'VendorType',
        ];

        $hasAccountingFields = false;
        foreach ($accountingIndicators as $field) {
            if (! empty($entity->$field)) {
                $hasAccountingFields = true;
                break;
            }
        }

        // Decide operation type and status
        $operationType = 'OperationalEntityCreation'; // default for operational
        $statusToSet = 'Create';                    // default for final

        if ($hasAccountingFields) {
            $intermediateStatuses = [
                'PendingCountryLevelApproval',
                'PendingGroupLevelApproval',
            ];

            if (in_array($entity->Status, $intermediateStatuses)) {
                // Intermediate approval → status update
                $operationType = 'AccountingEntityUpdate';

                $statusToSet = $request->input('status');

                if (! $statusToSet) {
                    return response()->json([
                        'success' => false,
                        'message' => 'New status is required for intermediate approval.',
                    ], 422);
                }

                $allowedUpdate = ['PendingGroupLevelApproval', 'Review', 'Rejected', 'PendingITVerification'];

                if (! in_array($statusToSet, $allowedUpdate)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid status for update. Allowed: ' . implode(', ', $allowedUpdate),
                    ], 422);
                }
            } else {
                // Final accounting creation
                $operationType = 'AccountingEntityCreation';
                $statusToSet = 'Create';
            }
        }

        // Build payload for main sp_EntityMain
        $payload = [
            'OperationType' => $operationType,
            'Action' => 'Add',
            'SubmittedDatetime' => now()->format('YmdHis'),
            'SubmittedBy' => $user->LoginID ?? $user->full_name ?? 'system',
            'RequestNo' => $entity->Code,
            'Status' => $statusToSet,
            'Remark' => $request->input('comment', 'Approved by ' . ($user->full_name ?? 'system')),
        ];

        // For FINAL accounting creation: call D365 first to get DebtorNo / VendorNo
        $debtorNo = null;
        $vendorNo = null;

        if ($operationType === 'AccountingEntityCreation') {
            $scmRequest = new HttpRequest(['requestID' => $entity->Id]);
            $scmResult = $this->CreateClientPartySCM($scmRequest);

            \Log::info('D365 creation result during final approval', [
                'entity_id' => $entity->Id,
                'result' => $scmResult,
            ]);

            if (! $scmResult['success'] || $scmResult['data']['Status'] !== 'Success') {
                return response()->json([
                    'success' => false,
                    'message' => 'D365 creation failed: ' . ($scmResult['data']['Remark'] ?? $scmResult['error'] ?? 'Unknown error'),
                ], 500);
            }

            // Extract DebtorNo / VendorNo
            $debtorNo = $scmResult['data']['DebtorNo'] ?? null;
            $vendorNo = $scmResult['data']['VendorNo'] ?? null;

            // Add to payload
            if ($debtorNo) {
                $payload['DebtorNo'] = $debtorNo;
            }
            if ($vendorNo) {
                $payload['VendorNo'] = $vendorNo;
            }
        }

        \Log::info('approve() - Sending to main SP', [
            'entity_id' => $id,
            'code' => $entity->Code,
            'current_status' => $entity->Status,
            'operation_type' => $operationType,
            'payload' => $payload,
        ]);

        try {
            $response = $this->executeJsonEntityProcedure($payload);

            \Log::info('approve() - SP response', [
                'entity_id' => $id,
                'code' => $entity->Code,
                'status' => $response['Status'] ?? 'missing',
                'remarks' => $response['Remarks'] ?? [],
            ]);

            $remarks = collect($response['Remarks'] ?? [])
                ->flatten()
                ->map(fn($v) => is_array($v) ? json_encode($v, JSON_UNESCAPED_SLASHES) : (string) $v)
                ->filter()
                ->implode(' • ') ?: 'Operation completed';

            $success = ($response['Status'] ?? 'Failed') === 'Success';

            return response()->json([
                'success' => $success,
                'new_status' => $success ? $statusToSet : $entity->Status,
                'message' => $success ? $remarks : ($remarks ?: 'Operation completed with warnings'),
                'remarks' => $response['Remarks'] ?? [],
            ]);
        } catch (\Exception $e) {
            \Log::error('Finalization failed', [
                'entity_id' => $id,
                'code' => $entity->Code ?? 'unknown',
                'operation' => $operationType,
                'payload' => $payload,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage() ?: 'Finalization failed – check logs',
            ], 422);
        }
    }

    /**
     * Get appropriate approval comment based on type and status
     */
    private function getApprovalComment($isAccounting, $status)
    {
        if ($isAccounting) {
            switch ($status) {
                case 'PendingCountryLevelApproval':
                    return 'Entity Pending Country Level Approval';
                case 'PendingGroupLevelApproval':
                    return 'Entity Pending Group Level Approval';
                case 'PendingITVerification':
                    return 'Entity Pending IT Verification';
                case 'Created':
                    return 'Entity Approved and Created in SCM';
                default:
                    return 'Entity Status Updated';
            }
        } else {
            return $status === 'Submitted' ? 'Entity Submitted' : 'Entity Approved and Created in SCM';
        }
    }

    /**
     * Get appropriate approval message based on type and status
     */
    private function getApprovalMessage($isAccounting, $status)
    {
        if ($isAccounting) {
            switch ($status) {
                case 'PendingCountryLevelApproval':
                    return 'Entity pending country level approval';
                case 'PendingGroupLevelApproval':
                    return 'Entity pending group level approval';
                case 'PendingITVerification':
                    return 'Entity pending IT verification';
                case 'Created':
                    return 'Entity created successfully';
                default:
                    return 'Entity status updated';
            }
        } else {
            return $status === 'Submitted' ? 'Entity submitted successfully' : 'Entity created successfully';
        }
    }

    public function approvals(Request $request)
    {
        $user = Auth::user();

        if (! $user->can('approve-entity-request')) {
            Alert::warning('Warning!', 'You are not authorised to view this page. Contact Admin')->showConfirmButton();

            return redirect()->route('home');
        }

        // Calculate counts for info boxes
        $counts = [
            'not_submitted' => ClientParty::where('Status', 'Not Submitted')
                ->when(! $user->can('approve-all-entity-request'), fn($q) => $q->where('OfficeCode', $user->OfficeCode))
                ->count(),
            'pending_country' => ClientParty::where('Status', 'PendingCountryLevelApproval')
                ->when(! $user->can('approve-all-entity-request'), fn($q) => $q->where('OfficeCode', $user->OfficeCode))
                ->count(),
            'pending_group' => ClientParty::where('Status', 'PendingGroupLevelApproval')
                ->when(! $user->can('approve-all-entity-request'), fn($q) => $q->where('OfficeCode', $user->OfficeCode))
                ->count(),
            'submitted' => ClientParty::where('Status', 'Submitted')
                ->when(! $user->can('approve-all-entity-request'), fn($q) => $q->where('OfficeCode', $user->OfficeCode))
                ->count(),
            'all_pending' => ClientParty::whereIn('Status', [
                'Not Submitted',
                'Submitted',
                'PendingCountryLevelApproval',
                'PendingGroupLevelApproval',
            ])
                ->when(! $user->can('approve-all-entity-request'), fn($q) => $q->where('OfficeCode', $user->OfficeCode))
                ->count(),
        ];

        return view('client-party.approve', compact('counts'));
    }

    public function deleteFile($id)
    {
        if (! Auth::user()->can('create-client-party-SCM')) {
            alert()->warning('Warning !', 'You are not authorised to veiw this page. Contact Admin')->showConfirmButton();

            return redirect()->route('home');
        }

        $file = ClientPartyAttahment::findOrFail($id);
        $file->IsActive = 0;
        $file->IsDeleted = 1;
        $file->DeletedOn = Carbon::now();
        $file->DeletedBy = Auth::user()->ID;
        $file->save();

        return response()->json(['ok', 200]);
    }

    public function checkLicense()
    {
        try {
            $input = request()->all();
            $partyType = $input['partyType'] ?? 'Debtor';
            $licenseNo = $input['licenseNo'] ?? null;
            $officeCode = $input['officeCode'] ?? null;

            $partyType = str_contains($partyType, 'Debtor') ? 'Debtor' : $partyType;

            Log::info('Checking license for duplicate:', [
                'partyType' => $partyType,
                'licenseNo' => $licenseNo,
                'officeCode' => $officeCode,
            ]);

            if (empty($partyType) || empty($licenseNo) || empty($officeCode)) {
                return response()->json(['error' => 'Missing required fields'], 400);
            }

            // Execute sp_LicenseNoCheck
            $results = DB::select(
                'SET NOCOUNT ON;
                 DECLARE @Status AS VARCHAR(20),
                         @Remark AS VARCHAR(100),
                         @IsDuplicate AS BIT;
                 EXEC dbo.sp_LicenseNoCheck @officeCode = ?, @PartyType = ?, @LicenseNo = ?,
                      @Status = @Status OUTPUT, @Remark = @Remark OUTPUT, @IsDuplicate = @IsDuplicate OUTPUT;
                 SELECT @Status AS Status, @Remark AS Remark, @IsDuplicate AS IsDuplicate',
                [$officeCode, $partyType, $licenseNo]
            );

            if (empty($results)) {
                Log::warning('sp_LicenseNoCheck returned no results', [
                    'partyType' => $partyType,
                    'licenseNo' => $licenseNo,
                    'officeCode' => $officeCode,
                ]);

                return response()->json(['error' => 'Failed to check license'], 500);
            }

            $result = $results[0];

            Log::info('sp_LicenseNoCheck response:', (array) $result);

            return response()->json([
                'status' => $result->Status,
                'remark' => $result->Remark,
                'isDuplicate' => $result->IsDuplicate,
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('sp_LicenseNoCheck Error: ' . $e->getMessage(), [
                'partyType' => $partyType,
                'licenseNo' => $licenseNo,
                'officeCode' => $officeCode,
                'sql' => $e->getSql(),
            ]);

            return response()->json(['error' => 'Database error while checking license'], 500);
        }
    }

    public function getCompanies()
    {
        try {
            $entityType = Request::get('entityType');
            $query = Request::get('query', '');

            if (! $entityType) {
                return response()->json(['error' => 'Entity type is required'], 400);
            }

            // Execute stored procedure with SET NOCOUNT ON
            $results = DB::select('SET NOCOUNT ON; EXEC dbo.sp_EntitySearchList ?, ?', [$entityType, $query]);

            // Handle string-based error response
            if ($results && is_array($results) && count($results) > 0 && is_string($results[0])) {
                \Log::error('sp_EntitySearchList failed for entityType: ' . $entityType . ', Error: ' . $results[0]);

                return response()->json(['error' => $results[0]], 500);
            }

            // Handle structured error response
            if ($results && count($results) > 0 && isset($results[0]->Name) && strpos($results[0]->Name, 'Failed') === 0) {
                \Log::error('sp_EntitySearchList failed for entityType: ' . $entityType . ', Error: ' . $results[0]->Name);

                return response()->json(['error' => $results[0]->Name], 500);
            }

            // Map results to expected format
            $companies = array_map(function ($item) {
                return [
                    'id' => $item->Id,
                    'code' => $item->Code,
                    'name' => $item->Name,
                ];
            }, $results);

            // Handle empty result set
            if (empty($companies)) {
                \Log::warning('sp_EntitySearchList returned no results for entityType: ' . $entityType);

                return response()->json([]);
            }

            return response()->json($companies);
        } catch (QueryException $e) {
            \Log::error('Error executing sp_EntitySearchList for entityType: ' . ($entityType ?? 'unknown') . ', SQL Error: ' . $e->getMessage());

            return response()->json(['error' => 'Failed to fetch companies: ' . $e->getMessage()], 500);
        }
    }

    public function getCompanyDetails()
    {

        try {

            $entityType = Request::get('entityType');
            $companyId = Request::get('companyId');

            if (! $entityType || ! $companyId) {
                return response()->json(['error' => 'Entity type and company ID are required'], 400);
            }

            // Execute stored procedure
            $results = DB::select('SET NOCOUNT ON; EXEC sp_EntityAddressDetails ?, ?', [$entityType, $companyId]);

            // Handle string-based error
            if ($results && is_array($results) && count($results) > 0 && is_string($results[0])) {
                \Log::error('tityAddressDetails failed for entityType: ' . $entityType . ', companyId: ' . $companyId . ', Error: ' . $results[0]);

                return response()->json(['error' => $results[0]], 500);
            }

            // Handle structured error
            if ($results && count($results) > 0 && isset($results[0]->Name) && strpos($results[0]->Name, 'Failed') === 0) {
                \Log::error('sp_EntityAddressDetails failed for entityType: ' . $entityType . ', companyId: ' . $companyId . ', Error: ' . $results[0]->Name);

                return response()->json(['error' => $results[0]->Name], 500);
            }

            // Map results to form fields
            $details = array_map(function ($item) {
                // Use Address_CountryId if available
                $countryId = property_exists($item, 'Address_CountryId') && $item->Address_CountryId ? $item->Address_CountryId : null;
                $countryCode = property_exists($item, 'CountryCode2') && $item->CountryCode2 ? $item->CountryCode2 : (property_exists($item, 'CountryCode') ? $item->CountryCode : null);
                $countryName = property_exists($item, 'CountryName2') && $item->CountryName2 ? $item->CountryName2 : (property_exists($item, 'CountryName') ? $item->CountryName : null);

                // Optional: Validate countryId using Cust_Country if needed
                if ($countryCode && ! $countryId) {
                    try {
                        $country = DB::selectOne(
                            'SELECT CountryID FROM dbo.Cust_Country WHERE CountryCode = ?',
                            [$countryCode]
                        );
                        $countryId = $country ? $country->CountryID : null;
                    } catch (QueryException $e) {
                        \Log::warning('Failed to fetch CountryID for code: ' . $countryCode . ', Error: ' . $e->getMessage());
                        $countryId = null;
                    }
                }

                // Base mapping
                $mapped = [
                    'companyId' => $item->Id,
                    'companyCode' => $item->Code,
                    'companyName' => $item->Name,
                    'addressLine1' => property_exists($item, 'Address_Line1') ? $item->Address_Line1 : null,
                    'addressLine2' => property_exists($item, 'Address_Line2') ? $item->Address_Line2 : null,
                    'cityName' => property_exists($item, 'Address_City') ? $item->Address_City : null,
                    'cityID' => null,
                    'cityCode' => null,
                    'zipCode' => property_exists($item, 'Address_Pin') ? $item->Address_Pin : null,
                    'provinceName' => property_exists($item, 'Address_State') ? $item->Address_State : null,
                    'provinceID' => property_exists($item, 'Address_StateId') ? $item->Address_StateId : null,
                    'provinceCode' => null,
                    'countryID' => $countryId,
                    'countryCode' => $countryCode,
                    'countryName' => $countryName,
                    'phoneNo' => property_exists($item, 'Cont_Bch_PhoneNumbers')
                        ? ($item->Cont_Bch_PhoneNumbers !== '-' ? $item->Cont_Bch_PhoneNumbers : null)
                        : (property_exists($item, 'Contact_PhoneNumbers')
                            ? ($item->Contact_PhoneNumbers !== '-' ? $item->Contact_PhoneNumbers : null)
                            : null),

                    'contractEmail' => property_exists($item, 'Cont_Bch_Email')
                        ? ($item->Cont_Bch_Email !== '-' ? $item->Cont_Bch_Email : null)
                        : (property_exists($item, 'Contact_Email')
                            ? $item->Contact_Email
                            : null),
                    'contactId' => property_exists($item, 'ContactId') ? $item->ContactId : null,
                    'addressType' => property_exists($item, 'Cont_Bch_PhoneNumbers') && $item->Cont_Bch_PhoneNumbers ? $item->Cont_Bch_PhoneNumbers : null,
                    'firstName' => property_exists($item, 'Cont_Bch_PersonName_FirstName')
                        ? $item->Cont_Bch_PersonName_FirstName
                        : (property_exists($item, 'Contact_PersonName_FirstName')
                            ? $item->Contact_PersonName_FirstName
                            : null),

                    'lastName' => property_exists($item, 'Cont_Bch_PersonName_LastName')
                        ? $item->Cont_Bch_PersonName_LastName
                        : (property_exists($item, 'Contact_PersonName_LastName')
                            ? $item->Contact_PersonName_LastName
                            : null),
                    'department' => property_exists($item, 'Cont_Bch_Dept')
                        ? ($item->Cont_Bch_Dept !== '-' ? $item->Cont_Bch_Dept : null)
                        : null,
                ];

                return $mapped;
            }, $results);

            // Return empty if no results
            if (empty($details)) {
                \Log::warning('sp_EntityAddressDetails returned no results for entityType: ' . $entityType . ', companyId: ' . $companyId);

                return response()->json([]);
            }

            // Select primary address (main:1 if available, else first)
            $primaryDetail = array_reduce($details, function ($carry, $item) {
                return $item['addressType'] && strpos($item['addressType'], 'main:1') !== false ? $item : $carry;
            }, $details[0]);

            return response()->json($primaryDetail);
        } catch (QueryException $e) {
            \Log::error('Error executing sp_EntityAddressDetails for entityType: ' . ($entityType ?? 'unknown') . ', companyId: ' . ($companyId ?? 'unknown') . ', SQL Error: ' . $e->getMessage());

            return response()->json(['error' => 'Failed to fetch company details: ' . $e->getMessage()], 500);
        }
    }

    public function fetchAddressDetails(Request $request)
    {
        $companyId = $request->input('companyId');
        $partyEntityType = $request->input('partyEntityType');

        try {
            $companyDetails = DB::select(
                'SET NOCOUNT ON;
             EXEC [dbo].[tityAddressDetails] @PartyType = ?, @PartyID = ?',
                [$partyEntityType, $companyId]
            );

            if (! empty($companyDetails) && isset($companyDetails[0])) {
                return response()->json([
                    'success' => true,
                    'data' => (array) $companyDetails[0],
                ]);
            } else {
                Log::warning('No valid data found in sp_EntityAddressDetails for companyId: ' . $companyId);

                return response()->json(['success' => false]);
            }
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('Failed to query sp_EntityAddressDetails for companyId: ' . $companyId . ', Error: ' . $e->getMessage());

            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Preview/Download attachment file
     *
     * @param  string  $attachmentId
     * @return \Illuminate\Http\Response
     */
}
