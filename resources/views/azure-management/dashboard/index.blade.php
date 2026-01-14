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
                                @if ($user->Id)
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="{{ route('azure.users.show', $user->Id) }}" class="btn btn-info btn-sm"
                                            title="View User">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if (strtolower($user->Status ?? 'inactive') === 'inactive')
                                            <button class="btn btn-warning btn-sm btn-disabled" disabled
                                                title="Enable user first to edit"
                                                style="cursor: not-allowed; opacity: 0.6;">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        @else
                                            <a href="{{ route('azure.users.edit', $user->Id) }}"
                                                class="btn btn-warning btn-sm" title="Edit User">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        @endif
                                        @if (strtolower($user->Status ?? 'inactive') === 'active')
                                            <button class="btn btn-warning btn-sm"
                                                onclick="openManageAccessModal('{{ $user->Id }}', '{{ addslashes($user->displayName) }}', '{{ $user->Status }}')"
                                                title="Manage Access">
                                                <i class="fas fa-cog"></i>
                                            </button>
                                        @else
                                            <button class="btn btn-warning btn-sm"
                                                onclick="openManageAccessModal('{{ $user->Id }}', '{{ addslashes($user->displayName) }}', '{{ $user->Status }}')"
                                                title="Manage Access">
                                                <i class="fas fa-cog"></i>
                                            </button>
                                        @endif
                                    </div>
                                @else
                                    <button class="btn btn-warning btn-sm"
                                        onclick="fixUserRecord('{{ $user->userPrincipalName }}')" title="Fix missing ID">
                                        <i class="fas fa-wrench"></i> Fix Record
                                    </button>
                                @endif
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
                            @if ($users->onFirstPage())
                                <li class="page-item disabled"><span class="page-link">First</span></li>
                            @else
                                <li class="page-item"><a class="page-link"
                                        href="{{ $users->appends(request()->except('page'))->url(1) }}">First</a></li>
                            @endif
                            @if ($users->onFirstPage())
                                <li class="page-item disabled"><span class="page-link">&laquo;</span></li>
                            @else
                                <li class="page-item"><a class="page-link"
                                        href="{{ $users->appends(request()->except('page'))->previousPageUrl() }}">&laquo;</a>
                                </li>
                            @endif
                            @foreach ($users->getUrlRange(1, $users->lastPage()) as $page => $url)
                                @if ($page == $users->currentPage())
                                    <li class="page-item active"><span class="page-link">{{ $page }}</span></li>
                                @else
                                    <li class="page-item"><a class="page-link"
                                            href="{{ $users->appends(request()->except('page'))->url($page) }}">{{ $page }}</a>
                                    </li>
                                @endif
                            @endforeach
                            @if ($users->hasMorePages())
                                <li class="page-item"><a class="page-link"
                                        href="{{ $users->appends(request()->except('page'))->nextPageUrl() }}">&raquo;</a>
                                </li>
                            @else
                                <li class="page-item disabled"><span class="page-link">&raquo;</span></li>
                            @endif
                            @if ($users->currentPage() == $users->lastPage())
                                <li class="page-item disabled"><span class="page-link">Last</span></li>
                            @else
                                <li class="page-item"><a class="page-link"
                                        href="{{ $users->appends(request()->except('page'))->url($users->lastPage()) }}">Last</a>
                                </li>
                            @endif
                        </ul>
                    </div>
                </div>
            </div>
        @endif
    </div>

    {{-- ============================================ --}}
    {{-- MANAGE USER ACCESS MODAL                     --}}
    {{-- ============================================ --}}
    <div class="modal fade" id="manageAccessModal" tabindex="-1" role="dialog"
        aria-labelledby="manageAccessModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-gradient-primary text-white">
                    <h5 class="modal-title" id="manageAccessModalLabel">
                        <i class="fas fa-cog"></i> Manage User Access
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="modal-user-id" value="">
                    <input type="hidden" id="modal-user-name" value="">
                    <input type="hidden" id="modal-user-status" value="">

                    {{-- User Info Banner --}}
                    <div class="alert alert-info mb-3">
                        <strong><i class="fas fa-user"></i> <span id="modal-user-display"></span></strong>
                        <span class="badge badge-secondary ml-2" id="modal-status-badge">Loading...</span>
                    </div>

                    {{-- Quick Action: Disable/Enable All --}}
                    <div class="card mb-3 border-primary">
                        <div class="card-body py-2">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="disableAllCheckbox">
                                <label class="custom-control-label font-weight-bold" for="disableAllCheckbox">
                                    <i class="fas fa-bolt text-warning"></i>
                                    <span id="quickActionLabel">Disable All Applications</span>
                                </label>
                                <small class="form-text text-muted mt-0">
                                    Quickly disable/enable access to all assigned systems
                                </small>
                            </div>
                        </div>
                    </div>

                    <div class="text-center text-muted mb-3">
                        <small>───────── OR Select Individually ─────────</small>
                    </div>

                    {{-- Applications List (Compact) --}}
                    <div class="card">
                        <div class="card-body p-3">
                            {{-- Azure AD --}}
                            <div class="d-flex align-items-center justify-content-between py-2 border-bottom">
                                <div class="d-flex align-items-center flex-grow-1">
                                    <div class="custom-control custom-checkbox mb-0">
                                        <input type="checkbox" class="custom-control-input app-checkbox"
                                            id="azureCheckbox" data-app="azure">
                                        <label class="custom-control-label mb-0" for="azureCheckbox">
                                            <i class="fab fa-microsoft text-primary mr-2"></i>
                                            <strong>Azure AD Account</strong>
                                        </label>
                                    </div>
                                </div>
                                <span class="badge badge-success ml-3" id="azure-status-badge">Active</span>
                            </div>

                            {{-- OPS/SCM (Special with radio buttons) --}}
                            <div class="py-2 border-bottom">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center flex-grow-1">
                                        <div class="custom-control custom-checkbox mb-0">
                                            <input type="checkbox" class="custom-control-input app-checkbox"
                                                id="opsCheckbox" data-app="ops">
                                            <label class="custom-control-label mb-0" for="opsCheckbox">
                                                <i class="fas fa-warehouse text-warning mr-2"></i>
                                                <strong>OPS/SCM</strong>
                                                <span class="badge badge-danger badge-sm ml-2">Required Choice</span>
                                            </label>
                                        </div>
                                    </div>
                                    <div>
                                        <span class="badge badge-info badge-sm" id="ops-user-status">User: Active</span>
                                        <span class="badge badge-info badge-sm ml-1" id="ops-resource-status">Resource:
                                            Active</span>
                                    </div>
                                </div>

                                {{-- OPS Radio Options (Collapsible) --}}
                                <div id="opsRadioGroup" class="mt-2 ml-4 pl-3" style="display: none;">
                                    <div class="alert alert-warning py-2 mb-2">
                                        <small><i class="fas fa-exclamation-triangle"></i> <strong>Select one
                                                option:</strong></small>
                                    </div>
                                    <div class="custom-control custom-radio mb-2">
                                        <input type="radio" class="custom-control-input" id="opsUserOnly"
                                            name="opsAction" value="user_only">
                                        <label class="custom-control-label" for="opsUserOnly">
                                            <strong>Disable User Only</strong>
                                            <span class="text-muted small d-block">User cannot access OPS/SCM, resource
                                                remains active</span>
                                        </label>
                                    </div>
                                    <div class="custom-control custom-radio mb-2">
                                        <input type="radio" class="custom-control-input" id="opsResourceBoth"
                                            name="opsAction" value="resource_both">
                                        <label class="custom-control-label" for="opsResourceBoth">
                                            <strong>Disable Resource (Both)</strong>
                                            <span class="text-muted small d-block">Complete deactivation from
                                                OPS/SCM</span>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            {{-- D365 --}}
                            <div class="d-flex align-items-center justify-content-between py-2 border-bottom">
                                <div class="d-flex align-items-center flex-grow-1">
                                    <div class="custom-control custom-checkbox mb-0">
                                        <input type="checkbox" class="custom-control-input app-checkbox"
                                            id="d365Checkbox" data-app="d365" disabled>
                                        <label class="custom-control-label mb-0 text-muted" for="d365Checkbox">
                                            <i class="fas fa-chart-line text-success mr-2"></i>
                                            <strong>D365</strong>
                                            <span class="badge badge-warning badge-sm ml-2">Pending SP</span>
                                        </label>
                                    </div>
                                </div>
                                <div>
                                    <span class="badge badge-success badge-sm ml-3" id="d365-status-badge">Active</span>
                                    <span class="badge badge-secondary badge-sm ml-1">Coming Soon</span>
                                </div>
                            </div>

                            {{-- BIZ PRO --}}
                            <div class="d-flex align-items-center justify-content-between py-2 border-bottom">
                                <div class="d-flex align-items-center flex-grow-1">
                                    <div class="custom-control custom-checkbox mb-0">
                                        <input type="checkbox" class="custom-control-input app-checkbox" id="bizCheckbox"
                                            data-app="biz" disabled>
                                        <label class="custom-control-label mb-0 text-muted" for="bizCheckbox">
                                            <i class="fas fa-briefcase text-info mr-2"></i>
                                            <strong>BIZ PRO</strong>
                                            <span class="badge badge-warning badge-sm ml-2">Pending SP</span>
                                        </label>
                                    </div>
                                </div>
                                <div>
                                    <span class="badge badge-warning badge-sm ml-3" id="biz-status-badge">Pending</span>
                                    <span class="badge badge-secondary badge-sm ml-1">Coming Soon</span>
                                </div>
                            </div>

                            {{-- FIT Express --}}
                            <div class="d-flex align-items-center justify-content-between py-2">
                                <div class="d-flex align-items-center flex-grow-1">
                                    <div class="custom-control custom-checkbox mb-0">
                                        <input type="checkbox" class="custom-control-input app-checkbox" id="fitCheckbox"
                                            data-app="fit" disabled>
                                        <label class="custom-control-label mb-0 text-muted" for="fitCheckbox">
                                            <i class="fas fa-shipping-fast text-danger mr-2"></i>
                                            <strong>FIT Express</strong>
                                            <span class="badge badge-warning badge-sm ml-2">Pending SP</span>
                                        </label>
                                    </div>
                                </div>
                                <div>
                                    <span class="badge badge-success badge-sm ml-3" id="fit-status-badge">Active</span>
                                    <span class="badge badge-secondary badge-sm ml-1">Coming Soon</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Summary Section --}}
                    <div class="alert alert-info mt-3" id="changesSummary" style="display: none;">
                        <strong><i class="fas fa-info-circle"></i> Summary:</strong>
                        <span id="summaryText">0 applications will be affected</span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                    <button type="button" class="btn btn-primary" id="applyChangesBtn">
                        <i class="fas fa-check"></i> Apply Changes
                    </button>
                </div>
            </div>
        </div>
    </div>

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

        /* Modal Styling */
        #manageAccessModal .modal-content {
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        }

        #manageAccessModal .bg-gradient-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        #manageAccessModal .custom-control-label {
            cursor: pointer;
            user-select: none;
        }

        #manageAccessModal .custom-control-input:disabled~.custom-control-label {
            cursor: not-allowed;
            opacity: 0.6;
        }

        #manageAccessModal .border-bottom:last-child {
            border-bottom: none !important;
        }

        #opsRadioGroup {
            padding: 12px;
            background: #fff8e1;
            border-radius: 6px;
            border: 2px dashed #ffc107;
        }

        #opsRadioGroup .custom-radio {
            padding: 8px;
            background: white;
            border-radius: 4px;
            border: 2px solid #e0e0e0;
            transition: all 0.2s ease;
        }

        #opsRadioGroup .custom-radio:hover {
            border-color: #ffc107;
            background: #fffef7;
        }

        #opsRadioGroup .custom-radio input:checked~label {
            color: #d68910;
            font-weight: 600;
        }

        .badge-sm {
            font-size: 0.7rem;
            padding: 0.2rem 0.4rem;
        }

        #manageAccessModal .card-body>div {
            transition: background-color 0.2s ease;
        }

        #manageAccessModal .card-body>div:hover {
            background-color: #f8f9fa;
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
            const department = document.getElementById('departmentFilter').value;
            const company = document.getElementById('companyFilter').value;
            const location = locationFilter ? locationFilter.value : '';

            const url = new URL(window.location.href);
            if (search) url.searchParams.set('search', search);
            else url.searchParams.delete('search');
            if (department) url.searchParams.set('department', department);
            else url.searchParams.delete('department');
            if (company) url.searchParams.set('company', company);
            else url.searchParams.delete('company');
            if (location) url.searchParams.set('location', location);
            else url.searchParams.delete('location');
            url.searchParams.delete('page');
            window.location.href = url.toString();
        }

        function clearAllFilters() {
            window.location.href = window.location.pathname;
        }

        // Enable / Disable functions (kept as they were)
        function enableUser(userId) {
            if (!confirm(
                    'Enable User\n\nAre you sure you want to enable this user? They will regain access to all systems.'))
                return;
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
                        if (data.requires_base_office) {
                            const errorMessage = data.message + '\n\n' + data.instructions +
                                '\n\nClick OK to go to the user\'s edit page and set the base office.';
                            if (confirm(errorMessage)) {
                                window.location.href = `/azure/users/${userId}/edit`;
                            }
                        } else {
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
            if (!confirm(`${actionTitle}\n\n${actionMessage}`)) return;

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
                        window.location.href = window.location.href;
                    } else {
                        alert(data.message || `Failed to ${actionText} user`);
                        window.location.href = window.location.href;
                    }
                })
                .catch(error => {
                    console.error(`Error ${actionText}ing user:`, error);
                    alert(`Failed to ${actionText} user. ${error.message}`);
                    window.location.href = window.location.href;
                });
        }

        // ────────────────────────────────────────────
        // MANAGE USER ACCESS MODAL LOGIC
        // ────────────────────────────────────────────

        function openManageAccessModal(userId, userName, userStatus) {
            $('#modal-user-id').val(userId);
            $('#modal-user-name').val(userName);
            $('#modal-user-status').val(userStatus);
            $('#modal-user-display').text(userName);

            const statusBadge = $('#modal-status-badge');
            statusBadge.removeClass('badge-success badge-warning badge-danger');

            if (userStatus.toLowerCase() === 'active') {
                statusBadge.addClass('badge-success').text('Active');
                $('#quickActionLabel').text('Disable All Applications');
                $('#azureActionLabel').text('Disable Azure Account');
                $('#opsActionLabel').text('Enable OPS/SCM Changes');
            } else {
                statusBadge.addClass('badge-danger').text('Inactive');
                $('#quickActionLabel').text('Enable All Applications');
                $('#azureActionLabel').text('Enable Azure Account');
                $('#opsActionLabel').text('Enable OPS/SCM Access');
            }

            $('.app-checkbox').prop('checked', false);
            $('#disableAllCheckbox').prop('checked', false);
            $('#opsRadioGroup').hide();
            $('input[name="opsAction"]').prop('checked', false);
            $('#changesSummary').hide();

            $('#manageAccessModal').modal('show');
        }

        //-----------------------------------------------------------
        //Cancel BUTTON & MODAL RESET HANDLING
        //-----------------------------------------------------------

        //Reset modal state when closed (via Cancel or 'X' button)

        $('#manageAccessModal').on('hidden.bs.modal', function() {
            //Reset all checkboxes 
            $('.app-checkbox').prop('checked', false);
            $('#disableAlllCheckbox').prop('checked', false);

            //Hide and reset OPS radio group
            $('#opsRadioGroup').hide();
            $('input[name="opsActino"]').ptop('checked', false);

            //Hide summary
            $('#changeSummary').hide();

            //clear hidden inputs
            $('#modal-user-id').val('');
            $('#modal-user-name').val('');
            $('#modal-user-status').val('');

            console.log('✅ Modal reset on close')
        });

        //Explicit cancel button handler (backup)
        $('#manageAccessModal .btn-secondary[data-dismiss="modal"]').on('click', function(e) {
            console.log('Cancel button clicked');
            $('#manageAccessModal').modal('hide');
        });

        //-----------------------------------------------------------
        //CLOSE BUTTON (X) HANDLER
        //-----------------------------------------------------------

        //Handle X button click 
        $('#manageAccessModal .close').on('click', function(e) {
            e.preventDefault();
            console.log('X button clicked');
            $('#manageAccessModal').modal('hide');
            $('#manageAccessModal').modal('hide');
        });

        //Alternative: Use event delegation (more robust)
        $(document).on('click', '#manageAccessModal .close', function(e) {
            e.preventDefault();
            $('#manageAccessModal').modal('hide');
        });

        $('#disableAllCheckbox').on('change', function() {
            const isChecked = $(this).is(':checked');
            $('.app-checkbox:not(:disabled)').prop('checked', isChecked);

            if (isChecked && $('#opsCheckbox').is(':checked')) {
                $('#opsRadioGroup').show();
                $('#opsResourceBoth').prop('checked', true);
            } else {
                $('#opsRadioGroup').hide();
                $('input[name="opsAction"]').prop('checked', false);
            }

            updateChangesSummary();
        });

        $('#opsCheckbox').on('change', function() {
            if ($(this).is(':checked')) {
                $('#opsRadioGroup').slideDown(200);
                if ($('#disableAllCheckbox').is(':checked')) {
                    $('#opsResourceBoth').prop('checked', true);
                }
            } else {
                $('#opsRadioGroup').slideUp(200);
                $('input[name="opsAction"]').prop('checked', false);
            }
            updateChangesSummary();
        });

        $('.app-checkbox').on('change', function() {
            updateChangesSummary();
            if (!$(this).is(':checked')) {
                $('#disableAllCheckbox').prop('checked', false);
            }
        });

        $('input[name="opsAction"]').on('change', function() {
            updateChangesSummary();
        });

        function updateChangesSummary() {
            const checkedApps = $('.app-checkbox:checked').length;
            if (checkedApps > 0) {
                $('#changesSummary').show();
                $('#summaryText').text(`${checkedApps} application(s) will be affected`);
            } else {
                $('#changesSummary').hide();
            }
        }

        $('#applyChangesBtn').on('click', function() {
            const userId = $('#modal-user-id').val();
            const userStatus = $('#modal-user-status').val();

            if ($('#opsCheckbox').is(':checked') && !$('input[name="opsAction"]:checked').val()) {
                alert(
                    '⚠️ OPS/SCM Validation Error\n\nYou must select how to disable OPS/SCM:\n• Disable User Only\n• Disable Resource (Both)'
                    );
                return;
            }

            const checkedApps = $('.app-checkbox:checked').length;
            if (checkedApps === 0 && !$('#disableAllCheckbox').is(':checked')) {
                alert('⚠️ No Changes Selected\n\nPlease select at least one application to modify.');
                return;
            }

            const changes = [];
            if ($('#azureCheckbox').is(':checked')) changes.push('Azure AD');
            if ($('#opsCheckbox').is(':checked')) {
                const opsAction = $('input[name="opsAction"]:checked').val();
                changes.push(opsAction === 'user_only' ? 'OPS/SCM (User Only)' : 'OPS/SCM (Resource + User)');
            }
            if ($('#d365Checkbox').is(':checked')) changes.push('D365');
            if ($('#bizCheckbox').is(':checked')) changes.push('BIZ PRO');
            if ($('#fitCheckbox').is(':checked')) changes.push('FIT Express');

            const action = userStatus.toLowerCase() === 'active' ? 'disable' : 'enable';
            const confirmMessage =
                `⚠️ Confirm Action\n\nYou are about to ${action}:\n${changes.map(c => '• ' + c).join('\n')}\n\nContinue?`;

            if (!confirm(confirmMessage)) return;

            if ($('#azureCheckbox').is(':checked')) {
                $('#manageAccessModal').modal('hide');
                if (userStatus.toLowerCase() === 'active') {
                    toggleUserStatus(userId, 'disable');
                } else {
                    enableUser(userId);
                }
            } else {
                alert(
                    '✅ Changes Saved (UI Only)\n\nOther applications will be integrated once stored procedures are ready.\n\nFor now, only Azure AD changes are functional.'
                    );
                $('#manageAccessModal').modal('hide');
            }
        });

        console.log('✅ Manage Access Modal loaded');
        console.log('Dashboard scripts loaded successfully');
    </script>
@stop
