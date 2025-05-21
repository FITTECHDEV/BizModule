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
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\QueryException;
use JeroenNoten\LaravelAdminLte\Components\Form\Select;
use Ramsey\Uuid\Guid\Guid;
use RealRashid\SweetAlert\Facades\Alert;
use PDF;

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
        $results = ClientParty::where('OperatioEntityType', "Client without Debtor or Vendor")
            ->where('Name', 'LIKE', "%{$search}%")->pluck('Name');
        return response()->json($results);
    }

    public function fetchClientDetails($companyName)
    {
        $client = ClientParty::where('OperatioEntityType', "Client without Debtor or Vendor")->where('Name', $companyName)->first();
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
                ->where('OperatioEntityType', "Client + Shipper + Consignee")
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

        // Log form data
        Log::info('Store Request Data:', $request->all());
        Log::info('createBtn:', [isset($request->createBtn)]);
        Log::info('Checkboxes:', [
            'clientCheck' => isset($request->clientCheck),
            'shipperCheck' => isset($request->shipperCheck),
            'consigneeCheck' => isset($request->consigneeCheck),
            'otherCheck' => isset($request->otherCheck),
            'debtorCheck' => isset($request->debtorCheck),
            'vendorCheck' => isset($request->vendorCheck)
        ]);

        // Initialize variables
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

        // Log addressLine1 and contact details
        if ($addressLine1 === '') {
            Log::warning('AddressLine1 is empty from front-end.', ['request' => $request->all()]);
        } else {
            Log::info('AddressLine1 provided by front-end:', ['addressLine1' => $addressLine1]);
        }
        Log::info('Contact details from front-end:', [
            'firstName' => $firstName,
            'lastName' => $lastName,
            'phoneNo' => $phoneNo,
            'contractEmail' => $contractEmail
        ]);

        // Fallback to sp_EntityAddressDetails for address and contact details
        if (($addressLine1 === '' || $firstName === '' || $lastName === '' || $phoneNo === '' || $contractEmail === '')
            && $request->partyEntityType === 'Client' && $request->companyId
        ) {
            Log::info('Attempting fallback to sp_EntityAddressDetails for address and contact details:', [
                'companyId' => $request->companyId,
                'partyType' => $request->partyEntityType
            ]);
            try {
                $companyDetails = DB::select(
                    'SET NOCOUNT ON;
                 EXEC [FITSYSUAT].[dbo].[sp_EntityAddressDetails] @PartyType = ?, @PartyID = ?',
                    [$request->partyEntityType, $request->companyId]
                );
                if (!empty($companyDetails) && isset($companyDetails[0])) {
                    Log::info('Fetched details from sp_EntityAddressDetails:', (array)$companyDetails[0]);
                    $addressLine1 = $addressLine1 ?: ($companyDetails[0]->AddressLine1 ?? 'Nairobi');
                    $addressLine2 = $addressLine2 ?: ($companyDetails[0]->AddressLine2 ?? '');
                    $firstName = $firstName ?: ($companyDetails[0]->Cont_Bch_PersonName_FirstName ?? '');
                    $lastName = $lastName ?: ($companyDetails[0]->Cont_Bch_PersonName_LastName ?? '');
                    $phoneNo = $phoneNo ?: ($companyDetails[0]->Cont_Bch_PhoneNumbers ?? '');
                    $contractEmail = $contractEmail ?: ($companyDetails[0]->Cont_Bch_Email ?? '');
                } else {
                    Log::warning('No valid data found in sp_EntityAddressDetails for companyId: ' . $request->companyId);
                    $addressLine1 = $addressLine1 ?: 'Nairobi';
                    $firstName = $firstName ?: '';
                    $lastName = $lastName ?: '';
                    $phoneNo = $phoneNo ?: '';
                    $contractEmail = $contractEmail ?: '';
                }
            } catch (\Illuminate\Database\QueryException $e) {
                Log::error('Failed to query sp_EntityAddressDetails for companyId: ' . $request->companyId . ', Error: ' . $e->getMessage());
                $addressLine1 = $addressLine1 ?: 'Nairobi';
                $firstName = $firstName ?: '';
                $lastName = $lastName ?: '';
                $phoneNo = $phoneNo ?: '';
                $contractEmail = $contractEmail ?: '';
            }
        } elseif ($addressLine1 === '') {
            $addressLine1 = 'Nairobi';
            Log::info('Using default addressLine1: Nairobi', ['partyEntityType' => $request->partyEntityType, 'companyId' => $request->companyId]);
        }

        // Single license check for legalEntityType
        if ($licenseNo && $partyType && $officeCode && !in_array($request->partyEntityType, ['NoSCMIntergration', 'Create only in D365'])) {
            $partyTypes = explode(' & ', $partyType);
            $partyTypeMap = [
                'debtor' => 'Debtor',
                'vendor' => 'Vendor'
            ];

            foreach ($partyTypes as $singlePartyType) {
                $mappedPartyType = $partyTypeMap[strtolower($singlePartyType)] ?? $singlePartyType;

                Log::info('Checking license for:', [
                    'partyType' => $mappedPartyType,
                    'licenseNo' => $licenseNo,
                    'officeCode' => $officeCode
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
                            'remark' => $results[0]->Remark
                        ]);
                        Alert::error('Error', 'License number is already in use: ' . $results[0]->Remark)->persistent(true, false);
                        return redirect()->back()->withInput();
                    }
                } catch (\Illuminate\Database\QueryException $e) {
                    Log::error('License check failed:', [
                        'partyType' => $mappedPartyType,
                        'licenseNo' => $licenseNo,
                        'officeCode' => $officeCode,
                        'error' => $e->getMessage()
                    ]);
                    Alert::error('Error', 'License check failed: ' . $e->getMessage())->persistent(true, false);
                    return redirect()->back()->withInput();
                }
            }
        } else {
            Log::info('Skipping license check:', [
                'partyType' => $partyType,
                'licenseNo' => $licenseNo,
                'officeCode' => $officeCode,
                'partyEntityType' => $request->partyEntityType
            ]);
        }

        // Fallback for countryCode if null
        if (!$countryCode) {
            if (!$request->companyId || !$request->partyEntityType) {
                Log::error('Missing companyId or partyEntityType for countryCode fallback.', ['request' => $request->all()]);
                Alert::error('Error', 'Please provide a valid company and entity type.')->persistent(true, false);
                return redirect()->back()->withInput();
            }

            Log::info('Calling sp_EntityAddressDetails for countryCode fallback:', [
                'PartyType' => $request->partyEntityType,
                'PartyID' => $request->companyId
            ]);

            $companyDetails = DB::select(
                'SET NOCOUNT ON;
             EXEC [FITSYSUAT].[dbo].[sp_EntityAddressDetails] @PartyType = ?, @PartyID = ?',
                [$request->partyEntityType, $request->companyId]
            );

            if (!empty($companyDetails) && isset($companyDetails[0]->CountryCode)) {
                Log::info('sp_EntityAddressDetails raw response:', (array)$companyDetails[0]);
                $countryCode = $countryCode ?? ($companyDetails[0]->CountryCode ?? null);
            } else {
                Log::warning('sp_EntityAddressDetails returned no or invalid results:', [
                    'PartyType' => $request->partyEntityType,
                    'PartyID' => $request->companyId,
                    'result' => $companyDetails
                ]);
            }
        }

        // Validate required fields
        if (!$countryCode) {
            Log::error('CountryCode is required but could not be fetched.', ['request' => $request->all()]);
            Alert::error('Error', 'Please select a valid country.')->persistent(true, false);
            return redirect()->back()->withInput();
        }

        // Log final values
        Log::info('Final values used:', [
            'addressLine1' => $addressLine1,
            'firstName' => $firstName,
            'lastName' => $lastName,
            'phoneNo' => $phoneNo,
            'contractEmail' => $contractEmail
        ]);

        // Determine status
        $clientName = $request->client ?? $request->clientInput;
        $status = 'Not Submitted';

        if (isset($request->submitBtn) || isset($request->createBtn)) {
            $status = 'Submitted';
        }
        if ((isset($request->submitBtn) || isset($request->createBtn)) &&
            (isset($request->debtor) || isset($request->debtorCheck) ||
                isset($request->vendor) || isset($request->vendorCheck))
        ) {
            $status = 'PendingManagerApproval';
        }

        $clientParty = new ClientParty();
        $code = $clientParty->generateUniqueCode($user->OfficeID);
        $client_party_id = guid();

        try {
            DB::beginTransaction();
            $data = [
                'Id' => $client_party_id,
                'Code' => $code,
                'OperatioEntityType' => $request->partyEntityType,
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

            DB::table('Cust_Entity')->updateOrInsert($data);
            DB::commit();
        } catch (Exception $th) {
            DB::rollback();
            Log::error('Store Failed: ' . $th->getMessage());
            Alert::alert('Error', $th->getMessage(), 'error');
            return redirect()->route('client-party.index');
        }

        // Handle file attachments
        if ($request->hasFile('attachmentNames')) {
            foreach ($request->attachmentNames as $key => $attachment) {
                if (!$attachment) continue; // Skip null attachments
                $fileName = $attachment->getClientOriginalName();
                $newName = $code . '_' . pathinfo($fileName, PATHINFO_FILENAME) . '.' . $attachment->getClientOriginalExtension();
                try {
                    $request->attachmentNames[$key]->move(storage_path('client-party'), $newName);
                    $local_storage_path = storage_path() . '/client-party/' . $newName;
                    $storage_path = 'Kenya/ClientParty/' . $newName;
                    Storage::disk('azure')->put($storage_path, file_get_contents($local_storage_path));
                    DB::beginTransaction();
                    DB::table('Cust_EntityAttachment')->updateOrInsert([
                        'ID' => guid(),
                        'CustEntityID' => $client_party_id,
                        'RequestNo' => $code,
                        'Title' => $request->attachmentDescription[$key] ?? 'Attachment',
                        'FileName' => $newName,
                        'FilePath' => $storage_path,
                        'CreatedBy' => $user->ID,
                        'CreatedOn' => Carbon::now(),
                        'LastModifiedBy' => $user->ID,
                        'LastModifiedOn' => Carbon::now(),
                    ]);
                    DB::commit();
                    File::delete($local_storage_path);
                } catch (Exception $ex) {
                    Log::warning('Attachment Upload Failed: ' . $ex->getMessage());
                    DB::rollBack();
                }
            }
        }

        // Record history
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

        $client_party_status = 0;

        // Legal entity creation and SCM integration only for Submitted status
        if ($status === 'Submitted' && isset($request->createBtn)) {
            // Handle debtor or vendor legal entity creation
            if (isset($request->debtorCheck) || isset($request->vendorCheck)) {
                $entity_types = (strtolower($request->legalEntityType) == 'vendor') ? ['Vendor'] : ((strtolower($request->legalEntityType) == 'debtor') ? ['Customer'] : ['Vendor', 'Customer']);
                foreach ($entity_types as $entity_type) {
                    if ($request->vendorCode != null || $request->debtorCode != null) {
                        continue;
                    }
                    $noSeries = $this->getOfficeNumberSeries($request->officeCode, ($entity_type == 'Customer') ? 'Debtor' : $entity_type);
                    $data = [
                        "office" => ($request->officeCode == 'UPS') ? 'KE' : $request->officeCode,
                        "partyType" => (strcmp(strtolower(App::environment()), 'production') == 0) ? $entity_type : 'TST',
                        "no" => "",
                        "name" => $request->companyName,
                        "postingGroup" => $entity_type == 'Vendor' ? $request->vendorType : $request->debtorType,
                        "vatRegistrationNo" => $request->licenceTypeValue ?? '-',
                        "postCode" => $request->zipCode ?? "",
                        "address" => $addressLine1,
                        "address2" => $addressLine2 ?? "",
                        "city" => $request->cityName ?? "",
                        "countryCode" => $countryCode,
                        "emailAddress" => $contractEmail ?? "",
                        "phoneNo" => $phoneNo ?? "",
                        "phoneNo2" => "",
                        "billingCurrency" => $request->billingCurrencyCode,
                        "paymentTermsCode" => (string)($request->creditDays) == "" ? "0" : (string)($request->creditDays),
                        "creditLimitLCY" => intval($request->maxCreditLimit ?? 0),
                        "contactPersonName" => ($firstName && $lastName) ? "$firstName $lastName" : "",
                        "contactPersonEmailAddress" => $contractEmail ?? "",
                        "contactPersonPhoneNo" => $phoneNo ?? "",
                        "contactPersonPhoneNo2" => "",
                        "relatedParty" => "",
                        "blocked" => false,
                        "NoSeries" => $noSeries
                    ];

                    if (config('app.env') != 'Local') {
                        $entity = $this->createLegalEntityType($entity_type, $data);
                        Log::info('Legal Entity Data:', $data);
                        Log::info('Legal Entity Response:', (array)$entity);
                        $statusCode = $entity->status ?? ($entity->Status ?? null);
                        $errors = $entity->errors ?? ($entity->Remark ?? null);

                        if ($statusCode !== true) {
                            $client_party_status = 0;
                            Log::warning('Legal Entity Creation Failed:', ['response' => (array)$entity, 'errors' => $errors]);
                            $errorMessage = $errors ? json_encode($errors) : 'Failed to create legal entity.';
                            Alert::warning('Error', $errorMessage)->persistent(true, false);
                            return redirect()->route('client-party.index');
                        }

                        $entity_type_col = (strtolower($entity_type) == 'vendor') ? 'Vendor' : 'Debtor';
                        DB::table('Cust_Entity')->where('Id', $client_party_id)->update([
                            ucfirst($entity_type_col) . 'Code' => $entity->responseCode ?? $entity->Code ?? null,
                        ]);
                        $client_party_status = 1;
                    } else {
                        $client_party_status = 1;
                    }
                }
            }

            // Set client_party_status for operational entities
            if (
                isset($request->clientCheck) || isset($request->shipperCheck) ||
                isset($request->consigneeCheck) || isset($request->otherCheck)
            ) {
                $client_party_status = 1;
            }

            // SCM integration
            if ($client_party_status == 1) {
                $results = [];
                if ($request->partyEntityType != 'NoSCMIntergration' && $request->partyEntityType != 'Create only in D365') {
                    $results = $this->CreateClientPartySCM($client_party_id);
                } else {
                    DB::table('Cust_Entity')->where('Id', $client_party_id)->update(['Status' => 'Created']);
                    $results = json_decode(json_encode([['Status' => 'Success', 'Remark' => 'Debtor / Vendor Created In D365']]), FALSE);
                }

                Log::info('SCM Creation Results:', (array)$results);
                if ($results[0]->Status != 'Failed') {
                    alert()->success($results[0]->Status, $results[0]->Remark)->showConfirmButton();
                } else {
                    alert()->error($results[0]->Status, $results[0]->Remark)->showConfirmButton();
                }
            }
        } else {
            alert()->success('Success !', 'Entity Saved.')->showConfirmButton();
        }

        return redirect()->route('client-party.index');
    }


    // Get Office Number

    public function getOfficeNumberSeries($officeCode = null, $entityType = null)
    {
        $numSeries = DB::table('Cust_OfficeSeriesMapping')
            ->where('EntityType', $entityType)
            ->where('OfficeCode', trim($officeCode))
            ->pluck('SeriesNo');

        return $numSeries[0];
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
        $response = Http::withToken($entityToken['token'])
            ->post($request_url, $post_data);

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


    //  1
    // public function show(ClientParty $clientParty)
    // {
    //     $user = request()->user();

    //     if (!$user->can('view-client-party')) {
    //         abort(403, "You are not allowed to view this page.");
    //     }

    //     $type = $clientParty->LegalEntityType ? 'accounting' : 'operational';
    //     $approve = false;
    //     $editForm = true;

    //     if (($clientParty->Status == 'PendingManagerApproval') && !$user->can('approve-my-office-entity-request')) {
    //         alert()->warning('Warning !', 'Entity Pending for Manager Approval')->showConfirmButton();
    //         return redirect()->back();
    //     }

    //     if ($clientParty->Status == 'Created') {
    //         $editForm = false;
    //     }

    //     if ((Auth::user()->Role != 1 && ($clientParty->Status == 'Submitted')) && !Auth::user()->can('create-client-party')) {
    //         $editForm = false;
    //     }

    //     if (Auth::user()->Role == 1 && ($clientParty->Status != 'Created')) {
    //         $editForm = true;
    //     }

    //     if (url()->previous() == url('client-party/approve') || ($clientParty->Status == 'PendingManagerApproval')) {
    //         $editForm = false;
    //         $approve = true;
    //     }

    //     $countries = $this->getCountries();
    //     $offices = $this->getOffices();
    //     $currencyList = $this->getCurrencyList($clientParty->OfficeID) ?: ['does not exist'];

    //     if ($clientParty->OfficeID && str_contains(json_encode($currencyList[0]), 'does not exist')) {
    //         $scm_office_id = DB::select(DB::raw("SELECT SCMOfficeID FROM Cust_Office WHERE ID = ?", [$clientParty->OfficeID]));
    //         $currencyList = $this->getCurrencyList($scm_office_id[0]->SCMOfficeID ?? null);
    //     } else {
    //         $idX = Auth::user()->office->SCMOfficeID ?? null;
    //         $currencyList = $this->getCurrencyList($idX);
    //     }

    //     $licenceTypes = $this->getLicenceType();
    //     $tarrifs = ($clientParty->Office == "FIT - Kenya - UPS") ? $this->getTarrif($clientParty->OfficeID) : [];
    //     $incoterms = $this->getIncoTerms();
    //     $payTerms = $this->getPaymentTermList();

    //     return view('client-party.show', compact(
    //         'approve',
    //         'editForm',
    //         'payTerms',
    //         'incoterms',
    //         'clientParty',
    //         'countries',
    //         'offices',
    //         'currencyList',
    //         'licenceTypes',
    //         'tarrifs',
    //         'type'
    //     ));
    // }


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

        if (($clientParty->Status == 'PendingManagerApproval') && !$user->can('approve-my-office-entity-request')) {
            alert()->warning('Warning !', 'Entity Pending for Manager Approval')->showConfirmButton();
            return redirect()->back();
        }

        if ($clientParty->Status == 'Created') {
            $editForm = false;
        }

        if ((Auth::user()->Role != 1 && ($clientParty->Status == 'Submitted')) && !Auth::user()->can('create-client-party')) {
            $editForm = false;
        }

        if (Auth::user()->Role == 1 && ($clientParty->Status != 'Created')) {
            $editForm = true;
        }

        if (url()->previous() == url('client-party/approve') || ($clientParty->Status == 'PendingManagerApproval')) {
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
                'operatioEntityType' => $clientParty->OperatioEntityType
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
    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateClientPartyRequest  $request
     * @param  \App\Models\ClientParty  $clientParty
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateClientPartyRequest $request, $id)
    {
        $user = Auth::user();
        $cp = ClientParty::find($id);

        if (!$user->can('update-client-party')) {
            alert()->warning('Warning !', 'Unauthorised.')->showConfirmButton();
            return redirect()->back();
        }

        // Log form data for debugging
        Log::info('Update Request Data:', $request->all());
        Log::info('createBtn:', [isset($request->createBtn)]);
        Log::info('Checkboxes:', [
            'clientCheck' => isset($request->clientCheck),
            'shipperCheck' => isset($request->shipperCheck),
            'consigneeCheck' => isset($request->consigneeCheck),
            'otherCheck' => isset($request->otherCheck),
            'debtorCheck' => isset($request->debtorCheck),
            'vendorCheck' => isset($request->vendorCheck)
        ]);

        $clientName = isset($request->client) ? $request->client : $request->clientInput;
        $status = $cp->Status;

        if (isset($request->submitBtn)) {
            $status = 'Submitted';
        } elseif (auth()->user()->Role != 1) {
            $status = 'Not Submitted';
        }

        if ($user->can('approve-my-office-entity-request') && $user->can('approve-all-entity-request') && isset($request->createBtn)) {
            $status = 'Submitted';
        }

        try {
            DB::beginTransaction();
            DB::table('Cust_Entity')->where('ID', $id)->update([
                'OperatioEntityType' => $request->partyEntityType,
                'Name' => $request->companyName,
                'Address_Line1' => $request->addressLine1,
                'Address_Line2' => $request->addressLine2,
                'Address_CountryId' => $request->countryID != '' ? $request->countryID : null,
                'Address_CountryName' => $request->countryName,
                'Address_CountryCode' => $request->countryCode,
                'Address_State' => $request->provinceName,
                'Address_StateId' => $request->provinceID != '' ? $request->provinceID : null,
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
                'OfficeCode' => isset($request->officeCode) ? $request->officeCode : $request->officeCountryDisp,
                'OfficeID' => $request->officeCountryID,
                'DefaultNotifyPartyName' => $request->notifyParty1,
                'DefaultNotifyPartyId' => $request->notifyParty1ID != '' ? $request->notifyParty1ID : null,
                'DefaultNotifyPartyCode' => $request->notifyParty1Code,
                'AccountMgrOfficeFunMapName' => $request->accountPIC,
                'AccountMgrOfficeFunMapId' => $request->accountPICID != '' ? $request->accountPICID : null,
                'CreditDays' => $request->creditDays,
                'CreditLimitCurrencyId' => $request->billingCurrency != '' ? $request->billingCurrency : null,
                'CreditLimitCurrencyCode' => $request->billingCurrencyCode,
                'BillingCurrencyCode' => $request->billingCurrencyCode,
                'BillingCurrencyId' => $request->billingCurrency != '' ? $request->billingCurrency : null,
                'BaseCurrencyName' => $request->baseCurrencyName,
                'BaseCurrencyCode' => $request->baseCurrencyCode,
                'BaseCurrencyId' => $request->baseCurrencyID != '' ? $request->baseCurrencyID : null,
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
                'ClientID' => $request->clientID != '' ? $request->clientID : null,
                'ClientCode' => $request->clientCode,
                'RequestId' => $request->requestID != '' ? $request->requestID : null,
                'RequestName' => $request->clientOptionVal ?? null,
                'RequestCode' => $request->requestCode ?? null,
                'ShipperConsigneeName' => $request->shipperInput,
                'ShipperConsigneeID' => $request->shipperID != '' ? $request->shipperID : null,
                'ShipperConsigneeCode' => $request->shipperCode,
                'OtherPartyName' => $request->otherInput,
                'OtherPartyID' => $request->otherID != '' ? $request->otherID : null,
                'OtherPartyCode' => $request->otherCode,
                'Debtor' => $request->debtor,
                'DebtorID' => $request->debtorID != '' ? $request->debtorID : null,
                'DebtorCode' => $request->debtorCode,
                'Vendor' => $request->vendor,
                'VendorID' => $request->vendorID != '' ? $request->vendorID : null,
                'VendorCode' => $request->vendorCode,
                'RequestforClient' => isset($request->clientCheck) ? 1 : 0,
                'RequestforShipperConsignee' => isset($request->consigneeCheck) ? 1 : (isset($request->shipperCheck) ? 1 : 0),
                'RequestforOtherParty' => isset($request->otherCheck) ? 1 : 0,
                'RequestforDebtor' => isset($request->debtorCheck) ? 1 : 0,
                'RequestforVendor' => isset($request->vendorCheck) ? 1 : 0,
                'LastModifiedOn' => Carbon::now()->format('Y-m-d H:i:s'),
                'LastModifiedBy' => $user->full_name,
                'Status' => $status
            ]);
            DB::commit();
        } catch (Exception $th) {
            Log::error('Update Failed: ' . $th->getMessage());
            DB::rollBack();
            alert()->error('Error !', 'Entity Not Updated.')->showConfirmButton();
            return redirect()->route('client-party.show', [$id]);
        }

        if ($request->hasFile('attachmentNames')) {
            foreach ($request->attachmentNames as $key => $attachment) {
                $fileName = $attachment->getClientOriginalName();
                $newName = $cp->Code . '_' . pathinfo($fileName, PATHINFO_FILENAME) . '.' . $attachment->getClientOriginalExtension();
                try {
                    $request->attachmentNames[$key]->move(storage_path('client-party'), $newName);
                    $local_storage_path = storage_path() . '/client-party/' . $newName;
                    $storage_path = 'Kenya/ClientParty/' . $newName;
                    Storage::disk('azure')->put($storage_path, file_get_contents($local_storage_path));
                    DB::beginTransaction();
                    DB::table('Cust_EntityAttachment')->updateOrInsert([
                        'ID' => guid(),
                        'CustEntityID' => $id,
                        'RequestNo' => $cp->Code,
                        'Title' => $request->attachmentDescription[$key],
                        'FileName' => $newName,
                        'FilePath' => $storage_path,
                        'CreatedBy' => $user->ID,
                        'CreatedOn' => Carbon::now(),
                        'LastModifiedBy' => $user->ID,
                        'LastModifiedOn' => Carbon::now(),
                    ]);
                    DB::commit();
                    File::delete($local_storage_path);
                } catch (Exception $ex) {
                    Log::warning('Attachment Upload Failed: ' . $ex->getMessage());
                    DB::rollBack();
                }
            }
        }

        $history = [
            'ID' => guid(),
            'CustEntityID' => $id,
            'RequestNo' => $cp->Code,
            'Role' => $user->Role,
            'ReasonID' => '84d62970-a1ce-47d3-9428-36e4647811f9',
            'Comment' => 'Entity Updated',
            'Type' => 'Entity',
            'PreviousValue' => $cp->Status,
            'NewValue' => $status,
            'CreatedBy' => $user->ID,
            'CreatedOn' => Carbon::now(),
            'LastModifiedBy' => $user->ID,
            'LastModifiedOn' => Carbon::now()
        ];
        EntityHistory::create($history);

        $client_party_status = 0;

        // Legal entity creation and SCM integration only for Submitted status
        if ($status === 'Submitted' && isset($request->createBtn)) {
            // Handle debtor or vendor legal entity creation
            if (isset($request->debtorCheck) || isset($request->vendorCheck)) {
                $entity_types = (strtolower($request->legalEntityType) == 'vendor') ? ['Vendor'] : ((strtolower($request->legalEntityType) == 'debtor') ? ['Customer'] : ['Vendor', 'Customer']);
                foreach ($entity_types as $entity_type) {
                    if ($request->vendorCode != null || $request->debtorCode != null) {
                        continue;
                    }
                    $noSeries = $this->getOfficeNumberSeries($request->officeCode, ($entity_type == 'Customer') ? 'Debtor' : $entity_type);
                    $data = [
                        "office" => ($request->officeCode == 'UPS') ? 'KE' : $request->officeCode,
                        "partyType" => (strcmp(strtolower(App::environment()), 'production') == 0) ? $entity_type : 'TST',
                        "no" => "",
                        "name" => $request->companyName,
                        "postingGroup" => $entity_type == 'Vendor' ? $request->vendorType : $request->debtorType,
                        "vatRegistrationNo" => $request->licenceTypeValue ?? '-',
                        "postCode" => $request->zipCode ?? "",
                        "address" => $request->addressLine1,
                        "address2" => $request->addressLine2 ?? "",
                        "city" => $request->cityName ?? "",
                        "countryCode" => $request->countryCode,
                        "emailAddress" => $request->contractEmail ?? "",
                        "phoneNo" => $request->phoneNo ?? "",
                        "phoneNo2" => "",
                        "billingCurrency" => $request->billingCurrencyCode,
                        "paymentTermsCode" => (string)($request->creditDays) == "" ? "0" : (string)($request->creditDays),
                        // "creditLimitLCY" => intval($request->maxCreditLimit) ?? 0,
                        "creditLimitLCY" => (intval(str_replace(',', '', $request->maxCreditLimit)) > 0)
                            ? intval(str_replace(',', '', $request->maxCreditLimit))
                            : 1,
                        "contactPersonName" => $request->firstName == "-" ? "" : $request->firstName . ' ' . $request->lastName ?? "",
                        "contactPersonEmailAddress" => $request->contractEmail ?? "",
                        "contactPersonPhoneNo" => $request->phoneNo ?? "",
                        "contactPersonPhoneNo2" => "",
                        "relatedParty" => "",
                        "blocked" => false,
                        "NoSeries" => $noSeries
                    ];

                    if (config('app.env') != 'Local') {
                        $entity = $this->createLegalEntityType($entity_type, $data);
                        Log::info('Legal Entity Data:', $data);
                        Log::info('Legal Entity Response:', (array)$entity);
                        if (!(bool)$entity->status || (int)$entity->status == 400) {
                            $client_party_status = 0;
                            Log::warning('Legal Entity Creation Failed: ' . json_encode($entity));
                            Alert::warning($entity->responseCode . ' : ' . $entity->responseDescription)->persistent(true, false);
                            return redirect()->route('client-party.show', $id);
                        }
                        $entity_type_col = (strtolower($entity_type) == 'vendor') ? 'Vendor' : 'Debtor';
                        DB::table('Cust_Entity')->where('Id', $id)->update([
                            ucfirst($entity_type_col) . 'Code' => $entity->responseCode,
                        ]);
                    }
                    $client_party_status = 1;
                }
            }

            // Set client_party_status for operational entities
            if (
                isset($request->clientCheck) || isset($request->shipperCheck) ||
                isset($request->consigneeCheck) || isset($request->otherCheck)
            ) {
                $client_party_status = 1;
            }

            // SCM integration
            if ($client_party_status == 1) {
                $results = [];
                if ($request->partyEntityType != 'NoSCMIntergration') {
                    $results = $this->CreateClientPartySCM($id);
                } else {
                    DB::table('Cust_Entity')->where('Id', $id)->update(['Status' => 'Created']);
                    $results = json_decode(json_encode([['Status' => 'Success', 'Remark' => 'Debtor / Vendor Created In D365']]), FALSE);
                }

                Log::info('SCM Creation Results:', (array)$results);
                if ($results[0]->Status != 'Failed') {
                    alert()->success($results[0]->Status, $results[0]->Remark)->showConfirmButton();
                } else {
                    alert()->error($results[0]->Status, $results[0]->Remark)->showConfirmButton();
                }
            }
        } else {
            alert()->success('Success !', 'Entity Updated.')->showConfirmButton();
        }

        return redirect()->route('client-party.index');
    }

    public function CreateClientPartySCM($requestID)
    {
        $entity = DB::table('Cust_Entity')->where('Id', $requestID)->get();
        Log::info('CreateClientPartySCM Input:', ['RequestID' => $requestID, 'OperatioEntityType' => $entity[0]->OperatioEntityType]);

        $results = json_decode(json_encode([['Status' => 'Failed', 'Remark' => 'Entity Not Created']]));

        if (!Auth::user()->can('create-client-party-SCM') && $entity[0]->OperatioEntityType != 'Client without Debtor or Vendor') {
            $results = json_decode(json_encode([['Status' => 'Failed', 'Remark' => 'Unauthorised SCM Access.']]));
            Log::warning('SCM Permission Denied:', ['UserID' => Auth::user()->id, 'Permissions' => Auth::user()->getAllPermissions()->pluck('name')->toArray()]);
            return $results;
        }

        try {
            $results = DB::select("SET NOCOUNT ON;
                DECLARE
                    @RequestID AS UNIQUEIDENTIFIER,
                    @Status AS VARCHAR(30),
                    @Remark AS VARCHAR(1000),
                    @ClientCode AS VARCHAR(60),
                    @ClientID AS UNIQUEIDENTIFIER,
                    @OtherCode AS VARCHAR(60),
                    @OtherID AS UNIQUEIDENTIFIER;
                EXEC [dbo].[Sp_OperationEntityCreation]
                    @RequestID = ?,
                    @status = @status OUTPUT,
                    @Remark = @Remark OUTPUT,
                    @ClientCode = @ClientCode OUTPUT,
                    @ClientID = @ClientID OUTPUT,
                    @OtherCode = @OtherCode OUTPUT,
                    @OtherID = @OtherID OUTPUT;
                SELECT @Status AS Status, @Remark AS Remark, @ClientCode AS ClientCode,
                    @ClientID AS ClientID, @OtherCode AS OtherCode, @OtherID AS OtherID", [$requestID]);

            DB::beginTransaction();
            DB::table('Cust_Entity')->where('Id', $requestID)->update(['Status' => 'Created']);
            DB::commit();
            Log::info('SCM Creation Success:', (array)$results);
        } catch (\Exception $th) {
            Log::error('SCM Creation Failed: ' . $th->getMessage());
            DB::rollBack();
            $results = json_decode(json_encode([['Status' => 'Failed', 'Remark' => 'Entity Creation Failed: ' . $th->getMessage()]]));
        }

        return $results;
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

    public function reject()
    {
        $user = Auth::user();

        if ((!$user->can('approve-entity-request')) && (!$user->can('create-client-party'))) {
            alert()->warning('Warning !', 'Unauthorised.')->showConfirmButton();
            return response()->json(['false', 312]);
        }

        $id = \request()->get('id');
        $level = \request()->get('level');
        $reason = \request()->get('reason');

        $clientParty = DB::table('Cust_Entity')->where('Id', $id)->first();

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
            exit();
        }

        try {
            DB::table('Cust_Entity')->where('Id', $id)->update([
                "RejectedBy" => Auth::user()->full_name,
                "RejectedOn" => date('Y-m-d H:i:s'),
                "RejectedReason" => $reason,
                "Status" => $level,
            ]);
        } catch (Exception $e) {
            alert()->warning('Entity', $e);
            exit();
        }

        alert()->success('Entity', 'Entity Rejected.');
        return response()->json(['ok', 200]);
    }

    public function approve()
    {
        $user = Auth::user();

        if (!$user->can('approve-entity-request')) {
            alert()->warning('Warning !', 'You are not authorised to veiw this page. Contact Admin')->showConfirmButton();
            return redirect()->route('home');
        }

        $id = \request()->get('id');
        $status = \request()->get('status');

        $clientParty = DB::table('Cust_Entity')->where('Id', $id)->first();

        $status = ($status == 'PendingManagerApproval') ? 'Submitted' : $status;

        try {
            DB::table('Cust_Entity')->where('Id', $id)->update([
                "Status" => $status,
            ]);
        } catch (Exception $e) {
            alert()->warning('Entity', $e);
            exit();
        }

        $history = [
            'ID' => guid(),
            'CustEntityID' => $id,
            'RequestNo' => $clientParty->Code,
            'Role' => $user->Role,
            'ReasonID' => 'e6b21257-a530-45ff-bf56-939e53a536b5',
            'Comment' => "Entity Approved",
            'Type' => 'Entity',
            'PreviousValue' => $clientParty->Status,
            'NewValue' => $status,
            'CreatedBy' => $user->ID,
            'CreatedOn' => Carbon::now(),
            'LastModifiedBy' => $user->ID,
            'LastModifiedOn' => Carbon::now()
        ];

        EntityHistory::create($history);

        alert()->success('Entity', 'Entity Approved.');
        return response()->json(['ok', 200]);
    }

    public function approvals()
    {
        if (!Auth::user()->can('approve-entity-request')) {
            alert()->warning('Warning !', 'You are not authorised to veiw this page. Contact Admin')->showConfirmButton();
            return redirect()->route('home');
        }

        return view('client-party.approve');
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
            $input = Request::all();
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


    // public function getCompanies()
    // {
    //     try {
    //         // Use Request::get() to access query parameter
    //         $entityType = Request::get('entityType');
    //         if (!$entityType) {
    //             return response()->json(['error' => 'Entity type is required'], 400);
    //         }

    //         // Execute stored procedure with SET NOCOUNT ON to suppress PRINT output
    //         $results = DB::select('SET NOCOUNT ON; EXEC dbo.sp_EntitySearchList ?, ?', [$entityType, '']);

    //         // Handle string-based error response (e.g., 'Failed - Error message is - ...')
    //         if ($results && is_array($results) && count($results) > 0 && is_string($results[0])) {
    //             \Log::error('sp_EntitySearchList failed for entityType: ' . $entityType . ', Error: ' . $results[0]);
    //             return response()->json(['error' => $results[0]], 500);
    //         }

    //         // Handle structured error response (e.g., Name: 'Failed - ...')
    //         if ($results && count($results) > 0 && isset($results[0]->Name) && strpos($results[0]->Name, 'Failed') === 0) {
    //             \Log::error('sp_EntitySearchList failed for entityType: ' . $entityType . ', Error: ' . $results[0]->Name);
    //             return response()->json(['error' => $results[0]->Name], 500);
    //         }

    //         // Map results to expected format
    //         $companies = array_map(function ($item) {
    //             return [
    //                 'id' => $item->Id,
    //                 'code' => $item->Code,
    //                 'name' => $item->Name
    //             ];
    //         }, $results);

    //         // Handle empty result set
    //         if (empty($companies)) {
    //             \Log::warning('sp_EntitySearchList returned no results for entityType: ' . $entityType);
    //             return response()->json([]);
    //         }

    //         return response()->json($companies);
    //     } catch (QueryException $e) {
    //         \Log::error('Error executing sp_EntitySearchList for entityType: ' . ($entityType ?? 'unknown') . ', SQL Error: ' . $e->getMessage());
    //         return response()->json(['error' => 'Failed to fetch companies: ' . $e->getMessage()], 500);
    //     }
    // }
    public function getCompanies()
    {
        try {
            $entityType = Request::get('entityType');
            $query = Request::get('query', ''); // Optional query parameter for name filtering

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
            $results = DB::select('SET NOCOUNT ON; EXEC [FITSYSUAT].dbo.sp_EntityAddressDetails ?, ?', [$entityType, $companyId]);

            // Handle string-based error
            if ($results && is_array($results) && count($results) > 0 && is_string($results[0])) {
                \Log::error('sp_EntityAddressDetails failed for entityType: ' . $entityType . ', companyId: ' . $companyId . ', Error: ' . $results[0]);
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
                            "SELECT CountryID FROM [FITSYSUAT].dbo.Cust_Country WHERE CountryCode = ?",
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
             EXEC [FITSYSUAT].[dbo].[sp_EntityAddressDetails] @PartyType = ?, @PartyID = ?',
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
