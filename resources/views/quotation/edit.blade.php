@extends('adminlte::page')

@section('title', 'Edit Booking Quotation')


@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1><i class="fas fa-file-invoice"></i> Inquiry No - {{ $quotation->bookingno ?? '' }}</h1>
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
                            <div class="form-group row mb-0">
                                <div class="col-sm-7 offset-sm-5">
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
                        <div class="form-group row">
                            <label class="col-sm-5 col-form-label">Origin Country</label>
                            <div class="col-sm-7">
                                <input type="text" class="form-control form-control-sm"
                                    value="{{ $quotation->origincountry ?? '' }}" readonly>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-5 col-form-label">Destination Country</label>
                            <div class="col-sm-7">
                                <input type="text" class="form-control form-control-sm"
                                    value="{{ $quotation->destinationcountry ?? '' }}" readonly>
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
                                <input type="text" class="form-control form-control-sm"
                                    value="{{ $quotation->ServiceClass ?? '' }}" readonly>
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
                            <label class="col-sm-5 col-form-label">Scope Of Volume</label>
                            <div class="col-sm-7">
                                <input type="text" class="form-control form-control-sm"
                                    value="{{ $quotation->scopevolume ?? '' }}" readonly>
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
                            <button type="button" class="btn btn-email-quote btn-sm mr-2" id="generateQuoteBtn" disabled
                                title="Please apply Terms & Conditions first">
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
                                            value="{{ $quotation->companyname ?? '' }}" readonly>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-4 col-form-label">Address 1</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control form-control-sm"
                                            value="{{ $quotation->pri_partner_steet ?? '' }}" readonly>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-4 col-form-label">City</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control form-control-sm"
                                            value="{{ $quotation->pri_partner_city ?? '' }}" readonly>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-4 col-form-label">State</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control form-control-sm"
                                            value="{{ $quotation->pri_partner_statename ?? '' }}" readonly>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-4 col-form-label">Contact Person</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control form-control-sm"
                                            value="{{ $quotation->contact_name ?? '' }}" readonly>
                                    </div>
                                </div>
                                <div class="form-group row mb-0">
                                    <label class="col-sm-4 col-form-label">Phone No</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control form-control-sm"
                                            value="{{ $quotation->pri_partner_phone ?? '' }}" readonly>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group row">
                                    <label class="col-sm-4 col-form-label">Client ID</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control form-control-sm"
                                            value="{{ $quotation->clientid ?? '' }}" readonly>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-4 col-form-label">Address 2</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control form-control-sm"
                                            value="{{ $quotation->pri_partner_steet2 ?? '' }}" readonly>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-4 col-form-label">Zip</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control form-control-sm"
                                            value="{{ $quotation->pri_partner_zip ?? '' }}" readonly>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-4 col-form-label">Country</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control form-control-sm"
                                            value="{{ $quotation->pri_partner_countryname ?? '' }}" readonly>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-4 col-form-label">Email ID</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control form-control-sm"
                                            value="{{ $quotation->pri_partner_email ?? '' }}" readonly>
                                    </div>
                                </div>
                                <div class="form-group row mb-0">
                                    <label class="col-sm-4 col-form-label">Industry</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control form-control-sm"
                                            value="{{ $quotation->pri_partner_industrytype ?? '' }}" readonly>
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
                                        <th class="col-extra">Based On UOM</th>
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
                                        <th class="col-extra">Based On UOM</th>
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
                                        <label for="revenue_bill_to_party" class="form-label">Bill to Party</label>
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
                                                Amount in Base Currency <span class="text-muted">(KES)</span></label>
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
                    <div class="modal-body">
                        <form id="addCostChargesForm">
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
                                            class="office-base-currency">—</strong></p>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="cost_charge_code" class="form-label">
                                            <span class="text-danger">*</span> Charge Code</label>
                                        <input type="text" class="form-control" id="cost_charge_code"
                                            name="cost_charge_code" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="cost_bill_to_party" class="form-label">Bill to Party</label>
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
                                                Amount in Base Currency <span class="text-muted">(KES)</span></label>
                                            <input type="number" class="form-control" id="cost_base_amount"
                                                name="cost_base_amount" step="0.01" value="0.00" readonly>
                                        </div>
                                    </div>
                                </div>
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
                                        <input type="text" class="form-control" id="cost_remark" name="cost_remark">
                                    </div>
                                    <div class="mb-3">
                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="checkbox" id="costIsReimbursed"
                                                name="cost_is_reimbursed">
                                            <label class="form-check-label fw-semibold" for="costIsReimbursed">
                                                Is Reimbursed <small class="text-muted">(Adds to Revenue)</small>
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="costIsAccrued"
                                                name="cost_is_accrued">
                                            <label class="form-check-label fw-semibold" for="costIsAccrued">
                                                Is Accrued <small class="text-muted">(Adds to Cost)</small>
                                            </label>
                                        </div>
                                        <div class="mb-3 mt-2" id="costSecondaryBillToPartyContainer"
                                            style="display:none;">
                                            <label for="costSecondaryBillToParty" class="form-label">
                                                <span class="text-danger">*</span> Secondary Bill To Party</label>
                                            <input type="text" class="form-control" id="costSecondaryBillToParty"
                                                placeholder="Select Secondary Party">
                                            <input type="hidden" id="costSecondaryBillToPartyCode">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
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
    </style>
@stop



@section('js')
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>

    <script>
        const isCopiedBooking = {{ request()->query('copied', 0) ? 'true' : 'false' }};
        const quotationId = '{{ $quotation->ID }}';

        const tcAlreadyApplied = {{ $quotation->QuotationTCTemplate ? 'true' : 'false' }};

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
            let officeCode = '';

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

            let incoTermsLoaded = false;
            let originalIncoTerm = '';

            $('#editShipmentDetailsBtn').on('click', function() {
                originalShipmentMode = $('#shipmentModeSelect').val();
                originalTransportMode = $('#transportModeSelect').val();
                originalIncoTerm = $('#incoTermDisplay').val();

                $('#equipmentTypeSelect, #shipmentModeSelect, #transportModeSelect').prop('disabled',
                    false);

                // Load InCo Terms once then swap display→select
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

                $(this).hide();
                $('#saveShipmentDetailsBtn, #cancelShipmentDetailsBtn').show();
            });
            $('#cancelShipmentDetailsBtn').on('click', function() {
                $('#shipmentModeSelect').val(originalShipmentMode);
                $('#transportModeSelect').val(originalTransportMode);
                $('#incoTermSelect').val(originalIncoTerm);

                $('#equipmentTypeSelect, #shipmentModeSelect, #transportModeSelect').prop('disabled', true);

                // Swap back select→display
                $('#incoTermSelect').addClass('d-none').prop('disabled', true);
                $('#incoTermDisplay').removeClass('d-none').val(originalIncoTerm);

                $(this).hide();
                $('#saveShipmentDetailsBtn').hide();
                $('#editShipmentDetailsBtn').show();
            });

            $('#saveShipmentDetailsBtn').on('click', function() {
                const shipmentMode = $('#shipmentModeSelect').val();
                const transportMode = $('#transportModeSelect').val();

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
                        booking_no: bookingNo,
                        shipment_mode: shipmentMode,
                        transport_mode: transportMode,
                        incoterm: $('#incoTermSelect').val(),
                    },
                    beforeSend: function() {
                        $('#saveShipmentDetailsBtn').prop('disabled', true)
                            .html('<i class="fas fa-spinner fa-spin mr-1"></i> Saving...');
                    },
                    success: function(response) {
                        if (response.success) {
                            showNotification('Shipment details saved successfully', 'success');

                            // Update snapshots so Cancel now reflects the saved values
                            originalShipmentMode = shipmentMode;
                            originalTransportMode = transportMode;

                            // Re-lock selects
                            $('#equipmentTypeSelect, #shipmentModeSelect, #transportModeSelect')
                                .prop('disabled', true);

                            // Swap back to display input with saved value
                            const savedInco = $('#incoTermSelect').val();
                            $('#incoTermSelect').addClass('d-none').prop('disabled', true);
                            $('#incoTermDisplay').removeClass('d-none').val(savedInco);
                            originalIncoTerm = savedInco;
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

            // ── T&C Gate ─────────────────────────────────────────────
            // Unblock Generate Quote immediately if T&C was previously applied
            if (tcAlreadyApplied) {
                $('#generateQuoteBtn')
                    .prop('disabled', false)
                    .removeAttr('title');
            }

            console.log('Booking Number:', bookingNo);
            console.log('Quotation Status:', quotationStatus);
            console.log('Team ID:', teamId);

            // ============================================
            // INITIALIZE OFFICE CODE ON PAGE LOAD
            // ============================================
            if (teamId) {
                getOfficeCode(teamId);
            }

            function getOfficeCode(teamId) {
                $.ajax({
                    url: '/quotation/office-code',
                    type: 'GET',
                    data: {
                        team_id: teamId
                    },
                    success: function(response) {
                        if (response.success && response.office_code) {
                            officeCode = response.office_code.trim();
                            console.log('Office Code loaded:', officeCode);
                            loadTermsTemplates();
                        }
                    },
                    error: function(xhr) {
                        console.error('Failed to load office code:', xhr);
                    }
                });
            }

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
                        booking_no: bookingNo
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
                if (!currentEditingCharge) {
                    // Add mode — initialize dropdowns fresh
                    $('#revenueTypeRevenue').prop('checked', true);
                    toggleRevenueFormFields('revenue');
                    loadBasedOnList('revenue');
                    populateCurrencyDropdown('revenue');
                } else {
                    // Edit mode — populateModal already owns the loading sequence via
                    // loadBasedOnListWithCallback → loadBasedOnUOMWithCallback.
                    // Only ensure currency is populated (it has its own populated guard).
                    populateCurrencyDropdown('revenue');
                }
                setTimeout(function() {
                    initializeChargeCodeAutocomplete('revenue');
                    initializeBillToPartyAutocomplete('revenue');
                }, 100);
            });

            $('#addCostModal').on('show.bs.modal', function() {
                $(this).find('.cost-markup-row').removeClass('active').css('display', 'none');
                if (!currentEditingCharge) {
                    // Add mode — initialize dropdowns fresh
                    $('#costTypeCost').prop('checked', true);
                    toggleCostFormFields('cost');
                    loadBasedOnList('cost');
                    populateCurrencyDropdown('cost');
                } else {
                    // Edit mode — populateModal already owns the loading sequence via
                    // loadBasedOnListWithCallback → loadBasedOnUOMWithCallback.
                    // Only ensure currency is populated (it has its own populated guard).
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

                if (!officeCode && bookingNo) {
                    $.ajax({
                        url: '/quotation/office-code',
                        type: 'GET',
                        data: {
                            team_id: teamId
                        },
                        async: false,
                        success: function(response) {
                            if (response.success) officeCode = response.office_code.trim();
                        }
                    });
                }

                if (!officeCode) {
                    console.error('Office code not available');
                    return;
                }

                $.ajax({
                    url: '/quotation/currencies',
                    type: 'GET',
                    data: {
                        office_code: officeCode
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
                        office_code: officeCode,
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

            // ============================================
            // LOAD UOM WHEN BASED ON CHANGES
            // ============================================
            $(document).on('change', 'select[name="revenue_based_on"], select[name="cost_based_on"]', function() {
                const $select = $(this);
                const prefix = $select.attr('name').split('_')[0];
                const basedOnCode = $select.val();
                loadBasedOnUOM(prefix, basedOnCode);
            });

            function loadBasedOnUOM(type, basedOnCode) {
                const prefix = type === 'revenue' ? 'revenue' : 'cost';
                const $uomSelect = $(`select[name="${prefix}_based_on_uom"]`);

                if (!basedOnCode) {
                    $uomSelect.empty()
                        .append('<option value="">Select Based On first</option>')
                        .prop('disabled', true);
                    return;
                }

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
                        $.ajax({
                            url: '/quotation/office-code',
                            type: 'GET',
                            data: {
                                team_id: teamId
                            },
                            success: function(res) {
                                if (res.success && res.office_code) {
                                    officeCode = res.office_code.trim();
                                    execSearch(officeCode);
                                } else {
                                    response([{
                                        label: 'Could not resolve office code',
                                        value: '',
                                        disabled: true
                                    }]);
                                }
                            },
                            error: function() {
                                response([{
                                    label: 'Error resolving office code',
                                    value: '',
                                    disabled: true
                                }]);
                            }
                        });
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
                const $isReimbursedContainer = $('#costIsReimbursed').closest('.form-check');

                if (type === 'costmarkup') {
                    $markupRow.addClass('active').css('display', 'flex');
                    $isReimbursedContainer.hide();
                    console.log('Showing cost markup row');
                } else if (type === 'revenue') {
                    $markupRow.removeClass('active').css('display', 'none');
                    $markupRow.find('input[type="number"]').val('0.00');
                    $markupRow.find('select').prop('selectedIndex', 0);
                    $isReimbursedContainer.hide();
                    console.log('Hiding cost markup row - Revenue selected');
                } else {
                    // type === 'cost'
                    $markupRow.removeClass('active').css('display', 'none');
                    $markupRow.find('input[type="number"]').val('0.00');
                    $markupRow.find('select').prop('selectedIndex', 0);
                    $isReimbursedContainer.show();
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

                // FIX: Derive revenueOrCost from the radio button, not the modal context.
                const revenueOrCost = chargeType === 'revenue' ? 'Revenue' :
                    chargeType === 'costmarkup' ? 'Cost with Markup' : 'Cost';

                console.log('Saving charge — modal:', type, '| radio:', chargeType, '| revenueOrCost:',
                    revenueOrCost);

                // FIX: to_be_added logic was inverted in the original code.
                // Revenue modal has no checkboxes, so it is always 0 for revenue.
                // For cost, check both IsReimbursed and IsAccrued.
                let toBeAdded = 0;
                if (type === 'cost') {
                    toBeAdded = ($modal.find('#costIsReimbursed').is(':checked') ||
                        $modal.find('#costIsAccrued').is(':checked')) ? 1 : 0;
                }

                const chargeData = {
                    _token: '{{ csrf_token() }}',
                    action: currentEditingCharge ? 'Edit' : 'Add',
                    booking_no: bookingNo,
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
                        'KES',
                    based_on_code: $modal.find(`select[name="${prefix}_based_on"]`).val() || '',
                    based_on_uom: $modal.find(`select[name="${prefix}_based_on_uom"]`).val() || '',
                    charge_remark: $modal.find(`input[name="${prefix}_remark"]`).val() || '',
                    to_be_added: toBeAdded,
                    increase_by: 0,
                    increase_type: 'Percentage',
                    source_type: 'manual'
                };

                if (chargeType === 'costmarkup') {
                    chargeData.increase_by = parseFloat($modal.find(`input[name="${prefix}_increase_by"]`).val()) ||
                        0;
                    chargeData.increase_type = $modal.find(`select[name="${prefix}_increase_type"]`).val() ||
                        'Percentage';
                }

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
                        if (response.success) {
                            showNotification(response.message, 'success');
                            loadCharges();
                            if (addMore) {
                                if (type === 'revenue') {
                                    clearRevenueModalForm();
                                } else {
                                    clearCostModalForm();
                                }
                            } else {
                                $modal.modal('hide');
                            }
                            currentEditingCharge = null;
                        } else {
                            showNotification(response.message, 'error');
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
            $(document).on('click', '.edit-revenue-charge', function() {
                const index = $(this).data('index');
                const charge = revenueCharges[index];
                currentEditingCharge = charge.id;
                currentEditingType = 'revenue';
                populateModal('revenue', charge);
                $('#addRevenueModal').modal('show');
            });

            $(document).on('click', '.edit-cost-charge', function() {
                const index = $(this).data('index');
                const charge = costCharges[index];
                currentEditingCharge = charge.id;
                currentEditingType = 'cost';
                populateModal('cost', charge);
                $('#addCostModal').modal('show');
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

                // ── Based On → UOM — sequential async chain ──
                loadBasedOnListWithCallback(type, function() {
                    if (charge.basedOnCode) {
                        $modal.find(`select[name="${prefix}_based_on"]`).val(charge.basedOnCode);

                        loadBasedOnUOMWithCallback(type, charge.basedOnCode, function() {
                            if (charge.basedOnUOM) {
                                $modal.find(`select[name="${prefix}_based_on_uom"]`).val(charge
                                    .basedOnUOM);
                            }
                        });
                    }
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
                                <td class="col-extra">${charge.basedOnUOM || ''}</td>
                                <td class="col-extra">${charge.chgCurr || 'KES'}</td>
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

                $('#totalRevenueAmount').text('KES ' + totalAmount.toFixed(2));
                $('#totalRevenueBaseAmount').text('KES ' + totalBaseAmount.toFixed(2));
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
                                <td class="col-extra">${charge.basedOnUOM || ''}</td>
                                <td class="col-extra">${charge.chgCurr || 'KES'}</td>
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

                $('#totalCostAmount').text('KES ' + totalAmount.toFixed(2));
                $('#totalCostBaseAmount').text('KES ' + totalBaseAmount.toFixed(2));
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

                $('#quotedRevenue').val('KES ' + totalRevenue.toFixed(2));
                $('#quotedMargin').val('KES ' + margin.toFixed(2) + ' (' + marginPercentage.toFixed(2) + '%)');
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
                updateStatus('Won', '', remark, bookingNo);
            });

            $('#wonModal').on('hidden.bs.modal', function() {
                $('#wonRemarkInput').val('');
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
                const $btn = $(this);
                let generationSucceeded = false;

                $btn.prop('disabled', true)
                    .html('<i class="fas fa-spinner fa-spin mr-1"></i> Generating...');

                $.ajax({
                    url: GENERATE_PDF_URL,
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            generationSucceeded = true;
                            showNotification('Quote generated — ' + response.label, 'success');
                            loadVersions();

                            if ($('#termsDropdownWrapper').is(':visible')) {
                                // Multi-template: reset dropdown and lock Generate until
                                // user selects a new template (auto-apply fires on change)
                                $('#termsTemplateSelect').val('');
                                $('#termsDescriptionPreview').val('');
                                $('#termsDescriptionWrapper').hide();
                                $('#applyTermsBtn').prop('disabled', true);
                                $('#termsReSelectMsg').show();
                            } else {
                                // Single-template: silently re-apply so Generate
                                // re-enables automatically via loadTermsTemplates()
                                loadTermsTemplates();
                            }
                        } else {
                            showNotification(response.message || 'Failed to generate quote.',
                                'error');
                        }
                    },
                    error: function(xhr) {
                        showNotification(
                            xhr.responseJSON?.message ||
                            'An error occurred while generating the quote.',
                            'error'
                        );
                    },
                    complete: function() {
                        // Restore button label always.
                        // Only re-enable if generation FAILED — success path keeps it locked
                        // until T&C is re-selected.
                        $btn.html('<i class="fas fa-file-alt mr-1"></i> Generate Quote');
                        if (!generationSucceeded) {
                            // Failed or errored — let user retry
                            $btn.prop('disabled', false).removeAttr('title');
                        } else {
                            // Succeeded — keep disabled, set tooltip explaining why
                            $btn.prop('disabled', true)
                                .attr('title',
                                    'Select a Terms & Conditions template to generate a new version'
                                );
                        }
                    }
                });
            });

            // Load versions on page init
            loadVersions();

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
                                        `<option value="${t.Name}">${t.Name}</option>`);
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
                            $select.empty().append(`<option value="" disabled>${msg}</option>`);
                            console.warn('loadEmailTemplates error:', res?.message || xhr.status);
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
                    $.ajax({
                        url: '/quotation/office-code',
                        type: 'GET',
                        data: {
                            team_id: teamId
                        },
                        success: function(response) {
                            if (response.success && response.office_code) {
                                officeCode = response.office_code.trim();
                                fetchTemplates(officeCode);
                            } else {
                                $select.empty().append(
                                    '<option value="" disabled>⚠ Could not resolve office</option>'
                                );
                                $select.prop('disabled', false);
                            }
                        },
                        error: function() {
                            $select.empty().append(
                                '<option value="" disabled>⚠ Failed to resolve office</option>'
                            );
                            $select.prop('disabled', false);
                        }
                    });
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
            $('#swapEmailBtn').on('click', function() {
                const toVal = $('#emailTo').val();
                const fromVal = $('#emailFrom').val();
                $('#emailTo').val(fromVal);
                $('#emailFrom').val(toVal);
            });

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
                                    `<option value="${v.url}">${v.label}</option>`);
                            });
                        } else {
                            $select.append(
                                '<option value="" disabled>No quotes generated yet</option>');
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
                            .html('<i class="fas fa-spinner fa-spin mr-1"></i> Sending...');
                    },
                    success: function(response) {
                        if (response.success) {
                            showNotification('Email sent successfully', 'success');
                            $('#emailQuoteModal').modal('hide');
                            loadEmailHistory();
                        } else {
                            showNotification(response.message || 'Failed to send email',
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
                        '<tr><td colspan="3" class="text-center text-muted">No emails sent yet</td></tr>');
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

            $('#emailQuoteModal .close, #emailQuoteModal button[data-dismiss="modal"]').on('click', function(
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
                    if ($el.hasClass('ui-autocomplete-input')) $el.autocomplete('destroy');
                });
            });

            // Load all dropdowns when modal opens
            $('#createBookingModal').on('show.bs.modal', function() {
                loadServiceTemplates();
                loadBillToParties();
                loadForwarders();
                // Small delay ensures modal DOM is visible before autocomplete binds
                setTimeout(function() {
                    initLocationAutocomplete('cb_place_of_receipt', 'cb_place_of_receipt_code',
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
                        $select.empty().append('<option value="" disabled>Failed to load</option>');
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
                                '<option value="" disabled>No parties available</option>');
                        }
                    },
                    error: function() {
                        $select.empty().append('<option value="" disabled>Failed to load</option>');
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
                                '<option value="" disabled>No forwarders available</option>');
                        }
                    },
                    error: function() {
                        $select.empty().append('<option value="" disabled>Failed to load</option>');
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
                                include_country: $('#cb_include_country').is(':checked') ?
                                    1 : 0,
                                country_code: ''
                            },
                            success: function(data) {
                                if (data.success && data.locations.length) {
                                    response(data.locations.map(function(l) {
                                        return {
                                            label: l.LocationCode + ' — ' +
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
                        bill_to_party_id: $('#cb_bill_to_party option:selected').data('id'),
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
                                '<i class="fas fa-spinner fa-spin mr-1"></i> Creating...');
                    },
                    success: function(response) {
                        if (response.success) {
                            showNotification('Booking created successfully', 'success');
                            $('#createBookingModal').modal('hide');
                        } else {
                            showNotification(response.message || 'Failed to create booking',
                                'error');
                        }
                    },
                    error: function(xhr) {
                        showNotification(
                            xhr.responseJSON?.message || 'Failed to create booking',
                            'error'
                        );
                    },
                    complete: function() {
                        $('#submitCreateBookingBtn').prop('disabled', false).html('Create');
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

                        if (response.success && Array.isArray(response.data) && response.data
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
                            console.warn('fetchCustomerTypes: SP returned empty or failed response',
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
                    complete: function() {
                        // Keep locked — Edit button controls when this becomes interactive
                        $select.prop('disabled', true);
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
                        $select.empty().append('<option value="">-- Select Agent Type --</option>');

                        if (response.success && Array.isArray(response.data) && response.data
                            .length >
                            0) {
                            response.data.forEach(function(item) {
                                const code = item.Code ?? item.code ?? '';
                                const name = item.Name ?? item.name ?? '';

                                // FIX: value is now Code (e.g. "WCA"), not the GUID Id.
                                // sp_Quote_DetailsUpdate requires the Code.
                                const isSelected = String(code) === String(
                                    SAVED_AGENT_TYPE);

                                $select.append(
                                    `<option value="${code}" ${isSelected ? 'selected' : ''}>${name}</option>`
                                );
                            });
                        } else {
                            $select.append(
                                '<option value="" disabled>-- No agent types available --</option>'
                            );
                            console.warn('fetchAgentTypes: SP returned empty or failed response',
                                response);
                        }
                    },
                    error: function(xhr) {
                        $select.empty()
                            .append('<option value="">-- Select Agent Type --</option>')
                            .append('<option value="" disabled>-- Failed to load --</option>');
                        console.error('fetchAgentTypes: AJAX error', xhr);
                    },
                    complete: function() {
                        // Keep locked — syncAgentTypeState() controls when this becomes interactive
                        $select.prop('disabled', true);
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

            $('#editCustomerDetailsBtn').on('click', function() {
                originalCustomerType = $('#customerTypeSelect').val();
                originalAgentType = $('#agentTypeSelect').val();

                $('#customerTypeSelect').prop('disabled', false);
                syncAgentTypeState();

                $(this).hide();
                $('#saveCustomerDetailsBtn, #cancelCustomerDetailsBtn').show();
            });

            $('#cancelCustomerDetailsBtn').on('click', function() {
                $('#customerTypeSelect').val(originalCustomerType);
                $('#agentTypeSelect').val(originalAgentType);

                $('#customerTypeSelect, #agentTypeSelect').prop('disabled', true);

                // Restore all inputs to readonly and original styling
                $('#customerDetailsCard .card-body')
                    .find('input:not([type="hidden"])')
                    .prop('readonly', true)
                    .css({
                        'background-color': '',
                        'border-color': ''
                    });

                $(this).hide();
                $('#saveCustomerDetailsBtn').hide();
                $('#editCustomerDetailsBtn').show();
            });

            $('#customerTypeSelect').on('change', function() {
                syncAgentTypeState();

                if ($(this).val() === 'New Agent') {
                    // Clear all text inputs in the customer card
                    $('#customerDetailsCard .card-body')
                        .find('input:not([type="hidden"])')
                        .val('');

                    // Make all inputs editable for New Agent data entry
                    $('#customerDetailsCard .card-body')
                        .find('input:not([type="hidden"])')
                        .prop('readonly', false)
                        .css({
                            'background-color': '#fff9e6',
                            'border-color': '#ffc107'
                        });

                } else {
                    // Switching away from New Agent — restore readonly on all fields
                    // (the user can still use Edit button to change customer type/agent type)
                    $('#customerDetailsCard .card-body')
                        .find('input:not([type="hidden"])')
                        .prop('readonly', true)
                        .css({
                            'background-color': '',
                            'border-color': ''
                        });
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

                if (!customerType) {
                    showNotification('Customer Type is required', 'error');
                    return;
                }

                if (customerType.toLowerCase().includes('agent') && !agentType) {
                    showNotification('Agent Type is required for Agent customers', 'error');
                    return;
                }

                $.ajax({
                    url: '{{ route('quotation.customer.save') }}',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        booking_no: bookingNo,
                        customer_type: customerType,
                        agent_type: agentType
                    },
                    beforeSend: function() {
                        $('#saveCustomerDetailsBtn').prop('disabled', true)
                            .html('<i class="fas fa-spinner fa-spin mr-1"></i> Saving...');
                    },
                    success: function(response) {
                        if (response.success) {
                            showNotification('Customer details saved successfully',
                                'success');

                            originalCustomerType = customerType;
                            originalAgentType = agentType;

                            $('#customerTypeSelect, #agentTypeSelect').prop('disabled',
                                true);
                            $('#saveCustomerDetailsBtn, #cancelCustomerDetailsBtn').hide();
                            $('#editCustomerDetailsBtn').show();
                        } else {
                            showNotification(response.message || 'Failed to save', 'error');
                        }
                    },
                    error: function(xhr) {
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
                const increaseTypeSelectName = type === 'revenue' ? 'revenue_increase_type' : 'cost_increase_type';

                $modal.find('input[type="text"], input[type="number"]').val('');
                $modal.find('input[type="number"]').val('0');

                // Reset selects EXCEPT increase_type — let toggleCostFormFields own its default
                $modal.find('select').not(`select[name="${increaseTypeSelectName}"]`).prop('selectedIndex', 0);

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
                const currentType = $('#addRevenueModal').find('input[name="revenueChargeType"]:checked').val() ||
                    'revenue';
                clearModalForm('revenue');
                $(`#addRevenueModal input[name="revenueChargeType"][value="${currentType}"]`).prop('checked', true);
                toggleRevenueFormFields(currentType);
            }

            function clearCostModalForm() {
                // Read the currently selected type BEFORE clearModalForm resets the form
                const currentType = $('#addCostModal').find('input[name="costChargeType"]:checked').val() || 'cost';
                clearModalForm('cost');
                // Re-apply the checked state that clearModalForm wiped
                $(`#addCostModal input[name="costChargeType"][value="${currentType}"]`).prop('checked', true);
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

            $('.card-header').on('click', function(e) {
                if (!$(e.target).closest('[data-card-widget="collapse"]').length &&
                    !$(e.target).closest('.show-items-link').length) {
                    $(this).find('[data-card-widget="collapse"]').click();
                }
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

            // ============================================
            // TERMS & CONDITIONS
            // ============================================
            function loadTermsTemplates() {
                const codeToUse = officeCode;

                if (!codeToUse) {
                    setTimeout(loadTermsTemplates, 500);
                    return;
                }

                $.ajax({
                    url: '{{ route('quotation.communication.templates') }}',
                    type: 'GET',
                    data: {
                        module: 'Quotation',
                        sub_module: 'T&C',
                        office_code: codeToUse, // ← kept for backwards compat (ignored by SP path)
                        booking_no: bookingNo // ← NEW: triggers SP path in controller
                    },
                    success: function(response) {
                        const $select = $('#termsTemplateSelect');
                        $select.empty();

                        if (response.success && response.templates.length > 0) {

                            // ── SINGLE TEMPLATE: auto-apply, no dropdown needed ──────────
                            if (response.templates.length === 1) {
                                const t = response.templates[0];

                                // Hide dropdown, show badge
                                $('#termsDropdownWrapper').hide();
                                $('#termsAutoAppliedName').text(t.Name);
                                $('#termsAutoAppliedBadge').show();

                                // Fetch description then silently save
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
                                        // Show preview read-only (no Apply button needed)
                                        $('#termsDescriptionWrapper').show();
                                        $('#applyTermsBtn').hide();

                                        // Only auto-save if T&C hasn't been applied yet
                                        if (tcAlreadyApplied) {
                                            // Already applied — just unlock Generate Quote
                                            $('#generateQuoteBtn')
                                                .prop('disabled', false)
                                                .removeAttr('title');
                                            return;
                                        }

                                        // Silently save the single T&C
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
                                                    // Unlock Generate Quote
                                                    $('#generateQuoteBtn')
                                                        .prop('disabled', false)
                                                        .removeAttr('title');
                                                } else {
                                                    showNotification(
                                                        'Auto-apply T&C failed: ' +
                                                        (saveResponse
                                                            .message ||
                                                            'Unknown error'
                                                        ),
                                                        'warning'
                                                    );
                                                }
                                            },
                                            error: function() {
                                                showNotification(
                                                    'Failed to auto-apply Terms & Conditions',
                                                    'warning');
                                            }
                                        });
                                    },
                                    error: function() {
                                        showNotification('Failed to load T&C description',
                                            'warning');
                                    }
                                });

                                return; // ← skip the multi-template path below
                            }

                            // ── MULTIPLE TEMPLATES: show dropdown as before ──────────────
                            $select.append('<option value="">-- Select Template --</option>');
                            response.templates.forEach(function(t) {
                                $select.append(`<option value="${t.Name}">${t.Name}</option>`);
                            });

                            $('#termsDropdownWrapper').show();
                            $('#termsAutoAppliedBadge').hide();

                            @if ($quotation->QuotationTCTemplate)
                                // Pre-populate preview if T&C was previously applied
                                $('#termsDescriptionPreview').val(
                                    '{{ addslashes($quotation->terms_condition ?? '') }}');
                                if ($('#termsDescriptionPreview').val()) {
                                    $('#termsDescriptionWrapper').show();
                                }
                            @endif

                        } else {
                            // Error states — unchanged
                            if (response.code === 'NOT_CONFIGURED') {
                                $select.append(
                                    `<option value="" disabled>⚠ T&C not configured for office: ${codeToUse}</option>`
                                );
                            } else if (response.code === 'OFFICE_NOT_FOUND') {
                                $select.append(
                                    `<option value="" disabled>⚠ Office not found: ${codeToUse}</option>`
                                );
                            } else {
                                $select.append(
                                    `<option value="" disabled>-- No templates available --</option>`
                                );
                            }
                        }
                    },
                    error: function(xhr) {
                        const response = xhr.responseJSON;
                        const $select = $('#termsTemplateSelect');
                        $select.empty();

                        if (response && response.code === 'NOT_CONFIGURED') {
                            $select.append(
                                `<option value="" disabled>⚠ T&C not configured for office: ${codeToUse}</option>`
                            );
                        } else if (response && response.code === 'OFFICE_NOT_FOUND') {
                            $select.append(
                                `<option value="" disabled>⚠ Office not found: ${codeToUse}</option>`
                            );
                        } else {
                            $select.append(
                                `<option value="" disabled>-- Failed to load templates --</option>`
                            );
                        }

                        console.warn('loadTermsTemplates error:', response?.message || xhr.status);
                    }
                });
            }

            $('#termsTemplateSelect').on('change', function() {
                const selectedName = $(this).val();

                // Hide re-select instruction and disable Generate until auto-apply succeeds
                $('#termsReSelectMsg').hide();
                $('#generateQuoteBtn').prop('disabled', true).attr('title',
                    'Please wait — applying Terms & Conditions...');
                $('#applyTermsBtn').prop('disabled', true);

                if (!selectedName) {
                    $('#termsDescriptionWrapper').hide();
                    $('#termsDescriptionPreview').val('');
                    $('#generateQuoteBtn').prop('disabled', true).attr('title',
                        'Please select a Terms & Conditions template first');
                    return;
                }

                // Step 1: Fetch the T&C description
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
                            $('#generateQuoteBtn').prop('disabled', true).attr('title',
                                'T&C description failed to load');
                            return;
                        }

                        const description = response.description;
                        $('#termsDescriptionPreview').val(description);
                        $('#applyTermsBtn').prop('disabled', false);

                        // Step 2: Auto-apply the selected T&C silently
                        $.ajax({
                            url: '{{ route('quotation.communication.save-terms') }}',
                            type: 'POST',
                            data: {
                                _token: '{{ csrf_token() }}',
                                booking_no: bookingNo,
                                terms_condition: description,
                                template_name: selectedName
                            },
                            success: function(saveResponse) {
                                if (saveResponse.success) {
                                    // T&C saved — unlock Generate Quote
                                    $('#generateQuoteBtn')
                                        .prop('disabled', false)
                                        .removeAttr('title');
                                } else {
                                    showNotification(saveResponse.message ||
                                        'Failed to apply Terms & Conditions',
                                        'error');
                                    $('#generateQuoteBtn').prop('disabled', true)
                                        .attr('title',
                                            'T&C apply failed — please retry');
                                }
                            },
                            error: function() {
                                showNotification(
                                    'Failed to apply Terms & Conditions',
                                    'error');
                                $('#generateQuoteBtn').prop('disabled', true).attr(
                                    'title', 'T&C apply failed — please retry');
                            }
                        });
                    },
                    error: function() {
                        $('#termsDescriptionPreview').val('Error loading description.');
                        showNotification('Error loading T&C description', 'error');
                        $('#generateQuoteBtn').prop('disabled', true).attr('title',
                            'T&C description failed to load');
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
                    showNotification('No booking number — cannot save Terms & Conditions', 'error');
                    return;
                }

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
                            .html('<i class="fas fa-spinner fa-spin mr-1"></i> Saving...');
                    },
                    success: function(response) {
                        if (response.success) {
                            showNotification('Terms & Conditions applied successfully',
                                'success');

                            // ── Unblock Generate Quote now that T&C is applied ──
                            $('#generateQuoteBtn')
                                .prop('disabled', false)
                                .removeAttr('title');
                        } else {
                            showNotification(response.message || 'Failed to apply Terms',
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
            // COPIED BOOKING — UNLOCK EDITABLE FIELDS
            // ============================================
            if (isCopiedBooking) {
                const editableCards = ['#customerDetailsCard', '#shipmentDetailsCard', '#cargoDetailsCard'];

                editableCards.forEach(function(cardId) {
                    const $card = $(cardId);

                    $card.find('.card-body').show();
                    $card.find('[data-card-widget="collapse"] i')
                        .removeClass('fa-chevron-down').addClass('fa-chevron-up');
                    $card.removeClass('collapsed-card');

                    $card.find('input[readonly]').each(function() {
                        $(this).removeAttr('readonly').css({
                            'background-color': '#fff',
                            'border': '1px solid #ced4da',
                            'border-radius': '4px',
                            'padding': '0.25rem 0.5rem',
                            'cursor': 'text'
                        });
                    });
                });

                $('.content-header h1, .content-header .d-flex').first().after(`
                    <div class="alert alert-info alert-dismissible fade show mx-3 mt-2" role="alert">
                        <i class="fas fa-copy mr-1"></i>
                        <strong>Copied Booking</strong> — Customer, Shipment and Cargo details are now editable.
                        Update them before sending this quotation.
                        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                    </div>
                `);
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
                    const $select = $('<select class="form-control form-control-sm container-type-select">');
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
                    const msg = editable ? 'No containers added — click "Add a line" below' : 'No containers';
                    $tbody.append(`<tr><td colspan="3" class="text-muted text-center">${msg}</td></tr>`);
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
                        quote_no: QUOTE_NO
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
                        qty: parseInt($(this).find('td:eq(1)').text().trim(), 10) || 0
                    });
                });

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
                $('#commodityInput').val(originalCommodity).prop('readonly', true).css('background-color',
                    '');
                renderContainerRows(originalContainers, false);
                $('#addContainerRowBtn').addClass('d-none');

                $(this).addClass('d-none');
                $('#saveCargoDetailsBtn').addClass('d-none');
                $('#editCargoDetailsBtn').removeClass('d-none');
            });

            // Save
            $('#saveCargoDetailsBtn').on('click', function() {
                const cargoVolume = $('#cargoVolumeInput').val();
                const containers = serializeContainerList();
                const scopeVolume = '{{ $quotation->scopevolume ?? '' }}';

                if (scopeVolume !== 'Containerized' && (!cargoVolume || parseFloat(cargoVolume) <= 0)) {
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
                        quote_no: QUOTE_NO,
                        commodity: $('#commodityInput').val(),
                        cargo_volume: cargoVolume || 0,
                        containers: containers
                    },
                    beforeSend: function() {
                        $('#saveCargoDetailsBtn').prop('disabled', true)
                            .html('<i class="fas fa-spinner fa-spin mr-1"></i> Saving...');
                    },
                    success: function(response) {
                        if (response.success) {
                            showNotification('Cargo details saved successfully', 'success');

                            originalCargoVolume = cargoVolume;
                            originalContainers = [];
                            $('#containerListBody tr[data-container-row]').each(function() {
                                originalContainers.push({
                                    type: $(this).find(
                                            '.container-type-select')
                                        .val(),
                                    qty: parseInt($(this).find(
                                            '.container-qty-input')
                                        .val(),
                                        10) || 0
                                });
                            });

                            $('#cargoVolumeInput').prop('readonly', true).css(
                                'background-color', '');
                            $('#commodityInput').prop('readonly', true).css('background-color',
                                '');
                            renderContainerRows(originalContainers, false);
                            $('#addContainerRowBtn').addClass('d-none');
                            $('#saveCargoDetailsBtn, #cancelCargoDetailsBtn').addClass(
                                'd-none');
                            $('#editCargoDetailsBtn').removeClass('d-none');
                        } else {
                            showNotification(response.message || 'Failed to save', 'error');
                        }
                    },
                    error: function(xhr) {
                        showNotification(xhr.responseJSON?.message || 'Failed to save',
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
            });

            $('#cancelInquiryBtn').on('click', function() {
                $('#salesPersonDisplay').val(inquirySnapshot.display).attr('readonly', true);
                $('#salesPersonCode').val(inquirySnapshot.code);
                $('#salesPersonEmail').val(inquirySnapshot.email);
                $('#salesPersonSuggestions').hide();
                $('#editInquiryBtn').removeClass('d-none');
                $('#saveInquiryBtn, #cancelInquiryBtn').addClass('d-none');
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
                        booking_no: bookingNo,
                        sales_person_code: code,
                        sales_person_email: email,
                    },
                    success: function(res) {
                        if (res.success) {
                            $('#salesPersonDisplay').attr('readonly', true);
                            $('#salesPersonEmail').attr('readonly', true);
                            $('#editInquiryBtn').removeClass('d-none');
                            $('#saveInquiryBtn, #cancelInquiryBtn').addClass('d-none');
                            showNotification('Inquiry details saved.', 'success');
                        } else {
                            showNotification(res.message || 'Save failed.', 'error');
                        }
                    },
                    error: function(xhr) {
                        showNotification(xhr.responseJSON?.message || 'Server error.', 'error');
                    },
                    complete: function() {
                        $btn.prop('disabled', false).html(
                            '<i class="fas fa-save mr-1"></i> Save');
                    }
                });
            });

            // Sales Person autocomplete
            var spDebounce = null;
            $('#salesPersonDisplay').on('input', function() {
                var query = $(this).val().trim();
                clearTimeout(spDebounce);
                $('#salesPersonSuggestions').hide().empty();
                $('#salesPersonCode').val('');
                if (query.length < 2) return;

                spDebounce = setTimeout(function() {
                    $.get('{{ route('quotation.sales-persons') }}', {
                            filter: query
                        })
                        .done(function(res) {
                            var $list = $('#salesPersonSuggestions').empty();
                            if (!res.success || !res.persons.length) {
                                $list.hide();
                                return;
                            }

                            $.each(res.persons, function(i, p) {
                                var fullName = p.FullName || p
                                    .Contact_PersonName_FirstName || '';
                                var email = p.Email || '';
                                $('<li>', {
                                    class: 'list-group-item list-group-item-action',
                                    css: {
                                        cursor: 'pointer',
                                        padding: '0.4rem 0.75rem',
                                        fontSize: '0.875rem'
                                    },
                                    text: p.Code + ' — ' + fullName
                                }).on('mousedown', function(e) {
                                    e.preventDefault();
                                    $('#salesPersonDisplay').val(fullName || p
                                        .Code);
                                    $('#salesPersonCode').val(p.Code);
                                    $('#salesPersonEmail').val(email);
                                    $list.hide();
                                }).appendTo($list);
                            });
                            $list.show();
                        });
                }, 300);
            });

            $('#salesPersonDisplay').on('blur', function() {
                setTimeout(function() {
                    $('#salesPersonSuggestions').hide();
                }, 200);
            });

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
    </script>
@stop
