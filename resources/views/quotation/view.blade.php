@extends('adminlte::page')

@section('title', 'View Booking Quotation')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1><i class="fas fa-file-invoice"></i> Inquiry No - {{ $quotation->bookingno ?? 'N/A' }}</h1>
            <p class="text-muted mb-0">View booking quotation details</p>
        </div>
        <div>
            <a href="{{ route('quotation.index') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>

            <button type="button" class="btn btn-info btn-sm" id="btnCopyBooking" data-bookingno="{{ $quotation->bookingno }}">
                <i class="fas fa-copy"></i> Copy Booking
            </button>

            <a href="{{ route('quotation.edit', $quotation->ID) }}" class="btn btn-warning btn-sm">
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
                            <input type="text" class="form-control form-control-sm"
                                value="{{ $quotation->source ?? 'N/A' }}" readonly>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-5 col-form-label">Status</label>
                        <div class="col-sm-7">
                            <input type="text" class="form-control form-control-sm"
                                value="{{ $quotation->status ?? 'N/A' }}" readonly>
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
                                value="{{ $quotation->serviceverticalname ?? 'N/A' }}" readonly>
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
                                value="{{ $quotation->incoterm ?? 'N/A' }}" readonly>
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
                            <input type="text" class="form-control form-control-sm"
                                value="{{ $quotation->SCMBookingXML ?? 'N/A' }}" readonly>
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
                                        value="{{ $quotation->pri_partner_steet ?? 'N/A' }}" readonly>
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
                                        value="{{ $quotation->pri_partner_statename ?? 'N/A' }}" readonly>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-4 col-form-label">Contact Person</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control form-control-sm"
                                        value="{{ $quotation->pri_partnermaster_name ?? 'N/A' }}" readonly>
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
                                        value="{{ $quotation->pri_partner_steet2 ?? 'N/A' }}" readonly>
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
                                        value="{{ $quotation->industryname ?? 'N/A' }}" readonly>
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
            <div class="card card-primary summary-card">
                <div class="card-header">
                    <h3 class="card-title">Summary</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group row">
                                <label class="col-sm-6 col-form-label">Expected Revenue</label>
                                <div class="col-sm-6">
                                    <input type="text" class="form-control form-control-sm"
                                        value="{{ $quotation->expected_revenue ?? 'N/A' }}" readonly>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-6 col-form-label">Quoted Revenue</label>
                                <div class="col-sm-6">
                                    <input type="text" class="form-control form-control-sm"
                                        value="{{ $quotation->quoted_revenue ?? 'N/A' }}" readonly>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-6 col-form-label">Actual Revenue</label>
                                <div class="col-sm-6">
                                    <input type="text" class="form-control form-control-sm" value="N/A" readonly>
                                </div>
                            </div>
                            <div class="form-group row mb-0">
                                <label class="col-sm-6 col-form-label">Terms & Condition Section</label>
                                <div class="col-sm-6">
                                    <input type="text" class="form-control form-control-sm"
                                        value="{{ $quotation->terms_condition ?? 'N/A' }}" readonly>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group row">
                                <label class="col-sm-6 col-form-label">Quoted Margin</label>
                                <div class="col-sm-6">
                                    <input type="text" class="form-control form-control-sm"
                                        value="{{ $quotation->quoted_margin ?? 'N/A' }}" readonly>
                                </div>
                            </div>
                            <div class="form-group row mb-0">
                                <label class="col-sm-6 col-form-label">Actual Margin</label>
                                <div class="col-sm-6">
                                    <input type="text" class="form-control form-control-sm" value="N/A" readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ============================================ --}}
    {{-- COPY BOOKING SUCCESS MODAL                   --}}
    {{-- ============================================ --}}
    <div class="modal fade" id="copySuccessModal" tabindex="-1" role="dialog" data-backdrop="static"
        data-keyboard="false">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header" style="background-color: #4B49AC;">
                    <h5 class="modal-title text-white">
                        <i class="fas fa-check-circle"></i> Booking Copied Successfully
                    </h5>
                </div>
                <div class="modal-body text-center py-4">
                    <p class="mb-1 text-muted">A new inquiry has been created:</p>
                    <h2 class="font-weight-bold mt-2" id="newBookingNoDisplay" style="color: #4B49AC;"></h2>
                    <p class="text-muted mt-2 mb-0">Click below to open the new booking.</p>
                </div>
                <div class="modal-footer justify-content-center">
                    <a href="#" id="btnGoToNewBooking" class="btn btn-primary">
                        <i class="fas fa-arrow-right"></i> Open New Booking
                    </a>
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
            // FLASH MESSAGES FROM COPY/REDIRECT
            // ============================================
            @if (session('success'))
                alert('{{ session('success') }}');
            @endif
            @if (session('error'))
                alert('{{ session('error') }}');
            @endif

            const bookingNo = '{{ $quotation->bookingno ?? '' }}';

            // ============================================
            // COPY BOOKING - AJAX + MODAL
            // ============================================
            $('#btnCopyBooking').on('click', function() {
                if (!confirm('Copy booking ' + bookingNo + '? A new inquiry will be created.')) {
                    return;
                }

                const $btn = $(this);
                $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Copying...');

                $.ajax({
                    url: '/quotation/' + bookingNo + '/copy',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#newBookingNoDisplay').text('Inquiry No: ' + response
                                .new_booking_no);
                            $('#btnGoToNewBooking').attr('href', response.redirect_url);
                            $('#copySuccessModal').modal('show');
                        } else {
                            alert('Failed to copy booking: ' + response.message);
                        }
                    },
                    error: function(xhr) {
                        const msg = xhr.responseJSON ? xhr.responseJSON.message :
                            'An unexpected error occurred.';
                        alert('Error: ' + msg);
                    },
                    complete: function() {
                        $btn.prop('disabled', false).html(
                            '<i class="fas fa-copy"></i> Copy Booking');
                    }
                });
            });

            // ============================================
            // LOAD CHARGES ON PAGE LOAD
            // ============================================
            if (bookingNo) {
                loadChargesForView();
            } else {
                console.error('No booking number found');
            }

            function loadChargesForView() {
                console.log('Loading charges for booking:', bookingNo);

                $.ajax({
                    url: '{{ route('quotation.charges') }}',
                    type: 'GET',
                    data: {
                        booking_no: bookingNo
                    },
                    beforeSend: function() {
                        $('.card-success tbody').html(
                            '<tr><td colspan="5" class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading charges...</td></tr>'
                        );
                        $('.card-danger tbody').html(
                            '<tr><td colspan="5" class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading charges...</td></tr>'
                        );
                    },
                    success: function(response) {
                        console.log('Charges loaded:', response);
                        if (response.success) {
                            renderRevenueChargesView(response.revenue || []);
                            renderCostChargesView(response.cost || []);
                            updateViewSummary(response.revenue || [], response.cost || []);
                        } else {
                            console.error('Failed to load charges');
                            $('.card-success tbody').html(
                                '<tr><td colspan="5" class="text-center text-danger">Failed to load charges</td></tr>'
                            );
                            $('.card-danger tbody').html(
                                '<tr><td colspan="5" class="text-center text-danger">Failed to load charges</td></tr>'
                            );
                        }
                    },
                    error: function(xhr) {
                        console.error('Error loading charges:', xhr);
                        $('.card-success tbody').html(
                            '<tr><td colspan="5" class="text-center text-danger">Error loading charges</td></tr>'
                        );
                        $('.card-danger tbody').html(
                            '<tr><td colspan="5" class="text-center text-danger">Error loading charges</td></tr>'
                        );
                    }
                });
            }

            // ============================================
            // RENDER REVENUE CHARGES (VIEW-ONLY)
            // ============================================
            function renderRevenueChargesView(charges) {
                const tbody = $('.card-success tbody');
                tbody.empty();

                if (charges.length === 0) {
                    tbody.append(
                        '<tr><td colspan="5" class="text-center text-muted">No revenue charges found</td></tr>');
                    return;
                }

                charges.forEach(function(charge) {
                    const row = `
                        <tr>
                            <td>${charge.chargeName || charge.chargeCode || 'N/A'}</td>
                            <td class="text-right">${parseFloat(charge.rate || 0).toFixed(2)}</td>
                            <td>${charge.basedOn || 'N/A'}</td>
                            <td class="text-right">${charge.chgCurr || 'KES'} ${parseFloat(charge.amtInChgCurr || 0).toFixed(2)}</td>
                            <td class="text-right">KES ${parseFloat(charge.amtInBaseCurr || 0).toFixed(2)}</td>
                        </tr>
                    `;
                    tbody.append(row);
                });

                const totalAmount = charges.reduce((sum, charge) => sum + parseFloat(charge.amtInChgCurr || 0), 0);
                const totalBaseAmount = charges.reduce((sum, charge) => sum + parseFloat(charge.amtInBaseCurr || 0),
                    0);

                tbody.append(`
                    <tr class="font-weight-bold bg-light">
                        <td colspan="3" class="text-right">TOTAL REVENUE</td>
                        <td class="text-right">KES ${totalAmount.toFixed(2)}</td>
                        <td class="text-right">KES ${totalBaseAmount.toFixed(2)}</td>
                    </tr>
                `);
            }

            // ============================================
            // RENDER COST CHARGES (VIEW-ONLY)
            // ============================================
            function renderCostChargesView(charges) {
                const tbody = $('.card-danger tbody');
                tbody.empty();

                if (charges.length === 0) {
                    tbody.append(
                        '<tr><td colspan="5" class="text-center text-muted">No cost charges found</td></tr>');
                    return;
                }

                charges.forEach(function(charge) {
                    const row = `
                        <tr>
                            <td>${charge.chargeName || charge.chargeCode || 'N/A'}</td>
                            <td class="text-right">${parseFloat(charge.rate || 0).toFixed(2)}</td>
                            <td>${charge.basedOn || 'N/A'}</td>
                            <td class="text-right">${charge.chgCurr || 'KES'} ${parseFloat(charge.amtInChgCurr || 0).toFixed(2)}</td>
                            <td class="text-right">KES ${parseFloat(charge.amtInBaseCurr || 0).toFixed(2)}</td>
                        </tr>
                    `;
                    tbody.append(row);
                });

                const totalAmount = charges.reduce((sum, charge) => sum + parseFloat(charge.amtInChgCurr || 0), 0);
                const totalBaseAmount = charges.reduce((sum, charge) => sum + parseFloat(charge.amtInBaseCurr || 0),
                    0);

                tbody.append(`
                    <tr class="font-weight-bold bg-light">
                        <td colspan="3" class="text-right">TOTAL COST</td>
                        <td class="text-right">KES ${totalAmount.toFixed(2)}</td>
                        <td class="text-right">KES ${totalBaseAmount.toFixed(2)}</td>
                    </tr>
                `);
            }

            // ============================================
            // UPDATE SUMMARY SECTION WITH ACTUAL DATA
            // ============================================
            function updateViewSummary(revenueCharges, costCharges) {
                const totalRevenue = revenueCharges.reduce((sum, charge) =>
                    sum + parseFloat(charge.amtInBaseCurr || 0), 0);
                const totalCost = costCharges.reduce((sum, charge) =>
                    sum + parseFloat(charge.amtInBaseCurr || 0), 0);
                const margin = totalRevenue - totalCost;
                const marginPercentage = totalRevenue > 0 ? (margin / totalRevenue * 100) : 0;

                $('input[value*="Expected Revenue"]').closest('.form-group').find('.col-sm-6 input').val('KES ' +
                    totalRevenue.toFixed(2));
                $('input[value*="Quoted Revenue"]').closest('.form-group').find('.col-sm-6 input').val('KES ' +
                    totalRevenue.toFixed(2));
                $('input[value*="Quoted Margin"]').closest('.form-group').find('.col-sm-6 input').val('KES ' +
                    margin.toFixed(2) + ' (' + marginPercentage.toFixed(2) + '%)');
            }

            // ============================================
            // CARD COLLAPSE FUNCTIONALITY
            // ============================================
            $('[data-card-widget="collapse"]').on('click', function() {
                var icon = $(this).find('i');
                icon.toggleClass('fa-chevron-down fa-chevron-up');
            });

            $('.card-header').on('click', function(e) {
                if (!$(e.target).closest('[data-card-widget="collapse"]').length) {
                    $(this).find('[data-card-widget="collapse"]').click();
                }
            });

            // ============================================
            // SHOW/HIDE ITEMS TOGGLE
            // ============================================
            $('.card-tools a').on('click', function(e) {
                e.preventDefault();
                e.stopPropagation();

                const $card = $(this).closest('.card');
                const $tableWrapper = $card.find('table').closest('div');
                const $link = $(this);

                if ($tableWrapper.is(':visible')) {
                    $tableWrapper.slideUp(300);
                    $link.text('Show Items');
                } else {
                    $tableWrapper.slideDown(300);
                    $link.text('Hide Items');
                }
            });

            // ============================================
            // INITIALIZE - Collapse all cards by default EXCEPT Revenue, Cost and Summary
            // ============================================
            $('.card').not('.card-success, .card-danger, .summary-card').each(function() {
                var $card = $(this);
                var $cardBody = $card.find('.card-body');
                var $icon = $card.find('[data-card-widget="collapse"] i');

                $cardBody.hide();
                $icon.removeClass('fa-chevron-up').addClass('fa-chevron-down');
                $card.addClass('collapsed-card');
            });

            $('.summary-card').each(function() {
                var $card = $(this);
                $card.removeClass('collapsed-card');
                $card.find('.card-body').show();
            });

            $('.card').each(function() {
                var card = $(this);
                var collapseBtn = card.find('[data-card-widget="collapse"] i');

                if (card.find('.card-body').is(':visible')) {
                    collapseBtn.removeClass('fa-minus').addClass('fa-chevron-up');
                } else {
                    collapseBtn.removeClass('fa-minus').addClass('fa-chevron-down');
                }
            });

        });
    </script>
@stop
