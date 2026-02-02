<?php

namespace App\Http\Controllers;

use App\Models\ClientParty;
use App\Http\Requests\StoreClientPartyRequest;
use App\Http\Requests\UpdateClientPartyRequest;
use App\Mail\RejectEntity;
use App\Models\ClientPartyAttahment;
use App\Models\EntityHistory;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Validator; // Correct import

use Illuminate\Support\Facades\Storage;
use Illuminate\Database\QueryException;
use JeroenNoten\LaravelAdminLte\Components\Form\Select;
use Ramsey\Uuid\Guid\Guid;
use RealRashid\SweetAlert\Facades\Alert;
use PDF;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request as HttpRequest;



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
        $this->clientParty = new ClientParty();
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
        if (!\request()->user()->can('view-client-party')) {
            abort(403, "You are not allowed to view this page.");
        }

        $clientParty = $this->clientParty;
        return view('client-party.index', compact('clientParty', 'title'));
    }



    public function create()
    {
        if (!\request()->user()->hasPermissionTo('request-client-party')) {
            abort(403, "You are not allowed to view this page.");
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

        if (!empty($officeCode)) {
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
                            'CreditDays' => $term->Code // Use Code as CreditDays
                        ];
                    }, $response);
                }
                Log::info('Fetched payTerms in create:', ['payTerms' => $payTerms, 'officeCode' => $officeCode]);
            } catch (\Illuminate\Database\QueryException $e) {
                Log::error('sp_PaymentTerms Error in create: ' . $e->getMessage(), [
                    'officeCode' => $officeCode,
                    'sql' => $e->getSql()
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
                        $debtorVendors[] = array(
                            "value" => $resp->PartyID,
                            "label" => $resp->PartyName,
                            "code" => $resp->PartyCode,
                        );
                    }
                }
            }
            return response()->json($debtorVendors);
        }
    }

    /**
     * Get country listing
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
                        $cities[] = array(
                            "value" => $resp->CountryID,
                            "label" => $resp->CountryName,
                            "code" => $resp->CountCode,
                        );
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
                    $provinces[] = array(
                        "value" => $resp->CountryID,
                        "label" => $resp->CountryName,
                        "code" => $resp->CountCode,
                    );
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
                    $locations[] = array(
                        "value" => $location->LocationID,
                        "label" => $location->LocationName,
                    );
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
                    $baseCurrency[] = array(
                        "value" => $location->BaseCurrencyID,
                        "label" => $location->BaseCurrencyName,
                        "code" => $location->BaseCurrencyCode,
                    );
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
        return response()->json(["Vendors" => $v, "Debtors" => $d]);
    }

    public function autocompleteClient(Request $request)
    {
        $search = \request()->all()['query'];
        $results = ClientParty::where('OperationalEntityType', "Client without Debtor or Vendor")
            ->where('Name', 'LIKE', "%{$search}%")->pluck('Name');
        return response()->json($results);
    }

    public function fetchClientDetails($companyName)
    {
        $client = ClientParty::where('OperationalEntityType', "Client without Debtor or Vendor")->where('Name', $companyName)->first();
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
        if ($type == "master") {
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
                        $clients[] = array(
                            "value" => $resp->ClientID,
                            "label" => $resp->ClientName,
                            "code" => $resp->ClientCode,
                        );
                    }
                }
            }
        } else if ($type == "local") {
            $results = ClientParty::select('name', 'Id', 'code')
                ->where('OperationalEntityType', "Client + Shipper + Consignee")
                ->where('Name', 'LIKE', '%' . $query . '%')->get();
            if (count($results) > 0) {
                foreach ($results as $resp) {
                    if (isset($resp->Id)) {
                        $clients[] = array(
                            "value" => $resp->Id,
                            "label" => $resp->name,
                            "code" => $resp->Code,
                        );
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
                    $partyList[] = array(
                        "value" => $resp->PartyID,
                        "label" => $resp->PartyName,
                        "code" => $resp->PartyCode,
                    );
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
    public function getPaymentTermList($officeCode = null, Request $request = null)
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
                    'CreditDays' => $term->Code // Use Code as CreditDays
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
                'sql' => $e->getSql()
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
        if (!empty($query)) {
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
                        $infos[] = array(
                            "value" => $resp->Id,
                            "label" => $resp->PersonName,
                        );
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
                    $list[] = array(
                        "value" => $location->PartyID,
                        "label" => $location->PartyName,
                        "code" => $location->PartyCode,
                    );
                }
            }
        }
        return $list;
    }
///NEW OPERATIONAL ENTITY STORE FUNCTION


/**
 * Centralized call to the new JSON-based entity procedure
 *
 * @param array $payload
 * @return array Decoded JSON response {Status: "Success"|"Failed", Remarks: [...]}
 * @throws \Exception on failure or invalid response
 */
private function executeJsonEntityProcedure(array $payload): array
{
    try {
        $jsonInput = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    } catch (\JsonException $e) {
        \Log::error('Failed to encode payload to JSON', [
            'payload' => $payload,
            'error'   => $e->getMessage(),
        ]);
        throw new \Exception('Failed to prepare JSON input for stored procedure');
    }

    \Log::info('sp_CustEntity payload (JSON)', [
        'operation'    => $payload['OperationType'] ?? 'unknown',
        'RequestNo'    => $payload['RequestNo'] ?? '(new)',
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
             EXEC [dbo].[sp_CustEntity] @InputJson = ?, @OutputJson = @OutputJson OUTPUT;
             SELECT @OutputJson AS OutputJson',
            [$jsonInput]
        );

        if (empty($results) || !isset($results[0]->OutputJson) || trim($results[0]->OutputJson) === '') {
            \Log::error('sp_CustEntity returned empty or null OutputJson', [
                'sent_payload' => $jsonInput,
            ]);
            throw new \Exception('No valid response from entity procedure');
        }

        $rawOutput = $results[0]->OutputJson;
        $output = json_decode($rawOutput, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            \Log::error('Invalid JSON from sp_CustEntity', [
                'raw_output'   => $rawOutput,
                'json_error'   => json_last_error_msg(),
                'sent_payload' => $jsonInput,
            ]);
            throw new \Exception('Invalid JSON response from stored procedure');
        }

        if (!isset($output['Status'])) {
            \Log::error('Response missing "Status" field', [
                'raw_output'   => $rawOutput,
                'sent_payload' => $jsonInput,
            ]);
            throw new \Exception('Response missing "Status" field');
        }

        \Log::info('sp_CustEntity response received', [
            'status'       => $output['Status'],
            'remarks'      => $output['Remarks'] ?? null,
            'sent_operation' => $payload['OperationType'] ?? 'unknown',
        ]);

        if ($output['Status'] !== 'Success') {
            $firstRemark = $output['Remarks'][0]['Remark'] ?? 'Unknown error from database';
            \Log::warning('sp_CustEntity failed', [
                'status'       => $output['Status'],
                'remark'       => $firstRemark,
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

    if (!$user->can('request-client-party')) {
        abort(403, "You are not allowed to request client parties.");
    }

    $operationType = $request->input('OperationType', 'OperationalEntity');
    $partyEntityType = $request->partyEntityType ?? '';

    \Log::info('Incoming entity submission', [
        'operation_type' => $operationType,
        'party_entity_type' => $partyEntityType,
        'legal_entity_type' => $request->legalEntityType,
        'has_accounting_fields' => $request->has('creditDays') || $request->has('maxCreditLimit') || $request->has('debtorType'),
        'request_data' => $request->except(['_token', 'attachmentNames', 'attachmentDescription']),
    ]);

    // ── Determine ClientCode vs OtherPartyCode based on entity type ─────
    $clientCode = '';
    $otherPartyCode = '';

    if (in_array($partyEntityType, ['Client', 'Agent / Forwarder'])) {
        // For Client and Agent/Forwarder, use ClientCode
        $clientCode = $request->clientCode ?? '';
    } else {
        // For Airline, Shipping Line, Trucker, etc., use OtherPartyCode
        $otherPartyCode = $request->otherCode ?? '';
    }

    $payload = [
        "OperationType"     => $operationType,
        "Action"            => "Add",
        "SubmittedDatetime" => now()->format('YmdHis'),
        "SubmittedBy"       => $user->LoginID ?? $user->email ?? $user->full_name ?? 'system',
        "Name"              => $request->companyName ?? trim($request->input('name', '')),
        "Address_Line1"     => $request->addressLine1 ?? '',
        "Address_Line2"     => $request->addressLine2 ?? '',
        "Address_City"      => $request->cityName ?? '',
        "Address_CityCode"  => $request->cityCode ?? '',
        "Address_State"     => $request->provinceName ?? '',
        "Address_StateCode" => $request->provinceCode ?? '',
        "Address_CountryCode" => $request->countryCode ?? '',
        "Address_Pin"       => $request->zipCode ?? '',
        "Cont_Bch_Dept"     => $request->department ?? '',
        "Cont_Bch_PersonName_FirstName" => $request->firstName ?? '',
        "Cont_Bch_PersonName_LastName"  => $request->lastName ?? '',
        "Cont_Bch_PhoneNumbers" => $request->phoneNo ?? '',
        "Cont_Bch_Email"    => $request->contractEmail ?? '',
        "OfficeCode"        => $request->officeCode ?? $user->OfficeCode ?? '',
        "OperationalEntityType" => $partyEntityType,
        "ClientCode"        => $clientCode,
        "OtherPartyCode"    => $otherPartyCode,
    ];

    if ($operationType === 'AccountingEntity' || $request->has('legalEntityType')) {
        $creditDaysValue = $request->creditDays !== '' && is_numeric($request->creditDays) ? $request->creditDays : '0';
        $creditLimitValue = $request->maxCreditLimit !== '' && is_numeric($request->maxCreditLimit) ? $request->maxCreditLimit : '0';

        $payload = array_merge($payload, [
            "LegalEntityType"          => $request->legalEntityType ?? '',
            "CreditDays"               => $creditDaysValue,
            "CreditLimit"              => $creditLimitValue,
            "CreditLimitCurrencyCode"  => $request->creditLimitCurrencyCode ?? '',
            "BillingCurrencyCode"      => $request->billingCurrencyCode ?? '',
            "BaseCurrencyCode"         => $request->baseCurrencyCode ?? '',
            "LicenceTypeCode"          => $request->licenceTypeCode ?? '',
            "LicenceTypeValue"         => $request->licenceTypeValue ?? '',
            "DebtorType"               => $request->debtorType ?? '',
            "VendorType"               => $request->vendorType ?? '',
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

        if (!$requestNo) {
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

        if (!$entityRecord) {
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
                if (!$file || !$file->isValid()) {
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




    public function getOfficeNumberSeries($officeCode = null, $entityType = null)
    {

        try {
            // Add validation for required parameters
            if (!$officeCode || !$entityType) {
                Log::error('getOfficeNumberSeries: Missing required parameters', [
                    'officeCode' => $officeCode,
                    'entityType' => $entityType
                ]);
                return null;
            }

            Log::info('getOfficeNumberSeries called', [
                'officeCode' => $officeCode,
                'entityType' => $entityType
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
                    'query_result' => $numSeries->toArray()
                ]);
                return null;
            }

            $seriesNo = $numSeries->first(); // Use first() instead of [0] for better error handling

            Log::info('getOfficeNumberSeries result', [
                'officeCode' => $officeCode,
                'entityType' => $entityType,
                'seriesNo' => $seriesNo,
                'found_count' => $numSeries->count()
            ]);

            return $seriesNo;
        } catch (Exception $e) {
            Log::error('getOfficeNumberSeries exception', [
                'officeCode' => $officeCode,
                'entityType' => $entityType,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
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
 * @param string $attachmentId - The unique ID of the attachment
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
            'entityId' => $attachment->CustEntityID
        ]);

        // Check if file exists in Azure storage
        if (!Storage::disk('azure')->exists($attachment->FilePath)) {
            Log::error('File not found in Azure storage:', [
                'filePath' => $attachment->FilePath,
                'attachmentId' => $attachmentId
            ]);
            abort(404, 'File not found in storage');
        }

        // Get file extension to determine content type
        $extension = pathinfo($attachment->FileName, PATHINFO_EXTENSION);
        $mimeType = $extension === 'pdf' ? 'application/pdf' : 'application/octet-stream';

        Log::info('File retrieved successfully:', [
            'attachmentId' => $attachmentId,
            'fileName' => $attachment->FileName,
            'mimeType' => $mimeType
        ]);

        // Download file from Azure and return as inline (for preview)
        return Storage::disk('azure')->download(
            $attachment->FilePath,
            $attachment->FileName,
            [
                'Content-Type' => $mimeType,
                'Content-Disposition' => 'inline; filename="' . $attachment->FileName . '"'
            ]
        );

    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        Log::error('Attachment not found in database:', [
            'attachmentId' => $attachmentId,
            'error' => $e->getMessage()
        ]);

        return response()->json([
            'error' => 'Attachment not found'
        ], 404);

    } catch (\Exception $e) {
        Log::error('Preview failed with exception:', [
            'attachmentId' => $attachmentId,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);

        return response()->json([
            'error' => 'Error retrieving file: ' . $e->getMessage()
        ], 500);
    }
}

    public function createLegalEntityType(string $type, array $post_data)
    {

        $debtor_url = env('DEBTOR_URL');
        $vendor_url = env('VENDER_URL');

        $request_url = (strtolower($type) == 'vendor') ? $vendor_url : $debtor_url;

        $entityToken = $this->entityToken();
        if (!$entityToken['status']) {
            return ['status' => false, 'data' => []];
        }
        log::info('Entity Token Retrieved:', ['status' => $entityToken['status'], 'token' => $entityToken['token']]);
        $response = Http::withToken($entityToken['token'])
            ->post($request_url, $post_data);
        log::info('Legal Entity Creation Response:', [
            'status' => $response->status(),
            'body' => $response->body(),
            'type' => $type,
            'post_data' => $post_data
        ]);
        return json_decode($response->body());
    }

    public function entityToken()
    {
        $token_url = env('D365_TOKEN_URL');

        $response = Http::post($token_url, [
            "username" => env('D365_USERNAME'),
            "password" => env('D365_PASSWORD')
        ]);

        return ["status" => $response->successful(), "token" => $response->body()];
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ClientParty  $clientParty
     * @return \Illuminate\Http\Response
     */





    // 2

    public function show(ClientParty $clientParty)
    {
        $user = request()->user();

        if (!$user->can('view-client-party')) {
            abort(403, "You are not allowed to view this page.");
        }

        $type = $clientParty->LegalEntityType ? 'accounting' : 'operational';
        $approve = false;
        $editForm = true;

        if (($clientParty->Status == 'PendingCountryLevelApproval' || $clientParty->Status == 'PendingGroupLevelApproval') && !$user->can('approve-entity-request')) {
            alert()->warning('Warning !', 'Entity Pending Approval')->showConfirmButton();
            return redirect()->back();
        }

        if ($clientParty->Status == 'Created') {
            $editForm = false;
        }

        if ((Auth::user()->Role != 1 && ($clientParty->Status == 'Not Submitted' || $clientParty->Status == 'Submitted')) && !Auth::user()->can('create-client-party')) {
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
            $scm_office_id = DB::select(DB::raw("SELECT SCMOfficeID FROM Cust_Office WHERE ID = ?", [$clientParty->OfficeID]));
            $currencyList = $this->getCurrencyList($scm_office_id[0]->SCMOfficeID ?? null);
        } else {
            $idX = Auth::user()->office->SCMOfficeID ?? null;
            $currencyList = $this->getCurrencyList($idX);
        }

        $licenceTypes = $this->getLicenceType();
        $tarrifs = ($clientParty->Office == "FIT - Kenya - UPS") ? $this->getTarrif($clientParty->OfficeID) : [];
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
     * @param  \App\Models\ClientParty  $clientParty
     * @return \Illuminate\Http\Response
     */
    public function edit(ClientParty $clientParty)
    {
        $user = Auth::user();
        if (!$user->can('edit-client-party') && !$user->can('approve-my-office-entity-request')) {
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
                'OperationalEntityType' => $clientParty->OperationalEntityType
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
                'currencyList' => DB::table('Currencies')->get()
            ]);
        } catch (Exception $e) {
            Log::error('Failed to load edit form:', [
                'id' => $clientParty->Id,
                'error' => $e->getMessage()
            ]);
            alert()->error('Error', 'Failed to load entity for editing.')->showConfirmButton();
            return redirect()->route('client-party.index');
        }
    }

    public function update(UpdateClientPartyRequest $request, $id)
    {
        $user = Auth::user();

        if (!$user->can('update-client-party')) {
            Log::warning('Unauthorized update attempt', ['userId' => $user?->Id, 'id' => $id]);
            Alert::warning('Warning', 'Unauthorized.')->persistent(true, false);
            return redirect()->back();
        }

        Log::info('Update Request Data:', $request->all());
        Log::info('createBtn:', [isset($request->createBtn)]);
        Log::info('submitBtn:', [isset($request->submitBtn)]);
        Log::info('Checkboxes:', [
            'clientCheck' => isset($request->clientCheck),
            'shipperCheck' => isset($request->shipperCheck),
            'consigneeCheck' => isset($request->consigneeCheck),
            'otherCheck' => isset($request->otherCheck),
            'debtorCheck' => isset($request->debtorCheck),
            'vendorCheck' => isset($request->vendorCheck)
        ]);

        // Log OtherParty fields
        $missingFields = [];
        if (!$request->has('otherInput')) $missingFields[] = 'otherInput';
        if (!$request->has('otherID')) $missingFields[] = 'otherID';
        if (!$request->has('otherCode')) $missingFields[] = 'otherCode';
        if ($missingFields) {
            Log::warning('Missing OtherParty fields in update request', [
                'id' => $id,
                'missing' => $missingFields,
                'otherCheck' => isset($request->otherCheck)
            ]);
        }

        Log::info('OtherParty fields in update request', [
            'id' => $id,
            'otherInput' => $request->otherInput ?? 'NOT_SET',
            'otherID' => $request->otherID ?? 'NOT_SET',
            'otherCode' => $request->otherCode ?? 'NOT_SET',
            'otherCheck' => isset($request->otherCheck)
        ]);

        $partyEntityType = $request->partyEntityType;
        Log::info('Updating Party Type:', ['partyEntityType' => $partyEntityType]);

        $clientName = $request->client ?? $request->clientInput;
        $isAccounting = Request::query('type') === 'accounting';
        $isNewEntity = false;

        // Check if entity exists
        $cp = ClientParty::where('Id', $id)->first();
        if (!$cp && isset($request->createBtn) && !$request->otherCheck && $request->otherInput) {
            $cp = new ClientParty;
            $cp->Id = $id;
            $cp->Code = 'ENT-' . Str::random(6);
            $cp->CreatedBy = $user->Id ?? 'Unknown';
            $cp->CreatedOn = Carbon::now();
            $isNewEntity = true;
        } elseif (!$cp) {
            Log::error('ClientParty not found', ['id' => $id]);
            Alert::error('Error', 'Entity not found.')->persistent(true, false);
            return redirect()->route('client-party.index');
        }

        $currentStatus = $cp->Status ?? 'Not Submitted';
        Log::info('Current entity status', ['id' => $id, 'currentStatus' => $currentStatus]);

        // Enhanced Status logic - No D365 integration at early stages
        $canCreateClientParty = $user->can('create-client-party');
        $hasAccountingEntities = isset($request->debtorCheck) || isset($request->vendorCheck) ||
            isset($request->debtor) || isset($request->vendor) ||
            $request->legalEntityType;

        $status = $currentStatus; // Default to current status

        if (isset($request->submitBtn)) {
            if ($hasAccountingEntities) {
                $status = 'PendingCountryLevelApproval';
            } else {
                $status = 'Submitted';
            }
        } elseif ($user->Role != 1) {
            $status = 'Not Submitted';
        }

        if (isset($request->createBtn)) {
            if ($canCreateClientParty) {
                if ($hasAccountingEntities) {
                    // Accounting entities workflow - D365 integration moved to PendingITVerification
                    switch ($currentStatus) {
                        case 'Not Submitted':
                            $status = 'PendingCountryLevelApproval';
                            break;
                        case 'PendingCountryLevelApproval':
                            $status = 'PendingGroupLevelApproval';
                            break;
                        case 'PendingGroupLevelApproval':
                            $status = 'PendingITVerification';
                            break;
                        case 'PendingITVerification':
                            // D365 integration will happen here, then status becomes 'Created'
                            $status = 'Created';
                            break;
                        default:
                            if (in_array($currentStatus, ['Created', 'Rejected'])) {
                                $status = $currentStatus;
                            } else {
                                $status = 'PendingCountryLevelApproval';
                            }
                    }
                } else {
                    // Operational entities workflow
                    $status = 'Created';
                }
            } else {
                // User cannot create - goes to approval workflow
                if ($hasAccountingEntities) {
                    $status = 'PendingCountryLevelApproval';
                } else {
                    $status = 'Submitted';
                }
            }
        }

        Log::info('Status transition', [
            'id' => $id,
            'currentStatus' => $currentStatus,
            'newStatus' => $status,
            'isAccounting' => $isAccounting,
            'hasAccountingEntities' => $hasAccountingEntities,
            'canCreateClientParty' => $canCreateClientParty,
            'createBtn' => isset($request->createBtn),
            'submitBtn' => isset($request->submitBtn)
        ]);

        // License validation
        $licenseNo = $request->licenceTypeValue;
        $partyType = $request->legalEntityType ?? 'Debtor';
        $officeCode = $request->officeCountryDisp ?? 'KE';

        if ($licenseNo && $partyType && $officeCode && !in_array($request->partyEntityType, ['NoSCMIntergration', 'Create only in D365'])) {
            $partyTypes = explode(' & ', $partyType);
            $partyTypeMapLicense = [
                'debtor' => 'Debtor',
                'vendor' => 'Vendor'
            ];

            foreach ($partyTypes as $singlePartyType) {
                $mappedPartyType = $partyTypeMapLicense[strtolower($singlePartyType)] ?? $singlePartyType;
                Log::info('Checking license for update:', [
                    'partyType' => $mappedPartyType,
                    'licenseNo' => $licenseNo,
                    'officeCode' => $officeCode,
                    'id' => $id
                ]);

                try {
                    $results = DB::select(
                        'SET NOCOUNT ON;
                     DECLARE @Status AS VARCHAR(20),
                             @Remark AS VARCHAR(100),
                             @IsDuplicate AS INT;
                     EXEC dbo.sp_LicenseNoCheck @officeCode = ?, @PartyType = ?, @LicenseNo = ?,
                          @Status = @Status OUTPUT, @Remark = @Remark OUTPUT, @IsDuplicate = @IsDuplicate OUTPUT;
                     SELECT @Status AS Status, @Remark AS Remark, @IsDuplicate AS IsDuplicate',
                        [$officeCode, $mappedPartyType, $licenseNo]
                    );

                    if (!empty($results) && $results[0]->IsDuplicate === 1) {
                        Log::warning('Duplicate license detected on update:', [
                            'partyType' => $mappedPartyType,
                            'licenseNo' => $licenseNo,
                            'officeCode' => $officeCode,
                            'remark' => $results[0]->Remark,
                            'id' => $id
                        ]);
                        Alert::error('Error', 'License number is already in use: ' . $results[0]->Remark)->persistent(true, false);
                        return redirect()->back()->withInput();
                    }
                } catch (QueryException $e) {
                    Log::error('License check failed on update:', [
                        'partyType' => $mappedPartyType,
                        'licenseNo' => $licenseNo,
                        'officeCode' => $officeCode,
                        'error' => $e->getMessage(),
                        'id' => $id
                    ]);
                    Alert::error('Error', 'License check failed: ' . $e->getMessage())->persistent(true, false);
                    return redirect()->back()->withInput();
                }
            }
        }

        try {
            DB::beginTransaction();

            $updateData = [
                'OperationalEntityType' => $partyEntityType,
                'LegalEntityType' => $request->legalEntityType,
                'Name' => $request->companyName,
                'Address_Line1' => $request->addressLine1,
                'Address_Line2' => $request->addressLine2,
                'Address_CountryId' => $request->countryID ?: null,
                'Address_CountryName' => $request->countryName,
                'Address_CountryCode' => $request->countryCode,
                'Address_State' => $request->provinceName,
                'Address_StateId' => $request->provinceID ?: null,
                'Address_StateCode' => $request->provinceCode,
                'Address_Pin' => $request->zipCode,
                'Address_City' => $request->cityName,
                'Address_CityCode' => $request->cityCode,
                'Cont_Bch_PersonName_FirstName' => $request->firstName,
                'Cont_Bch_PersonName_LastName' => $request->lastName,
                'Cont_Bch_Dept' => $request->department,
                'Cont_Bch_PhoneNumbers' => $request->phoneNo,
                'Cont_Bch_Email' => $request->contractEmail,
                'SpecialInstructions' => $request->specialInstruction,
                'Office' => $request->officeCountry,
                'OfficeCode' => $request->officeCode ?? $request->officeCountryDisp,
                'OfficeID' => $request->officeCountryID,
                'DefaultNotifyPartyName' => $request->notifyParty1,
                'DefaultNotifyPartyId' => $request->notifyParty1ID ?: null,
                'DefaultNotifyPartyCode' => $request->notifyParty1Code,
                'DefaultNotifyParty1Name' => $request->notifyParty2,
                'DefaultNotifyParty1Id' => $request->notifyParty2ID ?: null,
                'DefaultNotifyParty1Code' => $request->notifyParty2Code,
                'DefaultFowarderName' => $request->forwarderName,
                'DefaultFowarderId' => $request->forwarderID ?: null,
                'AccountMgrOfficeFunMapName' => $request->accountPIC,
                'AccountMgrOfficeFunMapId' => $request->accountPICID ?: null,
                'CreditDays' => $request->creditDays,
                'CreditLimitCurrencyId' => $request->billingCurrency ?: null,
                'CreditLimitCurrencyCode' => $request->billingCurrencyCode,
                'BillingCurrencyCode' => $request->billingCurrencyCode,
                'BillingCurrencyId' => $request->billingCurrency ?: null,
                'BaseCurrencyName' => $request->baseCurrencyName,
                'BaseCurrencyCode' => $request->baseCurrencyCode,
                'BaseCurrencyId' => $request->baseCurrencyID ?: null,
                'CreditLimit' => isset($request->maxCreditLimit) ? str_replace(',', '', $request->maxCreditLimit) : 0,
                'LicenceTypeID' => $request->licenceTypeID,
                'LicenceTypeCode' => $request->licenceTypeCode,
                'LicenceTypeValue' => $request->licenceTypeValue,
                'TariffValue' => $request->tarrif,
                'FSCType' => $request->fscType,
                'FSCRate' => isset($request->fscRate) ? $request->fscRate : 0,
                'BillingType' => $request->billingType,
                'Discount' => isset($request->discount) ? $request->discount : 0,
                'ClientName' => $clientName,
                'ClientID' => $request->clientID ?: null,
                'ClientCode' => $request->clientCode,
                'RequestId' => $request->requestID ?: null,
                'RequestName' => $request->clientOptionVal ?? null,
                'RequestCode' => $request->requestCode ?? null,
                'ShipperConsigneeName' => $request->shipperInput,
                'ShipperConsigneeID' => $request->shipperID ?: null,
                'ShipperConsigneeCode' => $request->shipperCode,
                'Debtor' => $request->debtor,
                'DebtorID' => $request->debtorID ?: null,
                'DebtorCode' => $request->debtorCode,
                'Vendor' => $request->vendor,
                'VendorID' => $request->vendorID ?: null,
                'VendorCode' => $request->vendorCode,
                'RequestforClient' => isset($request->clientCheck) ? 1 : 0,
                'RequestforShipperConsignee' => isset($request->consigneeCheck) ? 1 : (isset($request->shipperCheck) ? 1 : 0),
                'RequestforOtherParty' => $request->otherCheck ? 1 : 0,
                'RequestforDebtor' => $request->debtorCheck ? 1 : 0,
                'RequestforVendor' => $request->vendorCheck ? 1 : 0,
                'LastModifiedOn' => Carbon::now()->format('Y-m-d H:i:s'),
                'LastModifiedBy' => $user->full_name ?? $user->Id ?? 'System',
                'DebtorType' => $request->debtorType,
                'VendorType' => $request->vendorType,
                'Status' => $status,
            ];

            // Handle OtherParty fields
            $updateData['OtherPartyName'] = !empty($request->otherInput) ? $request->otherInput : ($cp->OtherPartyName ?? null);
            $updateData['OtherPartyID'] = !empty($request->otherID) ? $request->otherID : ($isNewEntity ? Str::uuid()->toString() : ($cp->OtherPartyID ?? null));
            $updateData['OtherPartyCode'] = !empty($request->otherCode) ? $request->otherCode : ($isNewEntity ? 'OTHER-' . Str::random(6) : ($cp->OtherPartyCode ?? null));

            Log::info('OtherParty fields prepared for update', [
                'id' => $id,
                'OtherPartyName' => $updateData['OtherPartyName'],
                'OtherPartyID' => $updateData['OtherPartyID'],
                'OtherPartyCode' => $updateData['OtherPartyCode'],
                'otherCheck' => isset($request->otherCheck)
            ]);

            if ($isNewEntity) {
                $cp->fill($updateData)->save();
            } else {
                $updated = DB::table('Cust_entity')->where('Id', $id)->update($updateData);
                if ($updated === 0) {
                    Log::warning('No rows updated in Cust_entity', [
                        'id' => $id,
                        'updateData' => $updateData
                    ]);
                }
            }

            // Verify database values
            $dbValues = DB::table('Cust_entity')->where('Id', $id)->first(['OtherPartyName', 'OtherPartyID', 'OtherPartyCode', 'Status']);
            if (
                !$dbValues || $dbValues->OtherPartyName !== $updateData['OtherPartyName'] ||
                $dbValues->OtherPartyID !== $updateData['OtherPartyID'] ||
                $dbValues->OtherPartyCode !== $updateData['OtherPartyCode']
            ) {
                Log::error('OtherParty fields not saved correctly', [
                    'id' => $id,
                    'expected' => [
                        'OtherPartyName' => $updateData['OtherPartyName'],
                        'OtherPartyID' => $updateData['OtherPartyID'],
                        'OtherPartyCode' => $updateData['OtherPartyCode']
                    ],
                    'actual' => $dbValues ? (array)$dbValues : 'No record found'
                ]);
            } else {
                Log::info('OtherParty fields saved to database', [
                    'id' => $id,
                    'OtherPartyName' => $dbValues->OtherPartyName,
                    'OtherPartyID' => $dbValues->OtherPartyID,
                    'OtherPartyCode' => $dbValues->OtherPartyCode,
                    'Status' => $dbValues->Status
                ]);
            }

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Update Failed', ['id' => $id, 'error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            Alert::error('Error', 'Entity Not Updated: ' . $e->getMessage())->persistent(true, false);
            return redirect()->route('client-party.show', $id);
        }

        // Handle attachments
        if ($request->hasFile('attachmentNames')) {
            foreach ($request->file('attachmentNames') as $key => $attachment) {
                if (!$attachment) continue;
                $fileName = $attachment->getClientOriginalName();
                $newName = $cp->Code . '_' . pathinfo($fileName, PATHINFO_FILENAME) . '.' . $attachment->getClientOriginalExtension();
                try {
                    $localPath = $attachment->storeAs('client-party', $newName, 'local');
                    $storagePath = 'Kenya/ClientParty/' . $newName;
                    Storage::disk('azure')->put($storagePath, file_get_contents(storage_path('app/' . $localPath)));

                    DB::beginTransaction();
                    ClientPartyAttahment::create([
                        'ID' => Str::uuid()->toString(),
                        'CustEntityID' => $cp->Id,
                        'RequestNo' => $cp->Code,
                        'Title' => $request->attachmentDescription[$key] ?? 'Attachment',
                        'FileName' => $newName,
                        'FilePath' => $storagePath,
                        'CreatedBy' => $user->Id ?? 'System',
                        'CreatedOn' => Carbon::now(),
                        'LastModifiedBy' => $user->Id ?? 'System',
                        'LastModifiedOn' => Carbon::now(),
                    ]);
                    DB::commit();
                    File::delete(storage_path('app/' . $localPath));
                } catch (Exception $e) {
                    Log::warning('Attachment Upload Failed', ['id' => $id, 'error' => $e->getMessage()]);
                    DB::rollBack();
                }
            }
        }

        // D365 INTEGRATION - NOW HAPPENS AT PendingITVerification STAGE
        if (isset($request->createBtn) && $currentStatus === 'PendingITVerification' && $status === 'Created') {
            if (isset($request->debtorCheck) || isset($request->vendorCheck)) {
                // Determine if this is a D365-only entity
                $legalEntityType = strtolower($request->legalEntityType);
                $isD365OnlyEntity = in_array($legalEntityType, [
                    'only in d365 vendor',
                    'only in d365 debtor'
                ]);

                // Determine entity types based on legal entity type
                if ($legalEntityType == 'only in d365 vendor') {
                    $entity_types = ['Vendor'];
                } elseif ($legalEntityType == 'only in d365 debtor') {
                    $entity_types = ['Customer'];
                } elseif ($legalEntityType == 'vendor') {
                    $entity_types = ['Vendor'];
                } elseif ($legalEntityType == 'debtor') {
                    $entity_types = ['Customer'];
                } else {
                    $entity_types = ['Vendor', 'Customer'];
                }

                Log::info('D365 Integration Starting', [
                    'id' => $id,
                    'currentStatus' => $currentStatus,
                    'newStatus' => $status,
                    'entity_types' => $entity_types,
                    'legalEntityType' => $request->legalEntityType,
                    'isD365OnlyEntity' => $isD365OnlyEntity
                ]);

                $d365IntegrationSuccess = true;

                foreach ($entity_types as $entity_type) {
                    // FIX 1: Check the correct fields for existing codes
                    $skipIntegration = false;
                    if ($entity_type == 'Vendor' && !empty($request->vendorCode)) {
                        Log::info('Skipping Vendor integration - code already exists', ['vendorCode' => $request->vendorCode]);
                        $skipIntegration = true;
                    }
                    if ($entity_type == 'Customer' && !empty($request->debtorCode)) {
                        Log::info('Skipping Customer integration - code already exists', ['debtorCode' => $request->debtorCode]);
                        $skipIntegration = true;
                    }

                    if ($skipIntegration) {
                        continue;
                    }

                    // FIX 2: Use the correct office code field consistently
                    $officeCodeForSeries = $request->officeCountryDisp ?? $request->officeCode ?? 'KE';
                    $noSeries = $this->getOfficeNumberSeries(
                        $officeCodeForSeries,
                        ($entity_type == 'Customer') ? 'Debtor' : $entity_type
                    );

                    Log::info('Office code and series lookup', [
                        'officeCodeForSeries' => $officeCodeForSeries,
                        'entity_type' => $entity_type,
                        'mappedType' => ($entity_type == 'Customer') ? 'Debtor' : $entity_type,
                        'noSeries' => $noSeries
                    ]);

                    if ($noSeries === null) {
                        Log::error('D365 Integration Failed: No series number found', [
                            'officeCode' => $officeCodeForSeries,
                            'entityType' => ($entity_type == 'Customer') ? 'Debtor' : $entity_type,
                            'id' => $id
                        ]);
                        Alert::error(
                            'D365 Integration Error',
                            'No series number found for office: ' . $officeCodeForSeries .
                                ' and entity type: ' . (($entity_type == 'Customer') ? 'Debtor' : $entity_type)
                        )->persistent(true, false);

                        DB::table('Cust_entity')->where('Id', $id)->update(['Status' => 'PendingITVerification']);
                        return redirect()->route('client-party.index');
                    }

                    $addressLine1 = $request->addressLine1 ?? 'Nairobi';
                    $addressLine2 = $request->addressLine2 ?? '';
                    $firstName = $request->firstName ?? '';
                    $lastName = $request->lastName ?? '';
                    $phoneNo = $request->phoneNo ?? '';
                    $contractEmail = $request->contractEmail ?? '';
                    $countryCode = $request->countryCode;

                    // FIX 3: Use consistent office code for D365 data
                    $data = [
                        "office" => ($officeCodeForSeries == 'UPS') ? 'KE' : $officeCodeForSeries,
                        "partyType" => (strcmp(strtolower(App::environment()), 'production') == 0) ? $entity_type : 'TST',
                        "no" => "",
                        "name" => $request->companyName,
                        "postingGroup" => $entity_type == 'Vendor' ? $request->vendorType : $request->debtorType,
                        "vatRegistrationNo" => $request->licenceTypeValue ?? '-',
                        "postCode" => $request->zipCode ?? "",
                        "address" => $addressLine1,
                        "address2" => $addressLine2,
                        "city" => $request->cityName ?? "",
                        'ClientID' => $request->clientID,
                        'ClientCode' => $request->clientCode,
                        "countryCode" => $countryCode,
                        "emailAddress" => $contractEmail,
                        "phoneNo" => $phoneNo,
                        "phoneNo2" => "",
                        "billingCurrency" => $request->billingCurrencyCode,
                        "paymentTermsCode" => (string)($request->creditDays) == "" ? "0" : (string)($request->creditDays),
                        "creditLimitLCY" => intval($request->maxCreditLimit ?? 0),
                        "contactPersonName" => ($firstName && $lastName) ? "$firstName $lastName" : "",
                        "contactPersonEmailAddress" => $contractEmail,
                        "contactPersonPhoneNo" => $phoneNo,
                        "contactPersonPhoneNo2" => "",
                        "relatedParty" => "",
                        "blocked" => false,
                        "NoSeries" => $noSeries
                    ];

                    Log::info('D365 Integration - Data being sent', [
                        'entity_type' => $entity_type,
                        'data' => $data,
                        'id' => $id
                    ]);

                    if (config('app.env') != 'Local') {
                        try {
                            $entity = $this->createLegalEntityType($entity_type, $data);

                            Log::info('D365 Integration - Raw Response', [
                                'entity_type' => $entity_type,
                                'response' => json_encode($entity),
                                'response_type' => gettype($entity),
                                'id' => $id
                            ]);

                            // FIX 4: Improved response validation based on your actual response format
                            $isSuccess = false;
                            $errorMessage = null;
                            $entityCode = null;

                            if (is_object($entity)) {
                                // Check for HTTP error status codes first
                                if (property_exists($entity, 'status') && is_numeric($entity->status) && $entity->status >= 400) {
                                    $isSuccess = false;
                                    $errorMessage = property_exists($entity, 'title') ? $entity->title : 'HTTP Error: ' . $entity->status;

                                    if (property_exists($entity, 'errors') && is_object($entity->errors)) {
                                        $validationErrors = [];
                                        foreach ($entity->errors as $field => $messages) {
                                            if (is_array($messages)) {
                                                $validationErrors[] = $field . ': ' . implode(', ', $messages);
                                            } else {
                                                $validationErrors[] = $field . ': ' . $messages;
                                            }
                                        }
                                        $errorMessage .= ' - Validation Errors: ' . implode('; ', $validationErrors);
                                    }
                                }
                                // Check for success based on your actual response format
                                elseif (property_exists($entity, 'status') && $entity->status === true) {
                                    $isSuccess = true;
                                    // Your response has responseCode when successful
                                    if (property_exists($entity, 'responseCode') && !empty($entity->responseCode)) {
                                        $entityCode = $entity->responseCode;
                                    }
                                }
                                // Check for boolean false status
                                elseif (property_exists($entity, 'status') && $entity->status === false) {
                                    $isSuccess = false;
                                    $errorMessage = property_exists($entity, 'responseDescription') ?
                                        $entity->responseDescription : (property_exists($entity, 'Remark') ? $entity->Remark : 'D365 API returned status: false');
                                }
                                // Check for other success indicators
                                elseif (property_exists($entity, 'responseCode') && !empty($entity->responseCode)) {
                                    $isSuccess = true;
                                    $entityCode = $entity->responseCode;
                                } else {
                                    $isSuccess = false;
                                    $errorMessage = 'No valid success indicator found in D365 API response';
                                }
                            } else {
                                $isSuccess = false;
                                $errorMessage = 'Invalid response format from D365 API (not an object)';
                            }

                            Log::info('D365 Integration - Response Analysis', [
                                'entity_type' => $entity_type,
                                'isSuccess' => $isSuccess,
                                'errorMessage' => $errorMessage,
                                'entityCode' => $entityCode,
                                'id' => $id,
                                'has_status' => property_exists($entity, 'status'),
                                'status_value' => property_exists($entity, 'status') ? $entity->status : 'N/A',
                                'has_responseCode' => property_exists($entity, 'responseCode'),
                                'responseCode_value' => property_exists($entity, 'responseCode') ? $entity->responseCode : 'N/A'
                            ]);

                            if (!$isSuccess) {
                                Log::error('D365 Integration Failed', [
                                    'response' => json_encode($entity),
                                    'errorMessage' => $errorMessage,
                                    'id' => $id,
                                    'entity_type' => $entity_type,
                                    'data_sent' => $data
                                ]);

                                $d365IntegrationSuccess = false;
                                DB::table('Cust_entity')->where('Id', $id)->update(['Status' => 'PendingITVerification']);

                                Alert::error(
                                    'D365 Integration Error',
                                    'Failed to create ' . $entity_type . ' in D365: ' . $errorMessage
                                )->persistent(true, false);
                                return redirect()->route('client-party.index');
                            }

                            // FIX 5: Success - update entity codes with proper field mapping
                            if ($entityCode) {
                                $fieldToUpdate = ($entity_type == 'Vendor') ? 'VendorCode' : 'DebtorCode';

                                Log::info('D365 Integration - Updating database', [
                                    'entity_type' => $entity_type,
                                    'fieldToUpdate' => $fieldToUpdate,
                                    'entityCode' => $entityCode,
                                    'id' => $id
                                ]);

                                // Get current state before update
                                $beforeUpdate = DB::table('Cust_entity')->where('Id', $id)->first(['VendorCode', 'DebtorCode', 'Status']);

                                Log::info('D365 Integration - Before update', [
                                    'id' => $id,
                                    'before_VendorCode' => $beforeUpdate->VendorCode ?? 'NULL',
                                    'before_DebtorCode' => $beforeUpdate->DebtorCode ?? 'NULL',
                                    'before_Status' => $beforeUpdate->Status ?? 'NULL'
                                ]);

                                $updateResult = DB::table('Cust_Entity')->where('Id', $id)->update([
                                    $fieldToUpdate => $entityCode,
                                ]);

                                // Verify the update worked
                                $afterUpdate = DB::table('Cust_entity')->where('Id', $id)->first(['VendorCode', 'DebtorCode', 'Status']);

                                Log::info('D365 Integration - After update', [
                                    'id' => $id,
                                    'updateResult' => $updateResult,
                                    'fieldUpdated' => $fieldToUpdate,
                                    'newValue' => $entityCode,
                                    'after_VendorCode' => $afterUpdate->VendorCode ?? 'NULL',
                                    'after_DebtorCode' => $afterUpdate->DebtorCode ?? 'NULL',
                                    'after_Status' => $afterUpdate->Status ?? 'NULL',
                                    'rows_affected' => $updateResult
                                ]);

                                if ($updateResult === 0) {
                                    Log::warning('D365 Integration - Database update affected 0 rows', [
                                        'id' => $id,
                                        'fieldToUpdate' => $fieldToUpdate,
                                        'entityCode' => $entityCode,
                                        'entity_exists' => DB::table('Cust_entity')->where('Id', $id)->exists()
                                    ]);
                                }
                            } else {
                                Log::warning('D365 Integration - No entity code to update despite success', [
                                    'id' => $id,
                                    'entity_type' => $entity_type,
                                    'response_properties' => property_exists($entity, 'responseCode') ? 'has responseCode' : 'no responseCode'
                                ]);
                            }

                            Log::info('D365 Integration Success', [
                                'entity_type' => $entity_type,
                                'entityCode' => $entityCode,
                                'id' => $id
                            ]);
                        } catch (Exception $e) {
                            Log::error('D365 Integration Exception', [
                                'error' => $e->getMessage(),
                                'trace' => $e->getTraceAsString(),
                                'id' => $id,
                                'entity_type' => $entity_type,
                                'data_sent' => $data
                            ]);

                            $d365IntegrationSuccess = false;
                            DB::table('Cust_entity')->where('Id', $id)->update(['Status' => 'PendingITVerification']);

                            Alert::error(
                                'D365 Integration Error',
                                'D365 integration failed with exception: ' . $e->getMessage()
                            )->persistent(true, false);
                            return redirect()->route('client-party.index');
                        }
                    } else {
                        Log::info('D365 Integration skipped (Local environment)', [
                            'id' => $id,
                            'entity_type' => $entity_type,
                            'noSeries' => $noSeries
                        ]);
                    }
                }

                // Final check and verification
                if (!$d365IntegrationSuccess) {
                    DB::table('Cust_entity')->where('Id', $id)->update(['Status' => 'PendingITVerification']);
                    Log::error('D365 Integration overall failure - status rolled back', ['id' => $id]);
                    Alert::error('D365 Integration Error', 'D365 integration failed. Status rolled back to PendingITVerification.')
                        ->persistent(true, false);
                    return redirect()->route('client-party.index');
                }

                // Log final state after all integrations
                $finalState = DB::table('Cust_entity')->where('Id', $id)->first(['VendorCode', 'DebtorCode', 'Status']);
                Log::info('D365 Integration - Final state', [
                    'id' => $id,
                    'final_VendorCode' => $finalState->VendorCode ?? 'NULL',
                    'final_DebtorCode' => $finalState->DebtorCode ?? 'NULL',
                    'final_Status' => $finalState->Status ?? 'NULL'
                ]);
            } else {
                Log::info('D365 Integration skipped - no accounting entity checks', [
                    'id' => $id,
                    'debtorCheck' => isset($request->debtorCheck),
                    'vendorCheck' => isset($request->vendorCheck)
                ]);
            }
        } else {
            Log::info('D365 Integration conditions not met', [
                'id' => $id,
                'createBtn' => isset($request->createBtn),
                'currentStatus' => $currentStatus,
                'expectedCurrentStatus' => 'PendingITVerification',
                'newStatus' => $status,
                'expectedNewStatus' => 'Created'
            ]);
        }

        // Log history
        try {
            $historyComment = $isNewEntity ? 'Entity Created' : 'Entity Updated';
            if (isset($request->createBtn) && $hasAccountingEntities) {
                switch ($status) {
                    case 'PendingCountryLevelApproval':
                        $historyComment = 'Entity Pending Country Level Approval';
                        break;
                    case 'PendingGroupLevelApproval':
                        $historyComment = 'Entity Pending Group Level Approval';
                        break;
                    case 'PendingITVerification':
                        $historyComment = 'Entity Pending IT Verification';
                        break;
                    case 'Created':
                        if ($isD365OnlyEntity) {
                            $historyComment = 'Entity Created Successfully with D365 Integration Only';
                        } else {
                            $historyComment = 'Entity Created Successfully with D365 Integration';
                        }
                        break;
                }
            } elseif (isset($request->createBtn) && !$hasAccountingEntities) {
                $historyComment = ($status === 'Submitted') ? 'Entity Submitted' : 'Entity Created Successfully';
            }

            $history = [
                'ID' => Str::uuid()->toString(),
                'CustEntityID' => $cp->Id,
                'RequestNo' => $cp->Code,
                'Role' => $user->Role,
                'ReasonID' => '84d62970-a1ce-47d3-9428-36e4647811f9',
                'Comment' => $historyComment,
                'Type' => 'Entity',
                'PreviousValue' => $currentStatus,
                'NewValue' => $status,
                'CreatedBy' => $user->Id ?? 'System',
                'CreatedOn' => Carbon::now(),
                'LastModifiedBy' => $user->Id ?? 'System',
                'LastModifiedOn' => Carbon::now()
            ];
            EntityHistory::create($history);
            Log::info('Entity history logged', ['id' => $id, 'history' => $history]);
        } catch (Exception $e) {
            Log::warning('Failed to log history', ['id' => $id, 'error' => $e->getMessage()]);
        }

        // SCM Integration - Only proceed if status is being set to 'Created' AND it's NOT an accounting entity OR if it's an accounting entity that already went through D365
        $isD365OnlyEntity = in_array(strtolower($request->legalEntityType), [
            'only in d365 vendor',
            'only in d365 debtor'
        ]);

        $shouldRunSCM = isset($request->createBtn) &&
            ($status === 'Created') &&
            !$isD365OnlyEntity && // Skip SCM for D365-only entities
            (
                !$hasAccountingEntities ||
                ($hasAccountingEntities && $currentStatus === 'PendingITVerification')
            );

        Log::info('SCM Decision Logic', [
            'id' => $id,
            'shouldRunSCM' => $shouldRunSCM,
            'isD365OnlyEntity' => $isD365OnlyEntity,
            'legalEntityType' => $request->legalEntityType,
            'createBtn' => isset($request->createBtn),
            'status' => $status,
            'currentStatus' => $currentStatus,
            'hasAccountingEntities' => $hasAccountingEntities
        ]);

        if ($shouldRunSCM) {
            if ($request->partyEntityType != 'NoSCMIntegration') {
                try {
                    $results = DB::select("SET NOCOUNT ON;
                                   DECLARE
                                   @RequestID AS UNIQUEIDENTIFIER,
                                   @Status AS VARCHAR(30),
                                   @Remark AS VARCHAR(1000)
                                   EXEC [dbo].[sp_CreateDebtorandVendor]
                                   @RequestID = ?,
                                   @status = @Status OUTPUT,
                                   @Remark = @Remark OUTPUT
                                   SELECT @Status AS Status, @Remark AS Remark", [
                        $id
                    ]);
                    Log::info('sp_CreateDebtorandVendor Results', [
                        'id' => $id,
                        'results' => $results
                    ]);
                    if ($results && !empty($results[0]->Status) && $results[0]->Status !== 'Failed') {
                        $client_party_status = 1;
                    } else {
                        Log::warning('sp_CreateDebtorandVendor failed', [
                            'id' => $id,
                            'status' => $results[0]->Status ?? 'Unknown',
                            'remark' => $results[0]->Remark ?? 'No remark'
                        ]);
                        Alert::error('SCM Error', $results[0]->Remark ?? 'Failed to create debtor/vendor in SCM')->persistent(true, false);
                        return redirect()->route('client-party.show', $id);
                    }
                } catch (Exception $e) {
                    Log::error('sp_CreateDebtorandVendor execution failed', [
                        'id' => $id,
                        'error' => $e->getMessage()
                    ]);
                    Alert::error('SCM Error', 'Failed to execute SCM stored procedure: ' . $e->getMessage())->persistent(true, false);
                    return redirect()->route('client-party.show', $id);
                }
            } else {
                $client_party_status = 1;
            }

            // Create entity on SCM
            if ($client_party_status == 1) {
                if ($request->partyEntityType != 'NoSCMIntegration') {
                    Log::info('Calling CreateClientPartySCM', ['id' => $id]);
                    $scmRequest = new \Illuminate\Http\Request(['requestID' => $cp->Id]);
                    $results = $this->CreateClientPartySCM($scmRequest);
                } else {
                    // Ensure status is set to 'Created' for NoSCMIntegration
                    DB::table('Cust_entity')->where('Id', $cp->Id)->update(['Status' => 'Created']);
                    $results = ['success' => true, 'data' => ['Status' => 'Success', 'Remark' => 'Entity Created (No SCM Integration)']];
                }

                Log::info('SCM Creation Results:', (array)$results);
                if (isset($results['success']) && $results['success'] && isset($results['data'])) {
                    if ($results['data']['Status'] === 'Success') {
                        $successMessage = $hasAccountingEntities && $currentStatus === 'PendingITVerification'
                            ? 'Entity created successfully with D365 and SCM integration'
                            : ($results['data']['Remark'] ?? 'Entity created successfully in SCM');
                        Alert::success('Success', $successMessage)->showConfirmButton();
                    } else {
                        Alert::error('Failed', $results['data']['Remark'] ?? 'SCM creation failed')->showConfirmButton();
                    }
                } else {
                    Alert::error('Error', $results['error'] ?? 'SCM creation failed')->showConfirmButton();
                }
            }
        } elseif ($isD365OnlyEntity && $status === 'Created' && $currentStatus === 'PendingITVerification') {
            // For D365-only entities that don't need SCM integration
            Log::info('D365-only entity created successfully', [
                'id' => $id,
                'legalEntityType' => $request->legalEntityType,
                'status' => $status
            ]);
            Alert::success('Success', 'Entity created successfully in D365 (SCM integration skipped)')->showConfirmButton();
        } elseif ($hasAccountingEntities && $status === 'Created' && $currentStatus === 'PendingITVerification') {
            // For accounting entities that reach 'Created' status from PendingITVerification (D365 integration completed)
            Log::info('D365 integration completed, entity created', [
                'id' => $id,
                'hasAccountingEntities' => $hasAccountingEntities,
                'status' => $status,
                'currentStatus' => $currentStatus
            ]);
            Alert::success('Success', 'Entity created successfully with D365 integration')->showConfirmButton();
        } else {
            // Provide appropriate success message based on status
            $successMessage = 'Entity Updated.';
            if (isset($request->createBtn)) {
                switch ($status) {
                    case 'PendingCountryLevelApproval':
                        $successMessage = 'Entity pending country level approval.';
                        break;
                    case 'PendingGroupLevelApproval':
                        $successMessage = 'Entity pending group level approval.';
                        break;
                    case 'PendingITVerification':
                        $successMessage = 'Entity pending IT verification. D365 integration will occur at next approval.';
                        break;
                    case 'Submitted':
                        $successMessage = 'Entity submitted successfully.';
                        break;
                    case 'Created':
                        $successMessage = 'Entity created successfully.';
                        break;
                }
            }
            Alert::success('Success', $successMessage)->showConfirmButton();
        }

        // Log source
        $source = Request::query('source');
        if ($source) {
            Log::info("ClientParty updated from source: {$source}");
        }

        return redirect()->route('client-party.index');
    }


    public function CreateClientPartySCM(HttpRequest $request)
    {
        $requestID = $request->input('requestID');

        // Validate requestID format (UUID)
        if (!preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $requestID)) {
            Log::warning('Invalid RequestID format:', ['RequestID' => $requestID]);
            return ['success' => false, 'error' => 'Invalid Request ID format'];
        }

        // Check permissions and OperatioEntityType
        $entity = DB::table('Cust_entity')->where('Id', $requestID)->first();
        if (!$entity) {
            Log::error('❌ STEP 3: Entity Not Found', ['requestID' => $requestID]);
            return [
                'success' => false,
                'data' => [
                    'Status' => 'Failed',
                    'Remark' => 'Entity not found with ID: ' . $requestID
                ]
            ];
        }

        if (!Auth::user()->can('create-client-party-SCM') && $entity->OperatioEntityType !== 'Client without Debtor or Vendor') {
            Log::warning('SCM Permission Denied:', [
                'UserID' => Auth::user()->Id,
                'Permissions' => Auth::user()->getAllPermissions()->pluck('name')->toArray(),
                'OperatioEntityType' => $entity->OperatioEntityType
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
            if (!empty($results) && isset($results[0])) {
                $result = (array)$results[0];

                // Log success
                Log::info('SCM Creation Success:', [
                    'RequestID' => $requestID,
                    'Result' => $result,
                    'OperatioEntityType' => $entity->OperatioEntityType
                ]);

                // Check stored procedure status
                if ($result['Status'] === 'Success') {
                    return ['success' => true, 'data' => $result];
                } else {
                    Log::warning('Stored Procedure Failed:', [
                        'RequestID' => $requestID,
                        'Remark' => $result['Remark'],
                        'OperatioEntityType' => $entity->OperatioEntityType
                    ]);
                    return ['success' => false, 'error' => $result['Remark']];
                }
            } else {
                Log::warning('⚠️ STEP 14a: No Cust_Clients Entry Found', [
                    'requestID' => $requestID,
                    'searchedCode' => $restoreData['ClientCode'] ?? $entity->Code
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
                    'partyType' => $scmParty->PartyType
                ]);
            } else {
                Log::warning('⚠️ STEP 14b: No Cust_Party Entry Found', [
                    'requestID' => $requestID,
                    'searchedCode' => $restoreData['OtherPartyCode'] ?? $entity->Code
                ]);
            }
        } catch (QueryException $e) {
            Log::error('Error executing Sp_OperationEntityCreation:', [
                'RequestID' => $requestID,
                'Error' => $e->getMessage(),
                'OperatioEntityType' => $entity->OperatioEntityType
            ]);
            return ['success' => false, 'error' => 'Entity Creation Failed: ' . $e->getMessage()];
        }

        Log::info('═══════════════════════════════════════════════════════════════════════');

        return [
            'success' => false,
            'data' => [
                'Status' => $status,
                'Remark' => $remarks
            ]
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
        if (!Auth::user()->can('delete-entity-request')) {
            alert()->warning('Warning !', 'Unauthorised.')->showConfirmButton();
            return redirect()->back();
        }
    }


//     public function reject()
// {
//     $user = Auth::user();

//     if (!$user->can('approve-entity-request') && !$user->can('create-client-party')) {
//         alert()->warning('Warning !', 'Unauthorised.')->showConfirmButton();
//         return response()->json(['false', 312]);
//     }

//     $id = \request()->get('id');
//     $level = \request()->get('level');
//     $reason = \request()->get('reason');

//     $clientParty = DB::table('Cust_entity')->where('Id', $id)->first();

//     $history = [
//         'ID' => guid(),
//         'CustEntityID' => $id,
//         'RequestNo' => $clientParty->Code,
//         'Role' => $user->Role,
//         'ReasonID' => '84d62970-a1ce-47d3-9428-36e4647811f9',
//         'Comment' => $reason,
//         'Type' => 'Entity',
//         'PreviousValue' => $clientParty->Status,
//         'NewValue' => $level,
//         'CreatedBy' => $user->ID,
//         'CreatedOn' => Carbon::now(),
//         'LastModifiedBy' => $user->ID,
//         'LastModifiedOn' => Carbon::now()
//     ];

//     EntityHistory::create($history);

//     // Update the status FIRST before attempting to send email
//     try {
//         DB::table('Cust_entity')->where('Id', $id)->update([
//             'RejectedBy' => Auth::user()->full_name,
//             'RejectedOn' => date('Y-m-d H:i:s'),
//             'RejectedReason' => $reason,
//             'Status' => $level,
//         ]);
//     } catch (Exception $e) {
//         alert()->warning('Entity', $e);
//         return response()->json(['false', 500]);
//     }

//     // Try to send email, but don't fail if it doesn't work
//     try {
//         $userEmail = User::where('LoginID', $clientParty->CreatedBy)->select('Email')->first();
//         if ($userEmail && $userEmail->Email) {
//             Mail::to($userEmail->Email)->send(new RejectEntity($clientParty));
//         }
//     } catch (Exception $th) {
//         // Log the error but don't stop the process
//         Log::warning('Failed to send rejection email: ' . $th->getMessage());
//     }

//     alert()->success('Entity', 'Entity Rejected at ' . $level)->showConfirmButton();
//     return response()->json(['ok', 200]);
// }
public function reject()
{
    $user = Auth::user();

    if (!$user->can('approve-entity-request') && !$user->can('create-client-party')) {
        return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
    }

    $id     = request()->input('id');
    $level  = request()->input('level');   // usually 'Rejected'
    $reason = request()->input('reason', 'No reason provided');

    $entity = ClientParty::findOrFail($id);

    $payload = [
        'OperationType'     => 'OperationalEntityUpdate',
        'Action'            => 'Add',
        'SubmittedDatetime' => now()->format('YmdHis'),
        'SubmittedBy'       => $user->LoginID ?? $user->email ?? $user->full_name ?? 'system',
        'RequestNo'         => $entity->Code,
        'Status'            => $level,
        'Remark'            => $reason,
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
            'remarks' => $response['Remarks'] ?? []
        ]);

    } catch (\Exception $e) {
        \Log::error('Reject via JSON failed', [
            'id' => $id,
            'level' => $level,
            'error' => $e->getMessage()
        ]);

        return response()->json([
            'success' => false,
            'message' => $e->getMessage() ?: 'Failed to reject entity'
        ], 422);
    }
}



public function approve(HttpRequest $request)
{
    $user = Auth::user();

    if (!$user->can('approve-entity-request')) {
        return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
    }

    $id = $request->input('id');
    $entity = ClientParty::findOrFail($id);

    // Allowed statuses for any approval action (update or final)
    $allowedStatuses = [
        'Submitted',                        // Operational
        'PendingCountryLevelApproval',      // Accounting initial
        'PendingGroupLevelApproval',        // Accounting after country
        'PendingITVerification',            // Accounting final pending
    ];

    if (!in_array($entity->Status, $allowedStatuses)) {
        \Log::warning('Attempt to approve entity in invalid status', [
            'entity_id'      => $id,
            'code'           => $entity->Code ?? 'unknown',
            'current_status' => $entity->Status,
        ]);

        return response()->json([
            'success' => false,
            'message' => "This entity is not in an approvable status. Current: {$entity->Status}",
        ], 422);
    }

    // Detect accounting entity
    $accountingIndicators = [
        'LegalEntityType', 'LicenceTypeCode', 'LicenceTypeValue',
        'CreditDays', 'CreditLimit', 'CreditLimitCurrencyCode',
        'BillingCurrencyCode', 'BaseCurrencyCode',
        'DebtorType', 'VendorType',
    ];

    $hasAccountingFields = false;
    foreach ($accountingIndicators as $field) {
        if (!empty($entity->$field)) {
            $hasAccountingFields = true;
            break;
        }
    }

    // Decide operation type and status to set
    $operationType = 'OperationalEntityCreation'; // default
    $statusToSet   = 'Create';                    // default for final

    if ($hasAccountingFields) {
        $intermediateStatuses = [
            'PendingCountryLevelApproval',
            'PendingGroupLevelApproval',
        ];

        if (in_array($entity->Status, $intermediateStatuses)) {
            // Intermediate approval → status update
            $operationType = 'AccountingEntityUpdate';

            // Get new status from request (required for update)
            $statusToSet = $request->input('status');

            if (!$statusToSet) {
                return response()->json([
                    'success' => false,
                    'message' => 'New status is required for intermediate approval.',
                ], 422);
            }

            $allowedUpdateStatuses = [
                'PendingGroupLevelApproval',
                'Review',
                'Rejected',
                'PendingITVerification',
            ];

            if (!in_array($statusToSet, $allowedUpdateStatuses)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid status for update. Allowed: ' . implode(', ', $allowedUpdateStatuses),
                ], 422);
            }
        } else {
            // Final step (e.g. after IT verification) → creation
            $operationType = 'AccountingEntityCreation';
            $statusToSet   = 'Create';
        }
    }

    $payload = [
        'OperationType'     => $operationType,
        'Action'            => 'Add',
        'SubmittedDatetime' => now()->format('YmdHis'),
        'SubmittedBy'       => $user->LoginID ?? $user->full_name ?? 'system',
        'RequestNo'         => $entity->Code,
        'Status'            => $statusToSet,
        'Remark'            => $request->input('comment', 'Approved by ' . ($user->full_name ?? 'system')),
    ];

    \Log::info('approve() - Preparing payload for main SP', [
        'entity_id'         => $id,
        'code'              => $entity->Code,
        'current_status'    => $entity->Status,
        'operation_type'    => $operationType,
        'status_to_set'     => $statusToSet,
        'payload'           => $payload,
    ]);

    try {
        $response = $this->executeJsonEntityProcedure($payload);

        \Log::info('approve() - Response from main SP', [
            'entity_id' => $id,
            'code'      => $entity->Code,
            'status'    => $response['Status'] ?? 'missing',
            'remarks'   => $response['Remarks'] ?? [],
        ]);

        $remarks = collect($response['Remarks'] ?? [])
            ->flatten()
            ->map(fn($v) => is_array($v) ? json_encode($v, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : (string)$v)
            ->filter()
            ->implode(' • ') ?: 'Status updated / entity processed';

        $success = ($response['Status'] ?? 'Failed') === 'Success';

        if ($success && ($entity->OperationalEntityType ?? '') !== 'NoSCMIntegration') {
            try {
                $scmRequest = new HttpRequest(['requestID' => $entity->Id]);
                $scmResult = $this->CreateClientPartySCM($scmRequest);
                \Log::info('SCM integration triggered', ['result' => $scmResult]);
            } catch (\Exception $e) {
                \Log::warning('SCM integration failed (non-blocking)', ['error' => $e->getMessage()]);
            }
        }

        return response()->json([
            'success'    => $success,
            'new_status' => $success ? $statusToSet : $entity->Status,
            'message'    => $success ? $remarks : ($remarks ?: 'Operation completed with warnings'),
            'remarks'    => $response['Remarks'] ?? [],
        ]);

    } catch (\Exception $e) {
        \Log::error('Approval/finalization failed', [
            'entity_id'      => $id,
            'code'           => $entity->Code ?? 'unknown',
            'current_status' => $entity->Status,
            'operation'      => $operationType,
            'payload'        => $payload,
            'error'          => $e->getMessage(),
            'trace'          => $e->getTraceAsString(),
        ]);

        return response()->json([
            'success' => false,
            'message' => $e->getMessage() ?: 'Operation failed – check logs',
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

        if (!$user->can('approve-entity-request')) {
            Alert::warning('Warning!', 'You are not authorised to view this page. Contact Admin')->showConfirmButton();
            return redirect()->route('home');
        }

        // Calculate counts for info boxes
        $counts = [
            'not_submitted' => ClientParty::where('Status', 'Not Submitted')
                ->when(!$user->can('approve-all-entity-request'), fn($q) => $q->where('OfficeCode', $user->OfficeCode))
                ->count(),
            'pending_country' => ClientParty::where('Status', 'PendingCountryLevelApproval')
                ->when(!$user->can('approve-all-entity-request'), fn($q) => $q->where('OfficeCode', $user->OfficeCode))
                ->count(),
            'pending_group' => ClientParty::where('Status', 'PendingGroupLevelApproval')
                ->when(!$user->can('approve-all-entity-request'), fn($q) => $q->where('OfficeCode', $user->OfficeCode))
                ->count(),
            'submitted' => ClientParty::where('Status', 'Submitted')
                ->when(!$user->can('approve-all-entity-request'), fn($q) => $q->where('OfficeCode', $user->OfficeCode))
                ->count(),
            'all_pending' => ClientParty::whereIn('Status', [
                'Not Submitted',
                'Submitted',
                'PendingCountryLevelApproval',
                'PendingGroupLevelApproval'
            ])
                ->when(!$user->can('approve-all-entity-request'), fn($q) => $q->where('OfficeCode', $user->OfficeCode))
                ->count(),
        ];
        return view('client-party.approve', compact('counts'));
    }

    public function deleteFile($id)
    {
        if (!Auth::user()->can('create-client-party-SCM')) {
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
                'officeCode' => $officeCode
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
                    'officeCode' => $officeCode
                ]);
                return response()->json(['error' => 'Failed to check license'], 500);
            }

            $result = $results[0];

            Log::info('sp_LicenseNoCheck response:', (array)$result);

            return response()->json([
                'status' => $result->Status,
                'remark' => $result->Remark,
                'isDuplicate' => $result->IsDuplicate
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('sp_LicenseNoCheck Error: ' . $e->getMessage(), [
                'partyType' => $partyType,
                'licenseNo' => $licenseNo,
                'officeCode' => $officeCode,
                'sql' => $e->getSql()
            ]);
            return response()->json(['error' => 'Database error while checking license'], 500);
        }
    }


    public function getCompanies()
    {
        try {
            $entityType = Request::get('entityType');
            $query = Request::get('query', '');

            if (!$entityType) {
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
                    'name' => $item->Name
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

            if (!$entityType || !$companyId) {
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
            $details = array_map(function ($item) use ($entityType) {
                // Use Address_CountryId if available
                $countryId = property_exists($item, 'Address_CountryId') && $item->Address_CountryId ? $item->Address_CountryId : null;
                $countryCode = property_exists($item, 'CountryCode2') && $item->CountryCode2 ? $item->CountryCode2 : (property_exists($item, 'CountryCode') ? $item->CountryCode : null);
                $countryName = property_exists($item, 'CountryName2') && $item->CountryName2 ? $item->CountryName2 : (property_exists($item, 'CountryName') ? $item->CountryName : null);

                // Optional: Validate countryId using Cust_Country if needed
                if ($countryCode && !$countryId) {
                    try {
                        $country = DB::selectOne(
                            "SELECT CountryID FROM dbo.Cust_Country WHERE CountryCode = ?",
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

                    'lastName'  => property_exists($item, 'Cont_Bch_PersonName_LastName')
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

            if (!empty($companyDetails) && isset($companyDetails[0])) {
                return response()->json([
                    'success' => true,
                    'data' => (array)$companyDetails[0]
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
 * @param string $attachmentId
 * @return \Illuminate\Http\Response
 */

}
