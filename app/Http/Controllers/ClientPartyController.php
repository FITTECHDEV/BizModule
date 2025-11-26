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

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    // public function create()
    // {
    //     // authorization
    //     if (!\request()->user()->can('request-client-party')) {
    //         abort(403, "You are not allowed to view this page.");
    //     }

    //     $countries = $this->getCountries();
    //     $offices = $this->getOffices();
    //     $currencyList = $this->getCurrencyList();
    //     $licenceTypes = $this->getLicenceType();
    //     $tarrifs = [];
    //     $tarrifs = $this->getTarrif();
    //     $incoterms = $this->getIncoTerms();
    //     $payTerms = $this->getPaymentTermList();

    //     $debtors = '';
    //     $vendors = '';
    //     return view(
    //         'client-party.create',
    //         compact('payTerms', 'incoterms', 'countries', 'offices', 'currencyList', 'licenceTypes', 'tarrifs', 'debtors', 'vendors')
    //     );
    // }

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

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreClientPartyRequest  $request
     * @return \Illuminate\Http\Response
     */


    public function store(StoreClientPartyRequest $request)
    {
        $user = $request->user();
        if (!$user->can('request-client-party')) {
            abort(403, "You are not allowed to view this page.");
        }

        // MOVE CODE GENERATION TO THE VERY BEGINNING - FIX FOR UNDEFINED VARIABLE ERROR
        try {
            $clientParty = new ClientParty();
            $code = $clientParty->generateUniqueCode($user->OfficeID);
            $client_party_id = guid();

            Log::info('Generated code and ID successfully:', [
                'code' => $code,
                'client_party_id' => $client_party_id,
                'office_id' => $user->OfficeID
            ]);
        } catch (Exception $e) {
            Log::error('Code generation failed, using fallback: ' . $e->getMessage());
            // Fallback code generation
            $code = 'CP' . date('Ymd') . '_' . substr(uniqid(), -6);
            $client_party_id = uniqid('cp_', true);
            Log::info('Using fallback code generation:', ['code' => $code, 'client_party_id' => $client_party_id]);
        }

        Log::info('Store Request Data:', $request->all());
        Log::info('createBtn:', [isset($request->createBtn)]);
        Log::info('File Upload Check:', [
            'hasFile' => $request->hasFile('attachmentNames'),
            'fileCount' => $request->hasFile('attachmentNames') ? count($request->file('attachmentNames')) : 0,
            'requestType' => $request->query('type'),
            'isAccountingMode' => $request->query('type') === 'accounting',
            'code' => $code
        ]);

        // SERVER-SIDE FILE VALIDATION (BEFORE PROCESSING)
        if ($request->hasFile('attachmentNames')) {
            $files = $request->file('attachmentNames');
            $descriptions = $request->input('attachmentDescription', []);

            foreach ($files as $index => $file) {
                if (!$file) continue;

                // Validate file type
                $allowedMimes = ['application/pdf'];
                $allowedExtensions = ['pdf'];
                $fileExtension = strtolower($file->getClientOriginalExtension());
                $fileMimeType = $file->getMimeType();

                if (!in_array($fileMimeType, $allowedMimes) && !in_array($fileExtension, $allowedExtensions)) {
                    Log::error('Invalid file type uploaded:', [
                        'fileName' => $file->getClientOriginalName(),
                        'mimeType' => $fileMimeType,
                        'extension' => $fileExtension,
                        'code' => $code
                    ]);
                    Alert::error('Error', 'Invalid file type. Only PDF files are allowed: ' . $file->getClientOriginalName())->persistent(true, false);
                    return redirect()->back()->withInput();
                }

                // Validate file size (10MB max)
                $maxSize = 10 * 1024 * 1024; // 10MB in bytes
                if ($file->getSize() > $maxSize) {
                    Log::error('File size too large:', [
                        'fileName' => $file->getClientOriginalName(),
                        'size' => $file->getSize(),
                        'maxSize' => $maxSize,
                        'code' => $code
                    ]);
                    Alert::error('Error', 'File size too large. Maximum 10MB allowed: ' . $file->getClientOriginalName())->persistent(true, false);
                    return redirect()->back()->withInput();
                }

                // Validate description for accounting mode
                if ($request->query('type') === 'accounting') {
                    if (!isset($descriptions[$index]) || trim($descriptions[$index]) === '') {
                        Log::error('Missing description for accounting mode file:', [
                            'fileName' => $file->getClientOriginalName(),
                            'index' => $index,
                            'code' => $code
                        ]);
                        Alert::error('Error', 'Description is required for uploaded files in accounting mode.')->persistent(true, false);
                        return redirect()->back()->withInput();
                    }
                }
            }
        }

        // VALIDATE REQUIRED FILE UPLOAD FOR ACCOUNTING MODE
        if ($request->query('type') === 'accounting') {
            if (!$request->hasFile('attachmentNames') || empty(array_filter($request->file('attachmentNames')))) {
                Log::error('No files uploaded for accounting mode', ['code' => $code]);
                Alert::error('Error', 'File upload is required for accounting mode.')->persistent(true, false);
                return redirect()->back()->withInput();
            }
        }

        $partyEntityType = $request->partyEntityType;
        Log::info('Storing Party Type:', ['partyEntityType' => $partyEntityType, 'code' => $code]);

        $partyType = $request->legalEntityType ?? 'Debtor';
        $licenseNo = $request->licenceTypeValue;
        $officeCode = $request->officeCode ?? 'KE';
        $countryCode = $request->countryCode;
        $addressLine1 = $request->addressLine1 ?? '';
        $addressLine2 = $request->addressLine2 ?? '';
        $firstName = $request->firstName ?? '';
        $lastName = $request->lastName ?? '';
        $phoneNo = $request->phoneNo ?? '';
        $contractEmail = $request->contractEmail ?? '';

        // Address and contact fallback logic
        if ($addressLine1 === '') {
            Log::warning('AddressLine1 is empty from front-end.', ['request' => $request->all(), 'code' => $code]);
        } else {
            Log::info('AddressLine1 provided by front-end:', ['addressLine1' => $addressLine1, 'code' => $code]);
        }

        if (($addressLine1 === '' || $firstName === '' || $lastName === '' || $phoneNo === '' || $contractEmail === '')
            && $request->partyEntityType === 'Client' && $request->companyId
        ) {
            Log::info('Attempting fallback to sp_EntityAddressDetails:', [
                'companyId' => $request->companyId,
                'partyType' => $request->partyEntityType,
                'code' => $code
            ]);
            try {
                $companyDetails = DB::select(
                    'SET NOCOUNT ON;
                 EXEC [dbo].[sp_EntityAddressDetails] @PartyType = ?, @PartyID = ?',
                    [$request->partyEntityType, $request->companyId]
                );
                if (!empty($companyDetails) && isset($companyDetails[0])) {
                    Log::info('Fetched details from sp_EntityAddressDetails:', ['details' => (array)$companyDetails[0], 'code' => $code]);
                    $addressLine1 = $addressLine1 ?: ($companyDetails[0]->AddressLine1 ?? 'Nairobi');
                    $addressLine2 = $addressLine2 ?: ($companyDetails[0]->AddressLine2 ?? '');
                    $firstName = $firstName ?: ($companyDetails[0]->Cont_Bch_PersonName_FirstName ?? '');
                    $lastName = $lastName ?: ($companyDetails[0]->Cont_Bch_PersonName_LastName ?? '');
                    $phoneNo = $phoneNo ?: ($companyDetails[0]->Cont_Bch_PhoneNumbers ?? '');
                    $contractEmail = $contractEmail ?: ($companyDetails[0]->Cont_Bch_Email ?? '');
                } else {
                    Log::warning('No valid data found in sp_EntityAddressDetails for companyId: ' . $request->companyId, ['code' => $code]);
                    $addressLine1 = $addressLine1 ?: 'Nairobi';
                }
            } catch (QueryException $e) {
                Log::error('Failed to query sp_EntityAddressDetails: ' . $e->getMessage(), ['code' => $code]);
                $addressLine1 = $addressLine1 ?: 'Nairobi';
            }
        } elseif ($addressLine1 === '') {
            $addressLine1 = 'Nairobi';
            Log::info('Using default addressLine1: Nairobi', ['code' => $code]);
        }

        // License validation logic
        if ($licenseNo && $partyType && $officeCode && !in_array($request->partyEntityType, ['NoSCMIntergration', 'Create only in D365'])) {
            $partyTypes = explode(' & ', $partyType);
            $partyTypeMapLicense = [
                'debtor' => 'Debtor',
                'vendor' => 'Vendor'
            ];

            foreach ($partyTypes as $singlePartyType) {
                $mappedPartyType = $partyTypeMapLicense[strtolower($singlePartyType)] ?? $singlePartyType;
                Log::info('Checking license for:', [
                    'partyType' => $mappedPartyType,
                    'licenseNo' => $licenseNo,
                    'officeCode' => $officeCode,
                    'code' => $code
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
                        Log::warning('Duplicate license detected on store:', [
                            'partyType' => $mappedPartyType,
                            'licenseNo' => $licenseNo,
                            'officeCode' => $officeCode,
                            'remark' => $results[0]->Remark,
                            'code' => $code
                        ]);
                        Alert::error('Error', 'License number is already in use: ' . $results[0]->Remark)->persistent(true, false);
                        return redirect()->back()->withInput();
                    }
                } catch (QueryException $e) {
                    Log::error('License check failed:', [
                        'partyType' => $mappedPartyType,
                        'licenseNo' => $licenseNo,
                        'officeCode' => $officeCode,
                        'error' => $e->getMessage(),
                        'code' => $code
                    ]);
                    Alert::error('Error', 'License check failed: ' . $e->getMessage())->persistent(true, false);
                    return redirect()->back()->withInput();
                }
            }
        }

        // Country code validation
        if (!$countryCode) {
            if (!$request->companyId || !$request->partyEntityType) {
                Log::error('Missing companyId or partyEntityType for countryCode fallback.', ['code' => $code]);
                Alert::error('Error', 'Please provide a valid company and entity type.')->persistent(true, false);
                return redirect()->back()->withInput();
            }

            $companyDetails = DB::select(
                'SET NOCOUNT ON;
             EXEC [dbo].[sp_EntityAddressDetails] @PartyType = ?, @PartyID = ?',
                [$request->partyEntityType, $request->companyId]
            );

            if (!empty($companyDetails) && isset($companyDetails[0]->CountryCode)) {
                $countryCode = $countryCode ?? $companyDetails[0]->CountryCode;
            }
        }

        if (!$countryCode) {
            Log::error('CountryCode is required but could not be fetched.', ['code' => $code]);
            Alert::error('Error', 'Please select a valid country.')->persistent(true, false);
            return redirect()->back()->withInput();
        }

        $clientName = $request->client ?? $request->clientInput;

        // Status logic - NO D365 integration at early stages
        $canCreateClientParty = $user->can('create-client-party');
        $hasAccountingEntities = isset($request->debtorCheck) || isset($request->vendorCheck) ||
            isset($request->debtor) || isset($request->vendor) ||
            $request->legalEntityType;

        $status = 'Not Submitted'; // Default for draft saves

        if (isset($request->createBtn) && $canCreateClientParty) {
            if ($hasAccountingEntities) {
                $status = 'PendingCountryLevelApproval'; // No D365 integration here anymore
            } else {
                $status = 'Created';
            }
        } elseif (isset($request->submitBtn)) {
            if ($hasAccountingEntities) {
                $status = 'PendingCountryLevelApproval';
            } else {
                $status = 'Submitted';
            }
        } elseif (isset($request->createBtn) && !$canCreateClientParty) {
            if ($hasAccountingEntities) {
                $status = 'PendingCountryLevelApproval';
            } else {
                $status = 'Submitted';
            }
        }

        Log::info('Status Logic Applied:', [
            'createBtn' => isset($request->createBtn),
            'submitBtn' => isset($request->submitBtn),
            'canCreateClientParty' => $canCreateClientParty,
            'hasAccountingEntities' => $hasAccountingEntities,
            'finalStatus' => $status,
            'code' => $code
        ]);

        try {
            DB::beginTransaction();

            Log::info('Starting database transaction', ['code' => $code, 'client_party_id' => $client_party_id]);

            // Prepare entity data
            $data = [
                'Id' => $client_party_id,
                'Code' => $code,
                'OperationalEntityType' => $partyEntityType,
                'LegalEntityType' => $request->legalEntityType,
                'Name' => $request->companyName,
                'Address_Line1' => $addressLine1,
                'Address_Line2' => $addressLine2,
                'Address_CountryId' => $request->countryID ?: null,
                'Address_CountryName' => $request->countryName,
                'Address_CountryCode' => $countryCode,
                'Address_State' => $request->provinceName,
                'Address_StateId' => $request->provinceID ?: null,
                'Address_StateCode' => $request->provinceCode,
                'Address_Pin' => $request->zipCode,
                'Address_City' => $request->cityName,
                'Address_CityCode' => $request->cityCode,
                'Cont_Bch_PersonName_FirstName' => $firstName,
                'Cont_Bch_PersonName_LastName' => $lastName,
                'Cont_Bch_Dept' => $request->department,
                'Cont_Bch_PhoneNumbers' => $phoneNo,
                'Cont_Bch_Email' => $contractEmail,
                'SpecialInstructions' => $request->specialInstruction,
                'Office' => $request->officeCountry,
                'OfficeCode' => $request->officeCode,
                'OfficeID' => $request->officeCountryID,
                'DefaultNotifyPartyName' => $request->notifyParty1,
                'DefaultNotifyPartyId' => $request->notifyParty1ID ?: null,
                'DefaultNotifyPartyCode' => $request->notifyParty1Code,
                'AccountMgrOfficeFunMapName' => $request->accountPIC,
                'AccountMgrOfficeFunMapId' => $request->accountPICID ?: null,
                'DefaultNotifyParty1Name' => $request->notifyParty2,
                'DefaultNotifyParty1Id' => $request->notifyParty2ID ?: null,
                'DefaultNotifyParty1Code' => $request->notifyParty2Code,
                'DefaultFowarderName' => $request->forwarderName,
                'DefaultFowarderId' => $request->forwarderID ?: null,
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
                'ClientID' => $request->clientID,
                'ClientCode' => $request->clientCode,
                'RequestId' => $request->requestID ?? null,
                'RequestName' => $request->clientOptionVal ?? null,
                'RequestCode' => $request->requestCode ?? null,
                'ShipperConsigneeName' => $request->shipperInput,
                'ShipperConsigneeID' => $request->shipperID,
                'ShipperConsigneeCode' => $request->shipperCode,
                'OtherPartyName' => $request->otherInput,
                'OtherPartyID' => $request->otherID,
                'OtherPartyCode' => $request->otherCode,
                'Debtor' => $request->debtor,
                'DebtorID' => $request->debtorID,
                'DebtorCode' => $request->debtorCode,
                'Vendor' => $request->vendor,
                'VendorID' => $request->vendorID,
                'VendorCode' => $request->vendorCode,
                'RequestforClient' => isset($request->clientCheck) ? 1 : 0,
                'RequestforShipperConsignee' => isset($request->consigneeCheck) ? 1 : (isset($request->shipperCheck) ? 1 : 0),
                'RequestforOtherParty' => isset($request->otherCheck) ? 1 : 0,
                'RequestforDebtor' => isset($request->debtorCheck) ? 1 : 0,
                'RequestforVendor' => isset($request->vendorCheck) ? 1 : 0,
                'CreatedBy' => $user->full_name,
                'CreatedOn' => Carbon::now()->format('Y-m-d H:i:s'),
                'LastModifiedOn' => Carbon::now()->format('Y-m-d H:i:s'),
                'LastModifiedBy' => $user->full_name,
                'DebtorType' => $request->debtorType,
                'VendorType' => $request->vendorType,
                'Status' => $status,
            ];

            // Insert entity data
            Log::info('Inserting entity data', ['code' => $code, 'client_party_id' => $client_party_id]);
            DB::table('Cust_Entity')->insert($data);

            // HANDLE FILE UPLOADS WITHIN THE SAME TRANSACTION
            if ($request->hasFile('attachmentNames')) {
                $uploadedFiles = [];

                Log::info('Starting file upload process inside transaction', [
                    'code' => $code,
                    'fileCount' => count($request->file('attachmentNames'))
                ]);

                foreach ($request->file('attachmentNames') as $key => $attachment) {
                    if (!$attachment) continue;

                    $fileName = $attachment->getClientOriginalName();
                    $newName = $code . '_' . pathinfo($fileName, PATHINFO_FILENAME) . '.' . $attachment->getClientOriginalExtension();

                    Log::info('Processing file upload inside transaction:', [
                        'originalName' => $fileName,
                        'newName' => $newName,
                        'code' => $code,
                        'key' => $key
                    ]);

                    try {
                        // Store locally first
                        $localPath = $attachment->storeAs('client-party', $newName, 'local');
                        $storagePath = 'Kenya/ClientParty/' . $newName;

                        Log::info('File stored locally, uploading to Azure', [
                            'localPath' => $localPath,
                            'storagePath' => $storagePath,
                            'code' => $code
                        ]);

                        // Upload to Azure
                        $azureUpload = Storage::disk('azure')->put($storagePath, file_get_contents(storage_path('app/' . $localPath)));

                        if (!$azureUpload) {
                            throw new Exception('Failed to upload file to Azure storage: ' . $newName);
                        }

                        // Create attachment record
                        $attachmentRecord = ClientPartyAttahment::create([
                            'ID' => guid(),
                            'CustEntityID' => $client_party_id,
                            'RequestNo' => $code,
                            'Title' => $request->attachmentDescription[$key] ?? 'Attachment',
                            'FileName' => $newName,
                            'FilePath' => $storagePath,
                            'CreatedBy' => $user->ID,
                            'CreatedOn' => Carbon::now(),
                            'LastModifiedBy' => $user->ID,
                            'LastModifiedOn' => Carbon::now(),
                        ]);

                        $uploadedFiles[] = [
                            'originalName' => $fileName,
                            'newName' => $newName,
                            'attachmentId' => $attachmentRecord->ID
                        ];

                        Log::info('File uploaded successfully inside transaction:', [
                            'originalName' => $fileName,
                            'newName' => $newName,
                            'storagePath' => $storagePath,
                            'attachmentId' => $attachmentRecord->ID,
                            'description' => $request->attachmentDescription[$key] ?? 'Attachment',
                            'code' => $code
                        ]);
                    } catch (Exception $e) {
                        Log::error('File upload failed inside transaction:', [
                            'fileName' => $fileName,
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString(),
                            'code' => $code
                        ]);
                        // Rollback transaction on file upload failure
                        throw new Exception('File upload failed: ' . $fileName . ' - ' . $e->getMessage());
                    } finally {
                        // Clean up local file if it exists
                        if (isset($localPath) && file_exists(storage_path('app/' . $localPath))) {
                            File::delete(storage_path('app/' . $localPath));
                            Log::info('Cleaned up local file', ['localPath' => $localPath, 'code' => $code]);
                        }
                    }
                }

                Log::info('All files uploaded successfully inside transaction:', [
                    'uploadedFiles' => $uploadedFiles,
                    'totalFiles' => count($uploadedFiles),
                    'entityId' => $client_party_id,
                    'requestNo' => $code
                ]);
            } else {
                Log::info('No files to upload inside transaction:', [
                    'entityId' => $client_party_id,
                    'requestNo' => $code,
                    'requestType' => $request->query('type')
                ]);
            }

            // Create history record
            $history = [
                'ID' => guid(),
                'CustEntityID' => $client_party_id,
                'RequestNo' => $code,
                'Role' => $user->Role,
                'ReasonID' => '9c608054-1879-485d-aee7-c15c33565b13',
                'Comment' => 'New Entity Created',
                'Type' => 'Entity',
                'PreviousValue' => '',
                'NewValue' => $status,
                'CreatedBy' => $user->ID,
                'CreatedOn' => Carbon::now(),
                'LastModifiedBy' => $user->ID,
                'LastModifiedOn' => Carbon::now()
            ];
            EntityHistory::create($history);

            // Commit the transaction
            DB::commit();
            Log::info('Entity and files saved successfully in transaction', ['code' => $code, 'client_party_id' => $client_party_id]);
        } catch (Exception $e) {
            DB::rollback();
            Log::error('Store Failed in transaction: ' . $e->getMessage(), [
                'code' => $code,
                'client_party_id' => $client_party_id,
                'trace' => $e->getTraceAsString()
            ]);
            Alert::error('Error', $e->getMessage())->persistent(true, false);
            return redirect()->route('client-party.index');
        }

        $client_party_status = 0;
        $results = [];

        // Integration logic - NO D365 integration at PendingCountryLevelApproval anymore
        if (isset($request->createBtn)) {
            if ($status === 'PendingCountryLevelApproval') {
                // No D365 integration here - just proceed to approval workflow
                $results = ['data' => ['Status' => 'Success', 'Remark' => 'Entity pending country level approval']];
            } elseif ($status === 'Created') {
                $client_party_status = 1;

                if ($request->partyEntityType != 'NoSCMIntegration' && $request->partyEntityType != 'Create only in D365') {
                    $scmRequest = new \Illuminate\Http\Request(['requestID' => $client_party_id]);
                    $results = $this->CreateClientPartySCM($scmRequest);

                    if (!isset($results['success']) || !$results['success']) {
                        if (isset($results['data']) && $results['data']['Status'] === 'Success') {
                            $results = ['data' => $results['data']];
                        } else {
                            $results = ['data' => ['Status' => 'Failed', 'Remark' => 'SCM Creation failed']];
                        }
                    } else {
                        $results = ['data' => $results['data']];
                    }
                } else {
                    $results = ['data' => ['Status' => 'Success', 'Remark' => 'Entity Created Successfully']];
                }
            }
        } elseif (isset($request->submitBtn)) {
            $results = ['data' => ['Status' => 'Success', 'Remark' => 'Entity Saved for Approval']];
        } else {
            $results = ['data' => ['Status' => 'Success', 'Remark' => 'Entity Saved']];
        }

        Log::info('Final Results:', ['results' => (array)$results, 'code' => $code]);

        // Display appropriate alert based on results
        if (
            isset($results['data']) && is_array($results['data']) &&
            isset($results['data']['Status']) && $results['data']['Status'] !== 'Failed'
        ) {
            Log::info('Triggering success alert', [
                'status' => $results['data']['Status'],
                'remark' => $results['data']['Remark'],
                'code' => $code
            ]);
            Alert::success($results['data']['Status'], $results['data']['Remark'])
                ->persistent(true, false)->showConfirmButton();
        } else {
            $errorStatus = $results['data']['Status'] ?? 'Failed';
            $errorRemark = $results['data']['Remark'] ?? 'Unknown error occurred';
            Log::error('Triggering error alert', ['status' => $errorStatus, 'remark' => $errorRemark, 'code' => $code]);
            Alert::error($errorStatus, $errorRemark)->persistent(true, false)->showConfirmButton();
        }

        Log::info('Final alert state before redirect', ['session' => session()->all(), 'code' => $code]);
        return redirect()->route('client-party.index')
            ->with('success', $results['data']['Status'] ?? 'Success')
            ->with('success_message', $results['data']['Remark'] ?? 'Entity processed successfully');
    }
    // Get Office Number

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

    public function preview(ClientParty $path)
    {
        $attachment = $path->attachments;
        $filePath = $attachment[0]->FilePath;

        return Storage::disk('azure')->download($filePath, basename($attachment[0]->FilePath), [
            'Content-Type' => 'application/octet-stream',
            'Content-Disposition' => 'inline; filename="' . basename($attachment[0]->FilePath) . '"'
        ]);
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

        // Check permissions and OperationalEntityType
        $entity = DB::table('Cust_entity')->where('Id', $requestID)->first();
        if (!$entity) {
            Log::warning('Entity not found:', ['RequestID' => $requestID]);
            return ['success' => false, 'error' => 'Entity not found'];
        }

        if (!Auth::user()->can('create-client-party-SCM') && $entity->OperationalEntityType !== 'Client without Debtor or Vendor') {
            Log::warning('SCM Permission Denied:', [
                'UserID' => Auth::user()->Id,
                'Permissions' => Auth::user()->getAllPermissions()->pluck('name')->toArray(),
                'OperationalEntityType' => $entity->OperationalEntityType
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
                    'OperationalEntityType' => $entity->OperationalEntityType
                ]);

                // Check stored procedure status
                if ($result['Status'] === 'Success') {
                    return ['success' => true, 'data' => $result];
                } else {
                    Log::warning('Stored Procedure Failed:', [
                        'RequestID' => $requestID,
                        'Remark' => $result['Remark'],
                        'OperationalEntityType' => $entity->OperationalEntityType
                    ]);
                    return ['success' => false, 'error' => $result['Remark']];
                }
            } else {
                Log::warning('No valid data returned from Sp_OperationEntityCreation:', ['RequestID' => $requestID]);
                return ['success' => false, 'error' => 'No data returned from stored procedure'];
            }
        } catch (QueryException $e) {
            Log::error('Error executing Sp_OperationEntityCreation:', [
                'RequestID' => $requestID,
                'Error' => $e->getMessage(),
                'OperationalEntityType' => $entity->OperationalEntityType
            ]);
            return ['success' => false, 'error' => 'Entity Creation Failed: ' . $e->getMessage()];
        }
    }
// public function CreateClientPartySCM(HttpRequest $request)
// {
//     $requestID = $request->input('requestID');
//     $logContext = ['RequestID' => $requestID, 'Method' => 'CreateClientPartySCM'];

//     Log::info('═══════════════════════════════════════════════════════════', $logContext);
//     Log::info('SCM Entity Creation Started', $logContext);
//     Log::info('═══════════════════════════════════════════════════════════', $logContext);

//     // Step 1: Validate RequestID Format
//     Log::info('Step 1: Validating RequestID format', $logContext);
//     if (!preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $requestID)) {
//         Log::error('❌ RequestID validation failed - Invalid UUID format', array_merge($logContext, [
//             'RequestID_Received' => $requestID,
//             'Expected_Format' => 'UUID (xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx)'
//         ]));
//         return ['success' => false, 'error' => 'Invalid Request ID format'];
//     }
//     Log::info('✅ RequestID format valid', $logContext);

//     // Step 2: Fetch Entity from Database
//     Log::info('Step 2: Fetching entity from Cust_entity table', $logContext);
//     try {
//         $entity = DB::table('Cust_entity')->where('Id', $requestID)->first();

//         if (!$entity) {
//             Log::error('❌ Entity not found in database', array_merge($logContext, [
//                 'Table' => 'Cust_entity',
//                 'Query' => "SELECT * FROM Cust_entity WHERE Id = '$requestID'"
//             ]));
//             return ['success' => false, 'error' => 'Entity not found'];
//         }

//         Log::info('✅ Entity found in database', array_merge($logContext, [
//             'Entity_Code' => $entity->Code,
//             'Entity_Name' => $entity->Name,
//             'Entity_Status' => $entity->Status,
//             'OperatioEnaltityType' => $entity->OperatioEntityType,
//             'OfficeCode' => $entity->OfficeCode,
//             'Current_ClientCode' => $entity->ClientCode ?? 'NULL',
//             'Current_ClientID' => $entity->ClientID ?? 'NULL',
//             'Current_OtherCode' => $entity->OtherCode ?? 'NULL',
//             'Current_OtherID' => $entity->OtherID ?? 'NULL',
//             'RequestforOtherParty' => $entity->RequestforOtherParty ?? 0
//         ]));

//     } catch (QueryException $e) {
//         Log::error('❌ Database query failed when fetching entity', array_merge($logContext, [
//             'Error' => $e->getMessage(),
//             'SQL' => $e->getSql(),
//             'Bindings' => $e->getBindings(),
//             'ErrorCode' => $e->getCode()
//         ]));
//         return ['success' => false, 'error' => 'Database error: ' . $e->getMessage()];
//     }

//     // Step 3: Permission Check
//     Log::info('Step 3: Checking user permissions', $logContext);
//     $user = Auth::user();
//     $hasPermission = $user->can('create-client-party-SCM');
//     $isSpecialType = $entity->OperatioEntityType === 'Client without Debtor or Vendor';

//     Log::info('Permission check details', array_merge($logContext, [
//         'User_ID' => $user->Id,
//         'User_Name' => $user->full_name ?? $user->LoginID,
//         'Has_create-client-party-SCM' => $hasPermission ? 'YES' : 'NO',
//         'Entity_Type' => $entity->OperatioEntityType,
//         'Is_Special_Type' => $isSpecialType ? 'YES' : 'NO',
//         'Permission_Required' => (!$hasPermission && !$isSpecialType) ? 'YES - FAILED' : 'NO - PASSED'
//     ]));

//     if (!$hasPermission && !$isSpecialType) {
//         Log::error('❌ Permission denied - User lacks required permission', array_merge($logContext, [
//             'User_ID' => $user->Id,
//             'User_Permissions' => $user->getAllPermissions()->pluck('name')->toArray(),
//             'Required_Permission' => 'create-client-party-SCM',
//             'Entity_Type' => $entity->OperatioEntityType
//         ]));
//         return ['success' => false, 'error' => 'Unauthorized SCM Access'];
//     }
//     Log::info('✅ Permission check passed', $logContext);

//     // Step 4: Capture Entity State BEFORE SP Execution
//     Log::info('Step 4: Capturing entity state BEFORE SP execution', $logContext);
//     $entityBeforeSP = DB::table('Cust_entity')
//         ->where('Id', $requestID)
//         ->first([
//             'Code', 'Status', 'Name',
//             'ClientCode', 'ClientID', 'ClientName',
//             'OtherPartyCode', 'OtherPartyID', 'OtherPartyName',
//             'Address_Line1', 'Address_CountryCode',
//             'Cont_Bch_Email', 'Cont_Bch_PhoneNumbers',
//             'LegalEntityType', 'DebtorCode', 'VendorCode',
//             'CreditLimit', 'CreditDays', 'BillingCurrencyCode',
//             'LastModifiedBy', 'LastModifiedOn'
//         ]);

//     Log::info('Entity state captured BEFORE SP', array_merge($logContext, [
//         'BEFORE' => [
//             'Code' => $entityBeforeSP->Code,
//             'Status' => $entityBeforeSP->Status,
//             'Name' => $entityBeforeSP->Name,
//             'ClientCode' => $entityBeforeSP->ClientCode ?? 'NULL',
//             'ClientID' => $entityBeforeSP->ClientID ?? 'NULL',
//             'OtherCode' => $entityBeforeSP->OtherPartyCode ?? 'NULL',
//             'OtherID' => $entityBeforeSP->OtherPartyID ?? 'NULL',
//             'LegalEntityType' => $entityBeforeSP->LegalEntityType ?? 'NULL',
//             'DebtorCode' => $entityBeforeSP->DebtorCode ?? 'NULL',
//             'VendorCode' => $entityBeforeSP->VendorCode ?? 'NULL',
//             'CreditLimit' => $entityBeforeSP->CreditLimit ?? 'NULL',
//             'CreditDays' => $entityBeforeSP->CreditDays ?? 'NULL',
//             'BillingCurrencyCode' => $entityBeforeSP->BillingCurrencyCode ?? 'NULL',
//             'LastModifiedBy' => $entityBeforeSP->LastModifiedBy ?? 'NULL',
//             'LastModifiedOn' => $entityBeforeSP->LastModifiedOn ?? 'NULL'
//         ]
//     ]));

//     // Step 5: Prepare JSON Input for SP
//     Log::info('Step 5: Preparing JSON input for sp_CustEntity', $logContext);
//     $inputData = [
//         'OperationType' => 'OperationalEntityCreation',
//         'SubmittedDatetime' => Carbon::now()->format('YmdHis'),
//         'SubmittedBy' => $user->full_name ?? $user->LoginID ?? 'System',
//         'Action' => 'Add',
//         'RequestNo' => $entity->Code,
//         'Status' => 'Create'
//     ];

//     $inputJson = json_encode($inputData);

//     Log::info('JSON input prepared', array_merge($logContext, [
//         'InputData' => $inputData,
//         'InputJson' => $inputJson,
//         'JsonLength' => strlen($inputJson)
//     ]));

//     // Step 6: Execute Stored Procedure
//     Log::info('Step 6: Executing sp_CustEntity stored procedure', array_merge($logContext, [
//         'SP_Name' => 'sp_CustEntity',
//         'Database' => config('database.connections.sqlsrv.database'),
//         'Execution_Time_Start' => Carbon::now()->toDateTimeString()
//     ]));

//     try {
//         $startTime = microtime(true);

//         $results = DB::select(
//             'SET NOCOUNT ON;
//              DECLARE @OutputJson VARCHAR(MAX);
//              EXEC [dbo].[sp_CustEntity]
//                  @InputJson = ?,
//                  @OutputJson = @OutputJson OUTPUT;
//              SELECT @OutputJson AS OutputJson',
//             [$inputJson]
//         );

//         $executionTime = round((microtime(true) - $startTime) * 1000, 2);

//         Log::info('✅ Stored procedure executed successfully', array_merge($logContext, [
//             'Execution_Time_MS' => $executionTime,
//             'Execution_Time_End' => Carbon::now()->toDateTimeString(),
//             'Results_Count' => count($results),
//             'Has_Output' => !empty($results) && isset($results[0]->OutputJson) ? 'YES' : 'NO'
//         ]));

//         // Step 7: Validate SP Output
//         Log::info('Step 7: Validating stored procedure output', $logContext);

//         if (empty($results) || !isset($results[0]->OutputJson)) {
//             Log::error('❌ SP returned empty or invalid output', array_merge($logContext, [
//                 'Results_Empty' => empty($results) ? 'YES' : 'NO',
//                 'OutputJson_Present' => isset($results[0]->OutputJson) ? 'YES' : 'NO',
//                 'Results_Structure' => !empty($results) ? json_encode($results) : 'NULL'
//             ]));
//             return ['success' => false, 'error' => 'No output returned from stored procedure'];
//         }

//         $rawOutput = $results[0]->OutputJson;
//         Log::info('Raw SP output received', array_merge($logContext, [
//             'Raw_Output' => $rawOutput,
//             'Output_Length' => strlen($rawOutput)
//         ]));

//         // Step 8: Parse JSON Output
//         Log::info('Step 8: Parsing JSON output', $logContext);
//         $output = json_decode($rawOutput, true);

//         if (json_last_error() !== JSON_ERROR_NONE) {
//             Log::error('❌ JSON parsing failed', array_merge($logContext, [
//                 'Raw_Output' => $rawOutput,
//                 'JSON_Error_Code' => json_last_error(),
//                 'JSON_Error_Message' => json_last_error_msg(),
//                 'Attempted_Parse' => 'json_decode($rawOutput, true)'
//             ]));
//             return ['success' => false, 'error' => 'Failed to parse stored procedure output: ' . json_last_error_msg()];
//         }

//         Log::info('✅ JSON parsed successfully', array_merge($logContext, [
//             'Parsed_Output' => $output
//         ]));

//         // Step 9: Extract Status and Remarks
//         Log::info('Step 9: Extracting status and remarks from output', $logContext);
//         $status = $output['Status'] ?? 'UNKNOWN';
//         $remarks = $output['Remarks'] ?? [];

//         $remarkMessage = '';
//         if (is_array($remarks)) {
//             $remarkMessages = array_column($remarks, 'Remark');
//             $remarkMessage = implode('; ', $remarkMessages);

//             Log::info('Remarks extracted', array_merge($logContext, [
//                 'Remarks_Count' => count($remarks),
//                 'Remarks_Array' => $remarkMessages,
//                 'Remarks_Combined' => $remarkMessage
//             ]));
//         } else {
//             Log::warning('⚠️ Remarks is not an array', array_merge($logContext, [
//                 'Remarks_Type' => gettype($remarks),
//                 'Remarks_Value' => $remarks
//             ]));
//         }

//         Log::info('Status and remarks processed', array_merge($logContext, [
//             'Status' => $status,
//             'Remarks_Message' => $remarkMessage ?: 'No remarks'
//         ]));

//         // Step 10: Check Transaction Log
//         Log::info('Step 10: Checking Cust_TransactionLog for this operation', $logContext);
//         try {
//             $transactionLog = DB::table('Cust_TransactionLog')
//                 ->where('InputJson', 'LIKE', '%"RequestNo":"' . $entity->Code . '"%')
//                 ->orderBy('SubmittedOn', 'desc')
//                 ->first();

//             if ($transactionLog) {
//                 Log::info('Transaction log entry found', array_merge($logContext, [
//                     'Transaction_ID' => $transactionLog->Id,
//                     'Operation_Type' => $transactionLog->OperationType,
//                     'Transaction_Status' => $transactionLog->Status ?? 'NULL',
//                     'SubmittedOn' => $transactionLog->SubmittedOn,
//                     'SubmittedBy' => $transactionLog->SubmittedBy,
//                     'Transaction_Remarks' => $transactionLog->Remarks ?? 'NULL'
//                 ]));
//             } else {
//                 Log::warning('⚠️ No transaction log entry found', array_merge($logContext, [
//                     'Expected_RequestNo' => $entity->Code,
//                     'Search_Pattern' => '%"RequestNo":"' . $entity->Code . '"%'
//                 ]));
//             }
//         } catch (Exception $e) {
//             Log::warning('⚠️ Failed to query transaction log', array_merge($logContext, [
//                 'Error' => $e->getMessage()
//             ]));
//         }

//         // Step 11: Handle Success/Failure
//         if ($status === 'Success') {
//             Log::info('═══════════════════════════════════════════════════════════', $logContext);
//             Log::info('✅ SP Execution Status: SUCCESS', $logContext);
//             Log::info('═══════════════════════════════════════════════════════════', $logContext);

//             // Step 12: Capture Entity State AFTER SP Execution
//             Log::info('Step 12: Capturing entity state AFTER SP execution', $logContext);

//             $entityAfterSP = DB::table('Cust_entity')
//                 ->where('Id', $requestID)
//                 ->first([
//                     'Code', 'Status', 'Name',
//                     'ClientCode', 'ClientID', 'ClientName',
//                     'OtherPartyCode', 'OtherPartyID', 'OtherPartyName',
//                     'Address_Line1', 'Address_CountryCode',
//                     'Cont_Bch_Email', 'Cont_Bch_PhoneNumbers',
//                     'LegalEntityType', 'DebtorCode', 'VendorCode',
//                     'CreditLimit', 'CreditDays', 'BillingCurrencyCode',
//                     'LastModifiedBy', 'LastModifiedOn'
//                 ]);

//             Log::info('Entity state captured AFTER SP', array_merge($logContext, [
//                 'AFTER' => [
//                     'Code' => $entityAfterSP->Code,
//                     'Status' => $entityAfterSP->Status,
//                     'Name' => $entityAfterSP->Name,
//                     'ClientCode' => $entityAfterSP->ClientCode ?? 'NULL',
//                     'ClientID' => $entityAfterSP->ClientID ?? 'NULL',
//                     'OtherCode' => $entityAfterSP->OtherPartyCode ?? 'NULL',
//                     'OtherID' => $entityAfterSP->OtherPartyID ?? 'NULL',
//                     'LegalEntityType' => $entityAfterSP->LegalEntityType ?? 'NULL',
//                     'DebtorCode' => $entityAfterSP->DebtorCode ?? 'NULL',
//                     'VendorCode' => $entityAfterSP->VendorCode ?? 'NULL',
//                     'CreditLimit' => $entityAfterSP->CreditLimit ?? 'NULL',
//                     'CreditDays' => $entityAfterSP->CreditDays ?? 'NULL',
//                     'BillingCurrencyCode' => $entityAfterSP->BillingCurrencyCode ?? 'NULL',
//                     'LastModifiedBy' => $entityAfterSP->LastModifiedBy ?? 'NULL',
//                     'LastModifiedOn' => $entityAfterSP->LastModifiedOn ?? 'NULL'
//                 ]
//             ]));

//             // Step 13: Compare Before and After
//             Log::info('Step 13: Comparing entity state BEFORE vs AFTER SP', $logContext);

//             $changes = [];
//             $preservedFields = [];
//             $modifiedFields = [];

//             // Check what changed
//             if ($entityBeforeSP->ClientCode !== $entityAfterSP->ClientCode) {
//                 $changes['ClientCode'] = [
//                     'before' => $entityBeforeSP->ClientCode ?? 'NULL',
//                     'after' => $entityAfterSP->ClientCode ?? 'NULL'
//                 ];
//                 $modifiedFields[] = 'ClientCode';
//             } else {
//                 $preservedFields[] = 'ClientCode';
//             }

//             if ($entityBeforeSP->ClientID !== $entityAfterSP->ClientID) {
//                 $changes['ClientID'] = [
//                     'before' => $entityBeforeSP->ClientID ?? 'NULL',
//                     'after' => $entityAfterSP->ClientID ?? 'NULL'
//                 ];
//                 $modifiedFields[] = 'ClientID';
//             } else {
//                 $preservedFields[] = 'ClientID';
//             }

//             if ($entityBeforeSP->OtherPartyCode !== $entityAfterSP->OtherPartyCode) {
//                 $changes['OtherPartyCode'] = [
//                     'before' => $entityBeforeSP->OtherPartyCode ?? 'NULL',
//                     'after' => $entityAfterSP->OtherPartyCode ?? 'NULL'
//                 ];
//                 $modifiedFields[] = 'OtherPartyCode';
//             } else {
//                 $preservedFields[] = 'OtherPartyCode';
//             }

//             if ($entityBeforeSP->OtherPartyID !== $entityAfterSP->OtherPartyID) {
//                 $changes['OtherPartyID'] = [
//                     'before' => $entityBeforeSP->OtherPartyID ?? 'NULL',
//                     'after' => $entityAfterSP->OtherPartyID ?? 'NULL'
//                 ];
//                 $modifiedFields[] = 'OtherPartyID';
//             } else {
//                 $preservedFields[] = 'OtherPartyID';
//             }

//             if ($entityBeforeSP->Status !== $entityAfterSP->Status) {
//                 $changes['Status'] = [
//                     'before' => $entityBeforeSP->Status,
//                     'after' => $entityAfterSP->Status
//                 ];
//                 $modifiedFields[] = 'Status';
//             } else {
//                 $preservedFields[] = 'Status';
//             }

//             // Check critical fields for unintended changes
//             $criticalFields = [
//                 'LegalEntityType', 'DebtorCode', 'VendorCode',
//                 'CreditLimit', 'CreditDays', 'BillingCurrencyCode',
//                 'Address_Line1', 'Address_CountryCode',
//                 'Cont_Bch_Email', 'Cont_Bch_PhoneNumbers'
//             ];

//             $unintendedChanges = [];
//             foreach ($criticalFields as $field) {
//                 if ($entityBeforeSP->$field !== $entityAfterSP->$field) {
//                     $unintendedChanges[$field] = [
//                         'before' => $entityBeforeSP->$field ?? 'NULL',
//                         'after' => $entityAfterSP->$field ?? 'NULL'
//                     ];
//                 }
//             }

//             if (!empty($unintendedChanges)) {
//                 Log::error('❌ CRITICAL: Unintended field changes detected!', array_merge($logContext, [
//                     'Unintended_Changes' => $unintendedChanges,
//                     'Warning' => 'SP may have overwritten Laravel-managed fields'
//                 ]));
//             } else {
//                 Log::info('✅ No unintended field changes detected', array_merge($logContext, [
//                     'Critical_Fields_Checked' => count($criticalFields),
//                     'All_Preserved' => 'YES'
//                 ]));
//             }

//             Log::info('Field change summary', array_merge($logContext, [
//                 'Total_Changes' => count($changes),
//                 'Modified_Fields' => $modifiedFields,
//                 'Preserved_Fields' => $preservedFields,
//                 'Changes_Detail' => $changes,
//                 'Unintended_Changes' => !empty($unintendedChanges) ? $unintendedChanges : 'NONE'
//             ]));

//             // Step 14: Check SCM Tables
//             Log::info('Step 14: Verifying entity creation in SCM tables', $logContext);

//             try {
//                 // Check Cust_Clients
//                 if ($entityAfterSP->ClientID) {
//                     $scmClient = DB::connection('sqlsrv') // or your SCM connection
//                         ->table('FITSCMUAT.dbo.Cust_Clients')
//                         ->where('Id', $entityAfterSP->ClientID)
//                         ->first();

//                     if ($scmClient) {
//                         Log::info('✅ Client found in SCM Cust_Clients', array_merge($logContext, [
//                             'ClientID' => $entityAfterSP->ClientID,
//                             'ClientCode' => $scmClient->Code,
//                             'ClientName' => $scmClient->Name,
//                             'CreatedBy' => $scmClient->CreatedBy,
//                             'CreatedOn' => $scmClient->CreatedOn
//                         ]));
//                     } else {
//                         Log::warning('⚠️ Client NOT found in SCM Cust_Clients', array_merge($logContext, [
//                             'Expected_ClientID' => $entityAfterSP->ClientID
//                         ]));
//                     }
//                 }

//                 // Check Cust_Party
//                 if ($entityAfterSP->OtherPartyID) {
//                     $scmParty = DB::connection('sqlsrv')
//                         ->table('FITSCMUAT.dbo.Cust_Party')
//                         ->where('Id', $entityAfterSP->OtherPartyID)
//                         ->first();

//                     if ($scmParty) {
//                         Log::info('✅ Party found in SCM Cust_Party', array_merge($logContext, [
//                             'PartyID' => $entityAfterSP->OtherPartyID,
//                             'PartyCode' => $scmParty->Code,
//                             'PartyName' => $scmParty->Name,
//                             'CreatedBy' => $scmParty->CreatedBy,
//                             'CreatedOn' => $scmParty->CreatedOn
//                         ]));
//                     } else {
//                         Log::warning('⚠️ Party NOT found in SCM Cust_Party', array_merge($logContext, [
//                             'Expected_PartyID' => $entityAfterSP->OtherPartyID
//                         ]));
//                     }
//                 }

//             } catch (Exception $e) {
//                 Log::warning('⚠️ Failed to verify SCM tables', array_merge($logContext, [
//                     'Error' => $e->getMessage(),
//                     'Note' => 'This may be expected if using different database connection'
//                 ]));
//             }

//             // Step 15: Return Success
//             Log::info('Step 15: Preparing success response', $logContext);
//             $response = [
//                 'success' => true,
//                 'data' => [
//                     'Status' => 'Success',
//                     'Remark' => $remarkMessage ?: 'Entity created successfully',
//                     'ClientCode' => $entityAfterSP->ClientCode ?? null,
//                     'ClientID' => $entityAfterSP->ClientID ?? null,
//                     'OtherCode' => $entityAfterSP->OtherPartyCode ?? null,
//                     'OtherID' => $entityAfterSP->OtherPartyID ?? null
//                 ]
//             ];

//             Log::info('═══════════════════════════════════════════════════════════', $logContext);
//             Log::info('✅ SCM Entity Creation COMPLETED SUCCESSFULLY', array_merge($logContext, [
//                 'Response' => $response
//             ]));
//             Log::info('═══════════════════════════════════════════════════════════', $logContext);

//             return $response;

//         } else {
//             // Handle Failure
//             Log::error('═══════════════════════════════════════════════════════════', $logContext);
//             Log::error('❌ SP Execution Status: FAILED', array_merge($logContext, [
//                 'Status' => $status,
//                 'Remarks' => $remarkMessage,
//                 'Full_Output' => $output
//             ]));
//             Log::error('═══════════════════════════════════════════════════════════', $logContext);

//             // Check error log
//             try {
//                 $errorLog = DB::table('Cust_Errorlog')
//                     ->where('ErrorMessage', 'LIKE', '%' . $entity->Code . '%')
//                     ->orWhere('ErrorMessage', 'LIKE', '%OperationalEntityCreation%')
//                     ->orderBy('ErrorDate', 'desc')
//                     ->first();

//                 if ($errorLog) {
//                     Log::error('Error log entry found', array_merge($logContext, [
//                         'Error_ID' => $errorLog->Id,
//                         'Error_Number' => $errorLog->ErrorNumber,
//                         'Error_State' => $errorLog->ErrorState,
//                         'Error_Severity' => $errorLog->ErrorSeverity,
//                         'Error_Procedure' => $errorLog->ErrorProcedure,
//                         'Error_Line' => $errorLog->ErrorLine,
//                         'Error_Message' => $errorLog->ErrorMessage,
//                         'Error_Date' => $errorLog->ErrorDate
//                     ]));
//                 }
//             } catch (Exception $e) {
//                 Log::warning('⚠️ Failed to query error log', array_merge($logContext, [
//                     'Error' => $e->getMessage()
//                 ]));
//             }

//             return [
//                 'success' => false,
//                 'error' => $remarkMessage ?: 'Entity creation failed',
//                 'data' => [
//                     'Status' => 'Failed',
//                     'Remark' => $remarkMessage
//                 ]
//             ];
//         }

//     } catch (QueryException $e) {
//         Log::error('═══════════════════════════════════════════════════════════', $logContext);
//         Log::error('❌ DATABASE EXCEPTION during SP execution', array_merge($logContext, [
//             'Exception_Type' => 'QueryException',
//             'Error_Message' => $e->getMessage(),
//             'SQL_State' => $e->errorInfo[0] ?? 'N/A',
//             'Driver_Error_Code' => $e->errorInfo[1] ?? 'N/A',
//             'Driver_Error_Message' => $e->errorInfo[2] ?? 'N/A',
//             'SQL_Query' => $e->getSql(),
//             'SQL_Bindings' => $e->getBindings(),
//             'File' => $e->getFile(),
//             'Line' => $e->getLine(),
//             'Stack_Trace' => $e->getTraceAsString()
//         ]));
//         Log::error('═══════════════════════════════════════════════════════════', $logContext);

//         return ['success' => false, 'error' => 'Database error: ' . $e->getMessage()];

//     } catch (Exception $e) {
//         Log::error('═══════════════════════════════════════════════════════════', $logContext);
//         Log::error('❌ UNEXPECTED EXCEPTION', array_merge($logContext, [
//             'Exception_Type' => get_class($e),
//             'Error_Message' => $e->getMessage(),
//             'Error_Code' => $e->getCode(),
//             'File' => $e->getFile(),
//             'Line' => $e->getLine(),
//             'Stack_Trace' => $e->getTraceAsString()
//         ]));
//         Log::error('═══════════════════════════════════════════════════════════', $logContext);

//         return ['success' => false, 'error' => 'Unexpected error: ' . $e->getMessage()];
//     }
// }

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

    public function reject()
    {
        $user = Auth::user();

        if (!$user->can('approve-entity-request') && !$user->can('create-client-party')) {
            alert()->warning('Warning !', 'Unauthorised.')->showConfirmButton();
            return response()->json(['false', 312]);
        }

        $id = \request()->get('id');
        $level = \request()->get('level');
        $reason = \request()->get('reason');

        $clientParty = DB::table('Cust_entity')->where('Id', $id)->first();

        $history = [
            'ID' => guid(),
            'CustEntityID' => $id,
            'RequestNo' => $clientParty->Code,
            'Role' => $user->Role,
            'ReasonID' => '84d62970-a1ce-47d3-9428-36e4647811f9',
            'Comment' => $reason,
            'Type' => 'Entity',
            'PreviousValue' => $clientParty->Status,
            'NewValue' => $level,
            'CreatedBy' => $user->ID,
            'CreatedOn' => Carbon::now(),
            'LastModifiedBy' => $user->ID,
            'LastModifiedOn' => Carbon::now()
        ];

        EntityHistory::create($history);

        try {
            $userEmail = User::where('LoginID', $clientParty->CreatedBy)->select('Email')->first();
            Mail::to($userEmail->Email)->send(new RejectEntity($clientParty));
        } catch (Exception $th) {
            alert()->warning('Entity Notify Email', $th);
            return response()->json(['false', 500]);
        }

        try {
            DB::table('Cust_entity')->where('Id', $id)->update([
                'RejectedBy' => Auth::user()->full_name,
                'RejectedOn' => date('Y-m-d H:i:s'),
                'RejectedReason' => $reason,
                'Status' => $level,
            ]);
        } catch (Exception $e) {
            alert()->warning('Entity', $e);
            return response()->json(['false', 500]);
        }

        alert()->success('Entity', 'Entity Rejected at ' . $level)->showConfirmButton();
        return response()->json(['ok', 200]);
    }



    public function approve(HttpRequest $request)
    {
        Log::debug('Approve method called', ['request' => $request->all()]);

        $user = Auth::user();

        if (!$user || !$user->can('approve-entity-request')) {
            Log::warning('Unauthorized approval attempt', ['userId' => $user?->Id]);
            Alert::warning('Warning!', 'You are not authorised to view this page. Contact Admin')->showConfirmButton();
            return redirect()->route('home');
        }

        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:Cust_entity,Id',
            'status' => 'required|in:Submitted,PendingCountryLevelApproval,PendingGroupLevelApproval,PendingITVerification,Created',
            'type' => 'required|in:accounting,operational',
            'otherCheck' => 'nullable|boolean',
            'otherInput' => 'nullable|string|max:255',
            'otherID' => 'nullable|string|max:36',
            'otherCode' => 'nullable|string|max:50',
            'partyEntityType' => 'nullable|string|max:50',
            'debtorType' => 'nullable|string|max:50',
            'vendorType' => 'nullable|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation failed', 'errors' => $validator->errors()], 422);
        }

        $validated = $validator->validated();
        $id = $validated['id'];

        try {
            $clientParty = ClientParty::where('Id', $id)->firstOrFail();
            Log::info('Current ClientParty status and OtherParty fields', [
                'id' => $id,
                'status' => $clientParty->Status,
                'OtherPartyName' => $clientParty->OtherPartyName,
                'OtherPartyID' => $clientParty->OtherPartyID,
                'OtherPartyCode' => $clientParty->OtherPartyCode,
            ]);

            DB::beginTransaction();

            $isAccounting = $validated['type'] === 'accounting';
            $previousStatus = $clientParty->Status;

            $updateData = [
                'LastModifiedBy' => $user->full_name ?? 'System',
                'LastModifiedOn' => Carbon::now()->format('Y-m-d H:i:s'),
                'RequestforOtherParty' => $validated['otherCheck'] ?? false,
                'OtherPartyName' => $validated['otherInput'] ?? $clientParty->OtherPartyName,
                'OtherPartyID' => $validated['otherID'] ?? $clientParty->OtherPartyID,
                'OtherPartyCode' => $validated['otherCode'] ?? $clientParty->OtherPartyCode,
                'OperationalEntityType' => $validated['partyEntityType'] ?? $clientParty->OperationalEntityType,
                'DebtorType' => $validated['debtorType'] ?? $clientParty->DebtorType,
                'VendorType' => $validated['vendorType'] ?? $clientParty->VendorType,
            ];

            Log::debug('Update query data', ['id' => $id, 'updateData' => $updateData]);

            // FIXED STATUS TRANSITION LOGIC - Added PendingITVerification step
            if ($isAccounting && $clientParty->Status === 'Not Submitted' && $validated['status'] === 'PendingCountryLevelApproval') {
                $updateData['Status'] = 'PendingCountryLevelApproval';
                $updatedRows = DB::table('Cust_entity')->where('Id', $id)->update($updateData);
                Log::info('Updated to PendingCountryLevelApproval', ['id' => $id, 'updatedRows' => $updatedRows]);

                $dbStatus = DB::table('Cust_entity')->where('Id', $id)->value('Status');
                if ($dbStatus !== 'PendingCountryLevelApproval') {
                    Log::error('Status update failed', ['id' => $id, 'expected' => 'PendingCountryLevelApproval', 'actual' => $dbStatus]);
                    DB::rollBack();
                    return response()->json(['message' => 'Failed to update status'], 500);
                }
            } elseif ($isAccounting && $clientParty->Status === 'PendingCountryLevelApproval' && $validated['status'] === 'PendingGroupLevelApproval') {
                $updateData['Status'] = 'PendingGroupLevelApproval';
                $updatedRows = DB::table('Cust_entity')->where('Id', $id)->update($updateData);
                Log::info('Updated to PendingGroupLevelApproval', ['id' => $id, 'updatedRows' => $updatedRows]);

                $dbStatus = DB::table('Cust_entity')->where('Id', $id)->value('Status');
                if ($dbStatus !== 'PendingGroupLevelApproval') {
                    Log::error('Status update failed', ['id' => $id, 'expected' => 'PendingGroupLevelApproval', 'actual' => $dbStatus]);
                    DB::rollBack();
                    return response()->json(['message' => 'Failed to update status'], 500);
                }
            }
            // NEW: Handle PendingGroupLevelApproval to PendingITVerification transition
            elseif ($isAccounting && $clientParty->Status === 'PendingGroupLevelApproval' && $validated['status'] === 'PendingITVerification') {
                $updateData['Status'] = 'PendingITVerification';
                $updatedRows = DB::table('Cust_entity')->where('Id', $id)->update($updateData);
                Log::info('Updated to PendingITVerification', ['id' => $id, 'updatedRows' => $updatedRows]);

                $dbStatus = DB::table('Cust_entity')->where('Id', $id)->value('Status');
                if ($dbStatus !== 'PendingITVerification') {
                    Log::error('Status update failed', ['id' => $id, 'expected' => 'PendingITVerification', 'actual' => $dbStatus]);
                    DB::rollBack();
                    return response()->json(['message' => 'Failed to update status to PendingITVerification'], 500);
                }
            }
            // MODIFIED: Handle PendingITVerification to Created transition
            elseif ($isAccounting && $clientParty->Status === 'PendingITVerification' && $validated['status'] === 'Created') {
                $scmRequest = new HttpRequest(['requestID' => $id]);
                $results = $this->CreateClientPartySCM($scmRequest);
                Log::info('SCM Creation results during Approve:', (array)$results);

                if ($results['success'] && $results['data']['Status'] === 'Success') {
                    // Update OtherParty fields with SP output
                    $updateData['OtherPartyCode'] = $results['data']['OtherCode'] ?? $updateData['OtherPartyCode'];
                    $updateData['OtherPartyID'] = $results['data']['OtherID'] ?? $updateData['OtherPartyID'];
                    $updateData['Status'] = 'Created';
                    $updatedRows = DB::table('Cust_entity')->where('Id', $id)->update($updateData);
                    Log::info('Updated to Created', ['id' => $id, 'updatedRows' => $updatedRows]);

                    $dbStatus = DB::table('Cust_entity')->where('Id', $id)->value('Status');
                    if ($dbStatus !== 'Created') {
                        Log::error('Status update failed', ['id' => $id, 'expected' => 'Created', 'actual' => $dbStatus]);
                        DB::rollBack();
                        return response()->json(['message' => 'Failed to update status to Created'], 500);
                    }
                } else {
                    Log::error('SCM Creation Failed', ['id' => $id, 'remark' => $results['data']['Remark'] ?? $results['error'] ?? 'Unknown error']);
                    DB::rollBack();
                    return response()->json(['message' => 'SCM Creation Failed: ' . ($results['data']['Remark'] ?? $results['error'] ?? 'Unknown error')], 500);
                }
            } elseif (!$isAccounting && $clientParty->Status === 'Not Submitted' && $validated['status'] === 'Submitted') {
                $updateData['Status'] = 'Submitted';
                $updatedRows = DB::table('Cust_entity')->where('Id', $id)->update($updateData);
                Log::info('Updated to Submitted', ['id' => $id, 'updatedRows' => $updatedRows]);

                $dbStatus = DB::table('Cust_Entity')->where('Id', $id)->value('Status');
                if ($dbStatus !== 'Submitted') {
                    Log::error('Status update failed', ['id' => $id, 'expected' => 'Submitted', 'actual' => $dbStatus]);
                    DB::rollBack();
                    return response()->json(['message' => 'Failed to update status to Submitted'], 500);
                }
            } elseif (!$isAccounting && $clientParty->Status === 'Submitted' && $validated['status'] === 'Created') {
                // Prepare OtherParty fields
                $updateData['OtherPartyCode'] = $validated['otherCode'] ?? $updateData['OtherPartyCode'] ?? 'OTHER-' . Str::random(6);
                $updateData['OtherPartyID'] = $validated['otherID'] ?? $updateData['OtherPartyID'] ?? Str::uuid()->toString();
                $updateData['RequestforOtherParty'] = $validated['otherCheck'] ?? $updateData['RequestforOtherParty'];

                // Update Cust_entity before SCM call
                $updatedRows = DB::table('Cust_Entity')->where('Id', $id)->update($updateData);
                Log::info('Pre-SCM Update', ['id' => $id, 'updatedRows' => $updatedRows]);

                // Call SCM
                $scmRequest = new HttpRequest(['requestID' => $id]);
                $results = $this->CreateClientPartySCM($scmRequest);
                Log::info('SCM Creation results during Approve:', (array)$results);

                if ($results['success'] && $results['data']['Status'] === 'Success') {
                    // Update with SCM-generated OtherCode, skip 'Not Required'
                    $updateData['OtherPartyCode'] = ($results['data']['OtherCode'] !== 'Not Required')
                        ? $results['data']['OtherCode']
                        : $updateData['OtherPartyCode'];
                    $updateData['OtherPartyID'] = $results['data']['OtherID'] ?? $updateData['OtherPartyID'];
                    $updateData['Status'] = 'Created';
                    $updatedRows = DB::table('Cust_entity')->where('Id', $id)->update($updateData);
                    Log::info('Updated to Created', ['id' => $id, 'updatedRows' => $updatedRows]);

                    $dbStatus = DB::table('Cust_entity')->where('Id', $id)->value('Status');
                    if ($dbStatus !== 'Created') {
                        Log::error('Status update failed', ['id' => $id, 'expected' => 'Created', 'actual' => $dbStatus]);
                        DB::rollBack();
                        return response()->json(['message' => 'Failed to update status to Created'], 500);
                    }
                } else {
                    Log::error('SCM Creation Failed', ['id' => $id, 'remark' => $results['data']['Remark'] ?? $results['error'] ?? 'Unknown error']);
                    DB::rollBack();
                    return response()->json(['message' => 'SCM Creation Failed: ' . ($results['data']['Remark'] ?? $results['error'] ?? 'Unknown error')], 500);
                }
            } else {
                Log::warning('Invalid status transition', [
                    'id' => $id,
                    'currentStatus' => $clientParty->Status,
                    'requestedStatus' => $validated['status'],
                    'isAccounting' => $isAccounting
                ]);
                return response()->json(['message' => "Invalid status transition from {$clientParty->Status} to {$validated['status']}"], 422);
            }

            Log::info('OtherParty fields saved to database', [
                'id' => $id,
                'OtherPartyName' => $updateData['OtherPartyName'],
                'OtherPartyID' => $updateData['OtherPartyID'],
                'OtherPartyCode' => $updateData['OtherPartyCode'],
            ]);

            DB::commit();

            // Create history record
            $history = [
                'ID' => Str::uuid()->toString(),
                'CustEntityID' => $id,
                'RequestNo' => $clientParty->Code ?? 'N/A',
                'Role' => $user->Role ?? 'Unknown',
                'ReasonID' => 'e6b21257-a530-45ff-bf56-939e53a536b5',
                'Comment' => $this->getApprovalComment($isAccounting, $validated['status']),
                'Type' => 'Entity',
                'PreviousValue' => $previousStatus,
                'NewValue' => $validated['status'],
                'CreatedBy' => $user->Id ?? 'Unknown',
                'CreatedOn' => Carbon::now(),
                'LastModifiedBy' => $user->Id ?? 'Unknown',
                'LastModifiedOn' => Carbon::now(),
            ];

            try {
                EntityHistory::create($history);
                Log::info('EntityHistory logged', ['id' => $id, 'history' => $history]);
            } catch (\Exception $e) {
                Log::error('Failed to log EntityHistory', ['id' => $id, 'error' => $e->getMessage()]);
            }

            return response()->json([
                'status' => 200,
                'message' => $this->getApprovalMessage($isAccounting, $validated['status']),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Approval Exception', ['id' => $id, 'error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['message' => 'Approval failed: ' . $e->getMessage()], 500);
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
                    'phoneNo' => property_exists($item, 'Cont_Bch_PhoneNumbers') && $item->Cont_Bch_PhoneNumbers !== '-' ? $item->Cont_Bch_PhoneNumbers : null,
                    'contractEmail' => property_exists($item, 'Cont_Bch_Email') && $item->Cont_Bch_Email !== '-' ? $item->Cont_Bch_Email : null,
                    'contactId' => property_exists($item, 'ContactId') ? $item->ContactId : null,
                    'addressType' => property_exists($item, 'Cont_Bch_PhoneNumbers') && $item->Cont_Bch_PhoneNumbers ? $item->Cont_Bch_PhoneNumbers : null,
                    'firstName' => property_exists($item, 'Cont_Bch_PersonName_FirstName') ? $item->Cont_Bch_PersonName_FirstName : null, // Pass '-' through
                    'lastName' => property_exists($item, 'Cont_Bch_PersonName_LastName') ? $item->Cont_Bch_PersonName_LastName : null, // Pass '-' through
                    'department' => property_exists($item, 'Cont_Bch_Dept') && $item->Cont_Bch_Dept !== '-' ? $item->Cont_Bch_Dept : null
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
}
