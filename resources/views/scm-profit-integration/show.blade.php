@extends('adminlte::page')

@section('title', 'View Booking Quotation')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1><i class="fas fa-file-invoice"></i> Inquiry No - &lt;&lt;Booking No&gt;&gt;</h1>
            <p class="text-muted mb-0">View booking quotation details</p>
        </div>
        <div>
            <a href="#" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
            <a href="#" class="btn btn-warning btn-sm">
                <i class="fas fa-edit"></i> Edit Booking
            </a>
        </div>
    </div>
@stop

@section('content')

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
                            <input type="text" class="form-control form-control-sm" placeholder="<< Search Engine >>"
                                readonly>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-5 col-form-label">Status</label>
                        <div class="col-sm-7">
                            <input type="text" class="form-control form-control-sm" placeholder="<< Status >>" readonly>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-5 col-form-label">Sales person</label>
                        <div class="col-sm-7">
                            <input type="text" class="form-control form-control-sm" placeholder="<<username>>" readonly>
                        </div>
                    </div>
                    <div class="form-group row mb-0">
                        <label class="col-sm-5 col-form-label">Office</label>
                        <div class="col-sm-7">
                            <input type="text" class="form-control form-control-sm" placeholder="<<teamname>>" readonly>
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
                            <input type="text" class="form-control form-control-sm" placeholder="<<ServiceVerticalname>>"
                                readonly>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-5 col-form-label">Process</label>
                        <div class="col-sm-7">
                            <input type="text" class="form-control form-control-sm" placeholder="<<process>>" readonly>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-5 col-form-label">Origin Country</label>
                        <div class="col-sm-7">
                            <input type="text" class="form-control form-control-sm" placeholder="<<origincountry>>"
                                readonly>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-5 col-form-label">Destination Country</label>
                        <div class="col-sm-7">
                            <input type="text" class="form-control form-control-sm" placeholder="<<destinationcountry>>"
                                readonly>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-5 col-form-label">InCO Terms</label>
                        <div class="col-sm-7">
                            <input type="text" class="form-control form-control-sm" placeholder="<<incoterms>>" readonly>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-5 col-form-label">Shipment Mode</label>
                        <div class="col-sm-7">
                            <input type="text" class="form-control form-control-sm" placeholder="<<shipmentmode>>"
                                readonly>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-5 col-form-label">Transport Mode</label>
                        <div class="col-sm-7">
                            <input type="text" class="form-control form-control-sm" placeholder="<<TransportMode>>"
                                readonly>
                        </div>
                    </div>
                    <div class="form-group row mb-0">
                        <label class="col-sm-5 col-form-label">Service Class</label>
                        <div class="col-sm-7">
                            <input type="text" class="form-control form-control-sm" placeholder="<<ServiceClass>>"
                                readonly>
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
                            <input type="text" class="form-control form-control-sm" placeholder="<<commodity>>"
                                readonly>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-5 col-form-label">Scope Of Volume</label>
                        <div class="col-sm-7">
                            <input type="text" class="form-control form-control-sm" placeholder="<<scopevolume>>"
                                readonly>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-5 col-form-label">Cargo Volume</label>
                        <div class="col-sm-7">
                            <input type="text" class="form-control form-control-sm" placeholder="<<cargovolume>>"
                                readonly>
                        </div>
                    </div>
                    <div class="form-group row mb-0">
                        <label class="col-sm-5 col-form-label">Container List</label>
                        <div class="col-sm-7">
                            <input type="text" class="form-control form-control-sm" placeholder="<<containerlist>>"
                                readonly>
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
                            <input type="text" class="form-control form-control-sm" placeholder="ClientIntegration"
                                readonly>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-5 col-form-label">Boking No</label>
                        <div class="col-sm-7">
                            <input type="text" class="form-control form-control-sm" placeholder="SCMBookingNo"
                                readonly>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-5 col-form-label">Booking Integration</label>
                        <div class="col-sm-7">
                            <input type="text" class="form-control form-control-sm" placeholder="SCMINTStatus"
                                readonly>
                        </div>
                    </div>
                    <div class="form-group row mb-0">
                        <label class="col-sm-5 col-form-label">Booking XML</label>
                        <div class="col-sm-7">
                            <input type="text" class="form-control form-control-sm" placeholder="SCMBookingXML"
                                readonly>
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
                                        placeholder="<< companyname>>" readonly>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-4 col-form-label">Address 1</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control form-control-sm"
                                        placeholder="pri_partner_street" readonly>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-4 col-form-label">City</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control form-control-sm"
                                        placeholder="pri_partner_city" readonly>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-4 col-form-label">State</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control form-control-sm"
                                        placeholder="pri_partner_state" readonly>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-4 col-form-label">Contact Person</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control form-control-sm"
                                        placeholder="pri_partnername" readonly>
                                </div>
                            </div>
                            <div class="form-group row mb-0">
                                <label class="col-sm-4 col-form-label">Phone No</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control form-control-sm"
                                        placeholder="pri_partner_phon" readonly>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group row">
                                <label class="col-sm-4 col-form-label">Client ID</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control form-control-sm"
                                        placeholder="<<Client ID>>" readonly>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-4 col-form-label">Address 2</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control form-control-sm"
                                        placeholder="pri_partner_street2" readonly>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-4 col-form-label">Zip</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control form-control-sm"
                                        placeholder="pri_partner_zip" readonly>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-4 col-form-label">Country</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control form-control-sm"
                                        placeholder="pri_partner_countryname" readonly>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-4 col-form-label">Email ID</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control form-control-sm"
                                        placeholder="pri_partner_email" readonly>
                                </div>
                            </div>
                            <div class="form-group row mb-0">
                                <label class="col-sm-4 col-form-label">Industry</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control form-control-sm"
                                        placeholder="<<Industryname>>" readonly>
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
                        <a href="#" class="text-white">Show Items</a>
                    </div>
                </div>
                <div class="card-body">
                    <table class="table table-bordered table-sm">
                        <thead>
                            <tr>
                                <th>Charge Name</th>
                                <th>Rate</th>
                                <th>Based On</th>
                                <th>Amount</th>
                                <th>Based Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="5" class="text-center text-muted">No charges added</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Cost -->
            <div class="card card-danger">
                <div class="card-header">
                    <h3 class="card-title">Cost</h3>
                    <div class="card-tools">
                        <a href="#" class="text-white">Show Items</a>
                    </div>
                </div>
                <div class="card-body">
                    <table class="table table-bordered table-sm">
                        <thead>
                            <tr>
                                <th>Charge Name</th>
                                <th>Rate</th>
                                <th>Based On</th>
                                <th>Amount</th>
                                <th>Based Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="5" class="text-center text-muted">No charges added</td>
                            </tr>
                        </tbody>
                    </table>
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
                                <label class="col-sm-6 col-form-label">Exected Revenue</label>
                                <div class="col-sm-6">
                                    <input type="text" class="form-control form-control-sm" placeholder="expected_"
                                        readonly>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-6 col-form-label">Quoted Revenue</label>
                                <div class="col-sm-6">
                                    <input type="text" class="form-control form-control-sm" placeholder="Based on"
                                        readonly>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-6 col-form-label">Actual Revenue</label>
                                <div class="col-sm-6">
                                    <input type="text" class="form-control form-control-sm" placeholder="Blank"
                                        readonly>
                                </div>
                            </div>
                            <div class="form-group row mb-0">
                                <label class="col-sm-6 col-form-label">Terms & Condition Section</label>
                                <div class="col-sm-6">
                                    <input type="text" class="form-control form-control-sm" readonly>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group row">
                                <label class="col-sm-6 col-form-label">Quoted Margin</label>
                                <div class="col-sm-6">
                                    <input type="text" class="form-control form-control-sm"
                                        placeholder="Based on Calculation" readonly>
                                </div>
                            </div>
                            <div class="form-group row mb-0">
                                <label class="col-sm-6 col-form-label">Actual Margin</label>
                                <div class="col-sm-6">
                                    <input type="text" class="form-control form-control-sm" placeholder="Blank"
                                        readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

@stop

@section('css')
    <style>
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

        /* Table styling */
        .table-sm th,
        .table-sm td {
            padding: 0.3rem;
        }

        /* Links in card headers */
        .card-tools a {
            text-decoration: underline;
        }

        .card-tools a:hover {
            opacity: 0.8;
        }

        /* Make card header clickable for collapse */
        .card-header {
            cursor: pointer;
            user-select: none;
        }

        .card-header:hover {
            opacity: 0.95;
        }
    </style>
@stop

@section('js')
    <script>
        $(document).ready(function() {
            console.log('Booking Quotation View - Page Loaded');

            // ============================================
            // CARD COLLAPSE
            // ============================================

            // Handle collapse arrow rotation
            $('[data-card-widget="collapse"]').on('click', function() {
                var icon = $(this).find('i');
                icon.toggleClass('fa-chevron-down fa-chevron-up');
            });

            // Make entire card header clickable for collapse
            $('.card-header').on('click', function(e) {
                // Don't trigger if clicking the collapse button itself
                if (!$(e.target).closest('[data-card-widget="collapse"]').length) {
                    $(this).find('[data-card-widget="collapse"]').click();
                }
            });

            // ============================================
            // INITIALIZE
            // ============================================

            // Collapse all cards by default EXCEPT Revenue and Cost
            $('.card').not('.card-success, .card-danger').each(function() {
                var $card = $(this);
                var $cardBody = $card.find('.card-body');
                var $icon = $card.find('[data-card-widget="collapse"] i');

                // Hide card body
                $cardBody.hide();

                // Change icon to down arrow
                $icon.removeClass('fa-chevron-up').addClass('fa-chevron-down');

                // Add collapsed-card class
                $card.addClass('collapsed-card');
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
        });
    </script>
@stop
