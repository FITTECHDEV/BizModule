@extends('adminlte::page')

@section('title', 'Quotation Management')

@section('content_header')
    <h1>QUOTATION</h1>
@stop

@section('content')
    <div class="container-fluid">

        {{-- Statistics Cards --}}
        <div class="row mb-3">
            {{-- 1. In Progress --}}
            <div class="col-6 col-md-4 col-lg mb-2">
                <div class="small-box bg-info card-filter-trigger compact-card" data-filter="in_progress"
                    title="Click to view in progress records">
                    <div class="inner">
                        <h3 id="stat-in-progress">{{ $stats['in_progress'] }}</h3>
                        <p>In Progress</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-hourglass-half"></i>
                    </div>
                </div>
            </div>

            {{-- 2. Won --}}
            <div class="col-6 col-md-4 col-lg mb-2">
                <div class="small-box bg-success card-filter-trigger compact-card" data-filter="won"
                    title="Click to view won records">
                    <div class="inner">
                        <h3 id="stat-won">{{ $stats['won'] }}</h3>
                        <p>Won</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
            </div>

            {{-- 3. Lost --}}
            <div class="col-6 col-md-4 col-lg mb-2">
                <div class="small-box bg-danger card-filter-trigger compact-card" data-filter="lost"
                    title="Click to view lost records">
                    <div class="inner">
                        <h3 id="stat-lost">{{ $stats['lost'] }}</h3>
                        <p>Lost</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-times-circle"></i>
                    </div>
                </div>
            </div>

            {{-- 4. Canceled --}}
            <div class="col-6 col-md-4 col-lg mb-2">
                <div class="small-box bg-warning card-filter-trigger compact-card" data-filter="canceled"
                    title="Click to view canceled records">
                    <div class="inner">
                        <h3 id="stat-canceled">{{ $stats['canceled'] }}</h3>
                        <p>Canceled</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-ban"></i>
                    </div>
                </div>
            </div>

            {{-- 5. Failed Progress --}}
            <div class="col-6 col-md-4 col-lg mb-2">
                <div class="small-box bg-secondary card-filter-trigger compact-card" data-filter="Failed Progress"
                    title="Click to view failed progress records">
                    <div class="inner">
                        <h3 id="stat-failed-progress">{{ $stats['failed_progress'] }}</h3>
                        <p>Failed Progress</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-exclamation-circle"></i>
                    </div>
                </div>
            </div>

            {{-- 6. Failed Int. --}}
            <div class="col-6 col-md-4 col-lg mb-2">
                <div class="small-box bg-danger card-filter-trigger compact-card" data-filter="failed_int"
                    title="Click to view failed integration records">
                    <div class="inner">
                        <h3 id="stat-failed-int">{{ $stats['failed_int'] }}</h3>
                        <p>Failed Int.</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                </div>
            </div>

            {{-- 7. Total --}}
            <div class="col-6 col-md-4 col-lg mb-2">
                <div class="small-box bg-primary compact-card" title="Total records">
                    <div class="inner">
                        <h3 id="stat-total">{{ $stats['total'] }}</h3>
                        <p>Total</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-database"></i>
                    </div>
                </div>
            </div>
        </div>

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
                                <option value="{{ $office }}" {{ $office === $defaultOffice ? 'selected' : '' }}>
                                    {{ $office }}
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

                    {{-- CRM Sync Button --}}
                    <div class="col-12 col-sm-6 col-md-auto order-6">
                        <button type="button" class="btn btn-outline-primary btn-sm w-100 w-md-auto" id="btnOpenCrmSync"
                            title="Sync records from CRM">
                            <i class="fas fa-sync-alt me-1"></i> CRM Sync
                        </button>
                    </div>
                </div>

                {{-- DataTable --}}
                <div class="table-responsive-wrapper">
                    <table id="quotationTable" class="table table-bordered table-striped table-hover w-100 dtr-inline">
                        <thead>
                            <tr>
                                <th data-priority="1">Date</th>
                                <th data-priority="2">Office</th>
                                <th data-priority="3">Client Name</th>
                                <th data-priority="8">Client ID</th>
                                <th data-priority="9">Sales Person</th>
                                <th data-priority="10">Origin</th>
                                <th data-priority="11">Destination</th>
                                <th data-priority="4">Status</th>
                                <th data-priority="13">Booking No</th>
                                <th>Remarks</th>
                                <th data-priority="5">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- CREATE BOOKING MODAL --}}
        <div class="modal fade" id="createBookingModal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="modal-title" style="color: #003580; font-weight: 700;">
                            Creation of Booking
                        </h2>
                        <button type="button" class="close" data-dismiss="modal">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body" style="padding: 1.75rem 2rem;">
                        <form id="createBookingForm">

                            {{-- Service Template --}}
                            <div class="form-group mb-4">
                                <label for="cb_service_template">
                                    Service Template <span class="text-danger">*</span>
                                </label>
                                <select class="form-control" id="cb_service_template" name="service_template" required>
                                    <option value=""></option>
                                </select>
                            </div>

                            {{-- Bill To Party + Forwarder --}}
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <div class="form-group mb-0">
                                        <label for="cb_bill_to_party">
                                            Bill To Party <span class="text-danger">*</span>
                                        </label>
                                        <select class="form-control" id="cb_bill_to_party" name="bill_to_party" required>
                                            <option value=""></option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-0">
                                        <label for="cb_forwarder">
                                            Forwarder <span class="text-danger">*</span>
                                        </label>
                                        <select class="form-control" id="cb_forwarder" name="forwarder" required>
                                            <option value=""></option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            {{-- Include the Country --}}
                            <div class="form-group mb-3 mt-3">
                                <div class="form-check d-flex align-items-center">
                                    <input class="form-check-input me-2" type="checkbox" id="cb_include_country"
                                        name="include_country">
                                    <label class="form-check-label mb-0" for="cb_include_country">
                                        Include the Country
                                    </label>
                                </div>
                            </div>

                            {{-- Place of Receipt + Place of Delivery --}}
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

                        </form>

                        {{-- PROGRESS PANEL — hidden until submit --}}
                        <div id="cbProgressPanel" class="d-none mt-3">
                            <p class="text-muted small mb-2">Processing — please do not close this window.</p>
                            <div class="list-group" id="cbStepList"></div>
                            <div id="cbProgressError" class="alert alert-danger mt-3 d-none"></div>
                            <div id="cbProgressSuccess" class="alert alert-success mt-3 d-none">
                                <i class="fas fa-check-circle mr-2"></i>
                                All steps completed. Booking has been created in SCM.
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <a href="#" class="text-primary mr-3" id="cancelCreateBookingBtn" data-dismiss="modal"
                            style="font-weight: 500; text-decoration: underline;">Cancel</a>

                        <button type="button" class="btn btn-primary px-4" id="submitCreateBookingBtn">
                            Create
                        </button>
                        <button type="button" class="btn btn-secondary px-4 d-none" id="cbCloseBtn"
                            data-dismiss="modal">
                            Close
                        </button>

                    </div>


                </div>
            </div>
        </div>


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

                        {{-- Options --}}
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
                                <label class="font-weight-bold">Limit <small class="text-muted">(optional)</small></label>
                                <input type="number" id="crmLimit" class="form-control form-control-sm"
                                    placeholder="blank = all records">
                            </div>
                            <div class="col-md-4">
                                <label class="font-weight-bold">Booking No <small
                                        class="text-muted">(optional)</small></label>
                                <input type="text" id="crmBooking" class="form-control form-control-sm"
                                    placeholder="e.g. 7037 — blank = all">
                            </div>
                        </div>

                        {{-- Progress --}}
                        <div id="crmSyncProgress" class="d-none mb-3">
                            <div class="d-flex align-items-center text-muted">
                                <div class="spinner-border spinner-border-sm mr-2" role="status"></div>
                                <span>Connecting to CRM and running sp_QuoteDetails()...</span>
                            </div>
                        </div>

                        {{-- Result --}}
                        <div id="crmSyncResult"></div>

                        {{-- Sync History --}}
                        <div class="mt-4">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <h6 class="mb-0 font-weight-bold">
                                    <i class="fas fa-history mr-1 text-muted"></i> Recent Sync History
                                </h6>
                                <small class="text-muted" id="crmHistoryMeta"></small>
                            </div>
                            <div id="crmHistoryContainer">
                                <div class="text-muted small text-center py-2">
                                    <i class="fas fa-spinner fa-spin mr-1"></i> Loading history...
                                </div>
                            </div>
                        </div>


                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary" id="btnRunCrmSync">
                            <i class="fas fa-play mr-1"></i> Run Sync
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop


@section('css')
    <style>
        /* COMPACT CARD STYLING */
        .compact-card {
            min-height: 75px !important;
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

        /* Total card should not appear clickable */
        .compact-card:not(.card-filter-trigger) {
            cursor: default;
        }

        .compact-card.card-filter-trigger:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        /* Mobile adjustments */
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

        @media (hover: hover) and (pointer: fine) {
            .card-filter-trigger:hover {
                transform: translateY(-3px);
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            }
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

        /* FORM CONTROLS */
        .form-control-sm {
            height: calc(1.5em + 0.5rem + 2px);
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }

        /* FILTER ROW LAYOUT */
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

        /* BUTTON STYLING */
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

        /* RESPONSIVE GRID SPACING */
        .g-2 {
            --bs-gutter-x: 0.5rem;
            --bs-gutter-y: 0.5rem;
        }

        /* TABLE RESPONSIVE WRAPPER */
        .table-responsive-wrapper {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        body {
            overflow-x: hidden;
        }

        .container-fluid {
            overflow-x: hidden;
        }

        /* DATATABLE RESPONSIVE STYLING */
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

        /* DATATABLE INFO AND PAGINATION */
        .dataTables_info,
        .dataTables_paginate {
            padding-top: 0.75rem;
        }

        .dataTables_wrapper>.row:first-child {
            display: none;
        }

        /* MOBILE RESPONSIVE ADJUSTMENTS */
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

        /* TABLET ADJUSTMENTS */
        @media (min-width: 768px) and (max-width: 1199.98px) {
            #searchContainer .dataTables_filter input {
                max-width: 150px;
            }
        }

        /* DESKTOP ADJUSTMENTS */
        @media (min-width: 1200px) {
            .w-md-auto {
                width: auto !important;
            }
        }

        /* UTILITY CLASSES */
        .ms-md-auto {
            margin-left: auto !important;
        }

        @media (max-width: 767.98px) {
            .ms-md-auto {
                margin-left: 0 !important;
            }
        }

        .card-header {
            background-color: #17a2b8;
            color: white;
            padding: 0.75rem 1.25rem;
        }

        .card-title {
            font-size: 1.1rem;
            margin-bottom: 0;
        }

        /* COMPACT CARD STYLING */
        .compact-card {
            min-height: 90px !important;
            margin-bottom: 0.5rem;
            cursor: pointer;
            border-radius: 0.25rem;
            transition: all 0.3s ease;
        }

        /* ✅ FIX: Total card should not appear clickable */
        .compact-card:not(.card-filter-trigger) {
            cursor: default;
        }

        .compact-card.card-filter-trigger:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

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
    </style>
@stop


@section('js')
    <script>
        $(document).ready(function() {
            var currentCardFilter = 'in_progress';
            var statsUpdateTimer = null;

            // Set date filter to All Time BEFORE DataTable initialises
            // so the first AJAX request picks up the correct value
            $('#dateFilter').val('all');

            // Initialize DataTable
            var table = $('#quotationTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('quotation.data') }}',
                    type: 'GET',
                    data: function(d) {
                        d.days = $('#dateFilter').val();
                        d.start_date = $('#startDate').val();
                        d.end_date = $('#endDate').val();
                        d.office = $('#officeFilter').val();
                        d.card_filter = currentCardFilter;
                    },
                    error: function(xhr, error, code) {
                        console.error('DataTables Ajax Error:', xhr, error, code);
                        alert('Error loading data. Please check the console.');
                    }
                },
                columns: [{
                        data: 'report_date',
                        name: 'report_date',
                        searchable: false,
                        responsivePriority: 1
                    },
                    {
                        data: 'OfficeName',
                        name: 'OfficeName',
                        responsivePriority: 2
                    },
                    {
                        data: 'ClientName',
                        name: 'ClientName',
                        responsivePriority: 3,
                        render: function(data, type, row) {
                            if (type === 'display' && data && data.length > 50) {
                                return '<span title="' + data + '">' + data.substr(0, 50) +
                                    '...</span>';
                            }
                            return data;
                        }
                    },
                    {
                        data: 'ClientID',
                        name: 'ClientID',
                        responsivePriority: 8
                    },
                    {
                        data: 'SalesPerson',
                        name: 'SalesPerson',
                        responsivePriority: 9
                    },
                    {
                        data: 'OriginCountry',
                        name: 'OriginCountry',
                        responsivePriority: 10
                    },
                    {
                        data: 'DestinationCountry',
                        name: 'DestinationCountry',
                        responsivePriority: 11
                    },
                    {
                        data: 'QuotationStatus',
                        name: 'QuotationStatus',
                        responsivePriority: 4,
                        render: function(data, type, row) {
                            if (type === 'display') {
                                var badgeClass = 'badge-secondary';
                                if (data === 'Won') badgeClass = 'badge-success';
                                else if (data === 'Lost') badgeClass = 'badge-danger';
                                else if (data === 'Canceled') badgeClass = 'badge-warning';
                                else if (data === 'Failed Int.') badgeClass = 'badge-danger';
                                else if (data === 'Failed Progress') badgeClass = 'badge-secondary';
                                else if (data === 'In Progress') badgeClass = 'badge-info';

                                return '<span class="badge ' + badgeClass + '">' + data + '</span>';
                            }
                            return data;
                        }
                    },

                    {
                        data: 'BookingNo',
                        name: 'BookingNo',
                        responsivePriority: 13
                    },
                    // ── NEW: Remarks / Error column ──────────────────────────────
                    // Parses failure_reason JSON from the DB and renders validation
                    // errors in red for Failed Int. / Failed Progress rows,
                    // or muted grey for informational remarks on other statuses.
                    {
                        data: 'remarks_error',
                        name: 'remarks_error',
                        orderable: false,
                        searchable: false,
                        responsivePriority: 6,
                        render: function(data, type, row) {
                            if (type !== 'display') return data || '';
                            if (!data || data === '-') return '<span class="text-muted">—</span>';

                            var isError = row.QuotationStatus === 'Failed Int.' ||
                                row.QuotationStatus === 'Failed Progress';

                            if (isError) {
                                return '<span class="text-danger" title="' + data + '">' +
                                    '<i class="fas fa-exclamation-circle mr-1"></i>' +
                                    (data.length > 80 ? data.substr(0, 80) + '...' : data) +
                                    '</span>';
                            }
                            return '<span class="text-muted">' + data + '</span>';
                        }
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        responsivePriority: 1,
                        className: 'text-center action-column',
                        render: function(data, type, row) {
                            if (type === 'display') {
                                return '<div class="action-buttons">' + data + '</div>';
                            }
                            return data;
                        }
                    }
                ],
                order: [
                    [0, 'desc']
                ],
                pageLength: 25,
                responsive: {
                    details: {
                        type: 'inline',
                        display: $.fn.dataTable.Responsive.display.childRow,
                        renderer: function(api, rowIdx, columns) {
                            var data = $.map(columns, function(col, i) {
                                return col.hidden ?
                                    '<li data-dtr-index="' + col.columnIndex +
                                    '" data-dt-row="' + col.rowIndex +
                                    '" data-dt-column="' + col.columnIndex + '">' +
                                    '<span class="dtr-title">' + col.title + '</span> ' +
                                    '<span class="dtr-data">' + col.data + '</span>' +
                                    '</li>' : '';
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
                    search: "Search:",
                    searchPlaceholder: "Client, Office, Status...",
                    lengthMenu: "Show _MENU_"
                },
                initComplete: function() {
                    $('.dataTables_length').appendTo('#entriesContainer');
                    $('.dataTables_filter').appendTo('#searchContainer');
                    adjustSearchPlaceholder();
                    $('.card-filter-trigger[data-filter="in_progress"]').addClass('active');
                }
            });

            // ============================================
            // DEBUG
            // ============================================
            table.on('xhr', function() {
                var json = table.ajax.json();
                console.log('DataTable Response:', json);
            });

            // ============================================
            // SEARCH PLACEHOLDER
            // ============================================
            function adjustSearchPlaceholder() {
                var $searchInput = $('.dataTables_filter input');
                if ($(window).width() < 768) {
                    $searchInput.attr('placeholder', 'Search...');
                } else {
                    $searchInput.attr('placeholder', 'Client, Office, Status...');
                }
            }

            $(window).on('resize', function() {
                adjustSearchPlaceholder();
            });

            // ============================================
            // RETRIGGER BUTTON
            // ============================================
            $('#quotationTable').on('click', '.btn-retrigger', function(e) {
                e.stopPropagation();
                var $btn = $(this);
                var bookingNo = $btn.data('booking');

                if (!bookingNo) {
                    alert('Booking number not found');
                    return;
                }

                if (!confirm('Are you sure you want to retrigger booking: ' + bookingNo + '?')) {
                    return;
                }

                $btn.prop('disabled', true);
                $btn.html('<i class="fas fa-spinner fa-spin"></i>');

                $.ajax({
                    url: '{{ route('quotation.retrigger') }}',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        booking_no: bookingNo
                    },
                    success: function(response) {
                        if (response.success) {
                            alert('Successfully retriggered booking: ' + bookingNo);
                            table.ajax.reload(null, false);
                        } else {
                            alert('Failed to retrigger: ' + (response.message ||
                                'Unknown error'));
                        }
                    },
                    error: function(xhr) {
                        alert('Error retriggering booking: ' + (xhr.responseJSON?.message ||
                            'Server error'));
                    },
                    complete: function() {
                        $btn.prop('disabled', false);
                        $btn.html('<i class="fas fa-sync-alt"></i>');
                    }
                });
            });

            // ============================================
            // CREATE BOOKING BUTTON
            // ============================================
            $('#quotationTable').on('click', '.btn-create-booking', function(e) {
                e.stopPropagation();

                var $btn = $(this);

                $('#createBookingModal')
                    .data('quotation-id', $btn.data('id'))
                    .data('booking-no', $btn.data('booking'))
                    .data('team-id', $btn.data('team'))
                    .data('quote-no', $btn.data('quoteno'))
                    .modal('show');
            });

            // ============================================
            // CREATE BOOKING MODAL — POPULATE DROPDOWNS
            // ============================================
            $('#createBookingModal').on('show.bs.modal', function() {
                var modal = $(this);
                var quoteNo = modal.data('quote-no');
                var teamId = modal.data('team-id');

                resetCreateBookingForm();

                setDropdownLoading('#cb_service_template');
                setDropdownLoading('#cb_bill_to_party');
                setDropdownLoading('#cb_forwarder');

                // ── 1. Service Templates ──
                $.get('{{ route('quotation.booking.service-templates') }}', {
                        quotation_id: quoteNo
                    })
                    .done(function(res) {
                        var $select = $('#cb_service_template').empty().append(
                            '<option value=""></option>');
                        if (res.success && res.templates && res.templates.length) {
                            $.each(res.templates, function(i, t) {
                                $select.append($('<option>', {
                                    value: t.Name,
                                    text: t.Name
                                }));
                            });
                        } else {
                            $select.append('<option value="" disabled>No templates available</option>');
                        }
                    })
                    .fail(function(xhr) {
                        console.error('Service templates load failed:', xhr);
                        $('#cb_service_template').empty()
                            .append('<option value="" disabled>Failed to load — try again</option>');
                    });

                // ── 2. Bill To Parties ──
                $.get('{{ route('quotation.booking.bill-to-parties') }}', {
                        team_id: teamId
                    })
                    .done(function(res) {
                        var $select = $('#cb_bill_to_party').empty().append(
                            '<option value=""></option>');
                        if (res.success && res.parties && res.parties.length) {
                            $.each(res.parties, function(i, p) {
                                $select.append($('<option>', {
                                    value: p.PartyCode,
                                    text: p.PartyCode + ' — ' + p.PartyName
                                }));
                            });
                        } else {
                            $select.append('<option value="" disabled>No parties available</option>');
                        }
                    })
                    .fail(function(xhr) {
                        console.error('Bill to parties load failed:', xhr);
                        $('#cb_bill_to_party').empty()
                            .append('<option value="" disabled>Failed to load — try again</option>');
                    });

                // ── 3. Forwarders ──
                $.get('{{ route('quotation.booking.forwarders') }}', {
                        team_id: teamId
                    })
                    .done(function(res) {
                        var $select = $('#cb_forwarder').empty().append('<option value=""></option>');
                        if (res.success && res.parties && res.parties.length) {
                            $.each(res.parties, function(i, p) {
                                $select.append($('<option>', {
                                    value: p.PartyCode,
                                    text: p.PartyCode + ' — ' + p.PartyName
                                }));
                            });
                        } else {
                            $select.append(
                                '<option value="" disabled>No forwarders available</option>');
                        }
                    })
                    .fail(function(xhr) {
                        console.error('Forwarders load failed:', xhr);
                        $('#cb_forwarder').empty()
                            .append('<option value="" disabled>Failed to load — try again</option>');
                    });
            });

            // Clean up when modal closes
            $('#createBookingModal').on('hidden.bs.modal', function() {
                $('.location-suggestions').remove();

                $('#createBookingForm').removeClass('d-none');
                $('#cbProgressPanel').addClass('d-none');
                $('#submitCreateBookingBtn, #cancelCreateBookingBtn').removeClass('d-none');
                $('#cbCloseBtn').addClass('d-none');
                $('#cbStepList').empty();
                $('#cbProgressError, #cbProgressSuccess').addClass('d-none');
            });

            // ============================================
            // CANCEL / X BUTTON
            // ============================================
            $(document).on('click', '#cancelCreateBookingBtn, #createBookingModal .close', function(e) {
                e.preventDefault();
                $('#createBookingModal').modal('hide');
            });

            // ============================================
            // HELPERS — FORM RESET & LOADING STATE
            // ============================================
            function resetCreateBookingForm() {
                $('#cb_service_template, #cb_bill_to_party, #cb_forwarder')
                    .empty().append('<option value=""></option>');
                $('#cb_include_country').prop('checked', false);
                $('#cb_place_of_receipt, #cb_place_of_delivery').val('');
                $('#cb_place_of_receipt_code, #cb_place_of_receipt_id').val('');
                $('#cb_place_of_delivery_code, #cb_place_of_delivery_id').val('');
                $('.location-suggestions').remove();
            }

            function setDropdownLoading(selector) {
                $(selector).empty().append('<option value="" disabled selected>Loading...</option>');
            }

            // ============================================
            // PLACE OF RECEIPT / DELIVERY — AUTOCOMPLETE
            // ============================================
            function setupLocationAutocomplete(inputId, codeId, idHiddenId) {
                var debounceTimer = null;

                $('#' + inputId).on('input', function() {
                    var query = $(this).val().trim();
                    clearTimeout(debounceTimer);
                    removeSuggestions(inputId);

                    if (query.length < 2) return;

                    debounceTimer = setTimeout(function() {
                        $.get('{{ route('quotation.booking.locations') }}', {
                                filter: query,
                                include_country: $('#cb_include_country').is(':checked') ? 1 : 0
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
                                            $('#' + inputId).val(loc.LocationName +
                                                (loc.LocationType ? ' (' + loc
                                                    .LocationType + ')' : ''));
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
                });

                $('#' + inputId).on('blur', function() {
                    setTimeout(function() {
                        removeSuggestions(inputId);
                    }, 200);
                });
            }

            function removeSuggestions(inputId) {
                $('#' + inputId).closest('.form-group').find('.location-suggestions').remove();
            }

            setupLocationAutocomplete('cb_place_of_receipt', 'cb_place_of_receipt_code', 'cb_place_of_receipt_id');
            setupLocationAutocomplete('cb_place_of_delivery', 'cb_place_of_delivery_code',
                'cb_place_of_delivery_id');

            // ============================================
            // STEP LABELS FOR PROGRESS VIEW
            // ============================================
            var BOOKING_STEPS = [{
                    step: 0,
                    label: 'Save Booking Info'
                },
                {
                    step: 1,
                    label: 'Client Creation'
                },
                {
                    step: 2,
                    label: 'Booking XML'
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

            function renderBookingSteps(completedSteps) {
                var $list = $('#cbStepList').empty();
                BOOKING_STEPS.forEach(function(def) {
                    var completed = completedSteps.find(function(s) {
                        return s.step === def.step;
                    });
                    var iconClass, badgeClass, statusText, remarkHtml = '';

                    if (!completed) {
                        iconClass = 'fas fa-circle text-secondary';
                        badgeClass = 'badge-secondary';
                        statusText = 'Pending';
                    } else if (completed.status === 'Success') {
                        iconClass = 'fas fa-check-circle text-success';
                        badgeClass = 'badge-success';
                        statusText = 'Success';
                        if (completed.remark) remarkHtml = '<small class="text-muted d-block mt-1">' +
                            completed.remark + '</small>';
                    } else if (completed.status === 'Skipped') {
                        iconClass = 'fas fa-minus-circle text-secondary';
                        badgeClass = 'badge-secondary';
                        statusText = 'Skipped';
                    } else {
                        iconClass = 'fas fa-times-circle text-danger';
                        badgeClass = 'badge-danger';
                        statusText = 'Failed';
                        if (completed.remark) remarkHtml = '<small class="text-danger d-block mt-1">' +
                            completed.remark + '</small>';
                    }

                    $list.append(
                        '<div class="list-group-item py-2">' +
                        '<div class="d-flex align-items-center justify-content-between">' +
                        '<div><i class="' + iconClass + ' mr-2"></i><strong>Step ' + def.step +
                        ':</strong> ' + def.label + remarkHtml + '</div>' +
                        '<span class="badge ' + badgeClass + '">' + statusText + '</span>' +
                        '</div>' +
                        '</div>'
                    );
                });
            }

            // ============================================
            // SUBMIT CREATE BOOKING → FULL PIPELINE
            // ============================================
            $('#submitCreateBookingBtn').on('click', function() {
                var quotationId = $('#createBookingModal').data('quotation-id');
                var bookingNo = $('#createBookingModal').data('booking-no');

                var serviceTemplate = $('#cb_service_template').val();
                var billToParty = $('#cb_bill_to_party').val();
                var forwarder = $('#cb_forwarder').val();
                var placeOfReceipt = $('#cb_place_of_receipt_code').val();
                var placeOfDelivery = $('#cb_place_of_delivery_code').val();

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

                $('#createBookingForm, #cbProgressPanel').toggleClass('d-none');
                $('#submitCreateBookingBtn, #cancelCreateBookingBtn').addClass('d-none');
                $('#cbProgressError, #cbProgressSuccess').addClass('d-none');
                renderBookingSteps([]);

                $.ajax({
                    url: '{{ route('quotation.process-booking') }}',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        id: quotationId,
                        booking_no: bookingNo,
                        service_template: serviceTemplate,
                        bill_to_party: billToParty,
                        forwarder: forwarder,
                        include_country: $('#cb_include_country').is(':checked') ? 1 : 0,
                        place_of_receipt: placeOfReceipt,
                        place_of_delivery: placeOfDelivery,
                    },
                    success: function(res) {
                        renderBookingSteps(res.steps || []);

                        if (res.success) {
                            $('#cbProgressSuccess').removeClass('d-none');
                            table.ajax.reload(null, false);
                        } else {
                            var failedStep = (res.steps || []).find(function(s) {
                                return s.status === 'Failed';
                            });
                            $('#cbProgressError')
                                .html('<strong>' + (res.failed_label || 'Step') +
                                    ' failed:</strong> ' + (failedStep ? failedStep.remark :
                                        'Unknown error'))
                                .removeClass('d-none');
                        }
                    },
                    error: function(xhr) {
                        var msg = xhr.responseJSON?.message || 'An unexpected error occurred.';
                        $('#cbProgressError').text(msg).removeClass('d-none');
                        if (xhr.responseJSON?.steps) renderBookingSteps(xhr.responseJSON.steps);
                    },
                    complete: function() {
                        $('#cbCloseBtn').removeClass('d-none');
                    }
                });
            });

            // ============================================
            // STATS UPDATE (debounced)
            // ============================================
            function updateCardStats() {
                if (statsUpdateTimer) {
                    clearTimeout(statsUpdateTimer);
                }

                statsUpdateTimer = setTimeout(function() {
                    $.ajax({
                        url: '{{ route('quotation.stats') }}',
                        type: 'GET',
                        data: {
                            days: $('#dateFilter').val(),
                            start_date: $('#startDate').val(),
                            end_date: $('#endDate').val(),
                            office: $('#officeFilter').val()
                        },
                        beforeSend: function() {
                            $('.compact-card .inner h3').css('opacity', '0.5');
                        },
                        success: function(response) {
                            animateValue('stat-total', parseInt($('#stat-total').text()) || 0,
                                response.total, 500);
                            animateValue('stat-in-progress', parseInt($('#stat-in-progress')
                                .text()) || 0, response.in_progress, 500);
                            animateValue('stat-won', parseInt($('#stat-won').text()) || 0,
                                response.won, 500);
                            animateValue('stat-lost', parseInt($('#stat-lost').text()) || 0,
                                response.lost, 500);
                            animateValue('stat-canceled', parseInt($('#stat-canceled')
                                .text()) || 0, response.canceled, 500);
                            animateValue('stat-failed-progress', parseInt($(
                                    '#stat-failed-progress').text()) || 0, response
                                .failed_progress, 500);
                        },
                        error: function(xhr, error, code) {
                            console.error('Failed to update card statistics:', xhr, error,
                                code);
                            $('.compact-card .inner h3').css('opacity', '1');
                        },
                        complete: function() {
                            $('.compact-card .inner h3').css('opacity', '1');
                        }
                    });
                }, 300);
            }

            // ============================================
            // CRM SYNC HISTORY
            // ============================================
            function loadCrmHistory() {
                $('#crmHistoryContainer').html(
                    '<div class="text-muted small text-center py-2">' +
                    '<i class="fas fa-spinner fa-spin mr-1"></i> Loading history...</div>'
                );
                $('#crmHistoryMeta').text('');

                $.ajax({
                    url: '{{ route('crm.sync.history') }}',
                    type: 'GET',
                    success: function(res) {
                        var history = res.history || [];

                        if (!history.length) {
                            $('#crmHistoryContainer').html(
                                '<p class="text-muted small mb-0">No sync runs recorded yet.</p>');
                            return;
                        }

                        $('#crmHistoryMeta').text('Last ' + history.length + ' run(s)');

                        var rows = history.map(function(h) {
                            var failBadge = h.failed > 0 ?
                                '<span class="badge badge-danger ml-1">' + h.failed +
                                ' failed</span>' : '';
                            var scopeTag = h.booking_no ?
                                '<span class="badge badge-secondary ml-1">Booking #' + h
                                .booking_no + '</span>' :
                                (h.limit ? '<span class="badge badge-secondary ml-1">Limit ' + h
                                    .limit + '</span>' : '');

                            return '<tr>' +
                                '<td class="text-nowrap">' + h.run_at + '</td>' +
                                '<td>' + escapeHtml(h.triggered_by) + '</td>' +
                                '<td class="text-center">' + h.total + scopeTag + '</td>' +
                                '<td class="text-center text-success font-weight-bold">' + h
                                .inserted + '</td>' +
                                '<td class="text-center text-info font-weight-bold">' + h
                                .updated + '</td>' +
                                '<td class="text-center">' + h.skipped + '</td>' +
                                '<td class="text-center">' + h.failed + failBadge + '</td>' +
                                '</tr>';
                        }).join('');

                        $('#crmHistoryContainer').html(
                            '<div style="max-height: 220px; overflow-y: auto;">' +
                            '<table class="table table-sm table-bordered table-striped mb-0" style="font-size:0.8rem;">' +
                            '<thead class="thead-light"><tr>' +
                            '<th>Run At</th><th>Triggered By</th><th class="text-center">Total</th>' +
                            '<th class="text-center">Ins.</th><th class="text-center">Upd.</th>' +
                            '<th class="text-center">Skip</th><th class="text-center">Fail</th>' +
                            '</tr></thead>' +
                            '<tbody>' + rows + '</tbody>' +
                            '</table></div>'
                        );
                    },
                    error: function() {
                        $('#crmHistoryContainer').html(
                            '<p class="text-danger small mb-0">Failed to load history.</p>');
                    }
                });
            }

            function escapeHtml(str) {
                if (!str) return '-';
                return String(str)
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;');
            }

            function animateValue(id, start, end, duration) {
                var element = document.getElementById(id);
                if (!element) return;

                start = parseInt(start) || 0;
                end = parseInt(end) || 0;
                var range = end - start;

                if (range === 0 || Math.abs(range) > 100) {
                    element.textContent = end;
                    return;
                }

                var current = start;
                var increment = end > start ? 1 : -1;
                var stepTime = Math.abs(Math.floor(duration / range));

                if (!isFinite(stepTime) || stepTime > 100 || stepTime < 1) {
                    element.textContent = end;
                    return;
                }

                var timer = setInterval(function() {
                    current += increment;
                    element.textContent = current;
                    if (current === end) {
                        clearInterval(timer);
                    }
                }, stepTime);
            }

            // ============================================
            // CARD CLICK FILTERING
            // ============================================
            var touchMoved = false;

            $('.card-filter-trigger').on('touchstart', function() {
                touchMoved = false;
            });
            $('.card-filter-trigger').on('touchmove', function() {
                touchMoved = true;
            });

            $('.card-filter-trigger').on('click touchend', function(e) {
                if (e.type === 'touchend' && touchMoved) return;
                if (e.type === 'touchend') e.preventDefault();

                var filterType = $(this).data('filter');
                if (!filterType) return;

                $('.card-filter-trigger').removeClass('active');
                $(this).addClass('active');
                currentCardFilter = filterType;

                console.log('Card filter set to:', currentCardFilter);

                updateCardStats();
                table.ajax.reload();
            });

            // ============================================
            // CLEAR BUTTON VISIBILITY
            // ============================================
            function updateClearButtonVisibility() {
                var dateFilter = $('#dateFilter').val();
                var officeFilter = $('#officeFilter').val();
                var defaultOffice = '{{ $defaultOffice ?? '' }}';

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

            // ============================================
            // DATE FILTER
            // ============================================
            $('#dateFilter').on('change', function() {
                var selectedValue = $(this).val();

                if (selectedValue === 'custom') {
                    $('#customDateRange').show();
                    $('#customDateRangeTo').show();
                    updateClearButtonVisibility();
                } else {
                    $('#customDateRange').hide();
                    $('#customDateRangeTo').hide();
                    $('#startDate').val('');
                    $('#endDate').val('');
                    updateCardStats();
                    table.ajax.reload();
                    updateClearButtonVisibility();
                }
            });

            // ============================================
            // CUSTOM DATE RANGE
            // ============================================
            $('#startDate, #endDate').on('change', function() {
                if ($('#startDate').val() && $('#endDate').val()) {
                    var startDate = new Date($('#startDate').val());
                    var endDate = new Date($('#endDate').val());

                    if (endDate < startDate) {
                        alert('End date must be after start date');
                        $('#endDate').val('');
                        return;
                    }

                    updateCardStats();
                    table.ajax.reload();
                    updateClearButtonVisibility();
                }
            });

            $('#clearCustomDates').on('click', function() {
                $('#startDate').val('');
                $('#endDate').val('');
                $('#customDateRange').hide();
                $('#customDateRangeTo').hide();
                $('#dateFilter').val('all');
                updateCardStats();
                table.ajax.reload();
            });

            // ============================================
            // OFFICE FILTER
            // ============================================
            $('#officeFilter').on('change', function() {
                updateCardStats();
                table.ajax.reload();
                updateClearButtonVisibility();
            });

            // ============================================
            // CLEAR ALL FILTERS
            // ============================================
            $('#clearAllFilters').on('click', function() {
                $('#dateFilter').val('all');
                $('#startDate').val('');
                $('#endDate').val('');
                $('#customDateRange').hide();
                $('#customDateRangeTo').hide();
                $('#officeFilter').val('{{ $defaultOffice ?? '' }}');

                $('.card-filter-trigger').removeClass('active');
                $('.card-filter-trigger[data-filter="in_progress"]').addClass('active');
                currentCardFilter = 'in_progress';

                updateCardStats();
                table.ajax.reload();
                updateClearButtonVisibility();
            });

            // ============================================
            // CRM SYNC MODAL
            // ============================================
            $('#btnOpenCrmSync').on('click', function() {
                $('#crmSyncResult').html('');
                $('#crmSyncProgress').addClass('d-none');
                $('#crmDryRun').prop('checked', false);
                $('#crmLimit, #crmBooking').val('');
                $('#crmSyncModal').modal('show');
                loadCrmHistory();
            });

            $(document).on('click', '#crmSyncModal .close, #crmSyncModal .btn-secondary', function(e) {
                e.preventDefault();
                $('#crmSyncModal').modal('hide');
            });

            $('#btnRunCrmSync').on('click', function() {
                var btn = $(this);
                var dryRun = $('#crmDryRun').is(':checked') ? 1 : 0;
                var limit = $('#crmLimit').val().trim();
                var booking = $('#crmBooking').val().trim();

                btn.prop('disabled', true);
                $('#crmSyncProgress').removeClass('d-none');
                $('#crmSyncResult').html('');

                $.ajax({
                    url: '{{ route('crm.sync.run') }}',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        dry_run: dryRun,
                        limit: limit || null,
                        booking: booking || null,
                    },
                    success: function(res) {
                        var s = res.summary;
                        var dryTag = res.dry_run ?
                            '<span class="badge badge-warning ml-2">DRY RUN</span>' : '';
                        var alertClass = res.success ? 'alert-success' : 'alert-danger';
                        var icon = res.success ? 'check-circle' : 'times-circle';

                        var html = `
                <div class="alert ${alertClass} mb-3">
                    <i class="fas fa-${icon} mr-2"></i>
                    <strong>Sync ${res.success ? 'completed' : 'completed with failures'}</strong>${dryTag}
                </div>
                <table class="table table-sm table-bordered w-auto">
                    <tbody>
                        <tr><th>Total CRM records</th><td>${s.total}</td></tr>
                        <tr class="table-success"><th>Inserted</th><td>${s.inserted}</td></tr>
                        <tr class="table-info"><th>Updated</th><td>${s.updated}</td></tr>
                        <tr><th>Skipped</th><td>${s.skipped}</td></tr>
                        <tr class="${s.failed > 0 ? 'table-danger' : ''}">
                            <th>Failed</th><td>${s.failed}</td>
                        </tr>
                    </tbody>
                </table>`;

                        if (res.errors && res.errors.length) {
                            html +=
                                '<h6 class="text-danger mt-3">Errors</h6><ul class="list-group">';
                            res.errors.forEach(function(e) {
                                html += `<li class="list-group-item list-group-item-danger">
                        <strong>Booking #${e.bookingno ?? 'N/A'}</strong>: ${e.error}
                    </li>`;
                            });
                            html += '</ul>';
                        }

                        if (res.verification && res.verification.total !== undefined) {
                            var v = res.verification;
                            html += `<h6 class="mt-3">Verification (SQL Server)</h6>
                    <p class="mb-1">Rows tagged <code>crm_sync</code>: <strong>${v.total}</strong></p>`;

                            if (v.recent && v.recent.length) {
                                html += `<table class="table table-sm table-bordered mt-2">
                        <thead><tr><th>BookingNo</th><th>Company</th><th>Team</th><th>Status</th><th>UpdatedOn</th></tr></thead>
                        <tbody>`;
                                v.recent.forEach(function(r) {
                                    html += `<tr>
                            <td>${r.bookingno}</td>
                            <td>${r.companyname ?? '-'}</td>
                            <td>${r.teamname ?? '-'}</td>
                            <td>${r.QuoteStatus ?? '-'}</td>
                            <td>${r.updatedon ?? '-'}</td>
                        </tr>`;
                                });
                                html += '</tbody></table>';
                            }
                        }

                        if (res.verification && res.verification.record !== undefined) {
                            var rec = res.verification.record;
                            if (rec) {
                                html += `<h6 class="mt-3">Verified — Booking #${rec.bookingno}</h6>
                        <table class="table table-sm table-bordered w-auto"><tbody>`;
                                Object.entries(rec).forEach(function([k, v]) {
                                    html +=
                                        `<tr><th>${k}</th><td>${v ?? '-'}</td></tr>`;
                                });
                                html += '</tbody></table>';
                            } else {
                                html +=
                                    '<div class="alert alert-danger mt-3">Record not found in SQL Server after sync.</div>';
                            }
                        }

                        if (!res.dry_run && (s.inserted > 0 || s.updated > 0)) {
                            table.ajax.reload(null, false);
                            updateCardStats();
                        }

                        $('#crmSyncResult').html(html);

                        if (!res.dry_run) {
                            loadCrmHistory();
                        }
                    },
                    error: function(xhr) {
                        $('#crmSyncResult').html(
                            '<div class="alert alert-danger">Request failed: ' +
                            (xhr.responseJSON?.message || xhr.statusText) + '</div>'
                        );
                    },
                    complete: function() {
                        btn.prop('disabled', false);
                        $('#crmSyncProgress').addClass('d-none');
                    }
                });
            });
        });
    </script>
@stop
