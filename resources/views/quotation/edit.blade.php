@extends('adminlte::page')

@section('title', 'Edit Booking Quotation')

@section('content_header')
    <h1>Inquiry No - {{ $quotation->bookingno ?? 'N/A' }}</h1>
@stop

@section('content')
    <form id="bookingQuotationForm">
        @csrf

        <div class="row">
            <!-- LEFT COLUMN -->
            <div class="col-md-4">

                <!-- Booking Details -->
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
                <div class="card card-primary">
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
                <div class="card card-primary">
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

                <!-- Quotation Document -->
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title">Quotation Document</h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
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
                        <div class="form-group row mb-0">
                            <label class="col-sm-4 col-form-label">Version 4</label>
                            <div class="col-sm-8">
                                <a href="#" class="text-primary">DocumentDownloadLink</a>
                            </div>
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

            <!-- RIGHT COLUMN -->
            <div class="col-md-8">

                <!-- Customer Details -->
                <div class="card card-primary">
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
                    </div>
                </div>

                <!-- Revenue Section -->
                <div class="card card-success">
                    <div class="card-header">
                        <h3 class="card-title">Revenue</h3>
                        <div class="card-tools">
                            <a href="#" class="text-white show-items-link">Show Items</a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm table-hover">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Charge Name</th>
                                        <th>Rate</th>
                                        <th>Based On</th>
                                        <th>Amount</th>
                                        <th>Base Amount</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="revenueChargesTable">
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">Loading charges...</td>
                                    </tr>
                                </tbody>
                                <tfoot>
                                    <tr class="font-weight-bold">
                                        <td colspan="3" class="text-right">TOTAL REVENUE</td>
                                        <td id="totalRevenueAmount">0.00</td>
                                        <td id="totalRevenueBaseAmount">0.00</td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        <div class="mt-2">
                            <button type="button" class="btn btn-primary btn-sm" id="openChargesModal">
                                <i class="fas fa-plus"></i> Add Charges
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Cost Section -->
                <div class="card card-danger">
                    <div class="card-header">
                        <h3 class="card-title">Cost</h3>
                        <div class="card-tools">
                            <a href="#" class="text-white show-items-link">Show Items</a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm table-hover">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Charge Name</th>
                                        <th>Rate</th>
                                        <th>Based On</th>
                                        <th>Amount</th>
                                        <th>Base Amount</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="costChargesTable">
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">Loading charges...</td>
                                    </tr>
                                </tbody>
                                <tfoot>
                                    <tr class="font-weight-bold">
                                        <td colspan="3" class="text-right">TOTAL COST</td>
                                        <td id="totalCostAmount">0.00</td>
                                        <td id="totalCostBaseAmount">0.00</td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Summary -->
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title">Summary</h3>
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
                                <div class="form-group row">
                                    <label class="col-sm-5 col-form-label">Actual Revenue</label>
                                    <div class="col-sm-7">
                                        <input type="text" class="form-control form-control-sm" id="actualRevenue"
                                            value="N/A" readonly>
                                    </div>
                                </div>
                                <div class="form-group row mb-0">
                                    <label class="col-sm-5 col-form-label">Terms & Condition</label>
                                    <div class="col-sm-7">
                                        <input type="text" class="form-control form-control-sm"
                                            value="{{ $quotation->terms_condition ?? 'N/A' }}" readonly>
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
                                <button type="button" class="btn btn-primary" id="generateQuoteBtn">
                                    <i class="fas fa-file-alt"></i> Generate Quote
                                </button>
                                <button type="button" class="btn btn-primary" id="emailQuoteBtn">
                                    <i class="fas fa-envelope"></i> Email Quote
                                </button>
                                <a href="{{ route('quotation.index') }}" class="btn btn-link">Close</a>

                                <div class="float-right">
                                    <button type="button" class="btn btn-warning" id="cancelQuoteBtn">
                                        <i class="fas fa-ban"></i> Cancel
                                    </button>
                                    <button type="button" class="btn btn-danger" id="lostQuoteBtn">
                                        <i class="fas fa-times"></i> LOST
                                    </button>
                                    <button type="button" class="btn btn-success" id="wonQuoteBtn">
                                        <i class="fas fa-check"></i> WON
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- MODALS - EXACT COPY FROM DOCUMENT 2 WITH YOUR NAMING -->

        {{-- REVENUE MODAL - Using Document 2 structure with your field names --}}
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

                            <!-- Top Row: Revenue/Cost + Base Currency -->
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
                                <!-- Left Column -->
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="revenue_charge_code" class="form-label"><span
                                                class="text-danger">*</span>
                                            Charge Code</label>
                                        <input type="text" class="form-control" id="revenue_charge_code"
                                            name="revenue_charge_code" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="revenue_bill_to_party" class="form-label">
                                            Bill to Party</label>
                                        <input type="text" class="form-control" id="revenue_bill_to_party"
                                            name="revenue_bill_to_party">
                                        <input type="hidden" class="form-control" id="revenue_bill_to_party_code"
                                            name="revenue_bill_to_party_code" />
                                    </div>
                                    <div class="mb-3">
                                        <label for="revenue_based_on" class="form-label"><span
                                                class="text-danger">*</span> Based
                                            On</label>
                                        <select class="form-select form-control" id="revenue_based_on"
                                            name="revenue_based_on" required>
                                            <option value="">Select</option>
                                        </select>
                                    </div>

                                    <!-- Quantity only -->
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
                                            <label for="revenue_exchange_rate" class="form-label"><span
                                                    class="text-danger">*</span> Exchange Rate</label>
                                            <input type="number" class="form-control" id="revenue_exchange_rate"
                                                name="revenue_exchange_rate" readonly>
                                        </div>
                                        <div class="col-6">
                                            <label for="revenue_base_amount" class="form-label text-nowrap">Amount in
                                                Base Currency <span class="text-muted">(KES)</span></label>
                                            <input type="number" class="form-control" id="revenue_base_amount"
                                                name="revenue_base_amount" step="0.01" value="0.00" readonly>
                                        </div>
                                    </div>
                                </div>

                                <!-- Right Column -->
                                <div class="col-md-6">
                                    <!-- Remark moved here -->
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
                                        <label for="revenue_based_on_uom" class="form-label"><span
                                                class="text-danger">*</span> Based
                                            On UOM</label>
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

                                    <!-- Checkboxes -->
                                    <div class="mb-3">
                                        <div class="form-check mb-2" id="revenueIsReimbursedContainer"
                                            style="display:none;">
                                            <input class="form-check-input" type="checkbox" id="revenueIsReimbursed"
                                                name="revenue_is_reimbursed">
                                            <label class="form-check-label fw-semibold" for="revenueIsReimbursed">
                                                Is Reimbursed <small class="text-muted">(Adds to Revenue)</small>
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="revenueIsAccrued"
                                                name="revenue_is_accrued">
                                            <label class="form-check-label fw-semibold" for="revenueIsAccrued">
                                                Is Accrued <small class="text-muted">(Adds to Cost)</small>
                                            </label>
                                        </div>

                                        <div class="mb-3" id="revenueSecondaryBillToPartyContainer"
                                            style="display:none;">
                                            <label for="revenueSecondaryBillToParty" class="form-label"><span
                                                    class="text-danger">*</span> Secondary Bill To Party</label>
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

        {{-- COST MODAL - Using Document 2 structure with your field names --}}
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

                            <!-- Top Row: Revenue/Cost + Base Currency -->
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
                                <!-- Left Column -->
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="cost_charge_code" class="form-label"><span
                                                class="text-danger">*</span>
                                            Charge Code</label>
                                        <input type="text" class="form-control" id="cost_charge_code"
                                            name="cost_charge_code" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="cost_bill_to_party" class="form-label">
                                            Bill to Party</label>
                                        <input type="text" class="form-control" id="cost_bill_to_party"
                                            name="cost_bill_to_party">
                                        <input type="hidden" class="form-control" id="cost_bill_to_party_code"
                                            name="cost_bill_to_party_code" />
                                    </div>
                                    <div class="mb-3">
                                        <label for="cost_based_on" class="form-label"><span class="text-danger">*</span>
                                            Based
                                            On</label>
                                        <select class="form-select form-control" id="cost_based_on" name="cost_based_on"
                                            required>
                                            <option value="">Select</option>
                                        </select>
                                    </div>

                                    <!-- Quantity only -->
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

                                    <div class="row mb-3">
                                        <div class="col-6">
                                            <label for="cost_exchange_rate" class="form-label"><span
                                                    class="text-danger">*</span> Exchange Rate</label>
                                            <input type="number" class="form-control" id="cost_exchange_rate"
                                                name="cost_exchange_rate" readonly>
                                        </div>
                                        <div class="col-6">
                                            <label for="cost_base_amount" class="form-label text-nowrap">Amount in
                                                Base Currency <span class="text-muted">(KES)</span></label>
                                            <input type="number" class="form-control" id="cost_base_amount"
                                                name="cost_base_amount" step="0.01" value="0.00" readonly>
                                        </div>
                                    </div>
                                </div>

                                <!-- Right Column -->
                                <div class="col-md-6">
                                    <!-- Remark moved here -->
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
                                        <label for="cost_based_on_uom" class="form-label"><span
                                                class="text-danger">*</span> Based
                                            On UOM</label>
                                        <select class="form-select form-control" id="cost_based_on_uom"
                                            name="cost_based_on_uom" required>
                                            <option value="">Select</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="cost_remark" class="form-label">Remark</label>
                                        <input type="text" class="form-control" id="cost_remark" name="cost_remark">
                                    </div>

                                    <!-- Checkboxes -->
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

                                        <div class="mb-3" id="costSecondaryBillToPartyContainer" style="display:none;">
                                            <label for="costSecondaryBillToParty" class="form-label"><span
                                                    class="text-danger">*</span> Secondary Bill To Party</label>
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
            border-left: 0;
            flex: 0 0 auto;
            width: auto;
            min-width: 80px;
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

        /* Increase-by row (for costmarkup mode) */
        .increase-by-row {
            display: none;
        }

        /* General form styling */
        .form-control-sm {
            height: calc(1.5em + 0.5rem + 2px);
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }

        /* Autocomplete dropdown */
        /* Autocomplete dropdown */
        .ui-autocomplete {
            z-index: 99999 !important;
            /* Increased from 9999 */
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
            background-color: #513d6b !important;
            color: white !important;
        }

        .card-success>.card-header,
        .card.card-success>.card-header {
            background-color: #513d6b !important;
            color: white !important;
        }

        .card-danger>.card-header,
        .card.card-danger>.card-header {
            background-color: #513d6b !important;
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
    </style>
@stop



@section('js')
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>

    <script>
        $(document).ready(function() {
            console.log('Booking Quotation Edit - Page Loaded');

            // ============================================
            // GLOBAL VARIABLES
            // ============================================
            const bookingNo = '{{ $quotation->bookingno ?? '' }}';
            const quotationStatus = '{{ $quotation->status ?? 'Proposal' }}';
            const teamId = '{{ $quotation->teamid ?? '' }}'; // NEW: For office lookup
            let revenueCharges = [];
            let costCharges = [];
            let currentEditingCharge = null;
            let currentEditingType = null;
            let currencyMap = {}; // NEW: Maps currency code → currency ID
            let officeCode = ''; // NEW: Store office code globally

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
                        }
                    },
                    error: function(xhr) {
                        console.error('Failed to load office code:', xhr);
                        // Fallback: will be fetched in modal if needed
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
                    error: function(xhr) {
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
            $('#addRevenueModal').on('shown.bs.modal', function() {
                if (!currentEditingCharge) {
                    $('#revenueTypeRevenue').prop('checked', true);
                    toggleRevenueFormFields('revenue');
                    loadBasedOnList('revenue');
                    populateCurrencyDropdown('revenue');
                }

                // Initialize autocomplete with a small delay to ensure modal is fully rendered
                setTimeout(function() {
                    initializeChargeCodeAutocomplete('revenue');
                }, 100);
            });

            $('#addCostModal').on('shown.bs.modal', function() {
                if (!currentEditingCharge) {
                    $('#costTypeCost').prop('checked', true);
                    toggleCostFormFields('cost');
                    loadBasedOnList('cost');
                    populateCurrencyDropdown('cost');
                }

                // Initialize autocomplete with a small delay to ensure modal is fully rendered
                setTimeout(function() {
                    initializeChargeCodeAutocomplete('cost');
                }, 100);
            });

            // ============================================
            // POPULATE CURRENCY DROPDOWN (NEW FEATURE)
            // ============================================
            function populateCurrencyDropdown(type) {
                const prefix = type === 'revenue' ? 'revenue' : 'cost';
                const $currencySelect = $(`select[name="${prefix}_currency"]`);

                // If already populated, skip
                if ($currencySelect.data('populated')) {
                    return;
                }

                // Get office code if not loaded yet
                if (!officeCode && bookingNo) {
                    // Fetch office code inline if needed
                    $.ajax({
                        url: '/quotation/office-code',
                        type: 'GET',
                        data: {
                            team_id: teamId
                        },
                        async: false, // Synchronous for this critical step
                        success: function(response) {
                            if (response.success) {
                                officeCode = response.office_code.trim();
                            }
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
                        $currencySelect.prop('disabled', true);
                        $currencySelect.empty().append('<option value="">Loading...</option>');
                    },
                    success: function(response) {
                        if (response.success && response.currencies && response.currencies.length > 0) {
                            $currencySelect.empty().append('<option value="">Select</option>');

                            // Build currency map and populate dropdown
                            currencyMap = {};
                            response.currencies.forEach(function(currency) {
                                currencyMap[currency.CurrencyCode] = currency.CurrencyID;
                                $currencySelect.append(
                                    `<option value="${currency.CurrencyID}" data-code="${currency.CurrencyCode}">${currency.CurrencyCode}</option>`
                                );
                            });

                            $currencySelect.data('populated', true);
                            $currencySelect.prop('disabled', false);
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
            // AUTO-FETCH EXCHANGE RATE ON CURRENCY CHANGE (NEW FEATURE)
            // ============================================
            $(document).on('change', 'select[name="revenue_currency"], select[name="cost_currency"]', function() {
                const $currencySelect = $(this);
                const prefix = $currencySelect.attr('name').split('_')[0]; // 'revenue' or 'cost'
                const currencyCode = $currencySelect.find('option:selected').data('code');
                const $exchangeRateInput = $(`input[name="${prefix}_exchange_rate"]`);

                if (!currencyCode || !officeCode) {
                    console.warn('Currency code or office code missing');
                    return;
                }

                // If currency is KES (base currency), set exchange rate to 1
                if (currencyCode === 'KES') {
                    $exchangeRateInput.val('1.000000');
                    calculateAmount(prefix);
                    return;
                }

                // Fetch exchange rate
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
                    error: function(xhr) {
                        showNotification('Failed to load Based On list', 'error');
                        $basedOnSelect.empty().append('<option value="">Failed to load</option>').prop(
                            'disabled', false);
                    }
                });
            }

            // ============================================
            // LOAD UOM WHEN BASED ON CHANGES (ENHANCED)
            // ============================================
            $(document).on('change', 'select[name="revenue_based_on"], select[name="cost_based_on"]', function() {
                const $select = $(this);
                const prefix = $select.attr('name').split('_')[0]; // 'revenue' or 'cost'
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

                            // If no UOM required (e.g., AssessableValue, CommercialInvoiceVal)
                            if (!response.uom_list || response.uom_list.length === 0) {
                                $uomSelect.append('<option value="">No UOM Required</option>');
                                $uomSelect.prop('disabled', true); // Disable since not needed
                                console.log('No UOM required for:', basedOnCode);
                            } else {
                                // UOM options available
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
                    error: function(xhr) {
                        console.error('Failed to load UOM list:', xhr);
                        showNotification('Failed to load UOM list', 'error');
                        $uomSelect.empty().append('<option value="">Error loading</option>');
                    }
                });
            }

            // ============================================
            // CHARGE CODE AUTOCOMPLETE (ENHANCED)
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
                        const chargeType = type === 'revenue' ? 'Revenue' : 'Cost';

                        if (request.term.length < 3) {
                            response([]);
                            return;
                        }

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

                        // Populate fields
                        $chargeCodeInput.val(ui.item.value);
                        $(`input[name="${prefix}_description"]`).val(ui.item.name);

                        // Set currency by mapping code to ID
                        if (ui.item.currency && currencyMap[ui.item.currency]) {
                            const currencyId = currencyMap[ui.item.currency];
                            const $currencySelect = $(`select[name="${prefix}_currency"]`);
                            $currencySelect.val(currencyId);
                            $currencySelect.trigger('change'); // Trigger exchange rate fetch
                        }

                        // Set VAT
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
            // CHARGE TYPE TOGGLES
            // ============================================
            $('input[name="revenueChargeType"]').on('change', function() {
                toggleRevenueFormFields($(this).val());
            });

            $('input[name="costChargeType"]').on('change', function() {
                toggleCostFormFields($(this).val());
            });

            function toggleRevenueFormFields(type) {
                const $modal = $('#addRevenueModal');
                let $increaseByRow = $modal.find('.increase-by-row');
                const $isReimbursedContainer = $('#revenueIsReimbursedContainer'); // FIX THIS

                if (type === 'costmarkup') {
                    if ($increaseByRow.length === 0) {
                        const increaseByHtml = `
                <div class="row mb-3 increase-by-row">
                    <div class="col-6">
                        <label for="revenue_increase_by" class="form-label">Increase By</label>
                        <div class="input-group">
                            <input type="number" class="form-control" id="revenue_increase_by" name="revenue_increase_by" step="0.01">
                            <select class="form-select form-control" id="revenue_increase_type" name="revenue_increase_type">
                                <option value="Percentage">Percentage</option>
                                <option value="Fixed">Fixed</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-6">
                        <label for="revenue_markup_amount" class="form-label">Amount</label>
                        <input type="number" class="form-control" id="revenue_markup_amount" name="revenue_markup_amount" step="0.01" value="0.00" readonly>
                    </div>
                </div>
            `;
                        $modal.find('.rate-row').after(increaseByHtml);
                    }
                    $increaseByRow = $modal.find('.increase-by-row');
                    $increaseByRow.show();
                    $isReimbursedContainer.hide(); // Hide "Is Reimbursed" for markup
                } else if (type === 'cost') {
                    $increaseByRow.hide();
                    $isReimbursedContainer.show(); // Show "Is Reimbursed" for cost
                } else { // revenue
                    $increaseByRow.hide();
                    $isReimbursedContainer.hide(); // Hide for revenue
                }
            }

            function toggleCostFormFields(type) {
                const $modal = $('#addCostModal');
                let $increaseByRow = $modal.find('.increase-by-row');
                const $isReimbursedContainer = $('#costIsReimbursed').closest('.form-check');

                if (type === 'costmarkup') {
                    if ($increaseByRow.length === 0) {
                        const increaseByHtml = `
                <div class="row mb-3 increase-by-row">
                    <div class="col-6">
                        <label for="cost_increase_by" class="form-label">Increase By</label>
                        <div class="input-group">
                            <input type="number" class="form-control" id="cost_increase_by" name="cost_increase_by" step="0.01">
                            <select class="form-select form-control" id="cost_increase_type" name="cost_increase_type">
                                <option value="Percentage">Percentage</option>
                                <option value="Fixed">Fixed</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-6">
                        <label for="cost_markup_amount" class="form-label">Amount</label>
                        <input type="number" class="form-control" id="cost_markup_amount" name="cost_markup_amount" step="0.01" value="0.00" readonly>
                    </div>
                </div>
            `;
                        $modal.find('.rate-row').after(increaseByHtml);
                    }
                    $increaseByRow = $modal.find('.increase-by-row');
                    $increaseByRow.show();
                    $isReimbursedContainer.hide();
                } else {
                    $increaseByRow.hide();
                    $isReimbursedContainer.show();
                }
            }

            // ============================================
            // AUTO-CALCULATE AMOUNTS (ENHANCED)
            // ============================================
            $('#addRevenueModal').on('input change',
                'input[name="revenue_quantity"], input[name="revenue_rate"], input[name="revenue_exchange_rate"], input[name="revenue_increase_by"], select[name="revenue_increase_type"]',
                function() {
                    calculateAmount('revenue');
                });

            $('#addCostModal').on('input change',
                'input[name="cost_quantity"], input[name="cost_rate"], input[name="cost_exchange_rate"], input[name="cost_increase_by"], select[name="cost_increase_type"]',
                function() {
                    calculateAmount('cost');
                });

            function calculateAmount(prefix) {
                const $modal = $(`#add${prefix.charAt(0).toUpperCase() + prefix.slice(1)}Modal`);
                const quantity = parseFloat($modal.find(`input[name="${prefix}_quantity"]`).val()) || 0;
                const rate = parseFloat($modal.find(`input[name="${prefix}_rate"]`).val()) || 0;
                const exchangeRate = parseFloat($modal.find(`input[name="${prefix}_exchange_rate"]`).val()) || 1;

                const amount = quantity * rate;
                const baseAmount = amount * exchangeRate;

                $modal.find(`input[name="${prefix}_amount"]`).val(amount.toFixed(2));
                $modal.find(`input[name="${prefix}_base_amount"]`).val(baseAmount.toFixed(2));

                // Handle markup calculation if applicable
                const chargeType = $modal.find(`input[name="${prefix}ChargeType"]:checked`).val();
                if (chargeType === 'costmarkup') {
                    const increaseBy = parseFloat($modal.find(`input[name="${prefix}_increase_by"]`).val()) || 0;
                    const increaseType = $modal.find(`select[name="${prefix}_increase_type"]`).val();

                    let markupAmount = 0;
                    if (increaseType === 'Percentage') {
                        markupAmount = amount + (amount * increaseBy / 100);
                    } else {
                        markupAmount = amount + increaseBy;
                    }

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

            function saveCharge(type, addMore) {
                const $modal = type === 'revenue' ? $('#addRevenueModal') : $('#addCostModal');
                const chargeType = $modal.find('input[name="' + type + 'ChargeType"]:checked').val();

                let revenueOrCost = 'Revenue';
                if (chargeType === 'cost') {
                    revenueOrCost = 'Cost';
                } else if (chargeType === 'costmarkup') {
                    revenueOrCost = 'Cost with Markup';
                }

                const chargeData = {
                    _token: '{{ csrf_token() }}',
                    action: currentEditingCharge ? 'Edit' : 'Add',
                    booking_no: bookingNo,
                    quote_charge_id: currentEditingCharge || '',
                    party_code: '',
                    revenue_or_cost: revenueOrCost,
                    charge_code: $modal.find('input[name="' + type + '_charge_code"]').val(),
                    quantity: parseFloat($modal.find('input[name="' + type + '_quantity"]').val()) || 0,
                    rate: parseFloat($modal.find('input[name="' + type + '_rate"]').val()) || 0,
                    amt_in_chg_curr: parseFloat($modal.find('input[name="' + type + '_amount"]').val()) || 0,
                    amt_in_base_curr: parseFloat($modal.find('input[name="' + type + '_base_amount"]').val()) ||
                        0,
                    exchange_rate: parseFloat($modal.find('input[name="' + type + '_exchange_rate"]').val()) ||
                        1,
                    chg_curr: $modal.find('select[name="' + type + '_currency"] option:selected').data(
                        'code') || 'KES',
                    based_on_code: $modal.find('select[name="' + type + '_based_on"]').val() || '',
                    based_on_uom: $modal.find('select[name="' + type + '_based_on_uom"]').val() || '',
                    charge_remark: $modal.find('input[name="' + type + '_remark"]').val() || '',
                    to_be_added: chargeType === 'cost' || chargeType === 'revenue' ? 1 : 0,
                    increase_by: 0,
                    increase_type: 'Percentage',
                    source_type: 'manual'
                };

                if (chargeType === 'costmarkup') {
                    chargeData.increase_by = parseFloat($modal.find('input[name="' + type + '_increase_by"]')
                        .val()) || 0;
                    chargeData.increase_type = $modal.find('select[name="' + type + '_increase_type"]').val() ||
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

                // Set currency
                if (charge.chgCurr && currencyMap[charge.chgCurr]) {
                    $modal.find(`select[name="${type}_currency"]`).val(currencyMap[charge.chgCurr]);
                }

                // Load dropdowns
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
                    tbody.append('<tr><td colspan="6" class="text-center text-muted">No charges added</td></tr>');
                } else {
                    revenueCharges.forEach(function(charge, index) {
                        totalAmount += parseFloat(charge.amtInChgCurr || 0);
                        totalBaseAmount += parseFloat(charge.amtInBaseCurr || 0);
                        tbody.append(`
                            <tr>
                                <td>${charge.chargeName || charge.chargeCode || 'N/A'}</td>
                                <td class="text-right">${parseFloat(charge.rate || 0).toFixed(2)}</td>
                                <td>${charge.basedOn || 'N/A'}</td>
                                <td class="text-right">${charge.chgCurr || 'KES'} ${parseFloat(charge.amtInChgCurr || 0).toFixed(2)}</td>
                                <td class="text-right">KES ${parseFloat(charge.amtInBaseCurr || 0).toFixed(2)}</td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-warning btn-action edit-revenue-charge" data-index="${index}" data-id="${charge.id}"><i class="fas fa-edit"></i></button>
                                    <button type="button" class="btn btn-danger btn-action delete-revenue-charge" data-index="${index}" data-id="${charge.id}"><i class="fas fa-trash"></i></button>
                                </td>
                            </tr>
                        `);
                    });
                }

                $('#totalRevenueAmount').text('KES ' + totalAmount.toFixed(2));
                $('#totalRevenueBaseAmount').text('KES ' + totalBaseAmount.toFixed(2));
            }

            function renderCostCharges() {
                const tbody = $('#costChargesTable');
                tbody.empty();
                let totalAmount = 0,
                    totalBaseAmount = 0;

                if (costCharges.length === 0) {
                    tbody.append('<tr><td colspan="6" class="text-center text-muted">No charges added</td></tr>');
                } else {
                    costCharges.forEach(function(charge, index) {
                        totalAmount += parseFloat(charge.amtInChgCurr || 0);
                        totalBaseAmount += parseFloat(charge.amtInBaseCurr || 0);
                        tbody.append(`
                            <tr>
                                <td>${charge.chargeName || charge.chargeCode || 'N/A'}</td>
                                <td class="text-right">${parseFloat(charge.rate || 0).toFixed(2)}</td>
                                <td>${charge.basedOn || 'N/A'}</td>
                                <td class="text-right">${charge.chgCurr || 'KES'} ${parseFloat(charge.amtInChgCurr || 0).toFixed(2)}</td>
                                <td class="text-right">KES ${parseFloat(charge.amtInBaseCurr || 0).toFixed(2)}</td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-warning btn-action edit-cost-charge" data-index="${index}" data-id="${charge.id}"><i class="fas fa-edit"></i></button>
                                    <button type="button" class="btn btn-danger btn-action delete-cost-charge" data-index="${index}" data-id="${charge.id}"><i class="fas fa-trash"></i></button>
                                </td>
                            </tr>
                        `);
                    });
                }

                $('#totalCostAmount').text('KES ' + totalAmount.toFixed(2));
                $('#totalCostBaseAmount').text('KES ' + totalBaseAmount.toFixed(2));
            }

            // ============================================
            // UPDATE SUMMARY
            // ============================================
            function updateSummary() {
                const totalRevenue = revenueCharges.reduce((sum, charge) => sum + parseFloat(charge.amtInBaseCurr ||
                    0), 0);
                const totalCost = costCharges.reduce((sum, charge) => sum + parseFloat(charge.amtInBaseCurr || 0),
                    0);
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
                if (confirm('Mark this quotation as WON?')) {
                    updateStatus('Won', '', '');
                }
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
                                            <option value="">Select Reason</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>Remarks</label>
                                        <textarea class="form-control" id="reasonRemark" rows="3"></textarea>
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
                        if (response.success && response.reasons) {
                            response.reasons.forEach(function(reason) {
                                $('#reasonCode').append(
                                    `<option value="${reason.Code}">${reason.Description}</option>`
                                );
                            });
                        }
                    }
                });

                $('#statusModal').modal('show');

                $('#confirmStatus').on('click', function() {
                    const reasonCode = $('#reasonCode').val();
                    const reasonRemark = $('#reasonRemark').val();

                    if (!reasonCode) {
                        showNotification('Please select a reason', 'error');
                        return;
                    }

                    updateStatus(status, reasonCode, reasonRemark);
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

            // ============================================
            // XML VIEWER
            // ============================================
            $('#viewXmlBtn').on('click', function(e) {
                e.preventDefault();
                $('#xmlViewerModal').modal('show');
            });

            // ============================================
            // GENERATE & EMAIL QUOTE BUTTONS
            // ============================================
            $('#generateQuoteBtn').on('click', function() {
                showNotification('Generate Quote functionality coming soon', 'info');
            });

            $('#emailQuoteBtn').on('click', function() {
                showNotification('Email Quote functionality coming soon', 'info');
            });

            // ============================================
            // HELPER FUNCTIONS
            // ============================================
            function clearModalForm(type) {
                const $modal = type === 'revenue' ? $('#addRevenueModal') : $('#addCostModal');
                $modal.find('input[type="text"], input[type="number"]').val('');
                $modal.find('input[type="number"]').val('0');
                $modal.find('select').prop('selectedIndex', 0);
                $modal.find('input[type="checkbox"]').prop('checked', false);

                // Reload dropdowns
                loadBasedOnList(type);
                populateCurrencyDropdown(type);
            }

            function clearRevenueModalForm() {
                clearModalForm('revenue');
                $('#revenueTypeRevenue').prop('checked', true);
                toggleRevenueFormFields('revenue');
            }

            function clearCostModalForm() {
                clearModalForm('cost');
                $('#costTypeCost').prop('checked', true);
                toggleCostFormFields('cost');
            }

            function showNotification(message, type) {
                const alertClass = type === 'success' ? 'alert-success' :
                    type === 'error' ? 'alert-danger' :
                    type === 'warning' ? 'alert-warning' : 'alert-info';

                const notification = `
                    <div class="alert ${alertClass} alert-dismissible fade show notification-toast" role="alert" style="position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
                        <strong>${message}</strong>
                        <button type="button" class="close" data-dismiss="alert">
                            <span>&times;</span>
                        </button>
                    </div>
                `;

                $('body').append(notification);

                setTimeout(function() {
                    $('.notification-toast').fadeOut(function() {
                        $(this).remove();
                    });
                }, 3000);
            }

            // ============================================
            // CARD COLLAPSE & SHOW ITEMS
            // ============================================
            $('.show-items-link').on('click', function(e) {
                e.preventDefault();
                const $card = $(this).closest('.card');
                const $tableWrapper = $card.find('.table-responsive');

                if ($tableWrapper.is(':visible')) {
                    $tableWrapper.slideUp();
                    $(this).text('Show Items');
                } else {
                    $tableWrapper.slideDown();
                    $(this).text('Hide Items');
                }
            });

            $('[data-card-widget="collapse"]').on('click', function() {
                const icon = $(this).find('i');
                icon.toggleClass('fa-chevron-down fa-chevron-up');
            });

            $('.card-header').on('click', function(e) {
                if (!$(e.target).closest('[data-card-widget="collapse"]').length &&
                    !$(e.target).closest('.show-items-link').length) {
                    $(this).find('[data-card-widget="collapse"]').click();
                }
            });

            // Initialize collapsed cards (except Revenue and Cost)
            $('.card').not('.card-success, .card-danger').each(function() {
                const $card = $(this);
                const $cardBody = $card.find('.card-body');
                const $icon = $card.find('[data-card-widget="collapse"] i');

                $cardBody.hide();
                $icon.removeClass('fa-chevron-up').addClass('fa-chevron-down');
                $card.addClass('collapsed-card');
            });
        });
    </script>
@stop
