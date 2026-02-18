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
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">Revenue</h3>
                    <div class="d-flex align-items-center ml-auto">
                        <span class="text-white mr-3 small">
                            Total: <strong id="revenueTotalHeaderAmount">0.00</strong>
                        </span>
                        <a href="#" class="text-white show-items-link" data-table="revenue">
                            <i class="fas fa-columns mr-1"></i> Show More
                        </a>
                    </div>
                </div>
                <div class="card-body">
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
                            </tr>
                        </thead>
                        <tbody id="revenueChargesViewTable">
                            <tr>
                                <td colspan="7" class="text-center text-muted">Loading charges...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Cost -->
            <div class="card card-danger">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">Cost</h3>
                    <div class="d-flex align-items-center ml-auto">
                        <span class="text-white mr-3 small">
                            Total: <strong id="costTotalHeaderAmount">0.00</strong>
                        </span>
                        <a href="#" class="text-white show-items-link" data-table="cost">
                            <i class="fas fa-columns mr-1"></i> Show More
                        </a>
                    </div>
                </div>
                <div class="card-body">
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
                            </tr>
                        </thead>
                        <tbody id="costChargesViewTable">
                            <tr>
                                <td colspan="7" class="text-center text-muted">Loading charges...</td>
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
                                    <input type="text" class="form-control form-control-sm" id="viewExpectedRevenue"
                                        value="{{ $quotation->expected_revenue ?? 'N/A' }}" readonly>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-6 col-form-label">Quoted Revenue</label>
                                <div class="col-sm-6">
                                    <input type="text" class="form-control form-control-sm" id="viewQuotedRevenue"
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
                                <label class="col-sm-6 col-form-label">Terms & Condition</label>
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
                                    <input type="text" class="form-control form-control-sm" id="viewQuotedMargin"
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

@stop

@section('css')
    <style>
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

        .form-control-sm {
            height: calc(1.5em + 0.5rem + 2px);
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }

        .form-control[readonly] {
            background-color: transparent;
            border: none;
            cursor: default;
            padding-left: 0;
            font-weight: 500;
            color: #333;
        }

        .form-control[readonly]:focus {
            outline: none;
            box-shadow: none;
        }

        .form-group.row {
            margin-bottom: 0.5rem;
        }

        .card-body {
            padding: 1rem;
        }

        .table-sm th,
        .table-sm td {
            padding: 0.3rem;
        }

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

            const bookingNo = '{{ $quotation->bookingno ?? '' }}';

            // ============================================
            // COPY BOOKING — INLINE LINK RESULT
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
                            $btn.replaceWith(`
                                <a href="${response.redirect_url}"
                                   class="btn btn-success btn-sm"
                                   id="newInquiryLink">
                                    <i class="fas fa-check-circle mr-1"></i>
                                    Copied — Inquiry No: ${response.new_booking_no}
                                </a>
                            `);
                        } else {
                            alert('Failed to copy booking: ' + response.message);
                            $btn.prop('disabled', false)
                                .html('<i class="fas fa-copy"></i> Copy Booking');
                        }
                    },
                    error: function(xhr) {
                        const msg = xhr.responseJSON ? xhr.responseJSON.message :
                            'An unexpected error occurred.';
                        alert('Error: ' + msg);
                        $btn.prop('disabled', false)
                            .html('<i class="fas fa-copy"></i> Copy Booking');
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
                $.ajax({
                    url: '{{ route('quotation.charges') }}',
                    type: 'GET',
                    data: {
                        booking_no: bookingNo
                    },
                    beforeSend: function() {
                        $('#revenueChargesViewTable').html(
                            '<tr><td colspan="7" class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading...</td></tr>'
                        );
                        $('#costChargesViewTable').html(
                            '<tr><td colspan="7" class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading...</td></tr>'
                        );
                    },
                    success: function(response) {
                        if (response.success) {
                            renderRevenueChargesView(response.revenue || []);
                            renderCostChargesView(response.cost || []);
                            updateViewSummary(response.revenue || [], response.cost || []);
                        } else {
                            $('#revenueChargesViewTable').html(
                                '<tr><td colspan="7" class="text-center text-danger">Failed to load charges</td></tr>'
                            );
                            $('#costChargesViewTable').html(
                                '<tr><td colspan="7" class="text-center text-danger">Failed to load charges</td></tr>'
                            );
                        }
                    },
                    error: function() {
                        $('#revenueChargesViewTable').html(
                            '<tr><td colspan="7" class="text-center text-danger">Error loading charges</td></tr>'
                        );
                        $('#costChargesViewTable').html(
                            '<tr><td colspan="7" class="text-center text-danger">Error loading charges</td></tr>'
                        );
                    }
                });
            }

            // ============================================
            // RENDER REVENUE CHARGES (VIEW-ONLY)
            // ============================================
            function renderRevenueChargesView(charges) {
                const tbody = $('#revenueChargesViewTable');
                tbody.empty();

                if (charges.length === 0) {
                    tbody.append(
                        '<tr><td colspan="7" class="text-center text-muted">No revenue charges found</td></tr>');
                    return;
                }

                let totalBaseAmount = 0;
                charges.forEach(function(charge) {
                    totalBaseAmount += parseFloat(charge.amtInBaseCurr || 0);
                    tbody.append(`
                        <tr>
                            <td>${charge.chargeName || charge.chargeCode || 'N/A'}</td>
                            <td class="text-right">${parseFloat(charge.rate || 0).toFixed(2)}</td>
                            <td class="text-right col-extra">${parseFloat(charge.quantity || 0).toFixed(2)}</td>
                            <td class="col-extra">${charge.basedOnUOM || 'N/A'}</td>
                            <td class="col-extra">${charge.chgCurr || 'KES'}</td>
                            <td class="text-right">${parseFloat(charge.amtInChgCurr || 0).toFixed(2)}</td>
                            <td class="text-right">${parseFloat(charge.amtInBaseCurr || 0).toFixed(2)}</td>
                        </tr>
                    `);
                });

                $('#revenueTotalHeaderAmount').text(totalBaseAmount.toFixed(2));
            }

            // ============================================
            // RENDER COST CHARGES (VIEW-ONLY)
            // ============================================
            function renderCostChargesView(charges) {
                const tbody = $('#costChargesViewTable');
                tbody.empty();

                if (charges.length === 0) {
                    tbody.append(
                        '<tr><td colspan="7" class="text-center text-muted">No cost charges found</td></tr>');
                    return;
                }

                let totalBaseAmount = 0;
                charges.forEach(function(charge) {
                    totalBaseAmount += parseFloat(charge.amtInBaseCurr || 0);
                    tbody.append(`
                        <tr>
                            <td>${charge.chargeName || charge.chargeCode || 'N/A'}</td>
                            <td class="text-right">${parseFloat(charge.rate || 0).toFixed(2)}</td>
                            <td class="text-right col-extra">${parseFloat(charge.quantity || 0).toFixed(2)}</td>
                            <td class="col-extra">${charge.basedOnUOM || 'N/A'}</td>
                            <td class="col-extra">${charge.chgCurr || 'KES'}</td>
                            <td class="text-right">${parseFloat(charge.amtInChgCurr || 0).toFixed(2)}</td>
                            <td class="text-right">${parseFloat(charge.amtInBaseCurr || 0).toFixed(2)}</td>
                        </tr>
                    `);
                });

                $('#costTotalHeaderAmount').text(totalBaseAmount.toFixed(2));
            }

            // ============================================
            // UPDATE SUMMARY
            // ============================================
            function updateViewSummary(revenueCharges, costCharges) {
                const totalRevenue = revenueCharges.reduce((sum, c) => sum + parseFloat(c.amtInBaseCurr || 0), 0);
                const totalCost = costCharges.reduce((sum, c) => sum + parseFloat(c.amtInBaseCurr || 0), 0);
                const margin = totalRevenue - totalCost;
                const marginPct = totalRevenue > 0 ? (margin / totalRevenue * 100) : 0;

                $('#viewQuotedRevenue').val('KES ' + totalRevenue.toFixed(2));
                $('#viewQuotedMargin').val('KES ' + margin.toFixed(2) + ' (' + marginPct.toFixed(2) + '%)');
            }

            // ============================================
            // SHOW MORE / SHOW LESS TOGGLE
            // ============================================
            $('.col-extra').hide();

            $('.show-items-link').on('click', function(e) {
                e.preventDefault();
                e.stopPropagation();

                const tableId = $(this).data('table');
                const $table = tableId === 'revenue' ?
                    $('#revenueChargesViewTable').closest('table') :
                    $('#costChargesViewTable').closest('table');

                const $extraCols = $table.find('.col-extra');
                const isExpanded = $(this).data('expanded') || false;

                if (isExpanded) {
                    $extraCols.hide();
                    $(this).html('<i class="fas fa-columns mr-1"></i> Show More');
                    $(this).data('expanded', false);
                } else {
                    $extraCols.show();
                    $(this).html('<i class="fas fa-columns mr-1"></i> Show Less');
                    $(this).data('expanded', true);
                }
            });

            // ============================================
            // CARD COLLAPSE
            // ============================================
            $('[data-card-widget="collapse"]').on('click', function() {
                $(this).find('i').toggleClass('fa-chevron-down fa-chevron-up');
            });

            $('.card-header').on('click', function(e) {
                if (!$(e.target).closest('[data-card-widget="collapse"]').length &&
                    !$(e.target).closest('.show-items-link').length) {
                    $(this).find('[data-card-widget="collapse"]').click();
                }
            });

            // ============================================
            // INITIALIZE — collapse all except Revenue, Cost, Summary
            // ============================================
            $('.card').not('.card-success, .card-danger, .summary-card').each(function() {
                $(this).find('.card-body').hide();
                $(this).find('[data-card-widget="collapse"] i')
                    .removeClass('fa-chevron-up').addClass('fa-chevron-down');
                $(this).addClass('collapsed-card');
            });

        });
    </script>
@stop
