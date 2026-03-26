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
                <div class="card-body p-0">
                    <div class="bs-stepper linear">
                        <div class="row justify-content-center p-0">
                            <div class="bs-stepper-header col-md-10 col-sm p-0 mt-2 pr-3 pl-3 " style="border-radius: 10px"
                                role="tablist">
                                <!-- your steps here -->
                                <div class="step" data-target="#basic-details">
                                    <button type="button" class="step-trigger pb-0 pt-0" role="tab"
                                        aria-controls="basic-details" id="basic-details-trigger" aria-selected="false"
                                        disabled="disabled">
                                        <span class="bs-stepper-circle">1</span>
                                        <span class="bs-stepper-label">Basic Details</span>
                                    </button>
                                </div>
                                <div class="line"></div>
                                <div class="step" data-target="#office-details">
                                    <button type="button" class="step-trigger pb-0 pt-0" role="tab"
                                        aria-controls="office-details" id="office-details-trigger" aria-selected="true">
                                        <span class="bs-stepper-circle">2</span>
                                        <span class="bs-stepper-label">Office Details</span>
                                    </button>
                                </div>
                                @if (Auth::user()->can('create-client-party'))
                                    <div class="line"></div>
                                    <div class="step" data-target="#admin-details">
                                        <button type="button" class="step-trigger pb-0 pt-0" role="tab"
                                            aria-controls="admin-details" id="admin-details-trigger" aria-selected="true">
                                            <span class="bs-stepper-circle">3</span>
                                            <span class="bs-stepper-label"> Admin Details</span>
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
                            <form action="/client-party" method="POST" class="form-horizontal"
                                enctype="multipart/form-data" id="clientPartyForm" novalidate>
                                @csrf
                                <input type="hidden" name="OperationType" id="operationType" value="OperationalEntity">
                                <!-- your steps content here -->
                                <div id="basic-details" class="content" role="tabpanel"
                                    aria-labelledby="basic-details-trigger">
                                    <!-- Step validation error message -->
                                    <div id="step-validation-error" class="step-validation-error" style="display: none;">
                                        <i class="fa fa-exclamation-triangle"></i>
                                        <span id="step-error-message">Please complete all required fields before proceeding
                                            to the next step.</span>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12 col-sm-12">
                                            <fieldset class="border pl-2 pr-2 card shadow-sm m-0"
                                                style="border-radius: 10px;">
                                                <legend class="w-auto p-0 m-0">Entity Creation Options</legend>
                                                <div class="row">
                                                    <div class="col col-md-6 col-sm-12 ">
                                                        <div class="form-group row mb-1">
                                                            <label for="partyEntityType"
                                                                class="col-sm-3 col-lg-4 col-md-4 col-form-label col-form-label-sm">Operation
                                                                Type<span class="text-danger">*</span></label>
                                                            <div class="col-sm-9 col-lg-8 col-md-8">
                                                                <select class="form-control form-control-sm"
                                                                    id="partyEntityType" name="partyEntityType" required>
                                                                    <option value="" disabled selected></option>
                                                                    @if (request()->query('type') === 'accounting')
                                                                        <option value="Client"
                                                                            @if (old('partyEntityType') == 'Client') selected @endif>
                                                                            Client</option>
                                                                        <option value="Create only in D365"
                                                                            @if (old('partyEntityType') == 'Create only in D365') selected @endif>
                                                                            Create only in D365</option>
                                                                    @endif
                                                                    <option value="Airline"
                                                                        @if (old('partyEntityType') == 'Airline') selected @endif>
                                                                        Airline</option>
                                                                    <option value="Shipping Line"
                                                                        @if (old('partyEntityType') == 'Shipping Line') selected @endif>
                                                                        Shipping Line</option>
                                                                    <option value="Agent / Forwarder"
                                                                        @if (old('partyEntityType') == 'Agent / Forwarder') selected @endif>
                                                                        Agent / Forwarder</option>
                                                                    <option value="Trucker / Transporter"
                                                                        @if (old('partyEntityType') == 'Trucker / Transporter') selected @endif>
                                                                        Trucker / Transporter</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col col-md-6 col-sm-12 ">
                                                        <div class="form-group row mb-1">
                                                            <label for="legalEntityType"
                                                                class="col-sm-3 col-lg-4 col-md-4 col-form-label col-form-label-sm">Legal
                                                                Type <span class="text-danger">*</span></label>
                                                            <div class="col-sm-9 col-lg-8 col-md-8">
                                                                <select class="form-control form-control-sm"
                                                                    id="legalEntityType" name="legalEntityType" required>
                                                                    <option value="" disabled selected></option>
                                                                    <option value="Debtor & Vendor"
                                                                        @if (old('legalEntityType') == 'Debtor & Vendor') selected @endif>
                                                                        Debtor & Vendor</option>
                                                                    <option value="Debtor"
                                                                        @if (old('legalEntityType') == 'Debtor') selected @endif>
                                                                        Debtor</option>
                                                                    <option value="Vendor"
                                                                        @if (old('legalEntityType') == 'Vendor') selected @endif>
                                                                        Vendor</option>
                                                                    <option value="Only in D365 Debtor"
                                                                        @if (old('legalEntityType') == 'Only in D365 Debtor') selected @endif>
                                                                        Only in D365 Debtor</option>
                                                                    <option value="Only in D365 Vendor"
                                                                        @if (old('legalEntityType') == 'Only in D365 Vendor') selected @endif>
                                                                        Only in D365 Vendor</option>
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
                                                <div class="form-group row mb-1">
                                                    <label for="companyName"
                                                        class="col-sm-3 col-lg-4 col-md-4 col-form-label col-form-label-sm"><i
                                                            class="fa fa-building" aria-hidden="true"></i>Company
                                                        Name<span class="text-danger">*</span></label>
                                                    <div class="col-sm-9 col-lg-8 col-md-8">
                                                        <!-- Input for Create only in D365 -->
                                                        <input type="text" class="form-control form-control-sm"
                                                            value="{{ old('companyName') }}" name="companyName"
                                                            id="companyName" maxlength="50">

                                                        <!-- Company Autocomplete Dropdown (for other entity types) -->
                                                        <div id="companyDropdown" style="display: none;">
                                                            <div class="company-autocomplete-container">
                                                                <input type="text" class="form-control form-control-sm"
                                                                    id="companyAutocomplete" name="companyAutocomplete"
                                                                    placeholder="Type to search companies..."
                                                                    autocomplete="off"
                                                                    value="{{ old('companyAutocomplete') }}">
                                                                <div id="companyAutocompleteList"
                                                                    class="autocomplete-list"></div>
                                                            </div>

                                                            <!-- Hidden inputs to store Id and Code -->
                                                            <input type="hidden" name="companyId" id="companyId"
                                                                value="{{ old('companyId') }}">
                                                            <input type="hidden" name="companyCode" id="companyCode"
                                                                value="{{ old('companyCode') }}">
                                                            <input type="hidden" name="companySelect" id="companySelect"
                                                                value="{{ old('companySelect') }}">
                                                        </div>
                                                    </div>
                                                </div>
                                                {{-- Address Line 1 and 2 --}}

                                                <div class="form-group row mb-1">
                                                    <label for="addressLine1"
                                                        class="col-sm-3 col-lg-4 col-form-label col-form-label-sm">
                                                        <i class="fa fa-map-marker" aria-hidden="true"></i> Address Line 1
                                                        <span class="text-danger">*</span>
                                                    </label>
                                                    <div class="col-sm-9 col-lg-8">
                                                        <input type="text"
                                                            class="form-control form-control-sm basic-detail-field"
                                                            value="{{ old('addressLine1') }}" name="addressLine1"
                                                            id="addressLine1" maxlength="50" required>
                                                    </div>
                                                </div>

                                                <div class="form-group row mb-1">
                                                    <label for="addressLine2"
                                                        class="col-sm-3 col-lg-4 col-form-label col-form-label-sm">
                                                        <i class="fa fa-map-marker" aria-hidden="true"></i> Address Line 2
                                                    </label>
                                                    <div class="col-sm-9 col-lg-8">
                                                        <input type="text"
                                                            class="form-control form-control-sm basic-detail-field"
                                                            value="{{ old('addressLine2') }}" name="addressLine2"
                                                            id="addressLine2" maxlength="50">
                                                    </div>
                                                </div>

                                                {{-- Country Code and Country Name --}}
                                                <div class="form-group row mb-1">
                                                    <label for="country"
                                                        class="col-sm-3 col-md-3 col-lg-4 col-form-label col-form-label-sm"><i
                                                            class="fa fa-globe" aria-hidden="true"></i> Country <span
                                                            class="text-danger">*</span></label>
                                                    <div class="col col-sm-9 col-md-9 col-lg-8">
                                                        <select class="form-control form-control-sm basic-detail-field"
                                                            name="countryID" id="countryID">
                                                            <option value=""></option>
                                                            @forelse ($countries as $country)
                                                                <option value="{{ $country->CountryID }}"
                                                                    id="{{ $country->CountCode }}"
                                                                    @if (old('countryID') == $country->CountryID) selected @endif>
                                                                    {{ $country->CountryName }}
                                                                </option>
                                                            @empty
                                                            @endforelse
                                                        </select>
                                                        <input type="hidden" name="countryName" id="countryName"
                                                            value="{{ old('countryName') }}" class="basic-detail-field">
                                                        <input type="hidden" name="countryCode" id="countryCode"
                                                            value="{{ old('countryCode') }}" class="basic-detail-field">

                                                    </div>
                                                </div>
                                                {{-- Province zipcode --}}
                                                <div class="form-group row">
                                                    <label for="province"
                                                        class="col-sm-3 col-lg-4 col-form-label col-form-label-sm"><i
                                                            class="fa fa-map-pin" aria-hidden="true"></i> Province /
                                                        State</label>
                                                    <div class="col col-sm-5 col-lg-4">
                                                        <input type="text"
                                                            class="form-control form-control-sm basic-detail-field"
                                                            name="provinceName" id="provinceName"
                                                            value="{{ old('provinceName') }}" placeholder="" />
                                                        <input type="hidden" name="provinceID" id="provinceID"
                                                            class="basic-detail-field" />
                                                        <input type="hidden" name="provinceCode" id="provinceCode"
                                                            class="basic-detail-field" />
                                                    </div>
                                                    <div class="col col-sm-4 col-lg-4">
                                                        <input type="text"
                                                            class="form-control form-control-sm number_input basic-detail-field"
                                                            value="{{ old('zipCode') }}" name="zipCode" id="zipCode"
                                                            placeholder="Zip Code">
                                                    </div>
                                                </div>

                                                {{-- City --}}
                                                <div class="form-group row mb-1">
                                                    <label for="cityName"
                                                        class="col-sm-3 col-lg-4 col-form-label col-form-label-sm"><i
                                                            class="fas fa-city"></i> City</label>
                                                    <div class="col-sm-9 col-lg-8">
                                                        <input type="text"
                                                            class="form-control form-control-sm basic-detail-field"
                                                            value="{{ old('cityName') }}" name="cityName"
                                                            id="cityName">
                                                        <input type="hidden" name="cityID" id="cityID"
                                                            value="{{ old('cityID') }}" class="basic-detail-field">
                                                        <input type="hidden" name="cityCode" id="cityCode"
                                                            value="{{ old('cityCode') }}" class="basic-detail-field">
                                                    </div>
                                                </div>
                                            </fieldset>
                                        </div>
                                        <div class="col-md-6 col-sm-12">
                                            <fieldset class="border p-2 card shadow-lg mb-0">
                                                <legend class="w-auto p-0 m-0">Contact Details</legend>
                                                <div class="form-group row mb-2">
                                                    <label for="person"
                                                        class="col col-sm-3 col-md-3 col-lg-4 col-form-label col-form-label-sm">
                                                        <i class="fa fa-user" aria-hidden="true"></i> Person <span
                                                            class="text-danger">*</span>
                                                    </label>
                                                    <div class="col col-sm-4 col-md-4 col-lg-4">
                                                        <input type="text"
                                                            class="form-control form-control-sm basic-detail-field"
                                                            value="{{ old('firstName') }}" name="firstName"
                                                            id="firstName" placeholder="First Name">
                                                    </div>
                                                    <div class="col col-sm-5 col-md-5 col-lg-4">
                                                        <input type="text"
                                                            class="form-control form-control-sm basic-detail-field"
                                                            value="{{ old('lastName') }}" name="lastName" id="lastName"
                                                            placeholder="Last Name">
                                                    </div>
                                                </div>
                                                <div class="form-group row mb-2">
                                                    <label for="department"
                                                        class="col col-sm-3 col-md-3 col-lg-4 col-form-label col-form-label-sm">
                                                        <i class="fa fa-sitemap" aria-hidden="true"></i> Department
                                                    </label>
                                                    <div class="col col-sm-9 col-md-9 col-lg-8">
                                                        <input type="text"
                                                            class="form-control form-control-sm basic-detail-field"
                                                            value="{{ old('department') }}" name="department"
                                                            id="department" placeholder="Department">
                                                    </div>
                                                </div>
                                                <div class="form-group row mb-2">
                                                    <label for="phoneNo"
                                                        class="col col-sm-3 col-md-3 col-lg-4 col-form-label col-form-label-sm">
                                                        <i class="fa fa-phone" aria-hidden="true"></i> Phone No. <span
                                                            class="text-danger">*</span>
                                                    </label>
                                                    <div class="col col-sm-9 col-md-9 col-lg-8">
                                                        <input type="text"
                                                            class="form-control form-control-sm number_input basic-detail-field"
                                                            value="{{ old('phoneNo') }}" name="phoneNo" id="phoneNo"
                                                            placeholder="Phone Number">
                                                    </div>
                                                </div>

                                                <div class="form-group row mb-3">
                                                    <label for="contractEmail"
                                                        class="col col-sm-3 col-md-3 col-lg-4 col-form-label col-form-label-sm">
                                                        <i class="fa fa-envelope" aria-hidden="true"></i> Email
                                                    </label>
                                                    <div class="col col-sm-9 col-md-9 col-lg-8">
                                                        <input type="email" name="contractEmail" id="contractEmail"
                                                            value="{{ old('contractEmail') }}"
                                                            class="form-control form-control-sm basic-detail-field"
                                                            placeholder="Enter Email">
                                                    </div>
                                                </div>

                                            </fieldset>

                                        </div>
                                    </div>

                                    <button class="btn btn-primary m-2 btn-sm float-right" name="next" type="button"
                                        id="nextBtn">Next <i class="fa fa-step-forward"
                                            aria-hidden="true"></i></button>
                                    <button type="button" name="save-step1" id="save-step1"
                                        class="btn btn-success btn-sm float-right mt-2" data-toggle="tooltip"
                                        data-placement="top" title="Save"><i class="fa fa-plus-circle"
                                            aria-hidden="true"></i> Save for Later</button>
                                </div>

                                {{-- office details --}}

                                <div id="office-details" class="content active dstepper-block" role="tabpanel"
                                    aria-labelledby="office-details-trigger">
                                    <!-- Step validation error message -->
                                    <div id="step2-validation-error" class="step-validation-error"
                                        style="display: none;">
                                        <i class="fa fa-exclamation-triangle"></i>
                                        <span id="step2-error-message">Please complete all required fields before
                                            proceeding to the next step.</span>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12 col-lg-12 col-sm-12">
                                            <fieldset class="border p-2 card shadow-sm mb-0 pt-0 pb-0">
                                                <legend class="w-auto p-0 m-0">Office Mapping</legend>
                                                <div class="form-group row m-0">
                                                    <label for="officeCountry"
                                                        class="col-sm-6 col-lg-4 col-md-4 col-form-label col-form-label-sm">Office
                                                        <span class="text-danger">*</span></label>
                                                    <div class="col-sm-6 col-lg-8 col-md-8">
                                                        @if (Auth::user()->can('create-client-for-any-office'))
                                                            <select class="form-control form-control-sm"
                                                                name="officeCountry" id="officeCountry">
                                                                <option value="" disabled selected></option>
                                                                @forelse ($offices as $office)
                                                                    <option code="{{ $office->Code }}"
                                                                        value="{{ $office->Name }}"
                                                                        id="{{ $office->ID }}"
                                                                        @if (old('officeCountry') == $office->Name) selected @endif>
                                                                        {{ $office->Name }}
                                                                    </option>
                                                                @empty
                                                                @endforelse
                                                            </select>
                                                            <input type="hidden" name="officeCountryID"
                                                                id="officeCountryID">
                                                        @else
                                                            <input type="text" name="officeCountryDisp"
                                                                code="{{ Auth::user()->Office->SCMOfficeCode }}"
                                                                id="officeCountryDisp"
                                                                value="{{ Auth::user()->Office->Name ?? '' }}" disabled>
                                                            <input type="hidden" name="officeCountryID"
                                                                id="officeCountryID"
                                                                value="{{ Auth::user()->Office->ID ?? '' }}">
                                                            <input type="hidden" name="officeCode"
                                                                value='{{ Auth::user()->Office->Code }}'>
                                                            <input type="hidden" name="officeCountry" id="officeCountry"
                                                                class="form-control"
                                                                value="{{ Auth::user()->Office->Name ?? '' }}">
                                                            <input type="hidden" name="officeCountryName"
                                                                id="officeCountryName" class="form-control"
                                                                value="{{ Auth::user()->Office->Name ?? '' }}" disabled>
                                                        @endif
                                                    </div>
                                                    {{-- Hidden Other fields ONLY for accounting mode users without admin access --}}
                                                    @if (request()->query('type') === 'accounting' && !Auth::user()->can('create-client-party'))
                                                        <input type="hidden" name="otherInput" id="hiddenOtherInput"
                                                            value="{{ old('otherInput') }}">
                                                        <input type="hidden" name="otherID" id="hiddenOtherID"
                                                            value="{{ old('otherID') }}">
                                                        <input type="hidden" name="otherCode" id="hiddenOtherCode"
                                                            value="{{ old('otherCode') }}">
                                                        <input type="hidden" name="otherCheck" id="hiddenOtherCheck"
                                                            value="1">
                                                    @endif
                                                </div>
                                            </fieldset>
                                            <fieldset class="border p-2 card shadow-lg mb-0 pt-0 pb-0">
                                                <legend>File Upload (PDF Only)
                                                    @if (request()->query('type') === 'accounting')
                                                        <span class="text-danger">*</span>
                                                    @endif
                                                </legend>
                                                <div class="file-upload-container">
                                                    <label for="attachmentFile">Attachment (PDF files only) <i
                                                            class="fa fa-paperclip"></i>
                                                        @if (request()->query('type') === 'accounting')
                                                            <span class="text-danger">Required for accounting mode</span>
                                                        @endif
                                                        <button type="button"
                                                            class="btn btn-sm btn-outline-warning float-right w-20 addAttachment"
                                                            id="addFile">
                                                            <i class="fa fa-plus"></i> File
                                                        </button>
                                                    </label>
                                                    <div class="input-group hdtuto control-group lst increment"
                                                        id="initial-attachment">
                                                        <input id="attachments" type="file" name="attachmentNames[]"
                                                            class="myfrm form-control mr-1 form-control-sm"
                                                            accept="application/pdf,.pdf"
                                                            title="Only PDF files are accepted"
                                                            @if (request()->query('type') === 'accounting') required @endif>
                                                        <input type="text" name="attachmentDescription[]"
                                                            class="myfrm form-control form-control-sm"
                                                            placeholder="Description"
                                                            @if (request()->query('type') === 'accounting') required @endif>
                                                    </div>
                                                    <small class="text-muted">📄 Only PDF files are accepted (Max 10MB)
                                                        @if (request()->query('type') === 'accounting')
                                                            <span class="text-danger font-weight-bold">- File upload is
                                                                mandatory for accounting mode</span>
                                                        @endif
                                                    </small>
                                                    <div id="file-upload-error" class="text-danger mt-2"
                                                        style="display: none;">
                                                        <i class="fa fa-exclamation-triangle"></i> At least one file is
                                                        required for accounting mode.
                                                    </div>
                                                    <div id="attachClone"></div>
                                                </div>
                                                <div class="clone-template d-none">
                                                    <div
                                                        class="hdtuto control-group lst input-group mt-1 cloned-attachment">
                                                        <input type="file" name="attachmentNames[]"
                                                            class="myfrm form-control mr-1 form-control-sm"
                                                            accept="application/pdf,.pdf"
                                                            title="Only PDF files are accepted">
                                                        <input type="text" name="attachmentDescription[]"
                                                            class="myfrm form-control form-control-sm"
                                                            placeholder="Description">
                                                        <div class="input-group-btn">
                                                            <button class="btn btn-danger btn-sm remove-attachment"
                                                                type="button">
                                                                <i class="fa fa-minus"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </fieldset>
                                        </div>
                                        <div class="col-md-12 col-lg-12 col-sm-12">
                                            <fieldset class="border p-2 card shadow-lg mb-0 pt-0 pb-0" id="debtor-vendor">
                                                <legend class="w-auto p-0 m-0">Debtor/Vendor</legend>
                                                <div class="row">
                                                    <div class="col-md-6 col-sm-12 col-lg-6">
                                                        <div class="form-group row mb-1">
                                                            <div class="col-md-12">
                                                                <label for="debtorType"
                                                                    class="col-form-label col-form-label-sm">Debtor
                                                                    Type</label>
                                                                <select class="form-control form-control-sm"
                                                                    name="debtorType" id="debtorType">
                                                                    <option value="" disabled selected></option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="row mb-0 pb-0">
                                                            <div class="form-group col-md-6 mb-0 pb-0">
                                                                <label for="billingCurrency"
                                                                    class="col-form-label col-form-label-sm">Billing
                                                                    Currency <span style="color: red;">*</span></label>
                                                                <input type="hidden" class="form-control form-control-sm"
                                                                    value="{{ old('billingCurrencyCode') }}"
                                                                    name="billingCurrencyCode" id="billingCurrencyCode">
                                                                <select class="form-control form-control-sm"
                                                                    name="billingCurrency" id="billingCurrency" required>
                                                                    <option value="" disabled selected>Select
                                                                        Currency</option>
                                                                    @if (!empty($currencyList) && is_array($currencyList))
                                                                        @foreach ($currencyList as $currency)
                                                                            <option value="{{ $currency->CurrencyID }}"
                                                                                data-code="{{ $currency->CurrencyCode }}"
                                                                                @if (old('billingCurrency') == $currency->CurrencyID) selected @endif>
                                                                                {{ $currency->CurrencyCode }}
                                                                            </option>
                                                                        @endforeach
                                                                    @else
                                                                        <option value="" disabled>No currencies
                                                                            available</option>
                                                                    @endif
                                                                </select>
                                                            </div>
                                                            <div class="form-group col-md-6 mb-0 pb-0">
                                                                <label for="creditDays"
                                                                    class="col-form-label col-form-label-sm">
                                                                    <span style="color: red;">*</span></label>Credit Days
                                                                <select class="form-control form-control-sm"
                                                                    name="creditDays" id="creditDays" required>
                                                                    <option value="" disabled selected>Select Credit
                                                                        Days</option>
                                                                    @if (!empty($payTerms) && is_array($payTerms) && !isset($payTerms['error']))
                                                                        @foreach ($payTerms as $term)
                                                                            <option value="{{ $term['CreditDays'] }}"
                                                                                @if (old('creditDays') == $term['CreditDays']) selected @endif>
                                                                                {{ $term['CreditDays'] }}
                                                                            </option>
                                                                        @endforeach
                                                                    @endif
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6 col-sm-12 col-lg-6 mb-0 pb-0">
                                                        <div class="form-group row mb-1 mb-0 pb-0">
                                                            <div class="col-md-12">
                                                                <label for="vendorType"
                                                                    class="col-form-label col-form-label-sm">Vendor
                                                                    Type</label>
                                                                <select class="form-control form-control-sm"
                                                                    name="vendorType" id="vendorType">
                                                                    <option value="" disabled selected></option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="form-group">
                                                            <div class="row mb-0 pb-0">
                                                                <div class="col-sm-6 col-md-6 col-lg-6 mb-0 pb-0">
                                                                    <label for="maxCreditLimit"
                                                                        class="col-form-label col-form-label-sm">Credit
                                                                        Limit <span style="color: red;">*</span></label>
                                                                    <input type="text"
                                                                        class="form-control form-control-sm number_input"
                                                                        value="{{ old('maxCreditLimit') }}"
                                                                        name="maxCreditLimit" id="maxCreditLimit"
                                                                        placeholder="0.00" required>
                                                                </div>
                                                                <div class="col-sm-6 col-md-6 col-lg-6">
                                                                    <label for="baseCurrency"
                                                                        class="col-form-label col-form-label-sm">Base
                                                                        Currency</label>
                                                                    <input type="text"
                                                                        class="form-control form-control-sm"
                                                                        name="baseCurrency" id="baseCurrency" disabled>
                                                                    <input type="hidden"
                                                                        class="form-control form-control-sm"
                                                                        name="baseCurrencyCode" id="baseCurrencyCode"
                                                                        value="{{ old('baseCurrencyCode') }}">
                                                                    <input type="hidden"
                                                                        class="form-control form-control-sm"
                                                                        name="baseCurrencyName" id="baseCurrencyName"
                                                                        value="{{ old('baseCurrencyName') }}">
                                                                    <input type="hidden"
                                                                        class="form-control form-control-sm"
                                                                        name="baseCurrencyID" id="baseCurrencyID"
                                                                        value="{{ old('baseCurrencyID') }}">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <fieldset>
                                                    <div class="form-group row mb-0 mt-0 p-0">
                                                        <div class="col col-sm-6 col-lg-6 col-md-6 m-0">
                                                            <div class="row">
                                                                <label for="licenceTypeID"
                                                                    class="col-md-3 col-form-label col-form-label-sm pr-0">Licence
                                                                    Type</label>
                                                                <span style="color: red;">*</span>
                                                                <div class="col-md-8">
                                                                    <select class="form-control form-control-sm"
                                                                        name="licenceTypeID" id="licenceTypeID" required>
                                                                        <option value="" disabled selected>Select
                                                                            License
                                                                            Type</option>
                                                                        @forelse ($licenceTypes as $type)
                                                                            <option value="{{ $type->LicenseID }}"
                                                                                data-code="{{ $type->LicenseCode }}"
                                                                                @if (old('licenceTypeID') == $type->LicenseID) selected @endif>
                                                                                {{ $type->LicenseName }}
                                                                            </option>
                                                                        @empty
                                                                            <option value="" disabled>No license
                                                                                types
                                                                                available</option>
                                                                        @endforelse
                                                                    </select>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col col-sm-6 col-lg-6 col-md-6">
                                                            <input type="hidden" class="form-control form-control-sm"
                                                                value="{{ old('licenceTypeCode') }}"
                                                                name="licenceTypeCode" id="licenceTypeCode">
                                                            <input type="text" class="form-control form-control-sm"
                                                                value="{{ old('licenceTypeValue') }}"
                                                                name="licenceTypeValue" id="licenceTypeValue" required>
                                                            <small id="licenseError" class="text-danger"
                                                                style="display: none;"></small>
                                                            <small id="licenseSuccess" class="text-success"
                                                                style="display: none;"></small>
                                                        </div>
                                                    </div>
                                                    <div class="form-group row mb-0 mt-2 p-0">
                                                        <div class="col col-sm-12 col-lg-12 col-md-12">
                                                            <div class="form-check">
                                                                <input type="checkbox" class="form-check-input"
                                                                    id="overseasEntity" name="overseasEntity">
                                                                <label class="form-check-label"
                                                                    for="overseasEntity">Overseas
                                                                    Entity</label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </fieldset>

                                            </fieldset>
                                        </div>
                                    </div>
                                    <button class="btn btn-primary m-2 btn-sm" type="button" name="previous"
                                        onclick="stepper.previous()"><i class="fa fa-step-backward"
                                            aria-hidden="true"></i> Previous</button>
                                    @if (Auth::user()->can('create-client-party'))
                                        <button class="btn btn-primary m-2 btn-sm float-right" type="button"
                                            name="next" id="nextBtn2">Next <i class="fa fa-step-forward"
                                                aria-hidden="true"></i></button>
                                    @else
                                        <button type="submit" name="submitBtn" value="submitBtn"
                                            class="btn btn-outline-success btn-sm float-right mt-2" data-toggle="tooltip"
                                            data-placement="top" title="Will Be Submitted for Creating"><i
                                                class="fa fa-thumbs-up" aria-hidden="true"></i> Submit</button>
                                    @endif
                                    <button type="button" name="save-step2" id="save-step2"
                                        class="btn btn-success btn-sm float-right m-2" data-toggle="tooltip"
                                        data-placement="top" title="Save"><i class="fa fa-plus-circle"
                                            aria-hidden="true"></i> Save for Later</button>
                                </div>

                                {{-- Admin details --}}

                                @if (Auth::user()->can('create-client-party'))
                                    <div id="admin-details" class="content" role="tabpanel"
                                        aria-labelledby="admin-details-trigger">
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
                                                        <tbody>
                                                            <!-- Rows for Client, Shipper, Consignee, and Other -->
                                                            <tr id="client-check">
                                                                <td>Client</td>
                                                                <td><input type="checkbox" name="clientCheck"
                                                                        id="clientCheck"></td>
                                                                <td>
                                                                    <input type="text" name="clientInput"
                                                                        id="clientInput" value="{{ old('clientInput') }}"
                                                                        class="form-control form-control-sm" required>
                                                                    <input type="hidden" name="clientID" id="clientID"
                                                                        value="{{ old('clientID') }}">
                                                                    <input type="hidden" name="clientCode"
                                                                        id="clientCode" value="{{ old('clientCode') }}">
                                                                </td>
                                                                <td id="client_Input"></td>
                                                            </tr>
                                                            <tr id="shipper-check">
                                                                <td>Shipper</td>
                                                                <td><input type="checkbox" name="shipperCheck"
                                                                        id="shipperCheck"></td>
                                                                <td>
                                                                    <input type="text" name="shipperInput"
                                                                        id="shipperInput"
                                                                        class="form-control form-control-sm" required>
                                                                    <input type="hidden" name="shipperID"
                                                                        id="shipperID">
                                                                    <input type="hidden" name="shipperCode"
                                                                        id="shipperCode">
                                                                </td>
                                                                <td id="shipper_Input"></td>
                                                            </tr>
                                                            <tr id="consignee-check">
                                                                <td>Consignee</td>
                                                                <td><input type="checkbox" name="consigneeCheck"
                                                                        id="consigneeCheck"></td>
                                                                <td>
                                                                    <input type="text" name="consigneeInput"
                                                                        id="consigneeInput"
                                                                        class="form-control form-control-sm" required>
                                                                    <input type="hidden" name="consigneeID"
                                                                        id="consigneeID">
                                                                    <input type="hidden" name="consigneeCode"
                                                                        id="consigneeCode">
                                                                </td>
                                                                <td id="consignee_Input"></td>
                                                            </tr>

                                                            <!-- Other row -->
                                                            <tr id="other-check">
                                                                <td>Other</td>
                                                                <td><input type="checkbox" name="otherCheck"
                                                                        id="otherCheck" value="1"></td>
                                                                <td>
                                                                    <input type="text" name="otherInput"
                                                                        id="otherInput"
                                                                        class="form-control form-control-sm"
                                                                        autocomplete="off">
                                                                    <input type="hidden" name="otherID" id="otherID">
                                                                    <input type="hidden" name="otherCode"
                                                                        id="otherCode">
                                                                    <div class="invalid-feedback">Please select or enter a
                                                                        name.</div>
                                                                </td>
                                                                <td id="other_Input"></td>
                                                            </tr>
                                                            <!-- Visible rows for Debtor and Vendor -->
                                                            <tr id="debtor-check">
                                                                <td>Debtor</td>
                                                                <td><input type="checkbox" name="debtorCheck"
                                                                        id="debtorCheck"></td>
                                                                <td>
                                                                    <input type="text" name="debtor" id="debtor"
                                                                        class="form-control form-control-sm" required>
                                                                    <input type="hidden" name="debtorID" id="debtorID">
                                                                    <input type="hidden" name="debtorCode"
                                                                        id="debtorCode">
                                                                </td>
                                                                <td id="debtor_code"></td>
                                                            </tr>
                                                            <tr id="vendor-check">
                                                                <td>Vendor</td>
                                                                <td><input type="checkbox" name="vendorCheck"
                                                                        id="vendorCheck"></td>
                                                                <td>
                                                                    <input type="text" name="vendor" id="vendor"
                                                                        class="form-control form-control-sm" required>
                                                                    <input type="hidden" name="vendorID" id="vendorID">
                                                                    <input type="hidden" name="vendorCode"
                                                                        id="vendorCode">
                                                                </td>
                                                                <td id="vendor_code"></td>
                                                            </tr>

                                                        </tbody>
                                                    </table>
                                                </fieldset>

                                            </div>
                                        </div>
                                        <button class="btn btn-primary mt-2" type="button" name="previous"
                                            onclick="stepper.previous()"><i class="fa fa-step-backward"
                                                aria-hidden="true"></i> Previous</button>
                                        @if (Auth::user()->can('create-client-party'))
                                            <button type="submit" name="createBtn" value="true"
                                                class="btn btn-outline-success float-right m-2 btn-sm"
                                                data-toggle="tooltip" data-placement="top"
                                                title="Save and Create On SCM"><i class="fa fa-cogs"
                                                    aria-hidden="true"></i> Create</button>
                                        @endif
                                        <button type="submit" name="submitBtn" id="submitBtn"
                                            class="btn btn-success float-right mt-2 btn-sm" data-toggle="tooltip"
                                            data-placement="top" title="Save"><i class="fa fa-plus-circle"
                                                aria-hidden="true"></i> Save For Later</button>
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

{{-- CSS --}}
@section('css')
    {{-- Select2 CSS --}}
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    {{-- Stepper CSS --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bs-stepper@1.7.0/dist/css/bs-stepper.min.css">
    <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">

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
            color: #E91809;
            font-weight: bold;
        }

        label {
            font-weight: bold;
        }

        /* Company Autocomplete Styles */
        .company-autocomplete-container {
            position: relative;
            z-index: 1050;
        }

        .autocomplete-list {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border: 1px solid #ced4da;
            border-top: none;
            max-height: 250px;
            overflow-y: auto;
            z-index: 1051;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.15);
            border-radius: 0 0 4px 4px;
            display: none;
        }

        .autocomplete-list.show {
            display: block;
        }

        .autocomplete-item {
            padding: 10px 12px;
            cursor: pointer;
            border-bottom: 1px solid #f1f1f1;
            font-size: 14px;
            transition: background-color 0.2s ease;
            line-height: 1.4;
        }

        .autocomplete-item:hover,
        .autocomplete-item.highlighted {
            background-color: #e9ecef;
        }

        .autocomplete-item:last-child {
            border-bottom: none;
        }

        .autocomplete-no-results {
            padding: 12px;
            color: #6c757d;
            font-style: italic;
            font-size: 14px;
            text-align: center;
        }

        .company-name {
            font-weight: 500;
            color: #495057;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .company-code {
            color: #6c757d;
            font-size: 12px;
            margin-top: 2px;
            font-family: monospace;
        }

        /* Ensure autocomplete container has proper positioning */
        .form-group {
            position: relative;
        }

        .form-group .company-autocomplete-container {
            z-index: 1050;
        }

        /* Focus styles for autocomplete input */
        .company-autocomplete-container .form-control:focus {
            border-color: #80bdff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }

        /* Prevent autocomplete from being clipped */
        .company-autocomplete-container .autocomplete-list {
            min-width: 100%;
            white-space: nowrap;
        }

        /* File upload styles */
        .file-upload-container {
            position: relative;
        }

        .file-upload-hint {
            font-size: 12px;
            color: #6c757d;
            margin-top: 4px;
            font-style: italic;
        }

        input[type="file"] {
            border: 1px solid #ced4da;
            border-radius: 4px;
            padding: 4px 8px;
        }

        input[type="file"]:focus {
            border-color: #80bdff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }

        .file-upload-error {
            color: #dc3545;
            font-size: 12px;
            margin-top: 4px;
        }

        /* Required file upload styling for accounting mode */
        .file-upload-required {
            border-left: 4px solid #dc3545;
            padding-left: 8px;
        }

        .file-upload-required legend {
            color: #dc3545;
        }

        #file-upload-error {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            border-radius: 4px;
            padding: 8px 12px;
            margin-top: 8px;
        }

        /* Success state for file uploads */
        .file-upload-success {
            color: #28a745;
            font-size: 12px;
            margin-top: 4px;
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            border-radius: 4px;
            padding: 4px 8px;
        }

        /* PDF file icon indicator */
        input[type="file"][accept*="pdf"]::file-selector-button {
            background-color: #dc3545;
            color: white;
            border: none;
            padding: 4px 8px;
            border-radius: 3px;
            margin-right: 8px;
            font-size: 12px;
        }

        input[type="file"][accept*="pdf"]::file-selector-button::before {
            content: "📄 ";
        }

        /* Accounting mode specific styling */
        .accounting-mode .file-upload-container {
            border: 2px dashed #dc3545;
            border-radius: 8px;
            padding: 12px;
            background-color: #fff5f5;
        }

        .accounting-mode .file-upload-container.has-files {
            border-color: #28a745;
            background-color: #f8fff9;
        }

        /* Shake animation for validation errors */
        .shake-animation {
            animation: shake 0.5s ease-in-out;
        }

        @keyframes shake {

            0%,
            100% {
                transform: translateX(0);
            }

            10%,
            30%,
            50%,
            70%,
            90% {
                transform: translateX(-5px);
            }

            20%,
            40%,
            60%,
            80% {
                transform: translateX(5px);
            }
        }

        /* Enhanced error styling for missing files */
        .file-upload-container.validation-error {
            border-color: #dc3545 !important;
            background-color: #f8d7da !important;
            box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
        }

        /* Next button disabled state */
        .btn-primary:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        /* Step validation indicator */
        .step-validation-error {
            border: 2px solid #dc3545;
            border-radius: 8px;
            padding: 8px;
            margin: 8px 0;
            background-color: #f8d7da;
        }

        .step-validation-error .fa-exclamation-triangle {
            color: #dc3545;
            margin-right: 8px;
        }

        /* Style for readonly country field in accounting mode */
        .readonly-country {
            background-color: #e9ecef !important;
            opacity: 0.7;
            cursor: not-allowed;
        }

        /* Style for readonly select elements */
        .readonly-select {
            background-color: #e9ecef !important;
            opacity: 0.7;
            cursor: not-allowed;
            pointer-events: none;
        }

        .readonly-select:focus {
            background-color: #e9ecef !important;
            border-color: #ced4da;
            box-shadow: none;
        }

        /* Loading indicator for autocomplete */
        .autocomplete-loading {
            padding: 12px;
            color: #6c757d;
            font-style: italic;
            text-align: center;
            font-size: 14px;
        }

        .autocomplete-loading::after {
            content: "";
            display: inline-block;
            width: 12px;
            height: 12px;
            margin-left: 8px;
            border: 2px solid #6c757d;
            border-radius: 50%;
            border-top-color: transparent;
            animation: spin 1s ease-in-out infinite;
        }

        .autocomplete-item strong {
            background-color: #fff3cd;
            padding: 1px 3px;
            border-radius: 2px;
        }

        .autocomplete-loading,
        .autocomplete-no-results {
            padding: 10px 14px;
            color: #6c757d;
            font-style: italic;
        }

        .autocomplete-item.highlighted {
            background-color: #e9ecef !important;
        }

        .company-autocomplete-container input.is-invalid {
            border-color: #dc3545 !important;
        }

        .company-autocomplete-container input.is-valid {
            border-color: #28a745 !important;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
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
            background: #191857;
            -webkit-animation: preloader-fx 1.6s linear infinite;
            animation: preloader-fx 1.6s linear infinite;
        }

        .preloader-icon span:last-child {
            animation-delay: -0.8s;
            -webkit-animation-delay: -0.8s;
        }

        .address-row .col-md-6 {
            padding-right: 5px;
            padding-left: 5px;
        }

        .address-row textarea.auto-resize {
            width: 100%;
            resize: vertical;
            min-height: 30px;
            max-height: 100px;
            height: 30px;
            line-height: 1.2;
            padding: 5px;
            overflow-y: hidden;
        }

        .address-row label {
            display: block;
            margin-bottom: 5px;
        }

        @media (max-width: 767px) {
            .address-row .col-md-6 {
                padding-right: 15px;
                padding-left: 15px;
            }

            .address-row .col-md-6:first-child {
                margin-bottom: 10px;
            }

            .autocomplete-list {
                max-height: 150px;
            }

            .autocomplete-item {
                padding: 10px;
            }
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

        .hdtuto.control-group.lst.input-group {
            display: flex;
            align-items: center;
            margin-bottom: 5px;
        }

        .hdtuto.control-group.lst.input-group input[type="file"],
        .hdtuto.control-group.lst.input-group input[type="text"] {
            flex: 1;
            margin-right: 5px;
        }

        .hdtuto.control-group.lst.input-group .input-group-btn {
            flex: 0 0 auto;
        }

        .clone-template {
            display: none !important;
        }

        .is-invalid {
            border-color: #dc3545;
        }

        .invalid-feedback {
            display: none;
            color: #dc3545;
            font-size: 0.875rem;
        }

        .is-invalid~.invalid-feedback {
            display: block;
        }

        /* New style for disabled fields */
        .basic-detail-field[readonly],
        .basic-detail-field[disabled] {
            background-color: #e9ecef;
            opacity: 0.7;
            cursor: not-allowed;
            pointer-events: none;
        }

        .hidden-row {
            display: none;
        }

        #companyDropdown {
            display: block;
        }

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

        /* License validation styles */
        #licenceTypeValue.is-invalid {
            border-color: #dc3545;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='none' stroke='%23dc3545' viewBox='0 0 12 12'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath stroke-linejoin='round' d='M5.8 3.6h.4L6 6.5z'/%3e%3ccircle cx='6' cy='8.2' r='.6' fill='%23dc3545' stroke='none'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right calc(0.375em + 0.1875rem) center;
            background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
        }

        #licenceTypeValue.is-valid {
            border-color: #28a745;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' width='8' height='8' viewBox='0 0 8 8'%3e%3cpath fill='%2328a745' d='M2.3 6.73L.6 4.53c-.4-1.04.46-1.4 1.1-.8l1.1 1.4 3.4-3.8c.6-.63 1.6-.27 1.2.7l-4 4.6c-.43.5-.8.4-1.1.1z'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right calc(0.375em + 0.1875rem) center;
            background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
        }

        .license-checking {
            display: inline-block;
            color: #17a2b8;
            font-size: 0.875rem;
            animation: pulse 1.5s ease-in-out infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.5;
            }
        }

        #licenseError {
            color: #dc3545;
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }

        #licenseSuccess {
            color: #28a745;
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }
    </style>
@stop

{{-- JS --}}
@section('js')
    <script src="//code.jquery.com/jquery-1.12.4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bs-stepper@1.7.0/dist/js/bs-stepper.min.js"></script>
    <script src="{{ asset('js/client-party/party-entity.js') }}"></script>
    <script src="{{ asset('js/client-party/validation.js') }}"></script>
    <script src="{{ asset('js/client-party/sub-form.js') }}"></script>
    <script src="{{ asset('js/client-party/jquery-number/jquery.number.min.js') }}"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>

    <script>
        /* Ajax setup CSRF */
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });


        $(document).ready(function() {
            // Cache DOM elements
            const partyEntityType = $('#partyEntityType');
            const legalEntityType = $('#legalEntityType');
            const companyNameInput = $('#companyName');
            const companyDropdown = $('#companyDropdown');
            const companyAutocomplete = $('#companyAutocomplete');
            const companyAutocompleteList = $('#companyAutocompleteList');
            const companyIdInput = $('#companyId');
            const companyCodeInput = $('#companyCode');
            const companySelectHidden = $('#companySelect');
            const countrySelect = $('#countryID');
            const debtorType = $('#debtorType');
            const vendorType = $('#vendorType');
            const creditDays = $('#creditDays');
            const maxCreditLimit = $('#maxCreditLimit');
            const billingCurrency = $('#billingCurrency');
            const requestType = new URLSearchParams(window.location.search).get('type');
            const overseasEntityCheckbox = $('#overseasEntity');
            const licenceTypeValue = $('#licenceTypeValue');
            const licenceTypeID = $('#licenceTypeID');
            const licenceTypeCode = $('#licenceTypeCode');
            const clientIdInput = $('#clientID');
            const clientCodeInput = $('#clientCode');

            // Company autocomplete variables
            // Company autocomplete variables
            let companiesData = [];
            let selectedIndex = -1;
            let searchTimeout = null;

            const DEBOUNCE_DELAY = 350; // ms — feels responsive but avoids typing storm
            const MAX_RESULTS = 40; // sweet spot between speed and usability

            // File upload variables
            let attachmentCount = 1;
            const maxAttachments = 3;

            // Function to check if office is Kenya-based
            function isKenyaOffice(officeText) {
                if (!officeText) return false;
                const trimmedOffice = officeText.trim().toLowerCase();

                // Check for all Kenya office variations
                return trimmedOffice === 'fit-kenya' ||
                    trimmedOffice === 'fit - kenya' ||
                    trimmedOffice === 'fit-kenya (fit express)' ||
                    trimmedOffice === 'fit - kenya (fit express)' ||
                    trimmedOffice === 'fit-kenya(fit express)' ||
                    trimmedOffice === 'fit - kenya(fit express)' ||
                    (trimmedOffice.includes('kenya') && trimmedOffice.includes('fit'));
            }

            // Function to auto-select license type based on office
            function autoSelectLicenseType(officeText) {
                const licenceTypeSelect = $('#licenceTypeID');

                // Only proceed if the license type select exists
                if (!licenceTypeSelect.length) {
                    console.warn('License type select not found');
                    return;
                }

                const isKenya = isKenyaOffice(officeText);

                console.log('Auto-selecting license for office:', {
                    officeText: officeText,
                    isKenya: isKenya
                });

                let targetOption;

                if (isKenya) {
                    // For Kenya: Find "PERSONAL IDENTIFICATION NUMBER"
                    targetOption = licenceTypeSelect.find('option').filter(function() {
                        const optionText = $(this).text().trim().toLowerCase();
                        return optionText.includes('personal identification number') ||
                            optionText.includes('personal id');
                    });
                    console.log('Looking for PIN, found:', targetOption.length, targetOption.text());
                } else {
                    // For non-Kenya: Find "TAXPAYER IDENTIFICATION NUMBER"
                    targetOption = licenceTypeSelect.find('option').filter(function() {
                        const optionText = $(this).text().trim().toLowerCase();
                        return optionText.includes('taxpayer identification number') ||
                            optionText.includes('taxpayer id');
                    });
                    console.log('Looking for TIN, found:', targetOption.length, targetOption.text());
                }

                if (targetOption.length > 0) {
                    // Select the appropriate option
                    licenceTypeSelect.val(targetOption.val());

                    // Make it non-editable (disabled)
                    licenceTypeSelect.prop('disabled', true);

                    // Add visual indicator that it's auto-selected
                    licenceTypeSelect.addClass('bg-light');

                    // Manually trigger the change event to update license code
                    licenceTypeSelect.trigger('change');

                    // Update the license code
                    setTimeout(function() {
                        updateLicenseCode();
                    }, 50);

                    console.log('✓ Auto-selected License Type:', {
                        office: officeText,
                        isKenya: isKenya,
                        selectedOption: targetOption.text().trim(),
                        selectedValue: targetOption.val(),
                        licenseType: isKenya ? 'PERSONAL IDENTIFICATION NUMBER' :
                            'TAXPAYER IDENTIFICATION NUMBER'
                    });
                } else {
                    console.warn(
                        `${isKenya ? 'PERSONAL' : 'TAXPAYER'} IDENTIFICATION NUMBER option not found in license types`
                    );
                }
            }
            // Function to reset license type selection
            // Function to reset license type selection
            function resetLicenseType() {
                const licenceTypeSelect = $('#licenceTypeID');

                if (!licenceTypeSelect.length) return;

                // Re-enable the select
                licenceTypeSelect.prop('disabled', false);

                // Remove visual indicator
                licenceTypeSelect.removeClass('bg-light');

                // Reset to default option
                licenceTypeSelect.val('');

                // Clear license code and value
                if ($('#licenceTypeCode').length) {
                    $('#licenceTypeCode').val('');
                }
                if ($('#licenceTypeValue').length) {
                    $('#licenceTypeValue').val('');
                }

                console.log('License type reset - field enabled');
            }

            // ================================
            // EXISTING: Your validation checks
            // ================================
            if (!overseasEntityCheckbox.length || !licenceTypeValue.length || !licenceTypeID.length) {
                console.error('Critical DOM elements missing:', {
                    overseasEntity: !!overseasEntityCheckbox.length,
                    licenceTypeValue: !!licenceTypeValue.length,
                    licenceTypeID: !!licenceTypeID.length
                });
            }
            if (!clientIdInput.length || !clientCodeInput.length) {
                console.warn('ClientID or ClientCode inputs missing:', {
                    clientIdInput: !!clientIdInput.length,
                    clientCodeInput: !!clientCodeInput.length
                });
            }

            // if (!overseasEntityCheckbox.length || !licenceTypeValue.length || !licenceTypeID.length) {
            //     console.error('Critical DOM elements missing:', {
            //         overseasEntity: !!overseasEntityCheckbox.length,
            //         licenceTypeValue: !!licenceTypeValue.length,
            //         licenceTypeID: !!licenceTypeID.length
            //     });
            // }
            // if (!clientIdInput.length || !clientCodeInput.length) {
            //     console.warn('ClientID or ClientCode inputs missing:', {
            //         clientIdInput: !!clientIdInput.length,
            //         clientCodeInput: !!clientCodeInput.length
            //     });
            // }

            // ================================
            // OTHER FIELDS INITIALIZATION FOR ACCOUNTING MODE
            // ================================

            function initializeOtherFieldsFromClient() {
                // ONLY for accounting mode
                if (requestType === 'accounting') {
                    const companyName = companyNameInput.val() || companyAutocomplete.val();
                    const companyId = companyIdInput.val();
                    const companyCode = companyCodeInput.val();

                    if (companyName && companyId && companyCode) {
                        const hasAdminAccess = {{ Auth::user()->can('create-client-party') ? 'true' : 'false' }};

                        if (hasAdminAccess) {
                            // Use visible admin fields (Step 3)
                            $('#otherInput').val(companyName);
                            $('#otherID').val(companyId);
                            $('#otherCode').val(companyCode);
                            $('#other_Input').text(companyCode);
                            $('#otherCheck').prop('checked', true);
                        } else {
                            // Use hidden fields in Step 2 for accounting mode only
                            $('#hiddenOtherInput').val(companyName);
                            $('#hiddenOtherID').val(companyId);
                            $('#hiddenOtherCode').val(companyCode);
                            $('#hiddenOtherCheck').val('1');
                        }

                        console.log('Initialized Other fields (accounting mode):', {
                            otherInput: companyName,
                            otherID: companyId,
                            otherCode: companyCode,
                            hasAdminAccess: hasAdminAccess,
                            mode: 'accounting'
                        });
                    }
                }
                // Do nothing for operational mode
            }

            function initializeOtherFieldsFromManualInput() {
                // ONLY for accounting mode with "Create only in D365"
                if (requestType === 'accounting') {
                    const companyName = companyNameInput.val().trim();

                    if (companyName) {
                        const hasAdminAccess = {{ Auth::user()->can('create-client-party') ? 'true' : 'false' }};

                        if (hasAdminAccess) {
                            // Use visible admin fields
                            $('#otherInput').val(companyName);
                            if (!$('#otherID').val()) {
                                // $('#otherID').val(generateUUID()); // Use proper UUID
                                $('#otherID').val();
                            }
                            if (!$('#otherCode').val()) {
                                // $('#otherCode').val(generateTempCode()); // Code can remain string
                                $('#otherCode').val();
                            }
                            $('#other_Input').text($('#otherCode').val());
                            $('#otherCheck').prop('checked', true);
                        } else {
                            // Use hidden fields for accounting mode only
                            $('#hiddenOtherInput').val(companyName);
                            // $('#hiddenOtherID').val(generateUUID()); // Use proper UUID
                            // $('#hiddenOtherCode').val(generateTempCode()); // Code can remain string
                            $('#hiddenOtherID').val(); // Use proper UUID
                            $('#hiddenOtherCode').val();
                            $('#hiddenOtherCheck').val('1');
                        }

                        console.log('Initialized Other fields from manual input (accounting mode):', {
                            otherInput: companyName,
                            hasAdminAccess: hasAdminAccess,
                            mode: 'accounting - Create only in D365'
                        });
                    }
                }
            }

            // Enhanced function to ensure Other fields are always populated for accounting mode
            function ensureOtherFieldsForAccountingMode() {
                // ONLY run this for accounting mode
                if (requestType === 'accounting') {
                    const hasAdminAccess = {{ Auth::user()->can('create-client-party') ? 'true' : 'false' }};

                    // Check appropriate fields based on user access
                    const otherInput = hasAdminAccess ? $('#otherInput').val() : $('#hiddenOtherInput').val();
                    const companyName = companyNameInput.val() || companyAutocomplete.val();
                    const companyId = companyIdInput.val();
                    const companyCode = companyCodeInput.val();

                    if (!otherInput && companyName) {
                        // Force initialization of Other fields
                        if (companyId && companyCode) {
                            initializeOtherFieldsFromClient();
                        } else {
                            initializeOtherFieldsFromManualInput();
                        }
                    }

                    // Ensure Other checkbox/hidden field is set
                    if (hasAdminAccess) {
                        $('#otherCheck').prop('checked', true);
                    } else {
                        $('#hiddenOtherCheck').val('1');
                    }

                    console.log('ensureOtherFieldsForAccountingMode (accounting only):', {
                        hasAdminAccess: hasAdminAccess,
                        otherInput: hasAdminAccess ? $('#otherInput').val() : $('#hiddenOtherInput').val(),
                        otherChecked: hasAdminAccess ? $('#otherCheck').is(':checked') : $(
                            '#hiddenOtherCheck').val(),
                        companyName: companyName,
                        mode: 'accounting'
                    });
                }
                // Do nothing for operational mode
            }

            // Helper functions for generating temporary IDs and codes
            function generateTempId() {
                // Generate a proper GUID/UUID format
                return generateUUID();
            }

            function generateTempCode() {
                return 'TEMP-' + Math.random().toString(36).substr(2, 6).toUpperCase();
            }

            function generateUUID() {
                return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
                    var r = Math.random() * 16 | 0,
                        v = c == 'x' ? r : (r & 0x3 | 0x8);
                    return v.toString(16);
                });
            }

            // ================================
            // FILE UPLOAD VALIDATION FUNCTIONS
            // ================================

            function validateFileUpload() {
                if (requestType === 'accounting') {
                    const fileInputs = $('input[name="attachmentNames[]"]');
                    const descInputs = $('input[name="attachmentDescription[]"]');
                    let hasValidFile = false;
                    let hasValidDescription = false;
                    const errors = [];

                    fileInputs.each(function(index) {
                        const fileInput = this;
                        const descInput = descInputs.eq(index);

                        if (fileInput.files && fileInput.files.length > 0) {
                            const file = fileInput.files[0];

                            // Validate file type
                            const allowedTypes = ['application/pdf'];
                            const allowedExtensions = /(\.pdf)$/i;

                            if (!allowedTypes.includes(file.type) && !allowedExtensions.exec(file.name)) {
                                errors.push(`Invalid file type: ${file.name}. Only PDF files are allowed.`);
                                $(fileInput).addClass('is-invalid');
                                return;
                            }

                            // Validate file size (10MB)
                            const maxSize = 10 * 1024 * 1024;
                            if (file.size > maxSize) {
                                errors.push(`File too large: ${file.name}. Maximum size is 10MB.`);
                                $(fileInput).addClass('is-invalid');
                                return;
                            }

                            // Check if description is provided
                            const description = descInput.val().trim();
                            if (!description) {
                                errors.push(`Description required for: ${file.name}`);
                                descInput.addClass('is-invalid');
                                return;
                            }

                            // File is valid
                            $(fileInput).removeClass('is-invalid');
                            descInput.removeClass('is-invalid');
                            hasValidFile = true;
                            hasValidDescription = true;
                        }
                    });

                    const errorElement = $('#file-upload-error');
                    const container = $('.file-upload-container');

                    if (errors.length > 0) {
                        errorElement.html('<i class="fa fa-exclamation-triangle"></i> ' + errors.join('<br>'));
                        errorElement.show();
                        container.addClass('validation-error');
                        return false;
                    }

                    if (!hasValidFile) {
                        errorElement.html(
                            '<i class="fa fa-exclamation-triangle"></i> At least one PDF file is required for accounting mode.'
                        );
                        errorElement.show();
                        container.addClass('validation-error');
                        return false;
                    }

                    // All validations passed
                    errorElement.hide();
                    container.removeClass('validation-error');
                    return true;
                }

                return true; // Always valid for operational mode
            }

            function validateGuidFields() {
                const guidFields = [
                    'companyId', 'clientID', 'otherID', 'hiddenOtherID',
                    'countryID', 'officeCountryID', 'billingCurrency'
                ];

                guidFields.forEach(fieldId => {
                    const field = $('#' + fieldId);
                    if (field.length && field.val() && field.val().trim() !== '') {
                        const value = field.val().trim();

                        // Check if it's a valid GUID format
                        const guidRegex =
                            /^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i;

                        if (!guidRegex.test(value)) {
                            console.warn(`Invalid GUID format for ${fieldId}:`, value);

                            // If it's a temp ID that's not a proper GUID, replace it
                            if (value.startsWith('temp-')) {
                                field.val(generateUUID());
                                console.log(`Replaced invalid temp ID with proper GUID for ${fieldId}`);
                            }
                        }
                    }
                });
            }

            function showFileError(message) {
                const errorElement = $('#file-upload-error');
                errorElement.html('<i class="fa fa-exclamation-triangle"></i> ' + message);
                errorElement.show();
                $('.file-upload-container').addClass('validation-error');

                // Auto-hide after 5 seconds
                setTimeout(function() {
                    errorElement.fadeOut();
                    $('.file-upload-container').removeClass('validation-error');
                }, 5000);
            }

            function clearStepValidationError() {
                const currentStep = $('.content.active, .content.dstepper-block').attr('id');
                if (currentStep === 'office-details') {
                    $('#step2-validation-error').fadeOut();
                }
            }

            // Real-time file validation
            $(document).on('change', 'input[name="attachmentNames[]"]', function() {
                const fileInput = this;
                const file = fileInput.files[0];

                if (file) {
                    // Reset validation state
                    $(fileInput).removeClass('is-invalid');

                    // Validate file type
                    const allowedTypes = ['application/pdf'];
                    const allowedExtensions = /(\.pdf)$/i;

                    if (!allowedTypes.includes(file.type) && !allowedExtensions.exec(file.name)) {
                        $(fileInput).addClass('is-invalid').val('');
                        showFileError(`Invalid file type: ${file.name}. Only PDF files are allowed.`);
                        return false;
                    }

                    // Validate file size (10MB)
                    const maxSize = 10 * 1024 * 1024;
                    if (file.size > maxSize) {
                        $(fileInput).addClass('is-invalid').val('');
                        showFileError(`File too large: ${file.name}. Maximum size is 10MB.`);
                        return false;
                    }

                    // Show file size info
                    const fileSizeMB = (file.size / (1024 * 1024)).toFixed(2);
                    console.log(`File uploaded: ${file.name} (${fileSizeMB} MB)`);

                    // Update container state for accounting mode
                    if (requestType === 'accounting') {
                        setTimeout(function() {
                            if (validateFileUpload()) {
                                $('.file-upload-container').addClass('has-files');
                                clearStepValidationError();
                            }
                        }, 100);
                    }
                }
            });

            // Real-time description validation
            $(document).on('input', 'input[name="attachmentDescription[]"]', function() {
                $(this).removeClass('is-invalid');

                if (requestType === 'accounting') {
                    setTimeout(function() {
                        if (validateFileUpload()) {
                            clearStepValidationError();
                        }
                    }, 100);
                }
            });

            // ================================
            // ATTACHMENT MANAGEMENT
            // ================================

            // Add attachment button
            $(".addAttachment").click(function(e) {
                e.preventDefault();
                e.stopPropagation();

                if (attachmentCount >= maxAttachments) {
                    alert('Maximum of 3 file attachments allowed.');
                    return;
                }

                // Validate current files before adding new ones (accounting mode only)
                if (requestType === 'accounting' && attachmentCount > 1) {
                    const currentFiles = $('input[name="attachmentNames[]"]');
                    const currentDescs = $('input[name="attachmentDescription[]"]');
                    let isCurrentValid = true;

                    currentFiles.each(function(index) {
                        if (this.files && this.files.length > 0) {
                            const desc = currentDescs.eq(index).val().trim();
                            if (!desc) {
                                currentDescs.eq(index).addClass('is-invalid');
                                isCurrentValid = false;
                            }
                        }
                    });

                    if (!isCurrentValid) {
                        alert('Please provide descriptions for all uploaded files before adding more.');
                        return false;
                    }
                }

                // Clone the template
                const templateHtml = $(".clone-template").html();
                $("#attachClone").append(templateHtml);
                attachmentCount++;

                console.log('Added attachment field. Total:', attachmentCount);
            });

            // Remove attachment
            $("body").on("click", ".remove-attachment", function() {
                $(this).closest(".cloned-attachment").remove();
                attachmentCount--;

                console.log('Removed attachment field. Total:', attachmentCount);

                // Re-validate file uploads after removal for accounting mode
                if (requestType === 'accounting') {
                    setTimeout(function() {
                        if (!validateFileUpload()) {
                            const currentStep = $('.content.active, .content.dstepper-block').attr(
                                'id');
                            if (currentStep === 'office-details') {
                                $('#step2-error-message').text(
                                    'File upload is required for accounting mode. Please upload a PDF file with description.'
                                );
                                $('#step2-validation-error').show();
                            }
                        }
                    }, 100);
                }
            });

            // ================================
            // LICENSE CODE MANAGEMENT (FIXED)
            // ================================

            // FIXED: Function to manually update license code with better error handling
            function updateLicenseCode() {
                const licenceTypeSelect = $('#licenceTypeID');
                const selectedOption = licenceTypeSelect.find('option:selected');
                const code = selectedOption.attr('data-code') || '';

                console.log('Manual license code update:', {
                    selectExists: licenceTypeSelect.length > 0,
                    selectedValue: licenceTypeSelect.val(),
                    selectedOption: selectedOption.length > 0,
                    dataCodeAttribute: selectedOption.attr('data-code'),
                    code: code,
                    hiddenInputExists: $('#licenceTypeCode').length > 0,
                    currentHiddenValue: $('#licenceTypeCode').val()
                });

                $('#licenceTypeCode').val(code);

                // Trigger change event to notify other handlers
                $('#licenceTypeCode').trigger('change');

                return code;
            }


            // FIXED: Delegated event handler for license type change (more robust)
            $(document).off('change', '#licenceTypeID').on('change', '#licenceTypeID', function() {
                const selectedOption = $(this).find('option:selected');
                const code = selectedOption.attr('data-code') || '';

                console.log('licenceTypeID change (delegated):', {
                    selectedValue: $(this).val(),
                    selectedText: selectedOption.text(),
                    code: code,
                    isOverseas: overseasEntityCheckbox.is(':checked'),
                    requestType: requestType,
                    timestamp: new Date().toISOString()
                });

                // Set the code immediately
                $('#licenceTypeCode').val(code);

                // Add a small delay to ensure DOM is fully updated, then update again
                setTimeout(function() {
                    updateLicenseCode();
                }, 10);
            });

            // ================================
            // NEW: Office selection change handler - Add this
            // ================================
            $(document).off('change', '#officeCountry').on('change', '#officeCountry', function() {
                const selectedOption = $(this).find('option:selected');
                const officeText = selectedOption.text().trim();
                const officeValue = $(this).val();

                console.log('Office changed:', {
                    value: officeValue,
                    text: officeText,
                    isKenya: isKenyaOffice(officeText),
                    requestType: requestType
                });

                if (officeValue && officeText) {
                    // Auto-select license type for all offices
                    autoSelectLicenseType(officeText);
                } else {
                    // Reset if no office selected
                    resetLicenseType();
                }
            });

            // ================================
            // COMPANY AUTOCOMPLETE FUNCTIONS
            // ================================

            function toggleAutocompleteDropdown(show = true) {
                if (show && companyAutocomplete.is(':visible') && companiesData.length > 0) {
                    companyAutocompleteList.addClass('show');
                } else {
                    companyAutocompleteList.removeClass('show').empty();
                    selectedIndex = -1;
                }
            }

            function renderAutocompleteItems(companies) {
                companyAutocompleteList.empty();

                if (!companies || companies.length === 0) {
                    companyAutocompleteList.html(
                        '<div class="autocomplete-no-results">No matching companies found</div>'
                    ).addClass('show');
                    return;
                }

                const searchTerm = companyAutocomplete.val().trim().toLowerCase();
                const terms = searchTerm ? searchTerm.split(/\s+/).filter(t => t) : [];

                companies.forEach((company, index) => {
                    let nameHtml = escapeHtml(company.name);

                    // Highlight matches (optional but very nice UX)
                    if (terms.length > 0) {
                        terms.forEach(term => {
                            if (!term) return;
                            const regex = new RegExp(`(${escapeRegExp(term)})`, 'gi');
                            nameHtml = nameHtml.replace(regex, '<strong>$1</strong>');
                        });
                    }

                    const item = $(`
            <div class="autocomplete-item" data-index="${index}">
                <div class="company-name">${nameHtml}</div>
                <div class="company-code">Code: ${escapeHtml(company.code || '—')}</div>
            </div>
        `);

                    item.data('company', company);
                    companyAutocompleteList.append(item);
                });

                companyAutocompleteList.addClass('show');
            }

            // Helper for safe regex
            function escapeRegExp(string) {
                return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
            }

            function escapeHtml(text) {
                const div = document.createElement('div');
                div.textContent = text;
                return div.innerHTML;
            }

            function filterCompanies(searchTerm) {
                if (!searchTerm.trim()) {
                    return companiesData.slice(0, MAX_RESULTS);
                }

                const terms = searchTerm.toLowerCase().split(/\s+/).filter(t => t.length > 0);

                return companiesData.filter(company => {
                    const nameLower = company.name.toLowerCase();
                    const codeLower = (company.code || '').toLowerCase();

                    // Every search word must appear in name OR code
                    return terms.every(term =>
                        nameLower.includes(term) || codeLower.includes(term)
                    );
                });
            }

            function selectCompany(company) {
                if (!company) return;

                companyAutocomplete.val(company.name);
                companyNameInput.val(company.name);
                companyIdInput.val(company.id);
                companyCodeInput.val(company.code);
                companySelectHidden.val(company.id);

                toggleAutocompleteDropdown(false);

                // Trigger the existing company selection logic
                if (partyEntityType.val()) {
                    fetchCompanyDetails(partyEntityType.val(), company.id);
                }

                // Sync client fields
                syncClientFields();

                // AUTO-INITIALIZE Other fields for accounting mode
                setTimeout(function() {
                    if (requestType === 'accounting') {
                        initializeOtherFieldsFromClient();
                    }
                }, 100);

                console.log('Company selected:', {
                    name: company.name,
                    id: company.id,
                    code: company.code,
                    otherFieldsInitialized: requestType === 'accounting'
                });
            }

            function highlightItem(index) {
                companyAutocompleteList.find('.autocomplete-item').removeClass('highlighted');
                if (index >= 0 && index < companyAutocompleteList.find('.autocomplete-item').length) {
                    companyAutocompleteList.find('.autocomplete-item').eq(index).addClass('highlighted');
                    selectedIndex = index;
                }
            }

            function loadCompaniesForAutocomplete(entityType) {
                console.log('Loading companies for autocomplete, entityType:', entityType);

                // Show loading state
                if (companyAutocomplete.is(':visible')) {
                    companyAutocompleteList.html('<div class="autocomplete-loading">Loading companies...</div>')
                        .addClass('show');
                }

                $.ajax({
                    url: '{{ route('companies') }}',
                    method: 'GET',
                    data: {
                        entityType: entityType
                    },
                    success: function(data) {
                        console.log('Companies loaded:', data.length || 0);

                        // Hide loading state
                        companyAutocompleteList.removeClass('show').empty();

                        if (data.error) {
                            companiesData = [];
                            companyAutocomplete.attr('placeholder', 'Error loading companies...');
                            if (companyAutocomplete.is(':focus')) {
                                companyAutocompleteList.html(
                                        `<div class="autocomplete-no-results">${data.error}</div>`)
                                    .addClass('show');
                            }
                        } else {
                            companiesData = data || [];
                            companyAutocomplete.attr('placeholder',
                                `Type to search ${companiesData.length} companies...`);

                            // If input is focused and has value, show filtered results
                            if (companyAutocomplete.is(':focus')) {
                                const searchTerm = companyAutocomplete.val().trim();
                                if (searchTerm) {
                                    const filteredCompanies = filterCompanies(searchTerm);
                                    renderAutocompleteItems(filteredCompanies);
                                }
                            }
                        }
                    },
                    error: function(xhr) {
                        console.error('Error loading companies:', xhr.responseJSON?.error || xhr
                            .statusText);
                        companiesData = [];
                        companyAutocompleteList.removeClass('show').empty();
                        companyAutocomplete.attr('placeholder', 'Error loading companies...');

                        if (companyAutocomplete.is(':focus')) {
                            companyAutocompleteList.html(
                                '<div class="autocomplete-no-results">Error loading companies. Please try again.</div>'
                            ).addClass('show');
                        }

                        alert('Failed to load company list. Please try again.');
                    }
                });
            }

            companyAutocomplete.on('input', function() {
                clearTimeout(searchTimeout);

                const term = $(this).val().trim();

                // Clear everything when input is emptied
                if (!term) {
                    clearCompanySelection();
                    toggleAutocompleteDropdown(false);
                    companyAutocomplete.removeClass('is-invalid is-valid');
                    return;
                }

                // Show mini loading state
                companyAutocompleteList
                    .html('<div class="autocomplete-loading">Searching...</div>')
                    .addClass('show');

                searchTimeout = setTimeout(() => {
                    const filtered = filterCompanies(term);
                    renderAutocompleteItems(filtered.slice(0, MAX_RESULTS));
                }, DEBOUNCE_DELAY);
            });


            function clearCompanySelection() {
                companyNameInput.val('');
                companyAutocomplete.val('');
                companyIdInput.val('');
                companyCodeInput.val('');
                companySelectHidden.val('');

                // Critical for accounting mode — reset dependent fields
                if (requestType === 'accounting') {
                    $('#otherInput, #hiddenOtherInput').val('');
                    $('#otherID, #hiddenOtherID').val('');
                    $('#otherCode, #hiddenOtherCode').val('');
                    $('#other_Input').text('');
                    $('#otherCheck').prop('checked', false);
                    $('#hiddenOtherCheck').val('0');
                }

                toggleAutocompleteDropdown(false);
                selectedIndex = -1;
                companyAutocomplete.removeClass('is-valid').removeClass('is-invalid');
            }








            companyAutocomplete.on('focus', function() {
                const searchTerm = $(this).val().trim();
                if (companiesData.length > 0) {
                    const filteredCompanies = filterCompanies(searchTerm);
                    renderAutocompleteItems(filteredCompanies);
                } else if (searchTerm) {
                    // Show loading if we have a search term but no data yet
                    companyAutocompleteList.html(
                        '<div class="autocomplete-loading">Loading companies...</div>').addClass('show');
                }
            });

            companyAutocomplete.on('blur', function(e) {
                setTimeout(() => {
                    // Only hide if user is not hovering the list or focused back
                    if (!companyAutocompleteList.is(':hover') &&
                        !companyAutocomplete.is(':focus')) {

                        toggleAutocompleteDropdown(false);

                        // Visual feedback: red if typed something but didn't select
                        if (companyAutocomplete.val().trim() && !companyIdInput.val()) {
                            companyAutocomplete.addClass('is-invalid');
                        } else if (companyIdInput.val()) {
                            companyAutocomplete.addClass('is-valid');
                        }
                    }
                }, 220); // slightly longer → more forgiving
            });

            // Keyboard navigation for autocomplete
            companyAutocomplete.on('keydown', function(e) {
                const items = companyAutocompleteList.find('.autocomplete-item');

                if (!companyAutocompleteList.hasClass('show') || items.length === 0) {
                    return;
                }

                switch (e.key) {
                    case 'ArrowDown':
                        e.preventDefault();
                        selectedIndex = Math.min(selectedIndex + 1, items.length - 1);
                        highlightItem(selectedIndex);
                        break;

                    case 'ArrowUp':
                        e.preventDefault();
                        selectedIndex = Math.max(selectedIndex - 1, 0);
                        highlightItem(selectedIndex);
                        break;

                    case 'Enter':
                        e.preventDefault();
                        if (selectedIndex >= 0) {
                            const selectedItem = items.eq(selectedIndex);
                            const company = selectedItem.data('company');
                            if (company) {
                                selectCompany(company);
                            }
                        }
                        break;

                    case 'Escape':
                        e.preventDefault();
                        toggleAutocompleteDropdown(false);
                        $(this).blur();
                        break;
                }
            });

            // Click handler for autocomplete items
            companyAutocompleteList.on('click', '.autocomplete-item', function(e) {
                e.preventDefault();
                e.stopPropagation();
                const company = $(this).data('company');
                if (company) {
                    selectCompany(company);
                }
            });

            // Prevent autocomplete list from losing focus
            companyAutocompleteList.on('mousedown', function(e) {
                e.preventDefault();
            });

            // Click outside to close autocomplete
            $(document).on('click', function(e) {
                if (!$(e.target).closest('.company-autocomplete-container').length) {
                    toggleAutocompleteDropdown(false);
                }
            });

            // ================================
            // FORM FIELD MANAGEMENT
            // ================================

            function syncClientFields() {
                if (companyIdInput.val() && companyCodeInput.val()) {
                    clientIdInput.val(companyIdInput.val());
                    clientCodeInput.val(companyCodeInput.val());
                    console.log('syncClientFields: Synced client fields', {
                        clientID: clientIdInput.val(),
                        clientCode: clientCodeInput.val(),
                        companyId: companyIdInput.val(),
                        companyCode: companyCodeInput.val(),
                        timestamp: new Date().toISOString()
                    });
                } else {
                    console.warn('syncClientFields: companyId or companyCode empty, not clearing client fields', {
                        companyId: companyIdInput.val(),
                        companyCode: companyCodeInput.val(),
                        clientID: clientIdInput.val(),
                        clientCode: clientCodeInput.val(),
                        timestamp: new Date().toISOString()
                    });
                }
            }

            function toggleCompanyInput() {
                let selectedType = partyEntityType.val() || '';
                const typeMapping = {
                    'Agent/Forwarder': 'Forwarder',
                    'ShippingLine': 'Shipping Line',
                    'Trucker': 'Trucker / Transporter',
                    'Airline': 'Airline',
                    'Client': 'Client',
                    'CreateonlyinD365': 'Create only in D365'
                };
                const normalizedType = selectedType.replace(/\s*\/\s*/g, '/').replace(/\s+/g, '');
                const apiType = typeMapping[normalizedType] || selectedType;

                companyNameInput.hide();
                companyDropdown.hide();
                legalEntityType.find('option').prop('disabled', false).show();
                clearBasicDetails();
                toggleFieldsReadonly(false);

                const dropdownTypes = ['Client', 'Agent/Forwarder', 'Airline', 'ShippingLine',
                    'Trucker/Transporter'
                ];

                if (requestType === 'accounting') {
                    if (selectedType === 'Create only in D365') {
                        companyNameInput.show();
                        companyDropdown.css('display', 'none');
                        legalEntityType.find('option').each(function() {
                            const value = $(this).val();
                            if (value && value !== 'Only in D365 Debtor' && value !==
                                'Only in D365 Vendor') {
                                $(this).prop('disabled', true).hide();
                            }
                        });
                    } else if (dropdownTypes.includes(normalizedType)) {
                        companyDropdown.show();
                        companyNameInput.hide();
                        toggleFieldsReadonly(true);

                        // Load companies for autocomplete
                        loadCompaniesForAutocomplete(apiType);

                        legalEntityType.find('option').each(function() {
                            const value = $(this).val();
                            if (value === 'Only in D365 Debtor' || value === 'Only in D365 Vendor') {
                                $(this).prop('disabled', true).hide();
                            }
                        });
                    }
                } else {
                    companyNameInput.show();
                    companyDropdown.css('display', 'none');
                }

                legalEntityType.val('');
                console.log('toggleCompanyInput:', {
                    requestType,
                    selectedType,
                    normalizedType,
                    apiType,
                    dropdownTypesIncludes: dropdownTypes.includes(normalizedType),
                    companyNameVisible: companyNameInput.is(':visible'),
                    companyDropdownVisible: companyDropdown.is(':visible')
                });
                syncClientFields();
            }

            function clearBasicDetails() {
                $('.basic-detail-field').not('#countryCode, #countryName, #countryID').val('');
                companyNameInput.val(''); // Clear the original companyName field
                companyAutocomplete.val(''); // Clear the autocomplete field
                companyIdInput.val('');
                companyCodeInput.val('');
                companySelectHidden.val('');
                clientIdInput.val('');
                clientCodeInput.val('');
                toggleAutocompleteDropdown(false);

                // Clear Other fields when clearing basic details
                if (requestType === 'accounting') {
                    $('#otherInput').val('');
                    $('#otherID').val('');
                    $('#otherCode').val('');
                    $('#other_Input').text('');
                }
            }

            function toggleFieldsReadonly(disable) {
                $('.basic-detail-field').not('#countryCode, #countryName, #countryID').each(function() {
                    $(this).prop('readonly', disable);
                });
            }

            // function fetchCompanyDetails(entityType, companyId) {
            //     console.log('fetchCompanyDetails: entityType=', entityType, 'companyId=', companyId);
            //     $.ajax({
            //         url: '{{ route('company.details') }}',
            //         method: 'GET',
            //         data: {
            //             entityType: entityType,
            //             companyId: companyId
            //         },
            //         success: function(data) {
            //             console.log('fetchCompanyDetails: Response=', data);
            //             if (!data.countryCode || !data.countryID) {
            //                 console.warn('fetchCompanyDetails: Missing countryCode or countryID', {
            //                     countryCode: data.countryCode,
            //                     countryID: data.countryID
            //                 });
            //                 alert(
            //                     'Warning: No country code for this company. Please select a country manually.'
            //                 );
            //             }
            //             $('#addressLine1').val(data.addressLine1 || '');
            //             $('#addressLine2').val(data.addressLine2 || '');
            //             $('#cityName').val(data.cityName || '');
            //             $('#zipCode').val(data.zipCode || '');
            //             $('#provinceName').val(data.provinceName || '');
            //             $('#provinceID').val(data.provinceID || '');
            //             $('#provinceCode').val(data.provinceCode || '');
            //             $('#cityID').val(data.cityID || '');
            //             $('#cityCode').val(data.cityCode || '');
            //             $('#countryID').val(data.countryID || '').trigger('change');
            //             $('#countryCode').val(data.countryCode || '');
            //             $('#countryName').val(data.countryName || '');
            //             $('#firstName').val(data.firstName || '-');
            //             $('#lastName').val(data.lastName || '-');
            //             $('#department').val(data.department || '');
            //             $('#phoneNo').val(data.phoneNo || '0');
            //             $('#contractEmail').val(data.contractEmail || '');
            //             companyIdInput.val(data.companyId || '');
            //             companyCodeInput.val(data.companyCode || '');
            //             if (data.companyId && data.companyCode) {
            //                 clientIdInput.val(data.companyId);
            //                 clientCodeInput.val(data.companyCode);
            //             }
            //             syncClientFields();

            //             // NEW: Initialize Other fields with client data for accounting mode
            //             setTimeout(function() {
            //                 initializeOtherFieldsFromClient();
            //             }, 100);
            //         },
            //         error: function(xhr) {
            //             console.error('Error fetching company details:', xhr.responseJSON?.error || xhr
            //                 .statusText);
            //             clearBasicDetails();
            //             toggleFieldsReadonly(true);
            //             alert('Failed to retrieve company details.');
            //         }
            //     });
            // }
            function fetchCompanyDetails(entityType, companyId) {
                console.log('fetchCompanyDetails: entityType=', entityType, 'companyId=', companyId);
                $.ajax({
                    url: '{{ route('company.details') }}',
                    method: 'GET',
                    data: {
                        entityType: entityType,
                        companyId: companyId
                    },
                    success: function(data) {
                        console.log('fetchCompanyDetails: Response=', data);
                        if (!data.countryCode || !data.countryID) {
                            console.warn('fetchCompanyDetails: Missing countryCode or countryID', {
                                countryCode: data.countryCode,
                                countryID: data.countryID
                            });
                            alert(
                                'Warning: No country code for this company. Please select a country manually.'
                            );
                        }

                        $('#addressLine1').val(data.addressLine1 || '');
                        $('#addressLine2').val(data.addressLine2 || '');
                        $('#cityName').val(data.cityName || '');
                        $('#zipCode').val(data.zipCode || '');
                        $('#provinceName').val(data.provinceName || '');
                        $('#provinceID').val(data.provinceID || '');
                        $('#provinceCode').val(data.provinceCode || '');
                        $('#cityID').val(data.cityID || '');
                        $('#cityCode').val(data.cityCode || '');
                        $('#countryID').val(data.countryID || '').trigger('change');
                        $('#countryCode').val(data.countryCode || '');
                        $('#countryName').val(data.countryName || '');
                        $('#firstName').val(data.firstName || '-');
                        $('#lastName').val(data.lastName || '-');
                        $('#department').val(data.department || '');
                        $('#phoneNo').val(data.phoneNo || '0');
                        $('#contractEmail').val(data.contractEmail || '');
                        companyIdInput.val(data.companyId || '');
                        companyCodeInput.val(data.companyCode || '');

                        if (data.companyId && data.companyCode) {
                            clientIdInput.val(data.companyId);
                            clientCodeInput.val(data.companyCode);
                        }

                        syncClientFields();

                        // Set otherCode immediately here — don't rely solely on initializeOtherFieldsFromClient
                        // This fixes the case where companyId/companyCode are set but the timeout fires too late
                        if (data.companyId && data.companyCode) {
                            const hasAdminAccess =
                                {{ Auth::user()->can('create-client-party') ? 'true' : 'false' }};
                            const partyName = data.companyName || companyAutocomplete.val() ||
                                companyNameInput.val();

                            if (hasAdminAccess) {
                                $('#otherInput').val(partyName);
                                $('#otherID').val(data.companyId);
                                $('#otherCode').val(data.companyCode);
                                $('#other_Input').text(data.companyCode);
                                $('#otherCheck').prop('checked', true);
                            } else {
                                $('#hiddenOtherInput').val(partyName);
                                $('#hiddenOtherID').val(data.companyId);
                                $('#hiddenOtherCode').val(data.companyCode);
                                $('#hiddenOtherCheck').val('1');
                            }

                            console.log('fetchCompanyDetails: otherCode set directly', {
                                entityType: entityType,
                                otherCode: data.companyCode,
                                otherID: data.companyId,
                                otherName: partyName,
                                hasAdminAccess: hasAdminAccess
                            });
                        }

                        // Also run initializeOtherFieldsFromClient as a backup
                        setTimeout(function() {
                            initializeOtherFieldsFromClient();
                        }, 100);
                    },
                    error: function(xhr) {
                        console.error('Error fetching company details:', xhr.responseJSON?.error || xhr
                            .statusText);
                        clearBasicDetails();
                        toggleFieldsReadonly(true);
                        alert('Failed to retrieve company details.');
                    }
                });
            }

            function toggleCountryReadonly() {
                const partyEntityTypeValue = partyEntityType.val();

                if (requestType === 'accounting' && partyEntityTypeValue !== 'Create only in D365') {
                    // Use readonly styling but keep field enabled for submission
                    countrySelect
                        .addClass('readonly-country')
                        .css('pointer-events', 'none') // Prevent user interaction
                        .prop('readonly', true)
                        .prop('disabled', false); // Keep enabled so value submits

                    console.log('Country field set to readonly for accounting mode (except Create only in D365)');
                } else {
                    countrySelect
                        .removeClass('readonly-country')
                        .css('pointer-events', 'auto')
                        .prop('readonly', false)
                        .prop('disabled', false);

                    console.log('Country field enabled - operational mode or Create only in D365');
                }
            }

            function handleDebtorTypeChange() {
                const debtorTypeValue = debtorType.val();
                console.log('debtorType changed:', {
                    debtorType: debtorTypeValue
                });
                if (debtorTypeValue === 'CASHCUSTOM') {
                    creditDays.val('0').prop('readonly', true).prop('disabled', true);
                    maxCreditLimit.val('0').prop('readonly', true).prop('disabled', true);
                    console.log('Cash Custom selected: Set creditDays=0, maxCreditLimit=0, fields readonly');
                } else {
                    creditDays.prop('readonly', false).prop('disabled', false);
                    maxCreditLimit.prop('readonly', false).prop('disabled', false);
                    console.log('Non-Cash Custom selected: creditDays and maxCreditLimit editable');
                }
            }

            // ================================
            // FIXED OVERSEAS ENTITY LICENSE HANDLING
            // ================================

            function toggleLicenseInput() {
                const isOverseas = overseasEntityCheckbox.is(':checked');
                console.log('toggleLicenseInput:', {
                    isOverseas,
                    licenceTypeValueRequired: $('#licenceTypeValue').attr('required'),
                    licenceTypeIDRequired: $('#licenceTypeID').attr('required'),
                    requestType: requestType,
                    timestamp: new Date().toISOString()
                });

                if (isOverseas || requestType === 'operational') {
                    licenceTypeValue
                        .prop('readonly', true) // Use readonly instead of disabled
                        .prop('disabled', false) // Ensure it's not disabled so it submits
                        .val('NIL')
                        .removeClass('is-invalid')
                        .removeAttr('required')
                        .addClass('overseas-readonly'); // Add visual class for styling

                    licenceTypeID
                        .prop('disabled', false) // Keep enabled to preserve value
                        .addClass('readonly-select') // Add readonly styling
                        .attr('readonly', true) // Add readonly attribute for styling
                        .removeClass('is-invalid')
                        .removeAttr('required');

                    // FIXED: Use CSS pointer-events instead of preventing JavaScript events
                    // This allows change events to still fire for our handlers
                    licenceTypeID.css('pointer-events', 'none');

                    // Remove any previous event handlers that might conflict
                    licenceTypeID.off('click.overseas change.overseas');

                    $('#licenseError').hide();
                    $('#licenseSuccess').hide();
                    $('#submitBtn').prop('disabled', false);
                } else {
                    licenceTypeValue
                        .prop('readonly', false)
                        .prop('disabled', false)
                        .val('')
                        .removeClass('overseas-readonly');

                    licenceTypeID
                        .prop('disabled', false)
                        .removeClass('readonly-select')
                        .removeAttr('readonly');

                    // Re-enable pointer events
                    licenceTypeID.css('pointer-events', 'auto');

                    // Remove event handlers that prevent interaction
                    licenceTypeID.off('click.overseas change.overseas');
                    $('#licenseSuccess').hide();

                    // Set required attributes for accounting mode
                    if (requestType === 'accounting') {
                        licenceTypeValue.attr('required', 'required');
                        licenceTypeID.attr('required', 'required');
                    }
                }

                // FIXED: Trigger license code update after toggle
                setTimeout(function() {
                    updateLicenseCode();
                }, 50);
            }
            // ============================================
            // LICENSE DUPLICATE CHECK FUNCTION
            // ============================================
            let licenseCheckTimeout;
            let lastCheckedLicense = '';

            async function checkLicenseDuplicate() {
                const licenceTypeValue = $('#licenceTypeValue').val()?.trim();
                const licenceTypeID = $('#licenceTypeID').val();
                const officeCode = $('#officeCountry').find('option:selected').attr('code') ||
                    $('input[name="officeCode"]').val();
                const legalEntityType = $('#legalEntityType').val();

                // Clear previous states
                $('#licenseError').hide().text('');
                $('#licenseSuccess').hide().text('');
                $('#licenceTypeValue').removeClass('is-invalid is-valid');

                // Don't check if overseas entity or operational mode
                const isOverseas = $('#overseasEntity').is(':checked');
                const requestType = new URLSearchParams(window.location.search).get('type');

                if (isOverseas || requestType === 'operational' || licenceTypeValue === 'NIL') {
                    console.log('License check skipped: Overseas entity or operational mode');
                    return true; // Return true since no validation needed
                }

                // Validate required fields
                if (!licenceTypeValue || !licenceTypeID || !officeCode) {
                    console.log('License check skipped: Missing required fields', {
                        licenceTypeValue,
                        licenceTypeID,
                        officeCode
                    });
                    return true; // Return true if fields not ready yet
                }

                // Don't check if same value as last time (unless it was invalid)
                if (licenceTypeValue === lastCheckedLicense && $('#licenceTypeValue').hasClass('is-valid')) {
                    console.log('License check skipped: Already validated');
                    return true;
                }

                // Determine party type based on legal entity type
                let partyType = 'Debtor'; // Default
                if (legalEntityType) {
                    if (legalEntityType.includes('Vendor') && !legalEntityType.includes('Debtor')) {
                        partyType = 'Vendor';
                    } else if (legalEntityType.includes('Debtor')) {
                        partyType = 'Debtor';
                    }
                }

                console.log('🔍 Checking license for duplicate:', {
                    licenceTypeValue,
                    licenceTypeID,
                    officeCode,
                    partyType,
                    legalEntityType
                });

                // Show loading indicator
                $('#licenceTypeValue').after(
                    '<span class="license-checking ml-2"><i class="fa fa-spinner fa-spin"></i> Checking...</span>'
                );

                try {
                    const response = await $.ajax({
                        url: '{{ route('client-party.check-license') }}',
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            licenseNo: licenceTypeValue,
                            partyType: partyType,
                            officeCode: officeCode,
                            _token: '{{ csrf_token() }}'
                        }
                    });

                    console.log('✅ License check response:', response);

                    // Remove loading indicator
                    $('.license-checking').remove();

                    // 🔥 FIXED: Better response handling with explicit boolean check
                    const isDuplicate = response.isDuplicate === true ||
                        response.isDuplicate === 1 ||
                        response.isDuplicate === "1" ||
                        response.status === 'Duplicate' ||
                        response.status === 'Error';

                    console.log('🔍 isDuplicate evaluation:', {
                        raw: response.isDuplicate,
                        type: typeof response.isDuplicate,
                        status: response.status,
                        evaluated: isDuplicate
                    });

                    if (isDuplicate) {
                        // ❌ DUPLICATE FOUND
                        console.log('❌ Duplicate license detected');

                        $('#licenseError')
                            .html(
                                `<i class="fa fa-exclamation-triangle"></i> ${response.remark || 'This license number already exists!'}`
                            )
                            .show();

                        $('#licenceTypeValue')
                            .addClass('is-invalid')
                            .removeClass('is-valid');

                        // Disable submit buttons
                        $('button[type="submit"], button[name="createBtn"], button[name="submitBtn"]')
                            .prop('disabled', true)
                            .addClass('disabled')
                            .attr('title', 'Cannot submit: Duplicate license number');

                        lastCheckedLicense = licenceTypeValue;
                        return false;

                    } else {
                        // ✅ NO DUPLICATE - LICENSE IS AVAILABLE
                        console.log('✅ License is available');

                        $('#licenseSuccess')
                            .html(
                                `<i class="fa fa-check-circle"></i> ${response.remark || 'License number is available'}`
                            )
                            .show();

                        $('#licenceTypeValue')
                            .addClass('is-valid')
                            .removeClass('is-invalid');

                        // Enable submit buttons
                        $('button[type="submit"], button[name="createBtn"], button[name="submitBtn"]')
                            .prop('disabled', false)
                            .removeClass('disabled')
                            .removeAttr('title');

                        lastCheckedLicense = licenceTypeValue;
                        return true;
                    }

                } catch (xhr) {
                    console.error('❌ License check AJAX error:', xhr);
                    $('.license-checking').remove();

                    // Show error message
                    $('#licenseError')
                        .html(
                            `<i class="fa fa-exclamation-circle"></i> Error checking license: ${xhr.responseJSON?.error || xhr.statusText || 'Unknown error'}`
                        )
                        .show();

                    $('#licenceTypeValue')
                        .addClass('is-invalid')
                        .removeClass('is-valid');

                    lastCheckedLicense = licenceTypeValue;
                    return false;
                }
            }

            // ============================================
            // LICENSE DUPLICATE CHECK EVENT HANDLERS
            // ============================================
            $(document).ready(function() {
                // ... your existing code ...

                // Real-time license check on input (with debounce)
                $('#licenceTypeValue').on('input', function() {
                    clearTimeout(licenseCheckTimeout);

                    const value = $(this).val()?.trim();

                    // Clear previous validation states
                    $('#licenseError').hide();
                    $('#licenseSuccess').hide();
                    $(this).removeClass('is-invalid is-valid');

                    // Only check if not overseas and not NIL
                    const isOverseas = $('#overseasEntity').is(':checked');
                    const requestType = new URLSearchParams(window.location.search).get('type');

                    if (!isOverseas && requestType !== 'operational' && value && value !== 'NIL') {
                        // Debounce: wait 1 second after user stops typing
                        licenseCheckTimeout = setTimeout(() => {
                            checkLicenseDuplicate();
                        }, 1000);
                    }
                });

                // Check on blur (when user leaves the field)
                $('#licenceTypeValue').on('blur', function() {
                    const value = $(this).val()?.trim();
                    const isOverseas = $('#overseasEntity').is(':checked');
                    const requestType = new URLSearchParams(window.location.search).get('type');

                    if (!isOverseas && requestType !== 'operational' && value && value !== 'NIL') {
                        checkLicenseDuplicate();
                    }
                });

                // Re-check when legal entity type changes (party type might change)
                $('#legalEntityType').on('change', function() {
                    const licenceTypeValue = $('#licenceTypeValue').val()?.trim();
                    const isOverseas = $('#overseasEntity').is(':checked');

                    if (!isOverseas && licenceTypeValue && licenceTypeValue !== 'NIL') {
                        // Reset last checked to force re-check with new party type
                        lastCheckedLicense = '';
                        setTimeout(() => {
                            checkLicenseDuplicate();
                        }, 500);
                    }
                });

                // Re-check when office changes
                $('#officeCountry').on('change', function() {
                    const licenceTypeValue = $('#licenceTypeValue').val()?.trim();
                    const isOverseas = $('#overseasEntity').is(':checked');

                    if (!isOverseas && licenceTypeValue && licenceTypeValue !== 'NIL') {
                        // Reset last checked to force re-check with new office
                        lastCheckedLicense = '';
                        setTimeout(() => {
                            checkLicenseDuplicate();
                        }, 500);
                    }
                });

                // Clear license check when overseas entity is checked
                $('#overseasEntity').on('change', function() {
                    if ($(this).is(':checked')) {
                        $('#licenseError').hide();
                        $('#licenseSuccess').hide();
                        $('#licenceTypeValue').removeClass('is-invalid is-valid');
                        $('.license-checking').remove();

                        // Re-enable submit buttons
                        $('button[type="submit"], button[name="createBtn"], button[name="submitBtn"]')
                            .prop('disabled', false)
                            .removeClass('disabled')
                            .removeAttr('title');
                    }
                });
            });

            function toggleAccountingFields() {
                if (requestType === 'operational') {
                    legalEntityType
                        .prop('disabled', true)
                        .removeAttr('required')
                        .removeClass('is-invalid')
                        .val('');
                    billingCurrency
                        .prop('disabled', true)
                        .removeAttr('required')
                        .removeClass('is-invalid')
                        .val('');
                    creditDays
                        .prop('disabled', true)
                        .removeAttr('required')
                        .removeClass('is-invalid')
                        .val('');
                    maxCreditLimit
                        .prop('disabled', true)
                        .removeAttr('required')
                        .removeClass('is-invalid')
                        .val('');
                    licenceTypeValue
                        .prop('disabled', false) // Keep enabled for submission
                        .prop('readonly', true)
                        .removeAttr('required')
                        .removeClass('is-invalid')
                        .val('NIL')
                        .addClass('overseas-readonly');
                    licenceTypeID
                        .prop('disabled', false)
                        .addClass('readonly-select')
                        .attr('readonly', true)
                        .removeAttr('required')
                        .removeClass('is-invalid')
                        .css('pointer-events', 'none');
                    console.log('toggleAccountingFields: Disabled accounting fields for operational mode');
                } else if (requestType === 'accounting') {
                    legalEntityType
                        .prop('disabled', false)
                        .attr('required', 'required');
                    billingCurrency
                        .prop('disabled', false)
                        .attr('required', 'required');
                    creditDays
                        .prop('disabled', false)
                        .attr('required', 'required');
                    maxCreditLimit
                        .prop('disabled', false)
                        .attr('required', 'required');
                    console.log('toggleAccountingFields: Enabled accounting fields for accounting mode');
                    toggleLicenseInput();
                }
            }

            // ================================
            // VALIDATION FUNCTIONS
            // ================================

            function validateCompanySelection() {
                if (requestType === 'accounting' && companyAutocomplete.is(':visible')) {
                    if (companyAutocomplete.val() && !companyIdInput.val()) {
                        companyAutocomplete.addClass('is-invalid');
                        alert('Please select a company from the dropdown list.');
                        return false;
                    }
                    companyAutocomplete.removeClass('is-invalid');
                }
                return true;
            }

            function validateForm() {
                const form = document.getElementById('clientPartyForm');

                // Check HTML5 form validity
                if (!form.checkValidity()) {
                    console.warn('Form validation failed - HTML5 validation');
                    return false;
                }

                // Check company selection for accounting mode
                if (!validateCompanySelection()) {
                    console.warn('Form validation failed - Company selection');
                    return false;
                }

                // Check file upload for accounting mode
                if (!validateFileUpload()) {
                    console.warn('Form validation failed - File upload requirement');
                    return false;
                }

                // Update license code before validation
                updateLicenseCode();

                console.log('All form validations passed');
                return true;
            }

            // ================================
            // OTHER INPUT MANAGEMENT
            // ================================

            function toggleOtherInput() {
                const isOtherChecked = $('#otherCheck').is(':checked');
                const $otherInput = $('#otherInput');
                const $otherID = $('#otherID');
                const $otherCode = $('#otherCode');
                const $otherInputDisplay = $('#other_Input');

                if (isOtherChecked) {
                    $otherInput.attr('required', 'required').val('').off('input').prop('readonly', false);
                    $otherInputDisplay.text('');

                    // For accounting mode, initialize with client data if available
                    if (requestType === 'accounting') {
                        setTimeout(function() {
                            initializeOtherFieldsFromClient();
                        }, 100);
                    }
                } else {
                    $otherInput.attr('required', 'required').val('').prop('readonly', false);
                    let debounceTimeout;
                    $otherInput.on('input', function() {
                        clearTimeout(debounceTimeout);
                        debounceTimeout = setTimeout(() => {
                            const query = $(this).val().trim().toLowerCase();
                            let entityType = $('#partyEntityType').val() || '';
                            const typeMapping = {
                                'Agent / Forwarder': 'Forwarder',
                                'ShippingLine': 'Shipping Line',
                                'Trucker': 'Trucker / Transporter',
                                'Airline': 'Airline',
                                'Client': 'Client',
                                'CreateonlyinD365': 'Create only in D365'
                            };
                            const normalizedType = entityType.replace(/\s*\/\s*/g, '/').replace(
                                /\s+/g, '');
                            entityType = typeMapping[normalizedType] || entityType;
                            const $autocompleteList = $('<div class="autocomplete-list"></div>');
                            $otherInput.parent().find('.autocomplete-list').remove();
                            $otherInput.parent().append($autocompleteList);

                            if (!entityType) {
                                $autocompleteList.html('<div>Please select a party type</div>');
                                return;
                            }

                            console.log('otherInput AJAX:', {
                                entityType
                            });
                            $.ajax({
                                url: '{{ route('companies') }}',
                                type: 'GET',
                                data: {
                                    entityType: entityType
                                },
                                success: function(matches) {
                                    console.log('otherInput response:', matches);
                                    $autocompleteList.empty();
                                    if (matches.error) {
                                        $autocompleteList.html(
                                            `<div>${matches.error}</div>`);
                                        return;
                                    }
                                    if (matches.length === 0) {
                                        $autocompleteList.html(
                                            '<div>No entities found</div>');
                                        return;
                                    }
                                    // Filter client-side
                                    const filteredMatches = query ?
                                        matches.filter(entity => entity.name
                                            .toLowerCase().startsWith(query)) :
                                        matches;
                                    if (filteredMatches.length === 0) {
                                        $autocompleteList.html(
                                            '<div>No entities found</div>');
                                        return;
                                    }
                                    filteredMatches.forEach(entity => {
                                        const $div = $(
                                                '<div class="autocomplete-item"></div>'
                                            )
                                            .text(
                                                `${entity.name} (${entity.code})`
                                            )
                                            .data('entity', {
                                                value: entity.id,
                                                label: entity.name,
                                                code: entity.code
                                            });
                                        $autocompleteList.append($div);
                                    });
                                },
                                error: function(xhr, status, error) {
                                    console.error('Error:', status, error);
                                    $autocompleteList.html(
                                        '<div>Error loading suggestions</div>');
                                }
                            });
                        }, 300);
                    });

                    $otherInput.parent().on('click', '.autocomplete-item', function() {
                        const entity = $(this).data('entity');
                        $otherInput.val(entity.label).removeClass('is-invalid');
                        $otherID.val(entity.value);
                        $otherCode.val(entity.code);
                        $otherInputDisplay.text(entity.code);
                        $otherInput.parent().find('.autocomplete-list').remove();
                    });

                    $(document).on('click', function(e) {
                        if (!$(e.target).closest('#otherInput, .autocomplete-list').length) {
                            $otherInput.parent().find('.autocomplete-list').remove();
                        }
                    });
                }
            }

            // ================================
            // EVENT HANDLERS
            // ================================

            // Bind debtorType change handler
            debtorType.on('change', handleDebtorTypeChange);
            handleDebtorTypeChange();

            if ($('#countryID').length) {
                $('#countryID').on('change', function() {
                    const selectedOption = $(this).find('option:selected');
                    const countryCode = selectedOption.attr('id') || '';
                    const countryName = selectedOption.text() || '';
                    $('#countryCode').val(countryCode);
                    $('#countryName').val(countryName);
                    console.log('countryID changed:', {
                        countryID: $(this).val(),
                        countryCode: countryCode,
                        countryName: countryName,
                        countryIdOptionExists: $(this).find(`option[value="${$(this).val()}"]`)
                            .length > 0
                    });
                    if (!countryCode) {
                        console.warn(
                            'countryID changed: No countryCode set. Please select a valid country.');
                        $('#countryID').addClass('is-invalid');
                    } else {
                        $('#countryID').removeClass('is-invalid');
                    }
                });
            } else {
                console.error('countryID select not found');
            }

            // Handle partyEntityType change
            partyEntityType.on('change', function() {
                toggleCompanyInput();
                toggleCountryReadonly(); // Update country readonly state based on entity type

                // Clear Other fields when changing entity type
                if (requestType === 'accounting') {
                    $('#otherInput').val('');
                    $('#otherID').val('');
                    $('#otherCode').val('');
                    $('#other_Input').text('');
                }
            });

            // NEW: Handle company name input for "Create only in D365" option
            companyNameInput.on('input blur', function() {
                const partyEntityTypeValue = partyEntityType.val();

                // For accounting mode with "Create only in D365" option
                if (requestType === 'accounting' && partyEntityTypeValue === 'Create only in D365') {
                    setTimeout(function() {
                        initializeOtherFieldsFromManualInput();
                    }, 100);
                }
            });

            // // Initialize maxCreditLimit input
            // $("#maxCreditLimit").on("input", function() {
            //     let maxCreditLimit = $(this).val();
            //     if (!$(this).prop('readonly') && maxCreditLimit.startsWith("0")) {
            //         $(this).val("1" + maxCreditLimit.slice(1));
            //     }
            // });
            // Credit Limit formatting is now handled at the bottom of $(document).ready()
            // See comprehensive implementation starting around line 2020
            // FIXED: Bind overseas entity event and initialize
            overseasEntityCheckbox.on('change', function() {
                console.log('Overseas entity checkbox changed:', $(this).is(':checked'));
                toggleLicenseInput();
            });

            // FIXED: Global override for overseasEntity changes with proper readonly handling
            $(document).on('change input', '#overseasEntity', function() {
                console.log('Overseas entity checkbox changed (global override):', $(this).is(':checked'));

                if ($(this).is(':checked')) {
                    licenceTypeValue
                        .removeAttr('required')
                        .removeClass('is-invalid')
                        .prop('readonly', true)
                        .prop('disabled', false) // Keep enabled for form submission
                        .val('NIL')
                        .addClass('overseas-readonly');

                    licenceTypeID
                        .removeAttr('required')
                        .removeClass('is-invalid')
                        .prop('disabled', false)
                        .addClass('readonly-select')
                        .attr('readonly', true)
                        .css('pointer-events', 'none');

                         $('#licenceTypeCode').val('NIL');

                    console.log('Global override: Set licence fields for overseas entity with NIL value');
                } else {
                    // Reset fields when unchecked
                    licenceTypeValue
                        .prop('readonly', false)
                        .prop('disabled', false)
                        .val('')
                        .removeClass('overseas-readonly');

                    if (requestType === 'accounting') {
                        licenceTypeValue.attr('required', 'required');
                        licenceTypeID.attr('required', 'required');
                    }

                    licenceTypeID
                        .prop('disabled', false)
                        .removeClass('readonly-select')
                        .removeAttr('readonly')
                        .css('pointer-events', 'auto');

                    console.log('Global override: Reset licence fields when overseas unchecked');
                }

                // Trigger the main toggle function
                setTimeout(toggleLicenseInput, 10);
            });

            // Initialize and bind event for Other checkbox
            toggleOtherInput();
            $('#otherCheck').on('change', function() {
                toggleOtherInput();

                // For accounting mode, initialize with client data when checkbox is checked
                if (requestType === 'accounting' && $(this).is(':checked')) {
                    setTimeout(function() {
                        initializeOtherFieldsFromClient();
                    }, 200);
                }
            });

            // Handle Next button click with validation (for both steps)
            $('#nextBtn, #nextBtn2').on('click', function(e) {
                e.preventDefault();

                const buttonId = $(this).attr('id');
                let stepErrorContainer, stepErrorMessage;

                // Determine which step we're validating based on button
                if (buttonId === 'nextBtn') {
                    // Step 1 to Step 2
                    stepErrorContainer = '#step-validation-error';
                    stepErrorMessage = '#step-error-message';
                } else {
                    // Step 2 to Step 3
                    stepErrorContainer = '#step2-validation-error';
                    stepErrorMessage = '#step2-error-message';
                }

                // Hide any existing step validation errors
                $(stepErrorContainer).hide();

                // Get current active step
                const currentStep = $('.content.active, .content.dstepper-block').attr('id');
                console.log('Current step:', currentStep, 'Button:', buttonId);

                // Step 1 (Basic Details) validation
                if (currentStep === 'basic-details') {
                    // Additional validation for first step fields
                    const requiredFields = [{
                            field: $('#partyEntityType'),
                            name: 'Operation Type'
                        },
                        {
                            field: $('#legalEntityType'),
                            name: 'Legal Type'
                        },
                        {
                            field: $('#companyName, #companyAutocomplete'),
                            name: 'Company Name'
                        },
                        {
                            field: $('#addressLine1'),
                            name: 'Address Line 1'
                        },
                        {
                            field: $('#countryID'),
                            name: 'Country'
                        },
                        {
                            field: $('#firstName'),
                            name: 'First Name'
                        },
                        {
                            field: $('#phoneNo'),
                            name: 'Phone Number'
                        }
                    ];

                    let hasError = false;
                    let missingFields = [];

                    requiredFields.forEach(function(item) {
                        const $field = item.field;
                        if ($field.is(':visible') && $field.attr('required') && !$field.val()) {
                            $field.addClass('is-invalid');
                            hasError = true;
                            missingFields.push(item.name);
                        } else {
                            $field.removeClass('is-invalid');
                        }
                    });

                    if (hasError) {
                        $(stepErrorMessage).text(
                            `Please fill in the following required fields: ${missingFields.join(', ')}`);
                        $(stepErrorContainer).show();

                        // Scroll to the first missing field
                        const firstInvalidField = $('.is-invalid').first();
                        if (firstInvalidField.length) {
                            $('html, body').animate({
                                scrollTop: firstInvalidField.offset().top - 100
                            }, 500);
                        }

                        return false;
                    }
                }

                // Step 2 (Office Details) validation - File upload validation only here
                else if (currentStep === 'office-details') {
                    // File upload validation only for accounting mode
                    if (requestType === 'accounting' && !validateFileUpload()) {
                        // Show step validation error
                        $(stepErrorMessage).text(
                            'File upload is required for accounting mode. Please upload a PDF file with description.'
                        );
                        $(stepErrorContainer).show();

                        // Scroll to file upload section to show the error
                        $('html, body').animate({
                            scrollTop: $('#file-upload-error').offset().top - 100
                        }, 500);

                        // Highlight the file upload section
                        $('.file-upload-container').addClass('shake-animation');
                        setTimeout(function() {
                            $('.file-upload-container').removeClass('shake-animation');
                        }, 1000);

                        return false;
                    }

                    // Validate other required fields in step 2
                    const step2RequiredFields = [{
                            field: $('#officeCountry'),
                            name: 'Office'
                        },
                        {
                            field: $('#billingCurrency'),
                            name: 'Billing Currency'
                        },
                        {
                            field: $('#creditDays'),
                            name: 'Credit Days'
                        },
                        {
                            field: $('#maxCreditLimit'),
                            name: 'Credit Limit'
                        },
                        {
                            field: $('#licenceTypeID'),
                            name: 'Licence Type'
                        },
                        {
                            field: $('#licenceTypeValue'),
                            name: 'Licence Value'
                        }
                    ];

                    let hasError = false;
                    let missingFields = [];

                    step2RequiredFields.forEach(function(item) {
                        const $field = item.field;
                        if ($field.is(':visible') && $field.attr('required') && !$field.val()) {
                            $field.addClass('is-invalid');
                            hasError = true;
                            missingFields.push(item.name);
                        } else {
                            $field.removeClass('is-invalid');
                        }
                    });

                    if (hasError) {
                        $(stepErrorMessage).text(
                            `Please fill in the following required fields: ${missingFields.join(', ')}`);
                        $(stepErrorContainer).show();

                        // Scroll to the first missing field
                        const firstInvalidField = $('.is-invalid').first();
                        if (firstInvalidField.length) {
                            $('html, body').animate({
                                scrollTop: firstInvalidField.offset().top - 100
                            }, 500);
                        }

                        return false;
                    }
                }

                // All validations passed, proceed to next step
                console.log('All current step validations passed, proceeding to next step');
                if (typeof stepper !== 'undefined') {
                    stepper.next();
                } else {
                    console.error('Stepper not found');
                }
            });

            // Clear step validation errors when fields are corrected
            $(document).on('input change', 'input, select', function() {
                // Clear individual field validation
                $(this).removeClass('is-invalid');

                // Check if all required fields are now filled for accounting mode
                if (requestType === 'accounting') {
                    setTimeout(function() {
                        const hasValidFiles = validateFileUpload();
                        const currentStep = $('.content.active, .content.dstepper-block').attr(
                            'id');

                        if (currentStep === 'basic-details') {
                            const requiredFields = [
                                $('#partyEntityType'),
                                $('#legalEntityType'),
                                $('#companyName, #companyAutocomplete'),
                                $('#addressLine1'),
                                $('#countryID'),
                                $('#firstName'),
                                $('#phoneNo')
                            ];

                            let allFieldsValid = true;
                            requiredFields.forEach(function($field) {
                                if ($field.is(':visible') && $field.attr('required') && !
                                    $field.val()) {
                                    allFieldsValid = false;
                                }
                            });

                            // Hide step validation error if everything is valid
                            if (allFieldsValid) {
                                $('#step-validation-error').fadeOut();
                            }
                        } else if (currentStep === 'office-details') {
                            const step2RequiredFields = [
                                $('#officeCountry'),
                                $('#billingCurrency'),
                                $('#creditDays'),
                                $('#maxCreditLimit'),
                                $('#licenceTypeID'),
                                $('#licenceTypeValue')
                            ];

                            let allFieldsValid = true;
                            step2RequiredFields.forEach(function($field) {
                                if ($field.is(':visible') && $field.attr('required') && !
                                    $field.val()) {
                                    allFieldsValid = false;
                                }
                            });

                            // Hide step validation error if everything is valid
                            if (hasValidFiles && allFieldsValid) {
                                $('#step2-validation-error').fadeOut();
                            }
                        }
                    }, 100);
                }
            });

            // Validate on submit
            $('button[name="createBtn"], button[name="submitBtn"]').on('click', function(e) {
                syncClientFields();

                // Validate company selection
                if (!validateCompanySelection()) {
                    e.preventDefault();
                    console.warn('Submit button: Company validation failed');
                    return;
                }

                // Validate file upload for accounting mode
                if (!validateFileUpload()) {
                    e.preventDefault();
                    console.warn('Submit button: File upload validation failed');
                    return;
                }

                // Validate form
                if (!validateForm()) {
                    e.preventDefault();
                    console.warn('Submit button: Form validation failed');
                } else {
                    console.log('Submit button: All validations passed');
                }
            });



            $('#clientPartyForm').on('submit', function(e) {
                console.log('Form submission - validating files...');
                console.log('Form submission - ensuring Other fields for accounting mode...');



                // ── FIX: Ensure otherCode is set for non-client entity types ──────────
                const entityType = partyEntityType.val();
                const nonClientTypes = ['Trucker / Transporter', 'Airline', 'Shipping Line'];

                if (nonClientTypes.includes(entityType)) {
                    const code = companyCodeInput.val();
                    const id = companyIdInput.val();
                    const name = companyAutocomplete.val() || companyNameInput.val();

                    if (code && id) {
                        $('#otherCode').val(code);
                        $('#otherID').val(id);
                        $('#otherInput').val(name);
                        $('#hiddenOtherCode').val(code);
                        $('#hiddenOtherID').val(id);
                        $('#hiddenOtherInput').val(name);
                        console.log('otherCode fixed for ' + entityType + ':', code);
                    } else {
                        console.warn(
                            'otherCode fix skipped - companyCode or companyId empty for:',
                            entityType);
                    }
                }
                // ── END FIX ────────────────────────────────────────────────────────────



                // CRITICAL: Ensure Other fields are populated for accounting mode before submission
                if (requestType === 'accounting') {
                    const hasAdminAccess =
                        {{ Auth::user()->can('create-client-party') ? 'true' : 'false' }};

                    console.log(
                        'Form submission - ensuring Other fields for accounting mode only');
                    ensureOtherFieldsForAccountingMode();
                    validateGuidFields();

                    // Double-check that Other fields are properly set based on user access level
                    let otherInput, otherID, otherCode, otherChecked;

                    if (hasAdminAccess) {
                        // Check admin fields (visible in Step 3)
                        otherInput = $('#otherInput').val();
                        otherID = $('#otherID').val();
                        otherCode = $('#otherCode').val();
                        otherChecked = $('#otherCheck').is(':checked');
                    } else {
                        // Check hidden fields (in Step 2)
                        otherInput = $('#hiddenOtherInput').val();
                        otherID = $('#hiddenOtherID').val();
                        otherCode = $('#hiddenOtherCode').val();
                        otherChecked = $('#hiddenOtherCheck').val() === '1';
                    }

                    if (!otherInput || !otherID || !otherCode || !otherChecked) {
                        console.warn(
                            'Other fields not properly initialized for accounting mode, attempting to fix...'
                        );

                        // Try one more time to initialize
                        const companyName = companyNameInput.val() || companyAutocomplete.val();
                        if (companyName) {
                            if (companyIdInput.val() && companyCodeInput.val()) {
                                initializeOtherFieldsFromClient();
                            } else {
                                initializeOtherFieldsFromManualInput();
                            }
                        }
                    }

                    // Log final Other fields state for accounting mode
                    console.log(
                        'Final Other fields state before submission (accounting mode):', {
                            hasAdminAccess: hasAdminAccess,
                            otherInput: hasAdminAccess ? $('#otherInput').val() : $(
                                    '#hiddenOtherInput')
                                .val(),
                            otherID: hasAdminAccess ? $('#otherID').val() : $(
                                '#hiddenOtherID').val(),
                            otherCode: hasAdminAccess ? $('#otherCode').val() : $(
                                    '#hiddenOtherCode')
                                .val(),
                            otherChecked: hasAdminAccess ? $('#otherCheck').is(':checked') : $(
                                '#hiddenOtherCheck').val() === '1',
                            companyName: companyNameInput.val() || companyAutocomplete
                                .val(),
                            mode: 'accounting'
                        });
                } else {
                    console.log(
                        'Operational mode - Other fields not required, skipping initialization'
                    );
                }

                // FIXED: Update license code before form submission
                updateLicenseCode();

                // Validate company selection
                if (!validateCompanySelection()) {
                    e.preventDefault();
                    console.log(
                        'Form submission prevented due to company selection validation');
                    return;
                }

                // Validate file upload for accounting mode
                if (!validateFileUpload()) {
                    e.preventDefault();
                    console.log('Form submission prevented due to file validation errors');

                    // Scroll to file upload section
                    $('html, body').animate({
                        scrollTop: $('.file-upload-container').offset().top - 100
                    }, 500);

                    return false;
                }

                // For accounting mode, ensure Other fields are populated based on user access
                if (requestType === 'accounting') {
                    const hasAdminAccess =
                        {{ Auth::user()->can('create-client-party') ? 'true' : 'false' }};
                    const companyName = companyNameInput.val() || companyAutocomplete.val();

                    if (hasAdminAccess && $('#otherCheck').is(':checked')) {
                        // Admin user with checkbox checked
                        if (!$('#otherInput').val() && companyName) {
                            initializeOtherFieldsFromClient();
                        }
                    } else if (!hasAdminAccess && companyName) {
                        // Non-admin user - ensure hidden fields are populated
                        if (!$('#hiddenOtherInput').val()) {
                            if (companyIdInput.val() && companyCodeInput.val()) {
                                initializeOtherFieldsFromClient();
                            } else {
                                initializeOtherFieldsFromManualInput();
                            }
                        }
                    }
                }

                // Handle field states for different modes
                if (requestType === 'operational') {
                    // For operational mode, disable accounting fields but keep license fields enabled
                    legalEntityType
                        .removeAttr('required')
                        .removeClass('is-invalid')
                        .prop('disabled', true)
                        .val('');
                    billingCurrency
                        .removeAttr('required')
                        .removeClass('is-invalid')
                        .prop('disabled', true)
                        .val('');
                    creditDays
                        .removeAttr('required')
                        .removeClass('is-invalid')
                        .prop('disabled', true)
                        .val('');
                    maxCreditLimit
                        .removeAttr('required')
                        .removeClass('is-invalid')
                        .prop('disabled', true)
                        .val('');

                    // For license fields in operational mode - always NIL
                    licenceTypeValue
                        .removeAttr('required')
                        .removeClass('is-invalid')
                        .prop('disabled', false) // Keep enabled for submission
                        .prop('readonly', true)
                        .val('NIL');

                    licenceTypeID
                        .removeAttr('required')
                        .removeClass('is-invalid')
                        .prop('disabled', false)
                        .removeClass('readonly-select')
                        .removeAttr('readonly')
                        .css('pointer-events', 'auto');

                    console.log(
                        'Form submit: operational mode, ensured license fields submit with NIL'
                    );

                } else if (overseasEntityCheckbox.is(':checked')) {
                    // For accounting mode with overseas entity
                    licenceTypeValue
                        .removeAttr('required')
                        .removeClass('is-invalid')
                        .prop('readonly', true)
                        .prop('disabled', false) // Keep enabled for submission
                        .val('NIL');

                    licenceTypeID
                        .removeAttr('required')
                        .removeClass('is-invalid')
                        .prop('disabled', false)
                        .removeClass('readonly-select')
                        .removeAttr('readonly')
                        .css('pointer-events', 'auto');

                        // ADD THIS:
                        $('#licenceTypeCode').val('NIL');

                    console.log(
                        'Form submit: overseas entity checked, license fields will submit with NIL'
                    );
                }

                // Log submission data
                console.log('Form submitting with license data:', {
                    overseasEntity: overseasEntityCheckbox.is(':checked'),
                    licenceTypeValue: licenceTypeValue.val(),
                    licenceTypeID: licenceTypeID.val(),
                    licenceTypeCode: licenceTypeCode.val(),
                    licenceTypeValueDisabled: licenceTypeValue.prop('disabled'),
                    licenceTypeValueReadonly: licenceTypeValue.prop('readonly'),
                    requestType: requestType
                });

                // Log Other fields data for debugging - check both admin and hidden fields
                if (requestType === 'accounting') {
                    const hasAdminAccess =
                        {{ Auth::user()->can('create-client-party') ? 'true' : 'false' }};

                    console.log('Form submitting with Other fields data (accounting mode):', {
                        hasAdminAccess: hasAdminAccess,
                        // Admin fields (Step 3)
                        adminOtherCheck: $('#otherCheck').is(':checked'),
                        adminOtherInput: $('#otherInput').val(),
                        adminOtherID: $('#otherID').val(),
                        adminOtherCode: $('#otherCode').val(),
                        // Hidden fields (Step 2)
                        hiddenOtherCheck: $('#hiddenOtherCheck').val(),
                        hiddenOtherInput: $('#hiddenOtherInput').val(),
                        hiddenOtherID: $('#hiddenOtherID').val(),
                        hiddenOtherCode: $('#hiddenOtherCode').val(),
                        // Active fields being submitted
                        activeOtherInput: hasAdminAccess ? $('#otherInput').val() : $(
                            '#hiddenOtherInput').val(),
                        activeOtherID: hasAdminAccess ? $('#otherID').val() : $(
                                '#hiddenOtherID')
                            .val(),
                        activeOtherCode: hasAdminAccess ? $('#otherCode').val() : $(
                            '#hiddenOtherCode').val(),
                        activeOtherChecked: hasAdminAccess ? $('#otherCheck').is(
                            ':checked') : $(
                            '#hiddenOtherCheck').val() === '1',
                        requestType: requestType
                    });
                } else {
                    console.log(
                        'Form submitting - operational mode, no Other fields data required');
                }

                if ($('#clientPartyForm')[0].checkValidity() === false) {
                    e.preventDefault();
                    e.stopPropagation();
                    console.warn('Form validation failed due to required fields');
                }

                $('#clientPartyForm').addClass('was-validated');
                console.log('All validations passed, form submitting...');
            });

            setTimeout(function() {
                if (requestType === 'accounting') {
                    // Check if we have old values (form was submitted with errors and returned)
                    const oldOtherInput = $('input[name="otherInput"]').val();
                    const oldCompanyName = $('input[name="companyName"]').val() || $(
                        'input[name="companyAutocomplete"]').val();

                    if (oldOtherInput) {
                        // Form was returned with errors, restore Other checkbox state
                        $('#otherCheck').prop('checked', true);
                        console.log('Restored Other checkbox state from old values');
                    } else if (oldCompanyName) {
                        // We have company data but no Other data, initialize it
                        ensureOtherFieldsForAccountingMode();
                        console.log(
                            'Auto-initialized Other fields on page load for accounting mode'
                        );
                    }
                }
            }, 1000);

            // ================================
            // UTILITY FUNCTIONS FOR DEBUGGING
            // ================================

            // Global function to manually trigger Other fields initialization
            window.forceInitializeOtherFields = function() {
                if (requestType === 'accounting') {
                    ensureOtherFieldsForAccountingMode();
                    console.log('Manually triggered Other fields initialization');
                } else {
                    console.log('Not in accounting mode, Other fields initialization not needed');
                }
            };

            // Enhanced debug function for Other fields
            window.debugOtherFields = function() {
                console.log('Other Fields Debug State:', {
                    requestType: requestType,
                    otherCheck_checked: $('#otherCheck').is(':checked'),
                    otherInput_value: $('#otherInput').val(),
                    otherID_value: $('#otherID').val(),
                    otherCode_value: $('#otherCode').val(),
                    otherInput_display: $('#other_Input').text(),
                    companyName: $('#companyName').val(),
                    companyAutocomplete: $('#companyAutocomplete').val(),
                    companyId: $('#companyId').val(),
                    companyCode: $('#companyCode').val(),
                    adminSectionVisible: $('#admin-details').is(':visible'),
                    canCreateClientParty: {{ Auth::user()->can('create-client-party') ? 'true' : 'false' }}
                });
            };

            // ================================
            // UTILITY FUNCTIONS
            // ================================

            function sortCreditDaysAscending() {
                const $creditDaysSelect = $('#creditDays');
                const $options = $creditDaysSelect.find('option:not([value=""], [disabled])');

                if ($options.length > 0) {
                    // Convert options to array and sort by numeric value
                    const optionsArray = $options.toArray().sort(function(a, b) {
                        const valueA = parseInt($(a).val()) || 0;
                        const valueB = parseInt($(b).val()) || 0;
                        return valueA - valueB;
                    });

                    // Remove all non-default options
                    $options.remove();

                    // Append sorted options
                    optionsArray.forEach(function(option) {
                        $creditDaysSelect.append(option);
                    });

                    console.log('Credit days sorted in ascending order');
                }
            }

            // Monitor required attribute changes
            function monitorRequiredAttribute() {
                const observer = new MutationObserver((mutations) => {
                    mutations.forEach((mutation) => {
                        if (mutation.type === 'attributes' && mutation.attributeName ===
                            'required') {
                            console.warn('Required attribute changed:', {
                                element: mutation.target.id,
                                required: $(mutation.target).attr('required'),
                                isOverseas: overseasEntityCheckbox.is(
                                    ':checked'),
                                requestType: requestType,
                                timestamp: new Date().toISOString()
                            });
                            if (requestType === 'operational' || overseasEntityCheckbox
                                .is(
                                    ':checked')) {
                                if (['legalEntityType', 'billingCurrency', 'creditDays',
                                        'maxCreditLimit', 'licenceTypeValue',
                                        'licenceTypeID'
                                    ].includes(mutation.target.id) && $(mutation.target)
                                    .attr(
                                        'required')) {
                                    console.warn(
                                        `Conflict: ${mutation.target.id} required despite operational mode or overseasEntity checked. Forcing removal.`
                                    );
                                    $(mutation.target).removeAttr('required')
                                        .removeClass(
                                            'is-invalid');
                                    if (mutation.target.id === 'licenceTypeValue') {
                                        $(mutation.target).val('NIL').prop('readonly',
                                            true).prop(
                                            'disabled', false
                                        ); // Keep enabled for submission
                                    } else if (mutation.target.id === 'licenceTypeID') {
                                        $(mutation.target).prop('disabled', false)
                                            .addClass(
                                                'readonly-select').attr('readonly',
                                                true).css(
                                                'pointer-events', 'none');
                                    } else {
                                        $(mutation.target).val('').prop('disabled',
                                            true);
                                    }
                                }
                            }
                        }
                    });
                });

                ['legalEntityType', 'billingCurrency', 'creditDays', 'maxCreditLimit',
                    'licenceTypeValue',
                    'licenceTypeID'
                ].forEach(id => {
                    if (document.getElementById(id)) {
                        observer.observe(document.getElementById(id), {
                            attributes: true
                        });
                    } else {
                        console.error(`Cannot observe: ${id} not found in DOM`);
                    }
                });
            }

            // ================================
            // DRAG AND DROP FUNCTIONALITY (OPTIONAL ENHANCEMENT)
            // ================================

            $('.file-upload-container').on('dragover', function(e) {
                e.preventDefault();
                e.stopPropagation();
                $(this).addClass('drag-over');
            });

            $('.file-upload-container').on('dragleave', function(e) {
                e.preventDefault();
                e.stopPropagation();
                $(this).removeClass('drag-over');
            });

            $('.file-upload-container').on('drop', function(e) {
                e.preventDefault();
                e.stopPropagation();
                $(this).removeClass('drag-over');

                const files = e.originalEvent.dataTransfer.files;
                if (files.length > 0) {
                    const fileInput = $(this).find('input[type="file"]').first()[0];
                    if (fileInput) {
                        fileInput.files = files;
                        $(fileInput).trigger('change');
                    }
                }
            });

            // ================================
            // RE-RUN ON STEPPER CHANGE
            // ================================

            // Re-run on stepper change
            $(document).on('shown.bs-stepper', '.bs-stepper', function() {
                if (requestType === 'operational' || overseasEntityCheckbox.is(':checked')) {
                    legalEntityType
                        .removeAttr('required')
                        .removeClass('is-invalid')
                        .prop('disabled', true)
                        .val('');
                    billingCurrency
                        .removeAttr('required')
                        .removeClass('is-invalid')
                        .prop('disabled', true)
                        .val('');
                    creditDays
                        .removeAttr('required')
                        .removeClass('is-invalid')
                        .prop('disabled', true)
                        .val('');
                    maxCreditLimit
                        .removeAttr('required')
                        .removeClass('is-invalid')
                        .prop('disabled', true)
                        .val('');
                    licenceTypeValue
                        .removeAttr('required')
                        .removeClass('is-invalid')
                        .prop('readonly', true)
                        .prop('disabled', false) // Keep enabled for submission
                        .val('NIL')
                        .addClass('overseas-readonly');
                    licenceTypeID
                        .removeAttr('required')
                        .removeClass('is-invalid')
                        .prop('disabled', false)
                        .addClass('readonly-select')
                        .attr('readonly', true)
                        .css('pointer-events', 'none');
                    console.log('Stepper change: Ensured accounting fields are not required');
                }

                // Update license code after stepper change
                setTimeout(updateLicenseCode, 100);

                if (requestType === 'accounting') {
                    setTimeout(function() {
                        ensureOtherFieldsForAccountingMode();
                    }, 200);
                }
            });

            // Re-sort if credit days are updated via AJAX
            $(document).on('DOMNodeInserted', '#creditDays option', function() {
                setTimeout(sortCreditDaysAscending, 100);
            });

            // Form submission logging
            $('form').on('submit', function() {
                console.log('Form submitting:', {
                    partyEntityType: partyEntityType.val(),
                    companyName: companyNameInput.val(),
                    companyAutocomplete: companyAutocomplete.val(),
                    companyId: companyIdInput.val(),
                    companyCode: companyCodeInput.val(),
                    otherCheck: $('#otherCheck').is(':checked'),
                    otherInput: $('#otherInput').val(),
                    otherID: $('#otherID').val(),
                    otherCode: $('#otherCode').val(),
                    overseasEntity: overseasEntityCheckbox.is(':checked'),
                    licenceTypeValue: licenceTypeValue.val(),
                    licenceTypeID: licenceTypeID.val(),
                    licenceTypeCode: licenceTypeCode.val(),
                    debtorType: debtorType.val(),
                    creditDays: creditDays.val(),
                    maxCreditLimit: maxCreditLimit.val(),
                    legalEntityType: legalEntityType.val(),
                    billingCurrency: billingCurrency.val()
                });
            });

            // ================================
            // INITIALIZATION
            // ================================

            // Initialize
            toggleLicenseInput();
            toggleAccountingFields();

            // Show/hide fields based on request type
            if (requestType === "operational") {
                partyEntityType.closest(".col-md-6").show();
                legalEntityType.closest(".col-md-6").hide();
                $("#debtor-vendor").hide();
            } else if (requestType === "accounting") {
                partyEntityType.closest(".col-md-6").show();
                legalEntityType.closest(".col-md-6").show();
            }

            // Initialize on page load
            toggleCompanyInput();
            syncClientFields();

            // Initialize country readonly state
            toggleCountryReadonly();

            // Apply accounting mode styling if needed
            if (requestType === 'accounting') {
                $('body').addClass('accounting-mode');
                $('.file-upload-container').addClass('file-upload-required');

                // Add visual indicator for required files
                $('legend:contains("File Upload")').append(' <span class="text-danger">*</span>');

                console.log('Accounting mode: File upload is mandatory');

                // Show initial validation message for file upload
                setTimeout(function() {
                    validateFileUpload();
                }, 500);
            }

            // Call sort function after DOM is ready and after any AJAX updates
            sortCreditDaysAscending();

            // Re-run to catch late overrides
            setTimeout(toggleLicenseInput, 1000);
            setTimeout(toggleLicenseInput, 3000);
            setTimeout(toggleAccountingFields, 1000);
            setTimeout(toggleAccountingFields, 3000);

            // Monitor required attribute changes
            monitorRequiredAttribute();

            // Sort credit days after a short delay to ensure options are loaded
            setTimeout(function() {
                sortCreditDaysAscending();
            }, 1000);

            // FIXED: Additional event handlers to ensure license code is always updated
            $('#licenceTypeID').on('focus blur', function() {
                setTimeout(updateLicenseCode, 10);
            });

            // FIXED: Backup event handler using different event namespace
            $(document).on('change.backup', '#licenceTypeID', function() {
                console.log('Backup license change handler triggered');
                setTimeout(updateLicenseCode, 20);
            });

            // FIXED: Manual trigger on specific actions
            $('#licenceTypeID').on('click', function() {
                if (!overseasEntityCheckbox.is(':checked') && requestType !== 'operational') {
                    setTimeout(updateLicenseCode, 50);
                }
            });

            // Expose validation function globally for external calls
            window.validateFileUpload = validateFileUpload;

            // Add CSS for overseas readonly styling
            const overseaStyles = `
                          .overseas-readonly {
                         background-color: #f8f9fa !important;
                         cursor: not-allowed !important;
                         border-color: #ced4da !important;
                         }

                         .overseas-readonly:focus {
                            background-color: #f8f9fa !important;
                            border-color: #ced4da !important;
                            box-shadow: none !important;
                            }

                            .overseas-readonly::placeholder {
                                color: #6c757d !important;
                                opacity: 0.8 !important;
                                }
                                `;

            // Append styles to head if not already present
            if (!$('head').find('style[data-overseas-styles]').length) {
                $('<style>').attr('data-overseas-styles', 'true').text(overseaStyles).appendTo('head');
            }
            /* --------------------------------------------------------------
            MAX CREDIT LIMIT – comma separator (any length, no errors)
            -------------------------------------------------------------- */
            function fmtNum(n) {
                return n.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
            }

            function stripNum(s) {
                return s.replace(/,/g, '').replace(/[^0-9]/g, '');
            }

            /* ---- change the input type once (only if it is still number) ---- */
            $('#maxCreditLimit').attr('type', 'text'); // <-- critical

            /* ---- real-time formatting while typing ---- */
            $(document).on('input', '#maxCreditLimit', function() {
                if ($(this).prop('readonly') || $(this).prop('disabled')) return;

                const $el = $(this);
                const cur = $el[0].selectionStart; // cursor pos
                const old = $el.val();
                const clean = stripNum(old); // only digits
                const num = clean === '' ? '' : (clean.replace(/^0+/, '') || '0');
                const formatted = num === '' ? '' : fmtNum(num);

                if (formatted !== old) {
                    $el.val(formatted);

                    /* ---- restore cursor (count commas before the original position) ---- */
                    const commasBeforeOld = (old.slice(0, cur).match(/,/g) || []).length;
                    const commasBeforeNew = (formatted.slice(0, cur).match(/,/g) || []).length;
                    const newPos = cur + (commasBeforeNew - commasBeforeOld);
                    const safePos = Math.max(0, Math.min(newPos, formatted.length));

                    setTimeout(() => $el[0].setSelectionRange(safePos, safePos), 0);
                }
            });

            /* ---- on blur – enforce minimum 1 (except CASHCUSTOM) ---- */
            $(document).on('blur', '#maxCreditLimit', function() {
                const $el = $(this);
                let raw = stripNum($el.val()) || '0';
                let n = parseInt(raw, 10);

                if ($('#debtorType').val() === 'CASHCUSTOM') {
                    $el.val('0');
                    return;
                }

                if (isNaN(n) || n < 1) n = 1;
                $el.val(fmtNum(n));
            });

            /* ---- before submit – send clean integer ---- */
            $('#clientPartyForm').on('submit', function() {
                const $el = $('#maxCreditLimit');
                if (!$el.length) return;

                let clean = stripNum($el.val());
                if (clean === '' || clean === '0') {
                    clean = $('#debtorType').val() === 'CASHCUSTOM' ? '0' : '1';
                }
                $el.val(clean); // plain number for the backend
                console.log('Submitted maxCreditLimit (clean):', clean);
            });

            /* ---- initialise any value that may already be in the field ---- */
            $(function() {
                setTimeout(() => {
                    const $el = $('#maxCreditLimit');
                    const raw = stripNum($el.val());
                    if (raw && !isNaN(parseInt(raw, 10))) {
                        $el.val(fmtNum(parseInt(raw, 10)));
                    }
                }, 300);
            });

            const officeSelect = $('#officeCountry');
            if (officeSelect.length) {
                const selectedOffice = officeSelect.find('option:selected');
                const officeText = selectedOffice.text().trim();

                if (selectedOffice.val() && officeText) {
                    // Auto-select license type if office is pre-selected
                    setTimeout(function() {
                        autoSelectLicenseType(officeText);
                    }, 100);
                }
            }
            // NEW: Dynamically set OperationType based on URL and/or selection
            function updateOperationType() {
                const urlType = new URLSearchParams(window.location.search).get('type');
                let opType = 'OperationalEntity'; // default

                // Priority 1: URL param ?type=accounting
                if (urlType === 'accounting') {
                    opType = 'AccountingEntity';
                }
                // Priority 2: Selected partyEntityType is accounting-related
                else if (partyEntityType.val()) {
                    const accountingValues = [
                        'Client',
                        'Create only in D365',
                        'Debtor & Vendor',
                        'Debtor',
                        'Vendor',
                        'Only in D365 Debtor',
                        'Only in D365 Vendor'
                    ];
                    if (accountingValues.includes(partyEntityType.val())) {
                        opType = 'AccountingEntity';
                    }
                }

                // Set the hidden field
                $('#operationType').val(opType);

                console.log('OperationType updated:', {
                    finalValue: opType,
                    urlType: urlType,
                    selectedType: partyEntityType.val(),
                    timestamp: new Date().toISOString()
                });
            }

            // Run on page load
            updateOperationType();

            // Re-run when partyEntityType changes
            partyEntityType.on('change', updateOperationType);

            // Optional: Re-run when anything changes (safety net)
            $(document).on('change input', 'select, input', function() {
                updateOperationType();
            });

        });

        // ================================
        // GLOBAL FUNCTIONS FOR COMPATIBILITY
        // ================================

        window.User = {
            value: '{{ auth()->user()->can('edit-client-party') }}'
        }

        // FIXED: Global access to license update function for debugging
        window.updateLicenseCode = function() {
            const licenceTypeSelect = $('#licenceTypeID');
            const selectedOption = licenceTypeSelect.find('option:selected');
            const code = selectedOption.attr('data-code') || '';

            console.log('Global license code update:', {
                selectExists: licenceTypeSelect.length > 0,
                selectedValue: licenceTypeSelect.val(),
                selectedOption: selectedOption.length > 0,
                dataCodeAttribute: selectedOption.attr('data-code'),
                code: code,
                hiddenInputExists: $('#licenceTypeCode').length > 0,
                currentHiddenValue: $('#licenceTypeCode').val()
            });

            $('#licenceTypeCode').val(code);
            $('#licenceTypeCode').trigger('change');

            return code;
        };

        // Make sure to call the ensure function during initial setup
        if (requestType === 'accounting') {
            setTimeout(function() {
                ensureOtherFieldsForAccountingMode();
            }, 2000);
        }

        // FIXED: Global debug function
        window.debugLicenseState = function() {
            console.log('License Debug State:', {
                licenceTypeID_value: $('#licenceTypeID').val(),
                licenceTypeID_disabled: $('#licenceTypeID').prop('disabled'),
                licenceTypeID_readonly: $('#licenceTypeID').attr('readonly'),
                licenceTypeID_pointerEvents: $('#licenceTypeID').css('pointer-events'),
                licenceTypeID_events: $._data($('#licenceTypeID')[0], 'events'),
                licenceTypeCode_value: $('#licenceTypeCode').val(),
                overseasEntity_checked: $('#overseasEntity').is(':checked'),
                requestType: new URLSearchParams(window.location.search).get('type')
            });
        };


        // NEW: Global debug function for Other fields
        window.debugOtherFields = function() {
            console.log('Other Fields Debug State:', {
                otherCheck_checked: $('#otherCheck').is(':checked'),
                otherInput_value: $('#otherInput').val(),
                otherID_value: $('#otherID').val(),
                otherCode_value: $('#otherCode').val(),
                otherInput_display: $('#other_Input').text(),
                companyName: $('#companyName').val(),
                companyAutocomplete: $('#companyAutocomplete').val(),
                companyId: $('#companyId').val(),
                companyCode: $('#companyCode').val(),
                requestType: new URLSearchParams(window.location.search).get('type')
            });
            biz
        };
    </script>

    <script src="{{ asset('js/client-party/show-create-client-party.js') }}"></script>
@stop
