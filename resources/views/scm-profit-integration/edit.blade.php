@extends('adminlte::page')

@section('title', 'Booking Quotation')

@section('content_header')
    <h1>Inquiry No - &lt;&lt;Booking No&gt;&gt;</h1>
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
                        <h3 class="card-title">Booking Details</h3>
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
                                    placeholder="<< Search Engine >>">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-5 col-form-label">Status</label>
                            <div class="col-sm-7">
                                <input type="text" class="form-control form-control-sm" placeholder="<< Status >>">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-5 col-form-label">Sales person</label>
                            <div class="col-sm-7">
                                <input type="text" class="form-control form-control-sm" placeholder="<<username>>">
                            </div>
                        </div>
                        <div class="form-group row mb-0">
                            <label class="col-sm-5 col-form-label">Office</label>
                            <div class="col-sm-7">
                                <input type="text" class="form-control form-control-sm" placeholder="<<teamname>>">
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
                                    placeholder="<<ServiceVerticalname>>">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-5 col-form-label">Process</label>
                            <div class="col-sm-7">
                                <input type="text" class="form-control form-control-sm" placeholder="<<process>>">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-5 col-form-label">Origin Country</label>
                            <div class="col-sm-7">
                                <input type="text" class="form-control form-control-sm" placeholder="<<origincountry>>">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-5 col-form-label">Destination Country</label>
                            <div class="col-sm-7">
                                <input type="text" class="form-control form-control-sm"
                                    placeholder="<<destinationcountry>>">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-5 col-form-label">InCO Terms</label>
                            <div class="col-sm-7">
                                <input type="text" class="form-control form-control-sm" placeholder="<<incoterms>>">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-5 col-form-label">Shipment Mode</label>
                            <div class="col-sm-7">
                                <input type="text" class="form-control form-control-sm" placeholder="<<shipmentmode>>">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-5 col-form-label">Transport Mode</label>
                            <div class="col-sm-7">
                                <input type="text" class="form-control form-control-sm" placeholder="<<TransportMode>>">
                            </div>
                        </div>
                        <div class="form-group row mb-0">
                            <label class="col-sm-5 col-form-label">Service Class</label>
                            <div class="col-sm-7">
                                <input type="text" class="form-control form-control-sm" placeholder="<<ServiceClass>>">
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
                                <input type="text" class="form-control form-control-sm" placeholder="<<commodity>>">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-5 col-form-label">Scope Of Volume</label>
                            <div class="col-sm-7">
                                <input type="text" class="form-control form-control-sm" placeholder="<<scopevolume>>">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-5 col-form-label">Cargo Volume</label>
                            <div class="col-sm-7">
                                <input type="text" class="form-control form-control-sm" placeholder="<<cargovolume>>">
                            </div>
                        </div>
                        <div class="form-group row mb-0">
                            <label class="col-sm-5 col-form-label">Container List</label>
                            <div class="col-sm-7">
                                <input type="text" class="form-control form-control-sm"
                                    placeholder="<<containerlist>>">
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
                                    placeholder="ClientIntegration">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-5 col-form-label">Boking No</label>
                            <div class="col-sm-7">
                                <input type="text" class="form-control form-control-sm" placeholder="SCMBookingNo">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-5 col-form-label">Booking Integration</label>
                            <div class="col-sm-7">
                                <input type="text" class="form-control form-control-sm" placeholder="SCMINTStatus">
                            </div>
                        </div>
                        <div class="form-group row mb-0">
                            <label class="col-sm-5 col-form-label">Booking XML</label>
                            <div class="col-sm-7">
                                <input type="text" class="form-control form-control-sm" placeholder="SCMBookingXML">
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
                                            placeholder="<< companyname>>">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-4 col-form-label">Address 1</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control form-control-sm"
                                            placeholder="pri_partner_street">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-4 col-form-label">City</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control form-control-sm"
                                            placeholder="pri_partner_city">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-4 col-form-label">State</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control form-control-sm"
                                            placeholder="pri_partner_state">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-4 col-form-label">Contact Person</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control form-control-sm"
                                            placeholder="pri_partnername">
                                    </div>
                                </div>
                                <div class="form-group row mb-0">
                                    <label class="col-sm-4 col-form-label">Phone No</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control form-control-sm"
                                            placeholder="pri_partner_phon">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group row">
                                    <label class="col-sm-4 col-form-label">Client ID</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control form-control-sm"
                                            placeholder="<<Client ID>>">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-4 col-form-label">Address 2</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control form-control-sm"
                                            placeholder="pri_partner_street2">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-4 col-form-label">Zip</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control form-control-sm"
                                            placeholder="pri_partner_zip">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-4 col-form-label">Country</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control form-control-sm"
                                            placeholder="pri_partner_countryname">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-4 col-form-label">Email ID</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control form-control-sm"
                                            placeholder="pri_partner_email">
                                    </div>
                                </div>
                                <div class="form-group row mb-0">
                                    <label class="col-sm-4 col-form-label">Industry</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control form-control-sm"
                                            placeholder="<<Industryname>>">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Revenue -->
                <div class="card card-success">
                    <div class="card-header">
                        <h3 class="card-title">Revenue</h3>
                        <div class="card-tools">
                            <a href="#" class="text-white show-items-link">Show Items</a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="mb-2">
                            <button type="button" class="btn btn-primary btn-sm" id="openRevenueModal">
                                <i class="fas fa-plus"></i> Add Charges
                            </button>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm table-hover">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Charge Name</th>
                                        <th>Quantity</th>
                                        <th>Based On</th>
                                        <th>UOM</th>
                                        <th>Rate</th>
                                        <th>Currency</th>
                                        <th>Amount</th>
                                        <th>Base Amount</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="revenueChargesTable">
                                    <!-- Dynamic rows will be added here -->
                                </tbody>
                                <tfoot>
                                    <tr class="font-weight-bold">
                                        <td colspan="6" class="text-right">TOTAL REVENUE</td>
                                        <td id="totalRevenueAmount">0.00</td>
                                        <td id="totalRevenueBaseAmount">0.00</td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Cost -->
                <div class="card card-danger">
                    <div class="card-header">
                        <h3 class="card-title">Cost</h3>
                        <div class="card-tools">
                            <a href="#" class="text-white show-items-link">Show Items</a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="mb-2">
                            <button type="button" class="btn btn-primary btn-sm" id="openCostModal">
                                <i class="fas fa-plus"></i> Add Charges
                            </button>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm table-hover">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Charge Name</th>
                                        <th>Quantity</th>
                                        <th>Based On</th>
                                        <th>UOM</th>
                                        <th>Rate</th>
                                        <th>Currency</th>
                                        <th>Amount</th>
                                        <th>Base Amount</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="costChargesTable">
                                    <!-- Dynamic rows will be added here -->
                                </tbody>
                                <tfoot>
                                    <tr class="font-weight-bold">
                                        <td colspan="6" class="text-right">TOTAL COST</td>
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
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="font-weight-bold">Exected Revenue</label>
                                    <div>expected_</div>
                                </div>
                                <div class="form-group">
                                    <label class="font-weight-bold">Quoted Revenue</label>
                                    <div>Based on</div>
                                </div>
                                <div class="form-group mb-0">
                                    <label class="font-weight-bold">Actual Revenue</label>
                                    <div>Blank</div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="font-weight-bold">Quoted Margin</label>
                                    <div>Based on Calculation</div>
                                </div>
                                <div class="form-group mb-0">
                                    <label class="font-weight-bold">Actual Margin</label>
                                    <div>Blank</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-0">
                                    <label class="font-weight-bold">Terms & Condition Section</label>
                                    <input type="text" class="form-control form-control-sm" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-md-12">
                                <button type="button" class="btn btn-primary">
                                    <i class="fas fa-file-alt"></i> Generate Quote
                                </button>
                                <button type="button" class="btn btn-primary">
                                    <i class="fas fa-envelope"></i> Email Quote
                                </button>
                                <a href="#" class="btn btn-link">Close</a>

                                <div class="float-right">
                                    <button type="button" class="btn btn-warning">
                                        <i class="fas fa-ban"></i> Cancel
                                    </button>
                                    <button type="button" class="btn btn-danger">
                                        <i class="fas fa-times"></i> LOST
                                    </button>
                                    <button type="button" class="btn btn-success">
                                        <i class="fas fa-check"></i> WON
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- MODALS - PLACED OUTSIDE THE ROW/COLUMNS -->

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
                                    <input type="text" class="form-control form-control-sm">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Tax Applicable</label>
                                    <select class="form-control form-control-sm">
                                        <option value="">Select</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Description</label>
                                    <input type="text" class="form-control form-control-sm">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>* Based On</label>
                                    <select class="form-control form-control-sm">
                                        <option value="">Select</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>* Based On UOM</label>
                                    <select class="form-control form-control-sm">
                                        <option value="">Select</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Quantity</label>
                                    <input type="number" class="form-control form-control-sm" value="0"
                                        step="0.01">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Remark</label>
                                    <input type="text" class="form-control form-control-sm">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Rate</label>
                                    <input type="number" class="form-control form-control-sm" value="0"
                                        step="0.01">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <select class="form-control form-control-sm">
                                        <option value="">Select</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Amount</label>
                                    <input type="number" class="form-control form-control-sm" value="0.00"
                                        step="0.01" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>* Exchange Rate</label>
                                    <input type="number" class="form-control form-control-sm" value="0.00"
                                        step="0.01">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Amount in Base Currency (KES)</label>
                                    <input type="number" class="form-control form-control-sm" value="0.00"
                                        step="0.01" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="revenueIsAccrued">
                                    <label class="form-check-label" for="revenueIsAccrued">
                                        Is Accrued <span class="text-muted">(Adds to Cost)</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-success btn-sm">
                            <i class="fas fa-save"></i> Save
                        </button>
                        <button type="button" class="btn btn-primary btn-sm">
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
        <div class="modal fade" id="addCostModal" tabindex="-1" role="dialog"
            aria-labelledby="addCostModalLabel" aria-hidden="true">
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
                                            id="costTypeCost" value="cost">
                                        <label class="form-check-label" for="costTypeCost">Cost</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="costChargeType"
                                            id="costTypeCostMarkup" value="costmarkup" checked>
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

                        <!-- Form fields for Cost with markup -->
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>* Charge Code</label>
                                    <input type="text" class="form-control form-control-sm">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Tax Applicable</label>
                                    <select class="form-control form-control-sm">
                                        <option value="">Select</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Description</label>
                                    <input type="text" class="form-control form-control-sm">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>* Based On</label>
                                    <select class="form-control form-control-sm">
                                        <option value="">Select</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>* Based On UOM</label>
                                    <select class="form-control form-control-sm">
                                        <option value="">Select</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Quantity</label>
                                    <input type="number" class="form-control form-control-sm" value="0"
                                        step="0.01">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Remark</label>
                                    <input type="text" class="form-control form-control-sm">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Rate</label>
                                    <input type="number" class="form-control form-control-sm" value="0"
                                        step="0.01">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <select class="form-control form-control-sm">
                                        <option value="">Select</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Amount</label>
                                    <input type="number" class="form-control form-control-sm" value="0.00"
                                        step="0.01" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Increase By</label>
                                    <input type="number" class="form-control form-control-sm" value="0"
                                        step="0.01">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <select class="form-control form-control-sm">
                                        <option value="">Select</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Amount</label>
                                    <input type="number" class="form-control form-control-sm" value="0.00"
                                        step="0.01" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>* Exchange Rate</label>
                                    <input type="number" class="form-control form-control-sm" value="0.00"
                                        step="0.01">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Amount in Base Currency (KES)</label>
                                    <input type="number" class="form-control form-control-sm" value="0.00"
                                        step="0.01" readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-success btn-sm">
                            <i class="fas fa-save"></i> Save
                        </button>
                        <button type="button" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Save & Add More
                        </button>
                        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">
                            <i class="fas fa-times"></i> Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </form>
@stop

@section('css')
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

        /* Ensure consistent card header colors - all using #305496 with higher specificity */
        .card-primary>.card-header,
        .card.card-primary>.card-header {
            background-color: #305496 !important;
            color: white !important;
        }

        .card-success>.card-header,
        .card.card-success>.card-header {
            background-color: #305496 !important;
            color: white !important;
        }

        .card-danger>.card-header,
        .card.card-danger>.card-header {
            background-color: #305496 !important;
            color: white !important;
        }

        /* Small form controls for compact layout */
        .form-control-sm {
            height: calc(1.5em + 0.5rem + 2px);
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }

        /* Readonly input styling */
        .form-control[readonly] {
            background-color: #e9ecef;
            cursor: default;
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

        /* Modal styling */
        .modal-header {
            background-color: #f4f6f9;
            border-bottom: 1px solid #dee2e6;
        }

        .modal-title {
            font-size: 1.1rem;
            font-weight: 600;
        }

        .form-check-inline {
            margin-right: 1.5rem;
        }

        .form-check-input {
            margin-top: 0.25rem;
        }

        /* Modal form controls */
        .modal-body .form-control-sm {
            font-size: 0.875rem;
        }

        .modal-body label {
            font-weight: 500;
            margin-bottom: 0.25rem;
            font-size: 0.875rem;
        }

        .modal-footer {
            background-color: #f4f6f9;
            border-top: 1px solid #dee2e6;
        }
    </style>
@stop

@section('js')
    <script>
        $(document).ready(function() {
            console.log('Booking Quotation View - Page Loaded');
            console.log('jQuery loaded:', typeof jQuery !== 'undefined');
            console.log('Bootstrap loaded:', typeof $.fn.modal !== 'undefined');

            // Manual modal triggers
            $('#openRevenueModal').on('click', function(e) {
                e.preventDefault();
                console.log('Revenue modal button clicked');
                $('#addRevenueModal').modal('show');
            });

            $('#openCostModal').on('click', function(e) {
                e.preventDefault();
                console.log('Cost modal button clicked');
                $('#addCostModal').modal('show');
            });

            // Modal event listeners for debugging
            $('#addRevenueModal').on('show.bs.modal', function() {
                console.log('Revenue modal is opening');
                // Set default to Revenue option
                $('#revenueTypeRevenue').prop('checked', true);
                toggleRevenueFormFields('revenue');
            });

            $('#addCostModal').on('show.bs.modal', function() {
                console.log('Cost modal is opening');
                // Set default to Cost with markup option
                $('#costTypeCostMarkup').prop('checked', true);
                toggleCostFormFields('costmarkup');
            });

            // Handle Revenue modal charge type changes
            $('input[name="revenueChargeType"]').on('change', function() {
                var selectedType = $(this).val();
                console.log('Revenue charge type changed to:', selectedType);
                toggleRevenueFormFields(selectedType);
            });

            // Handle Cost modal charge type changes
            $('input[name="costChargeType"]').on('change', function() {
                var selectedType = $(this).val();
                console.log('Cost charge type changed to:', selectedType);
                toggleCostFormFields(selectedType);
            });

            // Function to toggle Revenue modal form fields
            function toggleRevenueFormFields(type) {
                var $modal = $('#addRevenueModal');
                var $increaseByRow = $modal.find('.increase-by-row');
                var $isAccruedCheckbox = $modal.find('.is-accrued-row');

                if (type === 'costmarkup') {
                    // Show "Increase By" field for Cost with markup
                    if ($increaseByRow.length === 0) {
                        // Insert "Increase By" row after Rate row
                        var increaseByHtml = `
                            <div class="row increase-by-row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Increase By</label>
                                        <input type="number" class="form-control form-control-sm" value="0" step="0.01">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>&nbsp;</label>
                                        <select class="form-control form-control-sm">
                                            <option value="">Select</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Amount</label>
                                        <input type="number" class="form-control form-control-sm" value="0.00" step="0.01" readonly>
                                    </div>
                                </div>
                            </div>
                        `;
                        $modal.find('.rate-row').after(increaseByHtml);
                    }
                    $increaseByRow.show();
                    $isAccruedCheckbox.hide();
                } else if (type === 'cost') {
                    // Hide "Increase By" for Cost, show "Is Accrued"
                    $increaseByRow.hide();
                    $isAccruedCheckbox.show();
                } else {
                    // Revenue - hide both
                    $increaseByRow.hide();
                    $isAccruedCheckbox.show();
                }
            }

            // Function to toggle Cost modal form fields
            function toggleCostFormFields(type) {
                var $modal = $('#addCostModal');
                var $increaseByRow = $modal.find('.increase-by-row');
                var $isAccruedCheckbox = $modal.find('.is-accrued-row');

                if (type === 'costmarkup') {
                    // Show "Increase By" field for Cost with markup
                    $increaseByRow.show();
                    if ($isAccruedCheckbox.length > 0) {
                        $isAccruedCheckbox.hide();
                    }
                } else if (type === 'cost') {
                    // Hide "Increase By" for Cost, show "Is Accrued" if exists
                    $increaseByRow.hide();
                    if ($isAccruedCheckbox.length > 0) {
                        $isAccruedCheckbox.show();
                    }
                } else {
                    // Revenue - hide "Increase By", show "Is Accrued" if exists
                    $increaseByRow.hide();
                    if ($isAccruedCheckbox.length > 0) {
                        $isAccruedCheckbox.show();
                    }
                }
            }

            // Handle collapse arrow rotation
            $('[data-card-widget="collapse"]').on('click', function() {
                var icon = $(this).find('i');
                icon.toggleClass('fa-chevron-down fa-chevron-up');
            });

            // Set initial state for all collapse buttons
            $('.card').each(function() {
                var card = $(this);
                var collapseBtn = card.find('[data-card-widget="collapse"] i');

                // Check if card body is visible
                if (card.find('.card-body').is(':visible')) {
                    collapseBtn.removeClass('fa-minus').addClass('fa-chevron-up');
                } else {
                    collapseBtn.removeClass('fa-minus').addClass('fa-chevron-down');
                }
            });

            // Sample data for demonstration (remove this when integrating with backend)
            var revenueCharges = [{
                chargeName: 'Freight Charges (Auto)',
                quantity: 1.00,
                basedOn: 'Chargeable',
                uom: 'KGS',
                rate: 23.05,
                currency: 'USD',
                amount: 23.05,
                baseAmount: 2973.45
            }];

            // Function to render revenue charges
            function renderRevenueCharges() {
                var tbody = $('#revenueChargesTable');
                tbody.empty();

                var totalAmount = 0;
                var totalBaseAmount = 0;

                if (revenueCharges.length === 0) {
                    tbody.append('<tr><td colspan="9" class="text-center text-muted">No charges added</td></tr>');
                } else {
                    revenueCharges.forEach(function(charge, index) {
                        totalAmount += parseFloat(charge.amount);
                        totalBaseAmount += parseFloat(charge.baseAmount);

                        var row = `
                    <tr>
                        <td>${charge.chargeName}</td>
                        <td class="text-right">${parseFloat(charge.quantity).toFixed(2)}</td>
                        <td>${charge.basedOn}</td>
                        <td>${charge.uom}</td>
                        <td class="text-right">${parseFloat(charge.rate).toFixed(2)}</td>
                        <td>${charge.currency}</td>
                        <td class="text-right">${parseFloat(charge.amount).toFixed(2)}</td>
                        <td class="text-right">${parseFloat(charge.baseAmount).toFixed(2)}</td>
                        <td class="text-center">
                            <button type="button" class="btn btn-warning btn-action edit-revenue-charge" data-index="${index}">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button type="button" class="btn btn-danger btn-action delete-revenue-charge" data-index="${index}">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                `;
                        tbody.append(row);
                    });
                }

                $('#totalRevenueAmount').text(totalAmount.toFixed(2));
                $('#totalRevenueBaseAmount').text(totalBaseAmount.toFixed(2));
            }

            // Initialize the table
            renderRevenueCharges();

            // Sample data for cost charges
            var costCharges = [];

            // Function to render cost charges
            function renderCostCharges() {
                var tbody = $('#costChargesTable');
                tbody.empty();

                var totalAmount = 0;
                var totalBaseAmount = 0;

                if (costCharges.length === 0) {
                    tbody.append('<tr><td colspan="9" class="text-center text-muted">No charges added</td></tr>');
                } else {
                    costCharges.forEach(function(charge, index) {
                        totalAmount += parseFloat(charge.amount);
                        totalBaseAmount += parseFloat(charge.baseAmount);

                        var row = `
                    <tr>
                        <td>${charge.chargeName}</td>
                        <td class="text-right">${parseFloat(charge.quantity).toFixed(2)}</td>
                        <td>${charge.basedOn}</td>
                        <td>${charge.uom}</td>
                        <td class="text-right">${parseFloat(charge.rate).toFixed(2)}</td>
                        <td>${charge.currency}</td>
                        <td class="text-right">${parseFloat(charge.amount).toFixed(2)}</td>
                        <td class="text-right">${parseFloat(charge.baseAmount).toFixed(2)}</td>
                        <td class="text-center">
                            <button type="button" class="btn btn-warning btn-action edit-cost-charge" data-index="${index}">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button type="button" class="btn btn-danger btn-action delete-cost-charge" data-index="${index}">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                `;
                        tbody.append(row);
                    });
                }

                $('#totalCostAmount').text(totalAmount.toFixed(2));
                $('#totalCostBaseAmount').text(totalBaseAmount.toFixed(2));
            }

            // Initialize cost table
            renderCostCharges();

            // Delete revenue charge
            $(document).on('click', '.delete-revenue-charge', function() {
                var index = $(this).data('index');
                if (confirm('Are you sure you want to delete this charge?')) {
                    revenueCharges.splice(index, 1);
                    renderRevenueCharges();
                }
            });

            // Delete cost charge
            $(document).on('click', '.delete-cost-charge', function() {
                var index = $(this).data('index');
                if (confirm('Are you sure you want to delete this charge?')) {
                    costCharges.splice(index, 1);
                    renderCostCharges();
                }
            });

            // Edit revenue charge (placeholder - will open modal with pre-filled data)
            $(document).on('click', '.edit-revenue-charge', function() {
                var index = $(this).data('index');
                // TODO: Open modal with charge data pre-filled
                alert('Edit functionality will be implemented with backend integration');
            });

            // Edit cost charge (placeholder - will open modal with pre-filled data)
            $(document).on('click', '.edit-cost-charge', function() {
                var index = $(this).data('index');
                // TODO: Open modal with charge data pre-filled
                alert('Edit functionality will be implemented with backend integration');
            });
        });
    </script>
@stop
