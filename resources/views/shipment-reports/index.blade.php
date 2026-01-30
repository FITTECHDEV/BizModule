@extends('adminlte::page')

@section('title', 'Shipment Email Reports')

@section('content_header')
    <h1>Shipment Email Reports Dashboard</h1>
@stop

@section('content')
    <div class="container-fluid">
        
        {{-- Statistics Cards - Compact Version --}}
        <div class="row mb-3">
            {{-- 1. Sent (Total Emails) --}}
            <div class="col-xl col-lg-4 col-md-4 col-sm-6 col-12 mb-2">
                <div class="small-box bg-info card-filter-trigger compact-card" data-filter="all" style="cursor: pointer;" title="Click to view all records">
                    <div class="inner">
                        <h3>{{ $stats['total_emails'] }}</h3>
                        <p>Total Sent</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-paper-plane"></i>
                    </div>
                </div>
            </div>

            {{-- 2. No ID Present --}}
            <div class="col-xl col-lg-4 col-md-4 col-sm-6 col-12 mb-2">
                <div class="small-box bg-warning card-filter-trigger compact-card" data-filter="no_email" style="cursor: pointer;" title="Click to view records with no email">
                    <div class="inner">
                        <h3>{{ $stats['no_email'] }}</h3>
                        <p>No ID Present</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-envelope-open-text"></i>
                    </div>
                </div>
            </div>

            {{-- 3. No Milestones --}}
            <div class="col-xl col-lg-4 col-md-4 col-sm-6 col-12 mb-2">
                <div class="small-box bg-danger card-filter-trigger compact-card" data-filter="milestone_failures" style="cursor: pointer;" title="Click to view milestone failures">
                    <div class="inner">
                        <h3>{{ $stats['milestone_failures'] }}</h3>
                        <p>No Milestones</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                </div>
            </div>

            {{-- 4. Not Updated (Failed to send) --}}
            <div class="col-xl col-lg-4 col-md-4 col-sm-6 col-12 mb-2">
                <div class="small-box bg-secondary card-filter-trigger compact-card" data-filter="failed" style="cursor: pointer;" title="Click to view failed emails">
                    <div class="inner">
                        <h3>{{ $stats['emails_failed'] }}</h3>
                        <p>Not Updated</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-times-circle"></i>
                    </div>
                </div>
            </div>

            {{-- 5. Email Sent Successfully --}}
            <div class="col-xl col-lg-4 col-md-4 col-sm-6 col-12 mb-2">
                <div class="small-box bg-success card-filter-trigger compact-card" data-filter="success" style="cursor: pointer;" title="Click to view successful emails">
                    <div class="inner">
                        <h3>{{ $stats['emails_sent'] }}</h3>
                        <p>Email Sent</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- Filters and Table Card --}}
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    Email Report History
                    <span id="filterIndicator" class="filter-badge" style="display: none;"></span>
                </h3>
            </div>
            <div class="card-body">
                {{-- Filters Row - Inline with DataTables Controls --}}
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
                                    <option value="15" selected>Last 15 Days</option>
                                    <option value="30">Last 30 Days</option>
                                    <option value="custom">Custom Range</option>
                                </select>
                            </div>

                            {{-- Office Filter --}}
                            <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6">
                                <select id="officeFilter" class="form-control form-control-sm">
                                    <option value="">All Offices</option>
                                    @foreach($offices as $office)
                                        <option value="{{ $office }}">{{ $office }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Custom Date Range - Start Date --}}
                            <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6" id="customDateRange" style="display: none;">
                                <input type="date" id="startDate" class="form-control form-control-sm" placeholder="Start Date">
                            </div>

                            {{-- Custom Date Range - End Date --}}
                            <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6" id="customDateRangeTo" style="display: none;">
                                <div class="input-group input-group-sm">
                                    <input type="date" id="endDate" class="form-control form-control-sm" placeholder="End Date">
                                    <button class="btn btn-outline-secondary btn-sm" type="button" id="clearCustomDates" title="Clear custom date range">
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
                    <table id="shipmentReportsTable" class="table table-bordered table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Office</th>
                                <th>Client Name</th>
                                <th>Service</th>
                                <th>No. of Shipments</th>
                                <th>Status</th>
                                <th>Remark</th>
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
            background-color: #6c757d;
            color: white;
            padding: 0.75rem 1.25rem;
        }
        
        /* Card hover and active states */
        .card-filter-trigger:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        
        .card-filter-trigger.active {
            box-shadow: 0 0 0 3px rgba(255,255,255,0.5), 0 0 0 6px rgba(0,0,0,0.3);
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
        
        /* Extra large screens - 5 cards in one row */
        @media (min-width: 1200px) {
            .col-xl {
                flex: 0 0 20%;
                max-width: 20%;
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
            
            .row.align-items-center.mb-3 > .col-auto,
            .row.align-items-center.mb-3 > .col {
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
        }
        
        /* Filter badge */
        .filter-badge {
            display: inline-block;
            margin-left: 10px;
            padding: 4px 8px;
            background-color: #17a2b8;
            color: white;
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
        
        /* Hide default DataTables wrapper elements since we're using custom containers */
        .dataTables_wrapper > .row:first-child {
            display: none;
        }
    </style>
@stop

@section('js')
    <script>
        $(document).ready(function() {
            // Track current card filter
            var currentCardFilter = 'all';
            
            // Initialize DataTable
            var table = $('#shipmentReportsTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route("shipment-reports.data") }}',
                    type: 'GET',
                    data: function(d) {
                        d.days = $('#dateFilter').val();
                        d.start_date = $('#startDate').val();
                        d.end_date = $('#endDate').val();
                        d.office = $('#officeFilter').val();
                        d.card_filter = currentCardFilter;
                    },
                    error: function(xhr, error, code) {
                        console.error('DataTables Ajax Error:', {
                            status: xhr.status,
                            statusText: xhr.statusText,
                            responseText: xhr.responseText,
                            error: error,
                            code: code
                        });
                        alert('Error loading data. Please check the console for details.');
                    }
                },
                columns: [
                    { data: 'report_date', name: 'report_date', searchable: false },
                    { data: 'OfficeName', name: 'OfficeName' },
                    { data: 'ClientName', name: 'ClientName' },
                    { data: 'ServiceName', name: 'ServiceName' },
                    { data: 'shipment_count', name: 'shipment_count', searchable: false },
                    { data: 'Status', name: 'Status' },
                    { data: 'SendRemark', name: 'SendRemark' }
                ],
                order: [[0, 'desc']],
                pageLength: 25,
                responsive: true,
                dom: 'lfrtip',
                language: {
                    search: "Search:",
                    searchPlaceholder: "Client, Service, Status...",
                    lengthMenu: "Show _MENU_ entries"
                },
                initComplete: function() {
                    // Move length menu to custom container
                    $('.dataTables_length').appendTo('#entriesContainer');
                    
                    // Move search box to custom container
                    $('.dataTables_filter').appendTo('#searchContainer');
                }
            });

            // Card click filtering
            $('.card-filter-trigger').on('click', function() {
                var filterType = $(this).data('filter');
                
                // Remove active class from all cards
                $('.card-filter-trigger').removeClass('active');
                
                // Add active class to clicked card
                $(this).addClass('active');
                
                // Update current filter
                currentCardFilter = filterType;
                
                // Update filter indicator
                updateFilterIndicator(filterType);
                
                // Reload table with new filter
                table.ajax.reload();
            });

            // Function to update filter indicator
            function updateFilterIndicator(filterType) {
                var filterText = '';
                var $indicator = $('#filterIndicator');
                
                switch(filterType) {
                    case 'all':
                        $indicator.hide();
                        return;
                    case 'no_email':
                        filterText = 'Filtered: No Email ID Present';
                        break;
                    case 'milestone_failures':
                        filterText = 'Filtered: Milestone Failures';
                        break;
                    case 'failed':
                        filterText = 'Filtered: Failed Emails';
                        break;
                    case 'success':
                        filterText = 'Filtered: Successful Emails';
                        break;
                }
                
                $indicator.text(filterText).show();
            }

            // Date filter change
            $('#dateFilter').on('change', function() {
                var selectedValue = $(this).val();
                
                if (selectedValue === 'custom') {
                    // Show custom date range inputs
                    $('#customDateRange').show();
                    $('#customDateRangeTo').show();
                } else {
                    // Hide custom date range inputs and clear values
                    $('#customDateRange').hide();
                    $('#customDateRangeTo').hide();
                    $('#startDate').val('');
                    $('#endDate').val('');
                    
                    // Reload table with selected preset range
                    table.ajax.reload();
                }
            });

            // Custom date range change
            $('#startDate, #endDate').on('change', function() {
                // Only reload if both dates are selected
                if ($('#startDate').val() && $('#endDate').val()) {
                    // Validate that end date is after start date
                    var startDate = new Date($('#startDate').val());
                    var endDate = new Date($('#endDate').val());
                    
                    if (endDate < startDate) {
                        alert('End date must be after start date');
                        $('#endDate').val('');
                        return;
                    }
                    
                    table.ajax.reload();
                }
            });

            // Clear custom date range button
            $('#clearCustomDates').on('click', function() {
                // Clear the date inputs
                $('#startDate').val('');
                $('#endDate').val('');
                
                // Hide custom date inputs
                $('#customDateRange').hide();
                $('#customDateRangeTo').hide();
                
                // Reset date filter to default (15 days)
                $('#dateFilter').val('15');
                
                // Reload table with default filter
                table.ajax.reload();
            });

            // Office filter change
            $('#officeFilter').on('change', function() {
                table.ajax.reload();
            });
        });
    </script>
@stop