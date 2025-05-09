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
                                enctype="multipart/form-data" id="clientPartyForm">
                                @csrf
                                <!-- your steps content here -->
                                <div id="basic-details" class="content" role="tabpanel"
                                    aria-labelledby="basic-details-trigger">
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
                                                                    <option value="Agent / Forwarder"
                                                                        @if (old('partyEntityType') == 'Agent / Forwarder') selected @endif>
                                                                        Agent / Forwarder</option>
                                                                    <option value="Airline"
                                                                        @if (old('partyEntityType') == 'Airline') selected @endif>
                                                                        Airline</option>
                                                                    <option value="Shipping Line"
                                                                        @if (old('partyEntityType') == 'Shipping Line') selected @endif>
                                                                        Shipping Line</option>
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
                                                            value="{{ old('companyName') }}" name="companyName" id="companyName" maxlength="50">
                                                        <!-- Dropdown for other entity types -->
                                                        <div id="companyDropdown" style="display: none;">
                                                            <select class="form-control form-control-sm" id="companySelect" name="companySelect">
                                                                <option value="" disabled selected>Select Company</option>
                                                                <!-- Options will be populated via JavaScript -->
                                                            </select>
                                                            <!-- Hidden inputs to store Id and Code -->
                                                            <input type="hidden" name="companyId" id="companyId" value="{{ old('companyId') }}">
                                                            <input type="hidden" name="companyCode" id="companyCode" value="{{ old('companyCode') }}">
                                                        </div>
                                                    </div>
                                                </div>
                                                {{-- Address Line 1 and 2 --}}
                                                <div class="form-group row mb-1 address-row">
                                                    <div class="col-md-6">
                                                        <label for="addressLine1"
                                                            class="col-form-label col-form-label-sm">
                                                            <i class="fa fa-map-marker" aria-hidden="true"></i> Address
                                                            Line 1 <span class="text-danger">*</span>
                                                        </label>
                                                        <input type="text"
                                                            class="form-control form-control-sm basic-detail-field"
                                                            value="{{ old('addressLine1') }}" name="addressLine1"
                                                            id="addressLine1" maxlength="50" required>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label for="addressLine2"
                                                            class="col-form-label col-form-label-sm">
                                                            <i class="fa fa-map-marker" aria-hidden="true"></i> Address
                                                            Line 2
                                                        </label>
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
                                                <div class="form-group mb-3">
                                                    <label for="phoneNo" class="col-form-label col-form-label-sm">
                                                        <i class="fa fa-phone" aria-hidden="true"></i> Phone No. <span class="text-danger">*</span>
                                                    </label>
                                                    <input type="text"
                                                        class="form-control form-control-sm number_input basic-detail-field"
                                                        value="{{ old('phoneNo') }}" name="phoneNo" id="phoneNo"
                                                        placeholder="Phone Number">
                                                </div>

                                                <div class="form-group mb-3">
                                                    <label for="contractEmail" class="col-form-label col-form-label-sm">
                                                        <i class="fa fa-envelope" aria-hidden="true"></i> Email
                                                    </label>
                                                    <input type="email" name="contractEmail" id="contractEmail"
                                                        value="{{ old('contractEmail') }}"
                                                        class="form-control form-control-sm basic-detail-field"
                                                        placeholder="Enter Email">
                                                </div>

                                            </fieldset>

                                        </div>
                                    </div>

                                    <button class="btn btn-primary m-2 btn-sm float-right" name="next" type="button"
                                        onclick="stepper.next()">Next <i class="fa fa-step-forward"
                                            aria-hidden="true"></i></button>
                                    <button type="button" name="save-step1" id="save-step1"
                                        class="btn btn-success btn-sm float-right mt-2" data-toggle="tooltip"
                                        data-placement="top" title="Save"><i class="fa fa-plus-circle"
                                            aria-hidden="true"></i> Save for Later</button>
                                </div>

                                {{-- office details --}}
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
                                                </div>
                                            </fieldset>
                                            <fieldset class="border p-2 card shadow-lg mb-0 pt-0 pb-0">
                                                <legend>File Upload</legend>
                                                <label for="attachmentFile">Attachment <i
                                                        class="fa fa-paperclip"></i><span style="color: red;">*</span>
                                                    <button type="button"
                                                        class="btn btn-sm btn-outline-warning float-right w-20 addAttachment"
                                                        id="addFile">
                                                        <i class="fa fa-plus"></i> File
                                                    </button>
                                                </label>
                                                <div class="input-group hdtuto control-group lst increment"
                                                    id="initial-attachment">
                                                    <input id="attachments" type="file" name="attachmentNames[]"
                                                        class="myfrm form-control mr-1 form-control-sm" required>
                                                    <input type="text" name="attachmentDescription[]"
                                                        class="myfrm form-control form-control-sm"
                                                        placeholder="Description" required>
                                                </div>
                                                <div id="attachClone"></div>
                                                <div class="clone-template d-none">
                                                    <div
                                                        class="hdtuto control-group lst input-group mt-1 cloned-attachment">
                                                        <input type="file" name="attachmentNames[]"
                                                            class="myfrm form-control mr-1 form-control-sm">
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
                                                                    class="col-form-label col-form-label-sm">Credit Days
                                                                    <span style="color: red;">*</span></label>
                                                                <select class="form-control form-control-sm"
                                                                    name="creditDays" id="creditDays" required>
                                                                    <option value="" disabled selected>Select Credit
                                                                        Days</option>
                                                                    @if (!empty($payTerms) && is_array($payTerms) && !isset($payTerms['error']))
                                                                        @foreach ($payTerms as $term)
                                                                            <option value="{{ $term['CreditDays'] }}"
                                                                                @if (old('creditDays') == $term['CreditDays']) selected @endif>
                                                                                {{ $term['CreditDays'] }} Days
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
                                                                    <input type="number"
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

                                                {{-- Licence Type --}}
                                                {{-- <div class="form-group row mb-0 mt-0 p-0">
                                                    <div class="col col-sm-6 col-lg-6 col-md-6 m-0">
                                                        <div class="row">
                                                            <label for="licence"
                                                                class="col-md-3 col-form-label col-form-label-sm pr-0">Licence
                                                                Type</label> <span style="color: red;">*</span>
                                                            <div class="col-md-8">
                                                                <select class="form-control form-control-sm"
                                                                    name="licenceTypeID" id="licenceTypeID" required>
                                                                    <option value="" disabled selected></option>
                                                                    @forelse ($licenceTypes as $type)
                                                                        <option value="{{ $type->LicenseID }}"
                                                                            id="{{ $type->LicenseCode }}"
                                                                            @if (old('licenceTypeID') == $type->LicenseID) selected @endif>
                                                                            {{ $type->LicenseName }}
                                                                        </option>
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
                                                            value="{{ old('licenceTypeValue') }}" name="licenceTypeValue"
                                                            id="licenceTypeValue" required>
                                                    </div>
                                                </div> --}}

                                                <div class="form-group row mb-0 mt-0 p-0">
                                                    <div class="col col-sm-6 col-lg-6 col-md-6 m-0">
                                                        <div class="row">
                                                            <label for="licence" class="col-md-3 col-form-label col-form-label-sm pr-0">Licence Type</label>
                                                            <span style="color: red;">*</span>
                                                            <div class="col-md-8">
                                                                <select class="form-control form-control-sm" name="licenceTypeID" id="licenceTypeID" required>
                                                                    <option value="" disabled selected>Select License Type</option>
                                                                    @forelse ($licenceTypes as $type)
                                                                        <option value="{{ $type->LicenseID }}"
                                                                                data-code="{{ $type->LicenseCode }}"
                                                                                @if (old('licenceTypeID') == $type->LicenseID) selected @endif>
                                                                            {{ $type->LicenseName }}
                                                                        </option>
                                                                    @empty
                                                                        <option value="" disabled>No license types available</option>
                                                                    @endforelse
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col col-sm-6 col-lg-6 col-md-6">
                                                        <input type="hidden" class="form-control form-control-sm"
                                                               value="{{ old('licenceTypeCode') }}" name="licenceTypeCode" id="licenceTypeCode">
                                                        <input type="text" class="form-control form-control-sm"
                                                               value="{{ old('licenceTypeValue') }}" name="licenceTypeValue" id="licenceTypeValue" required>
                                                        <small id="licenseError" class="text-danger" style="display: none;"></small>
                                                    </div>
                                                </div>
                                            </fieldset>
                                        </div>
                                    </div>
                                    <button class="btn btn-primary m-2 btn-sm" type="button" name="previous"
                                        onclick="stepper.previous()"><i class="fa fa-step-backward"
                                            aria-hidden="true"></i> Previous</button>
                                    @if (Auth::user()->can('create-client-party'))
                                        <button class="btn btn-primary m-2 btn-sm float-right" type="button"
                                            name="next" onclick="stepper.next()">Next <i class="fa fa-step-forward"
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
                                                        <tr id="client-check">
                                                            <td>Client</td>
                                                            <td><input type="checkbox" name="clientCheck"
                                                                    id="clientCheck"></td>
                                                            <td>
                                                                <input type="text" name="clientInput" id="clientInput"
                                                                    value="{{ old('clientInput') }}"
                                                                    class="form-control form-control-sm" required>
                                                                <input type="hidden" name="clientID" id="clientID"
                                                                    value="{{ old('clientID') }}">
                                                                <input type="hidden" name="clientCode" id="clientCode"
                                                                    value="{{ old('clientCode') }}">
                                                            </td>
                                                            <td id="client_Input"></td>
                                                        </tr>
                                                        <tr id="shipper-check">
                                                            <td>Shipper</td>
                                                            <td><input type="checkbox" name="shipperCheck"
                                                                    id="shipperCheck"></td>
                                                            <td>
                                                                <input type="text" name="shipperInput"
                                                                    id="shipperInput" class="form-control form-control-sm"
                                                                    required>
                                                                <input type="hidden" name="shipperID" id="shipperID">
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
                                                        <tr>
                                                            <td>Other</td>
                                                            <td><input type="checkbox" name="otherCheck" id="otherCheck"
                                                                    value="1"></td>
                                                            <td>
                                                                <input type="text" name="otherInput" id="otherInput"
                                                                    class="form-control form-control-sm"
                                                                    autocomplete="off">
                                                                <input type="hidden" name="otherID" id="otherID">
                                                                <input type="hidden" name="otherCode" id="otherCode">
                                                                <div class="invalid-feedback">Please select or enter a
                                                                    name.</div>
                                                            </td>
                                                            <td id="other_Input"></td>
                                                        </tr>
                                                        <tr id="debtor-check">
                                                            <td>Debtor</td>
                                                            <td><input type="checkbox" name="debtorCheck"
                                                                    id="debtorCheck"></td>
                                                            <td>
                                                                <input type="text" name="debtor" id="debtor"
                                                                    class="form-control form-control-sm" required>
                                                                <input type="hidden" name="debtorID" id="debtorID">
                                                                <input type="hidden" name="debtorCode" id="debtorCode">
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
                                                                <input type="hidden" name="vendorCode" id="vendorCode">
                                                            </td>
                                                            <td id="vendor_code"></td>
                                                        </tr>
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
            color: #E91809;
            font-weight: bold;
        }

        label {
            font-weight: bold;
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
            /* Prevents clicking or focusing */
        }
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
        /* Ajax setup CSRF */
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
    </script>
    <script>
        $(document).ready(function () {
            // Cache DOM elements
            const partyEntityType = $('#partyEntityType');
            const legalEntityType = $('#legalEntityType');
            const companyNameInput = $('#companyName');
            const companyDropdown = $('#companyDropdown');
            const companySelect = $('#companySelect');
            const companyIdInput = $('#companyId');
            const companyCodeInput = $('#companyCode');
            const debtorType = $('#debtorType');
            const vendorType = $('#vendorType');
            const requestType = new URLSearchParams(window.location.search).get('type');

            // Function to toggle input/dropdown visibility
            function toggleCompanyInput() {
                const selectedType = partyEntityType.val();
                legalEntityType.find('option').prop('disabled', false).show();
                clearBasicDetails();
                companyNameInput.hide();
                companyDropdown.hide(); // Hide dropdown by default
                toggleFieldsReadonly(false);

                const dropdownTypes = ['Client', 'Agent/Forwarder', 'Airline', 'ShippingLine', 'Trucker'];

                if (requestType === 'accounting') {
                    if (selectedType === 'Create only in D365') {
                        companyNameInput.show();
                        companyDropdown.hide(); // Explicitly hide dropdown
                        legalEntityType.find('option').each(function () {
                            const value = $(this).val();
                            if (value && value !== 'Only in D365 Debtor' && value !== 'Only in D365 Vendor') {
                                $(this).prop('disabled', true).hide();
                            }
                        });
                    } else if (dropdownTypes.includes(selectedType)) {
                        companyDropdown.show();
                        companyNameInput.hide(); // Ensure input is hidden
                        toggleFieldsReadonly(true);
                        loadCompanies(selectedType);
                        legalEntityType.find('option').each(function () {
                            const value = $(this).val();
                            if (value === 'Only in D365 Debtor' || value === 'Only in D365 Vendor') {
                                $(this).prop('disabled', true).hide();
                            }
                        });
                    }
                } else {
                    companyNameInput.show();
                    companyDropdown.hide(); // Hide dropdown for non-accounting
                }

                legalEntityType.val('');
                console.log('toggleCompanyInput: requestType=', requestType, 'selectedType=', selectedType, 'companyNameVisible=', companyNameInput.is(':visible'), 'companyDropdownVisible=', companyDropdown.is(':visible'));
            }

            // Function to load companies via AJAX
            function loadCompanies(entityType) {
                $.ajax({
                    url: '{{ route("companies") }}',
                    method: 'GET',
                    data: { entityType: entityType },
                    success: function (data) {
                        companySelect.find('option:not(:first)').remove();
                        if (data.error) {
                            companySelect.append('<option value="" disabled>' + data.error + '</option>');
                            console.error('API error:', data.error);
                        } else if (data.length === 0) {
                            companySelect.append('<option value="" disabled>No companies found</option>');
                        } else {
                            $.each(data, function (index, company) {
                                companySelect.append(
                                    `<option value="${company.id}" data-code="${company.code}" data-name="${company.name}">${company.name} (${company.code})</option>`
                                );
                            });
                        }
                    },
                    error: function (xhr) {
                        console.error('Error fetching companies:', xhr.responseJSON?.error || xhr.statusText);
                        companySelect.find('option:not(:first)').remove();
                        companySelect.append('<option value="" disabled>Error loading companies</option>');
                        alert('Failed to load company list. Please try again.');
                    }
                });
            }

            // Function to clear Basic Details fields
            function clearBasicDetails() {
                $('.basic-detail-field').val('');
                companyNameInput.val('');
                companySelect.val('');
                companyIdInput.val('');
                companyCodeInput.val('');
                $('#autocomplete-list').empty();
            }

            // Function to toggle field readonly/disabled state
            function toggleFieldsReadonly(disable) {
                $('.basic-detail-field').each(function () {
                    $(this).prop('readonly', disable).prop('disabled', disable);
                });
            }

            // Function to fetch and populate company details
            function fetchCompanyDetails(entityType, companyId) {
                $.ajax({
                    url: '{{ route("company.details") }}',
                    method: 'GET',
                    data: { entityType: entityType, companyId: companyId },
                    success: function (data) {
                        console.log('Company details:', data);
                        $('#addressLine1').val(data.addressLine1 || '');
                        $('#addressLine2').val(data.addressLine2 || '');
                        $('#cityName').val(data.cityName || '');
                        $('#zipCode').val(data.zipCode || '');
                        $('#provinceName').val(data.provinceName || '');
                        $('#provinceID').val(data.provinceID || '');
                        $('#provinceCode').val(data.provinceCode || '');
                        $('#cityID').val(data.cityID || '');
                        $('#cityCode').val(data.cityCode || '');
                        $('#countryID').val(data.countryID || '');
                        $('#countryCode').val(data.countryCode || '');
                        $('#countryName').val(data.countryName || '');
                        $('#firstName').val(data.firstName || '');
                        $('#lastName').val(data.lastName || '');
                        $('#department').val(data.department || '');
                        $('#phoneNo').val(data.phoneNo || '');
                        $('#contractEmail').val(data.contractEmail || '');
                    },
                    error: function (xhr) {
                        console.error('Error fetching company details:', xhr.responseJSON?.error || xhr.statusText);
                        clearBasicDetails();
                        toggleFieldsReadonly(true);
                        alert('Failed to retrieve company details.');
                    }
                });
            }

            // Handle partyEntityType change
            partyEntityType.on('change', function () {
                toggleCompanyInput();
            });

            // Handle company dropdown selection
            companySelect.on('change', function () {
                const selectedOption = $(this).find('option:selected');
                const companyName = selectedOption.data('name') || selectedOption.text().split(' (')[0];
                companyIdInput.val(selectedOption.val());
                companyCodeInput.val(selectedOption.data('code'));
                companyNameInput.val(companyName);
                if (selectedOption.val() && partyEntityType.val()) {
                    fetchCompanyDetails(partyEntityType.val(), selectedOption.val());
                } else {
                    clearBasicDetails();
                    toggleFieldsReadonly(true);
                }
            });

            // Initialize maxCreditLimit input
            $("#maxCreditLimit").on("input", function () {
                let maxCreditLimit = $(this).val();
                if (maxCreditLimit.startsWith("0")) {
                    $(this).val("1" + maxCreditLimit.slice(1));
                }
            });

            // Handle license type and validation
            $('#licenceTypeID').on('change', function () {
                const selectedOption = $(this).find('option:selected');
                $('#licenceTypeCode').val(selectedOption.data('code'));
            });

            $('#licenceTypeValue').on('input', function () {
                const licenseNo = $(this).val().trim();
                const countryCode = $('#countryCode').val();
                const partyType = legalEntityType.val() || 'Debtor';

                if (licenseNo && countryCode && partyType) {
                    $.ajax({
                        url: '{{ route("client-party.check-license") }}',
                        method: 'POST',
                        data: {
                            countryCode: countryCode,
                            partyType: partyType,
                            licenseNo: licenseNo,
                            _token: '{{ csrf_token() }}'
                        },
                        success: function (response) {
                            if (response.isDuplicate === 1) {
                                $('#licenseError').text('License number is already in use: ' + response.remark).show();
                                $('#licenceTypeValue').addClass('is-invalid');
                                $('#submitBtn').prop('disabled', true);
                            } else {
                                $('#licenseError').hide();
                                $('#licenceTypeValue').removeClass('is-invalid');
                                $('#submitBtn').prop('disabled', false);
                            }
                        },
                        error: function (xhr) {
                            $('#licenseError').text('Error checking license number.').show();
                            $('#licenceTypeValue').addClass('is-invalid');
                        }
                    });
                } else {
                    $('#licenseError').hide();
                    $('#licenceTypeValue').removeClass('is-invalid');
                    $('#submitBtn').prop('disabled', false);
                }
            });

            // File attachment logic
            let init = 1;
            const max_fields = 3;
            $(".addAttachment").click(function (e) {
                e.preventDefault();
                e.stopPropagation();
                const lsthmtl = $(".clone-template").html();
                if (init < max_fields) {
                    if (init > 1) {
                        const $names = $('input[name="attachmentNames[]"]');
                        const $desc = $('input[name="attachmentDescription[]"]');
                        const $fileInputName = $names.last();
                        const $fileInputDescription = $desc.last();
                        const allowedExtensions = /(\.jpg|\.jpeg|\.png|\.gif|\.pdf|\.doc|\.xls|\.docx|\.xlsx)$/i;
                        const fileValue = $fileInputName.val();
                        const descValue = $fileInputDescription.val().trim();
                        if (fileValue) {
                            if (!allowedExtensions.exec(fileValue)) {
                                $fileInputName.addClass('is-invalid');
                                alert('Invalid file type. Allowed types: jpg, jpeg, png, gif, pdf, doc, xls, docx, xlsx');
                                $fileInputName.val('');
                                return false;
                            }
                            if (!descValue) {
                                $fileInputDescription.addClass('is-invalid');
                                alert('Please enter a description.');
                                return false;
                            }
                            $fileInputName.removeClass('is-invalid');
                            $fileInputDescription.removeClass('is-invalid');
                        }
                        $("#attachClone").append(lsthmtl);
                        init++;
                        console.log('Added attachment field. Total:', init);
                    } else {
                        $("#attachClone").append(lsthmtl);
                        init++;
                        console.log('Added attachment field. Total:', init);
                    }
                } else {
                    alert('Maximum File Attachment Reached.');
                }
            });

            $("body").on("click", ".remove-attachment", function () {
                $(this).parents(".cloned-attachment").remove();
                init--;
                console.log('Removed attachment field. Total:', init);
            });

            // Admin details autocomplete
            const inputs = ['clientInput', 'shipperInput', 'consigneeInput', 'debtor', 'vendor', 'otherInput'];
            inputs.forEach(id => {
                const $input = $(`#${id}`);
                if (!$input.length) return;
                const $codeCell = $(`#${id.split('Input')[0]}_Input, #${id}_code`);
                let debounceTimeout;

                $input.on('input', function () {
                    clearTimeout(debounceTimeout);
                    debounceTimeout = setTimeout(() => {
                        const query = $(this).val().trim();
                        const $autocompleteList = $('<div class="autocomplete-list"></div>');
                        $input.parent().find('.autocomplete-list').remove();
                        $input.parent().append($autocompleteList);

                        if (!query) {
                            $autocompleteList.empty();
                            return;
                        }

                        $.ajax({
                            url: '/api/entities',
                            type: 'GET',
                            data: { q: query },
                            success: function (matches) {
                                $autocompleteList.empty();
                                matches.forEach(entity => {
                                    const $div = $('<div class="autocomplete-item"></div>')
                                        .text(`${entity.name} (${entity.code})`)
                                        .data('entity', entity);
                                    $autocompleteList.append($div);
                                });
                            },
                            error: function (xhr, status, error) {
                                console.error('Autocomplete error:', status, error);
                                $autocompleteList.html('<div>Error loading suggestions</div>');
                            }
                        });
                    }, 300);
                });

                $input.parent().on('click', '.autocomplete-item', function () {
                    const entity = $(this).data('entity');
                    $input.val(entity.name).removeClass('is-invalid');
                    $(`#${id.replace('Input', 'ID')}`).val(entity.value);
                    $(`#${id.replace('Input', 'Code')}`).val(entity.code);
                    if ($codeCell.length) $codeCell.text(entity.code);
                    $input.parent().find('.autocomplete-list').remove();
                });

                $(document).on('click', function (e) {
                    if (!$(e.target).closest(`#${id}, .autocomplete-list`).length) {
                        $input.parent().find('.autocomplete-list').remove();
                    }
                });
            });

            // Other input toggle
            function toggleOtherInput() {
                const isOtherChecked = $('#otherCheck').is(':checked');
                const $otherInput = $('#otherInput');
                const $otherID = $('#otherID');
                const $otherCode = $('#otherCode');
                const $otherInputDisplay = $('#other_Input');

                if (isOtherChecked) {
                    $otherInput.attr('required', 'required').val('').off('input').prop('readonly', false);
                    $otherID.val('');
                    $otherCode.val('');
                    $otherInputDisplay.text('');
                } else {
                    $otherInput.attr('required', 'required').val('').prop('readonly', false);

                    let debounceTimeout;
                    $otherInput.on('input', function () {
                        clearTimeout(debounceTimeout);
                        debounceTimeout = setTimeout(() => {
                            const query = $(this).val().trim();
                            const $autocompleteList = $('<div class="autocomplete-list"></div>');
                            $otherInput.parent().find('.autocomplete-list').remove();
                            $otherInput.parent().append($autocompleteList);

                            if (!query) {
                                $autocompleteList.empty();
                                return;
                            }

                            $.ajax({
                                url: '/api/entities',
                                type: 'GET',
                                data: { q: query },
                                success: function (matches) {
                                    $autocompleteList.empty();
                                    matches.forEach(entity => {
                                        const $div = $('<div class="autocomplete-item"></div>')
                                            .text(`${entity.name} (${entity.code})`)
                                            .data('entity', entity);
                                        $autocompleteList.append($div);
                                    });
                                },
                                error: function (xhr, status, error) {
                                    console.error('Autocomplete error:', status, error);
                                    $autocompleteList.html('<div>Error loading suggestions</div>');
                                }
                            });
                        }, 300);
                    });

                    $otherInput.parent().on('click', '.autocomplete-item', function () {
                        const entity = $(this).data('entity');
                        $otherInput.val(entity.name).removeClass('is-invalid');
                        $otherID.val(entity.value);
                        $otherCode.val(entity.code);
                        $otherInputDisplay.text(entity.code);
                        $otherInput.parent().find('.autocomplete-list').remove();
                    });
                }
            }

            toggleOtherInput();
            $('#otherCheck').on('change', toggleOtherInput);

            $('#otherInput').on('change', function () {
                const isOtherChecked = $('#otherCheck').is(':checked');
                const $otherInput = $('#otherInput');
                const $otherID = $('#otherID');

                if (!isOtherChecked && !$otherID.val()) {
                    $otherInput.addClass('is-invalid');
                    console.log('Validation failed: No valid autocomplete selection');
                } else if (isOtherChecked && !$otherInput.val().trim()) {
                    $otherInput.addClass('is-invalid');
                    console.log('Validation failed: Empty free-text input');
                } else {
                    $otherInput.removeClass('is-invalid');
                }
            });

            // Show/hide fields based on request type
            if (requestType === "operational") {
                partyEntityType.closest(".col-md-6").show();
                legalEntityType.closest(".col-md-6").hide();
                $("#debtor-vendor").hide();
            } else if (requestType === "accounting") {
                partyEntityType.closest(".col-md-6").show();
                legalEntityType.closest(".col-md-6").show();
            }

            // Form submission logging
            $('form').on('submit', function () {
                console.log('Form submitting:', {
                    partyEntityType: partyEntityType.val(),
                    companyName: companyNameInput.val(),
                    companyId: companyIdInput.val(),
                    companyCode: companyCodeInput.val(),
                    otherCheck: $('#otherCheck').is(':checked'),
                    otherInput: $('#otherInput').val(),
                    otherID: $('#otherID').val(),
                    otherCode: $('#otherCode').val()
                });
            });

            // Initialize on page load
            toggleCompanyInput();
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
        /** Get user authorization status */
        window.User = {
            value: '{{ auth()->user()->can('edit-client-party') }}'
        }
    </script>
    <script src="{{ asset('js/client-party/show-create-client-party.js') }}"></script>
@stop
