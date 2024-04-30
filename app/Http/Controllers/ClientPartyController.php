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
use JeroenNoten\LaravelAdminLte\Components\Form\Select;
use Ramsey\Uuid\Guid\Guid;
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
        // logged in user who is not admin
        // if ($user->Role != 1) {
        // $clientsparties =   ClientParty::where('CreatedBy', '=', $user->full_name)->paginate(50);
        // }
        // dd($user->full_name);

        //for submitted requests
        // if (\request()->filled('submitted_request')) {
        //     $clientsparties = ClientParty::where('Status', 'Submitted')
        //         ->orWhere('Status', 'Created')->orWhere('Status', 'Rejected')
        //         ->orderby('CreatedOn', 'desc')
        //         ->paginate(50);
        //     $title = 'User Submitted Client Party Requests';
        // };

        $clientParty = $this->clientParty;
        // dd($clientParty->clientPartyCount('Status', , null));

        //redirect to the landing pages
        return view('client-party.index', compact('clientParty', 'title'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // authorization
        if (!\request()->user()->can('request-client-party')) {
            abort(403, "You are not allowed to view this page.");
        }

        $countries =  $this->getCountries();
        $offices = $this->getOffices();
        $currencyList = $this->getCurrencyList();
        $licenceTypes = $this->getLicenceType();
        $tarrifs = [];
        $tarrifs = $this->getTarrif();
        $incoterms = $this->getIncoTerms();
        $payTerms = $this->getPaymentTermList();

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
            $response =  DB::select(
                'SET NOCOUNT ON;
                Declare  @OfficeID as Uniqueidentifier,
                @PartyType as Varchar(60),
                @Filter as Varchar(50)
                Exec sp_PartyList
                @OfficeID = ?, @PartyType = ?, @Filter = ?',
                [$query['officeID'], $type, $query['query']]
            );
            // dd($response);
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
        $response =  DB::select(
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
            // exec countryList database procedure
            $response =  DB::select(
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
        // exec countryList database procedure
        $response =  DB::select(
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
        // exec countryList database procedure
        $response =  DB::select(
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
        // dd($locationType);
        $response =  DB::select(
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
        // return $response;
        // dd($response);
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
        // dd($locations);
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
        $response =  DB::select(
            'SET NOCOUNT ON;
                Declare @OfficeID as Uniqueidentifier
                Exec [dbo].[sp_BaseOfficeCurrency]
                @officeID = ?',
            [$query]
        );
        // return $response;
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
        $response =  DB::select(
            'SET NOCOUNT ON;
                Declare @OfficeID as Uniqueidentifier
                Exec [dbo].[sp_EntityType]
                @OfficeCode = ?',
            [$query]
        );
        // dd($query, $response);
        $v = [];
        $d = [];
        if (count($response) > 0) {
            foreach ($response as $key => $row) {
                ($row->Type == 'Vendor') ? array_push($v, $row->Code) : array_push($d, $row->Code);
            }
        }

        // return $response;
        return response()->json(["Vendors" => $v, "Debtors" => $d]);
    }

    public function getClients($type = null)
    {
        $clients = [];
        $query = \request()->all()['query'];

        if ($type == "master") {
            //get clients form the master table
            $response =  DB::select(
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
            // Get clients from the local table
            $results = ClientParty::select('name', 'Id', 'code')
                ->where('OperatioEntityType', "Client + Shipper + Consignee")
                // ->where('OfficeID', Auth::user()->office->ID)
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
        $response =  DB::select(
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
        $response =  DB::select(
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
        $response =  DB::select(
            'SET NOCOUNT ON;
            Declare @OfficeID as Uniqueidentifier,
            @PartyType as Varchar(60),
            @Filter as Varchar(50)
            Exec [dbo].[sp_PartyList]
            @OfficeID = ?,
            @PartyType = ?,
            @Filter = ?
            ',
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
        $response =  DB::select(
            'SET NOCOUNT ON;
            Declare @Filter as Varchar(50)
            Exec [dbo].[sp_IncoTerms]
            @Filter = ?',
            ['']
        );

        return $response;
    }

    public function getPaymentTermList()
    {
        $response =  DB::select(
            'SET NOCOUNT ON;
            Declare @Filter as Varchar(50)
            Exec [dbo].[sp_PaymentTerms]
            @Filter = ?',
            ['']
        );

        return $response;
    }
    public function getCurrencyList($var = null)
    {
        $query = '';
        if (\request()->ajax()) {
            $query = \request()['query'];
        } else {
            $query = $var;
        }
        // dd($query);
        //validate empty
        if (!empty($query)) {
            $response =  DB::select(
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
    public function salesAccountPCI($var =  null)
    {
        if (\request()->ajax()) {
            $query = \request()->all();
            $response =  DB::select(
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
            // dd($response);
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
    public function getNotifyPartyList($var =  null)
    {
        // dd($var);
        //  exit();
        $query = \request()->all();
        $response =  DB::select(
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
// dd($request);
        $user = \request()->user();
        // authorization
        if (!$user->can('request-client-party')) {
            abort(403, "You are not allowed to view this page.");
        }

        $clientName = isset($request->client) ? $request->client : $request->clientInput;

        $status = '';
        if (isset($request->submitBtn)) {
            // update status to ready
            $status = 'Submitted';
        } else {
            // default status
            $status = 'Not Submitted';
        };

        if (isset($request->debtor) || isset($request->debtorCheck)) {
            $status = 'PendingManagerApproval';
        }
        //client party instatnce
        $clientParty = new ClientParty();
        /*Logged in user*/
        $user = Auth::user();
        $code = $clientParty->generateUniqueCode($user->OfficeID);
        $client_party_id = guid();

        try {
            DB::beginTransaction();
            $data = [
                'Id' => $client_party_id,
                'Code' => $code,
                //Entity type
                'OperatioEntityType' => $request->partyEntityType,
                'LegalEntityType' => $request->legalEntityType,
                //basic informaiton
                'Name' => $request->companyName,
                'Address_Line1' => $request->addressLine1,
                'Address_Line2' => $request->addressLine2,
                'Address_CountryId' => $request->countryID != '' ? $request->countryID : null, //guid
                'Address_CountryName' =>  $request->countryName,
                'Address_CountryCode' => $request->countryCode,

                'Address_State' => $request->provinceName,
                'Address_StateId' => $request->provinceID != '' ? $request->provinceID : null, //guid
                'Address_StateCode' => $request->provinceCode,

                'Address_Pin' => $request->zipCode,
                'Address_City' => $request->cityName,
                'Address_CityCode' => $request->cityCode,
                // contact details
                'Cont_Bch_PersonName_FirstName' => $request->firstName,
                'Cont_Bch_PersonName_LastName' => $request->lastName,
                'Cont_Bch_Dept' => $request->department,
                'Cont_Bch_PhoneNumbers' => $request->phoneNo,
                'Cont_Bch_Email' => $request->contractEmail,
                //special instructions
                'SpecialInstructions' => $request->specialInstruction,
                // office
                'Office' => $request->officeCountry,
                'OfficeCode' => $request->officeCode,
                'OfficeID' => $request->officeCountryID,

                //preffered ports
                'AirUsualPortOfLoadingName' => $request->portOfLoadingAir,
                'AirUsualPortOfLoading' => $request->portOfLoadingAirID != '' ? $request->portOfLoadingAirID : null, //guid
                'AirUsualPortofDischargeName' => $request->portOfDischargeAir,
                'AirUsualPortofDischarge' => $request->portOfDischargeAirID != '' ? $request->portOfDischargeAirID : null, //guid
                'SeaUsualPortOfLoadingName' => $request->portOfLoadingSea, //varchar
                'SeaUsualPortOfLoading' => $request->portOfLoadingSeaID != '' ? $request->portOfLoadingSeaID : null, //guid
                'SeaUsualPortofDischargeName' => $request->portOfDischargeSea, //varchar
                'SeaUsualPortofDischarge' => $request->portOfDischargeSeaID != '' ? $request->portOfDischargeSeaID : null, //guid
                // file upload

                //client
                'SalesMgrOfficeFunMapName' => $request->salesPIC,
                'SalesMgrOfficeFunMapId' => $request->salesPICID != '' ? $request->salesPICID : null, //!id needs to be added to procedure
                // 'SalesMgrOfficeFunMapId' => null,//!remove this
                'IncoTermID' => $request->incoTermID,
                'IncoTermName' => $request->incoTermName,
                'IncoTermCode' => $request->incoTermCode,

                'DefaultNotifyPartyName' => $request->notifyParty1,
                'DefaultNotifyPartyId' => $request->notifyParty1ID != '' ? $request->notifyParty1ID : null,
                'DefaultNotifyPartyCode' => $request->notifyParty1Code,

                'AccountMgrOfficeFunMapName' => $request->accountPIC,
                'AccountMgrOfficeFunMapId' => $request->accountPICID != '' ? $request->accountPICID : null, //!id needs to be added to procedure
                // 'AccountMgrOfficeFunMapId' => null, //!Remove this
                'PaymentTermsID' => $request->paymentTermsID,
                'PaymentTermsName' => $request->paymentTermsName,
                'PaymentTermsCode' => $request->paymentTermsCode,

                'DefaultNotifyParty1Name' => $request->notifyParty2,
                'DefaultNotifyParty1Id' => $request->notifyParty2ID != '' ? $request->notifyParty2ID : null, //!id needs to be added to procedure
                'DefaultNotifyParty1Code' => $request->notifyParty2Code,

                'DefaultFowarderName' => $request->forwarderName,
                'DefaultFowarderId' => $request->forwarderID != '' ? $request->forwarderID : null, //guid

                // debtor / vendor
                'CreditDays' => $request->creditDays,
                'CreditLimitCurrencyId' => $request->billingCurrency != '' ? $request->billingCurrency : null,
                'CreditLimitCurrencyCode' => $request->billingCurrencyCode,
                'BillingCurrencyCode' => $request->billingCurrencyCode,
                'BillingCurrencyId' => $request->billingCurrency != '' ? $request->billingCurrency : null, //guid
                'BaseCurrencyName' => $request->baseCurrencyName,
                'BaseCurrencyCode' => $request->baseCurrencyCode,
                'BaseCurrencyId' => $request->baseCurrencyID != '' ? $request->baseCurrencyID : null, //guid
                'CreditLimit' => isset($request->maxCreditLimit) ? str_replace(',', '', $request->maxCreditLimit) : 0,
                'LicenceTypeID' => $request->licenceTypeID,
                'LicenceTypeCode' => $request->licenceTypeCode,
                'LicenceTypeValue' => $request->licenceTypeValue,
                'TariffValue' => $request->tarrif,
                'FSCType' => $request->fscType,
                'FSCRate' => isset($request->fscRate) ? $request->fscRate : 0,
                'BillingType' => $request->billingType,
                'Discount' => isset($request->discount) ? $request->discount : 0,

                // Admin Details
                'ClientName' => $clientName,
                'ClientID' =>  $request->clientID,
                'ClientCode' =>  $request->clientCode,
                //request option
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

                'Vendor'  => $request->vendor,
                'VendorID' => $request->vendorID,
                'VendorCode' => $request->vendorCode,

                'RequestforClient' => isset($request->clientCheck) ? 1 : 0,
                'RequestforShipperConsignee' => isset($request->consigneeCheck) ? 1 : (isset($request->shipperCheck) ? 1 : 0),
                'RequestforOtherParty' => isset($request->otherCheck) ? 1 : 0,
                'RequestforDebtor' => isset($request->debtorCheck) ? 1 : 0,
                'RequestforVendor'  => isset($request->vendorCheck) ? 1 : 0,

                //System
                'CreatedBy' => $user->full_name,
                'CreatedOn' => Carbon::now()->format('Y-m-d H:i:s'),
                'LastModifiedOn' => Carbon::now()->format('Y-m-d H:i:s'),
                'LastModifiedBy' => $user->full_name,

                // 'DebtorLicenceTypeID' => $request->debtorLicenceType != '' ? $request->debtorLicenceType : null, //guid
                // 'DebtorLicenceTypeCode' => $request->debtorLicenceCode,
                // 'DebtorLicenceTypeValue' => $request->debtorLicence,
                // 'VendorLicenceTypeID' => $request->vendorLicenceType != '' ? $request->vendorLicenceType : null, //guid
                // 'VendorLicenceTypeCode' => $request->vendorLicenceCode,
                // 'VendorLicenceTypeValue' => $request->vendorLicence,
                'DebtorType' => $request->debtorType,
                'VendorType' => $request->vendorType,

                // 'DefaultPartyLocationId' => null,
                'Status' => $status,

            ];
            // dd($request, $data);
            DB::table('Cust_Entity')->updateOrInsert($data);
            DB::commit();
            // dd('here');
        } catch (Exception $th) {
            DB::rollback();
            Log::warning($th->getMessage());
            Alert::alert('Error', $th->getMessage(), 'error');
        }

        if ($request->hasFile('attachmentNames')) {

            foreach ($request->attachmentNames as $key => $attachment) {
                $fileName =   $attachment->getClientOriginalName();

                $newName = $code . '_' . pathinfo($fileName, PATHINFO_FILENAME) . '.' . $attachment->getClientOriginalExtension();
                    try {
                    $request->attachmentNames[$key]->move(storage_path('client-party'), $newName);
                    // upload to azure

                    $local_storage_path = storage_path() . '/client-party/' . $newName;
                    // Azure storage path
                    $storage_path = 'Kenya/ClientParty/' . $newName;
                    // move files to azure storage
                    Storage::disk('azure')->put($storage_path, file_get_contents($local_storage_path));

                    // update attachment to db
                    DB::beginTransaction();
                    DB::table('Cust_EntityAttachment')->updateOrInsert([
                        'ID' => guid(),
                        'CustEntityID' => $client_party_id,
                        'RequestNo' => $code,
                        'Title' => $request->attachmentDescription[$key],
                        'FileName' => $newName,
                        'FilePath' => $storage_path,
                        'CreatedBy' => $user->ID,
                        'CreatedOn' => Carbon::now(),
                        'LastModifiedBy' => $user->ID,
                        'LastModifiedOn' =>  Carbon::now(),
                    ]);
                    DB::commit();
                    //*Delete local file
                    File::delete($local_storage_path);
                } catch (Exception $ex) {
                    Log::warning($ex->getMessage());
                    DB::rollBack();
                }
            }
        }

        // todo: create history entity
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
        if (isset($request->createBtn) && (isset($request->debtorCheck) || isset($request->vendorCheck))) {

            $entity_types =  (strtolower($request->legalEntityType) == 'vendor') ? ['Vendor'] : ((strtolower($request->legalEntityType) == 'debtor') ? ['Customer'] : ['Vendor', 'Customer']);

            foreach ($entity_types as $key => $entity_type) {
                if ($request->vendorCode != null) {
                    continue;
                }
                if ($request->debtorCode != null) {
                    continue;
                }
                // todo get number series
                $noSeries = '';
                $ent_typ = ($entity_type == 'Customer') ? 'Debtor' : $entity_type;
                $noSeries = $this->getOfficeNumberSeries($request->officeCode, $ent_typ);

                $data = [
                    "office" => ($request->officeCode == 'UPS') ? 'KE' : $request->officeCode,
                    "partyType" => (strcmp(strtolower(APP::environment()), 'production') == 0) ? $entity_type : 'TST',
                    "no" => "",
                    "name" => $request->companyName,
                    "postingGroup" => $entity_type == 'vendor' ? $request->vendorType : $request->debtorType,
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
                    "creditLimitLCY" => intval($request->maxCreditLimit) ?? 0,
                    "contactPersonName" => $request->firstName == "-" ? "" : $request->firstName . ' ' . $request->lastName ?? "",
                    "contactPersonEmailAddress" => $request->contractEmail ?? "",
                    "contactPersonPhoneNo" => $request->phoneNo ?? "",
                    "contactPersonPhoneNo2" => "",
                    "relatedParty" => "",
                    "blocked" => false,
                    "NoSeries" => $noSeries
                ];

                if (config('app.env') != 'Local'){

                $entity = $this->createLegalEntityType($entity_type, $data);

                Log::info(json_encode($data));
                Log::info(json_encode($entity));

                // entity creation failed
                if (!(bool)$entity->status) {
                    $client_party_status = 0;
                    Log::warning(json_encode($entity));
                    Alert::warning($entity->responseCode . ' : ' . $entity->responseDescription)->persistent(true, false);
                    return redirect()->route('client-party.show', $client_party_id);
                    break;
                }

                if ((int)$entity->status == 400) {
                    $client_party_status = 0;
                    Log::warning(json_encode($entity));
                    Alert::warning(json_encode($entity->errors))->persistent(true, false);
                    return redirect()->route('client-party.show', $client_party_id);
                    break;
                }

                $entity_type_col =  (strtolower($entity_type) == 'vendor') ? 'Vendor' : 'Debtor';

                // update entity vendor / debtor
                DB::table('Cust_Entity')->where('Id', $client_party_id)->update([
                    ucfirst($entity_type_col) . 'Code' => $entity->responseCode,
                ]);}

                $client_party_status = 1;
            }
        }

        // create entity on SCM
        if (isset($request->createBtn) && ($client_party_status == 1)) {
            $results = [];
            if ($request->partyEntityType != 'NoSCMIntergration') {
                // create debtor vendor on SCM
                $results = DB::select("SET NOCOUNT ON;
                                    Declare
                                    @RequestID as Uniqueidentifier,
                                    @Status as Varchar(30),
                                    @Remark as Varchar(1000)
                                        Exec [dbo].[sp_CreateDebtorandVendor]
                                    @RequestID = ?,
                                    @status= @status  OUTPUT,
                                    @Remark= @Remark  OUTPUT
                                        Select @Status as Status, @Remark as Remark", [
                    $client_party_id
                ]);
                //  create client party on scm
                $results = $this->CreateClientPartySCM($client_party_id);
            } else {
                // update Status
                DB::table('Cust_Entity')->where('Id', $client_party_id)->update(['Status' => 'Created']);
                $rs = [['Status' => 'Success', 'Remark' => 'Debtor / Vendor Creatted In D365']];
                $results = json_decode(json_encode($rs), FALSE);
            }

            if ($results[0]->Status != 'Failed') {
                alert()->success($results[0]->Status, $results[0]->Remark)->showConfirmButton();
            } else {
                alert()->error($results[0]->Status, $results[0]->Remark)->showConfirmButton();
            }
        } else {
            alert()->success('Success !', 'Entity Saved.')->showConfirmButton();
        }

        //redirect to the index page
        return redirect()->route('client-party.index');
    }

    public function getOfficeNumberSeries($officeCode = null, $entityType = null)
    {
        // dd(trim($officeCode));
        $numSeries = DB::table('Cust_OfficeSeriesMapping')
            ->where('EntityType', $entityType)
            ->where('OfficeCode', trim($officeCode))
            ->pluck('SeriesNo');
        // dd($officeCode, $entityType, $numSeries);
        return $numSeries[0];
    }

    public function createLegalEntityType(string $type, array $post_data)
    {
        $debtor_url = 'http://10.0.1.14:8990/api/customer';
        $vendor_url = 'http://10.0.1.14:8990/api/vendor';

        $request_url = (strtolower($type) == 'vendor') ? $vendor_url : $debtor_url;

        // authentication token
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
        $token_url = 'http://10.0.1.14:8990/api/authenticate/token';

        $response = Http::post($token_url, [
            "username" => "D365Integration",
            "password" => "fitD365Integration@2019!"
        ]);

        return ["status" => $response->successful(), "token" => $response->body()];
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ClientParty  $clientParty
     * @return \Illuminate\Http\Response
     */
    public function show(ClientParty $clientParty)
    {


        $user = \request()->user();

        // authorization
        if (!$user->can('view-client-party')) {
            abort(403, "You are not allowed to view this page.");
        }
        // approve permission
        $approve = false;
        // Edit form permission
        $editForm = true;

        // check status
        // if status is pendingmangerapproval and authuser != approve-my-office-entity-request
        if (($clientParty->Status == 'PendingManagerApproval') && !$user->can('approve-my-office-entity-request')) {
            alert()->warning('Warning !', 'Entity Pending for Manager Approval')->showConfirmButton();
            return redirect()->back();
        }

        // if status pendingfinanceapproval  != approve-all-entity-request
        if (($clientParty->Status == 'PendingFinanceApproval') && !$user->can('approve-all-entity-request')) {
            alert()->warning('Warning !', 'Entity Pending for Finance Approval')->showConfirmButton();
            return redirect()->back();
        }


        if ($clientParty->Status == 'Created') {
            $editForm = false;
        }

        // permission for users to edit form
        if ((Auth::user()->Role != 1 && ($clientParty->Status == 'Submitted')) && !Auth::user()->can('create-client-party')) {
            $editForm = false;
        }
        // permission for admin
        if (Auth::user()->Role == 1 && ($clientParty->Status != 'Created')) {
            $editForm = true;
        }

        //permission for approve
        if (url()->previous() == url('client-party/approve') || (($clientParty->Status == 'PendingFinanceApproval') || ($clientParty->Status == 'PendingManagerApproval'))) {
            $editForm = false;
            $approve = true;
        }

        $countries =  $this->getCountries();
        $offices = $this->getOffices();

        $currencyList = $this->getCurrencyList($clientParty->OfficeID) == [] ? ['does not exist'] : $this->getCurrencyList($clientParty->OfficeID);

        if ($clientParty->OfficeID != null) {
            if (str_contains(json_encode($currencyList[0]), 'does not exist')) {
                $scm_office_id = DB::select(DB::raw("select SCMOfficeID
                from Cust_Office where ID = '$clientParty->OfficeID'"));
                $currencyList = $this->getCurrencyList($scm_office_id[0]->SCMOfficeID);
            }
        } else {
            $idX = Auth::user()->office->SCMOfficeID;
            $currencyList = $this->getCurrencyList($idX);
        }


        $licenceTypes = $this->getLicenceType();
        $tarrifs = [];

        if ($clientParty->Office == "FIT - Kenya - UPS") {
            $tarrifs = $this->getTarrif($clientParty->OfficeID);
        } else {
            $tarrifs = [];
        }

        $incoterms = $this->getIncoTerms();
        $payTerms = $this->getPaymentTermList();

        return view(
            'client-party.show',
            compact('approve', 'editForm', 'payTerms', 'incoterms', 'clientParty', 'countries', 'offices', 'currencyList', 'licenceTypes', 'tarrifs')
        );
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\ClientParty  $clientParty
     * @return \Illuminate\Http\Response
     */
    public function edit(ClientParty $clientParty)
    {
        if (!Auth::user()->can('edit-client-party')) {
            alert()->warning('Warning !', 'Unauthorised.')->showConfirmButton();
            return response()->back();
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

        // dd($request);

        $user = Auth::user();
        $cp = ClientParty::find($id);
        //authorization
        if (!$user->can('update-client-party')) {
            alert()->warning('Warning !', 'Unauthorised.')->showConfirmButton();
            return redirect()->back();
        }



        // get client name
        $clientName = isset($request->client) ? $request->client : $request->clientInput;

        $status = '';

        if (isset($request->submitBtn)) {
            // update status to ready
            $status = 'Submitted';
        } else {
            // default status change if not admin
            if (auth()->user()->Role != 1) {
                $status = 'Not Submitted';
            } else {
                $status = $cp->Status;
            }
        };

        // if status is pendingmangerapproval and authuser != approve-my-office-entity-request
        if (($request->status == 'PendingManagerApproval') && $user->can('approve-my-office-entity-request') && isset($createBtn)) {
            $status = 'PendingFinanceApproval';
        }
        // else {
        //     alert()->warning('Warning !', 'Entity Pending for Manager Approval')->showConfirmButton();
        //     return redirect()->back();
        // }

        // redirect back not authorized
        // if status pendingfinanceapproval  != approve-all-entity-request
        if (($request->status == 'PendingFinanceApproval') && $user->can('approve-all-entity-request') && isset($createBtn)) {
            $status = 'Submitted';
        }
        // else {
        //     alert()->warning('Warning !', 'Entity Pending for Finance Approval')->showConfirmButton();
        //     return redirect()->back();
        // }


        try {
            DB::beginTransaction();
            DB::table('Cust_Entity')->where('ID', $id)->update([
                //Entity type
                'OperatioEntityType' => $request->partyEntityType,
                'LegalEntityType' => $request->legalEntityType,
                //basic informaiton
                'Name' => $request->companyName,
                'Address_Line1' => $request->addressLine1,
                'Address_Line2' => $request->addressLine2,

                'Address_CountryId' => $request->countryID != '' ? $request->countryID : null, //guid
                'Address_CountryName' =>  $request->countryName,
                'Address_CountryCode' => $request->countryCode,

                'Address_State' => $request->provinceName,
                'Address_StateId' => $request->provinceID != '' ? $request->provinceID : null, //guid
                'Address_StateCode' => $request->provinceCode,

                'Address_Pin' => $request->zipCode,
                'Address_City' => $request->cityName,
                'Address_CityCode' => $request->cityCode,
                // contact details
                'Cont_Bch_PersonName_FirstName' => $request->firstName,
                'Cont_Bch_PersonName_LastName' => $request->lastName,
                'Cont_Bch_Dept' => $request->department,
                'Cont_Bch_PhoneNumbers' => $request->phoneNo,
                'Cont_Bch_Email' => $request->contractEmail,
                //special instructions
                'SpecialInstructions' => $request->specialInstruction,
                // office
                'Office' => $request->officeCountry,
                'OfficeCode' => isset($request->officeCode) ? $request->officeCode : $request->officeCountryDisp,
                'OfficeID' => $request->officeCountryID,

                //preffered ports
                'AirUsualPortOfLoadingName' => $request->portOfLoadingAir,
                'AirUsualPortOfLoading' => $request->portOfLoadingAirID != '' ? $request->portOfLoadingAirID : null, //guid
                'AirUsualPortofDischargeName' => $request->portOfDischargeAir,
                'AirUsualPortofDischarge' => $request->portOfDischargeAirID != '' ? $request->portOfDischargeAirID : null, //guid
                'SeaUsualPortOfLoadingName' => $request->portOfLoadingSea, //varchar
                'SeaUsualPortOfLoading' => $request->portOfLoadingSeaID != '' ? $request->portOfLoadingSeaID : null, //guid
                'SeaUsualPortofDischargeName' => $request->portOfDischargeSea, //varchar
                'SeaUsualPortofDischarge' => $request->portOfDischargeSeaID != '' ? $request->portOfDischargeSeaID : null, //guid
                // file upload

                //client
                'SalesMgrOfficeFunMapName' => $request->salesPIC,
                'SalesMgrOfficeFunMapId' => $request->salesPICID != '' ? $request->salesPICID : null, //!id needs to be added to procedure
                // 'SalesMgrOfficeFunMapId' => null,//!remove this
                'IncoTermID' => $request->incoTermID,
                'IncoTermName' => $request->incoTermName,
                'IncoTermCode' => $request->incoTermCode,

                'DefaultNotifyPartyName' => $request->notifyParty1,
                'DefaultNotifyPartyId' => $request->notifyParty1ID != '' ? $request->notifyParty1ID : null,
                'DefaultNotifyPartyCode' => $request->notifyParty1Code,

                'AccountMgrOfficeFunMapName' => $request->accountPIC,
                'AccountMgrOfficeFunMapId' => $request->accountPICID != '' ? $request->accountPICID : null, //!id needs to be added to procedure
                // 'AccountMgrOfficeFunMapId' => null, //!Remove this
                'PaymentTermsID' => $request->paymentTermsID,
                'PaymentTermsName' => $request->paymentTermsName,
                'PaymentTermsCode' => $request->paymentTermsCode,
                'DefaultNotifyParty1Name' => $request->notifyParty2,
                'DefaultNotifyParty1Id' => $request->notifyParty2ID != '' ? $request->notifyParty2ID : null, //!id needs to be added to procedure
                'DefaultNotifyParty1Code' => $request->notifyParty2Code,
                'DefaultFowarderName' => $request->forwarderName,
                'DefaultFowarderId' => $request->forwarderID != '' ? $request->forwarderID : null, //guid
                // debtor / vendor
                'CreditDays' => $request->creditDays,

                'CreditLimitCurrencyId' => $request->billingCurrency != '' ? $request->billingCurrency : null,
                'CreditLimitCurrencyCode' => $request->billingCurrencyCode,
                'BillingCurrencyCode' => $request->billingCurrencyCode,
                'BillingCurrencyId' => $request->billingCurrency != '' ? $request->billingCurrency : null, //guid
                'BaseCurrencyName' => $request->baseCurrencyName,
                'BaseCurrencyCode' => $request->baseCurrencyCode,
                'BaseCurrencyId' => $request->baseCurrencyID != '' ? $request->baseCurrencyID : null, //guid
                'CreditLimit' => isset($request->maxCreditLimit) ? str_replace(',', '', $request->maxCreditLimit) : 0,
                'LicenceTypeID' => $request->licenceTypeID,
                'LicenceTypeCode' => $request->licenceTypeCode,
                'LicenceTypeValue' => $request->licenceTypeValue,
                'TariffValue' => $request->tarrif,
                'FSCType' => $request->fscType,
                'FSCRate' => isset($request->fscRate) ? $request->fscRate : 0,
                'BillingType' => $request->billingType,
                'Discount' => isset($request->discount) ? $request->discount : 0,
                // Admin Details
                'ClientName' => $clientName,
                'ClientID' =>  $request->clientID != '' ? $request->clientID : null,
                'ClientCode' =>  $request->clientCode,
                //request option
                'RequestId' => $request->requestID != '' ? $request->requestID : null,
                'RequestName' => $request->clientOptionVal ?? null,
                'RequestCode' => $request->requestCode ?? null,

                'ShipperConsigneeName' => $request->shipperInput,
                'ShipperConsigneeID' => $request->shipperID != '' ? $request->shipperID : null,
                'ShipperConsigneeCode' => $request->shipperCode,
                'OtherPartyName' => $request->otherInput,
                'OtherPartyID' => $request->otherID != '' ? $request->otherID : null,
                'OtherPartyCode' => $request->otherCode,
                // 'Debtor' => $request->debtor,
                'DebtorID' => $request->debtorID != '' ? $request->debtorID : null,
                'DebtorCode' => $request->debtorCode,
                // 'Vendor'  => $request->vendor,
                'VendorID' => $request->vendorID != '' ? $request->vendorID : null,
                'VendorCode' => $request->vendorCode,

                'RequestforClient' => isset($request->clientCheck) ? 1 : 0,
                'RequestforShipperConsignee' => isset($request->consigneeCheck) ? 1 : (isset($request->shipperCheck) ? 1 : 0),

                'RequestforOtherParty' => $request->otherID != '' ? 0 : 1,
                // 'RequestforOtherParty' => isset($request->otherCheck) ? 1 : 0,

                'RequestforDebtor' => $request->debtorID != '' ? 0 : 1,
                'RequestforVendor'  => $request->vendorID != '' ? 0 : 1,
                // 'RequestforDebtor' => isset($request->debtorCheck) ? 1 : 0,
                // 'RequestforVendor'  => isset($request->vendorCheck) ? 1 : 0,

                //System
                'LastModifiedOn' => Carbon::now()->format('Y-m-d H:i:s'),
                'LastModifiedBy' => $user->full_name,

                // 'DebtorLicenceTypeID' => $request->debtorLicenceType != '' ? $request->debtorLicenceType : null, //guid
                // 'DebtorLicenceTypeCode' => $request->debtorLicenceCode,
                // 'DebtorLicenceTypeValue' => $request->debtorLicence,
                // 'VendorLicenceTypeID' => $request->vendorLicenceType != '' ? $request->vendorLicenceType : null, //guid
                // 'VendorLicenceTypeCode' => $request->vendorLicenceCode,
                // 'VendorLicenceTypeValue' => $request->vendorLicence,
                'DebtorType' => $request->debtorType,
                'VendorType' => $request->vendorType,
                // 'DefaultPartyLocationId' => null,
                'Status' => $status
            ]);
            DB::commit();
        } catch (Exception $th) {
            Log::warning($th);
            DB::rollBack();
            alert()->error('Error !', 'Entity Not Updated.')->showConfirmButton();
            return redirect()->route('client-party.show', [$id]);
        }


        if ($request->hasFile('attachmentNames')) {

            foreach ($request->attachmentNames as $key => $attachment) {
                $fileName =   $attachment->getClientOriginalName();

                $newName = $cp->Code . '_' . pathinfo($fileName, PATHINFO_FILENAME) . '.' . $attachment->getClientOriginalExtension();
                try {
                    $request->attachmentNames[$key]->move(storage_path('client-party'), $newName);
                    // upload to azure

                    $local_storage_path = storage_path() . '/client-party/' . $newName;
                    // Azure storage path
                    $storage_path = 'Kenya/ClientParty/' . $newName;
                    // move files to azure storage
                    Storage::disk('azure')->put($storage_path, file_get_contents($local_storage_path));

                    // update attachment to db
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
                        'LastModifiedOn' =>  Carbon::now(),
                    ]);
                    DB::commit();
                    //*Delete local file
                    File::delete($local_storage_path);
                } catch (Exception $ex) {
                    Log::warning($ex->getMessage());
                    DB::rollBack();
                }
            }
        }


        // todo: create history entity
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

        if (isset($request->createBtn) && (isset($request->debtorCheck) || isset($request->vendorCheck))) {
            // clean entity types
            $entity_types =  (strtolower($request->legalEntityType) == 'vendor') ? ['Vendor'] : ((strtolower($request->legalEntityType) == 'debtor') ? ['Customer'] : ['Vendor', 'Customer']);


            // dd($request->debtorCode);
            foreach ($entity_types as $key => $entity_type) {
                if ($request->vendorCode != null) {
                    continue;
                }
                if ($request->debtorCode != null) {
                    continue;
                }
                $g = (strtolower($entity_type) == 'vendor') ? $request->vendorType : $request->debtorType;
                $noSeries = '';
                $ent_typ = ($entity_type == 'Customer') ? 'Debtor' : $entity_type;
                // dd($request->officeCode, $ent_typ);
                $noSeries = $this->getOfficeNumberSeries($request->officeCode, $ent_typ);
                // dd((string)($request->creditDays));
                $data = [
                    "office" => ($request->officeCode == 'UPS') ? 'KE' : $request->officeCode,
                    "partyType" => (strcmp(strtolower(APP::environment()), 'production') == 0) ? $entity_type : 'TST',
                    "no" => "",
                    "name" => $request->companyName,
                    "postingGroup" => $g,
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
                    "creditLimitLCY" => intval($request->maxCreditLimit) ?? 0,
                    "contactPersonName" => $request->firstName == "-" ? "" : $request->firstName . ' ' . $request->lastName ?? "",
                    "contactPersonEmailAddress" => $request->contractEmail ?? "",
                    "contactPersonPhoneNo" => $request->phoneNo ?? "",
                    "contactPersonPhoneNo2" => "",
                    "relatedParty" => "",
                    "blocked" => false,
                    "NoSeries" => $noSeries
                ];
                // dd($noSeries);
                if (config('app.env') != 'Local'){

                try {
                    $entity = $this->createLegalEntityType($entity_type, $data);
                } catch (Exception $e) {
                    Alert::warning($e->getMessage());
                    return redirect()->back();
                }

                Log::info(json_encode($data));
                Log::info(json_encode($entity));

                // entity creation failed
                if (!(bool)$entity->status) {
                    $client_party_status = 0;
                    Log::warning(json_encode($entity));
                    Alert::warning($entity->responseCode . ' : ' . $entity->responseDescription)->persistent(true, false);
                    return redirect()->route('client-party.show', $id);
                    break;
                }

                if ((int)$entity->status == 400) {
                    $client_party_status = 0;
                    Log::warning(json_encode($entity));
                    Alert::warning(json_encode($entity->errors))->persistent(true, false);
                    return redirect()->route('client-party.show', $id);
                    break;
                }

                $entity_type_col =  (strtolower($entity_type) == 'vendor') ? 'Vendor' : 'Debtor';

                // update entity vendor / debtor
                DB::table('Cust_Entity')->where('Id', $id)->update([
                    ucfirst($entity_type_col) . 'Code' => $entity->responseCode,
                ]);
            }
                $client_party_status = 1;
            }

            // dd($request,'Stop');

            if ($request->partyEntityType != 'NoSCMIntergration') {
                $results = DB::select("SET NOCOUNT ON;
                                        Declare
                                        @RequestID as Uniqueidentifier,
                                        @Status as Varchar(30),
                                        @Remark as Varchar(1000)
                                            Exec [dbo].[sp_CreateDebtorandVendor]
                                        @RequestID = ?,
                                        @status= @status  OUTPUT,
                                        @Remark= @Remark  OUTPUT
                                            Select @Status as Status, @Remark as Remark", [
                    $id
                ]);
            }

            $client_party_status = 1;
            // Log::notice(json_encode($results));
        }

        // create entity on SCM
        if (isset($request->createBtn) && ($client_party_status == 1)) {
            if ($request->partyEntityType != 'NoSCMIntergration') {
                $results = $this->CreateClientPartySCM($id);
            } else {
                DB::table('Cust_Entity')->where('Id', $id)->update(['Status' => 'Created']);
                $rs = [['Status' => 'Success', 'Remark' => 'Debtor / Vendor Creatted In D365']];
                $results = json_decode(json_encode($rs), FALSE);
            }

            Log::notice(json_encode($results));

            if ($results[0]->Status != 'Failed') {
                alert()->success($results[0]->Status, $results[0]->Remark)->showConfirmButton();
            } else {
                alert()->error($results[0]->Status, $results[0]->Remark)->showConfirmButton();
            }
        } else {
            alert()->success('Success !', 'Entity Updated.')->showConfirmButton();
        }

        //redirect to the show page with the request ID
        return redirect()->route('client-party.show', [$id]);
    }

    public function preview(ClientParty $path){
        // dd($path->attachments);
        $attachment = $path->attachments;

        // dd($attachment[0]->FilePath);
        $filePath = $attachment[0]->FilePath;

        return Storage::disk('azure')->download($filePath, basename($attachment[0]->FilePath), [
            'Content-Type' => 'application/octet-stream',
            'Content-Disposition' => 'inline; filename="' . basename($attachment[0]->FilePath) . '"'
        ]);
    }

    public function CreateClientPartySCM($requestID)
    {

        $results = json_decode(json_encode([['Status' => 'Failed', 'Remark' => 'Entity Not Created']]));
        if (!Auth::user()->can('create-client-party-SCM')) {
            $results = json_decode(json_encode([['Status' => 'Failed', 'Remark' => 'Unauthorised SCM Access.']]));
            return $results;
        } //  dd($results[0]->Status); exit();
        try {
            //Execute the stored procedure
            $results = DB::select("SET NOCOUNT ON;
                                Declare
                                @RequestID as Uniqueidentifier,
                                @Status as Varchar(30),
                                @Remark as Varchar(1000),
                                @ClientCode as Varchar(60),
                                @ClientID as Uniqueidentifier,
                                @ShipperConsigneeCode as Varchar(60),
                                @ShipperConsigneeID as Uniqueidentifier,
                                @OtherCode as Varchar(60),
                                @OtherID as Uniqueidentifier
                                    Exec [dbo].[Sp_ClientandPartyCreation]
                                @RequestID = ?,
                                @status= @status  OUTPUT,
                                @Remark= @Remark  OUTPUT,
                                @ClientCode= @ClientCode  OUTPUT,
                                @ClientID= @ClientID OUTPUT,
                                @ShipperConsigneeCode= @ShipperConsigneeCode OUTPUT,
                                @ShipperConsigneeID= @ShipperConsigneeID  OUTPUT,
                                @OtherCode= @OtherCode  OUTPUT,
                                @OtherID= @OtherID  OUTPUT
                                Select @Status as Status, @Remark as Remark, @ClientCode as ClientCode,
                                @ClientID as ClientID, @ShipperConsigneeCode as ShipperConsigneeCode,
                                @ShipperConsigneeID as ShipperConsigneeID, @OtherCode as OtherCode,
                                @OtherID as OtherID", [$requestID]);

            DB::beginTransaction();
            DB::table('Cust_Entity')->where('Id', $requestID)->update(['Status' => 'Created']);
            DB::commit();
        } catch (\Exception $th) {
            Log::error($th->getMessage());
            DB::rollBack();
            $results = json_decode(json_encode([['Status' => 'Failed', 'Remark' => 'Entity Creation Failed.']]));
        }

        if ($results[0]->Status == 'Failed') {
            DB::beginTransaction();
            DB::table('Cust_Entity')->where('Id', $requestID)->update(['Status' => 'Submitted']);
            DB::commit();
        }
        // Log::info($results);
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

        //authorization #reject-entity-request
        if (!$user->can('create-client-party-SCM')) {
            alert()->warning('Warning !', 'Unauthorised.')->showConfirmButton();
            return response()->json(['false', 312]);
        }
        $id = \request()->get('id');
        $level = \request()->get('level');
        $reason = \request()->get('reason');

        $clientParty = DB::table('Cust_Entity')->where('Id', $id)->first();


        // todo: create history entity
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
            //send email
            $userEmail = User::where('LoginID', $clientParty->CreatedBy)->select('Email')->first();

            Mail::to($userEmail->Email)->send(new RejectEntity($clientParty));
        } catch (Exception $th) {
            dd($th);
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



        //  dd($clientParty);
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


        $status = ($status == 'PendingManagerApproval') ? 'PendingFinanceApproval' : 'Submitted';

        try {
            DB::table('Cust_Entity')->where('Id', $id)->update([
                "Status" => $status,
            ]);
        } catch (Exception $e) {
            alert()->warning('Entity', $e);
            exit();
        }

        // todo: create history entity
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
        //authorization #approve-entity-request
        if (!Auth::user()->can('approve-entity-request')) {
            alert()->warning('Warning !', 'You are not authorised to veiw this page. Contact Admin')->showConfirmButton();
            return redirect()->route('home');
        }

        return view('client-party.approve');
    }

    public function deleteFile($id)
    {
        //authorization #approve-entity-request
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

        // $file->delete();

        return response()->json(['ok', 200]);
    }
}
