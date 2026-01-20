@extends('adminlte::page')

@section('title', 'Inactive Users Management')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1><i class="fas fa-user-times text-danger"></i> Inactive Users Management</h1>
            <p class="text-muted mb-0">Manage disabled system users</p>
        </div>
        <div>
            <a href="{{ route('azure.dashboard.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </div>
@stop

@section('content')

<!-- Users Table Card -->
<div class="card">
    <div class="card-header">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h3 class="card-title mb-0">
                    <i class="fas fa-users"></i> Inactive Users
                    <span class="badge badge-danger ml-2">{{ $users->total() }}</span>
                </h3>
            </div>
            <div class="col-md-6 text-right">
                <div class="d-inline-flex align-items-center">
                    <div class="input-group input-group-sm mr-2" style="width: 350px;">
                        <input type="text" 
                            id="searchUsers" 
                            class="form-control form-control-sm" 
                            placeholder="Search... (Press Enter)" 
                            value="{{ request('search') }}"
                            autocomplete="off">
                        <div class="input-group-append">
                            <button class="btn btn-outline-secondary" type="button" id="clearSearch" title="Clear search">
                                <i class="fas fa-times"></i>
                            </button>
                            <button class="btn btn-primary" type="button" id="searchButton" title="Search">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

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
                        <span class="badge badge-danger">{{ $user->Status ?? 'Inactive' }}</span>
                    </td>
                    <td>
                        @if ($user->Id)
                            <div class="btn-group btn-group-sm" role="group">
                                <a href="{{ route('azure.users.show', $user->Id) }}" 
                                    class="btn btn-info btn-sm" 
                                    title="View User">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <button class="btn btn-warning btn-sm btn-disabled" 
                                    disabled
                                    title="Enable user first to edit"
                                    style="cursor: not-allowed; opacity: 0.6;">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-success btn-sm btn-manage-access" 
                                    data-user-id="{{ $user->Id }}"
                                    data-user-name="{{ addslashes($user->displayName) }}"
                                    data-user-status="{{ $user->Status }}"
                                    title="Manage Access - Enable User">
                                    <i class="fas fa-cog"></i>
                                </button>
                            </div>
                        @else
                            <button class="btn btn-warning btn-sm"
                                onclick="fixUserRecord('{{ $user->userPrincipalName }}')" 
                                title="Fix missing ID">
                                <i class="fas fa-wrench"></i> Fix Record
                            </button>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="text-center text-muted py-4">
                        <i class="fas fa-users fa-3x mb-3"></i>
                        <p>No inactive users found</p>
                        @if(request('search'))
                        <button class="btn btn-sm btn-outline-primary" onclick="clearAllFilters()">
                            <i class="fas fa-redo"></i> Clear Search
                        </button>
                        @endif
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($users->hasPages())
    <div class="card-footer clearfix">
        <div class="row align-items-center">
            <div class="col-md-6">
                <span class="text-muted">
                    Showing {{ $users->firstItem() ?? 0 }} to {{ $users->lastItem() ?? 0 }} of {{ $users->total() }} inactive users
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
                            <a class="page-link" href="{{ $users->appends(request()->except('page'))->url(1) }}">First</a>
                        </li>
                    @endif

                    {{-- Previous Page Link --}}
                    @if ($users->onFirstPage())
                        <li class="page-item disabled">
                            <span class="page-link">&laquo;</span>
                        </li>
                    @else
                        <li class="page-item">
                            <a class="page-link" href="{{ $users->appends(request()->except('page'))->previousPageUrl() }}">&laquo;</a>
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
                                <a class="page-link" href="{{ $users->appends(request()->except('page'))->url($page) }}">{{ $page }}</a>
                            </li>
                        @endif
                    @endforeach

                    {{-- Next Page Link --}}
                    @if ($users->hasMorePages())
                        <li class="page-item">
                            <a class="page-link" href="{{ $users->appends(request()->except('page'))->nextPageUrl() }}">&raquo;</a>
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
                            <a class="page-link" href="{{ $users->appends(request()->except('page'))->url($users->lastPage()) }}">Last</a>
                        </li>
                    @endif
                </ul>
            </div>
        </div>
    </div>
    @endif
</div>

{{-- ============================================ --}}
{{-- MANAGE USER ACCESS MODAL - SAME AS DASHBOARD --}}
{{-- ============================================ --}}
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

                {{-- User Info Banner --}}
                <div class="alert alert-info mb-3">
                    <strong><i class="fas fa-user"></i> <span id="modal-user-display"></span></strong>
                    <span class="badge badge-secondary ml-2" id="modal-status-badge">Loading...</span>
                </div>

                {{-- Loading Indicator --}}
                <div id="loadingApplicationStatus" class="text-center py-3" style="display: none;">
                    <i class="fas fa-spinner fa-spin fa-2x text-primary"></i>
                    <p class="mt-2 text-muted">Loading application status...</p>
                </div>

                {{-- Azure AD Master Control --}}
                <div class="card mb-3 border-primary">
                    <div class="card-body py-2">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="azureCheckbox" data-app="azure">
                            <label class="custom-control-label font-weight-bold" for="azureCheckbox">
                                <i class="fab fa-microsoft text-primary"></i>
                                <span id="azureActionLabel">Enable Azure AD & All Applications</span>
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

                {{-- Applications List (Compact) --}}
                <div class="card" id="applicationsCard">
                    <div class="card-body p-3">

                        {{-- D365 --}}
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

                        {{-- OPS/SCM --}}
                        <div class="d-flex align-items-center justify-content-between py-2 border-bottom">
                            <div class="d-flex align-items-center flex-grow-1">
                                <div class="custom-control custom-checkbox mb-0">
                                    <input type="checkbox" class="custom-control-input app-checkbox" id="opsCheckbox"
                                        data-app="ops">
                                    <label class="custom-control-label mb-0" for="opsCheckbox">
                                        <i class="fas fa-warehouse text-warning mr-2"></i>
                                        <strong>OPS/SCM</strong>
                                        <span class="badge badge-info badge-sm ml-2">User Level</span>
                                    </label>
                                </div>
                            </div>
                            <span class="badge badge-sm ml-3" id="ops-status-badge">Checking...</span>
                        </div>

                        {{-- BIZ PRO --}}
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

                        {{-- FIT Express --}}
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

                {{-- Summary Section --}}
                <div class="alert alert-info mt-3" id="changesSummary" style="display: none;">
                    <strong><i class="fas fa-info-circle"></i> Summary:</strong>
                    <span id="summaryText">No changes selected</span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="$('#manageAccessModal').modal('hide');">
                    <i class="fas fa-times"></i> Cancel
                </button>
                <button type="button" class="btn btn-success" id="applyChangesBtn" disabled>
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
        background: linear-gradient(135deg, #dc3545, #c82333);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: bold;
        font-size: 14px;
    }
    
    .btn-group-sm > .btn {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
    }
    
    /* Pagination Styles */
    .pagination {
        margin-bottom: 0;
    }
    
    .page-item.active .page-link {
        background-color: #dc3545;
        border-color: #dc3545;
    }
    
    .page-link {
        color: #dc3545;
    }
    
    .page-link:hover {
        color: #c82333;
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

    /* Visual indication when checkboxes are controlled by Azure AD */
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

    /* Disabled button styling for inactive user prevention */
    .btn-manage-access.restriction-active {
        cursor: not-allowed !important;
        opacity: 0.5 !important;
        pointer-events: none;
    }
</style>
@stop

@section('js')
<script>
    // ════════════════════════════════════════════════════════════════════════════════════
    // ⚙️ CONFIGURATION - Toggle this to enable/disable inactive user re-enablement
    // ════════════════════════════════════════════════════════════════════════════════════
    
    const PREVENT_INACTIVE_USER_REENABLEMENT = true; // 🔴 Set to false to allow re-enabling inactive users
    
    // ════════════════════════════════════════════════════════════════════════════════════

    // ────────────────────────────────────────────
    // GLOBAL VARIABLES
    // ────────────────────────────────────────────
    let currentUserApplicationStatus = {
        azure: false,
        d365: false,
        ops: false,
        biz: false,
        fit: false
    };

    // ────────────────────────────────────────────
    // SEARCH FUNCTIONS - WITH REAL-TIME SEARCH
    // ────────────────────────────────────────────
    
    let searchTimeout; // For debouncing search requests

    /**
     * Real-time search as user types (with 300ms debounce)
     */
    document.getElementById('searchUsers').addEventListener('input', function(e) {
        console.log('🔍 Search input changed:', e.target.value);
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            console.log('⏰ Performing debounced search');
            performSearch();
        }, 300);
    });

    /**
     * Keyboard shortcuts for search
     */
    document.getElementById('searchUsers').addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            console.log('⏎ Enter pressed - immediate search');
            clearTimeout(searchTimeout);
            performSearch();
        }
        
        if (e.key === 'Escape') {
            console.log('⎋ Escape pressed - clearing search');
            e.target.value = '';
            clearTimeout(searchTimeout);
            performSearch();
        }
    });

    /**
     * Search button click handler
     */
    document.getElementById('searchButton').addEventListener('click', function() {
        console.log('🔘 Search button clicked');
        clearTimeout(searchTimeout);
        performSearch();
    });

    /**
     * Clear search button
     */
    document.getElementById('clearSearch').addEventListener('click', function() {
        console.log('❌ Clear button clicked');
        clearTimeout(searchTimeout);
        document.getElementById('searchUsers').value = '';
        performSearch();
    });

    /**
     * Main search function
     */
    function performSearch() {
        const search = document.getElementById('searchUsers').value.trim();
        const url = new URL(window.location.href);
        
        if (search) {
            url.searchParams.set('search', search);
        } else {
            url.searchParams.delete('search');
        }
        
        url.searchParams.delete('page');
        
        console.log('🔗 Navigating to:', url.toString());
        window.location.href = url.toString();
    }

    /**
     * Clear all filters
     */
    function clearAllFilters() {
        console.log('🧹 Clearing all filters');
        clearTimeout(searchTimeout);
        document.getElementById('searchUsers').value = '';
        const url = new URL(window.location.href);
        url.searchParams.delete('search');
        url.searchParams.delete('page');
        window.location.href = url.toString();
    }

    console.log('✅ Inactive Users - Search functionality loaded with real-time search');

    // ────────────────────────────────────────────
    // MANAGE USER ACCESS MODAL
    // ────────────────────────────────────────────

    /**
     * Open the Manage Access Modal and load user's current application status
     */
    function openManageAccessModal(userId, userName, userStatus) {
        console.log('🔷 Opening Manage Access Modal', {
            userId,
            userName,
            userStatus,
            preventReenablement: PREVENT_INACTIVE_USER_REENABLEMENT
        });

        // Check if re-enablement is prevented
        if (PREVENT_INACTIVE_USER_REENABLEMENT && userStatus.toLowerCase() !== 'active') {
            showReenablementPreventionMessage(userName);
            return;
        }

        // Set basic user info
        $('#modal-user-id').val(userId);
        $('#modal-user-name').val(userName);
        $('#modal-user-status').val(userStatus);
        $('#modal-user-display').text(userName);

        // Update status badge
        const statusBadge = $('#modal-status-badge');
        statusBadge.removeClass('badge-success badge-warning badge-danger');

        if (userStatus.toLowerCase() === 'active') {
            statusBadge.addClass('badge-success').text('Active');
            $('#azureActionLabel').text('Disable Azure AD & All Applications');
        } else {
            statusBadge.addClass('badge-danger').text('Inactive');
            $('#azureActionLabel').text('Enable Azure AD & All Applications');
        }

        // Reset checkboxes and summary
        resetModalState();

        // Show loading state
        $('#loadingApplicationStatus').show();
        $('#applicationsCard').hide();

        // Open modal
        $('#manageAccessModal').modal('show');

        // Load user's current application status
        loadUserApplicationStatus(userId);
    }

    /**
     * Show prevention message when trying to re-enable inactive user
     */
    function showReenablementPreventionMessage(userName) {
        const message = `
            <div class="modal fade" id="preventionMessageModal" tabindex="-1" role="dialog">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-header bg-danger text-white">
                            <h5 class="modal-title">
                                <i class="fas fa-ban"></i> Re-enablement Not Allowed
                            </h5>
                            <button type="button" class="close text-white" data-dismiss="modal">
                                <span>&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="alert alert-warning mb-3">
                                <i class="fas fa-exclamation-triangle"></i>
                                <strong>User:</strong> ${userName}
                            </div>
                            <p class="mb-3">
                                <i class="fas fa-info-circle text-info"></i>
                                Inactive users cannot be re-enabled through this interface.
                            </p>
                            <p class="mb-0 text-muted small">
                                <strong>Reason:</strong> This restriction is in place to prevent accidental re-activation 
                                of disabled user accounts. If you need to re-enable this user, please contact your system administrator.
                            </p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">
                                <i class="fas fa-check"></i> Understood
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        // Remove existing prevention modal if any
        $('#preventionMessageModal').remove();
        
        // Add and show new modal
        $('body').append(message);
        $('#preventionMessageModal').modal('show');
        
        // Clean up on close
        $('#preventionMessageModal').on('hidden.bs.modal', function () {
            $(this).remove();
        });

        console.log('🚫 Re-enablement prevented for inactive user:', userName);
    }

    /**
     * Load user's current application access status from backend
     */
    function loadUserApplicationStatus(userId) {
        console.log('📡 Loading application status for user:', userId);

        fetch(`/azure/users/${userId}/application-status`, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                }
            })
            .then(response => {
                console.log('📥 Response status:', response.status);

                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }

                return response.json();
            })
            .then(data => {
                console.log('✅ Application status loaded:', data);

                if (data.success) {
                    currentUserApplicationStatus = data.data;
                    updateApplicationStatusUI();
                } else {
                    console.error('❌ Failed to load application status:', data.message);
                    showErrorInModal('Failed to load application status. Please try again.');
                }

                $('#loadingApplicationStatus').hide();
                $('#applicationsCard').show();
            })
            .catch(error => {
                console.error('❌ Error loading application status:', error);
                showErrorInModal(`Network error: ${error.message}. Please check your connection.`);
                $('#loadingApplicationStatus').hide();
                $('#applicationsCard').show();
            });
    }

    /**
     * Update UI based on user's current application status
     */
    function updateApplicationStatusUI() {
        console.log('🎨 Updating UI with status:', currentUserApplicationStatus);

        const apps = ['d365', 'ops', 'biz', 'fit'];

        apps.forEach(app => {
            const checkbox = $(`#${app}Checkbox`);
            const statusBadge = $(`#${app}-status-badge`);
            const hasAccess = currentUserApplicationStatus[app];

            // Grey out checkbox if user doesn't have access
            if (!hasAccess) {
                checkbox.prop('disabled', true);
                checkbox.closest('.custom-control').addClass('text-muted');
                statusBadge.removeClass('badge-success badge-danger badge-warning')
                    .addClass('badge-secondary')
                    .text('No Access');
            } else {
                checkbox.prop('disabled', false);
                checkbox.closest('.custom-control').removeClass('text-muted');
                statusBadge.removeClass('badge-secondary badge-danger badge-warning')
                    .addClass('badge-success')
                    .text('Active');
            }
        });

        console.log('✅ UI updated successfully');
    }

    /**
     * Reset modal to initial state
     */
    function resetModalState() {
        console.log('🔄 Resetting modal state');

        // Uncheck all checkboxes
        $('.app-checkbox, #azureCheckbox').prop('checked', false);

        // Enable all checkboxes (will be disabled later based on status)
        $('.app-checkbox').prop('disabled', false);
        $('.custom-control').removeClass('text-muted azure-controlled');

        // Reset status badges
        $('.badge-sm').removeClass('badge-success badge-danger badge-secondary')
            .addClass('badge-warning')
            .text('Checking...');

        // Hide summary
        $('#changesSummary').hide();

        // Enable apply button
        $('#applyChangesBtn').prop('disabled', true);
    }

    /**
     * Show error message in modal
     */
    function showErrorInModal(message) {
        const errorHtml = `
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle"></i> ${message}
            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
    `;
        $('#applicationsCard').before(errorHtml);
    }

    // ────────────────────────────────────────────
    // CHECKBOX EVENT HANDLERS
    // ────────────────────────────────────────────

    /**
     * Azure AD checkbox controls all other checkboxes
     */
    $('#azureCheckbox').on('change', function() {
        const isChecked = $(this).is(':checked');
        console.log('🔷 Azure AD checkbox changed:', isChecked);

        if (isChecked) {
            // Check ONLY application checkboxes that user has access to
            $('.app-checkbox').each(function() {
                const app = $(this).data('app');

                // Only check if user has access
                if (currentUserApplicationStatus[app]) {
                    $(this).prop('checked', true);
                    // Make it read-only/disabled while Azure AD is checked
                    $(this).prop('disabled', true);
                    // Add visual indication it's controlled by Azure AD
                    $(this).closest('.custom-control').addClass('azure-controlled');

                    console.log(`  ✓ Checked and disabled: ${app}`);
                }
            });
        } else {
            // Uncheck all and restore original state
            $('.app-checkbox').each(function() {
                const app = $(this).data('app');

                // Uncheck the checkbox
                $(this).prop('checked', false);

                // Remove azure-controlled class
                $(this).closest('.custom-control').removeClass('azure-controlled');

                // Re-enable only if user has access to this application
                if (currentUserApplicationStatus[app]) {
                    $(this).prop('disabled', false);
                    console.log(`  ↻ Unchecked and enabled: ${app} (user has access)`);
                } else {
                    $(this).prop('disabled', true);
                    console.log(`  ↻ Unchecked but kept disabled: ${app} (no access)`);
                }
            });
        }

        updateChangesSummary();
    });

    /**
     * Individual application checkbox change handler
     */
    $('.app-checkbox').on('change', function() {
        console.log('📦 Application checkbox changed:', $(this).data('app'));

        // Count accessible apps
        const accessibleApps = $('.app-checkbox').filter(function() {
            const app = $(this).data('app');
            return currentUserApplicationStatus[app];
        });

        const checkedAccessibleApps = accessibleApps.filter(':checked');

        console.log('📊 Checkbox count:', {
            total_accessible: accessibleApps.length,
            checked: checkedAccessibleApps.length
        });

        // If ALL accessible apps are checked, auto-check Azure AD
        if (accessibleApps.length > 0 && checkedAccessibleApps.length === accessibleApps.length) {
            console.log('✅ All accessible apps checked - auto-checking Azure AD');
            $('#azureCheckbox').prop('checked', true);

            // Make all accessible apps read-only
            accessibleApps.each(function() {
                $(this).prop('disabled', true);
                $(this).closest('.custom-control').addClass('azure-controlled');
            });
        }
        // If any accessible app is unchecked, uncheck Azure AD
        else if (checkedAccessibleApps.length < accessibleApps.length) {
            console.log('❌ Not all apps checked - unchecking Azure AD');
            $('#azureCheckbox').prop('checked', false);

            // Re-enable all accessible app checkboxes
            accessibleApps.each(function() {
                $(this).prop('disabled', false);
                $(this).closest('.custom-control').removeClass('azure-controlled');
            });
        }

        updateChangesSummary();
    });

    /**
     * Update the changes summary at the bottom of modal
     */
    function updateChangesSummary() {
        const checkedApps = [];

        // Check Azure AD
        if ($('#azureCheckbox').is(':checked')) {
            checkedApps.push('Azure AD (+ All Applications)');
        } else {
            // Check individual apps
            $('.app-checkbox:checked').each(function() {
                const appName = $(this).closest('label').find('strong').text();
                checkedApps.push(appName);
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
    // APPLY CHANGES BUTTON
    // ────────────────────────────────────────────

    $('#applyChangesBtn').on('click', function() {
        const userId = $('#modal-user-id').val();
        const userStatus = $('#modal-user-status').val();
        const action = userStatus.toLowerCase() === 'active' ? 'Disable' : 'Enable';

        console.log('🚀 Apply Changes clicked', {
            userId,
            userStatus,
            action
        });

        // Collect selected applications
        const selectedApps = [];

        if ($('#azureCheckbox').is(':checked')) {
            selectedApps.push('azure');
        }

        $('.app-checkbox:checked').each(function() {
            selectedApps.push($(this).data('app'));
        });

        if (selectedApps.length === 0) {
            alert('⚠️ No Changes Selected\n\nPlease select at least one application to modify.');
            return;
        }

        // Build confirmation message
        const appNames = selectedApps.map(app => {
            if (app === 'azure') return 'Azure AD';
            return $(`#${app}Checkbox`).closest('label').find('strong').text();
        });

        const confirmMessage =
            `⚠️ Confirm ${action}\n\nYou are about to ${action.toLowerCase()}:\n${appNames.map(n => '• ' + n).join('\n')}\n\nContinue?`;

        if (!confirm(confirmMessage)) {
            console.log('❌ User cancelled operation');
            return;
        }

        // Disable button and show loading
        $('#applyChangesBtn').prop('disabled', true).html(
            '<i class="fas fa-spinner fa-spin"></i> Processing...');

        // Send request to backend
        applyApplicationChanges(userId, action, selectedApps);
    });

    /**
     * Send application changes to backend
     */
    function applyApplicationChanges(userId, action, applications) {
        console.log('📤 Sending request to backend', {
            userId,
            action,
            applications
        });

        fetch(`/azure/users/${userId}/manage-access`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({
                    action: action,
                    applications: applications
                })
            })
            .then(response => {
                console.log('📥 Response status:', response.status);

                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }

                return response.json();
            })
            .then(data => {
                console.log('✅ Backend response:', data);

                $('#applyChangesBtn').prop('disabled', false).html('<i class="fas fa-check"></i> Apply Changes');

                if (data.success) {
                    // Show detailed results
                    showResultsModal(data);

                    // Close modal
                    $('#manageAccessModal').modal('hide');

                    // Reload page if Azure AD was modified
                    if (data.reload) {
                        setTimeout(() => {
                            window.location.reload();
                        }, 3000);
                    }
                } else {
                    alert('❌ Operation Failed\n\n' + data.message);
                }
            })
            .catch(error => {
                console.error('❌ Network error:', error);
                $('#applyChangesBtn').prop('disabled', false).html('<i class="fas fa-check"></i> Apply Changes');
                alert(`❌ Network Error\n\n${error.message}\n\nPlease check your connection and try again.`);
            });
    }

    /**
     * Show detailed results in alert/modal
     */
    function showResultsModal(data) {
        let message = data.message;

        if (data.summary) {
            message = `${data.summary.message}`;
        }

        // Show results
        alert(message);

        console.log('📊 Operation results:', data.results);
    }

    // ────────────────────────────────────────────
    // MODAL CLOSE HANDLERS
    // ────────────────────────────────────────────

    /**
     * Clean up when modal is hidden
     */
    $('#manageAccessModal').on('hidden.bs.modal', function() {
        console.log('🚪 Modal closed - cleaning up');

        // Reset all checkboxes
        $('.app-checkbox, #azureCheckbox').prop('checked', false);

        // Enable all checkboxes
        $('.app-checkbox').prop('disabled', false);
        $('.custom-control').removeClass('text-muted azure-controlled');

        // Reset status badges
        $('.badge-sm').removeClass('badge-success badge-danger badge-secondary')
            .addClass('badge-warning')
            .text('Checking...');

        // Hide summary
        $('#changesSummary').hide();

        // Reset apply button
        $('#applyChangesBtn').prop('disabled', true).html('<i class="fas fa-check"></i> Apply Changes');

        // Clear user info
        $('#modal-user-id').val('');
        $('#modal-user-name').val('');
        $('#modal-user-status').val('');

        // Reset status object
        currentUserApplicationStatus = {
            azure: false,
            d365: false,
            ops: false,
            biz: false,
            fit: false
        };

        console.log('✅ Cleanup complete');
    });

    // ────────────────────────────────────────────
    // BUTTON EVENT DELEGATION FOR MANAGE ACCESS
    // ────────────────────────────────────────────

    /**
     * Handle manage access button clicks with delegation
     * (Handles dynamically loaded content)
     */
    $(document).on('click', '.btn-manage-access', function(e) {
        e.preventDefault();
        
        const userId = $(this).data('user-id');
        const userName = $(this).data('user-name');
        const userStatus = $(this).data('user-status');
        
        openManageAccessModal(userId, userName, userStatus);
    });

    // ────────────────────────────────────────────
    // APPLY RESTRICTION STYLING ON PAGE LOAD
    // ────────────────────────────────────────────

    /**
     * Apply visual restriction to buttons if prevention is enabled
     */
    function applyRestrictionStyling() {
        if (PREVENT_INACTIVE_USER_REENABLEMENT) {
            $('.btn-manage-access').each(function() {
                const userStatus = $(this).data('user-status');
                
                if (userStatus && userStatus.toLowerCase() !== 'active') {
                    $(this)
                        .addClass('restriction-active')
                        .attr('title', 'Re-enablement not allowed for inactive users')
                        .css('cursor', 'not-allowed');
                }
            });
            
            console.log('🔒 Restriction styling applied to inactive user buttons');
        } else {
            $('.btn-manage-access').removeClass('restriction-active');
            console.log('🔓 Restriction styling removed - re-enablement allowed');
        }
    }

    // Apply styling on page load
    $(document).ready(function() {
        applyRestrictionStyling();
        
        console.log('✅ Inactive Users Page Initialized', {
            preventReenablement: PREVENT_INACTIVE_USER_REENABLEMENT
        });
    });

    // ────────────────────────────────────────────
    // INITIALIZE
    // ────────────────────────────────────────────
    console.log('✅ Inactive Users - Manage Access Modal scripts loaded successfully');
    console.log(`🔒 Inactive User Re-enablement Prevention: ${PREVENT_INACTIVE_USER_REENABLEMENT ? 'ENABLED' : 'DISABLED'}`);
</script>
@stop