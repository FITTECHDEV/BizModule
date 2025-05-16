@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')

@stop

@section('content')
    <div class="row">
        <div class="preloader">
            <div class="preloader-inner">
                <div class="preloader-icon">
                    <span></span>
                    <span></span>
                </div>
            </div>
        </div>
        <div class="col-md-12">
            <div class="card card-grey mt-2">
                <div class="card-body p-0">
                    <div class="bs-stepper linear">
                        <div class="row justify-content-center p-0">
                            <div class="bs-stepper-header col-md-10" role="tablist">
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
                                            id="office-details-trigger" aria-selected="false">
                                        <span class="bs-stepper-circle">2</span>
                                        <span class="bs-stepper-label">Office Details</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="bs-stepper-content container">
                            <form action="{{ route('client-party.update', $clientParty->Id) }}" method="POST"
                                  class="form-horizontal" enctype="multipart/form-data" id="clientPartyForm">
                                @csrf
                                @method('PUT')

                                <div id="basic-details" class="content active dstepper-block" role="tabpanel"
                                     aria-labelledby="basic-details-trigger">
                                    <fieldset class="border pl-2 pr-2 card shadow-sm m-0" style="border-radius: 10px;">
                                        <legend class="w-auto">Entity Creation Options</legend>
                                        <div class="row">
                                            <div class="form-group col-md-6 col-lg-6">
                                                <div class="row pr-4 pl-5">
                                                    <label for="partyEntityType"
                                                           class="col-sm-3 col-lg-4 col-md-4 col-form-label col-form-label-sm">
                                                        Operation Type<span class="text-danger">*</span></label>
                                                    <select class="col-sm-9 col-lg-8 col-md-8 form-control form-control-sm pr-3 pl-3"
                                                            id="partyEntityType" name="partyEntityType" required
                                                            {{ $clientParty->Status === 'PendingManagerApproval' ? '' : 'disabled' }}>
                                                        <option value="" disabled {{ old('partyEntityType', $clientParty->OperatioEntityType) ? '' : 'selected' }}></option>
                                                        <option value="Client + Shipper + Consignee"
                                                                {{ old('partyEntityType', $clientParty->OperatioEntityType) == 'Client + Shipper + Consignee' ? 'selected' : '' }}>
                                                            Client + Shipper + Consignee</option>
                                                        <option value="Shipper + Consignee"
                                                                {{ old('partyEntityType', $clientParty->OperatioEntityType) == 'Shipper + Consignee' ? 'selected' : '' }}>
                                                            Shipper + Consignee</option>
                                                        <option value="Shipper Only"
                                                                {{ old('partyEntityType', $clientParty->OperatioEntityType) == 'Shipper Only' ? 'selected' : '' }}>
                                                            Shipper Only</option>
                                                        <option value="Consignee Only"
                                                                {{ old('partyEntityType', $clientParty->OperatioEntityType) == 'Consignee Only' ? 'selected' : '' }}>
                                                            Consignee Only</option>
                                                        <option value="Agent / Forwarder"
                                                                {{ old('partyEntityType', $clientParty->OperatioEntityType) == 'Agent / Forwarder' ? 'selected' : '' }}>
                                                            Agent / Forwarder</option>
                                                        <option value="Airline"
                                                                {{ old('partyEntityType', $clientParty->OperatioEntityType) == 'Airline' ? 'selected' : '' }}>
                                                            Airline</option>
                                                        <option value="Shipplingline"
                                                                {{ old('partyEntityType', $clientParty->OperatioEntityType) == 'Shipplingline' ? 'selected' : '' }}>
                                                            Shipplingline</option>
                                                        <option value="Trucker / Transportor"
                                                                {{ old('partyEntityType', $clientParty->OperatioEntityType) == 'Trucker / Transportor' ? 'selected' : '' }}>
                                                            Trucker / Transporter</option>
                                                        <option value="NoSCMIntergration"
                                                                {{ old('partyEntityType', $clientParty->OperatioEntityType) == 'NoSCMIntergration' ? 'selected' : '' }}>
                                                            Debtor/Vendor In D365</option>
                                                    </select>
                                                    @error('partyEntityType')
                                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="form-group col-md-6 col-lg-6">
                                                <div class="row pr-3 pl-2">
                                                    <label for="legalEntityType"
                                                           class="col-sm-3 col-lg-4 col-form-label col-form-label-sm">
                                                        Legal Type <span class="text-danger">*</span></label>
                                                    <div class="col-sm-9 col-lg-8">
                                                        <select class="form-control form-control-sm" id="legalEntityType"
                                                                name="legalEntityType" required
                                                                {{ $clientParty->Status === 'PendingManagerApproval' ? '' : 'disabled' }}>
                                                            <option value="" disabled {{ old('legalEntityType', $clientParty->LegalEntityType) ? '' : 'selected' }}></option>
                                                            <option value="Debtor & Vendor"
                                                                    {{ old('legalEntityType', $clientParty->LegalEntityType) == 'Debtor & Vendor' ? 'selected' : '' }}>
                                                                Debtor & Vendor</option>
                                                            <option value="Debtor"
                                                                    {{ old('legalEntityType', $clientParty->LegalEntityType) == 'Debtor' ? 'selected' : '' }}>
                                                                Debtor</option>
                                                            <option value="Vendor"
                                                                    {{ old('legalEntityType', $clientParty->LegalEntityType) == 'Vendor' ? 'selected' : '' }}>
                                                                Vendor</option>
                                                        </select>
                                                        @error('legalEntityType')
                                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </fieldset>
                                    <div class="row">
                                        <div class="col-md-6 col-sm-12">
                                            <fieldset class="border p-2">
                                                <legend class="w-auto">Basic Information</legend>
                                                <div class="form-group row">
                                                    <label for="companyName"
                                                           class="col-sm-3 col-lg-4 col-md-4 col-form-label col-form-label-sm">
                                                        <i class="fa fa-building" aria-hidden="true"></i> Company Name
                                                        <span class="text-danger">*</span></label>
                                                    <div class="col-sm-9 col-lg-8">
                                                        <input type="text" class="form-control form-control-sm basic-detail-field"
                                                               value="{{ old('companyName', $clientParty->Name) }}"
                                                               name="companyName" id="companyName" required
                                                               {{ $clientParty->Status === 'PendingManagerApproval' ? '' : 'readonly' }}>
                                                        @error('companyName')
                                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <label for="addressLine1"
                                                           class="col-sm-3 col-lg-4 col-form-label col-form-label-sm">
                                                        <i class="fa fa-map-marker" aria-hidden="true"></i> Address Line 1
                                                        <span class="text-danger">*</span></label>
                                                    <div class="col-sm-9 col-lg-8">
                                                        <textarea name="addressLine1" id="addressLine1"
                                                                  class="form-control form-control-sm basic-detail-field" required
                                                                  {{ $clientParty->Status === 'PendingManagerApproval' ? '' : 'readonly' }}>{{ old('addressLine1', $clientParty->Address_Line1) }}</textarea>
                                                        @error('addressLine1')
                                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <label for="addressLine2"
                                                           class="col-sm-3 col-lg-4 col-form-label col-form-label-sm">
                                                        <i class="fa fa-map-marker" aria-hidden="true"></i> Address Line 2</label>
                                                    <div class="col-sm-9 col-lg-8">
                                                        <textarea name="addressLine2" id="addressLine2"
                                                                  class="form-control form-control-sm basic-detail-field"
                                                                  {{ $clientParty->Status === 'PendingManagerApproval' ? '' : 'readonly' }}>{{ old('addressLine2', $clientParty->Address_Line2) }}</textarea>
                                                        @error('addressLine2')
                                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <label for="countryID"
                                                           class="col-sm-3 col-lg-4 col-form-label col-form-label-sm">
                                                        <i class="fa fa-globe" aria-hidden="true"></i> Country
                                                        <span class="text-danger">*</span></label>
                                                    <div class="col-sm-8 col-lg-8">
                                                        <select class="form-control form-control-sm" name="countryID" id="countryID" required
                                                                {{ $clientParty->Status === 'PendingManagerApproval' ? '' : 'disabled' }}>
                                                            <option value="" {{ old('countryID', $clientParty->Address_CountryId) ? '' : 'selected' }}>Select Country</option>
                                                            @foreach ($countries as $country)
                                                                <option value="{{ $country->CountryID }}" id="{{ $country->CountCode }}"
                                                                        {{ old('countryID', $clientParty->Address_CountryId) == $country->CountryID ? 'selected' : '' }}>
                                                                    {{ $country->CountryName }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        <input type="hidden" name="countryName" id="countryName"
                                                               value="{{ old('countryName', $clientParty->Address_CountryName) }}">
                                                        <input type="hidden" name="countryCode" id="countryCode"
                                                               value="{{ old('countryCode', $clientParty->Address_CountryCode) }}">
                                                        @error('countryID')
                                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <label for="province"
                                                           class="col-sm-3 col-lg-4 col-form-label col-form-label-sm">
                                                        <i class="fa fa-map-pin" aria-hidden="true"></i> Province & Zipcode</label>
                                                    <div class="col-sm-4 col-lg-4">
                                                        <input type="text" class="form-control form-control-sm basic-detail-field"
                                                               name="province" id="provinceInput"
                                                               value="{{ old('province', $clientParty->Address_State) }}"
                                                               placeholder="Province"
                                                               {{ $clientParty->Status === 'PendingManagerApproval' ? '' : 'readonly' }}>
                                                        <select class="form-control form-control-sm" name="province" id="provinceSelect"
                                                                {{ $clientParty->Status === 'PendingManagerApproval' ? '' : 'disabled' }}>
                                                            <option value="" disabled selected>Enter Province</option>
                                                        </select>
                                                        @error('province')
                                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                    <div class="col-sm-4 col-lg-4">
                                                        <input type="text" class="form-control form-control-sm number_input basic-detail-field"
                                                               value="{{ old('zipCode', $clientParty->Address_Pin) }}"
                                                               name="zipCode" id="zipCode" placeholder="Zip Code"
                                                               {{ $clientParty->Status === 'PendingManagerApproval' ? '' : 'readonly' }}>
                                                        @error('zipCode')
                                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <label for="cityName"
                                                           class="col-sm-3 col-lg-4 col-form-label col-form-label-sm">
                                                        <i class="fas fa-city"></i> City</label>
                                                    <div class="col-sm-9 col-lg-8">
                                                        <input type="text" class="form-control form-control-sm basic-detail-field"
                                                               value="{{ old('cityName', $clientParty->Address_City) }}"
                                                               name="cityName" id="cityInput"
                                                               {{ $clientParty->Status === 'PendingManagerApproval' ? '' : 'readonly' }}>
                                                        <select class="form-control form-control-sm" name="cityID" id="citySelect"
                                                                {{ $clientParty->Status === 'PendingManagerApproval' ? '' : 'disabled' }}>
                                                            <option value="" disabled selected>Select City</option>
                                                        </select>
                                                        <input type="hidden" name="cityCode" id="cityCode"
                                                               value="{{ old('cityCode') }}">
                                                        @error('cityName')
                                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </fieldset>
                                        </div>
                                        <div class="col-md-6 col-sm-12">
                                            <fieldset class="border p-2">
                                                <legend class="w-auto">Contact Details</legend>
                                                <div class="form-group row">
                                                    <label for="firstName"
                                                           class="col-sm-3 col-md-4 col-lg-4 col-form-label col-form-label-sm">
                                                        <i class="fa fa-user" aria-hidden="true"></i> Person
                                                        <span class="text-danger">*</span></label>
                                                    <div class="col-sm-4 col-md-4 col-lg-4">
                                                        <input type="text" class="form-control form-control-sm basic-detail-field"
                                                               value="{{ old('firstName', $clientParty->Cont_Bch_PersonName_FirstName) }}"
                                                               name="firstName" id="firstName" placeholder="First Name" required
                                                               {{ $clientParty->Status === 'PendingManagerApproval' ? '' : 'readonly' }}>
                                                        @error('firstName')
                                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                    <div class="col-sm-5 col-md-4 col-lg-4">
                                                        <input type="text" class="form-control form-control-sm basic-detail-field"
                                                               value="{{ old('lastName', $clientParty->Cont_Bch_PersonName_LastName) }}"
                                                               name="lastName" id="lastName" placeholder="Last Name" required
                                                               {{ $clientParty->Status === 'PendingManagerApproval' ? '' : 'readonly' }}>
                                                        @error('lastName')
                                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <label for="department"
                                                           class="col-sm-3 col-lg-4 col-form-label col-form-label-sm">
                                                        <i class="fa fa-sitemap" aria-hidden="true"></i> Department</label>
                                                    <div class="col-sm-9 col-lg-8">
                                                        <input type="text" class="form-control form-control-sm basic-detail-field"
                                                               value="{{ old('department', $clientParty->Cont_Bch_Dept) }}"
                                                               name="department" id="department" placeholder="Department"
                                                               {{ $clientParty->Status === 'PendingManagerApproval' ? '' : 'readonly' }}>
                                                        @error('department')
                                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <label for="phoneNo"
                                                           class="col-sm-3 col-md-4 col-lg-4 col-form-label col-form-label-sm">
                                                        <i class="fa fa-phone" aria-hidden="true"></i> Phone No.
                                                        <span class="text-danger">*</span></label>
                                                    <div class="col-sm-9 col-md-8 col-lg-8">
                                                        <input type="text" class="form-control form-control-sm number_input basic-detail-field"
                                                               value="{{ old('phoneNo', $clientParty->Cont_Bch_PhoneNumbers) }}"
                                                               name="phoneNo" id="phoneNo" placeholder="Phone Number" required
                                                               {{ $clientParty->Status === 'PendingManagerApproval' ? '' : 'readonly' }}>
                                                        @error('phoneNo')
                                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <label for="contractEmail"
                                                           class="col-sm-3 col-lg-4 col-form-label col-form-label-sm">
                                                        <i class="fa fa-envelope" aria-hidden="true"></i> Email</label>
                                                    <div class="col-sm-9 col-lg-8">
                                                        <input type="email" name="contractEmail" id="contractEmail"
                                                               value="{{ old('contractEmail', $clientParty->Cont_Bch_Email) }}"
                                                               class="form-control form-control-sm basic-detail-field"
                                                               placeholder="Enter Email"
                                                               {{ $clientParty->Status === 'PendingManagerApproval' ? '' : 'readonly' }}>
                                                        @error('contractEmail')
                                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </fieldset>
                                            <fieldset class="border pl-2 pr-2">
                                                <legend class="w-auto">Other Information</legend>
                                                <div class="form-group row">
                                                    <label for="client"
                                                           class="col-sm-3 col-lg-4 col-form-label col-form-label-sm">
                                                        <i class="fa fa-user" aria-hidden="true"></i> Client
                                                        <span class="text-danger" id="clientSpan">*</span></label>
                                                    <div class="col-sm-9 col-lg-8">
                                                        <input type="text" name="client" id="client"
                                                               value="{{ old('client') }}"
                                                               class="form-control form-control-sm" placeholder="" disabled>
                                                        @error('client')
                                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <label for="specialInstruction"
                                                           class="col-sm-3 col-lg-4 col-form-label col-form-label-sm">
                                                        <i class="fas fa-comments"></i> Special Instructions</label>
                                                    <div class="col-sm-9 col-lg-8">
                                                        <textarea class="form-control form-control-sm basic-detail-field"
                                                                  name="specialInstruction" id="specialInstruction"
                                                                  placeholder="Special Instructions"
                                                                  {{ $clientParty->Status === 'PendingManagerApproval' ? '' : 'readonly' }}>{{ old('specialInstruction', $clientParty->SpecialInstructions) }}</textarea>
                                                        @error('specialInstruction')
                                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </fieldset>
                                        </div>
                                    </div>
                                    <button class="btn btn-primary mt-2 float-right" name="next" type="button"
                                            onclick="stepper.next()">Next <i class="fa fa-step-forward" aria-hidden="true"></i></button>
                                </div>
                                <div id="office-details" class="content" role="tabpanel" aria-labelledby="office-details-trigger">
                                    <!-- Office Details (unchanged for brevity, apply similar readonly logic) -->
                                    <div class="row">
                                        <div class="col-md-4 col-lg-4 col-sm-12">
                                            <fieldset class="border p-2">
                                                <legend class="w-auto">Office Mapping</legend>
                                                <div class="form-group row">
                                                    <div class="col-md-12 col-sm-12">
                                                        <label for="officeCountry" class="col-form-label col-form-label-sm">
                                                            Office <span class="text-danger">*</span></label>
                                                        <select class="form-control form-control-sm" name="officeCountry" id="officeCountry" required
                                                                {{ $clientParty->Status === 'PendingManagerApproval' ? '' : 'disabled' }}>
                                                            <option value="" disabled {{ old('officeCountry', $clientParty->Office) ? '' : 'selected' }}></option>
                                                            @foreach ($offices as $office)
                                                                <option value="{{ $office->Name }}"
                                                                        {{ old('officeCountry', $clientParty->Office) == $office->Name ? 'selected' : '' }}>
                                                                    {{ $office->Name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        @error('officeCountry')
                                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </fieldset>
                                            <!-- Other Office Details fields (apply readonly as needed) -->
                                        </div>
                                        <div class="col-md-8 col-lg-8 col-sm-12">
                                            <!-- Client, Debtor/Vendor, Additional Information (apply readonly as needed) -->
                                        </div>
                                    </div>
                                    <button class="btn btn-primary mt-2" type="button" name="previous"
                                            onclick="stepper.previous()"><i class="fa fa-step-backward" aria-hidden="true"></i> Previous</button>
                                    <button type="submit" name="createBtn" class="btn btn-primary float-right mt-2">
                                        <i class="fa fa-check" aria-hidden="true"></i> Approve and Submit</button>
                                    <button type="submit" name="submitBtn" class="btn btn-success float-right mt-2 mr-2">
                                        <i class="fa fa-edit" aria-hidden="true"></i> Update</button>
                                </div>
                                <input type="hidden" id="companyId" name="companyId"
                                       value="{{ old('companyId', $clientParty->ClientID ?? '') }}">
                            </form>
                        </div>
                    </div>
                </div>
                <div class="card-footer"></div>
            </div>
        </div>
    </div>

    <script>
        window.companyDetailsRoute = '{{ route('company.details') }}';
    </script>
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
    <script src="//rawgithub.com/indrimuska/jquery-editable-select/master/dist/jquery-editable-select.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bs-stepper/dist/js/bs-stepper.min.js"></script>
    <script src="{{ asset('js/client-party/party-entity.js') }}"></script>
    <script src="{{ asset('js/client-party/validation.js') }}"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        window.User = {
            value: '{{ auth()->user()->can('update-client-party') }}'
        };

        $(function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            const partyEntityType = $('#partyEntityType');
            const companyIdInput = $('#companyId');
            const firstNameInput = $('#firstName');
            const lastNameInput = $('#lastName');

            // Permissions
            if (window.User.value != 1) {
                $("#clientPartyForm :input").not("[name=next], [name=previous]").prop("disabled", true);
            } else if ('{{ $clientParty->Status }}' === 'PendingManagerApproval') {
                $('.basic-detail-field').prop('readonly', false);
                $('select').prop('disabled', false);
            }

            // Form validation
            function validateForm() {
                const addressLine1 = $('#addressLine1').val().trim();
                const countryCode = $('#countryCode').val().trim();
                const partyEntityTypeVal = partyEntityType.val();
                const firstName = firstNameInput.val().trim();
                const lastName = lastNameInput.val().trim();
                const phoneNo = $('#phoneNo').val().trim();
                let errors = [];

                if (!addressLine1) errors.push('Address Line 1 is required.');
                if (!countryCode) errors.push('Country is required.');
                if (!partyEntityTypeVal) errors.push('Operation Type is required.');
                if (!firstName) errors.push('First Name is required.');
                if (!lastName) errors.push('Last Name is required.');
                if (!phoneNo) errors.push('Phone Number is required.');

                if (errors.length > 0) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Validation Error',
                        text: errors.join('\n')
                    });
                    if (!addressLine1) $('#addressLine1').addClass('is-invalid');
                    if (!countryCode) $('#countryID').addClass('is-invalid');
                    if (!partyEntityTypeVal) $('#partyEntityType').addClass('is-invalid');
                    if (!firstName) firstNameInput.addClass('is-invalid');
                    if (!lastName) lastNameInput.addClass('is-invalid');
                    if (!phoneNo) $('#phoneNo').addClass('is-invalid');
                    console.log('Form validation failed:', {
                        addressLine1,
                        countryCode,
                        partyEntityType: partyEntityTypeVal,
                        firstName,
                        lastName,
                        phoneNo
                    });
                    return false;
                }

                $('#addressLine1, #countryID, #partyEntityType, #firstName, #lastName, #phoneNo').removeClass('is-invalid');
                return $('#clientPartyForm')[0].checkValidity();
            }

            $('button[name="createBtn"], button[name="submitBtn"]').on('click', function(e) {
                if (!validateForm()) {
                    e.preventDefault();
                }
            });

            // Country dropdown
            $.ajax({
                url: '/get-countries',
                type: 'get',
                data: { query: '' },
                dataType: 'json',
                success: function(response) {
                    $('#countryID option:not(:first)').remove();
                    response.forEach(function(item) {
                        $("#countryID").append(
                            `<option value="${item.CountryID}" id="${item.CountCode}">${item.CountryName}</option>`
                        );
                    });
                    // Re-select saved value
                    $('#countryID').val('{{ old('countryID', $clientParty->Address_CountryId) }}');
                },
                error: function(error) {
                    console.error('Error fetching countries:', error);
                }
            });

            $('#countryID').on('change', function() {
                var countryID = $(this).val();
                var countryName = $(this).find('option:selected').text();
                var countryCode = $(this).find('option:selected').attr('id');
                $('#countryName').val(countryName);
                $('#countryCode').val(countryCode);
                searchCity(countryCode);
                searchProvince(countryCode);
            });

            function searchCity(CountryCode) {
                $.ajax({
                    url: '/get-cities',
                    type: 'get',
                    data: { query: CountryCode },
                    dataType: 'json',
                    success: function(response) {
                        $('#citySelect option:not(:first)').remove();
                        if (response.length < 1) {
                            $('#cityInput').show();
                            $('#citySelect').hide();
                        } else {
                            $('#cityInput').hide();
                            $('#citySelect').show();
                            response.forEach(function(item) {
                                $("#citySelect").append(
                                    `<option value="${item.CityID}" id="${item.CityCode}">${item.CityName}</option>`
                                );
                            });
                        }
                    },
                    error: function(error) {
                        console.error('Error fetching cities:', error);
                    }
                });
            }

            $('#citySelect').on('change', function() {
                var cityName = $(this).find('option:selected').text();
                var cityCode = $(this).find('option:selected').attr('id');
                $('#cityName').val(cityName);
                $('#cityCode').val(cityCode);
            });

            function searchProvince(CountryCode) {
                $.ajax({
                    url: '/get-provinces',
                    type: 'post',
                    data: { query: CountryCode },
                    dataType: 'json',
                    success: function(response) {
                        $('#provinceSelect option:not(:first)').remove();
                        if (response.length < 1) {
                            $('#provinceInput').show();
                            $('#provinceSelect').hide();
                        } else {
                            $('#provinceSelect').show();
                            $('#provinceInput').hide();
                            response.forEach(function(item) {
                                $("#provinceSelect").append(
                                    `<option value="${item.ProvinceID}" id="${item.ProvinceCode}">${item.ProvinceName}</option>`
                                );
                            });
                        }
                    },
                    error: function(error) {
                        console.error('Error fetching provinces:', error);
                    }
                });
            }

            // Office handling
            $("#officeCountry").on('change', function() {
                var office = $(this).val();
                if (office == 'Kenya - UPS' && window.User.value == 1) {
                    $('#tarrif, #fscType, #fscRate, #billingType, #discount').removeAttr('disabled');
                } else {
                    $('#tarrif, #fscType, #fscRate, #billingType, #discount').val('').attr('disabled', 'true');
                }
            });

            if ('{{ $clientParty->Office }}' == 'Kenya - UPS' && window.User.value == 1) {
                $('#tarrif, #fscType, #fscRate, #billingType, #discount').removeAttr('disabled');
            }

            // Client field logic
            const entities = ['Client + Shipper + Consignee', 'Shipper + Consignee', 'Shipper Only', 'Consignee Only'];
            if (entities.includes(partyEntityType.val())) {
                $('#client').attr('required', 'true').removeAttr('disabled');
                $('#clientSpan').addClass('text-danger');
            }

            partyEntityType.on('change', function() {
                if (entities.includes($(this).val())) {
                    $('#client').attr('required', 'true').removeAttr('disabled');
                    $('#clientSpan').addClass('text-danger');
                } else {
                    $('#client').val('').attr('disabled', 'true').removeClass('is-invalid');
                    $('#clientSpan').removeClass('text-danger');
                }
            });

            // Number input validation
            $('.number_input').on('keydown keyup', function(e) {
                var value = $(this).val();
                var regExp = /[a-zA-Z]/;
                if (regExp.test(value)) {
                    $(this).val(value.replace(regExp, ''));
                }
            });

            // BS-Stepper
            window.stepper = new Stepper($('.bs-stepper')[0], { animation: true, linear: true });
        });
    </script>
@stop
