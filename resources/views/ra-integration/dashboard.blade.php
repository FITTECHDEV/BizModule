@extends('adminlte::page')

@section('title', 'Revenue Authority Integration')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center flex-wrap">
        <h1>Revenue Authority Integration Dashboard</h1>
        <button type="button" id="toggleViewBtn"
            class="btn btn-{{ $view === 'ar' ? 'purple' : 'info' }} btn-sm"
            title="Switch to {{ $view === 'ar' ? 'Accounts Payable' : 'Accounts Receivable' }}">
            <i class="fas fa-exchange-alt"></i>
            <span>{{ $view === 'ar' ? 'Accounts Payable' : 'Accounts Receivable' }}</span>
        </button>
    </div>
@stop

@section('content')
    <div class="container-fluid">

        {{-- Statistics Cards --}}
        <div class="row mb-3" id="dashboardCards">
            @if ($view === 'ar')
                @include('ra-integration.partials.ar-cards')
            @else
                @include('ra-integration.partials.ap-cards')
            @endif
        </div>

        {{-- Filters and Table Card --}}
        <div class="card">
            <div class="card-body">
                {{-- Filters Row - Responsive Layout --}}
                <div class="row mb-3 g-2">
                    {{-- Show entries - Full width on mobile --}}
                    <div class="col-12 col-md-auto order-1">
                        <div id="entriesContainer"></div>
                    </div>

                    {{-- Office Filter --}}
                    <div class="col-12 col-sm-6 col-md-auto order-2">
                        <select id="officeFilter" class="form-control form-control-sm">
                            @foreach ($offices as $off)
                                <option value="{{ $off['id'] }}" {{ $office === $off['id'] ? 'selected' : '' }}>
                                    {{ $off['name'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Date Preset Filter --}}
                    <div class="col-12 col-sm-6 col-md-auto order-3">
                        <select id="datePresetFilter" class="form-control form-control-sm">
                            <option value="today" selected>Today</option>
                            <option value="last_month">Last Month</option>
                            <option value="last_3_months">Last 3 Months</option>
                            <option value="custom">Custom Range</option>
                        </select>
                    </div>

                    {{-- Custom Date Range - Start Date --}}
                    <div class="col-12 col-sm-6 col-md-auto order-4" id="customDateFrom" style="display: none;">
                        <input type="date" id="dateFrom" class="form-control form-control-sm"
                            value="{{ $dateFrom }}" placeholder="Start Date">
                    </div>

                    {{-- Custom Date Range - End Date --}}
                    <div class="col-12 col-sm-6 col-md-auto order-5" id="customDateTo" style="display: none;">
                        <div class="input-group input-group-sm">
                            <input type="date" id="dateTo" class="form-control form-control-sm"
                                value="{{ $dateTo }}" placeholder="End Date">
                            <button class="btn btn-outline-secondary" type="button" id="clearCustomDates"
                                title="Clear custom date range">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>

                    {{-- Action Buttons --}}
                    <div class="col-12 col-sm-6 col-md-auto order-6">
                        <button type="button" id="applyFiltersBtn" class="btn btn-primary btn-sm" title="Apply filters">
                            <i class="fas fa-filter"></i>
                        </button>
                    </div>

                    {{-- Search - Full width on mobile, auto on desktop --}}
                    <div class="col-12 col-md-auto order-7 ms-md-auto">
                        <div id="searchContainer"></div>
                    </div>
                </div>

                {{-- DataTable - Responsive Wrapper --}}
                <div class="table-responsive-wrapper">
                    <table id="invoice-table" class="table table-bordered table-striped table-hover w-100 dtr-inline">
                        <thead>
                            <tr>
                                <th data-priority="1">Invoice Number</th>
                                <th data-priority="2">Type</th>
                                <th data-priority="3">Customer Name</th>
                                <th data-priority="4">Date</th>
                                <th data-priority="5">Status</th>
                                <th data-priority="6">Remarks</th>
                                <th data-priority="7">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
@stop


@section('css')
    <style>
        /* ============================================
           DASHBOARD CARDS RESPONSIVE LAYOUT
           ============================================ */
        #dashboardCards {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
            margin-bottom: 1rem;
        }

        /* Ensure cards are responsive across all screen sizes */
        #dashboardCards > div {
            flex: 1 1 auto;
            min-width: 0; /* Allow flex items to shrink below content size */
        }

        /* Mobile (Extra Small): Stack cards 2 per row (50% width) */
        @media (max-width: 575.98px) {
            #dashboardCards {
                gap: 0.5rem;
            }
            
            #dashboardCards > div {
                flex: 1 1 calc(50% - 0.25rem);
                max-width: calc(50% - 0.25rem);
            }
        }

        /* Small tablets: 3 cards per row */
        @media (min-width: 576px) and (max-width: 767.98px) {
            #dashboardCards > div {
                flex: 1 1 calc(33.333% - 0.5rem);
                max-width: calc(33.333% - 0.5rem);
            }
        }

        /* Tablets: 3 cards per row */
        @media (min-width: 768px) and (max-width: 991.98px) {
            #dashboardCards > div {
                flex: 1 1 calc(33.333% - 0.5rem);
                max-width: calc(33.333% - 0.5rem);
            }
        }

        /* Large tablets/Small desktops: 4 cards per row */
        @media (min-width: 992px) and (max-width: 1199.98px) {
            #dashboardCards > div {
                flex: 1 1 calc(25% - 0.5625rem);
                max-width: calc(25% - 0.5625rem);
            }
        }

        /* Desktop: All 6 cards in one row */
        @media (min-width: 1200px) {
            #dashboardCards > div {
                flex: 1 1 calc(16.666% - 0.625rem); /* 100% / 6 cards */
                max-width: calc(16.666% - 0.625rem);
            }
        }

        /* Extra large screens: comfortable spacing */
        @media (min-width: 1400px) {
            #dashboardCards {
                gap: 1rem;
            }
            
            #dashboardCards > div {
                flex: 1 1 calc(16.666% - 0.833rem);
                max-width: calc(16.666% - 0.833rem);
            }
        }

        /* ============================================
           CARD CONTENT OPTIMIZATION
           ============================================ */
        
        /* Reduce card padding and sizing on smaller screens */
        #dashboardCards .small-box {
            margin-bottom: 0 !important;
        }

        /* Medium screens optimization */
        @media (max-width: 991.98px) {
            #dashboardCards .small-box .inner {
                padding: 0.75rem 0.5rem;
            }
            
            #dashboardCards .small-box .inner h3 {
                font-size: 1.5rem;
                margin-bottom: 0.25rem;
            }
            
            #dashboardCards .small-box .inner p {
                font-size: 0.75rem;
                margin-bottom: 0;
            }
            
            #dashboardCards .small-box .icon {
                font-size: 3rem;
            }
        }

        /* Small screens optimization */
        @media (max-width: 575.98px) {
            #dashboardCards .small-box .inner {
                padding: 0.5rem 0.4rem;
            }
            
            #dashboardCards .small-box .inner h3 {
                font-size: 1.25rem;
            }
            
            #dashboardCards .small-box .inner p {
                font-size: 0.65rem;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }
            
            #dashboardCards .small-box .icon {
                font-size: 2.5rem;
                right: 5px;
            }
        }

        /* ============================================
           BADGE STYLING
           ============================================ */
        .badge-purple {
            background: #6f42c1;
            color: #fff;
        }

        /* ============================================
           FORM CONTROLS - Compact sizing
           ============================================ */
        .form-control-sm {
            height: calc(1.5em + 0.5rem + 2px);
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
            border-radius: 0.25rem;
            transition: all 0.2s ease;
        }

        .form-control-sm:focus {
            border-color: #80bdff;
            box-shadow: 0 0 0 0.15rem rgba(0, 123, 255, 0.2);
        }

        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
            height: calc(1.5em + 0.5rem + 2px);
            transition: all 0.2s ease;
        }

        .btn-sm:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .btn-sm:active {
            transform: translateY(0);
        }

        /* ============================================
           FILTER ROW LAYOUT
           ============================================ */
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

        /* ============================================
           BUTTON STYLING
           ============================================ */
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

        /* Button group gap */
        .d-flex.gap-2 {
            gap: 0.5rem;
        }

        #toggleViewBtn {
            font-weight: 600;
            letter-spacing: 0.3px;
            white-space: nowrap;
        }

        #toggleViewBtn:hover {
            opacity: 0.9;
        }

        /* Touch-friendly buttons on mobile (44px minimum) */
        @media (max-width: 767.98px) {
            .btn-sm {
                min-width: 44px;
                min-height: 44px;
                padding: 0.5rem;
                font-size: 1rem;
            }

            #applyFiltersBtn,
            #refreshBtn {
                min-width: 44px;
            }
        }

        /* ============================================
           RESPONSIVE GRID SPACING
           ============================================ */
        .g-2 {
            --bs-gutter-x: 0.5rem;
            --bs-gutter-y: 0.5rem;
        }

        /* ============================================
           TABLE RESPONSIVE WRAPPER
           ============================================ */
        .table-responsive-wrapper {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        /* Prevent horizontal page scroll */
        body {
            overflow-x: hidden;
        }

        .container-fluid {
            overflow-x: hidden;
            max-width: 100%;
            padding-right: 15px;
            padding-left: 15px;
        }

        /* ============================================
           DATATABLE RESPONSIVE STYLING
           ============================================ */
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

        /* Child row content styling */
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

        /* ============================================
           DATATABLE INFO AND PAGINATION
           ============================================ */
        .dataTables_info,
        .dataTables_paginate {
            padding-top: 0.75rem;
        }

        /* Hide default DataTables wrapper elements */
        .dataTables_wrapper>.row:first-child {
            display: none;
        }

        /* ============================================
           MOBILE RESPONSIVE ADJUSTMENTS
           ============================================ */
        @media (max-width: 767.98px) {

            /* Full width search input on mobile */
            #searchContainer .dataTables_filter input {
                max-width: 100%;
            }

            #searchContainer .dataTables_filter label {
                flex-direction: column;
                align-items: stretch;
                gap: 0.25rem;
            }

            /* Compact table text */
            #invoice-table th,
            #invoice-table td {
                font-size: 0.85rem;
                padding: 0.5rem;
            }

            /* Better spacing for entries selector on mobile */
            #entriesContainer .dataTables_length label {
                flex-direction: row;
                align-items: center;
            }

            #entriesContainer .dataTables_length select {
                flex: 0 0 auto;
            }
        }

        /* ============================================
           TABLET ADJUSTMENTS (768px - 1199px)
           ============================================ */
        @media (min-width: 768px) and (max-width: 1199.98px) {
            #searchContainer .dataTables_filter input {
                max-width: 150px;
            }
        }

        /* ============================================
           DESKTOP ADJUSTMENTS (1200px+)
           ============================================ */
        @media (min-width: 1200px) {

            /* Ensure proper button width on desktop */
            .w-md-auto {
                width: auto !important;
            }
        }

        /* ============================================
           UTILITY CLASSES
           ============================================ */
        .ms-md-auto {
            margin-left: auto !important;
        }

        @media (max-width: 767.98px) {
            .ms-md-auto {
                margin-left: 0 !important;
            }
        }

        /* Card header styling */
        .card-header {
            background-color: #17a2b8;
            color: white;
            padding: 0.75rem 1.25rem;
        }

        .card-title {
            font-size: 1.1rem;
            margin-bottom: 0;
        }

        /* Table styling improvements */
        .table {
            font-size: 0.9rem;
        }

        .table thead th {
            font-weight: 600;
            border-bottom: 2px solid #dee2e6;
        }

        .table tbody tr:hover {
            background-color: #f8f9fa;
        }

        /* Smooth transitions */
        * {
            transition: background-color 0.15s ease, border-color 0.15s ease;
        }
    </style>
@stop


@section('js')
    <script>
        $(document).ready(function() {

            // Initialize DataTable with empty data (no database fetching)
            const invoiceTable = $('#invoice-table').DataTable({
                data: [], // Empty dataset - no database calls
                columns: [{
                        data: 'invoice_number',
                        defaultContent: '-',
                        responsivePriority: 1
                    },
                    {
                        data: 'type',
                        defaultContent: '-',
                        responsivePriority: 2
                    },
                    {
                        data: 'customer_name',
                        defaultContent: '-',
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
                        data: 'date',
                        defaultContent: '-',
                        responsivePriority: 4
                    },
                    {
                        data: 'status',
                        defaultContent: '-',
                        responsivePriority: 5,
                        render: function(data, type, row) {
                            if (type === 'display' && data) {
                                var badgeClass = 'badge-secondary';
                                if (data.toLowerCase() === 'success') badgeClass = 'badge-success';
                                else if (data.toLowerCase() === 'failed') badgeClass =
                                    'badge-danger';
                                else if (data.toLowerCase() === 'pending') badgeClass =
                                    'badge-warning';

                                return '<span class="badge ' + badgeClass + '">' + data + '</span>';
                            }
                            return data;
                        }
                    },
                    {
                        data: 'remarks',
                        defaultContent: '-',
                        responsivePriority: 6
                    },
                    {
                        data: null,
                        orderable: false,
                        searchable: false,
                        responsivePriority: 7,
                        className: 'text-center',
                        render: function() {
                            return `<button class="btn btn-sm btn-info view-invoice" title="View Invoice">
                                <i class="fas fa-eye"></i>
                            </button>`;
                        }
                    }
                ],
                order: [
                    [3, 'desc']
                ],
                pageLength: 25,
                responsive: {
                    details: {
                        type: 'inline',
                        display: $.fn.dataTable.Responsive.display.childRowImmediate,
                        renderer: function(api, rowIdx, columns) {
                            var data = $.map(columns, function(col, i) {
                                return col.hidden ?
                                    '<li data-dtr-index="' + col.columnIndex +
                                    '" data-dt-row="' + col.rowIndex + '" data-dt-column="' +
                                    col.columnIndex + '">' +
                                    '<span class="dtr-title">' + col.title + '</span> ' +
                                    '<span class="dtr-data">' + col.data + '</span>' +
                                    '</li>' :
                                    '';
                            }).join('');

                            return data ?
                                $('<ul class="dtr-details"/>').append(data) :
                                false;
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
                    emptyTable: "No invoices found for the selected filters",
                    search: "Search:",
                    searchPlaceholder: "Invoice, Customer...",
                    lengthMenu: "Show _MENU_"
                },
                initComplete: function() {
                    // Move DataTable controls to custom containers
                    $('.dataTables_length').appendTo('#entriesContainer');
                    $('.dataTables_filter').appendTo('#searchContainer');

                    // Adjust search input placeholder for mobile
                    adjustSearchPlaceholder();
                }
            });

            // Adjust search placeholder based on screen size
            function adjustSearchPlaceholder() {
                var $searchInput = $('.dataTables_filter input');
                if ($(window).width() < 768) {
                    $searchInput.attr('placeholder', 'Search...');
                } else {
                    $searchInput.attr('placeholder', 'Invoice, Customer...');
                }
            }

            // Re-adjust on window resize
            $(window).on('resize', function() {
                adjustSearchPlaceholder();
            });

            // Show/hide custom date range based on preset selection
            $(document).on('change', '#datePresetFilter', function() {
                const isCustom = $(this).val() === 'custom';
                $('#customDateFrom, #customDateTo').toggle(isCustom);

                if (!isCustom) {
                    $('#dateFrom, #dateTo').val('');
                }
            });

            // Trigger on page load
            $('#datePresetFilter').trigger('change');

            // Clear custom dates button
            $(document).on('click', '#clearCustomDates', function() {
                $('#dateFrom').val('');
                $('#dateTo').val('');
                $('#customDateFrom').hide();
                $('#customDateTo').hide();
                $('#datePresetFilter').val('today');
            });

            // Apply filters button (no data reload since there's no Ajax)
            $(document).on('click', '#applyFiltersBtn', function() {
                // No action needed - display only
                console.log('Filters applied (display only mode)');
            });

            // Toggle between AR/AP view - FULLY FUNCTIONAL
            $(document).on('click', '#toggleViewBtn', function() {
                const currentView = '{{ $view }}';
                const newView = currentView === 'ar' ? 'ap' : 'ar';

                // Preserve current filters in URL
                const params = new URLSearchParams({
                    view: newView,
                    office: $('#officeFilter').val() || '',
                    date_from: $('#dateFrom').val() || '',
                    date_to: $('#dateTo').val() || ''
                });

                // Redirect to same page with toggled view
                window.location.href = window.location.pathname + '?' + params.toString();
            });

            // View invoice button click handler (with touch-friendly feedback)
            $('#invoice-table').on('click', '.view-invoice', function(e) {
                e.stopPropagation(); // Prevent row expansion on mobile

                var $btn = $(this);
                var rowData = invoiceTable.row($btn.closest('tr')).data();

                if (!rowData) {
                    alert('Invoice data not found');
                    return;
                }

                // Add your view invoice logic here
                console.log('View invoice:', rowData);
                alert('View invoice: ' + (rowData.invoice_number || 'N/A'));
            });

        });
    </script>
@endsection