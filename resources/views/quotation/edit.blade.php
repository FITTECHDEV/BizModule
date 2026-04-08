@extends('adminlte::page')

@section('title', 'Edit Booking Quotation')


@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1><i class="fas fa-file-invoice"></i> Quote No - {{ $quotation->QuoteNo ?? '' }}</h1>
            <p class="text-muted mb-0">Edit booking quotation</p>
        </div>
        <div>
            <a href="{{ route('quotation.index') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
            <a href="{{ route('quotation.view', $quotation->ID) }}" class="btn btn-info btn-sm">
                <i class="fas fa-eye"></i> Back to View
            </a>
        </div>
    </div>
@stop

@php
    $isBookingCreated = !empty($quotation->SCMBookingNo);
@endphp

@section('content')
    <form id="bookingQuotationForm">
        @csrf

        <div class="row">
            <!-- LEFT COLUMN -->
            <div class="col-md-4">

                <!-- Inquiry Details -->
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title">Inquiry Details</h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-chevron-up"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="form-group row">
                            <label class="col-sm-5 col-form-label">Source</label>
                            <div class="col-sm-7">
                                <input type="text" class="form-control form-control-sm"
                                    value="{{ $quotation->source ?? '' }}" readonly>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-5 col-form-label">Status</label>
                            <div class="col-sm-7">
                                <input type="text" class="form-control form-control-sm"
                                    value="{{ $quotation->status ?? 'Proposal' }}" readonly>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-5 col-form-label">Office</label>
                            <div class="col-sm-7">
                                <input type="text" class="form-control form-control-sm"
                                    value="{{ $quotation->teamname ?? '' }}" readonly>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-5 col-form-label">Sales Person</label>
                            <div class="col-sm-7" style="position:relative;">
                                <input type="text" class="form-control form-control-sm" id="salesPersonDisplay"
                                    value="{{ $quotation->username ?? '' }}" readonly
                                    placeholder="Search by name or code...">
                                <input type="hidden" id="salesPersonCode" value="{{ $quotation->SalesPersonCode ?? '' }}">
                                <ul id="salesPersonSuggestions" class="list-group"
                                    style="
                                    display:none; position:absolute; z-index:9999;
                                    width:100%; max-height:200px; overflow-y:auto;
                                    border:1px solid #ced4da; border-radius:0 0 4px 4px;
                                    background:#fff; margin-top:-1px;
                                    box-shadow:0 4px 8px rgba(0,0,0,0.1);">
                                </ul>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-5 col-form-label">Sales Person Email</label>
                            <div class="col-sm-7">
                                <input type="text" class="form-control form-control-sm" id="salesPersonEmail"
                                    value="{{ $quotation->SalesPersonEmail ?? '' }}" readonly>
                            </div>
                        </div>
                        @if (!$isBookingCreated)
                            <div class="row mt-3 pt-2" style="border-top: 1px solid #dee2e6;">
                                <div class="col-md-12 text-right">
                                    <button type="button" class="btn btn-warning btn-sm" id="editInquiryBtn">
                                        <i class="fas fa-edit mr-1"></i> Edit
                                    </button>
                                    <button type="button" class="btn btn-success btn-sm d-none" id="saveInquiryBtn">
                                        <i class="fas fa-save mr-1"></i> Save
                                    </button>
                                    <button type="button" class="btn btn-secondary btn-sm d-none" id="cancelInquiryBtn">
                                        Cancel
                                    </button>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Shipment Details -->
                <div class="card card-primary" id="shipmentDetailsCard">
                    <div class="card-header">
                        <h3 class="card-title">Shipment Details</h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-chevron-up"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="form-group row">
                            <label class="col-sm-5 col-form-label">Service</label>
                            <div class="col-sm-7">
                                <input type="text" class="form-control form-control-sm"
                                    value="{{ $quotation->SVName ?? '' }}" readonly>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-5 col-form-label">Process</label>
                            <div class="col-sm-7">
                                <input type="text" class="form-control form-control-sm"
                                    value="{{ $quotation->process ?? '' }}" readonly>
                            </div>
                        </div>
                        {{-- Origin Country field: --}}
                        <div class="form-group row">
                            <label class="col-sm-5 col-form-label">Origin Country</label>
                            <div class="col-sm-7">
                                <input type="text" class="form-control form-control-sm" id="originCountryDisplay"
                                    value="{{ $quotation->origincountry ?? '' }}" readonly>
                                <div id="originCountryEditWrap" style="display:none;">
                                    {{-- REMOVED: <input type="text" id="originCountrySearch"> --}}
                                    <select class="form-control form-control-sm" id="originCountrySelect">
                                        <option value="">-- Select --</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        {{-- Destination Country field: --}}
                        <div class="form-group row">
                            <label class="col-sm-5 col-form-label">Destination Country</label>
                            <div class="col-sm-7">
                                <input type="text" class="form-control form-control-sm" id="destCountryDisplay"
                                    value="{{ $quotation->destinationcountry ?? '' }}" readonly>
                                <div id="destCountryEditWrap" style="display:none;">
                                    {{-- REMOVED: <input type="text" id="destCountrySearch"> --}}
                                    <select class="form-control form-control-sm" id="destCountrySelect">
                                        <option value="">-- Select --</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        {{-- InCo Terms --}}
                        <div class="form-group row">
                            <label class="col-sm-5 col-form-label">InCO Terms</label>
                            <div class="col-sm-7">
                                <input type="text" class="form-control form-control-sm" id="incoTermDisplay"
                                    value="{{ $quotation->incoterm ?? '' }}" readonly>
                                <select class="form-control form-control-sm d-none" id="incoTermSelect" disabled>
                                    <option value="">-- Select --</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-5 col-form-label">Shipment Mode</label>
                            <div class="col-sm-7">
                                <select class="form-control form-control-sm" id="shipmentModeSelect" name="shipment_mode"
                                    disabled>
                                    <option value="">-- Loading... --</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-5 col-form-label">Transport Mode</label>
                            <div class="col-sm-7">
                                <select class="form-control form-control-sm" id="transportModeSelect"
                                    name="transport_mode" disabled>
                                    <option value="">-- Loading... --</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group row mb-0">
                            <label class="col-sm-5 col-form-label">Service Class</label>
                            <div class="col-sm-7">
                                <input type="text" class="form-control form-control-sm" id="serviceClassDisplay"
                                    value="{{ $quotation->ServiceClass ?? '' }}" readonly>
                                <select class="form-control form-control-sm d-none" id="serviceClassSelect" disabled>
                                    <option value="">-- Select --</option>
                                </select>
                                {{-- keep this hidden field for the save payload --}}
                                <input type="hidden" id="serviceClassCode"
                                    value="{{ $quotation->ServiceClassCode ?? ($quotation->ServiceClass ?? '') }}">
                            </div>
                        </div>

                        <div class="row mt-3 pt-2" style="border-top: 1px solid #dee2e6;">
                            <div class="col-md-12 text-right">
                                @if (!$isBookingCreated)
                                    <button type="button" class="btn btn-warning btn-sm" id="editShipmentDetailsBtn">
                                        <i class="fas fa-edit mr-1"></i> Edit
                                    </button>
                                @endif
                                <button type="button" class="btn btn-primary btn-sm" id="saveShipmentDetailsBtn"
                                    style="display:none">
                                    <i class="fas fa-save mr-1"></i> Save
                                </button>
                                <button type="button" class="btn btn-secondary btn-sm" id="cancelShipmentDetailsBtn"
                                    style="display:none">
                                    <i class="fas fa-times mr-1"></i> Cancel
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Cargo Details -->
                <div class="card card-primary" id="cargoDetailsCard">
                    <div class="card-header">
                        <h3 class="card-title">Cargo Details</h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-chevron-up"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="form-group row">
                            <label class="col-sm-5 col-form-label">Commodity</label>
                            <div class="col-sm-7">
                                <input type="text" class="form-control form-control-sm" id="commodityInput"
                                    value="{{ $quotation->commodity ?? '' }}" readonly>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-5 col-form-label">Scope Of Volume <span
                                    class="text-danger customer-mandatory-star" style="display:none;">*</span></label>
                            <div class="col-sm-7">
                                <input type="text" class="form-control form-control-sm" id="scopeVolumeDisplay"
                                    value="{{ $quotation->scopevolume ?? '' }}" readonly>
                                <select class="form-control form-control-sm d-none" id="scopeVolumeSelect" disabled>
                                    <option value="">-- Select --</option>
                                    <option value="Weight (in KGS)"
                                        {{ ($quotation->scopevolume ?? '') === 'Weight (in KGS)' ? 'selected' : '' }}>
                                        Weight (in KGS)</option>
                                    <option value="Volume (In CBM)"
                                        {{ ($quotation->scopevolume ?? '') === 'Volume (In CBM)' ? 'selected' : '' }}>
                                        Volume (In CBM)</option>
                                    <option value="Containerized"
                                        {{ ($quotation->scopevolume ?? '') === 'Containerized' ? 'selected' : '' }}>
                                        Containerized</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-5 col-form-label">Cargo Volume</label>
                            <div class="col-sm-7">
                                <input type="text" class="form-control form-control-sm" id="cargoVolumeInput"
                                    value="{{ $quotation->cargovolume ?? '' }}" readonly>
                            </div>
                        </div>
                        <div class="form-group row mb-0">
                            <label class="col-sm-5 col-form-label">Containers</label>
                            <div class="col-sm-7">
                                <table class="table table-sm table-bordered mb-1" id="containerListTable">
                                    <thead>
                                        <tr>
                                            <th>Container Type</th>
                                            <th style="width:90px;">Quantity</th>
                                            <th style="width:36px;" id="containerActionCol" class="d-none"></th>
                                        </tr>
                                    </thead>
                                    <tbody id="containerListBody">
                                        <tr>
                                            <td colspan="3" class="text-muted text-center">Loading...</td>
                                        </tr>
                                    </tbody>
                                </table>
                                <button type="button" class="btn btn-link btn-sm p-0 d-none" id="addContainerRowBtn">
                                    <i class="fas fa-plus mr-1"></i> Add a line
                                </button>
                            </div>
                        </div>

                        {{-- Edit / Save / Cancel --}}
                        <div class="row mt-3 pt-2" style="border-top: 1px solid #dee2e6;">
                            <div class="col-md-12 text-right">
                                @if (!$isBookingCreated)
                                    <button type="button" class="btn btn-warning btn-sm" id="editCargoDetailsBtn">
                                        <i class="fas fa-edit mr-1"></i> Edit
                                    </button>
                                @endif
                                <button type="button" class="btn btn-primary btn-sm d-none" id="saveCargoDetailsBtn">
                                    <i class="fas fa-save mr-1"></i> Save
                                </button>
                                <button type="button" class="btn btn-secondary btn-sm d-none"
                                    id="cancelCargoDetailsBtn">
                                    <i class="fas fa-times mr-1"></i> Cancel
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quotation Document (with Generate Quote button in title bar + T&C in body) -->
                <div class="card card-primary">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h3 class="card-title mb-0">Quotation Document</h3>
                        <div class="d-flex align-items-center ml-auto">
                            <button type="button" class="btn btn-email-quote btn-sm mr-2" id="generateQuoteBtn">

                                <i class="fas fa-file-alt mr-1"></i> Generate Quote
                            </button>
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-chevron-up"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">

                        {{-- Versions list — populated dynamically by JS --}}
                        <div id="quotationVersionsList">
                            <p class="text-muted text-center mb-2" id="versionsLoadingMsg">
                                <i class="fas fa-spinner fa-spin"></i> Loading versions...
                            </p>
                        </div>

                        <!-- Terms & Conditions -->
                        <div class="mt-3 pt-2" style="border-top: 1px solid #dee2e6;">
                            <div class="form-group row mb-0">
                                <label class="col-sm-4 col-form-label">Terms & Conditions</label>
                                <div class="col-sm-8">

                                    {{-- Multi-template: show dropdown --}}
                                    <div id="termsDropdownWrapper">
                                        <select class="form-control form-control-sm" id="termsTemplateSelect">
                                            <option value="">-- Select Template --</option>
                                        </select>
                                    </div>

                                    {{-- Single-template: read-only label --}}
                                    <div id="termsAutoAppliedBadge" style="display:none;">
                                        <span class="badge badge-success px-2 py-1" style="font-size:0.8rem;">
                                            <i class="fas fa-check-circle mr-1"></i>
                                            <span id="termsAutoAppliedName"></span>
                                        </span>
                                        <small class="text-muted ml-1">Auto-applied</small>
                                    </div>

                                    {{-- Instruction shown after PDF generation (multi-template only) --}}
                                    <div id="termsReSelectMsg" style="display:none; margin-top:5px;">
                                        <small class="text-warning">
                                            <i class="fas fa-exclamation-circle mr-1"></i>
                                            Please re-select a T&C template to generate a new version.
                                        </small>
                                    </div>

                                    {{-- Preview + Apply button --}}
                                    <div id="termsDescriptionWrapper" style="display:none; margin-top: 6px;">
                                        <textarea id="termsDescriptionPreview" class="form-control form-control-sm" rows="4" readonly
                                            style="background-color: #e9ecef; font-size: 0.8rem; resize: none;"></textarea>
                                        <button type="button" class="btn btn-primary btn-sm btn-block mt-2"
                                            id="applyTermsBtn">
                                            <i class="fas fa-save mr-1"></i> Apply Terms
                                        </button>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quote Communication -->
                <div class="card card-primary">
                    <div class="card-header d-flex justify-content-between align-items-center py-2">
                        <h3 class="card-title mb-0">
                            <i class="fas fa-envelope mr-2"></i>Quote Communication
                        </h3>
                        <div class="d-flex align-items-center ml-auto">
                            <button type="button" class="btn btn-email-quote btn-sm mr-2" id="emailQuoteBtn">
                                <i class="fas fa-paper-plane mr-1"></i> Email Quote
                            </button>
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-chevron-up"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm table-hover mb-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Sent On</th>
                                        <th>To Email ID</th>
                                        <th>Quote Version</th>
                                    </tr>
                                </thead>
                                <tbody id="emailHistoryTable">
                                    <tr>
                                        <td colspan="2" class="text-center text-muted">
                                            <i class="fas fa-spinner fa-spin"></i> Loading...
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Integration Details -->
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title">Integration Details</h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-chevron-up"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="form-group row">
                            <label class="col-sm-5 col-form-label">Client Status</label>
                            <div class="col-sm-7">
                                <input type="text" class="form-control form-control-sm"
                                    value="{{ $quotation->ClientIntegration ?? '' }}" readonly>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-5 col-form-label">Booking No</label>
                            <div class="col-sm-7">
                                <input type="text" class="form-control form-control-sm"
                                    value="{{ $quotation->SCMBookingNo ?? '' }}" readonly>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-5 col-form-label">Booking Integration</label>
                            <div class="col-sm-7">
                                <input type="text" class="form-control form-control-sm"
                                    value="{{ $quotation->SCMINTStatus ?? '' }}" readonly>
                            </div>
                        </div>
                        <div class="form-group row mb-0">
                            <label class="col-sm-5 col-form-label">Booking XML</label>
                            <div class="col-sm-7">
                                @if ($quotation->SCMBookingXML)
                                    <button type="button" class="btn btn-sm btn-info" id="viewXmlBtn">
                                        <i class="fas fa-code"></i> View XML
                                    </button>
                                @else
                                    <input type="text" class="form-control form-control-sm" value="" readonly>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

            </div>
            <!-- END LEFT COLUMN -->

            <!-- RIGHT COLUMN -->
            <div class="col-md-8">

                <!-- Customer Details -->
                <div class="card card-primary" id="customerDetailsCard">
                    <div class="card-header">
                        <h3 class="card-title">Customer Details</h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-chevron-up"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">

                        {{-- ── Customer Type & Agent Type — TOP ── --}}
                        <div class="row mb-3 pb-2" style="border-bottom: 1px solid #dee2e6;">
                            <div class="col-md-6">
                                <div class="form-group row mb-0">
                                    <label class="col-sm-4 col-form-label">
                                        Customer Type <span class="text-danger">*</span>
                                    </label>
                                    <div class="col-sm-8">
                                        <select class="form-control form-control-sm" id="customerTypeSelect"
                                            name="customer_type">
                                            <option value="">-- Select --</option>
                                            <option value="Customer"
                                                {{ ($quotation->CustomerType ?? '') === 'Customer' ? 'selected' : '' }}>
                                                Customer
                                            </option>
                                            <option value="Agent"
                                                {{ ($quotation->CustomerType ?? '') === 'Agent' ? 'selected' : '' }}>
                                                Agent
                                            </option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group row mb-0">
                                    <label class="col-sm-4 col-form-label">Agent Type</label>
                                    <div class="col-sm-8">
                                        <select class="form-control form-control-sm" id="agentTypeSelect"
                                            name="agent_type" disabled>
                                            <option value="">-- Loading... --</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- ── Two-column customer fields ── --}}
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group row">
                                    <label class="col-sm-4 col-form-label">Company</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control form-control-sm"
                                            data-field="companyname"
                                            value="{{ $quotation->pri_partnermaster_name ?? ($quotation->companyname ?? '') }}"
                                            readonly>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-4 col-form-label">Address 1 <span
                                            class="text-danger">*</span></label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control form-control-sm"
                                            data-field="pri_partner_steet"
                                            value="{{ $quotation->pri_partner_steet ?? '' }}" readonly>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-4 col-form-label">City</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control form-control-sm" id="customerCityInput"
                                            value="{{ $quotation->pri_partner_city ?? '' }}" readonly>

                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-4 col-form-label">State</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control form-control-sm"
                                            id="customerStateInput" value="{{ $quotation->pri_partner_statename ?? '' }}"
                                            readonly>

                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-4 col-form-label">Contact Person</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control form-control-sm"
                                            id="customerContactInput" value="{{ $quotation->contact_name ?? '' }}"
                                            readonly>

                                    </div>
                                </div>
                                <div class="form-group row mb-0">
                                    <label class="col-sm-4 col-form-label">Phone No</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control form-control-sm"
                                            id="customerPhoneInput" value="{{ $quotation->pri_partner_phone ?? '' }}"
                                            readonly>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group row">
                                    <label class="col-sm-4 col-form-label">Client ID</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control form-control-sm" id="clientIdDisplay"
                                            value="{{ $quotation->clientid ?? '' }}" readonly>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-4 col-form-label">Address 2</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control form-control-sm"
                                            id="customerAddress2Input" value="{{ $quotation->pri_partner_steet2 ?? '' }}"
                                            readonly>

                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-4 col-form-label">Zip</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control form-control-sm" id="customerZipInput"
                                            value="{{ $quotation->pri_partner_zip ?? '' }}" readonly>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-4 col-form-label">
                                        Country <span class="text-danger customer-mandatory-star"
                                            style="display:none;">*</span>
                                    </label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control form-control-sm"
                                            id="customerCountryDisplay"
                                            value="{{ $quotation->pri_partner_countryname ?? '' }}" readonly>
                                        <div id="customerCountryEditWrap" style="display:none;">
                                            <select class="form-control form-control-sm" id="customerCountrySelect">
                                                <option value="">-- Select --</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-4 col-form-label">Email ID</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control form-control-sm"
                                            id="customerEmailInput" value="{{ $quotation->pri_partner_email ?? '' }}"
                                            readonly>
                                    </div>
                                </div>
                                <div class="form-group row mb-0">
                                    <label class="col-sm-4 col-form-label">
                                        Industry <span class="text-danger customer-mandatory-star"
                                            style="display:none;">*</span>
                                    </label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control form-control-sm"
                                            id="customerIndustryDisplay" value="{{ $quotation->industryname ?? '' }}"
                                            readonly>
                                        <select class="form-control form-control-sm" id="customerIndustrySelect"
                                            style="display:none;">
                                            <option value="">-- Select --</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- ── Edit / Save / Cancel ── --}}
                        <div class="row mt-3 pt-2" style="border-top: 1px solid #dee2e6;">
                            <div class="col-md-12 text-right">
                                @if (!$isBookingCreated)
                                    <button type="button" class="btn btn-warning btn-sm" id="editCustomerDetailsBtn">
                                        <i class="fas fa-edit mr-1"></i> Edit
                                    </button>
                                @endif
                                <button type="button" class="btn btn-primary btn-sm" id="saveCustomerDetailsBtn"
                                    style="display:none">
                                    <i class="fas fa-save mr-1"></i> Save
                                </button>
                                <button type="button" class="btn btn-secondary btn-sm" id="cancelCustomerDetailsBtn"
                                    style="display:none">
                                    <i class="fas fa-times mr-1"></i> Cancel
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Revenue Section -->
                <div class="card card-success">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <h3 class="card-title mb-0 mr-1">Revenue</h3>
                            <span class="text-white small ml-2" id="revenueTotalHeader" style="white-space:nowrap;">
                                Total: <strong id="revenueTotalHeaderAmount">0.00</strong>
                            </span>
                        </div>
                        <div class="d-flex align-items-center ml-auto">
                            <a href="#" class="text-white show-items-link" data-table="revenue">
                                <i class="fas fa-columns mr-1"></i> Show more
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm table-hover">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Charge Name</th>
                                        <th>Rate</th>
                                        <th class="col-extra">Quantity</th>
                                        <th class="col-extra">Based On & UOM</th>
                                        <th class="col-extra">Currency</th>
                                        <th>Amount</th>
                                        <th>Base Amount</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="revenueChargesTable">
                                    <tr>
                                        <td colspan="8" class="text-center text-muted">Loading charges...</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Cost Section -->
                <div class="card card-danger">
                    <!-- AFTER -->
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <h3 class="card-title mb-0 mr-1">Cost</h3>
                            <span class="text-white small ml-2" id="costTotalHeader" style="white-space:nowrap;">
                                Total: <strong id="costTotalHeaderAmount">0.00</strong>
                            </span>
                        </div>
                        <div class="d-flex align-items-center ml-auto">
                            <a href="#" class="text-white show-items-link" data-table="cost">
                                <i class="fas fa-columns mr-1"></i> Show More
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm table-hover">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Charge Name</th>
                                        <th>Rate</th>
                                        <th class="col-extra">Quantity</th>
                                        <th class="col-extra">Based On & UOM</th>
                                        <th class="col-extra">Currency</th>
                                        <th>Amount</th>
                                        <th>Base Amount</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="costChargesTable">
                                    <tr>
                                        <td colspan="8" class="text-center text-muted">Loading charges...</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Summary -->
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title">Summary</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group row">
                                    <label class="col-sm-5 col-form-label">Expected Revenue</label>
                                    <div class="col-sm-7">
                                        <input type="text" class="form-control form-control-sm" id="expectedRevenue"
                                            value="0.00" readonly>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-5 col-form-label">Quoted Revenue</label>
                                    <div class="col-sm-7">
                                        <input type="text" class="form-control form-control-sm" id="quotedRevenue"
                                            value="0.00" readonly>
                                    </div>
                                </div>
                                <div class="form-group row mb-0">
                                    <label class="col-sm-5 col-form-label">Actual Revenue</label>
                                    <div class="col-sm-7">
                                        <input type="text" class="form-control form-control-sm" id="actualRevenue"
                                            value="" readonly>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group row">
                                    <label class="col-sm-5 col-form-label">Quoted Margin</label>
                                    <div class="col-sm-7">
                                        <input type="text" class="form-control form-control-sm" id="quotedMargin"
                                            value="0.00" readonly>
                                    </div>
                                </div>
                                <div class="form-group row mb-0">
                                    <label class="col-sm-5 col-form-label">Actual Margin</label>
                                    <div class="col-sm-7">
                                        <input type="text" class="form-control form-control-sm" id="actualMargin"
                                            value="" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-md-12">
                                <button type="button" class="btn btn-primary" id="openChargesModal">
                                    <i class="fas fa-plus"></i> Add Charges
                                </button>

                                <button type="button" class="btn btn-secondary ml-2" id="openCopyChargesBtn">
                                    <i class="fas fa-copy"></i> Copy Charges
                                </button>
                                <a href="{{ route('quotation.index') }}" class="btn btn-link">Close</a>

                                <div class="float-right d-flex align-items-center">
                                    <div class="btn-group" id="quoteActionDropdown"> <button type="button"
                                            class="btn btn-secondary dropdown-toggle" data-toggle="dropdown"
                                            aria-haspopup="true" aria-expanded="false">
                                            <i class="fas fa-ellipsis-v mr-1"></i> Action
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-right">
                                            <a class="dropdown-item" href="#" id="cancelQuoteBtn">
                                                <i class="fas fa-ban mr-2 text-warning"></i> Cancel
                                            </a>
                                            <a class="dropdown-item" href="#" id="lostQuoteBtn">
                                                <i class="fas fa-times mr-2 text-danger"></i> Lost
                                            </a>
                                            <a class="dropdown-item" href="#" id="wonQuoteBtn">
                                                <i class="fas fa-check mr-2 text-success"></i> Won
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
            <!-- END RIGHT COLUMN -->

        </div>
        <!-- END ROW -->

        <!-- ============================================
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                     MODALS
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                     ============================================ -->

        {{-- REVENUE MODAL --}}
        <div class="modal fade" id="addRevenueModal" tabindex="-1" role="dialog"
            aria-labelledby="addRevenueModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="modal-title" id="addRevenueModalLabel">Add Manual Charges</h2>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form id="addRevenueChargesForm">
                            <input type="hidden" id="FITExpressChargeID" name="revenue_charge_id" />

                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="revenueChargeType"
                                            id="revenueTypeRevenue" value="revenue" checked>
                                        <label class="form-check-label" for="revenueTypeRevenue">Revenue</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="revenueChargeType"
                                            id="revenueTypeCost" value="cost">
                                        <label class="form-check-label" for="revenueTypeCost">Cost</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="revenueChargeType"
                                            id="revenueTypeCostMarkup" value="costmarkup">
                                        <label class="form-check-label" for="revenueTypeCostMarkup">Cost with
                                            markup</label>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <p class="text-muted mb-0">Base Currency: <strong
                                            class="office-base-currency">—</strong></p>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="revenue_charge_code" class="form-label">
                                            <span class="text-danger">*</span> Charge Code</label>
                                        <input type="text" class="form-control" id="revenue_charge_code"
                                            name="revenue_charge_code" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="revenue_bill_to_party" class="form-label"
                                            id="revenueBillToPartyLabel">Bill to Party</label>
                                        <input type="text" class="form-control" id="revenue_bill_to_party"
                                            name="revenue_bill_to_party">
                                        <input type="hidden" id="revenue_bill_to_party_code"
                                            name="revenue_bill_to_party_code" />
                                    </div>
                                    <div class="mb-3">
                                        <label for="revenue_based_on" class="form-label">
                                            <span class="text-danger">*</span> Based On</label>
                                        <select class="form-select form-control" id="revenue_based_on"
                                            name="revenue_based_on" required>
                                            <option value="">Select</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="revenue_quantity" class="form-label">Quantity</label>
                                        <input type="number" class="form-control" id="revenue_quantity"
                                            name="revenue_quantity" step="0.01">
                                    </div>
                                    <div class="row mb-3 rate-row">
                                        <div class="col-6">
                                            <label for="revenue_rate" class="form-label">Rate</label>
                                            <div class="input-group">
                                                <input type="number" class="form-control" id="revenue_rate"
                                                    name="revenue_rate" step="0.01">
                                                <select class="form-select form-control" id="revenue_currency"
                                                    name="revenue_currency">
                                                    <option value="">Select</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <label for="revenue_amount" class="form-label">Amount</label>
                                            <input type="number" class="form-control" id="revenue_amount"
                                                name="revenue_amount" step="0.01" value="0.00" readonly>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-6">
                                            <label for="revenue_exchange_rate" class="form-label">
                                                <span class="text-danger">*</span> Exchange Rate</label>
                                            <input type="number" class="form-control" id="revenue_exchange_rate"
                                                name="revenue_exchange_rate" readonly>
                                        </div>
                                        <div class="col-6">
                                            <label for="revenue_base_amount" class="form-label text-nowrap">
                                                Amount in Base Currency <span class="text-muted"
                                                    id="revenue_base_currency_label">(KES)</span></label>
                                            <input type="number" class="form-control" id="revenue_base_amount"
                                                name="revenue_base_amount" step="0.01" value="0.00" readonly>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="revenue_description" class="form-label">Description</label>
                                        <input type="text" class="form-control" id="revenue_description"
                                            name="revenue_description" readonly>
                                    </div>
                                    <div class="mb-3">
                                        <label for="revenue_tax_applicable" class="form-label">Tax Applicable</label>
                                        <input type="number" class="form-control" id="revenue_tax_applicable"
                                            name="revenue_tax_applicable" step="0.01" readonly>
                                    </div>
                                    <div class="mb-3">
                                        <label for="revenue_based_on_uom" class="form-label">
                                            <span class="text-danger">*</span> Based On UOM</label>
                                        <select class="form-select form-control" id="revenue_based_on_uom"
                                            name="revenue_based_on_uom" required>
                                            <option value="">Select</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="revenue_remark" class="form-label">Remark</label>
                                        <input type="text" class="form-control" id="revenue_remark"
                                            name="revenue_remark">
                                    </div>
                                    <div class="mb-3" id="revenueIsAccruableContainer" style="display:none;">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="revenueIsAccruable"
                                                name="revenue_is_accruable">
                                            <label class="form-check-label fw-semibold" for="revenueIsAccruable">
                                                Is Accruable
                                            </label>
                                        </div>
                                    </div>

                                    <div class="mb-3" id="revenueIsReimbursableContainer" style="display:none;">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="revenueIsReimbursable"
                                                name="revenue_is_reimbursable">
                                            <label class="form-check-label fw-semibold" for="revenueIsReimbursable">
                                                Is Reimbursable <small class="text-muted">(Adds to Revenue)</small>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <div class="mb-3" id="revenueSecondaryBillToPartyContainer"
                                            style="display:none;">
                                            <label for="revenueSecondaryBillToParty" class="form-label">
                                                <span class="text-danger">*</span> Secondary Bill To Party</label>
                                            <input type="text" class="form-control" id="revenueSecondaryBillToParty"
                                                placeholder="Select Secondary Party">
                                            <input type="hidden" id="revenueSecondaryBillToPartyCode">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-success" id="saveRevenueCharge">Save</button>
                        <button type="button" class="btn btn-primary" id="saveAddMoreRevenueCharge">Save & Add
                            More</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    </div>
                </div>
            </div>
        </div>

        {{-- COST MODAL --}}
        <div class="modal fade" id="addCostModal" tabindex="-1" role="dialog" aria-labelledby="addCostModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="modal-title" id="addCostModalLabel">Add Manual Charges</h2>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form id="addCostChargesForm">
                        <div class="modal-body">
                            <input type="hidden" id="FITExpressChargeIDCost" name="cost_charge_id" />

                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="costChargeType"
                                            id="costTypeRevenue" value="revenue">
                                        <label class="form-check-label" for="costTypeRevenue">Revenue</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="costChargeType"
                                            id="costTypeCost" value="cost" checked>
                                        <label class="form-check-label" for="costTypeCost">Cost</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="costChargeType"
                                            id="costTypeCostMarkup" value="costmarkup">
                                        <label class="form-check-label" for="costTypeCostMarkup">Cost with markup</label>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <p class="text-muted mb-0">Base Currency: <strong
                                            class="office-base-currency">—</strong>
                                    </p>
                                </div>
                            </div>

                            <div class="row">
                                <!-- LEFT COLUMN -->
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="cost_charge_code" class="form-label">
                                            <span class="text-danger">*</span> Charge Code</label>
                                        <input type="text" class="form-control" id="cost_charge_code"
                                            name="cost_charge_code" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="cost_bill_to_party" class="form-label" id="costBillToPartyLabel">Bill
                                            to
                                            Party</label>
                                        <input type="text" class="form-control" id="cost_bill_to_party"
                                            name="cost_bill_to_party">
                                        <input type="hidden" id="cost_bill_to_party_code"
                                            name="cost_bill_to_party_code" />
                                    </div>
                                    <div class="mb-3">
                                        <label for="cost_based_on" class="form-label">
                                            <span class="text-danger">*</span> Based On</label>
                                        <select class="form-select form-control" id="cost_based_on" name="cost_based_on"
                                            required>
                                            <option value="">Select</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="cost_quantity" class="form-label">Quantity</label>
                                        <input type="number" class="form-control" id="cost_quantity"
                                            name="cost_quantity" step="0.01">
                                    </div>
                                    <div class="row mb-3 rate-row">
                                        <div class="col-6">
                                            <label for="cost_rate" class="form-label">Rate</label>
                                            <div class="input-group">
                                                <input type="number" class="form-control" id="cost_rate"
                                                    name="cost_rate" step="0.01">
                                                <select class="form-select form-control" id="cost_currency"
                                                    name="cost_currency">
                                                    <option value="">Select</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <label for="cost_amount" class="form-label">Amount</label>
                                            <input type="number" class="form-control" id="cost_amount"
                                                name="cost_amount" step="0.01" value="0.00" readonly>
                                        </div>
                                    </div>
                                    <div class="row mb-3 cost-markup-row" style="display: none;">
                                        <div class="col-6">
                                            <label for="cost_increase_by" class="form-label">Increase By</label>
                                            <div class="input-group no-divider">
                                                <input type="number" class="form-control" id="cost_increase_by"
                                                    name="cost_increase_by" step="0.01" value="0.00"
                                                    placeholder="0">
                                                <select class="form-select form-control" id="cost_increase_type"
                                                    name="cost_increase_type" style="max-width: 120px;">
                                                    <option value="">Select</option>
                                                    <option value="Percentage">Percentage</option>
                                                    <option value="Fixed">Fixed</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <label for="cost_revenue_amount" class="form-label">Amount</label>
                                            <input type="number" class="form-control" id="cost_revenue_amount"
                                                name="cost_revenue_amount" step="0.01" value="0.00" readonly>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-6">
                                            <label for="cost_exchange_rate" class="form-label">
                                                <span class="text-danger">*</span> Exchange Rate</label>
                                            <input type="number" class="form-control" id="cost_exchange_rate"
                                                name="cost_exchange_rate" readonly>
                                        </div>
                                        <div class="col-6">
                                            <label for="cost_base_amount" class="form-label text-nowrap">
                                                Amount in Base Currency <span class="text-muted"
                                                    id="cost_base_currency_label">(KES)</span></label>
                                            <input type="number" class="form-control" id="cost_base_amount"
                                                name="cost_base_amount" step="0.01" value="0.00" readonly>
                                        </div>
                                    </div>
                                </div>

                                <!-- RIGHT COLUMN -->
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="cost_description" class="form-label">Description</label>
                                        <input type="text" class="form-control" id="cost_description"
                                            name="cost_description" readonly>
                                    </div>
                                    <div class="mb-3">
                                        <label for="cost_tax_applicable" class="form-label">Tax Applicable</label>
                                        <input type="number" class="form-control" id="cost_tax_applicable"
                                            name="cost_tax_applicable" step="0.01" readonly>
                                    </div>
                                    <div class="mb-3">
                                        <label for="cost_based_on_uom" class="form-label">
                                            <span class="text-danger">*</span> Based On UOM</label>
                                        <select class="form-select form-control" id="cost_based_on_uom"
                                            name="cost_based_on_uom" required>
                                            <option value="">Select</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="cost_remark" class="form-label">Remark</label>
                                        <input type="text" class="form-control" id="cost_remark"
                                            name="cost_remark">
                                    </div>
                                    <div class="mb-3" id="costIsReimbursedContainer">
                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="checkbox" id="costIsReimbursed"
                                                name="cost_is_reimbursed">
                                            <label class="form-check-label fw-semibold" for="costIsReimbursed">
                                                Is Reimbursable <small class="text-muted">(Adds to Revenue)</small>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="mb-3" id="costIsAccruedContainer" style="display:none;">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="costIsAccrued"
                                                name="cost_is_accrued">
                                            <label class="form-check-label fw-semibold" for="costIsAccrued">
                                                Is Accrued <small class="text-muted">(Adds to Cost)</small>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="mb-3" id="costSecondaryBillToPartyContainer" style="display:none;">
                                        <label for="costSecondaryBillToParty" class="form-label">
                                            <span class="text-danger">*</span> Secondary Bill To Party</label>
                                        <input type="text" class="form-control" id="costSecondaryBillToParty"
                                            placeholder="Select Secondary Party">
                                        <input type="hidden" id="costSecondaryBillToPartyCode">
                                    </div>
                                </div>
                            </div>
                        </div>{{-- end modal-body --}}
                    </form>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-success" id="saveCostCharge">Save</button>
                        <button type="button" class="btn btn-primary" id="saveAddMoreCostCharge">Save & Add
                            More</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- XML Viewer Modal -->
        <div class="modal fade" id="xmlViewerModal" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-xl" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Booking XML</h5>
                        <button type="button" class="close" data-dismiss="modal">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <pre id="xmlContent"
                            style="max-height: 500px; overflow-y: auto; background-color: #f4f4f4; padding: 15px; border-radius: 4px;">{{ $quotation->SCMBookingXML ?? 'No XML available' }}</pre>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Email Quote Modal -->
        <div class="modal fade" id="emailQuoteModal" tabindex="-1" role="dialog"
            aria-labelledby="emailQuoteModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="modal-title" id="emailQuoteModalLabel">
                            <i class="fas fa-paper-plane mr-2"></i>Email Quote
                        </h2>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form id="emailQuoteForm">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Template <span class="text-danger">*</span></label>
                                        <select class="form-control form-control-sm" id="emailTemplateSelect" required>
                                            <option value="">-- Select Template --</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Quote Version</label>
                                        <select class="form-control form-control-sm" id="emailQuoteVersionSelect">
                                            <option value="">-- Select Quote --</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>From <span class="text-danger">*</span></label>
                                        <input type="email" class="form-control form-control-sm" id="emailFrom"
                                            placeholder="Enter sender email...">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>To <span class="text-danger">*</span></label>
                                        <input type="email" class="form-control form-control-sm" id="emailTo"
                                            value="{{ $quotation->pri_partner_email ?? '' }}">
                                    </div>
                                </div>
                            </div>

                            <div class="form-group mt-3">
                                <label>Subject</label>
                                <input type="text" class="form-control form-control-sm" id="emailSubject">
                            </div>
                            <div class="form-group mb-0">
                                <label>Body</label>
                                <textarea class="form-control form-control-sm" id="emailBody" rows="8"
                                    placeholder="Select a template to auto-fill..."></textarea>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-success" id="sendEmailQuoteBtn">
                            <i class="fas fa-paper-plane mr-1"></i> Send
                        </button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    </div>
                </div>
            </div>
        </div>

        {{-- WON MODAL --}}
        <div class="modal fade" id="wonModal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="modal-title">
                            <i class="fas fa-check-circle text-success mr-2"></i>Mark as Won
                        </h2>
                        <button type="button" class="close" data-dismiss="modal">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="wonRemarkInput">Remark <span class="text-muted">(optional)</span></label>
                            <textarea class="form-control" id="wonRemarkInput" rows="4" placeholder="Enter any remarks..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-success" id="confirmWonBtn">
                            <i class="fas fa-check mr-1"></i> Confirm Won
                        </button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    </div>
                </div>
            </div>
        </div>

        {{-- WON PROCESS MODAL --}}
        <div class="modal fade" id="wonProcessModal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="fas fa-trophy mr-2 text-success"></i> Marking as Won
                        </h5>
                        <button type="button" class="close" data-dismiss="modal">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p class="text-muted small mb-3">
                            Processing — please do not close this window.
                        </p>
                        <div class="list-group" id="wonStepList"></div>
                        <div id="wonProgressError" class="alert alert-danger mt-3 d-none"></div>
                        <div id="wonProgressSuccess" class="alert alert-success mt-3 d-none">
                            <i class="fas fa-check-circle mr-2"></i>
                            Quotation has been marked as Won successfully.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary d-none"
                            id="wonProcessCloseBtn" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>

        {{-- CREATE BOOKING MODAL --}}
        <div class="modal fade" id="createBookingModal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="modal-title" style="color: #003580; font-weight: 700;">
                            Creation of Booking
                        </h2>
                        <button type="button" class="close" data-dismiss="modal">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body" style="padding: 1.75rem 2rem;">
                        <form id="createBookingForm">

                            {{-- Service Template --}}
                            <div class="form-group mb-4">
                                <label for="cb_service_template">
                                    Service Template <span class="text-danger">*</span>
                                </label>
                                <select class="form-control" id="cb_service_template" name="service_template"
                                    required>
                                    <option value=""></option>
                                </select>
                            </div>

                            {{-- Bill To Party + Forwarder --}}
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <div class="form-group mb-0">
                                        <label for="cb_bill_to_party">
                                            Bill To Party <span class="text-danger">*</span>
                                        </label>
                                        <select class="form-control" id="cb_bill_to_party" name="bill_to_party"
                                            required>
                                            <option value=""></option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-0">
                                        <label for="cb_forwarder">
                                            Forwarder <span class="text-danger">*</span>
                                        </label>
                                        <select class="form-control" id="cb_forwarder" name="forwarder" required>
                                            <option value=""></option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            {{-- Include the Country --}}
                            <div class="form-group mb-3 mt-3">
                                <div class="form-check d-flex align-items-center">
                                    <input class="form-check-input me-2" type="checkbox" id="cb_include_country"
                                        name="include_country">
                                    <label class="form-check-label mb-0" for="cb_include_country">
                                        Include the Country
                                    </label>
                                </div>
                            </div>

                            {{-- Place of Receipt + Place of Delivery --}}
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-0">
                                        <label for="cb_place_of_receipt">
                                            Place of Receipt <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" class="form-control" id="cb_place_of_receipt"
                                            placeholder="Type at least 2 characters..." autocomplete="off">
                                        <input type="hidden" id="cb_place_of_receipt_code">
                                        <input type="hidden" id="cb_place_of_receipt_id">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-0">
                                        <label for="cb_place_of_delivery">
                                            Place of Delivery <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" class="form-control" id="cb_place_of_delivery"
                                            placeholder="Type at least 2 characters..." autocomplete="off">
                                        <input type="hidden" id="cb_place_of_delivery_code">
                                        <input type="hidden" id="cb_place_of_delivery_id">
                                    </div>
                                </div>
                            </div>

                        </form>
                    </div>
                    <div class="modal-footer">
                        <a href="#" class="text-primary mr-3" id="cancelCreateBookingBtn"
                            style="font-weight: 500; text-decoration: underline;">Cancel</a>
                        <button type="button" class="btn btn-primary px-4" id="submitCreateBookingBtn">
                            Create
                        </button>
                    </div>
                </div>
            </div>
        </div>

   <div class="modal fade" id="copyChargesModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl cc-modal-dialog" role="document">
        <div class="modal-content cc-modal-content">
 
            {{-- ── Header ─────────────────────────────────────────── --}}
            <div class="cc-modal-header">
                <span class="cc-modal-title">Copy Charges</span>
                <button type="button" class="cc-modal-close" data-dismiss="modal" aria-label="Close">
                    &times;
                </button>
            </div>
 
            {{-- ── Body ──────────────────────────────────────────── --}}
            <div class="cc-modal-body">
 
                {{-- ── 2-column info grid ──────────────────────────── --}}
                <div class="cc-info-grid">
                    {{-- LEFT COLUMN --}}
                    <div class="cc-info-col">
                        <div class="cc-info-row">
                            <span class="cc-lbl">Service Type</span>
                            <span class="cc-val" id="ccServiceType">{{ $quotation->SVName ?? '' }}</span>
                        </div>
                        <div class="cc-info-row">
                            <span class="cc-lbl cc-lbl-required">*&nbsp;Job No</span>
                            <div class="cc-input-group">
                                <input type="text"
                                       class="cc-input cc-input-highlight"
                                       id="ccJobNo"
                                       placeholder="Enter Job No…"
                                       autocomplete="off">
                                <button type="button" class="cc-search-icon-btn" id="ccJobNoSearchBtn">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                        <div class="cc-info-row" id="ccClientRow" style="display:none;">
                            <span class="cc-lbl">Client</span>
                            <span class="cc-val" id="ccClient"></span>
                        </div>
                        <div class="cc-info-row" id="ccShipperRow" style="display:none;">
                            <span class="cc-lbl">Shipper</span>
                            <span class="cc-val" id="ccShipper"></span>
                        </div>
                        <div class="cc-info-row" id="ccPolRow" style="display:none;">
                            <span class="cc-lbl">Port of Loading</span>
                            <span class="cc-val" id="ccPortOfLoading"></span>
                        </div>
                    </div>
 
                    {{-- RIGHT COLUMN --}}
                    <div class="cc-info-col">
                        <div class="cc-info-row">
                            <span class="cc-lbl">Process Type</span>
                            <span class="cc-val" id="ccProcessType">{{ $quotation->process ?? '' }}</span>
                        </div>
                        {{-- Spacer keeps right column aligned with left after Job No row --}}
                        <div class="cc-info-row cc-info-row-spacer"></div>
                        <div class="cc-info-row" id="ccConsigneeRow" style="display:none;">
                            <span class="cc-lbl">Consignee</span>
                            <span class="cc-val" id="ccConsignee"></span>
                        </div>
                        <div class="cc-info-row" id="ccPodRow" style="display:none;">
                            <span class="cc-lbl">Port of Discharge</span>
                            <span class="cc-val" id="ccPortOfDischarge"></span>
                        </div>
                    </div>
                </div>
                {{-- END info grid --}}
 
                {{-- ── REVENUE CHARGES TABLE ───────────────────────── --}}
                <div class="cc-section" id="ccJobDetails" style="display:none;">
 
                    <div class="cc-section-header">
                        <strong>Revenue Charges</strong>
                        <span class="cc-section-summary" id="ccRevenueSummary">
                            [ Total Record(s): 0 &nbsp; Total Amount: 0 KES ]
                        </span>
                    </div>
 
                    <div class="cc-view-toggle-bar">
                        <a href="#" class="cc-view-toggle cc-view-active"
                           data-tbody="ccRevenueBody" data-mode="all">View: <strong>All</strong></a>
                        <span class="cc-view-sep">|</span>
                        <a href="#" class="cc-view-toggle"
                           data-tbody="ccRevenueBody" data-mode="selected">Selected</a>
                    </div>
 
                    <div class="cc-table-wrap">
                        <table class="cc-table" id="ccRevenueTable">
                            <thead>
                                <tr>
                                    <th class="cc-col-chk">
                                        <input type="checkbox" id="ccRevenueSelectAll" title="Select All">
                                    </th>
                                    <th class="cc-col-service">Service</th>
                                    <th class="cc-col-activity">Activity</th>
                                    <th class="cc-col-party">Debtor</th>
                                    <th class="cc-col-code">Charge Code</th>
                                    <th class="cc-col-desc">Charge Description</th>
                                    <th class="cc-col-qty">Quantity</th>
                                    <th class="cc-col-basedon">BasedOn</th>
                                    <th class="cc-col-uom">UOM</th>
                                </tr>
                            </thead>
                            <tbody id="ccRevenueBody">
                                <tr>
                                    <td colspan="9" class="cc-empty-msg">
                                        Enter a Job No to load charges
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
 
                    {{-- Revenue pagination --}}
                    <div class="cc-pagination" data-tbody="ccRevenueBody">
                        <div class="cc-pag-controls">
                            <button type="button" class="cc-pag-btn cc-tbl-page-btn"
                                    data-tbody="ccRevenueBody" data-action="first">|&lt;</button>
                            <button type="button" class="cc-pag-btn cc-tbl-page-btn"
                                    data-tbody="ccRevenueBody" data-action="prev">&lt;&lt;</button>
                            <span class="cc-pag-label">Page</span>
                            <input type="number" class="cc-pag-input cc-tbl-page-input"
                                   data-tbody="ccRevenueBody" value="1" min="1">
                            <span class="cc-pag-label">of
                                <strong class="cc-tbl-total-pages" data-tbody="ccRevenueBody">1</strong>
                            </span>
                            <button type="button" class="cc-pag-btn cc-tbl-page-btn"
                                    data-tbody="ccRevenueBody" data-action="next">&gt;&gt;</button>
                            <button type="button" class="cc-pag-btn cc-tbl-page-btn"
                                    data-tbody="ccRevenueBody" data-action="last">&gt;|</button>
                            <select class="cc-pag-size cc-tbl-page-size" data-tbody="ccRevenueBody">
                                <option value="10" selected>10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                            </select>
                        </div>
                        <span class="cc-pag-range cc-tbl-view-range" data-tbody="ccRevenueBody">
                            View 1 - 0 of 0
                        </span>
                    </div>
 
                </div>{{-- /revenue section --}}
 
                {{-- ── COST CHARGES TABLE ──────────────────────────── --}}
                <div class="cc-section cc-section--cost" id="ccJobDetailsCost" style="display:none;">
 
                    <div class="cc-section-header">
                        <strong>Cost Charges</strong>
                        <span class="cc-section-summary" id="ccCostSummary">
                            [ Total Record(s): 0 &nbsp; Total Amount: 0 KES ]
                        </span>
                    </div>
 
                    <div class="cc-view-toggle-bar">
                        <a href="#" class="cc-view-toggle cc-view-active"
                           data-tbody="ccCostBody" data-mode="all">View: <strong>All</strong></a>
                        <span class="cc-view-sep">|</span>
                        <a href="#" class="cc-view-toggle"
                           data-tbody="ccCostBody" data-mode="selected">Selected</a>
                    </div>
 
                    <div class="cc-table-wrap">
                        <table class="cc-table" id="ccCostTable">
                            <thead>
                                <tr>
                                    <th class="cc-col-chk">
                                        <input type="checkbox" id="ccCostSelectAll" title="Select All">
                                    </th>
                                    <th class="cc-col-service">Service</th>
                                    <th class="cc-col-activity">Activity</th>
                                    <th class="cc-col-party">Vendor</th>  {{-- Vendor, not Debtor --}}
                                    <th class="cc-col-code">Charge Code</th>
                                    <th class="cc-col-desc">Charge Description</th>
                                    <th class="cc-col-qty">Quantity</th>
                                    <th class="cc-col-basedon">BasedOn</th>
                                    <th class="cc-col-uom">UOM</th>
                                </tr>
                            </thead>
                            <tbody id="ccCostBody">
                                <tr>
                                    <td colspan="9" class="cc-empty-msg">
                                        Enter a Job No to load charges
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
 
                    {{-- Cost pagination --}}
                    <div class="cc-pagination" data-tbody="ccCostBody">
                        <div class="cc-pag-controls">
                            <button type="button" class="cc-pag-btn cc-tbl-page-btn"
                                    data-tbody="ccCostBody" data-action="first">|&lt;</button>
                            <button type="button" class="cc-pag-btn cc-tbl-page-btn"
                                    data-tbody="ccCostBody" data-action="prev">&lt;&lt;</button>
                            <span class="cc-pag-label">Page</span>
                            <input type="number" class="cc-pag-input cc-tbl-page-input"
                                   data-tbody="ccCostBody" value="1" min="1">
                            <span class="cc-pag-label">of
                                <strong class="cc-tbl-total-pages" data-tbody="ccCostBody">1</strong>
                            </span>
                            <button type="button" class="cc-pag-btn cc-tbl-page-btn"
                                    data-tbody="ccCostBody" data-action="next">&gt;&gt;</button>
                            <button type="button" class="cc-pag-btn cc-tbl-page-btn"
                                    data-tbody="ccCostBody" data-action="last">&gt;|</button>
                            <select class="cc-pag-size cc-tbl-page-size" data-tbody="ccCostBody">
                                <option value="10" selected>10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                            </select>
                        </div>
                        <span class="cc-pag-range cc-tbl-view-range" data-tbody="ccCostBody">
                            View 1 - 0 of 0
                        </span>
                    </div>
 
                </div>{{-- /cost section --}}
 
            </div>{{-- /cc-modal-body --}}
 
            {{-- ── Footer ─────────────────────────────────────────── --}}
            <div class="cc-modal-footer">
                <button type="button" class="cc-btn-save" id="ccSaveBtn" disabled>
                    Save
                </button>
                <span class="cc-footer-or">or</span>
                <a href="#" class="cc-btn-cancel" id="ccCancelBtn" data-dismiss="modal">Cancel</a>
            </div>
 
        </div>{{-- /modal-content --}}
    </div>{{-- /modal-dialog --}}
</div>
 
 
{{-- ============================================================
     COPY CHARGES — JOB SEARCH MODAL
     Sits above the main modal (z-index: 1060 > 1050)
     ============================================================ --}}
<div class="modal fade" id="ccSearchModal" tabindex="-1" role="dialog" aria-hidden="true"
     style="z-index: 1060;">
    <div class="modal-dialog cc-search-dialog" role="document">
        <div class="modal-content cc-modal-content">
 
            <div class="cc-modal-header">
                <span class="cc-modal-title">Search</span>
                <button type="button" class="cc-modal-close" id="ccSearchModalClose">&times;</button>
            </div>
 
            <div class="cc-modal-body">
 
                {{-- Advanced search row --}}
                <div class="cc-search-controls">
                    <span class="cc-lbl" style="white-space:nowrap;">Advanced Search</span>
                   <select class="cc-select" id="ccSearchField" style="width:140px;">
                        <option value="">Select</option>
                        <option value="quote_no">Quote No</option>
                        <option value="customer">Customer</option>
                        <option value="origin">Origin Country</option>
                        <option value="destination">Destination Country</option>
                    </select>
                    <input type="text" class="cc-input" id="ccSearchValue"
                           style="width:200px;" placeholder="">
                    <button type="button" class="cc-btn-search-go" id="ccSearchGoBtn">Search</button>
                    <button type="button" class="cc-btn-add-filter" id="ccAddFilterBtn">Add filter</button>
                </div>
 
                {{-- Search results table --}}
                <div class="cc-table-wrap" style="margin-top:8px;">
                    <table class="cc-table" id="ccSearchResultsTable">
                        <thead>
                            <tr>
                                <th style="width:40px;">Select</th>
                                <th>Process Type
                                    <i class="fas fa-sort ml-1" style="font-size:0.65rem; color:#999;"></i>
                                </th>
                                <th>Job No.</th>
                            </tr>
                        </thead>
                        <tbody id="ccSearchResultsBody">
                            <tr>
                                <td colspan="3" class="cc-empty-msg">Click Search to find jobs</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
 
                {{-- Pagination --}}
                <div class="cc-pagination" style="margin-top:6px;">
                    <div class="cc-pag-controls">
                        <button type="button" class="cc-pag-btn cc-page-btn" data-action="first">|&lt;</button>
                        <button type="button" class="cc-pag-btn cc-page-btn" data-action="prev">&lt;&lt;</button>
                        <span class="cc-pag-label">Page</span>
                        <input type="number" id="ccPageInput" class="cc-pag-input" value="1" min="1">
                        <span class="cc-pag-label">of <strong id="ccTotalPages">1</strong></span>
                        <button type="button" class="cc-pag-btn cc-page-btn" data-action="next">&gt;&gt;</button>
                        <button type="button" class="cc-pag-btn cc-page-btn" data-action="last">&gt;|</button>
                        <select class="cc-pag-size" id="ccPageSize">
                            <option value="10" selected>10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                        </select>
                    </div>
                    <span class="cc-pag-range" id="ccViewRange">View 1 - 0 of 0</span>
                </div>
 
            </div>
 
            <div class="cc-modal-footer">
                <button type="button" class="cc-btn-save" id="ccSearchOkBtn" disabled>Ok</button>
                <span class="cc-footer-or">or</span>
                <a href="#" class="cc-btn-cancel" id="ccSearchCancelBtn">Cancel</a>
            </div>
 
        </div>
    </div>
</div>
 
        
        {{-- COPY CHARGES — JOB SEARCH MODAL --}}
        <div class="modal fade" id="ccSearchModal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="modal-title">Search</h2>
                        <button type="button" class="close" id="ccSearchModalClose">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        {{-- Advanced Search row --}}
                        <div class="d-flex align-items-center mb-3" style="gap:8px;">
                            <label class="mb-0 mr-2" style="font-size:0.875rem; white-space:nowrap;">Advanced Search</label>
                            <select class="form-control form-control-sm" id="ccSearchField" style="max-width:160px;">
                                <option value="">Select</option>
                                <option value="quote_no">Quote No</option>
                                <option value="customer">Customer</option>
                                <option value="origin">Origin Country</option>
                                <option value="destination">Destination Country</option>
                            </select>
                            <input type="text" class="form-control form-control-sm" id="ccSearchValue"
                                placeholder="" style="max-width:220px;">
                            <button type="button" class="btn btn-sm ml-1" id="ccSearchGoBtn"
                                style="background:#a8b400; color:#fff; font-weight:600; min-width:70px;">
                                Search
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="ccAddFilterBtn">
                                Add filter
                            </button>
                        </div>
        
                        {{-- Results table --}}
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered table-hover" id="ccSearchResultsTable">
                                <thead class="thead-light">
                                    <tr>
                                        <th style="width:40px;">Select</th>
                                        <th>Process Type <i class="fas fa-sort ml-1 text-muted" style="font-size:0.7rem;"></i></th>
                                        <th>Job No.</th>
                                    </tr>
                                </thead>
                                <tbody id="ccSearchResultsBody">
                                    <tr>
                                        <td colspan="3" class="text-center text-muted" style="font-size:0.8rem;">
                                            Click Search to find jobs
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
        
                        {{-- Pagination --}}
                        <div class="d-flex align-items-center justify-content-between mt-2">
                            <div class="d-flex align-items-center" style="gap:4px; font-size:0.8rem;">
                                <button type="button" class="btn btn-sm btn-outline-secondary cc-page-btn" data-action="first" style="padding:1px 5px;">|&lt;</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary cc-page-btn" data-action="prev" style="padding:1px 5px;">&lt;&lt;</button>
                                <span class="mx-1">Page</span>
                                <input type="number" id="ccPageInput" class="form-control form-control-sm text-center"
                                    value="1" min="1" style="width:46px; height:26px; font-size:0.8rem;">
                                <span class="mx-1">of <strong id="ccTotalPages">1</strong></span>
                                <button type="button" class="btn btn-sm btn-outline-secondary cc-page-btn" data-action="next" style="padding:1px 5px;">&gt;&gt;</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary cc-page-btn" data-action="last" style="padding:1px 5px;">&gt;|</button>
                                <select class="form-control form-control-sm ml-2" id="ccPageSize" style="width:58px; height:26px; font-size:0.8rem;">
                                    <option value="10" selected>10</option>
                                    <option value="25">25</option>
                                    <option value="50">50</option>
                                </select>
                            </div>
                            <span class="text-muted" style="font-size:0.8rem;" id="ccViewRange">View 1 - 0 of 0</span>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-sm" id="ccSearchOkBtn"
                            style="background:#a8b400; color:#fff; font-weight:600; min-width:50px;" disabled>
                            Ok
                        </button>
                        <span class="text-muted mx-2" style="font-size:0.85rem;">or</span>
                        <a href="#" class="text-primary" id="ccSearchCancelBtn"
                            style="font-size:0.875rem; font-weight:500;">Cancel</a>
                    </div>
                </div>
            </div>
        </div>

    </form>
@stop


@section('css')
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.min.css">
    <style>
        /* =============================================
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        MODAL STYLING — matched to FitExpress UI
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        ============================================= */

        .modal-header {
            background-color: #fff !important;
            color: #000 !important;
            border-bottom: 1px solid #dee2e6;
            padding: 1rem 1.5rem;
        }

        .modal-title {
            font-size: 1.125rem;
            font-weight: 600;
            color: #000 !important;
        }

        .modal-header .close {
            color: #000;
            opacity: 0.5;
        }

        .modal-header .close:hover {
            opacity: 0.75;
        }

        .modal-body {
            padding: 1.5rem;
            background-color: #fff;
        }

        .modal-footer {
            background-color: #fff;
            border-top: 1px solid #dee2e6;
            padding: 1rem 1.5rem;
        }

        /* Top charge-type row */
        .modal-body .charge-type-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        /* Form labels */
        .modal-body label {
            font-weight: 400;
            margin-bottom: 0.25rem;
            font-size: 0.875rem;
            color: #495057;
            display: block;
        }

        .modal-body label .text-danger {
            color: #dc3545 !important;
        }

        /* mb-3 spacing inside modal */
        .modal-body .mb-3 {
            margin-bottom: 1rem !important;
        }

        /* Standard form controls inside modal */
        .modal-body .form-control,
        .modal-body .form-select,
        .modal-body select.form-control {
            border: 1px solid #ced4da;
            background-color: #fff;
            font-size: 0.875rem;
            padding: 0.375rem 0.75rem;
            height: calc(1.5em + 0.75rem + 2px);
            border-radius: 0.25rem;
            width: 100%;
        }

        .modal-body .form-control:focus,
        .modal-body select.form-control:focus {
            border-color: #80bdff;
            outline: 0;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }

        .modal-body .form-control[readonly] {
            background-color: #e9ecef;
            cursor: not-allowed;
            border: 1px solid #ced4da;
            padding: 0.375rem 0.75rem;
            font-weight: 400;
            color: #495057;
        }

        .modal-body .form-control[readonly]:focus {
            outline: none;
            box-shadow: none;
        }

        /* Input group — rate + currency */
        .modal-body .input-group {
            display: flex;
            width: 100%;
        }

        .modal-body .input-group .form-control {
            border-radius: 0.25rem 0 0 0.25rem;
            flex: 1;
        }

        .modal-body .input-group select.form-control {
            border-radius: 0 0.25rem 0.25rem 0;
            border-left: 1px solid #dee2e6;
            /* Divider */
            flex: 0 0 auto;
            width: auto;
            min-width: 80px;
        }

        .modal-body .input-group .form-control:not(:last-child) {
            border-right: 0;
            /* Prevent double border */
        }


        /* Radio buttons */
        .modal-body .form-check-inline {
            margin-right: 1rem;
            display: inline-flex;
            align-items: center;
        }

        .modal-body .form-check-input {
            margin-top: 0;
            margin-right: 0.5rem;
        }

        .modal-body .form-check-label {
            margin-bottom: 0;
            font-size: 0.875rem;
            font-weight: 400;
        }

        /* Checkbox labels — semibold with small muted */
        .modal-body .form-check .fw-semibold {
            font-weight: 600;
            font-size: 0.875rem;
        }

        .modal-body .form-check .fw-semibold small {
            font-weight: 400;
            font-size: 0.75rem;
        }

        .modal-body .text-muted {
            color: #6c757d !important;
            font-size: 0.875rem;
        }

        .modal-body .text-muted strong {
            font-weight: 600;
            color: #495057;
        }

        .modal-body hr {
            margin: 1rem 0;
            border-top: 1px solid #dee2e6;
        }

        /* Secondary Bill To Party */
        #secondaryBillToPartyContainerRevenue,
        #secondaryBillToPartyContainerCost {
            display: none;
            margin-top: 1rem;
        }

        /* Modal footer buttons */
        .modal-footer .btn {
            font-size: 0.875rem;
            padding: 0.375rem 0.75rem;
            font-weight: 400;
            border-radius: 0.25rem;
        }

        .modal-footer .btn-success {
            background-color: #28a745;
            border-color: #28a745;
            color: #fff;
        }

        .modal-footer .btn-success:hover {
            background-color: #218838;
            border-color: #1e7e34;
        }

        .modal-footer .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
            color: #fff;
        }

        .modal-footer .btn-primary:hover {
            background-color: #0069d9;
            border-color: #0062cc;
        }

        .modal-footer .btn-secondary {
            background-color: #6c757d;
            border-color: #6c757d;
            color: #fff;
        }

        .modal-footer .btn-secondary:hover {
            background-color: #5a6268;
            border-color: #545b62;
        }

        /* Increase-by row and cost-markup-row - FIXED */
        .increase-by-row,
        .cost-markup-row {
            display: none;
        }

        /* When active, show as flex layout */
        .increase-by-row.active,
        .cost-markup-row.active {
            display: flex !important;
        }

        /* General form styling */
        .form-control-sm {
            height: calc(1.5em + 0.5rem + 2px);
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }

        /* Autocomplete dropdown */
        .ui-autocomplete {
            z-index: 99999 !important;
            max-height: 300px;
            overflow-y: auto;
            background: white;
            border: 1px solid #ced4da;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
        }

        .ui-menu-item {
            padding: 8px 12px;
            cursor: pointer;
            list-style: none;
        }

        .ui-menu-item:hover,
        .ui-state-focus {
            background-color: #f0f0f0;
        }

        .ui-autocomplete .ui-state-disabled {
            color: #999;
            font-style: italic;
            cursor: not-allowed;
            pointer-events: none;
        }

        .ui-autocomplete .ui-state-disabled div {
            padding: 8px 12px;
        }

        /* Revenue and Cost table styling */
        .table-responsive {
            overflow-x: auto;
        }

        .table-hover tbody tr:hover {
            background-color: #f5f5f5;
        }

        .thead-light th {
            background-color: #e9ecef;
            border-color: #dee2e6;
            font-weight: 600;
            font-size: 0.85rem;
        }

        .table-sm th,
        .table-sm td {
            padding: 0.4rem;
            vertical-align: middle;
            font-size: 0.85rem;
        }

        .table tfoot td {
            background-color: #f8f9fa;
            border-top: 2px solid #dee2e6;
        }

        .btn-action {
            padding: 0.15rem 0.4rem;
            font-size: 0.75rem;
        }

        /* Card styling */
        .card-primary>.card-header,
        .card.card-primary>.card-header {
            background-color: #4B49AC !important;
            color: white !important;
        }

        .card-success>.card-header,
        .card.card-success>.card-header {
            background-color: #4B49AC !important;
            color: white !important;
        }

        .card-danger>.card-header,
        .card.card-danger>.card-header {
            background-color: #4B49AC !important;
            color: white !important;
        }

        .card-tools a {
            text-decoration: underline;
        }

        .card-tools a:hover {
            opacity: 0.8;
        }

        /* Buttons */
        .btn-primary {
            background-color: #5b9bd5;
            border-color: #5b9bd5;
        }

        .btn-primary:hover {
            background-color: #4a8bc2;
            border-color: #4a8bc2;
        }

        .btn-warning {
            background-color: #ed7d31;
            border-color: #ed7d31;
            color: white;
        }

        .btn-warning:hover {
            background-color: #d96b28;
            border-color: #d96b28;
            color: white;
        }

        .btn-success {
            background-color: #a9d08e;
            border-color: #a9d08e;
            color: white;
        }

        .btn-success:hover {
            background-color: #95bb7a;
            border-color: #95bb7a;
            color: white;
        }

        /* Vertical separator for side-by-side columns */
        .vertical-separator {
            border-left: 2px solid #dee2e6 !important;
            padding-left: 15px !important;
            margin-left: -1px;
        }

        /* Padding adjustments for separation */
        .pr-2 {
            padding-right: 0.5rem !important;
        }

        .pl-2 {
            padding-left: 0.5rem !important;
        }

        /* Email Quote Action Bar */
        .card-primary .btn-email-quote {
            background-color: #ffffff;
            color: #4B49AC;
            border: 1px solid rgba(255, 255, 255, 0.3);
            font-size: 0.8rem;
            padding: 0.25rem 0.75rem;
            border-radius: 4px;
            font-weight: 500;
            transition: all 0.2s;
        }

        .card-primary .btn-email-quote:hover {
            background-color: rgba(255, 255, 255, 0.2);
            color: #ffffff;
            border-color: #ffffff;
        }

        .card-primary .card-title {
            font-size: 1rem;
            font-weight: 500;
        }

        /* Dropdown closes after item click — handled by Bootstrap automatically */
        #quoteActionDropdown .dropdown-item {
            font-size: 0.875rem;
            padding: 0.45rem 1rem;
        }

        /* Show/Hide extra columns */
        .col-extra {
            display: none;
            /* hidden by default — JS controls visibility */
        }

        .input-group {
            position: relative;
        }

        #cost_currency {
            border-left: 1px solid #dee2e6;
            margin-left: -1px;
            /* Prevent double thickness */
        }

        .no-divider .form-control {
            border-right: 1px solid #ced4da;
        }

        .no-divider select.form-control {
            border-left: 0;
        }

        #revenueTotalHeaderAmount,
        #costTotalHeaderAmount {
            font-size: 1.2rem;
            font-weight: 700;
            letter-spacing: 0.02em;
        }

        .modal-body textarea.form-control {
            height: auto;
            min-height: 120px;
            /* adjust to taste */
            resize: vertical;
        }

        /* =============================================
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                               CREATE BOOKING MODAL — Specific overrides
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                               to match the "Creation of Booking" design
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                               ============================================= */

        #createBookingModal .modal-title {
            color: #003580 !important;
            font-size: 1.4rem !important;
            font-weight: 700 !important;
        }

        #createBookingModal .modal-body label {
            font-weight: 700;
            font-size: 0.9rem;
            color: #1a1a1a;
            margin-bottom: 0.4rem;
        }

        /* Base styles for ALL form controls in create booking modal */
        #createBookingModal .modal-body .form-control {
            height: 44px;
            font-size: 0.95rem;
            border: 1.5px solid #b0b8c4;
            border-radius: 4px;
            background-color: #fff;
            background-image: none;
            /* reset — no arrow on text inputs */
            padding-right: 0.75rem;
            /* normal padding for text inputs */
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            cursor: text;
        }

        /* Arrow ONLY on select elements */
        #createBookingModal .modal-body select.form-control {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8'%3E%3Cpath fill='%23495057' d='M6 8L0 0h12z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 14px center;
            background-clip: padding-box;
            padding-right: 36px;
            cursor: pointer;
        }

        #createBookingModal .modal-body select.form-control:focus {
            border-color: #003580;
            box-shadow: 0 0 0 0.15rem rgba(0, 53, 128, 0.2);
        }

        /* Checkbox row — inline with label */
        #createBookingModal .form-check {
            display: flex;
            align-items: center;
            padding-left: 0;
            margin: 1rem 0 1rem 0;
            /* more breathing room above and below */
        }

        #createBookingModal .form-check-input {
            width: 18px;
            height: 18px;
            margin: 0 10px 0 0;
            border: 1.5px solid #b0b8c4;
            border-radius: 2px;
            cursor: pointer;
            flex-shrink: 0;
        }

        #createBookingModal .form-check-label {
            font-weight: 700;
            font-size: 0.9rem;
            color: #1a1a1a;
            margin-bottom: 0;
            cursor: pointer;
        }

        /* Footer layout — Cancel left, Create right */
        #createBookingModal .modal-footer {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            gap: 1rem;
            padding: 1rem 1.5rem;
        }

        #cancelCreateBookingBtn {
            font-size: 0.95rem;
            font-weight: 600;
            color: #003580 !important;
            text-decoration: underline !important;
            background: none;
            border: none;
            padding: 0;
            cursor: pointer;
        }

        #cancelCreateBookingBtn:hover {
            color: #001f5c !important;
        }

        #submitCreateBookingBtn {
            background-color: #2c4a7c;
            border-color: #2c4a7c;
            color: #fff;
            font-size: 1rem;
            font-weight: 600;
            padding: 0.45rem 1.75rem;
            border-radius: 4px;
            letter-spacing: 0.02em;
        }

        #submitCreateBookingBtn:hover {
            background-color: #1e3a6e;
            border-color: #1e3a6e;
        }

        #submitCreateBookingBtn:disabled {
            background-color: #6b85aa;
            border-color: #6b85aa;
        }

        #createBookingModal .form-check-input {
            position: static;
            margin-right: 12px;
        }

        #generateQuoteBtn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            pointer-events: auto;
            /* keeps the title tooltip visible on hover */
        }

        /* Disabled UOM select — visually greyed out, not editable */
        .modal-body select.form-control:disabled,
        .modal-body .form-select:disabled {
            background-color: #e9ecef;
            cursor: not-allowed;
            color: #6c757d;
            opacity: 1;
            /* override browser default which can make it too faint */
        }

        /* ── Modal shell ─────────────────────────────────────────────── */
.cc-modal-dialog {
    max-width: 900px;   /* matches screenshot width ~860px */
    margin: 30px auto;
}
.cc-search-dialog {
    max-width: 560px;
    margin: 40px auto;
}
.cc-modal-content {
    border-radius: 0;
    border: 1px solid #aaa;
    box-shadow: 2px 4px 12px rgba(0,0,0,.25);
    font-size: 12px;
    font-family: Arial, sans-serif;
    color: #222;
}
 
/* ── Header ──────────────────────────────────────────────────── */
.cc-modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: #fff;
    border-bottom: 1px solid #ccc;
    padding: 7px 12px;
}
.cc-modal-title {
    font-size: 14px;
    font-weight: 700;
    color: #222;
}
.cc-modal-close {
    background: none;
    border: 1px solid #bbb;
    font-size: 14px;
    line-height: 1;
    padding: 1px 6px;
    cursor: pointer;
    color: #555;
    border-radius: 2px;
}
.cc-modal-close:hover { background: #e0e0e0; }
 
/* ── Body ────────────────────────────────────────────────────── */
.cc-modal-body {
    padding: 10px 14px;
    background: #fff;
}
 
/* ── 2-column info grid ──────────────────────────────────────── */
.cc-info-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0 24px;
    margin-bottom: 10px;
}
.cc-info-col {
    display: flex;
    flex-direction: column;
    gap: 3px;
}
.cc-info-row {
    display: flex;
    align-items: center;
    min-height: 22px;
    gap: 4px;
}
/* Spacer row keeps right column vertically aligned with left's Job No row */
.cc-info-row-spacer {
    min-height: 26px; /* matches the input row height */
}
.cc-lbl {
    min-width: 120px;   /* fixed label width → values always start at same x */
    font-size: 12px;
    color: #555;
    text-align: right;
    padding-right: 6px;
    white-space: nowrap;
    flex-shrink: 0;
}
.cc-lbl-required {
    color: #c00;
    font-weight: 600;
}
.cc-val {
    font-size: 12px;
    color: #111;
    font-weight: 400;
}
 
/* Job No input + search button row */
.cc-input-group {
    display: flex;
    align-items: center;
    gap: 3px;
}
.cc-input {
    height: 24px;
    padding: 2px 5px;
    font-size: 12px;
    border: 1px solid #aaa;
    border-radius: 2px;
    outline: none;
    color: #222;
    background: #fff;
}
.cc-input:focus { border-color: #6699cc; box-shadow: none; }
 
/* Highlighted Job No field — yellow border like in screenshot */
.cc-input-highlight {
    border-color: #d4a800;
    background: #fffde6;
    width: 160px;
}
 
.cc-search-icon-btn {
    height: 24px;
    width: 24px;
    padding: 0;
    border: 1px solid #aaa;
    border-radius: 2px;
    background: #f0f0f0;
    cursor: pointer;
    font-size: 11px;
    color: #555;
    display: flex;
    align-items: center;
    justify-content: center;
}
.cc-search-icon-btn:hover { background: #ddd; }
 
/* ── Section header (blue band) ─────────────────────────────── */
.cc-section {
    margin-top: 10px;
}
.cc-section-header {
    display: flex;
    align-items: center;
    background: #d9e8f5;
    border: 1px solid #b8d4ea;
    border-bottom: none;
    padding: 4px 8px;
    font-size: 12px;
    color: #222;
    gap: 8px;
}
.cc-section-header strong {
    font-size: 12px;
    font-weight: 700;
}
.cc-section-summary {
    font-size: 11px;
    color: #555;
    font-weight: 400;
}
 
/* ── View All / Selected toggle bar ─────────────────────────── */
.cc-view-toggle-bar {
    display: flex;
    align-items: center;
    gap: 4px;
    padding: 3px 8px;
    border: 1px solid #b8d4ea;
    border-bottom: none;
    background: #fff;
    font-size: 11px;
}
.cc-view-toggle {
    color: #1a6dbf;
    text-decoration: none;
    font-size: 11px;
}
.cc-view-toggle:hover { text-decoration: underline; }
.cc-view-sep { color: #aaa; }
 
/* ── Table ───────────────────────────────────────────────────── */
.cc-table-wrap {
    overflow-x: auto;
    border: 1px solid #b8d4ea;
}
.cc-table {
    width: 100%;
    border-collapse: collapse;
    table-layout: fixed;   /* fixed layout — column widths are explicit */
    font-size: 12px;
}
 
/* Column widths — match screenshot proportions exactly */
.cc-col-chk     { width: 28px; }
.cc-col-service { width: 75px; }
.cc-col-activity{ width: 110px; }
.cc-col-party   { width: 115px; }
.cc-col-code    { width: 95px; }
.cc-col-desc    { width: auto; }   /* takes remaining space */
.cc-col-qty     { width: 58px; }
.cc-col-basedon { width: 90px; }
.cc-col-uom     { width: 45px; }
 
.cc-table thead tr {
    background: #e8edf2;
}
.cc-table thead th {
    padding: 4px 5px;
    font-size: 11px;
    font-weight: 700;
    color: #333;
    border: 1px solid #c8d4de;
    white-space: nowrap;
    text-align: left;
    overflow: hidden;
    text-overflow: ellipsis;
}
.cc-table thead th:first-child {
    text-align: center;
}
.cc-table tbody td {
    padding: 3px 4px;
    border: 1px solid #dde3ea;
    vertical-align: middle;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    font-size: 12px;
    color: #222;
}
.cc-table tbody tr:hover {
    background: #f0f5fb;
    cursor: pointer;
}
.cc-table tbody td:first-child {
    text-align: center;
}
 
/* Compact inline inputs inside table cells */
.cc-table .cc-cell-input {
    width: 100%;
    height: 22px;
    padding: 1px 4px;
    font-size: 11px;
    border: 1px solid #bbb;
    border-radius: 1px;
    background: #fff;
    color: #222;
    box-sizing: border-box;
}
.cc-table .cc-cell-select {
    width: 100%;
    height: 22px;
    padding: 0 2px;
    font-size: 11px;
    border: 1px solid #bbb;
    border-radius: 1px;
    background: #fff;
    color: #222;
    box-sizing: border-box;
}
/* Inline search button beside inputs in cells */
.cc-table .cc-cell-group {
    display: flex;
    align-items: center;
    gap: 2px;
}
.cc-table .cc-cell-search-btn {
    height: 22px;
    width: 20px;
    flex-shrink: 0;
    padding: 0;
    border: 1px solid #bbb;
    border-radius: 1px;
    background: #f0f0f0;
    cursor: pointer;
    font-size: 10px;
    color: #666;
    display: flex;
    align-items: center;
    justify-content: center;
}
.cc-table .cc-cell-search-btn:hover { background: #ddd; }
 
/* Empty state message in table */
.cc-empty-msg {
    text-align: center;
    color: #888;
    font-style: italic;
    font-size: 11px;
    padding: 8px 4px !important;
}
 
/* ── Pagination ──────────────────────────────────────────────── */
.cc-pagination {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 4px 6px;
    border: 1px solid #b8d4ea;
    border-top: 1px solid #dde3ea;
    background: #f7f9fc;
    font-size: 11px;
    color: #444;
}
.cc-pag-controls {
    display: flex;
    align-items: center;
    gap: 3px;
}
.cc-pag-btn {
    height: 22px;
    min-width: 22px;
    padding: 0 4px;
    border: 1px solid #bbb;
    border-radius: 2px;
    background: #f0f0f0;
    font-size: 10px;
    cursor: pointer;
    color: #444;
    line-height: 1;
}
.cc-pag-btn:hover { background: #ddd; }
.cc-pag-label {
    font-size: 11px;
    color: #555;
    white-space: nowrap;
}
.cc-pag-input {
    width: 38px;
    height: 22px;
    text-align: center;
    font-size: 11px;
    border: 1px solid #bbb;
    border-radius: 2px;
    padding: 0 2px;
}
.cc-pag-size {
    height: 22px;
    font-size: 11px;
    border: 1px solid #bbb;
    border-radius: 2px;
    padding: 0 2px;
    background: #fff;
    margin-left: 4px;
    width: 52px;
}
.cc-pag-range {
    font-size: 11px;
    color: #666;
    white-space: nowrap;
}
 
/* ── Footer ──────────────────────────────────────────────────── */
.cc-modal-footer {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 14px;
    background: #fff;
    border-top: 1px solid #ccc;
}
/* Save — olive/yellow-green to match system palette */
.cc-btn-save {
    height: 26px;
    padding: 0 16px;
    font-size: 12px;
    font-weight: 600;
    background: #a8b400;
    color: #fff;
    border: 1px solid #8a9400;
    border-radius: 2px;
    cursor: pointer;
}
.cc-btn-save:hover:not(:disabled) { background: #8a9400; }
.cc-btn-save:disabled {
    background: #c8cc80;
    border-color: #b0b870;
    cursor: not-allowed;
    color: #eee;
}
.cc-footer-or {
    font-size: 12px;
    color: #777;
}
.cc-btn-cancel {
    font-size: 12px;
    color: #1a6dbf;
    text-decoration: none;
    font-weight: 500;
}
.cc-btn-cancel:hover { text-decoration: underline; color: #0d4d9c; }
 
/* ── Search modal controls ───────────────────────────────────── */
.cc-search-controls {
    display: flex;
    align-items: center;
    gap: 6px;
    flex-wrap: wrap;
}
.cc-select {
    height: 24px;
    font-size: 12px;
    border: 1px solid #aaa;
    border-radius: 2px;
    padding: 0 4px;
    background: #fff;
    color: #222;
}
.cc-btn-search-go {
    height: 24px;
    padding: 0 12px;
    font-size: 12px;
    font-weight: 700;
    background: #a8b400;
    color: #fff;
    border: 1px solid #8a9400;
    border-radius: 2px;
    cursor: pointer;
    white-space: nowrap;
}
.cc-btn-search-go:hover { background: #8a9400; }
.cc-btn-add-filter {
    height: 24px;
    padding: 0 10px;
    font-size: 12px;
    background: #f0f0f0;
    color: #444;
    border: 1px solid #bbb;
    border-radius: 2px;
    cursor: pointer;
}
.cc-btn-add-filter:hover { background: #ddd; }
 
/* Search results table radio column */
#ccSearchResultsTable td:first-child,
#ccSearchResultsTable th:first-child {
    text-align: center;
    width: 40px;
}
 
/* ── Backdrop fix: search modal needs its own backdrop layer ─── */
#ccSearchModal.modal.show ~ .modal-backdrop {
    z-index: 1055;
}
    </style>
@stop



@section('js')
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>

    <script>
        const isCopiedBooking = {{ request()->query('copied', 0) ? 'true' : 'false' }};
        const quotationId = '{{ $quotation->ID ?? '' }}';
        const officeId = '{{ $quotation->OfficeID ?? '' }}'; // ← ADD
        const tcAlreadyApplied = {{ $quotation->QuotationTCTemplate ? 'true' : 'false' }};
        const quotationSVName = '{{ trim($quotation->SVName ?? '') }}';
        const quotationProcess = '{{ trim($quotation->process ?? '') }}';
        // Mirrors QuotationController::OFFICE_BASE_CURRENCY — single source of truth via PHP
        const OFFICE_BASE_CURRENCY = @json(\App\Http\Controllers\QuotationController::OFFICE_BASE_CURRENCY);


        $(document).ready(function() {

            console.log('Booking Quotation Edit - Page Loaded');

            // ============================================================
            // COPIED FLAG — highlight mandatory empty fields after duplication
            // ============================================================
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('copied') === '1') {

                // Fields that must be filled after duplication
                const mandatoryFields = [
                    '[data-field="username"]', // Sales Person
                    '#shipmentModeSelect', // Shipment Mode
                    '#transportModeSelect', // Transport Mode
                    '#originCountrySelect', // Origin Country
                    '#destCountrySelect', // Destination Country
                    '#incoTermSelect', // InCo Term
                    '#serviceClassSelect', // Service Class
                    '[data-field="commodity"]', // Commodity
                ];

                mandatoryFields.forEach(function(selector) {
                    const $el = $(selector);
                    if ($el.length && !$el.val()) {
                        $el.closest('.form-group').find('label').append(
                            ' <span class="text-danger mandatory-flag">*</span>'
                        );
                        $el.css('border-color', '#dc3545');
                    }
                });
            }

            // ============================================
            // GLOBAL VARIABLES
            // ============================================
            const bookingNo = '{{ $quotation->bookingno ?? '' }}';
            const quotationStatus = '{{ $quotation->status ?? 'Proposal' }}';
            const teamId = '{{ $quotation->teamid ?? '' }}';
            let revenueCharges = [];
            let costCharges = [];
            let currentEditingCharge = null;
            let currentEditingType = null;
            let currencyMap = {};
            let officeCode = '{{ trim($quotation->OfficeCode ?? '') }}';

            // ── Single source of truth for Generate Quote gate ────────────────────────
            // All paths that touch the button must go through setGenerateBtn().
            // Direct calls to .prop('disabled', ...) on #generateQuoteBtn are banned
            // outside this function — that's how the state got scattered before.
            const termsState = {
                applied: false, // true only after a successful save-terms call
                generating: false, // true while the POST is in flight
            };

            const termsDescriptionCache = {};

            function setGenerateBtn() {
                const $btn = $('#generateQuoteBtn');
                const shouldDisable = termsState.generating || !termsState.applied;
                $btn.prop('disabled', shouldDisable);
                if (termsState.generating) {
                    $btn.attr('title', 'Generating…');
                } else if (!termsState.applied) {
                    $btn.attr('title', 'Select a Terms & Conditions template first');
                } else {
                    $btn.removeAttr('title');
                }
            }

            function getBaseCurrency() {
                return OFFICE_BASE_CURRENCY[officeCode] ?? 'KES';
            }

            function updateBaseCurrencyLabels() {
                const bc = getBaseCurrency();
                $('#revenue_base_currency_label').text('(' + bc + ')');
                $('#cost_base_currency_label').text('(' + bc + ')');
                $('.office-base-currency').text(bc);
            }

            // Hidden fields to hold the ClientID and ClientCode selected from Company Name autocomplete
            $('input[data-field="companyname"]').closest('.form-group').find('.col-sm-8')
                .append('<input type="hidden" id="selectedClientId" value="">')
                .append('<input type="hidden" id="selectedClientCode" value="">');
                $('#selectedClientCode').val('{{ $quotation->pri_id ?? '' }}');
                $('#selectedClientId').val('{{ $quotation->ClientIDINSCM ?? '' }}');



            // ============================================
            // SAVED VALUES — read once from PHP, used for pre-selection
            // ============================================
            const savedShipmentMode = '{{ $quotation->shipmentmode ?? '' }}';
            const savedTransportMode = '{{ $quotation->TransportMode ?? '' }}';

            // ============================================
            // SHIPMENT MODE DROPDOWN
            // sp_ShipmentMode returns: Id, Code
            // Stored value is Code — match on Code for pre-selection
            // ============================================
            function loadShipmentModes() {
                $.ajax({
                    url: '{{ route('quotation.shipment-modes') }}',
                    type: 'GET',
                    success: function(response) {
                        const $select = $('#shipmentModeSelect');
                        $select.empty().append('<option value="">-- Select --</option>');

                        if (response.success && response.data.length > 0) {
                            response.data.forEach(function(item) {
                                const selected = item.Code === savedShipmentMode ? 'selected' :
                                    '';
                                $select.append(
                                    `<option value="${item.Code}" ${selected}>${item.Code}</option>`
                                );
                            });
                        } else {
                            $select.append('<option value="" disabled>No options available</option>');
                        }
                    },
                    error: function(xhr) {
                        console.error('loadShipmentModes failed:', xhr);
                        $('#shipmentModeSelect')
                            .empty()
                            .append('<option value="" disabled>Failed to load</option>');
                    }
                });
            }

            // ============================================
            // TRANSPORT MODE DROPDOWN
            // sp_TransportMode returns: Id, Code, Name
            // Stored value is Code — display Name, match on Code for pre-selection
            // ============================================
            function loadTransportModes() {
                $.ajax({
                    url: '{{ route('quotation.transport-modes') }}',
                    type: 'GET',
                    success: function(response) {
                        const $select = $('#transportModeSelect');
                        $select.empty().append('<option value="">-- Select --</option>');

                        if (response.success && response.data.length > 0) {
                            response.data.forEach(function(item) {
                                const selected = item.Code === savedTransportMode ? 'selected' :
                                    '';
                                $select.append(
                                    `<option value="${item.Code}" ${selected}>${item.Name}</option>`
                                );
                            });
                        } else {
                            $select.append('<option value="" disabled>No options available</option>');
                        }
                    },
                    error: function(xhr) {
                        console.error('loadTransportModes failed:', xhr);
                        $('#transportModeSelect')
                            .empty()
                            .append('<option value="" disabled>Failed to load</option>');
                    }
                });
            }

            // Load both dropdowns on page init
            loadShipmentModes();
            loadTransportModes();

            // ============================================
            // SHIPMENT DETAILS — EDIT / CANCEL / SAVE
            // Mirrors the Customer Details Edit/Save/Cancel pattern.
            // Both selects stay disabled until Edit is clicked.
            // ============================================
            let originalShipmentMode = '';
            let originalTransportMode = '';
            let originalOriginCountry = '';
            let originalOriginCountryName = '';
            let originalDestCountry = '';
            let originalDestCountryName = '';
            let shipmentCountriesLoaded = false;
            let incoTermsLoaded = false;
            let originalIncoTerm = '';

            let serviceClassesLoaded = false;
            let originalServiceClass = '';
            let originalServiceClassDisplay = '';

            function loadServiceClasses(callback) {
                if (serviceClassesLoaded) {
                    if (typeof callback === 'function') callback();
                    return;
                }
                const savedCode = '{{ $quotation->ServiceClass ?? '' }}';
                $.ajax({
                    url: '{{ route('quotation.lookup.serviceClasses') }}',
                    type: 'GET',
                    data: {
                        filter: ''
                    },
                    success: function(data) {
                        const list = Array.isArray(data) ? data : [];
                        const $sel = $('#serviceClassSelect').empty()
                            .append('<option value="">-- Select --</option>');
                        list.forEach(function(item) {
                            const code = (item.Code || item.ServiceClass || '').trim();
                            const name = (item.Name || item.Description || code).trim();
                            $sel.append(
                                `<option value="${code}" ${code === savedCode ? 'selected' : ''}>${name}</option>`
                            );
                        });
                        serviceClassesLoaded = true;
                    },
                    complete: function() {
                        if (typeof callback === 'function') callback();
                    }
                });
            }

            function loadShipmentCountries(callback) {
                if (shipmentCountriesLoaded) {
                    if (typeof callback === 'function') callback();
                    return;
                }
                const savedOriginCode = '{{ $quotation->origin_country_code ?? '' }}';
                const savedDestCode = '{{ $quotation->destination_country_code ?? '' }}';
                $.ajax({
                    url: '{{ route('quotation.lookup.countries') }}',
                    type: 'GET',
                    data: {
                        filter: ''
                    },
                    success: function(data) {
                        const list = Array.isArray(data) ? data : [];

                        function fill(selectId, savedCode) {
                            const $sel = $('#' + selectId).empty()
                                .append('<option value="">-- Select --</option>');
                            list.forEach(function(c) {
                                const name = (c.CountryName || '').trim();
                                const code = (c.CountryCode || '').trim();
                                $sel.append(
                                    `<option value="${code}" data-name="${name}" ${code === savedCode ? 'selected' : ''}>${name}</option>`
                                );
                            });
                        }
                        fill('originCountrySelect', savedOriginCode);
                        fill('destCountrySelect', savedDestCode);
                        shipmentCountriesLoaded = true;
                    },
                    complete: function() {
                        if (typeof callback === 'function') callback();
                    }
                });
            }

            $('#editShipmentDetailsBtn').on('click', function() {
                originalShipmentMode = $('#shipmentModeSelect').val();
                originalTransportMode = $('#transportModeSelect').val();
                originalIncoTerm = $('#incoTermDisplay').val();
                originalOriginCountry = $('#originCountrySelect').val(); // code
                originalOriginCountryName = $('#originCountryDisplay').val(); // name
                originalDestCountry = $('#destCountrySelect').val(); // code
                originalDestCountryName = $('#destCountryDisplay').val(); // name
                $('#shipmentModeSelect, #transportModeSelect').prop('disabled', false);

                if (!incoTermsLoaded) {
                    const savedIncoTerm = '{{ $quotation->incoterm ?? '' }}';
                    $.ajax({
                        url: '{{ route('quotation.inco-terms') }}',
                        type: 'GET',
                        success: function(r) {
                            const $sel = $('#incoTermSelect').empty().append(
                                '<option value="">-- Select --</option>');
                            (r.success ? r.data : []).forEach(function(item) {
                                const code = item.IncoTermCode || '';
                                $sel.append(
                                    `<option value="${code}" ${code === savedIncoTerm ? 'selected' : ''}>${code} — ${item.Name}</option>`
                                );
                            });
                            incoTermsLoaded = true;
                        }
                    });
                }

                $('#incoTermDisplay').addClass('d-none');
                $('#incoTermSelect').removeClass('d-none').prop('disabled', false);

                originalServiceClass = $('#serviceClassSelect').val();
                originalServiceClassDisplay = $('#serviceClassDisplay').val();

                loadShipmentCountries(function() {
                    $('#originCountryDisplay').hide();
                    $('#originCountryEditWrap').show();
                    $('#destCountryDisplay').hide();
                    $('#destCountryEditWrap').show();
                });

                loadServiceClasses(function() {
                    $('#serviceClassDisplay').addClass('d-none');
                    $('#serviceClassSelect').removeClass('d-none').prop('disabled', false);
                });


                $(this).hide();
                $('#saveShipmentDetailsBtn, #cancelShipmentDetailsBtn').show();
            });

            $('#cancelShipmentDetailsBtn').on('click', function() {
                $('#shipmentModeSelect').val(originalShipmentMode);
                $('#transportModeSelect').val(originalTransportMode);
                $('#incoTermSelect').val(originalIncoTerm);
                $('#originCountrySelect').val(originalOriginCountry);
                $('#destCountrySelect').val(originalDestCountry);

                $('#shipmentModeSelect, #transportModeSelect').prop('disabled', true);

                $('#incoTermSelect').addClass('d-none').prop('disabled', true);
                $('#incoTermDisplay').removeClass('d-none').val(originalIncoTerm);

                $('#serviceClassSelect').val(originalServiceClass).addClass('d-none').prop('disabled',
                    true);
                $('#serviceClassDisplay').val(originalServiceClassDisplay).removeClass('d-none');

                $('#originCountryEditWrap').hide();
                $('#originCountryDisplay').val(originalOriginCountryName).show();
                $('#destCountryEditWrap').hide();
                $('#destCountryDisplay').val(originalDestCountryName).show();

                $(this).hide();
                $('#saveShipmentDetailsBtn').hide();
                $('#editShipmentDetailsBtn').show();
            });

            $('#saveShipmentDetailsBtn').on('click', function() {
                const shipmentMode = $('#shipmentModeSelect').val();
                const transportMode = $('#transportModeSelect').val();
                const incoTerm = $('#incoTermSelect').val();
                const originCountry = $('#originCountrySelect').val();
                const destCountry = $('#destCountrySelect').val();
                const serviceClass = $('#serviceClassSelect').val();

                if (!shipmentMode) {
                    showNotification('Please select a Shipment Mode', 'error');
                    return;
                }
                if (!transportMode) {
                    showNotification('Please select a Transport Mode', 'error');
                    return;
                }

                $.ajax({
                    url: '{{ route('quotation.shipment.save') }}',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        quotation_id: quotationId,
                        shipment_mode: shipmentMode,
                        transport_mode: transportMode,
                        incoterm: incoTerm,
                        origincountry: originCountry,
                        destinationcountry: destCountry,
                        service_class: serviceClass,
                    },
                    beforeSend: function() {
                        $('#saveShipmentDetailsBtn').prop('disabled', true)
                            .html('<i class="fas fa-spinner fa-spin mr-1"></i> Saving...');
                    },
                    success: function(response) {
                        if (response.success) {
                            showNotification('Shipment details saved successfully', 'success');

                            originalShipmentMode = shipmentMode;
                            originalTransportMode = transportMode;
                            originalOriginCountry = originCountry;
                            originalDestCountry = destCountry;

                            $('#shipmentModeSelect, #transportModeSelect').prop('disabled',
                                true);

                            $('#incoTermSelect').addClass('d-none').prop('disabled', true);
                            $('#incoTermDisplay').removeClass('d-none').val(incoTerm);
                            originalIncoTerm = incoTerm;

                            $('#originCountryEditWrap').hide();
                            $('#originCountryDisplay').val(
                                $('#originCountrySelect option:selected').data('name')
                            ).show();
                            $('#destCountryEditWrap').hide();
                            $('#destCountryDisplay').val(
                                $('#destCountrySelect option:selected').data('name')
                            ).show();


                            $('#serviceClassSelect').addClass('d-none').prop('disabled', true);
                            $('#serviceClassDisplay').val(
                                $('#serviceClassSelect option:selected').text()
                            ).removeClass('d-none');
                            originalServiceClass = serviceClass;
                            originalServiceClassDisplay = $(
                                '#serviceClassSelect option:selected').text();

                            $('#saveShipmentDetailsBtn, #cancelShipmentDetailsBtn').hide();
                            $('#editShipmentDetailsBtn').show();
                        } else {
                            showNotification(response.message || 'Failed to save', 'error');
                        }
                    },
                    error: function(xhr) {
                        showNotification(xhr.responseJSON?.message || 'Failed to save',
                            'error');
                    },
                    complete: function() {
                        $('#saveShipmentDetailsBtn').prop('disabled', false)
                            .html('<i class="fas fa-save mr-1"></i> Save');
                    }
                });
            });

            updateBaseCurrencyLabels();


            // ── T&C Gate ─────────────────────────────────────────────
            // Unblock Generate Quote immediately if T&C was previously applied
            // Seed termsState from server-rendered flag so the gate
            // reflects reality on page load without an extra API round-trip.
            if (tcAlreadyApplied) {
                termsState.applied = true;
            }
            setGenerateBtn(); // single authoritative call — no direct .prop() here

            console.log('Booking Number:', bookingNo);
            console.log('Quotation Status:', quotationStatus);
            console.log('Team ID:', teamId);

            // ============================================
            // MODAL CLOSE HANDLERS
            // ============================================
            $('#addRevenueModal .close, #addRevenueModal button[data-dismiss="modal"]').on('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                $('#addRevenueModal').modal('hide');
            });

            $('#addCostModal .close, #addCostModal button[data-dismiss="modal"]').on('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                $('#addCostModal').modal('hide');
            });

            $('#xmlViewerModal .close, #xmlViewerModal button[data-dismiss="modal"]').on('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                $('#xmlViewerModal').modal('hide');
            });

            $(document).on('click', '#statusModal .close, #statusModal button[data-dismiss="modal"]', function(e) {
                e.preventDefault();
                e.stopPropagation();
                $('#statusModal').modal('hide');
            });

            // ============================================
            // ESC KEY & BACKDROP HANDLERS
            // ============================================
            $(document).on('keydown', function(e) {
                if (e.key === 'Escape' || e.keyCode === 27) {
                    $('.modal.show').modal('hide');
                }
            });

            $('#addRevenueModal, #addCostModal, #xmlViewerModal').on('click', function(e) {
                if ($(e.target).hasClass('modal')) {
                    $(this).modal('hide');
                }
            });

            $(document).on('click', '#statusModal', function(e) {
                if ($(e.target).hasClass('modal')) {
                    $(this).modal('hide');
                }
            });

            // ============================================
            // MODAL CLEANUP ON CLOSE
            // ============================================
            $('#addRevenueModal').on('hidden.bs.modal', function() {
                console.log('Revenue Modal hidden, resetting form');
                clearRevenueModalForm();
                currentEditingCharge = null;
                currentEditingType = null;
            });

            $('#addCostModal').on('hidden.bs.modal', function() {
                console.log('Cost Modal hidden, resetting form');
                clearCostModalForm();
                currentEditingCharge = null;
                currentEditingType = null;
            });

            // ============================================
            // LOAD EXISTING CHARGES ON PAGE LOAD
            // ============================================
            if (bookingNo) {
                loadCharges();
            } else {
                console.error('No booking number found!');
                showNotification('Cannot load charges: No booking number', 'error');
            }

            function loadCharges() {
                $.ajax({
                    url: '{{ route('quotation.charges') }}',
                    type: 'GET',
                    data: {
                        booking_no: bookingNo,
                        office_code: officeCode,
                        quotation_id: quotationId,
                    },
                    beforeSend: function() {
                        $('#revenueChargesTable, #costChargesTable').html(
                            '<tr><td colspan="6" class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading...</td></tr>'
                        );
                    },
                    success: function(response) {
                        if (response.success) {
                            revenueCharges = response.revenue || [];
                            costCharges = response.cost || [];
                            renderRevenueCharges();
                            renderCostCharges();
                            updateSummary();
                        } else {
                            showNotification('Failed to load charges', 'error');
                        }
                    },
                    error: function() {
                        showNotification('Failed to load charges', 'error');
                        $('#revenueChargesTable, #costChargesTable').html(
                            '<tr><td colspan="6" class="text-center text-danger">Failed to load</td></tr>'
                        );
                    }
                });
            }

            // ============================================
            // MODAL TRIGGERS
            // ============================================

            // NOTE: "Add Charges" opens the Revenue modal with Revenue pre-selected.
            // The radio buttons inside allow switching to Cost or Cost with Markup.
            // saveCharge() reads the radio value — not the modal name — to determine
            // revenueOrCost, so switching the radio correctly saves as Cost.
            $('#openChargesModal').on('click', function(e) {
                e.preventDefault();
                currentEditingCharge = null;
                currentEditingType = 'revenue';
                clearRevenueModalForm();
                $('#addRevenueModal').modal('show');
            });

            // ============================================
            // MODAL INITIALIZATION - SHOWN EVENT
            // ============================================
            $('#addRevenueModal').on('show.bs.modal', function() {
                $(this).find('.increase-by-row').removeClass('active').css('display', 'none');
                loadEquipmentTypeOptions(function() {}); // ← pre-load silently on modal open
                if (!currentEditingCharge) {
                    $('#revenueTypeRevenue').prop('checked', true);
                    toggleRevenueFormFields('revenue');
                    loadBasedOnList('revenue');
                    populateCurrencyDropdown('revenue');
                } else {
                    populateCurrencyDropdown('revenue');
                }
                setTimeout(function() {
                    initializeChargeCodeAutocomplete('revenue');
                    initializeBillToPartyAutocomplete('revenue');
                }, 100);
            });

            $('#addCostModal').on('show.bs.modal', function() {
                $(this).find('.cost-markup-row').removeClass('active').css('display', 'none');
                loadEquipmentTypeOptions(function() {}); // ← pre-load silently on modal open
                if (!currentEditingCharge) {
                    $('#costTypeCost').prop('checked', true);
                    toggleCostFormFields('cost');
                    $('#costIsReimbursedContainer').show();
                    loadBasedOnList('cost');
                    populateCurrencyDropdown('cost');
                } else {
                    populateCurrencyDropdown('cost');
                }
                setTimeout(function() {
                    initializeChargeCodeAutocomplete('cost');
                    initializeBillToPartyAutocomplete('cost');
                }, 100);
            });

            // ============================================
            // POPULATE CURRENCY DROPDOWN
            // ============================================
            function populateCurrencyDropdown(type) {
                const prefix = type === 'revenue' ? 'revenue' : 'cost';
                const $currencySelect = $(`select[name="${prefix}_currency"]`);

                if ($currencySelect.data('populated')) return;

                // if (!officeCode && bookingNo) {
                //     $.ajax({
                //         url: '/quotation/office-code',
                //         type: 'GET',
                //         data: {
                //             team_id: teamId
                //         },
                //         async: false,
                //         success: function(response) {
                //             if (response.success) officeCode = response.office_code.trim();
                //         }
                //     });
                // }

                if (!officeCode) {
                    console.error('populateCurrencyDropdown: officeCode not available');
                    return;
                }

                if (!officeCode) {
                    console.error('Office code not available');
                    return;
                }

                $.ajax({
                    url: '/quotation/currencies',
                    type: 'GET',
                    data: {
                        office_id: officeId
                    },
                    beforeSend: function() {
                        $currencySelect.prop('disabled', true).empty().append(
                            '<option value="">Loading...</option>');
                    },
                    success: function(response) {
                        if (response.success && response.currencies && response.currencies.length > 0) {
                            $currencySelect.empty().append('<option value="">Select</option>');
                            currencyMap = {};
                            response.currencies.forEach(function(currency) {
                                currencyMap[currency.CurrencyCode] = currency.CurrencyID;
                                $currencySelect.append(
                                    `<option value="${currency.CurrencyID}" data-code="${currency.CurrencyCode}">${currency.CurrencyCode}</option>`
                                );
                            });
                            $currencySelect.data('populated', true).prop('disabled', false);
                            console.log('Currencies loaded:', Object.keys(currencyMap));
                        } else {
                            $currencySelect.empty().append(
                                '<option value="">No currencies available</option>');
                        }
                    },
                    error: function(xhr) {
                        console.error('Failed to load currencies:', xhr);
                        $currencySelect.empty().append('<option value="">Failed to load</option>');
                    }
                });
            }

            // ============================================
            // AUTO-FETCH EXCHANGE RATE ON CURRENCY CHANGE
            // ============================================
            // Format date as '05/Mar/2026' to match fn_GetCurrentExRateUsingCodeandPeriod signature
            function formatDateForExRate(date) {
                const d = date || new Date();
                return d.toISOString().split('T')[0]; // '2026-03-09' — unambiguous, Carbon parses reliably
            }

            $(document).on('change', 'select[name="revenue_currency"], select[name="cost_currency"]', function() {
                const $currencySelect = $(this);
                const prefix = $currencySelect.attr('name').split('_')[0];
                const currencyCode = $currencySelect.find('option:selected').data('code');
                const $exchangeRateInput = $(`input[name="${prefix}_exchange_rate"]`);

                if (!currencyCode || !officeCode) {
                    console.warn('Currency code or office code missing', {
                        currencyCode,
                        officeCode
                    });
                    return;
                }

                const baseCurrency = OFFICE_BASE_CURRENCY[officeCode] ?? 'KES';

                if (currencyCode === baseCurrency) {
                    $exchangeRateInput.val('1.000000');
                    calculateAmount(prefix);
                    return;
                }

                const dateFor = formatDateForExRate(new Date());

                console.log('Fetching exchange rate', {
                    officeCode,
                    currencyCode,
                    baseCurrency,
                    dateFor
                });

                $.ajax({
                    url: '/quotation/exchange-rate',
                    type: 'GET',
                    data: {
                        office_code: officeId,
                        from_currency: currencyCode,
                        to_currency: baseCurrency,
                        date_for: dateFor
                    },
                    beforeSend: function() {
                        $exchangeRateInput.val('').prop('readonly', true);
                    },
                    success: function(response) {
                        if (response.success && response.exchange_rate) {
                            $exchangeRateInput.val(parseFloat(response.exchange_rate).toFixed(
                                6));
                            calculateAmount(prefix);
                        } else {
                            showNotification(
                                response.message ||
                                `No rate found for ${currencyCode} → ${baseCurrency}`,
                                'warning'
                            );
                            $exchangeRateInput.val('');
                        }
                    },
                    error: function(xhr) {
                        // Surface the actual server message — 404 = rate not configured, 500 = DB error
                        const serverMsg = xhr.responseJSON?.message;
                        const statusCode = xhr.status;

                        if (statusCode === 404) {
                            showNotification(
                                serverMsg ||
                                `No exchange rate found for ${currencyCode} → ${baseCurrency} on ${dateFor}. Please enter manually.`,
                                'warning' // warning, not error — rate may just not be set up for this date
                            );
                        } else {
                            showNotification(
                                serverMsg ||
                                'Exchange rate lookup failed. Please enter manually.',
                                'error'
                            );
                        }

                        console.error('Exchange rate fetch failed', {
                            status: statusCode,
                            message: serverMsg,
                            officeCode,
                            currencyCode,
                            baseCurrency,
                            dateFor
                        });

                        $exchangeRateInput.val('').prop('readonly',
                            false); // Let user enter manually
                    },
                    complete: function() {
                        $exchangeRateInput.prop('readonly', true);
                    }
                });
            });

            // ============================================
            // LOAD BASED ON LIST
            // ============================================
            function loadBasedOnList(type) {
                const prefix = type === 'revenue' ? 'revenue' : 'cost';
                const $basedOnSelect = $(`select[name="${prefix}_based_on"]`);

                $.ajax({
                    url: '{{ route('quotation.based-on-list') }}',
                    type: 'GET',
                    beforeSend: function() {
                        $basedOnSelect.prop('disabled', true).empty().append(
                            '<option value="">Loading...</option>');
                    },
                    success: function(response) {
                        if (response.success) {
                            $basedOnSelect.empty().append('<option value="">Select Based On</option>');
                            response.based_on_list.forEach(function(item) {
                                $basedOnSelect.append(
                                    `<option value="${item.Code}" data-id="${item.Id}">${item.Name}</option>`
                                );
                            });
                            $basedOnSelect.prop('disabled', false);
                        } else {
                            showNotification('Failed to load Based On list', 'error');
                            $basedOnSelect.empty().append('<option value="">Failed to load</option>');
                        }
                    },
                    error: function() {
                        showNotification('Failed to load Based On list', 'error');
                        $basedOnSelect.empty().append('<option value="">Failed to load</option>').prop(
                            'disabled', false);
                    }
                });
            }

            function loadBasedOnUOM(type, basedOnCode) {
                const prefix = type === 'revenue' ? 'revenue' : 'cost';
                const $uomSelect = $(`select[name="${prefix}_based_on_uom"]`);

                if (!basedOnCode) {
                    $uomSelect.empty()
                        .append('<option value="">Select Based On first</option>')
                        .prop('disabled', true);
                    return;
                }

                // ── NEW: Equipment branch (mirrors loadBasedOnUOMWithCallback) ───────────────
                if (basedOnCode === 'Equipment') {
                    $uomSelect.empty()
                        .append('<option value="">-- Select Container Type --</option>')
                        .prop('disabled', true);

                    loadEquipmentTypeOptions(function() {
                        $uomSelect.empty()
                            .append('<option value="">-- Select Container Type --</option>');
                        equipmentTypeOptions.forEach(function(item) {
                            $uomSelect.append(
                                `<option value="${item.Code}">${item.Code}</option>`
                            );
                        });
                        $uomSelect.prop('disabled', false).removeAttr('title');
                    });
                    return;
                }
                // ── END NEW ──────────────────────────────────────────────────────────────────

                $.ajax({
                    url: '{{ route('quotation.based-on-uom') }}',
                    type: 'GET',
                    data: {
                        based_on_code: basedOnCode
                    },
                    beforeSend: function() {
                        $uomSelect.prop('disabled', true).empty().append(
                            '<option value="">Loading...</option>');
                    },
                    success: function(response) {
                        if (response.success) {
                            $uomSelect.empty();
                            if (!response.uom_list || response.uom_list.length === 0) {
                                $uomSelect.append('<option value="">No UOM Required</option>');
                                $uomSelect.prop('disabled', true)
                                    .attr('title', 'No UOM required for this Based On selection');
                            } else {
                                $uomSelect.append('<option value="">Select UOM</option>');
                                response.uom_list.forEach(function(uom) {
                                    $uomSelect.append(
                                        `<option value="${uom.Code}">${uom.Name}</option>`);
                                });
                                $uomSelect.prop('disabled', false).removeAttr('title');
                            }
                        } else {
                            showNotification('Failed to load UOM list', 'error');
                            $uomSelect.empty().append('<option value="">Failed to load</option>');
                        }
                    },
                    error: function() {
                        showNotification('Failed to load UOM list', 'error');
                        $uomSelect.empty().append('<option value="">Error loading</option>');
                    }
                });
            }

            // Callback-aware version used by populateModal to ensure sequential loading
            function loadBasedOnListWithCallback(type, callback) {
                const prefix = type === 'revenue' ? 'revenue' : 'cost';
                const $basedOnSelect = $(`select[name="${prefix}_based_on"]`);

                $.ajax({
                    url: '{{ route('quotation.based-on-list') }}',
                    type: 'GET',
                    beforeSend: function() {
                        $basedOnSelect.prop('disabled', true)
                            .empty()
                            .append('<option value="">Loading...</option>');
                    },
                    success: function(response) {
                        if (response.success) {
                            $basedOnSelect.empty().append('<option value="">Select Based On</option>');
                            response.based_on_list.forEach(function(item) {
                                $basedOnSelect.append(
                                    `<option value="${item.Code}" data-id="${item.Id}">${item.Name}</option>`
                                );
                            });
                            $basedOnSelect.prop('disabled', false);
                            if (typeof callback === 'function') callback();
                        }
                    },
                    error: function() {
                        $basedOnSelect.empty()
                            .append('<option value="">Failed to load</option>')
                            .prop('disabled', false);
                    }
                });
            }

            function loadBasedOnUOMWithCallback(type, basedOnCode, callback) {
                const prefix = type === 'revenue' ? 'revenue' : 'cost';
                const $uomSelect = $(`select[name="${prefix}_based_on_uom"]`);

                if (!basedOnCode) {
                    $uomSelect.empty()
                        .append('<option value="">Select Based On first</option>')
                        .prop('disabled', true);
                    return;
                }

                // ── NEW: Equipment — UOM select is repurposed as container type picker ──────
                // sp_ChargeBasedOnUOM returns empty for 'Equipment'; equipment types come
                // from sp_EquipmentType (already loaded into equipmentTypeOptions).
                if (basedOnCode === 'Equipment') {
                    $uomSelect.empty()
                        .append('<option value="">-- Select Container Type --</option>')
                        .prop('disabled', true);

                    loadEquipmentTypeOptions(function() {
                        $uomSelect.empty()
                            .append('<option value="">-- Select Container Type --</option>');
                        equipmentTypeOptions.forEach(function(item) {
                            $uomSelect.append(
                                `<option value="${item.Code}">${item.Code}</option>`
                            );
                        });
                        $uomSelect.prop('disabled', false).removeAttr('title');
                        if (typeof callback === 'function') callback();
                    });
                    return;
                }
                // ── END NEW ──────────────────────────────────────────────────────────────────

                $.ajax({
                    url: '{{ route('quotation.based-on-uom') }}',
                    type: 'GET',
                    data: {
                        based_on_code: basedOnCode
                    },
                    beforeSend: function() {
                        $uomSelect.prop('disabled', true).empty().append(
                            '<option value="">Loading...</option>');
                    },
                    success: function(response) {
                        if (response.success) {
                            $uomSelect.empty();
                            if (!response.uom_list || response.uom_list.length === 0) {
                                $uomSelect.append('<option value="">No UOM Required</option>');
                                $uomSelect.prop('disabled', true).attr('title',
                                    'No UOM required for this Based On selection');
                            } else {
                                $uomSelect.append('<option value="">Select UOM</option>');
                                response.uom_list.forEach(function(uom) {
                                    $uomSelect.append(
                                        `<option value="${uom.Code}">${uom.Name}</option>`);
                                });
                                $uomSelect.prop('disabled', false).removeAttr('title');
                            }
                            if (typeof callback === 'function') callback();
                        }
                    },
                    error: function() {
                        $uomSelect.empty().append('<option value="">Error loading</option>').prop(
                            'disabled', false);
                    }
                });
            }

            // ============================================
            // FETCH BASED ON DEFAULTS FROM SP
            // ============================================
            function fetchBasedOnDefaults(prefix, basedOnCode, equipment) {
                if (!bookingNo || !basedOnCode) return;

                console.log('fetchBasedOnDefaults called:', {
                    prefix,
                    basedOnCode,
                    equipment,
                    equipmentHex: equipment ? [...equipment].map(c => c.charCodeAt(0)) : []
                });

                const $modal = prefix === 'revenue' ? $('#addRevenueModal') : $('#addCostModal');

                if (basedOnCode === 'Equipment' && !equipment) {
                    const containerTypes = [];
                    $('#cargoDetailsCard #containerListBody tr[data-container-row]').each(function() {
                        // Edit mode: select element — use .val() directly (reliable)
                        const fromSelect = $(this).find('td:first select.container-type-select').val();
                        if (fromSelect) {
                            containerTypes.push(fromSelect);
                            return;
                        }
                        // Read-only mode: strip child elements before reading text
                        const fromText = $(this).find('td:first').clone()
                            .children().remove().end()
                            .text().trim();
                        if (fromText && fromText !== '—') containerTypes.push(fromText);
                    });

                    if (containerTypes.length > 0) {
                        equipment = containerTypes[0];
                        const $uomSelect = $modal.find(`select[name="${prefix}_based_on_uom"]`);
                        if ($uomSelect.find(`option[value="${equipment}"]`).length) {
                            $uomSelect.val(equipment);
                        }
                    } else {
                        return;
                    }
                }

                $.ajax({
                    url: '{{ route('quotation.charge-based-on-defaults') }}',
                    type: 'GET',
                    data: {
                        booking_no: bookingNo,
                        quote_no: quotationId,
                        based_on: basedOnCode,
                        equipment: equipment || ''
                    },
                    success: function(response) {
                        if (!response.success) return;

                        // ── Equipment: read quantity directly from the cargo details table ──
                        // sp_Quote_ChargeBasedOnDefaultDetails queries Quote_Quotation_EquipmentDetails
                        // which may be out of sync with what's rendered on the page.
                        // The page is the authoritative source for the current session.
                        if (basedOnCode === 'Equipment' && equipment) {
                            // Find the container row matching the selected equipment type
                            let pageQty = 0;
                            $('#cargoDetailsCard #containerListBody tr[data-container-row]').each(
                                function() {
                                    const rowType = $(this).find('.container-type-select').val() ||
                                        $(this).find('td:first').clone().children().remove().end()
                                        .text().trim();
                                    if (rowType === equipment) {
                                        const qtyText = $(this).find('.container-qty-input')
                                            .val() ||
                                            $(this).find('td:eq(1)').text().trim();
                                        pageQty = parseInt(qtyText, 10) || 0;
                                        return false; // break
                                    }
                                });

                            if (pageQty > 0) {
                                $modal.find(`input[name="${prefix}_quantity"]`).val(pageQty);
                            } else if (response.quantity > 0) {
                                // Fallback to SP value if page row not found
                                $modal.find(`input[name="${prefix}_quantity"]`).val(response.quantity);
                            }

                            const $uomSelect = $modal.find(`select[name="${prefix}_based_on_uom"]`);
                            if ($uomSelect.find(`option[value="${equipment}"]`).length) {
                                $uomSelect.val(equipment);
                            }
                        } else {
                            // Non-equipment: SP is authoritative
                            if (response.quantity > 0) {
                                $modal.find(`input[name="${prefix}_quantity"]`).val(response.quantity);
                            }

                            if (response.based_on_uom_code) {
                                const $uomSelect = $modal.find(`select[name="${prefix}_based_on_uom"]`);
                                if ($uomSelect.find(`option[value="${response.based_on_uom_code}"]`)
                                    .length) {
                                    $uomSelect.val(response.based_on_uom_code);
                                }
                            }
                        }

                        calculateAmount(prefix);
                    },
                    error: function(xhr) {
                        console.warn('fetchBasedOnDefaults — request failed', {
                            status: xhr.status,
                            message: xhr.responseJSON?.message
                        });
                    }
                });
            }

            // ============================================
            // FETCH BASED ON DEFAULTS FROM SP
            // Calls sp_Quote_ChargeBasedOnDefaultDetails
            // via the existing getChargeBasedOnDefaults route.
            // Pre-fills:
            //   - Based On UOM select (if UOM list has a match)
            //   - Quantity input
            // @param prefix        'revenue' or 'cost'
            // @param basedOnCode   Selected Based On code
            // @param equipment     Equipment type code ('' unless BasedOn = 'Equipment')
            // ============================================
            $(document).on('change', 'select[name="revenue_based_on"], select[name="cost_based_on"]', function() {
                const $select = $(this);
                const prefix = $select.attr('name').split('_')[0];
                const basedOnCode = $select.val();

                if (!basedOnCode) {
                    loadBasedOnUOM(prefix, basedOnCode);
                    return;
                }

                loadBasedOnUOMWithCallback(prefix, basedOnCode, function() {
                    if (basedOnCode === 'Equipment') {
                        // ── Auto-select first container type from cargo details ────────────
                        // Runs INSIDE the callback — guaranteed after UOM options are rendered.
                        // Handles both read-only (text cell) and edit mode (select element).
                        const $modal = prefix === 'revenue' ? $('#addRevenueModal') : $(
                            '#addCostModal');
                        const containerTypes = [];
                        $('#cargoDetailsCard #containerListBody tr[data-container-row]').each(
                            function() {
                                const fromSelect = $(this).find(
                                    'td:first select.container-type-select').val();
                                if (fromSelect) {
                                    containerTypes.push(fromSelect);
                                    return;
                                }
                                const fromText = $(this).find('td:first').clone()
                                    .children().remove().end().text().trim();
                                if (fromText && fromText !== '—') containerTypes.push(fromText);
                            });

                        if (containerTypes.length > 0) {
                            const firstContainer = containerTypes[0];
                            const $uomSelect = $modal.find(`select[name="${prefix}_based_on_uom"]`);

                            // Confirm option exists before setting — no silent failures
                            if ($uomSelect.find(`option[value="${firstContainer}"]`).length) {
                                // Suppress the UOM change event during programmatic set
                                // to prevent a second fetchBasedOnDefaults call racing
                                // with the one below and overwriting the quantity with 0.
                                $uomSelect.off('change.equipAutoFill')
                                    .val(firstContainer)
                                    .on('change.equipAutoFill', function() {
                                        fetchBasedOnDefaults(prefix, 'Equipment', $(this)
                                            .val());
                                    });
                                fetchBasedOnDefaults(prefix, basedOnCode, firstContainer);
                            } else {
                                console.warn('Equipment option not in UOM select:', firstContainer,
                                    '| Available:', $uomSelect.find('option').map(function() {
                                        return $(this).val();
                                    }).get());
                            }
                        }
                        // No containers found — leave UOM blank, user must add cargo first
                    } else {
                        fetchBasedOnDefaults(prefix, basedOnCode, '');
                    }
                });
            });

            // ============================================
            // AUTO-FILL QUANTITY WHEN BASED ON UOM CHANGES
            // Only fires for Equipment — other Based On types
            // are handled by fetchBasedOnDefaults after UOM loads.
            // ============================================
            $(document).on(
                'change',
                'select[name="revenue_based_on_uom"], select[name="cost_based_on_uom"]',
                function() {
                    const $uomSelect = $(this);
                    const prefix = $uomSelect.attr('name').split('_based_on_uom')[0];
                    const $modal = prefix === 'revenue' ? $('#addRevenueModal') : $('#addCostModal');
                    const basedOn = $modal.find(`select[name="${prefix}_based_on"]`).val();
                    const uomCode = $uomSelect.val();

                    // Only Equipment needs this — other types already handled by fetchBasedOnDefaults.
                    // Skip if this is the programmatic set from the Based On change handler
                    // (that path uses .equipAutoFill namespace and calls fetchBasedOnDefaults directly).
                    if (basedOn !== 'Equipment' || !uomCode) return;

                    // Only fire for genuine user selection — not the auto-fill .val() call
                    if (!$uomSelect.data('suppressEquipFetch')) {
                        fetchBasedOnDefaults(prefix, basedOn, uomCode);
                    }
                }
            );

            // ============================================
            // EQUIPMENT TYPE CHANGE — RE-FETCH DEFAULTS
            // When Based On is 'Equipment' and the user
            // picks a container type from a charge row,
            // re-call the SP with the selected equipment.
            // This is only relevant inside the charge modal
            // when the Equipment-specific UOM/Quantity
            // needs to update after container selection.
            // ============================================
            $(document).on('change', '.container-type-select', function() {
                // Only fires if a charge modal is currently open
                const $openModal = $('#addRevenueModal.show, #addCostModal.show');
                if (!$openModal.length) return;

                const prefix = $openModal.attr('id') === 'addRevenueModal' ? 'revenue' : 'cost';
                const $basedOnSelect = $openModal.find(`select[name="${prefix}_based_on"]`);
                const basedOnCode = $basedOnSelect.val();

                if (basedOnCode !== 'Equipment') return;

                const equipment = $(this).val();
                if (!equipment) return;

                fetchBasedOnDefaults(prefix, basedOnCode, equipment);
            });


            // ============================================
            // CHARGE CODE AUTOCOMPLETE
            // FIX: chargeType is now read dynamically from the selected radio button
            // so searching in the Revenue modal with "Cost" selected fetches Cost codes.
            // ============================================
            function initializeChargeCodeAutocomplete(type) {
                const prefix = type === 'revenue' ? 'revenue' : 'cost';
                const $chargeCodeInput = $(`input[name="${prefix}_charge_code"]`);

                if ($chargeCodeInput.length === 0) {
                    console.error('Charge code input not found for type:', type);
                    return;
                }

                if ($chargeCodeInput.hasClass('ui-autocomplete-input')) {
                    $chargeCodeInput.autocomplete('destroy');
                }

                $chargeCodeInput.autocomplete({
                    source: function(request, response) {
                        if (request.term.length < 3) {
                            response([]);
                            return;
                        }

                        // FIX: Read the currently checked radio to determine charge type dynamically
                        const $modal = type === 'revenue' ? $('#addRevenueModal') : $('#addCostModal');
                        const selectedRadio = $modal.find(`input[name="${prefix}ChargeType"]:checked`)
                            .val();
                        const chargeType = (selectedRadio === 'cost' || selectedRadio ===
                            'costmarkup') ? 'Cost' : 'Revenue';

                        $.ajax({
                            url: '/quotation/charge-codes',
                            type: 'GET',
                            data: {
                                booking_no: bookingNo,
                                office_code: officeCode, // ← add this
                                charge_type: chargeType,
                                filter: request.term
                            },
                            dataType: 'json',
                            success: function(data) {
                                if (data.success && data.charge_codes && data.charge_codes
                                    .length > 0) {
                                    response(data.charge_codes.map(item => ({
                                        label: item.Code + ' - ' + item
                                            .Name,
                                        value: item.Code,
                                        name: item.Name,
                                        currency: item.Curr,
                                        vat: item.VAT
                                    })));
                                } else {
                                    response([{
                                        label: 'No charge codes found matching "' +
                                            request.term + '"',
                                        value: request.term,
                                        disabled: true
                                    }]);
                                }
                            },
                            error: function() {
                                response([{
                                    label: 'Error loading charge codes',
                                    value: '',
                                    disabled: true
                                }]);
                            }
                        });
                    },
                    minLength: 3,
                    delay: 300,
                    select: function(event, ui) {
                        if (ui.item.disabled) {
                            event.preventDefault();
                            return false;
                        }

                        $chargeCodeInput.val(ui.item.value);
                        $(`input[name="${prefix}_description"]`).val(ui.item.name);

                        if (ui.item.currency && currencyMap[ui.item.currency]) {
                            const $currencySelect = $(`select[name="${prefix}_currency"]`);
                            $currencySelect.val(currencyMap[ui.item.currency]).trigger('change');
                        }

                        // VAT format from SP: "VAT @ 18.00" or "" (empty = no VAT)
                        const $vatField = $(`input[name="${prefix}_tax_applicable"]`);
                        if (ui.item.vat && ui.item.vat.includes('@')) {
                            const vatValue = parseFloat(ui.item.vat.split('@')[1].trim());
                            $vatField.val(isNaN(vatValue) ? '' : vatValue);
                        } else {
                            $vatField.val('');
                        }

                        // Explicitly close — returning false prevents jQuery UI's default
                        // close behaviour, so we must do it manually before returning.
                        $chargeCodeInput.autocomplete('close');
                        return false;
                    }
                }).autocomplete("instance")._renderItem = function(ul, item) {
                    const vatLabel = item.vat ?
                        `<span class="text-muted ml-2" style="font-size:0.75rem;">(VAT: ${item.vat})</span>` :
                        '';
                    return $("<li>")
                        .addClass(item.disabled ? "ui-state-disabled" : "")
                        .append($("<div>").html(`${item.label}${vatLabel}`))
                        .appendTo(ul);
                };
            }

            // ============================================
            // BILL TO PARTY AUTOCOMPLETE
            // FIX: chargeType also read dynamically here for the same reason.
            // ============================================
            function initializeBillToPartyAutocomplete(type) {
                const prefix = type === 'revenue' ? 'revenue' : 'cost';
                const $input = $(`input[name="${prefix}_bill_to_party"]`);
                const $codeInput = $(`input[name="${prefix}_bill_to_party_code"]`);

                if ($input.length === 0) return;

                if ($input.hasClass('ui-autocomplete-input')) {
                    $input.autocomplete('destroy');
                }

                $input.off('keydown.billToParty keyup.billToParty keypress.billToParty');

                let percentCount = 0;
                let percentTimer = null;

                function doSearch(term, response) {
                    // FIX: Read charge type dynamically from radio button
                    const $modal = type === 'revenue' ? $('#addRevenueModal') : $('#addCostModal');
                    const selectedRadio = $modal.find(`input[name="${prefix}ChargeType"]:checked`).val();
                    const chargeType = (selectedRadio === 'cost' || selectedRadio === 'costmarkup') ? 'Cost' :
                        'Revenue';

                    function execSearch(resolvedOfficeCode) {
                        $.ajax({
                            url: '/quotation/legal-entities',
                            type: 'GET',
                            data: {
                                office_code: resolvedOfficeCode,
                                charge_type: chargeType,
                                filter: term
                            },
                            success: function(data) {
                                if (data.success && data.parties && data.parties.length > 0) {
                                    response(data.parties.map(item => ({
                                        label: item.Code + ' - ' + item.Name,
                                        value: item.Name,
                                        code: item.Code
                                    })));
                                } else {
                                    response([{
                                        label: 'No parties found',
                                        value: term,
                                        disabled: true
                                    }]);
                                }
                            },
                            error: function() {
                                response([{
                                    label: 'Error loading parties',
                                    value: '',
                                    disabled: true
                                }]);
                            }
                        });
                    }

                    if (officeCode) {
                        execSearch(officeCode);
                    } else {
                        response([{
                            label: 'Office code not available',
                            value: '',
                            disabled: true
                        }]);
                        console.error('initializeBillToPartyAutocomplete: officeCode not available');
                    }
                }

                function triggerWildcardSearch() {
                    $input.val('');
                    percentCount = 0;
                    $input.autocomplete('option', 'minLength', 0);
                    $input.autocomplete('search', 'WILDCARD__SEARCH');
                }

                $input.autocomplete({
                    minLength: 3,
                    delay: 300,
                    source: function(request, response) {
                        const term = request.term;
                        if (term === 'WILDCARD__SEARCH') {
                            setTimeout(function() {
                                $input.autocomplete('option', 'minLength', 3);
                            }, 100);
                            doSearch('', response);
                            return;
                        }
                        if (term.indexOf('%') !== -1) {
                            response([]);
                            return;
                        }
                        if (term.length >= 3) {
                            doSearch(term, response);
                            return;
                        }
                        response([]);
                    },
                    select: function(event, ui) {
                        if (ui.item.disabled) {
                            event.preventDefault();
                            return false;
                        }
                        $input.val(ui.item.value);
                        $codeInput.val(ui.item.code);
                        percentCount = 0;
                        return false;
                    }
                }).autocomplete('instance')._renderItem = function(ul, item) {
                    return $('<li>')
                        .addClass(item.disabled ? 'ui-state-disabled' : '')
                        .append($('<div>').text(item.label))
                        .appendTo(ul);
                };

                $input.on('keypress.billToParty', function(e) {
                    if (e.which === 37) {
                        percentCount++;
                        if (percentTimer) clearTimeout(percentTimer);
                        if (percentCount === 1) {
                            percentTimer = setTimeout(function() {
                                percentCount = 0;
                            }, 1500);
                        } else if (percentCount >= 2) {
                            e.preventDefault();
                            clearTimeout(percentTimer);
                            triggerWildcardSearch();
                        }
                    } else {
                        percentCount = 0;
                        if (percentTimer) clearTimeout(percentTimer);
                    }
                });

                $input.on('keyup.billToParty', function() {
                    if ($(this).val() === '') {
                        $codeInput.val('');
                        percentCount = 0;
                    }
                });
            }

            // ============================================
            // CHARGE TYPE TOGGLES
            // ============================================
            $('#addRevenueModal').on('change', 'input[name="revenueChargeType"]', function() {
                const selectedValue = $(this).val();
                console.log('Revenue Charge Type Changed:', selectedValue);
                $('#addRevenueModal .increase-by-row').removeClass('active').css('display', 'none');
                toggleRevenueFormFields(selectedValue);
            });

            $('#addCostModal').on('change', 'input[name="costChargeType"]', function() {
                const selectedValue = $(this).val();
                console.log('Cost Charge Type Changed:', selectedValue);
                $('#addCostModal .cost-markup-row').removeClass('active').css('display', 'none');
                toggleCostFormFields(selectedValue);
            });

            function toggleRevenueFormFields(type) {
                const $modal = $('#addRevenueModal');
                let $increaseByRow = $modal.find('.increase-by-row');

                $increaseByRow.removeClass('active').css('display', 'none');

                // Label: "Bill to Party" for Revenue, "Vendor" for Cost/Cost with Markup
                $('#revenueBillToPartyLabel').text(
                    (type === 'cost' || type === 'costmarkup') ? 'Vendor' : 'Bill to Party'
                );


                // Show Is Accruable only for Revenue radio
                $('#revenueIsAccruableContainer').css('display', type === 'revenue' ? 'block' : 'none');

                // Show Is Reimbursable only for Cost radio  ← THIS IS THE FIX
                $('#revenueIsReimbursableContainer').css('display', type === 'cost' ? 'block' : 'none');


                if (type === 'costmarkup') {
                    if ($increaseByRow.length === 0) {
                        const increaseByHtml = `
                <div class="row mb-3 increase-by-row" style="display: none;">
                    <div class="col-6">
                        <label for="revenue_increase_by" class="form-label">Increase By</label>
                        <div class="input-group">
                            <input type="number" class="form-control" id="revenue_increase_by"
                                name="revenue_increase_by" step="0.01" value="0.00">
                            <select class="form-select form-control" id="revenue_increase_type"
                                name="revenue_increase_type" style="max-width: 120px;">
                                <option value="Percentage">Percentage</option>
                                <option value="Fixed">Fixed</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-6">
                        <label for="revenue_markup_amount" class="form-label">Amount</label>
                        <input type="number" class="form-control" id="revenue_markup_amount"
                            name="revenue_markup_amount" step="0.01" value="0.00" readonly>
                    </div>
                </div>
                `;
                        $modal.find('.rate-row').after(increaseByHtml);
                        $increaseByRow = $modal.find('.increase-by-row');
                    }
                    $increaseByRow.addClass('active').css('display', 'flex');
                }
                // NOTE: 'cost' and 'revenue' types need no extra UI changes in the Revenue modal.
                // The revenue modal has no Is Reimbursed / Is Accrued checkboxes.
            }

            function toggleCostFormFields(type) {
                const $modal = $('#addCostModal');
                const $markupRow = $modal.find('.cost-markup-row');

                $('#costBillToPartyLabel').text(
                    (type === 'cost' || type === 'costmarkup') ? 'Vendor' : 'Bill to Party'
                );

                $('#costIsReimbursedContainer').css('display', type === 'cost' ? 'block' : 'none');

                if (type === 'costmarkup') {
                    $markupRow.addClass('active').css('display', 'flex');
                    console.log('Showing cost markup row');
                } else if (type === 'revenue') {
                    $markupRow.removeClass('active').css('display', 'none');
                    $markupRow.find('input[type="number"]').val('0.00');
                    $markupRow.find('select').prop('selectedIndex', 0);
                    console.log('Hiding cost markup row - Revenue selected');
                } else {
                    // type === 'cost'
                    $markupRow.removeClass('active').css('display', 'none');
                    $markupRow.find('input[type="number"]').val('0.00');
                    $markupRow.find('select').prop('selectedIndex', 0);
                    console.log('Hiding cost markup row - Cost selected');
                }
            }

            // ============================================
            // AUTO-CALCULATE AMOUNTS
            // ============================================
            $('#addRevenueModal').on('input change',
                'input[name="revenue_quantity"], input[name="revenue_rate"], input[name="revenue_exchange_rate"], input[name="revenue_increase_by"], select[name="revenue_increase_type"]',
                function() {
                    calculateAmount('revenue');
                }
            );

            $('#addCostModal').on('input change',
                'input[name="cost_quantity"], input[name="cost_rate"], input[name="cost_exchange_rate"], input[name="cost_increase_by"], select[name="cost_increase_type"]',
                function() {
                    calculateAmount('cost');
                }
            );

            function calculateAmount(prefix) {
                const $modal = $(`#add${prefix.charAt(0).toUpperCase() + prefix.slice(1)}Modal`);
                const quantity = parseFloat($modal.find(`input[name="${prefix}_quantity"]`).val()) || 0;
                const rate = parseFloat($modal.find(`input[name="${prefix}_rate"]`).val()) || 0;
                const exchangeRate = parseFloat($modal.find(`input[name="${prefix}_exchange_rate"]`).val()) || 1;
                const amount = quantity * rate;
                const baseAmount = amount * exchangeRate;

                $modal.find(`input[name="${prefix}_amount"]`).val(amount.toFixed(2));
                $modal.find(`input[name="${prefix}_base_amount"]`).val(baseAmount.toFixed(2));

                const chargeType = $modal.find(`input[name="${prefix}ChargeType"]:checked`).val();
                if (chargeType === 'costmarkup') {
                    const increaseBy = parseFloat($modal.find(`input[name="${prefix}_increase_by"]`).val()) || 0;
                    const increaseType = $modal.find(`select[name="${prefix}_increase_type"]`).val();

                    // Mirror SP logic: markup applied to Rate first, then multiply by Quantity
                    const newRate = increaseType === 'Percentage' ?
                        rate + (rate * increaseBy / 100) :
                        rate + increaseBy; // Fixed: add to rate, not to amount

                    const markupAmount = Math.round(newRate * quantity * 100) / 100;
                    const markupBaseAmount = Math.round(markupAmount * exchangeRate * 100) / 100;

                    const markupFieldName = prefix === 'revenue' ? 'revenue_markup_amount' : 'cost_revenue_amount';
                    $modal.find(`input[name="${markupFieldName}"]`).val(markupAmount.toFixed(2));
                }
            }

            // ============================================
            // SAVE CHARGES
            // ============================================
            $('#saveRevenueCharge').on('click', function() {
                saveCharge('revenue', false);
            });

            $('#saveAddMoreRevenueCharge').on('click', function() {
                saveCharge('revenue', true);
            });

            $('#saveCostCharge').on('click', function() {
                saveCharge('cost', false);
            });

            $('#saveAddMoreCostCharge').on('click', function() {
                saveCharge('cost', true);
            });

            // ============================================
            // FIX: revenue_or_cost is now determined by the RADIO BUTTON value,
            // not the modal type (type param).
            //
            // Root cause of the original bug:
            //   Old code: if (type === 'revenue') revenueOrCost = 'Revenue' — always.
            //   This meant clicking "Cost" radio in the Revenue modal was silently ignored.
            //
            // The radio button is now the single source of truth for revenueOrCost.
            // ============================================
            function saveCharge(type, addMore) {
                const $modal = type === 'revenue' ? $('#addRevenueModal') : $('#addCostModal');
                const prefix = type === 'revenue' ? 'revenue' : 'cost';
                const chargeType = $modal.find(`input[name="${prefix}ChargeType"]:checked`).val();

                const revenueOrCost = chargeType === 'revenue' ? 'Revenue' :
                    chargeType === 'costmarkup' ? 'Cost with Markup' : 'Cost';

                let toBeAdded = 0;
                if (revenueOrCost === 'Revenue') {
                    toBeAdded = $modal.find('#revenueIsAccruable').is(':checked') ? 1 : 0;
                } else if (revenueOrCost === 'Cost') {
                    // Revenue modal uses #revenueIsReimbursable, Cost modal uses #costIsReimbursed
                    // Both are scoped to $modal so only the correct one is ever found
                    const $reimbursableCheck = $modal.find('#revenueIsReimbursable, #costIsReimbursed');
                    toBeAdded = $reimbursableCheck.is(':checked') ? 1 : 0;
                }

                // Prepare data payload — SP will handle currency, office, base amounts
                const chargeData = {
                    _token: '{{ csrf_token() }}',
                    action: currentEditingCharge ? 'Edit' : 'Add',
                    quotation_id: quotationId,
                    booking_no: bookingNo,
                    office_code: officeCode,
                    quote_charge_id: currentEditingCharge || '',
                    party_code: $modal.find(`input[name="${prefix}_bill_to_party_code"]`).val() || '',
                    revenue_or_cost: revenueOrCost,
                    charge_code: $modal.find(`input[name="${prefix}_charge_code"]`).val(),
                    quantity: parseFloat($modal.find(`input[name="${prefix}_quantity"]`).val()) || 0,
                    rate: parseFloat($modal.find(`input[name="${prefix}_rate"]`).val()) || 0,
                    amt_in_chg_curr: parseFloat($modal.find(`input[name="${prefix}_amount"]`).val()) || 0,
                    amt_in_base_curr: parseFloat($modal.find(`input[name="${prefix}_base_amount"]`).val()) || 0,
                    exchange_rate: parseFloat($modal.find(`input[name="${prefix}_exchange_rate"]`).val()) || 1,
                    chg_curr: $modal.find(`select[name="${prefix}_currency"] option:selected`).data('code') ||
                        '',
                    based_on_code: $modal.find(`select[name="${prefix}_based_on"]`).val() || '',
                    based_on_uom: $modal.find(`select[name="${prefix}_based_on_uom"]`).val() || '',
                    charge_remark: $modal.find(`input[name="${prefix}_remark"]`).val() || '',
                    to_be_added: toBeAdded,
                    increase_by: 0,
                    increase_type: 'Percentage',
                    source_type: 'manual'
                };

                // Cost with Markup adjustments
                if (chargeType === 'costmarkup') {
                    chargeData.increase_by = parseFloat($modal.find(`input[name="${prefix}_increase_by"]`).val()) ||
                        0;
                    chargeData.increase_type = $modal.find(`select[name="${prefix}_increase_type"]`).val() ||
                        'Percentage';
                }

                // Local validation before sending to SP
                if (!chargeData.charge_code) {
                    showNotification('Please enter a charge code', 'error');
                    return;
                }
                if (!chargeData.based_on_code) {
                    showNotification('Please select Based On', 'error');
                    return;
                }

                $.ajax({
                    url: '{{ route('quotation.charge.save') }}',
                    type: 'POST',
                    data: chargeData,
                    beforeSend: function() {
                        $modal.find('.btn').prop('disabled', true);
                    },
                    success: function(response) {
                        const isSuccess = response.success === true;
                        showNotification(response.message || (isSuccess ? 'Charge saved successfully' :
                                'Failed to save charge'),
                            isSuccess ? 'success' : 'error');

                        if (isSuccess) {
                            if (addMore) {
                                type === 'revenue' ? clearRevenueModalForm() : clearCostModalForm();
                                loadCharges();
                            } else {
                                $modal.modal('hide');
                                loadCharges();
                            }
                            currentEditingCharge = null;
                        }
                    },
                    error: function(xhr) {
                        showNotification(xhr.responseJSON?.message || 'Failed to save charge', 'error');
                    },
                    complete: function() {
                        $modal.find('.btn').prop('disabled', false);
                    }
                });
            }

            // ============================================
            // DELETE CHARGES
            // ============================================
            $(document).on('click', '.delete-revenue-charge, .delete-cost-charge', function() {
                const chargeId = $(this).data('id');
                if (!confirm('Are you sure you want to delete this charge?')) return;

                $.ajax({
                    url: '{{ route('quotation.charge.delete') }}',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        booking_no: bookingNo,
                        office_code: officeCode,
                        quote_charge_id: chargeId
                    },
                    success: function(response) {
                        if (response.success) {
                            showNotification('Charge deleted successfully', 'success');
                            loadCharges();
                        } else {
                            showNotification(response.message, 'error');
                        }
                    },
                    error: function() {
                        showNotification('Failed to delete charge', 'error');
                    }
                });
            });

            // ============================================
            // EDIT CHARGES
            // ============================================
            function openChargeEditModal(chargeId, modalType) {
                $.ajax({
                    url: '{{ route('quotation.charge-details') }}',
                    type: 'GET',
                    data: {
                        booking_no: bookingNo,
                        charge_id: chargeId,
                        office_code: officeCode

                    },
                    beforeSend: function() {
                        // Show the modal immediately with a loading state
                        const $modal = modalType === 'revenue' ? $('#addRevenueModal') : $(
                            '#addCostModal');
                        $modal.modal('show');
                        $modal.find('.modal-body').css('opacity', 0.5);
                    },
                    success: function(response) {
                        const $modal = modalType === 'revenue' ? $('#addRevenueModal') : $(
                            '#addCostModal');
                        $modal.find('.modal-body').css('opacity', 1);

                        if (!response.success) {
                            showNotification(response.message || 'Failed to load charge details',
                                'error');
                            $modal.modal('hide');
                            return;
                        }

                        currentEditingCharge = chargeId;
                        currentEditingType = modalType;
                        populateModal(modalType, response);
                    },
                    error: function(xhr) {
                        const $modal = modalType === 'revenue' ? $('#addRevenueModal') : $(
                            '#addCostModal');
                        $modal.find('.modal-body').css('opacity', 1);
                        $modal.modal('hide');
                        showNotification(xhr.responseJSON?.message || 'Failed to load charge details',
                            'error');
                    }
                });
            }

            $(document).on('click', '.edit-revenue-charge', function() {
                openChargeEditModal($(this).data('id'), 'revenue');
            });

            $(document).on('click', '.edit-cost-charge', function() {
                openChargeEditModal($(this).data('id'), 'cost');
            });

            // ============================================
            // POPULATE MODAL FOR EDITING A CHARGE
            // ============================================
            function populateModal(type, charge) {
                const $modal = type === 'revenue' ? $('#addRevenueModal') : $('#addCostModal');
                const prefix = type;

                // ── Restore charge type radio ──
                const savedChargeType = charge.revenueOrCost === 'Revenue' ? 'revenue' :
                    charge.revenueOrCost === 'Cost with Markup' ? 'costmarkup' : 'cost';

                $modal.find(`input[name="${prefix}ChargeType"][value="${savedChargeType}"]`)
                    .prop('checked', true);

                if (type === 'revenue') {
                    toggleRevenueFormFields(savedChargeType);
                } else {
                    toggleCostFormFields(savedChargeType);
                }

                // ── Scalar fields — no async dependency ──
                $modal.find(`input[name="${prefix}_charge_code"]`).val(charge.chargeCode);
                $modal.find(`input[name="${prefix}_description"]`).val(charge.chargeName);
                $modal.find(`input[name="${prefix}_remark"]`).val(charge.chargeRemark);
                $modal.find(`input[name="${prefix}_quantity"]`).val(charge.quantity);
                $modal.find(`input[name="${prefix}_rate"]`).val(charge.rate);
                $modal.find(`input[name="${prefix}_exchange_rate"]`).val(charge.exChangeRate);
                $modal.find(`input[name="${prefix}_amount"]`).val(
                    parseFloat(charge.amtInChgCurr || 0).toFixed(2)
                );
                $modal.find(`input[name="${prefix}_base_amount"]`).val(
                    parseFloat(charge.amtInBaseCurr || 0).toFixed(2)
                );

                // ── Restore markup fields if Cost with Markup ──
                if (savedChargeType === 'costmarkup') {
                    $modal.find(`input[name="${prefix}_increase_by"]`).val(charge.increaseBy || 0);
                    $modal.find(`select[name="${prefix}_increase_type"]`).val(charge.increaseType || 'Percentage');
                }

                // ── Currency — wait for currencyMap to be populated ──
                function setCurrency() {
                    if (charge.chgCurr && currencyMap[charge.chgCurr]) {
                        $modal.find(`select[name="${prefix}_currency"]`).val(currencyMap[charge.chgCurr]);
                    }
                }

                const $currencySelect = $modal.find(`select[name="${prefix}_currency"]`);
                if ($currencySelect.data('populated')) {
                    setCurrency();
                } else {
                    const currencyPoll = setInterval(function() {
                        if ($currencySelect.data('populated')) {
                            clearInterval(currencyPoll);
                            setCurrency();
                        }
                    }, 100);
                }

                // ── Based On → UOM — sequential async chain ─────────────────────────────────
                loadBasedOnListWithCallback(type, function() {
                    if (!charge.basedOnCode) return;

                    $modal.find(`select[name="${prefix}_based_on"]`).val(charge.basedOnCode);

                    // Robustly resolve the stored UOM code regardless of Based On type.
                    // Priority: explicit uomCode field → last segment of basedOnAndUOM → empty
                    const rawUom = charge.uomCode ||
                        charge.basedOnUOM // ← ADD: try alternate field name
                        ||
                        charge.BasedOnUOM // ← ADD: try PascalCase variant
                        ||
                        '';

                    console.log('DEBUG populateModal charge object:', JSON.stringify(charge));

                    // ── DEBUG LOGS — remove after fix is confirmed ──
                    console.log('DEBUG populateModal charge object:', JSON.stringify(charge));
                    console.log('DEBUG uomCode field:', charge.uomCode);
                    console.log('DEBUG basedOnAndUOM field:', charge.basedOnAndUOM);
                    // ── END DEBUG ──


                    const storedUomCode = rawUom.trim() ||
                        ((charge.basedOnAndUOM || '').includes('/') ?
                            (charge.basedOnAndUOM || '').split('/').pop().trim() :
                            (charge.basedOnAndUOM || '').trim());

                    console.log('DEBUG storedUomCode final:', storedUomCode, '| charge:', charge);

                    loadBasedOnUOMWithCallback(type, charge.basedOnCode, function() {
                        if (!storedUomCode) return;

                        const $uomSelect = $modal.find(`select[name="${prefix}_based_on_uom"]`);

                        // Verify the option exists before setting — surface mismatches clearly
                        const $matchingOption = $uomSelect.find(`option[value="${storedUomCode}"]`);
                        if ($matchingOption.length) {
                            $uomSelect.val(storedUomCode);
                            console.log('DEBUG UOM pre-selected:', storedUomCode);
                        } else {
                            console.warn('DEBUG UOM option not found in select:', storedUomCode,
                                '| Available:', $uomSelect.find('option').map(function() {
                                    return $(this).val();
                                }).get()
                            );
                        }
                    });
                });
            }

            // ============================================
            // RENDER FUNCTIONS
            // ============================================
            function renderRevenueCharges() {
                const tbody = $('#revenueChargesTable');
                tbody.empty();
                let totalAmount = 0,
                    totalBaseAmount = 0;

                if (revenueCharges.length === 0) {
                    tbody.append(
                        '<tr><td colspan="8" class="text-center text-muted">No charges added</td></tr>');
                } else {
                    revenueCharges.forEach(function(charge, index) {
                        totalAmount += parseFloat(charge.amtInChgCurr || 0);
                        totalBaseAmount += parseFloat(charge.amtInBaseCurr || 0);
                        tbody.append(`
                            <tr>
                                <td>${charge.chargeName || charge.chargeCode || ''}</td>
                                <td class="text-right">${parseFloat(charge.rate || 0).toFixed(2)}</td>
                                <td class="text-right col-extra">${parseFloat(charge.quantity || 0).toFixed(2)}</td>
                                <td class="col-extra">${charge.basedOnAndUOM || charge.basedOnUOM || ''}</td>
                                <td class="col-extra">${charge.chgCurr || getBaseCurrency()}</td>
                                <td class="text-right">${parseFloat(charge.amtInChgCurr  || 0).toFixed(2)}</td>
                                <td class="text-right">${parseFloat(charge.amtInBaseCurr || 0).toFixed(2)}</td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-warning btn-action edit-revenue-charge"
                                        data-index="${index}" data-id="${charge.id}">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-danger btn-action delete-revenue-charge"
                                        data-index="${index}" data-id="${charge.id}">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        `);
                    });
                }

                const revBC = getBaseCurrency();
                $('#totalRevenueAmount').text(revBC + ' ' + totalAmount.toFixed(2));
                $('#totalRevenueBaseAmount').text(revBC + ' ' + totalBaseAmount.toFixed(2));
                $('#revenueTotalHeaderAmount').text(totalBaseAmount.toFixed(2));

                const revenueExpanded = $('.show-items-link[data-table="revenue"]').data('expanded') || false;
                if (!revenueExpanded) $('#revenueChargesTable').find('.col-extra').hide();
            }

            function renderCostCharges() {
                const tbody = $('#costChargesTable');
                tbody.empty();
                let totalAmount = 0,
                    totalBaseAmount = 0;

                if (costCharges.length === 0) {
                    tbody.append(
                        '<tr><td colspan="8" class="text-center text-muted">No charges added</td></tr>');
                } else {
                    costCharges.forEach(function(charge, index) {
                        totalAmount += parseFloat(charge.amtInChgCurr || 0);
                        totalBaseAmount += parseFloat(charge.amtInBaseCurr || 0);
                        tbody.append(`
                            <tr>
                                <td>${charge.chargeName || charge.chargeCode || ''}</td>
                                <td class="text-right">${parseFloat(charge.rate || 0).toFixed(2)}</td>
                                <td class="text-right col-extra">${parseFloat(charge.quantity || 0).toFixed(2)}</td>
                                <td class="col-extra">${charge.basedOnAndUOM || charge.basedOnUOM || ''}</td>
                                <td class="col-extra">${charge.chgCurr || getBaseCurrency()}</td>
                                <td class="text-right">${parseFloat(charge.amtInChgCurr  || 0).toFixed(2)}</td>
                                <td class="text-right">${parseFloat(charge.amtInBaseCurr || 0).toFixed(2)}</td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-warning btn-action edit-cost-charge"
                                        data-index="${index}" data-id="${charge.id}">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-danger btn-action delete-cost-charge"
                                        data-index="${index}" data-id="${charge.id}">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        `);
                    });
                }

                const costBC = getBaseCurrency();
                $('#totalCostAmount').text(costBC + ' ' + totalAmount.toFixed(2));
                $('#totalCostBaseAmount').text(costBC + ' ' + totalBaseAmount.toFixed(2));
                $('#costTotalHeaderAmount').text(totalBaseAmount.toFixed(2));

                const costExpanded = $('.show-items-link[data-table="cost"]').data('expanded') || false;
                if (!costExpanded) $('#costChargesTable').find('.col-extra').hide();
            }

            // ============================================
            // UPDATE SUMMARY
            // ============================================
            function updateSummary() {
                const totalRevenue = revenueCharges.reduce((sum, c) => sum + parseFloat(c.amtInBaseCurr || 0),
                    0);
                const totalCost = costCharges.reduce((sum, c) => sum + parseFloat(c.amtInBaseCurr || 0), 0);
                const margin = totalRevenue - totalCost;
                const marginPercentage = totalRevenue > 0 ? (margin / totalRevenue * 100) : 0;

                const bc = getBaseCurrency();
                $('#quotedRevenue').val(bc + ' ' + totalRevenue.toFixed(2));
                $('#quotedMargin').val(bc + ' ' + margin.toFixed(2) + ' (' + marginPercentage.toFixed(2) + '%)');
            }

            // ============================================
            // STATUS UPDATE BUTTONS
            // ============================================
            $('#cancelQuoteBtn').on('click', function(e) {
                e.preventDefault();
                showStatusModal('Cancel');
            });

            $('#lostQuoteBtn').on('click', function(e) {
                e.preventDefault();
                showStatusModal('Lost');
            });

            $('#wonQuoteBtn').on('click', function(e) {
                e.preventDefault();
                $('#wonRemarkInput').val('');
                $('#wonModal').modal('show');
            });

            $('#createBookingBtn').on('click', function() {
                $('#createBookingModal').modal('show');
            });

            $('#confirmWonBtn').on('click', function() {
                const remark = $('#wonRemarkInput').val().trim();
                runWonProcess(bookingNo, remark);
            });

            $('#wonModal').on('hidden.bs.modal', function() {
                $('#wonRemarkInput').val('');
            });

            $('#wonProcessModal').on('click', '.close, #wonProcessCloseBtn', function (e) {
    e.preventDefault();
    e.stopPropagation();
    $('#wonProcessModal').modal('hide');
});

// Prevent closing mid-process via backdrop click or ESC
$('#wonProcessModal').on('hide.bs.modal', function () {
    if ($('#wonProcessCloseBtn').hasClass('d-none')) {
        return false; // block close while processing
    }
});

            $('#wonModal .close, #wonModal button[data-dismiss="modal"]').on('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                $('#wonModal').modal('hide');
            });

            function showStatusModal(status) {
                const modalHtml = `
                    <div class="modal fade" id="statusModal" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Update Status to ${status}</h5>
                                    <button type="button" class="close" data-dismiss="modal">
                                        <span>&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <div class="form-group">
                                        <label>* Reason</label>
                                        <select class="form-control" id="reasonCode" required>
                                            <option value="">Loading reasons...</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>* Remarks <span class="text-danger">(required)</span></label>
                                        <textarea class="form-control" id="reasonRemark" rows="5"
                                            placeholder="Enter a remark..." required></textarea>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                    <button type="button" class="btn btn-primary" id="confirmStatus">Confirm</button>
                                </div>
                            </div>
                        </div>
                    </div>
                `;

                $('body').append(modalHtml);

                $.ajax({
                    url: '{{ route('quotation.reasons') }}',
                    type: 'GET',
                    data: {
                        reason_type: status
                    },
                    success: function(response) {
                        const $select = $('#reasonCode');
                        $select.empty().append('<option value="">-- Select Reason --</option>');
                        if (response.success && response.reasons?.length) {
                            response.reasons.forEach(function(r) {
                                const code = r.Code ?? r.code ?? r.ReasonCode ?? '';
                                const name = r.Name ?? r.name ?? r.ReasonName ?? r
                                    .Description ?? '';
                                $select.append(`<option value="${code}">${name}</option>`);
                            });
                            // Pre-select first reason
                            $select.find('option:eq(1)').prop('selected', true);
                        } else {
                            $select.append(
                                '<option value="" disabled>No reasons available</option>');
                        }
                    },
                    error: function() {
                        $('#reasonCode').empty().append(
                            '<option value="">Failed to load reasons</option>');
                    }
                });

                $('#statusModal').modal('show');

                $('#confirmStatus').on('click', function() {
                    const reasonCode = $('#reasonCode').val();
                    const reasonRemark = $('#reasonRemark').val().trim();

                    if (!reasonCode) {
                        showNotification('Please select a reason', 'error');
                        return;
                    }

                    if (!reasonRemark) {
                        showNotification('Remark is required', 'error');
                        $('#reasonRemark').addClass('is-invalid').focus();
                        return;
                    }

                    $('#reasonRemark').removeClass('is-invalid');
                    updateStatus(status, reasonCode, reasonRemark, bookingNo);
                });

                $('#statusModal').on('input', '#reasonRemark', function() {
                    if ($(this).val().trim()) $(this).removeClass('is-invalid');
                });

                $('#statusModal').on('hidden.bs.modal', function() {
                    $(this).remove();
                });
            }

            // ============================================
            // XML VIEWER
            // ============================================
            $('#viewXmlBtn').on('click', function(e) {
                e.preventDefault();
                $('#xmlViewerModal').modal('show');
            });

            // ============================================
            // QUOTATION VERSIONS — LOAD & RENDER
            // ============================================
            const GENERATE_PDF_URL = '{{ route('quotation.generate-pdf', $quotation->ID) }}';
            const VERSIONS_URL = '{{ route('quotation.versions', $quotation->ID) }}';

            function renderVersions(versions) {
                const $container = $('#quotationVersionsList');
                $container.empty();

                console.log('renderVersions called with:', versions); // Keep this for now

                if (!versions || versions.length === 0) {
                    $container.html('<p class="text-muted text-center mb-2">No versions generated yet.</p>');
                    return;
                }

                let html = '';
                versions.forEach(function(v) {
                    // Normalize field names — handle whatever the backend returns
                    const versionNo = v.version ?? v.Version ?? v.VersionNo ?? v.version_no ?? null;
                    const versionLabel = v.label ?? v.Label ?? v.VersionLabel ?? ('Version ' +
                        versionNo);
                    const versionUrl = v.url ?? v.Url ?? v.download_url ?? null;

                    if (!versionNo) {
                        console.warn('renderVersions: skipping entry — no recognizable version field',
                            v);
                        return;
                    }

                    const downloadUrl = versionUrl ??
                        `/quotation/${quotationId}/download-pdf/${versionNo}`;

                    html += `
            <div class="form-group row align-items-center mb-2">
                <label class="col-sm-4 col-form-label fw-bold">${versionLabel}</label>
                <div class="col-sm-8 d-flex align-items-center">
                    <button type="button"
                        class="btn btn-sm btn-outline-primary btn-download-pdf"
                        data-url="${downloadUrl}"
                        data-version="${versionNo}">
                        <i class="fas fa-download mr-1"></i> Download
                    </button>
                    <span class="ml-2 text-success"
                        id="download-success-${versionNo}"
                        style="display:none; font-size:0.8rem;">
                        <i class="fas fa-check-circle mr-1"></i> Downloaded
                    </span>
                </div>
            </div>
        `;
                });

                if (html) {
                    $container.html(html);
                    // Ensure card body stays visible after async load
                    $container.closest('.card-body').show();
                    $container.closest('.card')
                        .find('[data-card-widget="collapse"] i')
                        .removeClass('fa-chevron-down')
                        .addClass('fa-chevron-up');
                } else {
                    $container.html('<p class="text-muted text-center mb-2">No versions generated yet.</p>');
                }
            }

            function loadVersions() {
                $.ajax({
                    url: VERSIONS_URL,
                    type: 'GET',
                    success: function(response) {
                        console.log('loadVersions raw response:', JSON.stringify(response));
                        if (response.success) {
                            renderVersions(response.versions ?? response.data ?? []);
                        } else {
                            $('#quotationVersionsList').html(
                                '<p class="text-danger text-center mb-2">Failed to load versions.</p>'
                            );
                        }
                    },

                    error: function(xhr) {
                        console.error('loadVersions error:', xhr.status, xhr.responseText);
                        $('#quotationVersionsList').html(
                            '<p class="text-danger text-center mb-2">Error loading versions.</p>'
                        );
                    }
                });
            }

            // ============================================
            // GENERATE QUOTE BUTTON
            // ============================================
            $('#generateQuoteBtn').on('click', function() {
                if (termsState.generating || !termsState.applied) return;

                const $btn = $(this);
                termsState.generating = true;
                setGenerateBtn();
                $btn.html('<i class="fas fa-spinner fa-spin mr-1"></i> Generating...');

                $.ajax({
                    url: GENERATE_PDF_URL,
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            showNotification('Quote generated — ' + response.label, 'success');
                            if (response.warning) {
                                showNotification(response.warning, 'warning');
                            }
                            loadVersions();

                            // Keep gate open — T&C is still applied, allow generating again
                            termsState.generating = false;
                            setGenerateBtn();
                        } else {
                            showNotification(response.message || 'Failed to generate quote.',
                                'error');
                            // On failure, restore applied state so user can retry without reselecting
                            termsState.generating = false;
                            setGenerateBtn();
                        }
                    },
                    error: function(xhr) {
                        showNotification(
                            xhr.responseJSON?.message ||
                            'An error occurred while generating the quote.',
                            'error'
                        );
                        termsState.generating = false;
                        setGenerateBtn();
                    },
                    complete: function() {
                        $btn.html('<i class="fas fa-file-alt mr-1"></i> Generate Quote');
                    }
                });
            });

            // Load versions on page init
            loadVersions();
            loadTermsTemplates();

            // ============================================
            // EMAIL QUOTE — TEMPLATE LOADING
            // Reuses getCommunicationTemplates endpoint (sub_module='Email').
            // Description is already in the list response — no second fetch needed.
            // ============================================
            let emailTemplateMap = {}; // name → description, built once per modal open

            function loadEmailTemplates() {
                const $select = $('#emailTemplateSelect');
                $select.empty()
                    .append('<option value="">Loading templates...</option>')
                    .prop('disabled', true);
                emailTemplateMap = {};

                // Resolve office code first if not already available
                function fetchTemplates(resolvedOfficeCode) {
                    $.ajax({
                        url: '{{ route('quotation.communication.templates') }}',
                        type: 'GET',
                        data: {
                            module: 'Quotation',
                            sub_module: 'Email',
                            office_code: resolvedOfficeCode
                        },
                        success: function(response) {
                            $select.empty().append(
                                '<option value="">-- Select Template --</option>');

                            if (response.success && response.templates.length > 0) {
                                response.templates.forEach(function(t) {
                                    emailTemplateMap[t.Name] = t.Description || '';
                                    $select.append(
                                        `<option value="${t.Name}">${t.Name}</option>`
                                    );
                                });
                            } else {
                                const msg = response.code === 'NOT_CONFIGURED' ?
                                    `⚠ Email templates not configured for office: ${resolvedOfficeCode}` :
                                    response.code === 'OFFICE_NOT_FOUND' ?
                                    `⚠ Office not found: ${resolvedOfficeCode}` :
                                    '-- No email templates available --';
                                $select.append(`<option value="" disabled>${msg}</option>`);
                            }
                        },
                        error: function(xhr) {
                            const res = xhr.responseJSON;
                            const msg = res?.code === 'NOT_CONFIGURED' ?
                                `⚠ Email templates not configured for office: ${resolvedOfficeCode}` :
                                res?.code === 'OFFICE_NOT_FOUND' ?
                                `⚠ Office not found: ${resolvedOfficeCode}` :
                                '-- Failed to load templates --';
                            $select.empty().append(
                                `<option value="" disabled>${msg}</option>`);
                            console.warn('loadEmailTemplates error:', res?.message || xhr
                                .status);
                        },
                        complete: function() {
                            $select.prop('disabled', false);
                        }
                    });
                }

                // If officeCode already resolved from page load, use it directly
                // Otherwise fetch it fresh — avoids race condition on slow connections
                if (officeCode) {
                    fetchTemplates(officeCode);
                } else {
                    $select.empty().append(
                        '<option value="" disabled>⚠ Office code not available</option>'
                    );
                    $select.prop('disabled', false);
                    console.error('loadEmailTemplates: officeCode not available');
                }
            }

            // ============================================
            // EMAIL QUOTE — MODAL OPEN
            // ============================================
            $('#emailQuoteBtn').on('click', function() {
                $('#emailTemplateSelect').val('');
                $('#emailSubject').val('');
                $('#emailBody').val('');
                $('#emailFrom').val('');
                $('#emailTo').val('{{ $quotation->pri_partner_email ?? '' }}');
                loadEmailTemplates();
                populateQuoteVersionDropdown();
                $('#emailQuoteModal').modal('show');
            });

            // ============================================
            // EMAIL TO/FROM SWAP BUTTON
            // Swaps the To and From email addresses
            // ============================================
            // $('#swapEmailBtn').on('click', function() {
            //     const toVal = $('#emailTo').val();
            //     const fromVal = $('#emailFrom').val();
            //     $('#emailTo').val(fromVal);
            //     $('#emailFrom').val(toVal);
            // });

            function populateQuoteVersionDropdown() {
                const $select = $('#emailQuoteVersionSelect');
                $select.empty().append('<option value="">-- Select Quote --</option>');
                $.ajax({
                    url: VERSIONS_URL,
                    type: 'GET',
                    success: function(response) {
                        if (response.success && response.versions?.length) {
                            response.versions.forEach(function(v) {
                                $select.append(
                                    `<option value="${v.url}">${v.label}</option>`
                                );
                            });
                        } else {
                            $select.append(
                                '<option value="" disabled>No quotes generated yet</option>'
                            );
                        }
                    },
                    error: function() {
                        $select.append('<option value="" disabled>Failed to load</option>');
                    }
                });
            }


            // ============================================
            // EMAIL QUOTE — TEMPLATE SELECTION
            // Populates body from the in-memory map (no extra fetch).
            // Subject is left for the user to fill — the template only stores body.
            // ============================================
            $(document).on('change', '#emailTemplateSelect', function() {
                const name = $(this).val();
                if (!name) {
                    $('#emailSubject').val('');
                    $('#emailBody').val('');
                    return;
                }
                $('#emailBody').val(emailTemplateMap[name] || '');
                if (!$('#emailSubject').val().trim()) $('#emailSubject').val(name);
            });

            // ============================================
            // EMAIL QUOTE — SEND
            // ============================================
            $('#sendEmailQuoteBtn').on('click', function() {
                const templateId = $('#emailTemplateSelect').val();

                if (!templateId) {
                    showNotification('Please select a template', 'error');
                    return;
                }
                // Capture the selected version label (e.g. "Version 1").
                // The option value is the download URL; the text is the human-readable label.
                const $versionSelect = $('#emailQuoteVersionSelect');
                const selectedVersionLabel = $versionSelect.val() ?
                    $versionSelect.find('option:selected').text().trim() :
                    '';

                $.ajax({
                    url: '{{ route('quotation.email.send') }}',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        booking_no: bookingNo,
                        quote_id: quotationId,
                        template_id: templateId,
                        quote_version: selectedVersionLabel,
                        to_email: $('#emailTo').val(),
                        from_email: $('#emailFrom').val(),
                        subject: $('#emailSubject').val(),
                        body: $('#emailBody').val()
                    },
                    beforeSend: function() {
                        $('#sendEmailQuoteBtn').prop('disabled', true)
                            .html(
                                '<i class="fas fa-spinner fa-spin mr-1"></i> Sending...'
                            );
                    },
                    success: function(response) {
                        if (response.success) {
                            showNotification('Email sent successfully', 'success');
                            $('#emailQuoteModal').modal('hide');
                            loadEmailHistory();
                        } else {
                            showNotification(response.message ||
                                'Failed to send email',
                                'error');
                        }
                    },
                    error: function(xhr) {
                        showNotification(xhr.responseJSON?.message ||
                            'Failed to send email',
                            'error');
                    },
                    complete: function() {
                        $('#sendEmailQuoteBtn').prop('disabled', false)
                            .html('<i class="fas fa-paper-plane mr-1"></i> Send');
                    }
                });
            });

            // ============================================
            // PDF DOWNLOAD HANDLER
            // ============================================
            $(document).on('click', '.btn-download-pdf', function() {
                const $btn = $(this);
                const url = $btn.data('url');
                const version = $btn.data('version');

                $btn.prop('disabled', true)
                    .html('<i class="fas fa-spinner fa-spin mr-1"></i> Downloading...');

                const $anchor = $('<a>')
                    .attr('href', url)
                    .attr('download', '')
                    .appendTo('body');

                $anchor[0].click();
                $anchor.remove();

                setTimeout(function() {
                    $btn.prop('disabled', false)
                        .html('<i class="fas fa-download mr-1"></i> Download');

                    $('#download-success-' + version).fadeIn(200);
                    showNotification('Version ' + version + ' downloaded successfully',
                        'success');

                    setTimeout(function() {
                        $('#download-success-' + version).fadeOut(400);
                    }, 3000);
                }, 1500);
            });

            // ============================================
            // EMAIL HISTORY
            // ============================================
            function loadEmailHistory() {
                $.ajax({
                    url: '{{ route('quotation.email.history') }}',
                    type: 'GET',
                    data: {
                        booking_no: bookingNo
                    },
                    beforeSend: function() {
                        $('#emailHistoryTable').html(
                            '<tr><td colspan="3" class="text-center text-muted"><i class="fas fa-spinner fa-spin"></i> Loading...</td></tr>'
                        );
                    },
                    success: function(response) {
                        renderEmailHistory(response.emails || []);
                    },
                    error: function() {
                        $('#emailHistoryTable').html(
                            '<tr><td colspan="2" class="text-center text-danger">Failed to load</td></tr>'
                        );
                    }
                });
            }

            function renderEmailHistory(emails) {
                const tbody = $('#emailHistoryTable');
                tbody.empty();

                if (!emails.length) {
                    tbody.html(
                        '<tr><td colspan="3" class="text-center text-muted">No emails sent yet</td></tr>'
                    );
                    return;
                }

                emails.forEach(function(email) {
                    tbody.append(`
            <tr>
                <td>${email.sent_on   ?? ''}</td>
                <td>${email.to_email  ?? ''}</td>
                <td>${email.quote_version ?? ''}</td>
            </tr>
        `);
                });
            }

            $('#emailQuoteModal .close, #emailQuoteModal button[data-dismiss="modal"]').on('click',
                function(
                    e) {
                    e.preventDefault();
                    e.stopPropagation();
                    $('#emailQuoteModal').modal('hide');
                });

            $('#emailQuoteModal').on('hidden.bs.modal', function() {
                $('#emailTemplateSelect').val('');
                $('#emailSubject').val('');
                $('#emailBody').val('');
            });
            loadEmailHistory();


            // ============================================
            // CREATE BOOKING MODAL
            // ============================================

            // Close handlers
            $('#cancelCreateBookingBtn, #createBookingModal .close').on('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                $('#createBookingModal').modal('hide');
            });

            $('#createBookingModal').on('hidden.bs.modal', function() {
                $('#createBookingForm')[0].reset();
                // Clear location autocomplete state
                $('#cb_place_of_receipt_code, #cb_place_of_receipt_id').val('');
                $('#cb_place_of_delivery_code, #cb_place_of_delivery_id').val('');
                ['cb_place_of_receipt', 'cb_place_of_delivery'].forEach(function(id) {
                    const $el = $('#' + id);
                    if ($el.hasClass('ui-autocomplete-input')) $el.autocomplete(
                        'destroy');
                });
            });

            // Load all dropdowns when modal opens
            $('#createBookingModal').on('show.bs.modal', function() {
                loadServiceTemplates();
                loadBillToParties();
                loadForwarders();
                // Small delay ensures modal DOM is visible before autocomplete binds
                setTimeout(function() {
                    initLocationAutocomplete('cb_place_of_receipt',
                        'cb_place_of_receipt_code',
                        'cb_place_of_receipt_id');
                    initLocationAutocomplete('cb_place_of_delivery',
                        'cb_place_of_delivery_code',
                        'cb_place_of_delivery_id');
                }, 150);
            });

            // ---- Service Templates ----
            function loadServiceTemplates() {
                const $select = $('#cb_service_template');
                $select.empty().append('<option value="">Loading...</option>').prop('disabled', true);

                $.ajax({
                    url: '{{ route('quotation.booking.service-templates') }}',
                    type: 'GET',
                    data: {
                        quotation_id: quotationId
                    },
                    success: function(response) {
                        $select.empty().append('<option value=""></option>');
                        if (response.success && response.templates.length) {
                            response.templates.forEach(function(t) {
                                $select.append(
                                    `<option value="${t.Name}">${t.Name}</option>`
                                );
                            });
                        } else {
                            $select.append(
                                '<option value="" disabled>No templates available for this quotation</option>'
                            );
                        }
                    },
                    error: function() {
                        $select.empty().append(
                            '<option value="" disabled>Failed to load</option>');
                        showNotification('Failed to load service templates', 'error');
                    },
                    complete: function() {
                        $select.prop('disabled', false);
                    }
                });
            }

            // ---- Bill To Parties ----
            function loadBillToParties() {
                const $select = $('#cb_bill_to_party');
                $select.empty().append('<option value="">Loading...</option>').prop('disabled', true);

                $.ajax({
                    url: '{{ route('quotation.booking.bill-to-parties') }}',
                    type: 'GET',
                    data: {
                        team_id: teamId
                    },
                    success: function(response) {
                        $select.empty().append('<option value=""></option>');
                        if (response.success && response.parties.length) {
                            response.parties.forEach(function(p) {
                                $select.append(
                                    `<option value="${p.PartyCode}"
                                 data-id="${p.PartyID}">${p.PartyName}</option>`
                                );
                            });
                        } else {
                            $select.append(
                                '<option value="" disabled>No parties available</option>'
                            );
                        }
                    },
                    error: function() {
                        $select.empty().append(
                            '<option value="" disabled>Failed to load</option>');
                        showNotification('Failed to load bill to parties', 'error');
                    },
                    complete: function() {
                        $select.prop('disabled', false);
                    }
                });
            }

            // ---- Forwarders ----
            function loadForwarders() {
                const $select = $('#cb_forwarder');
                $select.empty().append('<option value="">Loading...</option>').prop('disabled', true);

                $.ajax({
                    url: '{{ route('quotation.booking.forwarders') }}',
                    type: 'GET',
                    data: {
                        team_id: teamId
                    },
                    success: function(response) {
                        $select.empty().append('<option value=""></option>');
                        if (response.success && response.parties.length) {
                            response.parties.forEach(function(p) {
                                $select.append(
                                    `<option value="${p.PartyCode}"
                                 data-id="${p.PartyID}">${p.PartyName}</option>`
                                );
                            });
                        } else {
                            $select.append(
                                '<option value="" disabled>No forwarders available</option>'
                            );
                        }
                    },
                    error: function() {
                        $select.empty().append(
                            '<option value="" disabled>Failed to load</option>');
                        showNotification('Failed to load forwarders', 'error');
                    },
                    complete: function() {
                        $select.prop('disabled', false);
                    }
                });
            }


            // ---- Locations autocomplete ----
            function initLocationAutocomplete(inputId, codeInputId, idInputId) {
                const $input = $('#' + inputId);
                const $codeInput = $('#' + codeInputId);
                const $idInput = $('#' + idInputId);

                // Destroy existing instance to allow re-init on checkbox change
                if ($input.hasClass('ui-autocomplete-input')) {
                    $input.autocomplete('destroy');
                }

                // Clear hidden values when user edits the text field
                $input.off('input.location').on('input.location', function() {
                    $codeInput.val('');
                    $idInput.val('');
                });

                $input.autocomplete({
                    minLength: 2,
                    delay: 350,
                    source: function(request, response) {
                        $.ajax({
                            url: '{{ route('quotation.booking.locations') }}',
                            type: 'GET',
                            data: {
                                filter: request.term,
                                include_country: $('#cb_include_country').is(
                                        ':checked') ?
                                    1 : 0,
                                country_code: ''
                            },
                            success: function(data) {
                                if (data.success && data.locations.length) {
                                    response(data.locations.map(function(l) {
                                        return {
                                            label: l.LocationCode +
                                                ' — ' +
                                                l
                                                .LocationName,
                                            value: l.LocationName,
                                            code: l.LocationCode,
                                            id: l.LocationID
                                        };
                                    }));
                                } else {
                                    response([{
                                        label: 'No locations found for "' +
                                            request.term + '"',
                                        value: request.term,
                                        disabled: true
                                    }]);
                                }
                            },
                            error: function() {
                                response([{
                                    label: 'Error loading locations',
                                    value: '',
                                    disabled: true
                                }]);
                            }
                        });
                    },
                    select: function(event, ui) {
                        if (ui.item.disabled) {
                            event.preventDefault();
                            return false;
                        }
                        $input.val(ui.item.value);
                        $codeInput.val(ui.item.code);
                        $idInput.val(ui.item.id);
                        return false;
                    }
                }).autocomplete('instance')._renderItem = function(ul, item) {
                    return $('<li>')
                        .addClass(item.disabled ? 'ui-state-disabled' : '')
                        .append($('<div>').text(item.label))
                        .appendTo(ul);
                };
            }

            // Re-init on checkbox change — clears selections since country filter changed
            $('#cb_include_country').on('change', function() {
                $('#cb_place_of_receipt, #cb_place_of_delivery').val('');
                $('#cb_place_of_receipt_code, #cb_place_of_delivery_code').val('');
                $('#cb_place_of_receipt_id, #cb_place_of_delivery_id').val('');

                initLocationAutocomplete('cb_place_of_receipt', 'cb_place_of_receipt_code',
                    'cb_place_of_receipt_id');
                initLocationAutocomplete('cb_place_of_delivery', 'cb_place_of_delivery_code',
                    'cb_place_of_delivery_id');
            });

            // ---- Submit ----
            $('#submitCreateBookingBtn').on('click', function() {
                const serviceTemplate = $('#cb_service_template').val();
                const billToParty = $('#cb_bill_to_party').val();
                const forwarder = $('#cb_forwarder').val();
                const placeOfReceipt = $('#cb_place_of_receipt_code').val();
                const placeOfDelivery = $('#cb_place_of_delivery_code').val();

                if (!serviceTemplate) {
                    showNotification('Please select a Service Template', 'error');
                    return;
                }
                if (!billToParty) {
                    showNotification('Please select a Bill To Party', 'error');
                    return;
                }
                if (!forwarder) {
                    showNotification('Please select a Forwarder', 'error');
                    return;
                }
                if (!placeOfReceipt) {
                    showNotification('Please select a Place of Receipt', 'error');
                    return;
                }
                if (!placeOfDelivery) {
                    showNotification('Please select a Place of Delivery', 'error');
                    return;
                }

                $.ajax({
                    url: '{{ route('quotation.booking.create') }}',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        booking_no: bookingNo,
                        quote_id: quotationId,
                        service_template: serviceTemplate,
                        bill_to_party: billToParty,
                        bill_to_party_id: $('#cb_bill_to_party option:selected').data(
                            'id'),
                        forwarder: forwarder,
                        forwarder_id: $('#cb_forwarder option:selected').data('id'),
                        include_country: $('#cb_include_country').is(':checked') ? 1 : 0,
                        place_of_receipt: $('#cb_place_of_receipt_code').val(),
                        place_of_receipt_id: $('#cb_place_of_receipt_id').val(),
                        place_of_delivery: $('#cb_place_of_delivery_code').val(),
                        place_of_delivery_id: $('#cb_place_of_delivery_id').val(),
                    },
                    beforeSend: function() {
                        $('#submitCreateBookingBtn').prop('disabled', true)
                            .html(
                                '<i class="fas fa-spinner fa-spin mr-1"></i> Creating...'
                            );
                    },
                    success: function(response) {
                        if (response.success) {
                            showNotification('Booking created successfully',
                                'success');
                            $('#createBookingModal').modal('hide');
                        } else {
                            showNotification(response.message ||
                                'Failed to create booking',
                                'error');
                        }
                    },
                    error: function(xhr) {
                        showNotification(
                            xhr.responseJSON?.message ||
                            'Failed to create booking',
                            'error'
                        );
                    },
                    complete: function() {
                        $('#submitCreateBookingBtn').prop('disabled', false).html(
                            'Create');
                    }
                });
            });

            // ============================================
            // QUOTATION DOCUMENT CARD HEADER CLICK
            // ============================================
            $('#quotationDocHeader').on('click', function(e) {
                if (!$(e.target).closest('#generateQuoteBtn').length) {
                    $(this).find('[data-card-widget="collapse"]').click();
                }
            });

            // ============================================
            // CUSTOMER TYPE FETCH (from SP)
            // FIX: Removed hardcoded fallback values that didn't match SP expectations.
            // Valid values come exclusively from sp_Quote_GetCustomerType.
            // If the SP fails, show an error instead of silently using wrong values.
            // ============================================
            function fetchCustomerTypes() {
                const $select = $('#customerTypeSelect');
                const savedType = '{{ $quotation->CustomerType ?? '' }}';

                $select.prop('disabled', true)
                    .empty()
                    .append('<option value="">Loading...</option>');

                $.ajax({
                    url: '{{ route('quotation.customer-types') }}',
                    type: 'GET',
                    success: function(response) {
                        $select.empty().append('<option value="">-- Select --</option>');

                        if (response.success && Array.isArray(response.data) && response
                            .data
                            .length >
                            0) {
                            response.data.forEach(function(item) {
                                const val = item.CategoryName;
                                $select.append(
                                    `<option value="${val}" ${val === savedType ? 'selected' : ''}>${val}</option>`
                                );
                            });
                        } else {
                            // SP returned no data — show a clear error, do NOT fall back to
                            // hardcoded values that would be rejected by sp_Quote_DetailsUpdate
                            $select.append(
                                '<option value="" disabled>⚠ No customer types available</option>'
                            );
                            console.warn(
                                'fetchCustomerTypes: SP returned empty or failed response',
                                response);
                        }
                    },
                    error: function(xhr) {
                        $select.empty()
                            .append('<option value="">-- Select --</option>')
                            .append(
                                '<option value="" disabled>⚠ Failed to load customer types</option>'
                            );
                        console.error('fetchCustomerTypes: AJAX error', xhr);
                    },
                    // AFTER
                    complete: function() {
                        // Only lock if we are NOT currently in edit mode.
                        // If Edit was clicked before this AJAX completed, leave it enabled.
                        const inEditMode = $('#editCustomerDetailsBtn').is(':hidden');
                        if (!inEditMode) {
                            $select.prop('disabled', true);
                        }
                    }
                });
            }

            // ============================================
            // AGENT TYPE FETCH
            // FIX: Option value now stores Code (e.g. "WCA") instead of Id (GUID).
            // sp_Quote_DetailsUpdate expects the Code, not the GUID.
            // ============================================
            const SAVED_AGENT_TYPE = '{{ $quotation->AgentType ?? '' }}';

            function fetchAgentTypes() {
                const $select = $('#agentTypeSelect');

                $select.prop('disabled', true)
                    .empty()
                    .append('<option value="">Loading...</option>');

                $.ajax({
                    url: '{{ route('quotation.agent-types') }}',
                    type: 'GET',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        $select.empty().append(
                            '<option value="">-- Select Agent Type --</option>');

                        if (response.success && Array.isArray(response.data) && response
                            .data
                            .length >
                            0) {
                            response.data.forEach(function(item) {
                                const name = item.Name ?? item.name ?? '';
                                const isSelected = String(name) === String(
                                    SAVED_AGENT_TYPE);

                                $select.append(
                                    `<option value="${name}" ${isSelected ? 'selected' : ''}>${name}</option>`
                                );
                            });
                        } else {
                            $select.append(
                                '<option value="" disabled>-- No agent types available --</option>'
                            );
                            console.warn(
                                'fetchAgentTypes: SP returned empty or failed response',
                                response);
                        }
                    },
                    error: function(xhr) {
                        $select.empty()
                            .append('<option value="">-- Select Agent Type --</option>')
                            .append(
                                '<option value="" disabled>-- Failed to load --</option>');
                        console.error('fetchAgentTypes: AJAX error', xhr);
                    },
                    complete: function() {
                        // Only call syncAgentTypeState — it already handles disabled state correctly
                        // based on both edit mode and customer type. Don't force-disable here.
                        syncAgentTypeState();
                    }
                });
            }

            // ============================================
            // SYNC AGENT TYPE STATE
            // Enabled only when: customer type contains "agent" AND edit mode is active.
            // FIX: Clears agent type selection when switching away from an agent customer type.
            // ============================================
            function syncAgentTypeState() {
                const customerTypeVal = $('#customerTypeSelect').val() || '';
                const isAgent = customerTypeVal.toLowerCase().includes('agent');
                const inEditMode = $('#editCustomerDetailsBtn').is(':hidden');

                $('#agentTypeSelect').prop('disabled', !isAgent || !inEditMode);

                if (!isAgent) {
                    $('#agentTypeSelect').val('');
                }
            }

            // ============================================
            // CUSTOMER DETAILS — EDIT / CANCEL / SAVE
            // ============================================

            // Lock both dropdowns on page load
            $('#customerTypeSelect, #agentTypeSelect').prop('disabled', true);

            // Snapshot values captured when Edit is clicked — restored on Cancel
            let originalCustomerType = '';
            let originalAgentType = '';
            let originalCustomerSnapshot = {}; // owned by Edit/Cancel — written ONCE on Edit click
            let newAgentTempSnapshot = {}; // owned by New Agent toggle — entirely separate

            // ============================================================
            // CUSTOMER — load country + industry dropdowns (once)
            // ============================================================
            let customerDropdownsLoaded = false;
            const savedCustomerCountry = '{{ $quotation->pri_partner_countryname ?? '' }}';
            const savedCustomerCountryCode = '{{ $quotation->pri_partner_countrycode ?? '' }}';

            const savedCustomerIndustry = '{{ $quotation->industryname ?? '' }}';

            // AFTER — remove the search event binding entirely
            function loadCustomerDropdowns(callback) {
                if (customerDropdownsLoaded) {
                    if (typeof callback === 'function') callback();
                    return;
                }

                let remaining = 2;

                function oneDone() {
                    remaining--;
                    if (remaining === 0) {
                        customerDropdownsLoaded = true;
                        if (typeof callback === 'function') callback();
                    }
                }

                // Countries - store both code and name
                $.ajax({
                    url: '{{ route('quotation.lookup.countries') }}',
                    type: 'GET',
                    data: {
                        filter: ''
                    },
                    success: function(data) {
                        const $sel = $('#customerCountrySelect').empty()
                            .append('<option value="">-- Select --</option>');

                        (Array.isArray(data) ? data : []).forEach(function(c) {
                            const name = (c.CountryName || '').trim();
                            const isoCode = (c.CountryCode || '').trim();
                            const countryId = (c.CountryID || '').trim();

                            const isSelected = isoCode === savedCustomerCountryCode;

                            $sel.append(
                                `<option value="${isoCode}"  // Store the code as the value
                    data-name="${name}"
                    data-code="${isoCode}"
                    ${isSelected ? 'selected' : ''}>${name}</option>`
                            );
                        });
                    },
                    complete: oneDone
                });

                // Industry Types - store both code and name
                $.ajax({
                    url: '{{ route('quotation.lookup.industryTypes') }}',
                    type: 'GET',
                    data: {
                        filter: ''
                    },
                    success: function(data) {
                        const $sel = $('#customerIndustrySelect').empty()
                            .append('<option value="">-- Select --</option>');
                        (Array.isArray(data) ? data : []).forEach(function(i) {
                            const name = i.Name || '';
                            const code = i.Code || ''; // Assuming the API returns Code field
                            $sel.append(
                                `<option value="${code}" data-name="${name}">${name}</option>`
                            );
                        });

                        // Pre-select if we have saved industry
                        if (savedCustomerIndustry) {
                            $sel.val(savedCustomerIndustry);
                        }
                    },
                    complete: oneDone
                });
            }

            $('#editCustomerDetailsBtn').on('click', function() {
                originalCustomerType = $('#customerTypeSelect').val();
                originalAgentType = $('#agentTypeSelect').val();

                // Capture with stable, explicit keys — never overwritten until next Edit click
                originalCustomerSnapshot = {
                    companyName: $('input[data-field="companyname"]').val(),
                    address1: $('input[data-field="pri_partner_steet"]').val(),
                    countryDisplay: $('#customerCountryDisplay').val(),
                    countrySelect: $('#customerCountrySelect').val(),
                    industryDisplay: $('#customerIndustryDisplay').val(),
                    industrySelect: $('#customerIndustrySelect').val(),
                };

                // Capture remaining fields by position — same selector, same eq() order as Cancel
                const $leftInputs = $(
                    '#customerDetailsCard .col-md-6:first-child input:not([type="hidden"])');
                const $rightInputs = $(
                    '#customerDetailsCard .col-md-6:last-child input:not([type="hidden"], #customerCountryDisplay, #customerIndustryDisplay)'
                );
                originalCustomerSnapshot.city = $leftInputs.eq(2).val();
                originalCustomerSnapshot.state = $leftInputs.eq(3).val();
                originalCustomerSnapshot.contactPerson = $leftInputs.eq(4).val();
                originalCustomerSnapshot.phoneNo = $leftInputs.eq(5).val();
                originalCustomerSnapshot.clientId = $rightInputs.eq(0).val();
                originalCustomerSnapshot.address2 = $rightInputs.eq(1).val();
                originalCustomerSnapshot.zip = $rightInputs.eq(2).val();
                originalCustomerSnapshot.emailId = $rightInputs.eq(3).val();

                loadCustomerDropdowns(function() {
                    $('#customerCountryDisplay').hide();
                    $('#customerCountryEditWrap').show();
                    $('#customerIndustryDisplay').hide();
                    $('#customerIndustrySelect').show();

                    // Lock ALL fields first — only Company Name is editable by default.
                    // Fields unlock only when "New Agent" is selected from Customer Type.
                    $('#customerDetailsCard .card-body')
                        .find('input:not([type="hidden"])')
                        .prop('readonly', true)
                        .css({
                            'background-color': '',
                            'border-color': ''
                        });

                    // Unlock only Company Name
                    $('input[data-field="companyname"]').prop('readonly', false);

                    // Remove the Address 1 unlock — it's read-only unless New Agent is selected
                    // $('input[data-field="pri_partner_steet"]').removeAttr('readonly');

                    $('.customer-mandatory-star')
                        .hide(); // hide mandatory stars — only shown for New Agent

                    $('#customerTypeSelect').prop('disabled', false);
                    syncAgentTypeState();

                    $('#editCustomerDetailsBtn').hide();
                    $('#saveCustomerDetailsBtn, #cancelCustomerDetailsBtn').show();

                    initCompanyAutocomplete();
                });
            });

            $('#cancelCustomerDetailsBtn').on('click', function() {
                // Restore dropdowns
                $('#customerTypeSelect').val(originalCustomerType).prop('disabled', true);
                $('#agentTypeSelect').val(originalAgentType);

                // Restore explicitly-keyed fields
                $('input[data-field="companyname"]').val(originalCustomerSnapshot.companyName ??
                    '');
                $('input[data-field="pri_partner_steet"]').val(originalCustomerSnapshot
                    .address1 ?? '');

                // Restore positionally-keyed fields — same selectors as Edit snapshot
                const $leftInputs = $(
                    '#customerDetailsCard .col-md-6:first-child input:not([type="hidden"])');
                const $rightInputs = $(
                    '#customerDetailsCard .col-md-6:last-child input:not([type="hidden"], #customerCountryDisplay, #customerCountrySearch, #customerIndustryDisplay)'
                );

                $leftInputs.eq(2).val(originalCustomerSnapshot.city ?? '');
                $leftInputs.eq(3).val(originalCustomerSnapshot.state ?? '');
                $leftInputs.eq(4).val(originalCustomerSnapshot.contactPerson ?? '');
                $leftInputs.eq(5).val(originalCustomerSnapshot.phoneNo ?? '');
                $rightInputs.eq(0).val(originalCustomerSnapshot.clientId ?? '');
                $rightInputs.eq(1).val(originalCustomerSnapshot.address2 ?? '');
                $rightInputs.eq(2).val(originalCustomerSnapshot.zip ?? '');
                $rightInputs.eq(3).val(originalCustomerSnapshot.emailId ?? '');

                // Restore country — swap back to display input
                $('#customerCountryDisplay').val(originalCustomerSnapshot.countryDisplay ?? '')
                    .show();
                $('#customerCountryEditWrap').hide();
                $('#customerCountrySelect').val(originalCustomerSnapshot.countrySelect ?? '')
                    .find('option').show();

                // Restore industry — swap back to display input
                $('#customerIndustryDisplay').val(originalCustomerSnapshot.industryDisplay ??
                    '').show();
                $('#customerIndustrySelect').val(originalCustomerSnapshot.industrySelect ?? '')
                    .hide();

                // Lock all inputs, clear any New Agent styling
                $('#customerDetailsCard .card-body input:not([type="hidden"])').prop('readonly',
                        true)
                    .css({
                        'background-color': '',
                        'border-color': ''
                    });

                // Clear both snapshots
                originalCustomerSnapshot = {};
                newAgentTempSnapshot = {};

                $('.customer-mandatory-star').hide();
                $('#saveCustomerDetailsBtn, #cancelCustomerDetailsBtn').hide();
                $('#editCustomerDetailsBtn').show();

                const $company = $('input[data-field="companyname"]');
                if ($company.hasClass('ui-autocomplete-input')) $company.autocomplete(
                    'destroy');
                $('#selectedClientId').val('');

                // FIX 1: syncAgentTypeState() called AFTER editBtn is shown so
                // inEditMode correctly evaluates to false → agentTypeSelect is locked.
                syncAgentTypeState();
            });

            $('#customerTypeSelect').on('change', function() {
                const selectedType = $(this).val();
                syncAgentTypeState();

                if (selectedType === 'New Agent') {
                    // Snapshot current values into the TEMP variable
                    newAgentTempSnapshot = {};
                    $('#customerDetailsCard .card-body').find('input:not([type="hidden"])')
                        .each(
                            function() {
                                const key = $(this).attr('name') || $(this).data('field') || $(
                                    this).attr(
                                    'id');
                                newAgentTempSnapshot[key] = $(this).val();
                            });
                    newAgentTempSnapshot['__countryDisplay'] = $('#customerCountryDisplay')
                        .val();
                    newAgentTempSnapshot['__industryDisplay'] = $('#customerIndustryDisplay')
                        .val();
                    newAgentTempSnapshot['__countrySelect'] = $('#customerCountrySelect').val();
                    newAgentTempSnapshot['__industrySelect'] = $('#customerIndustrySelect')
                        .val();

                    // Clear and enable ALL fields for New Agent
                    $('#customerDetailsCard .card-body').find('input:not([type="hidden"])')
                        .val('').prop('readonly', false)
                        .css({
                            'background-color': '#fff9e6',
                            'border-color': '#ffc107'
                        });

                } else {
                    // Restore from TEMP snapshot if switching away from New Agent
                    if (Object.keys(newAgentTempSnapshot).length) {
                        $('#customerDetailsCard .card-body').find('input:not([type="hidden"])')
                            .each(
                                function() {
                                    const key = $(this).attr('name') || $(this).data('field') ||
                                        $(this)
                                        .attr('id');
                                    if (newAgentTempSnapshot[key] !== undefined) $(this).val(
                                        newAgentTempSnapshot[key]);
                                });
                        $('#customerCountryDisplay').val(newAgentTempSnapshot[
                            '__countryDisplay'] || '');
                        $('#customerIndustryDisplay').val(newAgentTempSnapshot[
                            '__industryDisplay'] || '');
                        $('#customerCountrySelect').val(newAgentTempSnapshot[
                            '__countrySelect'] || '');
                        $('#customerIndustrySelect').val(newAgentTempSnapshot[
                            '__industrySelect'] || '');
                        newAgentTempSnapshot = {};
                    }

                    // For ALL other types: lock every field except Company Name (Address 1 edit already
                    // captured in originalCustomerSnapshot and protected by the save validation).
                    // Only Company Name remains editable.
                    $('#customerDetailsCard .card-body')
                        .find('input:not([type="hidden"])')
                        .css({
                            'background-color': '',
                            'border-color': ''
                        })
                        .prop('readonly', true);

                    // Re-enable only Company Name
                    $('input[data-field="companyname"]').prop('readonly', false);
                }
            });

            // ============================================
            // SAVE CUSTOMER DETAILS
            // FIX: agentType reads option value directly — which is now the Code (e.g. "WCA"),
            // not the GUID. This matches what sp_Quote_DetailsUpdate expects.
            // ============================================
            $('#saveCustomerDetailsBtn').on('click', function() {
                const customerType = $('#customerTypeSelect').val();
                const agentType = $('#agentTypeSelect').val() || '';
                const companyName = $('input[data-field="companyname"]').val()?.trim() || '';
                const address1 = $('input[data-field="pri_partner_steet"]').val()?.trim() || '';

                const countryCode = $('#customerCountrySelect option:selected').data('code')?.trim() || '';
                const countryName = $('#customerCountrySelect option:selected').text()?.trim() || '';

                const industryCode = $('#customerIndustrySelect option:selected').val()?.trim() || '';
                const industryName = $('#customerIndustrySelect option:selected').text()?.trim() || '';

                // Validation
                if (!customerType) {
                    showNotification('Customer Type is required', 'error');
                    return;
                }
                if (customerType.toLowerCase().includes('agent') && !agentType) {
                    showNotification('Agent Type is required for Agent customers', 'error');
                    return;
                }
                if (!companyName) {
                    showNotification('Company Name is required', 'error');
                    return;
                }
                const isNewAgent = customerType === 'New Agent';
                if (isNewAgent && !address1) {
                    showNotification('Address 1 is required', 'error');
                    return;
                }
                if (!countryCode) {
                    showNotification('Country is required', 'error');
                    return;
                }
                if (!industryCode) {
                    showNotification('Industry Type is required', 'error');
                    return;
                }

                // Format date as YYYYMMDDHHMMSS (14 digits, no letters, no separators)
                const now = new Date();
                const year = now.getFullYear();
                const month = String(now.getMonth() + 1).padStart(2, '0');
                const day = String(now.getDate()).padStart(2, '0');
                const hours = String(now.getHours()).padStart(2, '0');
                const minutes = String(now.getMinutes()).padStart(2, '0');
                const seconds = String(now.getSeconds()).padStart(2, '0');
                const submittedDatetime = `${year}${month}${day}${hours}${minutes}${seconds}`;

                console.log('Formatted datetime:', submittedDatetime); // Should be like: 20260327151222

                // Build the JSON input
                const inputJson = JSON.stringify({
                    OperationType: "CustomerUpdate",
                    SubmittedDatetime: submittedDatetime,
                    SubmittedBy: "{{ Auth::user()->username ?? 'system' }}",
                    Action: "Update",
                    QuotationID: quotationId,
                    ClientCode: $('#selectedClientCode').val() || '',
                    Company: companyName,
                    Address1: address1,
                    Address2: $('#customerAddress2Input').val()?.trim() || '',
                    City: $('#customerCityInput').val()?.trim() || '',
                    ZipCode: $('#customerZipInput').val()?.trim() || '',
                    State: $('#customerStateInput').val()?.trim() || '',
                    CountryCode: countryCode,
                    ContactPerson: $('#customerContactInput').val()?.trim() || '',
                    Department: '',
                    EmailID: $('#customerEmailInput').val()?.trim() || '',
                    PhoneNo: $('#customerPhoneInput').val()?.trim() || '',
                    IndustryCode: industryCode,
                    Industry: industryName,
                    CustomerType: customerType,
                    AgentType: agentType
                });

                console.log('Saving with ClientCode:', $('#selectedClientCode').val());
                console.log('Saving JSON:', inputJson);

                $.ajax({
                    url: '{{ route('quotation.customer.save') }}',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        input_json: inputJson
                    },
                    beforeSend: function() {
                        $('#saveCustomerDetailsBtn').prop('disabled', true)
                            .html('<i class="fas fa-spinner fa-spin mr-1"></i> Saving...');
                    },
                    success: function(response) {
                        if (response.success) {
                            showNotification(response.message ||
                                'Customer details saved successfully', 'success');

                            // Update display fields
                            $('#customerCountryDisplay').val(countryName).show();
                            $('#customerCountryEditWrap').hide();
                            $('#customerIndustryDisplay').val(industryName).show();
                            $('#customerIndustrySelect').hide();

                            $('input[data-field="companyname"]').attr('readonly', true);
                            $('input[data-field="pri_partner_steet"]').attr('readonly', true);
                            $('.customer-mandatory-star').hide();

                            originalCustomerType = customerType;
                            originalAgentType = agentType;
                            $('#customerTypeSelect, #agentTypeSelect').prop('disabled', true);
                            $('#saveCustomerDetailsBtn, #cancelCustomerDetailsBtn').hide();
                            $('#editCustomerDetailsBtn').show();
                        } else {
                            showNotification(response.message || 'Failed to save', 'error');
                        }
                    },
                    error: function(xhr) {
                        console.error('Save error:', xhr);
                        showNotification(xhr.responseJSON?.message || 'Failed to save',
                            'error');
                    },
                    complete: function() {
                        $('#saveCustomerDetailsBtn').prop('disabled', false)
                            .html('<i class="fas fa-save mr-1"></i> Save');
                    }
                });
            });

            // Run on page load
            fetchCustomerTypes();
            fetchAgentTypes();


            // ============================================
            // HELPER FUNCTIONS
            // ============================================
            function clearModalForm(type) {
                const $modal = type === 'revenue' ? $('#addRevenueModal') : $('#addCostModal');
                const chargeTypeRadioName = type === 'revenue' ? 'revenueChargeType' : 'costChargeType';
                const increaseTypeSelectName = type === 'revenue' ? 'revenue_increase_type' :
                    'cost_increase_type';

                $modal.find('input[type="text"], input[type="number"]').val('');
                $modal.find('input[type="number"]').val('0');

                // Reset selects EXCEPT increase_type — let toggleCostFormFields own its default
                $modal.find('select').not(`select[name="${increaseTypeSelectName}"]`).prop(
                    'selectedIndex', 0);

                // Reset increase_type to a valid default (Percentage), not blank
                $modal.find(`select[name="${increaseTypeSelectName}"]`).val('Percentage');

                $modal.find('input[type="checkbox"]').prop('checked', false);

                if (type === 'revenue') {
                    $modal.find('.increase-by-row').removeClass('active').css('display', 'none');
                    $modal.find('.increase-by-row input[type="number"]').val('0.00');
                } else {
                    $modal.find('.cost-markup-row').removeClass('active').css('display', 'none');
                    $modal.find('.cost-markup-row input[type="number"]').val('0.00');
                }

                if (!currentEditingCharge) {
                    loadBasedOnList(type);
                    populateCurrencyDropdown(type);
                }
            }

            function clearRevenueModalForm() {
                const currentType = $('#addRevenueModal').find(
                        'input[name="revenueChargeType"]:checked').val() ||
                    'revenue';
                clearModalForm('revenue');
                $(`#addRevenueModal input[name="revenueChargeType"][value="${currentType}"]`).prop(
                    'checked', true);
                toggleRevenueFormFields(currentType);
            }

            function clearCostModalForm() {
                // Read the currently selected type BEFORE clearModalForm resets the form
                const currentType = $('#addCostModal').find('input[name="costChargeType"]:checked')
                    .val() || 'cost';
                clearModalForm('cost');
                // Re-apply the checked state that clearModalForm wiped
                $(`#addCostModal input[name="costChargeType"][value="${currentType}"]`).prop('checked',
                    true);
                toggleCostFormFields(currentType);
            }

            // ============================================
            // CARD COLLAPSE & SHOW ITEMS
            // ============================================
            $('.col-extra').hide();

            $('.show-items-link').on('click', function(e) {
                e.preventDefault();
                const tableId = $(this).data('table');
                const $table = tableId === 'revenue' ?
                    $('#revenueChargesTable').closest('table') :
                    $('#costChargesTable').closest('table');
                const isExpanded = $(this).data('expanded') || false;

                if (isExpanded) {
                    $table.find('.col-extra').hide();
                    $(this).html('<i class="fas fa-columns mr-1"></i> Show more');
                    $(this).data('expanded', false);
                } else {
                    $table.find('.col-extra').show();
                    $(this).html('<i class="fas fa-columns mr-1"></i> Show Less');
                    $(this).data('expanded', true);
                }
            });

            $('[data-card-widget="collapse"]').on('click', function() {
                $(this).find('i').toggleClass('fa-chevron-down fa-chevron-up');
            });

            // Replace the existing card-header click handler with this:
            $('.card-header').on('click', function(e) {
                if ($(e.target).closest(
                        '.ui-autocomplete, .ui-autocomplete li, select, input, button, a, label, .show-items-link, [data-card-widget="collapse"]'
                    ).length) {
                    return; // Do nothing for autocomplete or form elements
                }
                $(this).find('[data-card-widget="collapse"]').click();
            });

            // Also ensure autocomplete mousedown doesn't bubble (you already have this, but keep it)
            $(document).on('mousedown', '.ui-autocomplete', function(e) {
                e.stopPropagation();
            });

            $(document).on('mousedown', '.ui-autocomplete li', function(e) {
                // e.preventDefault();
                e.stopPropagation();
            });

            // Stop .card-header collapse from swallowing autocomplete dropdown clicks.
            // mousedown bubbles from .ui-autocomplete up to .card-header, which triggers
            // collapse and destroys focus before jQuery UI's select (mouseup) fires —
            // making every suggestion appear un-clickable.
            $(document).on('mousedown', '.ui-autocomplete', function(e) {
                e.stopPropagation();
            });

            $(document).on('mousedown', '.ui-autocomplete li', function(e) {
                e.preventDefault();
            });

            $('.card').not('.card-success, .card-danger').each(function() {
                const $card = $(this);
                const cardTitle = $card.find('.card-title').text().trim();

                // Never auto-collapse Summary or Quotation Document
                if (cardTitle === 'Summary' || cardTitle === 'Quotation Document') return;

                $card.find('.card-body').hide();
                $card.find('[data-card-widget="collapse"] i')
                    .removeClass('fa-chevron-up')
                    .addClass('fa-chevron-down');
                $card.addClass('collapsed-card');
            });


            function loadTermsTemplates() {
                const codeToUse = officeCode;

                if (!codeToUse) {
                    setTimeout(loadTermsTemplates, 500);
                    return;
                }

                $.ajax({
                    url: '{{ route('quotation.terms-templates') }}',
                    type: 'GET',
                    data: {
                        quotation_id: quotationId
                    },
                    success: function(response) {
                        const $select = $('#termsTemplateSelect');
                        $select.empty();

                        if (response.success && response.templates.length > 0) {

                            // ── SINGLE TEMPLATE: auto-apply ──────────────────────────
                            if (response.templates.length === 1) {
                                const t = response.templates[0];

                                $('#termsDropdownWrapper').hide();
                                $('#termsAutoAppliedName').text(t.Name);
                                $('#termsAutoAppliedBadge').show();

                                // Single template — use cached description if SP returned it,
                                // otherwise fall back to getCommunicationDescription
                                const singleDesc = t.Description || '';

                                if (singleDesc) {
                                    $('#termsDescriptionPreview').val(singleDesc);
                                    $('#termsDescriptionWrapper').show();
                                    $('#applyTermsBtn').hide();

                                    if (tcAlreadyApplied) {
                                        termsState.applied = true;
                                        setGenerateBtn();
                                        return;
                                    }

                                    $.ajax({
                                        url: '{{ route('quotation.communication.save-terms') }}',
                                        type: 'POST',
                                        data: {
                                            _token: '{{ csrf_token() }}',
                                            booking_no: bookingNo,
                                            terms_condition: singleDesc,
                                            template_name: t.Name
                                        },
                                        success: function(saveResponse) {
                                            if (saveResponse.success) {
                                                termsState.applied = true;
                                                setGenerateBtn();
                                            } else {
                                                showNotification(
                                                    'Auto-apply T&C failed: ' +
                                                    (saveResponse.message ||
                                                        'Unknown error'),
                                                    'warning'
                                                );
                                                setGenerateBtn();
                                            }
                                        },
                                        error: function() {
                                            setGenerateBtn();
                                        }
                                    });

                                } else {
                                    // SP did not return description — fetch it
                                    $.ajax({
                                        url: '{{ route('quotation.communication.description') }}',
                                        type: 'GET',
                                        data: {
                                            module: 'Quotation',
                                            sub_module: 'T&C',
                                            office_code: codeToUse,
                                            name: t.Name
                                        },
                                        success: function(descResponse) {
                                            if (!descResponse.success) return;

                                            const description = descResponse.description;
                                            $('#termsDescriptionPreview').val(description);
                                            $('#termsDescriptionWrapper').show();
                                            $('#applyTermsBtn').hide();

                                            if (tcAlreadyApplied) {
                                                termsState.applied = true;
                                                setGenerateBtn();
                                                return;
                                            }

                                            $.ajax({
                                                url: '{{ route('quotation.communication.save-terms') }}',
                                                type: 'POST',
                                                data: {
                                                    _token: '{{ csrf_token() }}',
                                                    booking_no: bookingNo,
                                                    terms_condition: description,
                                                    template_name: t.Name
                                                },
                                                success: function(saveResponse) {
                                                    if (saveResponse.success) {
                                                        termsState.applied =
                                                            true;
                                                        setGenerateBtn();
                                                    } else {
                                                        showNotification(
                                                            'Auto-apply T&C failed: ' +
                                                            (saveResponse
                                                                .message ||
                                                                'Unknown error'
                                                            ),
                                                            'warning'
                                                        );
                                                        setGenerateBtn();
                                                    }
                                                },
                                                error: function() {
                                                    setGenerateBtn();
                                                }
                                            });
                                        },
                                        error: function() {
                                            showNotification(
                                                'Failed to load T&C description',
                                                'warning');
                                            setGenerateBtn();
                                        }
                                    });
                                }

                                return; // skip multi-template path
                            }

                            // ── MULTIPLE TEMPLATES: show dropdown ────────────────────
                            $select.append('<option value="">-- Select Template --</option>');
                            response.templates.forEach(function(t) {
                                $select.append(
                                    `<option value="${t.Name}">${t.Name}</option>`
                                );
                            });

                            $('#termsDropdownWrapper').show();
                            $('#termsAutoAppliedBadge').hide();

                            // Cache descriptions returned by the SP — keyed by template name
                            response.templates.forEach(function(t) {
                                if (t.Description) {
                                    termsDescriptionCache[t.Name] = t.Description;
                                }
                            });

                            if (!tcAlreadyApplied) {
                                $('#termsTemplateSelect').closest('.mt-3.pt-2')
                                    .find('label').first()
                                    .append(
                                        ' <span class="text-danger terms-required-star">*</span>'
                                    );
                            }

                            // ── AUTO-SELECT: match template against service name + process ──────
                            const svNameRaw = quotationSVName ? quotationSVName.trim() : '';
                            const processRaw = quotationProcess ? quotationProcess.trim() : '';

                            if (svNameRaw) {
                                const svLower = svNameRaw.toLowerCase();
                                const processLower = processRaw.toLowerCase();
                                const combined = (svNameRaw + ' ' + processRaw).toLowerCase().trim();
                                let matchedName = null;

                                // Priority 1: exact match against combined "SVName + Process"
                                response.templates.forEach(function(t) {
                                    if (!matchedName && t.Name.toLowerCase().trim() ===
                                        combined) {
                                        matchedName = t.Name;
                                    }
                                });

                                // Priority 2: template name contains both service name AND process
                                if (!matchedName && processLower) {
                                    response.templates.forEach(function(t) {
                                        const tLower = t.Name.toLowerCase();
                                        if (!matchedName && tLower.includes(svLower) && tLower
                                            .includes(processLower)) {
                                            matchedName = t.Name;
                                        }
                                    });
                                }

                                // Priority 3: template name contains service name only (fallback)
                                if (!matchedName) {
                                    response.templates.forEach(function(t) {
                                        if (!matchedName && t.Name.toLowerCase().includes(
                                                svLower)) {
                                            matchedName = t.Name;
                                        }
                                    });
                                }

                                // Priority 4: service name contains template name keyword
                                if (!matchedName) {
                                    response.templates.forEach(function(t) {
                                        const tLower = t.Name.toLowerCase().trim();
                                        if (!matchedName && tLower.length > 3 && svLower
                                            .includes(tLower)) {
                                            matchedName = t.Name;
                                        }
                                    });
                                }

                                if (matchedName) {
                                    $('#termsTemplateSelect').val(matchedName);
                                    if (tcAlreadyApplied) {
                                        const savedDesc =
                                            '{{ addslashes($quotation->terms_condition ?? '') }}';
                                        if (savedDesc) {
                                            $('#termsDescriptionPreview').val(savedDesc);
                                            $('#termsDescriptionWrapper').show();
                                        }
                                        termsState.applied = true;
                                        setGenerateBtn();
                                    } else {
                                        $('#termsTemplateSelect').trigger('change');
                                    }
                                } else {
                                    if (tcAlreadyApplied) {
                                        const savedDesc =
                                            '{{ addslashes($quotation->terms_condition ?? '') }}';
                                        if (savedDesc) {
                                            $('#termsDescriptionPreview').val(savedDesc);
                                            $('#termsDescriptionWrapper').show();
                                        }
                                        termsState.applied = true;
                                        setGenerateBtn();
                                    }
                                    console.warn('loadTermsTemplates - No T&C template matched SVName:',
                                        svNameRaw, '| Process:', processRaw);
                                }
                            } else {
                                if (tcAlreadyApplied) {
                                    const savedDesc =
                                        '{{ addslashes($quotation->terms_condition ?? '') }}';
                                    if (savedDesc) {
                                        $('#termsDescriptionPreview').val(savedDesc);
                                        $('#termsDescriptionWrapper').show();
                                    }
                                    termsState.applied = true;
                                    setGenerateBtn();
                                }
                                console.warn(
                                    'loadTermsTemplates - quotationSVName is empty, T&C auto-select skipped.'
                                );
                            }

                            @if ($quotation->QuotationTCTemplate)
                                const savedTemplateName =
                                    '{{ addslashes($quotation->QuotationTCTemplate ?? '') }}';
                                if (savedTemplateName && !$('#termsTemplateSelect').val()) {
                                    $('#termsTemplateSelect').val(savedTemplateName);
                                }
                            @endif

                        } // ← closes if (response.success && response.templates.length > 0)
                        else {
                            // Error states — surface clearly, button stays disabled
                            const msg = response.code === 'NOT_CONFIGURED' ?
                                `⚠ T&C not configured for office: ${codeToUse}` :
                                response.code === 'OFFICE_NOT_FOUND' ?
                                `⚠ Office not found: ${codeToUse}` :
                                '-- No templates available --';
                            $select.append(`<option value="" disabled>${msg}</option>`);
                            setGenerateBtn();
                        }
                    },
                    error: function(xhr) {
                        const res = xhr.responseJSON;
                        const $select = $('#termsTemplateSelect');
                        $select.empty();

                        const msg = res?.code === 'NOT_CONFIGURED' ?
                            `⚠ T&C not configured for office: ${codeToUse}` :
                            res?.code === 'OFFICE_NOT_FOUND' ?
                            `⚠ Office not found: ${codeToUse}` :
                            '-- Failed to load templates --';
                        $select.append(`<option value="" disabled>${msg}</option>`);
                        console.warn('loadTermsTemplates error:', res?.message || xhr.status);
                        setGenerateBtn();
                    }
                });
            }
            // HELPER — called from both the cache path and the fetch fallback path
            function _applyTermsDescription(templateName, description) {
                $('#termsDescriptionPreview').val(description);
                $('#termsDescriptionWrapper').show();
                $('#applyTermsBtn').prop('disabled', false);

                $.ajax({
                    url: '{{ route('quotation.communication.save-terms') }}',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        booking_no: bookingNo,
                        terms_condition: description,
                        template_name: templateName
                    },
                    success: function(saveResponse) {
                        if (saveResponse.success) {
                            termsState.applied = true;
                            setGenerateBtn();
                        } else {
                            showNotification(saveResponse.message ||
                                'Failed to apply Terms & Conditions',
                                'error');
                            setGenerateBtn();
                        }
                    },
                    error: function() {
                        showNotification('Failed to apply Terms & Conditions', 'error');
                        setGenerateBtn();
                    }
                });
            }
            $('#termsTemplateSelect').on('change', function() {
                const selectedName = $(this).val();

                $('.terms-required-star').remove();
                termsState.applied = false;
                setGenerateBtn();
                $('#termsReSelectMsg').hide();
                $('#applyTermsBtn').prop('disabled', true);

                if (!selectedName) {
                    $('#termsDescriptionWrapper').hide();
                    $('#termsDescriptionPreview').val('');
                    return;
                }

                // USE CACHED DESCRIPTION — skip the getCommunicationDescription round-trip
                // if the SP already returned it in the template list response.
                if (termsDescriptionCache[selectedName]) {
                    _applyTermsDescription(selectedName, termsDescriptionCache[selectedName]);
                    return;
                }

                // FALLBACK — fetch description if not in cache (e.g. SP didn't return it)
                $.ajax({
                    url: '{{ route('quotation.communication.description') }}',
                    type: 'GET',
                    data: {
                        module: 'Quotation',
                        sub_module: 'T&C',
                        office_code: officeCode,
                        name: selectedName
                    },
                    beforeSend: function() {
                        $('#termsDescriptionPreview').val('Loading...');
                        $('#termsDescriptionWrapper').show();
                    },
                    success: function(response) {
                        if (!response.success) {
                            $('#termsDescriptionPreview').val('Failed to load description.');
                            showNotification('Could not load T&C description', 'warning');
                            return;
                        }
                        _applyTermsDescription(selectedName, response.description);
                    },
                    error: function() {
                        $('#termsDescriptionPreview').val('Error loading description.');
                        showNotification('Error loading T&C description', 'error');
                        setGenerateBtn();
                    }
                });
            });

            $('#applyTermsBtn').on('click', function() {
                const description = $('#termsDescriptionPreview').val();

                if (!description || description === 'Loading...') {
                    showNotification('No Terms & Conditions to apply', 'warning');
                    return;
                }

                if (!bookingNo) {
                    showNotification('No booking number — cannot save Terms & Conditions',
                        'error');
                    return;
                }

                termsState.applied = false;
                setGenerateBtn();

                $.ajax({
                    url: '{{ route('quotation.communication.save-terms') }}',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        booking_no: bookingNo,
                        terms_condition: description,
                        template_name: $('#termsTemplateSelect').val()
                    },
                    beforeSend: function() {
                        $('#applyTermsBtn').prop('disabled', true)
                            .html(
                                '<i class="fas fa-spinner fa-spin mr-1"></i> Saving...'
                            );
                    },
                    success: function(response) {
                        if (response.success) {
                            showNotification(
                                'Terms & Conditions applied successfully',
                                'success');
                            termsState.applied =
                                true; // ← consistent with all other save-terms paths
                            setGenerateBtn();
                        } else {
                            showNotification(response.message ||
                                'Failed to apply Terms',
                                'error');
                        }
                    },
                    error: function(xhr) {
                        showNotification(xhr.responseJSON?.message ||
                            'Failed to apply Terms',
                            'error');
                    },
                    complete: function() {
                        $('#applyTermsBtn').prop('disabled', false)
                            .html('<i class="fas fa-save mr-1"></i> Apply Terms');
                    }
                });
            });

            // ============================================
            // ============================================
            // COPIED BOOKING — PUT ALL CARDS INTO EDIT MODE
            // Triggers the Edit button on each card so all
            // dropdowns load and fields become editable,
            // exactly matching the normal edit page flow.
            // ============================================
            if (isCopiedBooking) {

                // Expand Inquiry Details card (not in the array below — needs explicit expand)
                (function() {
                    const $inquiryCard = $('#editInquiryBtn').closest('.card');
                    $inquiryCard.find('.card-body').show();
                    $inquiryCard.find('[data-card-widget="collapse"] i')
                        .removeClass('fa-chevron-down')
                        .addClass('fa-chevron-up');
                    $inquiryCard.removeClass('collapsed-card');
                })();

                // Expand the remaining three cards
                const cardsToExpand = [
                    '#customerDetailsCard',
                    '#shipmentDetailsCard',
                    '#cargoDetailsCard'
                ];

                cardsToExpand.forEach(function(cardId) {
                    const $card = $(cardId);
                    $card.find('.card-body').show();
                    $card.find('[data-card-widget="collapse"] i')
                        .removeClass('fa-chevron-down')
                        .addClass('fa-chevron-up');
                    $card.removeClass('collapsed-card');
                });

                // Trigger each card's Edit button with a staggered delay.
                // Inquiry first — lightweight (only sales person autocomplete).
                // Customer second — loads countries, industries, customer/agent types.
                // Shipment third — loads InCo Terms, modes, country selects.
                // Cargo last — loads equipment types.
                setTimeout(function() {
                    $('#editInquiryBtn').trigger('click');
                }, 200);

                setTimeout(function() {
                    $('#editCustomerDetailsBtn').trigger('click');
                }, 400);

                setTimeout(function() {
                    $('#editShipmentDetailsBtn').trigger('click');
                }, 600);

                setTimeout(function() {
                    $('#editCargoDetailsBtn').trigger('click');
                }, 800);
            }
            // ============================================
            // CARGO DETAILS — CONTAINER LIST
            // Loaded from Quote_Quotation_EquipmentDetails via getContainers.
            // Saved via sp_Quote_CargoDetailsUpdate.
            // Container string format: "20GPX2,40HCX1"
            // SP typo "Qunatity" is handled in the controller — JS just passes the string.
            // ============================================
            const QUOTE_NO = '{{ $quotation->QuoteNo ?? '' }}';
            let originalCargoVolume = '';
            let originalCommodity = '';
            let originalContainers = []; // [{type, qty}] snapshot for Cancel

            let equipmentTypeOptions = [];

            // Load containers on page init
            loadContainers();

            function loadEquipmentTypeOptions(callback) {
                if (equipmentTypeOptions.length > 0) {
                    if (typeof callback === 'function') callback();
                    return;
                }
                $.ajax({
                    url: '{{ route('quotation.equipment-types') }}',
                    type: 'GET',
                    success: function(response) {
                        if (response.success && response.data.length > 0) {
                            equipmentTypeOptions = response.data;
                        }
                        if (typeof callback === 'function') callback();
                    },
                    error: function() {
                        console.error('Failed to load equipment types');
                        if (typeof callback === 'function') callback();
                    }
                });
            }

            function serializeContainerList() {
                const rows = [];
                $('#containerListBody tr[data-container-row]').each(function() {
                    const type = $(this).find('.container-type-select').val();
                    const qty = parseInt($(this).find('.container-qty-input').val(), 10) || 0;
                    if (type && qty > 0) rows.push(type + 'X' + qty);
                });
                return rows.join(',');
            }

            function buildContainerRow(type, qty, editable) {
                const $row = $('<tr data-container-row="1">');

                const $typeCell = $('<td>');
                if (editable) {
                    const $select = $(
                        '<select class="form-control form-control-sm container-type-select">');
                    $select.append('<option value="">-- Select --</option>');
                    equipmentTypeOptions.forEach(function(item) {
                        $select.append(
                            `<option value="${item.Code}" ${item.Code === type ? 'selected' : ''}>${item.Code}</option>`
                        );
                    });
                    $typeCell.append($select);
                } else {
                    $typeCell.text(type || '—');
                }

                const $qtyCell = $('<td>');
                if (editable) {
                    $qtyCell.append(
                        `<input type="number" class="form-control form-control-sm container-qty-input"
                value="${qty}" min="1" style="width:75px;">`
                    );
                } else {
                    $qtyCell.text(qty);
                }

                const $actionCell = $('<td class="text-center">');
                if (editable) {
                    $actionCell.append(
                        `<button type="button" class="btn btn-danger btn-sm remove-container-row">
                <i class="fas fa-times"></i>
            </button>`
                    );
                }

                return $row.append($typeCell, $qtyCell, $actionCell);
            }

            function renderContainerRows(containers, editable) {
                const $tbody = $('#containerListBody');
                $tbody.empty();

                // Show/hide action column header
                if (editable) {
                    $('#containerActionCol').removeClass('d-none');
                } else {
                    $('#containerActionCol').addClass('d-none');
                }

                if (!containers.length) {
                    const msg = editable ? 'No containers added — click "Add a line" below' :
                        'No containers';
                    $tbody.append(
                        `<tr><td colspan="3" class="text-muted text-center">${msg}</td></tr>`);
                    return;
                }

                containers.forEach(function(c) {
                    $tbody.append(buildContainerRow(c.type, c.qty, editable));
                });
            }

            function loadContainers() {
                $.ajax({
                    url: '{{ route('quotation.containers') }}',
                    type: 'GET',
                    data: {
                        quotation_id: quotationId
                    },
                    success: function(response) {
                        if (response.success) {
                            const containers = (response.containers || []).map(function(c) {
                                return {
                                    type: c.EquipmentType,
                                    qty: c.Quantity
                                };
                            });
                            originalContainers = containers;
                            renderContainerRows(containers, false);
                        } else {
                            $('#containerListBody').html(
                                '<tr><td colspan="3" class="text-danger text-center">Failed to load</td></tr>'
                            );
                        }
                    },
                    error: function() {
                        $('#containerListBody').html(
                            '<tr><td colspan="3" class="text-danger text-center">Error loading containers</td></tr>'
                        );
                    }
                });
            }

            // Remove row
            $(document).on('click', '.remove-container-row', function() {
                $(this).closest('tr').remove();
            });

            // Add a line
            $('#addContainerRowBtn').on('click', function() {
                $('#containerListBody tr:not([data-container-row])').remove();
                loadEquipmentTypeOptions(function() {
                    $('#containerListBody').append(buildContainerRow('', 1, true));
                });
            });

            // Edit
            $('#editCargoDetailsBtn').on('click', function() {
                originalCargoVolume = $('#cargoVolumeInput').val();
                originalCommodity = $('#commodityInput').val();
                originalContainers = [];
                $('#containerListBody tr[data-container-row]').each(function() {
                    originalContainers.push({
                        type: $(this).find('td:first').text().trim(),
                        qty: parseInt($(this).find('td:eq(1)').text().trim(),
                            10) || 0
                    });
                });

                $('#scopeVolumeDisplay').hide();
                $('#scopeVolumeSelect').removeClass('d-none').prop('disabled', false);
                $('.customer-mandatory-star', '#cargoDetailsCard').show();

                $('#cargoVolumeInput, #commodityInput').prop('readonly', false).css({
                    'background-color': '#fff',
                    'border': '1px solid #ced4da'
                });

                // ← Load equipment types first, THEN render editable rows
                loadEquipmentTypeOptions(function() {
                    renderContainerRows(originalContainers, true);
                    $('#addContainerRowBtn').removeClass('d-none');
                });

                $(this).addClass('d-none');
                $('#saveCargoDetailsBtn, #cancelCargoDetailsBtn').removeClass('d-none');
            });

            // Cancel
            $('#cancelCargoDetailsBtn').on('click', function() {
                $('#cargoVolumeInput').val(originalCargoVolume).prop('readonly', true).css(
                    'background-color', '');
                $('#commodityInput').val(originalCommodity).prop('readonly', true).css(
                    'background-color',
                    '');
                renderContainerRows(originalContainers, false);
                $('#addContainerRowBtn').addClass('d-none');

                $('#scopeVolumeSelect').addClass('d-none').prop('disabled', true);
                $('#scopeVolumeDisplay').show();
                $('.customer-mandatory-star', '#cargoDetailsCard').hide();

                $(this).addClass('d-none');
                $('#saveCargoDetailsBtn').addClass('d-none');
                $('#editCargoDetailsBtn').removeClass('d-none');
            });

            // Save
            $('#saveCargoDetailsBtn').on('click', function() {
                const cargoVolume = $('#cargoVolumeInput').val();
                const containers = serializeContainerList();
                const scopeVolume = $('#scopeVolumeSelect').val() ||
                    '{{ $quotation->scopevolume ?? '' }}';

                if (!scopeVolume) {
                    showNotification('Scope of Volume is required', 'error');
                    return;
                }
                const commodity = $('#commodityInput').val().trim();
                if (!commodity) {
                    showNotification('Commodity is required', 'error');
                    return;
                }

                if (scopeVolume !== 'Containerized' && (!cargoVolume || parseFloat(
                        cargoVolume) <= 0)) {
                    showNotification('Cargo Volume must be greater than zero', 'error');
                    return;
                }

                if (scopeVolume === 'Containerized' && !containers) {
                    showNotification('Please add at least one container', 'error');
                    return;
                }

                $.ajax({
                    url: '{{ route('quotation.cargo.save') }}',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        quotation_id: quotationId,
                        scope_volume: scopeVolume,
                        commodity: $('#commodityInput').val(),
                        cargo_volume: cargoVolume || 0,
                        containers: containers
                    },
                    beforeSend: function() {
                        $('#saveCargoDetailsBtn').prop('disabled', true)
                            .html(
                                '<i class="fas fa-spinner fa-spin mr-1"></i> Saving...'
                            );
                    },
                    success: function(response) {
                        if (response.success) {
                            showNotification('Cargo details saved successfully',
                                'success');

                            originalCargoVolume = cargoVolume;
                            originalContainers = [];
                            $('#containerListBody tr[data-container-row]').each(
                                function() {
                                    originalContainers.push({
                                        type: $(this).find(
                                                '.container-type-select'
                                            )
                                            .val(),
                                        qty: parseInt($(this).find(
                                                '.container-qty-input'
                                            )
                                            .val(),
                                            10) || 0
                                    });
                                });

                            $('#cargoVolumeInput').prop('readonly', true).css(
                                'background-color', '');
                            $('#commodityInput').prop('readonly', true).css(
                                'background-color',
                                '');
                            renderContainerRows(originalContainers, false);
                            $('#addContainerRowBtn').addClass('d-none');
                            $('#saveCargoDetailsBtn, #cancelCargoDetailsBtn')
                                .addClass(
                                    'd-none');
                            $('#editCargoDetailsBtn').removeClass('d-none');
                        } else {
                            showNotification(response.message || 'Failed to save',
                                'error');
                        }
                    },
                    error: function(xhr) {
                        showNotification(xhr.responseJSON?.message ||
                            'Failed to save',
                            'error');
                    },
                    complete: function() {
                        $('#saveCargoDetailsBtn').prop('disabled', false)
                            .html('<i class="fas fa-save mr-1"></i> Save');
                    }
                });
            });


            // ============================================
            // INQUIRY DETAILS — EDIT / SAVE / CANCEL
            // ============================================
            var inquirySnapshot = {};

            $('#editInquiryBtn').on('click', function() {
                inquirySnapshot = {
                    display: $('#salesPersonDisplay').val(),
                    code: $('#salesPersonCode').val(),
                    email: $('#salesPersonEmail').val(),
                };
                $('#salesPersonDisplay').removeAttr('readonly');
                $('#salesPersonEmail').removeAttr('readonly');
                $('#editInquiryBtn').addClass('d-none');
                $('#saveInquiryBtn, #cancelInquiryBtn').removeClass('d-none');

                // FIX: Initialize autocomplete AFTER readonly is removed —
                // jQuery UI cannot attach properly to a readonly input
                initSalesPersonAutocomplete();
            });

            $('#cancelInquiryBtn').on('click', function() {
                $('#salesPersonDisplay').val(inquirySnapshot.display).attr('readonly', true);
                $('#salesPersonCode').val(inquirySnapshot.code);
                $('#salesPersonEmail').val(inquirySnapshot.email);
                $('#editInquiryBtn').removeClass('d-none');
                $('#saveInquiryBtn, #cancelInquiryBtn').addClass('d-none');

                // Destroy autocomplete and restore readonly — jQuery UI does not play
                // well with readonly inputs so we clean up on cancel
                const $sp = $('#salesPersonDisplay');
                if ($sp.hasClass('ui-autocomplete-input')) $sp.autocomplete('destroy');
            });

            $('#saveInquiryBtn').on('click', function() {
                var code = $('#salesPersonCode').val().trim();
                var email = $('#salesPersonEmail').val().trim();

                if (!code) {
                    showNotification('Please select a Sales Person.', 'error');
                    return;
                }
                if (!email) {
                    showNotification('Sales Person Email is required.', 'error');
                    return;
                }

                var $btn = $(this).prop('disabled', true).html(
                    '<i class="fas fa-spinner fa-spin"></i> Saving...');

                $.ajax({
                    url: '{{ route('quotation.inquiry.save') }}',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        quotation_id: quotationId,
                        sales_person_code: code,
                        sales_person_email: email,
                    },
                    success: function(res) {
                        if (res.success) {
                            $('#salesPersonDisplay').attr('readonly', true);
                            $('#salesPersonEmail').attr('readonly', true);
                            $('#editInquiryBtn').removeClass('d-none');
                            $('#saveInquiryBtn, #cancelInquiryBtn').addClass(
                                'd-none');
                            showNotification('Inquiry details saved.', 'success');
                        } else {
                            showNotification(res.message || 'Save failed.',
                                'error');
                        }
                    },
                    error: function(xhr) {
                        showNotification(xhr.responseJSON?.message ||
                            'Server error.', 'error');
                    },
                    complete: function() {
                        $btn.prop('disabled', false).html(
                            '<i class="fas fa-save mr-1"></i> Save');
                    }
                });
            });

            // ============================================
            // SALES PERSON AUTOCOMPLETE
            // FIX: Replaced manual <ul> suggestion pattern with jQuery UI autocomplete.
            // The manual input event did not fire reliably after removeAttr('readonly').
            // jQuery UI is already loaded on the page and handles this correctly.
            // ============================================
            function initSalesPersonAutocomplete() {
                const $input = $('#salesPersonDisplay');

                if ($input.hasClass('ui-autocomplete-input')) {
                    $input.autocomplete('destroy');
                }

                $input.autocomplete({
                    minLength: 2,
                    delay: 300,
                    source: function(request, response) {
                        $.ajax({
                            url: '{{ route('quotation.sales-persons') }}',
                            type: 'GET',
                            data: {
                                filter: request.term,
                                office_id: officeId
                            },
                            success: function(res) {
                                if (!res.success || !res.persons || !res.persons
                                    .length) {
                                    response([{
                                        label: 'No sales persons found for "' +
                                            request.term + '"',
                                        value: request.term,
                                        disabled: true
                                    }]);
                                    return;
                                }
                                response(res.persons.map(function(p) {
                                    const fullName = p.FullName || p
                                        .Contact_PersonName_FirstName ||
                                        '';
                                    return {
                                        label: (p.Code || '') +
                                            ' — ' +
                                            fullName,
                                        value: fullName || p.Code ||
                                            '',
                                        code: p.Code || '',
                                        email: p.Email || ''
                                    };
                                }));
                            },
                            error: function(xhr) {
                                console.error('Sales person lookup failed:', xhr
                                    .status, xhr
                                    .responseText);
                                response([{
                                    label: 'Error loading sales persons',
                                    value: '',
                                    disabled: true
                                }]);
                            }
                        });
                    },
                    select: function(event, ui) {
                        if (ui.item.disabled) {
                            event.preventDefault();
                            return false;
                        }
                        $('#salesPersonDisplay').val(ui.item.value);
                        $('#salesPersonCode').val(ui.item.code);
                        $('#salesPersonEmail').val(ui.item.email);
                        return false;
                    }
                }).autocomplete('instance')._renderItem = function(ul, item) {
                    return $('<li>')
                        .addClass(item.disabled ? 'ui-state-disabled' : '')
                        .append($('<div>').text(item.label))
                        .appendTo(ul);
                };
            }

            function initCompanyAutocomplete() {
                const $input = $('input[data-field="companyname"]');

                if ($input.hasClass('ui-autocomplete-input')) {
                    $input.autocomplete('destroy');
                }

                $input.autocomplete({
                    minLength: 2,
                    delay: 350,
                    appendTo: $input.closest('.card-body'),
                    source: function(request, response) {
                        $.ajax({
                            url: '{{ route('quotation.client-list') }}',
                            type: 'GET',
                            data: {
                                filter: request.term
                            },
                            success: function(res) {
                                if (!res.success || !res.clients || !res.clients.length) {
                                    response([{
                                        label: 'No clients found for "' +
                                            request.term + '"',
                                        value: request.term,
                                        disabled: true
                                    }]);
                                    return;
                                }
                                response(res.clients.map(function(c) {
                                    return {
                                        label: (c.ClientCode || '') + ' — ' + (c
                                            .ClientName || ''),
                                        value: c.ClientName || '',
                                        clientId: c.ClientID || '',
                                        clientCode: c.ClientCode || ''
                                    };
                                }));
                            },
                            error: function(xhr) {
                                console.error('Client list lookup failed:', xhr);
                                response([{
                                    label: 'Error loading clients',
                                    value: '',
                                    disabled: true
                                }]);
                            }
                        });
                    },
                    focus: function(event, ui) {
                        event.preventDefault();
                        return false;
                    },
                    select: function(event, ui) {
                        console.log('=== AUTOCOMPLETE SELECTED ===');
                        console.log('Client Code (e.g., KEAA):', ui.item.clientCode);
                        console.log('Client ID (GUID):', ui.item.clientId);

                        if (ui.item.disabled) {
                            event.preventDefault();
                            return false;
                        }

                        // Set the company name and store both ID and Code
                        $input.val(ui.item.value);
                        $('#selectedClientId').val(ui.item.clientId);
                        $('#selectedClientCode').val(ui.item.clientCode);

                        // Fetch client details using the CLIENT CODE (e.g., "KEAA")
                        if (ui.item.clientCode) {
                            console.log('Fetching client details with customer_code:', ui.item
                                .clientCode);

                            $.ajax({
                                url: '{{ route('quotation.client-details') }}',
                                type: 'GET',
                                data: {
                                    customer_code: ui.item.clientCode
                                },
                                success: function(res) {
                                    console.log('Client details response:', res);

                                    if (!res.success) {
                                        console.error('Client details failed:', res
                                            .message);
                                        showNotification(res.message ||
                                            'Failed to load client details', 'error');
                                        return;
                                    }

                                    // Populate Address fields
                                    $('input[data-field="pri_partner_steet"]').val(res
                                        .address1 || '');
                                    $('#customerAddress2Input').val(res.address2 || '');
                                    $('#customerCityInput').val(res.city || '');
                                    $('#customerStateInput').val(res.state || '');
                                    $('#customerZipInput').val(res.zip || '');

                                    // Populate Contact fields
                                    $('#customerContactInput').val(res.contact_person ||
                                        '');
                                    $('#customerPhoneInput').val(res.phone || '');
                                    $('#customerEmailInput').val(res.email || '');

                                    // Populate Country
                                    if (res.country_name) {
                                        $('#customerCountryDisplay').val(res.country_name);
                                    }
                                    if (res.country_id) {
                                        const $cSel = $('#customerCountrySelect');
                                        if ($cSel.find(`option[value="${res.country_id}"]`)
                                            .length === 0) {
                                            $cSel.append(
                                                `<option value="${res.country_id}" 
                                    data-name="${res.country_name}" 
                                    data-code="${res.country_code}">${res.country_name}</option>`
                                            );
                                        }
                                        $cSel.val(res.country_id);
                                    }

                                    // Populate Industry
                                    if (res.industry_name) {
                                        $('#customerIndustryDisplay').val(res
                                            .industry_name);
                                        const $iSel = $('#customerIndustrySelect');
                                        if ($iSel.find(
                                                `option[value="${res.industry_name}"]`)
                                            .length === 0) {
                                            $iSel.append(
                                                `<option value="${res.industry_name}">${res.industry_name}</option>`
                                            );
                                        }
                                        $iSel.val(res.industry_name);
                                    }

                                    // Populate Agent Type
                                    if (res.agent_type_name) {
                                        const $aSel = $('#agentTypeSelect');
                                        if ($aSel.find(
                                                `option[value="${res.agent_type_name}"]`)
                                            .length === 0) {
                                            $aSel.append(
                                                `<option value="${res.agent_type_name}">${res.agent_type_name}</option>`
                                            );
                                        }
                                        $aSel.val(res.agent_type_name);
                                    }

                                    $('#clientIdDisplay').val(res.client_code || '');

                                    // If in New Agent mode, keep fields editable
                                    const customerType = $('#customerTypeSelect').val();
                                    if (customerType === 'New Agent') {
                                        $('#customerDetailsCard .card-body')
                                            .find('input:not([type="hidden"])')
                                            .prop('readonly', false)
                                            .css({
                                                'background-color': '#fff9e6',
                                                'border-color': '#ffc107'
                                            });

                                        $('input[data-field="pri_partner_steet"]')
                                            .prop('readonly', false)
                                            .css({
                                                'background-color': '#fff9e6',
                                                'border-color': '#ffc107'
                                            });
                                    }

                                    showNotification('Client details loaded successfully',
                                        'success');
                                },
                                error: function(xhr) {
                                    console.error('getClientDetails failed:', xhr);
                                    showNotification(xhr.responseJSON?.message ||
                                        'Failed to load client details', 'error');
                                }
                            });
                        }

                        event.preventDefault();
                        return false;
                    },
                    open: function() {
                        const $widget = $(this).autocomplete('widget');
                        $widget.css('z-index', 9999);

                        $widget.off('mousedown.autocomplete-fix').on('mousedown.autocomplete-fix',
                            function(e) {
                                e.stopPropagation();
                                return true;
                            });
                    }
                });
            }

                        // ── Won process — 2-step modal (Client Creation → Mark as Won) ─────────────
            function runWonProcess(bookingNo, reasonRemark) {
            
                const WON_STEPS = [
                    { step: 1, label: 'Client Creation' },
                    { step: 2, label: 'Mark as Won' },
                ];
            
                function renderWonSteps(completedSteps, activeStep) {
                    var $list = $('#wonStepList').empty();
                    WON_STEPS.forEach(function(def) {
                        var done = completedSteps.find(function(s) { return s.step === def.step; });
                        var iconClass, badgeClass, statusText, remarkHtml = '';
                        var isActive = (activeStep !== undefined && def.step === activeStep);
            
                        if (isActive) {
                            iconClass  = 'fas fa-spinner fa-spin text-primary';
                            badgeClass = 'badge-primary';
                            statusText = 'Processing...';
                        } else if (!done) {
                            iconClass  = 'fas fa-circle text-secondary';
                            badgeClass = 'badge-secondary';
                            statusText = 'Pending';
                        } else if (done.status === 'Success') {
                            iconClass  = 'fas fa-check-circle text-success';
                            badgeClass = 'badge-success';
                            statusText = 'Success';
                            if (done.remark)
                                remarkHtml = '<small class="text-muted d-block mt-1">' + done.remark + '</small>';
                        } else {
                            iconClass  = 'fas fa-times-circle text-danger';
                            badgeClass = 'badge-danger';
                            statusText = 'Failed';
                            if (done.remark)
                                remarkHtml = '<small class="text-danger d-block mt-1">' + done.remark + '</small>';
                        }
            
                        $list.append(
                            '<div class="list-group-item py-2">'
                            + '<div class="d-flex align-items-center justify-content-between">'
                            + '<div><i class="' + iconClass + ' mr-2"></i>'
                            + '<strong>Step ' + def.step + ':</strong> ' + def.label
                            + remarkHtml + '</div>'
                            + '<span class="badge ' + badgeClass + '">' + statusText + '</span>'
                            + '</div></div>'
                        );
                    });
                }
            
                $('#wonModal').modal('hide');
                $('#wonProgressError, #wonProgressSuccess').addClass('d-none');
                $('#wonProcessCloseBtn').addClass('d-none');
                renderWonSteps([], 1);
                $('#wonProcessModal').modal('show');
            
                $.ajax({
                    url:  '{{ route("quotation.process-won") }}',
                    type: 'POST',
                    data: {
                        _token:        '{{ csrf_token() }}',
                        booking_no:    bookingNo,
                        quotation_id:  quotationId,   
                        reason_remark: reasonRemark || '',
                    },
                    success: function(res) {
                        renderWonSteps(res.steps || []);
                        if (res.success) {
                            $('#wonProgressSuccess').removeClass('d-none');
                            $('#wonProcessCloseBtn').removeClass('d-none');
                            setTimeout(function() {
                                window.location.href = '{{ route("quotation.index") }}';
                            }, 1500);
                    } else {
                     var failedStep = (res.steps || []).find(function(s) { return s.status === 'Failed'; });
var detailMsg  = (failedStep && failedStep.remark) ? failedStep.remark : (res.message || 'Unknown error');
$('#wonProgressError')
    .html('<strong>' + (res.failed_label || 'Step') + ' failed:</strong> ' + detailMsg)
    .removeClass('d-none');
                        $('#wonProcessCloseBtn').removeClass('d-none');
                    }
                    },
                    error: function(xhr) {
                        var msg = (xhr.responseJSON && xhr.responseJSON.message)
                            ? xhr.responseJSON.message
                            : 'An unexpected error occurred.';
                        $('#wonProgressError').html(msg).removeClass('d-none');
                        $('#wonProcessCloseBtn').removeClass('d-none');
                    }
                });
            }

        }); // end document.ready


        function updateStatus(status, reasonCode, reasonRemark, bookingNo) {
            $.ajax({
                url: '{{ route('quotation.status.update') }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    booking_no: bookingNo,
                    status: status,
                    reason_code: reasonCode,
                    reason_remark: reasonRemark
                },
                success: function(response) {
                    if (response.success) {
                        showNotification(response.message, 'success');
                        $('#statusModal').modal('hide');
                        $('#wonModal').modal('hide');

                        setTimeout(function() {
                            window.location.href = '{{ route('quotation.index') }}';
                        }, 1500);
                    } else {
                        showNotification(response.message, 'error');
                    }
                },
                error: function(xhr) {
                    showNotification(xhr.responseJSON?.message || 'Failed to update status',
                        'error');
                }
            });
        }


        function showNotification(message, type) {
            const alertClass = type === 'success' ? 'alert-success' :
                type === 'error' ? 'alert-danger' :
                type === 'warning' ? 'alert-warning' : 'alert-info';

            $('body').append(`
                    <div class="alert ${alertClass} alert-dismissible fade show notification-toast" role="alert"
                        style="position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
                        <strong>${message}</strong>
                        <button type="button" class="close" data-dismiss="alert">
                            <span>&times;</span>
                        </button>
                    </div>
                `);

            setTimeout(function() {
                $('.notification-toast').fadeOut(function() {
                    $(this).remove();
                });
            }, 3000);
        }

    // ============================================
    // COPY CHARGES
    // ============================================
    (function () {
    
        const CC_SEARCH_URL  = '{{ route("quotation.copy-charges.search") }}';
        const CC_CHARGES_URL = '{{ route("quotation.copy-charges.charges") }}';
        const CC_SAVE_URL    = '{{ route("quotation.charge.save") }}';
    
        let ccSearchResults  = [];
        let ccCurrentPage    = 1;
        let ccPageSize       = 10;
        let ccSelectedJobRow = null;
    
        const ccTableState = {
            ccRevenueBody: { rows: [], page: 1, pageSize: 10 },
            ccCostBody:    { rows: [], page: 1, pageSize: 10 },
        };
    
        // ── Open modal ────────────────────────────────────────────────────────────
        $('#openCopyChargesBtn').on('click', function () {
            resetCopyChargesModal();
            $('#copyChargesModal').modal('show');
        });
    
        // ── Open job search ───────────────────────────────────────────────────────
        $('#ccJobNoSearchBtn').on('click', function () {
            ccSelectedJobRow = null;
            $('#ccSearchOkBtn').prop('disabled', true);
            resetSearchModal();
            $('#ccSearchModal').css('z-index', 1060).modal('show');
        });
    
        // ── Search ────────────────────────────────────────────────────────────────
        $('#ccSearchGoBtn').on('click', function () {
            ccCurrentPage = 1;
            runSearch();
        });
    
        $('#ccSearchValue').on('keypress', function (e) {
            if (e.which === 13) $('#ccSearchGoBtn').trigger('click');
        });
    
        function runSearch() {
            const filterField = $('#ccSearchField').val();
            const filterValue = $('#ccSearchValue').val().trim();
    
            $.ajax({
                url: CC_SEARCH_URL,
                type: 'GET',
                data: { office_code: officeCode, filter_field: filterField, filter_value: filterValue },
                beforeSend: function () {
                    $('#ccSearchResultsBody').html(
                        '<tr><td colspan="5" class="cc-empty-msg"><i class="fas fa-spinner fa-spin"></i> Searching...</td></tr>'
                    );
                },
                success: function (response) {
                    ccSearchResults = response.success ? (response.data || []) : [];
                    ccCurrentPage   = 1;
                    renderSearchPage();
                },
                error: function () {
                    ccSearchResults = [];
                    renderSearchPage();
                    showNotification('Search failed', 'error');
                }
            });
        }
    
        // ── Search pagination ─────────────────────────────────────────────────────
        $(document).on('click', '.cc-page-btn', function () {
            const action     = $(this).data('action');
            const totalPages = Math.max(1, Math.ceil(ccSearchResults.length / ccPageSize));
            if (action === 'first') ccCurrentPage = 1;
            else if (action === 'prev')  ccCurrentPage = Math.max(1, ccCurrentPage - 1);
            else if (action === 'next')  ccCurrentPage = Math.min(totalPages, ccCurrentPage + 1);
            else if (action === 'last')  ccCurrentPage = totalPages;
            renderSearchPage();
        });
    
        $('#ccPageInput').on('change', function () {
            const totalPages = Math.max(1, Math.ceil(ccSearchResults.length / ccPageSize));
            ccCurrentPage = Math.min(totalPages, Math.max(1, parseInt($(this).val()) || 1));
            renderSearchPage();
        });
    
        $('#ccPageSize').on('change', function () {
            ccPageSize    = parseInt($(this).val()) || 10;
            ccCurrentPage = 1;
            renderSearchPage();
        });
    
        function renderSearchPage() {
            const $tbody     = $('#ccSearchResultsBody');
            const total      = ccSearchResults.length;
            const totalPages = Math.max(1, Math.ceil(total / ccPageSize));
            ccCurrentPage    = Math.min(ccCurrentPage, totalPages);
            const start      = (ccCurrentPage - 1) * ccPageSize;
            const end        = Math.min(start + ccPageSize, total);
            const page       = ccSearchResults.slice(start, end);
    
            $('#ccPageInput').val(ccCurrentPage);
            $('#ccTotalPages').text(totalPages);
            $('#ccViewRange').text(total === 0 ? 'View 0 - 0 of 0' : `View ${start + 1} - ${end} of ${total}`);
    
            $tbody.empty();
    
            if (!page.length) {
                $tbody.html('<tr><td colspan="5" class="cc-empty-msg">No results found</td></tr>');
                return;
            }
    
            page.forEach(function (row, i) {
                const globalIdx = start + i;
                $tbody.append(`
                    <tr style="cursor:pointer;" data-idx="${globalIdx}">
                        <td class="text-center">
                            <input type="radio" name="ccJobSearchRadio" class="cc-search-radio" value="${globalIdx}">
                        </td>
                        <td style="font-size:0.8rem;">${row.QuoteNo}</td>
                        <td style="font-size:0.8rem;">${row.CompanyName}</td>
                        <td style="font-size:0.8rem;">${row.Origin}</td>
                        <td style="font-size:0.8rem;">${row.Destination}</td>
                    </tr>
                `);
            });
    
            $tbody.find('tr').on('click', function () {
                $(this).find('input[type="radio"]').prop('checked', true).trigger('change');
            });
        }
    
        // ── Row selection ─────────────────────────────────────────────────────────
        $(document).on('change', '.cc-search-radio', function () {
            const idx        = parseInt($(this).val(), 10);
            ccSelectedJobRow = ccSearchResults[idx] || null;
            $('#ccSearchOkBtn').prop('disabled', !ccSelectedJobRow);
        });
    
        // ── Search Ok ─────────────────────────────────────────────────────────────
        $('#ccSearchOkBtn').on('click', function () {
            if (!ccSelectedJobRow) return;
            $('#ccJobNo').val(ccSelectedJobRow.QuoteNo);
            $('#ccSearchModal').modal('hide');
            loadJobCharges(ccSelectedJobRow);
        });
    
        // ── Search cancel / close ─────────────────────────────────────────────────
        $('#ccSearchCancelBtn, #ccSearchModalClose').on('click', function (e) {
            e.preventDefault();
            $('#ccSearchModal').modal('hide');
        });
    
        // ── Load charges for selected quote ───────────────────────────────────────
        function loadJobCharges(jobRow) {
            if (!jobRow.id) {
                showNotification('Quotation ID not found for this quote', 'error');
                return;
            }
    
            $('#ccJobDetails, #ccJobDetailsCost').hide();
            const emptyMsg = '<tr><td colspan="6" class="cc-empty-msg"><i class="fas fa-spinner fa-spin"></i> Loading...</td></tr>';
            $('#ccRevenueBody, #ccCostBody').html(emptyMsg);
    
            $.ajax({
                url: CC_CHARGES_URL,
                type: 'GET',
                data: { quotation_id: jobRow.id },
                success: function (response) {
                    if (!response.success) {
                        showNotification(response.message || 'Failed to load charges', 'error');
                        return;
                    }
    
                    var info = response.job_info || {};
                    $('#ccClient').text(info.client || '');
                    $('#ccShipper').text(info.shipper || '');
                    $('#ccConsignee').text(info.consignee || '');
                    $('#ccPortOfLoading').text(info.port_of_loading || '');
                    $('#ccPortOfDischarge').text(info.port_of_discharge || '');
                    $('#ccClientRow').toggle(!!info.client);
                    $('#ccShipperRow').toggle(!!info.shipper);
                    $('#ccConsigneeRow').toggle(!!info.consignee);
                    $('#ccPolRow').toggle(!!info.port_of_loading);
                    $('#ccPodRow').toggle(!!info.port_of_discharge);
    
                    ccTableState.ccRevenueBody.rows     = response.revenue || [];
                    ccTableState.ccRevenueBody.page     = 1;
                    ccTableState.ccRevenueBody.pageSize = 10;
    
                    ccTableState.ccCostBody.rows     = response.cost || [];
                    ccTableState.ccCostBody.page     = 1;
                    ccTableState.ccCostBody.pageSize = 10;
    
                    const rev  = response.revenue.length;
                    const cost = response.cost.length;
                    $('#ccRevenueSummary').text(`[ Total Record(s): ${rev} ]`);
                    $('#ccCostSummary').text(`[ Total Record(s): ${cost} ]`);
    
                    renderTablePage('ccRevenueBody');
                    renderTablePage('ccCostBody');
    
                    $('#ccJobDetails').show();
                    $('#ccJobDetailsCost').show();
                    updateSaveBtn();
                },
                error: function () {
                    showNotification('Failed to load charges', 'error');
                }
            });
        }
    
        // ── Per-table pagination ──────────────────────────────────────────────────
        $(document).on('click', '.cc-tbl-page-btn', function () {
            const tbodyId    = $(this).data('tbody');
            const action     = $(this).data('action');
            const s          = ccTableState[tbodyId];
            if (!s) return;
            const totalPages = Math.max(1, Math.ceil(s.rows.length / s.pageSize));
            if (action === 'first') s.page = 1;
            else if (action === 'prev') s.page = Math.max(1, s.page - 1);
            else if (action === 'next') s.page = Math.min(totalPages, s.page + 1);
            else if (action === 'last') s.page = totalPages;
            renderTablePage(tbodyId);
        });
    
        $(document).on('change', '.cc-tbl-page-input', function () {
            const tbodyId    = $(this).data('tbody');
            const s          = ccTableState[tbodyId];
            if (!s) return;
            const totalPages = Math.max(1, Math.ceil(s.rows.length / s.pageSize));
            s.page = Math.min(totalPages, Math.max(1, parseInt($(this).val()) || 1));
            renderTablePage(tbodyId);
        });
    
        $(document).on('change', '.cc-tbl-page-size', function () {
            const tbodyId = $(this).data('tbody');
            const s       = ccTableState[tbodyId];
            if (!s) return;
            s.pageSize = parseInt($(this).val()) || 10;
            s.page     = 1;
            renderTablePage(tbodyId);
        });
    
        // ── View All / Selected toggles ───────────────────────────────────────────
        $(document).on('click', '.cc-view-toggle', function (e) {
            e.preventDefault();
            const tbodyId = $(this).data('tbody');
            const mode    = $(this).data('mode');
            const $tbody  = $('#' + tbodyId);
    
            if (mode === 'selected') {
                $tbody.find('tr').each(function () {
                    $(this).toggle($(this).find('input[type="checkbox"]').is(':checked'));
                });
            } else {
                $tbody.find('tr').show();
            }
        });
    
        // ── Select all ────────────────────────────────────────────────────────────
        $('#ccRevenueSelectAll').on('change', function () {
            $('#ccRevenueBody input[type="checkbox"]').prop('checked', $(this).is(':checked'));
            updateSaveBtn();
        });
        $('#ccCostSelectAll').on('change', function () {
            $('#ccCostBody input[type="checkbox"]').prop('checked', $(this).is(':checked'));
            updateSaveBtn();
        });
        $(document).on('change', '#ccRevenueBody input[type="checkbox"], #ccCostBody input[type="checkbox"]',
            function () { updateSaveBtn(); }
        );
    
        // ── Build charge row (6 columns) ─────────────────────────────────────────
        function buildChargeRow(r, i, type) {
            return `
                <tr data-cc-row="${i}">
                    <td class="text-center">
                        <input type="checkbox" class="cc-charge-check" data-type="${type}" data-index="${i}">
                    </td>
                    <td style="font-size:0.8rem;">${r.chargeCode || ''}</td>
                    <td style="font-size:0.8rem; max-width:160px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;"
                        title="${r.chargeDesp || ''}">${r.chargeDesp || ''}</td>
                    <td class="text-center" style="font-size:0.8rem;">${r.quantity || 0}</td>
                    <td style="font-size:0.8rem;">${r.basedOn || ''}</td>
                    <td style="font-size:0.8rem;">${r.uomCode || ''}</td>
                </tr>
            `;
        }
    
        function renderTablePage(tbodyId) {
            const s          = ccTableState[tbodyId];
            const $tbody     = $('#' + tbodyId);
            const type       = tbodyId === 'ccRevenueBody' ? 'revenue' : 'cost';
            const total      = s.rows.length;
            const totalPages = Math.max(1, Math.ceil(total / s.pageSize));
            s.page           = Math.min(s.page, totalPages);
            const start      = (s.page - 1) * s.pageSize;
            const end        = Math.min(start + s.pageSize, total);
            const page       = s.rows.slice(start, end);
    
            $tbody.empty();
    
            if (!page.length) {
                const msg = total === 0 ? 'No charges on this quote' : 'No charges on this page';
                $tbody.html(`<tr><td colspan="6" class="cc-empty-msg">${msg}</td></tr>`);
            } else {
                page.forEach(function (r, i) {
                    $tbody.append(buildChargeRow(r, start + i, type));
                });
            }
    
            updateTablePagination(tbodyId);
        }
    
        function updateTablePagination(tbodyId) {
            const s          = ccTableState[tbodyId];
            const total      = s.rows.length;
            const totalPages = Math.max(1, Math.ceil(total / s.pageSize));
            const start      = (s.page - 1) * s.pageSize;
            const end        = Math.min(start + s.pageSize, total);
    
            $(`.cc-tbl-page-input[data-tbody="${tbodyId}"]`).val(s.page);
            $(`.cc-tbl-total-pages[data-tbody="${tbodyId}"]`).text(totalPages);
            $(`.cc-tbl-view-range[data-tbody="${tbodyId}"]`).text(
                total === 0 ? 'View 0 - 0 of 0' : `View ${start + 1} - ${end} of ${total}`
            );
        }
    
        function updateSaveBtn() {
            const any = $('#ccRevenueBody .cc-charge-check:checked, #ccCostBody .cc-charge-check:checked').length > 0;
            $('#ccSaveBtn').prop('disabled', !any);
        }
    
        // ── Save selected charges → reload page ───────────────────────────────────
        $('#ccSaveBtn').on('click', function () {
            const charges = [];
    
            $('#ccRevenueBody .cc-charge-check:checked').each(function () {
                const row = ccTableState.ccRevenueBody.rows[$(this).data('index')];
                if (row) charges.push(row);
            });
            $('#ccCostBody .cc-charge-check:checked').each(function () {
                const row = ccTableState.ccCostBody.rows[$(this).data('index')];
                if (row) charges.push(row);
            });
    
            if (!charges.length) return;
    
            const $btn = $(this).prop('disabled', true).text('Saving...');
            let chain  = Promise.resolve();
            let failed = false;
    
            charges.forEach(function (charge) {
                chain = chain.then(function () {
                    if (failed) return;
                    return $.ajax({
                        url: CC_SAVE_URL,
                        type: 'POST',
                        data: {
                            _token:          '{{ csrf_token() }}',
                            action:          'Add',
                            quotation_id:    quotationId,
                            booking_no:      bookingNo,
                            office_code:     officeCode,
                            revenue_or_cost: charge.isRevenue ? 'Revenue' : 'Cost',
                            charge_code:     charge.chargeCode,
                            quantity:        charge.quantity,
                            rate:            charge.rate,
                            amt_in_chg_curr: charge.amount,
                            amt_in_base_curr:charge.baseAmount,
                            exchange_rate:   charge.exchangeRate,
                            chg_curr:        charge.currency,
                            based_on_code:   charge.basedOnCode,
                            based_on_uom:    charge.uomCode,
                            charge_remark:   '',
                            to_be_added:     0,
                            increase_by:     0,
                            increase_type:   'Percentage',
                            source_type:     'manual',
                            quote_charge_id: '',
                            party_code:      '',
                        }
                    }).fail(function (xhr) {
                        failed = true;
                        showNotification(
                            (xhr.responseJSON?.message || 'Failed to copy') + ': ' + charge.chargeCode,
                            'error'
                        );
                    });
                });
            });
    
            chain.then(function () {
                if (!failed) {
                    $('#copyChargesModal').modal('hide');
                    window.location.reload();
                }
            }).always(function () {
                $btn.prop('disabled', false).text('Save');
            });
        });
    
        // ── Copy Charges modal close ──────────────────────────────────────────────
        $('#copyChargesModal').on('hidden.bs.modal', resetCopyChargesModal);
        $('#ccCancelBtn').on('click', function (e) {
            e.preventDefault();
            $('#copyChargesModal').modal('hide');
        });
    
        // ── Helpers ───────────────────────────────────────────────────────────────
        function resetCopyChargesModal() {
            $('#ccJobNo').val('');
            $('#ccJobDetails, #ccJobDetailsCost').hide();
            $('#ccClientRow, #ccShipperRow, #ccConsigneeRow, #ccPolRow, #ccPodRow').hide();
            $('#ccClient, #ccShipper, #ccConsignee, #ccPortOfLoading, #ccPortOfDischarge').text('');
            const empty = '<tr><td colspan="6" class="cc-empty-msg">Enter a Job No to load charges</td></tr>';
            $('#ccRevenueBody, #ccCostBody').html(empty);
            $('#ccRevenueSummary').text('[ Total Record(s): 0 ]');
            $('#ccCostSummary').text('[ Total Record(s): 0 ]');
            $('#ccRevenueSelectAll, #ccCostSelectAll').prop('checked', false);
            $('#ccSaveBtn').prop('disabled', true);
            ccTableState.ccRevenueBody = { rows: [], page: 1, pageSize: 10 };
            ccTableState.ccCostBody    = { rows: [], page: 1, pageSize: 10 };
            updateTablePagination('ccRevenueBody');
            updateTablePagination('ccCostBody');
            ccSelectedJobRow = null;
        }
    
        function resetSearchModal() {
            $('#ccSearchField').val('');
            $('#ccSearchValue').val('');
            ccSearchResults = [];
            ccCurrentPage   = 1;
            ccPageSize      = parseInt($('#ccPageSize').val()) || 10;
            renderSearchPage();
        }
    
    })();
    </script>
@stop
