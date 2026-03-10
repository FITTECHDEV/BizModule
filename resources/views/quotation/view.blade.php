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
                <i class="fas fa-copy"></i> Duplicate
            </button>

            {{--
                Edit Booking button is only shown when the quotation is genuinely
                in-progress (Proposal, Pending, or no status set yet).
                For Won, Lost, Cancel, or any other terminal status it is hidden.
                This is based on the record's actual status, not which card was clicked.
            --}}
            @php
                $quoteStatus = $quotation->QuoteStatus ?? ($quotation->status ?? null);
                $isInProgress = in_array($quoteStatus, ['Proposal', 'Pending']) || is_null($quoteStatus);
            @endphp

            @if ($isInProgress)
                <a href="{{ route('quotation.edit', $quotation->ID) }}" class="btn btn-warning btn-sm">
                    <i class="fas fa-edit"></i> Edit Booking
                </a>
            @endif
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
                    @php
                        $documents = DB::table('Quote_Quotation_Document')
                            ->where('QuotationInfoID', $quotation->ID)
                            ->where('DocumentType', 'QuotationPDF')
                            ->where('IsDeleted', 0)
                            ->orderByDesc('CreatedOn')
                            ->get(['Name', 'FilePath', 'CreatedOn']);
                    @endphp

                    @if ($documents->isNotEmpty())
                        @foreach ($documents as $index => $doc)
                            @php
                                // Extract version from filename if possible (fallback to index+1)
                                preg_match('/_V(\d+)_/i', $doc->Name, $matches);
                                $version = $matches[1] ?? $index + 1;
                                $displayName = $doc->Name ?? "Quotation_{$quotation->bookingno}_V{$version}.pdf";
                                $downloadUrl = \Storage::disk('public')->exists($doc->FilePath)
                                    ? \Storage::disk('public')->url($doc->FilePath)
                                    : '#';
                            @endphp
                            <div class="form-group row mb-1">
                                <label class="col-sm-4 col-form-label">Version {{ $version }}</label>
                                <div class="col-sm-8">
                                    <a href="{{ $downloadUrl }}" class="text-primary" target="_blank" download>
                                        {{ $displayName }}
                                    </a>
                                    <small class="text-muted d-block">
                                        {{ \Carbon\Carbon::parse($doc->CreatedOn)->format('d M Y H:i') }}
                                    </small>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <p class="text-muted text-center mb-0">
                            No PDF versions generated yet
                        </p>
                    @endif
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

            {{--
                REVENUE CARD
                Show More/Less link sits in the card-header (matching edit page layout).
                .col-extra columns are hidden by default — JS toggles them per table.
            --}}
            <div class="card card-success">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <h3 class="card-title mb-0 mr-1">Revenue</h3>
                        <span class="text-white small">
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
            </div>

            {{--
                COST CARD
                Same structure as Revenue — Show More/Less in header.
            --}}
            <div class="card card-danger">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <h3 class="card-title mb-0 mr-1">Cost</h3>
                        <span class="text-white small">
                            Total: <strong id="costTotalHeaderAmount">0.00</strong>
                        </span>
                    </div>
                    <div class="d-flex align-items-center ml-auto">
                        <a href="#" class="text-white show-items-link" data-table="cost">
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

        /* Extra columns hidden by default — JS toggles visibility per table */
        .col-extra {
            display: none;
        }

        /* Show more/less link styling — matches edit page */
        .show-items-link {
            text-decoration: underline;
            font-size: 0.875rem;
        }

        .show-items-link:hover {
            opacity: 0.8;
        }

        #revenueTotalHeaderAmount,
        #costTotalHeaderAmount {
            font-size: 1.2rem;
            font-weight: 700;
            letter-spacing: 0.02em;
        }

        .table-responsive {
            overflow-x: auto;
        }

        .thead-light th {
            background-color: #e9ecef;
            border-color: #dee2e6;
            font-weight: 600;
            font-size: 0.85rem;
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
                $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Duplicating...');

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
                                .html('<i class="fas fa-copy"></i> Duplicate');
                        }
                    },
                    error: function(xhr) {
                        const msg = xhr.responseJSON ? xhr.responseJSON.message :
                            'An unexpected error occurred.';
                        alert('Error: ' + msg);
                        $btn.prop('disabled', false)
                            .html('<i class="fas fa-copy"></i> Duplicate');
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
            // After rendering, respects current expanded state of the toggle link
            // so re-loading charges does not collapse extra columns if user expanded them.
            // ============================================
            function renderRevenueChargesView(charges) {
                const tbody = $('#revenueChargesViewTable');
                tbody.empty();

                if (charges.length === 0) {
                    tbody.append(
                        '<tr><td colspan="7" class="text-center text-muted">No revenue charges found</td></tr>');
                    $('#revenueTotalHeaderAmount').text('0.00');
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

                // Respect current toggle state — if user had expanded, keep expanded after reload
                const revenueExpanded = $('.show-items-link[data-table="revenue"]').data('expanded') || false;
                if (!revenueExpanded) {
                    tbody.find('.col-extra').hide();
                }
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
                    $('#costTotalHeaderAmount').text('0.00');
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

                // Respect current toggle state
                const costExpanded = $('.show-items-link[data-table="cost"]').data('expanded') || false;
                if (!costExpanded) {
                    tbody.find('.col-extra').hide();
                }
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
            // SHOW MORE / SHOW LESS — matches edit page behaviour exactly:
            //   - link lives in the card header
            //   - scoped to each table independently via data-table
            //   - toggles thead .col-extra + tbody .col-extra together
            //   - tracks state via .data('expanded')
            // ============================================
            // Hide all extra columns on load (CSS also does this, belt-and-braces)
            $('.col-extra').hide();

            $('.show-items-link').on('click', function(e) {
                e.preventDefault();
                e.stopPropagation(); // prevent card-header collapse from firing

                const tableId = $(this).data('table');
                const $table = tableId === 'revenue' ?
                    $('#revenueChargesViewTable').closest('table') :
                    $('#costChargesViewTable').closest('table');
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

            // ============================================
            // CARD COLLAPSE
            // Card header click triggers collapse, but must not fire
            // when the user clicks the Show More link inside the header.
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
