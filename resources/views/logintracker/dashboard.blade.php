@extends('logintracker.layouts.app')

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
            <div class="col-md-3">
                <label for="range" class="form-label">Date Range:</label>
                <select name="range" id="range" class="form-select">
                    @php
                        $selectedRange = request('range', 'this_month');
                    @endphp
                    <option value="this_month" {{ $selectedRange == 'this_month' ? 'selected' : '' }}>This Month</option>
                    <option value="last_month" {{ $selectedRange == 'last_month' ? 'selected' : '' }}>Last Month</option>
                    <option value="last_3_months" {{ $selectedRange == 'last_3_months' ? 'selected' : '' }}>Last 3 Months</option>
                </select>
            </div>

            <div class="col-md-3">
                <label for="system" class="form-label">System:</label>
                <select name="system" id="system" class="form-select">
                    @php
                        $selectedSystem = request('system', 'SCM');
                    @endphp
                    @foreach ($systems ?? [] as $sys)
                        <option value="{{ $sys }}" {{ $selectedSystem == $sys ? 'selected' : '' }}>{{ $sys }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <label for="search" class="form-label">Search:</label>
                <input type="text" name="search" id="search" class="form-control" placeholder="Name or UPN" value="{{ request('search') }}">
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
                <a href="{{ route('reporting.users.not-logged-in') }}" class="text-decoration-none" id="not-logged-in-link">
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

                {{-- Compact Pagination --}}
                <div class="mt-3">
                    <div class="compact-pagination">
                        {{ $users->withQueryString()->links() }}
                    </div>
                </div>
            @endif
        </div>
    </div>

    <style>
        .card-hover {
            transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
            cursor: pointer;
        }

        .card-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15) !important;
        }

        .card-hover:active {
            transform: translateY(-2px);
        }

        .table-responsive {
            border-radius: 0.375rem;
            overflow: hidden;
        }

        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }

        /* Compact Pagination Styles */
        .compact-pagination {
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .compact-pagination .pagination {
            margin: 0;
            gap: 2px;
        }

        .compact-pagination .page-item .page-link {
            padding: 0.375rem 0.75rem;
            font-size: 0.875rem;
            line-height: 1.2;
            border-radius: 0.25rem;
            margin: 0;
            min-width: 2.5rem;
            text-align: center;
            border: 1px solid #dee2e6;
            color: #0d6efd;
            background-color: #fff;
            transition: all 0.15s ease-in-out;
        }

        .compact-pagination .page-item.active .page-link {
            background-color: #0d6efd;
            border-color: #0d6efd;
            color: #fff;
            font-weight: 500;
        }

        .compact-pagination .page-item:hover .page-link {
            background-color: #e9ecef;
            border-color: #adb5bd;
            color: #0a58ca;
        }

        .compact-pagination .page-item.active:hover .page-link {
            background-color: #0b5ed7;
            border-color: #0a58ca;
            color: #fff;
        }

        .compact-pagination .page-item.disabled .page-link {
            color: #6c757d;
            background-color: #fff;
            border-color: #dee2e6;
            cursor: not-allowed;
            opacity: 0.65;
        }

        .compact-pagination .page-item:first-child .page-link,
        .compact-pagination .page-item:last-child .page-link {
            font-weight: 500;
        }

        /* Hide ellipsis dots if they're taking too much space */
        .compact-pagination .page-item.disabled .page-link:contains("...") {
            padding: 0.375rem 0.5rem;
            min-width: auto;
        }

        /* Responsive adjustments */
        @media (max-width: 576px) {
            .compact-pagination .page-item .page-link {
                padding: 0.25rem 0.5rem;
                font-size: 0.75rem;
                min-width: 2rem;
            }
            
            .compact-pagination {
                flex-wrap: wrap;
                gap: 4px;
            }
        }
    </style>

    <script>
        $(document).ready(function() {
            // Define the base URL for the users.show route
            const userShowBaseUrl = '{{ route('reporting.users.show', ['user' => ':id']) }}'.replace(':id', '');

            $('#apply').on('click', function(e) {
                e.preventDefault();
                updateDashboard();
            });

            // Initialize dashboard on page load
            // updateDashboard(); // Commented out to prevent initial AJAX call since we have server-side data

            $('#system, #range').on('change', function() {
                updateDashboard();
            });

            let searchTimeout;
            $('#search').on('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(updateDashboard, 500);
            });

            function updateDashboard() {
                var range = $('#range').val();
                var system = $('#system').val() || 'SCM';
                var search = encodeURIComponent($('#search').val());
                var rangeLabel = {
                    'this_month': 'This Month',
                    'last_month': 'Last Month',
                    'last_3_months': 'Last 3 Months',
                    'custom': 'Custom'
                }[range] || range;
                var systemLabel = system || 'All Systems';

                console.log('Sending AJAX with:', { range, system, search });
                $('#loading').show();
                $('#user-table').html('<div class="alert alert-info">Loading...</div>');

                $.ajax({
                    url: '{{ route('dashboard') }}',
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: { range, system, search },
                    success: function(response) {
                        console.log('AJAX Response:', response);
                        $('#loading').hide();

                        // Update summary cards
                        $('#total-users').text(response.totalUsers || 0);
                        $('#logged-in-count').text(response.loggedInCount || 0);
                        $('#not-logged-in-count').text(response.notLoggedInCount || 0);

                        // Update filter links
                        const queryParams = $.param({ range, system, search });
                        $('#logged-in-link').attr('href', '{{ route('reporting.users.logged-in') }}?' + queryParams);
                        $('#not-logged-in-link').attr('href', '{{ route('reporting.users.not-logged-in') }}?' + queryParams);

                        // Update filter labels
                        $('#range-label').text(rangeLabel).toggle(!!rangeLabel);
                        $('#system-label').text(systemLabel).toggle(!!system);
                        $('#search-label').text('Search: ' + (search ? decodeURIComponent(search) : '')).toggle(!!search);
                        $('#filter-summary').toggle(!!rangeLabel || !!system || !!search);

                        // Users data is directly in response.users array from controller
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
                                <div class="mt-3">
                                    <div class="compact-pagination" id="pagination"></div>
                                </div>
                            `;
                            $('#user-table').html(tableHtml);

                            // Populate table rows
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
                                    lastLogin = new Date(userSignIns[0].date_utc).toLocaleString();
                                }

                                // Use the ID from controller response
                                var userId = user.id;
                                var displayName = user.displayName || 'Unknown';
                                var email = user.userPrincipalName || 'N/A';

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

                            // Handle pagination if present
                            if (response.pagination) {
                                $('#pagination').html(response.pagination);
                                bindPaginationEvents();
                            }
                        } else {
                            $('#user-table').html(
                                '<div class="alert alert-info">No users found for the selected filters.</div>'
                            );
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX Error:', status, error, xhr.responseText);
                        $('#loading').hide();
                        $('#total-users').text('0');
                        $('#logged-in-count').text('0');
                        $('#not-logged-in-count').text('0');
                        $('#user-table').html(
                            '<div class="alert alert-danger">An error occurred while loading data. Please try again later.</div>'
                        );
                    }
                });
            }

            function bindPaginationEvents() {
                $('#pagination a').off('click').on('click', function(e) {
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
                            page: page
                        },
                        success: function(response) {
                            $('#loading').hide();
                            updateTableContent(response);
                        },
                        error: function(xhr, status, error) {
                            console.error('Pagination AJAX Error:', status, error, xhr.responseText);
                            $('#loading').hide();
                            $('#user-table').html(
                                '<div class="alert alert-danger">An error occurred while loading data. Please try again later.</div>'
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

                // Users data is directly in response.users array from controller
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
                            lastLogin = new Date(userSignIns[0].date_utc).toLocaleString();
                        }

                        var userId = user.id;
                        var displayName = user.displayName || 'Unknown';
                        var email = user.userPrincipalName || 'N/A';

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

                    // Update pagination with compact styling
                    if (response.pagination) {
                        $('#pagination').html(response.pagination);
                        bindPaginationEvents();
                    }
                } else {
                    $('#user-table').html(
                        '<div class="alert alert-info">No users found for the selected filters.</div>'
                    );
                }
            }

            // Bind initial pagination events if table exists
            bindPaginationEvents();
        });
    </script>
@endsection