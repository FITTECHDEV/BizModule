@extends('adminlte::page')

@section('title', 'Edit Booking Quotation')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1><i class="fas fa-file-invoice"></i> Inquiry No - {{ $quotation->bookingno ?? 'N/A' }}</h1>
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
                                    value="{{ $quotation->source ?? 'N/A' }}" readonly>
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
                            <label class="col-sm-5 col-form-label">Sales person</label>
                            <div class="col-sm-7">
                                <input type="text" class="form-control form-control-sm"
                                    value="{{ $quotation->username ?? 'N/A' }}" readonly>
                            </div>
                        </div>
                        <div class="form-group row mb-0">
                            <label class="col-sm-5 col-form-label">Office</label>
                            <div class="col-sm-7">
                                <input type="text" class="form-control form-control-sm"
                                    value="{{ $quotation->teamname ?? 'N/A' }}" readonly>
                            </div>
                        </div>
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
                                    value="{{ $quotation->ServiceVerticalname ?? 'N/A' }}" readonly>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-5 col-form-label">Process</label>
                            <div class="col-sm-7">
                                <input type="text" class="form-control form-control-sm"
                                    value="{{ $quotation->process ?? 'N/A' }}" readonly>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-5 col-form-label">Origin Country</label>
                            <div class="col-sm-7">
                                <input type="text" class="form-control form-control-sm"
                                    value="{{ $quotation->origincountry ?? 'N/A' }}" readonly>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-5 col-form-label">Destination Country</label>
                            <div class="col-sm-7">
                                <input type="text" class="form-control form-control-sm"
                                    value="{{ $quotation->destinationcountry ?? 'N/A' }}" readonly>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-5 col-form-label">InCO Terms</label>
                            <div class="col-sm-7">
                                <input type="text" class="form-control form-control-sm"
                                    value="{{ $quotation->incoterms ?? 'N/A' }}" readonly>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-5 col-form-label">Shipment Mode</label>
                            <div class="col-sm-7">
                                <input type="text" class="form-control form-control-sm"
                                    value="{{ $quotation->shipmentmode ?? 'N/A' }}" readonly>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-5 col-form-label">Transport Mode</label>
                            <div class="col-sm-7">
                                <input type="text" class="form-control form-control-sm"
                                    value="{{ $quotation->TransportMode ?? 'N/A' }}" readonly>
                            </div>
                        </div>
                        <div class="form-group row mb-0">
                            <label class="col-sm-5 col-form-label">Service Class</label>
                            <div class="col-sm-7">
                                <input type="text" class="form-control form-control-sm"
                                    value="{{ $quotation->ServiceClass ?? 'N/A' }}" readonly>
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
                                <input type="text" class="form-control form-control-sm"
                                    value="{{ $quotation->commodity ?? 'N/A' }}" readonly>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-5 col-form-label">Scope Of Volume</label>
                            <div class="col-sm-7">
                                <input type="text" class="form-control form-control-sm"
                                    value="{{ $quotation->scopevolume ?? 'N/A' }}" readonly>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-5 col-form-label">Cargo Volume</label>
                            <div class="col-sm-7">
                                <input type="text" class="form-control form-control-sm"
                                    value="{{ $quotation->cargovolume ?? 'N/A' }}" readonly>
                            </div>
                        </div>
                        <div class="form-group row mb-0">
                            <label class="col-sm-5 col-form-label">Container List</label>
                            <div class="col-sm-7">
                                <input type="text" class="form-control form-control-sm"
                                    value="{{ $quotation->containerlist ?? 'N/A' }}" readonly>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quotation Document (with Generate Quote button in title bar + T&C in body) -->
                <div class="card card-primary">
                    <div class="card-header d-flex justify-content-between align-items-center" style="cursor: pointer;"
                        id="quotationDocHeader">
                        <h3 class="card-title mb-0">Quotation Document</h3>
                        <div class="card-tools d-flex align-items-center">
                            <button type="button" class="btn btn-email-quote btn-sm mr-2" id="generateQuoteBtn"
                                onclick="event.stopPropagation()">
                                <i class="fas fa-file-alt mr-1"></i> Generate Quote
                            </button>
                            <button type="button" class="btn btn-tool" data-card-widget="collapse"
                                style="margin-left: auto;">
                                <i class="fas fa-chevron-up"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="form-group row">
                            <label class="col-sm-4 col-form-label">Version 1</label>
                            <div class="col-sm-8">
                                <a href="#" class="text-primary">DocumentDownloadLink</a>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-4 col-form-label">Version 2</label>
                            <div class="col-sm-8">
                                <a href="#" class="text-primary">DocumentDownloadLink</a>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-4 col-form-label">Version 3</label>
                            <div class="col-sm-8">
                                <a href="#" class="text-primary">DocumentDownloadLink</a>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-4 col-form-label">Version 4</label>
                            <div class="col-sm-8">
                                <a href="#" class="text-primary">DocumentDownloadLink</a>
                            </div>
                        </div>

                        <!-- Terms & Conditions -->
                        <div class="mt-3 pt-2" style="border-top: 1px solid #dee2e6;">
                            <div class="form-group row mb-0">
                                <label class="col-sm-4 col-form-label">Terms & Conditions</label>
                                <div class="col-sm-8">
                                    <select class="form-control form-control-sm" id="termsTemplateSelect">
                                        <option value="">-- Select Template --</option>
                                    </select>
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
                        <button type="button" class="btn btn-email-quote btn-sm" id="emailQuoteBtn">
                            <i class="fas fa-paper-plane mr-1"></i> Email Quote
                        </button>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm table-hover mb-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Sent On</th>
                                        <th>To Email ID</th>
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
                                    value="{{ $quotation->ClientIntegration ?? 'N/A' }}" readonly>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-5 col-form-label">Booking No</label>
                            <div class="col-sm-7">
                                <input type="text" class="form-control form-control-sm"
                                    value="{{ $quotation->SCMBookingNo ?? 'N/A' }}" readonly>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-5 col-form-label">Booking Integration</label>
                            <div class="col-sm-7">
                                <input type="text" class="form-control form-control-sm"
                                    value="{{ $quotation->SCMINTStatus ?? 'N/A' }}" readonly>
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
                                    <input type="text" class="form-control form-control-sm" value="N/A" readonly>
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
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group row">
                                    <label class="col-sm-4 col-form-label">Company</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control form-control-sm"
                                            value="{{ $quotation->companyname ?? 'N/A' }}" readonly>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-4 col-form-label">Address 1</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control form-control-sm"
                                            value="{{ $quotation->pri_partner_street ?? 'N/A' }}" readonly>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-4 col-form-label">City</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control form-control-sm"
                                            value="{{ $quotation->pri_partner_city ?? 'N/A' }}" readonly>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-4 col-form-label">State</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control form-control-sm"
                                            value="{{ $quotation->pri_partner_state ?? 'N/A' }}" readonly>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-4 col-form-label">Contact Person</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control form-control-sm"
                                            value="{{ $quotation->pri_partnername ?? 'N/A' }}" readonly>
                                    </div>
                                </div>
                                <div class="form-group row mb-0">
                                    <label class="col-sm-4 col-form-label">Phone No</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control form-control-sm"
                                            value="{{ $quotation->pri_partner_phone ?? 'N/A' }}" readonly>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group row">
                                    <label class="col-sm-4 col-form-label">Client ID</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control form-control-sm"
                                            value="{{ $quotation->clientid ?? 'N/A' }}" readonly>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-4 col-form-label">Address 2</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control form-control-sm"
                                            value="{{ $quotation->pri_partner_street2 ?? 'N/A' }}" readonly>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-4 col-form-label">Zip</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control form-control-sm"
                                            value="{{ $quotation->pri_partner_zip ?? 'N/A' }}" readonly>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-4 col-form-label">Country</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control form-control-sm"
                                            value="{{ $quotation->pri_partner_countryname ?? 'N/A' }}" readonly>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-4 col-form-label">Email ID</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control form-control-sm"
                                            value="{{ $quotation->pri_partner_email ?? 'N/A' }}" readonly>
                                    </div>
                                </div>
                                <div class="form-group row mb-0">
                                    <label class="col-sm-4 col-form-label">Industry</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control form-control-sm"
                                            value="{{ $quotation->Industryname ?? 'N/A' }}" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Customer Type & Agent Type — new row below the two-column section --}}
                        <div class="row mt-3 pt-3" style="border-top: 1px solid #dee2e6;">
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
                                            name="agent_type">
                                            <option value="">-- Loading... --</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Revenue Section -->
                <div class="card card-success">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <h3 class="card-title mb-0 mr-3">Revenue</h3>
                            <span class="text-white small" id="revenueTotalHeader">
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
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h3 class="card-title mb-0">Cost</h3>
                        <div class="d-flex align-items-center ml-auto">
                            <span class="text-white mr-3 small" id="costTotalHeader">
                                Total: <strong id="costTotalHeaderAmount">0.00</strong>
                            </span>
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
                                            value="N/A" readonly>
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
                                            value="N/A" readonly>
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
                                    <button type="button" class="btn btn-success mr-2" id="createBookingBtn"
                                        style="{{ ($quotation->status ?? '') === 'Won' ? '' : 'display:none;' }}">
                                        <i class="fas fa-plus-circle mr-1"></i> Create Booking
                                    </button>
                                    <div class="btn-group" id="quoteActionDropdown"
                                        style="{{ ($quotation->status ?? '') === 'Won' ? 'display:none;' : '' }}">
                                        <button type="button" class="btn btn-secondary dropdown-toggle"
                                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
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
                                    <p class="text-muted mb-0">Base Currency: <strong>KES</strong></p>
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
                                    <p class="text-muted mb-0">Base Currency: <strong>KES</strong></p>
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
                            <div class="mb-3">
                                <label for="emailTemplateSelect" class="form-label">
                                    <span class="text-danger">*</span> Template
                                </label>
                                <select class="form-control" id="emailTemplateSelect" name="email_template_id" required>
                                    <option value="">-- Select Template --</option>
                                    {{-- TODO: Populate via endpoint when template source is confirmed --}}
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="emailTo" class="form-label">
                                    <span class="text-danger">*</span> To Email
                                </label>
                                <input type="email" class="form-control" id="emailTo" name="email_to"
                                    value="{{ $quotation->pri_partner_email ?? '' }}" required>
                            </div>
                            <div class="mb-3">
                                <label for="emailFrom" class="form-label">
                                    <span class="text-danger">*</span> From Email
                                </label>
                                <input type="email" class="form-control" id="emailFrom" name="email_from"
                                    value="{{ $quotation->user_email ?? '' }}" required>
                            </div>
                            <div class="mb-3">
                                <label for="emailSubject" class="form-label">
                                    <span class="text-danger">*</span> Subject
                                </label>
                                <input type="text" class="form-control" id="emailSubject" name="email_subject"
                                    placeholder="Enter subject" required>
                            </div>
                            <div class="mb-3">
                                <label for="emailBody" class="form-label">
                                    <span class="text-danger">*</span> Body
                                </label>
                                <textarea class="form-control" id="emailBody" name="email_body" rows="6"
                                    placeholder="Email body will auto-populate when a template is selected" required></textarea>
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
                    <div class="modal-body">
                        <form id="createBookingForm">

                            {{-- Service Template --}}
                            <div class="form-group">
                                <label for="cb_service_template">
                                    Service Template <span class="text-danger">*</span>
                                </label>
                                <select class="form-control" id="cb_service_template" name="service_template"
                                    required>
                                    <option value="">-- Select --</option>
                                </select>
                            </div>

                            {{-- Bill To Party + Forwarder --}}
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="cb_bill_to_party">
                                            Bill To Party <span class="text-danger">*</span>
                                        </label>
                                        <select class="form-control" id="cb_bill_to_party" name="bill_to_party"
                                            required>
                                            <option value="">-- Select --</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="cb_forwarder">
                                            Forwarder <span class="text-danger">*</span>
                                        </label>
                                        <select class="form-control" id="cb_forwarder" name="forwarder" required>
                                            <option value="">-- Select --</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            {{-- Include the Country --}}
                            <div class="form-group">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="cb_include_country"
                                        name="include_country">
                                    <label class="form-check-label font-weight-bold" for="cb_include_country">
                                        Include the Country
                                    </label>
                                </div>
                            </div>

                            {{-- Place of Receipt + Place of Delivery --}}
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="cb_place_of_receipt">
                                            Place of Receipt <span class="text-danger">*</span>
                                        </label>
                                        <select class="form-control" id="cb_place_of_receipt" name="place_of_receipt"
                                            required>
                                            <option value="">-- Select --</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="cb_place_of_delivery">
                                            Place of Delivery <span class="text-danger">*</span>
                                        </label>
                                        <select class="form-control" id="cb_place_of_delivery"
                                            name="place_of_delivery" required>
                                            <option value="">-- Select --</option>
                                        </select>
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

        #createBookingModal .modal-body .form-control,
        #createBookingModal .modal-body select.form-control {
            height: 44px;
            font-size: 0.95rem;
            border: 1.5px solid #b0b8c4;
            border-radius: 4px;
            background-color: #fff;
            /* Custom dropdown arrow matching the screenshot */
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8'%3E%3Cpath fill='%23495057' d='M6 8L0 0h12z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 14px center;
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
            margin: 0.25rem 0 0.5rem 0;
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
    </style>
@stop


@section('js')
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>

    <script>
        const isCopiedBooking = {{ request()->query('copied', 0) ? 'true' : 'false' }};
        const quotationId = '{{ $quotation->ID }}';

        $(document).ready(function() {
            console.log('Booking Quotation Edit - Page Loaded');

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
                    $('#revenueTypeRevenue').prop('checked', true);
                    toggleRevenueFormFields('revenue');
                    loadBasedOnList('revenue');
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
                    $('#costTypeCost').prop('checked', true);
                    toggleCostFormFields('cost');
                    loadBasedOnList('cost');
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
            $(document).on('change', 'select[name="revenue_currency"], select[name="cost_currency"]', function() {
                const $currencySelect = $(this);
                const prefix = $currencySelect.attr('name').split('_')[0];
                const currencyCode = $currencySelect.find('option:selected').data('code');
                const $exchangeRateInput = $(`input[name="${prefix}_exchange_rate"]`);

                if (!currencyCode || !officeCode) {
                    console.warn('Currency code or office code missing');
                    return;
                }

                if (currencyCode === 'KES') {
                    $exchangeRateInput.val('1.000000');
                    calculateAmount(prefix);
                    return;
                }

                $.ajax({
                    url: '/quotation/exchange-rate',
                    type: 'GET',
                    data: {
                        office_code: officeCode,
                        from_currency: currencyCode
                    },
                    beforeSend: function() {
                        $exchangeRateInput.val('Loading...');
                    },
                    success: function(response) {
                        if (response.success && response.exchange_rate) {
                            $exchangeRateInput.val(parseFloat(response.exchange_rate).toFixed(
                                6));
                            calculateAmount(prefix);
                        } else {
                            showNotification('Failed to fetch exchange rate', 'warning');
                            $exchangeRateInput.val('1.000000');
                        }
                    },
                    error: function(xhr) {
                        console.error('Exchange rate fetch error:', xhr);
                        showNotification('Failed to fetch exchange rate', 'error');
                        $exchangeRateInput.val('1.000000');
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
                    $uomSelect.empty().append('<option value="">Select Based On first</option>').prop('disabled',
                        true);
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
                                $uomSelect.prop('disabled', true);
                                console.log('No UOM required for:', basedOnCode);
                            } else {
                                $uomSelect.append('<option value="">Select UOM</option>');
                                response.uom_list.forEach(function(uom) {
                                    $uomSelect.append(
                                        `<option value="${uom.Code}">${uom.Name}</option>`);
                                });
                                $uomSelect.prop('disabled', false);
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

                        if (ui.item.vat && ui.item.vat.includes('@')) {
                            const vatValue = parseFloat(ui.item.vat.split('@')[1]) || 0;
                            $(`select[name="${prefix}_tax_applicable"]`).val(vatValue);
                        }

                        return false;
                    }
                }).autocomplete("instance")._renderItem = function(ul, item) {
                    return $("<li>")
                        .addClass(item.disabled ? "ui-state-disabled" : "")
                        .append($("<div>").text(item.label))
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
                    const markupAmount = increaseType === 'Percentage' ?
                        amount + (amount * increaseBy / 100) :
                        amount + increaseBy;
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
                const revenueOrCost = (chargeType === 'revenue') ? 'Revenue' : 'Cost';

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
                                clearModalForm(type);
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

            function populateModal(type, charge) {
                const $modal = type === 'revenue' ? $('#addRevenueModal') : $('#addCostModal');

                $modal.find(`input[name="${type}_charge_code"]`).val(charge.chargeCode);
                $modal.find(`input[name="${type}_remark"]`).val(charge.chargeRemark);
                $modal.find(`input[name="${type}_quantity"]`).val(charge.quantity);
                $modal.find(`input[name="${type}_rate"]`).val(charge.rate);
                $modal.find(`input[name="${type}_exchange_rate"]`).val(charge.exChangeRate);

                if (charge.chgCurr && currencyMap[charge.chgCurr]) {
                    $modal.find(`select[name="${type}_currency"]`).val(currencyMap[charge.chgCurr]);
                }

                loadBasedOnList(type);
                setTimeout(function() {
                    $modal.find(`select[name="${type}_based_on"]`).val(charge.basedOnCode);
                    if (charge.basedOnCode) {
                        loadBasedOnUOM(type, charge.basedOnCode);
                        setTimeout(function() {
                            $modal.find(`select[name="${type}_based_on_uom"]`).val(charge
                                .basedOnUOM);
                        }, 500);
                    }
                }, 500);

                calculateAmount(type);
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
                    tbody.append('<tr><td colspan="8" class="text-center text-muted">No charges added</td></tr>');
                } else {
                    revenueCharges.forEach(function(charge, index) {
                        totalAmount += parseFloat(charge.amtInChgCurr || 0);
                        totalBaseAmount += parseFloat(charge.amtInBaseCurr || 0);
                        tbody.append(`
                            <tr>
                                <td>${charge.chargeName || charge.chargeCode || 'N/A'}</td>
                                <td class="text-right">${parseFloat(charge.rate || 0).toFixed(2)}</td>
                                <td class="text-right col-extra">${parseFloat(charge.quantity || 0).toFixed(2)}</td>
                                <td class="col-extra">${charge.basedOnUOM || 'N/A'}</td>
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
                    tbody.append('<tr><td colspan="8" class="text-center text-muted">No charges added</td></tr>');
                } else {
                    costCharges.forEach(function(charge, index) {
                        totalAmount += parseFloat(charge.amtInChgCurr || 0);
                        totalBaseAmount += parseFloat(charge.amtInBaseCurr || 0);
                        tbody.append(`
                            <tr>
                                <td>${charge.chargeName || charge.chargeCode || 'N/A'}</td>
                                <td class="text-right">${parseFloat(charge.rate || 0).toFixed(2)}</td>
                                <td class="text-right col-extra">${parseFloat(charge.quantity || 0).toFixed(2)}</td>
                                <td class="col-extra">${charge.basedOnUOM || 'N/A'}</td>
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
                const totalRevenue = revenueCharges.reduce((sum, c) => sum + parseFloat(c.amtInBaseCurr || 0), 0);
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
                updateStatus('Won', '', remark);
            });

            $('#wonModal').on('hidden.bs.modal', function() {
                $('#wonRemarkInput').val('');
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
                        if (response.success) {
                            showNotification(response.message, 'success');
                            $('#statusModal').modal('hide');
                            setTimeout(function() {
                                window.location.href = '{{ route('quotation.index') }}';
                            }, 1500);
                        } else {
                            showNotification(response.message, 'error');
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
                    updateStatus(status, reasonCode, reasonRemark);
                });

                $('#statusModal').on('input', '#reasonRemark', function() {
                    if ($(this).val().trim()) $(this).removeClass('is-invalid');
                });

                $('#statusModal').on('hidden.bs.modal', function() {
                    $(this).remove();
                });
            }

            function updateStatus(status, reasonCode, reasonRemark) {
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

                            if (status === 'Won') {
                                // Stay on page — reveal the Create Booking button
                                $('#createBookingBtn').show();
                                $('#quoteActionDropdown').hide();
                            } else {
                                setTimeout(function() {
                                    window.location.href = '{{ route('quotation.index') }}';
                                }, 1500);
                            }
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

            // ============================================
            // XML VIEWER
            // ============================================
            $('#viewXmlBtn').on('click', function(e) {
                e.preventDefault();
                $('#xmlViewerModal').modal('show');
            });

            // ============================================
            // GENERATE QUOTE BUTTON
            // ============================================
            $('#generateQuoteBtn').on('click', function() {
                window.open('{{ route('quotation.generate-pdf', $quotation->ID) }}', '_blank');
            });

            // ============================================
            // EMAIL QUOTE — MODAL OPEN
            // ============================================
            $('#emailQuoteBtn').on('click', function() {
                $('#emailQuoteForm')[0].reset();
                $('#emailBody').val('');
                $('#emailTemplateSelect').empty().append('<option value="">-- Select Template --</option>');
                $('#emailTo').val('{{ $quotation->pri_partner_email ?? '' }}');
                $('#emailFrom').val('{{ $quotation->user_email ?? '' }}');
                $('#emailQuoteModal').modal('show');
            });

            $(document).on('change', '#emailTemplateSelect', function() {
                if (!$(this).val()) $('#emailBody').val('');
            });

            // ============================================
            // EMAIL QUOTE — SEND
            // ============================================
            $('#sendEmailQuoteBtn').on('click', function() {
                const templateId = $('#emailTemplateSelect').val();
                const toEmail = $('#emailTo').val().trim();
                const fromEmail = $('#emailFrom').val().trim();
                const subject = $('#emailSubject').val().trim();
                const body = $('#emailBody').val().trim();

                if (!templateId) {
                    showNotification('Please select a template', 'error');
                    return;
                }
                if (!toEmail) {
                    showNotification('To Email is required', 'error');
                    return;
                }
                if (!fromEmail) {
                    showNotification('From Email is required', 'error');
                    return;
                }
                if (!subject) {
                    showNotification('Subject is required', 'error');
                    return;
                }
                if (!body) {
                    showNotification('Email body is required', 'error');
                    return;
                }

                $.ajax({
                    url: '{{ route('quotation.email.send') }}',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        booking_no: bookingNo,
                        quote_id: quotationId,
                        template_id: templateId,
                        to_email: toEmail,
                        from_email: fromEmail,
                        subject: subject,
                        body: body
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
                        showNotification(xhr.responseJSON?.message || 'Failed to send email',
                            'error');
                    },
                    complete: function() {
                        $('#sendEmailQuoteBtn').prop('disabled', false)
                            .html('<i class="fas fa-paper-plane mr-1"></i> Send');
                    }
                });
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
                            '<tr><td colspan="2" class="text-center text-muted"><i class="fas fa-spinner fa-spin"></i> Loading...</td></tr>'
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
                    tbody.html('<tr><td colspan="2" class="text-center text-muted">No emails sent yet</td></tr>');
                    return;
                }

                emails.forEach(function(email) {
                    tbody.append(`
                        <tr>
                            <td>${email.sent_on  ?? 'N/A'}</td>
                            <td>${email.to_email ?? 'N/A'}</td>
                        </tr>
                    `);
                });
            }

            $('#emailQuoteModal .close, #emailQuoteModal button[data-dismiss="modal"]').on('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                $('#emailQuoteModal').modal('hide');
            });

            $('#emailQuoteModal').on('hidden.bs.modal', function() {
                $('#emailQuoteForm')[0].reset();
                $('#emailBody').val('');
            });

            loadEmailHistory();


            // ============================================
            // CREATE BOOKING MODAL
            // ============================================
            $('#cancelCreateBookingBtn').on('click', function(e) {
                e.preventDefault();
                $('#createBookingModal').modal('hide');
            });

            $('#createBookingModal').on('hidden.bs.modal', function() {
                $('#createBookingForm')[0].reset();
            });

            $('#submitCreateBookingBtn').on('click', function() {
                const serviceTemplate = $('#cb_service_template').val();
                const billToParty = $('#cb_bill_to_party').val();
                const forwarder = $('#cb_forwarder').val();
                const placeOfReceipt = $('#cb_place_of_receipt').val();
                const placeOfDelivery = $('#cb_place_of_delivery').val();

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
                    url: '{{ route('quotation.booking.create') }}', // TODO: add this route when ready
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        booking_no: bookingNo,
                        quote_id: quotationId,
                        service_template: serviceTemplate,
                        bill_to_party: billToParty,
                        forwarder: forwarder,
                        include_country: $('#cb_include_country').is(':checked') ? 1 : 0,
                        place_of_receipt: placeOfReceipt,
                        place_of_delivery: placeOfDelivery
                    },
                    beforeSend: function() {
                        $('#submitCreateBookingBtn').prop('disabled', true)
                            .html('<i class="fas fa-spinner fa-spin mr-1"></i> Creating...');
                    },
                    success: function(response) {
                        if (response.success) {
                            showNotification('Booking created successfully', 'success');
                            $('#createBookingModal').modal('hide');
                            // TODO: redirect to the new booking or reload page as needed
                        } else {
                            showNotification(response.message || 'Failed to create booking',
                                'error');
                        }
                    },
                    error: function(xhr) {
                        showNotification(xhr.responseJSON?.message ||
                            'Failed to create booking', 'error');
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
            // AGENT TYPE FETCH
            // ============================================
            const SAVED_AGENT_TYPE = '{{ $quotation->AgentType ?? '' }}';

            function fetchAgentTypes() {
                const $select = $('#agentTypeSelect');

                $.ajax({
                    url: '/quotation/agent-types',
                    type: 'GET',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success && Array.isArray(response.data) && response.data.length >
                            0) {
                            $select.empty().append('<option value="">-- Select Agent Type --</option>');
                            response.data.forEach(function(item) {
                                const id = item.Id ?? item.id;
                                const name = item.Name ?? item.name;
                                const selected = String(id) === String(SAVED_AGENT_TYPE) ?
                                    'selected' : '';
                                $select.append(
                                    `<option value="${id}" ${selected}>${name}</option>`);
                            });
                        } else {
                            $select.empty().append(
                                '<option value="">-- No agent types available --</option>');
                        }
                    },
                    error: function() {
                        $select.empty().append('<option value="">-- Failed to load --</option>');
                        console.error('Failed to fetch agent types');
                    }
                });
            }

            fetchAgentTypes();

            // ============================================
            // HELPER FUNCTIONS
            // ============================================
            function clearModalForm(type) {
                const $modal = type === 'revenue' ? $('#addRevenueModal') : $('#addCostModal');
                $modal.find('input[type="text"], input[type="number"]').val('');
                $modal.find('input[type="number"]').val('0');
                $modal.find('select').prop('selectedIndex', 0);
                $modal.find('input[type="checkbox"]').prop('checked', false);

                if (type === 'revenue') {
                    $modal.find('.increase-by-row').removeClass('active').css('display', 'none');
                    $modal.find('.increase-by-row input[type="number"]').val('0.00');
                } else {
                    $modal.find('.cost-markup-row').removeClass('active').css('display', 'none');
                    $modal.find('.cost-markup-row input[type="number"]').val('0.00');
                }

                loadBasedOnList(type);
                populateCurrencyDropdown(type);
            }

            function clearRevenueModalForm() {
                clearModalForm('revenue');
                $('#revenueTypeRevenue').prop('checked', true);
                toggleRevenueFormFields('revenue');
                $('#addRevenueModal .increase-by-row').removeClass('active').css('display', 'none');
            }

            function clearCostModalForm() {
                clearModalForm('cost');
                $('#costTypeCost').prop('checked', true);
                toggleCostFormFields('cost');
                $('#addCostModal .cost-markup-row').removeClass('active').css('display', 'none');
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
                if ($card.find('.card-title').text().trim() === 'Summary') return;

                $card.find('.card-body').hide();
                $card.find('[data-card-widget="collapse"] i').removeClass('fa-chevron-up').addClass(
                    'fa-chevron-down');
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
                        office_code: codeToUse
                    },
                    success: function(response) {
                        const $select = $('#termsTemplateSelect');
                        $select.empty();

                        if (response.success && response.templates.length > 0) {
                            $select.append('<option value="">-- Select Template --</option>');
                            response.templates.forEach(function(t) {
                                $select.append(`<option value="${t.Name}">${t.Name}</option>`);
                            });

                            @if ($quotation->terms_condition)
                                $('#termsDescriptionPreview').val(
                                    '{{ addslashes($quotation->terms_condition) }}');
                                $('#termsDescriptionWrapper').show();
                            @endif
                        } else {
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
                                `<option value="" disabled>-- Failed to load templates --</option>`);
                        }

                        console.warn('loadTermsTemplates error:', response?.message || xhr.status);
                    }
                });
            }

            $('#termsTemplateSelect').on('change', function() {
                const selectedName = $(this).val();

                if (!selectedName) {
                    $('#termsDescriptionWrapper').hide();
                    $('#termsDescriptionPreview').val('');
                    return;
                }

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
                        if (response.success) {
                            $('#termsDescriptionPreview').val(response.description);
                        } else {
                            $('#termsDescriptionPreview').val('Failed to load description.');
                            showNotification('Could not load T&C description', 'warning');
                        }
                    },
                    error: function() {
                        $('#termsDescriptionPreview').val('Error loading description.');
                        showNotification('Error loading T&C description', 'error');
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
                        terms_condition: description
                    },
                    beforeSend: function() {
                        $('#applyTermsBtn').prop('disabled', true)
                            .html('<i class="fas fa-spinner fa-spin mr-1"></i> Saving...');
                    },
                    success: function(response) {
                        if (response.success) {
                            showNotification('Terms & Conditions applied successfully',
                                'success');
                        } else {
                            showNotification(response.message || 'Failed to apply Terms',
                                'error');
                        }
                    },
                    error: function(xhr) {
                        showNotification(xhr.responseJSON?.message || 'Failed to apply Terms',
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

        }); // end document.ready
    </script>
@stop
