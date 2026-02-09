@extends('adminlte::page')

@section('title', 'SCM Profit Integration')

@section('content_header')
    <h1>SCM Profit Integration Dashboard</h1>
@stop

@section('content')
    <div class="container-fluid">

        {{-- Statistics Cards - Pure Bootstrap Grid --}}
        <div class="row mb-3">
            {{-- 1. Pending --}}
            <div class="col-6 col-md-6 col-lg-3 mb-2">
                <div class="small-box bg-warning card-filter-trigger compact-card" data-filter="pending"
                    title="Click to view pending records">
                    <div class="inner">
                        <h3 id="stat-pending">{{ $stats['pending'] }}</h3>
                        <p>Pending</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-clock"></i>
                    </div>
                </div>
            </div>

            {{-- 2. Success --}}
            <div class="col-6 col-md-6 col-lg-3 mb-2">
                <div class="small-box bg-success card-filter-trigger compact-card" data-filter="success"
                    title="Click to view successful records">
                    <div class="inner">
                        <h3 id="stat-success">{{ $stats['success'] }}</h3>
                        <p>Success</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
            </div>

            {{-- 3. Failed --}}
            <div class="col-6 col-md-6 col-lg-3 mb-2">
                <div class="small-box bg-danger card-filter-trigger compact-card" data-filter="failed"
                    title="Click to view failed records">
                    <div class="inner">
                        <h3 id="stat-failed">{{ $stats['failed'] }}</h3>
                        <p>Failed</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-times-circle"></i>
                    </div>
                </div>
            </div>

            {{-- 4. Total --}}
            <div class="col-6 col-md-6 col-lg-3 mb-2">
                <div class="small-box bg-info card-filter-trigger compact-card" data-filter="all"
                    title="Click to view all records">
                    <div class="inner">
                        <h3 id="stat-total">{{ $stats['total'] }}</h3>
                        <p>Total Records</p>
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
                {{-- Filters Row - Responsive Stacking --}}
                <div class="row mb-3 g-2">
                    {{-- Show entries - Full width on mobile --}}
                    <div class="col-12 col-md-auto order-1">
                        <div id="entriesContainer"></div>
                    </div>

                    {{-- Date Filter --}}
                    <div class="col-12 col-sm-6 col-md-auto order-2">
                        <select id="dateFilter" class="form-control form-control-sm">
                            <option value="0" selected>Today</option>
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

                    {{-- Search - Full width on mobile, auto on desktop --}}
                    <div class="col-12 col-md-auto order-7 ms-md-auto">
                        <div id="searchContainer"></div>
                    </div>
                </div>

                {{-- DataTable - Responsive Wrapper --}}
                <div class="table-responsive-wrapper">
                    <table id="scmProfitTable" class="table table-bordered table-striped table-hover w-100 dtr-inline">
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
        /* ============================================
               COMPACT CARD STYLING
               ============================================ */
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

        /* Card hover and active states - Desktop only */
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

        /* Touch-friendly active state for mobile */
        @media (hover: none) and (pointer: coarse) {
            .card-filter-trigger:active {
                opacity: 0.8;
            }
        }

        /* ============================================
               FORM CONTROLS
               ============================================ */
        .form-control-sm {
            height: calc(1.5em + 0.5rem + 2px);
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
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

        #clearAllFilters {
            height: calc(1.5em + 0.5rem + 2px);
            white-space: nowrap;
        }

        #clearAllFilters:hover {
            background-color: #6c757d;
            color: white;
            border-color: #6c757d;
        }

        /* Touch-friendly buttons on mobile (44px minimum) */
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
        }

        /* ============================================
               DATATABLE RESPONSIVE STYLING
               ============================================ */

        /* Child row (expandable detail) styling */
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

            /* Compact card mobile sizing */
            .compact-card .icon {
                font-size: 40px !important;
            }

            .compact-card .inner h3 {
                font-size: 1.5rem;
            }

            .compact-card .inner p {
                font-size: 0.75rem;
            }

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
            #scmProfitTable th,
            #scmProfitTable td {
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
    </style>
@stop

@section('js')
    <script>
        $(document).ready(function() {
            var currentCardFilter = 'all';

            // Initialize DataTable with Responsive Extension
            var table = $('#scmProfitTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('scm-profit-integration.data') }}',
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
                        data: 'SCMStatus',
                        name: 'SCMStatus',
                        responsivePriority: 4,
                        render: function(data, type, row) {
                            if (type === 'display') {
                                var badgeClass = 'badge-secondary';
                                if (data === 'Success') badgeClass = 'badge-success';
                                else if (data === 'Failed') badgeClass = 'badge-danger';
                                else if (data === 'Pending') badgeClass = 'badge-warning';

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
                        responsivePriority: 5,
                        className: 'text-center'
                    }
                ],
                order: [
                    [0, 'desc']
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
                    search: "Search:",
                    searchPlaceholder: "Client, Office, Status...",
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
                    $searchInput.attr('placeholder', 'Client, Office, Status...');
                }
            }

            // Re-adjust on window resize
            $(window).on('resize', function() {
                adjustSearchPlaceholder();
            });

            // Retrigger button click handler with touch-friendly feedback
            // $('#scmProfitTable').on('click', '.btn-retrigger', function(e) {
            //     e.stopPropagation(); // Prevent row expansion on mobile

            //     var $btn = $(this);
            //     var bookingNo = $btn.data('booking');

            //     if (!bookingNo) {
            //         alert('Booking number not found');
            //         return;
            //     }

            //     if (!confirm('Are you sure you want to retrigger booking: ' + bookingNo + '?')) {
            //         return;
            //     }

            //     $btn.prop('disabled', true);
            //     $btn.html('<i class="fas fa-spinner fa-spin"></i>');

            //     $.ajax({
            //         url: '{{ route('scm-profit-integration.retrigger') }}',
            //         type: 'POST',
            //         data: {
            //             _token: '{{ csrf_token() }}',
            //             booking_no: bookingNo
            //         },
            //         success: function(response) {
            //             if (response.success) {
            //                 alert('Successfully retriggered booking: ' + bookingNo);
            //                 table.ajax.reload(null, false);
            //             } else {
            //                 alert('Failed to retrigger: ' + (response.message ||
            //                     'Unknown error'));
            //             }
            //         },
            //         error: function(xhr) {
            //             alert('Error retriggering booking: ' + (xhr.responseJSON?.message ||
            //                 'Server error'));
            //         },
            //         complete: function() {
            //             $btn.prop('disabled', false);
            //             $btn.html('<i class="fas fa-sync-alt"></i>');
            //         }
            //     });
            // });

            // Update card statistics
            function updateCardStats() {
                $.ajax({
                    url: '{{ route('scm-profit-integration.stats') }}',
                    type: 'GET',
                    data: {
                        days: $('#dateFilter').val(),
                        start_date: $('#startDate').val(),
                        end_date: $('#endDate').val(),
                        office: $('#officeFilter').val()
                    },
                    success: function(response) {
                        animateValue('stat-total', parseInt($('#stat-total').text()), response.total,
                            500);
                        animateValue('stat-pending', parseInt($('#stat-pending').text()), response
                            .pending, 500);
                        animateValue('stat-success', parseInt($('#stat-success').text()), response
                            .success, 500);
                        animateValue('stat-failed', parseInt($('#stat-failed').text()), response.failed,
                            500);
                    },
                    error: function(xhr, error, code) {
                        console.error('Failed to update card statistics:', xhr, error, code);
                    }
                });
            }

            // Animate number changes
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

            // Card click filtering - Prevent on touch devices during scroll
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
                $('.card-filter-trigger').removeClass('active');
                $(this).addClass('active');
                currentCardFilter = filterType;
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
                } else if (selectedValue === '0' || selectedValue === '1') {
                    $('#customDateRange').hide();
                    $('#customDateRangeTo').hide();
                    $('#startDate').val('');
                    $('#endDate').val('');
                    updateCardStats();
                    table.ajax.reload();
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
                $('.card-filter-trigger').removeClass('active');
                $('.card-filter-trigger[data-filter="all"]').addClass('active');
                currentCardFilter = 'all';
                updateCardStats();
                table.ajax.reload();
                updateClearButtonVisibility();
            });
        });
    </script>
@stop
