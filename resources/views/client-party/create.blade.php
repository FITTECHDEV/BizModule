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
                            <div class="bs-stepper-header col-md-10 col-sm p-0 mt-2 pr-3 pl-3 " style="border-radius: 10px" role="tablist">
                                <!-- your steps here -->
                                <div class="step" data-target="#basic-details">
                                    <button type="button" class="step-trigger pb-0 pt-0" role="tab" aria-controls="basic-details"
                                        id="basic-details-trigger" aria-selected="false" disabled="disabled">
                                        <span class="bs-stepper-circle">1</span>
                                        <span class="bs-stepper-label">Basic Details</span>
                                    </button>
                                </div>
                                <div class="line"></div>
                                <div class="step" data-target="#office-details">
                                    <button type="button" class="step-trigger pb-0 pt-0" role="tab" aria-controls="office-details"
                                        id="office-details-trigger" aria-selected="true">
                                        <span class="bs-stepper-circle">2</span>
                                        <span class="bs-stepper-label">Office Details</span>
                                    </button>
                                </div>
                                @if (Auth::user()->can('create-client-party'))
                                <div class="line"></div>
                                <div class="step" data-target="#admin-details">
                                    <button type="button" class="step-trigger pb-0 pt-0" role="tab" aria-controls="office-details"
                                        id="admin-details-trigger" aria-selected="true">
                                        <span class="bs-stepper-circle">3</span>
                                        <span class="bs-stepper-label">Admin Details</span>
                                    </button>
                                </div>
                                @endif
                                <div class="">
                                    <a href="/client-party" class="bs-stepper-label btn btn-outline-primary">
                                        <i class="fa fa-home"></i>   Home
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="bs-stepper-content container">
                            <form action="/client-party" method="POST" class="form-horizontal" enctype="multipart/form-data" id="clientPartyForm">
                                @csrf
                                <!-- your steps content here -->
                                <div id="basic-details" class="content" role="tabpanel" aria-labelledby="basic-details-trigger">
                                    <div class="row">
                                        <div class="col-md-12 col-sm-12">
                                            <fieldset class="border pl-2 pr-2 card shadow-sm m-0" style="border-radius: 10px;">
                                                <legend class="w-auto p-0 m-0">Entity Creation Options</legend>
                                                <div class="row">
                                                    <div class="col col-md-6 col-sm-12 ">
                                                        <div class="form-group row mb-1" >
                                                            <label for="partyEntityType" class="col-sm-3 col-lg-4 col-md-4 col-form-label col-form-label-sm">Operation Type<span class="text-danger">*</span></label>
                                                            <div class="col-sm-9 col-lg-8 col-md-8">
                                                                <select class="form-control form-control-sm" id="partyEntityType" name="partyEntityType" required>
                                                                    <option value="" disabled selected></option>
                                                                    <option value="Client + Shipper + Consignee" @if(old('partyEntityType')=='Client + Shipper + Consignee'
                                                                        ) selected @endif>Client + Shipper + Consignee</option>
                                                                    {{-- <option value="Client Only">Client Only</option> --}}
                                                                    <option value="Shipper + Consignee" @if(old('partyEntityType')=='Shipper + Consignee' ) selected @endif>
                                                                        Shipper + Consignee</option>
                                                                    <option value="Shipper Only" @if(old('partyEntityType')=='Shipper Only' ) selected @endif>Shipper Only
                                                                    </option>
                                                                    <option value="Consignee Only" @if(old('partyEntityType')=='Consignee Only' ) selected @endif>Consignee
                                                                        Only</option>
                                                                    <option value="Forwarder" @if(old('partyEntityType')== 'Agent / Forwarder' ) selected @endif>Agent / Forwarder</option>
                                                                    <option value="Airline" @if(old('partyEntityType')=='Airline' ) selected @endif>Airline</option>
                                                                    <option value="Shipping Line" @if(old('partyEntityType')=='Shipplingline' ) selected @endif>
                                                                        Shipping Line</option>
                                                                    <option value="Trucker" @if(old('partyEntityType')=='Trucker' ) selected @endif>Trucker / Transporter</option>
                                                                    <option value="NoSCMIntergration" @if(old('partyEntityType')=='NoSCMIntergration' ) selected @endif>Debtor/Vendor In D365</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col col-md-6 col-sm-12 ">
                                                        <div class="form-group row mb-1" >
                                                            <label for="legalEntityType" class="col-sm-3 col-lg-4 col-md-4 col-form-label col-form-label-sm">Legal Type <span class="text-danger">*</span></label>
                                                            <div class="col-sm-9 col-lg-8 col-md-8">
                                                                <select class="form-control form-control-sm" id="legalEntityType" name="legalEntityType">
                                                                    <option value="" disabled selected></option>
                                                                    <option value="Debtor & Vendor" @if(old('legalEntityType') == 'Debtor & Vendor') selected @endif>Debtor & Vendor</option>
                                                                    <option value="Debtor" @if(old('legalEntityType') == 'Debtor') selected @endif>Debtor</option>
                                                                    <option value="Vendor" @if(old('legalEntityType') == 'Vendor') selected @endif>Vendor</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                            </fieldset>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 col-sm-12 ">
                                            <fieldset class="border p-2 card shadow-lg mb-0 pt-0">
                                                <legend class="w-auto p-0 m-0">Basic Information</legend>
                                                {{-- Company Name --}}
                                                <div class="form-group row mb-1" >
                                                    <label for="companyName" class="col-sm-3 col-lg-4 col-md-4 col-form-label col-form-label-sm"><i class="fa fa-building" aria-hidden="true"></i>Company Name<span class="text-danger">*</span></label>
                                                    <div class="col-sm-9 col-lg-8 col-md-8">
                                                        <input type="text" class="form-control form-control-sm"
                                                        value="{{ old('companyName') }}"
                                                        name="companyName" id="companyName" maxlength="50">
                                                    </div>
                                                </div>
                                                {{-- AddressLine 1 --}}
                                                <div class="form-group row mb-1">
                                                    <label for="addressLine1"
                                                        class="col-sm-3 col-lg-4 col-form-label col-form-label-sm"><i class="fa fa-map-marker" aria-hidden="true"></i> AddressLine 1 <span class="text-danger">*</span></label>
                                                    <div class="col-sm-9 col-lg-8">
                                                        <textarea name="addressLine1" id="addressLine1" maxlength="50" class="form-control form-control-sm">{{ old('addressLine1') }}</textarea>
                                                    </div>
                                                </div>
                                                {{-- AddressLine 2 --}}
                                                <div class="form-group row mb-1">
                                                    <label for="addressLine2" class="col-sm-3 col-lg-4 col-form-label col-form-label-sm"><i class="fa fa-map-marker" aria-hidden="true"></i> AddressLine 2</label>
                                                    <div class="col-sm-9 col-lg-8">
                                                        <textarea name="addressLine2" id="addressLine2" class="form-control form-control-sm">{{ old('addressLine2') }}</textarea>
                                                    </div>
                                                </div>
                                                {{-- Country Code and Country Name --}}
                                                <div class="form-group row mb-1">
                                                    <label for="country" class="col-sm-3 col-md-3 col-lg-4 col-form-label col-form-label-sm"><i class="fa fa-globe"
                                                            aria-hidden="true"></i> Country <span class="text-danger">*</span></label>
                                                    <div class="col col-sm-9 col-md-9 col-lg-8">
                                                            <select class="form-control form-control-sm" name="countryID" id="countryID">
                                                                <option value=""></option>
                                                                @forelse ($countries as $country)
                                                                    <option value="{{ $country->CountryID }}" id="{{ $country->CountCode }}" @if(old('countryID')==$country->CountryID) selected @endif >{{ $country->CountryName }}</option>
                                                                @empty
                                                                @endforelse
                                                            </select>
                                                            <input type="hidden" name="countryName" id="countryName" value="{{ old('countryName') }}">
                                                            <input type="hidden" name="countryCode" id="countryCode" value="{{ old('countryCode') }}">
                                                    </div>
                                                </div>
                                                {{-- Province zipcode --}}
                                                <div class="form-group row ">
                                                    <label for="province" class="col-sm-3 col-lg-4 col-form-label col-form-label-sm"><i class="fa fa-map-pin" aria-hidden="true"></i> Province / State</label>
                                                    <div class="col col-sm-5 col-lg-4">
                                                        <input type="text" class="form-control form-control-sm " name="provinceName" id="provinceName" value="{{ old('provinceName') }}" placeholder="">
                                                        <input type="hidden" name="provinceID" id="provinceID">
                                                        <input type="hidden" name="provinceCode" id="provinceCode">
                                                        {{-- <select class="form-control form-control-sm" name="province" id="provinceSelect">
                                                            <option value="" disabled>Enter Province</option>
                                                        </select> --}}
                                                    </div>
                                                    <div class="col col-sm-4 col-lg-4">
                                                        <input type="text" class="form-control form-control-sm number_input"
                                                        value="{{ old('zipCode') }}"
                                                        name="zipCode" id="zipCode" placeholder="Zip Code">
                                                    </div>
                                                </div>
                                                {{-- City --}}
                                                <div class="form-group row mb-1">
                                                    <label for="cityName" class="col-sm-3 col-lg-4 col-form-label col-form-label-sm"><i class="fas fa-city"></i> City</label>
                                                    <div class="col-sm-9 col-lg-8">
                                                        <input type="text" class="form-control form-control-sm" value="{{ old('cityName') }}"
                                                        name="cityName" id="cityName">
                                                        {{-- <select class="form-control form-control-sm" name="cityID" id="citySelect">
                                                            <option value="" disabled selected>Select City</option>
                                                        </select> --}}
                                                        <input type="hidden" name="cityID" id="cityID" value="{{ old('cityID') }}">
                                                        <input type="hidden" name="cityCode" id="cityCode" value="{{ old('cityCode') }}">
                                                    </div>
                                                </div>

                                            </fieldset>
                                        </div>
                                        <div class="col-md-6 col-sm-12">
                                            <fieldset class="border p-2 card shadow-lg mb-0" >
                                                <legend class="w-auto p-0 m-0">Contact Details</legend>
                                                <div class="form-group row mb-1">
                                                    <label for="person" class="col col-sm-3  col-md-3 col-lg-4 col-form-label col-form-label-sm"><i class="fa fa-user" aria-hidden="true"></i> Person <span class="text-danger">*</span></label>
                                                    {{-- <div class="col-sm-2 col-md-3 col-lg-2">
                                                        <input type="text" class="form-control form-control-sm"
                                                            name="namePrefix" id="namePrefix" placeholder="Prefix">
                                                    </div> --}}
                                                    <div class="col col-sm-4 col-md-4 col-lg-4">
                                                        <input type="text" class="form-control form-control-sm"
                                                        value="{{ old('firstName') }}"
                                                            name="firstName"
                                                            id="firstName" placeholder="First Name">
                                                    </div>
                                                    <div class="col col-sm-5 col-md-5 col-lg-4">
                                                        <input type="text" class="form-control form-control-sm"
                                                        value="{{ old('lastName') }}"
                                                            name="lastName"
                                                            id="lastName" placeholder="Last Name">
                                                    </div>
                                                </div>
                                                <div class="form-group row mb-1">
                                                    <label for="department" class="col col-sm-3 col-md-3 col-lg-4 col-form-label col-form-label-sm"><i class="fa fa-sitemap" aria-hidden="true"></i> Department</label>
                                                    <div class="col col-sm-9 col-md-9 col-lg-8">
                                                        <input type="text" class="form-control form-control-sm"
                                                        value="{{ old('department') }}"
                                                            name="department" id="department" placeholder="Department">
                                                    </div>
                                                </div>
                                                {{-- phone no --}}
                                                <div class="form-group row mb-1">
                                                    <label for="phoneNo"
                                                        class="col col-sm-3  col-md-3 col-lg-4 col-form-label col-form-label-sm"><i class="fa fa-phone" aria-hidden="true"></i> Phone No.<span class="text-danger">*</span></label>
                                                    <div class="col col-sm-9 col-md-9 col-lg-8">
                                                        <input type="text" class="form-control form-control-sm number_input"
                                                        value="{{ old('phoneNo') }}"
                                                            name="phoneNo" id="phoneNo" placeholder="Phone Number">
                                                    </div>
                                                </div>
                                                {{-- email --}}
                                                <div class="form-group row mb-0 pb-0">
                                                    <label for="contractEmail"
                                                        class="col col-sm-3 col-lg-4 col-form-label col-form-label-sm">
                                                        <i class="fa fa-envelope" aria-hidden="true"></i> Email</label>
                                                    <div class="col col-sm-9 col-lg-8">
                                                        <input type="email" name="contractEmail" id="contractEmail"
                                                        value="{{ old('contractEmail') }}"
                                                        class="form-control form-control-sm" placeholder="Enter Email">
                                                    </div>
                                                </div>
                                            </fieldset>
                                            {{-- Other Information --}}
                                            <fieldset class="border p-2 card shadow-lg mb-0">
                                                <legend class="w-auto p-0 m-0">Other information</legend>
                                                {{-- Client --}}
                                                <div class="form-group row mb-1">
                                                    <label for="client"
                                                        class="col-sm-3 col-lg-4 col-form-label col-form-label-sm"><i class="fa fa-user" aria-hidden="true"></i> Client <span class="text-danger" id="clientSpan">*</span></label>
                                                    <div class="col-sm-9 col-lg-8">
                                                        <div class="row">
                                                            <div class="col">
                                                                <select name="clientOption" id="clientOption" class="form-control form-control-sm" disabled>
                                                                    <option value="fromMaster" selected>Search Client</option>
                                                                    <option value="fromRequest">From Request</option>
                                                                </select>
                                                            </div>
                                                            <div class="col">
                                                                <input type="text" name="client" id="client" value="{{ old('client') }}" class="form-control form-control-sm" required disabled>
                                                                <input type="text" name="clientOptionVal" id="clientOptionVal" value="{{ old('clientOptionVal') }}" class="form-control form-control-sm" disabled>
                                                                <input type="hidden" name="requestID" value="{{ old('requestID') }}" id="requestID">
                                                                <input type="hidden" name="requestCode" value="{{ old('requestCode') }}" id="requestCode">
                                                            </div>
                                                        </div>

                                                    </div>
                                                </div>
                                                <div class="form-group row pb-0 mb-0">
                                                    <label for="specialInstructions" class="col-sm-3 col-lg-4 col-form-label col-form-label-sm"><i class="fas fa-comments"></i> Special
                                                        Instructions</label>
                                                    <div class="col-sm-9 col-lg-8">
                                                        <textarea class="form-control form-control-sm" name="specialInstruction" id="specialInstruction" placeholder="Special Instructions">{{ old('specialInstruction') }}</textarea>
                                                    </div>
                                                </div>
                                            </fieldset>
                                        </div>
                                    </div>
                                    <button class="btn btn-primary m-2 btn-sm float-right" name="next" type="button" onclick="stepper.next()">Next <i class="fa fa-step-forward" aria-hidden="true"></i></button>
                                    <button type="button" name="save-step1" id="save-step1" class="btn btn-success btn-sm float-right mt-2" data-toggle="tooltip" data-placement="top" title="Save"><i class="fa fa-plus-circle" aria-hidden="true"></i> Save for Later</button>
                                </div>
                                <div id="office-details" class="content active dstepper-block" role="tabpanel"
                                    aria-labelledby="office-details-trigger">
                                    <div class="row">
                                        <div class="col-md-4 col-lg-4 col-sm-12">
                                            <fieldset class="border p-2 card shadow-sm mb-0 pt-0 pb-0">
                                                <legend class="w-auto p-0 m-0">Office Mapping</legend>
                                                <div class="form-group row m-0">
                                                    <label for="officeCountry" class="col-sm-6 col-lg-4 col-md-4 col-form-label col-form-label-sm">Office <span
                                                            class="text-danger">*</span></label>
                                                    <div class="col-sm-6 col-lg-8 col-md-8">
                                                        @if (Auth::user()->can('create-client-for-any-office'))

                                                            <select class="form-control form-control-sm" name="officeCountry" id="officeCountry">
                                                                <option value="" disabled selected></option>
                                                                @forelse ($offices as $office)
                                                                    <option code="{{ $office->Code }}" value="{{ $office->Name }}" id="{{ $office->ID }}" @if (old('officeCountry') == $office->Name) selected @endif>{{ $office->Name }}
                                                                    </option>
                                                                @empty

                                                                @endforelse
                                                            </select>
                                                            <input type="hidden" name="officeCountryID"  id="officeCountryID" >
                                                        @else
                                                            <input type="text" name="officeCountryDisp" code="{{ Auth::user()->Office->SCMOfficeCode }}" id="officeCountryDisp" value="{{ Auth::user()->Office->Name ?? '' }}" disabled>
                                                            <input type="hidden" name="officeCountryID"  id="officeCountryID" value="{{ Auth::user()->Office->ID ?? '' }}">
                                                            <input type="hidden" name="officeCode" value='{{ Auth::user()->Office->Code }}'>

                                                            <input type="hidden" name="officeCountry"  id="officeCountry" class="form-control" value="{{ Auth::user()->Office->Name ?? '' }}">
                                                            <input type="hidden" name="officeCountryName" id="officeCountryName" class="form-control" value="{{ Auth::user()->Office->Name ?? '' }}" disabled>
                                                        @endif
                                                    </div>
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
                                                            <input class="form-check-input ml-3" type="checkbox" name="includeCountryCheckBox" id="includeCountryCheckBox">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row mb-0 pb-0">
                                                    <div class="col-md-6 col-sm-12 col-lg-6">
                                                        {{-- port of loading Air --}}
                                                        <div class="form-group  pt-0 mb-0">
                                                            <div class="">
                                                                <label for="portOfLoadingAir" class="col-form-label col-form-label-sm">Port Of Loading(Air) </label>
                                                                <input type="text" class="form-control form-control-sm" value="{{ old('portOfLoadingAir') }}"
                                                                name="portOfLoadingAir" id="portOfLoadingAir">
                                                                <input type="hidden" name="portOfLoadingAirID" id="portOfLoadingAirID"
                                                                value="{{ old('portOfLoadingAirID') }}">
                                                                {{-- <select class="form-control form-control-sm" name="portOfLoadingAir" id="portOfLoadingAir">
                                                                    <option value="" disabled selected></option>
                                                                </select> --}}
                                                            </div>
                                                        </div>
                                                        {{-- port of Discharge Air --}}
                                                        <div class="form-group mb-0">
                                                            <div class="">
                                                                <label for="portOfDischargeAir" class="col-form-label col-form-label-sm">Port Of Discharge(Air)</label>
                                                                <input type="text" class="form-control form-control-sm"
                                                                value="{{ old('portOfDischargeAir') }}"
                                                                name="portOfDischargeAir" id="portOfDischargeAir" >

                                                                <input type="hidden" class="form-control form-control-sm"
                                                                value="{{ old('portOfDischargeAirID') }}"
                                                                 name="portOfDischargeAirID" id="portOfDischargeAirID" >
                                                                {{-- <select class="form-control form-control-sm" name="portOfDischargeAir" id="portOfDischargeAir">
                                                                    <option value="" disabled selected></option>
                                                                </select> --}}
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6 col-sm-12 col-lg-6">
                                                        {{-- port of loading sea --}}
                                                        <div class="form-group row mb-1">
                                                            <div class="col-md-12 mb-0 pb-0">
                                                                <label for="portOfLoadingSea" class="col-form-label col-form-label-sm">Port Of Loading(Sea) </label>
                                                                <input type="text" class="form-control form-control-sm"
                                                                value="{{ old('portOfLoadingSea') }}"
                                                                name="portOfLoadingSea" id="portOfLoadingSea">
                                                                <input type="hidden" class="form-control form-control-sm" value="{{ old('portOfLoadingSeaID') }}"
                                                                name="portOfLoadingSeaID" id="portOfLoadingSeaID">
                                                                {{-- <select class="form-control form-control-sm" name="portOfLoadingSea" id="portOfLoadingSea" >
                                                                    <option value="" disabled selected></option>
                                                                </select> --}}
                                                            </div>
                                                        </div>
                                                        {{-- port of Discharge Sea --}}
                                                        <div class="form-group row pb-0 mb-0">
                                                            <div class="col-md-12 pb-0 mb-0">
                                                                <label for="portOfDischargeSea" class="col-form-label col-form-label-sm">Port Of Discharge(Sea)</label>
                                                                <input type="text" class="form-control form-control-sm"
                                                                value="{{ old('portOfDischargeSea') }}"
                                                                name="portOfDischargeSea" id="portOfDischargeSea">
                                                                <input type="hidden" class="form-control form-control-sm"
                                                                value="{{ old('portOfDischargeSeaID') }}"
                                                                 name="portOfDischargeSeaID" id="portOfDischargeSeaID">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </fieldset>
                                            <fieldset class="border p-2 card shadow-lg mb-0 pt-0 pb-0">
                                                <legend>File Upload</legend>
                                                <label for="attachmentFile">Attachment <i class="fa fa-paperclip"></i><span style="color: red;">*</span>
                                                    <button type="button" class="btn btn-sm btn-outline-warning pull-right w-20 addAttachment" id="addFile">
                                                        <i class="fa fa-plus"></i> File
                                                    </button>
                                                </label>
                                                <div class="input-group hdtuto control-group lst increment">
                                                    <input type="file" name="attachmentNames[]" class="myfrm form-control mr-1 form-control-sm" multiple required>
                                                    <input type="text" name="attachmentDescription[]" class="myfrm form-control form-control-sm"
                                                        placeholder="Description">
                                                </div>
                                                <div id="attachClone"></div>
                                                <div class="clone d-none">
                                                    <div class="hdtuto control-group lst input-group mt-1" style="">
                                                        <input type="file" name="attachmentNames[]"
                                                            class="myfrm form-control mr-1 form-control-sm">
                                                        <input type="text" name="attachmentDescription[]"
                                                            class="myfrm form-control form-control-sm" placeholder="Description">
                                                        <div class="input-group-btn">
                                                            <button class="btn btn-danger btn-sm" type="button" style="">
                                                                <i class="fldemo fa fa-minus"></i> </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </fieldset>
                                        </div>
                                        <div class="col-md-8 col-lg-8 col-sm-12">
                                            <fieldset class="border p-2 card shadow-lg mb-0 pt-0 pb-0">
                                                <legend class="w-auto p-0 m-0">Client</legend>
                                                <div class="row">
                                                    {{-- first detail client col --}}
                                                    <div class="col-md-6 col-sm-12 col-lg-6">
                                                        {{-- Sales Pic --}}
                                                        <div class="form-group row mb-1">
                                                            <label for="salesPIC" class="col-sm-3 col-lg-4 col-form-label col-form-label-sm">Sales PIC<span class="text-danger">*</span></label>
                                                            <div class="col-sm-9 col-lg-8">
                                                                <input type="text" required class="form-control form-control-sm salesPIC" value="{{ old('salesPIC') }}" name="salesPIC" id="salesPIC">
                                                                <input type="hidden" name="salesPICID" id="salesPICID" value="{{ old('salesPICID') }}">
                                                                {{-- <select class="form-control form-control-sm" name="salesPIC" id="salesPIC" required>
                                                                    <option value="" disabled selected></option>
                                                                </select> --}}
                                                            </div>
                                                        </div>
                                                        {{-- Inco Terms --}}
                                                        <div class="form-group row mb-1">
                                                            <label for="incoTermID" class="col-sm-3 col-lg-4 col-form-label col-form-label-sm">Inco Terms</label>
                                                            <div class="col-sm-9 col-lg-8">
                                                                <select class="form-control form-control-sm" name="incoTermID" id="incoTermID">
                                                                    <option value="" disabled selected></option>
                                                                    @forelse ($incoterms as $incoterm)
                                                                        <option value="{{ old('incoTermID', $incoterm->IncoTermID)  }}" id="{{  $incoterm->IncoTermCode  }}" @if(old('incoTermID') == $incoterm->IncoTermID) selected @endif>{{ $incoterm->Name }}</option>
                                                                    @empty

                                                                    @endforelse
                                                                </select>
                                                                <input type="hidden" name="incoTermName" id="incoTermName" value="{{ old('incoTermName') }}">
                                                                <input type="hidden" name="incoTermCode" id="incoTermCode" value="{{ old('incoTermCode') }}">
                                                            </div>
                                                        </div>
                                                        {{-- Notify party 1 --}}
                                                        <div class="form-group row mb-1">
                                                            <label for="notifyParty1"
                                                                class="col-sm-3 col-lg-4 col-form-label col-form-label-sm">Notify Party 1</label>
                                                            <div class="col-sm-9 col-lg-8">
                                                                <input type="text" class="form-control form-control-sm" value="{{ old('notifyParty1') }}"
                                                                name="notifyParty1" id="notifyParty1">
                                                                <input type="hidden" name="notifyParty1ID" id="notifyParty1ID">
                                                                <input type="hidden" name="notifyParty1Code" id="notifyParty1Code">
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
                                                        <div class="form-group row mb-1">
                                                            <label for="accountPIC" class="col-sm-3 col-lg-4 col-form-label col-form-label-sm">Account PIC</label>
                                                            <div class="col-sm-9 col-lg-8">
                                                                <input type="text" class="form-control form-control-sm"
                                                                 value="{{ old('accountPIC') }}" name="accountPIC" id="accountPIC" placeholder="">
                                                                 <input type="hidden" name="accountPICID" id="accountPICID"
                                                                 value="{{ old('accountPICID') }}">
                                                                {{-- <select class="form-control form-control-sm" name="accountPIC" id="accountPIC">
                                                                    <option value="" disabled selected></option>
                                                                </select> --}}
                                                            </div>
                                                        </div>
                                                        {{-- payment Terms --}}
                                                        <div class="form-group row mb-1">
                                                            <label for="paymentTermsID"
                                                                class="col-sm-3 col-lg-4 col-form-label col-form-label-sm pt-0 mt-0">Pay Terms</label>
                                                            <div class="col-sm-9 col-lg-8 ">
                                                                <select class="form-control form-control-sm" name="paymentTermsID" id="paymentTermsID">
                                                                    <option value="" disabled selected></option>
                                                                    @forelse ($payTerms as $payTerm)
                                                                        <option value="{{ old('paymentTermsID', $payTerm->IncoTermID)  }}" id="{{ $payTerm->IncoTermCode }}" @if(old('paymentTermsID') == $payTerm->IncoTermID) selected @endif>{{ $payTerm->Name }}</option>
                                                                    @empty

                                                                    @endforelse
                                                                </select>
                                                                <input type="hidden" name="paymentTermsName" id="paymentTermsName">
                                                                <input type="hidden" name="paymentTermsCode" id="paymentTermsCode">
                                                            </div>
                                                        </div>
                                                        {{-- Notify party 2 --}}
                                                        <div class="form-group row mb-1">
                                                            <label for="notifyParty2"
                                                                class="col-sm-3 col-lg-4 col-form-label col-form-label-sm">Notify Party 2</label>
                                                            <div class="col-sm-9 col-lg-8">
                                                                <input type="text" class="form-control form-control-sm" value="{{ old('notifyParty2') }}"
                                                                name="notifyParty2" id="notifyParty2" placeholder="">
                                                                <input type="hidden" name="notifyParty2ID" id="notifyParty2ID">
                                                                <input type="hidden" name="notifyParty2Code" id="notifyParty2Code">
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
                                                    <div class="form-group row mb-0">
                                                        <label for="forwarderName" class="col-sm-3 col-md-4 col-lg-2 col-form-label col-form-label-sm">Forwarder</label>
                                                        <div class="col-sm-9 col-lg-10 col-md-8">
                                                            <input type="text" class="form-control form-control-sm" name="forwarderName" id="forwarderName"
                                                            value="{{ old('forwarderName') }}">
                                                            <input type="hidden" class="form-control form-control-sm"
                                                            name="forwarderID" id="forwarderID" value="{{ old('forwarderID') }}">
                                                            {{-- <select class="form-control form-control-sm" name="forwarder" id="forwarder">
                                                                <option value="" disabled selected></option>
                                                            </select> --}}
                                                        </div>
                                                    </div>
                                                </div>
                                            </fieldset>
                                            <fieldset class="border p-2 card shadow-lg mb-0 pt-0 pb-0">
                                                <legend class="w-auto p-0 m-0">Debtor/Vendor</legend>
                                                <div class="row">
                                                    <div class="col-md-6 col-sm-12 col-lg-6">
                                                        {{-- Debtor Type --}}
                                                        <div class="form-group row mb-1">
                                                            <div class="col-md-12">
                                                                <label for="debtorType" class="col-form-label col-form-label-sm">Debtor Type</label>
                                                                <select class="form-control form-control-sm" name="debtorType" id="debtorType">
                                                                    <option value="" disabled selected></option>
                                                                    {{-- <option value="Cash Customer" @if(old('debtorType') == 'Cash Customer') selected @endif>Cash Customer</option>
                                                                    <option value="Approved Debtor" @if(old('debtorType') == 'Approved Debtor') selected @endif>Approved Debtor</option> --}}
                                                                </select>
                                                            </div>
                                                        </div>
                                                        {{-- Billing Currency --}}
                                                        <div class=" row mb-0 pb-0">
                                                            <div class="form-group col-md-6 mb-0 pb-0">
                                                                <label for="billingCurrency" class="col-form-label col-form-label-sm">Billing Currency <span style="color: red;">*</span> </label>
                                                                <input type="hidden" class="form-control form-control-sm" value="{{ old('billingCurrencyCode') }}"
                                                                name="billingCurrencyCode" id="billingCurrencyCode">
                                                                {{-- TODO:autocomplete billing currency --}}
                                                                <select class="form-control form-control-sm" name="billingCurrency" id="billingCurrency" required>
                                                                    <option value="" disabled selected></option>
                                                                </select>
                                                            </div>
                                                             {{-- Credit Days --}}
                                                            <div class="form-group col-md-6 mb-0 pb-0">
                                                                <label for="creditDays"
                                                                    class="col-form-label col-form-label-sm">Credit Days</label>  <span style="color: red;">*</span>
                                                                <input type="text" class="form-control form-control-sm number_input"
                                                                    value="{{ old('creditDays') }}"
                                                                    name="creditDays" id="creditDays"
                                                                    required min="0" oninput="this.value = Math.abs(this.value)">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6 col-sm-12 col-lg-6 mb-0 pb-0">
                                                         {{-- Vendor Type --}}
                                                         <div class="form-group row mb-1 mb-0 pb-0 ">
                                                            <div class="col-md-12">
                                                                <label for="vendorType" class="col-form-label col-form-label-sm">Vendor Type</label>
                                                                <select class="form-control form-control-sm" name="vendorType" id="vendorType">
                                                                    <option value="" disabled selected></option>
                                                                    {{-- <option value="Cash Customer" @if(old('vendorType') == 'Cash Customer') selected @endif>Cash Customer</option>
                                                                    <option value="Approved Debtor" @if(old('vendorType') == 'Approved Debtor') selected @endif>Approved Debtor</option> --}}
                                                                </select>
                                                            </div>
                                                        </div>
                                                        {{-- Credit Limit --}}
                                                        <div class="form-group">
                                                            <div class="row mb-0 pb-0 ">
                                                                <div class="col-sm-6 col-md-6 col-lg-6 mb-0 pb-0">
                                                                    <label for="minCreditLimit"
                                                                        class="col-form-label col-form-label-sm">Credit Limit <span style="color: red;">*</span></label>
                                                                    <input type="number"
                                                                        class="form-control form-control-sm number_input"
                                                                        value="{{ old('maxCreditLimit') }}"
                                                                        name="maxCreditLimit" id="maxCreditLimit"
                                                                        placeholder="0.00"
                                                                        required >
                                                                </div>
                                                                <div class="col-sm-6 col-md-6 col-lg-6 ">
                                                                    <label for="baseCurrency"
                                                                        class="col-form-label col-form-label-sm">Base
                                                                        Currency</label>
                                                                    <input type="text" class="form-control form-control-sm" name="baseCurrency" id="baseCurrency" disabled>
                                                                    <input type="hidden" class="form-control form-control-sm" name="baseCurrencyCode" id="baseCurrencyCode" value="{{old('baseCurrencyCode') }}">
                                                                    <input type="hidden" class="form-control form-control-sm" name="baseCurrencyName" id="baseCurrencyName" value="{{old('baseCurrencyName') }}">
                                                                    <input type="hidden" class="form-control form-control-sm" name="baseCurrencyID" id="baseCurrencyID" value="{{old('baseCurrencyID') }}">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                {{-- licence type --}}
                                                <div class="form-group row mb-0 mt-0 p-0 ">
                                                    <div class="col col-sm-6 col-lg-6 col-md-6 m-0">
                                                        <div class="row">
                                                            <label for="licence" class="col-md-3 col-form-label col-form-label-sm pr-0">Licence Type</label> <span style="color: red;">*</span>
                                                            <div class="col-md-8">
                                                                <select class="form-control form-control-sm" name="licenceTypeID" id="licenceTypeID" required>
                                                                    <option value="" disabled selected></option>
                                                                        @forelse ($licenceTypes as $type)
                                                                            <option value="{{ $type->LicenseID }}" id="{{ $type->LicenseCode }}" @if (old('licenceTypeID') == $type->LicenseID)  selected @endif>
                                                                                {{ $type->LicenseName }}</option>
                                                                        @empty
                                                                    @endforelse
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col col-sm-6 col-lg-6 col-md-6">
                                                        <input type="hidden" class="form-control form-control-sm"
                                                            value="{{ old('licenceTypeCode') }}" name="licenceTypeCode"
                                                            id="licenceTypeCode">
                                                        <input type="text" class="form-control form-control-sm"
                                                            value="{{ old('licenceTypeValue') }}"
                                                            name="licenceTypeValue" id="licenceTypeValue" required>
                                                    </div>
                                                </div>
                                            </fieldset>
                                            <fieldset class="border p-2 card shadow-lg mb-0 pt-0 pb-0">
                                                <legend class="w-auto p-0 m-0"> Additional Information</legend>
                                                <div class="row mb-0">
                                                   <div class="col-md-6 col-sm-12 col-lg-6 mb-0">
                                                            {{-- Tarrif --}}
                                                            <div class="form-group row mb-0">
                                                                <div class="col-md-12">
                                                                    <label for="tarrif"
                                                                        class="col-form-label col-form-label-sm">Tarrif</label>
                                                                        {{-- TODO: autocomplete tarrif list --}}
                                                                    <select class="form-control form-control-sm" name="tarrif" id="tarrif" required>
                                                                        <option value="" selected disabled></option>
                                                                        @forelse ($tarrifs as $tarrif)
                                                                            <option value="{{ $tarrif->TARIFF }}" @if(old('tarrif') == $tarrif->TARIFF) selected @endif>{{ $tarrif->TARIFF }}</option>
                                                                        @empty
                                                                        @endforelse
                                                                    </select>

                                                                </div>
                                                            </div>
                                                            {{-- FSC Type & Rate --}}
                                                            <div class="form-group row mb-0">
                                                                <div class="col-md-12">
                                                                    <label for="phoneNo" class="col-form-label col-form-label-sm"> FSC Type & Rate</label>
                                                                    <div class="row">
                                                                        <div class="col col-sm-6 col-lg-6">
                                                                            <select class="form-control form-control-sm" name="fscType" id="fscType" required>
                                                                                <option value="" selected disabled>Select Type</option>
                                                                                <option value="Fixed" @if(old('fscType') == 'Fixed') selected @endif>Fixed</option>
                                                                                <option value="Variable" @if(old('fscType') == 'Variable') selected @endif>Variable</option>
                                                                            </select>
                                                                        </div>
                                                                        <div class="col col-sm-6 col-lg-6">
                                                                            <input type="text" class="form-control form-control-sm number_input"
                                                                            value="{{ old('fscRate') }}"
                                                                            name="fscRate" id="fscRate" placeholder="Rate" required>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6 col-sm-12 col-lg-6 mb-0">
                                                            {{-- Billing Type --}}
                                                            <div class="form-group row mb-0">
                                                                <div class="col-md-12">
                                                                    <label for="billingType"
                                                                        class="col-form-label col-form-label-sm">Billing Type</label>
                                                                    <select class="form-control form-control-sm"
                                                                        name="billingType" id="billingType" required>
                                                                        <option value="" selected>Select Billing Type </option>
                                                                        <option value="Periodic" @if(old('billingType') == 'Periodic') selected @endif>Periodic</option>
                                                                        <option value="Shipment" @if(old('billingType') == 'Shipment') selected @endif>Shipment</option>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            {{-- discount --}}
                                                            <div class="form-group row mb-0">
                                                                <div class="col-md-12">
                                                                    <label for="discount"
                                                                        class="col-form-label col-form-label-sm">Discount in %</label>
                                                                    <input type="text" class="form-control form-control-sm number_input"
                                                                    value="{{ old('discount') }}"
                                                                    name="discount" id="discount" placeholder="Discount" required>
                                                                </div>
                                                            </div>
                                                        </div>
                                                </div>
                                            </fieldset>
                                        </div>
                                    </div>
                                    <button class="btn btn-primary m-2 btn-sm" type="button" name="previous" onclick="stepper.previous()"><i class="fa fa-step-backward" aria-hidden="true"></i> Previous</button>
                                    @if (Auth::user()->can('create-client-party'))
                                        <button class="btn btn-primary m-2 btn-sm float-right" type="button" name="next" onclick="stepper.next()">Next <i class="fa fa-step-forward" aria-hidden="true"></i></button>
                                    @else
                                        <button type="submit" name="submitBtn" value="submitBtn" class="btn btn-outline-success btn-sm float-right mt-2" data-toggle="tooltip" data-placement="top" title="Will Be Submitted for Creating"><i class="fa fa-thumbs-up" aria-hidden="true"></i> Sumbit</button>
                                    @endif
                                    <button type="button" name="save-step2" id="save-step2" class="btn btn-success btn-sm float-right m-2" data-toggle="tooltip" data-placement="top" title="Save"><i class="fa fa-plus-circle" aria-hidden="true" ></i> Save for Later</button>
                                </div>
                                @if (Auth::user()->can('create-client-party'))
                                    <div id="admin-details" class="content" role="tabpanel" aria-labelledby="admin-details-trigger">
                                        <div class="row justify-content-center">
                                            <div class="com-md-6 col-xl-6 col-sm-12 col-lg-6">
                                                <fieldset class="border p-2 card shadow-lg mb-0 pt-0 pb-0">
                                                    <legend class="w-auto p-0 m-0"></legend>
                                                    <table class="table table-sm">
                                                        <thead>
                                                            <th>Entity</th>
                                                            <th>Create</th>
                                                            <th>Select</th>
                                                            <th>Code</th>
                                                        </thead>
                                                        <tr>
                                                            <td>Client</td>
                                                            <td><input type="checkbox" name="clientCheck" id="clientCheck" =""></td>
                                                            <td>
                                                                <input type="text" name="clientInput" id="clientInput" value="{{ old('clientInput') }}" class="form-control form-control-sm"  required>
                                                                <input type="hidden" name="clientID" id="clientID" value="{{ old('clientID') }}">
                                                                <input type="hidden" name="clientCode" id="clientCode" value="{{ old('clientCode') }}">
                                                                {{-- <select name="clientID" id="clientID"class="form-control form-control-sm">
                                                                    <option value=""></option>
                                                                </select> --}}
                                                            </td>
                                                            <td id="client_Input"></td>
                                                        </tr>
                                                        <tr>
                                                            <td>Shipper</td>
                                                            <td><input type="checkbox" name="shipperCheck" id="shipperCheck" ></td>
                                                            <td>
                                                                <input type="text" name="shipperInput" id="shipperInput" class="form-control form-control-sm"  required>
                                                                <input type="hidden" name="shipperID" id="shipperID">
                                                                <input type="hidden" name="shipperCode" id="shipperCode">
                                                                {{-- <select name="shipperInput" id="shipperInput"class="form-control form-control-sm">
                                                                    <option value=""></option>
                                                                </select> --}}
                                                            </td>
                                                            <td id="shipper_Input"></td>
                                                        </tr>
                                                        <tr>
                                                            <td>Consignee</td>
                                                            <td><input type="checkbox" name="consigneeCheck" id="consigneeCheck"></td>
                                                            <td>
                                                                <input type="text" name="consigneeInput" id="consigneeInput" class="form-control form-control-sm"  required>
                                                                <input type="hidden" name="consigneeID" id="consigneeID">
                                                                <input type="hidden" name="consigneeCode" id="consigneeCode">
                                                                {{-- <select name="consigneeInput" id="consigneeInput"class="form-control form-control-sm">
                                                                    <option value=""></option>
                                                                </select> --}}
                                                            </td>
                                                            <td id="consignee_Input"></td>
                                                        </tr>
                                                        <tr>
                                                            <td>Other</td>
                                                            <td><input type="checkbox" name="otherCheck" id="otherCheck" ></td>
                                                            <td>
                                                                <input type="text" name="otherInput" id="otherInput" class="form-control form-control-sm"  required>
                                                                <input type="hidden" name="otherID" id="otherID">
                                                                <input type="hidden" name="otherCode" id="otherCode">
                                                                {{-- <select name="other" id="other"class="form-control form-control-sm">
                                                                    <option value=""></option>
                                                                </select> --}}
                                                            </td>
                                                            <td id="other_Input"></td>
                                                        </tr>
                                                        <tr>
                                                            <td>Debtor</td>
                                                            <td><input type="checkbox" name="debtorCheck" id="debtorCheck"></td>
                                                            <td>
                                                                <input type="text" name="debtor" id="debtor" class="form-control form-control-sm" required>
                                                                <input type="hidden" name="debtorID" id="debtorID">
                                                                <input type="hidden" name="debtorCode" id="debtorCode">
                                                                {{-- <select name="debtor" id="debtor"class="form-control form-control-sm">
                                                                    <option value=""></option>
                                                                </select> --}}
                                                            </td>
                                                        <td id="debtor_code"></td>
                                                        </tr>
                                                        <tr>
                                                            <td>Vendor</td>
                                                            <td><input type="checkbox" name="vendorCheck" id="vendorCheck"></td>
                                                            <td>
                                                                <input type="text" name="vendor" id="vendor" class="form-control form-control-sm" required>
                                                                <input type="hidden" name="vendorID" id="vendorID">
                                                                <input type="hidden" name="vendorCode" id="vendorCode">
                                                                {{-- <select name="vendor" id="vendor" class="form-control form-control-sm">
                                                                    <option value=""></option>
                                                                </select> --}}
                                                            </td>
                                                            <td id="vendor_code"></td>
                                                        </tr>
                                                    </table>
                                                </fieldset>
                                            </div>
                                        </div>
                                        <button class="btn btn-primary mt-2" type="button" name="previous" onclick="stepper.previous()"><i class="fa fa-step-backward" aria-hidden="true"></i> Previous</button>
                                       @if (Auth::user()->can('create-client-party'))
                                            <button type="submit" name="createBtn" value="true" class="btn btn-outline-success float-right m-2 btn-sm" data-toggle="tooltip" data-placement="top" title="Save and Create On SCM"><i class="fa fa-cogs" aria-hidden="true"></i> Create</button>
                                        @endif
                                        <button type="submit" name="submitBtn" id="submitBtn" class="btn btn-success float-right mt-2 btn-sm" data-toggle="tooltip" data-placement="top" title="Save"><i class="fa fa-plus-circle" aria-hidden="true"></i> Save For Later</button>
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
            /**/ font-weight: bold;
        }
        label {
            font-weight:bold;
            /* font-size: 72px; */
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
        #loader  {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            width: 100%;
            background: rgba(0,0,0,0.75) url('{{ asset('site_imgs/loading2.gif') }}') no-repeat center center;
            z-index: 10000;
            }

    </style>
@stop

@section('js')
<script>
    /*Ajax setup CSRF*/
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
</script>
<script type="text/javascript">
    $("#maxCreditLimit").on("input", function() {
        let maxCreditLimit = $(this).val();
        if (maxCreditLimit.startsWith("0")) {
            $(this).val("1" + maxCreditLimit.slice(1));
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
        value: '{{ auth()->user()->can("edit-client-party") }}'
    }
</script>

<script type="text/javascript">
   let init = 1;
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
                var allowedExtensions = /(\.jpg|\.jpeg|\.png|\.gif|\.pdf|\.doc|\.xls|\.docx|\.xlsx)$/i;
                if (!allowedExtensions.exec(fileInputName.value) || fileInputDescription.value == '') {
                    alert('Either Invalid file type / No Description');fileInputName.value = '';
                    return false;
                }else if(fileInputName.value != ''){
                    alert('Kindly Enter the File Name and Description');
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
</script>
<script src="{{ asset('js/client-party/show-create-client-party.js') }}"></script>
@stop
