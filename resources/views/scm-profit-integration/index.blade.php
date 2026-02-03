@extends('adminlte::page')

@section('title', 'SCM Profit Integration')

@section('content_header')
    <h1>SCM Profit Integration Dashboard</h1>
@stop

@section('content')
    <div class="container-fluid">

        {{-- Statistics Cards - Compact Version --}}
        <div class="row mb-3">
            {{-- 1. Total --}}
            <div class="col-xl col-lg-3 col-md-6 col-sm-6 col-12 mb-2">
                <div class="small-box bg-info card-filter-trigger compact-card" data-filter="all" style="cursor: pointer;"
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

            {{-- 2. Pending --}}
            <div class="col-xl col-lg-3 col-md-6 col-sm-6 col-12 mb-2">
                <div class="small-box bg-warning card-filter-trigger compact-card" data-filter="pending"
                    style="cursor: pointer;" title="Click to view pending records">
                    <div class="inner">
                        <h3 id="stat-pending">{{ $stats['pending'] }}</h3>
                        <p>Pending</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-clock"></i>
                    </div>
                </div>
            </div>

            {{-- 3. Success --}}
            <div class="col-xl col-lg-3 col-md-6 col-sm-6 col-12 mb-2">
                <div class="small-box bg-success card-filter-trigger compact-card" data-filter="success"
                    style="cursor: pointer;" title="Click to view successful records">
                    <div class="inner">
                        <h3 id="stat-success">{{ $stats['success'] }}</h3>
                        <p>Success</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
            </div>

            {{-- 4. Failed --}}
            <div class="col-xl col-lg-3 col-md-6 col-sm-6 col-12 mb-2">
                <div class="small-box bg-danger card-filter-trigger compact-card" data-filter="failed"
                    style="cursor: pointer;" title="Click to view failed records">
                    <div class="inner">
                        <h3 id="stat-failed">{{ $stats['failed'] }}</h3>
                        <p>Failed</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-times-circle"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- Filters and Table Card --}}
        <div class="card">

            </div>
            <div class="card-body">
                {{-- Filters Row --}}
                <div class="row align-items-center mb-3">
                    {{-- Left side - Show entries --}}
                    <div class="col-auto">
                        <div id="entriesContainer"></div>
                    </div>

                    {{-- Center - Date and Office Filters --}}
                    <div class="col">
                        <div class="row g-2">
                            {{-- Date Range Filter --}}
                            <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6">
                                <select id="dateFilter" class="form-control form-control-sm">
                                    <option value="0">Today</option>
                                    <option value="1" selected>Yesterday</option>
                                    <option value="15">Last 15 Days</option>
                                    <option value="30">Last 30 Days</option>
                                    <option value="custom">Custom Range</option>
                                </select>
                            </div>

                            {{-- Office Filter --}}
                            <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6">
                                <select id="officeFilter" class="form-control form-control-sm">
                                    <option value="">All Offices</option>
                                    @foreach ($offices as $office)
                                        <option value="{{ $office }}">{{ $office }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Clear Filters Button --}}
                            <div class="col-xl-auto col-lg-auto col-md-6 col-sm-6" id="clearFiltersContainer"
                                style="display: none;">
                                <button type="button" id="clearAllFilters" class="btn btn-outline-secondary btn-sm"
                                    title="Clear all filters">
                                    <i class="fas fa-redo"></i> Clear Filters
                                </button>
                            </div>

                            {{-- Custom Date Range - Start Date --}}
                            <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6" id="customDateRange" style="display: none;">
                                <input type="date" id="startDate" class="form-control form-control-sm"
                                    placeholder="Start Date">
                            </div>

                            {{-- Custom Date Range - End Date --}}
                            <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6" id="customDateRangeTo" style="display: none;">
                                <div class="input-group input-group-sm">
                                    <input type="date" id="endDate" class="form-control form-control-sm"
                                        placeholder="End Date">
                                    <button class="btn btn-outline-secondary btn-sm" type="button" id="clearCustomDates"
                                        title="Clear custom date range">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Right side - Search --}}
                    <div class="col-auto ms-auto">
                        <div id="searchContainer"></div>
                    </div>
                </div>

                {{-- DataTable --}}
                <div class="table-responsive">
                    <table id="scmProfitTable" class="table table-bordered table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Office</th>
                                <th>Client Name</th>
                                <th>Client ID</th>
                                <th>Sales Person</th>
                                <th>Origin</th>
                                <th>Destination</th>
                                <th>Status</th>
                                <th>Remark</th>
                                <th>Booking No</th>
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
        /* Compact card styling */
        .compact-card {
            min-height: 90px !important;
            margin-bottom: 0.5rem;
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

        .small-box {
            border-radius: 0.25rem;
            transition: all 0.3s ease;
        }

        .card-header {
            background-color: #17a2b8;
            color: white;
            padding: 0.75rem 1.25rem;
        }

        /* Card hover and active states */
        .card-filter-trigger:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .card-filter-trigger.active {
            box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.5), 0 0 0 6px rgba(0, 0, 0, 0.3);
            transform: scale(1.03);
        }

        /* Compact form controls */
        .form-control-sm {
            height: calc(1.5em + 0.5rem + 2px);
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }

        /* Custom filter row layout */
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
        }

        #searchContainer .dataTables_filter input {
            margin-left: 0;
            width: 200px;
        }

        /* Input group styling for date clear button */
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

        /* Clear All Filters button styling */
        #clearAllFilters {
            height: calc(1.5em + 0.5rem + 2px);
            white-space: nowrap;
        }

        #clearAllFilters:hover {
            background-color: #6c757d;
            color: white;
            border-color: #6c757d;
        }

        /* Responsive grid spacing */
        .g-2 {
            --bs-gutter-x: 0.5rem;
            --bs-gutter-y: 0.5rem;
        }

        /* Align items */
        .align-items-center {
            align-items: center !important;
        }

        .ms-auto {
            margin-left: auto !important;
        }

        /* Responsive adjustments for cards */
        @media (max-width: 576px) {
            .compact-card .icon {
                font-size: 40px !important;
            }

            .compact-card .inner h3 {
                font-size: 1.5rem;
            }

            .compact-card .inner p {
                font-size: 0.75rem;
            }
        }

        /* Extra large screens - 4 cards in one row */
        @media (min-width: 1200px) {
            .col-xl {
                flex: 0 0 25%;
                max-width: 25%;
            }
        }

        /* Responsive filter layout */
        @media (max-width: 991px) {
            #searchContainer .dataTables_filter input {
                width: 150px;
            }
        }

        @media (max-width: 767px) {
            .row.align-items-center.mb-3 {
                flex-direction: column;
                align-items: stretch !important;
            }

            .row.align-items-center.mb-3>.col-auto,
            .row.align-items-center.mb-3>.col {
                width: 100%;
                max-width: 100%;
                margin-left: 0 !important;
                margin-bottom: 0.5rem;
            }

            #searchContainer .dataTables_filter input {
                width: 100%;
            }

            #searchContainer .dataTables_filter label {
                width: 100%;
            }

            #clearAllFilters {
                width: 100%;
            }
        }

        /* Filter badge */
        .filter-badge {
            display: inline-block;
            margin-left: 10px;
            padding: 4px 8px;
            background-color: #ffc107;
            color: #212529;
            border-radius: 3px;
            font-size: 0.75rem;
            font-weight: normal;
        }

        /* DataTables info and pagination spacing */
        .dataTables_info,
        .dataTables_paginate {
            padding-top: 0.75rem;
        }

        /* Compact table header */
        .card-title {
            font-size: 1.1rem;
            margin-bottom: 0;
        }

        /* Hide default DataTables wrapper elements */
        .dataTables_wrapper>.row:first-child {
            display: none;
        }
    </style>
@stop

@section('js')
    <script>
        $(document).ready(function() {
            var currentCardFilter = 'all';

            // Initialize DataTable
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
                columns: [
                    { data: 'report_date', name: 'report_date', searchable: false },
                    { data: 'OfficeName', name: 'OfficeName' },
                    { data: 'ClientName', name: 'ClientName' },
                    { data: 'ClientID', name: 'ClientID' },
                    { data: 'SalesPerson', name: 'SalesPerson' },
                    { data: 'OriginCountry', name: 'OriginCountry' },
                    { data: 'DestinationCountry', name: 'DestinationCountry' },
                    { data: 'SCMStatus', name: 'SCMStatus' },
                    { data: 'StatusRemark', name: 'StatusRemark' },
                    { data: 'BookingNo', name: 'BookingNo' }
                ],
                order: [[0, 'desc']],
                pageLength: 25,
                responsive: true,
                dom: 'lfrtip',
                language: {
                    search: "Search:",
                    searchPlaceholder: "Client, Office, Status...",
                    lengthMenu: "Show _MENU_ entries"
                },
                initComplete: function() {
                    $('.dataTables_length').appendTo('#entriesContainer');
                    $('.dataTables_filter').appendTo('#searchContainer');
                }
            });

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
                        animateValue('stat-total', parseInt($('#stat-total').text()), response.total, 500);
                        animateValue('stat-pending', parseInt($('#stat-pending').text()), response.pending, 500);
                        animateValue('stat-success', parseInt($('#stat-success').text()), response.success, 500);
                        animateValue('stat-failed', parseInt($('#stat-failed').text()), response.failed, 500);
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

            // Card click filtering
            $('.card-filter-trigger').on('click', function() {
                var filterType = $(this).data('filter');
                $('.card-filter-trigger').removeClass('active');
                $(this).addClass('active');
                currentCardFilter = filterType;
                updateFilterIndicator(filterType);
                table.ajax.reload();
            });

            // Update filter indicator
            function updateFilterIndicator(filterType) {
                var filterText = '';
                var $indicator = $('#filterIndicator');

                switch (filterType) {
                    case 'all':
                        $indicator.hide();
                        return;
                    case 'pending':
                        filterText = 'Filtered: Pending';
                        break;
                    case 'success':
                        filterText = 'Filtered: Success';
                        break;
                    case 'failed':
                        filterText = 'Filtered: Failed';
                        break;
                }

                $indicator.text(filterText).show();
            }

            // Show/hide clear filters button
            function updateClearButtonVisibility() {
                var dateFilter = $('#dateFilter').val();
                var officeFilter = $('#officeFilter').val();

                if (dateFilter === 'custom') {
                    $('#clearFiltersContainer').hide();
                    return;
                }

                if (dateFilter !== '1' || officeFilter !== '') {
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
                $('#dateFilter').val('15');
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
                $('#dateFilter').val('1');
                $('#startDate').val('');
                $('#endDate').val('');
                $('#customDateRange').hide();
                $('#customDateRangeTo').hide();
                $('#officeFilter').val('');
                $('.card-filter-trigger').removeClass('active');
                currentCardFilter = 'all';
                $('#filterIndicator').hide();
                updateCardStats();
                table.ajax.reload();
                updateClearButtonVisibility();
            });
        });
    </script>
@stop