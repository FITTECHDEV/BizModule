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
                                value="{{ $quotation->source ?? '' }}" readonly>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-5 col-form-label">Status</label>
                        <div class="col-sm-7">
                            <input type="text" class="form-control form-control-sm"
                                value="{{ $quotation->status ?? '' }}" readonly>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-5 col-form-label">Sales person</label>
                        <div class="col-sm-7">
                            <input type="text" class="form-control form-control-sm"
                                value="{{ $quotation->username ?? '' }}" readonly>
                        </div>
                    </div>
                    <div class="form-group row mb-0">
                        <label class="col-sm-5 col-form-label">Office</label>
                        <div class="col-sm-7">
                            <input type="text" class="form-control form-control-sm"
                                value="{{ $quotation->teamname ?? '' }}" readonly>
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
                    <div class="form-group row">
                        <label class="col-sm-5 col-form-label">InCO Terms</label>
                        <div class="col-sm-7">
                            <input type="text" class="form-control form-control-sm"
                                value="{{ $quotation->incoterm ?? '' }}" readonly>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-5 col-form-label">Shipment Mode</label>
                        <div class="col-sm-7">
                            <input type="text" class="form-control form-control-sm"
                                value="{{ $quotation->shipmentmode ?? '' }}" readonly>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-5 col-form-label">Transport Mode</label>
                        <div class="col-sm-7">
                            <input type="text" class="form-control form-control-sm"
                                value="{{ $quotation->TransportMode ?? '' }}" readonly>
                        </div>
                    </div>
                    <div class="form-group row mb-0">
                        <label class="col-sm-5 col-form-label">Service Class</label>
                        <div class="col-sm-7">
                            <input type="text" class="form-control form-control-sm"
                                value="{{ $quotation->ServiceClass ?? '' }}" readonly>
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
                            <input type="text" class="form-control form-control-sm"
                                value="{{ $quotation->cargovolume ?? '' }}" readonly>
                        </div>
                    </div>
                    <div class="form-group row mb-0">
                        <label class="col-sm-5 col-form-label">Container List</label>
                        <div class="col-sm-7">
                            <input type="text" class="form-control form-control-sm"
                                value="{{ $quotation->containerlist ?? '' }}" readonly>
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
                            <input type="text" class="form-control form-control-sm"
                                value="{{ $quotation->SCMBookingXML ?? '' }}" readonly>
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
                                        value="{{ $quotation->pri_partnermaster_name ?? '' }}" readonly>
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
                                        value="{{ $quotation->industryname ?? '' }}" readonly>
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
                                        value="{{ $quotation->expected_revenue ?? '' }}" readonly>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-6 col-form-label">Quoted Revenue</label>
                                <div class="col-sm-6">
                                    <input type="text" class="form-control form-control-sm" id="viewQuotedRevenue"
                                        value="{{ $quotation->quoted_revenue ?? '' }}" readonly>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-6 col-form-label">Actual Revenue</label>
                                <div class="col-sm-6">
                                    <input type="text" class="form-control form-control-sm" value="" readonly>
                                </div>
                            </div>
                            <div class="form-group row mb-0">
                                <label class="col-sm-6 col-form-label">Terms & Condition</label>
                                <div class="col-sm-6">
                                    <input type="text" class="form-control form-control-sm"
                                        value="{{ $quotation->terms_condition ?? '' }}" readonly>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group row">
                                <label class="col-sm-6 col-form-label">Quoted Margin</label>
                                <div class="col-sm-6">
                                    <input type="text" class="form-control form-control-sm" id="viewQuotedMargin"
                                        value="{{ $quotation->quoted_margin ?? '' }}" readonly>
                                </div>
                            </div>
                            <div class="form-group row mb-0">
                                <label class="col-sm-6 col-form-label">Actual Margin</label>
                                <div class="col-sm-6">
                                    <input type="text" class="form-control form-control-sm" value="" readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ============================================================ --}}
    {{-- DUPLICATE MODAL                                              --}}
    {{-- ============================================================ --}}
    <div class="modal fade" id="duplicateModal" tabindex="-1" role="dialog" data-backdrop="static"
        data-keyboard="false">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">

                <div class="modal-header" style="background:#4B49AC; color:white;">
                    <h5 class="modal-title">
                        <i class="fas fa-copy mr-2"></i>
                        Duplicate Inquiry &mdash; {{ $quotation->bookingno }}
                    </h5>
                    <button type="button" class="close text-white" id="dupCloseX"> <span>&times;</span>
                    </button>
                </div>

                <div class="modal-body" style="max-height:75vh; overflow-y:auto; padding:1rem;">

                    {{-- 1. INQUIRY DETAILS --}}
                    <div class="card card-primary mb-2">
                        <div class="card-header p-2" data-toggle="collapse" data-target="#dupInquiry"
                            style="cursor:pointer; background:#4B49AC; color:white;">
                            <h6 class="mb-0"><i class="fas fa-info-circle mr-2"></i>Inquiry Details</h6>
                        </div>
                        <div class="collapse show" id="dupInquiry">
                            <div class="card-body py-3">
                                <div class="form-group row mb-2">
                                    <label class="col-sm-3 col-form-label col-form-label-sm">Sales Person</label>
                                    <div class="col-sm-5 position-relative">
                                        <input type="text" id="dupSalesPersonDisplay"
                                            class="form-control form-control-sm" placeholder="Search sales person..."
                                            value="{{ $quotation->username ?? '' }}" autocomplete="off">
                                        <input type="hidden" id="dupSalesPersonName"
                                            value="{{ $quotation->username ?? '' }}">
                                        <ul id="dupSalesPersonSuggestions"
                                            class="dup-autocomplete-list list-unstyled mb-0"></ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- 2. SERVICE DETAILS --}}
                    <div class="card card-primary mb-2">
                        <div class="card-header p-2" data-toggle="collapse" data-target="#dupService"
                            style="cursor:pointer; background:#4B49AC; color:white;">
                            <h6 class="mb-0">
                                <i class="fas fa-ship mr-2"></i>Service Details
                                <span class="badge badge-light ml-1">All fields required</span>
                            </h6>
                        </div>
                        <div class="collapse show" id="dupService">
                            <div class="card-body py-3">
                                <div class="row">
                                    <div class="col-md-6">
                                        {{-- Origin --}}
                                        <div class="form-group row mb-2">
                                            <label class="col-sm-4 col-form-label col-form-label-sm">
                                                Origin <span class="text-danger">*</span>
                                            </label>
                                            <div class="col-sm-8 position-relative">
                                                <input type="text" id="dupOriginDisplay"
                                                    class="form-control form-control-sm dup-required"
                                                    placeholder="Search country..."
                                                    value="{{ $quotation->origincountry ?? '' }}" autocomplete="off">
                                                <input type="hidden" id="dupOriginCountryId"
                                                    value="{{ $quotation->origin_country_id ?? '' }}">
                                                <input type="hidden" id="dupOriginCountryCode"
                                                    value="{{ $quotation->origin_country_code ?? '' }}">
                                                <ul id="dupOriginSuggestions"
                                                    class="dup-autocomplete-list list-unstyled mb-0"></ul>
                                            </div>
                                        </div>
                                        {{-- InCo Term --}}
                                        <div class="form-group row mb-2">
                                            <label class="col-sm-4 col-form-label col-form-label-sm">
                                                InCo Term <span class="text-danger">*</span>
                                            </label>
                                            <div class="col-sm-8 position-relative">
                                                <input type="text" id="dupIncoTermDisplay"
                                                    class="form-control form-control-sm dup-required"
                                                    placeholder="Search inco term..."
                                                    value="{{ $quotation->incoterm ?? '' }}" autocomplete="off">
                                                <input type="hidden" id="dupIncoTermCode"
                                                    value="{{ $quotation->incoterm ?? '' }}">
                                                <ul id="dupIncoTermSuggestions"
                                                    class="dup-autocomplete-list list-unstyled mb-0"></ul>
                                            </div>
                                        </div>
                                        {{-- Transport Mode --}}
                                        <div class="form-group row mb-2">
                                            <label class="col-sm-4 col-form-label col-form-label-sm">
                                                Transport Mode <span class="text-danger">*</span>
                                            </label>
                                            <div class="col-sm-8 position-relative">
                                                <input type="text" id="dupTransportModeDisplay"
                                                    class="form-control form-control-sm dup-required"
                                                    placeholder="Search transport mode..."
                                                    value="{{ $quotation->TransportMode ?? '' }}" autocomplete="off">
                                                <input type="hidden" id="dupTransportModeCode"
                                                    value="{{ $quotation->TransportMode ?? '' }}">
                                                <ul id="dupTransportModeSuggestions"
                                                    class="dup-autocomplete-list list-unstyled mb-0"></ul>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        {{-- Destination --}}
                                        <div class="form-group row mb-2">
                                            <label class="col-sm-4 col-form-label col-form-label-sm">
                                                Destination <span class="text-danger">*</span>
                                            </label>
                                            <div class="col-sm-8 position-relative">
                                                <input type="text" id="dupDestDisplay"
                                                    class="form-control form-control-sm dup-required"
                                                    placeholder="Search country..."
                                                    value="{{ $quotation->destinationcountry ?? '' }}"
                                                    autocomplete="off">
                                                <input type="hidden" id="dupDestCountryId"
                                                    value="{{ $quotation->dest_country_id ?? '' }}">
                                                <input type="hidden" id="dupDestCountryCode"
                                                    value="{{ $quotation->destination_country_code ?? '' }}">
                                                <ul id="dupDestSuggestions"
                                                    class="dup-autocomplete-list list-unstyled mb-0"></ul>
                                            </div>
                                        </div>
                                        {{-- Shipment Mode --}}
                                        <div class="form-group row mb-2">
                                            <label class="col-sm-4 col-form-label col-form-label-sm">
                                                Shipment Mode <span class="text-danger">*</span>
                                            </label>
                                            <div class="col-sm-8 position-relative">
                                                <input type="text" id="dupShipmentModeDisplay"
                                                    class="form-control form-control-sm dup-required"
                                                    placeholder="Search shipment mode..."
                                                    value="{{ $quotation->shipmentmode ?? '' }}" autocomplete="off">
                                                <input type="hidden" id="dupShipmentModeCode"
                                                    value="{{ $quotation->shipmentmode ?? '' }}">
                                                <ul id="dupShipmentModeSuggestions"
                                                    class="dup-autocomplete-list list-unstyled mb-0"></ul>
                                            </div>
                                        </div>
                                        {{-- Service Class --}}
                                        <div class="form-group row mb-2">
                                            <label class="col-sm-4 col-form-label col-form-label-sm">
                                                Service Class <span class="text-danger">*</span>
                                            </label>
                                            <div class="col-sm-8 position-relative">
                                                <input type="text" id="dupServiceClassDisplay"
                                                    class="form-control form-control-sm dup-required"
                                                    placeholder="Search service class..."
                                                    value="{{ $quotation->ServiceClass ?? '' }}" autocomplete="off">
                                                <input type="hidden" id="dupServiceClassCode"
                                                    value="{{ $quotation->ServiceClass ?? '' }}">
                                                <ul id="dupServiceClassSuggestions"
                                                    class="dup-autocomplete-list list-unstyled mb-0"></ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- 3. CUSTOMER DETAILS --}}
                    <div class="card card-primary mb-2">
                        <div class="card-header p-2" data-toggle="collapse" data-target="#dupCustomer"
                            style="cursor:pointer; background:#4B49AC; color:white;">
                            <h6 class="mb-0"><i class="fas fa-building mr-2"></i>Customer Details</h6>
                        </div>
                        <div class="collapse show" id="dupCustomer">
                            <div class="card-body py-3">
                                {{-- Customer Type & Agent Type on top --}}
                                <div class="row mb-2">
                                    <div class="col-md-6">
                                        <div class="form-group row mb-2">
                                            <label class="col-sm-4 col-form-label col-form-label-sm">
                                                Customer Type <span class="text-danger">*</span>
                                            </label>
                                            <div class="col-sm-8">
                                                <select id="dupCustomerType"
                                                    class="form-control form-control-sm dup-required">
                                                    <option value="">-- Loading... --</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group row mb-2">
                                            <label class="col-sm-4 col-form-label col-form-label-sm">Agent Type</label>
                                            <div class="col-sm-8">
                                                <input type="text" id="dupAgentType"
                                                    class="form-control form-control-sm"
                                                    value="{{ $quotation->AgentType ?? '' }}">
                                                <input type="hidden" id="dupAgentTypeId"
                                                    value="{{ $quotation->AgentTypeId ?? '' }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <hr class="my-2">

                                {{-- Customer fields — editable only for New / New Agent --}}
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group row mb-2">
                                            <label class="col-sm-4 col-form-label col-form-label-sm">
                                                Company Name <span class="text-danger">*</span>
                                            </label>
                                            <div class="col-sm-8">
                                                <input type="text" id="dupCompanyName"
                                                    class="form-control form-control-sm dup-required"
                                                    value="{{ $quotation->companyname ?? '' }}" readonly>
                                            </div>
                                        </div>
                                        <div class="form-group row mb-2">
                                            <label class="col-sm-4 col-form-label col-form-label-sm">
                                                Address 1 <span class="text-danger">*</span>
                                            </label>
                                            <div class="col-sm-8">
                                                <input type="text" id="dupAddress1"
                                                    class="form-control form-control-sm dup-required"
                                                    value="{{ $quotation->pri_partner_steet ?? '' }}" readonly>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group row mb-2">
                                            <label class="col-sm-4 col-form-label col-form-label-sm">
                                                Country <span class="text-danger">*</span>
                                            </label>
                                            <div class="col-sm-8 position-relative">
                                                <input type="text" id="dupCustCountryDisplay"
                                                    class="form-control form-control-sm dup-required"
                                                    value="{{ $quotation->pri_partner_countryname ?? '' }}"
                                                    autocomplete="off" readonly>
                                                <input type="hidden" id="dupCustCountryId"
                                                    value="{{ $quotation->pri_partner_countryid ?? '' }}">
                                                <input type="hidden" id="dupCustCountryName"
                                                    value="{{ $quotation->pri_partner_countryname ?? '' }}">
                                                <ul id="dupCustCountrySuggestions"
                                                    class="dup-autocomplete-list list-unstyled mb-0"></ul>
                                            </div>
                                        </div>
                                        <div class="form-group row mb-2">
                                            <label class="col-sm-4 col-form-label col-form-label-sm">
                                                Industry Type <span class="text-danger">*</span>
                                            </label>
                                            <div class="col-sm-8 position-relative">
                                                <input type="text" id="dupIndustryDisplay"
                                                    class="form-control form-control-sm dup-required"
                                                    value="{{ $quotation->industryname ?? '' }}" autocomplete="off"
                                                    readonly>
                                                <input type="hidden" id="dupIndustryId"
                                                    value="{{ $quotation->industryid ?? '' }}">
                                                <input type="hidden" id="dupIndustryName"
                                                    value="{{ $quotation->industryname ?? '' }}">
                                                <ul id="dupIndustrySuggestions"
                                                    class="dup-autocomplete-list list-unstyled mb-0"></ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- 4. CARGO DETAILS --}}
                    <div class="card card-primary mb-2">
                        <div class="card-header p-2" data-toggle="collapse" data-target="#dupCargo"
                            style="cursor:pointer; background:#4B49AC; color:white;">
                            <h6 class="mb-0"><i class="fas fa-boxes mr-2"></i>Cargo Details</h6>
                        </div>
                        <div class="collapse show" id="dupCargo">
                            <div class="card-body py-3">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group row mb-2">
                                            <label class="col-sm-4 col-form-label col-form-label-sm">
                                                Commodity <span class="text-danger">*</span>
                                            </label>
                                            <div class="col-sm-8">
                                                <input type="text" id="dupCommodity"
                                                    class="form-control form-control-sm dup-required"
                                                    value="{{ $quotation->commodity ?? '' }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group row mb-2">
                                            <label class="col-sm-4 col-form-label col-form-label-sm">
                                                Scope of Volume <span class="text-danger">*</span>
                                            </label>
                                            <div class="col-sm-8">
                                                <select id="dupScopeOfVolume"
                                                    class="form-control form-control-sm dup-required">
                                                    <option value="">-- Select --</option>
                                                    <option value="Weight (in KGS)"
                                                        {{ ($quotation->scopevolume ?? '') === 'Weight (in KGS)' ? 'selected' : '' }}>
                                                        Weight (in KGS)
                                                    </option>
                                                    <option value="Volume (In CBM)"
                                                        {{ ($quotation->scopevolume ?? '') === 'Volume (In CBM)' ? 'selected' : '' }}>
                                                        Volume (In CBM)
                                                    </option>
                                                    <option value="Containerized"
                                                        {{ ($quotation->scopevolume ?? '') === 'Containerized' ? 'selected' : '' }}>
                                                        Containerized
                                                    </option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Cargo Volume — hidden when Containerized --}}
                                <div class="row" id="dupCargoVolumeRow">
                                    <div class="col-md-6">
                                        <div class="form-group row mb-2">
                                            <label class="col-sm-4 col-form-label col-form-label-sm">
                                                Cargo Volume <span class="text-danger">*</span>
                                            </label>
                                            <div class="col-sm-8">
                                                <input type="number" id="dupCargoVolume"
                                                    class="form-control form-control-sm" min="0.01" step="0.01"
                                                    value="{{ $quotation->cargovolume ?? '' }}"
                                                    placeholder="Numeric, non-zero">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Equipment — shown only when Containerized --}}
                                <div id="dupEquipmentSection" style="display:none;">
                                    <hr class="my-2">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <strong><i class="fas fa-truck-loading mr-1"></i> Equipment</strong>
                                        <button type="button" class="btn btn-sm btn-success" id="dupAddEquipmentBtn">
                                            <i class="fas fa-plus mr-1"></i> Add
                                        </button>
                                    </div>
                                    <table class="table table-sm table-bordered mb-0" id="dupEquipmentTable">
                                        <thead class="thead-light">
                                            <tr>
                                                <th style="width:55%">
                                                    Equipment Type <span class="text-danger">*</span>
                                                </th>
                                                <th style="width:30%">
                                                    Quantity <span class="text-danger">*</span>
                                                </th>
                                                <th style="width:15%"></th>
                                            </tr>
                                        </thead>
                                        <tbody id="dupEquipmentBody">
                                            <tr id="dupEquipmentEmpty">
                                                <td colspan="3" class="text-center text-muted py-2">
                                                    Click Add to add equipment
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                            </div>
                        </div>
                    </div>

                </div>{{-- /.modal-body --}}

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" id="dupCancelBtn">
                        <i class="fas fa-times mr-1"></i> Cancel
                    </button>
                    <button type="button" class="btn btn-primary" id="btnConfirmDuplicate">
                        <i class="fas fa-copy mr-1"></i> Duplicate
                    </button>
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

            // ============================================================
            // DUPLICATE — MODAL FLOW
            // ============================================================
            const officeId = '{{ $quotation->OfficeID ?? '' }}';

            $('#dupCloseX, #dupCancelBtn').on('click', function() {
                $('#duplicateModal').modal('hide');
            });

            $('#btnCopyBooking').off('click').on('click', function() {
                dupResetValidation();
                dupToggleScopeFields();
                dupLoadCustomerTypes().then(function() {
                    dupToggleCustomerFields();
                });
                $('#duplicateModal').modal('show');
            });

            function dupLoadCustomerTypes() {
                return $.ajax({
                    url: '/quotation/customer-types',
                    type: 'GET',
                }).then(function(response) {
                    const $select = $('#dupCustomerType');
                    const current = '{{ $quotation->CustomerType ?? '' }}';
                    $select.empty().append('<option value="">-- Select --</option>');

                    const types = response.success ? response.data : [];
                    types.forEach(function(item) {
                        const selected = item.CategoryName === current ? 'selected' : '';
                        $select.append(
                            `<option value="${item.CategoryName}" ${selected}>${item.CategoryName}</option>`
                        );
                    });

                    dupToggleCustomerFields();
                }).fail(function() {
                    // Fallback — restore hardcoded options if SP fails
                    const current = '{{ $quotation->CustomerType ?? '' }}';
                    const fallback = ['Existing Agent', 'New Agent', 'Existing Customer', 'New Customer',
                        'Contract Customer', 'Handover Job', 'Inter-Company'
                    ];
                    const $select = $('#dupCustomerType');
                    $select.empty().append('<option value="">-- Select --</option>');
                    fallback.forEach(function(name) {
                        $select.append(
                            `<option value="${name}" ${name === current ? 'selected' : ''}>${name}</option>`
                        );
                    });
                    dupToggleCustomerFields();
                });
            }

            // ---- Scope of Volume toggle ----
            function dupToggleScopeFields() {
                const scope = $('#dupScopeOfVolume').val();
                if (scope === 'Containerized') {
                    $('#dupCargoVolumeRow').hide();
                    $('#dupCargoVolume').val('');
                    $('#dupEquipmentSection').show();
                } else {
                    $('#dupCargoVolumeRow').show();
                    $('#dupEquipmentSection').hide();
                    dupClearEquipment();
                }
            }
            $('#dupScopeOfVolume').on('change', dupToggleScopeFields);

            // ---- Customer Type toggle ----
            function dupToggleCustomerFields() {
                const isNewAgent = $('#dupCustomerType').val() === 'New Agent';
                $('#dupCompanyName, #dupAddress1').prop('readonly', !isNewAgent);
                $('#dupCustCountryDisplay, #dupIndustryDisplay').prop('readonly', !isNewAgent);
            }
            $('#dupCustomerType').on('change', dupToggleCustomerFields);

            // ---- Equipment ----
            let dupEquipmentRows = [];

            function dupRenderEquipment() {
                const tbody = $('#dupEquipmentBody');
                tbody.empty();

                if (!dupEquipmentRows.length) {
                    tbody.append(`<tr id="dupEquipmentEmpty">
            <td colspan="3" class="text-center text-muted py-2">Click Add to add equipment</td>
        </tr>`);
                    return;
                }

                dupEquipmentRows.forEach(function(row, idx) {
                    tbody.append(`
            <tr data-idx="${idx}">
                <td class="position-relative">
                    <input type="text" class="form-control form-control-sm dup-eq-type-display"
                        data-idx="${idx}" value="${row.equipment_type}"
                        placeholder="Search equipment type..." autocomplete="off">
                    <ul class="dup-autocomplete-list dup-eq-suggestions list-unstyled mb-0"
                        data-idx="${idx}"></ul>
                </td>
                <td>
                    <input type="number" class="form-control form-control-sm dup-eq-qty"
                        data-idx="${idx}" value="${row.quantity}" min="1">
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-danger dup-eq-remove"
                        data-idx="${idx}">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `);
                });
            }

            function dupClearEquipment() {
                dupEquipmentRows = [];
                dupRenderEquipment();
            }

            $('#dupAddEquipmentBtn').on('click', function() {
                dupEquipmentRows.push({
                    equipment_type_id: '',
                    equipment_type: '',
                    quantity: 1
                });
                dupRenderEquipment();
            });

            $(document).on('click', '.dup-eq-remove', function() {
                dupEquipmentRows.splice(parseInt($(this).data('idx')), 1);
                dupRenderEquipment();
            });

            $(document).on('change', '.dup-eq-qty', function() {
                dupEquipmentRows[parseInt($(this).data('idx'))].quantity = parseInt($(this).val()) || 1;
            });

            // Equipment type autocomplete
            let dupEqTimer = null;
            $(document).on('input', '.dup-eq-type-display', function() {
                const idx = parseInt($(this).data('idx'));
                const val = $(this).val().trim();
                const $ul = $(`.dup-eq-suggestions[data-idx="${idx}"]`);

                dupEquipmentRows[idx].equipment_type_id = '';
                dupEquipmentRows[idx].equipment_type = val;

                clearTimeout(dupEqTimer);
                if (!val) {
                    $ul.hide().empty();
                    return;
                }

                dupEqTimer = setTimeout(function() {
                    $.get('{{ route('quotation.lookup.equipmentTypes') }}', {
                        filter: val
                    }, function(data) {
                        $ul.empty();
                        if (!data.length) {
                            $ul.hide();
                            return;
                        }
                        data.forEach(function(item) {
                            $ul.append(
                                `<li data-id="${item.Id}" data-code="${item.Code}">${item.Code}</li>`
                            );
                        });
                        $ul.show();
                    });
                }, 250);
            });

            $(document).on('mousedown', '.dup-eq-suggestions li', function() {
                const idx = parseInt($(this).closest('ul').data('idx'));
                const id = $(this).data('id');
                const code = $(this).data('code');

                dupEquipmentRows[idx].equipment_type_id = id;
                dupEquipmentRows[idx].equipment_type = code;

                $(`.dup-eq-type-display[data-idx="${idx}"]`).val(code);
                $(this).closest('ul').hide().empty();
            });

            // ---- Generic autocomplete builder ----
            function dupBind(inputId, ulId, url, extraParams, labelFn, onPick) {
                let timer = null;

                $(document).on('input', '#' + inputId, function() {
                    const val = $(this).val().trim();
                    clearTimeout(timer);
                    if (!val) {
                        $('#' + ulId).hide().empty();
                        return;
                    }

                    const params = Object.assign({
                        filter: val
                    }, extraParams);

                    timer = setTimeout(function() {
                        $.get(url, params, function(data) {
                            const $ul = $('#' + ulId);
                            $ul.empty();
                            if (!data.length) {
                                $ul.hide();
                                return;
                            }
                            data.forEach(function(item) {
                                $ul.append(
                                    `<li data-item='${JSON.stringify(item)}'>${labelFn(item)}</li>`
                                );
                            });
                            $ul.show();
                        });
                    }, 250);
                });

                $(document).on('mousedown', '#' + ulId + ' li', function() {
                    onPick(JSON.parse($(this).attr('data-item')));
                    $('#' + ulId).hide().empty();
                });

                $(document).on('blur', '#' + inputId, function() {
                    setTimeout(function() {
                        $('#' + ulId).hide();
                    }, 200);
                });
            }

            // Sales Person
            dupBind('dupSalesPersonDisplay', 'dupSalesPersonSuggestions',
                '{{ route('quotation.lookup.persons') }}', {
                    office_id: officeId,
                    pic_type: 'S'
                },
                item => item.PersonName,
                item => {
                    $('#dupSalesPersonDisplay').val(item.PersonName);
                    $('#dupSalesPersonName').val(item.PersonName);
                }
            );

            // Origin Country
            dupBind('dupOriginDisplay', 'dupOriginSuggestions',
                '{{ route('quotation.lookup.countries') }}', {},
                item => item.CountryName,
                item => {
                    $('#dupOriginDisplay').val(item.CountryName);
                    $('#dupOriginCountryId').val(item.CountryID);
                    $('#dupOriginCountryCode').val(item.CountCode);
                }
            );

            // Destination Country
            dupBind('dupDestDisplay', 'dupDestSuggestions',
                '{{ route('quotation.lookup.countries') }}', {},
                item => item.CountryName,
                item => {
                    $('#dupDestDisplay').val(item.CountryName);
                    $('#dupDestCountryId').val(item.CountryID);
                    $('#dupDestCountryCode').val(item.CountCode);
                }
            );

            // InCo Terms
            dupBind('dupIncoTermDisplay', 'dupIncoTermSuggestions',
                '{{ route('quotation.lookup.incoterms') }}', {},
                item => item.IncoTermCode + ' — ' + item.Name,
                item => {
                    $('#dupIncoTermDisplay').val(item.IncoTermCode);
                    $('#dupIncoTermCode').val(item.IncoTermCode);
                }
            );

            // Shipment Mode
            dupBind('dupShipmentModeDisplay', 'dupShipmentModeSuggestions',
                '{{ route('quotation.lookup.shipmentModes') }}', {},
                item => item.Code,
                item => {
                    $('#dupShipmentModeDisplay').val(item.Code);
                    $('#dupShipmentModeCode').val(item.Code);
                }
            );

            // Transport Mode
            dupBind('dupTransportModeDisplay', 'dupTransportModeSuggestions',
                '{{ route('quotation.lookup.transportModes') }}', {},
                item => (item.Name || item.Code),
                item => {
                    $('#dupTransportModeDisplay').val(item.Name || item.Code);
                    $('#dupTransportModeCode').val(item.Code);
                }
            );

            // Service Class
            dupBind('dupServiceClassDisplay', 'dupServiceClassSuggestions',
                '{{ route('quotation.lookup.serviceClasses') }}', {},
                item => item.Code + ' — ' + item.Description,
                item => {
                    $('#dupServiceClassDisplay').val(item.Code);
                    $('#dupServiceClassCode').val(item.Code);
                }
            );

            // Customer Country
            dupBind('dupCustCountryDisplay', 'dupCustCountrySuggestions',
                '{{ route('quotation.lookup.countries') }}', {},
                item => item.CountryName,
                item => {
                    $('#dupCustCountryDisplay').val(item.CountryName);
                    $('#dupCustCountryId').val(item.CountryID);
                    $('#dupCustCountryName').val(item.CountryName);
                }
            );

            // Industry Type
            dupBind('dupIndustryDisplay', 'dupIndustrySuggestions',
                '{{ route('quotation.lookup.industryTypes') }}', {},
                item => item.Name,
                item => {
                    $('#dupIndustryDisplay').val(item.Name);
                    $('#dupIndustryId').val(item.Id);
                    $('#dupIndustryName').val(item.Name);
                }
            );

            // ---- Validation ----
            function dupResetValidation() {
                $('.dup-required, #dupCargoVolume').removeClass('is-invalid');
            }

            function dupValidate() {
                let valid = true;

                $('.dup-required').each(function() {
                    const empty = !$(this).val() || !$(this).val().trim();
                    $(this).toggleClass('is-invalid', empty);
                    if (empty) valid = false;
                });

                const scope = $('#dupScopeOfVolume').val();

                if (scope && scope !== 'Containerized') {
                    const vol = parseFloat($('#dupCargoVolume').val());
                    const bad = !vol || vol <= 0;
                    $('#dupCargoVolume').toggleClass('is-invalid', bad);
                    if (bad) valid = false;
                }

                if (scope === 'Containerized') {
                    if (!dupEquipmentRows.length) {
                        toastr.error('Please add at least one equipment row.');
                        valid = false;
                    }
                    dupEquipmentRows.forEach(function(row, idx) {
                        if (!row.equipment_type_id) {
                            $(`.dup-eq-type-display[data-idx="${idx}"]`).addClass('is-invalid');
                            valid = false;
                        }
                        if (!row.quantity || row.quantity < 1) {
                            $(`.dup-eq-qty[data-idx="${idx}"]`).addClass('is-invalid');
                            valid = false;
                        }
                    });
                }

                return valid;
            }

            // ---- Submit ----
            $('#btnConfirmDuplicate').on('click', function() {
                if (!dupValidate()) {
                    toastr.error('Please fill in all required fields.');
                    return;
                }

                const $btn = $(this);
                $btn.prop('disabled', true)
                    .html('<i class="fas fa-spinner fa-spin mr-1"></i> Duplicating...');

                const payload = {
                    _token: '{{ csrf_token() }}',
                    sales_person_name: $('#dupSalesPersonName').val(),
                    origin_country_id: $('#dupOriginCountryId').val(),
                    origin_country_name: $('#dupOriginDisplay').val(),
                    origin_country_code: $('#dupOriginCountryCode').val(),
                    dest_country_id: $('#dupDestCountryId').val(),
                    dest_country_name: $('#dupDestDisplay').val(),
                    dest_country_code: $('#dupDestCountryCode').val(),
                    incoterm_code: $('#dupIncoTermCode').val(),
                    shipment_mode_code: $('#dupShipmentModeCode').val(),
                    transport_mode_code: $('#dupTransportModeCode').val(),
                    service_class_code: $('#dupServiceClassCode').val(),
                    customer_type: $('#dupCustomerType').val(),
                    agent_type_id: $('#dupAgentTypeId').val(),
                    agent_type: $('#dupAgentType').val(),
                    company_name: $('#dupCompanyName').val(),
                    address1: $('#dupAddress1').val(),
                    cust_country_id: $('#dupCustCountryId').val(),
                    cust_country_name: $('#dupCustCountryName').val(),
                    industry_type_id: $('#dupIndustryId').val(),
                    industry_type_name: $('#dupIndustryName').val(),
                    commodity: $('#dupCommodity').val(),
                    scope_of_volume: $('#dupScopeOfVolume').val(),
                    cargo_volume: $('#dupCargoVolume').val() || null,
                    equipment: dupEquipmentRows,
                };

                $.ajax({
                    url: '/quotation/' + bookingNo + '/duplicate',
                    type: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify(payload),
                    success: function(response) {
                        if (response.success) {
                            $('#duplicateModal').modal('hide');
                            window.location.href = response.redirect_url;
                        } else {
                            toastr.error(response.message || 'Duplicate failed.');
                            $btn.prop('disabled', false)
                                .html('<i class="fas fa-copy mr-1"></i> Duplicate');
                        }
                    },
                    error: function(xhr) {
                        const msg = xhr.responseJSON?.message || 'Unexpected error.';
                        toastr.error('Error: ' + msg);
                        $btn.prop('disabled', false)
                            .html('<i class="fas fa-copy mr-1"></i> Duplicate');
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
                            <td>${charge.chargeName || charge.chargeCode || ''}</td>
                            <td class="text-right">${parseFloat(charge.rate || 0).toFixed(2)}</td>
                            <td class="text-right col-extra">${parseFloat(charge.quantity || 0).toFixed(2)}</td>
                            <td class="col-extra">${charge.basedOnUOM || ''}</td>
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
                            <td>${charge.chargeName || charge.chargeCode || ''}</td>
                            <td class="text-right">${parseFloat(charge.rate || 0).toFixed(2)}</td>
                            <td class="text-right col-extra">${parseFloat(charge.quantity || 0).toFixed(2)}</td>
                            <td class="col-extra">${charge.basedOnUOM || ''}</td>
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
            $('.card').not('.card-success, .card-danger, .summary-card, #duplicateModal .card').each(function() {
                $(this).find('.card-body').hide();
                $(this).find('[data-card-widget="collapse"] i')
                    .removeClass('fa-chevron-up').addClass('fa-chevron-down');
                $(this).addClass('collapsed-card');
            });



        });
    </script>
@stop
