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
                    <div class="bs-stepper linear">
                        <div class="row justify-content-center p-0">
                            <div class="bs-stepper-header col-md-10" role="tablist">
                                <!-- your steps here -->
                                <div class="step" data-target="#basic-details">
                                    <button type="button" class="step-trigger" role="tab" aria-controls="basic-details"
                                        id="basic-details-trigger" aria-selected="false" disabled="disabled">
                                        <span class="bs-stepper-circle">1</span>
                                        <span class="bs-stepper-label">Basic Details</span>
                                    </button>
                                </div>
                                <div class="line"></div>
                                <div class="step" data-target="#office-details">
                                    <button type="button" class="step-trigger" role="tab"
                                        aria-controls="office-details" id="office-details-trigger" aria-selected="true">
                                        <span class="bs-stepper-circle">2</span>
                                        <span class="bs-stepper-label">Office Details</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="bs-stepper-content container">
                            <form action="/client-party" method="POST" class="form-horizontal"
                                enctype="multipart/form-data" id="clientPartyForm">
                                @csrf
                                <!-- your steps content here -->
                                <div id="basic-details" class="content" role="tabpanel"
                                    aria-labelledby="basic-details-trigger">
                                    <fieldset class="border pl-2 pr-2 card shadow-sm m-0" style="border-radius: 10px;">
                                        <legend class="w-auto">Entity Creation Options</legend>
                                        <div class="row">
                                            <div class="form-group col-md-6 col-lg-6">
                                                <div class="row pr-4 pl-5">
                                                    <label for="partyEntityType"
                                                        class="col-sm-3 col-lg-4 col-md-4 col-form-label col-form-label-sm">Operation
                                                        Type<span class="text-danger">*</span></label>
                                                    <select
                                                        class="col-sm-9 col-lg-8 col-md-8 form-control form-control-sm pr-3 pl-3"
                                                        id="partyEntityType" name="partyEntityType" required>
                                                        <option value="" disabled selected></option>
                                                        <option value="Client + Shipper + Consignee"
                                                            @if (old('partyEntityType', $clientParty->OperatioEntityType) == 'Client + Shipper + Consignee') selected @endif>Client +
                                                            Shipper + Consignee</option>
                                                        {{-- <option value="Client Only" >Client Only</option> --}}
                                                        <option value="Shipper + Consignee"
                                                            @if (old('partyEntityType', $clientParty->OperatioEntityType) == 'Shipper + Consignee') selected @endif>Shipper +
                                                            Consignee</option>
                                                        <option value="Shipper Only"
                                                            @if (old('partyEntityType', $clientParty->OperatioEntityType) == 'Shipper Only') selected @endif>Shipper Only
                                                        </option>
                                                        <option value="Consignee Only"
                                                            @if (old('partyEntityType', $clientParty->OperatioEntityType) == 'Consignee Only') selected @endif>Consignee Only
                                                        </option>
                                                        <option value="Agent / Forwarder"
                                                            @if (old('partyEntityType', $clientParty->OperatioEntityType) == 'Agent / Forwarder') selected @endif>Agent /
                                                            Forwarder</option>
                                                        <option value="Airline"
                                                            @if (old('partyEntityType', $clientParty->OperatioEntityType) == 'Airline') selected @endif>Airline
                                                        </option>
                                                        <option value="Shipplingline"
                                                            @if (old('partyEntityType', $clientParty->OperatioEntityType) == 'Shipplingline') selected @endif>Shipplingline
                                                        </option>
                                                        <option value="Trucker / Transportor"
                                                            @if (old('partyEntityType', $clientParty->OperatioEntityType) == 'Trucker / Transportor') selected @endif>Trucker /
                                                            Transporter</option>
                                                        <option value="NoSCMIntergration"
                                                            @if (old('partyEntityType') == 'NoSCMIntergration') selected @endif>Debtor/Vendor
                                                            In D365</option>

                                                    </select>
                                                </div>
                                            </div>
                                            <div class="form-group col-md-6 col-lg-6 ">
                                                <div class="row pr-3 pl-2">
                                                    <label for="legalEntityType"
                                                        class="col-sm-3 col-lg-4 col-form-label col-form-label-sm">Legal
                                                        Type <span class="text-danger">*</span></label>
                                                    <div class="col-sm-9 col-lg-8">
                                                        <select class="form-control form-control-sm" id="legalEntityType"
                                                            name="legalEntityType">
                                                            <option value="" disabled selected></option>
                                                            <option value="Debtor & Vendor"
                                                                @if (old('legalEntityType', $clientParty->LegalEntityType) == 'Debtor & Vendor') selected @endif>Debtor &
                                                                Vendor</option>
                                                            <option value="Debtor"
                                                                @if (old('legalEntityType', $clientParty->LegalEntityType) == 'Debtor') selected @endif>Debtor
                                                            </option>
                                                            <option value="Vendor"
                                                                @if (old('legalEntityType', $clientParty->LegalEntityType) == 'Vendor') selected @endif>Vendor
                                                            </option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </fieldset>
                                    <div class="row">
                                        <div class="col-md-6 col-sm-12">
                                            <fieldset class="border p-2">
                                                <legend class="w-auto">Basic Information</legend>
                                                {{-- Company Name --}}
                                                <div class="form-group row">
                                                    <label for="companyName"
                                                        class="col-sm-3 col-lg-4 col-md-4 col-form-label col-form-label-sm"><i
                                                            class="fa fa-building" aria-hidden="true"></i>Company Name<span
                                                            class="text-danger">*</span></label>
                                                    <div class="col-sm-9 col-lg-8 col-md-8">
                                                        <input type="text" class="form-control form-control-sm"
                                                            value="{{ old('companyName', $clientParty->Name) }}"
                                                            name="companyName" id="companyName">
                                                    </div>
                                                </div>
                                                {{-- AddressLine 1 --}}
                                                <div class="form-group row">
                                                    <label for="addressLine1"
                                                        class="col-sm-3 col-lg-4 col-form-label col-form-label-sm"><i
                                                            class="fa fa-map-marker" aria-hidden="true"></i> AddressLine 1
                                                        <span class="text-danger">*</span></label>
                                                    <div class="col-sm-9 col-lg-8">
                                                        <textarea name="addressLine1" id="addressLine1" class="form-control form-control-sm">{{ old('addressLine1', $clientParty->Address_Line1) }}</textarea>
                                                    </div>
                                                </div>
                                                {{-- AddressLine 2 --}}
                                                <div class="form-group row">
                                                    <label for="addressLine2"
                                                        class="col-sm-3 col-lg-4 col-form-label col-form-label-sm"><i
                                                            class="fa fa-map-marker" aria-hidden="true"></i> AddressLine
                                                        2</label>
                                                    <div class="col-sm-9 col-lg-8">
                                                        <textarea name="addressLine2" id="addressLine2" class="form-control form-control-sm">{{ old('addressLine2', $clientParty->Address_Line2) }}</textarea>
                                                    </div>
                                                </div>
                                                {{-- Country Code and Country Name --}}
                                                <div class="form-group row">
                                                    <label for="country"
                                                        class="col-sm-3 col-lg-4 col-form-label col-form-label-sm"><i
                                                            class="fa fa-globe" aria-hidden="true"></i> Country <span
                                                            class="text-danger">*</span></label>
                                                    <div class="col-sm-8 col-lg-8">
                                                        {{-- <select class="form-control form-control-sm editable-select" name="country" id="country">
                                                            <option value="" disabled>Type Country</option>
                                                        </select> --}}
                                                        <label class="sr-only" for="country">Country</label>
                                                        <div class="form-group">
                                                            {{-- <div class="col-sm-5 col-lg-4"> --}}
                                                            {{-- <input type="text" class="form-control form-control-sm" name="city" id="city" placeholder="City"> --}}
                                                            {{-- {{ dd($countries) }} --}}
                                                            <select class="form-control form-control-sm" name="countryID"
                                                                id="countryID">
                                                                <option value=""></option>
                                                                @forelse ($countries as $country)
                                                                    <option value="{{ $country->CountryID }}"
                                                                        id="{{ $country->CountCode }}"
                                                                        @if (old('countryID', $country->CountryID) == $country->CountryID) selected @endif>
                                                                        {{ $country->CountryName }}</option>
                                                                @empty
                                                                @endforelse
                                                            </select>
                                                            {{-- </div> --}}
                                                            {{-- <input type="text" class="form-control form-control-sm" id="country" placeholder="Country"> --}}
                                                            <input type="hidden" name="countryName" id="countryName"
                                                                value="{{ old('countryName', $clientParty->Name) }}">
                                                            <input type="hidden" name="countryCode" id="countryCode"
                                                                value="{{ old('countryCode', $clientParty->CountryCode) }}">
                                                            {{-- <div class="input-group-prepend">
                                                                <div class="input-group-text"><i class="fa fa-search fa-sm" aria-hidden="true"></i> </div>
                                                            </div> --}}
                                                        </div>
                                                        {{-- <ul class="searchResult" id="countryResult"></ul> --}}
                                                    </div>
                                                    {{-- <div class="col-sm-5 col-lg-4">
                                                        {{-- <input type="text" class="form-control form-control-sm" name="city" id="city" placeholder="City">
                                                          <select class="form-control form-control-sm" name="city" id="city">
                                                            <option value="" disabled>Type Country</option>
                                                        </select>
                                                    </div> --}}
                                                </div>
                                                {{-- Province zipcode --}}
                                                <div class="form-group row ">
                                                    <label for="province"
                                                        class="col-sm-3 col-lg-4 col-form-label col-form-label-sm"><i
                                                            class="fa fa-map-pin" aria-hidden="true"></i> Province &
                                                        Zipcode</label>
                                                    <div class="col-sm-4 col-lg-4">
                                                        <input type="text" class="form-control form-control-sm "
                                                            name="province" id="provinceInput"
                                                            value="{{ old('province', $clientParty->Address_State) }}"
                                                            placeholder="Province">
                                                        <select class="form-control form-control-sm" name="province"
                                                            id="provinceSelect">
                                                            <option value="" disabled>Enter Province</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-sm-4 col-lg-4">
                                                        <input type="text"
                                                            class="form-control form-control-sm number_input"
                                                            value="{{ old('zipCode', $clientParty->Address_Pin) }}"
                                                            name="zipCode" id="zipCode" placeholder="Zip Code">
                                                    </div>
                                                </div>
                                                {{-- City --}}
                                                <div class="form-group row">
                                                    <label for="cityName"
                                                        class="col-sm-3 col-lg-4 col-form-label col-form-label-sm"><i
                                                            class="fas fa-city"></i> City</label>
                                                    <div class="col-sm-9 col-lg-8">
                                                        <input type="text" class="form-control form-control-sm"
                                                            value="{{ old('cityName', $clientParty->Address_City) }}"
                                                            name="cityName" id="cityInput">
                                                        <select class="form-control form-control-sm" name="cityID"
                                                            id="citySelect">
                                                            <option value="" disabled selected>Select City</option>
                                                        </select>
                                                        {{-- <input type="hidden" name="cityName" id="cityName" value="{{ old('cityName') }}"> --}}
                                                        <input type="hidden" name="cityCode" id="cityCode"
                                                            value="{{ old('cityCode') }}">
                                                    </div>
                                                </div>

                                            </fieldset>
                                        </div>
                                        <div class="col-md-6 col-sm-12">
                                            <fieldset class="border p-2">
                                                <legend class="w-auto">Contact Details</legend>
                                                <div class="form-group row">
                                                    <label for="person"
                                                        class="col-sm-3  col-md-4 col-lg-4 col-form-label col-form-label-sm">
                                                        <i class="fa fa-user" aria-hidden="true"></i> Person <span
                                                            class="text-danger">*</span></label>
                                                    {{-- <div class="col-sm-2 col-md-3 col-lg-2">
                                                        <input type="text" class="form-control form-control-sm"
                                                            name="namePrefix" id="namePrefix" placeholder="Prefix">
                                                    </div> --}}
                                                    <div class="col-sm-4 col-md-4 col-lg-4">
                                                        <input type="text" class="form-control form-control-sm"
                                                            value="{{ old('firstName', $clientParty->Cont_Bch_PersonName_FirstName) }}"
                                                            name="firstName" placeholder="First Name">
                                                    </div>
                                                    <div class="col-sm-5 col-md-4 col-lg-4">
                                                        <input type="text" class="form-control form-control-sm"
                                                            value="{{ old('lastName', $clientParty->Cont_Bch_PersonName_LastName) }}"
                                                            name="lastName" placeholder="Last Name">
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <label for="department"
                                                        class="col-sm-3 col-lg-4 col-form-label col-form-label-sm"><i
                                                            class="fa fa-sitemap" aria-hidden="true"></i>
                                                        Department</label>
                                                    <div class="col-sm-9 col-lg-8">
                                                        <input type="text" class="form-control form-control-sm"
                                                            value="{{ old('department', $clientParty->Cont_Bch_Dept) }}"
                                                            name="department" id="department" placeholder="Department">
                                                    </div>
                                                </div>
                                                {{-- phone no --}}
                                                <div class="form-group row">
                                                    <label for="phoneNo"
                                                        class="col-xs-12 col-sm-3  col-md-9 col-lg-4 col-form-label col-form-label-sm">
                                                        <i class="fa fa-phone" aria-hidden="true"></i> Phone No.<span
                                                            style="color:red">*</span></label>
                                                    <div class="col-xs-12 col-sm-9 col-md-9 col-lg-8">
                                                        <input type="text"
                                                            class="form-control form-control-sm number_input"
                                                            value="{{ old('phoneNo', $clientParty->Cont_Bch_PhoneNumbers) }}"
                                                            name="phoneNo" id="phoneNo" placeholder="Phone Number">
                                                    </div>
                                                </div>
                                                {{-- email --}}
                                                <div class="form-group row">
                                                    <label for="contractEmail"
                                                        class="col-sm-3 col-lg-4 col-form-label col-form-label-sm"><i
                                                            class="fa fa-envelope" aria-hidden="true"></i> Email</label>
                                                    <div class="col-sm-9 col-lg-8">
                                                        <input type="email" name="contractEmail" id="contractEmail"
                                                            value="{{ old('contractEmail', $clientParty->Cont_Bch_Email) }}"
                                                            class="form-control form-control-sm"
                                                            placeholder="Enter Email">
                                                    </div>
                                                </div>
                                            </fieldset>
                                            <fieldset class="border pl-2 pr-2">
                                                <legend class="w-auto">Other information</legend>
                                                {{-- Client --}}
                                                <div class="form-group row">
                                                    <label for="client"
                                                        class="col-sm-3 col-lg-4 col-form-label col-form-label-sm"><i
                                                            class="fa fa-user" aria-hidden="true"></i> Client <span
                                                            class="text-danger" id="clientSpan">*</span></label>
                                                    <div class="col-sm-9 col-lg-8">
                                                        <input type="text" name="client" id="client"
                                                            value="{{ old('client') }}"
                                                            class="form-control form-control-sm" placeholder="" disabled>
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <label for="specialInstructions"
                                                        class="col-sm-3 col-lg-4 col-form-label col-form-label-sm"><i
                                                            class="fas fa-comments"></i> Special
                                                        Instructions</label>
                                                    <div class="col-sm-9 col-lg-8">
                                                        <textarea class="form-control form-control-sm" name="specialInstruction" id="specialInstruction"
                                                            placeholder="Special Instructions">{{ old('specialInstruction', $clientParty->SpecialInstructions) }}</textarea>
                                                    </div>
                                                </div>
                                            </fieldset>
                                        </div>
                                    </div>
                                    <button class="btn btn-primary mt-2 float-right" name="next" type="button"
                                        onclick="stepper.next()">Next <i class="fa fa-step-forward"
                                            aria-hidden="true"></i></button>
                                </div>
                                <div id="office-details" class="content active dstepper-block" role="tabpanel"
                                    aria-labelledby="office-details-trigger">
                                    <div class="row">
                                        <div class="col-md-4 col-lg-4 col-sm-12">
                                            <fieldset class="border p-2">
                                                <legend class="w-auto">Office Mapping</legend>
                                                <div class="form-group row">
                                                    <div class="col-md-12 col-sm-12">
                                                        <label for="officeCountry"
                                                            class="col-form-label col-form-label-sm">Office <span
                                                                class="text-danger">*</span></label>
                                                        <select class="form-control form-control-sm" name="officeCountry"
                                                            id="officeCountry" required>
                                                            <option value="" disabled selected></option>
                                                            @forelse ($offices as $office)
                                                                <option value="{{ $office->Name }}"
                                                                    @if ($office->Name == $clientParty->Office) selected @endif>
                                                                    {{ $office->Name }}</option>
                                                            @empty
                                                            @endforelse
                                                        </select>
                                                    </div>
                                                </div>
                                            </fieldset>
                                            <fieldset class="border p-2">
                                                <legend class="w-auto pb-0 mb-0">Prefered Ports</legend>
                                                {{-- <div class="row pt-0 mt-0 justify-content-center">
                                                    <div class="col-md-8 col-sm-8 col-lg-8 ">
                                                        <div class="form-check">
                                                            <label class="form-check-label" for="includeCountryCheckBox">
                                                                Include Country
                                                            </label>
                                                            <input class="form-check-input ml-3" type="checkbox" name="includeCountryCheckBox" id="includeCountryCheckBox">
                                                        </div>
                                                    </div>
                                                </div> --}}
                                                <div class="row">
                                                    <div class="col-md-6 col-sm-12 col-lg-6">
                                                        {{-- port of loading Air --}}
                                                        <div class="form-group  pt-0">
                                                            <div class="">
                                                                <label for="portOfLoadingAir"
                                                                    class="col-form-label col-form-label-sm">Port Of
                                                                    Loading(Air) </label>
                                                                <input type="text" class="form-control form-control-sm"
                                                                    value="{{ old('portOfLoadingAir', $clientParty->AirUsualPortOfLoadingName) }}"
                                                                    name="portOfLoadingAir" id="portOfLoadingAir">
                                                                <input type="hidden" name="portOfLoadingAirID"
                                                                    id="portOfLoadingAirID"
                                                                    value="{{ old('portOfLoadingAirID', $clientParty->AirUsualPortOfLoading) }}">
                                                                {{-- <select class="form-control form-control-sm" name="portOfLoadingAir" id="portOfLoadingAir">
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
                                                                    value="{{ old('portOfDischargeAir', $clientParty->AirUsualPortofDischargeName) }}"
                                                                    name="portOfDischargeAir" id="portOfDischargeAir">

                                                                <input type="hidden" class="form-control form-control-sm"
                                                                    value="{{ old('portOfDischargeID', $clientParty->AirUsualPortofDischarge) }}"
                                                                    name="portOfDischargeID" id="portOfDischargeID">
                                                                {{-- <select class="form-control form-control-sm" name="portOfDischargeAir" id="portOfDischargeAir">
                                                                    <option value="" disabled selected></option>
                                                                </select> --}}
                                                            </div>
                                                        </div>

                                                    </div>
                                                    <div class="col-md-6 col-sm-12 col-lg-6">
                                                        {{-- port of loading sea --}}
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <label for="portOfLoadingSea"
                                                                    class="col-form-label col-form-label-sm">Port Of
                                                                    Loading(Sea) </label>
                                                                <input type="text" class="form-control form-control-sm"
                                                                    value="{{ old('portOfLoadingSea', $clientParty->SeaUsualPortOfLoadingName) }}"
                                                                    name="portOfLoadingSea" id="portOfLoadingSea">
                                                                <input type="hidden" class="form-control form-control-sm"
                                                                    value="{{ old('portOfLoadingSeaID', $clientParty->SeaUsualPortOfLoading) }}"
                                                                    name="portOfLoadingSeaID" id="portOfLoadingSeaID">
                                                                {{-- <select class="form-control form-control-sm" name="portOfLoadingSea" id="portOfLoadingSea" >
                                                                    <option value="" disabled selected></option>
                                                                </select> --}}
                                                            </div>
                                                        </div>
                                                        {{-- port of Discharge Sea --}}
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <label for="portOfDischargeSea"
                                                                    class="col-form-label col-form-label-sm">Port Of
                                                                    Discharge(Sea)</label>
                                                                <input type="text" class="form-control form-control-sm"
                                                                    value="{{ old('portOfDischargeSea', $clientParty->SeaUsualPortofDischargeName) }}"
                                                                    name="portOfDischargeSea" id="portOfDischargeSea">
                                                                <input type="hidden" class="form-control form-control-sm"
                                                                    value="{{ old('portOfDischargeSeaID', $clientParty->SeaUsualPortofDischarge) }}"
                                                                    name="portOfDischargeSeaID" id="portOfDischargeSeaID">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </fieldset>
                                            <fieldset class="border p-2">
                                                <legend>File Upload</legend>
                                                <label for="attachmentFile">Attachment <i
                                                        class="fa fa-paperclip"></i></label>
                                                <div class="custom-file">
                                                    <input type="file" class="custom-file-input" id="attachmentFile">
                                                    <label class="custom-file-label" for="attachmentFile">Choose
                                                        file</label>
                                                </div>
                                            </fieldset>
                                        </div>
                                        <div class="col-md-8 col-lg-8 col-sm-12">
                                            <fieldset class="border p-2">
                                                <legend class="w-auto">Client</legend>
                                                <div class="row">
                                                    {{-- first detail client col --}}
                                                    <div class="col-md-6 col-sm-12 col-lg-6">
                                                        {{-- Sales Pic --}}
                                                        <div class="form-group row">
                                                            <label for="salesPIC"
                                                                class="col-sm-3 col-lg-4 col-form-label col-form-label-sm">Sales
                                                                PIC <span class="text-danger">*</span></label>
                                                            <div class="col-sm-9 col-lg-8">
                                                                <input type="text" class="form-control form-control-sm"
                                                                    value="{{ old('salesPIC', $clientParty->SalesMgrOfficeFunMapName) }}"
                                                                    name="salesPIC" id="salesPIC" placeholder="">
                                                                <input type="hidden" name="salesPICID" id="salesPICID"
                                                                    value="{{ old('salesPICID', $clientParty->SalesMgrOfficeFunMapId) }}">
                                                                {{-- <select class="form-control form-control-sm" name="salesPIC" id="salesPIC" required>
                                                                    <option value="" disabled selected></option>
                                                                </select> --}}
                                                            </div>
                                                        </div>
                                                        {{-- Inco Terms --}}
                                                        <div class="form-group row">
                                                            <label for="incoTermID"
                                                                class="col-sm-3 col-lg-4 col-form-label col-form-label-sm">Inco
                                                                Terms</label>
                                                            <div class="col-sm-9 col-lg-8">
                                                                <select class="form-control form-control-sm"
                                                                    name="incoTermID" id="incoTermID">
                                                                    <option value="" disabled selected></option>
                                                                    @forelse ($incoterms as $incoterm)
                                                                        <option
                                                                            value="{{ old('incoTermID', $incoterm->IncoTermID) }}"
                                                                            @if (old('incoTermID', $incoterm->IncoTermID) == $clientParty->IncoTermID) selected @endif>
                                                                            {{ $incoterm->Name }}</option>
                                                                    @empty
                                                                    @endforelse
                                                                </select>
                                                                <input type="hidden" name="incoTermName"
                                                                    id="incoTermName"
                                                                    value="{{ old('incoTermName', $clientParty->IncoTermName) }}">
                                                            </div>
                                                        </div>
                                                        {{-- Notify party 1 --}}
                                                        <div class="form-group row">
                                                            <label for="notifyParty1"
                                                                class="col-sm-3 col-lg-4 col-form-label col-form-label-sm">Notify
                                                                Party 1</label>
                                                            <div class="col-sm-9 col-lg-8">
                                                                <input type="text" class="form-control form-control-sm"
                                                                    value="{{ old('notifyParty1', $clientParty->DefaultNotifyPartyName) }}"
                                                                    name="notifyParty1" id="notifyParty1" placeholder="">
                                                                {{-- <select class="form-control form-control-sm" name="notifyParty1" id="notifyParty1">
                                                                    <option value="" disabled selected></option>
                                                                </select> --}}
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <!-- /.end col 1 office details -->
                                                    {{-- second detail client col --}}
                                                    <div class="col-md-6 col-sm-12 col-lg-6 pt-0">
                                                        {{-- Account Pic --}}
                                                        <div class="form-group row">
                                                            <label for="accountPIC"
                                                                class="col-sm-3 col-lg-4 col-form-label col-form-label-sm">Account
                                                                PIC</label>
                                                            <div class="col-sm-9 col-lg-8">
                                                                <input type="text" class="form-control form-control-sm"
                                                                    value="{{ old('accountPIC', $clientParty->AccountMgrOfficeFunMapName) }}"
                                                                    name="accountPIC" id="accountPIC" placeholder="">
                                                                <input type="hidden" name="accountPICID"
                                                                    id="accountPICID"
                                                                    value="{{ old('accountPICID', $clientParty->AccountMgrOfficeFunMapId) }}">
                                                                {{-- <select class="form-control form-control-sm" name="accountPIC" id="accountPIC">
                                                                    <option value="" disabled selected></option>
                                                                </select> --}}
                                                            </div>
                                                        </div>
                                                        {{-- payment Terms --}}
                                                        <div class="form-group row">
                                                            <label for="paymentTermsID"
                                                                class="col-sm-3 col-lg-4 col-form-label col-form-label-sm pt-0 mt-0">Pay
                                                                Terms</label>
                                                            <div class="col-sm-9 col-lg-8 ">
                                                                <select class="form-control form-control-sm"
                                                                    name="paymentTermsID" id="paymentTermsID">
                                                                    <option value="" disabled selected></option>
                                                                    @forelse ($payTerms as $payTerm)
                                                                        <option
                                                                            value="{{ old('paymentTermsID', $payTerm->IncoTermID) }}"
                                                                            @if (old('paymentTermsID', $payTerm->IncoTermID) == $clientParty->PaymentTermsID) selected @endif>
                                                                            {{ $payTerm->Name }}</option>
                                                                    @empty
                                                                    @endforelse
                                                                </select>
                                                                <input type="hidden" name="paymentTermsName"
                                                                    id="paymentTermsName">
                                                            </div>
                                                        </div>
                                                        {{-- Notify party 2 --}}
                                                        <div class="form-group row">
                                                            <label for="notifyParty2"
                                                                class="col-sm-3 col-lg-4 col-form-label col-form-label-sm">Notify
                                                                Party 2</label>
                                                            <div class="col-sm-9 col-lg-8">
                                                                <input type="text" class="form-control form-control-sm"
                                                                    value="{{ old('notifyParty2', $clientParty->DefaultNotifyParty1Name) }}"
                                                                    name="notifyParty2" id="notifyParty2" placeholder="">
                                                                {{-- <select class="form-control form-control-sm" name="notifyParty2" id="notifyParty2">
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
                                                    <div class="form-group row">
                                                        <label for="forwarderName"
                                                            class="col-sm-3 col-md-4 col-lg-2 col-form-label col-form-label-sm">Forwarder</label>
                                                        <div class="col-sm-9 col-lg-10 col-md-8">
                                                            <input type="text" class="form-control form-control-sm"
                                                                name="forwarderName" id="forwarderName"
                                                                value="{{ old('forwarderName', $clientParty->DefaultFowarderName) }}">
                                                            <input type="hidden" class="form-control form-control-sm"
                                                                name="forwarderID" id="forwarderID"
                                                                value="{{ old('forwarderID', $clientParty->DefaultFowarderId) }}">
                                                            {{-- <select class="form-control form-control-sm" name="forwarder" id="forwarder">
                                                                <option value="" disabled selected></option>
                                                            </select> --}}
                                                        </div>
                                                    </div>
                                                </div>
                                            </fieldset>
                                            <fieldset class="border p-2">
                                                <legend class="w-auto">Debtor/Vendor</legend>
                                                <div class="row">
                                                    <div class="col-md-6 col-sm-12 col-lg-6">
                                                        {{-- Credit Days --}}
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <label for="creditDays"
                                                                    class="col-form-label col-form-label-sm">Credit
                                                                    Days</label>
                                                                <input type="text"
                                                                    class="form-control form-control-sm number_input"
                                                                    value="{{ old('creditDays', $clientParty->CreditDays) }}"
                                                                    name="creditDays" id="creditDays">
                                                            </div>
                                                        </div>
                                                        {{-- Billing Currency --}}
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <label for="billingCurrency"
                                                                    class="col-form-label col-form-label-sm">Billing
                                                                    Currency</label>
                                                                <input type="hidden" class="form-control form-control-sm"
                                                                    value="{{ old('billingCurrencyCode') }}"
                                                                    name="billingCurrencyCode" id="billingCurrencyCode">
                                                                <select class="form-control form-control-sm"
                                                                    name="billingCurrency" id="billingCurrency">
                                                                    <option value="" disabled selected></option>
                                                                    @forelse ($currencyList as $item)
                                                                        <option value="{{ $item->CurrencyID }}"
                                                                            @if (old('billingCurrency', $item->CurrencyID) == $clientParty->BillingCurrencyId) selected @endif>
                                                                            {{ $item->CurrencyCode }}</option>
                                                                    @empty
                                                                    @endforelse
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6 col-sm-12 col-lg-6">
                                                        {{-- Debtor Type --}}
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <label for="debtorType"
                                                                    class="col-form-label col-form-label-sm">Debtor
                                                                    Type</label>
                                                                <select class="form-control form-control-sm"
                                                                    name="debtorType" id="debtorType">
                                                                    <option value="" disabled selected></option>
                                                                    <option value="Cash Customer"
                                                                        @if (old('debtorType', $clientParty->DebtorType) == 'Cash Customer') selected @endif>
                                                                        Cash Customer</option>
                                                                    <option value="Approved Debtor"
                                                                        @if (old('debtorType', $clientParty->DebtorType) == 'Approved Debtor') selected @endif>
                                                                        Approved Debtor</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        {{-- Credit Limit --}}
                                                        <div class="form-group">
                                                            <label for="minCreditLimit"
                                                                class="col-form-label col-form-label-sm">Credit
                                                                Limit</label>
                                                            <div class="row">
                                                                <div class="col-sm-12 col-md-12 col-lg-12 ">
                                                                    <input type="text"
                                                                        class="form-control form-control-sm number_input"
                                                                        value="{{ old('maxCreditLimit', $clientParty->CreditLimit) }}"
                                                                        name="maxCreditLimit" id="maxCreditLimit"
                                                                        placeholder="Credit Limit">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                {{-- licence type --}}
                                                <div class="form-group row">
                                                    <label for="licence"
                                                        class="col-sm-3 col-lg-3 col-md-3 col-form-label col-form-label-sm">Licence
                                                        Type.</label>
                                                    <div class="col-sm-5 col-lg-5 col-md-5">
                                                        <select class="form-control form-control-sm" name="licenceTypeID"
                                                            id="licenceTypeID">
                                                            <option value="" disabled selected></option>
                                                            @forelse ($licenceTypes as $type)
                                                                <option value="{{ $type->LicenseID }}"
                                                                    @if (old('licenceTypeID', $type->LicenseID) == $clientParty->LicenceTypeID) selected @endif>
                                                                    {{ $type->LicenseName }}</option>
                                                            @empty
                                                            @endforelse
                                                        </select>
                                                    </div>
                                                    <div class="col-sm-4 col-lg-4 col-md-4">
                                                        <input type="hidden" class="form-control form-control-sm"
                                                            value="{{ old('licenceCode', $clientParty->licenceCode) }}"
                                                            name="licenceCode" id="licenceCode">
                                                        <input type="text" class="form-control form-control-sm"
                                                            value="{{ old('licenceTypeValue', $clientParty->LicenceTypeValue) }}"
                                                            name="licenceTypeValue" id="licenceTypeValue">
                                                    </div>
                                                </div>
                                            </fieldset>
                                            <fieldset class="border p-2">
                                                <legend class="w-auto"> Additional Information</legend>
                                                <div class="row">
                                                    <div class="col-md-6 col-sm-12 col-lg-6">
                                                        {{-- Tarrif --}}
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <label for="tarrif"
                                                                    class="col-form-label col-form-label-sm">Tarrif</label>
                                                                <select class="form-control form-control-sm"
                                                                    name="tarrif" id="tarrif" required>
                                                                    <option value="" selected disabled></option>
                                                                    @forelse ($tarrifs as $tarrif)
                                                                        <option value="{{ $tarrif->TARIFF }}"
                                                                            @if (old('tarrif', $tarrif->TARIFF) == $clientParty->TariffValue) selected @endif>
                                                                            {{ $tarrif->TARIFF }}</option>
                                                                    @empty
                                                                    @endforelse
                                                                </select>
                                                            </div>
                                                        </div>
                                                        {{-- FSC Type & Rate --}}
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <label for="fscType"
                                                                    class="col-form-label col-form-label-sm"> FSC Type &
                                                                    Rate</label>
                                                                <div class="row">
                                                                    <div class="col-sm-6 col-lg-6">
                                                                        <select class="form-control form-control-sm"
                                                                            name="fscType" id="fscType" required>
                                                                            <option value="" selected disabled>Select
                                                                                Type</option>
                                                                            <option value="Fixed"
                                                                                @if (old('fscType', $clientParty->FSCType) == 'Fixed') selected @endif>
                                                                                Fixed</option>
                                                                            <option value="Variable"
                                                                                @if (old('fscType', $clientParty->FSCType) == 'FixedVariable') selected @endif>
                                                                                Variable</option>
                                                                        </select>
                                                                    </div>
                                                                    <div class="col-sm-6 col-lg-6">
                                                                        <input type="text"
                                                                            class="form-control form-control-sm"
                                                                            value="{{ old('fscRate', $clientParty->FSCRate) }}"
                                                                            name="fscRate" id="fscRate"
                                                                            placeholder="Rate" required>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6 col-sm-12 col-lg-6">
                                                        {{-- Billing Type --}}
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <label for="billingType"
                                                                    class="col-form-label col-form-label-sm">Billing
                                                                    Type</label>
                                                                <select class="form-control form-control-sm"
                                                                    name="billingType" id="billingType" required>
                                                                    <option value="" selected>Select Billing Type
                                                                    </option>
                                                                    <option value="Periodic"
                                                                        @if (old('billingType', $clientParty->BillingType) == 'Periodic') selected @endif>
                                                                        Periodic</option>
                                                                    <option value="Shipment"
                                                                        @if (old('billingType', $clientParty->BillingType) == 'Shipment') selected @endif>
                                                                        Shipment</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        {{-- discount --}}
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <label for="discount"
                                                                    class="col-form-label col-form-label-sm">Discount in
                                                                    %</label>
                                                                <input type="text" class="form-control form-control-sm"
                                                                    value="{{ old('discount', $clientParty->Discount) }}"
                                                                    name="discount" id="discount" placeholder="Discount"
                                                                    required>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </fieldset>
                                        </div>
                                    </div>
                                    <button class="btn btn-primary mt-2" type="button" name="previous"
                                        onclick="stepper.previous()"><i class="fa fa-step-backward"
                                            aria-hidden="true"></i> Previous</button>
                                    <button type="submit" name="submitBtn" class="btn btn-success float-right mt-2"><i
                                            class="fa fa-edit" aria-hidden="true"></i> update</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <!-- /.card-body -->
                <div class="card-footer"></div>
            </div>
            <!-- /.card -->
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
            font-size: 14px;
            /* padding: 0 10px 10px 10px; */
            /* font-weight: bold; */
        }

        label {
            font-weight: bold;
            /* font-size: 72px; */
        }
    </style>
    <style>
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
    {{-- /*Editable Select Style*/ --}}
    <link href="//rawgithub.com/indrimuska/jquery-editable-select/master/dist/jquery-editable-select.min.css"
        rel="stylesheet">
    <style>
        .searchResult {
            list-style: none;
            padding: 0px;
            width: 250px;
            position: absolute;
            margin: 0;
            z-index: 5;
        }

        .searchResult li {
            background: lavender;
            padding: 4px;
            margin-bottom: 1px;
        }

        .searchResult li:nth-child(even) {
            background: cadetblue;
            color: white;
        }

        .searchResult li:hover {
            cursor: pointer;
        }

        .country {
            width: 340px;
        }
    </style>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
@stop

@section('js')

    <script src="//code.jquery.com/jquery-1.12.4.min.js"></script>
    {{-- /*Editable Select Script*/ --}}
    <script src="//rawgithub.com/indrimuska/jquery-editable-select/master/dist/jquery-editable-select.min.js"></script>
    {{-- /*Stepper pane js*/ --}}
    <script src="https://cdn.jsdelivr.net/npm/bs-stepper/dist/js/bs-stepper.min.js"></script>
    <script src="{{ asset('js/client-party/party-entity.js') }}"></script>
    {{-- <script src="{{ asset('js/client-party/country.js') }}"></script> --}}
    <script src="{{ asset('js/client-party/validation.js') }}"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
    <script>
        window.User = {
            value: '{{ auth()->user()->can('update-client-party') }}'
        }
    </script>
    <script>
        $(function() {
            /*manage permission*/
            if (window.User.value != 1) {
                $("#clientPartyForm :input").not("[name=next], [name=previous]").prop("disabled", true);
            }
            /*Control party entity and clint*/
            const entities = ['Shipper + Consignee', 'Consignee Only', 'Shipper Only'];
            var partyEntity = $('#partyEntityType').val();
            if (entities.includes(partyEntity)) {
                $('#client').attr('required', 'true').removeAttr('disabled');
            }

            $('#partyEntityType').on('change', function() {
                var criteria = entities.includes($('#partyEntityType').val());
                if (criteria) {
                    $('#client').attr('required', 'true').removeAttr('disabled');
                    $('#clientSpan').addeClass('text-danger');
                } else {
                    $('#client').val('').attr('disabled', 'true').removeClass('is-invalid');
                    $('#clientSpan').removeClass('text-danger');
                }
            });


            /* $("#clientPartyForm :input").prop("disabled", true);*/
            /*/ Variable declaration*/
            var office = '';
            var includeCountry = '';
            var countryCode = '';
            var includeCode = '';
            var regExp = new RegExp('[a-zA-Z]'),
                inputVal = '';
            $('.number_input').on('keydown keyup', function(e) {
                var value = $(this).val();
                /* Do not allow alphabets to be entered.*/
                if (regExp.test(value)) {
                    $(this).val(inputVal);
                } else {
                    inputVal = value;
                }
            });

            /*Ajax setup*/
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            /*get countries and append to options*/
            $.ajax({
                url: '/get-countries',
                type: 'get',
                data: {
                    query: ''
                },
                dataType: 'json',
                success: function(response) {
                    console.log(response);
                    var len = response.length;
                    for (var i = 0; i < len; i++) {
                        var id = response[i]['CountryID'];
                        var name = response[i]['CountryName'];
                        var code = response[i]['CountCode'];
                        /* $("#countryID").append("<option value='" + id + "' id='" + code + "'>" + name + "</option>");*/
                    }
                },
                error: function(error) {
                    console.log(error);
                }
            });

            $('#countryID').on('change', function() {
                var countryID = $('#countryID option:selected').val();
                var countryName = $('#countryID option:selected').text();
                countryCode = $('#countryID option:selected').attr('id');
                /*Set country id and code*/
                $('#countryName').val(countryName);
                $('#countryCode').val(countryCode);
                /*Search for Cities*/
                searchCity(countryCode);
                /*Search for provinces*/
                searchProvince(countryCode);
            });

            $('#citySelect').on('change', function() {
                var countryID = $('#citySelect option:selected').val();
                var countryName = $('#citySelect option:selected').text();
                countryCode = $('#citySelect option:selected').attr('id');
                /*Set country id and code*/
                $('#cityName').val(countryName);
                $('#cityCode').val(countryCode);
            });

            /*by default type city*/
            $('#citySelect').hide();
            $('#cityInput').show();

            function searchCity(CountryCode) {
                $.ajax({
                    url: '/get-cities',
                    type: 'get',
                    data: {
                        query: CountryCode
                    },
                    dataType: 'json',
                    success: function(response) {
                        var len = response.length;
                        if (len < 1) {
                            /*show the input city*/
                            $('#cityInput').show();
                            $('#citySelect').hide();
                        } else {
                            $('#cityInput').hide();
                            /*Show the select city options*/
                            $('#citySelect').show();
                            for (var i = 0; i < len; i++) {
                                var id = response[i]['CountryID'];
                                var name = response[i]['CountryName'];
                                var code = response[i]['CountCode'];
                                $("#citySelect").append("<option value='" + id + "' id='" + code +
                                    "'>" + name + "</option>");
                            }
                        }

                    }
                });
            }

            /*by default hide province select*/
            $('#provinceSelect').hide();
            $('#provinceInput').show();

            function searchProvince(CountryCode) {
                $.ajax({
                    url: '/get-provinces',
                    type: 'post',
                    data: {
                        query: CountryCode
                    },
                    dataType: 'json',
                    success: function(response) {
                        console.log(response);
                        var len = response.length;
                        if (len < 1) {
                            $('#provinceInput').show();
                            $('#provinceSelect').hide();
                        } else {
                            $('#provinceSelect').show();
                            $('#provinceInput').hide();
                            for (var i = 0; i < len; i++) {
                                var id = response[i]['CountryID'];
                                var name = response[i]['CountryName'];
                                var code = response[i]['CountCode'];
                                $("#provinceSelect").append("<option value='" + id + "' id='" + code +
                                    "'>" + name + "</option>");
                            }
                        }

                    }
                });
            }

            $('#tarrif').attr('disabled', 'true');
            $('#fscType').attr('disabled', 'true');
            $('#fscRate').attr('disabled', 'true');
            $('#billingType').attr('disabled', 'true');
            $('#discount').attr('disabled', 'true');


            /*Office on change operation*/
            $("#officeCountry").on('change', function() {
                office = $(this).val();
                /*Confirm include country check*/
                includeCountry = $('#includeCountryCheckBox').is(':checked') == true ? 1 : 0;
                countryCode = (includeCountry == 1) ? $('#countryCode').val() : null;
                /*Set sales PIC
                 salesPIC(office);*/
                /*Notify party one*/
                notifyPartyOne(office);
                /*Notify party two*/
                notifyPartyTwo(office);
                /*Account PIC*/
                accountPIC(office);
                /*Notify forwarder*/
                notifyForwarder(office);
                /*Currency list*/
                currencyList(office);
                /*tarrif list*/
                tarrif(office);
                /*Show additional information*/
                if (office == 'Kenya - UPS') {
                    $('#tarrif').removeAttr('disabled');
                    $('#fscType').removeAttr('disabled');
                    $('#fscRate').removeAttr('disabled');
                    $('#billingType').removeAttr('disabled');
                    $('#discount').removeAttr('disabled');
                } else {
                    $('#tarrif').val('').attr('disabled', 'true');
                    $('#fscType').val('').attr('disabled', 'true');
                    $('#fscRate').val('').attr('disabled', 'true');
                    $('#billingType').val('').attr('disabled', 'true');
                    $('#discount').val('').attr('disabled', 'true');
                }
            });
            var ofi = '{{ $clientParty->Office }}';
            if (ofi == 'Kenya - UPS' && (window.User.value == 1)) {
                $('#tarrif').removeAttr('disabled');
                $('#fscType').removeAttr('disabled');
                $('#fscRate').removeAttr('disabled');
                $('#billingType').removeAttr('disabled');
                $('#discount').removeAttr('disabled');
            }
            /*Search auto port of loading*/
            $("#portOfLoadingAir").autocomplete({
                delay: 250,
                source: function(request, response) {
                    console.log(request);
                    return $.get("{{ route('clientParty.getPOLPOD', ['Air']) }}", {
                        query: request.term,
                        office: office,
                        includeCode: includeCode,
                        includeCountry: includeCountry,
                        "_token": "{{ csrf_token() }}",
                    }, function(data) {
                        console.log(data);
                        response(data);
                    });

                },

                select: function(event, ui) {
                    /* Set selection*/
                    $('#portOfLoadingAir').val(ui.item.label); /* display the selected text*/
                    $('#portOfLoadingAirID').val(ui.item.value); /* save selected id to input*/
                    return false;
                },
                focus: function(event, ui) {
                    $("#portOfLoadingAir").val(ui.item.label);
                    $("#portOfLoadingAirID").val(ui.item.value);
                    return false;
                },
            });

            /*Search auto PIC*/
            $("#portOfLoadingSea").autocomplete({
                /* override default values here */
                minLength: 2,
                /*delay: 250,*/
                source: function(request, response) {
                    console.log(request);
                    return $.get("{{ route('clientParty.getPOLPOD', ['Sea']) }}", {
                        query: request.term,
                        office: office,
                        includeCode: includeCode,
                        includeCountry: includeCountry,
                        "_token": "{{ csrf_token() }}",
                    }, function(data) {
                        response(data);
                    });
                },
                select: function(event, ui) {
                    /* Set selection*/
                    $('#portOfLoadingSea').val(ui.item.label); /* display the selected text*/
                    $('#portOfLoadingSeaID').val(ui.item.value); /* save selected id to input*/
                    return false;
                },
                focus: function(event, ui) {
                    $("#portOfLoadingSea").val(ui.item.label);
                    $("#portOfLoadingSeaID").val(ui.item.value);
                    return false;
                },
            });
            /*Search auto PIC discharge*/
            $("#portOfDischarge").autocomplete({
                minLength: 2,
                /*delay: 250,*/
                source: function(request, response) {
                    console.log(request);
                    return $.get("{{ route('clientParty.getPOLPOD', ['Sea']) }}", {
                        query: request.term,
                        office: office,
                        includeCode: includeCode,
                        includeCountry: includeCountry,
                        "_token": "{{ csrf_token() }}",
                    }, function(data) {
                        response(data);
                    });
                },
                select: function(event, ui) {
                    /* Set selection*/
                    $('#portOfDischarge').val(ui.item.label); /* display the selected text*/
                    $('#portOfDischargeID').val(ui.item.value); /* save selected id to input*/
                    return false;
                },
                focus: function(event, ui) {
                    $("#portOfDischarge").val(ui.item.label);
                    $("#portOfDischargeID").val(ui.item.value);
                    return false;
                },
            });
            /*Search auto PIC discharge*/
            $("#portOfDischargeSea").autocomplete({
                minLength: 2,
                /*delay: 250,*/
                source: function(request, response) {
                    console.log(request);
                    return $.get("{{ route('clientParty.getPOLPOD', ['Air']) }}", {
                        query: request.term,
                        office: office,
                        includeCode: includeCode,
                        includeCountry: includeCountry,
                        "_token": "{{ csrf_token() }}",
                    }, function(data) {
                        response(data);
                    });
                },
                select: function(event, ui) {
                    /* Set selection*/
                    $('#portOfDischargeSea').val(ui.item.label); /* display the selected text*/
                    $('#portOfDischargeSeaID').val(ui.item.value); /* save selected id to input*/
                    return false;
                },
                focus: function(event, ui) {
                    $("#portOfDischargeSea").val(ui.item.label);
                    $("#portOfDischargeSeaID").val(ui.item.value);
                    return false;
                },
            });
            /*Search sales PIC*/
            $("#salesPIC").autocomplete({
                minLength: 2,
                source: function(request, response) {
                    var office = $('#officeCountry').val();
                    return $.post("/get-sales-account-pic/S", {
                        query: request.term,
                        office: office,
                        "_token": "{{ csrf_token() }}",
                    }, function(data) {
                        console.log(data);
                        response(data);
                    });
                },
                select: function(event, ui) {
                    /* Set selection*/
                    $('#salesPIC').val(ui.item.label); /* display the selected text*/
                    $('#salesPICID').val(ui.item.value); /* save selected id to input*/
                    return false;
                },
                focus: function(event, ui) {
                    $("#salesPIC").val(ui.item.label);
                    $("#salesPICID").val(ui.item.value); /**/
                    return false;
                },
            });
            /*Search sales PIC*/
            $("#forwarderName").autocomplete({
                source: function(request, response) {
                    var office = $('#officeCountry').val();
                    return $.post("/get-notify-party-list/Forwarder", {
                        query: request.term,
                        office: office,
                        "_token": "{{ csrf_token() }}",
                    }, function(data) {
                        response(data);
                    });
                },
                select: function(event, ui) {
                    /* Set selection*/
                    $('#forwarderName').val(ui.item.label); /* display the selected text*/
                    $('#forwarderID').val(ui.item.value); /*save selected id to input*/
                    return false;
                },
                focus: function(event, ui) {
                    $("#forwarderName").val(ui.item.label);
                    $("#forwarderID").val(ui.item.value); /**/
                    return false;
                },
            });

            /*Search account PIC*/
            $("#accountPIC").autocomplete({
                source: function(request, response) {
                    var office = $('#officeCountry').val();
                    return $.post("/get-sales-account-pic/A", {
                        query: request.term,
                        office: office,
                        "_token": "{{ csrf_token() }}",
                    }, function(data) {
                        response(data);
                    });
                },
                select: function(event, ui) {
                    /* Set selection*/
                    $('#accountPIC').val(ui.item.label); /* display the selected text*/
                    $('#accountPICID').val(ui.item.value); /*save selected id to input*/
                    return false;
                },
                focus: function(event, ui) {
                    $("#accountPIC").val(ui.item.label);
                    $("#accountPICID").val(ui.item.value); /**/
                    return false;
                },
            });

            /*Get Currency List*/
            function currencyList(param) {
                $.ajax({
                    url: '/get-currency-list',
                    type: 'get',
                    data: {
                        query: param
                    },
                    dataType: 'json',
                    success: function(response) {
                        console.log(response);
                        $('#billingCurrency option:not(:first)').remove();
                        var len = response.length;
                        for (var i = 0; i < len; i++) {
                            var currencyCode = response[i]['CurrencyCode'];
                            var currencyID = response[i]['CurrencyID'];
                            $("#billingCurrency").append('<option value="' + currencyID + '" id="' +
                                currencyCode + '">' + currencyCode + '</option>');
                        }
                    }
                });
            }
            /*get tarrif list*/
            function tarrif(param) {
                $.ajax({
                    url: '/get-tarrif-list',
                    type: 'get',
                    data: {
                        query: param
                    },
                    dataType: 'json',
                    success: function(response) {
                        $('#tarrif option:not(:first)').remove();
                        var len = response.length;
                        for (var i = 0; i < len; i++) {
                            var tarrif = response[i]['TARIFF'];
                            $("#tarrif").append('<option value="' + tarrif + '">' + tarrif +
                                '</option>');
                        }
                    }
                });
            }

            $("#billingCurrency").on('change', function() {
                var code = $("#billingCurrency option:selected").attr('id');
                $("#billingCurrencyCode").val(code);
            });
            /*Map party one dat to the dom*/
            function notifyPartyOne(office) {
                getNotifyPartyList(office, 'Notify Party').done(function(x, y) {
                    $('#notifyParty1 option:not(:first)').remove();
                    /*Append data to sales pic select*/
                    var len = x.length;
                    for (var i = 0; i < len; i++) {
                        var partyName = x[i]['PartyName'];
                        var partyID = x[i]['PartyID'];
                        var partyCode = x[i]['PartyCode'];
                        /*$("#notifyParty1").editableSelect('add',name);*/
                        $("#notifyParty1").append('<option id="' + partyCode + '" value="' + partyID +
                            '">' + partyName + '</option>');
                    }
                });
            }
            /*Map party two dat to the dom*/
            function notifyPartyTwo(country) {
                getNotifyPartyList(country, 'Notify Party - 2').done(function(x, y) {
                    $('#notifyParty2 option:not(:first)').remove();
                    /*Append data to sales pic select*/
                    var len = x.length;
                    for (var i = 0; i < len; i++) {
                        var partyName = x[i]['PartyName'];
                        var partyID = x[i]['PartyID'];
                        var partyCode = x[i]['PartyCode'];
                        $("#notifyParty2").append('<option id="' + partyCode + '" value="' + partyID +
                            '">' + partyName + '</option>');
                    } /**/
                });
            }
            /*Map party two dat to the dom*/
            function notifyForwarder(office) {
                getNotifyPartyList(office, 'Forwarder').done(function(x, y) {
                    $('#forwarder option:not(:first)').remove();
                    /*Append data to sales pic select*/
                    var len = x.length;
                    for (var i = 0; i < len; i++) {
                        var partyName = x[i]['PartyName'];
                        var partyID = x[i]['PartyID'];
                        var partyCode = x[i]['PartyCode'];
                        /* $("#forwarder").append('<option id="'+partyCode+'" value="'+partyID+'">'+partyName+'</option>');*/
                    }
                });
            }

            /*Map  account PIC data to the dom*/
            function accountPIC(a) {
                salesAccountPIC('A', a).done(function(x, y) {
                    $('#accountPIC option:not(:first)').remove();
                    /*Append data to sales pic select*/
                    var len = x.length;
                    for (var i = 0; i < len; i++) {
                        var name = x[i]['PersonName'];
                        $("#accountPIC").append('<option id="" value="' + name + '">' + name + '</option>');
                    } /**/
                });
            }
            /* get sales and acount PIC*/
            function salesAccountPIC(param, params) {
                return $.ajax({
                    url: '/get-sales-account-PIC/' + param,
                    type: 'post',
                    dataType: 'json',
                    data: {
                        query: params
                    }
                });
            }


            /*Ajax setup*/
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            /*Get party list  [party 1, party 2 & forwarder]*/
            function getNotifyPartyList(office, type) {
                return $.ajax({
                    url: '/get-notify-party-list/' + office,
                    type: 'post',
                    dataType: 'json',
                    data: {
                        query: type
                    }
                });
            }

            /*Fetch and append inco terms*/
            /* $.ajax({
                url: '/get-inco-terms',
                type: 'get',
                dataType: 'json',
                success: function (response) {
                console.log(response);
                    var len = response.length;
                    for (var i = 0; i < len; i++) {
                        var name = response[i]['Name'];*/
            /*$("#incoTerms").editableSelect('add',name);*/
            /* $("#incoTerms").append('<option value="'+name+'">'+name+'</option>');
                        }
                    }
                });*/

            /*Fetch and append payment terms*/
            $.ajax({
                url: '/get-payment-term-list',
                type: 'get',
                dataType: 'json',
                success: function(response) {
                    /* console.log(response);*/
                    var len = response.length;
                    for (var i = 0; i < len; i++) {
                        var name = response[i]['Name'];
                        /* $("#paymentTerms").editableSelect('add',name);*/
                    }
                }
            });

            /*Get Offices */
            $.ajax({
                url: '/get-offices',
                type: 'get',
                dataType: 'json',
                success: function(response) {
                    /* console.log(response);*/
                    var len = response.length;
                    for (var i = 0; i < len; i++) {
                        var name = response[i]['Name'];
                        $("#officeCountry").append('<option value="' + name + '">' + name +
                        '</option>');
                    }
                }
            });


            /*Map sales PIC data to the dom*/
            function salesPIC(par) {
                salesAccountPIC('S', par).done(function(x, y) {
                    console.log(x);
                    $('#salesPIC option:not(:first)').remove();
                    /*Append data to sales pic select*/
                    var len = x.length;
                    for (var i = 0; i < len; i++) {
                        var name = x[i]['PersonName'];
                        /* $("#salesPIC").append('<option value="'+name+'">'+name+'</option>');*/
                    }
                });
            }

            /*Checkbox Action listener
            $('input:checkbox').attr('checked', 'true');*/
            $('input:checkbox').change(function() {
                if ($(this).is(':checked')) {
                    $('#officeCountry').trigger('change');
                    countryCode = $('#countryID option:selected').attr('id');
                    console.log(countryCode);
                } else {
                    $('#officeCountry').trigger('change');
                }
            });
            /* console.log(getPOLPOD('Sea',1,'US', 'United'));
             Get PLO PLD
             function getPOLPOD(locationType,IncludeCountry, includeCode, filter) {
                return $.ajax({
                    url: '/get-plo-pld/'+locationType,
                    type: 'get',
                    dataType: 'json',
                    data:{
                        query: filter,
                        includeCountry: IncludeCountry,
                        includeCode: includeCode
                    }
                });
            }*/
            /*Get licence type*/
            $.ajax({
                url: '/get-licenceType',
                type: 'get',
                dataType: 'json',
                success: function(response) {
                    /*console.log(response);*/
                    var len = response.length;
                    for (var i = 0; i < len; i++) {
                        var licenceName = response[i]['LicenseName'];
                        var licenceID = response[i]['LicenseID'];
                        var licenseCode = response[i]['LicenseCode'];
                        /*$("#debtorLicenceType").append('<option value="'+licenceID+'" id="'+licenseCode+'">'+licenceName+'</option>');*/
                        /*$("#vendorLicenceType").append('<option value="'+licenceID+'" id="'+licenseCode+'">'+licenceName+'</option>');*/
                    }
                }
            });

            $("#vendorLicenceType").on('change', function() {
                var code = $("#vendorLicenceType option:selected").attr('id');
                $('#vendorLicenceCode').val(code);
            });
            $("#debtorLicenceType").on('change', function() {
                var code = $("#debtorLicenceType option:selected").attr('id');
                $('#licenceCode').val(code);
            });
            /** Editable Select*/

            /* BS-Stepper Init*/
            window.stepper = new Stepper($('.bs-stepper')[0], {
                animation: true
            });


        });
    </script>

@stop
