@extends('adminlte::page')

@section('title', 'Quotation Management')

@section('content')
    <div class="container-fluid">

        {{-- Statistics Cards --}}
        <div class="row mb-3">

            {{-- 1. In Progress --}}
            <div class="col-6 col-md-4 col-lg mb-2">
                <div class="small-box bg-info card-filter-trigger compact-card" data-filter="in_progress">
                    <div class="inner">
                        <h3 id="stat-in-progress">{{ $stats['in_progress'] }}</h3>
                        <p>In Progress</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-hourglass-half"></i>
                    </div>
                </div>
            </div>

            {{-- 1b. In Progress Request --}}
            <div class="col-6 col-md-4 col-lg mb-2">
                <div class="small-box bg-purple card-filter-trigger compact-card" data-filter="in_progress_request">
                    <div class="inner">
                        <h3 id="stat-in-progress-request">{{ $stats['in_progress_request'] ?? 0 }}</h3>
                        <p>In Progress(Requested Quote)</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-paper-plane"></i>
                    </div>
                </div>
            </div>

            {{-- 1c. Pending Approval --}}
            {{-- <div class="col-6 col-md-4 col-lg mb-2">
                <div class="small-box bg-orange card-filter-trigger compact-card" data-filter="pending_approval">
                    <div class="inner">
                        <h3 id="stat-pending-approval">{{ $stats['pending_approval'] ?? 0 }}</h3>
                        <p>Pending Approval</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-hourglass-half"></i>
                    </div>
                </div>
            </div> --}}

            {{-- 2. Won --}}
            <div class="col-6 col-md-4 col-lg mb-2">
                <div class="small-box bg-success card-filter-trigger compact-card" data-filter="won">
                    <div class="inner">
                        <h3 id="stat-won">{{ $stats['won'] }}</h3>
                        <p>Won</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
            </div>

            {{-- 3. Lost / Cancelled --}}
            <div class="col-6 col-md-4 col-lg mb-2">
                <div class="small-box bg-warning card-filter-trigger compact-card" data-filter="lost_canceled">
                    <div class="inner">
                        <h3 id="stat-lost-canceled">{{ $stats['lost_canceled'] }}</h3>
                        <p>Lost / Cancelled</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-times-circle"></i>
                    </div>
                </div>
            </div>

            {{-- 4. Failed Progress --}}
            <div class="col-6 col-md-4 col-lg mb-2">
                <div class="small-box bg-secondary card-filter-trigger compact-card" data-filter="failed_progress">
                    <div class="inner">
                        <h3 id="stat-failed-progress">{{ $stats['failed_progress'] }}</h3>
                        <p>Failed Progress</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-exclamation-circle"></i>
                    </div>
                </div>
            </div>

            {{-- 5. Failed Int. --}}
            <div class="col-6 col-md-4 col-lg mb-2">
                <div class="small-box bg-danger card-filter-trigger compact-card" data-filter="failed_int">
                    <div class="inner">
                        <h3 id="stat-failed-int">{{ $stats['failed_int'] }}</h3>
                        <p>Failed Int.</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                </div>
            </div>

            {{-- 6. Completed --}}
            <div class="col-6 col-md-4 col-lg mb-2">
                <div class="small-box bg-teal card-filter-trigger compact-card" data-filter="completed">
                    <div class="inner">
                        <h3 id="stat-completed">{{ $stats['completed'] }}</h3>
                        <p>Completed</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-clipboard-check"></i>
                    </div>
                </div>
            </div>

            {{-- 7. Total --}}
            <div class="col-6 col-md-4 col-lg mb-2">
                <div class="small-box bg-primary card-filter-trigger compact-card" data-filter="total">
                    <div class="inner">
                        <h3 id="stat-total">{{ $stats['total'] }}</h3>
                        <p>Total</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-database"></i>
                    </div>
                </div>
            </div>

        </div>{{-- END Statistics Cards row --}}

        {{-- Filters and Table Card --}}
        <div class="card">
            <div class="card-body">

                {{-- Filters Row --}}
                <div class="row mb-3 g-2">

                    {{-- Show entries --}}
                    <div class="col-12 col-md-auto order-1">
                        <div id="entriesContainer"></div>
                    </div>

                    {{-- Date Filter --}}
                    <div class="col-12 col-sm-6 col-md-auto order-2">
                        <select id="dateFilter" class="form-control form-control-sm">
                            <option value="all" selected>All Time</option>
                            <option value="0">Today</option>
                            <option value="1">Yesterday</option>
                            <option value="15">Last 15 Days</option>
                            <option value="30">Last 30 Days</option>
                            <option value="custom">Custom Range</option>
                        </select>
                    </div>

                    {{-- Office Filter --}}
                    <div class="col-12 col-sm-6 col-md-auto order-3">
                        @if (!empty($officeError))
                            <div class="alert alert-danger py-1 px-2 mb-1" style="font-size:0.85rem;">
                                <i class="fas fa-exclamation-triangle"></i> {{ $officeError }}
                            </div>
                        @endif
                        <select id="officeFilter" class="form-control form-control-sm"
                            {{ $offices->isEmpty() ? 'disabled' : '' }}>
                            @if ($offices->count() > 1)
                                <option value="">All Offices</option>
                            @endif
                            @foreach ($offices as $office)
                                <option value="{{ $office['name'] }}"
                                    {{ ($activeOfficeName ?? $defaultOffice) === $office['name'] ? 'selected' : '' }}>
                                    {{ $office['name'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Custom Date Range - Start Date --}}
                    <div class="col-12 col-sm-6 col-md-auto order-4" id="customDateRange" style="display: none;">
                        <input type="date" id="startDate" class="form-control form-control-sm" placeholder="Start Date">
                    </div>

                    {{-- Custom Date Range - End Date --}}
                    <div class="col-12 col-sm-6 col-md-auto order-5" id="customDateRangeTo" style="display: none;">
                        <div class="input-group input-group-sm">
                            <input type="date" id="endDate" class="form-control form-control-sm"
                                placeholder="End Date">
                            <button class="btn btn-outline-secondary" type="button" id="clearCustomDates"
                                title="Clear custom date range">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>

                    {{-- Clear Filters Button --}}
                    <div class="col-12 col-sm-6 col-md-auto order-6" id="clearFiltersContainer" style="display: none;">
                        <button type="button" id="clearAllFilters"
                            class="btn btn-outline-secondary btn-sm w-100 w-md-auto" title="Clear all filters">
                            <i class="fas fa-redo"></i> Clear Filters
                        </button>
                    </div>

                    {{-- Search --}}
                    <div class="col-12 col-md-auto order-7 ms-md-auto">
                        <div id="searchContainer"></div>
                    </div>

                    {{-- CRM Sync button --}}
                    <div class="col-12 col-sm-auto col-md-auto order-8">
                        <button type="button" class="btn btn-outline-primary btn-sm" id="btnOpenCrmSync"
                            title="Sync records from CRM">
                            <i class="fas fa-sync-alt mr-1"></i> CRM Sync
                        </button>
                    </div>

                </div>{{-- END filters row --}}

                {{-- DataTable --}}
                <div class="table-responsive-wrapper">
                    <table id="quotationTable" class="table table-bordered table-striped table-hover w-100 dtr-inline">
                        <thead>
                            <tr>
                                <th data-priority="1">Quote No</th>
                                <th data-priority="2">Date</th>
                                <th data-priority="3">Client Name</th>
                                <th data-priority="9">Service</th>
                                <th data-priority="10">Process</th>
                                <th data-priority="11">Origin</th>
                                <th data-priority="12">Destination</th>
                                <th data-priority="8">IncoTerm</th>
                                <th data-priority="13">Volume / Containers</th>
                                <th data-priority="4">Status</th>
                                <th data-priority="6">Remark</th>
                                <th data-priority="7">Sales Person</th>
                                <th data-priority="5">Action</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>{{-- END table-responsive-wrapper --}}

            </div>{{-- END card-body --}}
        </div>{{-- END card --}}

        {{-- ═══════════════════════════════════════════════════ --}}
        {{-- MODALS — must live OUTSIDE the card, at page level --}}
        {{-- ═══════════════════════════════════════════════════ --}}

        {{-- CREATE BOOKING MODAL --}}
        <div class="modal fade" id="createBookingModal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="modal-title" style="color: #003580; font-weight: 700;">
                            Creation of Booking
                        </h2>
                        <button type="button" class="close" id="cbXBtn" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body" style="padding: 1.75rem 2rem;">
                        <div id="createBookingForm">

                            <div class="form-group mb-4">
                                <label for="cb_service_template">
                                    Service Template <span class="text-danger">*</span>
                                </label>
                                <select class="form-control" id="cb_service_template" name="service_template" required>
                                    <option value=""></option>
                                </select>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <div class="form-group mb-0">
                                        <label for="cb_bill_to_party">
                                            Bill To Party <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" class="form-control" id="cb_bill_to_party"
                                            placeholder="Type at least 3 characters..." autocomplete="off">
                                        <input type="hidden" id="cb_bill_to_party_code">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-0">
                                        <label for="cb_forwarder">
                                            Forwarder <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" class="form-control" id="cb_forwarder"
                                            placeholder="Type at least 3 characters..." autocomplete="off">
                                        <input type="hidden" id="cb_forwarder_code">
                                    </div>
                                </div>
                            </div>

                            <div class="form-group mb-3 mt-3">
                                <div class="form-check d-flex align-items-center">
                                    <input class="form-check-input me-2" type="checkbox" id="cb_include_country"
                                        name="include_country">
                                    <label class="form-check-label mb-0" for="cb_include_country">
                                        Include the Country
                                    </label>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-0">
                                        <label for="cb_place_of_receipt">
                                            Place of Receipt <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" class="form-control" id="cb_place_of_receipt"
                                            placeholder="Type at least 2 characters..." autocomplete="off">
                                        <input type="hidden" id="cb_place_of_receipt_code">
                                        <input type="hidden" id="cb_place_of_receipt_id">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-0">
                                        <label for="cb_place_of_delivery">
                                            Place of Delivery <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" class="form-control" id="cb_place_of_delivery"
                                            placeholder="Type at least 2 characters..." autocomplete="off">
                                        <input type="hidden" id="cb_place_of_delivery_code">
                                        <input type="hidden" id="cb_place_of_delivery_id">
                                    </div>
                                </div>
                            </div>

                        </div>{{-- END createBookingForm --}}

                        <div id="cbProgressPanel" class="d-none mt-3">
                            <p class="text-muted small mb-2">
                                Processing — please do not close this window.
                            </p>
                            <div class="list-group" id="cbStepList"></div>
                            <div id="cbProgressError" class="alert alert-danger mt-3 d-none"></div>
                            <div id="cbProgressSuccess" class="alert alert-success mt-3 d-none">
                                <i class="fas fa-check-circle mr-2"></i>
                                All steps completed. Booking has been created in SCM.
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer d-flex justify-content-between align-items-center">
                        <div>
                            <a href="#" id="cbEditLink" class="btn btn-sm btn-outline-secondary d-none"
                                title="Edit booking information">
                                <i class="fas fa-edit mr-1"></i> Edit
                            </a>
                            <a href="#" id="cbViewStepsLink" class="btn btn-sm btn-outline-info d-none ml-1"
                                title="View integration steps">
                                <i class="fas fa-list-ol mr-1"></i> View Steps
                            </a>
                        </div>
                        <div class="d-flex align-items-center">
                            <a href="#" class="text-primary mr-3" id="cancelCreateBookingBtn"
                                style="font-weight: 500; text-decoration: underline;">Cancel</a>
                            <button type="button" class="btn btn-primary px-4"
                                id="submitCreateBookingBtn">Create</button>
                            <button type="button" class="btn btn-secondary px-4 d-none" id="cbCloseBtn">Close</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>{{-- END createBookingModal --}}

        {{-- CRM SYNC MODAL --}}
        <div class="modal fade" id="crmSyncModal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="fas fa-sync-alt mr-2"></i> CRM → Quotation Sync
                        </h5>
                        <button type="button" class="close" data-dismiss="modal">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-2 mb-3">
                            <div class="col-md-4">
                                <label class="font-weight-bold">Mode</label>
                                <div class="form-check mt-1">
                                    <input class="form-check-input" type="checkbox" id="crmDryRun">
                                    <label class="form-check-label" for="crmDryRun">
                                        Dry Run <small class="text-muted">(preview, no writes)</small>
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="font-weight-bold">
                                    Limit <small class="text-muted">(optional)</small>
                                </label>
                                <input type="number" id="crmLimit" class="form-control form-control-sm"
                                    placeholder="blank = all records">
                            </div>
                            <div class="col-md-4">
                                <label class="font-weight-bold">
                                    Booking No <small class="text-muted">(optional)</small>
                                </label>
                                <input type="text" id="crmBooking" class="form-control form-control-sm"
                                    placeholder="e.g. 7037 — blank = all">
                            </div>
                        </div>
                        <div id="crmSyncProgress" class="d-none mb-3">
                            <div class="d-flex align-items-center text-muted">
                                <div class="spinner-border spinner-border-sm mr-2" role="status"></div>
                                <span>Connecting to CRM and running sp_QuoteDetails()...</span>
                            </div>
                        </div>
                        <div id="crmSyncResult"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary" id="btnRunCrmSync">
                            <i class="fas fa-play mr-1"></i> Run Sync
                        </button>
                    </div>
                </div>
            </div>
        </div>{{-- END crmSyncModal --}}

        {{-- QUOTATION REPORT MODAL --}}
        <div class="modal fade" id="quotationReportModal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-xl" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="fas fa-search mr-2"></i> Quotation Report
                        </h5>
                        <button type="button" class="close" data-dismiss="modal">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">

                        {{-- Filter Row --}}
                        <div class="row g-2 mb-3">

                            {{-- Hidden status input — always sends Total --}}
                            <input type="hidden" id="rpt_status" value="Total">

                            <div class="col-12 col-sm-6 col-md-2">
                                <label class="font-weight-bold small">From Date</label>
                                <input type="date" id="rpt_from_date" class="form-control form-control-sm">
                            </div>
                            <div class="col-12 col-sm-6 col-md-2">
                                <label class="font-weight-bold small">To Date</label>
                                <input type="date" id="rpt_to_date" class="form-control form-control-sm">
                            </div>
                            <div class="col-12 col-sm-6 col-md-2">
                                <label class="font-weight-bold small">Customer</label>
                                <input type="text" id="rpt_customer" class="form-control form-control-sm"
                                    placeholder="Company name...">
                            </div>
                            <div class="col-12 col-sm-6 col-md-2">
                                <label class="font-weight-bold small">Service</label>
                                <input type="text" id="rpt_service" class="form-control form-control-sm"
                                    placeholder="e.g. Air Freight">
                            </div>
                            <div class="col-12 col-sm-6 col-md-1">
                                <label class="font-weight-bold small">Process</label>
                                <select id="rpt_process" class="form-control form-control-sm">
                                    <option value="">All</option>
                                    <option value="Export">Export</option>
                                    <option value="Import">Import</option>
                                    <option value="Local">Local</option>
                                </select>
                            </div>
                            <div class="col-12 col-sm-6 col-md-1">
                                <label class="font-weight-bold small">IncoTerm</label>
                                <input type="text" id="rpt_incoterm" class="form-control form-control-sm"
                                    placeholder="e.g. FOB">
                            </div>
                            <div class="col-12 col-sm-6 col-md-auto d-flex align-items-end">
                                <button type="button" id="btnClearReport" class="btn btn-outline-secondary btn-sm">
                                    <i class="fas fa-redo mr-1"></i> Clear
                                </button>
                            </div>

                        </div>{{-- END filter row --}}

                        {{-- Error alert --}}
                        <div id="rptErrorAlert" class="alert alert-danger d-none py-2 px-3 mb-2"
                            style="font-size:0.875rem;"></div>

                        {{-- Results table --}}
                        <div class="table-responsive">
                            <table id="reportTable" class="table table-bordered table-striped table-hover w-100"
                                style="font-size:0.85rem;">
                                <thead>
                                    <tr>
                                        <th>Quote No</th>
                                        <th>Date</th>
                                        <th>Customer</th>
                                        <th>Service</th>
                                        <th>Process</th>
                                        <th>Origin</th>
                                        <th>Destination</th>
                                        <th>IncoTerm</th>
                                        <th>Status</th>
                                        <th>Remark</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>

                        <div id="rptRowCount" class="text-muted small mt-2"></div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>{{-- END quotationReportModal --}}

    </div>
@stop


@section('css')
    <style>
        /* COMPACT CARD STYLING */
        .compact-card {
            min-height: 90px !important;
            margin-bottom: 0.5rem;
            cursor: pointer;
            border-radius: 0.25rem;
            transition: all 0.3s ease;
        }

        .compact-card .inner {
            padding: 7px 10px !important;
        }

        .compact-card .inner h3 {
            font-size: 1.5rem;
            margin-bottom: 2px;
        }

        .compact-card .inner p {
            font-size: 0.75rem;
            margin-bottom: 0;
            white-space: nowrap;
        }

        .compact-card .icon {
            font-size: 38px !important;
            top: 8px;
            right: 8px;
        }

        /* Non-clickable cards should not show pointer cursor */
        .compact-card:not(.card-filter-trigger) {
            cursor: default;
        }

        /* Hover — desktop/mouse only */
        @media (hover: hover) and (pointer: fine) {
            .card-filter-trigger:hover {
                transform: translateY(-3px);
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            }
        }

        .compact-card.card-filter-trigger:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .card-filter-trigger.active {
            box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.5), 0 0 0 6px rgba(0, 0, 0, 0.3);
            transform: scale(1.03);
        }

        @media (hover: none) and (pointer: coarse) {
            .card-filter-trigger:active {
                opacity: 0.8;
            }
        }

        @media (max-width: 767.98px) {
            .compact-card .icon {
                font-size: 28px !important;
            }

            .compact-card .inner h3 {
                font-size: 1.2rem;
            }

            .compact-card .inner p {
                font-size: 0.7rem;
            }
        }

        /* FORM CONTROLS */
        .form-control-sm {
            height: calc(1.5em + 0.5rem + 2px);
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }

        /* FILTER ROW */
        #entriesContainer .dataTables_length,
        #searchContainer .dataTables_filter {
            margin: 0;
        }

        #entriesContainer .dataTables_length label,
        #searchContainer .dataTables_filter label {
            margin: 0;
            font-size: 0.875rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            width: 100%;
        }

        #entriesContainer .dataTables_length select {
            width: auto;
            min-width: 60px;
        }

        #searchContainer .dataTables_filter input {
            margin-left: 0;
            width: 100%;
            max-width: 200px;
        }

        /* BUTTONS */
        .input-group-sm .btn {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
            line-height: 1.5;
        }

        .input-group-sm .btn i {
            font-size: 0.75rem;
        }

        #clearCustomDates {
            border-left: 0;
        }

        #clearCustomDates:hover {
            background-color: #dc3545;
            color: white;
            border-color: #dc3545;
        }

        #clearAllFilters {
            height: calc(1.5em + 0.5rem + 2px);
            white-space: nowrap;
        }

        #clearAllFilters:hover {
            background-color: #6c757d;
            color: white;
            border-color: #6c757d;
        }

        @media (max-width: 767.98px) {
            .btn-retrigger {
                min-width: 44px;
                min-height: 44px;
                padding: 0.5rem;
                font-size: 1rem;
            }

            #clearAllFilters,
            #clearCustomDates {
                min-height: 44px;
            }
        }

        /* LAYOUT */
        .g-2 {
            --bs-gutter-x: 0.5rem;
            --bs-gutter-y: 0.5rem;
        }

        .table-responsive-wrapper {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        body,
        .container-fluid {
            overflow-x: hidden;
        }

        /* DATATABLE RESPONSIVE */
        table.dataTable.dtr-inline.collapsed>tbody>tr>td.child,
        table.dataTable.dtr-inline.collapsed>tbody>tr>th.child,
        table.dataTable.dtr-inline.collapsed>tbody>tr>td.dataTables_empty {
            cursor: default !important;
        }

        table.dataTable.dtr-inline.collapsed>tbody>tr[role="row"]>td:first-child:before,
        table.dataTable.dtr-inline.collapsed>tbody>tr[role="row"]>th:first-child:before {
            top: 50%;
            transform: translateY(-50%);
            line-height: 1;
        }

        table.dataTable.dtr-inline.collapsed>tbody>tr.parent>td:first-child:before,
        table.dataTable.dtr-inline.collapsed>tbody>tr.parent>th:first-child:before {
            background-color: #28a745;
        }

        table.dataTable>tbody>tr.child ul.dtr-details {
            width: 100%;
        }

        table.dataTable>tbody>tr.child ul.dtr-details>li {
            border-bottom: 1px solid #efefef;
            padding: 0.5rem 0;
        }

        table.dataTable>tbody>tr.child span.dtr-title {
            font-weight: bold;
            min-width: 120px;
            display: inline-block;
        }

        .dataTables_info,
        .dataTables_paginate {
            padding-top: 0.75rem;
        }

        .dataTables_wrapper>.row:first-child {
            display: none;
        }

        /* MOBILE / TABLET */
        @media (max-width: 767.98px) {
            .compact-card .icon {
                font-size: 40px !important;
            }

            .compact-card .inner h3 {
                font-size: 1.5rem;
            }

            .compact-card .inner p {
                font-size: 0.75rem;
            }

            #searchContainer .dataTables_filter input {
                max-width: 100%;
            }

            #searchContainer .dataTables_filter label {
                flex-direction: column;
                align-items: stretch;
                gap: 0.25rem;
            }

            #quotationTable th,
            #quotationTable td {
                font-size: 0.85rem;
                padding: 0.5rem;
            }

            #entriesContainer .dataTables_length label {
                flex-direction: row;
                align-items: center;
            }

            #entriesContainer .dataTables_length select {
                flex: 0 0 auto;
            }
        }

        @media (min-width: 768px) and (max-width: 1199.98px) {
            #searchContainer .dataTables_filter input {
                max-width: 150px;
            }
        }

        /* UTILITY */
        .w-md-auto {
            width: auto !important;
        }

        .ms-md-auto {
            margin-left: auto !important;
        }

        @media (max-width: 767.98px) {
            .ms-md-auto {
                margin-left: 0 !important;
            }
        }

        /* ACTION BUTTONS */
        .action-buttons {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 6px;
            flex-wrap: nowrap;
        }

        .action-buttons .btn {
            padding: 0.35rem 0.5rem;
            min-width: 34px;
        }

        @media (max-width: 576px) {
            .action-buttons .btn {
                padding: 0.4rem;
            }
        }

        /* CREATE BOOKING MODAL — LOCKED STATE */
        #createBookingForm.cb-locked select,
        #createBookingForm.cb-locked input[type="text"] {
            background-color: #f8f9fa;
            pointer-events: none;
            opacity: 0.85;
            border-color: #dee2e6;
        }

        .bg-purple {
            background-color: #7b68ee !important;
            color: #fff !important;
        }

        .bg-purple .inner h3,
        .bg-purple .inner p {
            color: #fff !important;
        }
    </style>
@stop


@section('js')
    <script>
        $(document).ready(function() {

            var currentCardFilter = 'in_progress';
            var _activeQuotationId = null; // tracks the currently open booking modal's quotation ID
            // ── Booking step definitions ──────────────────────────────────────────────
            // Must stay in sync with processBooking() step order in QuotationController.
            const BOOKING_STEPS = [{
                    step: 0,
                    label: 'Save Booking Info'
                },
                {
                    step: 2,
                    label: 'Booking XML'
                },
                {
                    step: '2b',
                    label: 'SCM Push'
                },
                {
                    step: 3,
                    label: 'Charge Transfer'
                },
                {
                    step: 4,
                    label: 'Document Transfer'
                },
            ];

            // Set date filter before DataTable initialises so the first request picks it up
            $('#dateFilter').val('all');

            var _savedOffice = null;
            try {
                _savedOffice = localStorage.getItem('selectedOffice');
            } catch (e) {}
            if (_savedOffice && $('#officeFilter option[value="' + _savedOffice + '"]').length) {
                $('#officeFilter').val(_savedOffice);
            }

            // ── updateCards() ────────────────────────────────────────────────────────
            // Called by dataSrc on every table reload.
            // Stats and rows arrive in the same response — zero mismatch risk.
            // Replaces the old updateCardStats() + animateValue() pair entirely.
            function updateCards(stats) {
                if (!stats) return;
                $('#stat-total').text(stats.total || 0);
                $('#stat-in-progress').text(stats.in_progress || 0);
                $('#stat-in-progress-request').text(stats.in_progress_request || 0);
                $('#stat-pending-approval').text(stats.pending_approval || 0);
                $('#stat-won').text(stats.won || 0);
                $('#stat-completed').text(stats.completed || 0);
                $('#stat-lost-canceled').text(stats.lost_canceled || 0);
                $('#stat-failed-progress').text(stats.failed_progress || 0);
                $('#stat-failed-int').text(stats.failed_int || 0);
            }

            // ── DataTable ────────────────────────────────────────────────────────────
            var table = $('#quotationTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('quotation.portal') }}',
                    type: 'GET',
                    data: function(d) {
                        d.days = $('#dateFilter').val();
                        d.start_date = $('#startDate').val();
                        d.end_date = $('#endDate').val();
                        d.office = $('#officeFilter').val();
                        d.card_filter = currentCardFilter;
                    },
                    // dataSrc fires before DataTables processes the response.
                    // We extract stats here so the cards update in the same round-trip
                    // as the table rows — no separate XHR ever needed.
                    dataSrc: function(json) {
                        updateCards(json.stats);
                        return json.data;
                    },
                    error: function(xhr, error, code) {
                        console.error('DataTables Ajax Error:', xhr, error, code);
                    }
                },
                columns: [
                    // col 0 — QuoteNo
                    {
                        data: 'QuoteNo',
                        name: 'QuoteNo',
                        responsivePriority: 1
                    },
                    // col 1 — Date
                    {
                        data: 'Date',
                        name: 'Date',
                        searchable: false,
                        responsivePriority: 2
                    },
                    // col 2 — Client Name
                    {
                        data: 'CompanyName',
                        name: 'CompanyName',
                        responsivePriority: 3,
                        render: function(data, type) {
                            if (type === 'display' && data && data.length > 50)
                                return '<span title="' + data + '">' + data.substr(0, 50) +
                                    '...</span>';
                            return data;
                        }
                    },
                    // col 3 — Service
                    {
                        data: 'Service',
                        name: 'Service',
                        responsivePriority: 9
                    },
                    // col 4 — Process
                    {
                        data: 'Process',
                        name: 'Process',
                        responsivePriority: 10
                    },
                    // col 5 — Origin
                    {
                        data: 'OriginCountry',
                        name: 'OriginCountry',
                        responsivePriority: 11
                    },
                    // col 6 — Destination
                    {
                        data: 'DestinationCountry',
                        name: 'DestinationCountry',
                        responsivePriority: 12
                    },
                    // col 7 — IncoTerm
                    {
                        data: 'IncoTerm',
                        name: 'IncoTerm',
                        responsivePriority: 8
                    },
                    // col 8 — Volume / Containers (combined, display-only)
                    {
                        data: null,
                        name: 'volume',
                        orderable: false,
                        searchable: false,
                        responsivePriority: 13,
                        render: function(data, type, row) {
                            if (type !== 'display') return '';
                            var parts = [];
                            if (row.ScopeOfVolume && row.ScopeOfVolume !== '-')
                                parts.push(row.ScopeOfVolume);
                            if (row.CargoVolume && parseFloat(row.CargoVolume) > 0)
                                parts.push(parseFloat(row.CargoVolume).toLocaleString());
                            if (row.ContainerList && row.ContainerList !== '-')
                                parts.push('<span class="text-muted">' + row.ContainerList +
                                    '</span>');
                            return parts.length ?
                                parts.join('<br>') :
                                '<span class="text-muted">—</span>';
                        }
                    },
                    // col 9 — Status
                    {
                        data: 'Status',
                        name: 'Status',
                        responsivePriority: 4
                    },
                    // col 10 — Remark
                    {
                        data: 'Remark',
                        name: 'Remark',
                        orderable: false,
                        searchable: false,
                        responsivePriority: 6
                    },
                    // col 11 — Sales Person
                    {
                        data: 'SalesPerson',
                        name: 'SalesPerson',
                        responsivePriority: 7
                    },
                    // col 12 — Action
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        responsivePriority: 1,
                        className: 'text-center action-column'
                    }
                ],
                order: [
                    [1, 'desc']
                ],
                pageLength: 25,
                responsive: {
                    details: {
                        type: 'inline',
                        display: $.fn.dataTable.Responsive.display.childRow,
                        renderer: function(api, rowIdx, columns) {
                            var data = $.map(columns, function(col) {
                                return col.hidden ?
                                    '<li data-dtr-index="' + col.columnIndex + '"' +
                                    ' data-dt-row="' + col.rowIndex + '"' +
                                    ' data-dt-column="' + col.columnIndex + '">' +
                                    '<span class="dtr-title">' + col.title + '</span> ' +
                                    '<span class="dtr-data">' + col.data + '</span>' +
                                    '</li>' :
                                    '';
                            }).join('');
                            return data ? $('<ul class="dtr-details"/>').append(data) : false;
                        }
                    },
                    breakpoints: [{
                            name: 'desktop',
                            width: Infinity
                        },
                        {
                            name: 'tablet-l',
                            width: 1024
                        },
                        {
                            name: 'tablet-p',
                            width: 768
                        },
                        {
                            name: 'mobile-l',
                            width: 480
                        },
                        {
                            name: 'mobile-p',
                            width: 320
                        }
                    ]
                },
                dom: 'lfrtip',
                language: {
                    search: 'Search:',
                    searchPlaceholder: 'Company, Quote No, Status...',
                    lengthMenu: 'Show _MENU_'
                },
                initComplete: function() {
                    $('.dataTables_length').appendTo('#entriesContainer');
                    $('.dataTables_filter').appendTo('#searchContainer');
                    adjustSearchPlaceholder();
                    $('.card-filter-trigger[data-filter="in_progress"]').addClass('active');
                }
            });

            // ── Search placeholder — responsive ──────────────────────────────────────
            function adjustSearchPlaceholder() {
                $('.dataTables_filter input').attr(
                    'placeholder',
                    $(window).width() < 768 ? 'Search...' : 'Company, Quote No, Status...'
                );
            }
            $(window).on('resize', adjustSearchPlaceholder);

            // ── Card click ───────────────────────────────────────────────────────────
            var touchMoved = false;
            $('.card-filter-trigger')
                .on('touchstart', function() {
                    touchMoved = false;
                })
                .on('touchmove', function() {
                    touchMoved = true;
                })
                .on('click touchend', function(e) {
                    if (e.type === 'touchend' && touchMoved) return;
                    if (e.type === 'touchend') e.preventDefault();
                    var filterType = $(this).data('filter');
                    if (!filterType) return;
                    $('.card-filter-trigger').removeClass('active');
                    $(this).addClass('active');
                    currentCardFilter = filterType;
                    table.ajax.reload(); // stats arrive automatically via dataSrc
                });

            // ── Clear button visibility ───────────────────────────────────────────────
            function updateClearButtonVisibility() {
                var dateFilter = $('#dateFilter').val();
                var officeFilter = $('#officeFilter').val();
                var defaultOffice = '{{ $activeOfficeName ?? ($defaultOffice ?? '') }}';
                if (dateFilter === 'custom') {
                    $('#clearFiltersContainer').hide();
                    return;
                }
                if (dateFilter !== 'all' || officeFilter !== defaultOffice) {
                    $('#clearFiltersContainer').show();
                } else {
                    $('#clearFiltersContainer').hide();
                }
            }

            // ── Date filter ──────────────────────────────────────────────────────────
            $('#dateFilter').on('change', function() {
                if ($(this).val() === 'custom') {
                    $('#customDateRange, #customDateRangeTo').show();
                    updateClearButtonVisibility();
                } else {
                    $('#customDateRange, #customDateRangeTo').hide();
                    $('#startDate, #endDate').val('');
                    table.ajax.reload();
                    updateClearButtonVisibility();
                }
            });

            $('#startDate, #endDate').on('change', function() {
                if ($('#startDate').val() && $('#endDate').val()) {
                    if (new Date($('#endDate').val()) < new Date($('#startDate').val())) {
                        alert('End date must be after start date');
                        $('#endDate').val('');
                        return;
                    }
                    table.ajax.reload();
                    updateClearButtonVisibility();
                }
            });

            $('#clearCustomDates').on('click', function() {
                $('#startDate, #endDate').val('');
                $('#customDateRange, #customDateRangeTo').hide();
                $('#dateFilter').val('all');
                table.ajax.reload();
            });

            // ── Office filter ────────────────────────────────────────────────────────
            $('#officeFilter').on('change', function() {
                try {
                    localStorage.setItem('selectedOffice', $(this).val());
                } catch (e) {}
                table.ajax.reload();
                updateClearButtonVisibility();
            });

            // ── Clear all filters ────────────────────────────────────────────────────
            $('#clearAllFilters').on('click', function() {
                $('#dateFilter').val('all');
                $('#startDate, #endDate').val('');
                $('#customDateRange, #customDateRangeTo').hide();
                $('#officeFilter').val('{{ $activeOfficeName ?? ($defaultOffice ?? '') }}');
                try {
                    localStorage.setItem('selectedOffice', $('#officeFilter').val());
                } catch (e) {}
                $('.card-filter-trigger').removeClass('active');
                $('.card-filter-trigger[data-filter="in_progress"]').addClass('active');
                currentCardFilter = 'in_progress';
                table.ajax.reload();
                updateClearButtonVisibility();
            });

            // ── Create Booking button ─────────────────────────────────────────────────
            $('#quotationTable').on('click', '.btn-create-booking', function(e) {
                e.stopPropagation();
                var $btn = $(this);
                _activeQuotationId = $btn.data('id'); // capture before modal opens
                $('#createBookingModal')
                    .data('quotation-id', $btn.data('id'))
                    .data('booking-no', $btn.data('quoteno'))
                    .data('team-id', $btn.data('team'))
                    .data('quote-no', $btn.data('quoteno'))
                    .data('origin-country', $btn.data('origin'))
                    .data('destination-country', $btn.data('destination'))
                    .data('card-filter', currentCardFilter)
                    .modal('show');
            });

            // ── Create Booking modal — on show ───────────────────────────────────────
            // AFTER — restore previous state if same quotation
            $('#createBookingModal').on('show.bs.modal', function() {
                var modal = $(this);
                var quoteNo = modal.data('quote-no');
                var teamId = modal.data('team-id');
                var quotationId = modal.data('quotation-id');
                var cardFilter = modal.data('card-filter') || 'in_progress';
                var lastQuoteNo = modal.data('last-quote-no');
                var savedState = modal.data('saved-form-state');

                // If reopening for the same quotation after a failure, restore form state
                // Fall back to sessionStorage if jQuery data was lost (e.g. page refresh)
                if ((!savedState || String(lastQuoteNo) !== String(quoteNo)) && quotationId) {
                    try {
                        var _ss = sessionStorage.getItem('cbFormState_' + quotationId);
                        if (_ss) {
                            savedState = JSON.parse(_ss);
                            // Write back so the restore check below finds them
                            modal.data('saved-form-state', savedState);
                            lastQuoteNo = quoteNo;
                            modal.data('last-quote-no', quoteNo);
                        }
                    } catch (e) {}
                }

                // ── 1. Failed steps take priority — go straight to steps panel ───────────
                var _savedSteps = null;
                try {
                    var _ssSteps = sessionStorage.getItem('cbStepsState_' + quotationId);
                    if (_ssSteps) _savedSteps = JSON.parse(_ssSteps);
                } catch (e) {}

                if (_savedSteps && _savedSteps.find(function(s) {
                        return s.status && s.status.toLowerCase() === 'failed';
                    })) {
                    if (!modal.data('saved-form-state')) {
                        try {
                            var _ssForm = sessionStorage.getItem('cbFormState_' + quotationId);
                            if (_ssForm) modal.data('saved-form-state', JSON.parse(_ssForm));
                        } catch (e) {}
                    }
                    modal.data('completed-steps', _savedSteps);
                    modal.data('last-quote-no', quoteNo);
                    resetCreateBookingForm();
                    $('#createBookingForm').addClass('d-none');
                    $('#cbProgressPanel').removeClass('d-none');
                    $('#submitCreateBookingBtn, #cancelCreateBookingBtn').addClass('d-none');
                    $('#cbCloseBtn').removeClass('d-none');
                    $('#cbProgressError, #cbProgressSuccess').addClass('d-none');
                    $('#cbEditLink').removeClass('d-none');
                    renderBookingSteps(_savedSteps);
                    return;
                }

                // ── 2. Restore form state if available ───────────────────────────────────
                if (savedState && String(lastQuoteNo) === String(quoteNo)) {
                    resetCreateBookingForm();
                    setDropdownLoading('#cb_service_template');
                    $.get('{{ route('quotation.booking.service-templates') }}', {
                            quotation_id: quotationId
                        })
                        .done(function(res) {
                            var $s = $('#cb_service_template').empty().append(
                                '<option value=""></option>');
                            if (res.success && res.templates && res.templates.length) {
                                $.each(res.templates, function(i, t) {
                                    $s.append($('<option>', {
                                        value: t.Name,
                                        text: t.Name
                                    }));
                                });
                                $s.val(savedState.serviceTemplate);
                            }
                        });
                    $('#cb_bill_to_party').val(savedState.billToPartyText);
                    $('#cb_bill_to_party_code').val(savedState.billToPartyCode);
                    $('#cb_forwarder').val(savedState.forwarderText);
                    $('#cb_forwarder_code').val(savedState.forwarderCode);
                    $('#cb_include_country').prop('checked', savedState.includeCountry);
                    $('#cb_place_of_receipt').val(savedState.receiptText);
                    $('#cb_place_of_receipt_code').val(savedState.receiptCode);
                    $('#cb_place_of_receipt_id').val(savedState.receiptId);
                    $('#cb_place_of_delivery').val(savedState.deliveryText);
                    $('#cb_place_of_delivery_code').val(savedState.deliveryCode);
                    $('#cb_place_of_delivery_id').val(savedState.deliveryId);
                    // Show "View Steps" only if there are saved failed steps
                    var _ssStepsCheck = null;
                    try {
                        var _raw = sessionStorage.getItem('cbStepsState_' + quotationId);
                        if (_raw) _ssStepsCheck = JSON.parse(_raw);
                    } catch (e) {}
                    if (_ssStepsCheck && _ssStepsCheck.find(function(s) {
                            return s.status && s.status.toLowerCase() === 'failed';
                        })) {
                        $('#cbViewStepsLink').removeClass('d-none');
                    }
                    return;
                }

                // Fresh open — load templates AND existing booking data in parallel
                modal.data('last-quote-no', quoteNo);
                resetCreateBookingForm();
                setDropdownLoading('#cb_service_template');

                $.when(
                    $.get('{{ route('quotation.booking.service-templates') }}', {
                        quotation_id: quotationId
                    }),
                    $.get('{{ route('quotation.booking.existing-data') }}', {
                        quotation_id: quotationId
                    })
                ).done(function(templatesRes, existingRes) {
                    var templates = templatesRes[0];
                    var existing = existingRes[0];

                    // ── Populate service template dropdown ──────────────────────────────
                    var $s = $('#cb_service_template').empty().append('<option value=""></option>');
                    if (templates.success && templates.templates && templates.templates.length) {
                        $.each(templates.templates, function(i, t) {
                            $s.append($('<option>', {
                                value: t.Name,
                                text: t.Name
                            }));
                        });
                    } else {
                        var msg = templates.code === 'NO_TEMPLATES_CONFIGURED' ?
                            'No templates available for this office.' :
                            (templates.message || 'No templates available for this office.');
                        $s.append('<option value="" disabled>No templates available</option>');
                        $('#cb_service_template').closest('.form-group').append(
                            '<small class="text-danger cb-config-warning mt-1 d-block">' +
                            '<i class="fas fa-exclamation-triangle mr-1"></i>' + msg +
                            '</small>'
                        );
                        $('#submitCreateBookingBtn').prop('disabled', true);
                    }

                    // ── Pre-populate from DB if data exists ─────────────────────────────
                    if (existing.success && existing.data) {
                        var d = existing.data;

                        if (d.service_template) $s.val(d.service_template);

                        if (d.bill_to_party_code) {
                            $('#cb_bill_to_party').val(d.bill_to_party_text);
                            $('#cb_bill_to_party_code').val(d.bill_to_party_code);
                        }

                        if (d.forwarder_code) {
                            $('#cb_forwarder').val(d.forwarder_text);
                            $('#cb_forwarder_code').val(d.forwarder_code);
                        }

                        $('#cb_include_country').prop('checked', !!d.include_country);

                        if (d.receipt_code) {
                            $('#cb_place_of_receipt').val(d.receipt_text);
                            $('#cb_place_of_receipt_code').val(d.receipt_code);
                        }

                        if (d.delivery_code) {
                            $('#cb_place_of_delivery').val(d.delivery_text);
                            $('#cb_place_of_delivery_code').val(d.delivery_code);
                        }
                    }
                }).fail(function() {
                    $('#cb_service_template').empty().append(
                        '<option value="" disabled>Failed to load — try again</option>'
                    );
                });
            });

            $('#createBookingModal').on('hidden.bs.modal', function() {
                $('.location-suggestions').remove();
                if (!$('#cbProgressSuccess').hasClass('d-none')) {
                    // Success — clear everything including saved steps
                    $('#createBookingModal').removeData('saved-form-state').removeData('last-quote-no')
                        .removeData('completed-steps');
                    try {
                        sessionStorage.removeItem('cbStepsState_' + _activeQuotationId);
                    } catch (e) {}
                    try {
                        sessionStorage.removeItem('cbFormState_' + _activeQuotationId);
                    } catch (e) {}
                }
                $('#createBookingForm').removeClass('d-none cb-locked');
                $('#cbProgressPanel').addClass('d-none');
                $('#submitCreateBookingBtn, #cancelCreateBookingBtn').removeClass('d-none');
                $('#cbCloseBtn').addClass('d-none');
                $('#cbEditLink').addClass('d-none');
                $('#cbViewStepsLink').addClass('d-none');
                $('#cbStepList').empty();
                $('#cbProgressError, #cbProgressSuccess').addClass('d-none');
                $('#cb_service_template').prop('disabled', false);
                $('#cb_bill_to_party, #cb_forwarder').val('').prop('disabled', false);
                $('#cb_bill_to_party_code, #cb_forwarder_code').val('');
                $('#cb_include_country, #cb_place_of_receipt, #cb_place_of_delivery').prop('disabled',
                    false);
            });

            $('#cbCloseBtn, #cbXBtn, #cancelCreateBookingBtn').on('click', function(e) {
                e.preventDefault();
                $('#createBookingModal').modal('hide');
            });

            $('#cbEditLink').on('click', function(e) {
                e.preventDefault();
                var savedState = $('#createBookingModal').data('saved-form-state');

                // Show the form panel, hide the progress panel
                $('#createBookingForm').removeClass('cb-locked d-none');
                $('#cbProgressPanel').addClass('d-none');
                $('#submitCreateBookingBtn, #cancelCreateBookingBtn').removeClass('d-none');
                $('#cbCloseBtn').addClass('d-none');
                $(this).addClass('d-none');

                // Re-enable all fields
                $('#cb_service_template').prop('disabled', false);
                $('#cb_bill_to_party, #cb_forwarder').prop('disabled', false);
                $('#cb_include_country, #cb_place_of_receipt, #cb_place_of_delivery').prop('disabled',
                    false);

                // Restore saved values if available
                if (savedState) {
                    $('#cb_bill_to_party').val(savedState.billToPartyText);
                    $('#cb_bill_to_party_code').val(savedState.billToPartyCode);
                    $('#cb_forwarder').val(savedState.forwarderText);
                    $('#cb_forwarder_code').val(savedState.forwarderCode);
                    $('#cb_include_country').prop('checked', savedState.includeCountry);
                    $('#cb_place_of_receipt').val(savedState.receiptText);
                    $('#cb_place_of_receipt_code').val(savedState.receiptCode);
                    $('#cb_place_of_receipt_id').val(savedState.receiptId);
                    $('#cb_place_of_delivery').val(savedState.deliveryText);
                    $('#cb_place_of_delivery_code').val(savedState.deliveryCode);
                    $('#cb_place_of_delivery_id').val(savedState.deliveryId);
                }
                // Show "View Steps" since failed steps still exist
                var _quotationId = $('#createBookingModal').data('quotation-id');
                try {
                    var _raw = sessionStorage.getItem('cbStepsState_' + _quotationId);
                    if (_raw) {
                        var _steps = JSON.parse(_raw);
                        if (_steps.find(function(s) {
                                return s.status && s.status.toLowerCase() === 'failed';
                            })) {
                            $('#cbViewStepsLink').removeClass('d-none');
                        }
                    }
                } catch (e) {}
            });

            $('#cbViewStepsLink').on('click', function(e) {
                e.preventDefault();
                var quotationId = $('#createBookingModal').data('quotation-id');
                var _savedSteps = null;
                try {
                    var _raw = sessionStorage.getItem('cbStepsState_' + quotationId);
                    if (_raw) _savedSteps = JSON.parse(_raw);
                } catch (e) {}

                $('#createBookingForm').addClass('d-none');
                $('#cbProgressPanel').removeClass('d-none');
                $('#submitCreateBookingBtn, #cancelCreateBookingBtn').addClass('d-none');
                $('#cbCloseBtn').removeClass('d-none');
                $('#cbEditLink').removeClass('d-none');
                $(this).addClass('d-none');
                $('#cbProgressError, #cbProgressSuccess').addClass('d-none');
                if (_savedSteps) {
                    $('#createBookingModal').data('completed-steps', _savedSteps);
                    renderBookingSteps(_savedSteps);
                }
            });

            // ── Booking form helpers ──────────────────────────────────────────────────
            function resetCreateBookingForm() {
                $('#cb_service_template').empty().append('<option value=""></option>').prop('disabled', false);
                $('#cb_bill_to_party, #cb_forwarder').val('').prop('disabled', false);
                $('#cb_bill_to_party_code, #cb_forwarder_code').val('');
                $('#cb_include_country').prop('checked', false).prop('disabled', false);
                $('#cb_place_of_receipt, #cb_place_of_delivery').val('').prop('disabled', false);
                $('#cb_place_of_receipt_code, #cb_place_of_receipt_id').val('');
                $('#cb_place_of_delivery_code, #cb_place_of_delivery_id').val('');
                $('.location-suggestions, .party-suggestions').remove();
                $('#createBookingForm').removeClass('cb-locked');
                $('#cbEditLink').addClass('d-none');
                $('#submitCreateBookingBtn').prop('disabled', false);
                $('.cb-config-warning').remove();
                $('#cbViewStepsLink').addClass('d-none');
            }

            function setDropdownLoading(selector) {
                $(selector).empty().append('<option value="" disabled selected>Loading...</option>');
            }

            // ── Location autocomplete ─────────────────────────────────────────────────
            function setupLocationAutocomplete(inputId, codeId, idHiddenId) {
                var debounceTimer = null;

                $('#' + inputId).on('input', function() {
                        var query = $(this).val().trim();
                        clearTimeout(debounceTimer);
                        removeSuggestions(inputId);
                        if (query.length < 2) return;

                        debounceTimer = setTimeout(function() {
                            var modal = $('#createBookingModal');
                            var countryCode = (inputId === 'cb_place_of_receipt') ?
                                modal.data('origin-country') :
                                modal.data('destination-country');

                            $.get('{{ route('quotation.booking.locations') }}', {
                                    filter: query,
                                    include_country: $('#cb_include_country').is(':checked') ? 1 :
                                        0,
                                    country_code: $('#cb_include_country').is(':checked') ? (
                                        countryCode || '') : ''
                                })
                                .done(function(res) {
                                    removeSuggestions(inputId);
                                    if (!res.success || !res.locations || !res.locations.length)
                                        return;

                                    var $input = $('#' + inputId);
                                    var $list = $('<ul>', {
                                        class: 'location-suggestions list-group',
                                        css: {
                                            position: 'absolute',
                                            zIndex: 9999,
                                            width: $input.outerWidth(),
                                            maxHeight: '200px',
                                            overflowY: 'auto',
                                            border: '1px solid #ced4da',
                                            borderRadius: '0 0 4px 4px',
                                            boxShadow: '0 4px 8px rgba(0,0,0,0.1)',
                                            background: '#fff',
                                            marginTop: '-1px'
                                        }
                                    }).on('mousedown', function(e) {
                                        e.preventDefault();
                                    });

                                    $.each(res.locations, function(i, loc) {
                                        $('<li>', {
                                                class: 'list-group-item list-group-item-action',
                                                css: {
                                                    cursor: 'pointer',
                                                    padding: '0.4rem 0.75rem',
                                                    fontSize: '0.875rem'
                                                },
                                                text: loc.LocationName + (loc
                                                    .LocationType ? ' (' + loc
                                                    .LocationType + ')' : '')
                                            })
                                            .on('mousedown', function(e) {
                                                e.preventDefault();
                                                $('#' + inputId).val(
                                                    loc.LocationName + (loc
                                                        .LocationType ? ' (' + loc
                                                        .LocationType + ')' : '')
                                                );
                                                $('#' + codeId).val(loc.LocationCode);
                                                $('#' + idHiddenId).val(loc.LocationID);
                                                removeSuggestions(inputId);
                                            })
                                            .appendTo($list);
                                    });

                                    $input.closest('.form-group').css('position', 'relative')
                                        .append($list);
                                })
                                .fail(function(xhr) {
                                    console.error('Location search failed:', xhr);
                                });
                        }, 300);
                    })
                    .on('blur', function() {
                        setTimeout(function() {
                            removeSuggestions(inputId);
                        }, 200);
                    });
            }

            // ── Party search autocomplete (Bill To Party & Forwarder) ─────────────────
            function setupPartyAutocomplete(inputId, codeId, fetchUrl, teamId) {
                var debounceTimer = null;

                $('#' + inputId).on('input', function() {
                        var query = $(this).val().trim();
                        clearTimeout(debounceTimer);
                        removePartySuggestions(inputId);
                        if (query.length < 3) return;

                        debounceTimer = setTimeout(function() {
                            $.get(fetchUrl, {
                                    team_id: (typeof teamId === 'function') ? teamId() : teamId,
                                    filter: query
                                })
                                .done(function(res) {
                                    removePartySuggestions(inputId);
                                    if (!res.success || !res.parties || !res.parties.length) return;

                                    var $input = $('#' + inputId);
                                    var $list = $('<ul>', {
                                        class: 'party-suggestions list-group',
                                        css: {
                                            position: 'absolute',
                                            zIndex: 9999,
                                            width: $input.outerWidth(),
                                            maxHeight: '200px',
                                            overflowY: 'auto',
                                            border: '1px solid #ced4da',
                                            borderRadius: '0 0 4px 4px',
                                            boxShadow: '0 4px 8px rgba(0,0,0,0.1)',
                                            background: '#fff',
                                            marginTop: '-1px'
                                        }
                                    }).on('mousedown', function(e) {
                                        e.preventDefault();
                                    });

                                    $.each(res.parties, function(i, p) {
                                        $('<li>', {
                                                class: 'list-group-item list-group-item-action',
                                                css: {
                                                    cursor: 'pointer',
                                                    padding: '0.4rem 0.75rem',
                                                    fontSize: '0.875rem'
                                                },
                                                text: p.PartyCode + ' — ' + p.PartyName
                                            })
                                            .on('mousedown', function(e) {
                                                e.preventDefault();
                                                $('#' + inputId).val(p.PartyCode +
                                                    ' — ' + p.PartyName);
                                                $('#' + codeId).val(p.PartyCode);
                                                removePartySuggestions(inputId);
                                            })
                                            .appendTo($list);
                                    });

                                    $input.closest('.form-group').css('position', 'relative')
                                        .append($list);
                                })
                                .fail(function(xhr) {
                                    console.error('Party search failed:', xhr);
                                });
                        }, 300);
                    })
                    .on('blur', function() {
                        setTimeout(function() {
                            removePartySuggestions(inputId);
                        }, 200);
                    });
            }

            function removePartySuggestions(inputId) {
                $('#' + inputId).closest('.form-group').find('.party-suggestions').remove();
            }

            function removeSuggestions(inputId) {
                $('#' + inputId).closest('.form-group').find('.location-suggestions').remove();
            }

            setupLocationAutocomplete('cb_place_of_receipt', 'cb_place_of_receipt_code', 'cb_place_of_receipt_id');
            setupLocationAutocomplete('cb_place_of_delivery', 'cb_place_of_delivery_code',
                'cb_place_of_delivery_id');


            setupPartyAutocomplete(
                'cb_bill_to_party',
                'cb_bill_to_party_code',
                '{{ route('quotation.booking.bill-to-parties') }}',
                function() {
                    return $('#createBookingModal').data('team-id');
                }
            );
            setupPartyAutocomplete(
                'cb_forwarder',
                'cb_forwarder_code',
                '{{ route('quotation.booking.forwarders') }}',
                function() {
                    return $('#createBookingModal').data('team-id');
                }
            );

            $('#cb_include_country').on('change', function() {
                // Only clear the hidden code/id fields so a re-search is triggered on next input.
                // Do NOT clear the visible text inputs — the user typed those and should keep them.
                // Do NOT clear if the fields are already empty (nothing to invalidate).
                $('#cb_place_of_receipt_code, #cb_place_of_receipt_id').val('');
                $('#cb_place_of_delivery_code, #cb_place_of_delivery_id').val('');

                // Remove any open suggestion dropdowns since the filter has changed
                removeSuggestions('cb_place_of_receipt');
                removeSuggestions('cb_place_of_delivery');

                // If there's already text in either field, retrigger the search immediately
                // so the dropdown reflects the new include_country state
                var receiptVal = $('#cb_place_of_receipt').val().trim();
                var deliveryVal = $('#cb_place_of_delivery').val().trim();
                if (receiptVal.length >= 2) {
                    $('#cb_place_of_receipt').trigger('input');
                }
                if (deliveryVal.length >= 2) {
                    $('#cb_place_of_delivery').trigger('input');
                }
            });

            // ── Booking step renderer ─────────────────────────────────────────────────
            function renderBookingSteps(completedSteps, activeStep) {
                var $list = $('#cbStepList').empty();
                var quotationId = $('#createBookingModal').data('quotation-id');
                var bookingNo = $('#createBookingModal').data('booking-no');

                BOOKING_STEPS.forEach(function(def) {
                    var done = completedSteps.find(function(s) {
                        return String(s.step) === String(def.step);
                    });
                    var iconClass, badgeClass, statusText, remarkHtml = '',
                        retriggerBtn = '';
                    var isActive = (activeStep !== null && activeStep !== undefined && String(def.step) ===
                        String(activeStep));

                    if (isActive) {
                        iconClass = 'fas fa-spinner fa-spin text-primary';
                        badgeClass = 'badge-primary';
                        statusText = 'Processing...';
                    } else if (!done) {
                        iconClass = 'fas fa-circle text-secondary';
                        badgeClass = 'badge-secondary';
                        statusText = 'Pending';
                    } else if (done.status === 'Success') {
                        iconClass = 'fas fa-check-circle text-success';
                        badgeClass = 'badge-success';
                        statusText = 'Success';
                        if (done.remark)
                            remarkHtml = '<small class="text-muted d-block mt-1">' + done.remark +
                            '</small>';
                        if ([2, '2b', 3, 4].indexOf(def.step) !== -1) {
                            retriggerBtn =
                                '<button class="btn btn-xs btn-outline-secondary btn-retrigger ml-2"' +
                                ' data-quotation-id="' + quotationId + '"' +
                                ' data-booking-no="' + bookingNo + '"' +
                                ' data-step="' + def.step + '"' +
                                ' title="Re-run this step">' +
                                '<i class="fas fa-redo-alt mr-1"></i>Re-run' +
                                '</button>';
                        }
                    } else if (done.status === 'Skipped') {
                        iconClass = 'fas fa-minus-circle text-secondary';
                        badgeClass = 'badge-secondary';
                        statusText = 'Skipped';
                    } else {
                        // Failed
                        iconClass = 'fas fa-times-circle text-danger';
                        badgeClass = 'badge-danger';
                        statusText = 'Failed';
                        if (done.remark)
                            remarkHtml = '<small class="text-danger d-block mt-1">' + done.remark +
                            '</small>';

                        // Only show retrigger on steps that the retrigger endpoint handles (2, 3, 4)
                        if ([2, '2b', 3, 4].indexOf(def.step) !== -1) {
                            retriggerBtn =
                                '<button class="btn btn-xs btn-outline-warning btn-retrigger ml-2"' +
                                ' data-quotation-id="' + quotationId + '"' +
                                ' data-booking-no="' + bookingNo + '"' +
                                ' data-step="' + def.step + '"' +
                                ' title="Retrigger this step">' +
                                '<i class="fas fa-redo-alt mr-1"></i>Retry' +
                                '</button>';
                        }
                    }

                    $list.append(
                        '<div class="list-group-item py-2">' +
                        '<div class="d-flex align-items-center justify-content-between">' +
                        '<div><i class="' + iconClass + ' mr-2"></i>' +
                        '<strong>Step ' + def.step + ':</strong> ' + def.label +
                        remarkHtml + '</div>' +
                        '<div class="d-flex align-items-center gap-2">' +
                        '<span class="badge ' + badgeClass + '">' + statusText + '</span>' +
                        retriggerBtn +
                        '</div>' +
                        '</div></div>'
                    );
                });
            }

            // ── Retrigger failed booking step ─────────────────────────────────────────
            $('#cbStepList').on('click', '.btn-retrigger', function() {
                var $btn = $(this);
                var quotationId = $btn.data('quotation-id');
                var bookingNo = $btn.data('booking-no');
                var failedStep = $btn.data('step');

                // force_step2 = 1 when the failed step is 2b or 3.
                // Both indicate the booking is missing in SCM (orphaned SCMBookingID).
                // The backend guard ensures this is ignored if the booking actually exists in SCM.
                var forceStep2 = (['2', '2b', 3].indexOf(String(failedStep)) !== -1) ? 1 : 0;

                $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i>Retrying...');

                $.ajax({
                    url: '{{ route('quotation.retrigger') }}',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        quotation_id: quotationId,
                        booking_no: bookingNo,
                        force_step2: forceStep2,
                    },
                    success: function(res) {
                        var previousSteps = $('#createBookingModal').data('completed-steps') ||
                            [];
                        var newSteps = res.steps || [];

                        // Merge: new steps override previous ones for the same step number
                        var merged = previousSteps.filter(function(ps) {
                            return !newSteps.find(function(ns) {
                                return ns.step === ps.step;
                            });
                        }).concat(newSteps);

                        $('#createBookingModal').data('completed-steps', merged);
                        renderBookingSteps(merged);
                        if (res.success) {
                            $('#cbProgressError').addClass('d-none');
                            $('#cbProgressSuccess').removeClass('d-none');
                            $('#cbEditLink').addClass('d-none');
                            table.ajax.reload(null, false);
                            var _qId = $('#createBookingModal').data('quotation-id');
                            try {
                                sessionStorage.removeItem('cbStepsState_' + _qId);
                            } catch (e) {}

                        } else {
                            var failedStep = (res.steps || []).find(function(s) {
                                return s.status === 'Failed';
                            });
                            $('#cbProgressError')
                                .html('<strong>' + (res.failed_label || 'Step') +
                                    ' failed:</strong> ' +
                                    (failedStep ? failedStep.remark : 'Unknown error'))
                                .removeClass('d-none');
                            var _qId = $('#createBookingModal').data('quotation-id');
                            try {
                                sessionStorage.setItem('cbStepsState_' + _qId, JSON.stringify(
                                    merged));
                            } catch (e) {}
                        }
                    },
                    error: function(xhr) {
                        var msg = xhr.responseJSON?.message ||
                            'Retrigger failed. Please try again.';
                        $('#cbProgressError').html(msg).removeClass('d-none');
                        $btn.prop('disabled', false).html(
                            '<i class="fas fa-redo-alt mr-1"></i>Retry');
                    }
                });
            });

            // ── Submit Create Booking ─────────────────────────────────────────────────
            $('#submitCreateBookingBtn').on('click', function() {
                var $btn = $(this);
                var quotationId = $('#createBookingModal').data('quotation-id');
                var bookingNo = $('#createBookingModal').data('booking-no');

                // ── Guard: if failed steps already exist, show steps panel ──────────
                var _savedSteps = null;
                if (quotationId) {
                    try {
                        var _raw = sessionStorage.getItem('cbStepsState_' + quotationId);
                        if (_raw) _savedSteps = JSON.parse(_raw);
                    } catch (e) {}
                }
                if (_savedSteps && _savedSteps.find(function(s) {
                        return s.status && s.status.toLowerCase() === 'failed';
                    })) {
                    $('#createBookingModal').data('completed-steps', _savedSteps);
                    $('#createBookingForm').addClass('d-none');
                    $('#cbProgressPanel').removeClass('d-none');
                    $('#submitCreateBookingBtn, #cancelCreateBookingBtn').addClass('d-none');
                    $('#cbCloseBtn').removeClass('d-none');
                    $('#cbEditLink').removeClass('d-none');
                    $('#cbViewStepsLink').addClass('d-none');
                    $('#cbProgressError, #cbProgressSuccess').addClass('d-none');
                    renderBookingSteps(_savedSteps);
                    return;
                }

                // ── Validate form fields ──────────────────────────────────────────────
                var serviceTemplate = $('#cb_service_template').val();
                var billToParty = $('#cb_bill_to_party_code').val();
                var forwarder = $('#cb_forwarder_code').val();
                var placeOfReceipt = $('#cb_place_of_receipt_code').val();
                var placeOfDelivery = $('#cb_place_of_delivery_code').val();
                var includeCountry = $('#cb_include_country').is(':checked') ? 1 : 0;

                var errors = [];
                if (!serviceTemplate) errors.push('Service Template');
                if (!billToParty) errors.push('Bill To Party');
                if (!forwarder) errors.push('Forwarder');
                if (!placeOfReceipt) errors.push('Place of Receipt');
                if (!placeOfDelivery) errors.push('Place of Delivery');

                if (errors.length) {
                    alert('Please complete the following required fields:\n• ' + errors.join('\n• '));
                    return;
                }

                // ── Save form state ───────────────────────────────────────────────────
                var _formState = {
                    serviceTemplate: serviceTemplate,
                    billToPartyText: $('#cb_bill_to_party').val(),
                    billToPartyCode: billToParty,
                    forwarderText: $('#cb_forwarder').val(),
                    forwarderCode: forwarder,
                    includeCountry: $('#cb_include_country').is(':checked'),
                    receiptText: $('#cb_place_of_receipt').val(),
                    receiptCode: placeOfReceipt,
                    receiptId: $('#cb_place_of_receipt_id').val(),
                    deliveryText: $('#cb_place_of_delivery').val(),
                    deliveryCode: placeOfDelivery,
                    deliveryId: $('#cb_place_of_delivery_id').val(),
                };
                $('#createBookingModal').data('saved-form-state', _formState);
                try {
                    sessionStorage.setItem('cbFormState_' + quotationId, JSON.stringify(_formState));
                } catch (e) {}

                // ── Switch to progress panel — all steps Pending ──────────────────────
                $btn.prop('disabled', true);
                $('#createBookingForm').addClass('d-none');
                $('#cbProgressPanel').removeClass('d-none');
                $('#submitCreateBookingBtn, #cancelCreateBookingBtn').addClass('d-none');
                $('#cbEditLink').addClass('d-none');
                $('#cbProgressError, #cbProgressSuccess').addClass('d-none');

                // Show all steps as Pending initially — no active step yet
                renderBookingSteps([], null);

                // ── Shared AJAX payload builder ───────────────────────────────────────
                var basePayload = {
                    _token: '{{ csrf_token() }}',
                    id: quotationId,
                    booking_no: bookingNo,
                    service_template: serviceTemplate,
                    bill_to_party: billToParty,
                    forwarder: forwarder,
                    include_country: includeCountry,
                    place_of_receipt: placeOfReceipt,
                    place_of_delivery: placeOfDelivery,
                };

                // completedSteps accumulates results as each step finishes
                var completedSteps = [];

                // ── runStep: fires one AJAX call, updates UI, returns a Promise ───────
                // stepNo    — matches BOOKING_STEPS[].step  (0, 2, '2b', 3, 4)
                // url       — the route for this step
                // extraData — any additional POST fields for this step only
                function runStep(stepNo, url, extraData) {
                    // Mark this step as active (spinner) before the request fires
                    renderBookingSteps(completedSteps, stepNo);

                    var payload = $.extend({}, basePayload, extraData || {});

                    return $.ajax({
                        url: url,
                        type: 'POST',
                        data: payload,
                    });
                }

                // ── Pipeline — each .then() runs after the previous step completes ────
                // Step 0 — Save Booking Info
                runStep(0, '{{ route('quotation.booking.step.save-info') }}')
                    .then(function(res) {
                        completedSteps.push({
                            step: 0,
                            label: 'Save Booking Info',
                            status: res.status,
                            remark: res.remark || ''
                        });
                        renderBookingSteps(completedSteps, null);

                        if (!res.success) {
                            return $.Deferred().reject(res, 0, 'Save Booking Info');
                        }

                        // Step 2 — Booking XML
                        return runStep(2, '{{ route('quotation.booking.step.xml') }}');
                    })
                    .then(function(res) {
                        completedSteps.push({
                            step: 2,
                            label: 'Booking XML',
                            status: res.status,
                            remark: res.remark || ''
                        });
                        renderBookingSteps(completedSteps, null);

                        if (!res.success) {
                            return $.Deferred().reject(res, 2, 'Booking XML');
                        }

                        // Step 2b — SCM Push
                        return runStep('2b', '{{ route('quotation.booking.step.scm-push') }}');
                    })
                    .then(function(res) {
                        completedSteps.push({
                            step: '2b',
                            label: 'SCM Push',
                            status: res.status,
                            remark: res.remark || ''
                        });
                        renderBookingSteps(completedSteps, null);

                        if (!res.success) {
                            return $.Deferred().reject(res, '2b', 'SCM Push');
                        }

                        // Step 3 — Charge Transfer
                        return runStep(3, '{{ route('quotation.booking.step.charges') }}');
                    })
                    .then(function(res) {
                        completedSteps.push({
                            step: 3,
                            label: 'Charge Transfer',
                            status: res.status,
                            remark: res.remark || ''
                        });
                        renderBookingSteps(completedSteps, null);

                        if (!res.success) {
                            return $.Deferred().reject(res, 3, 'Charge Transfer');
                        }

                        // Step 4 — Document Transfer
                        return runStep(4, '{{ route('quotation.booking.step.documents') }}');
                    })
                    .then(function(res) {
                        // ── All steps succeeded ───────────────────────────────────────────
                        completedSteps.push({
                            step: 4,
                            label: 'Document Transfer',
                            status: res.status,
                            remark: res.remark || ''
                        });
                        renderBookingSteps(completedSteps, null);

                        $('#createBookingModal').data('completed-steps', completedSteps);

                        var msg =
                            '<i class="fas fa-check-circle mr-2"></i>All steps completed. Booking has been created in SCM.';
                        $('#cbProgressSuccess').html(msg).removeClass('d-none');
                        $('#cbProgressError').addClass('d-none');
                        $('#cbEditLink').addClass('d-none');
                        $('#cbViewStepsLink').addClass('d-none');

                        $('#createBookingModal').removeData('saved-form-state').removeData(
                            'last-quote-no');
                        try {
                            sessionStorage.removeItem('cbFormState_' + quotationId);
                            sessionStorage.removeItem('cbStepsState_' + quotationId);
                        } catch (e) {}

                        table.ajax.reload(null, false);
                    })
                    .fail(function(res, failedStepNo, failedLabel) {
                        // ── A step failed — mark remaining steps as Skipped ──────────────
                        // failedStepNo and failedLabel come from $.Deferred().reject() above
                        // res here may be a jqXHR object (network error) or our response object
                        var stepOrder = [0, 2, '2b', 3, 4];
                        var failedIdx = stepOrder.indexOf(failedStepNo);

                        // Safety: if strict indexOf missed (type mismatch), retry with string comparison
                        if (failedIdx === -1) {
                            failedIdx = stepOrder.map(String).indexOf(String(failedStepNo));
                        }

                        stepOrder.forEach(function(sNo, idx) {
                            if (idx > failedIdx) {
                                // Guard: don't push a Skipped entry if this step was already recorded
                                var alreadyRecorded = completedSteps.find(function(s) {
                                    return String(s.step) === String(sNo);
                                });
                                if (!alreadyRecorded) {
                                    var def = BOOKING_STEPS.find(function(d) {
                                        return String(d.step) === String(sNo);
                                    });
                                    completedSteps.push({
                                        step: sNo,
                                        label: def ? def.label : 'Step ' + sNo,
                                        status: 'Skipped',
                                        remark: 'Skipped due to previous step failure',
                                    });
                                }
                            }
                        });
                        $('#createBookingModal').data('completed-steps', completedSteps);
                        renderBookingSteps(completedSteps, null);

                        // Determine error message
                        var errorMsg = 'Unknown error';
                        if (res && res.remark) errorMsg = res.remark;
                        else if (res && res.responseJSON) {
                            errorMsg = res.responseJSON.message || errorMsg;
                        } else if (res && res.statusText) {
                            errorMsg = 'Network error: ' + res.statusText;
                        }

                        $('#cbProgressError')
                            .html('<strong>' + (failedLabel || 'Step') + ' failed:</strong> ' +
                                errorMsg)
                            .removeClass('d-none');
                        $('#cbEditLink').removeClass('d-none');

                        try {
                            sessionStorage.setItem('cbStepsState_' + quotationId, JSON.stringify(
                                completedSteps));
                        } catch (e) {}

                        table.ajax.reload(null, false);
                    })
                    .always(function() {
                        $btn.prop('disabled', false).html('Create');
                        $('#cbCloseBtn').removeClass('d-none');
                    });
            });

            // ── CRM Sync modal ────────────────────────────────────────────────────────
            $('#btnOpenCrmSync').on('click', function() {
                $('#crmSyncResult').html('');
                $('#crmSyncProgress').addClass('d-none');
                $('#crmDryRun').prop('checked', false);
                $('#crmLimit, #crmBooking').val('');
                $('#crmSyncModal').modal('show');
            });

            $(document).on('click', '#crmSyncModal .close, #crmSyncModal .modal-footer .btn-secondary',
                function(e) {
                    e.preventDefault();
                    $('#crmSyncModal').modal('hide');
                }
            );
            $(document).on('click',
                '#quotationReportModal .close, #quotationReportModal .modal-footer .btn-secondary',
                function(e) {
                    e.preventDefault();
                    $('#quotationReportModal').modal('hide');
                }
            );

            $('#btnRunCrmSync').on('click', function() {
                var btn = $(this);
                var dryRun = $('#crmDryRun').is(':checked') ? 1 : 0;
                var limit = $('#crmLimit').val().trim();
                var booking = $('#crmBooking').val().trim();

                btn.prop('disabled', true);
                $('#crmSyncResult').html('');

                // ── Live progress steps ───────────────────────────────────────────────
                $('#crmSyncProgress').removeClass('d-none').html(
                    '<div class="list-group" id="crmStepList">' +
                    '<div class="list-group-item py-2" id="crm-step-connect">' +
                    '<i class="fas fa-spinner fa-spin text-primary mr-2"></i>' +
                    '<strong>Connecting to CRM...</strong>' +
                    '</div>' +
                    '<div class="list-group-item py-2 text-muted" id="crm-step-fetch">' +
                    '<i class="fas fa-circle text-secondary mr-2"></i>' +
                    'Fetching records from CRM (last 24 hrs)' +
                    '</div>' +
                    '<div class="list-group-item py-2 text-muted" id="crm-step-sync">' +
                    '<i class="fas fa-circle text-secondary mr-2"></i>' +
                    'Syncing to local database' +
                    '</div>' +
                    '<div class="list-group-item py-2 text-muted" id="crm-step-verify">' +
                    '<i class="fas fa-circle text-secondary mr-2"></i>' +
                    'Verifying results' +
                    '</div>' +
                    '</div>'
                );

                // Simulate step progression while the request is in-flight
                var stepDelay = 1800;
                setTimeout(function() {
                    $('#crm-step-connect')
                        .html(
                            '<i class="fas fa-check-circle text-success mr-2"></i><strong>Connected to CRM</strong>'
                        );
                    $('#crm-step-fetch')
                        .removeClass('text-muted')
                        .html(
                            '<i class="fas fa-spinner fa-spin text-primary mr-2"></i><strong>Fetching records from CRM (last 24 hrs)...</strong>'
                        );
                }, stepDelay);

                setTimeout(function() {
                    $('#crm-step-fetch')
                        .html(
                            '<i class="fas fa-check-circle text-success mr-2"></i><strong>Records fetched</strong>'
                        );
                    $('#crm-step-sync')
                        .removeClass('text-muted')
                        .html(
                            '<i class="fas fa-spinner fa-spin text-primary mr-2"></i><strong>Syncing to local database...</strong>'
                        );
                }, stepDelay * 2);

                setTimeout(function() {
                    if ($('#crm-step-verify').hasClass('text-muted')) {
                        $('#crm-step-sync')
                            .html(
                                '<i class="fas fa-check-circle text-success mr-2"></i><strong>Sync complete</strong>'
                            );
                        $('#crm-step-verify')
                            .removeClass('text-muted')
                            .html(
                                '<i class="fas fa-spinner fa-spin text-primary mr-2"></i><strong>Verifying results...</strong>'
                            );
                    }
                }, stepDelay * 3);

                $.ajax({
                    url: '{{ route('crm.sync.run') }}',
                    type: 'POST',
                    timeout: 120000,
                    data: {
                        _token: '{{ csrf_token() }}',
                        dry_run: dryRun,
                        limit: limit || null,
                        booking: booking || null
                    },
                    success: function(res) {
                        // Mark all steps done
                        $('#crmStepList .list-group-item').each(function() {
                            if ($(this).find('.fa-spinner').length) {
                                $(this).html(
                                    '<i class="fas fa-check-circle text-success mr-2"></i>' +
                                    '<strong>' + $(this).text().trim() + '</strong>'
                                );
                            }
                        });

                        $('#crmSyncProgress').addClass('d-none');

                        var s = res.summary;
                        var records = res.records || [];
                        var dryTag = res.dry_run ?
                            ' <span class="badge badge-warning ml-1">DRY RUN</span>' :
                            '';

                        // ── message-driven banner ─────────────────────────────────────
                        var html = '';

                        if (res.message === 'no_records') {
                            html =
                                '<div class="alert alert-warning mb-3 py-2">' +
                                '<i class="fas fa-exclamation-circle mr-2"></i>' +
                                '<strong>No records found.' + dryTag + '</strong> ' +
                                'The CRM returned no records in the last 24 hrs for this office. ' +
                                'If you expect records, update them in CRM first then run sync again.' +
                                '</div>';
                            $('#crmSyncResult').html(html);
                            return;
                        }

                        if (res.message === 'up_to_date') {
                            html =
                                '<div class="alert alert-success mb-3 py-2">' +
                                '<i class="fas fa-check-double mr-2"></i>' +
                                '<strong>Everything is up to date.' + dryTag + '</strong> ' +
                                s.total +
                                ' record(s) checked — all already match CRM, nothing to insert or update.' +
                                '</div>' +
                                '<div class="d-flex flex-wrap" style="gap:0.5rem;">' +
                                '<span class="badge badge-secondary px-3 py-2" style="font-size:0.8rem;">' +
                                '<i class="fas fa-database mr-1"></i> Checked: ' + s.total +
                                '</span>' +
                                '<span class="badge badge-light border px-3 py-2" style="font-size:0.8rem;">' +
                                '<i class="fas fa-forward mr-1"></i> Skipped: ' + s.skipped +
                                '</span>' +
                                '</div>';
                            $('#crmSyncResult').html(html);
                            return;
                        }

                        // all_failed or changes_made — show full summary + table
                        var alertClass, icon, label;
                        if (res.message === 'all_failed') {
                            alertClass = 'alert-danger';
                            icon = 'times-circle';
                            label = 'Sync failed — no changes were written';
                        } else if (res.success) {
                            alertClass = 'alert-success';
                            icon = 'check-circle';
                            label = 'Sync completed successfully';
                        } else {
                            alertClass = 'alert-warning';
                            icon = 'exclamation-circle';
                            label = 'Sync completed with some failures';
                        }

                        html +=
                            '<div class="alert ' + alertClass + ' mb-3 py-2">' +
                            '<i class="fas fa-' + icon + ' mr-2"></i>' +
                            '<strong>' + label + '.' + dryTag + '</strong>' +
                            '</div>';

                        // ── Summary badges ────────────────────────────────────────────
                        html +=
                            '<div class="d-flex flex-wrap mb-3" style="gap:0.5rem;">' +
                            '<span class="badge badge-secondary px-3 py-2" style="font-size:0.8rem;">' +
                            '<i class="fas fa-database mr-1"></i> Total: ' + s.total +
                            '</span>' +
                            '<span class="badge badge-success px-3 py-2" style="font-size:0.8rem;">' +
                            '<i class="fas fa-plus-circle mr-1"></i> Inserted: ' + s.inserted +
                            '</span>' +
                            '<span class="badge badge-info px-3 py-2" style="font-size:0.8rem;">' +
                            '<i class="fas fa-pencil-alt mr-1"></i> Updated: ' + s.updated +
                            '</span>' +
                            '<span class="badge badge-light border px-3 py-2" style="font-size:0.8rem;">' +
                            '<i class="fas fa-forward mr-1"></i> Skipped: ' + s.skipped +
                            '</span>' +
                            '<span class="badge ' + (s.failed > 0 ? 'badge-danger' :
                                'badge-light border') +
                            ' px-3 py-2" style="font-size:0.8rem;">' +
                            '<i class="fas fa-times-circle mr-1"></i> Failed: ' + s.failed +
                            '</span>' +
                            '</div>';

                        function statusBadge(status) {
                            var cfg = {
                                inserted: {
                                    cls: 'badge-success',
                                    icon: 'plus-circle',
                                    label: 'Inserted'
                                },
                                updated: {
                                    cls: 'badge-info',
                                    icon: 'pencil-alt',
                                    label: 'Updated'
                                },
                                skipped: {
                                    cls: 'badge-secondary',
                                    icon: 'forward',
                                    label: 'Skipped'
                                },
                                failed: {
                                    cls: 'badge-danger',
                                    icon: 'times-circle',
                                    label: 'Failed'
                                },
                            };
                            var c = cfg[status];
                            if (!c) return '<span class="badge badge-light">' + esc(status) +
                                '</span>';
                            return '<span class="badge ' + c.cls + '">' +
                                '<i class="fas fa-' + c.icon + ' mr-1"></i>' + c.label +
                                '</span>';
                        }

                        if (records.length) {
                            html +=
                                '<div style="max-height:300px;overflow-y:auto;">' +
                                '<table class="table table-sm table-bordered table-striped mb-0" style="font-size:0.8rem;">' +
                                '<thead class="thead-light"><tr>' +
                                '<th style="width:36px;" class="text-center">#</th>' +
                                '<th style="width:110px;">Booking No</th>' +
                                '<th>Company</th>' +
                                '<th style="width:100px;" class="text-center">Status</th>' +
                                '<th>Error</th>' +
                                '</tr></thead><tbody>';

                            records.forEach(function(r, idx) {
                                var rowClass = r.status === 'failed' ?
                                    'class="table-danger"' : '';
                                var company = esc(String(r.company || '-'));
                                var errorCell = r.error ? esc(String(r.error)) : '';
                                html +=
                                    '<tr ' + rowClass + '>' +
                                    '<td class="text-center text-muted">' + (idx + 1) +
                                    '</td>' +
                                    '<td class="text-nowrap font-weight-bold">' + esc(
                                        String(r.bookingno || '-')) + '</td>' +
                                    '<td>' + company + '</td>' +
                                    '<td class="text-center">' + statusBadge(r.status) +
                                    '</td>' +
                                    '<td class="text-danger small">' + errorCell +
                                    '</td>' +
                                    '</tr>';
                            });

                            html += '</tbody></table></div>';
                        }

                        if (!res.dry_run && (s.inserted > 0 || s.updated > 0)) {
                            table.ajax.reload(null, false);
                        }

                        $('#crmSyncResult').html(html);
                    },
                    error: function(xhr) {
                        $('#crmSyncProgress').addClass('d-none');
                        var msg = 'Request failed: ';
                        if (xhr.status === 0) {
                            msg += 'Connection lost or request timed out.';
                        } else if (xhr.status >= 500) {
                            msg += 'Server error (' + xhr.status + '). Check Laravel logs.';
                        } else {
                            msg += (xhr.responseJSON?.message || xhr.statusText);
                        }
                        $('#crmSyncResult').html(
                            '<div class="alert alert-danger">' +
                            '<i class="fas fa-times-circle mr-2"></i>' + msg +
                            '</div>'
                        );
                    },
                    complete: function() {
                        btn.prop('disabled', false);
                        $('#crmSyncProgress').addClass('d-none');
                    }
                });
            });

            function esc(str) {
                if (!str) return '-';
                return String(str)
                    .replace(/&/g, '&amp;').replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;').replace(/"/g, '&quot;');
            }

        }); // end document.ready
    </script>
@stop
