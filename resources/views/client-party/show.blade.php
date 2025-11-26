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
                                    <button type="button" class="step-trigger" role="tab"
                                        aria-controls="office-details" id="office-details-trigger" aria-selected="true">
                                        <span class="bs-stepper-circle">2</span>
                                        <span class="bs-stepper-label">Office Details</span>
                                    </button>
                                </div>
                                @if (Auth::user()->can('create-client-party'))
                                    <div class="line"></div>
                                    <div class="step" data-target="#admin-details">
                                        <button type="button" class="step-trigger" role="tab"
                                            aria-controls="office-details" id="admin-details-trigger" aria-selected="true">
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
                            <form action="{{ route('client-party.update', [$clientParty->Id]) }}" method="POST"
                                class="form-horizontal" enctype="multipart/form-data" id="clientPartyForm">
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
                                                        <select class="form-control form-control-sm" id="partyEntityType"
                                                            name="partyEntityType" required>
                                                            <option value="Client"
                                                                @if (old('partyEntityType', $clientParty->OperationalEntityType) == 'Client') selected @endif>Client
                                                            </option>
                                                            <option value="Create only in D365"
                                                                @if (old('partyEntityType') == 'Create only in D365') selected @endif>
                                                                Create only in D365</option>
                                                            <option value="Agent / Forwarder"
                                                                @if (old('partyEntityType', $clientParty->OperationalEntityType) == 'Agent / Forwarder') selected @endif>Agent /
                                                                Forwarder</option>
                                                            <option value="Airline"
                                                                @if (old('partyEntityType', $clientParty->OperationalEntityType) == 'Airline') selected @endif>Airline
                                                            </option>
                                                            <option value="Shipping Line"
                                                                @if (old('partyEntityType', $clientParty->OperationalEntityType) == 'Shipping Line') selected @endif>Shipping
                                                                Line</option>
                                                            <option value="Trucker / Transporter"
                                                                @if (old('partyEntityType', $clientParty->OperationalEntityType) == 'Trucker / Transporter') selected @endif>Trucker /
                                                                Transporter</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>

                                            @if ($type === 'accounting')
                                                <div class="col col-md-6 col-sm-12">
                                                    <div class="form-group row mb-1">
                                                        <label for="legalEntityType"
                                                            class="col-sm-3 col-lg-4 col-md-4 col-form-label col-form-label-sm"
                                                            style="font-size: 17px">Legal Type <span
                                                                class="text-danger">*</span></label>
                                                        <div class="col-sm-9 col-lg-8 col-md-8">
                                                            <select class="form-control form-control-sm"
                                                                id="legalEntityType" name="legalEntityType">
                                                                <option value="" disabled selected></option>
                                                                <option value="Debtor & Vendor"
                                                                    @if (old('legalEntityType', $clientParty->LegalEntityType) == 'Debtor & Vendor') selected @endif>
                                                                    Debtor & Vendor
                                                                </option>
                                                                <option value="Debtor"
                                                                    @if (old('legalEntityType', $clientParty->LegalEntityType) == 'Debtor') selected @endif>
                                                                    Debtor
                                                                </option>
                                                                <option value="Vendor"
                                                                    @if (old('legalEntityType', $clientParty->LegalEntityType) == 'Vendor') selected @endif>
                                                                    Vendor
                                                                </option>
                                                                <option value="Only in D365 Debtor"
                                                                    @if (old('legalEntityType', $clientParty->LegalEntityType) == 'Only in D365 Debtor') selected @endif>
                                                                    Only in D365 Debtor</option>
                                                                <option value="Only in D365 Vendor"
                                                                    @if (old('legalEntityType', $clientParty->LegalEntityType) == 'Only in D365 Vendor') selected @endif>
                                                                    Only in D365 Vendor</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif

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
                                                            class="fa fa-building" aria-hidden="true"></i>Company
                                                        Name<span class="text-danger">*</span></label>
                                                    <div class="col-sm-9 col-lg-8 col-md-8">
                                                        <input type="text" class="form-control form-control-sm"
                                                            value="{{ old('companyName', $clientParty->Name) }}"
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
                                                        <textarea name="addressLine1" id="addressLine1" class="form-control form-control-sm">{{ old('addressLine1', $clientParty->Address_Line1) }}</textarea>
                                                    </div>
                                                </div>
                                                {{-- AddressLine 2 --}}
                                                <div class="form-group row mb-1">
                                                    <label for="addressLine2"
                                                        class="col-sm-3 col-lg-4 col-form-label col-form-label-sm"><i
                                                            class="fa fa-map-marker" aria-hidden="true"></i> AddressLine
                                                        2</label>
                                                    <div class="col-sm-9 col-lg-8">
                                                        <textarea name="addressLine2" id="addressLine2" class="form-control form-control-sm">{{ old('addressLine2', $clientParty->Address_Line2) }}</textarea>
                                                    </div>
                                                </div>
                                                {{-- Country Code and Country Name --}}
                                                <div class="form-group row mb-1">
                                                    <label for="country"
                                                        class="col-sm-3 col-lg-4 col-form-label col-form-label-sm"><i
                                                            class="fa fa-globe" aria-hidden="true"></i> Country <span
                                                            class="text-danger">*</span></label>
                                                    <div class="col-sm-8 col-lg-8">

                                                        <label class="sr-only" for="country">Country</label>
                                                        <div class="form-group">

                                                            <select class="form-control form-control-sm" name="countryID"
                                                                id="countryID">
                                                                <option value="">Select a Country...</option>
                                                                @forelse ($countries as $country)
                                                                    <option value="{{ $country->CountryID }}"
                                                                        {{-- FIX 1: Value is now the country's actual ID --}}
                                                                        data-code="{{ $country->CountCode }}"
                                                                        {{-- FIX 2: Use data-code instead of a duplicate id --}}
                                                                        @if (old('countryID', $clientParty->Address_CountryId) == $country->CountryID) selected @endif>
                                                                        {{-- This logic was already correct --}}
                                                                        {{ $country->CountryName }}
                                                                    </option>
                                                                @empty
                                                                    <option value="" disabled>No countries found
                                                                    </option>
                                                                @endforelse
                                                            </select>

                                                            <input type="hidden" name="countryName" id="countryName"
                                                                value="{{ old('countryName', $clientParty->Address_CountryName) }}">
                                                            <input type="hidden" name="countryCode" id="countryCode"
                                                                value="{{ old('countryCode', $clientParty->Address_CountryCode) }}">

                                                        </div>

                                                    </div>
                                                </div>
                                                {{-- Province zipcode --}}
                                                <div class="form-group row mb-1 ">
                                                    <label for="province"
                                                        class="col-sm-3 col-lg-4 col-form-label col-form-label-sm"><i
                                                            class="fa fa-map-pin" aria-hidden="true"></i> Province
                                                        &
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

                                                    </div>
                                                    <div class="col-sm-4 col-lg-4">
                                                        <input type="text" class="form-control form-control-sm"
                                                            value="{{ old('zipCode', $clientParty->Address_Pin) }}"
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
                                                            value="{{ old('cityName', $clientParty->Address_City) }}"
                                                            name="cityName" id="cityInput">

                                                    </div>
                                                </div>

                                            </fieldset>
                                        </div>
                                        <div class="col-md-6 col-sm-12">
                                            <!-- Contact Details -->

                                            {{-- NEW ONE  --}}

                                            <fieldset class="border p-2 card shadow-lg mb-0 pt-0">
                                                <legend class="w-auto p-0 m-0">Contact Details</legend>
                                                <div class="form-group row mb-1">
                                                    <label for="person"
                                                        class="col-sm-3 col-md-4 col-lg-4 col-form-label col-form-label-sm">
                                                        <i class="fa fa-user" aria-hidden="true"></i> Person <span
                                                            style="color:red">*</span>
                                                    </label>
                                                    <div class="col-sm-4 col-md-4 col-lg-4">
                                                        <input type="text" class="form-control form-control-sm"
                                                            value="{{ old('firstName', $clientParty->Cont_Bch_PersonName_FirstName ?: '-') }}"
                                                            name="firstName" id="firstName" placeholder="First Name">
                                                    </div>
                                                    <div class="col-sm-5 col-md-4 col-lg-4">
                                                        <input type="text" class="form-control form-control-sm"
                                                            value="{{ old('lastName', $clientParty->Cont_Bch_PersonName_LastName ?: '-') }}"
                                                            name="lastName" id="lastName" placeholder="Last Name">
                                                    </div>
                                                </div>
                                                <div class="form-group row mb-1">
                                                    <label for="department"
                                                        class="col-sm-3 col-lg-4 col-form-label col-form-label-sm">
                                                        <i class="fa fa-sitemap" aria-hidden="true"></i> Department
                                                    </label>
                                                    <div class="col-sm-9 col-lg-8">
                                                        <input type="text" class="form-control form-control-sm"
                                                            value="{{ old('department', $clientParty->Cont_Bch_Dept) }}"
                                                            name="department" id="department" placeholder="Department">
                                                    </div>
                                                </div>
                                                <div class="form-group row mb-1">
                                                    <label for="phoneNo"
                                                        class="col col-sm-3 col-md-3 col-lg-4 col-form-label col-form-label-sm">
                                                        <i class="fa fa-phone" aria-hidden="true"></i> Phone No. <span
                                                            style="color:red">*</span>
                                                    </label>
                                                    <div class="col col-sm-9 col-md-9 col-lg-8">
                                                        <input type="text"
                                                            class="form-control form-control-sm number_input"
                                                            value="{{ old('phoneNo', $clientParty->Cont_Bch_PhoneNumbers ?: '0') }}"
                                                            name="phoneNo" id="phoneNo" placeholder="Phone Number">
                                                    </div>
                                                </div>
                                                <div class="form-group row mb-1">
                                                    <label for="contractEmail"
                                                        class="col-sm-3 col-lg-4 col-form-label col-form-label-sm">
                                                        <i class="fa fa-envelope" aria-hidden="true"></i> Email
                                                    </label>
                                                    <div class="col-sm-9 col-lg-8">
                                                        <input type="email" name="contractEmail" id="contractEmail"
                                                            value="{{ old('contractEmail', $clientParty->Cont_Bch_Email) }}"
                                                            class="form-control form-control-sm"
                                                            placeholder="Enter Email">
                                                    </div>
                                                </div>
                                            </fieldset>

                                        </div>
                                    </div>
                                    <button class="btn btn-primary m-2 btn-sm float-right" name="next" type="button"
                                        onclick="stepper.next()">Next <i class="fa fa-step-forward"
                                            aria-hidden="true"></i></button>
                                    @if (Auth::user()->can('create-client-party'))
                                        <button type="button" name="rejectBtn" id="rejectBtn" data-toggle="modal"
                                            data-target="#cancelModal" class="btn btn-danger float-right m-2 btn-sm"
                                            data-toggle="tooltip" data-placement="top" title="Reject For Amendment"
                                            data-bs-original-title="Update">
                                            <i class="fa fa-edit" aria-hidden="true"></i> Reject </button>
                                    @endif
                                    {{-- <button type="button" name="updateBtn" id="save-step1"
                                        class="btn btn-success btn-sm float-right m-2" data-toggle="tooltip"
                                        data-placement="top" title="" data-bs-original-title="Update"><i
                                            class="fa fa-edit" aria-hidden="true"></i> Update for later</button> --}}
                                </div>
                                {{-- Office Details Content Section --}}
                                <div id="office-details" class="content active dstepper-block" role="tabpanel"
                                    aria-labelledby="office-details-trigger">
                                    <div class="row">
                                        <div class="col-md-12 col-lg-12 col-sm-12">
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
                                                            id="officeCountry" required
                                                            @if ($clientParty->Status == 'Submitted') disabled @endif>
                                                            <option value="" disabled selected></option>
                                                            @forelse ($offices as $office)
                                                                <option code="{{ $office->Code }}"
                                                                    value="{{ $office->Name }}" id="{{ $office->ID }}"
                                                                    @if (
                                                                        $office->ID ==
                                                                            (count($clientParty->getSCMOfficeID($clientParty->OfficeID ?? Auth::user()->office->SCMOfficeID)) < 1
                                                                                ? $clientParty->OfficeID ?? Auth::user()->office->SCMOfficeID
                                                                                : $clientParty->getSCMOfficeID($clientParty->OfficeID ?? Auth::user()->office->SCMOfficeID)[0]->SCMOfficeID)) selected @endif>
                                                                    {{ $office->Name }}</option>
                                                            @empty
                                                            @endforelse
                                                        </select>
                                                        @if (!Auth::user()->can('create-client-for-any-office') || $clientParty->Status == 'Submitted')
                                                            <input type="hidden" name="officeCountry"
                                                                code="{{ $clientParty->OfficeCode }}"
                                                                value="{{ old('officeCountry', $clientParty->Office) }}">
                                                            <input type="hidden" name="officeCode"
                                                                value='{{ $clientParty->OfficeCode }}'>
                                                        @endif
                                                        <input type="hidden" name="officeCountryID" id="officeCountryID"
                                                            value="{{ old('officeCountryID', $clientParty->OfficeID) }}">
                                                    </div>
                                                </div>
                                            </fieldset>

                                            {{-- ENHANCED FILE UPLOAD SECTION --}}
                                            @if ($type === 'accounting')
                                                <fieldset class="border p-2 card shadow-lg mb-0 pt-0 pb-0">
                                                    <legend>File Upload (PDF Only)
                                                        @if ($clientParty->Status !== 'Created' && $clientParty->Status !== 'Submitted')
                                                            <span class="text-danger">*</span>
                                                        @endif
                                                    </legend>

                                                    {{-- File Upload Controls (only show if status allows editing) --}}
                                                    @if ($clientParty->Status !== 'Created' && $clientParty->Status !== 'Submitted' && count($clientParty->attachments) < 3)
                                                        <div class="file-upload-container">
                                                            <label for="attachmentFile">Attachment (PDF files only) <i
                                                                    class="fa fa-paperclip"></i>
                                                                @if ($clientParty->Status !== 'Created' && $clientParty->Status !== 'Submitted')
                                                                    <span class="text-danger">Required for accounting
                                                                        mode</span>
                                                                @endif
                                                                <button type="button"
                                                                    class="btn btn-sm btn-outline-warning float-right w-20 addAttachment"
                                                                    id="addFile">
                                                                    <i class="fa fa-plus"></i> Add File
                                                                </button>
                                                            </label>

                                                            <div class="input-group hdtuto control-group lst increment"
                                                                id="initial-attachment">
                                                                <input id="attachments" type="file"
                                                                    name="attachmentNames[]"
                                                                    class="myfrm form-control mr-1 form-control-sm"
                                                                    accept="application/pdf,.pdf"
                                                                    title="Only PDF files are accepted"
                                                                    @if (
                                                                        $clientParty->Status !== 'Created' &&
                                                                            $clientParty->Status !== 'Submitted' &&
                                                                            $clientParty->Status !== 'PendingITVerification') required @endif>
                                                                <input type="text" name="attachmentDescription[]"
                                                                    class="myfrm form-control form-control-sm"
                                                                    placeholder="Description"
                                                                    @if (
                                                                        $clientParty->Status !== 'Created' &&
                                                                            $clientParty->Status !== 'Submitted' &&
                                                                            $clientParty->Status !== 'PendingITVerification') required @endif>
                                                            </div>
                                                            <small class="text-muted">📄 Only PDF files are accepted (Max
                                                                10MB)
                                                                @if ($clientParty->Status !== 'Created' && $clientParty->Status !== 'Submitted')
                                                                    <span class="text-danger font-weight-bold">- File
                                                                        upload is mandatory for accounting mode</span>
                                                                @endif
                                                            </small>
                                                            <div id="file-upload-error" class="text-danger mt-2"
                                                                style="display: none;">
                                                                <i class="fa fa-exclamation-triangle"></i> At least one
                                                                file is required for accounting mode.
                                                            </div>
                                                            <div id="attachClone"></div>
                                                        </div>
                                                    @endif

                                                    {{-- Display Existing Files --}}
                                                    @if (count($clientParty->attachments) > 0)
                                                        <div class="existing-files-section mt-3">
                                                            <h6 class="font-weight-bold text-muted mb-2">
                                                                <i class="fa fa-paperclip text-primary"></i>
                                                                Uploaded Files ({{ count($clientParty->attachments) }})
                                                            </h6>

                                                            <div class="uploaded-files-container">
                                                                @foreach ($clientParty->attachments as $index => $attachment)
                                                                    <div class="file-item mb-2 p-2 border rounded"
                                                                        data-file-id="{{ $attachment->ID }}"
                                                                        data-file-type="pdf">
                                                                        <div class="row align-items-center">
                                                                            <div class="col-md-8">
                                                                                <div class="file-info">
                                                                                    <span
                                                                                        class="file-icon text-danger mr-2">
                                                                                        <i
                                                                                            class="fa fa-file-pdf-o fa-lg"></i>
                                                                                    </span>
                                                                                    <div
                                                                                        class="file-details d-inline-block">
                                                                                        <strong
                                                                                            class="file-name">{{ $attachment->FileName }}</strong>
                                                                                        @if ($attachment->Title)
                                                                                            <br>
                                                                                            <small
                                                                                                class="text-muted file-description">
                                                                                                <i
                                                                                                    class="fa fa-comment-o"></i>
                                                                                                {{ $attachment->Title }}
                                                                                            </small>
                                                                                        @endif
                                                                                        <div class="file-meta">
                                                                                            <small class="text-muted">
                                                                                                <i
                                                                                                    class="fa fa-calendar"></i>
                                                                                                Uploaded:
                                                                                                {{ \Carbon\Carbon::parse($attachment->CreatedOn ?? now())->format('M d, Y') }}
                                                                                            </small>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-md-4 text-right">
                                                                                <div class="file-actions">
                                                                                    {{-- Preview/Download Button --}}
                                                                                    <a href="{{ route('client-party.preview', [$attachment->ID]) }}"
                                                                                        class="btn btn-sm btn-outline-primary mr-1"
                                                                                        target="_blank"
                                                                                        data-toggle="tooltip"
                                                                                        title="Preview/Download PDF">
                                                                                        <i class="fa fa-eye"></i> View
                                                                                    </a>

                                                                                    {{-- Delete Button (only show if status allows editing) --}}
                                                                                    @if ($clientParty->Status !== 'Created' && $clientParty->Status !== 'Submitted')
                                                                                        <button type="button"
                                                                                            class="btn btn-sm btn-outline-danger deleteFile"
                                                                                            data-file-id="{{ $attachment->ID }}"
                                                                                            data-toggle="tooltip"
                                                                                            title="Delete File">
                                                                                            <i class="fa fa-trash"></i>
                                                                                        </button>
                                                                                    @endif
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                @endforeach
                                                            </div>

                                                            {{-- File Statistics --}}
                                                            <div class="file-statistics mt-2 p-2 bg-light rounded">
                                                                <small class="text-muted">
                                                                    <i class="fa fa-info-circle"></i>
                                                                    <strong>{{ count($clientParty->attachments) }}</strong>
                                                                    file(s) uploaded
                                                                    @if (count($clientParty->attachments) >= 3)
                                                                        <span class="text-warning">• Maximum limit
                                                                            reached</span>
                                                                    @else
                                                                        <span class="text-success">•
                                                                            {{ 3 - count($clientParty->attachments) }} more
                                                                            file(s) can be added</span>
                                                                    @endif
                                                                </small>
                                                            </div>
                                                        </div>
                                                    @else
                                                        {{-- No Files Uploaded Message --}}
                                                        <div
                                                            class="no-files-message text-center p-4 bg-light rounded mt-3">
                                                            <i class="fa fa-file-o fa-3x text-muted mb-2"></i>
                                                            <p class="text-muted mb-0">
                                                                @if ($clientParty->Status === 'Created' || $clientParty->Status === 'Submitted')
                                                                    No files were uploaded with this request.
                                                                @else
                                                                    No files uploaded yet. Please upload required PDF
                                                                    documents.
                                                                @endif
                                                            </p>
                                                        </div>
                                                    @endif

                                                    {{-- Clone Template for Dynamic File Addition --}}
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
                                            @endif
                                        </div>
                                        <div class="col-md-8 col-lg-12 col-sm-12">

                                            @if ($type === 'accounting')
                                                <fieldset class="border p-2 card shadow-lg mb-0 pt-0">
                                                    <legend class="w-auto p-0 m-0">Debtor/Vendor</legend>
                                                    <div class="row">
                                                        <div class="col-md-6 col-sm-12 col-lg-6">
                                                            <div class="form-group row mb-1">
                                                                <div class="col-md-12">
                                                                    <label for="debtorType"
                                                                        class="col-form-label col-form-label-sm">Debtor
                                                                        Type</label>
                                                                    <input type="text"
                                                                        class="form-control form-control-sm"
                                                                        id="debtorType"
                                                                        value="{{ $clientParty->DebtorType ?? 'No Debtor Selected' }}"
                                                                        readonly>
                                                                    <input type="hidden" name="debtorType"
                                                                        value="{{ $clientParty->DebtorType ?? '' }}">
                                                                </div>
                                                            </div>

                                                            <div class="row mb-0 pb-0">
                                                                <div class="form-group col-md-6 mb-0 pb-0">
                                                                    <label for="billingCurrency"
                                                                        class="col-form-label col-form-label-sm">Billing
                                                                        Currency <span class="text-danger">*</span></label>
                                                                    <input type="hidden"
                                                                        class="form-control form-control-sm"
                                                                        value="{{ $clientParty->BillingCurrencyCode ?? '' }}"
                                                                        name="billingCurrencyCode"
                                                                        id="billingCurrencyCode">
                                                                    <select class="form-control form-control-sm"
                                                                        name="billingCurrency" id="billingCurrency"
                                                                        disabled>
                                                                        <option value="" disabled>Select Currency
                                                                        </option>
                                                                        @forelse ($currencyList as $item)
                                                                            <option value="{{ $item->CurrencyID }}"
                                                                                id="{{ $item->CurrencyCode }}"
                                                                                @if ($clientParty->BillingCurrencyId == $item->CurrencyID) selected @endif>
                                                                                {{ $item->CurrencyCode }}
                                                                            </option>
                                                                        @empty
                                                                            <option value="" disabled selected>No
                                                                                currencies available</option>
                                                                        @endforelse
                                                                    </select>
                                                                </div>

                                                                <div class="form-group col-md-6 mb-0 pb-0">
                                                                    <label for="creditDays"
                                                                        class="col-form-label col-form-label-sm">Credit
                                                                        Days<span class="text-danger">*</span></label>
                                                                    <input type="text"
                                                                        class="form-control form-control-sm number_input"
                                                                        value="{{ $clientParty->CreditDays ?? '' }}"
                                                                        name="creditDays" id="creditDays" readonly>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6 col-sm-12 col-lg-6">
                                                            <div class="form-group row mb-1">
                                                                <div class="col-md-12">
                                                                    <label for="vendorType"
                                                                        class="col-form-label col-form-label-sm">Vendor
                                                                        Type</label>
                                                                    <input type="text"
                                                                        class="form-control form-control-sm"
                                                                        id="vendorType"
                                                                        value="{{ $clientParty->VendorType ?? 'No Vendor Selected' }}"
                                                                        readonly>
                                                                    <input type="hidden" name="vendorType"
                                                                        value="{{ $clientParty->VendorType ?? '' }}">
                                                                </div>
                                                            </div>

                                                            <div class="form-group">
                                                                <div class="row">
                                                                    <div class="col-sm-6 col-md-6 col-lg-6">
                                                                        <label for="maxCreditLimit"
                                                                            class="col-form-label col-form-label-sm">Credit
                                                                            Limit<span class="text-danger">*</span></label>
                                                                        <input type="text"
                                                                            class="form-control form-control-sm number_input"
                                                                            value="{{ $clientParty->CreditLimit ?? '' }}"
                                                                            name="maxCreditLimit" id="maxCreditLimit"
                                                                            readonly>
                                                                    </div>
                                                                    <div class="col-sm-6 col-md-6 col-lg-6">
                                                                        <label for="baseCurrency"
                                                                            class="col-form-label col-form-label-sm">Base
                                                                            Currency</label>
                                                                        <input type="text"
                                                                            class="form-control form-control-sm"
                                                                            name="baseCurrency" id="baseCurrency"
                                                                            value="{{ $clientParty->BaseCurrencyCode ?? '' }}"
                                                                            readonly>
                                                                        <input type="hidden"
                                                                            class="form-control form-control-sm"
                                                                            name="baseCurrencyCode" id="baseCurrencyCode"
                                                                            value="{{ $clientParty->BaseCurrencyCode ?? '' }}">
                                                                        <input type="hidden"
                                                                            class="form-control form-control-sm"
                                                                            name="baseCurrencyName" id="baseCurrencyName"
                                                                            value="{{ $clientParty->BaseCurrencyName ?? '' }}">
                                                                        <input type="hidden"
                                                                            class="form-control form-control-sm"
                                                                            name="baseCurrencyID" id="baseCurrencyID"
                                                                            value="{{ $clientParty->BaseCurrencyId ?? '' }}">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="form-group row mb-0 mt-0 p-0">
                                                        <div class="col col-sm-6 col-lg-6 col-md-6 m-0">
                                                            <div class="row">
                                                                <label for="licence"
                                                                    class="col-md-3 col-form-label col-form-label-sm pr-0">Licence
                                                                    Type<span class="text-danger">*</span></label>
                                                                <div class="col-md-9">
                                                                    <select class="form-control form-control-sm"
                                                                        name="licenceTypeID" id="licenceTypeID" disabled>
                                                                        <option value="" disabled>Select Licence Type
                                                                        </option>
                                                                        @forelse ($licenceTypes as $type)
                                                                            <option value="{{ $type->LicenseID }}"
                                                                                id="{{ $type->LicenseCode }}"
                                                                                @if ($clientParty->LicenceTypeID == $type->LicenseID) selected @endif>
                                                                                {{ $type->LicenseName }}
                                                                            </option>
                                                                        @empty
                                                                            <option value="" disabled>No licence
                                                                                types available</option>
                                                                        @endforelse
                                                                    </select>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col col-sm-6 col-lg-6 col-md-6">
                                                            <input type="hidden" class="form-control form-control-sm"
                                                                value="{{ $clientParty->LicenceTypeCode ?? '' }}"
                                                                name="licenceTypeCode" id="licenceTypeCode">
                                                            <input type="text" class="form-control form-control-sm"
                                                                value="{{ $clientParty->LicenceTypeValue ?? '' }}"
                                                                name="licenceTypeValue" id="licenceTypeValue" readonly>
                                                        </div>
                                                    </div>
                                                </fieldset>
                                            @endif

                                        </div>
                                    </div>

                                    {{-- Navigation Buttons Section in Office Details --}}
                                    <button class="btn btn-primary m-2 btn-sm" type="button" name="previous"
                                        onclick="stepper.previous()">
                                        <i class="fa fa-step-backward" aria-hidden="true"></i> Previous
                                    </button>

                                    {{-- Check user permissions and entity type for button display --}}
                                    @if (Auth::user()->can('approve-entity-request'))
                                        {{-- User can approve entity --}}
                                        @if ($type === 'operational')
                                            {{-- For operational type, only show Next button to go to Admin Details --}}
                                            <button class="btn btn-primary m-2 btn-sm float-right" type="button"
                                                name="next" onclick="stepper.next()">
                                                Next <i class="fa fa-step-forward" aria-hidden="true"></i>
                                            </button>
                                        @else
                                            {{-- For accounting type, show approve/reject buttons here --}}
                                            @if ($approve)
                                                <button type="button" name="approveBtn" id="approveBtn"
                                                    class="btn btn-outline-success float-right m-2 btn-sm"
                                                    data-toggle="tooltip" data-placement="top"
                                                    title="Approve For Creation">
                                                    <i class="fa fa-check" aria-hidden="true"></i> Approve
                                                </button>
                                                <button type="button" name="rejectBtn" id="rejectBtn"
                                                    data-toggle="modal" data-target="#cancelModal"
                                                    class="btn btn-danger float-right m-2 btn-sm" data-toggle="tooltip"
                                                    data-placement="top" title="Reject For Amendment">
                                                    <i class="fa fa-edit" aria-hidden="true"></i> Reject
                                                </button>
                                            @else
                                                <button type="submit" name="createBtn" value="true"
                                                    class="btn btn-outline-success float-right m-2 btn-sm"
                                                    data-toggle="tooltip" data-placement="top"
                                                    title="Approve Entity For Creation">
                                                    <i class="fa fa-check" aria-hidden="true"></i> Create
                                                </button>
                                                <button type="button" name="rejectBtn" id="rejectBtn"
                                                    data-toggle="modal" data-target="#cancelModal"
                                                    class="btn btn-danger float-right m-2 btn-sm" data-toggle="tooltip"
                                                    data-placement="top" title="Reject For Amendment">
                                                    <i class="fa fa-edit" aria-hidden="true"></i> Reject
                                                </button>
                                            @endif
                                        @endif
                                    @elseif (Auth::user()->can('create-client-party'))
                                        {{-- User can create client party - always show Next button --}}
                                        <button class="btn btn-primary m-2 btn-sm float-right" type="button"
                                            name="next" onclick="stepper.next()">
                                            Next <i class="fa fa-step-forward" aria-hidden="true"></i>
                                        </button>
                                    @else
                                        {{-- Regular user - show Submit/Update buttons --}}
                                        <button type="submit" name="submitBtn" value="true"
                                            class="btn btn-outline-success btn-sm float-right m-2" data-toggle="tooltip"
                                            data-placement="top" title="Will be processed by admin">
                                            <i class="fa fa-thumbs-up" aria-hidden="true"></i> Submit
                                        </button>
                                        <button type="submit" name="updateBtn" id="updateBtn"
                                            class="btn btn-success btn-sm float-right m-2" data-toggle="tooltip"
                                            data-placement="top" title="Update">
                                            <i class="fa fa-edit" aria-hidden="true"></i> Update for later
                                        </button>
                                    @endif
                                </div>
                        </div>


                        {{-- Admin Details --}}
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
                                                    <td>Other</td>
                                                    <td><input type="checkbox" name="otherCheck" id="otherCheck"
                                                            @if ($clientParty->OtherPartyName == '') checked @endif disabled
                                                            readonly></td>
                                                    <td>
                                                        <input type="text" name="otherInput" id="otherInput"
                                                            value="{{ old('otherID', $clientParty->OtherPartyName) }}"
                                                            class="form-control form-control-sm" readonly disabled>
                                                        <input type="hidden" name="otherID" id="otherID"
                                                            value="{{ old('otherID', $clientParty->OtherPartyID) }}">
                                                        <input type="hidden" name="otherCode" id="otherCode"
                                                            value="{{ old('otherCode', $clientParty->OtherPartyCode) }}">
                                                    </td>
                                                    <td id="other_Input" onclick='copyText(this)' data-toggle="tooltip"
                                                        title="Click to copy">
                                                        {{ $clientParty->OtherPartyCode ?? '' }}
                                                    </td>
                                                </tr>

                                                {{-- Only show Debtor and Vendor rows if type is NOT operational --}}
                                                @if ($type !== 'operational')
                                                    <tr>
                                                        <td>Debtor</td>
                                                        <td><input type="checkbox" name="debtorCheck" id="debtorCheck"
                                                                @if ($clientParty->RequestforDebtor) checked @endif disabled
                                                                readonly></td>
                                                        <td>
                                                            <input type="text" name="debtor" id="debtor"
                                                                value="{{ old('debtor', $clientParty->Debtor) }}"
                                                                class="form-control form-control-sm" readonly disabled>
                                                            <input type="hidden" name="debtorID"
                                                                value="{{ old('debtorID', $clientParty->DebtorID) }}"
                                                                id="debtorID">
                                                            <input type="hidden" name="debtorCode"
                                                                value="{{ old('debtorCode', $clientParty->DebtorCode) }}"
                                                                id="debtorCode">
                                                        </td>
                                                        <td id="debtor_code" onclick='copyText(this)'
                                                            data-toggle="tooltip" title="Click to copy">
                                                            {{ $clientParty->DebtorCode ?? '' }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Vendor</td>
                                                        <td><input type="checkbox" name="vendorCheck" id="vendorCheck"
                                                                @if ($clientParty->RequestforVendor) checked @endif disabled
                                                                readonly></td>
                                                        <td>
                                                            <input type="text" name="vendor" id="vendor"
                                                                value="{{ old('vendor', $clientParty->Vendor) }}"
                                                                class="form-control form-control-sm" readonly disabled>
                                                            <input type="hidden" name="vendorID"
                                                                value="{{ old('vendorID', $clientParty->VendorID) }}"
                                                                id="vendorID">
                                                            <input type="hidden" name="vendorCode"
                                                                value="{{ old('vendorCode', $clientParty->VendorCode) }}"
                                                                id="vendorCode">
                                                        </td>
                                                        <td id="vendor_code" onclick="copyText(this)"
                                                            data-toggle="tooltip" title="Click to Copy">
                                                            {{ $clientParty->VendorCode ?? '' }}</td>
                                                    </tr>
                                                @endif
                                            </table>
                                        </fieldset>

                                        <button class="btn btn-primary m-2 btn-sm" type="button" name="previous"
                                            onclick="stepper.previous()">
                                            <i class="fa fa-step-backward" aria-hidden="true"></i> Previous
                                        </button>

                                        {{-- Show approval buttons based on entity type and user permissions --}}
                                        @if (Auth::user()->can('approve-entity-request') && $type === 'operational')
                                            {{-- For operational type with approve-entity permission, show buttons here --}}
                                            @if ($approve)
                                                <button type="button" name="approveBtn" id="approveBtn"
                                                    class="btn btn-outline-success float-right m-2 btn-sm"
                                                    data-toggle="tooltip" data-placement="top"
                                                    title="Approve For Creation">
                                                    <i class="fa fa-check" aria-hidden="true"></i> Approve
                                                </button>
                                                <button type="button" name="rejectBtn" id="rejectBtn"
                                                    data-toggle="modal" data-target="#cancelModal"
                                                    class="btn btn-danger float-right m-2 btn-sm" data-toggle="tooltip"
                                                    data-placement="top" title="Reject For Amendment">
                                                    <i class="fa fa-edit" aria-hidden="true"></i> Reject
                                                </button>
                                            @else
                                                <button type="submit" name="createBtn" value="true"
                                                    class="btn btn-outline-success float-right m-2 btn-sm"
                                                    data-toggle="tooltip" data-placement="top"
                                                    title="Approve Entity For Creation">
                                                    <i class="fa fa-check" aria-hidden="true"></i> Create
                                                </button>
                                                <button type="button" name="rejectBtn" id="rejectBtn"
                                                    data-toggle="modal" data-target="#cancelModal"
                                                    class="btn btn-danger float-right m-2 btn-sm" data-toggle="tooltip"
                                                    data-placement="top" title="Reject For Amendment">
                                                    <i class="fa fa-edit" aria-hidden="true"></i> Reject
                                                </button>
                                            @endif
                                        @elseif (!Auth::user()->can('approve-entity-request'))
                                            {{-- For users without approve-entity permission (existing logic) --}}
                                            @if ($approve)
                                                <button type="button" name="approveBtn" id="approveBtn"
                                                    class="btn btn-outline-success float-right m-2 btn-sm"
                                                    data-toggle="tooltip" data-placement="top"
                                                    title="Approve For Creation">
                                                    <i class="fa fa-check" aria-hidden="true"></i> Approve
                                                </button>
                                                <button type="button" name="rejectBtn" id="rejectBtn"
                                                    data-toggle="modal" data-target="#cancelModal"
                                                    class="btn btn-danger float-right m-2 btn-sm" data-toggle="tooltip"
                                                    data-placement="top" title="Reject For Amendment">
                                                    <i class="fa fa-edit" aria-hidden="true"></i> Reject
                                                </button>
                                            @else
                                                <button type="submit" name="createBtn" value="true"
                                                    class="btn btn-outline-success float-right m-2 btn-sm"
                                                    data-toggle="tooltip" data-placement="top"
                                                    title="Approve Entity For Creation">
                                                    <i class="fa fa-check" aria-hidden="true"></i> Create
                                                </button>
                                                <button type="button" name="rejectBtn" id="rejectBtn"
                                                    data-toggle="modal" data-target="#cancelModal"
                                                    class="btn btn-danger float-right m-2 btn-sm" data-toggle="tooltip"
                                                    data-placement="top" title="Reject For Amendment">
                                                    <i class="fa fa-edit" aria-hidden="true"></i> Reject
                                                </button>
                                            @endif
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endif
                        </form>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="card shadow-sm p-3 mb-4 bg-white rounded">
                        <div class="text-center">
                            <div class="mb-2">
                                <span class="font-weight-bold text-muted">Request ID:</span>
                                <span class="badge badge-primary copy-text" onclick="copyText(this)"
                                    data-toggle="tooltip" data-placement="top" title="Click to copy">
                                    {{ $clientParty->Code }}
                                </span>

                                <span class="font-weight-bold text-muted">Requested By:</span>
                                <span class="font-weight-bold">{{ $clientParty->CreatedBy }}</span>

                            </div>

                            <div class="mb-2">
                                <span class="font-weight-bold text-muted">Requested On:</span>
                                <span class="font-weight-bold">{{ $clientParty->CreatedOn }}</span>
                            </div>
                            <div class="mb-2">
                                <span class="font-weight-bold text-muted">Status:</span>
                                @if ($clientParty->Status == 'Submitted')
                                    <span
                                        class="badge badge-{{ $clientParty->paintClientPartyStatus($clientParty->Status) }} badge-pill">
                                        Submitted
                                    </span>
                                @else
                                    <span
                                        class="badge badge-{{ $clientParty->paintClientPartyStatus($clientParty->Status) }} badge-pill">
                                        {{ $clientParty->Status }}
                                    </span>
                                @endif
                            </div>
                            @if ($clientParty->Status == 'Rejected' || $clientParty->Status == 'Review')
                                <div class="mb-2">
                                    <span class="font-weight-bold text-muted">Rejected By:</span>
                                    <span class="font-weight-bold">{{ $clientParty->RejectedBy }}</span>
                                </div>
                                <div class="mb-2">
                                    <span class="font-weight-bold text-muted">Rejected On:</span>
                                    <span class="font-weight-bold">
                                        {{ date_format(date_create($clientParty->RejectedOn), 'Y-m-d H:i:s') }}
                                    </span>
                                </div>
                                <div class="mb-2">
                                    <span class="font-weight-bold text-muted">Reason:</span>
                                    <span class="text-sm text-danger">{{ $clientParty->RejectedReason }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Cancel Modal --}}
            <div class="modal fade" id="cancelModal" tabindex="-1" role="dialog" aria-labelledby="cancelModalLabel"
                aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="cancelModalLabel">Reject Entity Request</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <form id="cancelForm">
                            <div class="modal-body">
                                <div class="form-group">
                                    <label for="level">Rejection Level</label>
                                    <select class="form-control" id="level" name="level" required>
                                        <option value="">Select Level</option>
                                        <option value="Rejected">Reject</option>
                                        <option value="Review">Review</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="reason">Reason for Rejection</label>
                                    <textarea class="form-control" id="reason" name="reason" rows="3" required
                                        placeholder="Please provide a detailed reason for rejection..."></textarea>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" id="close-modal">Cancel</button>
                                <button type="button" class="btn btn-danger" id="submit-entity">Submit
                                    Rejection</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        @stop

        @section('css')
            <!-- ============================================
                                                                                                                                     EXTERNAL STYLESHEETS
                                                                                                                                ============================================ -->
            <!-- Bootstrap Stepper -->
            <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bs-stepper/dist/css/bs-stepper.min.css">

            <!-- SweetAlert2 -->
            <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

            <!-- jQuery UI -->
            <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">

            <style>
                /* ============================================
                                                                                                                                       CSS VARIABLES (Custom Properties)
                                                                                                                                    ============================================ */
                :root {
                    --primary-color: #007bff;
                    --primary-hover: #0056b3;
                    --success-color: #28a745;
                    --danger-color: #dc3545;
                    --warning-color: #ffc107;
                    --info-color: #17a2b8;
                    --dark-color: #343a40;
                    --muted-color: #6c757d;
                    --light-gray: #f8f9fa;
                    --border-color: #ddd;
                    --legend-color: #E91809;
                    --shadow-light: 0 2px 4px rgba(0, 0, 0, 0.1);
                    --shadow-medium: 0 4px 12px rgba(0, 0, 0, 0.1);
                    --transition-base: all 0.3s ease;
                    --border-radius-sm: 4px;
                    --border-radius-md: 8px;
                    --border-radius-lg: 12px;
                }

                /* ============================================
                                                                                                                                       CARD COMPONENTS
                                                                                                                                    ============================================ */
                .card {
                    border: none;
                    border-radius: var(--border-radius-md);
                    transiti]on: var(--transition-base);
                    box-shadow: var(--shadow-light);
                }

                .card:hover {
                    box-shadow: var(--shadow-medium);
                    transform: translateY(-2px);
                }

                .card-header {
                    background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-hover) 100%);
                    color: white;
                    border-radius: var(--border-radius-md) var(--border-radius-md) 0 0 !important;
                    font-weight: 600;
                }

                /* ============================================
                                                                                                                                       ENHANCED FILE DISPLAY STYLES
                                                                                                                                    ============================================ */
                .file-item {
                    background: #f8f9fa;
                    transition: all 0.3s ease;
                    border: 1px solid #e9ecef !important;
                    border-radius: 8px;
                }

                .file-item:hover {
                    background: #e9ecef;
                    border-color: #007bff !important;
                    transform: translateY(-1px);
                    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
                }

                .file-icon .fa-file-pdf-o {
                    color: #dc3545;
                    font-size: 1.2rem;
                }

                .file-name {
                    color: #495057;
                    font-size: 0.9rem;
                    font-weight: 500;
                    cursor: pointer;
                }

                .file-name:hover {
                    color: #007bff;
                    text-decoration: underline;
                }

                .file-description {
                    font-size: 0.8rem;
                    color: #6c757d;
                    margin-top: 2px;
                }

                .file-meta {
                    font-size: 0.75rem;
                    color: #6c757d;
                    margin-top: 0.25rem;
                }

                .file-actions .btn {
                    font-size: 0.8rem;
                    padding: 0.25rem 0.5rem;
                    margin: 0 2px;
                }

                .file-statistics {
                    border-left: 4px solid #007bff;
                    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
                }

                .no-files-message {
                    border: 2px dashed #dee2e6;
                    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
                }

                .existing-files-section h6 {
                    border-bottom: 2px solid #dee2e6;
                    padding-bottom: 0.5rem;
                    color: #495057;
                }

                .uploaded-files-container {
                    max-height: 400px;
                    overflow-y: auto;
                }

                /* File validation states */
                .file-item.invalid {
                    border-color: #dc3545 !important;
                    background-color: #f8d7da;
                }

                .file-item.valid {
                    border-color: #28a745 !important;
                    background-color: #d4edda;
                }

                /* Animation for file deletion */
                .file-item.deleting {
                    opacity: 0.5;
                    transform: scale(0.95);
                    transition: all 0.3s ease;
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

                /* PDF file specific styling */
                .file-item[data-file-type="pdf"] {
                    border-left: 4px solid #dc3545;
                }

                /* Enhanced buttons for file actions */
                .file-actions .btn-outline-primary:hover {
                    background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
                    border-color: #0056b3;
                }

                .file-actions .btn-outline-danger:hover {
                    background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
                    border-color: #c82333;
                }

                /* ============================================
                                                                                                                                       BADGE SYSTEM
                                                                                                                                    ============================================ */
                .badge {
                    font-size: 0.9rem;
                    padding: 0.5em 1em;
                    border-radius: var(--border-radius-lg);
                    vertical-align: middle;
                    font-weight: 500;
                    transition: var(--transition-base);
                }

                .badge-primary {
                    background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-hover) 100%);
                    color: white;
                    cursor: pointer;
                }

                .badge-primary:hover {
                    background: linear-gradient(135deg, var(--primary-hover) 0%, #004085 100%);
                    transform: scale(1.05);
                }

                .badge-success {
                    background: linear-gradient(135deg, var(--success-color) 0%, #1e7e34 100%);
                    color: white;
                }

                .badge-danger {
                    background: linear-gradient(135deg, var(--danger-color) 0%, #c82333 100%);
                    color: white;
                }

                .badge-warning {
                    background: linear-gradient(135deg, var(--warning-color) 0%, #e0a800 100%);
                    color: #212529;
                }

                .badge-info {
                    background: linear-gradient(135deg, var(--info-color) 0%, #138496 100%);
                    color: white;
                }

                /* Animated badge for copy functionality */
                .copy-text {
                    position: relative;
                    overflow: hidden;
                }

                .copy-text:hover::after {
                    content: "Click to copy";
                    position: absolute;
                    top: -30px;
                    left: 50%;
                    transform: translateX(-50%);
                    background: var(--dark-color);
                    color: white;
                    padding: 4px 8px;
                    border-radius: var(--border-radius-sm);
                    font-size: 0.75rem;
                    white-space: nowrap;
                    opacity: 0.9;
                    animation: fadeInUp 0.3s ease;
                }

                @keyframes fadeInUp {
                    from {
                        opacity: 0;
                        transform: translateX(-50%) translateY(10px);
                    }

                    to {
                        opacity: 0.9;
                        transform: translateX(-50%) translateY(0);
                    }
                }

                /* ============================================
                                                                                                                                       TYPOGRAPHY
                                                                                                                                    ============================================ */
                .text-muted {
                    font-size: 0.95rem;
                    color: var(--muted-color) !important;
                }

                .text-sm {
                    font-size: 0.85rem;
                }

                .text-danger {
                    color: var(--danger-color) !important;
                }

                label {
                    font-weight: 600;
                    color: var(--dark-color);
                    margin-bottom: 0.5rem;
                }

                /* ============================================
                                                                                                                                       FIELDSET & LEGEND STYLING
                                                                                                                                    ============================================ */
                fieldset {
                    border: 2px solid var(--border-color);
                    border-radius: var(--border-radius-md);
                    padding: 1rem;
                    margin-bottom: 1rem;
                    background: linear-gradient(145deg, #ffffff 0%, #f8f9fa 100%);
                    box-shadow: var(--shadow-light);
                    transition: var(--transition-base);
                }

                fieldset:hover {
                    border-color: var(--primary-color);
                    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
                }

                fieldset.scheduler-border {
                    border: solid 2px var(--border-color);
                    padding: 0 1rem 1rem 1rem;
                    border-bottom: none;
                }

                legend {
                    font-size: 1.1rem;
                    font-weight: 700;
                    color: var(--legend-color);
                    padding: 0 0.5rem;
                    margin-bottom: 0;
                    background: white;
                    border-radius: var(--border-radius-sm);
                    box-shadow: var(--shadow-light);
                }

                legend.scheduler-border {
                    width: auto !important;
                    border: none;
                    font-size: 1rem;
                }

                /* ============================================
                                                                                                                                       FORM ELEMENTS
                                                                                                                                    ============================================ */
                .form-control:focus {
                    border-color: var(--primary-color);
                    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
                }

                .form-control.is-invalid {
                    border-color: var(--danger-color);
                    animation: shake 0.5s ease-in-out;
                }

                @keyframes shake {

                    0%,
                    100% {
                        transform: translateX(0);
                    }

                    25% {
                        transform: translateX(-5px);
                    }

                    75% {
                        transform: translateX(5px);
                    }
                }

                /* ============================================
                                                                                                                                       DISABLED STATES
                                                                                                                                    ============================================ */
                .disabled {
                    opacity: 0.6;
                    cursor: not-allowed;
                    filter: grayscale(20%);
                    transition: var(--transition-base);
                }

                .admin-section-disabled {
                    position: relative;
                }

                .admin-section-disabled::before {
                    content: '';
                    position: absolute;
                    top: 0;
                    left: 0;
                    right: 0;
                    bottom: 0;
                    background: rgba(248, 249, 250, 0.8);
                    z-index: 1;
                    border-radius: var(--border-radius-md);
                }

                /* ============================================
                                                                                                                                       TOOLTIP ENHANCEMENTS
                                                                                                                                    ============================================ */
                .tooltip-inner {
                    background: linear-gradient(135deg, var(--dark-color) 0%, #495057 100%);
                    color: white;
                    border-radius: var(--border-radius-sm);
                    font-size: 0.875rem;
                    padding: 0.5rem 0.75rem;
                    box-shadow: var(--shadow-medium);
                }

                .tooltip .arrow::before {
                    border-top-color: var(--dark-color);
                }

                /* ============================================
                                                                                                                                       LOADING & PRELOADER
                                                                                                                                    ============================================ */
                #loader {
                    display: none;
                    position: fixed;
                    top: 0;
                    left: 0;
                    right: 0;
                    bottom: 0;
                    width: 100%;
                    background: rgba(0, 0, 0, 0.8);
                    z-index: 9999;
                    backdrop-filter: blur(2px);
                }

                #loader::after {
                    content: '';
                    position: absolute;
                    top: 50%;
                    left: 50%;
                    transform: translate(-50%, -50%);
                    width: 50px;
                    height: 50px;
                    border: 5px solid var(--light-gray);
                    border-top: 5px solid var(--primary-color);
                    border-radius: 50%;
                    animation: spin 1s linear infinite;
                }

                @keyframes spin {
                    0% {
                        transform: translate(-50%, -50%) rotate(0deg);
                    }

                    100% {
                        transform: translate(-50%, -50%) rotate(360deg);
                    }
                }

                /* Original Preloader Styles */
                .preloader {
                    position: fixed;
                    top: 0;
                    left: 0;
                    z-index: 999999999;
                    width: 100%;
                    height: 100%;
                    background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
                    overflow: hidden;
                }

                .preloader-inner {
                    position: absolute;
                    top: 50%;
                    left: 50%;
                    transform: translate(-50%, -50%);
                }

                .preloader-icon {
                    width: 100px;
                    height: 100px;
                    display: inline-block;
                    padding: 0;
                }

                .preloader-icon span {
                    position: absolute;
                    display: inline-block;
                    width: 100px;
                    height: 100px;
                    border-radius: 50%;
                    background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-hover) 100%);
                    animation: preloader-fx 1.6s linear infinite;
                }

                .preloader-icon span:last-child {
                    animation-delay: -0.8s;
                }

                @keyframes preloader-fx {
                    0% {
                        transform: scale(0);
                        opacity: 0.5;
                    }

                    100% {
                        transform: scale(1);
                        opacity: 0;
                    }
                }

                /* ============================================
                                                                                                                                       BUTTON ENHANCEMENTS
                                                                                                                                    ============================================ */
                .btn {
                    border-radius: var(--border-radius-sm);
                    font-weight: 500;
                    transition: var(--transition-base);
                    border: none;
                    box-shadow: var(--shadow-light);
                }

                .btn:hover {
                    transform: translateY(-1px);
                    box-shadow: var(--shadow-medium);
                }

                .btn:active {
                    transform: translateY(0);
                }

                .btn-primary {
                    background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-hover) 100%);
                }

                .btn-success {
                    background: linear-gradient(135deg, var(--success-color) 0%, #1e7e34 100%);
                }

                .btn-danger {
                    background: linear-gradient(135deg, var(--danger-color) 0%, #c82333 100%);
                }

                .btn-warning {
                    background: linear-gradient(135deg, var(--warning-color) 0%, #e0a800 100%);
                    color: #212529;
                }

                .btn-info {
                    background: linear-gradient(135deg, var(--info-color) 0%, #138496 100%);
                }

                /* ============================================
                     RESPONSIVE DESIGN
                    ============================================ */
                @media (max-width: 768px) {
                    .card {
                        margin-bottom: 1rem;
                    }

                    fieldset {
                        padding: 0.75rem;
                    }

                    .badge {
                        font-size: 0.8rem;
                        padding: 0.4em 0.8em;
                    }

                    .file-item .row {
                        flex-direction: column;
                    }

                    .file-item .col-md-8,
                    .file-item .col-md-4 {
                        flex: 0 0 100%;
                        max-width: 100%;
                    }

                    .file-actions {
                        margin-top: 0.5rem;
                        text-align: left !important;
                    }

                    .file-actions .btn {
                        margin-bottom: 0.25rem;
                    }
                }

                /* ============================================
                     UTILITY CLASSES
                   ============================================ */
                .shadow-soft {
                    box-shadow: var(--shadow-light);
                }

                .shadow-strong {
                    box-shadow: var(--shadow-medium);
                }

                .rounded-soft {
                    border-radius: var(--border-radius-sm);
                }

                .rounded-medium {
                    border-radius: var(--border-radius-md);
                }

                .rounded-large {
                    border-radius: var(--border-radius-lg);
                }

                .gradient-primary {
                    background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-hover) 100%);
                }

                .gradient-success {
                    background: linear-gradient(135deg, var(--success-color) 0%, #1e7e34 100%);
                }

                .gradient-danger {
                    background: linear-gradient(135deg, var(--danger-color) 0%, #c82333 100%);
                }

                /* ============================================
                    PRINT STYLES
                    ============================================ */
                @media print {

                    .btn,
                    .badge,
                    .preloader,
                    #loader {
                        display: none !important;
                    }

                    .card {
                        box-shadow: none;
                        border: 1px solid #ddd;
                    }

                    fieldset {
                        break-inside: avoid;
                    }
                }

                /* Enhanced Read-Only Styling */
                .readonly-field {
                    background-color: #e9ecef !important;
                    cursor: not-allowed !important;
                    opacity: 0.8 !important;
                    border-color: #ced4da !important;
                }

                .readonly-alert {
                    border-left: 4px solid #6c757d;
                    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
                    margin-bottom: 1rem;
                }

                #basic-details fieldset::after,
                #office-details fieldset::after {
                    content: '';
                    position: absolute;
                    top: 0;
                    left: 0;
                    right: 0;
                    bottom: 0;
                    background: rgba(248, 249, 250, 0.3);
                    pointer-events: none;
                    z-index: 5;
                }
            </style>
        @stop

        @section('js')
            <!-- SweetAlert2 JS -->
            <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js"></script>

            <script src="//code.jquery.com/jquery-1.12.4.min.js"></script>
            <script src="https://cdn.jsdelivr.net/npm/bs-stepper/dist/js/bs-stepper.min.js"></script>
            <script src="{{ asset('js/client-party/jquery-number/jquery.number.min.js') }}"></script>
            <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>

            <script>
                window.isShowView = true;
                /* Ajax setup CSRF */
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                // 🛠️ SweetAlert2 Utility Functions
                const SwalUtils = {
                    success: (title, text = '', timer = 2000) => {
                        return Swal.fire({
                            icon: 'success',
                            title: title,
                            text: text,
                            confirmButtonColor: '#28a745',
                            timer: timer,
                            timerProgressBar: true
                        });
                    },
                    error: (title, text = '') => {
                        return Swal.fire({
                            icon: 'error',
                            title: title,
                            text: text,
                            confirmButtonColor: '#dc3545'
                        });
                    },
                    warning: (title, text = '') => {
                        return Swal.fire({
                            icon: 'warning',
                            title: title,
                            text: text,
                            confirmButtonColor: '#ffc107'
                        });
                    },
                    confirm: (title, text = '', confirmText = 'Yes', cancelText = 'Cancel') => {
                        return Swal.fire({
                            title: title,
                            text: text,
                            icon: 'question',
                            showCancelButton: true,
                            confirmButtonColor: '#28a745',
                            cancelButtonColor: '#6c757d',
                            confirmButtonText: confirmText,
                            cancelButtonText: cancelText
                        });
                    },
                    loading: (title = 'Processing...', text = 'Please wait...') => {
                        return Swal.fire({
                            title: title,
                            text: text,
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });
                    }
                };

                /** Function to copy text */
                function copyText(element) {
                    var $temp = $("<input>");
                    $("body").append($temp);
                    $temp.val($(element).html()).select();
                    document.execCommand("copy");
                    $('.tooltip .tooltip-inner').text('Copied!');
                    $temp.remove();
                }

                /** Get user authorization status */
                window.User = {
                    value: '{{ auth()->user()->can('edit-client-party') }}',
                    edit: '{{ $editForm }}'
                };
                window.approve_client_party = `{{ $approve }}`;
                window.default_bill_curr = `{{ $clientParty->BillingCurrencyId }}`;

                window.requestType = @json($type ?? 'operational');

                //New Code
                $(document).ready(function() {



                    // ====================================================================
                    // COMPREHENSIVE READ-ONLY ENFORCEMENT FOR TABS 1 & 2
                    // ====================================================================

                    function enforceReadOnlyTabs() {
                        console.log('🔒 Enforcing read-only mode on Basic & Office Details tabs...');

                        // Define all selectors for tabs 1 & 2
                        const tab1Selectors = '#basic-details input, #basic-details textarea, #basic-details select';
                        const tab2Selectors =
                            '#office-details input:not([type="button"]):not([type="submit"]):not([name="next"]):not([name="previous"]), #office-details textarea, #office-details select';

                        // Apply read-only properties
                        $(`${tab1Selectors}, ${tab2Selectors}`).each(function() {
                            $(this)
                                .prop('readonly', true)
                                .prop('disabled', true)
                                .addClass('readonly-field')
                                .attr('title', 'This field is read-only and cannot be edited');
                        });

                        // Disable action buttons but keep navigation
                        $('#basic-details button:not([name="next"]):not([name="previous"]), #office-details button:not([name="next"]):not([name="previous"]):not([name="createBtn"]):not([name="submitBtn"]):not([name="approveBtn"]):not([name="rejectBtn"])')
                            .each(function() {
                                $(this).prop('disabled', true).addClass('disabled').hide();
                            });

                        // Hide file operation buttons
                        $('.addAttachment, .deleteFile, .remove-attachment').prop('disabled', true).addClass('disabled')
                            .css('display', 'none');

                        // Add visual indicators
                        if (!$('#basic-details .readonly-alert').length) {
                            $('#basic-details').prepend(
                                '<div class="alert alert-secondary readonly-alert" role="alert">' +
                                '<i class="fa fa-eye"></i> <strong>View Only:</strong> These fields are for viewing purposes only.' +
                                '</div>'
                            );
                        }

                        if (!$('#office-details .readonly-alert').length) {
                            $('#office-details').prepend(
                                '<div class="alert alert-secondary readonly-alert" role="alert">' +
                                '<i class="fa fa-eye"></i> <strong>View Only:</strong> These fields are for viewing purposes only.' +
                                '</div>'
                            );
                        }

                        // Apply visual styling
                        $('#basic-details fieldset, #office-details fieldset').css({
                            'position': 'relative',
                            'background-color': '#f8f9fa'
                        });

                        // Prevent any click interactions
                        $('#basic-details, #office-details').on('click',
                            'input, textarea, select, button:not([name="next"]):not([name="previous"])',
                            function(e) {
                                const allowedButtons = ['next', 'previous', 'createBtn', 'submitBtn', 'approveBtn',
                                    'rejectBtn'
                                ];
                                const buttonName = $(this).attr('name');

                                if (!allowedButtons.includes(buttonName)) {
                                    e.preventDefault();
                                    e.stopPropagation();
                                    return false;
                                }
                            });

                        console.log('✅ Read-only mode successfully enforced');
                    }

                    // Execute immediately on page load
                    enforceReadOnlyTabs();

                    // Re-enforce after stepper navigation
                    $('button[onclick*="stepper"]').on('click', function() {
                        setTimeout(enforceReadOnlyTabs, 150);
                    });

                    // Re-enforce on window focus (prevents dev tools manipulation)
                    $(window).on('focus', enforceReadOnlyTabs);



                    // Enhanced File Handling Functions
                    function validateFile(fileInput) {
                        const file = fileInput.files[0];
                        if (!file) return false;

                        const allowedTypes = ['application/pdf'];
                        const allowedExtensions = /(\.pdf)$/i;
                        const maxSize = 10 * 1024 * 1024; // 10MB

                        if (!allowedTypes.includes(file.type) && !allowedExtensions.exec(file.name)) {
                            SwalUtils.error('Invalid File Type', 'Only PDF files are allowed.');
                            fileInput.value = '';
                            return false;
                        }

                        if (file.size > maxSize) {
                            SwalUtils.error('File Too Large', 'File size must be less than 10MB.');
                            fileInput.value = '';
                            return false;
                        }

                        return true;
                    }

                    function formatFileSize(bytes) {
                        if (bytes === 0) return '0 Bytes';
                        const k = 1024;
                        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
                        const i = Math.floor(Math.log(bytes) / Math.log(k));
                        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
                    }

                    function updateFileStatistics() {
                        const fileCount = $('.file-item:not(.deleting)').length;
                        const maxFiles = 3;
                        const remainingFiles = maxFiles - fileCount;

                        let statisticsHtml = `<strong>${fileCount}</strong> file(s) uploaded `;
                        if (fileCount >= maxFiles) {
                            statisticsHtml += '<span class="text-warning">• Maximum limit reached</span>';
                        } else {
                            statisticsHtml +=
                                `<span class="text-success">• ${remainingFiles} more file(s) can be added</span>`;
                        }

                        $('.file-statistics small').html('<i class="fa fa-info-circle"></i> ' + statisticsHtml);

                        // Show/hide add file button based on limit
                        if (fileCount >= maxFiles) {
                            $('.addAttachment').hide();
                        } else {
                            $('.addAttachment').show();
                        }
                    }

                    // File input change handler with enhanced validation
                    $(document).on('change', 'input[name="attachmentNames[]"]', function() {
                        const isValid = validateFile(this);
                        const fileItem = $(this).closest('.file-item');

                        if (isValid) {
                            fileItem.addClass('valid').removeClass('invalid');
                            const file = this.files[0];
                            const fileSize = formatFileSize(file.size);

                            // Add file metadata
                            const metaInfo =
                                `<div class="file-meta">Size: ${fileSize} • ${new Date().toLocaleDateString()}</div>`;
                            fileItem.find('.file-details').append(metaInfo);
                        } else {
                            fileItem.addClass('invalid').removeClass('valid');
                        }
                    });

                    // Enhanced delete file handler with improved UX
                    $(document).on('click', '.deleteFile', async function(e) {
                        e.preventDefault();

                        const fileId = $(this).data('file-id');
                        const fileItem = $(this).closest('.file-item');
                        const fileName = fileItem.find('.file-name').text();

                        // Enhanced confirmation dialog
                        const confirmResult = await SwalUtils.confirm(
                            'Delete File?',
                            `Are you sure you want to delete "${fileName}"? This action cannot be undone.`,
                            'Yes, Delete!',
                            'Cancel'
                        );

                        if (!confirmResult.isConfirmed) return;

                        // Add deleting animation
                        fileItem.addClass('deleting');
                        $(this).prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');

                        // Show loading with file name
                        SwalUtils.loading('Deleting File', `Deleting "${fileName}"...`);

                        try {
                            const response = await $.ajax({
                                url: `/delete-client-party-attachment/${fileId}`,
                                type: 'POST',
                                dataType: 'json',
                                headers: {
                                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                }
                            });

                            if (response[1] == 200) {
                                await SwalUtils.success('Deleted!',
                                    `"${fileName}" has been deleted successfully.`, 2000);

                                // Remove file item with enhanced animation
                                fileItem.fadeOut(300, function() {
                                    $(this).remove();
                                    updateFileStatistics();

                                    // Check if no files remain
                                    if ($('.file-item').length === 0) {
                                        $('.existing-files-section').fadeOut(300);
                                        $('.no-files-message').fadeIn(300);
                                    }
                                });
                            } else {
                                fileItem.removeClass('deleting');
                                $(this).prop('disabled', false).html('<i class="fa fa-trash"></i>');
                                await SwalUtils.error('Deletion Failed', 'Unable to delete the file.');
                            }
                        } catch (xhr) {
                            fileItem.removeClass('deleting');
                            $(this).prop('disabled', false).html('<i class="fa fa-trash"></i>');
                            console.error('Delete file error:', xhr);
                            await SwalUtils.error('Deletion Failed',
                                xhr.responseJSON?.message || 'An error occurred while deleting the file.');
                        }
                    });

                    // Enhanced add attachment functionality
                    let attachmentCount = {{ count($clientParty->attachments) }};
                    const maxAttachments = 3;

                    $('.addAttachment').click(async function(e) {
                        e.preventDefault();

                        if (attachmentCount >= maxAttachments) {
                            await SwalUtils.warning('Limit Reached',
                                'Maximum file attachment limit reached (3 files).');
                            return;
                        }

                        const cloneHtml = $('.clone-template').html();
                        $('#attachClone').append(cloneHtml);
                        attachmentCount++;

                        updateFileStatistics();
                    });

                    // Remove attachment handler
                    $(document).on('click', '.remove-attachment', function() {
                        $(this).closest('.cloned-attachment').fadeOut(300, function() {
                            $(this).remove();
                            attachmentCount--;
                            updateFileStatistics();
                        });
                    });

                    // File preview enhancement
                    $(document).on('click', '.file-item .file-name', function(e) {
                        e.preventDefault();
                        const previewUrl = $(this).closest('.file-item').find('a[href*="preview"]').attr('href');
                        if (previewUrl) {
                            window.open(previewUrl, '_blank');
                        }
                    });

                    // Handle legalEntityType visibility
                    let type = document.body.dataset.type;
                    console.log("Type:-", type);
                    let legalTypeDiv = document.getElementById("legalEntityType")?.closest('.col');
                    if (legalTypeDiv) {
                        legalTypeDiv.style.display = type === "accounting" ? "none" : "block";
                    }

                    // Initialize debtor and vendor dropdowns
                    let debtor_val = window.default_debtor || '';
                    let vendor_val = window.default_vendor || '';
                    if (debtor_val === '') {
                        $('#debtorType').attr('disabled', 'disabled');
                    } else {
                        $('#debtorType').val(debtor_val).trigger('change');
                    }
                    if (vendor_val === '') {
                        $('#vendorType').attr('disabled', 'disabled');
                    } else {
                        $('#vendorType').val(vendor_val).trigger('change');
                    }

                    // // Make all form fields read-only or disabled, EXCLUDING the <fieldset> inputs
                    // // Selects (excluding those in the <fieldset>)
                    // $('select[name="partyEntityType"], select[name="legalEntityType"], select[name="countryID"], select[name="officeCountry"], select[name="billingCurrency"], select[name="licenceTypeID"], #level')
                    //     .prop('readonly', true);

                    // // Checkboxes (excluding those in the <fieldset>)
                    // $('input[name="otherCheck"]:not(.border.p-2 input), input[name="debtorCheck"]:not(.border.p-2 input), input[name="vendorCheck"]:not(.border.p-2 input)')
                    //     .prop('readonly', true);

                    // // Text inputs and textareas (excluding those in the <fieldset>)
                    // $('input[name="companyName"], input[name="provinceName"], input[name="zipCode"], input[name="cityName"], input[name="firstName"], input[name="lastName"], input[name="department"], input[name="phoneNo"], input[name="contractEmail"], textarea[name="addressLine1"], textarea[name="addressLine2"], input[name="attachmentDescription[]"], input[name="creditDays"], input[name="maxCreditLimit"], input[name="baseCurrency"], input[name="licenceTypeValue"]')
                    //     .prop('readonly', true);

                    // // File inputs
                    // $('input[name="attachmentNames[]"]').prop('disabled', true);

                    // // Buttons - EXCLUDE approve and reject buttons
                    // $('.addAttachment, .deleteFile').prop('disabled', true).addClass('disabled');

                    // // Enable the specific inputs in the <fieldset> explicitly (optional, for clarity)
                    // $('fieldset.border.p-2 input[type="text"], fieldset.border.p-2 input[type="checkbox"], fieldset.border.p-2 input[type="hidden"]')
                    //     .prop('readonly', false)
                    //     .prop('disabled', false);

                    // ===== UPDATED CODE: Only disable Admin Details if status is "Created" =====
                    let currentStatus = '{{ $clientParty->Status }}';
                    console.log('Current Status:', currentStatus);

                    if (currentStatus === 'Created') {
                        // Use setTimeout to ensure this runs after all other jQuery operations
                        setTimeout(function() {
                            // More specific selectors for admin details
                            $('#admin-details table input[type="checkbox"]').prop('disabled', true).prop('checked',
                                true);
                            $('#admin-details table input[type="text"]').prop('readonly', true).prop('disabled',
                                true);
                            // $('#admin-details table input[
                            $('#admin-details table input[type="hidden"]').prop('disabled', true);

                            // Force disable by ID and name - more specific targeting
                            $('#otherCheck, #debtorCheck, #vendorCheck').prop('disabled', true);
                            $('#otherInput, #debtor, #vendor').prop('readonly', true).prop('disabled', true);
                            $('#otherID, #otherCode, #debtorID, #debtorCode, #vendorID, #vendorCode').prop(
                                'disabled', true);

                            // ONLY disable action buttons if status is "Created"
                            $('#admin-details button[name="approveBtn"]').prop('disabled', true).addClass(
                                'disabled');
                            $('#admin-details button[name="createBtn"]').prop('disabled', true).addClass(
                                'disabled');
                            $('#admin-details button[name="rejectBtn"]').prop('disabled', true).addClass(
                                'disabled');
                            $('#approveBtn, #createBtn, #rejectBtn').prop('disabled', true).addClass('disabled');

                            // Add stronger visual styling that overrides other styles
                            $('#admin-details table input, #admin-details table td').css({
                                'background-color': '#f8f9fa !important',
                                'cursor': 'not-allowed !important',
                                'opacity': '0.65 !important',
                                'pointer-events': 'none'
                            });

                            // Completely disable the table cells and remove all event handlers
                            $('#other_Input, #debtor_code, #vendor_code').css({
                                'cursor': 'not-allowed !important',
                                'opacity': '0.65 !important',
                                'pointer-events': 'none',
                                'background-color': '#f8f9fa'
                            }).off().attr('onclick', '').removeAttr('data-toggle').removeAttr('title');


                            // 🎯 Updated: Prevent any form submission from admin section with SweetAlert2
                            $('#admin-details form').on('submit', async function(e) {
                                e.preventDefault();
                                await SwalUtils.error('Action Not Allowed',
                                    'Cannot modify entity - Status is Created');
                                return false;
                            });

                            console.log('Admin Details forcefully disabled due to Created status');
                        }, 100);
                    } else {
                        // ===== ENSURE BUTTONS ARE ENABLED FOR NON-CREATED STATUS =====
                        setTimeout(function() {
                            // Explicitly enable approve and reject buttons for non-"Created" status
                            $('#approveBtn, #createBtn, #rejectBtn').prop('disabled', false).removeClass(
                                'disabled');
                            $('#submit-entity').prop('disabled', false).removeClass('disabled');

                            // Make sure they have proper styling
                            $('#approveBtn, #createBtn').removeClass('disabled').css({
                                'opacity': '1',
                                'pointer-events': 'auto',
                                'cursor': 'pointer'
                            });

                            $('#rejectBtn').removeClass('disabled').css({
                                'opacity': '1',
                                'pointer-events': 'auto',
                                'cursor': 'pointer'
                            });

                            console.log('Approve and Reject buttons enabled for status:', currentStatus);
                        }, 150);
                    }

                    function toggleEntityInput(checkboxId, inputId) {
                        const checkbox = $(checkboxId);
                        const input = $(inputId);

                        if (checkbox.is(':checked')) {
                            // If checkbox is checked, disable the input and remove required attribute
                            input.prop('disabled', true)
                                .prop('readonly', true)
                                .addClass('disabled')
                                .removeAttr('required')
                                .removeClass('is-invalid')
                                .css({
                                    'background-color': '#d4d2d2',
                                    'cursor': 'not-allowed',
                                    'opacity': '0.65'
                                });
                            console.log(`${inputId} disabled - ${checkboxId} is checked`);
                        } else {
                            // If checkbox is unchecked, enable the input and make it required (unless status is Created)
                            let currentStatus = '{{ $clientParty->Status }}';
                            if (currentStatus !== 'Created') {
                                input.prop('disabled', false)
                                    .prop('readonly', false)
                                    .removeClass('disabled')
                                    .attr('required', true)
                                    .css({
                                        'background-color': '',
                                        'cursor': '',
                                        'opacity': ''
                                    });

                                // Add visual indicator for required field (specifically for otherInput)
                                if (inputId === '#otherInput') {
                                    const label = $('label[for="otherInput"]');
                                    if (label.length && !label.find('.text-danger').length) {
                                        label.append(' <span class="text-danger">*</span>');
                                    }
                                }

                                console.log(`${inputId} enabled and made required - ${checkboxId} is unchecked`);
                            } else {
                                console.log(`${inputId} remains disabled - status is Created`);
                            }
                        }
                    }

                    // Function to initialize all entity toggles
                    function initializeEntityToggles() {
                        // Toggle for Other entity
                        toggleEntityInput('#otherCheck', '#otherInput');

                        // Toggle for Debtor entity (if it exists)
                        if ($('#debtorCheck').length && $('#debtor').length) {
                            toggleEntityInput('#debtorCheck', '#debtor');
                        }

                        // Toggle for Vendor entity (if it exists)
                        if ($('#vendorCheck').length && $('#vendor').length) {
                            toggleEntityInput('#vendorCheck', '#vendor');
                        }
                    }

                    // Initialize all toggles on page load
                    initializeEntityToggles();

                    // Add event listeners for all checkboxes with real-time validation
                    $('#otherCheck, #debtorCheck, #vendorCheck').on('change click', function() {
                        const checkboxId = '#' + $(this).attr('id');
                        let inputId;

                        // Map checkbox to corresponding input
                        switch (checkboxId) {
                            case '#otherCheck':
                                inputId = '#otherInput';
                                break;
                            case '#debtorCheck':
                                inputId = '#debtor';
                                break;
                            case '#vendorCheck':
                                inputId = '#vendor';
                                break;
                        }

                        if (inputId) {
                            // Small delay to ensure the checked state is updated
                            setTimeout(() => toggleEntityInput(checkboxId, inputId), 10);
                        }
                    });

                    // Add real-time validation for otherInput
                    $('#otherInput').on('input blur', function() {
                        const otherCheckbox = $('#otherCheck');
                        const input = $(this);

                        if (!otherCheckbox.is(':checked')) {
                            const value = input.val()?.trim();
                            if (!value) {
                                input.addClass('is-invalid');
                            } else {
                                input.removeClass('is-invalid');
                            }
                        }
                    });

                    // Handle partyEntityType validation (skipped since field is disabled)
                    const typeConfig = {
                        'Agent / Forwarder': {
                            display: 'Agent / Forwarder',
                            value: 'Agent / Forwarder'
                        },
                        'Trucker / Transporter': {
                            display: 'Trucker / Transporter',
                            value: 'Trucker / Transporter'
                        },
                        'Client': {
                            display: 'Client',
                            value: 'Client'
                        },
                        'Airline': {
                            display: 'Airline',
                            value: 'Airline'
                        },
                        'Shipping Line': {
                            display: 'Shipping Line',
                            value: 'Shipping Line'
                        }
                    };
                    const serverPartyType = '{{ $clientParty->OperationalEntityType }}';
                    const config = typeConfig[serverPartyType] || {
                        display: serverPartyType,
                        value: serverPartyType
                    };
                    $('#partyEntityType').val(config.value);

                    // 🔥 UPDATED: Approve button handler with SweetAlert2
                    $('#approveBtn').off('click').on('click', async function(e) {
                        e.preventDefault();

                        // Validate otherInput if otherCheck is unchecked
                        const otherCheckbox = $('#otherCheck');
                        const otherInput = $('#otherInput');

                        if (otherCheckbox.is(':checked')) {
                            const otherInputValue = otherInput.val()?.trim();
                            if (otherInputValue) {
                                // 🎯 SweetAlert2 validation error
                                await SwalUtils.warning('Validation Error',
                                    'Other Party field is required when not using existing entity.');
                                otherInput.addClass('is-invalid').focus();
                                return;
                            } else {
                                otherInput.removeClass('is-invalid');
                            }
                        }

                        // 🎯 SweetAlert2 confirmation
                        const confirmResult = await SwalUtils.confirm(
                            'Confirm Approval',
                            'Are you sure you want to approve this entity?',
                            'Yes, Approve!',
                            'Cancel'
                        );

                        if (!confirmResult.isConfirmed) return;

                        // Validate OtherParty fields
                        const otherInputVal = $('input[name="otherInput"]').val()?.trim();
                        const otherID = $('input[name="otherID"]').val()?.trim();
                        const otherCode = $('input[name="otherCode"]').val()?.trim();

                        if (type === 'operational' && (!otherInputVal || !otherID || !otherCode)) {
                            // 🎯 SweetAlert2 validation error
                            await SwalUtils.error('Missing Information',
                                'Please ensure all Other Party fields (Name, ID, Code) are filled.');
                            console.error('Validation failed: Missing OtherParty fields', {
                                otherInputVal,
                                otherID,
                                otherCode
                            });
                            return;
                        }

                        let currentStatus = '{{ $clientParty->Status }}';
                        let isAccounting =
                            '{{ $clientParty->DebtorType || $clientParty->VendorType ? 'true' : 'false' }}' ===
                            'true' ||
                            '{{ request()->query('type') === 'accounting' ? 'true' : 'false' }}' === 'true';
                        let targetStatus;

                        if (isAccounting) {
                            if (currentStatus === 'Not Submitted') {
                                targetStatus = 'PendingCountryLevelApproval';
                            } else if (currentStatus === 'PendingCountryLevelApproval') {
                                targetStatus = 'PendingGroupLevelApproval';
                            } else if (currentStatus === 'PendingGroupLevelApproval') {
                                targetStatus = 'PendingITVerification';
                            } else if (currentStatus === 'PendingITVerification') {
                                targetStatus = 'Created';
                            } else {
                                // 🎯 SweetAlert2 error
                                await SwalUtils.error('Invalid Action',
                                    `Approval not allowed for status: ${currentStatus}`);
                                console.error('Invalid status transition', {
                                    currentStatus
                                });
                                return;
                            }
                        } else {
                            if (currentStatus === 'Not Submitted') {
                                targetStatus = 'Submitted';
                            } else if (currentStatus === 'Submitted') {
                                targetStatus = 'Created';
                            } else {
                                // 🎯 SweetAlert2 error
                                await SwalUtils.error('Invalid Action',
                                    `Approval not allowed for status: ${currentStatus}`);
                                console.error('Invalid status transition', {
                                    currentStatus
                                });
                                return;
                            }
                        }

                        // Show loading state
                        SwalUtils.loading('Processing Approval',
                            'Please wait while we process your approval...');

                        let form_data = {
                            _token: '{{ csrf_token() }}',
                            id: '{{ $clientParty->Id }}',
                            status: targetStatus,
                            partyEntityType: $('#partyEntityType').val() ||
                                '{{ $clientParty->OperationalEntityType ?? '' }}',
                            debtorType: $('input[name="debtorType"]').val() || null,
                            vendorType: $('input[name="vendorType"]').val() || null,
                            type: isAccounting ? 'accounting' : 'operational',
                            otherInput: otherInputVal,
                            otherID: otherID,
                            otherCode: otherCode,
                            otherCheck: $('#otherCheck').is(':checked') ? 1 : 0,
                            debtor: $('input[name="debtor"]').val() || '',
                            debtorID: $('input[name="debtorID"]').val() || '',
                            debtorCode: $('input[name="debtorCode"]').val() || '',
                            debtorCheck: $('#debtorCheck').is(':checked') ? 1 : 0,
                            vendor: $('input[name="vendor"]').val() || '',
                            vendorID: $('input[name="vendorID"]').val() || '',
                            vendorCode: $('input[name="vendorCode"]').val() || '',
                            vendorCheck: $('#vendorCheck').is(':checked') ? 1 : 0
                        };

                        console.log('Sending form_data:', form_data);

                        try {
                            const response = await $.ajax({
                                url: '{{ route('client-party.approve') }}',
                                type: 'POST',
                                dataType: 'json',
                                data: form_data
                            });

                            console.log('Approval success:', response);

                            // 🎯 SweetAlert2 success
                            await SwalUtils.success(
                                'Success!',
                                response.message || 'Entity approved successfully!',
                                2000
                            );

                            window.location.href = '{{ route('client-party.index') }}';
                        } catch (xhr) {
                            console.error('AJAX Error:', xhr.responseJSON || xhr.statusText);
                            let errorMessage = xhr.responseJSON?.message || 'Approval failed';
                            if (xhr.status === 422 && xhr.responseJSON?.errors) {
                                errorMessage = 'Validation failed:\n' + Object.values(xhr.responseJSON.errors)
                                    .flat().join('\n');
                            }

                            // 🎯 SweetAlert2 error
                            await SwalUtils.error('Approval Failed', errorMessage);
                            $('#approveBtn').prop('disabled', false).text('Approve');
                        }
                    });

                    $('button[name="createBtn"]').off('click').on('click', async function(e) {
                        e.preventDefault();

                        let requestType = window.requestType || 'operational';
                        let isAccountingType = requestType === 'accounting';

                        // Client-side validation (same as before)
                        if (isAccountingType) {
                            console.log('Processing createBtn click for accounting type entity');

                            // Get checkbox values
                            let otherCheckValue = $('#otherCheck').is(':checked') ? 1 : 0;
                            let debtorCheckValue = $('#debtorCheck').is(':checked') ? 1 : 0;
                            let vendorCheckValue = $('#vendorCheck').is(':checked') ? 1 : 0;

                            console.log('Checkbox values:', {
                                otherCheck: otherCheckValue,
                                debtorCheck: debtorCheckValue,
                                vendorCheck: vendorCheckValue
                            });

                            // Validation for accounting entities
                            if (otherCheckValue === 0 && !$('#otherInput').val()?.trim()) {
                                await SwalUtils.warning('Validation Error',
                                    'Other Party field is required when checkbox is unchecked.');
                                $('#otherInput').addClass('is-invalid').focus();
                                return;
                            }

                            if (debtorCheckValue === 0 && !$('#debtor').val()?.trim()) {
                                await SwalUtils.warning('Validation Error',
                                    'Debtor field is required when checkbox is unchecked.');
                                $('#debtor').addClass('is-invalid').focus();
                                return;
                            }

                            if (vendorCheckValue === 0 && !$('#vendor').val()?.trim()) {
                                await SwalUtils.warning('Validation Error',
                                    'Vendor field is required when checkbox is unchecked.');
                                $('#vendor').addClass('is-invalid').focus();
                                return;
                            }
                        }

                        // Confirm action
                        const confirmResult = await SwalUtils.confirm(
                            'Confirm Creation',
                            'Are you sure you want to create this entity?',
                            'Yes, Create!',
                            'Cancel'
                        );

                        if (!confirmResult.isConfirmed) return;

                        // Add checkbox values as hidden inputs to the form
                        $('#clientPartyForm').find(
                            'input[name="otherCheckValue"], input[name="debtorCheckValue"], input[name="vendorCheckValue"]'
                        ).remove();

                        // Add hidden inputs with checkbox values
                        $('<input>').attr('type', 'hidden').attr('name', 'otherCheck').attr('value', $(
                            '#otherCheck').is(':checked') ? 1 : 0).appendTo('#clientPartyForm');
                        $('<input>').attr('type', 'hidden').attr('name', 'debtorCheck').attr('value', $(
                            '#debtorCheck').is(':checked') ? 1 : 0).appendTo('#clientPartyForm');
                        $('<input>').attr('type', 'hidden').attr('name', 'vendorCheck').attr('value', $(
                            '#vendorCheck').is(':checked') ? 1 : 0).appendTo('#clientPartyForm');

                        // Add createBtn indicator
                        $('<input>').attr('type', 'hidden').attr('name', 'createBtn').attr('value', 'true')
                            .appendTo('#clientPartyForm');

                        // Show loading
                        SwalUtils.loading('Creating Entity', 'Please wait while we create the entity...');

                        // Submit the form traditionally
                        $('#clientPartyForm')[0].submit();
                    });


                    // 🔥 UPDATED: Reject button handler with SweetAlert2
                    $('#close-modal').click(function() {
                        $('#cancelForm')[0].reset();
                        $('#cancelModal').modal('hide');
                    });

                    $('#submit-entity').on('click', async function() {
                        let form_data = {
                            id: '{{ $clientParty->Id }}',
                            level: $('#level option:selected').val(),
                            reason: $('#reason').val(),
                        };

                        if (!form_data.level) {
                            $('#level').addClass('is-invalid');
                            await SwalUtils.warning('Missing Level', 'Please select a rejection level.');
                            return;
                        }
                        if (!form_data.reason) {
                            $('#reason').addClass('is-invalid');
                            await SwalUtils.warning('Missing Reason', 'Please provide a reason for rejection.');
                            return;
                        }

                        // Show loading
                        SwalUtils.loading('Processing Rejection',
                            'Please wait while we process the rejection...');

                        try {
                            const response = await $.ajax({
                                url: '/client-party/reject',
                                type: 'POST',
                                dataType: 'json',
                                data: form_data
                            });

                            console.log(response);

                            // Close the modal first
                            $('#cancelModal').modal('hide');

                            // 🎯 SweetAlert2 success with redirect
                            await SwalUtils.success('Rejected!',
                                'Entity has been rejected successfully.', 2000);

                            // ✅ Redirect to dashboard instead of reload
                            setTimeout(() => {
                                window.location.href = '{{ route('client-party.index') }}';
                            }, 2000);

                        } catch (xhr) {
                            // 🎯 SweetAlert2 error
                            await SwalUtils.error('Rejection Failed', xhr.responseJSON?.message ||
                                'Unknown error occurred');
                        }
                    });

                    // 🔥 UPDATED: File attachment handling with SweetAlert2
                    let init = {{ $clientParty->attachments->count() + 1 }};
                    let max_fields = 3;
                    $('.addAttachment').click(async function() {
                        let lsthmtl = $('.clone').html();
                        if (init < max_fields) {
                            let names = document.getElementsByName('attachmentNames[]');
                            let desc = document.getElementsByName('attachmentDescription[]');
                            let fileInputName = names[names.length - 2];
                            let fileInputDescription = desc[desc.length - 2];
                            let allowedExtensions = /(\.jpg|\.jpeg|\.png|\.gif)$/i;

                            if (!allowedExtensions.exec(fileInputName.value) || fileInputDescription.value ===
                                '') {
                                // 🎯 SweetAlert2 validation error
                                await SwalUtils.warning('Invalid File',
                                    'Either invalid file type or no description provided. Please ensure you upload a valid image file (jpg, jpeg, png, gif) and provide a description.'
                                );
                                fileInputName.value = '';
                                return false;
                            } else {
                                $('#attachClone').before(lsthmtl);
                                init++;
                            }
                        } else {
                            // 🎯 SweetAlert2 warning
                            await SwalUtils.warning('Limit Reached',
                                'Maximum file attachment limit reached (3 files).');
                        }
                    });

                    $('body').on('click', '.btn-danger', function() {
                        $(this).parents('.hdtuto').remove();
                        init--;
                    });

                    // 🔥 UPDATED: Delete file handler with SweetAlert2
                    let spinner = $('#loader');
                    $('body').on('click', '.deleteFile', async function() {
                        let id = $(this).attr('id');

                        // 🎯 SweetAlert2 confirmation for file deletion
                        const confirmResult = await SwalUtils.confirm(
                            'Delete File?',
                            'Are you sure you want to delete this file? This action cannot be undone.',
                            'Yes, Delete!',
                            'Cancel'
                        );

                        if (!confirmResult.isConfirmed) return;

                        let url = '/delete-client-party-attachment/' + id;

                        // Show loading
                        SwalUtils.loading('Deleting File', 'Please wait while we delete the file...');
                        spinner.show();

                        try {
                            const response = await $.ajax({
                                url: url,
                                type: 'POST',
                                dataType: 'json'
                            });

                            if (response[1] == 200) {
                                // 🎯 SweetAlert2 success
                                await SwalUtils.success('Deleted!', 'File has been deleted successfully.',
                                    2000);
                                location.reload();
                            }
                        } catch (xhr) {
                            spinner.hide();
                            // 🎯 SweetAlert2 error
                            await SwalUtils.error('Deletion Failed', xhr.responseJSON?.message ||
                                'Unknown error occurred');
                        }
                    });


                    // Function to handle Legal Entity Type changes
                    function handleLegalEntityType(selectedValue) {
                        console.log('Legal Entity Type changed to:', selectedValue);

                        // Only proceed if not in disabled/readonly mode (status !== 'Created')
                        let currentStatus = '{{ $clientParty->Status }}';
                        if (currentStatus === 'Created') {
                            console.log('Legal Entity Type changes disabled - Status is Created');
                            return;
                        }

                        // Reset all validation states first
                        $('#debtorType, #vendorType').removeClass('is-invalid');

                        // Remove any existing hidden inputs for checkboxes
                        $('#debtorCheckVal, #vendorCheckVal').remove();

                        switch (selectedValue) {
                            case 'Debtor':
                                showDebtorOnly();
                                break;

                            case 'Vendor':
                                showVendorOnly();
                                break;

                            case 'Debtor & Vendor':
                                showDebtorAndVendor();
                                break;

                            case 'Only in D365 Debtor':
                                showD365DebtorOnly();
                                break;

                            case 'Only in D365 Vendor':
                                showD365VendorOnly();
                                break;

                            default:
                                // Hide all by default
                                hideAllEntities();
                                break;
                        }

                        // Update visual indicators
                        updateAdminDetailsVisuals();
                    }

                    // Show only Debtor row, hide and disable Vendor
                    function showDebtorOnly() {
                        console.log('Showing Debtor only, hiding Vendor');

                        // Show and enable Debtor row
                        showAndEnableDebtorRow();

                        // Hide and disable Vendor row
                        hideAndDisableVendorRow();

                        // Add hidden input for debtor checkbox
                        $('form').append('<input type="hidden" value="1" name="debtorCheck" id="debtorCheckVal">');

                        showEntityTypeChangeFeedback('Debtor only - Vendor section hidden');
                    }

                    // Show only Vendor row, hide and disable Debtor
                    function showVendorOnly() {
                        console.log('Showing Vendor only, hiding Debtor');

                        // Hide and disable Debtor row
                        hideAndDisableDebtorRow();

                        // Show and enable Vendor row
                        showAndEnableVendorRow();

                        // Add hidden input for vendor checkbox
                        $('form').append('<input type="hidden" value="1" name="vendorCheck" id="vendorCheckVal">');

                        showEntityTypeChangeFeedback('Vendor only - Debtor section hidden');
                    }

                    // Show both Debtor and Vendor rows
                    function showDebtorAndVendor() {
                        console.log('Showing both Debtor and Vendor');

                        // Show and enable both rows
                        showAndEnableDebtorRow();
                        showAndEnableVendorRow();

                        // Add hidden inputs for both checkboxes
                        $('form').append('<input type="hidden" value="1" name="debtorCheck" id="debtorCheckVal">');
                        $('form').append('<input type="hidden" value="1" name="vendorCheck" id="vendorCheckVal">');

                        showEntityTypeChangeFeedback('Both Debtor & Vendor sections enabled');
                    }

                    // Show only D365 Debtor, hide Vendor
                    function showD365DebtorOnly() {
                        console.log('Showing D365 Debtor only, hiding Vendor');

                        // Show and enable Debtor row with D365 styling
                        showAndEnableDebtorRow();
                        addD365StylingToDebtor();

                        // Hide and disable Vendor row
                        hideAndDisableVendorRow();

                        // Add hidden input for debtor checkbox
                        $('form').append('<input type="hidden" value="1" name="debtorCheck" id="debtorCheckVal">');

                        showEntityTypeChangeFeedback('D365 Debtor only - Vendor section hidden');
                    }

                    // Show only D365 Vendor, hide Debtor
                    function showD365VendorOnly() {
                        console.log('Showing D365 Vendor only, hiding Debtor');

                        // Hide and disable Debtor row
                        hideAndDisableDebtorRow();

                        // Show and enable Vendor row with D365 styling
                        showAndEnableVendorRow();
                        addD365StylingToVendor();

                        // Add hidden input for vendor checkbox
                        $('form').append('<input type="hidden" value="1" name="vendorCheck" id="vendorCheckVal">');

                        showEntityTypeChangeFeedback('D365 Vendor only - Debtor section hidden');
                    }

                    // Hide all entity rows
                    function hideAllEntities() {
                        console.log('Hiding all entity sections');

                        hideAndDisableDebtorRow();
                        hideAndDisableVendorRow();

                        showEntityTypeChangeFeedback('All entity sections hidden');
                    }

                    // Show and enable Debtor row
                    function showAndEnableDebtorRow() {
                        // Show the debtor table row
                        $('tr').has('#debtorCheck').show().removeClass('d-none');

                        // Enable Debtor checkbox and input
                        $('#debtorCheck')
                            .prop('checked', true)
                            .prop('disabled', false)
                            .removeClass('disabled');

                        $('#debtor')
                            .prop('disabled', false)
                            .prop('readonly', false)
                            .removeClass('disabled')
                            .attr('required', false);

                        // Update debtor type dropdown
                        $('#debtorType')
                            .prop('disabled', false)
                            .prop('required', true)
                            .removeClass('disabled');


                    }

                    // Show and enable Vendor row
                    function showAndEnableVendorRow() {
                        // Show the vendor table row
                        $('tr').has('#vendorCheck').show().removeClass('d-none');

                        // Enable Vendor checkbox and input
                        $('#vendorCheck')
                            .prop('checked', true)
                            .prop('disabled', false)
                            .removeClass('disabled');

                        $('#vendor')
                            .prop('disabled', false)
                            .prop('readonly', false)
                            .removeClass('disabled')
                            .attr('required', false);

                        // Update vendor type dropdown
                        $('#vendorType')
                            .prop('disabled', false)
                            .prop('required', true)
                            .removeClass('disabled')
                    }

                    // Hide and disable Debtor row
                    function hideAndDisableDebtorRow() {
                        // Hide the debtor table row
                        $('tr').has('#debtorCheck').hide().addClass('d-none');

                        // Disable and clear Debtor fields
                        $('#debtorCheck')
                            .prop('checked', false)
                            .prop('disabled', true)
                            .addClass('disabled');

                        $('#debtor')
                            .prop('disabled', true)
                            .prop('readonly', true)
                            .addClass('disabled')
                            .removeAttr('required')
                            .val(''); // Clear debtor input

                        // Update debtor type dropdown
                        $('#debtorType')
                            .prop('disabled', true)
                            .removeAttr('required')
                            .addClass('disabled')
                            .val(''); // Reset selection

                        // Clear debtor related hidden fields
                        $('#debtorID, #debtorCode').val('');

                        // Make debtor code cell non-interactive
                        $('#debtor_code')
                            .css({
                                'opacity': '0.3',
                                'pointer-events': 'none',
                                'background-color': '#f8f9fa'
                            })
                            .removeAttr('onclick')
                            .removeAttr('data-toggle')
                            .attr('title', 'Debtor disabled by Legal Entity Type');

                        // Remove D365 styling
                        $('#debtor, #debtorType').removeClass('d365-entity');
                    }

                    // Hide and disable Vendor row
                    function hideAndDisableVendorRow() {
                        // Hide the vendor table row
                        $('tr').has('#vendorCheck').hide().addClass('d-none');

                        // Disable and clear Vendor fields
                        $('#vendorCheck')
                            .prop('checked', false)
                            .prop('disabled', true)
                            .addClass('disabled');

                        $('#vendor')
                            .prop('disabled', true)
                            .prop('readonly', true)
                            .addClass('disabled')
                            .removeAttr('required')
                            .val(''); // Clear vendor input

                        // Update vendor type dropdown
                        $('#vendorType')
                            .prop('disabled', true)
                            .removeAttr('required')
                            .addClass('disabled')
                            .val(''); // Reset selection

                        // Clear vendor related hidden fields
                        $('#vendorID, #vendorCode').val('');

                        // Make vendor code cell non-interactive
                        $('#vendor_code')
                            .css({
                                'opacity': '0.3',
                                'pointer-events': 'none',
                                'background-color': '#f8f9fa'
                            })
                            .removeAttr('onclick')
                            .removeAttr('data-toggle')
                            .attr('title', 'Vendor disabled by Legal Entity Type');

                        // Remove D365 styling
                        $('#vendor, #vendorType').removeClass('d365-entity');
                    }

                    // Add D365 styling to Debtor
                    function addD365StylingToDebtor() {
                        $('#debtor').addClass('d365-entity').attr('placeholder', 'D365 Debtor Entity');
                        $('#debtorType').addClass('d365-entity');
                        $('#debtor_code').addClass('d365-code');
                    }

                    // Add D365 styling to Vendor
                    function addD365StylingToVendor() {
                        $('#vendor').addClass('d365-entity').attr('placeholder', 'D365 Vendor Entity');
                        $('#vendorType').addClass('d365-entity');
                        $('#vendor_code').addClass('d365-code');
                    }

                    // Update visual indicators for admin details
                    function updateAdminDetailsVisuals() {
                        // Add animation class for smooth transitions
                        $('#admin-details table tr').addClass('entity-transition');

                        // Update table styling
                        setTimeout(() => {
                            $('#admin-details table tr').removeClass('entity-transition');
                        }, 300);
                    }

                    // Show user feedback when entity type changes
                    function showEntityTypeChangeFeedback(message) {
                        // Use SweetAlert2 for better feedback
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'info',
                                title: 'Entity Configuration Updated',
                                text: message,
                                toast: true,
                                position: 'top-end',
                                showConfirmButton: false,
                                timer: 3000,
                                timerProgressBar: true
                            });
                        } else {
                            // Fallback to console log if SweetAlert2 not available
                            console.log('Entity Configuration:', message);
                        }
                    }

                    // Event listener for Legal Entity Type dropdown changes
                    $('#legalEntityType').on('change', function() {
                        const selectedValue = $(this).val();
                        console.log('Legal Entity Type dropdown changed:', selectedValue);

                        if (selectedValue) {
                            handleLegalEntityType(selectedValue);
                        } else {
                            // If no selection, hide all
                            hideAllEntities();
                        }
                    });

                    // Initialize on page load if legal entity type is already selected
                    $(document).ready(function() {
                        const currentLegalEntityType = $('#legalEntityType').val();
                        if (currentLegalEntityType && currentLegalEntityType !== '') {
                            console.log('Initializing with existing Legal Entity Type:', currentLegalEntityType);
                            handleLegalEntityType(currentLegalEntityType);
                        } else {
                            // Hide all by default if no legal entity type selected
                            hideAllEntities();
                        }
                    });

                    // Override copy function for disabled states
                    window.originalCopyText = window.copyText;
                    window.copyText = function(element) {
                        const $element = $(element);
                        if ($element.css('pointer-events') === 'none') {
                            console.log('Copy disabled for this entity type');
                            return;
                        }
                        if (window.originalCopyText) {
                            window.originalCopyText(element);
                        }
                    };

                    console.log('Legal Entity Type handler (Hide/Disable version) initialized for show blade');
                });
            </script>
            <script src="{{ asset('js/client-party/show-create-client-party.js') }}"></script>
        @stop
