@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')

@stop

@section('content')
<div class="row">
    <!-- Preloader -->
    <div class="preloader">
        <div class="preloader-inner">
            <div class="preloader-icon">
                <span></span>
                <span></span>
            </div>
        </div>
    </div>
    <!-- End Preloader -->
    <div class="col-md-12">
        <div class="card card-grey mt-2">
            {{-- <div class="card-header">
                <h1 class="card-title text-uppercase">Create Client Party Request</h1>
            </div> --}}
            <div class="card-body p-0">
                <div class="bs-stepper">
                    <div class="row justify-content-center">
                        <div class="bs-stepper-header col-md-10" role="tablist">
                            <!-- your steps here -->
                            <div class="step" data-target="#basic-details">
                                <button type="button" class="step-trigger" role="tab" aria-controls="basic-details"
                                    id="basic-details-trigger" aria-selected="true">
                                    <span class="bs-stepper-circle">1</span>
                                    <span class="bs-stepper-label">Basic Details</span>
                                </button>
                            </div>
                            <div class="line"></div>
                            <div class="step" data-target="#office-details">
                                <button type="button" class="step-trigger" role="tab" aria-controls="office-details"
                                    id="office-details-trigger" aria-selected="true">
                                    <span class="bs-stepper-circle">2</span>
                                    <span class="bs-stepper-label">Office Details</span>
                                </button>
                            </div>
                            @if (Auth::user()->can('create-client-party'))
                            <div class="line"></div>
                            <div class="step" data-target="#admin-details">
                                <button type="button" class="step-trigger" role="tab" aria-controls="office-details"
                                    id="admin-details-trigger" aria-selected="true">
                                    <span class="bs-stepper-circle">3</span>
                                    <span class="bs-stepper-label">Admin Details</span>
                                </button>
                            </div>
                            @endif
                            <div class="">
                                <a href="/client-party" class="bs-stepper-label btn btn-outline-primary">
                                    <i class="fa fa-home"></i> Home
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="bs-stepper-content container">
                        <form action="{{ route('client-party.update',[$clientParty->Id]) }}" method="POST" class="form-horizontal"
                            enctype="multipart/form-data" id="clientPartyForm">
                            @csrf
                            @method('put')
                            <!-- your steps content here -->
                            <div id="basic-details" class="content" role="tabpanel"
                                aria-labelledby="basic-details-trigger">
                                <fieldset class="border pl-2 pr-2 card shadow-sm m-0" style="border-radius: 10px;">
                                    <legend class="w-auto p-0 m-0">Entity Creation Options</legend>
                                    <div class="row">
                                        <div class="col col-md-6 col-sm-12">
                                            <div class="form-group row mb-1">
                                                <label for="partyEntityType"
                                                    class="col-sm-3 col-lg-4 col-md-4 col-form-label col-form-label-sm"
                                                    style="font-size: 17px">Operation Type<span
                                                        class="text-danger">*</span></label>
                                                <div class="col-sm-9 col-lg-8 col-md-8">
                                                    {{-- {{ dd($clientParty) }} --}}
                                                    <select class="form-control form-control-sm" id="partyEntityType"
                                                        name="partyEntityType" required>
                                                        <option value="" disabled selected></option>
                                                        <option value="Client + Shipper + Consignee"
                                                            @if(old('partyEntityType',$clientParty->OperatioEntityType)
                                                            == 'Client + Shipper + Consignee') selected @endif>Client +
                                                            Shipper + Consignee</option>
                                                        {{-- <option value="Client Only">Client Only</option> --}}
                                                        <option value="Shipper + Consignee"
                                                            @if(old('partyEntityType',$clientParty->OperatioEntityType)
                                                            == 'Shipper + Consignee') selected @endif>Shipper +
                                                            Consignee</option>
                                                        <option value="Shipper Only"
                                                            @if(old('partyEntityType',$clientParty->OperatioEntityType)
                                                            == 'Shipper Only') selected @endif>Shipper Only</option>
                                                        <option value="Consignee Only"
                                                            @if(old('partyEntityType',$clientParty->OperatioEntityType)
                                                            == 'Consignee Only') selected @endif>Consignee Only</option>
                                                        <option value="Forwarder"
                                                            @if(old('partyEntityType',$clientParty->OperatioEntityType)
                                                            == 'Forwarder') selected @endif>Agent / Forwarder</option>
                                                        <option value="Airline" @if(old('partyEntityType',$clientParty->
                                                            OperatioEntityType) == 'Airline') selected @endif>Airline
                                                        </option>
                                                        <option value="Shipping Line"
                                                            @if(old('partyEntityType',$clientParty->OperatioEntityType)
                                                            == 'Shipping Line') selected @endif>Shipping Line</option>
                                                        <option value="Trucker" @if(old('partyEntityType',$clientParty->
                                                            OperatioEntityType) == 'Trucker') selected @endif>Trucker /
                                                            Transportor</option>
                                                        <option value="NoSCMIntergration" @if(old('partyEntityType',
                                                            $clientParty->OperatioEntityType)=='NoSCMIntergration' )
                                                            selected @endif>Debtor/Vendor In D365</option>

                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col col-md-6 col-sm-12">
                                            <div class="form-group row mb-1">
                                                <label for="legalEntityType"
                                                    class="col-sm-3 col-lg-4 col-md-4 col-form-label col-form-label-sm"
                                                    style="font-size: 17px">Legal Type <span
                                                        class="text-danger">*</span></label>
                                                <div class="col-sm-9 col-lg-8 col-md-8">
                                                    <select class="form-control form-control-sm" id="legalEntityType"
                                                        name="legalEntityType">
                                                        <option value="" disabled selected></option>
                                                        <option value="Debtor & Vendor"
                                                            @if(old('legalEntityType',$clientParty->LegalEntityType) ==
                                                            'Debtor & Vendor') selected @endif>Debtor & Vendor</option>
                                                        <option value="Debtor" @if(old('legalEntityType',$clientParty->
                                                            LegalEntityType) == 'Debtor') selected @endif>Debtor
                                                        </option>
                                                        <option value="Vendor" @if(old('legalEntityType',$clientParty->
                                                            LegalEntityType) == 'Vendor') selected @endif>Vendor
                                                        </option>
                                                        {{-- <option value="None"
                                                            @if(old('legalEntityType',$clientParty->LegalEntityType) ==
                                                            'None') selected @endif>None</option> --}}
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </fieldset>
                                <div class="row">
                                    <div class="col-md-6 col-sm-12">
                                        <fieldset class="border p-2 card shadow-lg mb-0 pt-0">
                                            <legend class="w-auto p-0 m-0">Basic Information</legend>
                                            {{-- Company Name --}}
                                            <div class="form-group row mb-1">
                                                <label for="companyName"
                                                    class="col-sm-3 col-lg-4 col-md-4 col-form-label col-form-label-sm"><i
                                                        class="fa fa-building" aria-hidden="true"></i>Company Name<span
                                                        class="text-danger">*</span></label>
                                                <div class="col-sm-9 col-lg-8 col-md-8">
                                                    <input type="text" class="form-control form-control-sm"
                                                        value="{{ old('companyName',$clientParty->Name) }}"
                                                        name="companyName" id="companyName">
                                                </div>
                                            </div>
                                            {{-- AddressLine 1 --}}
                                            <div class="form-group row mb-1">
                                                <label for="addressLine1"
                                                    class="col-sm-3 col-lg-4 col-form-label col-form-label-sm"><i
                                                        class="fa fa-map-marker" aria-hidden="true"></i> AddressLine 1
                                                    <span class="text-danger">*</span></label>
                                                <div class="col-sm-9 col-lg-8">
                                                    <textarea name="addressLine1" id="addressLine1"
                                                        class="form-control form-control-sm">{{ old('addressLine1',$clientParty->Address_Line1) }}</textarea>
                                                </div>
                                            </div>
                                            {{-- AddressLine 2 --}}
                                            <div class="form-group row mb-1">
                                                <label for="addressLine2"
                                                    class="col-sm-3 col-lg-4 col-form-label col-form-label-sm"><i
                                                        class="fa fa-map-marker" aria-hidden="true"></i> AddressLine
                                                    2</label>
                                                <div class="col-sm-9 col-lg-8">
                                                    <textarea name="addressLine2" id="addressLine2"
                                                        class="form-control form-control-sm">{{ old('addressLine2',$clientParty->Address_Line2) }}</textarea>
                                                </div>
                                            </div>
                                            {{-- Country Code and Country Name --}}
                                            <div class="form-group row mb-1">
                                                <label for="country"
                                                    class="col-sm-3 col-lg-4 col-form-label col-form-label-sm"><i
                                                        class="fa fa-globe" aria-hidden="true"></i> Country <span
                                                        class="text-danger">*</span></label>
                                                <div class="col-sm-8 col-lg-8">
                                                    {{-- <select class="form-control form-control-sm editable-select"
                                                        name="country" id="country">
                                                        <option value="" disabled>Type Country</option>
                                                    </select> --}}
                                                    <label class="sr-only" for="country">Country</label>
                                                    <div class="form-group">
                                                        {{-- <div class="col-sm-5 col-lg-4"> --}}
                                                            {{-- <input type="text" class="form-control form-control-sm"
                                                                name="city" id="city" placeholder="City"> --}}
                                                            {{-- {{ dd($clientParty) }} --}}
                                                            <select class="form-control form-control-sm"
                                                                name="countryID" id="countryID">
                                                                <option value=""></option>
                                                                @forelse ($countries as $country)
                                                                <option
                                                                    value="{{ old('countryID',$country->CountryID) }}"
                                                                    id="{{ $country->CountCode }}" @if (old('countryID',
                                                                    $clientParty->Address_CountryId) ==
                                                                    $country->CountryID) selected @endif >{{
                                                                    $country->CountryName }}</option>
                                                                @empty

                                                                @endforelse
                                                            </select>
                                                            {{--
                                                        </div> --}}
                                                        {{-- <input type="text" class="form-control form-control-sm"
                                                            id="country" placeholder="Country"> --}}
                                                        <input type="hidden" name="countryName" id="countryName"
                                                            value="{{ old('countryName',$clientParty->Address_CountryName) }}">
                                                        <input type="hidden" name="countryCode" id="countryCode"
                                                            value="{{ old('countryCode',$clientParty->Address_CountryCode) }}">
                                                        {{-- <div class="input-group-prepend">
                                                            <div class="input-group-text"><i class="fa fa-search fa-sm"
                                                                    aria-hidden="true"></i> </div>
                                                        </div> --}}
                                                    </div>
                                                    {{-- <ul class="searchResult" id="countryResult"></ul> --}}
                                                </div>
                                                {{-- <div class="col-sm-5 col-lg-4">
                                                    {{-- <input type="text" class="form-control form-control-sm"
                                                        name="city" id="city" placeholder="City">
                                                    <select class="form-control form-control-sm" name="city" id="city">
                                                        <option value="" disabled>Type Country</option>
                                                    </select>
                                                </div> --}}
                                            </div>
                                            {{-- Province zipcode --}}
                                            <div class="form-group row mb-1 ">
                                                <label for="province"
                                                    class="col-sm-3 col-lg-4 col-form-label col-form-label-sm"><i
                                                        class="fa fa-map-pin" aria-hidden="true"></i> Province &
                                                    Zipcode</label>
                                                <div class="col-sm-4 col-lg-4">
                                                    <input type="text" class="form-control form-control-sm "
                                                        name="provinceName" id="provinceInput"
                                                        value="{{ old('provinceName', $clientParty->Address_State) }}"
                                                        placeholder="S / P">
                                                    <input type="hidden" name="provinceID" id="provinceID"
                                                        value="{{ old('provinceID', $clientParty->Address_StateId) }}">
                                                    <input type="hidden" name="provinceCode" id="provinceCode"
                                                        value="{{ old('provinceCode', $clientParty->Address_StateCode) }}">
                                                    {{-- <select class="form-control form-control-sm" name="province"
                                                        id="provinceSelect">
                                                        <option value="" disabled>Enter Province</option>
                                                    </select> --}}
                                                </div>
                                                <div class="col-sm-4 col-lg-4">
                                                    <input type="text" class="form-control form-control-sm"
                                                        value="{{ old('zipCode',$clientParty->Address_Pin) }}"
                                                        name="zipCode" id="zipCode" placeholder="Zip Code">
                                                </div>
                                            </div>
                                            {{-- City {{ dd($clientParty) }} --}}
                                            <div class="form-group row mb-1">
                                                <label for="cityName"
                                                    class="col-sm-3 col-lg-4 col-form-label col-form-label-sm"><i
                                                        class="fas fa-city"></i> City</label>
                                                <div class="col-sm-9 col-lg-8">
                                                    <input type="text" class="form-control form-control-sm"
                                                        value="{{ old('cityName',$clientParty->Address_City) }}"
                                                        name="cityName" id="cityInput">
                                                    {{-- <select class="form-control form-control-sm" name="cityID"
                                                        id="citySelect">
                                                        <option value="" disabled selected>Select City</option>
                                                    </select> --}}
                                                    {{-- <input type="hidden" name="cityName" id="cityName"
                                                        value="{{ old('cityName',$clientParty->Address_City) }}"> --}}
                                                    {{-- <input type="hidden" name="cityCode" id="cityCode"
                                                        value="{{ old('cityCode',$clientParty->Address_CityCode) }}">
                                                    --}}
                                                </div>
                                            </div>

                                        </fieldset>
                                    </div>
                                    <div class="col-md-6 col-sm-12">
                                        <fieldset class="border p-2 card shadow-lg mb-0 pt-0">
                                            <legend class="w-auto p-0 m-0">Contact Details</legend>
                                            <div class="form-group row mb-1">
                                                <label for="person"
                                                    class="col-sm-3  col-md-4 col-lg-4 col-form-label col-form-label-sm">
                                                    <i class="fa fa-user" aria-hidden="true"></i> Person <span
                                                        style="color:red">*</span></label>
                                                {{-- <div class="col-sm-2 col-md-3 col-lg-2">
                                                    <input type="text" class="form-control form-control-sm"
                                                        name="namePrefix" id="namePrefix" placeholder="Prefix">
                                                </div> --}}
                                                <div class="col-sm-4 col-md-4 col-lg-4">
                                                    <input type="text" class="form-control form-control-sm"
                                                        value="{{ old('firstName',$clientParty->Cont_Bch_PersonName_FirstName) }}"
                                                        name="firstName" id="firstName" placeholder="First Name">
                                                </div>
                                                <div class="col-sm-5 col-md-4 col-lg-4">
                                                    <input type="text" class="form-control form-control-sm"
                                                        value="{{ old('lastName',$clientParty->Cont_Bch_PersonName_LastName) }}"
                                                        name="lastName" id="lastName" placeholder="Last Name">
                                                </div>
                                            </div>
                                            <div class="form-group row mb-1">
                                                <label for="department"
                                                    class="col-sm-3 col-lg-4 col-form-label col-form-label-sm"><i
                                                        class="fa fa-sitemap" aria-hidden="true"></i> Department</label>
                                                <div class="col-sm-9 col-lg-8">
                                                    <input type="text" class="form-control form-control-sm"
                                                        value="{{ old('department',$clientParty->Cont_Bch_Dept) }}"
                                                        name="department" id="department" placeholder="Department">
                                                </div>
                                            </div>
                                            {{-- phone no --}}
                                            <div class="form-group row mb-1">
                                                <label for="phoneNo"
                                                    class="col col-sm-3  col-md-3 col-lg-4 col-form-label col-form-label-sm">
                                                    <i class="fa fa-phone" aria-hidden="true"></i> Phone No. <span
                                                        style="color:red">*</span></label>
                                                <div class="col col-sm-9 col-md-9 col-lg-8">
                                                    <input type="text" class="form-control form-control-sm number_input"
                                                        value="{{ old('phoneNo',$clientParty->Cont_Bch_PhoneNumbers) }}"
                                                        name="phoneNo" id="phoneNo" placeholder="Phone Number">
                                                </div>
                                            </div>
                                            {{-- email --}}
                                            <div class="form-group row mb-1">
                                                <label for="contractEmail"
                                                    class="col-sm-3 col-lg-4 col-form-label col-form-label-sm"><i
                                                        class="fa fa-envelope" aria-hidden="true"></i> Email</label>
                                                <div class="col-sm-9 col-lg-8">
                                                    <input type="email" name="contractEmail" id="contractEmail"
                                                        value="{{ old('contractEmail',$clientParty->Cont_Bch_Email) }}"
                                                        class="form-control form-control-sm" placeholder="Enter Email">
                                                </div>
                                            </div>
                                        </fieldset>
                                        <fieldset class="border p-2 card shadow-lg mb-0">
                                            <legend class="w-auto p-0 m-0">Other information</legend>
                                            {{-- Client --}}
                                            <div class="form-group row mb-1">
                                                <label for="client"
                                                    class="col-sm-3 col-lg-4 col-form-label col-form-label-sm"><i
                                                        class="fa fa-user" aria-hidden="true"></i> Client <span
                                                        class="text-danger" id="clientSpan">*</span></label>
                                                <div class="col-sm-9 col-lg-8">
                                                    <div class="row">
                                                        <div class="col"><select name="clientOption" id="clientOption"
                                                                class="form-control form-control-sm" disabled>
                                                                <option value="fromMaster" @isset($clientParty->
                                                                    ClientName) selected @endisset>Search Client
                                                                </option>
                                                                <option value="fromRequest" @isset($clientParty->
                                                                    RequestId) selected @endisset>From Request</option>
                                                            </select></div>
                                                        <div class="col">
                                                            @isset($clientParty->RequestId)
                                                            <input type="text" name="clientOptionVal"
                                                                id="clientOptionVal"
                                                                value="{{ old('clientOptionVal', $clientParty->RequestName) }}"
                                                                class="form-control form-control-sm"
                                                                @isset($request->RequestId) @else disabled @endisset>
                                                            <input type="hidden" name="requestID"
                                                                value="{{ old('requestID', $clientParty->RequestId) }}"
                                                                id="requestID">
                                                            <input type="hidden" name="requestCode"
                                                                value="{{ old('requestCode', $clientParty->RequestCode) }}"
                                                                id="requestCode">
                                                            @else
                                                            <input type="text" name="client" id="client"
                                                                value="{{ old('client', $clientParty->ClientName) }}"
                                                                class="form-control form-control-sm"
                                                                @isset($request->ClientName) @else disabled @endisset >
                                                            @endisset
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group row mb-1">
                                                <label for="specialInstructions"
                                                    class="col-sm-3 col-lg-4 col-form-label col-form-label-sm"><i
                                                        class="fas fa-comments"></i> Special
                                                    Instructions</label>
                                                <div class="col-sm-9 col-lg-8">
                                                    <textarea class="form-control form-control-sm"
                                                        name="specialInstruction" id="specialInstruction"
                                                        placeholder="Special Instructions">{{ old('specialInstruction', $clientParty->SpecialInstructions) }}</textarea>
                                                </div>
                                            </div>
                                        </fieldset>
                                    </div>
                                </div>
                                <button class="btn btn-primary m-2 btn-sm float-right" name="next" type="button"
                                    onclick="stepper.next()">Next <i class="fa fa-step-forward"
                                        aria-hidden="true"></i></button>
                                <button type="button" name="rejectBtn" id="rejectBtn"
                                            data-toggle="modal" data-target="#cancelModal"
                                            class="btn btn-danger float-right m-2 btn-sm" data-toggle="tooltip"
                                            data-placement="top" title="Reject For Amendment" data-bs-original-title="Update">
                                            <i class="fa fa-edit" aria-hidden="true"></i> Reject </button>
                                <button type="button" name="updateBtn" id="save-step1"
                                    class="btn btn-success btn-sm float-right m-2" data-toggle="tooltip"
                                    data-placement="top" title="" data-bs-original-title="Update"><i class="fa fa-edit"
                                        aria-hidden="true"></i> Update for Later</button>
                            </div>
                            <div id="office-details" class="content active dstepper-block" role="tabpanel"
                                aria-labelledby="office-details-trigger">
                                <div class="row">
                                    <div class="col-md-4 col-lg-4 col-sm-12">
                                        <fieldset class="border p-2 card shadow-sm mb-0 pt-0 pb-0">
                                            <legend class="w-auto p-0 m-0">Office Mapping</legend>
                                            <div class="form-group row m-0">
                                                <label for="officeCountry"
                                                    class="col-sm-6 col-lg-4 col-md-4 col-form-label col-form-label-sm">Office
                                                    <span class="text-danger">*</span></label>
                                                <div class="col-sm-6 col-lg-8 col-md-8">
                                                    <input type="hidden" name="officeCountryDisp"
                                                        code="{{ $clientParty->OfficeCode }}" id="officeCountryDisp"
                                                        value="{{ $clientParty->OfficeCode }}">
                                                    <select class="form-control form-control-sm" name="officeCountry"
                                                        id="officeCountry" required @if ($clientParty->Status ==
                                                        "Submitted") disabled @endif>
                                                        <option value="" disabled selected></option>
                                                        @forelse ($offices as $office)
                                                        <option code="{{ $office->Code }}" value="{{ $office->Name }}"
                                                            id="{{ $office->ID }}" @if ($office->ID ==
                                                            (count($clientParty->getSCMOfficeID($clientParty->OfficeID
                                                            ?? Auth::user()->office->SCMOfficeID)) < 1 ? ($clientParty->
                                                                OfficeID ?? Auth::user()->office->SCMOfficeID) :
                                                                $clientParty->getSCMOfficeID($clientParty->OfficeID ??
                                                                Auth::user()->office->SCMOfficeID)[0]->SCMOfficeID))
                                                                selected @endif >{{ $office->Name }}</option>
                                                        @empty
                                                        @endforelse
                                                    </select>
                                                    @if (!Auth::user()->can('create-client-for-any-office') ||
                                                    ($clientParty->Status == 'Submitted'))
                                                    <input type="hidden" name="officeCountry"
                                                        code="{{ $clientParty->OfficeCode }}"
                                                        value="{{ old('officeCountry', $clientParty->Office) }}">
                                                    <input type="hidden" name="officeCode"
                                                        value='{{ $clientParty->OfficeCode }}'>
                                                    @endif
                                                    <input type="hidden" name="officeCountryID" id="officeCountryID"
                                                        value="{{ old('officeCountryID', $clientParty->OfficeID) }}">
                                                </div>
                                                {{-- {{ dd($clientParty) }} --}}
                                            </div>
                                        </fieldset>
                                        <fieldset class="border p-2 card shadow-lg mb-0 pt-0 pb-0">
                                            <legend class="w-auto pb-0 mb-0">Prefered Ports</legend>
                                            <div class="row pt-0 mt-0 justify-content-center">
                                                <div class="col-md-8 col-sm-8 col-lg-8 ">
                                                    <div class="form-check">
                                                        <label class="form-check-label" for="includeCountryCheckBox">
                                                            Include Country
                                                        </label>
                                                        <input class="form-check-input ml-3" type="checkbox"
                                                            name="includeCountryCheckBox" id="includeCountryCheckBox">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6 col-sm-12 col-lg-6">
                                                    {{-- port of loading Air --}}
                                                    <div class="form-group  pt-0">
                                                        <div class="">
                                                            <label for="portOfLoadingAir"
                                                                class="col-form-label col-form-label-sm">Port Of
                                                                Loading(Air) </label>
                                                            <input type="text" class="form-control form-control-sm"
                                                                value="{{ old('portOfLoadingAir',$clientParty->AirUsualPortOfLoadingName) }}"
                                                                name="portOfLoadingAir" id="portOfLoadingAir">
                                                            <input type="hidden" name="portOfLoadingAirID"
                                                                id="portOfLoadingAirID"
                                                                value="{{ old('portOfLoadingAirID',$clientParty->AirUsualPortOfLoading) }}">
                                                            {{-- <select class="form-control form-control-sm"
                                                                name="portOfLoadingAir" id="portOfLoadingAir">
                                                                <option value="" disabled selected></option>
                                                            </select> --}}
                                                        </div>
                                                    </div>
                                                    {{-- port of Discharge Air --}}
                                                    <div class="form-group ">
                                                        <div class="">
                                                            <label for="portOfDischargeAir"
                                                                class="col-form-label col-form-label-sm">Port Of
                                                                Discharge(Air)</label>
                                                            <input type="text" class="form-control form-control-sm"
                                                                value="{{ old('portOfDischargeAir',$clientParty->AirUsualPortofDischargeName) }}"
                                                                name="portOfDischargeAir" id="portOfDischargeAir">
                                                            <input type="hidden" class="form-control form-control-sm"
                                                                value="{{ old('portOfDischargeAirID', $clientParty->AirUsualPortofDischarge) }}"
                                                                name="portOfDischargeAirID" id="portOfDischargeAirID">
                                                            {{-- <select class="form-control form-control-sm"
                                                                name="portOfDischargeAir" id="portOfDischargeAir">
                                                                <option value="" disabled selected></option>
                                                            </select> --}}
                                                        </div>
                                                    </div>

                                                </div>
                                                <div class="col-md-6 col-sm-12 col-lg-6">
                                                    {{-- port of loading sea --}}
                                                    <div class="form-group row mb-1">
                                                        <div class="col-md-12">
                                                            <label for="portOfLoadingSea"
                                                                class="col-form-label col-form-label-sm">Port Of
                                                                Loading(Sea) </label>
                                                            <input type="text" class="form-control form-control-sm"
                                                                value="{{ old('portOfLoadingSea',$clientParty->SeaUsualPortOfLoadingName) }}"
                                                                name="portOfLoadingSea" id="portOfLoadingSea">
                                                            <input type="hidden" class="form-control form-control-sm"
                                                                value="{{ old('portOfLoadingSeaID',$clientParty->SeaUsualPortOfLoading) }}"
                                                                name="portOfLoadingSeaID" id="portOfLoadingSeaID">
                                                            {{-- <select class="form-control form-control-sm"
                                                                name="portOfLoadingSea" id="portOfLoadingSea">
                                                                <option value="" disabled selected></option>
                                                            </select> --}}
                                                        </div>
                                                    </div>
                                                    {{-- port of Discharge Sea --}}
                                                    <div class="form-group row mb-1">
                                                        <div class="col-md-12">
                                                            <label for="portOfDischargeSea"
                                                                class="col-form-label col-form-label-sm">Port Of
                                                                Discharge(Sea)</label>
                                                            <input type="text" class="form-control form-control-sm"
                                                                value="{{ old('portOfDischargeSea',$clientParty->SeaUsualPortofDischargeName) }}"
                                                                name="portOfDischargeSea" id="portOfDischargeSea">
                                                            <input type="hidden" class="form-control form-control-sm"
                                                                value="{{ old('portOfDischargeSeaID',$clientParty->SeaUsualPortofDischarge) }}"
                                                                name="portOfDischargeSeaID" id="portOfDischargeSeaID">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </fieldset>
                                       <fieldset class="border p-2 card shadow-lg mb-0 pt-0 pb-0">
                                            <legend>File Upload</legend>
                                            @if (count($clientParty->attachments) < 3)
                                                <label for="attachmentFile">Attachment<span class="text-danger">*</span> <i class="fa fa-paperclip"></i>
                                                    <button type="button" class="btn btn-sm btn-outline-warning pull-right w-20 addAttachment" id="addFile">
                                                        <i class="fa fa-plus"></i> File
                                                    </button>
                                                </label>
                                                <div class="input-group hdtuto control-group lst increment">
                                                    <input type="file" name="attachmentNames[]" class="myfrm form-control mr-1 form-control-sm">
                                                    <input type="text" name="attachmentDescription[]" class="myfrm form-control form-control-sm"
                                                        placeholder="Description">
                                                </div>
                                                <div id="attachClone"></div>
                                                <div class="clone d-none">
                                                    <div class="hdtuto control-group lst input-group mt-1" style="">
                                                        <input type="file" name="attachmentNames[]" class="myfrm form-control mr-1 form-control-sm">
                                                        <input type="text" name="attachmentDescription[]" class="myfrm form-control form-control-sm"
                                                            placeholder="Description">
                                                        <div class="input-group-btn">
                                                            <button class="btn btn-danger btn-sm" type="button" style="">
                                                                <i class="fldemo fa fa-minus"></i> </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                            @if (count($clientParty->attachments) > 0)
                                                <ul>
                                                    {{-- <a href="{{ route('client-party.preview',[$attachment->Id]) }}"></a> --}}
                                                    {{-- {{ route('client-party.preview',[$attachment->Id]) }} --}}
                                                    @foreach ($clientParty->attachments as $attachment)
                                                        <li><a href="{{ route('client-party.preview',[$attachment->CustEntityID]) }}">{{ $attachment->FileName }} | {{ $attachment->Title }}</a> <a href="#" class="deleteFile" id="{{ $attachment->ID }}"><i class="fa fa-times-circle text-danger"></i></a></li>
                                                    @endforeach

                                                    {{-- @foreach ($clientParty as $client)
                                                        <li><a href="{{ route('client-party.preview', [$client->Id]) }}">{{ $client->attachments->FileName }} | {{ $client->attachments->Title }}</a> <a href="#" class="deleteFile" id="{{ $attachment->ID }}"><i class="fa fa-times-circle text-danger"></i></a></li>
                                                    @endforeach --}}
                                                </ul>
                                            @endif
                                        </fieldset>
                                    </div>
                                    <div class="col-md-8 col-lg-8 col-sm-12">
                                        <fieldset class="border p-2 card shadow-lg mb-0 pt-0">
                                            <legend class="w-auto p-0 m-0">Client</legend>
                                            <div class="row">
                                                {{-- first detail client col --}}
                                                <div class="col-md-6 col-sm-12 col-lg-6">
                                                    {{-- Sales Pic --}}
                                                    <div class="form-group row mb-1">
                                                        <label for="salesPIC"
                                                            class="col-sm-3 col-lg-4 col-form-label col-form-label-sm">Sales
                                                            PIC <span class="text-danger">*</span></label>
                                                        <div class="col-sm-9 col-lg-8">
                                                            <input type="text" class="form-control form-control-sm"
                                                                value="{{ old('salesPIC', $clientParty->SalesMgrOfficeFunMapName) }}"
                                                                name="salesPIC" id="salesPIC" placeholder="">
                                                            <input type="hidden" name="salesPICID" id="salesPICID"
                                                                value="{{ old('salesPICID', $clientParty->SalesMgrOfficeFunMapId) }}">
                                                            {{-- <select class="form-control form-control-sm"
                                                                name="salesPIC" id="salesPIC" required>
                                                                <option value="" disabled selected></option>
                                                            </select> --}}
                                                        </div>
                                                    </div>
                                                    {{-- Inco Terms --}}
                                                    <div class="form-group row mb-1">
                                                        <label for="incoTermID"
                                                            class="col-sm-3 col-lg-4 col-form-label col-form-label-sm">Inco
                                                            Terms</label>
                                                        <div class="col-sm-9 col-lg-8">
                                                            <select class="form-control form-control-sm"
                                                                name="incoTermID" id="incoTermID">
                                                                <option value="" disabled selected></option>
                                                                @forelse ($incoterms as $incoterm)
                                                                <option
                                                                    value="{{ old('incoTermID', $incoterm->IncoTermID)  }}"
                                                                    id="{{ old('incoTermCode', $incoterm->IncoTermCode)  }}"
                                                                    @if(old('incoTermID', $clientParty->IncoTermID) ==
                                                                    $incoterm->IncoTermID) selected @endif>{{
                                                                    $incoterm->Name }}</option>
                                                                @empty

                                                                @endforelse
                                                            </select>
                                                            <input type="hidden" name="incoTermName" id="incoTermName"
                                                                value="{{ old('incoTermName',$clientParty->IncoTermName) }}">
                                                            <input type="hidden" name="incoTermCode" id="incoTermCode"
                                                                value="{{ old('incoTermCode',$clientParty->IncoTermCode) }}">
                                                        </div>
                                                    </div>
                                                    {{-- Notify party 1 --}}
                                                    <div class="form-group row mb-1">
                                                        <label for="notifyParty1"
                                                            class="col-sm-3 col-lg-4 col-form-label col-form-label-sm">Notify
                                                            Party 1</label>
                                                        <div class="col-sm-9 col-lg-8">
                                                            <input type="text" class="form-control form-control-sm"
                                                                value="{{ old('notifyParty1', $clientParty->DefaultNotifyPartyName) }}"
                                                                name="notifyParty1" id="notifyParty1" placeholder="">
                                                            <input type="hidden" name="notifyParty1ID"
                                                                value="{{ old('notifyParty1ID', $clientParty->DefaultNotifyPartyId) }}"
                                                                id="notifyParty1ID">
                                                            <input type="hidden" name="notifyParty1Code"
                                                                value="{{ old('notifyParty1Code', $clientParty->DefaultNotifyPartyCode) }}"
                                                                id="notifyParty1Code">
                                                            {{-- <select class="form-control form-control-sm"
                                                                name="notifyParty1" id="notifyParty1">
                                                                <option value="" disabled selected></option>
                                                            </select> --}}
                                                        </div>
                                                    </div>
                                                </div>
                                                <!-- /.end col 1 office details -->
                                                {{-- second detail client col --}}
                                                <div class="col-md-6 col-sm-12 col-lg-6 pt-0">
                                                    {{-- Account Pic --}}
                                                    <div class="form-group row mb-1">
                                                        <label for="accountPIC"
                                                            class="col-sm-3 col-lg-4 col-form-label col-form-label-sm">Account
                                                            PIC</label>
                                                        <div class="col-sm-9 col-lg-8">
                                                            <input type="text" class="form-control form-control-sm"
                                                                value="{{ old('accountPIC', $clientParty->AccountMgrOfficeFunMapName) }}"
                                                                name="accountPIC" id="accountPIC" placeholder="">
                                                            <input type="hidden" name="accountPICID" id="accountPICID"
                                                                value="{{ old('accountPICID',$clientParty->AccountMgrOfficeFunMapId) }}">
                                                            {{-- <select class="form-control form-control-sm"
                                                                name="accountPIC" id="accountPIC">
                                                                <option value="" disabled selected></option>
                                                            </select> --}}
                                                        </div>
                                                    </div>
                                                    {{-- payment Terms --}}
                                                    <div class="form-group row mb-1">
                                                        <label for="paymentTermsID"
                                                            class="col-sm-3 col-lg-4 col-form-label col-form-label-sm pt-0 mt-0">Pay
                                                            Terms</label>
                                                        <div class="col-sm-9 col-lg-8 ">
                                                            <select class="form-control form-control-sm"
                                                                name="paymentTermsID" id="paymentTermsID">
                                                                <option value="" disabled selected></option>
                                                                @forelse ($payTerms as $payTerm)
                                                                <option
                                                                    value="{{ old('paymentTermsID', $payTerm->IncoTermID)  }}"
                                                                    id="{{ $payTerm->IncoTermCode }}"
                                                                    @if(old('paymentTermsID', $payTerm->IncoTermID)
                                                                    ==$clientParty->PaymentTermsID) selected @endif>{{
                                                                    $payTerm->Name }}</option>
                                                                @empty

                                                                @endforelse
                                                            </select>
                                                            <input type="hidden" name="paymentTermsName"
                                                                value="{{ old('paymentTermsName', $clientParty->PaymentTermsName)  }}"
                                                                id="paymentTermsName">
                                                            <input type="hidden" name="paymentTermsCode"
                                                                id="paymentTermsCode"
                                                                value="{{ old('paymentTermsCode', $clientParty->PaymentTermsCode)  }}">
                                                        </div>
                                                    </div>
                                                    {{-- Notify party 2 --}}
                                                    <div class="form-group row mb-1">
                                                        <label for="notifyParty2"
                                                            class="col-sm-3 col-lg-4 col-form-label col-form-label-sm">Notify
                                                            Party 2</label>
                                                        <div class="col-sm-9 col-lg-8">
                                                            <input type="text" class="form-control form-control-sm"
                                                                value="{{ old('notifyParty2', $clientParty->DefaultNotifyParty1Name) }}"
                                                                name="notifyParty2" id="notifyParty2" placeholder="">
                                                            <input type="hidden" name="notifyParty2ID"
                                                                value="{{ old('notifyParty2ID', $clientParty->DefaultNotifyParty1Id) }}"
                                                                id="notifyParty2ID">
                                                            <input type="hidden" name="notifyParty2Code"
                                                                value="{{ old('notifyParty2Code', $clientParty->DefaultNotifyParty1Code) }}"
                                                                id="notifyParty2Code">
                                                            {{-- <select class="form-control form-control-sm"
                                                                name="notifyParty2" id="notifyParty2">
                                                                <option value="" disabled selected></option>
                                                            </select> --}}
                                                        </div>
                                                    </div>
                                                </div>
                                                <!-- /.end col 2 office details -->
                                            </div>
                                            <!-- row 1 office details -->
                                            <div class="">
                                                {{-- Forwarder --}}
                                                <div class="form-group row mb-1">
                                                    <label for="forwarderName"
                                                        class="col-sm-3 col-md-4 col-lg-2 col-form-label col-form-label-sm">Forwarder</label>
                                                    <div class="col-sm-9 col-lg-10 col-md-8">
                                                        <input type="text" class="form-control form-control-sm"
                                                            name="forwarderName" id="forwarderName"
                                                            value="{{ old('forwarderName', $clientParty->DefaultFowarderName) }}">
                                                        <input type="hidden" class="form-control form-control-sm"
                                                            name="forwarderID" id="forwarderID"
                                                            value="{{ old('forwarderID', $clientParty->DefaultFowarderId) }}">
                                                        {{-- <select class="form-control form-control-sm"
                                                            name="forwarder" id="forwarder">
                                                            <option value="" disabled selected></option>
                                                        </select> --}}
                                                    </div>
                                                </div>
                                            </div>
                                        </fieldset>
                                        <fieldset class="border p-2 card shadow-lg mb-0 pt-0 ">
                                            <legend class="w-auto p-0 m-0">Debtor/Vendor</legend>
                                            <div class="row">
                                                <div class="col-md-6 col-sm-12 col-lg-6">
                                                    {{-- Debtor Type --}}
                                                    <div class="form-group row mb-1">
                                                        <div class="col-md-12">
                                                            <label for="debtorType"
                                                                class="col-form-label col-form-label-sm">Debtor
                                                                Type</label>
                                                            <select class="form-control form-control-sm"
                                                                name="debtorType" id="debtorType">
                                                                <option value="" disabled selected></option>
                                                                {{-- <option value="Cash Customer"
                                                                    @if(old('debtorType')=='Cash Customer' ) selected
                                                                    @endif>Cash Customer</option>
                                                                <option value="Approved Debtor"
                                                                    @if(old('debtorType')=='Approved Debtor' ) selected
                                                                    @endif>Approved Debtor</option> --}}
                                                            </select>
                                                        </div>
                                                    </div>
                                                    {{-- Billing Currency --}}
                                                    {{-- {{ dd($clientParty->BillingCurrencyId, $currencyList) }} --}}
                                                    <div class=" row mb-0 pb-0">
                                                        <div class="form-group col-md-6 mb-0 pb-0">
                                                            <label for="billingCurrency"
                                                                class="col-form-label col-form-label-sm">Billing
                                                                Currency <span class="text-danger">*</span></label>
                                                            <input type="hidden" class="form-control form-control-sm"
                                                                value="{{ old('billingCurrencyCode', $clientParty->BillingCurrencyCode) }}"
                                                                name="billingCurrencyCode" id="billingCurrencyCode">
                                                            {{-- {{ dd($clientParty->BillingCurrencyId,$currencyList) }}
                                                            --}}
                                                            <select class="form-control form-control-sm"
                                                                name="billingCurrency" id="billingCurrency">
                                                                <option value="" disabled selected></option>
                                                                @forelse ($currencyList as $item)
                                                                <option value="{{ $item->CurrencyID }}"
                                                                    id="{{ $item->CurrencyCode }}"
                                                                    @if(old('billingCurrency', $clientParty->
                                                                    BillingCurrencyId) == $item->CurrencyID) selected
                                                                    @endif>{{ $item->CurrencyCode }}</option>
                                                                @empty
                                                                <option value="" disabled selected></option>
                                                                @endforelse
                                                            </select>
                                                        </div>
                                                        {{-- Credit Days --}}
                                                        <div class="form-group col-md-6 mb-0 pb-0">
                                                            <label for="creditDays"
                                                                class="col-form-label col-form-label-sm">Credit
                                                                Days<span class="text-danger">*</span></label>
                                                            <input type="text"
                                                                class="form-control form-control-sm number_input"
                                                                value="{{ old('creditDays', $clientParty->CreditDays) }}"
                                                                name="creditDays" id="creditDays">
                                                        </div>
                                                    </div>

                                                </div>
                                                <div class="col-md-6 col-sm-12 col-lg-6">
                                                    {{-- Debtor Type --}}
                                                    <div class="form-group row mb-1">
                                                        <div class="col-md-12">
                                                            <label for="vendorType"
                                                                class="col-form-label col-form-label-sm">Vendor
                                                                Type</label>
                                                            <select class="form-control form-control-sm"
                                                                name="vendorType" id="vendorType">
                                                                <option value="" disabled selected></option>
                                                                {{-- <option value="Cash Customer" @if(old('vendorType',
                                                                    $clientParty->vendorType) == 'Cash Customer')
                                                                    selected @endif>Cash Customer</option>
                                                                <option value="Approved Debtor" @if(old('vendorType',
                                                                    $clientParty->VendorType) == 'Approved Debtor')
                                                                    selected @endif>Approved Debtor</option> --}}
                                                            </select>
                                                        </div>
                                                    </div>

                                                    {{-- Credit Limit --}}
                                                    <div class="form-group">
                                                        <div class="row">
                                                            <div class="col-sm-6 col-md-6 col-lg-6 ">
                                                                <label for="minCreditLimit"
                                                                    class="col-form-label col-form-label-sm">Credit
                                                                    Limit<span class="text-danger">*</span></label>
                                                                <input type="text"
                                                                    class="form-control form-control-sm number_input"
                                                                    value="{{ old('maxCreditLimit',$clientParty->CreditLimit) }}"
                                                                    name="maxCreditLimit" id="maxCreditLimit"
                                                                    placeholder="Credit Limit">
                                                            </div>
                                                            <div class="col-sm-6 col-md-6 col-lg-6 ">
                                                                <label for="baseCurrency"
                                                                    class="col-form-label col-form-label-sm">Base
                                                                    Currency</label>
                                                                <input type="text" class="form-control form-control-sm"
                                                                    name="baseCurrency" id="baseCurrency"
                                                                    value="{{old('baseCurrencyCode', $clientParty->BaseCurrencyCode) }}"
                                                                    disabled>
                                                                <input type="hidden"
                                                                    class="form-control form-control-sm"
                                                                    name="baseCurrencyCode" id="baseCurrencyCode"
                                                                    value="{{old('baseCurrencyCode', $clientParty->BaseCurrencyCode) }}">
                                                                <input type="hidden"
                                                                    class="form-control form-control-sm"
                                                                    name="baseCurrencyName" id="baseCurrencyName"
                                                                    value="{{old('baseCurrencyName', $clientParty->BaseCurrencyName) }}">
                                                                <input type="hidden"
                                                                    class="form-control form-control-sm"
                                                                    name="baseCurrencyID" id="baseCurrencyID"
                                                                    value="{{old('baseCurrencyID', $clientParty->BaseCurrencyId) }}">

                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            {{-- licence type --}}
                                            <div class="form-group row mb-0 mt-0 p-0 ">
                                                <div class="col col-sm-6 col-lg-6 col-md-6 m-0">
                                                    <div class="row">
                                                        <label for="licence"
                                                            class="col-md-3 col-form-label col-form-label-sm pr-0">Licence
                                                            Type<span class="text-danger">*</span></label>
                                                        <div class="col-md-9">
                                                            <select class="form-control form-control-sm"
                                                                name="licenceTypeID" id="licenceTypeID">
                                                                <option value="" disabled selected></option>
                                                                @forelse ($licenceTypes as $type)
                                                                <option value="{{ $type->LicenseID }}"
                                                                    id="{{ $type->LicenseCode }}"
                                                                    @if(old('licenceTypeID',$type->LicenseID ) ==
                                                                    $clientParty->LicenceTypeID) selected @endif>{{
                                                                    $type->LicenseName }}</option>
                                                                @empty
                                                                @endforelse
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col col-sm-6 col-lg-6 col-md-6">
                                                    <input type="hidden" class="form-control form-control-sm"
                                                        value="{{ old('licenceTypeCode', $clientParty->LicenceTypeCode) }}"
                                                        name="licenceTypeCode" id="licenceTypeCode"><input type="text"
                                                        class="form-control form-control-sm"
                                                        value="{{ old('licenceTypeValue', $clientParty->LicenceTypeValue) }}"
                                                        name="licenceTypeValue" id="licenceTypeValue">
                                                </div>
                                            </div>
                                        </fieldset>
                                        <fieldset class="border p-2 card shadow-lg mb-0 pt-0">
                                            <legend class="w-auto p-0 m-0"> Additional Information</legend>
                                            <div class="row">
                                                <div class="col-md-6 col-sm-12 col-lg-6">
                                                    {{-- Tarrif {{ dd($tarrifs[2]->TARIFF == $clientParty->TariffValue)
                                                    }} --}}
                                                    <div class="form-group row mb-1">
                                                        <div class="col-md-12">
                                                            <label for="tarrif"
                                                                class="col-form-label col-form-label-sm">Tarrif</label>
                                                            <select class="form-control form-control-sm" name="tarrif"
                                                                id="tarrif" required>
                                                                <option value="" selected disabled></option>
                                                                @forelse ($tarrifs as $tarrif)
                                                                <option value="{{ $tarrif->TARIFF }}" @if(old('tarrif',
                                                                    $clientParty->TariffValue) == $tarrif->TARIFF)
                                                                    selected @endif>{{ $tarrif->TARIFF }}</option>
                                                                @empty
                                                                @endforelse
                                                            </select>
                                                        </div>
                                                    </div>
                                                    {{-- FSC Type & Rate --}}
                                                    <div class="form-group row mb-1">
                                                        <div class="col-md-12">
                                                            <label for="phoneNo"
                                                                class="col-form-label col-form-label-sm"> FSC Type &
                                                                Rate</label>
                                                            <div class="row">
                                                                <div class="col-sm-6 col-lg-6">
                                                                    <select class="form-control form-control-sm"
                                                                        name="fscType" id="fscType" required>
                                                                        <option value="" selected disabled>Select Type
                                                                        </option>
                                                                        <option value="Fixed"
                                                                            @if(old('fscType',$clientParty->FSCType) ==
                                                                            'Fixed') selected @endif>Fixed</option>
                                                                        <option value="Variable" @if(old('fscType',
                                                                            $clientParty->FSCType) == 'FixedVariable')
                                                                            selected @endif>Variable</option>
                                                                    </select>
                                                                </div>
                                                                <div class="col-sm-6 col-lg-6">
                                                                    <input type="text"
                                                                        class="form-control form-control-sm"
                                                                        value="{{ old('fscRate',$clientParty->FSCRate) }}"
                                                                        name="fscRate" id="fscRate" placeholder="Rate"
                                                                        required>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6 col-sm-12 col-lg-6">
                                                    {{-- Billing Type --}}
                                                    <div class="form-group row mb-1">
                                                        <div class="col-md-12">
                                                            <label for="billingType"
                                                                class="col-form-label col-form-label-sm">Billing
                                                                Type</label>
                                                            <select class="form-control form-control-sm"
                                                                name="billingType" id="billingType" required>
                                                                <option value="" selected>Select Billing Type </option>
                                                                <option value="Periodic"
                                                                    @if(old('billingType',$clientParty->BillingType) ==
                                                                    'Periodic') selected @endif>Periodic</option>
                                                                <option value="Shipment"
                                                                    @if(old('billingType',$clientParty->BillingType) ==
                                                                    'Shipment') selected @endif>Shipment</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    {{-- discount --}}
                                                    <div class="form-group row mb-1">
                                                        <div class="col-md-12">
                                                            <label for="discount"
                                                                class="col-form-label col-form-label-sm">Discount in
                                                                %</label>
                                                            <input type="text" class="form-control form-control-sm"
                                                                value="{{ old('discount', number_format($clientParty->Discount,2)) }}"
                                                                name="discount" id="discount" placeholder="Discount"
                                                                required>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </fieldset>
                                    </div>
                                </div>
                                <button class="btn btn-primary m-2 btn-sm" type="button" name="previous"
                                    onclick="stepper.previous()"><i class="fa fa-step-backward" aria-hidden="true"></i>
                                    Previous</button>
                                @if (Auth::user()->can('create-client-party'))
                                    <button class="btn btn-primary m-2 btn-sm float-right" type="button" name="next"
                                        onclick="stepper.next()">Next <i class="fa fa-step-forward"
                                            aria-hidden="true"></i></button>
                                @else
                                    <button type="submit" name="submitBtn" value="true"
                                        class="btn btn-outline-success btn-sm float-right m-2" data-toggle="tooltip"
                                        data-placement="top" title="Will be processed by admin"><i class="fa fa-thumbs-up"
                                            aria-hidden="true"></i> Submit</button>
                                    <button type="submit" name="updateBtn" id="updateBtn"
                                        class="btn btn-success btn-sm float-right m-2" data-toggle="tooltip"
                                        data-placement="top" title="Update"><i class="fa fa-edit" aria-hidden="true"></i>
                                        Update for Later</button>
                                @endif
                            </div>
                            @if (Auth::user()->can('create-client-party'))
                                <div id="admin-details" class="content" role="tabpanel"
                                    aria-labelledby="admin-details-trigger">
                                    <div class="row justify-content-center">
                                        <div class="com-md-6 col-xl-6 col-sm-12 col-lg-6">
                                            <fieldset class="border p-2 card shadow-lg mb-0 pt-0">
                                                <legend class="w-auto p-0 m-0"></legend>
                                                <table class="table table-sm table-borderless">
                                                    <thead>
                                                        <th>Entity</th>
                                                        <th>Create</th>
                                                        <th>Select</th>
                                                        <th>Code</th>
                                                    </thead>
                                                    <tr>
                                                        <td>Client</td>
                                                        <td><input type="checkbox" name="clientCheck" id="clientCheck"
                                                                @if($clientParty->ClientName == '') checked @endif></td>
                                                        <td>
                                                            <input type="text" name="clientInput" id="clientInput"
                                                                value="{{ old('clientInput',$clientParty->ClientName) }}"
                                                                class="form-control form-control-sm" required>
                                                            <input type="hidden" name="clientID" id="clientID"
                                                                value="{{ old('clientID',$clientParty->ClientID) }}">
                                                            <input type="hidden" name="clientCode" id="clientCode"
                                                                value="{{ old('clientCode',$clientParty->ClientCode) }}">
                                                            {{-- <select name="clientID" id="clientID"
                                                                class="form-control form-control-sm">
                                                                <option value=""></option>
                                                            </select> --}}
                                                        </td>
                                                        <td id="client_Input" onclick='copyText(this)' data-toggle="tooltip"
                                                            title="Click to copy">{{ $clientParty->ClientCode ?? '' }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Shipper</td>
                                                        <td><input type="checkbox" name="shipperCheck" id="shipperCheck"
                                                                @if($clientParty->ShipperConsigneeName == '') checked
                                                            @endif></td>
                                                        <td>
                                                            <input type="text" name="shipperInput" id="shipperInput"
                                                                value="{{ old('shipperInput',$clientParty->ShipperConsigneeName) }}"
                                                                class="form-control form-control-sm" required>
                                                            <input type="hidden" name="shipperID" id="shipperID"
                                                                value="{{ old('shipperID',$clientParty->ShipperConsigneeID) }}">
                                                            <input type="hidden" name="shipperCode" id="shipperCode"
                                                                value="{{ old('shipperID',$clientParty->ShipperConsigneeCode) }}">
                                                            {{-- <select name="shipperInput" id="shipperInput"
                                                                class="form-control form-control-sm">
                                                                <option value=""></option>
                                                            </select> --}}
                                                        </td>
                                                        <td id="shipper_Input" onclick='copyText(this)'
                                                            data-toggle="tooltip" title="Click to copy">{{
                                                            $clientParty->ShipperConsigneeCode ?? '' }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Consignee</td>
                                                        <td><input type="checkbox" name="consigneeCheck" id="consigneeCheck"
                                                                @if($clientParty->ShipperConsigneeName == '') checked
                                                            @endif></td>
                                                        <td>
                                                            <input type="text" name="consigneeInput" id="consigneeInput"
                                                                value="{{ old('consigneeInput',$clientParty->ShipperConsigneeName) }}"
                                                                class="form-control form-control-sm" required>
                                                            <input type="hidden" name="consigneeID" id="consigneeID"
                                                                value="{{ old('consigneeID',$clientParty->ShipperConsigneeID) }}">
                                                            <input type="hidden" name="consigneeCode" id="consigneeCode"
                                                                value="{{ old('consigneeCode',$clientParty->ShipperConsigneeCode) }}">

                                                        </td>
                                                        <td id="consignee_Input" onclick='copyText(this)'
                                                            data-toggle="tooltip" title="Click to copy">{{
                                                            $clientParty->ShipperConsigneeCode ?? '' }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Other</td>
                                                        <td><input type="checkbox" name="otherCheck" id="otherCheck"
                                                                @if($clientParty->OtherPartyName == '') checked @endif></td>
                                                        <td>
                                                            <input type="text" name="otherInput" id="otherInput"
                                                                value="{{ old('otherID',$clientParty->OtherPartyName) }}"
                                                                class="form-control form-control-sm" required>
                                                            <input type="hidden" name="otherID" id="otherID"
                                                                value="{{ old('otherID',$clientParty->OtherPartyID) }}">
                                                            <input type="hidden" name="otherCode" id="otherCode"
                                                                value="{{ old('otherCode',$clientParty->OtherPartyCode) }}">

                                                        </td>

                                                        <td id="other_Input" onclick='copyText(this)' data-toggle="tooltip"
                                                            title="Click to copy">{{ $clientParty->OtherPartyCode ?? '' }}
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td>Debtor</td>
                                                        <td><input type="checkbox" name="debtorCheck" id="debtorCheck"
                                                                @if($clientParty->RequestforDebtor) checked @endif></td>
                                                        <td>
                                                            <input type="text" name="debtor" id="debtor"
                                                                value="{{ old('debtor', $clientParty->Debtor) }}"
                                                                class="form-control form-control-sm" required>
                                                            <input type="hidden" name="debtorID"
                                                                value="{{ old('debtorID',$clientParty->DebtorID) }}"
                                                                id="debtorID">
                                                            <input type="hidden" name="debtorCode"
                                                                value="{{ old('debtorCode', $clientParty->DebtorCode) }}"
                                                                id="debtorCode">

                                                        </td>
                                                        <td id="debtor_code" onclick='copyText(this)' data-toggle="tooltip"
                                                            title="Click to copy">{{ $clientParty->DebtorCode ?? '' }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Vendor</td>
                                                        <td><input type="checkbox" name="vendorCheck" id="vendorCheck"
                                                                @if($clientParty->RequestforVendor) checked @endif></td>
                                                        <td>
                                                            <input type="text" name="vendor" id="vendor"
                                                                value="{{ old('vendor',$clientParty->Vendor) }}"
                                                                class="form-control form-control-sm" required>
                                                            <input type="hidden" name="vendorID"
                                                                value="{{ old('vendorID',$clientParty->VendorID) }}"
                                                                id="vendorID">
                                                            <input type="hidden" name="vendorCode"
                                                                value="{{ old('vendorCode',$clientParty->VendorCode) }}"
                                                                id="vendorCode">

                                                        </td>
                                                        <td id="vendor_code" onclick="copyText(this)" data-toggle="tooltip"
                                                            title="Click to Copy">{{ $clientParty->VendorCode ?? '' }}</td>
                                                    </tr>
                                                </table>
                                            </fieldset>
                                            <button class="btn btn-primary m-2 btn-sm" type="button" name="previous"
                                                onclick="stepper.previous()"><i class="fa fa-step-backward"
                                                    aria-hidden="true"></i> Previous</button>
                                            @if ($approve)
                                                <button type="button" name="approveBtn" id="approveBtn"
                                                    class="btn btn-outline-success float-right m-2 btn-sm" data-toggle="tooltip"
                                                    data-placement="top" title=""
                                                    data-bs-original-title="Approve For Creation"><i class="fa fa-check"
                                                        aria-hidden="true"></i> Approve</button>
                                                <button type="button" name="rejectBtn" id="rejectBtn"
                                                    data-toggle="modal" data-target="#cancelModal"
                                                    class="btn btn-danger float-right m-2 btn-sm" data-toggle="tooltip"
                                                    data-placement="top" title="Reject For Amendment" data-bs-original-title="Update">
                                                    <i class="fa fa-edit" aria-hidden="true"></i> Reject </button>
                                            @else
                                                <button type="submit" name="createBtn" value="true"
                                                    class="btn btn-outline-success float-right m-2 btn-sm" data-toggle="tooltip"
                                                    data-placement="top" title=""
                                                    data-bs-original-title="Approve Entity For Creation"><i class="fa fa-check"
                                                    aria-hidden="true"></i> Approve</button>
                                                <button type="button" name="rejectBtn" id="rejectBtn"
                                                        data-toggle="modal" data-target="#cancelModal"
                                                        class="btn btn-danger float-right m-2 btn-sm" data-toggle="tooltip"
                                                        data-placement="top" title="Reject For Amendment" data-bs-original-title="Update">
                                                        <i class="fa fa-edit" aria-hidden="true"></i> Reject </button>
                                                <button type="submit" name="submitBtn" value="true"
                                                    class="btn btn-success float-right m-2 btn-sm" data-toggle="tooltip"
                                                    data-placement="top" title="" data-bs-original-title="Update">
                                                    <i class="fa fa-edit" aria-hidden="true"></i> Update for Later</button>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endif

                        </form>
                    </div>
                </div>
            </div>
            <!-- /.card-body -->
            <div class="card-footer"></div>
            <div id="loader"></div>
        </div>
        <!-- /.card -->
    </div>
</div>
<div class="row">
    <div class="col-md-12 text-center">
        Request ID: <span class="font-weight-bold" onclick='copyText(this)' data-toggle="tooltip"
            title="Click to copy">{{ $clientParty->Code }}</span> </b> Requested By: <b>{{ $clientParty->CreatedBy
            }}</b> Requested On: <b>{{ $clientParty->CreatedOn }}</b> <br>
        @if($clientParty->Status == 'Submitted')
        Status: <span
            class="badge badge-{{ $clientParty->paintClientPartyStatus($clientParty->Status) }}">PendingFinanceApproval</span>
        @else
        Status: <span
            class="badge badge-{{ $clientParty->paintClientPartyStatus($clientParty->Status) }}">{{$clientParty->Status}}</span>
        @endif
        @if (($clientParty->Status == 'Rejected') || ($clientParty->Status == 'Review'))Rejected By: <b>{{
            $clientParty->RejectedBy }}</b> RejectedOn: <b>{{ date_format(date_create($clientParty->RejectedOn),'Y-m-d
            H:i:s') }}</b> <br>Reason: <b class="text-sm">{{ $clientParty->RejectedReason }}</b>@endif
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="cancelModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Reject Entity Options</h5>

                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="" id="cancelForm" >
                    <div class="form-group">
                        <label for="level" class="control-label">Level</label>
                        <select name="level" id="level" class="form-control">
                            <option value="" selected disabled>Select an option</option>
                            <option value="Review">Reject for Review</option>
                            <option value="Rejected">Reject Permanently</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="reason" class="control-label">Reason</label>
                        <textarea name="reason" id="reason" class="form-control"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal" id="close-modal">Close</button>
                <button type="button" class="btn btn-primary" id="submit-entity">Save
                    changes</button>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
{{-- stepper css --}}
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bs-stepper/dist/css/bs-stepper.min.css">
<style>
    fieldset.scheduler-border {
        border: solid 1px #DDD !important;
        padding: 0 10px 10px 10px;
        border-bottom: none;
    }

    legend.scheduler-border {
        width: auto !important;
        border: none;
        font-size: 14px;
    }

    legend {
        font-size: 16px;
        /* color: #060ac4; */
        /* color: #0174f3; */
        color: #E91809;
        /* padding: 0 10px 10px 10px; */
        /**/
        font-weight: bold;
    }

    label {
        font-weight: bold;
        /* font-size: 18px; */
    }

    /* Preloader */
    .preloader {
        position: fixed;
        top: 0;
        left: 0;
        z-index: 999999999;
        width: 100%;
        height: 100%;
        background-color: #fff;
        overflow: hidden;
    }

    .preloader-inner {
        position: absolute;
        top: 50%;
        left: 50%;
        -webkit-transform: translate(-50%, -50%);
        -moz-transform: translate(-50%, -50%);
        transform: translate(-50%, -50%);
    }

    .preloader-icon {
        width: 100px;
        height: 100px;
        display: inline-block;
        padding: 0px;
    }

    .preloader-icon span {
        position: absolute;
        display: inline-block;
        width: 100px;
        height: 100px;
        border-radius: 100%;
        /* background:#F7941D; */
        background: #191857;
        -webkit-animation: preloader-fx 1.6s linear infinite;
        animation: preloader-fx 1.6s linear infinite;
    }

    .preloader-icon span:last-child {
        animation-delay: -0.8s;
        -webkit-animation-delay: -0.8s;
    }

    @keyframes preloader-fx {
        0% {
            transform: scale(0, 0);
            opacity: 0.5;
        }

        100% {
            transform: scale(1, 1);
            opacity: 0;
        }
    }

    @-webkit-keyframes preloader-fx {
        0% {
            -webkit-transform: scale(0, 0);
            opacity: 0.5;
        }

        100% {
            -webkit-transform: scale(1, 1);
            opacity: 0;
        }
    }

    /* End Preloader */
</style>
<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<style>
    #loader {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        width: 100%;
        background: rgba(0, 0, 0, 0.75) url('{{ asset('site_imgs/loading2.gif') }}') no-repeat center center;
        z-index: 10000;
    }
</style>
@stop

@section('js')
<script>
    /**Function to copy text */
    function copyText(element) {
        var $temp = $("<input>");
        $("body").append($temp);
        $temp.val($(element).html()).select();
        document.execCommand("copy");
        $('.tooltip .tooltip-inner').text('Copied!');
        $temp.remove();
      }
</script>
<script>
    /*Ajax setup CSRF*/
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
</script>
<script src="//code.jquery.com/jquery-1.12.4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bs-stepper/dist/js/bs-stepper.min.js"></script>
<script src="{{ asset('js/client-party/party-entity.js') }}"></script>
<script src="{{ asset('js/client-party/validation.js') }}"></script>
<script src="{{ asset('js/client-party/sub-form.js') }}"></script>
<script src="{{ asset('js/client-party/jquery-number/jquery.number.min.js') }}"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
<script>
    /** get user authorization status */
    window.User = {
        value: '{{ auth()->user()->can("edit-client-party") }}',
        edit: '{{ $editForm }}'
    }
    window.approve_client_party = `{{ $approve }}`;
</script>

<script src="{{ asset('js/client-party/show-create-client-party.js') }}"></script>
<script type="text/javascript">

   $('#close-modal').click(function(){
        $('#cancelForm')[0].reset();
        $('#cancelModal').modal('hide');
    });


    $('#submit-entity').on('click',function(){
        // validate form data
        let form_data = {
            id: `{{ $clientParty->Id }}`,
            level: $('#level option:selected').val(),
            reason: $('#reason').val(),
        };

        if(form_data.level == ""){
            $('#level').addClass('is-invalid');
        }else if(form_data.reason == ""){
            $('#reason').addClass('is-invalid');
        }else{
            $.ajax({
                url: '/client-party/reject',
                type: 'POST',
                datatype: 'json',
                data: form_data,
                success: function(response){
                    console.log(response);
                    location.reload();
                }
            });
        }
    });

    $('#approveBtn').click(function(){
        let approve = confirm('Confirm Approval.');
        if (approve) {
            let form_data = {
                id: `{{ $clientParty->Id }}`,
                status: `{{ $clientParty->Status }}`,
            };
            $.ajax({
                url: '/client-party/approve',
                type: 'POST',
                datatype: 'json',
                data: form_data,
                success: function(response){
                    if(response[1] == 200){
                        alert("Entity Updated. !!");
                        window.location.href = `{{ route("client-party.approvals") }}`
                    }
                    /*location.reload();*/
                }
            });
        }
    });


    window.default_debtor = `{{ $clientParty->DebtorType }}`;
    window.default_vendor = `{{ $clientParty->VendorType }}`;


    /*check if select disabled ? append : select*/
    let debtor_val = (window.default_debtor == '' ? '' : window.default_debtor);
    if(!$('#debtorType').attr('disabled') && typeof $('#debtorType').attr('disabled') !== 'undefined'){
        $('#debtorType option').remove();
        $('#debtorType').append('<option selected>'+debtor_val+'</option>');
    }else{
         if (debtor_val == '') {
            $('#debtorType').attr('disabled','disabled');
        }else{
            console.log('saving debtor val');
            $('#debtorType').val(debtor_val).change();
        }
    }

    let vendor_val = (window.default_vendor  == '' ? '' : window.default_vendor );

    if(!$('#vendorType').attr('disabled') && typeof $('#vendorType').attr('disabled') !== 'undefined'){
        $('#vendorType option').remove();
        $('#vendorType').append('<option selected>'+vendor_val+'</option>');
    }else{
        if (vendor_val == '') {
            $('#vendorType').attr('disabled','disabled');
        }else{
            $('#vendorType').val(vendor_val);
        }
    }

    window.default_bill_curr = `{{ $clientParty->BillingCurrencyId }}`;
</script>

<script type="text/javascript">
    let init = `{{ $clientParty->attachments->count()+1 }}`;
    let max_fields = 3;
    $(".addAttachment").click(function() {
        var lsthmtl = $(".clone").html();
        if (init < max_fields) {
            // check if file attachment i has content
            var names = document.getElementsByName('attachmentNames[]');
            var desc = document.getElementsByName('attachmentDescription[]');

                var fileInputName = names[names.length-2];
                var fileInputDescription = desc[desc.length-2];

                // -- validation --//
                // Allowing file type
                var allowedExtensions = /(\.jpg|\.jpeg|\.png|\.gif)$/i;
                if (!allowedExtensions.exec(fileInputName.value) || fileInputDescription.value == '') {
                    alert('Either Invalid file type / No Description');
                    fileInputName.value = '';
                    return false;
                }else{
                    $("#attachClone").before(lsthmtl);
                    init++;
                }

        } else {
            alert('Maximum File Attachment Reached.');
        }
    });

    $("body").on("click", ".btn-danger", function() {
            $(this).parents(".hdtuto").remove();
            init--;
        });

    let spinner = $('#loader');

    $("body").on("click", ".deleteFile", function() {
            let id =  $(this).attr('id');
            let choice = confirm('Are you Sure You wish to delete file ?');
            let url = "/delete-client-party-attachment/"+id;
            if (choice) {
                spinner.show();
                $.ajax({
                    url: url,
                    type: 'POST',
                    success: function(response){
                        if (response[1] == 200) {
                            alert("File Deleted !!");
                            location.reload();
                        }
                    }
                });
            }
        });
</script>
@stop
