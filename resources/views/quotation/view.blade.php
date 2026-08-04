@extends('adminlte::page')

@section('title', 'View Booking Quotation')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1><i class="fas fa-file-invoice"></i> Quote No - {{ $quotation->QuoteNo ?? '' }}</h1>
            <p class="text-muted mb-0">View booking quotation details</p>
        </div>
        <div>
            <a href="{{ route('quotation.index') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>

            @php
                $quoteStatus = $quotation->QuoteStatus ?? ($quotation->status ?? null);
                $isInProgress =
                    in_array($quoteStatus, ['Proposal', 'Pending', 'In Progress', 'Failed Progress']) ||
                    is_null($quoteStatus);
                $canDuplicate = $isInProgress || $quoteStatus === 'Completed';
            @endphp

            @if ($canDuplicate)
                <button type="button" class="btn btn-info btn-sm" id="btnCopyBooking"
                    data-bookingno="{{ $quotation->bookingno }}">
                    <i class="fas fa-copy"></i> Duplicate
                </button>
            @endif

            @if ($isInProgress)
                <a href="{{ route('quotation.edit', $quotation->ID) }}" class="btn btn-warning btn-sm">
                    <i class="fas fa-edit"></i> Edit Booking
                </a>
            @endif
        </div>
    </div>
@stop

@section('content')

    {{-- ============================================================ --}}
    {{-- DUPLICATE RESULT CARD — fixed center overlay                 --}}
    {{-- ============================================================ --}}
    <div id="dupResultCard"
        style="display:none; position:fixed; top:50%; left:50%; transform:translate(-50%,-50%); z-index:9999; min-width:420px; max-width:560px;">
        <div class="alert alert-success shadow-lg" style="border-left: 5px solid #28a745; border-radius:8px;">
            <div class="d-flex align-items-center">
                <i class="fas fa-check-circle mr-3" style="font-size:2rem; color:#28a745;"></i>
                <div class="flex-grow-1">
                    <h5 class="mb-1 font-weight-bold">Quotation duplicated successfully!</h5>
                    <p class="mb-0">
                        New Inquiry No:&nbsp;
                        <a id="dupResultLink" href="#" class="font-weight-bold"
                            style="color:#4B49AC; font-size:1.1rem;">
                            —
                        </a>
                        <small class="text-muted ml-1">— click to open the new quotation</small>
                    </p>
                </div>
                <button type="button" class="btn btn-sm btn-outline-secondary ml-3" id="dupResultClose">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    </div>

    @php
        $quoteStatus = $quotation->QuoteStatus ?? ($quotation->status ?? null);
        $isInProgress =
            in_array($quoteStatus, ['Proposal', 'Pending', 'In Progress', 'Failed Progress']) || is_null($quoteStatus);
        $canDuplicate = $isInProgress || $quoteStatus === 'Completed';
    @endphp

    <div class="row">
        <!-- LEFT COLUMN -->
        <div class="col-md-4">

            {{-- SALES INTELLIGENCE CARD (read‑only) --}}
            <div class="row mb-3" id="salesIntelligenceSection">
                <div class="col-12">
                    <div class="card" id="salesIntelligenceCard" style="border-top: 3px solid #4B49AC;">
                        <div class="card-header d-flex justify-content-between align-items-center"
                            style="background:#f4f4fb; cursor:pointer;" id="salesIntelligenceHeader">
                            <h3 class="card-title mb-0" style="color:#4B49AC; font-weight:700;">
                                <i class="fas fa-chart-line mr-2"></i> Sales Intelligence
                            </h3>
                            <div class="d-flex align-items-center" style="gap:8px;">
                                <div class="btn-group btn-group-sm" id="siViewToggle" style="display:none !important;">
                                    <button type="button" class="btn btn-outline-secondary active" id="siTableViewBtn"
                                        title="Table view">
                                        <i class="fas fa-table"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary" id="siChartViewBtn"
                                        title="Chart view">
                                        <i class="fas fa-chart-bar"></i>
                                    </button>
                                </div>
                                <button type="button" class="btn btn-tool" id="siCollapseBtn">
                                    <i class="fas fa-chevron-up"></i>
                                </button>
                            </div>
                        </div>

                        <div class="card-body" id="salesIntelligenceBody">
                            <!-- Loading, error, content (copy exactly from edit page) -->
                            <div id="siLoading" class="text-center py-4">
                                <i class="fas fa-spinner fa-spin text-primary" style="font-size:1.5rem;"></i>
                                <p class="text-muted mt-2 mb-0">Loading client intelligence...</p>
                            </div>
                            <div id="siError" class="alert alert-warning d-none py-2 px-3">
                                <i class="fas fa-exclamation-triangle mr-2"></i>
                                <span id="siErrorMsg">Unable to load sales intelligence.</span>
                            </div>

                            {{-- Content (hidden until loaded) --}}
                            <div id="siContent" style="display:none;">

                                {{-- Top row: Probability + Expected Revenue + Profit --}}
                                <div class="row mb-3" id="siMetricsRow">

                                    {{-- Probability --}}
                                    <div class="col-6 col-md-3 mb-2">
                                        <div class="p-3 rounded text-center"
                                            style="background:#f0f0fb; border:1px solid #dddcf5;">
                                            <div
                                                style="font-size:0.7rem; font-weight:700; text-transform:uppercase;
                                    letter-spacing:0.08em; color:#4B49AC; margin-bottom:4px;">
                                                Win Probability
                                            </div>
                                            <div id="siProbabilityValue"
                                                style="font-size:2rem; font-weight:800; color:#4B49AC; line-height:1;">
                                                —
                                            </div>
                                            <div id="siProbabilityBasis"
                                                style="font-size:0.7rem; color:#888; margin-top:4px;">
                                                —
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Confidence --}}
                                    <div class="col-6 col-md-3 mb-2">
                                        <div class="p-3 rounded text-center"
                                            style="background:#f0f0fb; border:1px solid #dddcf5;">
                                            <div
                                                style="font-size:0.7rem; font-weight:700; text-transform:uppercase;
                                    letter-spacing:0.08em; color:#4B49AC; margin-bottom:4px;">
                                                Confidence
                                            </div>
                                            <div id="siConfidenceValue"
                                                style="font-size:2rem; font-weight:800; line-height:1;">
                                                —
                                            </div>
                                            <div id="siConfidenceBasis"
                                                style="font-size:0.7rem; color:#888; margin-top:4px;">
                                                —
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Expected Revenue --}}
                                    <div class="col-6 col-md-3 mb-2">
                                        <div class="p-3 rounded text-center"
                                            style="background:#f0f0fb; border:1px solid #dddcf5;">
                                            <div
                                                style="font-size:0.7rem; font-weight:700; text-transform:uppercase;
                                    letter-spacing:0.08em; color:#4B49AC; margin-bottom:4px;">
                                                Expected Revenue
                                            </div>
                                            <div id="siExpectedRevenue"
                                                style="font-size:1.4rem; font-weight:800; color:#28a745; line-height:1;">
                                                —
                                            </div>
                                            <div style="font-size:0.7rem; color:#888; margin-top:4px;">
                                                Quoted × Probability
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Expected Profit --}}
                                    <div class="col-6 col-md-3 mb-2">
                                        <div class="p-3 rounded text-center"
                                            style="background:#f0f0fb; border:1px solid #dddcf5;">
                                            <div
                                                style="font-size:0.7rem; font-weight:700; text-transform:uppercase;
                                    letter-spacing:0.08em; color:#4B49AC; margin-bottom:4px;">
                                                Expected Profit
                                            </div>
                                            <div id="siExpectedProfit"
                                                style="font-size:1.4rem; font-weight:800; line-height:1;">
                                                —
                                            </div>
                                            <div id="siProfitMargin"
                                                style="font-size:0.7rem; color:#888; margin-top:4px;">
                                                —
                                            </div>
                                        </div>
                                    </div>

                                </div>{{-- END metrics row --}}

                                {{-- TABLE VIEW --}}
                                <div id="siTableView">

                                    {{-- Service History --}}
                                    <div class="mb-3">
                                        <div
                                            style="font-size:0.75rem; font-weight:700; text-transform:uppercase;
                                letter-spacing:0.08em; color:#4B49AC; border-bottom:2px solid #4B49AC;
                                padding-bottom:4px; margin-bottom:8px;">
                                            Client Service History
                                        </div>
                                        <div class="table-responsive">
                                            <table class="table table-sm table-bordered mb-0" style="font-size:0.82rem;">
                                                <thead style="background:#f4f4fb;">
                                                    <tr>
                                                        <th>Service</th>
                                                        <th class="text-center">Offered</th>
                                                        <th class="text-center">Won</th>
                                                        <th class="text-center">Lost/Cancelled</th>
                                                        <th class="text-center">Win Rate</th>
                                                        <th>Last Offer</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="siServiceHistoryBody">
                                                    <tr>
                                                        <td colspan="6" class="text-center text-muted">
                                                            Loading...
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                    {{-- Recent Quotes --}}
                                    <div>
                                        <div
                                            style="font-size:0.75rem; font-weight:700; text-transform:uppercase;
                                letter-spacing:0.08em; color:#4B49AC; border-bottom:2px solid #4B49AC;
                                padding-bottom:4px; margin-bottom:8px;">
                                            Recent Quote History
                                            <span id="siQuoteCount" class="badge badge-secondary ml-1"
                                                style="font-size:0.65rem; font-weight:600;">0</span>
                                        </div>
                                        <div class="table-responsive">
                                            <table class="table table-sm table-bordered mb-0" style="font-size:0.82rem;">
                                                <thead style="background:#f4f4fb;">
                                                    <tr>
                                                        <th>Quote No</th>
                                                        <th>Date</th>
                                                        <th>Service</th>
                                                        <th>Status</th>
                                                        <th>Sales Person</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="siRecentQuotesBody">
                                                    <tr>
                                                        <td colspan="5" class="text-center text-muted">
                                                            Loading...
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                </div>{{-- END siTableView --}}

                                {{-- CHART VIEW --}}
                                <div id="siChartView" style="display:none;">
                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <div
                                                style="font-size:0.75rem; font-weight:700; text-transform:uppercase;
                                    letter-spacing:0.08em; color:#4B49AC; margin-bottom:8px;">
                                                Win / Loss Ratio
                                            </div>
                                            <canvas id="siWinLossChart" height="200"></canvas>
                                        </div>
                                        <div class="col-md-8 mb-3">
                                            <div
                                                style="font-size:0.75rem; font-weight:700; text-transform:uppercase;
                                    letter-spacing:0.08em; color:#4B49AC; margin-bottom:8px;">
                                                Service Performance
                                            </div>
                                            <canvas id="siServiceChart" height="200"></canvas>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-12">
                                            <div
                                                style="font-size:0.75rem; font-weight:700; text-transform:uppercase;
                                    letter-spacing:0.08em; color:#4B49AC; margin-bottom:8px;">
                                                Quote Activity (Last 12 Months)
                                            </div>
                                            <canvas id="siTrendChart" height="100"></canvas>
                                        </div>
                                    </div>
                                </div>{{-- END siChartView --}}

                            </div>{{-- END siContent --}}
                        </div>
                    </div>
                </div>
            </div>

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
                            <input type="text" class="form-control form-control-sm" data-field="source"
                                value="{{ $quotation->source ?? '' }}" readonly>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-5 col-form-label">Status</label>
                        <div class="col-sm-7">
                            <input type="text" class="form-control form-control-sm" data-field="status"
                                value="{{ $quotation->status ?? '' }}" readonly>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-5 col-form-label">Office</label>
                        <div class="col-sm-7">
                            <input type="text" class="form-control form-control-sm" data-field="teamname"
                                value="{{ $quotation->teamname ?? '' }}" readonly>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-5 col-form-label">Sales Person</label>
                        <div class="col-sm-7">
                            <input type="text" class="form-control form-control-sm" data-field="username"
                                data-code="{{ $quotation->SalesPersonCode ?? '' }}"
                                value="{{ $quotation->username ?? '' }}" readonly>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-5 col-form-label">Sales Person Email</label>
                        <div class="col-sm-7">
                            <input type="text" class="form-control form-control-sm" data-field="SalesPersonEmail"
                                value="{{ $quotation->SalesPersonEmail ?? '' }}" readonly>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-5 col-form-label">Currency</label>
                        <div class="col-sm-7">
                            <input type="text" class="form-control form-control-sm"
                                value="{{ $quotation->QuoteCurr ?? '' }}" readonly>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Shipment Details -->
            <div class="card card-primary" id="viewShipmentCard">
                <div class="card-header">
                    <h3 class="card-title">Shipment Details</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-chevron-up"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- LEFT COLUMN -->
                        <div class="col-md-6">
                            <div class="form-group row">
                                <label class="col-sm-5 col-form-label">Service</label>
                                <div class="col-sm-7">
                                    <input type="text" class="form-control form-control-sm"
                                        value="{{ $quotation->SVName ?? '' }}" readonly>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-5 col-form-label">InCO Terms</label>
                                <div class="col-sm-7">
                                    <input type="text" class="form-control form-control-sm" id="viewIncoTermDisplay"
                                        value="{{ $quotation->incoterm ?? '' }}" readonly>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-5 col-form-label">Transport Mode</label>
                                <div class="col-sm-7">
                                    <input type="text" class="form-control form-control-sm"
                                        id="viewTransportModeDisplay" value="{{ $quotation->TransportMode ?? '' }}"
                                        readonly>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-5 col-form-label">Origin Country</label>
                                <div class="col-sm-7">
                                    <input type="text" class="form-control form-control-sm"
                                        id="viewOriginCountryDisplay" value="{{ $quotation->origincountry ?? '' }}"
                                        readonly>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-5 col-form-label">Port of Loading</label>
                                <div class="col-sm-7">
                                    <input type="text" class="form-control form-control-sm"
                                        value="{{ $quotation->PortOfLoadingCode ?? '' }}" readonly>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-5 col-form-label">Place of Receipt</label>
                                <div class="col-sm-7">
                                    <input type="text" class="form-control form-control-sm"
                                        value="{{ $quotation->PlaceOfReceiptCode ?? '' }}" readonly>
                                </div>
                            </div>
                        </div>

                        <!-- RIGHT COLUMN -->
                        <div class="col-md-6">
                            <div class="form-group row">
                                <label class="col-sm-5 col-form-label">Process</label>
                                <div class="col-sm-7">
                                    <input type="text" class="form-control form-control-sm"
                                        value="{{ $quotation->process ?? '' }}" readonly>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-5 col-form-label">Shipment Mode</label>
                                <div class="col-sm-7">
                                    <input type="text" class="form-control form-control-sm"
                                        id="viewShipmentModeDisplay" value="{{ $quotation->shipmentmode ?? '' }}"
                                        readonly>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-5 col-form-label">Service Class</label>
                                <div class="col-sm-7">
                                    <input type="text" class="form-control form-control-sm"
                                        id="viewServiceClassDisplay" value="{{ $quotation->ServiceClass ?? '' }}"
                                        readonly>
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
                                <label class="col-sm-5 col-form-label">Port of Discharge</label>
                                <div class="col-sm-7">
                                    <input type="text" class="form-control form-control-sm"
                                        value="{{ $quotation->PortOfDischargeCode ?? '' }}" readonly>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-5 col-form-label">Place of Delivery</label>
                                <div class="col-sm-7">
                                    <input type="text" class="form-control form-control-sm"
                                        value="{{ $quotation->PlaceOfDeliveryCode ?? '' }}" readonly>
                                </div>
                            </div>
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
                            <input type="text" class="form-control form-control-sm" data-field="commodity"
                                value="{{ $quotation->commodity ?? '' }}" readonly>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-5 col-form-label">Scope Of Volume</label>
                        <div class="col-sm-7">
                            <input type="text" class="form-control form-control-sm" id="viewScopeVolumeDisplay"
                                value="{{ $quotation->scopevolume ?? '' }}" readonly>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-5 col-form-label">Cargo Volume</label>
                        <div class="col-sm-7">
                            <input type="text" class="form-control form-control-sm" data-field="cargovolume"
                                value="{{ $quotation->cargovolume ?? '' }}" readonly>
                        </div>
                    </div>
                    <div class="form-group row mb-0" id="viewContainerSection"
                        style="{{ ($quotation->scopevolume ?? '') === 'Containerized' ? '' : 'display:none;' }}">
                        <label class="col-sm-5 col-form-label">Containers</label>
                        <div class="col-sm-7">
                            <table class="table table-bordered table-sm mb-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Equipment Type</th>
                                        <th class="text-center">Qty</th>
                                    </tr>
                                </thead>
                                <tbody id="viewContainerTableBody">
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
                                preg_match('/_V(\d+)_/i', $doc->Name, $matches);
                                $version = $matches[1] ?? $index + 1;
                                $displayName = $doc->Name ?? "Quotation_{$quotation->bookingno}_V{$version}.pdf";

                                // Use the same route the edit page uses — avoids the storage::exists() false negative
                                $downloadUrl = route('quotation.download-pdf', [
                                    'id' => $quotation->ID,
                                    'version' => $version,
                                ]);
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
                        <p class="text-muted text-center mb-0">No PDF versions generated yet</p>
                    @endif
                    @if ($quotation->QuotationTCTemplate)
                        <div class="mt-2 pt-2" style="border-top:1px solid #dee2e6;">
                            <div class="form-group row mb-0">
                                <label class="col-sm-4 col-form-label">Terms & Conditions</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control form-control-sm"
                                        value="{{ $quotation->QuotationTCTemplate }}" readonly>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Quote Communication -->
            <div class="card card-primary">
                <div class="card-header d-flex justify-content-between align-items-center py-2">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-envelope mr-2"></i>Quote Communication
                    </h3>
                    <div class="card-tools ml-auto">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-chevron-up"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm table-hover mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>Sent On</th>
                                    <th>To Email ID</th>
                                    <th>Quote Version</th>
                                </tr>
                            </thead>
                            <tbody id="viewEmailHistoryTable">
                                <tr>
                                    <td colspan="3" class="text-center text-muted">
                                        <i class="fas fa-spinner fa-spin"></i> Loading...
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Request Office Quotation -->
            <div class="card card-primary">
                <div class="card-header d-flex justify-content-between align-items-center py-2">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-paper-plane mr-2"></i>Request Office Quotation
                    </h3>
                    <div class="card-tools ml-auto">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-chevron-up"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm table-hover mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>Office Name</th>
                                    <th>Quote No</th>
                                    <th>Request</th>
                                </tr>
                            </thead>
                            <tbody id="viewRequestOfficeHistoryTable">
                                <tr>
                                    <td colspan="3" class="text-center text-muted">
                                        <i class="fas fa-spinner fa-spin"></i> Loading...
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Approval -->
            <div class="card card-primary" id="approvalCard">
                <div class="card-header d-flex justify-content-between align-items-center py-2">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-check-double mr-2"></i>Approval
                    </h3>
                    <div class="card-tools ml-auto">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-chevron-up"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div id="viewApprovalEmpty" class="text-center text-muted py-2">
                        <i class="fas fa-info-circle mr-1"></i> Not yet sent for approval.
                    </div>
                    <div id="viewApprovalLoading" class="text-center py-2" style="display:none;">
                        <i class="fas fa-spinner fa-spin text-primary"></i>
                    </div>
                    <div id="viewApprovalUnavailable" class="alert alert-secondary py-2 px-3 mb-0"
                        style="display:none; font-size:0.85rem;">
                        <i class="fas fa-tools mr-1"></i> <span id="viewApprovalUnavailableMsg"></span>
                    </div>
                    <div id="viewApprovalContent" style="display:none;">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span id="viewApprovalStatusBadge" class="badge badge-secondary px-2 py-1"
                                style="font-size:0.8rem;">—</span>
                            <span class="text-muted" style="font-size:0.8rem;" id="viewApprovalProgressLabel">0 of 0
                                Approved</span>
                        </div>
                        <div class="progress mb-3" style="height: 8px;">
                            <div id="viewApprovalProgressBar" class="progress-bar bg-success" role="progressbar"
                                style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        <table class="table table-sm table-borderless mb-0" id="viewApprovalApproverTable">
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Audit Information -->
            <div class="card card-primary" id="auditInformationCard">
                <div class="card-header d-flex justify-content-between align-items-center py-2">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-history mr-2"></i>Audit Information
                    </h3>
                    <div class="card-tools ml-auto">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-chevron-up"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="form-group row">
                        <label class="col-sm-5 col-form-label">Created On</label>
                        <div class="col-sm-7">
                            <input type="text" class="form-control form-control-sm"
                                value="{{ $quotation->CreatedOn ? date('d/m/Y H:i', strtotime($quotation->CreatedOn)) : '-' }}"
                                readonly>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-5 col-form-label">Created By</label>
                        <div class="col-sm-7">
                            <input type="text" class="form-control form-control-sm"
                                value="{{ $quotation->CreatedBy ?? '-' }}" readonly>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-5 col-form-label">Last Modified On</label>
                        <div class="col-sm-7">
                            <input type="text" class="form-control form-control-sm"
                                value="{{ $quotation->updatedon ? date('d/m/Y H:i', strtotime($quotation->updatedon)) : '-' }}"
                                readonly>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-5 col-form-label">Last Modified By</label>
                        <div class="col-sm-7">
                            <input type="text" class="form-control form-control-sm"
                                value="{{ $quotation->LastModifiedBy ?? '-' }}" readonly>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-5 col-form-label">Marked WON on</label>
                        <div class="col-sm-7">
                            <input type="text" class="form-control form-control-sm"
                                value="{{ $quotation->WonDate ? date('d/m/Y H:i', strtotime($quotation->WonDate)) : '-' }}"
                                readonly>
                        </div>
                    </div>
                    <div class="form-group row mb-0">
                        <label class="col-sm-5 col-form-label">Marked WON By</label>
                        <div class="col-sm-7">
                            <input type="text" class="form-control form-control-sm"
                                value="{{ $quotation->WonBy ?? '-' }}" readonly>
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
                    {{-- Customer Type & Agent Type --}}
                    <div class="row mb-3 pb-2" style="border-bottom: 1px solid #dee2e6;">
                        <div class="col-md-6">
                            <div class="form-group row mb-0">
                                <label class="col-sm-4 col-form-label">Customer Type</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control form-control-sm"
                                        value="{{ $quotation->CustomerType ?? '' }}" readonly>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group row mb-0">
                                <label class="col-sm-4 col-form-label">Agent Type</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control form-control-sm"
                                        value="{{ $quotation->AgentType ?? '' }}" readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group row">
                                <label class="col-sm-4 col-form-label">Company</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control form-control-sm" data-field="companyname"
                                        value="{{ $quotation->pri_partnermaster_name ?? ($quotation->companyname ?? '') }}"
                                        readonly>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-4 col-form-label">Address 1</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control form-control-sm"
                                        data-field="pri_partner_steet" value="{{ $quotation->pri_partner_steet ?? '' }}"
                                        readonly>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-4 col-form-label">City</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control form-control-sm"
                                        data-field="pri_partner_city" value="{{ $quotation->pri_partner_city ?? '' }}"
                                        readonly>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-4 col-form-label">State</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control form-control-sm"
                                        data-field="pri_partner_statename"
                                        value="{{ $quotation->pri_partner_statename ?? '' }}" readonly>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-4 col-form-label">Contact Person</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control form-control-sm"
                                        data-field="pri_partnermaster_name" value="{{ $quotation->contact_name ?? '' }}"
                                        readonly>
                                </div>
                            </div>
                            <div class="form-group row mb-0">
                                <label class="col-sm-4 col-form-label">Phone No</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control form-control-sm"
                                        data-field="pri_partner_phone" value="{{ $quotation->pri_partner_phone ?? '' }}"
                                        readonly>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group row">
                                <label class="col-sm-4 col-form-label">Client ID</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control form-control-sm" data-field="clientid"
                                        value="{{ $quotation->clientid ?? '' }}" readonly>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-4 col-form-label">Address 2</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control form-control-sm"
                                        data-field="pri_partner_steet2"
                                        value="{{ $quotation->pri_partner_steet2 ?? '' }}" readonly>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-4 col-form-label">Zip</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control form-control-sm"
                                        data-field="pri_partner_zip" value="{{ $quotation->pri_partner_zip ?? '' }}"
                                        readonly>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-4 col-form-label">Country</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control form-control-sm"
                                        data-field="pri_partner_countryname"
                                        value="{{ $quotation->pri_partner_countryname ?? '' }}" readonly>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-4 col-form-label">Email ID</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control form-control-sm"
                                        data-field="pri_partner_email" value="{{ $quotation->pri_partner_email ?? '' }}"
                                        readonly>
                                </div>
                            </div>
                            <div class="form-group row mb-0">
                                <label class="col-sm-4 col-form-label">Industry</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control form-control-sm" data-field="industryname"
                                        value="{{ $quotation->industryname ?? '' }}" readonly>
                                </div>
                            </div>

                            {{-- Quote Includes --}}
                            @php
                                $insurance = 0;
                                $lastMile = 0;
                                $customDuty = 0;
                                if (\Illuminate\Support\Facades\Schema::hasTable('Quote_Quotation_Includes')) {
                                    $quoteIncludes = DB::table('Quote_Quotation_Includes')
                                        ->where('QuotationInfoID', $quotation->ID)
                                        ->first();
                                    if ($quoteIncludes) {
                                        $insurance = $quoteIncludes->Insurance ?? 0;
                                        $lastMile = $quoteIncludes->LastMile ?? 0;
                                        $customDuty = $quoteIncludes->CustomDuty ?? 0;
                                    }
                                }
                            @endphp
                            <div class="row mt-3 pt-2" style="border-top: 1px solid #dee2e6;">
                                <div class="col-12">
                                    <p class="text-uppercase font-weight-bold mb-2"
                                        style="font-size: 0.75rem; letter-spacing: 0.08em; color: #003580;">
                                        Quote Includes
                                    </p>
                                    <div class="d-flex flex-wrap" style="gap: 8px;">
                                        <span class="d-inline-block border rounded px-3 py-2 mb-0"
                                            style="background:#f8f9fa; font-size:0.875rem;">
                                            <i
                                                class="fas {{ $insurance ? 'fa-check-circle text-success' : 'fa-times-circle text-muted' }} mr-1"></i>
                                            Insurance
                                        </span>
                                        <span class="d-inline-block border rounded px-3 py-2 mb-0"
                                            style="background:#f8f9fa; font-size:0.875rem;">
                                            <i
                                                class="fas {{ $lastMile ? 'fa-check-circle text-success' : 'fa-times-circle text-muted' }} mr-1"></i>
                                            Last Mile
                                        </span>
                                        <span class="d-inline-block border rounded px-3 py-2 mb-0"
                                            style="background:#f8f9fa; font-size:0.875rem;">
                                            <i
                                                class="fas {{ $customDuty ? 'fa-check-circle text-success' : 'fa-times-circle text-muted' }} mr-1"></i>
                                            Custom Duty
                                        </span>
                                    </div>
                                </div>
                            </div>

                            {{-- Product Details (if Custom Duty is checked) --}}
                            @if ($customDuty == 1)
                                @php
                                    $products = [];
                                    if (
                                        \Illuminate\Support\Facades\Schema::hasTable('Quote_Quotation_ProductDetails')
                                    ) {
                                        $products = DB::table('Quote_Quotation_ProductDetails')
                                            ->where('QuotationInfoID', $quotation->ID)
                                            ->where('IsDeleted', 0)
                                            ->get();
                                    }
                                @endphp
                                @if ($products->isNotEmpty())
                                    <div class="row mt-3">
                                        <div class="col-12">
                                            <p class="text-uppercase font-weight-bold mb-2"
                                                style="font-size: 0.75rem; letter-spacing: 0.08em; color: #003580;">
                                                Product Details
                                            </p>
                                            <div class="table-responsive">
                                                <table class="table table-sm table-bordered mb-1">
                                                    <thead class="thead-light">
                                                        <tr>
                                                            <th style="font-size:0.75rem;">Description</th>
                                                            <th style="width:100px; font-size:0.75rem;">Weight (kg)</th>
                                                            <th style="width:100px; font-size:0.75rem;">L×B×H (m³)</th>
                                                            <th style="width:80px; font-size:0.75rem;">QTY</th>
                                                            <th style="width:120px; font-size:0.75rem;">Price (USD)</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($products as $product)
                                                            <tr>
                                                                <td>{{ $product->Description ?? '' }}</td>
                                                                <td>{{ $product->Weight ?? '' }}</td>
                                                                <td>{{ $product->Dimensions ?? '' }}</td>
                                                                <td>{{ $product->QTY ?? '' }}</td>
                                                                <td>{{ number_format($product->Price ?? 0, 2) }}</td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Revenue -->
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

            <!-- Cost -->
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

        .col-extra {
            display: none;
        }

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

        .card-action-footer {
            background: #f8f9fa;
            border-top: 1px solid rgba(0, 0, 0, .125);
            padding: 0.5rem 1rem;
            display: flex;
            justify-content: flex-end;
            gap: 0.25rem;
        }

        .card-in-edit input[data-field]:not([readonly]) {
            background-color: #fff9e6;
            border-color: #ffc107 !important;
        }
    </style>
@stop

@section('js')
    <script>
        const OFFICE_BASE_CURRENCY = @json(\App\Http\Controllers\QuotationController::OFFICE_BASE_CURRENCY);
        const officeCode = '{{ trim($quotation->OfficeCode ?? '') }}';

        function getBaseCurrency() {
            return OFFICE_BASE_CURRENCY[officeCode] ?? 'KES';
        }

        $(document).ready(function() {

            const bookingNo = '{{ $quotation->bookingno ?? '' }}';
            const QUOTE_NO_VIEW = '{{ $quotation->QuoteNo ?? '' }}';
            const SCOPE_OF_VOLUME = '{{ $quotation->scopevolume ?? '' }}';

            // SALES INTELLIGENCE
            (function() {

                // Values already available on the page from PHP/JS
                const SI_COMPANY = '{{ addslashes(trim($quotation->pri_partnermaster_name ?? '')) }}';
                const SI_SERVICE = '{{ addslashes(trim($quotation->SVName ?? '')) }}';
                const SI_OFFICE = '{{ addslashes(trim($officeShortCode ?? '')) }}';
                const SI_CURRENCY = '{{ addslashes(trim($quotation->QuoteCurr ?? 'USD')) }}';

                // Chart instances — kept so we can destroy before re-render
                let siWinLossChartInst = null;
                let siServiceChartInst = null;
                let siTrendChartInst = null;
                let siDataLoaded = false;

                // ── Collapse toggle ───────────────────────────────────────
                $('#salesIntelligenceHeader').on('click', function(e) {
                    if ($(e.target).closest('button, .btn-group').length) return;
                    const $body = $('#salesIntelligenceBody');
                    const $icon = $('#siCollapseBtn i');
                    $body.slideToggle(200);
                    $icon.toggleClass('fa-chevron-up fa-chevron-down');
                });

                $('#siCollapseBtn').on('click', function(e) {
                    e.stopPropagation();
                    const $body = $('#salesIntelligenceBody');
                    const $icon = $(this).find('i');
                    $body.slideToggle(200);
                    $icon.toggleClass('fa-chevron-up fa-chevron-down');
                });

                // ── View toggle ───────────────────────────────────────────
                $('#siTableViewBtn').on('click', function() {
                    $(this).addClass('active');
                    $('#siChartViewBtn').removeClass('active');
                    $('#siTableView').show();
                    $('#siChartView').hide();
                });

                $('#siChartViewBtn').on('click', function() {
                    $(this).addClass('active');
                    $('#siTableViewBtn').removeClass('active');
                    $('#siTableView').hide();
                    $('#siChartView').show();
                    // Render charts only when first switched to chart view
                    if (siDataLoaded) renderCharts(window._siComputedData);
                });

                // ── Helpers ───────────────────────────────────────────────
                function fmtCurrency(val, currency) {
                    return new Intl.NumberFormat('en-US', {
                        style: 'currency',
                        currency: currency || 'USD',
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2,
                    }).format(val);
                }

                function statusGroup(status) {
                    if (['Won', 'Completed'].includes(status)) return 'won';
                    if (['Lost', 'Cancelled'].includes(status)) return 'lost';
                    if (['In Progress'].includes(status)) return 'open';
                    return 'other';
                }

                // ── Probability engine ────────────────────────────────────
                // Returns { probability: float|null, confidence: string, basis: string }
                function computeProbability(allQuotes, targetService) {
                    const closed = allQuotes.filter(q => ['Won', 'Completed', 'Lost', 'Cancelled'].includes(q
                        .Status));

                    // Insufficient data rule — minimum 3 closed quotes required
                    if (closed.length < 3) {
                        return {
                            probability: null,
                            confidence: 'Insufficient data',
                            basis: closed.length + ' closed quote(s) — need at least 3',
                            color: '#aaa',
                        };
                    }

                    // Base win rate across all services
                    const won = closed.filter(q => statusGroup(q.Status) === 'won').length;
                    const baseRate = won / closed.length;

                    // Service-specific win rate (weighted 60%)
                    const svcQuotes = closed.filter(q => q.Service === targetService);
                    let serviceRate = baseRate; // fallback to base if no service history
                    if (svcQuotes.length >= 2) {
                        const svcWon = svcQuotes.filter(q => statusGroup(q.Status) === 'won').length;
                        serviceRate = svcWon / svcQuotes.length;
                    }
                    let probability = (baseRate * 0.4) + (serviceRate * 0.6);

                    // Recency adjustment — days since most recent quote
                    const dates = allQuotes.map(q => new Date(q.Date)).filter(d => !isNaN(d));
                    const lastDate = dates.length ? new Date(Math.max(...dates)) : null;
                    if (lastDate) {
                        const daysSince = Math.floor((new Date() - lastDate) / 86400000);
                        if (daysSince < 30) probability = Math.min(1, probability + 0.05);
                        else if (daysSince > 180) probability = Math.max(0, probability - 0.10);
                    }

                    // Confidence tier
                    let confidence, confColor;
                    if (closed.length >= 10) {
                        confidence = 'High';
                        confColor = '#28a745';
                    } else if (closed.length >= 5) {
                        confidence = 'Medium';
                        confColor = '#fd7e14';
                    } else {
                        confidence = 'Low';
                        confColor = '#dc3545';
                    }

                    return {
                        probability: Math.round(probability * 1000) / 10, // 1 decimal place
                        confidence,
                        confColor,
                        basis: `Based on ${closed.length} closed quotes (${won} won)`,
                    };
                }

                // ── Service history aggregation ───────────────────────────
                function buildServiceHistory(allQuotes) {
                    const map = {};
                    allQuotes.forEach(function(q) {
                        const svc = q.Service || 'Unknown';
                        if (!map[svc]) map[svc] = {
                            offered: 0,
                            won: 0,
                            lost: 0,
                            lastDate: null
                        };
                        map[svc].offered++;
                        const grp = statusGroup(q.Status);
                        if (grp === 'won') map[svc].won++;
                        if (grp === 'lost') map[svc].lost++;
                        const d = new Date(q.Date);
                        if (!isNaN(d) && (!map[svc].lastDate || d > map[svc].lastDate)) {
                            map[svc].lastDate = d;
                        }
                    });
                    return map;
                }

                // ── Render metrics ────────────────────────────────────────
                function renderMetrics(prob, quotedRevenue, quotedCost, currency) {
                    if (prob.probability === null) {
                        $('#siProbabilityValue').text('N/A').css('color', '#aaa');
                        $('#siProbabilityBasis').text(prob.basis);
                        $('#siConfidenceValue').text('N/A').css('color', '#aaa');
                        $('#siConfidenceBasis').text('Insufficient history');
                        $('#siExpectedRevenue').text('N/A');
                        $('#siExpectedProfit').text('N/A');
                        $('#siProfitMargin').text('—');
                        return;
                    }

                    const pct = prob.probability;
                    const expectedRev = (quotedRevenue * pct) / 100;
                    const profit = quotedRevenue - quotedCost;
                    const expectedProfit = (profit * pct) / 100;
                    const margin = quotedRevenue > 0 ?
                        ((profit / quotedRevenue) * 100).toFixed(2) :
                        null;

                    $('#siProbabilityValue').text(pct.toFixed(1) + '%').css('color', '#4B49AC');
                    $('#siProbabilityBasis').text(prob.basis);

                    $('#siConfidenceValue').text(prob.confidence).css('color', prob.confColor);
                    $('#siConfidenceBasis').text(closed => prob.basis);
                    $('#siConfidenceBasis').text('');

                    $('#siExpectedRevenue')
                        .text(fmtCurrency(expectedRev, currency))
                        .css('color', expectedRev >= 0 ? '#28a745' : '#dc3545');

                    $('#siExpectedProfit')
                        .text(fmtCurrency(expectedProfit, currency))
                        .css('color', expectedProfit >= 0 ? '#28a745' : '#dc3545');

                    $('#siProfitMargin').text(
                        margin !== null ?
                        'Margin: ' + margin + '%' :
                        quotedCost === 0 ? 'No cost data' : '—'
                    );
                }

                // ── Render service history table ──────────────────────────
                function renderServiceHistory(svcMap) {
                    const $tbody = $('#siServiceHistoryBody').empty();
                    const rows = Object.entries(svcMap).sort((a, b) => b[1].offered - a[1].offered);

                    if (!rows.length) {
                        $tbody.html('<tr><td colspan="6" class="text-center text-muted">No history</td></tr>');
                        return;
                    }

                    rows.forEach(function([svc, data]) {
                        const winRate = data.offered > 0 ?
                            ((data.won / data.offered) * 100).toFixed(1) + '%' :
                            '—';
                        const winColor = data.offered > 0 ?
                            (data.won / data.offered >= 0.5 ? '#28a745' : '#dc3545') :
                            '#aaa';
                        const lastDate = data.lastDate ?
                            data.lastDate.toLocaleDateString('en-GB') :
                            '—';
                        $tbody.append(`
                <tr>
                    <td>${svc}</td>
                    <td class="text-center">${data.offered}</td>
                    <td class="text-center">${data.won}</td>
                    <td class="text-center">${data.lost}</td>
                    <td class="text-center font-weight-bold" style="color:${winColor};">
                        ${winRate}
                    </td>
                    <td>${lastDate}</td>
                </tr>
            `);
                    });
                }

                // ── Render recent quotes table ────────────────────────────
                function renderRecentQuotes(allQuotes) {
                    const STATUS_COLORS = {
                        'Won': 'success',
                        'Completed': 'success',
                        'Lost': 'danger',
                        'Cancelled': 'danger',
                        'In Progress': 'primary',
                        'Failed Int.': 'warning',
                        'Failed Progress': 'secondary',
                    };

                    const $tbody = $('#siRecentQuotesBody').empty();
                    const sorted = [...allQuotes].sort((a, b) => new Date(b.Date) - new Date(a.Date));
                    const recent = sorted.slice(0, 10);

                    $('#siQuoteCount').text(allQuotes.length + ' total');

                    if (!recent.length) {
                        $tbody.html(
                            '<tr><td colspan="5" class="text-center text-muted">No quotes found</td></tr>');
                        return;
                    }

                    recent.forEach(function(q) {
                        const badgeType = STATUS_COLORS[q.Status] || 'secondary';
                        $tbody.append(`
                <tr>
                    <td><strong>${q.QuoteNo}</strong></td>
                    <td>${q.Date || '—'}</td>
                    <td>${q.Service || '—'}</td>
                    <td>
                        <span class="badge badge-${badgeType}">${q.Status}</span>
                    </td>
                    <td>${q.SalesPerson || '—'}</td>
                </tr>
            `);
                    });
                }

                // ── Render charts ─────────────────────────────────────────
                function renderCharts(data) {
                    if (!data) return;

                    const {
                        allQuotes,
                        svcMap
                    } = data;

                    // -- Win/Loss doughnut --
                    const won = allQuotes.filter(q => statusGroup(q.Status) === 'won').length;
                    const lost = allQuotes.filter(q => statusGroup(q.Status) === 'lost').length;
                    const open = allQuotes.filter(q => statusGroup(q.Status) === 'open').length;
                    const other = allQuotes.length - won - lost - open;

                    if (siWinLossChartInst) siWinLossChartInst.destroy();
                    siWinLossChartInst = new Chart(
                        document.getElementById('siWinLossChart').getContext('2d'), {
                            type: 'doughnut',
                            data: {
                                labels: ['Won / Completed', 'Lost / Cancelled', 'In Progress', 'Other'],
                                datasets: [{
                                    data: [won, lost, open, other],
                                    backgroundColor: ['#28a745', '#dc3545', '#4B49AC', '#adb5bd'],
                                    borderWidth: 1,
                                }],
                            },
                            options: {
                                responsive: true,
                                plugins: {
                                    legend: {
                                        position: 'bottom',
                                        labels: {
                                            font: {
                                                size: 11
                                            }
                                        }
                                    }
                                },
                            },
                        });

                    // -- Service bar --
                    const svcLabels = Object.keys(svcMap);
                    const svcWon = svcLabels.map(s => svcMap[s].won);
                    const svcLost = svcLabels.map(s => svcMap[s].lost);
                    const svcOpen = svcLabels.map(s =>
                        svcMap[s].offered - svcMap[s].won - svcMap[s].lost
                    );

                    if (siServiceChartInst) siServiceChartInst.destroy();
                    siServiceChartInst = new Chart(
                        document.getElementById('siServiceChart').getContext('2d'), {
                            type: 'bar',
                            data: {
                                labels: svcLabels,
                                datasets: [{
                                        label: 'Won',
                                        data: svcWon,
                                        backgroundColor: '#28a745'
                                    },
                                    {
                                        label: 'Lost',
                                        data: svcLost,
                                        backgroundColor: '#dc3545'
                                    },
                                    {
                                        label: 'In Progress',
                                        data: svcOpen,
                                        backgroundColor: '#4B49AC'
                                    },
                                ],
                            },
                            options: {
                                responsive: true,
                                scales: {
                                    x: {
                                        stacked: true
                                    },
                                    y: {
                                        stacked: true,
                                        beginAtZero: true
                                    }
                                },
                                plugins: {
                                    legend: {
                                        position: 'bottom',
                                        labels: {
                                            font: {
                                                size: 11
                                            }
                                        }
                                    }
                                },
                            },
                        });

                    // -- Monthly trend line (last 12 months) --
                    const now = new Date();
                    const months = [];
                    const monthly = {};
                    for (let i = 11; i >= 0; i--) {
                        const d = new Date(now.getFullYear(), now.getMonth() - i, 1);
                        const key = d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0');
                        months.push(key);
                        monthly[key] = {
                            total: 0,
                            won: 0
                        };
                    }
                    allQuotes.forEach(function(q) {
                        const d = new Date(q.Date);
                        if (isNaN(d)) return;
                        const key = d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0');
                        if (monthly[key]) {
                            monthly[key].total++;
                            if (statusGroup(q.Status) === 'won') monthly[key].won++;
                        }
                    });

                    if (siTrendChartInst) siTrendChartInst.destroy();
                    siTrendChartInst = new Chart(
                        document.getElementById('siTrendChart').getContext('2d'), {
                            type: 'line',
                            data: {
                                labels: months,
                                datasets: [{
                                        label: 'Total Quotes',
                                        data: months.map(m => monthly[m].total),
                                        borderColor: '#4B49AC',
                                        backgroundColor: 'rgba(75,73,172,0.08)',
                                        tension: 0.3,
                                        fill: true,
                                    },
                                    {
                                        label: 'Won',
                                        data: months.map(m => monthly[m].won),
                                        borderColor: '#28a745',
                                        backgroundColor: 'rgba(40,167,69,0.08)',
                                        tension: 0.3,
                                        fill: true,
                                    },
                                ],
                            },
                            options: {
                                responsive: true,
                                scales: {
                                    y: {
                                        beginAtZero: true
                                    }
                                },
                                plugins: {
                                    legend: {
                                        position: 'bottom',
                                        labels: {
                                            font: {
                                                size: 11
                                            }
                                        }
                                    }
                                },
                            },
                        });
                }

                // ── Main load function ────────────────────────────────────
                function loadSalesIntelligence() {
                    if (!SI_COMPANY || !SI_OFFICE) {
                        $('#siLoading').hide();
                        $('#siError').removeClass('d-none');
                        $('#siErrorMsg').text('Client or office information not available.');
                        return;
                    }

                    $.ajax({
                        url: '{{ route('quotation.sales-intelligence') }}',
                        type: 'GET',
                        data: {
                            office_code: SI_OFFICE,
                            customer: SI_COMPANY
                        },
                        success: function(response) {
                            $('#siLoading').hide();

                            if (!response.success || !response.data.length) {
                                $('#siError').removeClass('d-none');
                                $('#siErrorMsg').text('No quote history found for this client.');
                                return;
                            }

                            const allQuotes = response.data;

                            // Read quoted revenue + cost from the page (already calculated)
                            const parsePageFloat = function(selector) {
                                const raw = $(selector).val() || '0';
                                return parseFloat(raw.replace(/[^0-9.\-]/g, '')) || 0;
                            };
                            const quotedRevenue = parsePageFloat('#viewQuotedRevenue');
                            const quotedCost = parsePageFloat('#costTotalHeaderAmount');

                            // Compute
                            const prob = computeProbability(allQuotes, SI_SERVICE);
                            const svcMap = buildServiceHistory(allQuotes);

                            // Store for chart re-render on tab switch
                            window._siComputedData = {
                                allQuotes,
                                svcMap
                            };
                            siDataLoaded = true;

                            // Render
                            renderMetrics(prob, quotedRevenue, quotedCost, SI_CURRENCY);
                            renderServiceHistory(svcMap);
                            renderRecentQuotes(allQuotes);

                            // Show view toggle + content
                            $('#siViewToggle').css('display', 'flex !important')
                                .attr('style', 'display:flex !important; gap:4px;');
                            $('#siContent').show();
                        },
                        error: function(xhr) {
                            $('#siLoading').hide();
                            $('#siError').removeClass('d-none');
                            $('#siErrorMsg').text(
                                xhr.responseJSON?.message ||
                                'Failed to load sales intelligence.'
                            );
                        },
                    });
                }

                // Delay slightly so page charges load first (they populate #quotedRevenue)
                setTimeout(loadSalesIntelligence, 1200);

            })();
            // END SALES INTELLIGENCE

            // ============================================================
            // VIEW PAGE — Load containers (read-only)
            // ============================================================
            if (QUOTE_NO_VIEW && SCOPE_OF_VOLUME === 'Containerized') {
                $.ajax({
                    url: '{{ route('quotation.containers') }}',
                    type: 'GET',
                    data: {
                        quote_no: QUOTE_NO_VIEW
                    },
                    success: function(response) {
                        const tbody = $('#viewContainerTableBody');
                        tbody.empty();
                        if (response.success && response.containers && response.containers.length) {
                            response.containers.forEach(function(row) {
                                tbody.append(`
                                    <tr>
                                        <td>${row.EquipmentType || ''}</td>
                                        <td class="text-center">${row.Quantity || 0}</td>
                                    </tr>`);
                            });
                        } else {
                            tbody.append(
                                '<tr><td colspan="2" class="text-center text-muted">No containers added</td></tr>'
                            );
                        }
                    },
                    error: function() {
                        $('#viewContainerTableBody').html(
                            '<tr><td colspan="2" class="text-center text-danger">Failed to load</td></tr>'
                        );
                    }
                });
            }

            // ============================================================
            // SHIPMENT DETAILS — Edit / Save / Cancel
            // ============================================================
            const savedShipmentMode = '{{ $quotation->shipmentmode ?? '' }}';
            const savedTransportMode = '{{ $quotation->TransportMode ?? '' }}';
            const savedOriginCountry = '{{ $quotation->origincountry ?? '' }}';
            const savedDestCountry = '{{ $quotation->destinationcountry ?? '' }}';
            const savedIncoTerm = '{{ $quotation->incoterm ?? '' }}';
            const savedServiceClass = '{{ $quotation->ServiceClass ?? '' }}';

            let viewShipmentSnapshot = {};
            let viewShipmentDropdownsLoaded = false;

            function bindCountrySearch(searchId, selectId) {
                const $select = $('#' + selectId);
                const allOptions = $select.find('option').clone();
                $('#' + searchId).on('input', function() {
                    const term = $(this).val().toLowerCase().trim();
                    const currentVal = $select.val();
                    $select.empty();
                    allOptions.each(function() {
                        const optText = $(this).text().toLowerCase();
                        const optVal = $(this).val();
                        if (optVal === '' || optText.includes(term)) {
                            $select.append($(this).clone());
                        }
                    });
                    if ($select.find(`option[value="${currentVal}"]`).length) {
                        $select.val(currentVal);
                    }
                });
            }

            function loadViewShipmentDropdowns(callback) {
                if (viewShipmentDropdownsLoaded) {
                    if (typeof callback === 'function') callback();
                    return;
                }
                let remaining = 5;

                function oneDone() {
                    remaining--;
                    if (remaining === 0) {
                        viewShipmentDropdownsLoaded = true;
                        if (typeof callback === 'function') callback();
                    }
                }
                $.ajax({
                    url: '{{ route('quotation.shipment-modes') }}',
                    type: 'GET',
                    success: function(r) {
                        const $sel = $('#viewShipmentModeSelect').empty()
                            .append('<option value="">-- Select --</option>');
                        (r.success ? r.data : []).forEach(function(item) {
                            $sel.append(
                                `<option value="${item.Code}" ${item.Code === savedShipmentMode ? 'selected' : ''}>${item.Code}</option>`
                            );
                        });
                    },
                    complete: oneDone
                });
                $.ajax({
                    url: '{{ route('quotation.transport-modes') }}',
                    type: 'GET',
                    success: function(r) {
                        const $sel = $('#viewTransportModeSelect').empty()
                            .append('<option value="">-- Select --</option>');
                        (r.success ? r.data : []).forEach(function(item) {
                            $sel.append(
                                `<option value="${item.Code}" ${item.Code === savedTransportMode ? 'selected' : ''}>${item.Name}</option>`
                            );
                        });
                    },
                    complete: oneDone
                });
                $.ajax({
                    url: '{{ route('quotation.lookup.countries') }}',
                    type: 'GET',
                    data: {
                        filter: ''
                    },
                    success: function(countries) {
                        const list = Array.isArray(countries) ? countries : [];

                        function fillCountrySelect(selectId, savedValue) {
                            const $sel = $('#' + selectId).empty()
                                .append('<option value="">-- Select --</option>');
                            const normalizedSaved = (savedValue || '').trim().toLowerCase();
                            list.forEach(function(c) {
                                const name = (c.CountryName || '').trim();
                                $sel.append(
                                    `<option value="${name}" ${name.toLowerCase() === normalizedSaved ? 'selected' : ''}>${name}</option>`
                                );
                            });
                        }
                        fillCountrySelect('viewOriginCountrySelect', savedOriginCountry);
                        fillCountrySelect('viewDestCountrySelect', savedDestCountry);
                        bindCountrySearch('viewOriginCountrySearch', 'viewOriginCountrySelect');
                        bindCountrySearch('viewDestCountrySearch', 'viewDestCountrySelect');
                    },
                    complete: oneDone
                });
                $.ajax({
                    url: '{{ route('quotation.inco-terms') }}',
                    type: 'GET',
                    success: function(r) {
                        const $sel = $('#viewIncoTermSelect').empty()
                            .append('<option value="">-- Select --</option>');
                        (r.success ? r.data : []).forEach(function(item) {
                            const code = item.IncoTermCode || item.Code || '';
                            const name = item.Name || code;
                            $sel.append(
                                `<option value="${code}" ${code === savedIncoTerm ? 'selected' : ''}>${code} — ${name}</option>`
                            );
                        });
                    },
                    complete: oneDone
                });
                $.ajax({
                    url: '{{ route('quotation.lookup.serviceClasses') }}',
                    type: 'GET',
                    data: {
                        filter: ''
                    },
                    success: function(classes) {
                        const $sel = $('#viewServiceClassSelect').empty()
                            .append('<option value="">-- Select --</option>');
                        (Array.isArray(classes) ? classes : []).forEach(function(item) {
                            const code = item.Code || '';
                            const display = item.Name || item.Description || code;
                            $sel.append(
                                `<option value="${code}" ${code === savedServiceClass ? 'selected' : ''}>${display}</option>`
                            );
                        });
                    },
                    complete: oneDone
                });
            }

            $('#viewShipmentEditBtn').on('click', function() {
                viewShipmentSnapshot = {
                    shipmentMode: $('#viewShipmentModeSelect').val() || savedShipmentMode,
                    transportMode: $('#viewTransportModeSelect').val() || savedTransportMode,
                    originCountry: $('#viewOriginCountrySelect').val() || savedOriginCountry,
                    destCountry: $('#viewDestCountrySelect').val() || savedDestCountry,
                    incoTerm: $('#viewIncoTermSelect').val() || savedIncoTerm,
                    serviceClass: $('#viewServiceClassSelect').val() || savedServiceClass,
                };
                loadViewShipmentDropdowns(function() {
                    $('#viewShipmentModeDisplay, #viewTransportModeDisplay, #viewIncoTermDisplay, #viewServiceClassDisplay')
                        .addClass('d-none');
                    $('#viewOriginCountryDisplay, #viewDestCountryDisplay').addClass('d-none');
                    $('#viewShipmentModeSelect, #viewTransportModeSelect, #viewIncoTermSelect, #viewServiceClassSelect')
                        .removeClass('d-none').prop('disabled', false);
                    $('#viewOriginCountryEditWrap, #viewDestCountryEditWrap').removeClass('d-none');
                    $('#viewShipmentEditBtn').hide();
                    $('#viewShipmentSaveBtn, #viewShipmentCancelBtn').show();
                });
            });

            $('#viewShipmentCancelBtn').on('click', function() {
                $('#viewShipmentModeSelect').val(viewShipmentSnapshot.shipmentMode);
                $('#viewTransportModeSelect').val(viewShipmentSnapshot.transportMode);
                $('#viewOriginCountrySelect').val(viewShipmentSnapshot.originCountry);
                $('#viewDestCountrySelect').val(viewShipmentSnapshot.destCountry);
                $('#viewIncoTermSelect').val(viewShipmentSnapshot.incoTerm);
                $('#viewServiceClassSelect').val(viewShipmentSnapshot.serviceClass);
                $('#viewOriginCountrySearch, #viewDestCountrySearch').val('');
                $('#viewShipmentModeSelect, #viewTransportModeSelect, #viewIncoTermSelect, #viewServiceClassSelect')
                    .addClass('d-none').prop('disabled', true);
                $('#viewOriginCountryEditWrap, #viewDestCountryEditWrap').addClass('d-none');
                $('#viewShipmentModeDisplay, #viewTransportModeDisplay, #viewIncoTermDisplay, #viewServiceClassDisplay')
                    .removeClass('d-none');
                $('#viewOriginCountryDisplay, #viewDestCountryDisplay').removeClass('d-none');
                $(this).hide();
                $('#viewShipmentSaveBtn').hide();
                $('#viewShipmentEditBtn').show();
            });

            $('#viewShipmentSaveBtn').on('click', function() {
                const shipmentMode = $('#viewShipmentModeSelect').val();
                const transportMode = $('#viewTransportModeSelect').val();
                const originCountry = $('#viewOriginCountrySelect').val();
                const destCountry = $('#viewDestCountrySelect').val();
                const incoTerm = $('#viewIncoTermSelect').val();
                const serviceClass = $('#viewServiceClassSelect').val();

                if (!shipmentMode) {
                    toastr.error('Please select a Shipment Mode.');
                    return;
                }
                if (!transportMode) {
                    toastr.error('Please select a Transport Mode.');
                    return;
                }
                if (!originCountry) {
                    toastr.error('Please select an Origin Country.');
                    return;
                }
                if (!destCountry) {
                    toastr.error('Please select a Destination Country.');
                    return;
                }
                if (!incoTerm) {
                    toastr.error('Please select an InCO Term.');
                    return;
                }
                if (!serviceClass) {
                    toastr.error('Please select a Service Class.');
                    return;
                }

                const $btn = $(this);
                $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Saving...');

                $.ajax({
                    url: '{{ route('quotation.shipment.save') }}',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        booking_no: bookingNo,
                        shipment_mode: shipmentMode,
                        transport_mode: transportMode,
                        incoterm: incoTerm,
                        origincountry: originCountry,
                        destinationcountry: destCountry,
                        service_class: serviceClass,
                    },
                    success: function(r) {
                        if (!r.success) {
                            toastr.error(r.message || 'Shipment save failed.');
                            $btn.prop('disabled', false).html(
                                '<i class="fas fa-save mr-1"></i> Save');
                            return;
                        }
                        $('#viewShipmentModeDisplay').val(shipmentMode);
                        $('#viewTransportModeDisplay').val($(
                            '#viewTransportModeSelect option:selected').text().trim());
                        $('#viewOriginCountryDisplay').val(originCountry);
                        $('#viewDestCountryDisplay').val(destCountry);
                        $('#viewIncoTermDisplay').val(incoTerm);
                        $('#viewServiceClassDisplay').val(serviceClass);
                        $('#viewShipmentModeSelect, #viewTransportModeSelect, #viewIncoTermSelect, #viewServiceClassSelect')
                            .addClass('d-none').prop('disabled', true);
                        $('#viewOriginCountryEditWrap, #viewDestCountryEditWrap').addClass(
                            'd-none');
                        $('#viewShipmentModeDisplay, #viewTransportModeDisplay, #viewIncoTermDisplay, #viewServiceClassDisplay')
                            .removeClass('d-none');
                        $('#viewOriginCountryDisplay, #viewDestCountryDisplay').removeClass(
                            'd-none');
                        $('#viewOriginCountrySearch, #viewDestCountrySearch').val('');
                        $('#viewShipmentSaveBtn, #viewShipmentCancelBtn').hide();
                        $('#viewShipmentEditBtn').show();
                        toastr.success('Shipment details saved successfully.');
                    },
                    error: function(xhr) {
                        toastr.error(xhr.responseJSON?.message ||
                            'Unexpected error saving shipment details.');
                    },
                    complete: function() {
                        $btn.prop('disabled', false).html(
                            '<i class="fas fa-save mr-1"></i> Save');
                    }
                });
            });

            // ============================================================
            // DUPLICATE — centered overlay card (existing button)
            // ============================================================
            $('#btnCopyBooking').off('click').on('click', function() {
                const $btn = $(this);
                $btn.prop('disabled', true).html(
                    '<i class="fas fa-spinner fa-spin mr-1"></i> Duplicating...');
                $.ajax({
                    url: '{{ route('quotation.processDuplicate', $quotation->QuoteNo) }}',
                    type: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    contentType: 'application/json',
                    data: JSON.stringify({}),
                    success: function(response) {
                        $btn.prop('disabled', false).html(
                            '<i class="fas fa-copy mr-1"></i> Duplicate');
                        if (response.success) {
                            $('#dupResultLink').text(response.new_booking_no).attr('href',
                                response.redirect_url);
                            $('#dupResultCard').slideDown(300);
                        } else {
                            toastr.error(response.message || 'Duplication failed.');
                        }
                    },
                    error: function(xhr) {
                        $btn.prop('disabled', false).html(
                            '<i class="fas fa-copy mr-1"></i> Duplicate');
                        toastr.error('Error: ' + (xhr.responseJSON?.message ||
                            'Unexpected error (HTTP ' + xhr.status + ')'));
                    }
                });
            });

            $('#dupResultClose').on('click', function() {
                $('#dupResultCard').slideUp(200);
            });

            // ============================================================
            // LOAD CHARGES ON PAGE LOAD
            // ============================================================
            if (bookingNo) loadChargesForView();

            function loadChargesForView() {
                $.ajax({
                    url: '{{ route('quotation.charges') }}',
                    type: 'GET',
                    data: {
                        booking_no: bookingNo,
                        quotation_id: '{{ $quotation->ID ?? '' }}'
                    },
                    beforeSend: function() {
                        const spinner =
                            '<tr><td colspan="7" class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading...</td></tr>';
                        $('#revenueChargesViewTable, #costChargesViewTable').html(spinner);
                    },
                    success: function(response) {
                        if (response.success) {
                            renderRevenueChargesView(response.revenue || []);
                            renderCostChargesView(response.cost || []);
                            updateViewSummary(response.revenue || [], response.cost || []);
                        } else {
                            const err =
                                '<tr><td colspan="7" class="text-center text-danger">Failed to load charges</td></tr>';
                            $('#revenueChargesViewTable, #costChargesViewTable').html(err);
                        }
                    },
                    error: function() {
                        const err =
                            '<tr><td colspan="7" class="text-center text-danger">Error loading charges</td></tr>';
                        $('#revenueChargesViewTable, #costChargesViewTable').html(err);
                    }
                });
            }

            // ============================================================
            // REQUEST OFFICE QUOTATION HISTORY — view page (read-only)
            // Mirrors the edit page implementation without the action button.
            // ============================================================
            const CURRENT_OFFICE_CODE_VIEW = '{{ trim($officeShortCode ?? '') }}';
            const QUOTATION_ID_VIEW = '{{ $quotation->ID ?? '' }}';

            loadRequestOfficeHistoryView();

            function loadRequestOfficeHistoryView() {
                $.ajax({
                    url: '{{ route('quotation.request-office-history') }}',
                    type: 'GET',
                    data: {
                        quotation_id: QUOTATION_ID_VIEW
                    },
                    beforeSend: function() {
                        $('#viewRequestOfficeHistoryTable').html(
                            '<tr><td colspan="3" class="text-center text-muted">' +
                            '<i class="fas fa-spinner fa-spin"></i> Loading...</td></tr>'
                        );
                    },
                    success: function(response) {
                        renderRequestOfficeHistoryView(response.data || []);
                    },
                    error: function() {
                        $('#viewRequestOfficeHistoryTable').html(
                            '<tr><td colspan="3" class="text-center text-danger">Failed to load</td></tr>'
                        );
                    }
                });
            }

            function renderRequestOfficeHistoryView(rows) {
                const $tbody = $('#viewRequestOfficeHistoryTable');
                $tbody.empty();

                if (!rows.length) {
                    $tbody.html(
                        '<tr><td colspan="3" class="text-center text-muted">No requests made yet</td></tr>'
                    );
                    return;
                }

                rows.forEach(function(row) {
                    const isSend = (row.RequestedByOfficeCode || '').toUpperCase() ===
                        CURRENT_OFFICE_CODE_VIEW.toUpperCase();

                    const requestType = isSend ? 'Send' : 'Received';
                    const badgeClass = isSend ? 'badge-primary' : 'badge-success';

                    // Show the OTHER party's office code
                    const officeName = isSend ?
                        (row.RequestedToCode || '') :
                        (row.RequestedByOfficeCode || '');

                    const quoteNo = row.RequestedToQuoteNo || '';

                    $tbody.append(`
            <tr>
                <td>${officeName}</td>
                <td>${quoteNo}</td>
                <td>
                    <span class="badge ${badgeClass}">${requestType}</span>
                </td>
            </tr>
        `);
                });
            }

            function renderRevenueChargesView(charges) {
                const tbody = $('#revenueChargesViewTable');
                tbody.empty();
                if (!charges.length) {
                    tbody.append(
                        '<tr><td colspan="7" class="text-center text-muted">No revenue charges found</td></tr>');
                    $('#revenueTotalHeaderAmount').text('0.00');
                    return;
                }
                let total = 0;
                charges.forEach(function(c) {
                    total += parseFloat(c.amtInBaseCurr || 0);
                    tbody.append(`<tr>
                        <td>${c.chargeName || c.chargeCode || ''}</td>
                        <td class="text-right">${parseFloat(c.rate || 0).toFixed(2)}</td>
                        <td class="text-right col-extra">${parseFloat(c.quantity || 0).toFixed(2)}</td>
                        <td class="col-extra">${c.basedOnUOM || ''}</td>
                        <td class="col-extra">${c.chgCurr || getBaseCurrency()}</td>
                        <td class="text-right">${parseFloat(c.amtInChgCurr || 0).toFixed(2)}</td>
                        <td class="text-right">${parseFloat(c.amtInBaseCurr || 0).toFixed(2)}</td>
                    </tr>`);
                });
                $('#revenueTotalHeaderAmount').text(total.toFixed(2));
                if (!$('.show-items-link[data-table="revenue"]').data('expanded')) {
                    tbody.find('.col-extra').hide();
                }
            }

            function renderCostChargesView(charges) {
                const tbody = $('#costChargesViewTable');
                tbody.empty();
                if (!charges.length) {
                    tbody.append(
                        '<tr><td colspan="7" class="text-center text-muted">No cost charges found</td></tr>');
                    $('#costTotalHeaderAmount').text('0.00');
                    return;
                }
                let total = 0;
                charges.forEach(function(c) {
                    total += parseFloat(c.amtInBaseCurr || 0);
                    tbody.append(`<tr>
                        <td>${c.chargeName || c.chargeCode || ''}</td>
                        <td class="text-right">${parseFloat(c.rate || 0).toFixed(2)}</td>
                        <td class="text-right col-extra">${parseFloat(c.quantity || 0).toFixed(2)}</td>
                        <td class="col-extra">${c.basedOnUOM || ''}</td>
                        <td class="col-extra">${c.chgCurr || getBaseCurrency()}</td>
                        <td class="text-right">${parseFloat(c.amtInChgCurr || 0).toFixed(2)}</td>
                        <td class="text-right">${parseFloat(c.amtInBaseCurr || 0).toFixed(2)}</td>
                    </tr>`);
                });
                $('#costTotalHeaderAmount').text(total.toFixed(2));
                if (!$('.show-items-link[data-table="cost"]').data('expanded')) {
                    tbody.find('.col-extra').hide();
                }
            }

            function updateViewSummary(revenueCharges, costCharges) {
                const totalRevenue = revenueCharges.reduce((s, c) => s + parseFloat(c.amtInBaseCurr || 0), 0);
                const totalCost = costCharges.reduce((s, c) => s + parseFloat(c.amtInBaseCurr || 0), 0);
                const margin = totalRevenue - totalCost;
                const marginPct = totalRevenue > 0 ? (margin / totalRevenue * 100) : 0;
                const bc = getBaseCurrency();
                $('#viewQuotedRevenue').val(bc + ' ' + totalRevenue.toFixed(2));
                $('#viewQuotedMargin').val(bc + ' ' + margin.toFixed(2) + ' (' + marginPct.toFixed(2) + '%)');
            }

            // Load email history
            function loadViewEmailHistory() {
                $.ajax({
                    url: '{{ route('quotation.email.history') }}',
                    type: 'GET',
                    data: {
                        booking_no: '{{ $quotation->bookingno ?? '' }}'
                    },
                    beforeSend: function() {
                        $('#viewEmailHistoryTable').html(
                            '<tr><td colspan="3" class="text-center text-muted"><i class="fas fa-spinner fa-spin"></i> Loading...</td></tr>'
                        );
                    },
                    success: function(response) {
                        var tbody = $('#viewEmailHistoryTable');
                        tbody.empty();
                        if (response.emails && response.emails.length) {
                            response.emails.forEach(function(email) {
                                tbody.append(`
                        <tr>
                            <td>${email.sent_on ?? ''}</td>
                            <td>${email.to_email ?? ''}</td>
                            <td>${email.quote_version ?? ''}</td>
                        </tr>
                    `);
                            });
                        } else {
                            tbody.html(
                                '<tr><td colspan="3" class="text-center text-muted">No emails sent yet</td></tr>'
                            );
                        }
                    },
                    error: function() {
                        $('#viewEmailHistoryTable').html(
                            '<tr><td colspan="3" class="text-center text-danger">Failed to load</td></tr>'
                        );
                    }
                });
            }
            // Call it on page load
            loadViewEmailHistory();

            function loadViewApprovalStatus() {
                $('#viewApprovalEmpty, #viewApprovalContent, #viewApprovalUnavailable').hide();
                $('#viewApprovalLoading').show();

                $.ajax({
                    url: '{{ route('quotation.approval.status', $quotation->ID ?? 0) }}',
                    type: 'GET',
                    success: function(response) {
                        $('#viewApprovalLoading').hide();
                        if (response.success && response.data && response.data.status) {
                            renderViewApproval(response.data);
                        } else if (response.message) {
                            $('#viewApprovalUnavailableMsg').text(response.message);
                            $('#viewApprovalUnavailable').show();
                        } else {
                            $('#viewApprovalEmpty').show();
                        }
                    },
                    error: function(xhr) {
                        $('#viewApprovalLoading').hide();
                        $('#viewApprovalUnavailableMsg').text(xhr.responseJSON?.message ||
                            'Unable to load approval status.');
                        $('#viewApprovalUnavailable').show();
                    }
                });
            }

            function renderViewApproval(data) {
                $('#viewApprovalEmpty, #viewApprovalUnavailable').hide();
                $('#viewApprovalContent').show();

                var badgeClass = data.status === 'Approved' ? 'badge-success' :
                    data.status === 'Rejected' ? 'badge-danger' :
                    data.status === 'Pending Approval' ? 'badge-warning' : 'badge-secondary';
                $('#viewApprovalStatusBadge').removeClass().addClass('badge px-2 py-1 ' + badgeClass).text(data
                    .status);

                var pct = data.required > 0 ? Math.min(100, (data.approvedCount / data.required) * 100) : 0;
                $('#viewApprovalProgressBar').css('width', pct + '%');
                $('#viewApprovalProgressLabel').text(data.approvedCount + ' of ' + data.required + ' Approved');

                var tbody = $('#viewApprovalApproverTable tbody').empty();
                (data.approvers || []).forEach(function(a) {
                    var badge = a.status === 'Approved' ? 'badge-success' :
                        a.status === 'Rejected' ? 'badge-danger' : 'badge-secondary';
                    tbody.append(`
            <tr>
                <td style="font-size:0.85rem;">${a.name}</td>
                <td class="text-right">
                    <span class="badge ${badge}" style="font-size:0.7rem;">${a.status}</span>
                    ${a.date ? `<small class="text-muted ml-1">${a.date}</small>` : ''}
                </td>
            </tr>
        `);
                });
            }

            // Call on load
            loadViewApprovalStatus();

            // ============================================================
            // SHOW MORE / SHOW LESS
            // ============================================================
            $('.col-extra').hide();

            $('.show-items-link').on('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
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

            // ============================================================
            // CARD COLLAPSE
            // ============================================================
            $('[data-card-widget="collapse"]').on('click', function() {
                $(this).find('i').toggleClass('fa-chevron-down fa-chevron-up');
            });

            $('.card-header').on('click', function(e) {
                if (!$(e.target).closest('[data-card-widget="collapse"]').length &&
                    !$(e.target).closest('.show-items-link').length) {
                    $(this).find('[data-card-widget="collapse"]').click();
                }
            });

            $('.card').not('.card-success, .card-danger, .summary-card').each(function() {
                $(this).find('.card-body').hide();
                $(this).find('.card-footer').hide();
                $(this).find('[data-card-widget="collapse"] i')
                    .removeClass('fa-chevron-up')
                    .addClass('fa-chevron-down');
                $(this).addClass('collapsed-card');
            });

            $(document).on('click', '[data-card-widget="collapse"]', function() {
                const $card = $(this).closest('.card');
                setTimeout(function() {
                    const isExpanded = $card.find('.card-body').is(':visible');
                    $card.find('.card-footer').toggle(isExpanded);
                }, 0);
            });
        }); // end document.ready
    </script>
@endsection
