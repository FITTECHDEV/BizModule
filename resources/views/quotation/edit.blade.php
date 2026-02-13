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

                <!-- Revenue Section - CORRECTED -->
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

                <!-- Cost Section - CORRECTED -->
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

        <!-- MODALS -->

        <!-- Revenue Modal -->
        <div class="modal fade" id="addRevenueModal" tabindex="-1" role="dialog"
            aria-labelledby="addRevenueModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addRevenueModalLabel">Add Manual Charges</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <!-- Radio buttons for charge type -->
                        <div class="form-group">
                            <div class="row">
                                <div class="col-md-9">
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
                                <div class="col-md-3 text-right">
                                    <span class="text-muted">Base Currency: <strong>KES</strong></span>
                                </div>
                            </div>
                        </div>

                        <hr>

                        <!-- Form fields -->
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>* Charge Code</label>
                                    <input type="text" class="form-control form-control-sm" name="revenue_charge_code"
                                        required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Tax Applicable</label>
                                    <select class="form-control form-control-sm" name="revenue_tax_applicable">
                                        <option value="">Select</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Description</label>
                                    <input type="text" class="form-control form-control-sm"
                                        name="revenue_description">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>* Based On</label>
                                    <select class="form-control form-control-sm" name="revenue_based_on" required>
                                        <option value="">Select</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>* Based On UOM</label>
                                    <select class="form-control form-control-sm" name="revenue_based_on_uom" required>
                                        <option value="">Select</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Quantity</label>
                                    <input type="number" class="form-control form-control-sm" name="revenue_quantity"
                                        value="0" step="0.01">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Remark</label>
                                    <input type="text" class="form-control form-control-sm" name="revenue_remark">
                                </div>
                            </div>
                        </div>

                        <div class="row rate-row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Rate</label>
                                    <input type="number" class="form-control form-control-sm" name="revenue_rate"
                                        value="0" step="0.01">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Currency</label>
                                    <select class="form-control form-control-sm" name="revenue_currency">
                                        <option value="KES" selected>KES</option>
                                        <option value="USD">USD</option>
                                        <option value="EUR">EUR</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Amount</label>
                                    <input type="number" class="form-control form-control-sm" name="revenue_amount"
                                        value="0.00" step="0.01" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>* Exchange Rate</label>
                                    <input type="number" class="form-control form-control-sm"
                                        name="revenue_exchange_rate" value="1.00" step="0.01" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Amount in Base Currency (KES)</label>
                                    <input type="number" class="form-control form-control-sm" name="revenue_base_amount"
                                        value="0.00" step="0.01" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="row is-accrued-row">
                            <div class="col-md-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="revenueIsAccrued"
                                        name="revenue_is_accrued">
                                    <label class="form-check-label" for="revenueIsAccrued">
                                        Is Accrued <span class="text-muted">(Adds to Cost)</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-success btn-sm" id="saveRevenueCharge">
                            <i class="fas fa-save"></i> Save
                        </button>
                        <button type="button" class="btn btn-primary btn-sm" id="saveAddMoreRevenueCharge">
                            <i class="fas fa-plus"></i> Save & Add More
                        </button>
                        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">
                            <i class="fas fa-times"></i> Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cost Modal -->
        <div class="modal fade" id="addCostModal" tabindex="-1" role="dialog" aria-labelledby="addCostModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addCostModalLabel">Add Manual Charges</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <!-- Radio buttons for charge type -->
                        <div class="form-group">
                            <div class="row">
                                <div class="col-md-9">
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
                                        <label class="form-check-label" for="costTypeCostMarkup">Cost with
                                            markup</label>
                                    </div>
                                </div>
                                <div class="col-md-3 text-right">
                                    <span class="text-muted">Base Currency: <strong>KES</strong></span>
                                </div>
                            </div>
                        </div>

                        <hr>

                        <!-- Form fields for Cost -->
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>* Charge Code</label>
                                    <input type="text" class="form-control form-control-sm" name="cost_charge_code"
                                        required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Tax Applicable</label>
                                    <select class="form-control form-control-sm" name="cost_tax_applicable">
                                        <option value="">Select</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Description</label>
                                    <input type="text" class="form-control form-control-sm" name="cost_description">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>* Based On</label>
                                    <select class="form-control form-control-sm" name="cost_based_on" required>
                                        <option value="">Select</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>* Based On UOM</label>
                                    <select class="form-control form-control-sm" name="cost_based_on_uom" required>
                                        <option value="">Select</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Quantity</label>
                                    <input type="number" class="form-control form-control-sm" name="cost_quantity"
                                        value="0" step="0.01">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Remark</label>
                                    <input type="text" class="form-control form-control-sm" name="cost_remark">
                                </div>
                            </div>
                        </div>

                        <div class="row rate-row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Rate</label>
                                    <input type="number" class="form-control form-control-sm" name="cost_rate"
                                        value="0" step="0.01">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Currency</label>
                                    <select class="form-control form-control-sm" name="cost_currency">
                                        <option value="KES" selected>KES</option>
                                        <option value="USD">USD</option>
                                        <option value="EUR">EUR</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Amount</label>
                                    <input type="number" class="form-control form-control-sm" name="cost_amount"
                                        value="0.00" step="0.01" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="row increase-by-row" style="display: none;">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Increase By</label>
                                    <input type="number" class="form-control form-control-sm" name="cost_increase_by"
                                        value="0" step="0.01">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Type</label>
                                    <select class="form-control form-control-sm" name="cost_increase_type">
                                        <option value="Percentage">Percentage</option>
                                        <option value="Fixed">Fixed</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Revenue Amount</label>
                                    <input type="number" class="form-control form-control-sm" name="cost_revenue_amount"
                                        value="0.00" step="0.01" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>* Exchange Rate</label>
                                    <input type="number" class="form-control form-control-sm" name="cost_exchange_rate"
                                        value="1.00" step="0.01" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Amount in Base Currency (KES)</label>
                                    <input type="number" class="form-control form-control-sm" name="cost_base_amount"
                                        value="0.00" step="0.01" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="row is-accrued-row">
                            <div class="col-md-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="costIsAccrued"
                                        name="cost_is_accrued">
                                    <label class="form-check-label" for="costIsAccrued">
                                        Is Accrued <span class="text-muted">(Adds to Cost)</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-success btn-sm" id="saveCostCharge">
                            <i class="fas fa-save"></i> Save
                        </button>
                        <button type="button" class="btn btn-primary btn-sm" id="saveAddMoreCostCharge">
                            <i class="fas fa-plus"></i> Save & Add More
                        </button>
                        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">
                            <i class="fas fa-times"></i> Cancel
                        </button>
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
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">


    <style>
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

        /* Action buttons in table */
        .btn-action {
            padding: 0.15rem 0.4rem;
            font-size: 0.75rem;
        }

        /* Show Items link */
        .show-items-link {
            text-decoration: none;
            font-size: 0.875rem;
        }

        .show-items-link:hover {
            text-decoration: underline;
        }

        /* Card header colors - Updated to #513d6b */
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

        /* Small form controls for compact layout */
        .form-control-sm {
            height: calc(1.5em + 0.5rem + 2px);
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }

        /* Remove form control background - match left panel style */
        .form-control[readonly] {
            background-color: transparent;
            border: none;
            cursor: default;
            padding-left: 0;
            font-weight: 500;
            color: #333;
        }

        /* Remove focus outline on readonly fields */
        .form-control[readonly]:focus {
            outline: none;
            box-shadow: none;
        }

        /* Row spacing */
        .form-group.row {
            margin-bottom: 0.5rem;
        }

        /* Card body padding */
        .card-body {
            padding: 1rem;
        }

        /* Links in card headers */
        .card-tools a {
            text-decoration: underline;
        }

        .card-tools a:hover {
            opacity: 0.8;
        }

        /* Summary section specific styles */
        .card-body label.font-weight-bold {
            font-weight: 600;
            margin-bottom: 0.25rem;
            color: #333;
        }

        .card-body .form-group>div {
            color: #666;
            font-size: 0.9rem;
        }

        /* Button styling */
        .btn-primary {
            background-color: #5b9bd5;
            border-color: #5b9bd5;
        }

        .btn-primary:hover {
            background-color: #4a8bc2;
            border-color: #4a8bc2;
        }

        .btn-link {
            color: #007bff;
            text-decoration: underline;
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

        /* Modal styling - matching reference EXACTLY */
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

        .modal-header .btn-close,
        .modal-header .close {
            color: #000;
            opacity: 0.5;
            filter: none;
        }

        .modal-header .btn-close:hover,
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

        /* Top row with radio buttons and base currency */
        .modal-body .d-flex.justify-content-between {
            margin-bottom: 1rem;
        }

        /* Form labels - smaller and less bold */
        .modal-body label {
            font-weight: 400;
            margin-bottom: 0.25rem;
            font-size: 0.813rem;
            color: #495057;
        }

        .modal-body label .text-danger {
            color: #dc3545 !important;
        }

        /* Form controls - standard Bootstrap styling */
        .modal-body .form-control,
        .modal-body .form-select {
            border: 1px solid #ced4da;
            background-color: #fff;
            font-size: 0.875rem;
            padding: 0.375rem 0.75rem;
            height: calc(1.5em + 0.75rem + 2px);
            border-radius: 0.25rem;
        }

        .modal-body .form-control:focus,
        .modal-body .form-select:focus {
            border-color: #80bdff;
            outline: 0;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }

        .modal-body .form-control[readonly] {
            background-color: #e9ecef;
            cursor: not-allowed;
            border: 1px solid #ced4da;
            padding: 0.375rem 0.75rem;
        }

        /* Radio buttons and checkboxes - standard alignment */
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

        /* Text muted - lighter */
        .modal-body .text-muted {
            color: #6c757d !important;
            font-size: 0.813rem;
        }

        .modal-body .text-muted strong {
            font-weight: 600;
            color: #495057;
        }

        /* Horizontal rule */
        .modal-body hr {
            margin: 1rem 0;
            border-top: 1px solid #dee2e6;
        }

        /* Input group styling */
        .modal-body .input-group .form-control {
            border-radius: 0.25rem 0 0 0.25rem;
        }

        .modal-body .input-group .form-select {
            border-radius: 0 0.25rem 0.25rem 0;
            border-left: 0;
        }

        /* Modal footer buttons - standard Bootstrap colors */
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

        .modal-footer .btn-secondary,
        .modal-footer .btn-outline-secondary {
            background-color: #6c757d;
            border-color: #6c757d;
            color: #fff;
        }

        .modal-footer .btn-secondary:hover,
        .modal-footer .btn-outline-secondary:hover {
            background-color: #5a6268;
            border-color: #545b62;
        }

        /* Secondary Bill To Party Container */
        #secondaryBillToPartyContainer {
            display: none;
            margin-top: 1rem;
        }

        /* Checkbox styling */
        .modal-body .form-check {
            margin-bottom: 0.5rem;
        }

        .modal-body .form-check .form-check-label.fw-semibold {
            font-weight: 600;
            font-size: 0.875rem;
        }

        .modal-body .form-check .form-check-label small {
            font-size: 0.75rem;
            font-weight: 400;
        }

        /* Row and column spacing */
        .modal-body .row {
            margin-bottom: 0;
        }

        .modal-body .mb-3 {
            margin-bottom: 1rem !important;
        }

        .modal-body .col-md-6 .mb-3:last-child,
        .modal-body .col-md-4 .mb-3:last-child {
            margin-bottom: 1rem !important;
        }

        /* Make card header clickable for collapse */
        .card-header {
            cursor: pointer;
            user-select: none;
        }

        .card-header:hover {
            opacity: 0.95;
        }

        /* Styling for improved table readability */
        .custom-table {
            border-collapse: separate;
            border-spacing: 0;
            background-color: #fff;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            overflow: hidden;
        }

        .custom-table thead th {
            background-color: #f8f9fa;
            font-weight: 600;
            padding: 0.75rem;
            border-bottom: 1px solid #dee2e6;
            vertical-align: middle;
        }

        .custom-table tbody td {
            padding: 0.65rem 0.75rem;
            border-top: 1px solid #dee2e6;
            vertical-align: middle;
            white-space: nowrap;
        }

        .custom-table tbody tr:nth-child(even) {
            background-color: #fdfdfd;
        }

        .custom-table tbody tr.table-active {
            background-color: #e9ecef !important;
        }

        .custom-table td.text-end,
        .custom-table th.text-end {
            text-align: right;
        }

        .custom-table td.text-center,
        .custom-table th.text-center {
            text-align: center;
        }

        /* Button spacing in actions */
        .custom-table .btn-group .btn {
            padding: 0.25rem 0.5rem;
        }

        /* Badge spacing inside accordion header */
        .accordion-button .badge {
            margin-left: auto;
        }

        /* Accordion tweaks for tighter spacing */
        .accordion-button {
            padding: 0.75rem 1rem;
            font-size: 1rem;
        }

        /* Hide optional columns by default */
        .optional-column {
            display: none;
        }

        /* Show optional columns when toggled */
        .show-optional .optional-column {
            display: table-cell;
        }

        /* Sticky Actions column */
        #revenueTable th:nth-child(11),
        #costTable th:nth-child(11),
        #revenueTable td:nth-child(11),
        #costTable td:nth-child(11) {
            position: sticky;
            right: 0;
            background-color: #fff;
            z-index: 1;
            min-width: 100px;
            width: 100px;
        }

        /* Ensure table-responsive works */
        .table-responsive {
            overflow-x: auto;
        }

        /* Compact columns */
        #revenueTable th,
        #revenueTable td,
        #costTable th,
        #costTable td {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
    </style>
@stop

@section('js')
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>

    <script>
        $(document).ready(function() {
            console.log('Booking Quotation Edit - Page Loaded');

            // Global variables
            const bookingNo = '{{ $quotation->bookingno ?? '' }}';
            const quotationStatus = '{{ $quotation->status ?? 'Proposal' }}';
            let revenueCharges = [];
            let costCharges = [];
            let currentEditingCharge = null;
            let currentEditingType = null;

            // Debug: Log loaded data
            console.log('Booking Number:', bookingNo);
            console.log('Quotation Status:', quotationStatus);

            // ============================================
            // MODAL CLOSE HANDLERS - X and Cancel buttons
            // ============================================

            // Revenue Modal - X button (close icon)
            $('#addRevenueModal .close').on('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                console.log('Revenue Modal X button clicked');
                $('#addRevenueModal').modal('hide');
            });

            // Revenue Modal - Cancel button (in footer)
            $('#addRevenueModal button[data-dismiss="modal"]').on('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                console.log('Revenue Modal Cancel button clicked');
                $('#addRevenueModal').modal('hide');
            });

            // Cost Modal - X button
            $('#addCostModal .close').on('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                console.log('Cost Modal X button clicked');
                $('#addCostModal').modal('hide');
            });

            // Cost Modal - Cancel button
            $('#addCostModal button[data-dismiss="modal"]').on('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                console.log('Cost Modal Cancel button clicked');
                $('#addCostModal').modal('hide');
            });

            // XML Viewer Modal - X button
            $('#xmlViewerModal .close').on('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                console.log('XML Viewer Modal X button clicked');
                $('#xmlViewerModal').modal('hide');
            });

            // XML Viewer Modal - Close button
            $('#xmlViewerModal button[data-dismiss="modal"]').on('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                console.log('XML Viewer Modal Close button clicked');
                $('#xmlViewerModal').modal('hide');
            });

            // Status Modal - X button (dynamically created)
            $(document).on('click', '#statusModal .close', function(e) {
                e.preventDefault();
                e.stopPropagation();
                console.log('Status Modal X button clicked');
                $('#statusModal').modal('hide');
            });

            // Status Modal - Cancel button (dynamically created)
            $(document).on('click', '#statusModal button[data-dismiss="modal"]', function(e) {
                e.preventDefault();
                e.stopPropagation();
                console.log('Status Modal Cancel button clicked');
                $('#statusModal').modal('hide');
            });

            // ============================================
            // ESC KEY HANDLER - Close modals
            // ============================================

            $(document).on('keydown', function(e) {
                if (e.key === 'Escape' || e.keyCode === 27) {
                    // Close any open modals
                    if ($('#addRevenueModal').hasClass('show')) {
                        console.log('ESC pressed - closing Revenue Modal');
                        $('#addRevenueModal').modal('hide');
                    }
                    if ($('#addCostModal').hasClass('show')) {
                        console.log('ESC pressed - closing Cost Modal');
                        $('#addCostModal').modal('hide');
                    }
                    if ($('#xmlViewerModal').hasClass('show')) {
                        console.log('ESC pressed - closing XML Viewer Modal');
                        $('#xmlViewerModal').modal('hide');
                    }
                    if ($('#statusModal').hasClass('show')) {
                        console.log('ESC pressed - closing Status Modal');
                        $('#statusModal').modal('hide');
                    }
                }
            });

            // ============================================
            // BACKDROP CLICK HANDLER - Close modals
            // ============================================

            $('#addRevenueModal, #addCostModal, #xmlViewerModal').on('click', function(e) {
                // Close modal if clicking on the backdrop (outside modal content)
                if ($(e.target).hasClass('modal')) {
                    console.log('Backdrop clicked - closing modal');
                    $(this).modal('hide');
                }
            });

            // For dynamically created status modal
            $(document).on('click', '#statusModal', function(e) {
                if ($(e.target).hasClass('modal')) {
                    console.log('Status Modal backdrop clicked - closing modal');
                    $(this).modal('hide');
                }
            });

            // ============================================
            // MODAL CLEANUP ON CLOSE
            // ============================================

            // Reset Revenue Modal when hidden
            $('#addRevenueModal').on('hidden.bs.modal', function() {
                console.log('Revenue Modal hidden, resetting form');
                clearRevenueModalForm();
                currentEditingCharge = null;
                currentEditingType = null;
            });

            // Reset Cost Modal when hidden
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
                console.log('Loading charges for booking:', bookingNo);

                $.ajax({
                    url: '{{ route('quotation.charges') }}',
                    type: 'GET',
                    data: {
                        booking_no: bookingNo
                    },
                    beforeSend: function() {
                        $('#revenueChargesTable').html(
                            '<tr><td colspan="6" class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading charges...</td></tr>'
                        );
                        $('#costChargesTable').html(
                            '<tr><td colspan="6" class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading charges...</td></tr>'
                        );
                    },
                    success: function(response) {
                        console.log('Charges loaded successfully:', response);
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
                        console.error('Failed to load charges:', xhr);
                        showNotification('Failed to load charges: ' + (xhr.responseJSON?.message || xhr
                            .statusText), 'error');
                        $('#revenueChargesTable').html(
                            '<tr><td colspan="6" class="text-center text-danger">Failed to load charges</td></tr>'
                        );
                        $('#costChargesTable').html(
                            '<tr><td colspan="6" class="text-center text-danger">Failed to load charges</td></tr>'
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
            // MODAL INITIALIZATION WITH DROPDOWN LOADING
            // ============================================
            $('#addRevenueModal').on('show.bs.modal', function() {
                if (!currentEditingCharge) {
                    $('#revenueTypeRevenue').prop('checked', true);
                    toggleRevenueFormFields('revenue');
                    loadBasedOnList('revenue');
                }
                initializeChargeCodeAutocomplete('revenue');
            });

            $('#addCostModal').on('show.bs.modal', function() {
                if (!currentEditingCharge) {
                    $('#costTypeCost').prop('checked', true);
                    toggleCostFormFields('cost');
                    loadBasedOnList('cost');
                }
                initializeChargeCodeAutocomplete('cost');
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
                        $basedOnSelect.prop('disabled', true);
                        $basedOnSelect.empty().append('<option value="">Loading...</option>');
                    },
                    success: function(response) {
                        if (response.success) {
                            $basedOnSelect.empty().append('<option value="">Select Based On</option>');

                            response.based_on_list.forEach(function(item) {
                                $basedOnSelect.append(
                                    `<option value="${item.Code}" data-id="${item.Id}">${item.Name}</option>`
                                );
                            });
                        } else {
                            showNotification('Failed to load Based On list', 'error');
                            $basedOnSelect.empty().append('<option value="">Failed to load</option>');
                        }
                    },
                    error: function(xhr) {
                        console.error('Failed to load Based On list:', xhr);
                        showNotification('Failed to load Based On list', 'error');
                        $basedOnSelect.empty().append('<option value="">Failed to load</option>');
                    },
                    complete: function() {
                        $basedOnSelect.prop('disabled', false);
                    }
                });
            }

            // ============================================
            // LOAD UOM WHEN BASED ON CHANGES
            // ============================================

            // Revenue Based On change handler
            $(document).on('change', 'select[name="revenue_based_on"]', function() {
                const basedOnCode = $(this).val();
                loadBasedOnUOM('revenue', basedOnCode);
            });

            // Cost Based On change handler
            $(document).on('change', 'select[name="cost_based_on"]', function() {
                const basedOnCode = $(this).val();
                loadBasedOnUOM('cost', basedOnCode);
            });

            function loadBasedOnUOM(type, basedOnCode) {
                const prefix = type === 'revenue' ? 'revenue' : 'cost';
                const $uomSelect = $(`select[name="${prefix}_based_on_uom"]`);

                if (!basedOnCode) {
                    $uomSelect.empty().append('<option value="">Select Based On first</option>');
                    $uomSelect.prop('disabled', true);
                    return;
                }

                $.ajax({
                    url: '{{ route('quotation.based-on-uom') }}',
                    type: 'GET',
                    data: {
                        based_on_code: basedOnCode
                    },
                    beforeSend: function() {
                        $uomSelect.prop('disabled', true);
                        $uomSelect.empty().append('<option value="">Loading...</option>');
                    },
                    success: function(response) {
                        if (response.success) {
                            $uomSelect.empty().append('<option value="">Select UOM</option>');

                            response.uom_list.forEach(function(uom) {
                                $uomSelect.append(
                                    `<option value="${uom.Code}">${uom.Name}</option>`
                                );
                            });

                            $uomSelect.prop('disabled', false);
                        } else {
                            showNotification('Failed to load UOM list', 'error');
                            $uomSelect.empty().append('<option value="">Failed to load</option>');
                        }
                    },
                    error: function(xhr) {
                        console.error('Failed to load UOM list:', xhr);
                        showNotification('Failed to load UOM list', 'error');
                        $uomSelect.empty().append('<option value="">Failed to load</option>');
                    }
                });
            }

            // Add after document.ready
            function initializeChargeCodeAutocomplete(type) {
                const prefix = type === 'revenue' ? 'revenue' : 'cost';
                const $input = $(`input[name="${prefix}_charge_code"]`);
                const officeId = '{{ $quotation->teamid ?? '' }}';

                console.log('=== AUTOCOMPLETE DEBUG ===');
                console.log('Office ID:', officeId);
                console.log('Route URL:', '{{ route('quotation.charge-codes') }}');

                if (!officeId) {
                    console.error('No office ID available');
                    return;
                }

                $input.autocomplete({
                    source: function(request, response) {
                        console.log('Autocomplete triggered. Term:', request.term);

                        $.ajax({
                            url: '{{ route('quotation.charge-codes') }}',
                            type: 'GET',
                            data: {
                                office_id: officeId,
                                charge_type: type === 'revenue' ? 'Revenue' : 'Cost',
                                filter: request.term
                            },
                            success: function(data) {
                                console.log('SUCCESS Response:', data);
                                if (data.success && data.charge_codes) {
                                    response(data.charge_codes.map(item => ({
                                        label: item.Name + ' (' + item
                                            .Code + ')',
                                        value: item.Code
                                    })));
                                } else {
                                    console.error('No charge codes in response');
                                    response([]);
                                }
                            },
                            error: function(xhr, status, error) {
                                console.error('AJAX ERROR:', status, error);
                                console.error('Response:', xhr.responseText);
                                response([]);
                            }
                        });
                    },
                    minLength: 2,
                    select: function(event, ui) {
                        if (ui.item.value) {
                            $(this).val(ui.item.value);
                        }
                        return false;
                    }
                });
            }

            // Call when modals open
            $('#addRevenueModal').on('shown.bs.modal', function() {
                initializeChargeCodeAutocomplete('revenue');
            });

            $('#addCostModal').on('shown.bs.modal', function() {
                initializeChargeCodeAutocomplete('cost');
            });

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
                const $isAccruedCheckbox = $modal.find('.is-accrued-row');

                if (type === 'costmarkup') {
                    if ($increaseByRow.length === 0) {
                        const increaseByHtml = `
                    <div class="row increase-by-row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Increase By</label>
                                <input type="number" class="form-control form-control-sm" name="revenue_increase_by" value="0" step="0.01">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Type</label>
                                <select class="form-control form-control-sm" name="revenue_increase_type">
                                    <option value="Percentage">Percentage</option>
                                    <option value="Fixed">Fixed</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Revenue Amount</label>
                                <input type="number" class="form-control form-control-sm" name="revenue_markup_amount" value="0.00" step="0.01" readonly>
                            </div>
                        </div>
                    </div>
                `;
                        $modal.find('.rate-row').after(increaseByHtml);
                    }
                    $increaseByRow = $modal.find('.increase-by-row');
                    $increaseByRow.show();
                    $isAccruedCheckbox.hide();
                } else if (type === 'cost') {
                    $increaseByRow.hide();
                    $isAccruedCheckbox.show();
                } else {
                    $increaseByRow.hide();
                    $isAccruedCheckbox.show();
                }
            }

            function toggleCostFormFields(type) {
                const $modal = $('#addCostModal');
                const $increaseByRow = $modal.find('.increase-by-row');
                const $isAccruedCheckbox = $modal.find('.is-accrued-row');

                if (type === 'costmarkup') {
                    $increaseByRow.show();
                    if ($isAccruedCheckbox.length > 0) {
                        $isAccruedCheckbox.hide();
                    }
                } else if (type === 'cost') {
                    $increaseByRow.hide();
                    if ($isAccruedCheckbox.length > 0) {
                        $isAccruedCheckbox.show();
                    }
                } else {
                    $increaseByRow.hide();
                    if ($isAccruedCheckbox.length > 0) {
                        $isAccruedCheckbox.show();
                    }
                }
            }

            // ============================================
            // AUTO-CALCULATE AMOUNTS
            // ============================================
            $('#addRevenueModal').on('input',
                'input[name="revenue_quantity"], input[name="revenue_rate"], input[name="revenue_exchange_rate"]',
                function() {
                    calculateRevenueAmount();
                });

            $('#addRevenueModal').on('input', 'input[name="revenue_increase_by"]', function() {
                calculateRevenueAmount();
            });

            $('#addRevenueModal').on('change', 'select[name="revenue_increase_type"]', function() {
                calculateRevenueAmount();
            });

            function calculateRevenueAmount() {
                const $modal = $('#addRevenueModal');
                const quantity = parseFloat($modal.find('input[name="revenue_quantity"]').val()) || 0;
                const rate = parseFloat($modal.find('input[name="revenue_rate"]').val()) || 0;
                const exchangeRate = parseFloat($modal.find('input[name="revenue_exchange_rate"]').val()) || 1;

                const amount = quantity * rate;
                const baseAmount = amount * exchangeRate;

                $modal.find('input[name="revenue_amount"]').val(amount.toFixed(2));
                $modal.find('input[name="revenue_base_amount"]').val(baseAmount.toFixed(2));

                const chargeType = $modal.find('input[name="revenueChargeType"]:checked').val();
                if (chargeType === 'costmarkup') {
                    const increaseBy = parseFloat($modal.find('input[name="revenue_increase_by"]').val()) || 0;
                    const increaseType = $modal.find('select[name="revenue_increase_type"]').val();

                    let markupAmount = 0;
                    if (increaseType === 'Percentage') {
                        markupAmount = amount + (amount * increaseBy / 100);
                    } else {
                        markupAmount = amount + increaseBy;
                    }

                    $modal.find('input[name="revenue_markup_amount"]').val(markupAmount.toFixed(2));
                }
            }

            $('#addCostModal').on('input',
                'input[name="cost_quantity"], input[name="cost_rate"], input[name="cost_exchange_rate"]',
                function() {
                    calculateCostAmount();
                });

            $('#addCostModal').on('input', 'input[name="cost_increase_by"]', function() {
                calculateCostAmount();
            });

            $('#addCostModal').on('change', 'select[name="cost_increase_type"]', function() {
                calculateCostAmount();
            });

            function calculateCostAmount() {
                const $modal = $('#addCostModal');
                const quantity = parseFloat($modal.find('input[name="cost_quantity"]').val()) || 0;
                const rate = parseFloat($modal.find('input[name="cost_rate"]').val()) || 0;
                const exchangeRate = parseFloat($modal.find('input[name="cost_exchange_rate"]').val()) || 1;

                const amount = quantity * rate;
                const baseAmount = amount * exchangeRate;

                $modal.find('input[name="cost_amount"]').val(amount.toFixed(2));
                $modal.find('input[name="cost_base_amount"]').val(baseAmount.toFixed(2));

                const chargeType = $modal.find('input[name="costChargeType"]:checked').val();
                if (chargeType === 'costmarkup') {
                    const increaseBy = parseFloat($modal.find('input[name="cost_increase_by"]').val()) || 0;
                    const increaseType = $modal.find('select[name="cost_increase_type"]').val();

                    let markupAmount = 0;
                    if (increaseType === 'Percentage') {
                        markupAmount = amount + (amount * increaseBy / 100);
                    } else {
                        markupAmount = amount + increaseBy;
                    }

                    $modal.find('input[name="cost_revenue_amount"]').val(markupAmount.toFixed(2));
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
                    chg_curr: $modal.find('select[name="' + type + '_currency"]').val() || 'KES',
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
                        const message = xhr.responseJSON?.message || 'Failed to save charge';
                        showNotification(message, 'error');
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
                const $btn = $(this);

                if (!confirm('Are you sure you want to delete this charge?')) {
                    return;
                }

                $.ajax({
                    url: '{{ route('quotation.charge.delete') }}',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        booking_no: bookingNo,
                        quote_charge_id: chargeId
                    },
                    beforeSend: function() {
                        $btn.prop('disabled', true);
                    },
                    success: function(response) {
                        if (response.success) {
                            showNotification('Charge deleted successfully', 'success');
                            loadCharges();
                        } else {
                            showNotification(response.message, 'error');
                        }
                    },
                    error: function(xhr) {
                        showNotification('Failed to delete charge', 'error');
                    },
                    complete: function() {
                        $btn.prop('disabled', false);
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

                populateRevenueModal(charge);
                $('#addRevenueModal').modal('show');
            });

            $(document).on('click', '.edit-cost-charge', function() {
                const index = $(this).data('index');
                const charge = costCharges[index];
                currentEditingCharge = charge.id;
                currentEditingType = 'cost';

                populateCostModal(charge);
                $('#addCostModal').modal('show');
            });

            function populateRevenueModal(charge) {
                const $modal = $('#addRevenueModal');

                // Populate basic fields
                $modal.find('input[name="revenue_charge_code"]').val(charge.chargeCode);
                $modal.find('input[name="revenue_remark"]').val(charge.chargeRemark);
                $modal.find('input[name="revenue_quantity"]').val(charge.quantity);
                $modal.find('input[name="revenue_rate"]').val(charge.rate);
                $modal.find('input[name="revenue_exchange_rate"]').val(charge.exChangeRate);
                $modal.find('select[name="revenue_currency"]').val(charge.chgCurr);

                // Load Based On dropdown first
                loadBasedOnList('revenue');

                // Wait for dropdown to populate, then set values
                setTimeout(function() {
                    $modal.find('select[name="revenue_based_on"]').val(charge.basedOnCode);

                    // Load UOM based on selected Based On
                    if (charge.basedOnCode) {
                        loadBasedOnUOM('revenue', charge.basedOnCode);

                        // Set UOM value after it loads
                        setTimeout(function() {
                            $modal.find('select[name="revenue_based_on_uom"]').val(charge
                                .basedOnUOM);
                        }, 500);
                    }
                }, 500);

                calculateRevenueAmount();
            }

            function populateCostModal(charge) {
                const $modal = $('#addCostModal');

                // Populate basic fields
                $modal.find('input[name="cost_charge_code"]').val(charge.chargeCode);
                $modal.find('input[name="cost_remark"]').val(charge.chargeRemark);
                $modal.find('input[name="cost_quantity"]').val(charge.quantity);
                $modal.find('input[name="cost_rate"]').val(charge.rate);
                $modal.find('input[name="cost_exchange_rate"]').val(charge.exChangeRate);
                $modal.find('select[name="cost_currency"]').val(charge.chgCurr);

                // Load Based On dropdown first
                loadBasedOnList('cost');

                // Wait for dropdown to populate, then set values
                setTimeout(function() {
                    $modal.find('select[name="cost_based_on"]').val(charge.basedOnCode);

                    // Load UOM based on selected Based On
                    if (charge.basedOnCode) {
                        loadBasedOnUOM('cost', charge.basedOnCode);

                        // Set UOM value after it loads
                        setTimeout(function() {
                            $modal.find('select[name="cost_based_on_uom"]').val(charge.basedOnUOM);
                        }, 500);
                    }
                }, 500);

                calculateCostAmount();
            }

            // ============================================
            // RENDER FUNCTIONS
            // ============================================
            function renderRevenueCharges() {
                const tbody = $('#revenueChargesTable');
                tbody.empty();

                let totalAmount = 0;
                let totalBaseAmount = 0;

                if (revenueCharges.length === 0) {
                    tbody.append('<tr><td colspan="6" class="text-center text-muted">No charges added</td></tr>');
                } else {
                    revenueCharges.forEach(function(charge, index) {
                        totalAmount += parseFloat(charge.amtInChgCurr || 0);
                        totalBaseAmount += parseFloat(charge.amtInBaseCurr || 0);

                        const row = `
                    <tr>
                        <td>${charge.chargeName || charge.chargeCode || 'N/A'}</td>
                        <td class="text-right">${parseFloat(charge.rate || 0).toFixed(2)}</td>
                        <td>${charge.basedOn || 'N/A'}</td>
                        <td class="text-right">${charge.chgCurr || 'KES'} ${parseFloat(charge.amtInChgCurr || 0).toFixed(2)}</td>
                        <td class="text-right">KES ${parseFloat(charge.amtInBaseCurr || 0).toFixed(2)}</td>
                        <td class="text-center">
                            <button type="button" class="btn btn-warning btn-action edit-revenue-charge" data-index="${index}" data-id="${charge.id}" title="Edit">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button type="button" class="btn btn-danger btn-action delete-revenue-charge" data-index="${index}" data-id="${charge.id}" title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                `;
                        tbody.append(row);
                    });
                }

                $('#totalRevenueAmount').text('KES ' + totalAmount.toFixed(2));
                $('#totalRevenueBaseAmount').text('KES ' + totalBaseAmount.toFixed(2));
            }

            function renderCostCharges() {
                const tbody = $('#costChargesTable');
                tbody.empty();

                let totalAmount = 0;
                let totalBaseAmount = 0;

                if (costCharges.length === 0) {
                    tbody.append('<tr><td colspan="6" class="text-center text-muted">No charges added</td></tr>');
                } else {
                    costCharges.forEach(function(charge, index) {
                        totalAmount += parseFloat(charge.amtInChgCurr || 0);
                        totalBaseAmount += parseFloat(charge.amtInBaseCurr || 0);

                        const row = `
                    <tr>
                        <td>${charge.chargeName || charge.chargeCode || 'N/A'}</td>
                        <td class="text-right">${parseFloat(charge.rate || 0).toFixed(2)}</td>
                        <td>${charge.basedOn || 'N/A'}</td>
                        <td class="text-right">${charge.chgCurr || 'KES'} ${parseFloat(charge.amtInChgCurr || 0).toFixed(2)}</td>
                        <td class="text-right">KES ${parseFloat(charge.amtInBaseCurr || 0).toFixed(2)}</td>
                        <td class="text-center">
                            <button type="button" class="btn btn-warning btn-action edit-cost-charge" data-index="${index}" data-id="${charge.id}" title="Edit">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button type="button" class="btn btn-danger btn-action delete-cost-charge" data-index="${index}" data-id="${charge.id}" title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                `;
                        tbody.append(row);
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
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
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
                        const message = xhr.responseJSON?.message || 'Failed to update status';
                        showNotification(message, 'error');
                    }
                });
            }

            // ============================================
            // XML VIEWER
            // ============================================
            $('#viewXmlBtn').on('click', function(e) {
                e.preventDefault();
                console.log('View XML button clicked');
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
                $modal.find('input[type="text"]').val('');
                $modal.find('input[type="number"]').val('0');
                $modal.find('select').prop('selectedIndex', 0);
                $modal.find('input[type="checkbox"]').prop('checked', false);

                // Reload dropdowns for fresh state
                if (type === 'revenue') {
                    loadBasedOnList('revenue');
                } else {
                    loadBasedOnList('cost');
                }
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
