@extends('adminlte::page')

@section('title', 'View Booking Quotation')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1><i class="fas fa-file-invoice"></i> Inquiry No - {{ $quotation->QuoteNo ?? 'N/A' }}</h1>
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
        $isInProgress = in_array($quoteStatus, ['Proposal', 'Pending']) || is_null($quoteStatus);
    @endphp

    <div class="row">
        <!-- LEFT COLUMN -->
        <div class="col-md-4">

            <!-- Booking Details -->
            <div class="card card-primary" data-card-section="inquiry"
                data-save-url="{{ route('quotation.inquiry.save') }}">
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
                        <label class="col-sm-5 col-form-label">Sales person</label>
                        <div class="col-sm-7">
                            <input type="text" class="form-control form-control-sm" data-field="username"
                                value="{{ $quotation->username ?? '' }}" readonly>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-sm-5 col-form-label">Sales Person Email</label>
                        <div class="col-sm-7">
                            <input type="text" class="form-control form-control-sm" data-field="salesperson_email"
                                value="{{ $quotation->salesperson_email ?? '' }}" readonly>
                        </div>
                    </div>
                    <div class="form-group row mb-0">
                        <label class="col-sm-5 col-form-label">Office</label>
                        <div class="col-sm-7">
                            <input type="text" class="form-control form-control-sm" data-field="teamname"
                                value="{{ $quotation->teamname ?? '' }}" readonly>
                        </div>
                    </div>
                </div>
                @if ($isInProgress)
                    <div class="card-footer card-action-footer">
                        <button type="button" class="btn btn-warning btn-xs btn-card-edit">
                            <i class="fas fa-edit mr-1"></i> Edit
                        </button>
                        <button type="button" class="btn btn-success btn-xs btn-card-save" style="display:none;">
                            <i class="fas fa-save mr-1"></i> Save
                        </button>
                        <button type="button" class="btn btn-secondary btn-xs btn-card-cancel" style="display:none;">
                            <i class="fas fa-times mr-1"></i> Cancel
                        </button>
                    </div>
                @endif
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

                    {{-- Service — always read-only --}}
                    <div class="form-group row">
                        <label class="col-sm-5 col-form-label">Service</label>
                        <div class="col-sm-7">
                            <input type="text" class="form-control form-control-sm"
                                value="{{ $quotation->SVName ?? '' }}" readonly>
                        </div>
                    </div>

                    {{-- Process — always read-only --}}
                    <div class="form-group row">
                        <label class="col-sm-5 col-form-label">Process</label>
                        <div class="col-sm-7">
                            <input type="text" class="form-control form-control-sm"
                                value="{{ $quotation->process ?? '' }}" readonly>
                        </div>
                    </div>

                    {{-- Origin Country --}}
                    <div class="form-group row">
                        <label class="col-sm-5 col-form-label">Origin Country</label>
                        <div class="col-sm-7">
                            <input type="text" class="form-control form-control-sm" id="viewOriginCountryDisplay"
                                value="{{ $quotation->origincountry ?? '' }}" readonly>
                            <div class="d-none" id="viewOriginCountryEditWrap">
                                <input type="text" class="form-control form-control-sm mb-1"
                                    id="viewOriginCountrySearch" placeholder="Search country...">
                                <select class="form-control form-control-sm" id="viewOriginCountrySelect">
                                    <option value="">-- Select --</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- Destination Country --}}
                    <div class="form-group row">
                        <label class="col-sm-5 col-form-label">Destination Country</label>
                        <div class="col-sm-7">
                            <input type="text" class="form-control form-control-sm" id="viewDestCountryDisplay"
                                value="{{ $quotation->destinationcountry ?? '' }}" readonly>
                            <div class="d-none" id="viewDestCountryEditWrap">
                                <input type="text" class="form-control form-control-sm mb-1"
                                    id="viewDestCountrySearch" placeholder="Search country...">
                                <select class="form-control form-control-sm" id="viewDestCountrySelect">
                                    <option value="">-- Select --</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- InCO Terms --}}
                    <div class="form-group row">
                        <label class="col-sm-5 col-form-label">InCO Terms</label>
                        <div class="col-sm-7">
                            <input type="text" class="form-control form-control-sm" id="viewIncoTermDisplay"
                                value="{{ $quotation->incoterm ?? '' }}" readonly>
                            <select class="form-control form-control-sm d-none" id="viewIncoTermSelect" disabled>
                                <option value="">-- Select --</option>
                            </select>
                        </div>
                    </div>

                    {{-- Shipment Mode --}}
                    <div class="form-group row">
                        <label class="col-sm-5 col-form-label">Shipment Mode</label>
                        <div class="col-sm-7">
                            <input type="text" class="form-control form-control-sm" id="viewShipmentModeDisplay"
                                value="{{ $quotation->shipmentmode ?? '' }}" readonly>
                            <select class="form-control form-control-sm d-none" id="viewShipmentModeSelect" disabled>
                                <option value="">-- Select --</option>
                            </select>
                        </div>
                    </div>

                    {{-- Transport Mode --}}
                    <div class="form-group row">
                        <label class="col-sm-5 col-form-label">Transport Mode</label>
                        <div class="col-sm-7">
                            <input type="text" class="form-control form-control-sm" id="viewTransportModeDisplay"
                                value="{{ $quotation->TransportMode ?? '' }}" readonly>
                            <select class="form-control form-control-sm d-none" id="viewTransportModeSelect" disabled>
                                <option value="">-- Select --</option>
                            </select>
                        </div>
                    </div>

                    {{-- Service Class --}}
                    <div class="form-group row mb-0">
                        <label class="col-sm-5 col-form-label">Service Class</label>
                        <div class="col-sm-7">
                            <input type="text" class="form-control form-control-sm" id="viewServiceClassDisplay"
                                value="{{ $quotation->ServiceClass ?? '' }}" readonly>
                            <select class="form-control form-control-sm d-none" id="viewServiceClassSelect" disabled>
                                <option value="">-- Select --</option>
                            </select>
                        </div>
                    </div>

                </div>
                @if ($isInProgress)
                    <div class="card-footer card-action-footer">
                        <button type="button" class="btn btn-warning btn-xs" id="viewShipmentEditBtn">
                            <i class="fas fa-edit mr-1"></i> Edit
                        </button>
                        <button type="button" class="btn btn-success btn-xs" id="viewShipmentSaveBtn"
                            style="display:none;">
                            <i class="fas fa-save mr-1"></i> Save
                        </button>
                        <button type="button" class="btn btn-secondary btn-xs" id="viewShipmentCancelBtn"
                            style="display:none;">
                            <i class="fas fa-times mr-1"></i> Cancel
                        </button>
                    </div>
                @endif
            </div>

            <!-- Cargo Details -->
            <div class="card card-primary" data-card-section="cargo"
                data-save-url="{{ route('quotation.cargo.save') }}">
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
                            <input type="text" class="form-control form-control-sm" data-field="scopevolume"
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
                @if ($isInProgress)
                    <div class="card-footer card-action-footer">
                        <button type="button" class="btn btn-warning btn-xs btn-card-edit">
                            <i class="fas fa-edit mr-1"></i> Edit
                        </button>
                        <button type="button" class="btn btn-success btn-xs btn-card-save" style="display:none;">
                            <i class="fas fa-save mr-1"></i> Save
                        </button>
                        <button type="button" class="btn btn-secondary btn-xs btn-card-cancel" style="display:none;">
                            <i class="fas fa-times mr-1"></i> Cancel
                        </button>
                    </div>
                @endif
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
            <div class="card card-primary" data-card-section="customer"
                data-save-url="{{ route('quotation.customer.save') }}">
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
                                    <input type="text" class="form-control form-control-sm" data-field="companyname"
                                        value="{{ $quotation->companyname ?? '' }}" readonly>
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
                                        data-field="pri_partnermaster_name"
                                        value="{{ $quotation->pri_partnermaster_name ?? '' }}" readonly>

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
                        </div>
                    </div>
                </div>
                @if ($isInProgress)
                    <div class="card-footer card-action-footer">
                        <button type="button" class="btn btn-warning btn-xs btn-card-edit">
                            <i class="fas fa-edit mr-1"></i> Edit
                        </button>
                        <button type="button" class="btn btn-success btn-xs btn-card-save" style="display:none;">
                            <i class="fas fa-save mr-1"></i> Save
                        </button>
                        <button type="button" class="btn btn-secondary btn-xs btn-card-cancel" style="display:none;">
                            <i class="fas fa-times mr-1"></i> Cancel
                        </button>
                    </div>
                @endif
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

        /* Card footer action buttons */
        .card-action-footer {
            background: #f8f9fa;
            border-top: 1px solid rgba(0, 0, 0, .125);
            padding: 0.5rem 1rem;
        }

        /* Highlight inputs when card is in edit mode */
        .card-in-edit input[data-field]:not([readonly]) {
            background-color: #fff9e6;
            border-color: #ffc107 !important;
        }

        .card-action-footer {
            background: #f8f9fa;
            border-top: 1px solid rgba(0, 0, 0, .125);
            padding: 0.5rem 1rem;
        }

        .card-in-edit input[data-field]:not([readonly]) {
            background-color: #fff9e6;
            border-color: #ffc107 !important;
        }
    </style>
@stop

@section('js')
    <script>
        $(document).ready(function() {

            // ============================================================
            // CARD EDITOR — per-card Edit / Save / Cancel
            // ============================================================
            class CardEditor {
                constructor(cardEl) {
                    this.card = cardEl;
                    this.section = cardEl.dataset.cardSection;
                    this.saveUrl = cardEl.dataset.saveUrl;
                    this.snapshot = {};
                    this._bind();
                }

                _fields() {
                    return Array.from(this.card.querySelectorAll('[data-field]'));
                }

                _bind() {
                    this.card.querySelector('.btn-card-edit')
                        ?.addEventListener('click', () => this.enterEdit());
                    this.card.querySelector('.btn-card-save')
                        ?.addEventListener('click', () => this.save());
                    this.card.querySelector('.btn-card-cancel')
                        ?.addEventListener('click', () => this.cancel());
                }

                enterEdit() {
                    this.snapshot = {};
                    this._fields().forEach(input => {
                        this.snapshot[input.dataset.field] = input.value;
                        input.removeAttribute('readonly');
                    });
                    this.card.classList.add('card-in-edit');
                    this.card.querySelector('.btn-card-edit').style.display = 'none';
                    this.card.querySelector('.btn-card-save').style.display = '';
                    this.card.querySelector('.btn-card-cancel').style.display = '';
                }

                cancel() {
                    this._fields().forEach(input => {
                        input.value = this.snapshot[input.dataset.field] ?? input.value;
                        input.setAttribute('readonly', true);
                    });
                    this.card.classList.remove('card-in-edit');
                    this.card.querySelector('.btn-card-edit').style.display = '';
                    this.card.querySelector('.btn-card-save').style.display = 'none';
                    this.card.querySelector('.btn-card-cancel').style.display = 'none';
                    this.snapshot = {};
                }

                save() {
                    const saveBtn = this.card.querySelector('.btn-card-save');
                    const payload = {
                        _token: '{{ csrf_token() }}',
                        id: '{{ $quotation->ID }}'
                    };
                    this._fields().forEach(input => {
                        payload[input.dataset.field] = input.value;
                    });

                    saveBtn.disabled = true;
                    saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Saving...';

                    $.ajax({
                        url: this.saveUrl,
                        type: 'POST',
                        data: payload,
                        success: (res) => {
                            if (res.success) {
                                this._fields().forEach(input => input.setAttribute('readonly',
                                    true));
                                this.card.classList.remove('card-in-edit');
                                this.card.querySelector('.btn-card-edit').style.display = '';
                                this.card.querySelector('.btn-card-save').style.display = 'none';
                                this.card.querySelector('.btn-card-cancel').style.display = 'none';
                                this.snapshot = {};
                                toastr.success('Saved successfully.');
                            } else {
                                toastr.error(res.message || 'Save failed.');
                                saveBtn.disabled = false;
                                saveBtn.innerHTML = '<i class="fas fa-save mr-1"></i> Save';
                            }
                        },
                        error: (xhr) => {
                            toastr.error('Error: ' + (xhr.responseJSON?.message ||
                                'Unexpected error.'));
                            saveBtn.disabled = false;
                            saveBtn.innerHTML = '<i class="fas fa-save mr-1"></i> Save';
                        }
                    });
                }
            }

            // Mount CardEditor on every card that declares data-card-section
            document.querySelectorAll('[data-card-section]').forEach(el => new CardEditor(el));

            const bookingNo = '{{ $quotation->bookingno ?? '' }}';

            // ============================================================
            // VIEW PAGE — Load containers (read-only table)
            // ============================================================
            const QUOTE_NO_VIEW = '{{ $quotation->QuoteNo ?? '' }}';
            const SCOPE_OF_VOLUME = '{{ $quotation->scopevolume ?? '' }}';

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
                        if (response.success && response.data && response.data.length) {
                            response.data.forEach(function(row) {
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
                $('#' + searchId).on('input', function() {
                    const term = $(this).val().toLowerCase();
                    const $opts = $('#' + selectId + ' option');
                    $opts.each(function() {
                        $(this).toggle($(this).val() === '' || $(this).text().toLowerCase()
                            .includes(term));
                    });
                    const $sel = $('#' + selectId);
                    if ($sel.find('option:selected').css('display') === 'none') $sel.val('');
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

                // Shipment Mode
                $.ajax({
                    url: '{{ route('quotation.shipment-modes') }}',
                    type: 'GET',
                    success: function(r) {
                        const $sel = $('#viewShipmentModeSelect').empty().append(
                            '<option value="">-- Select --</option>');
                        (r.success ? r.data : []).forEach(function(item) {
                            $sel.append(
                                `<option value="${item.Code}" ${item.Code === savedShipmentMode ? 'selected' : ''}>${item.Code}</option>`
                            );
                        });
                    },
                    complete: oneDone
                });

                // Transport Mode
                $.ajax({
                    url: '{{ route('quotation.transport-modes') }}',
                    type: 'GET',
                    success: function(r) {
                        const $sel = $('#viewTransportModeSelect').empty().append(
                            '<option value="">-- Select --</option>');
                        (r.success ? r.data : []).forEach(function(item) {
                            $sel.append(
                                `<option value="${item.Code}" ${item.Code === savedTransportMode ? 'selected' : ''}>${item.Name}</option>`
                            );
                        });
                    },
                    complete: oneDone
                });

                // Countries
                $.ajax({
                    url: '{{ route('quotation.lookup.countries') }}',
                    type: 'GET',
                    data: {
                        filter: ''
                    },
                    success: function(countries) {
                        const list = Array.isArray(countries) ? countries : [];

                        function fillCountrySelect(selectId, savedValue) {
                            const $sel = $('#' + selectId).empty().append(
                                '<option value="">-- Select --</option>');
                            list.forEach(function(c) {
                                const name = c.Name || c.CountryName || '';
                                $sel.append(
                                    `<option value="${name}" ${name === savedValue ? 'selected' : ''}>${name}</option>`
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

                // InCo Terms — uses quotation.inco-terms (now correctly above wildcards)
                $.ajax({
                    url: '{{ route('quotation.inco-terms') }}',
                    type: 'GET',
                    success: function(r) {
                        const $sel = $('#viewIncoTermSelect').empty().append(
                            '<option value="">-- Select --</option>');
                        const list = r.success ? (r.data || []) : [];
                        list.forEach(function(item) {
                            const code = item.IncoTermCode || item.Code || '';
                            $sel.append(
                                `<option value="${code}" ${code === savedIncoTerm ? 'selected' : ''}>${code}</option>`
                            );
                        });
                    },
                    complete: oneDone
                });

                // Service Class
                $.ajax({
                    url: '{{ route('quotation.lookup.serviceClasses') }}',
                    type: 'GET',
                    data: {
                        filter: ''
                    },
                    success: function(classes) {
                        const $sel = $('#viewServiceClassSelect').empty().append(
                            '<option value="">-- Select --</option>');
                        (Array.isArray(classes) ? classes : []).forEach(function(item) {
                            const code = item.Code || item.ServiceClass || '';
                            $sel.append(
                                `<option value="${code}" ${code === savedServiceClass ? 'selected' : ''}>${code}</option>`
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
                    $('#viewShipmentModeDisplay,#viewTransportModeDisplay,#viewIncoTermDisplay,#viewServiceClassDisplay')
                        .addClass('d-none');
                    $('#viewOriginCountryDisplay,#viewDestCountryDisplay').addClass('d-none');
                    $('#viewShipmentModeSelect,#viewTransportModeSelect,#viewIncoTermSelect,#viewServiceClassSelect')
                        .removeClass('d-none').prop('disabled', false);
                    $('#viewOriginCountryEditWrap,#viewDestCountryEditWrap').removeClass('d-none');
                    $('#viewShipmentEditBtn').hide();
                    $('#viewShipmentSaveBtn,#viewShipmentCancelBtn').show();
                });
            });

            $('#viewShipmentCancelBtn').on('click', function() {
                $('#viewShipmentModeSelect').val(viewShipmentSnapshot.shipmentMode);
                $('#viewTransportModeSelect').val(viewShipmentSnapshot.transportMode);
                $('#viewOriginCountrySelect').val(viewShipmentSnapshot.originCountry);
                $('#viewDestCountrySelect').val(viewShipmentSnapshot.destCountry);
                $('#viewIncoTermSelect').val(viewShipmentSnapshot.incoTerm);
                $('#viewServiceClassSelect').val(viewShipmentSnapshot.serviceClass);
                $('#viewOriginCountrySearch,#viewDestCountrySearch').val('');
                $('#viewOriginCountrySelect option,#viewDestCountrySelect option').show();
                $('#viewShipmentModeSelect,#viewTransportModeSelect,#viewIncoTermSelect,#viewServiceClassSelect')
                    .addClass('d-none').prop('disabled', true);
                $('#viewOriginCountryEditWrap,#viewDestCountryEditWrap').addClass('d-none');
                $('#viewShipmentModeDisplay,#viewTransportModeDisplay,#viewIncoTermDisplay,#viewServiceClassDisplay')
                    .removeClass('d-none');
                $('#viewOriginCountryDisplay,#viewDestCountryDisplay').removeClass('d-none');
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
                        transport_mode: transportMode
                    },
                    success: function(r1) {
                        if (!r1.success) {
                            toastr.error(r1.message || 'Shipment mode save failed.');
                            $btn.prop('disabled', false).html(
                                '<i class="fas fa-save mr-1"></i> Save');
                            return;
                        }
                        $.ajax({
                            url: '{{ route('quotation.update', $quotation->ID) }}',
                            type: 'POST',
                            data: {
                                _token: '{{ csrf_token() }}',
                                origincountry: originCountry,
                                destinationcountry: destCountry,
                                // incoterm: incoTerm,
                                ServiceClass: serviceClass
                            },
                            success: function(r2) {
                                if (r2.success) {
                                    $('#viewShipmentModeDisplay').val(shipmentMode);
                                    $('#viewTransportModeDisplay').val($(
                                        '#viewTransportModeSelect option:selected'
                                    ).text().trim());
                                    $('#viewOriginCountryDisplay').val(
                                        originCountry);
                                    $('#viewDestCountryDisplay').val(destCountry);
                                    $('#viewIncoTermDisplay').val(incoTerm);
                                    $('#viewServiceClassDisplay').val(serviceClass);
                                    $('#viewShipmentModeSelect,#viewTransportModeSelect,#viewIncoTermSelect,#viewServiceClassSelect')
                                        .addClass('d-none').prop('disabled', true);
                                    $('#viewOriginCountryEditWrap,#viewDestCountryEditWrap')
                                        .addClass('d-none');
                                    $('#viewShipmentModeDisplay,#viewTransportModeDisplay,#viewIncoTermDisplay,#viewServiceClassDisplay')
                                        .removeClass('d-none');
                                    $('#viewOriginCountryDisplay,#viewDestCountryDisplay')
                                        .removeClass('d-none');
                                    $('#viewOriginCountrySearch,#viewDestCountrySearch')
                                        .val('');
                                    $('#viewShipmentSaveBtn,#viewShipmentCancelBtn')
                                        .hide();
                                    $('#viewShipmentEditBtn').show();
                                    toastr.success(
                                        'Shipment details saved successfully.');
                                } else {
                                    toastr.error(r2.message ||
                                        'Failed to save country / incoterm / service class.'
                                    );
                                }
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
                    },
                    error: function(xhr) {
                        toastr.error(xhr.responseJSON?.message ||
                            'Unexpected error saving shipment mode.');
                        $btn.prop('disabled', false).html(
                            '<i class="fas fa-save mr-1"></i> Save');
                    }
                });
            });

            // ============================================================
            // DUPLICATE — centered overlay card
            // ============================================================
            $('#btnCopyBooking').off('click').on('click', function() {
                const $btn = $(this);
                $btn.prop('disabled', true).html(
                    '<i class="fas fa-spinner fa-spin mr-1"></i> Duplicating...');

                $.ajax({
                    url: '{{ route('quotation.processDuplicate', $quotation->bookingno) }}',
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
                        console.error('Duplicate error:', xhr.responseJSON || xhr.responseText);
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
                        booking_no: bookingNo
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
                <td class="col-extra">${c.chgCurr || 'KES'}</td>
                <td class="text-right">${parseFloat(c.amtInChgCurr || 0).toFixed(2)}</td>
                <td class="text-right">${parseFloat(c.amtInBaseCurr || 0).toFixed(2)}</td>
            </tr>`);
                });
                $('#revenueTotalHeaderAmount').text(total.toFixed(2));
                if (!$('.show-items-link[data-table="revenue"]').data('expanded')) tbody.find('.col-extra').hide();
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
                <td class="col-extra">${c.chgCurr || 'KES'}</td>
                <td class="text-right">${parseFloat(c.amtInChgCurr || 0).toFixed(2)}</td>
                <td class="text-right">${parseFloat(c.amtInBaseCurr || 0).toFixed(2)}</td>
            </tr>`);
                });
                $('#costTotalHeaderAmount').text(total.toFixed(2));
                if (!$('.show-items-link[data-table="cost"]').data('expanded')) tbody.find('.col-extra').hide();
            }

            function updateViewSummary(revenueCharges, costCharges) {
                const totalRevenue = revenueCharges.reduce((s, c) => s + parseFloat(c.amtInBaseCurr || 0), 0);
                const totalCost = costCharges.reduce((s, c) => s + parseFloat(c.amtInBaseCurr || 0), 0);
                const margin = totalRevenue - totalCost;
                const marginPct = totalRevenue > 0 ? (margin / totalRevenue * 100) : 0;
                $('#viewQuotedRevenue').val('KES ' + totalRevenue.toFixed(2));
                $('#viewQuotedMargin').val('KES ' + margin.toFixed(2) + ' (' + marginPct.toFixed(2) + '%)');
            }

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

            // Collapse all on load except Revenue, Cost, Summary — exclude dupOverlay inner card
            $('.card').not('.card-success, .card-danger, .summary-card').each(function() {
                $(this).find('.card-body').hide();
                $(this).find('[data-card-widget="collapse"] i').removeClass('fa-chevron-up').addClass(
                    'fa-chevron-down');
                $(this).addClass('collapsed-card');
            });

        });
    </script>
@endsection
