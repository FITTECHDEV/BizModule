@extends('adminlte::page')

@section('title', 'User Management Dashboard')

@section('content_header')
    <h1><i class="fas fa-users-cog"></i> User Management Dashboard</h1>
    <p class="text-muted mb-0">Manage and monitor all system users</p>
@stop

@section('content')

    <!-- Statistics Cards Row -->
    <div class="row">
        <!-- Active Users Card -->
        <div class="col-lg-3 col-md-6">
            <div class="small-box bg-success" style="cursor: pointer;"
                onclick="window.location=`{{ route('azure.users.active') }}`">
                <div class="inner">
                    <h3>{{ $stats['active'] ?? 0 }}</h3>
                    <p>Active Users</p>
                </div>
                <div class="icon">
                    <i class="fas fa-user-check"></i>
                </div>
            </div>
        </div>

        <!-- Pending Users Card -->
        <div class="col-lg-3 col-md-6">
            <div class="small-box bg-warning" style="cursor: pointer;"
                onclick="window.location=`{{ route('azure.users.pending') }}`">
                <div class="inner">
                    <h3>{{ $stats['pending'] ?? 0 }}</h3>
                    <p>Pending</p>
                </div>
                <div class="icon">
                    <i class="fas fa-user-clock"></i>
                </div>
            </div>
        </div>

        <!-- Inactive Users Card -->
        <div class="col-lg-3 col-md-6">
            <div class="small-box bg-danger" style="cursor: pointer;"
                onclick="window.location=`{{ route('azure.users.inactive') }}`">
                <div class="inner">
                    <h3>{{ $stats['inactive'] ?? 0 }}</h3>
                    <p>Inactive</p>
                </div>
                <div class="icon">
                    <i class="fas fa-user-times"></i>
                </div>
            </div>
        </div>

        <!-- Total Users Card -->
        <div class="col-lg-3 col-md-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $stats['total'] ?? 0 }}</h3>
                    <p>Total Users</p>
                </div>
                <div class="icon">
                    <i class="fas fa-users"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Users Table Card -->
    <div class="card">
        <div class="card-header">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <div class="d-flex align-items-center">
                        <!-- Department Filter -->
                        <div class="mr-2" style="min-width: 180px;">
                            <select id="departmentFilter" class="form-control form-control-sm">
                                <option value="">All Departments</option>
                                @foreach ($departments as $dept)
                                    <option value="{{ $dept }}"
                                        {{ request('department') == $dept ? 'selected' : '' }}>
                                        {{ $dept }}
                                    </option>
                                @endforeach
                                <option value="no_department"
                                    {{ request('department') == 'no_department' ? 'selected' : '' }}>
                                    (No Department)
                                </option>
                            </select>
                        </div>

                        <!-- Company Filter -->
                        <div class="mr-2" style="min-width: 180px;">
                            <select id="companyFilter" class="form-control form-control-sm">
                                <option value="">All Companies</option>
                                @foreach ($companies as $company)
                                    <option value="{{ $company }}"
                                        {{ request('company') == $company ? 'selected' : '' }}>
                                        {{ $company }}
                                    </option>
                                @endforeach
                                <option value="no_company" {{ request('company') == 'no_company' ? 'selected' : '' }}>
                                    (No Company)
                                </option>
                            </select>
                        </div>

                        <!-- Office Location Filter -->
                        <div class="mr-2" style="min-width: 180px;">
                            <select id="locationFilter" class="form-control form-control-sm">
                                <option value="">All Locations</option>
                                @foreach ($locations as $location)
                                    <option value="{{ $location }}"
                                        {{ request('location') == $location ? 'selected' : '' }}>
                                        {{ $location }}
                                    </option>
                                @endforeach
                                <option value="no_location" {{ request('location') == 'no_location' ? 'selected' : '' }}>
                                    (No Location)
                                </option>
                            </select>
                        </div>

                        <!-- Search Type Dropdown (Hidden by default, shows when searching) -->
                        <div id="searchTypeContainer" class="mr-2" style="display: none; min-width: 180px;">
                            <select id="searchType" class="form-control form-control-sm">
                                <option value="all" {{ request('search_type', 'all') == 'all' ? 'selected' : '' }}>
                                    Search All Fields</option>
                                <option value="name" {{ request('search_type') == 'name' ? 'selected' : '' }}>Name Only
                                </option>
                                <option value="email" {{ request('search_type') == 'email' ? 'selected' : '' }}>Email Only
                                </option>
                                <option value="department" {{ request('search_type') == 'department' ? 'selected' : '' }}>
                                    Department Only</option>
                                <option value="company" {{ request('search_type') == 'company' ? 'selected' : '' }}>Company
                                    Only</option>
                                <option value="location" {{ request('search_type') == 'location' ? 'selected' : '' }}>
                                    Location Only</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 text-right">
                    <div class="d-inline-flex align-items-center">
                        <div class="input-group input-group-sm mr-2" style="width: 350px;">
                            <input type="text" id="searchUsers" class="form-control form-control-sm"
                                placeholder="Search by name, email, department, company, location... (Press Enter)"
                                value="{{ request('search') }}" autocomplete="off">
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary" type="button" id="clearSearch"
                                    title="Clear all filters">
                                    <i class="fas fa-times"></i>
                                </button>
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                            </div>
                        </div>

                        {{-- Sync from Odoo Button --}}
                        <button onclick="syncOdooUsers()" class="btn btn-success btn-sm mr-2" id="syncBtn"
                            title="Import/update users from Odoo">
                            <i class="fas fa-sync-alt"></i> Sync from Odoo
                        </button>

                        {{-- Retry Provisioning for Pending Users --}}
                        <button onclick="retryAllPendingUsers()" class="btn btn-warning btn-sm mr-2"
                            id="retryProvisioningBtn" title="Retry provisioning for all pending users">
                            <i class="fas fa-redo-alt"></i> Retry Pending
                        </button>

                        {{-- Add User Button --}}
                        <a href="{{ route('azure.users.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-user-plus"></i> Add User
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Users Table -->
        <div class="card-body table-responsive p-0">
            <table class="table table-hover text-nowrap">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Login ID</th>
                        <th>Employee ID</th>
                        <th>Department</th>
                        <th>Company</th>
                        <th>Location</th>
                        <th>Status</th>
                        <th style="width: 120px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="user-avatar mr-2">
                                        {{ strtoupper(substr($user->displayName ?? 'NA', 0, 2)) }}
                                    </div>
                                    <span>{{ $user->displayName ?? 'N/A' }}</span>
                                </div>
                            </td>
                            <td>{{ $user->mail ?? ($user->userPrincipalName ?? 'N/A') }}</td>
                            <td>{{ $user->userPrincipalName ?? 'N/A' }}</td>
                            <td>{{ $user->EmployeCode ?? 'N/A' }}</td>
                            <td>{{ $user->department ?? 'N/A' }}</td>
                            <td>{{ $user->companyName ?? 'N/A' }}</td>
                            <td>{{ $user->officeLocation ?? ($user->city ?? 'N/A') }}</td>
                            <td>
                                @php
                                    $status = $user->Status ?? 'Inactive';
                                    $badgeClass = match (strtolower($status)) {
                                        'active' => 'badge-success',
                                        'pending' => 'badge-warning',
                                        'inactive' => 'badge-danger',
                                        default => 'badge-secondary',
                                    };
                                @endphp
                                <span class="badge {{ $badgeClass }}">{{ $status }}</span>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="{{ route('azure.users.show', $user->Id) }}" class="btn btn-info btn-sm"
                                        title="View User">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @if (strtolower($user->Status ?? 'inactive') === 'inactive')
                                        <button class="btn btn-warning btn-sm btn-disabled" disabled
                                            title="Enable user first to edit" style="cursor: not-allowed; opacity: 0.6;">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    @else
                                        <a href="{{ route('azure.users.edit', $user->Id) }}"
                                            class="btn btn-warning btn-sm" title="Edit User">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    @endif
                                    @if (strtolower($user->Status ?? 'inactive') === 'active')
                                        <button class="btn btn-danger btn-sm"
                                            onclick="disableUser('{{ $user->Id }}')" title="Disable User">
                                            <i class="fas fa-user-slash"></i>
                                        </button>
                                    @else
                                        <button class="btn btn-success btn-sm"
                                            onclick="enableUser('{{ $user->Id }}')" title="Enable User">
                                            <i class="fas fa-user-check"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">
                                <i class="fas fa-users fa-3x mb-3"></i>
                                <p>No users found</p>
                                @if (request('search') || request('status') || request('department') || request('company') || request('location'))
                                    <button class="btn btn-sm btn-outline-primary" onclick="clearAllFilters()">
                                        <i class="fas fa-redo"></i> Clear Filters
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($users->hasPages())
            <div class="card-footer clearfix">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <span class="text-muted">
                            Showing {{ $users->firstItem() ?? 0 }} to {{ $users->lastItem() ?? 0 }} of
                            {{ $users->total() }} users
                        </span>
                    </div>
                    <div class="col-md-6">
                        <ul class="pagination pagination-sm m-0 float-right">
                            {{-- First Page Link --}}
                            @if ($users->onFirstPage())
                                <li class="page-item disabled">
                                    <span class="page-link">First</span>
                                </li>
                            @else
                                <li class="page-item">
                                    <a class="page-link"
                                        href="{{ $users->appends(request()->except('page'))->url(1) }}">First</a>
                                </li>
                            @endif

                            {{-- Previous Page Link --}}
                            @if ($users->onFirstPage())
                                <li class="page-item disabled">
                                    <span class="page-link">&laquo;</span>
                                </li>
                            @else
                                <li class="page-item">
                                    <a class="page-link"
                                        href="{{ $users->appends(request()->except('page'))->previousPageUrl() }}">&laquo;</a>
                                </li>
                            @endif

                            {{-- Pagination Elements --}}
                            @foreach ($users->getUrlRange(1, $users->lastPage()) as $page => $url)
                                @if ($page == $users->currentPage())
                                    <li class="page-item active">
                                        <span class="page-link">{{ $page }}</span>
                                    </li>
                                @else
                                    <li class="page-item">
                                        <a class="page-link"
                                            href="{{ $users->appends(request()->except('page'))->url($page) }}">{{ $page }}</a>
                                    </li>
                                @endif
                            @endforeach

                            {{-- Next Page Link --}}
                            @if ($users->hasMorePages())
                                <li class="page-item">
                                    <a class="page-link"
                                        href="{{ $users->appends(request()->except('page'))->nextPageUrl() }}">&raquo;</a>
                                </li>
                            @else
                                <li class="page-item disabled">
                                    <span class="page-link">&raquo;</span>
                                </li>
                            @endif

                            {{-- Last Page Link --}}
                            @if ($users->currentPage() == $users->lastPage())
                                <li class="page-item disabled">
                                    <span class="page-link">Last</span>
                                </li>
                            @else
                                <li class="page-item">
                                    <a class="page-link"
                                        href="{{ $users->appends(request()->except('page'))->url($users->lastPage()) }}">Last</a>
                                </li>
                            @endif
                        </ul>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- JavaScript for Filters -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Get filter elements
            const statusFilter = document.getElementById('statusFilter');
            const departmentFilter = document.getElementById('departmentFilter');
            const companyFilter = document.getElementById('companyFilter');
            const locationFilter = document.getElementById('locationFilter');
            const searchUsers = document.getElementById('searchUsers');
            const searchType = document.getElementById('searchType');
            const searchTypeContainer = document.getElementById('searchTypeContainer');
            const clearSearchBtn = document.getElementById('clearSearch');

            // Show search type dropdown when typing in search box
            searchUsers.addEventListener('input', function() {
                if (this.value.trim().length > 0) {
                    searchTypeContainer.style.display = 'block';
                } else {
                    searchTypeContainer.style.display = 'none';
                }
            });

            // Handle Enter key in search box
            searchUsers.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    applyFilters();
                }
            });

            // Handle filter changes
            [statusFilter, departmentFilter, companyFilter, locationFilter].forEach(filter => {
                if (filter) {
                    filter.addEventListener('change', applyFilters);
                }
            });

            // Clear search button - resets everything and reloads page
            clearSearchBtn.addEventListener('click', function() {
                window.location.href = window.location.pathname;
            });

            // Apply all filters
            function applyFilters() {
                const url = new URL(window.location.href.split('?')[0]);
                const params = new URLSearchParams();

                // Add status filter
                if (statusFilter && statusFilter.value) {
                    params.append('status', statusFilter.value);
                }

                // Add department filter
                if (departmentFilter.value) {
                    params.append('department', departmentFilter.value);
                }

                // Add company filter
                if (companyFilter.value) {
                    params.append('company', companyFilter.value);
                }

                // Add location filter
                if (locationFilter && locationFilter.value) {
                    params.append('location', locationFilter.value);
                }

                // Add search term and type
                const searchValue = searchUsers.value.trim();
                if (searchValue) {
                    params.append('search', searchValue);
                    if (searchType && searchType.value !== 'all') {
                        params.append('search_type', searchType.value);
                    }
                }

                // Redirect with filters
                const queryString = params.toString();
                window.location.href = queryString ? `${url}?${queryString}` : url;
            }

            // Clear all filters
            window.clearAllFilters = function() {
                window.location.href = window.location.pathname;
            };

            // Initialize search type container based on current search
            if (searchUsers.value.trim().length > 0) {
                searchTypeContainer.style.display = 'block';
            }
        });
    </script>

    <style>
        .user-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background-color: #3498db;
            color: white;
            display: flex;
            align-items-center;
            justify-content: center;
            font-weight: bold;
            font-size: 12px;
        }

        .badge {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
        }

        /* Adjust dropdown widths for better layout */
        @media (min-width: 768px) {
            .card-header .col-md-6 .d-flex {
                flex-wrap: nowrap;
            }
        }

        @media (max-width: 767px) {
            .card-header .col-md-6 .d-flex {
                flex-wrap: wrap;
            }

            .card-header .mr-2 {
                margin-bottom: 5px;
                min-width: 100% !important;
            }
        }

        /* Disabled button style for inactive users */
        .btn-disabled {
            pointer-events: none;
        }
    </style>

@stop

@section('css')
    <style>
        .user-avatar {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background: linear-gradient(135deg, #3498db, #2980b9);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 14px;
        }

        .btn-group-sm>.btn {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }

        .small-box .icon {
            font-size: 70px;
        }

        .small-box h3 {
            font-size: 2.2rem;
            font-weight: bold;
        }

        /* Clickable stats cards */
        .small-box[onclick] {
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .small-box[onclick]:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        /* Pagination Styles */
        .pagination {
            margin-bottom: 0;
        }

        .page-item.active .page-link {
            background-color: #007bff;
            border-color: #007bff;
        }

        .page-link {
            color: #007bff;
        }

        .page-link:hover {
            color: #0056b3;
        }

        .card-footer {
            background-color: #f8f9fa;
            border-top: 1px solid #dee2e6;
        }

        /* Compact form controls */
        #statusFilter {
            max-width: 150px;
        }

        #clearSearch {
            border-left: 0;
        }

        #clearSearch:hover {
            background-color: #e9ecef;
        }

        /* Search input focus */
        #searchUsers:focus {
            border-color: #80bdff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }
    </style>
@stop

@section('js')
    <script>
        // Search functionality - only trigger on Enter key
        document.getElementById('searchUsers').addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                performSearch();
            }

            // Clear search on Escape key
            if (e.key === 'Escape') {
                e.target.value = '';
                performSearch();
            }
        });

        // Status filter
        const statusFilter = document.getElementById('statusFilter');
        if (statusFilter) {
            statusFilter.addEventListener('change', function() {
                performSearch();
            });
        }

        // Department filter
        document.getElementById('departmentFilter').addEventListener('change', function() {
            performSearch();
        });

        // Company filter
        document.getElementById('companyFilter').addEventListener('change', function() {
            performSearch();
        });

        // Location filter
        const locationFilter = document.getElementById('locationFilter');
        if (locationFilter) {
            locationFilter.addEventListener('change', function() {
                performSearch();
            });
        }

        // Clear search button
        document.getElementById('clearSearch').addEventListener('click', function() {
            window.location.href = window.location.pathname;
        });

        function performSearch() {
            const search = document.getElementById('searchUsers').value.trim();
            const statusFilter = document.getElementById('statusFilter');
            const status = statusFilter ? statusFilter.value : '';
            const department = document.getElementById('departmentFilter').value;
            const company = document.getElementById('companyFilter').value;
            const locationFilter = document.getElementById('locationFilter');
            const location = locationFilter ? locationFilter.value : '';

            const url = new URL(window.location.href);

            if (search) {
                url.searchParams.set('search', search);
            } else {
                url.searchParams.delete('search');
            }

            if (status) {
                url.searchParams.set('status', status);
            } else {
                url.searchParams.delete('status');
            }

            if (department) {
                url.searchParams.set('department', department);
            } else {
                url.searchParams.delete('department');
            }

            if (company) {
                url.searchParams.set('company', company);
            } else {
                url.searchParams.delete('company');
            }

            if (location) {
                url.searchParams.set('location', location);
            } else {
                url.searchParams.delete('location');
            }

            url.searchParams.delete('page');
            window.location.href = url.toString();
        }

        function clearAllFilters() {
            window.location.href = window.location.pathname;
        }

        // ✅ UPDATED: Enable user with base office check
        function enableUser(userId) {
            if (!confirm(
                    'Enable User\n\nAre you sure you want to enable this user? They will regain access to all systems.')) {
                return;
            }

            const url = `/azure/users/${userId}/enable`;

            fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message || 'User has been enabled successfully');
                        window.location.reload();
                    } else {
                        // ✅ Check if it's a base office error
                        if (data.requires_base_office) {
                            const errorMessage = data.message + '\n\n' + data.instructions +
                                '\n\nClick OK to go to the user\'s edit page and set the base office.';

                            if (confirm(errorMessage)) {
                                // Redirect to edit page
                                window.location.href = `/azure/users/${userId}/edit`;
                            }
                        } else {
                            // Other errors
                            alert(data.message || 'Failed to enable user');
                        }
                    }
                })
                .catch(error => {
                    console.error('Error enabling user:', error);
                    alert('Failed to enable user. ' + error.message);
                });
        }

        function disableUser(userId) {
            toggleUserStatus(userId, 'disable');
        }

        function toggleUserStatus(userId, action) {
            const actionText = action === 'enable' ? 'enable' : 'disable';
            const actionTitle = action === 'enable' ? 'Enable User' : 'Disable User';
            const actionMessage = action === 'enable' ?
                'Are you sure you want to enable this user? They will regain access to all systems.' :
                'Are you sure you want to disable this user? They will lose access to all systems.';

            if (!confirm(`${actionTitle}\n\n${actionMessage}`)) {
                return;
            }

            const url = `/azure/users/${userId}/${actionText}`;

            fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message || `User has been ${actionText}d successfully`);
                        // ✅ IMPROVED: Force reload with location.href
                        window.location.href = window.location.href;
                    } else {
                        alert(data.message || `Failed to ${actionText} user`);
                        // ✅ NEW: Also reload on failure to show correct state
                        window.location.href = window.location.href;
                    }
                })
                .catch(error => {
                    console.error(`Error ${actionText}ing user:`, error);
                    alert(`Failed to ${actionText} user. ${error.message}`);
                    // ✅ NEW: Reload even on error to show correct state
                    window.location.href = window.location.href;
                });
        }

        function syncOdooUsers() {
            const btn = document.getElementById('syncBtn');
            const originalHtml = btn.innerHTML;

            if (!confirm(
                    'Sync users from Odoo?\n\nThis will:\n• Import new users (Status: Pending)\n• Update existing users (Status: Active)\n\nContinue?'
                )) {
                return;
            }

            // Disable button and show loading
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Syncing...';

            // Get CSRF token
            const csrfToken = document.querySelector('meta[name="csrf-token"]');

            if (!csrfToken) {
                console.error('❌ CSRF token not found in page!');
                alert('Error: CSRF token missing. Please refresh the page.');
                btn.disabled = false;
                btn.innerHTML = originalHtml;
                return;
            }

            console.log('🔄 Starting sync...', {
                url: '/azure/sync/odoo/sync-users-ajax',
                csrf: csrfToken.content.substring(0, 10) + '...',
                timestamp: new Date().toISOString()
            });

            fetch('/azure/sync/odoo/sync-users-ajax', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken.content,
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => {
                    console.log('📡 Response received:', {
                        status: response.status,
                        statusText: response.statusText,
                        ok: response.ok,
                        headers: Object.fromEntries(response.headers.entries())
                    });

                    // Check if response is JSON
                    const contentType = response.headers.get('content-type');
                    if (!contentType || !contentType.includes('application/json')) {
                        console.error('❌ Response is not JSON!', {
                            contentType,
                            status: response.status
                        });

                        // Try to get the actual response text for debugging
                        return response.text().then(text => {
                            console.error('📄 Actual response:', text.substring(0, 500));
                            throw new Error(
                                `Server returned ${response.status}: Expected JSON but got ${contentType}. Check console for details.`
                            );
                        });
                    }

                    if (!response.ok) {
                        return response.json().then(err => {
                            throw new Error(err.message ||
                                `HTTP ${response.status}: ${response.statusText}`);
                        });
                    }

                    return response.json();
                })
                .then(data => {
                    console.log('✅ Sync completed:', data);

                    if (data.success) {
                        alert(`Sync completed successfully!\n\n` +
                            `✅ Inserted: ${data.stats.inserted} (Pending)\n` +
                            `🔄 Updated: ${data.stats.updated} (Active)\n` +
                            `❌ Failed: ${data.stats.failed}\n\n` +
                            `Refreshing page...`);
                        window.location.reload();
                    } else {
                        alert('Sync failed: ' + (data.message || 'Unknown error'));
                        btn.disabled = false;
                        btn.innerHTML = originalHtml;
                    }
                })
                .catch(error => {
                    console.error('❌ Sync error:', error);
                    alert(`Sync failed!\n\nError: ${error.message}\n\nCheck browser console (F12) for details.`);
                    btn.disabled = false;
                    btn.innerHTML = originalHtml;
                });
        }

        /**
         * ✅ NEW: Retry provisioning for all pending users
         */
        function retryAllPendingUsers() {
            if (!confirm(
                    'Retry Provisioning for All Pending Users\n\n' +
                    'This will:\n' +
                    '• Find all users with Status = Pending\n' +
                    '• Create Azure accounts if missing\n' +
                    '• Provision to external systems (D365, SCM, BIZ, FIT Express)\n' +
                    '• Update status to Active if successful\n\n' +
                    'This may take a few minutes.\n\n' +
                    'Continue?'
                )) {
                return;
            }

            const btn = document.getElementById('retryProvisioningBtn');
            const originalHtml = btn.innerHTML;

            // Disable button and show loading
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';

            fetch('/azure/sync/activate-users', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('✅ Retry Provisioning Completed!\n\n' + data.message);
                        window.location.reload();
                    } else {
                        alert('⚠️ Provisioning completed with errors:\n\n' + data.message);
                        btn.disabled = false;
                        btn.innerHTML = originalHtml;
                    }
                })
                .catch(error => {
                    console.error('Retry provisioning error:', error);
                    alert('❌ Retry Failed\n\n' + error.message);
                    btn.disabled = false;
                    btn.innerHTML = originalHtml;
                });
        }

        /**
         * Sync user to all external systems using unified SP
         */
        function syncUserToExternalSystems(userId) {
            if (!confirm(
                    'Sync User to External Systems\n\n' +
                    'This will sync the user to:\n' +
                    '• D365 (if assigned)\n' +
                    '• SCM/OPS (if assigned)\n' +
                    '• BIZ PRO (if assigned)\n' +
                    '• FIT Express (if assigned)\n\n' +
                    'Continue?'
                )) {
                return;
            }

            // Show loading state
            const btn = event.target.closest('button');
            const originalHtml = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

            fetch(`/azure/users/${userId}/sync-unified`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Build detailed message
                        let message = '✅ Sync Completed!\n\n';

                        if (data.results) {
                            for (const [system, result] of Object.entries(data.results)) {
                                const icon = result.status === 'success' ? '✅' : '❌';
                                const systemName = system.toUpperCase().replace('_', ' ');
                                message += `${icon} ${systemName}: ${result.message}\n`;
                            }
                        }

                        alert(message);

                        // Reload to show updated status
                        window.location.reload();
                    } else {
                        alert('❌ Sync Failed\n\n' + (data.message || 'Unknown error'));
                        btn.disabled = false;
                        btn.innerHTML = originalHtml;
                    }
                })
                .catch(error => {
                    console.error('Sync error:', error);
                    alert('❌ Sync Failed\n\n' + error.message);
                    btn.disabled = false;
                    btn.innerHTML = originalHtml;
                });
        }

        console.log('Dashboard scripts loaded successfully');
    </script>
@stop
