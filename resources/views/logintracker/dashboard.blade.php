{{-- @extends('logintracker.layouts.app') --}}
@extends('adminlte::page')

@section('content')
    <div class="container mt-4">
        <h1 class="mb-4">Login Tracker Dashboard</h1>

        <div class="mb-3">
            <a href="{{ route('imports.index') }}" class="btn btn-primary">Import Data</a>
            <a href="{{ route('activity.report') }}" class="btn btn-info">Activity Report</a>
        </div>

        @include('logintracker.partials.alerts')

        {{-- Filters --}}
        <div class="row g-2 mb-4" id="filter-form">
            <div class="col-md-2">
                <label for="range" class="form-label">Date Range:</label>
                <select name="range" id="range" class="form-select">
                    @php
                        $selectedRange = request('range', 'this_month');
                    @endphp
                    <option value="this_month" {{ $selectedRange == 'this_month' ? 'selected' : '' }}>This Month</option>
                    <option value="last_month" {{ $selectedRange == 'last_month' ? 'selected' : '' }}>Last Month</option>
                    <option value="last_3_months" {{ $selectedRange == 'last_3_months' ? 'selected' : '' }}>Last 3 Months
                    </option>
                    <option value="custom" {{ $selectedRange == 'custom' ? 'selected' : '' }}>Custom Date</option>
                </select>
            </div>

            <div class="col-md-2">
                <label for="system" class="form-label">System:</label>
                <select name="system" id="system" class="form-select">
                    @php
                        $selectedSystem = request('system', 'all');
                    @endphp
                    <!-- <option value="all" {{ $selectedSystem == 'all' ? 'selected' : '' }}>All Systems</option> -->
                    @foreach ($systems ?? [] as $sys)
                        <option value="{{ $sys }}" {{ $selectedSystem == $sys ? 'selected' : '' }}>
                            {{ $sys }}</option>
                    @endforeach
                </select>
            </div>

            {{-- NEW: Calendar Date Picker --}}
            <div class="col-md-2" id="customDateDiv" style="{{ $selectedRange == 'custom' ? '' : 'display: none;' }}">
                <label for="date" class="form-label">Select Date:</label>
                <input type="date" name="date" id="date" class="form-control"
                    value="{{ request('date') ?? ($selectedDate ?? '') }}">
            </div>

            <div class="col-md-3">
                <label for="search" class="form-label">Search:</label>
                <input type="text" name="search" id="search" class="form-control" placeholder="Name or UPN"
                    value="{{ request('search') }}">
            </div>

            <div class="col-md-3 d-flex align-items-end">
                <button type="button" class="btn btn-primary me-2" id="apply">Apply</button>
                <a href="{{ route('dashboard') }}" class="btn btn-secondary">Reset</a>
            </div>
        </div>
        {{-- Filter Summary --}}
        <div id="filter-summary" class="alert alert-light" style="display: none;">
            <strong>Filters:</strong>
            <span id="range-label" class="badge bg-secondary me-1"></span>
            <span id="system-label" class="badge bg-info me-1"></span>
            <span id="search-label" class="badge bg-warning text-dark"></span>
        </div>

        {{-- Loading Indicator --}}
        <div id="loading" class="alert alert-info" style="display: none;">
            Loading data, please wait...
        </div>

        {{-- Summary Cards --}}
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card bg-info text-white shadow">
                    <div class="card-body">
                        <h5 class="card-title">Total Users</h5>
                        <p class="display-5" id="total-users">{{ $totalUsers ?? 0 }}</p>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <a href="{{ route('reporting.users.logged-in') }}" class="text-decoration-none" id="logged-in-link">
                    <div class="card bg-success text-white shadow card-hover">
                        <div class="card-body">
                            <h5 class="card-title">Logged In Users</h5>
                            <p class="display-5" id="logged-in-count">{{ $loggedInCount ?? 0 }}</p>
                            <small class="opacity-75">Click to view details</small>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-md-4">
                <a href="{{ route('reporting.users.not-logged-in') }}" class="text-decoration-none"
                    id="not-logged-in-link">
                    <div class="card bg-danger text-white shadow card-hover">
                        <div class="card-body">
                            <h5 class="card-title">Not Logged In Users</h5>
                            <p class="display-5" id="not-logged-in-count">{{ $notLoggedInCount ?? 0 }}</p>
                            <small class="opacity-75">Click to view details</small>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        {{-- User Table --}}
        <div id="user-table">
            @if ($users->isEmpty())
                <div class="alert alert-info">No users found for the selected filters.</div>
            @else
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Missed Days</th>
                                <th>Last Login</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="user-table-body">
                            @foreach ($users as $user)
                                @php
                                    // Calculate missed days based on sign-ins
                                    $userSignIns = $user->signIns ?? collect();
                                    $uniqueLoginDays = $userSignIns
                                        ->pluck('date_utc')
                                        ->map(fn($d) => \Carbon\Carbon::parse($d)->format('Y-m-d'))
                                        ->unique()
                                        ->count();
                                    $missedDays = max(0, ($totalDays ?? 30) - $uniqueLoginDays);

                                    // Use userPrincipalName as ID since regular ID might be null
                                    $userId = $user->id ?? $user->userPrincipalName;
                                @endphp


                                

                                <tr @if ($uniqueLoginDays === 0) class="table-danger" @endif>
                                    <td>{{ $user->displayName ?? 'Unknown' }}</td>
                                    <td>{{ $user->userPrincipalName ?? 'N/A' }}</td>
                                    <td>{{ $missedDays }}</td>
                                    <td>
                                        @if ($userSignIns->isNotEmpty())
                                            {{ $userSignIns->first()->date_utc->format('Y-m-d H:i') }}
                                        @else
                                            <span class="text-muted">Never</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($userId)
                                            <a href="{{ route('reporting.users.show', ['user' => $userId]) }}"
                                                class="btn btn-sm btn-primary">
                                                <i class="fas fa-eye"></i> View
                                            </a>
                                        @else
                                            <span class="text-muted">No ID available</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>


               

                {{-- Pagination --}}
                <div class="mt-3" id="pagination-container">
                    {{ $users->withQueryString()->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection

@section('css')
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"
        integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg=="
        crossorigin="anonymous">
    <style>
        /* CRITICAL: Force remove ALL arrow elements and SVGs */
        .pagination svg,
        .logintracker-pagination svg,
        svg.arrow,
        svg.pagination-arrow,
        .pagination-arrow,
        .arrow-left,
        .arrow-right,
        .arrow-container,
        .pagination-large-arrow {
            display: none !important;
            width: 0 !important;
            height: 0 !important;
            visibility: hidden !important;
        }

        /* Reset any potential SVG containers */
        .pagination *:has(svg),
        .logintracker-pagination *:has(svg) {
            svg {
                display: none !important;
            }
        }

        /* Main pagination container */
        .logintracker-pagination,
        .pagination,
        #pagination-container nav,
        #pagination-container .pagination {
            display: flex !important;
            justify-content: center !important;
            align-items: center !important;
            gap: 0.5rem !important;
            margin: 1rem 0 !important;
            font-size: 0.875rem !important;
            flex-wrap: wrap !important;
            padding: 0 !important;
            list-style: none !important;
        }

        /* Reset all pagination items to consistent sizing */
        .logintracker-pagination a,
        .logintracker-pagination span,
        .logintracker-pagination button,
        .pagination a,
        .pagination span,
        .pagination button,
        .page-link,
        #pagination-container a,
        #pagination-container span,
        #pagination-container button {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            min-width: 32px !important;
            max-width: auto !important;
            height: 32px !important;
            padding: 0 0.5rem !important;
            margin: 0 2px !important;
            font-size: 0.875rem !important;
            font-weight: 500 !important;
            line-height: 1 !important;
            color: #4b5563 !important;
            background-color: #ffffff !important;
            border: 1px solid #d1d5db !important;
            border-radius: 6px !important;
            text-decoration: none !important;
            transition: all 0.15s ease !important;
            cursor: pointer !important;
            vertical-align: middle !important;
            overflow: hidden !important;
            white-space: nowrap !important;
        }

        /* Hover state */
        .logintracker-pagination a:hover:not(.disabled):not(.active),
        .pagination a:hover:not(.disabled):not(.active),
        .page-link:hover:not(.disabled):not(.active),
        #pagination-container a:hover:not(.disabled):not(.active) {
            background-color: #f3f4f6 !important;
            border-color: #9ca3af !important;
            color: #1f2937 !important;
        }

        /* Active/Current page */
        .logintracker-pagination .active,
        .logintracker-pagination .current,
        .pagination .active,
        .pagination .current,
        .page-item.active .page-link,
        #pagination-container .active,
        #pagination-container .current {
            background-color: #3b82f6 !important;
            border-color: #3b82f6 !important;
            color: #ffffff !important;
            font-weight: 600 !important;
        }

        /* Disabled state */
        .logintracker-pagination .disabled,
        .logintracker-pagination [disabled],
        .pagination .disabled,
        .pagination [disabled],
        .page-item.disabled .page-link,
        #pagination-container .disabled,
        #pagination-container [disabled] {
            opacity: 0.5 !important;
            cursor: not-allowed !important;
            background-color: #f9fafb !important;
            color: #9ca3af !important;
            pointer-events: none !important;
        }

        /* Previous/Next buttons with text arrows only */
        .logintracker-pagination .prev,
        .logintracker-pagination .next,
        .logintracker-pagination [rel="prev"],
        .logintracker-pagination [rel="next"],
        .pagination .prev,
        .pagination .next,
        .pagination [rel="prev"],
        .pagination [rel="next"],
        #pagination-container [rel="prev"],
        #pagination-container [rel="next"] {
            padding: 0 0.75rem !important;
            font-size: 0.875rem !important;
        }

        /* Text-based arrows using ::before and ::after */
        .logintracker-pagination .prev::before,
        .logintracker-pagination [rel="prev"]::before,
        .pagination .prev::before,
        .pagination [rel="prev"]::before,
        #pagination-container [rel="prev"]::before {
            content: "←" !important;
            margin-right: 0.25rem !important;
            font-size: 1rem !important;
            line-height: 1 !important;
            display: inline-block !important;
        }

        .logintracker-pagination .next::after,
        .logintracker-pagination [rel="next"]::after,
        .pagination .next::after,
        .pagination [rel="next"]::after,
        #pagination-container [rel="next"]::after {
            content: "→" !important;
            margin-left: 0.25rem !important;
            font-size: 1rem !important;
            line-height: 1 !important;
            display: inline-block !important;
        }

        /* Ellipsis */
        .logintracker-pagination .dots,
        .logintracker-pagination .ellipsis,
        .pagination .dots,
        .pagination .ellipsis,
        #pagination-container .dots,
        #pagination-container .ellipsis {
            border: none !important;
            background: none !important;
            cursor: default !important;
            padding: 0 0.25rem !important;
        }

        /* Page items container */
        .pagination li,
        .page-item {
            margin: 0 2px !important;
            display: inline-block !important;
        }

        /* Container specific overrides */
        #pagination-container {
            display: flex !important;
            flex-direction: column !important;
            align-items: center !important;
            gap: 0.5rem !important;
            margin: 1rem 0 !important;
        }

        /* Force inline-flex for all direct children */
        #pagination-container>*,
        .logintracker-pagination>*,
        .pagination>* {
            display: inline-flex !important;
            vertical-align: middle !important;
        }

        /* Mobile responsive */
        @media (max-width: 640px) {

            .logintracker-pagination,
            .pagination,
            #pagination-container nav {
                font-size: 0.75rem !important;
                gap: 0.25rem !important;
            }

            .logintracker-pagination a,
            .logintracker-pagination span,
            .logintracker-pagination button,
            .pagination a,
            .pagination span,
            .pagination button,
            .page-link,
            #pagination-container a,
            #pagination-container span,
            #pagination-container button {
                min-width: 28px !important;
                height: 28px !important;
                padding: 0 0.375rem !important;
                font-size: 0.75rem !important;
                margin: 0 1px !important;
            }
        }

        /* Extra specificity to override any framework styles */
        body #pagination-container svg,
        body .logintracker-pagination svg,
        body .pagination svg {
            display: none !important;
            max-width: 0 !important;
            max-height: 0 !important;
        }
    </style>
@endsection
@section('js')
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Bootstrap Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
    </script>
    <script>
        $(document).ready(function() {
            // Define the base URL for the users.show route
            const userShowBaseUrl = '{{ route('reporting.users.show', ['user' => ':id']) }}'.replace(':id', '');

            // Enhanced system label mapping for better display
            const systemDisplayLabels = {
                'all': 'All Systems',
                'this_month': 'This Month',
                'last_month': 'Last Month',
                'last_3_months': 'Last 3 Months',
                'last_6_months': 'Last 6 Months',
                'last_year': 'Last Year',
                'all_time': 'All Time',
                'custom': 'Custom Date'
            };

            // Calendar functionality
            const rangeSelect = $('#range');
            const customDateDiv = $('#customDateDiv');
            const dateInput = $('#date');

            // Handle range selection change
            rangeSelect.on('change', function() {
                const selectedRange = $(this).val();

                if (selectedRange === 'custom') {
                    customDateDiv.show();
                    dateInput.attr('required', 'required');
                    // Don't auto-update when switching to custom - wait for date selection
                } else {
                    customDateDiv.hide();
                    dateInput.removeAttr('required');
                    dateInput.val('');
                    // Auto-update for predefined ranges
                    updateDashboard();
                }
            });

            // Handle date input change for custom date
            dateInput.on('change', function() {
                if (rangeSelect.val() === 'custom' && $(this).val()) {
                    updateDashboard();
                }
            });

            // Add cleanup function for pagination arrows
            function cleanupPaginationArrows() {
                // Remove any SVG elements in pagination
                $('#pagination-container svg, .pagination svg, .logintracker-pagination svg').remove();

                // Remove any elements with arrow classes
                $('.pagination-arrow, .arrow-left, .arrow-right, .arrow-container, .pagination-large-arrow')
                    .remove();

                // Force consistent styling on pagination elements
                $('#pagination-container a, #pagination-container span, #pagination-container button').each(
                    function() {
                        $(this).find('svg').remove(); // Remove any nested SVGs

                        // Apply consistent dimensions
                        $(this).css({
                            'display': 'inline-flex',
                            'align-items': 'center',
                            'justify-content': 'center',
                            'min-width': '32px',
                            'height': '32px',
                            'padding': '0 0.5rem',
                            'margin': '0 2px',
                            'font-size': '0.875rem',
                            'line-height': '1',
                            'vertical-align': 'middle',
                            'overflow': 'hidden'
                        });

                        // Ensure active state styling
                        if ($(this).hasClass('active') || $(this).hasClass('current')) {
                            $(this).css({
                                'background-color': '#3b82f6',
                                'border-color': '#3b82f6',
                                'color': '#ffffff'
                            });
                        }
                    });
            }

            $('#apply').on('click', function(e) {
                e.preventDefault();
                updateDashboard();
            });

            $('#system, #range').on('change', function() {
                // Range change is handled by the calendar functionality above
                if ($(this).attr('id') === 'system') {
                    updateDashboard();
                }
            });

            let searchTimeout;
            $('#search').on('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(updateDashboard, 500);
            });

            function updateDashboard() {
                var range = $('#range').val() || 'last_3_months';
                var system = $('#system').val() || 'all';
                var search = encodeURIComponent($('#search').val() || '');
                var date = $('#date').val() || ''; // Add date parameter

                // Enhanced label display using mapping
                var rangeLabel = systemDisplayLabels[range] || range;
                var systemLabel = system === 'all' ? 'All Systems' : system;

                console.log('Enhanced AJAX request:', {
                    range,
                    system,
                    search,
                    date,
                    rangeLabel,
                    systemLabel
                });

                $('#loading').show();
                $('#user-table').html('<div class="alert alert-info">Loading...</div>');

                $.ajax({
                    url: '{{ route('dashboard') }}',
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: {
                        range,
                        system,
                        search,
                        date
                    }, // Include date parameter
                    success: function(response) {
                        console.log('Enhanced AJAX Response:', response);
                        $('#loading').hide();

                        // Update summary cards with enhanced error handling
                        $('#total-users').text(response.totalUsers || 0);
                        $('#logged-in-count').text(response.loggedInCount || 0);
                        $('#not-logged-in-count').text(response.notLoggedInCount || 0);

                        // Update filter links with proper query parameters
                        const queryParams = $.param({
                            range,
                            system,
                            search,
                            date
                        });
                        $('#logged-in-link').attr('href', '{{ route('reporting.users.logged-in') }}?' +
                            queryParams);
                        $('#not-logged-in-link').attr('href',
                            '{{ route('reporting.users.not-logged-in') }}?' + queryParams);

                        // Enhanced filter label display with custom date support
                        if (range === 'custom' && date) {
                            $('#range-label').text(rangeLabel + ': ' + date).show();
                        } else {
                            $('#range-label').text(rangeLabel).toggle(range !== 'last_3_months');
                        }
                        $('#system-label').text(systemLabel).toggle(system !== 'all');
                        $('#search-label').text('Search: ' + (search ? decodeURIComponent(search) : ''))
                            .toggle(!!search);

                        // Show filter summary if any non-default filters are active
                        var hasActiveFilters = range !== 'last_3_months' || system !== 'all' || !!
                            search || (range === 'custom' && !!date);
                        $('#filter-summary').toggle(hasActiveFilters);

                        // Enhanced users data handling with better error checking
                        var usersData = response.users || [];

                        if (usersData && usersData.length > 0) {
                            var tableHtml = `
                            <div class="table-responsive">
                                <table class="table table-striped table-hover align-middle">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Missed Days</th>
                                            <th>Last Login</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="user-table-body">
                                    </tbody>
                                </table>
                            </div>
                            <div class="mt-3" id="pagination-container">
                            </div>
                        `;
                            $('#user-table').html(tableHtml);

                            // Enhanced table row population with better data validation
                            usersData.forEach(function(user) {
                                var userSignIns = user.signIns || [];
                                var uniqueLoginDays = 0;

                                if (userSignIns.length > 0) {
                                    var uniqueDates = userSignIns.map(d => {
                                        return new Date(d.date_utc)
                                            .toLocaleDateString();
                                    }).filter((d, i, arr) => arr.indexOf(d) === i);
                                    uniqueLoginDays = uniqueDates.length;
                                }

                                var missedDays = Math.max(0, (response.totalDays || 30) -
                                    uniqueLoginDays);
                                var rowClass = uniqueLoginDays === 0 ? 'table-danger' : '';
                                var lastLogin = 'Never';

                                if (userSignIns.length > 0) {
                                    try {
                                        lastLogin = new Date(userSignIns[0].date_utc)
                                            .toLocaleString();
                                    } catch (e) {
                                        console.warn('Date parsing error for user:', user.id,
                                            e);
                                        lastLogin = 'Invalid Date';
                                    }
                                }

                                // Enhanced user data validation
                                var userId = user.id || user.userPrincipalName;
                                var displayName = user.displayName || 'Unknown';
                                var email = user.userPrincipalName || user.mail || 'N/A';

                                var userRow = `
                                <tr class="${rowClass}">
                                    <td>${displayName}</td>
                                    <td>${email}</td>
                                    <td>${missedDays}</td>
                                    <td>${lastLogin}</td>
                                    <td>
                                        ${userId ?
                                            `<a href="${userShowBaseUrl}${encodeURIComponent(userId)}" class="btn btn-sm btn-primary">
                                                            <i class="fas fa-eye"></i> View
                                                        </a>` :
                                            '<span class="text-muted">No ID available</span>'
                                        }
                                    </td>
                                </tr>
                            `;
                                $('#user-table-body').append(userRow);
                            });

                            // Enhanced pagination handling
                            if (response.pagination) {
                                $('#pagination-container').html(response.pagination);
                                setTimeout(function() {
                                    cleanupPaginationArrows();
                                    bindPaginationEvents();
                                }, 10);
                            }
                        } else {
                            // Enhanced no-data message with system-specific context
                            var noDataMessage = system !== 'all' ?
                                `No users found for system "${systemLabel}" in the selected date range.` :
                                'No users found for the selected filters.';

                            if (range === 'custom' && date) {
                                noDataMessage += ` (Date: ${date})`;
                            }

                            $('#user-table').html(
                                `<div class="alert alert-info">${noDataMessage}</div>`
                            );
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Enhanced AJAX Error:', status, error, xhr.responseText);
                        $('#loading').hide();
                        $('#total-users').text('0');
                        $('#logged-in-count').text('0');
                        $('#not-logged-in-count').text('0');

                        // Enhanced error message with system context
                        var errorMessage = 'An error occurred while loading data.';
                        if (xhr.status === 404) {
                            errorMessage = 'Dashboard endpoint not found. Please check your routes.';
                        } else if (xhr.status === 500) {
                            errorMessage =
                                'Server error occurred. Please check the logs and try again.';
                        } else if (xhr.status === 422) {
                            errorMessage = 'Invalid filter parameters. Please reset and try again.';
                        }

                        $('#user-table').html(
                            `<div class="alert alert-danger">${errorMessage}</div>`
                        );
                    }
                });
            }

            function bindPaginationEvents() {
                cleanupPaginationArrows();

                $('#pagination-container a').off('click').on('click', function(e) {
                    e.preventDefault();
                    var url = $(this).attr('href');
                    if (!url || url === '#') return;

                    var urlParams = new URLSearchParams(url.split('?')[1]);
                    var page = urlParams.get('page');

                    $('#loading').show();

                    $.ajax({
                        url: '{{ route('dashboard') }}',
                        method: 'GET',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        data: {
                            range: $('#range').val(),
                            system: $('#system').val(),
                            search: $('#search').val(),
                            date: $('#date').val(), // Include date in pagination
                            page: page
                        },
                        success: function(response) {
                            $('#loading').hide();
                            updateTableContent(response);
                        },
                        error: function(xhr, status, error) {
                            console.error('Pagination AJAX Error:', status, error, xhr
                                .responseText);
                            $('#loading').hide();
                            $('#user-table').html(
                                '<div class="alert alert-danger">An error occurred while loading pagination data. Please try again.</div>'
                            );
                        }
                    });
                });
            }

            function updateTableContent(response) {
                // Update summary cards
                $('#total-users').text(response.totalUsers || 0);
                $('#logged-in-count').text(response.loggedInCount || 0);
                $('#not-logged-in-count').text(response.notLoggedInCount || 0);

                var usersData = response.users || [];
                var tbody = $('#user-table-body');
                tbody.empty();

                if (usersData && usersData.length > 0) {
                    usersData.forEach(function(user) {
                        var userSignIns = user.signIns || [];
                        var uniqueLoginDays = 0;

                        if (userSignIns.length > 0) {
                            var uniqueDates = userSignIns.map(d => {
                                return new Date(d.date_utc).toLocaleDateString();
                            }).filter((d, i, arr) => arr.indexOf(d) === i);
                            uniqueLoginDays = uniqueDates.length;
                        }

                        var missedDays = Math.max(0, (response.totalDays || 30) - uniqueLoginDays);
                        var rowClass = uniqueLoginDays === 0 ? 'table-danger' : '';
                        var lastLogin = 'Never';

                        if (userSignIns.length > 0) {
                            try {
                                lastLogin = new Date(userSignIns[0].date_utc).toLocaleString();
                            } catch (e) {
                                console.warn('Date parsing error for user:', user.id, e);
                                lastLogin = 'Invalid Date';
                            }
                        }

                        var userId = user.id || user.userPrincipalName;
                        var displayName = user.displayName || 'Unknown';
                        var email = user.userPrincipalName || user.mail || 'N/A';

                        var userRow = `
                        <tr class="${rowClass}">
                            <td>${displayName}</td>
                            <td>${email}</td>
                            <td>${missedDays}</td>
                            <td>${lastLogin}</td>
                            <td>
                                ${userId ?
                                    `<a href="${userShowBaseUrl}${encodeURIComponent(userId)}" class="btn btn-sm btn-primary">
                                                    <i class="fas fa-eye"></i> View
                                                </a>` :
                                    '<span class="text-muted">No ID available</span>'
                                }
                            </td>
                        </tr>
                    `;
                        tbody.append(userRow);
                    });

                    // Update pagination
                    if (response.pagination) {
                        $('#pagination-container').html(response.pagination);
                        setTimeout(function() {
                            cleanupPaginationArrows();
                            bindPaginationEvents();
                        }, 10);
                    }
                } else {
                    var system = $('#system').val();
                    var range = $('#range').val();
                    var date = $('#date').val();
                    var systemLabel = system === 'all' ? 'All Systems' : system;
                    var noDataMessage = system !== 'all' ?
                        `No users found for system "${systemLabel}" in the selected date range.` :
                        'No users found for the selected filters.';

                    if (range === 'custom' && date) {
                        noDataMessage += ` (Date: ${date})`;
                    }

                    $('#user-table').html(
                        `<div class="alert alert-info">${noDataMessage}</div>`
                    );
                }
            }

            // Enhanced MutationObserver for pagination cleanup
            const paginationObserver = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    if (mutation.addedNodes.length) {
                        mutation.addedNodes.forEach(function(node) {
                            if (node.nodeType === 1 && (
                                    node.classList?.contains('pagination') ||
                                    node.querySelector?.('.pagination') ||
                                    node.querySelector?.('svg')
                                )) {
                                setTimeout(cleanupPaginationArrows, 5);
                            }
                        });
                    }
                });
            });

            // Observe the pagination container for changes
            const paginationContainer = document.getElementById('pagination-container');
            if (paginationContainer) {
                paginationObserver.observe(paginationContainer, {
                    childList: true,
                    subtree: true
                });
            }

            // Initial cleanup and setup on page load
            setTimeout(function() {
                cleanupPaginationArrows();
                bindPaginationEvents();
            }, 100);

            // Enhanced error boundary for AJAX failures
            $(document).ajaxError(function(event, jqXHR, ajaxSettings, thrownError) {
                console.error('Global AJAX Error Handler:', {
                    url: ajaxSettings.url,
                    status: jqXHR.status,
                    statusText: jqXHR.statusText,
                    responseText: jqXHR.responseText,
                    thrownError: thrownError
                });

                // Only handle dashboard-related AJAX errors
                if (ajaxSettings.url && ajaxSettings.url.includes('dashboard')) {
                    $('#loading').hide();

                    if (!$('#user-table .alert-danger').length) {
                        $('#user-table').html(
                            '<div class="alert alert-danger">Network error occurred. Please refresh the page and try again.</div>'
                        );
                    }
                }
            });

            // Enhanced form validation
            $('#search').on('input', function() {
                var searchValue = $(this).val();
                if (searchValue.length > 255) {
                    $(this).val(searchValue.substring(0, 255));
                    $(this).addClass('is-invalid');
                    setTimeout(() => $(this).removeClass('is-invalid'), 2000);
                }
            });

            // Enhanced keyboard shortcuts
            $(document).on('keydown', function(e) {
                // Ctrl+R or F5 for refresh (prevent default and trigger update instead)
                if ((e.ctrlKey && e.key === 'r') || e.key === 'F5') {
                    e.preventDefault();
                    updateDashboard();
                    return false;
                }     

                // Enter key in search field
                if (e.key === 'Enter' && $('#search').is(':focus')) {
                    e.preventDefault();
                    updateDashboard();
                    return false;
                }
            });

            // Enhanced accessibility improvements
            $('#apply').attr('aria-label', 'Apply selected filters to update dashboard data');
            $('#system').attr('aria-label', 'Select system to filter users');
            $('#range').attr('aria-label', 'Select date range for filtering');
            $('#search').attr('aria-label', 'Search users by name or email');
            $('#date').attr('aria-label', 'Select custom date for filtering');

            // Performance monitoring (development only)
            if (typeof console !== 'undefined' && console.time) {
                var performanceLog = {
                    dashboardUpdates: 0,
                    totalResponseTime: 0,

                    startTimer: function() {
                        this.startTime = performance.now();
                    },

                    endTimer: function() {
                        if (this.startTime) {
                            var duration = performance.now() - this.startTime;
                            this.dashboardUpdates++;
                            this.totalResponseTime += duration;

                            console.log('Dashboard Update Performance:', {
                                duration: Math.round(duration) + 'ms',
                                average: Math.round(this.totalResponseTime / this
                                    .dashboardUpdates) + 'ms',
                                updates: this.dashboardUpdates
                            });

                            this.startTime = null;
                        }
                    }
                };

                // Hook into AJAX calls for performance monitoring
                $(document).ajaxSend(function(event, jqXHR, settings) {
                    if (settings.url.includes('dashboard')) {
                        performanceLog.startTimer();
                    }
                });

                $(document).ajaxComplete(function(event, jqXHR, settings) {
                    if (settings.url.includes('dashboard')) {
                        performanceLog.endTimer();
                    }
                });
            }
        });
    </script>
@endsection
