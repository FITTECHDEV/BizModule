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
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="6" class="text-center text-muted">No charges added</td>
                                </tr>
                            </tbody>
                        </table>
                        <div class="mt-2">
                            <button type="button" class="btn btn-primary btn-sm">Add Charges</button>
                        </div>
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
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="6" class="text-center text-muted">No charges added</td>
                                </tr>
                            </tbody>
                        </table>
                        <div class="mt-2">
                            <button type="button" class="btn btn-primary btn-sm">Add Charges</button>
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
    </form>
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
    </style>
@stop

@section('js')
    <script>
        $(document).ready(function() {
            console.log('Booking Quotation View - Page Loaded');

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
        });
    </script>
@stop
