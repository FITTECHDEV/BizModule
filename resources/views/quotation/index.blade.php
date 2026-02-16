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
            <div class="col-6 col-md-6 col-lg-2 mb-2">
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
            <div class="col-6 col-md-6 col-lg-2 mb-2">
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
            <div class="col-6 col-md-6 col-lg-2 mb-2">
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
            <div class="col-6 col-md-6 col-lg-2 mb-2">
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

            {{-- 5. Failed Int. --}}
            <div class="col-6 col-md-6 col-lg-2 mb-2">
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

            {{-- 6. Total --}}
            <div class="col-6 col-md-6 col-lg-2 mb-2">
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
                        <select id="officeFilter" class="form-control form-control-sm">
                            <option value="">All Offices</option>
                            @foreach ($offices as $office)
                                <option value="{{ $office }}">{{ $office }}</option>
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
                        <button type="button" id="clearAllFilters" class="btn btn-outline-secondary btn-sm w-100 w-md-auto"
                            title="Clear all filters">
                            <i class="fas fa-redo"></i> Clear Filters
                        </button>
                    </div>

                    {{-- Search --}}
                    <div class="col-12 col-md-auto order-7 ms-md-auto">
                        <div id="searchContainer"></div>
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
                                <th data-priority="12">Remark</th>
                                <th data-priority="13">Booking No</th>
                                <th data-priority="5">Action</th>
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
        /* COMPACT CARD STYLING */
        .compact-card {
            min-height: 90px !important;
            margin-bottom: 0.5rem;
            cursor: pointer;
            border-radius: 0.25rem;
            transition: all 0.3s ease;
        }

        .compact-card .inner {
            padding: 10px !important;
        }

        .compact-card .inner h3 {
            font-size: 2rem;
            margin-bottom: 5px;
        }

        .compact-card .inner p {
            font-size: 0.875rem;
            margin-bottom: 0;
        }

        .compact-card .icon {
            font-size: 50px !important;
            top: 10px;
            right: 10px;
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
            var currentCardFilter = 'all';
            var statsUpdateTimer = null; // Debounce timer

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
                                else if (data === 'In Progress') badgeClass = 'badge-info';

                                return '<span class="badge ' + badgeClass + '">' + data + '</span>';
                            }
                            return data;
                        }
                    },
                    {
                        data: 'StatusRemark',
                        name: 'StatusRemark',
                        responsivePriority: 12
                    },
                    {
                        data: 'BookingNo',
                        name: 'BookingNo',
                        responsivePriority: 13
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        responsivePriority: 1, // keep always visible
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
                    search: "Search:",
                    searchPlaceholder: "Client, Office, Status...",
                    lengthMenu: "Show _MENU_"
                },
                initComplete: function() {
                    $('.dataTables_length').appendTo('#entriesContainer');
                    $('.dataTables_filter').appendTo('#searchContainer');
                    adjustSearchPlaceholder();

                    // ✅ FIX: Update stats after table initializes
                    updateCardStats();
                }
            });

            // DEBUG: Log when table loads
            table.on('xhr', function() {
                var json = table.ajax.json();
                console.log('DataTable Response:', json);
            });

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

            // Retrigger button
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

            // ✅ FIX: Debounced stats update to prevent rapid-fire requests
            function updateCardStats() {
                // Clear existing timer
                if (statsUpdateTimer) {
                    clearTimeout(statsUpdateTimer);
                }

                // Set new timer (300ms debounce)
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
                            animateValue('stat-failed-int', parseInt($('#stat-failed-int')
                                .text()) || 0, response.failed_int, 500);
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
                }, 300); // 300ms debounce
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

            // Card click filtering
            var touchMoved = false;

            $('.card-filter-trigger').on('touchstart', function() {
                touchMoved = false;
            });

            $('.card-filter-trigger').on('touchmove', function() {
                touchMoved = true;
            });

            $('.card-filter-trigger').on('click touchend', function(e) {
                if (e.type === 'touchend' && touchMoved) {
                    return;
                }

                if (e.type === 'touchend') {
                    e.preventDefault();
                }

                var filterType = $(this).data('filter');

                // Debug log
                console.log('Card clicked:', filterType);

                // ✅ FIX: Don't do anything if no filter type (Total card)
                if (!filterType) {
                    return;
                }

                $('.card-filter-trigger').removeClass('active');
                $(this).addClass('active');
                currentCardFilter = filterType;

                // Debug log
                console.log('Current card filter set to:', currentCardFilter);

                updateCardStats();
                table.ajax.reload();
            });

            // Show/hide clear filters button
            function updateClearButtonVisibility() {
                var dateFilter = $('#dateFilter').val();
                var officeFilter = $('#officeFilter').val();

                if (dateFilter === 'custom') {
                    $('#clearFiltersContainer').hide();
                    return;
                }

                if (dateFilter !== '0' || officeFilter !== '') {
                    $('#clearFiltersContainer').show();
                } else {
                    $('#clearFiltersContainer').hide();
                }
            }

            // Date filter change
            $('#dateFilter').on('change', function() {
                var selectedValue = $(this).val();

                if (selectedValue === 'custom') {
                    $('#customDateRange').show();
                    $('#customDateRangeTo').show();
                    updateClearButtonVisibility();
                    // Don't update stats yet - wait for dates to be selected
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

            // Custom date range change
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

            // Clear custom date range
            $('#clearCustomDates').on('click', function() {
                $('#startDate').val('');
                $('#endDate').val('');
                $('#customDateRange').hide();
                $('#customDateRangeTo').hide();
                $('#dateFilter').val('0');
                updateCardStats();
                table.ajax.reload();
            });

            // Office filter change
            $('#officeFilter').on('change', function() {
                updateCardStats();
                table.ajax.reload();
                updateClearButtonVisibility();
            });

            // Clear all filters
            $('#clearAllFilters').on('click', function() {
                $('#dateFilter').val('0');
                $('#startDate').val('');
                $('#endDate').val('');
                $('#customDateRange').hide();
                $('#customDateRangeTo').hide();
                $('#officeFilter').val('');

                // ✅ FIX: Remove active class from all cards (no default selection)
                $('.card-filter-trigger').removeClass('active');

                // ✅ FIX: Reset to show all records (no card filter)
                currentCardFilter = 'all';

                updateCardStats();
                table.ajax.reload();
                updateClearButtonVisibility();
            });
        });
    </script>
@stop
