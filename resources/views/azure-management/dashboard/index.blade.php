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
            <div class="small-box bg-success status-filter-card" data-status="active" style="cursor: pointer;">
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
            <div class="small-box bg-warning status-filter-card" data-status="pending" style="cursor: pointer;">
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
            <div class="small-box bg-danger status-filter-card" data-status="inactive" style="cursor: pointer;">
                <div class="inner">
                    <h3>{{ $stats['inactive'] ?? 0 }}</h3>
                    <p>Inactive</p>
                </div>
                <div class="icon">
                    <i class="fas fa-user-times"></i>
                </div>
            </div>
        </div>

        <!-- Total Users Card - NOT CLICKABLE -->
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
                    <div class="d-inline-flex align-items-center">
                        <div class="input-group input-group-sm" style="width: 400px;">
                            <input type="text" id="searchUsers" class="form-control form-control-sm"
                                placeholder="Search by name, email, login ID..." value="{{ request('search') }}"
                                autocomplete="off">
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary" type="button" id="clearSearch"
                                    title="Clear search">
                                    <i class="fas fa-times"></i>
                                </button>
                                <button class="btn btn-primary" type="button" id="searchButton" title="Search">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 text-right">
                    <div class="d-inline-flex align-items-center">
                        <button onclick="syncOdooUsers()" class="btn btn-success btn-sm mr-2" id="syncBtn"
                            title="Import/update users from Odoo">
                            <i class="fas fa-sync-alt"></i> Sync from Odoo
                        </button>
                        <button onclick="retryAllPendingUsers()" class="btn btn-warning btn-sm mr-2"
                            id="retryProvisioningBtn" title="Retry provisioning for all pending users">
                            <i class="fas fa-redo-alt"></i> Retry Pending
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Users Table -->
        <div class="card-body table-responsive p-0" id="usersTableContainer">
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
                <tbody id="usersTableBody">
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
                                        @if (strtolower($user->Status ?? 'inactive') === 'active' && request('status', 'active') === 'active')
                                            <button class="btn btn-danger btn-sm"
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
                                @if (request('search') || request('status'))
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
            <div class="card-footer clearfix" id="paginationContainer">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <span class="text-muted" id="paginationInfo">
                            Showing {{ $users->firstItem() ?? 0 }} to {{ $users->lastItem() ?? 0 }} of
                            {{ $users->total() }} users
                        </span>
                    </div>
                    <div class="col-md-6">
                        <ul class="pagination pagination-sm m-0 float-right">
                            @if ($users->onFirstPage())
                                <li class="page-item disabled"><span class="page-link">First</span></li>
                            @else
                                <li class="page-item"><a class="page-link page-nav" href="#"
                                        data-page="1">First</a></li>
                            @endif
                            @if ($users->onFirstPage())
                                <li class="page-item disabled"><span class="page-link">&laquo;</span></li>
                            @else
                                <li class="page-item"><a class="page-link page-nav" href="#"
                                        data-page="{{ $users->currentPage() - 1 }}">&laquo;</a></li>
                            @endif
                            @foreach ($users->getUrlRange(1, $users->lastPage()) as $page => $url)
                                @if ($page == $users->currentPage())
                                    <li class="page-item active"><span class="page-link">{{ $page }}</span></li>
                                @else
                                    <li class="page-item"><a class="page-link page-nav" href="#"
                                            data-page="{{ $page }}">{{ $page }}</a></li>
                                @endif
                            @endforeach
                            @if ($users->hasMorePages())
                                <li class="page-item"><a class="page-link page-nav" href="#"
                                        data-page="{{ $users->currentPage() + 1 }}">&raquo;</a></li>
                            @else
                                <li class="page-item disabled"><span class="page-link">&raquo;</span></li>
                            @endif
                            @if ($users->currentPage() == $users->lastPage())
                                <li class="page-item disabled"><span class="page-link">Last</span></li>
                            @else
                                <li class="page-item"><a class="page-link page-nav" href="#"
                                        data-page="{{ $users->lastPage() }}">Last</a></li>
                            @endif
                        </ul>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- Manage Access Modal -->
    <div class="modal fade" id="manageAccessModal" tabindex="-1" role="dialog"
        aria-labelledby="manageAccessModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-gradient-primary text-white">
                    <h5 class="modal-title" id="manageAccessModalLabel">
                        <i class="fas fa-cog"></i> Manage User Access
                    </h5>
                    <button type="button" class="close text-white" onclick="$('#manageAccessModal').modal('hide');"
                        aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="modal-user-id" value="">
                    <input type="hidden" id="modal-user-name" value="">
                    <input type="hidden" id="modal-user-status" value="">

                    <div class="alert alert-info mb-3">
                        <strong><i class="fas fa-user"></i> <span id="modal-user-display"></span></strong>
                        <span class="badge badge-secondary ml-2" id="modal-status-badge">Loading...</span>
                    </div>

                    <div id="loadingApplicationStatus" class="text-center py-3" style="display: none;">
                        <i class="fas fa-spinner fa-spin fa-2x text-primary"></i>
                        <p class="mt-2 text-muted">Loading application status...</p>
                    </div>

                    <div class="card mb-3 border-primary">
                        <div class="card-body py-2">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="azureCheckbox" data-app="azure">
                                <label class="custom-control-label font-weight-bold" for="azureCheckbox">
                                    <i class="fab fa-microsoft text-primary"></i>
                                    <span id="azureActionLabel">Disable Azure AD & All Applications</span>
                                </label>
                                <small class="form-text text-muted mt-0">
                                    <i class="fas fa-info-circle"></i> Checking this will affect all systems below
                                </small>
                            </div>
                        </div>
                    </div>

                    <div class="text-center text-muted mb-3">
                        <small>───────── OR Select Applications Individually ─────────</small>
                    </div>

                    <div class="card" id="applicationsCard">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center justify-content-between py-2 border-bottom">
                                <div class="d-flex align-items-center flex-grow-1">
                                    <div class="custom-control custom-checkbox mb-0">
                                        <input type="checkbox" class="custom-control-input app-checkbox"
                                            id="d365Checkbox" data-app="d365">
                                        <label class="custom-control-label mb-0" for="d365Checkbox">
                                            <i class="fas fa-chart-line text-success mr-2"></i>
                                            <strong>D365 Live</strong>
                                        </label>
                                    </div>
                                </div>
                                <span class="badge badge-sm ml-3" id="d365-status-badge">Checking...</span>
                            </div>

                            <div class="d-flex align-items-center justify-content-between py-2 border-bottom">
                                <div class="d-flex align-items-center flex-grow-1">
                                    <div class="custom-control custom-checkbox mb-0">
                                        <input type="checkbox" class="custom-control-input app-checkbox" id="opsCheckbox"
                                            data-app="ops">
                                        <label class="custom-control-label mb-0" for="opsCheckbox">
                                            <i class="fas fa-warehouse text-warning mr-2"></i>
                                            <strong>OPS/SCM</strong>
                                        </label>
                                    </div>
                                </div>
                                <span class="badge badge-sm ml-3" id="ops-status-badge">Checking...</span>
                            </div>

                            <div class="d-flex align-items-center justify-content-between py-2 border-bottom">
                                <div class="d-flex align-items-center flex-grow-1">
                                    <div class="custom-control custom-checkbox mb-0">
                                        <input type="checkbox" class="custom-control-input app-checkbox" id="bizCheckbox"
                                            data-app="biz">
                                        <label class="custom-control-label mb-0" for="bizCheckbox">
                                            <i class="fas fa-briefcase text-info mr-2"></i>
                                            <strong>BIZ PRO</strong>
                                        </label>
                                    </div>
                                </div>
                                <span class="badge badge-sm ml-3" id="biz-status-badge">Checking...</span>
                            </div>

                            <div class="d-flex align-items-center justify-content-between py-2">
                                <div class="d-flex align-items-center flex-grow-1">
                                    <div class="custom-control custom-checkbox mb-0">
                                        <input type="checkbox" class="custom-control-input app-checkbox" id="fitCheckbox"
                                            data-app="fit">
                                        <label class="custom-control-label mb-0" for="fitCheckbox">
                                            <i class="fas fa-shipping-fast text-danger mr-2"></i>
                                            <strong>FIT Express</strong>
                                        </label>
                                    </div>
                                </div>
                                <span class="badge badge-sm ml-3" id="fit-status-badge">Checking...</span>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-info mt-3" id="changesSummary" style="display: none;">
                        <strong><i class="fas fa-info-circle"></i> Summary:</strong>
                        <span id="summaryText">No changes selected</span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="$('#manageAccessModal').modal('hide');">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                    <button type="button" class="btn btn-primary" id="applyChangesBtn" disabled>
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

        .small-box.status-filter-card {
            transition: transform 0.2s, box-shadow 0.2s;
            position: relative;
        }

        .small-box.status-filter-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .small-box.status-filter-card.active::after {
            content: '';
            position: absolute;
            top: 10px;
            right: 10px;
            width: 20px;
            height: 20px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .small-box.status-filter-card.active::before {
            content: '✓';
            position: absolute;
            top: 10px;
            right: 10px;
            width: 20px;
            height: 20px;
            z-index: 1;
            color: #28a745;
            font-weight: bold;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
        }

        .pagination {
            margin-bottom: 0;
        }

        .page-item.active .page-link {
            background-color: #007bff;
            border-color: #007bff;
        }

        .page-link {
            color: #007bff;
            cursor: pointer;
        }

        .page-link:hover {
            color: #0056b3;
        }

        .card-footer {
            background-color: #f8f9fa;
            border-top: 1px solid #dee2e6;
        }

        #clearSearch {
            border-left: 0;
        }

        #clearSearch:hover {
            background-color: #e9ecef;
        }

        #searchUsers:focus {
            border-color: #80bdff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }

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

        .azure-controlled {
            opacity: 0.7;
            background-color: #e3f2fd;
            padding: 5px;
            border-radius: 4px;
            transition: all 0.3s ease;
        }

        .azure-controlled label {
            color: #1976d2 !important;
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

        .table-loading {
            opacity: 0.5;
            pointer-events: none;
        }
    </style>
@stop

@section('js')
    <script>
        let currentUserApplicationStatus = {
            azure: false,
            d365: false,
            ops: false,
            biz: false,
            fit: false
        };

        let currentFilters = {
            status: '{{ request('status', 'active') }}',
            search: '{{ request('search', '') }}',
            page: 1,
            per_page: 15
        };

        // ────────────────────────────────────────────
        // AJAX TABLE LOADING
        // ────────────────────────────────────────────
        function loadUsers(filters = {}) {
            currentFilters = {
                ...currentFilters,
                ...filters
            };

            $('#usersTableContainer').addClass('table-loading');

            $.ajax({
                url: '/azure/dashboard/get-users',
                method: 'GET',
                data: currentFilters,
                success: function(response) {
                    if (response.success) {
                        $('#usersTableBody').html(response.table);

                        if (response.pagination) {
                            $('#paginationContainer').html(response.pagination).show();
                            $('#paginationInfo').html(
                                `Showing ${response.from} to ${response.to} of ${response.total} users`);
                        } else {
                            $('#paginationContainer').hide();
                        }

                        updateURL();
                    }
                    $('#usersTableContainer').removeClass('table-loading');
                },
                error: function(xhr) {
                    console.error('Error loading users:', xhr);
                    alert('Failed to load users. Please try again.');
                    $('#usersTableContainer').removeClass('table-loading');
                }
            });
        }

        function updateURL() {
            const url = new URL(window.location.href);
            Object.keys(currentFilters).forEach(key => {
                if (currentFilters[key]) {
                    url.searchParams.set(key, currentFilters[key]);
                } else {
                    url.searchParams.delete(key);
                }
            });
            window.history.pushState({}, '', url);
        }

        // ────────────────────────────────────────────
        // STATUS CARD FILTERING
        // ────────────────────────────────────────────
        $(document).ready(function() {
            // Set the appropriate status card as active on page load
            const currentStatus = '{{ request('status', 'active') }}'; // Default to 'active'

            if (currentStatus) {
                $(`.status-filter-card[data-status="${currentStatus}"]`).addClass('active');
            }

            // Status card click handler
            $('.status-filter-card').on('click', function() {
                const status = $(this).data('status');

                // Remove active from all cards
                $('.status-filter-card').removeClass('active');

                // Add active to clicked card
                $(this).addClass('active');

                // Update filters and reload
                currentFilters.status = status;
                loadUsers({
                    status: status,
                    page: 1
                });
            });
        });

        // ────────────────────────────────────────────
        // PAGINATION
        // ────────────────────────────────────────────
        $(document).on('click', '.page-nav', function(e) {
            e.preventDefault();
            const page = $(this).data('page');
            loadUsers({
                page: page
            });
        });

        // ────────────────────────────────────────────
        // SEARCH
        // ────────────────────────────────────────────
        let searchTimeout;

        $(document).ready(function() {
            $('#searchUsers').on('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(function() {
                    performSearch();
                }, 500);
            });

            $('#searchUsers').on('keypress', function(e) {
                if (e.which === 13) {
                    e.preventDefault();
                    clearTimeout(searchTimeout);
                    performSearch();
                }
            });

            $('#searchUsers').on('keyup', function(e) {
                if (e.key === 'Escape') {
                    clearAllFilters();
                }
            });

            $('#searchButton').on('click', function() {
                clearTimeout(searchTimeout);
                performSearch();
            });

            $('#clearSearch').on('click', function() {
                clearTimeout(searchTimeout);
                clearAllFilters();
            });
        });

        function performSearch() {
            const search = $('#searchUsers').val().trim();
            loadUsers({
                search: search,
                page: 1
            });
        }

        function clearAllFilters() {
            $('#searchUsers').val('');
            $('.status-filter-card').removeClass('active');
            $('.status-filter-card[data-status="active"]').addClass('active'); // Set Active as default
            currentFilters = {
                status: 'active', // Reset to active, not empty
                search: '',
                page: 1,
                per_page: 15
            };
            loadUsers();
        }

        // ────────────────────────────────────────────
        // MANAGE USER ACCESS MODAL
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
                $('#azureActionLabel').text('Disable Azure AD & All Applications');
            } else {
                statusBadge.addClass('badge-danger').text('Inactive');
                $('#azureActionLabel').text('Enable Azure AD & All Applications');
            }

            resetModalState();
            $('#loadingApplicationStatus').show();
            $('#applicationsCard').hide();
            $('#manageAccessModal').modal('show');
            loadUserApplicationStatus(userId);
        }

        function loadUserApplicationStatus(userId) {
            $.ajax({
                url: `/azure/users/${userId}/application-status`,
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(data) {
                    if (data.success) {
                        currentUserApplicationStatus = data.data;
                        updateApplicationStatusUI();
                    } else {
                        showErrorInModal('Failed to load application status');
                    }
                    $('#loadingApplicationStatus').hide();
                    $('#applicationsCard').show();
                },
                error: function(xhr, status, error) {
                    console.error('Error:', error);
                    showErrorInModal(`Network error: ${error}`);
                    $('#loadingApplicationStatus').hide();
                    $('#applicationsCard').show();
                }
            });
        }

        function updateApplicationStatusUI() {
            const apps = ['d365', 'ops', 'biz', 'fit'];
            let allAppsActive = true;

            apps.forEach(app => {
                const checkbox = $(`#${app}Checkbox`);
                const statusBadge = $(`#${app}-status-badge`);
                const hasAccess = currentUserApplicationStatus[app];

                if (!hasAccess) {
                    checkbox.prop('checked', false).prop('disabled', true);
                    checkbox.closest('.custom-control').addClass('text-muted');
                    statusBadge.attr('class', 'badge badge-sm badge-secondary ml-3').html('No Access');
                    allAppsActive = false;
                } else {
                    checkbox.prop('checked', true).prop('disabled', false);
                    checkbox.closest('.custom-control').removeClass('text-muted');
                    statusBadge.attr('class', 'badge badge-sm badge-success ml-3').html('Active');
                }
            });

            const azureCheckbox = $('#azureCheckbox');
            if (allAppsActive) {
                azureCheckbox.prop('checked', true);
                apps.forEach(app => {
                    if (currentUserApplicationStatus[app]) {
                        $(`#${app}Checkbox`).prop('disabled', true);
                        $(`#${app}Checkbox`).closest('.custom-control').addClass('azure-controlled');
                    }
                });
            } else {
                azureCheckbox.prop('checked', false);
            }
        }

        function resetModalState() {
            $('.app-checkbox, #azureCheckbox').prop('checked', false);
            $('.app-checkbox').prop('disabled', false);
            $('.custom-control').removeClass('text-muted azure-controlled');
            $('.badge-sm').removeClass('badge-success badge-danger badge-secondary').addClass('badge-warning').text(
                'Checking...');
            $('#changesSummary').hide();
            $('#applyChangesBtn').prop('disabled', true);
        }

        function showErrorInModal(message) {
            const errorHtml = `
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-triangle"></i> ${message}
                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
            </div>`;
            $('#applicationsCard').before(errorHtml);
        }

        // ────────────────────────────────────────────
        // CHECKBOX EVENT HANDLERS
        // ────────────────────────────────────────────
        $(document).ready(function() {
            $('#azureCheckbox').on('change', function() {
                const isChecked = $(this).is(':checked');

                $('.app-checkbox').each(function() {
                    const app = $(this).data('app');
                    if (currentUserApplicationStatus[app]) {
                        if (isChecked) {
                            $(this).prop('checked', true).prop('disabled', true);
                            $(this).closest('.custom-control').addClass('azure-controlled');
                        } else {
                            $(this).prop('checked', false).prop('disabled', false);
                            $(this).closest('.custom-control').removeClass('azure-controlled');
                        }
                    }
                });

                updateChangesSummary();
            });

            $('.app-checkbox').on('change', function() {
                const accessibleApps = $('.app-checkbox').filter(function() {
                    return currentUserApplicationStatus[$(this).data('app')];
                });
                const checkedAccessibleApps = accessibleApps.filter(':checked');

                if (accessibleApps.length > 0 && checkedAccessibleApps.length === accessibleApps.length) {
                    $('#azureCheckbox').prop('checked', true);
                    accessibleApps.each(function() {
                        $(this).prop('disabled', true).closest('.custom-control').addClass(
                            'azure-controlled');
                    });
                } else {
                    $('#azureCheckbox').prop('checked', false);
                    accessibleApps.each(function() {
                        $(this).prop('disabled', false).closest('.custom-control').removeClass(
                            'azure-controlled');
                    });
                }

                updateChangesSummary();
            });
        });

        function updateChangesSummary() {
            const checkedApps = [];

            if ($('#azureCheckbox').is(':checked')) {
                checkedApps.push('Azure AD (+ All Applications)');
            } else {
                $('.app-checkbox:checked').each(function() {
                    checkedApps.push($(this).closest('label').find('strong').text());
                });
            }

            if (checkedApps.length > 0) {
                $('#changesSummary').show();
                $('#summaryText').text(`${checkedApps.length} system(s) will be affected: ${checkedApps.join(', ')}`);
                $('#applyChangesBtn').prop('disabled', false);
            } else {
                $('#changesSummary').hide();
                $('#applyChangesBtn').prop('disabled', true);
            }
        }

        // ────────────────────────────────────────────
        // APPLY CHANGES
        // ────────────────────────────────────────────
        $(document).ready(function() {
            $('#applyChangesBtn').on('click', function() {
                const userId = $('#modal-user-id').val();
                const userStatus = $('#modal-user-status').val();
                const action = userStatus.toLowerCase() === 'active' ? 'Disable' : 'Enable';
                const selectedApps = [];

                if ($('#azureCheckbox').is(':checked')) selectedApps.push('azure');
                $('.app-checkbox:checked').each(function() {
                    selectedApps.push($(this).data('app'));
                });

                if (selectedApps.length === 0) {
                    alert('⚠️ No Changes Selected\n\nPlease select at least one application.');
                    return;
                }

                const appNames = selectedApps.map(app =>
                    app === 'azure' ? 'Azure AD' : $(`#${app}Checkbox`).closest('label').find('strong')
                    .text()
                );

                if (!confirm(
                        `⚠️ Confirm ${action}\n\nYou are about to ${action.toLowerCase()}:\n${appNames.map(n => '• ' + n).join('\n')}\n\nContinue?`
                    )) {
                    return;
                }

                $('#applyChangesBtn').prop('disabled', true).html(
                    '<i class="fas fa-spinner fa-spin"></i> Processing...');
                applyApplicationChanges(userId, action, selectedApps);
            });
        });

        function applyApplicationChanges(userId, action, applications) {
            $.ajax({
                url: `/azure/users/${userId}/manage-access`,
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: JSON.stringify({
                    action: action,
                    applications: applications
                }),
                success: function(data) {
                    if (data.success) {
                        alert(data.message || 'Operation completed successfully');
                        $('#manageAccessModal').modal('hide');
                        loadUsers();
                    } else {
                        $('#applyChangesBtn').prop('disabled', false).html(
                            '<i class="fas fa-check"></i> Apply Changes');
                        alert('❌ Operation Failed\n\n' + data.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error:', error);
                    $('#applyChangesBtn').prop('disabled', false).html(
                        '<i class="fas fa-check"></i> Apply Changes');
                    alert(`❌ Network Error\n\n${error}`);
                }
            });
        }

        // ────────────────────────────────────────────
        // SYNC & RETRY FUNCTIONS
        // ────────────────────────────────────────────
        function syncOdooUsers() {
            const $syncBtn = $('#syncBtn');
            const originalText = $syncBtn.html();
            $syncBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Syncing...');

            $.ajax({
                url: '{{ route('azure.sync.odoo.sync.ajax') }}',
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                timeout: 120000,
                success: function(response) {
                    if (response.success) {
                        alert(
                            `✅ Sync Completed!\n\nProcessed: ${response.stats.processed}\nInserted: ${response.stats.inserted}\nUpdated: ${response.stats.updated}`
                        );
                        loadUsers();
                    } else {
                        alert('❌ Sync Failed\n\n' + (response.message || 'Unknown error'));
                    }
                    $syncBtn.prop('disabled', false).html(originalText);
                },
                error: function(xhr) {
                    alert('❌ Sync Failed\n\n' + (xhr.responseJSON?.message || 'Network error'));
                    $syncBtn.prop('disabled', false).html(originalText);
                }
            });
        }

        function retryAllPendingUsers() {
            if (!confirm('⚠️ Retry Provisioning\n\nThis will attempt to activate all pending users.\n\nContinue?')) return;

            const $retryBtn = $('#retryProvisioningBtn');
            const originalText = $retryBtn.html();
            $retryBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Processing...');

            $.ajax({
                url: '{{ route('azure.sync.activate-users') }}',
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                timeout: 180000,
                success: function() {
                    alert('✅ Activation completed!');
                    loadUsers();
                    $retryBtn.prop('disabled', false).html(originalText);
                },
                error: function(xhr) {
                    alert('❌ Activation Failed\n\n' + (xhr.responseJSON?.message || 'Network error'));
                    $retryBtn.prop('disabled', false).html(originalText);
                }
            });
        }

        // ────────────────────────────────────────────
        // MODAL CLEANUP
        // ────────────────────────────────────────────
        $('#manageAccessModal').on('hidden.bs.modal', function() {
            resetModalState();
            $('#modal-user-id, #modal-user-name, #modal-user-status').val('');
            currentUserApplicationStatus = {
                azure: false,
                d365: false,
                ops: false,
                biz: false,
                fit: false
            };
        });
    </script>
@stop
