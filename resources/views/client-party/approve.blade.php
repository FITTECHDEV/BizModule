@extends('adminlte::page')

@section('title', 'Pending Entity Approvals')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0">Pending Entity Approvals</h1>
        <small class="text-muted">
            <i class="fas fa-tasks"></i>
            Manage pending entity approval requests
        </small>
    </div>
@stop

@section('plugins.Datatables', true)

@section('content')
    <div class="container-fluid p-3">
        <!-- Status Info Alert -->
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <i class="fas fa-info-circle"></i>
            <strong>Approval Dashboard:</strong> Review and manage entity requests pending your approval.
            Use the filters below to quickly find specific types of requests.
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>

        <!-- Info Boxes -->
        <div class="row">
            <div class="col-12 col-sm-6 col-md-3 col-lg-2 mb-3">
                <a href="#" id="notSubmitted" class="info-box-link">
                    <div class="info-box bg-gradient-secondary shadow-sm hover-scale">
                        <span class="info-box-icon"><i class="fas fa-save"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Not Submitted</span>
                            <span class="info-box-number">{{ $counts['not_submitted'] }}</span>
                            <span class="info-box-description text-xs">Draft status</span>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-12 col-sm-6 col-md-3 col-lg-2 mb-3">
                <a href="#" id="pendingCountry" class="info-box-link">
                    <div class="info-box bg-gradient-primary shadow-sm hover-scale">
                        <span class="info-box-icon"><i class="fas fa-hourglass-half"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Country Approval</span>
                            <span class="info-box-number">{{ $counts['pending_country'] }}</span>
                            <span class="info-box-description text-xs">Country level</span>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-12 col-sm-6 col-md-3 col-lg-2 mb-3">
                <a href="#" id="pendingGroup" class="info-box-link">
                    <div class="info-box bg-gradient-info shadow-sm hover-scale">
                        <span class="info-box-icon"><i class="fas fa-hourglass-half"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Group Approval</span>
                            <span class="info-box-number">{{ $counts['pending_group'] }}</span>
                            <span class="info-box-description text-xs">Group level</span>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-12 col-sm-6 col-md-3 col-lg-2 mb-3">
                <a href="#" id="submitted" class="info-box-link">
                    <div class="info-box bg-gradient-warning shadow-sm hover-scale">
                        <span class="info-box-icon"><i class="fas fa-paper-plane"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Submitted</span>
                            <span class="info-box-number">{{ $counts['submitted'] }}</span>
                            <span class="info-box-description text-xs">Awaiting review</span>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-12 col-sm-6 col-md-3 col-lg-2 mb-3">
                <a href="#" id="allPending" class="info-box-link">
                    <div class="info-box bg-gradient-dark shadow-sm hover-scale">
                        <span class="info-box-icon"><i class="fas fa-list"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">All Pending</span>
                            <span class="info-box-number">{{ $counts['all_pending'] }}</span>
                            <span class="info-box-description text-xs">Total pending</span>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        <!-- DataTable -->
        <div class="card shadow-sm position-relative">
            <div class="card-header bg-light">
                <h3 class="card-title">
                    <i class="fas fa-clipboard-check mr-2"></i>
                    Entities Pending Approval
                </h3>
                <div class="card-tools">
                    <span class="badge badge-warning">
                        <i class="fas fa-clock"></i>
                        Requires Action
                    </span>
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body p-0">
                <div id="table-loader" class="loader-overlay d-none">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered table-hover display text-sm" id="client-party-table">
                        <thead class="bg-light">
                            <tr>
                                <th>Code</th>
                                <th>Company Name</th>
                                <th>Office</th>
                                <th title="Operation Entity Type">OET</th>
                                <th title="Legal Entity Type">LET</th>
                                <th>Status</th>
                                <th>Requested By</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Enhanced Rejection Modal with SweetAlert2 Style -->
        <div class="modal fade" id="cancelModal" tabindex="-1" aria-labelledby="cancelModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg">
                    <div class="modal-header bg-gradient-danger text-white border-0">
                        <h5 class="modal-title d-flex align-items-center" id="cancelModalLabel">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            Reject Entity Request
                        </h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="alert alert-warning border-0 bg-light" role="alert">
                            <i class="fas fa-info-circle text-warning mr-2"></i>
                            <strong>Warning:</strong> This action will reject the entity request. Please provide a clear
                            reason for rejection.
                        </div>
                        <form id="cancelForm">
                            <input type="hidden" name="entityID" id="entityID">
                            <div class="form-group">
                                <label for="level" class="form-label font-weight-bold">
                                    <i class="fas fa-layer-group mr-2 text-primary"></i>
                                    Rejection Type
                                </label>
                                <select name="level" id="level" class="form-control form-control-lg border-2">
                                    <option value="" selected disabled>Select rejection type...</option>
                                    <option value="Not Submitted">Return to Draft (Not Submitted)</option>
                                    <option value="Review">Send Back for Review</option>
                                    <option value="Rejected">Reject Permanently</option>
                                </select>
                                <div class="invalid-feedback">
                                    <i class="fas fa-exclamation-circle mr-1"></i>
                                    Please select a rejection type.
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="reason" class="form-label font-weight-bold">
                                    <i class="fas fa-comment-dots mr-2 text-primary"></i>
                                    Reason for Rejection
                                </label>
                                <textarea name="reason" id="reason" class="form-control form-control-lg border-2" rows="4"
                                    placeholder="Please provide a detailed reason for rejecting this entity request. This will help the requester understand what needs to be corrected."></textarea>
                                <div class="invalid-feedback">
                                    <i class="fas fa-exclamation-circle mr-1"></i>
                                    Please provide a detailed reason for rejection.
                                </div>
                                <small class="form-text text-muted">
                                    <i class="fas fa-lightbulb mr-1"></i>
                                    Tip: Be specific about what needs to be corrected or improved.
                                </small>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer border-0 bg-light">
                        <button type="button" class="btn btn-secondary btn-lg px-4" id="close-modal"
                            data-dismiss="modal">
                            <i class="fas fa-times mr-2"></i>Cancel
                        </button>
                        <button type="button" class="btn btn-danger btn-lg px-4" id="submit-entity">
                            <span class="spinner-border spinner-border-sm d-none mr-2" role="status"
                                aria-hidden="true"></span>
                            <i class="fas fa-ban mr-2"></i>Submit Rejection
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    <style>
        /* Import Google Font */
        @import url('https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap');

        /* Apply Roboto font to the dashboard */
        .container-fluid,
        .content-header,
        .info-box,
        #client-party-table,
        #cancelModal {
            font-family: 'Roboto', Arial, sans-serif !important;
        }

        /* Enhanced Info Box Styling */
        .info-box {
            transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .info-box:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1), 0 5px 15px rgba(0, 0, 0, 0.05) !important;
        }

        .info-box-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 60px;
            font-size: 1.8rem;
            background: rgba(255, 255, 255, 0.2);
        }

        .info-box-content {
            padding: 15px;
        }

        .info-box-text {
            font-size: 0.9rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .info-box-number {
            font-size: 1.8rem;
            font-weight: 700;
            margin: 5px 0;
        }

        .info-box-description {
            font-size: 0.75rem;
            opacity: 0.9;
            font-style: italic;
        }

        .info-box-link {
            text-decoration: none !important;
            color: inherit;
        }

        /* Enhanced DataTable Styling */
        #client-party-table {
            font-size: 0.9rem;
            font-weight: 400;
        }

        #client-party-table th {
            background-color: #f8f9fa;
            color: #495057;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.8rem;
            letter-spacing: 0.5px;
            border: 1px solid #dee2e6;
        }

        #client-party-table tbody tr {
            transition: all 0.2s ease;
        }

        #client-party-table tbody tr:hover {
            background-color: rgba(0, 123, 255, 0.1);
            transform: scale(1.01);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .dataTables_wrapper .dataTables_filter input {
            width: 250px;
            border-radius: 25px;
            padding: 8px 16px;
            border: 2px solid #e3f2fd;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }

        .dataTables_wrapper .dataTables_filter input:focus {
            border-color: #2196f3;
            box-shadow: 0 0 0 0.2rem rgba(33, 150, 243, 0.25);
            outline: none;
        }

        .dataTables_wrapper .dataTables_filter label {
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 500;
            margin-bottom: 0;
        }

        /* Enhanced DataTable Controls Layout */
        #client-party-table_wrapper .row:first-child {
            align-items: center;
        }

        #client-party-table_wrapper .col-md-6:last-child {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            gap: 15px;
        }

        .dataTables_filter {
            margin-bottom: 0 !important;
        }

        .dt-buttons {
            margin-bottom: 0 !important;
        }

        /* Enhanced Modal Styling */
        #cancelModal .modal-content {
            border-radius: 15px;
            border: none;
        }

        #cancelModal .modal-header {
            border-radius: 15px 15px 0 0;
        }

        #cancelModal .form-control {
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        #cancelModal .form-control:focus {
            transform: scale(1.02);
            box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
        }

        #cancelModal .btn {
            border-radius: 25px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        #cancelModal .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        #cancelModal .invalid-feedback {
            display: none;
            font-weight: 500;
        }

        #cancelModal .is-invalid~.invalid-feedback {
            display: block;
        }

        /* Enhanced Loader Styling */
        .loader-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(2px);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            border-radius: 8px;
        }

        /* Enhanced Badge Styling */
        .card-header .badge {
            margin-left: 10px;
            font-size: 0.75rem;
            border-radius: 15px;
            padding: 5px 12px;
        }

        /* Action Button Styling */
        .btn-group .btn {
            margin: 0 1px;
            border-radius: 4px;
            transition: all 0.2s ease;
        }

        .btn-group .btn:hover {
            transform: scale(1.1);
            z-index: 1;
        }

        /* SweetAlert2 Custom Styling */
        .swal2-popup {
            border-radius: 15px !important;
            font-family: 'Roboto', sans-serif !important;
        }

        .swal2-title {
            font-weight: 600 !important;
            color: #2c3e50 !important;
        }

        .swal2-content {
            color: #5a6c7d !important;
        }

        .swal2-confirm {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%) !important;
            border: none !important;
            border-radius: 25px !important;
            padding: 10px 30px !important;
            font-weight: 500 !important;
        }

        .swal2-cancel {
            background: linear-gradient(135deg, #6c757d 0%, #495057 100%) !important;
            border: none !important;
            border-radius: 25px !important;
            padding: 10px 30px !important;
            font-weight: 500 !important;
        }

        /* Responsive Adjustments */
        @media (max-width: 767px) {
            .info-box-text {
                font-size: 0.8rem;
            }

            .info-box-number {
                font-size: 1.4rem;
            }

            .info-box-description {
                font-size: 0.7rem;
            }

            #client-party-table {
                font-size: 0.8rem;
            }

            .dataTables_wrapper .dataTables_filter input {
                width: 180px;
            }

            /* Stack controls vertically on mobile */
            #client-party-table_wrapper .col-md-6:last-child {
                flex-direction: column;
                align-items: stretch;
                gap: 10px;
            }

            .dataTables_filter {
                order: 2;
            }

            .dt-buttons {
                order: 3;
            }

            .btn-group {
                order: 1;
                align-self: flex-start;
            }
        }
    </style>
@stop

@section('js')
    <!-- SweetAlert2 JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js"></script>

    <script>
        // 🛠️ Professional SweetAlert2 Utilities
        const SwalPro = {
            success: (title, text = '', timer = 3000) => {
                return Swal.fire({
                    icon: 'success',
                    title: title,
                    text: text,
                    timer: timer,
                    timerProgressBar: true,
                    showConfirmButton: false,
                    toast: true,
                    position: 'top-end',
                    background: '#f8f9fa',
                    iconColor: '#28a745',
                    customClass: {
                        popup: 'colored-toast'
                    }
                });
            },

            error: (title, text = '') => {
                return Swal.fire({
                    icon: 'error',
                    title: title,
                    text: text,
                    confirmButtonColor: '#dc3545',
                    customClass: {
                        confirmButton: 'btn btn-danger btn-lg px-4'
                    }
                });
            },

            warning: (title, text = '') => {
                return Swal.fire({
                    icon: 'warning',
                    title: title,
                    text: text,
                    confirmButtonColor: '#ffc107',
                    confirmButtonText: 'I Understand',
                    customClass: {
                        confirmButton: 'btn btn-warning btn-lg px-4'
                    }
                });
            },

            confirm: (title, text = '', confirmText = 'Yes, Proceed', cancelText = 'Cancel') => {
                return Swal.fire({
                    title: title,
                    text: text,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#28a745',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: confirmText,
                    cancelButtonText: cancelText,
                    reverseButtons: true,
                    customClass: {
                        confirmButton: 'btn btn-success btn-lg px-4 mr-2',
                        cancelButton: 'btn btn-secondary btn-lg px-4'
                    }
                });
            },

            loading: (title = 'Processing...', text = 'Please wait while we process your request.') => {
                return Swal.fire({
                    title: title,
                    text: text,
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
            },

            networkError: () => {
                return Swal.fire({
                    icon: 'error',
                    title: 'Network Error',
                    text: 'Unable to connect to the server. Please check your internet connection and try again.',
                    confirmButtonColor: '#dc3545',
                    footer: '<i class="fas fa-wifi"></i> Check your network connection',
                    customClass: {
                        confirmButton: 'btn btn-danger btn-lg px-4'
                    }
                });
            },

            rejectionSuccess: (level) => {
                const messages = {
                    'Not Submitted': {
                        title: 'Returned to Draft',
                        text: 'Entity request has been returned to draft status.',
                        icon: 'info'
                    },
                    'Review': {
                        title: 'Sent for Review',
                        text: 'Entity request has been sent back for review.',
                        icon: 'warning'
                    },
                    'Rejected': {
                        title: 'Request Rejected',
                        text: 'Entity request has been permanently rejected.',
                        icon: 'error'
                    }
                };

                const message = messages[level] || messages['Rejected'];

                return Swal.fire({
                    icon: message.icon,
                    title: message.title,
                    text: message.text,
                    timer: 3000,
                    timerProgressBar: true,
                    showConfirmButton: false,
                    toast: true,
                    position: 'top-end'
                });
            }
        };

        // Ensure jQuery is available
        (function($) {
            // Ajax CSRF Setup
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $(document).ready(function() {
                // Show loader before DataTable AJAX request
                $('#table-loader').removeClass('d-none');

                window.user_raise_ticket = `{{ Auth::user()->can('request-client-party') ? 'true' : 'false' }}`;
                window.user_view_my_office =
                    `{{ !Auth::user()->can('approve-all-entity-request') ? 'true' : 'false' }}`;
                window.office = `{{ Auth::user()->OfficeCode ?? '' }}`;

                // Check if table element exists before initializing
                if (!$('#client-party-table').length) {
                    console.error('DataTable element not found');
                    $('#table-loader').addClass('d-none');
                    return;
                }

                const clientPartyTable = $('#client-party-table').DataTable({
                    responsive: true,
                    order: [
                        [6, 'desc']
                    ], // Order by CreatedBy column since CreatedOn is hidden
                    processing: true,
                    serverSide: true,
                    searching: true,
                    stateSave: true,
                    dom: '<"row mb-3"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6 d-flex justify-content-end align-items-center"fB>>t<"d-flex justify-content-between"ip>',
                    buttons: [{
                            extend: 'csv',
                            className: 'btn btn-sm btn-outline-secondary',
                            text: '<i class="fas fa-file-csv"></i> CSV',
                            title: 'Pending_Entity_Approvals_' + new Date().toISOString().slice(0,
                                10)
                        },
                        {
                            extend: 'pdf',
                            className: 'btn btn-sm btn-outline-secondary',
                            text: '<i class="fas fa-file-pdf"></i> PDF',
                            title: 'Pending Entity Approvals'
                        }
                    ],
                    ajax: {
                        url: '{!! route('datatables.approvalEntityRequests') !!}',
                        beforeSend: function() {
                            $('#table-loader').removeClass('d-none');
                        },
                        complete: function() {
                            $('#table-loader').addClass('d-none');
                        },
                        error: async function(xhr) {
                            $('#table-loader').addClass('d-none');
                            console.error('DataTable load failed:', xhr);

                            // Professional error handling with SweetAlert2
                            if (xhr.status === 0) {
                                await SwalPro.networkError();
                            } else {
                                await SwalPro.error(
                                    'Failed to Load Data',
                                    `Unable to load approval requests: ${xhr.responseJSON?.message || 'Unknown server error'}`
                                );
                            }
                        }
                    },
                    columns: [{
                            data: 'Code',
                            name: 'Code'
                        },
                        {
                            data: 'Name',
                            name: 'Name'
                        },
                        {
                            data: 'Office',
                            name: 'Office'
                        },
                        {
                            data: 'OperatioEntityType',
                            name: 'OperatioEntityType'
                        },
                        {
                            data: 'LegalEntityType',
                            name: 'LegalEntityType'
                        },
                        {
                            data: 'Status',
                            name: 'Status',
                            render: function(data) {
                                return data === 'Not Submitted' ? 'Not Submitted' :
                                    data === 'Submitted' ? 'Submitted' :
                                    data === 'PendingCountryLevelApproval' ?
                                    'Pending Country Approval' :
                                    data === 'PendingGroupLevelApproval' ?
                                    'Pending Group Approval' :
                                    data === 'Created' ? 'Created' :
                                    data === 'Rejected' ? 'Rejected' :
                                    data === 'Review' ? 'Review' : data;
                            }
                        },
                        {
                            data: 'CreatedBy',
                            name: 'CreatedBy'
                        },
                        {
                            data: 'Action',
                            name: 'Action',
                            orderable: false,
                            searchable: false,
                            render: function(data, type, row) {
                                let actions = `
                                    <div class="btn-group" role="group">
                                        <a href="/client-party/show/${row.Id}" class="btn btn-sm btn-info" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="#" id="deny" code="${row.Id}" class="btn btn-sm btn-danger" title="Reject Request">
                                            <i class="fas fa-times"></i>
                                        </a>
                                `;
                                if (row.Status === 'PendingCountryLevelApproval' || row
                                    .Status === 'PendingGroupLevelApproval' || row.Status ===
                                    'Submitted') {
                                    actions += `
                                        <a href="/client-party/show/${row.Id}" class="btn btn-sm btn-success" title="Approve Request">
                                            <i class="fas fa-check"></i>
                                        </a>
                                    `;
                                }
                                actions += `</div>`;
                                return actions;
                            }
                        }
                    ],
                    language: {
                        processing: '<div class="d-flex align-items-center"><div class="spinner-border text-primary mr-3" role="status"></div><span>Loading approval requests...</span></div>',
                        emptyTable: 'No pending approval requests found',
                        search: '<i class="fas fa-search mr-2"></i>',
                        searchPlaceholder: 'Search approval requests...'
                    }
                });

                // Wait for DataTable to initialize before adding buttons
                clientPartyTable.on('init.dt', function() {
                    // New Request Button with enhanced styling and proper alignment
                    if (window.user_raise_ticket === 'true') {
                        // Check if wrapper exists
                        const $wrapper = $('#client-party-table_wrapper .row:first .col-md-6:last');
                        if ($wrapper.length) {
                            $wrapper.prepend(`
                                <div class="btn-group mr-2 mb-2">
                                    <button type="button" class="btn btn-primary btn-sm dropdown-toggle shadow-sm" data-toggle="dropdown">
                                        <i class="fas fa-plus mr-1"></i> New Request
                                    </button>
                                    <ul class="dropdown-menu shadow">
                                        <li><a class="dropdown-item" href="/client-party/create?type=operational">
                                            <i class="fas fa-cogs mr-2 text-primary"></i>Operational Entity
                                        </a></li>
                                        <li><a class="dropdown-item" href="/client-party/create?type=accounting">
                                            <i class="fas fa-calculator mr-2 text-success"></i>Accounting Entity
                                        </a></li>
                                    </ul>
                                </div>
                            `);
                        }
                    }
                });

                // Office Filter
                if (window.user_view_my_office === 'true') {
                    searchOffice(window.office);
                }

                // Enhanced Filter Buttons with loading feedback
                function createFilterHandler(status, statusName, isRegex = false) {
                    return async function(e) {
                        e.preventDefault();

                        // Visual feedback
                        const $this = $(this);
                        $this.addClass('shadow-lg').find('.info-box').css('transform', 'scale(0.95)');

                        searchTable(status, isRegex);

                        // Show brief success feedback
                        setTimeout(() => {
                            $this.removeClass('shadow-lg').find('.info-box').css('transform',
                                '');
                            SwalPro.success('Filter Applied', `Showing ${statusName} requests`,
                                1500);
                        }, 300);
                    };
                }

                $('#notSubmitted').on('click', createFilterHandler('Not Submitted', 'Not Submitted'));
                $('#pendingCountry').on('click', createFilterHandler('PendingCountryLevelApproval',
                    'Pending Country Approval'));
                $('#pendingGroup').on('click', createFilterHandler('PendingGroupLevelApproval',
                    'Pending Group Approval'));
                $('#submitted').on('click', createFilterHandler('Submitted', 'Submitted'));
                $('#allPending').on('click', createFilterHandler(
                    'PendingCountryLevelApproval|PendingGroupLevelApproval|Submitted', 'All Pending',
                    true));

                function searchTable(param, regex = false) {
                    $('#table-loader').removeClass('d-none');
                    clientPartyTable.column(5).search(param, regex, false, false).draw();
                }

                function searchOffice(param) {
                    clientPartyTable.column(2).search(param, false, false, false).draw();
                }

                // Hide loader after draw
                clientPartyTable.on('draw.dt', function() {
                    $('#table-loader').addClass('d-none');
                });

                // Enhanced Rejection Modal Handlers
                $('#client-party-table').on('click', 'a#deny', function() {
                    rejectEntity($(this).attr('code'));
                });

                $('#close-modal').on('click', function() {
                    $('#cancelForm')[0].reset();
                    $('#cancelModal').modal('hide');
                    $('#level, #reason').removeClass('is-invalid');
                });

                // Enhanced Submit Entity Handler with SweetAlert2
                $('#submit-entity').on('click', async function() {
                    const $btn = $(this);
                    const $spinner = $btn.find('.spinner-border');
                    const formData = {
                        id: $('#entityID').val(),
                        level: $('#level').val(),
                        reason: $('#reason').val(),
                    };

                    let isValid = true;

                    // Enhanced validation with smooth animations
                    if (!formData.level) {
                        $('#level').addClass('is-invalid').focus();
                        isValid = false;
                    } else {
                        $('#level').removeClass('is-invalid');
                    }

                    if (!formData.reason.trim()) {
                        $('#reason').addClass('is-invalid').focus();
                        isValid = false;
                    } else {
                        $('#reason').removeClass('is-invalid');
                    }

                    if (!isValid) {
                        await SwalPro.warning(
                            'Incomplete Information',
                            'Please fill in all required fields before submitting the rejection.'
                        );
                        return;
                    }

                    // Final confirmation with detailed info
                    const confirmResult = await SwalPro.confirm(
                        'Confirm Rejection',
                        `Are you sure you want to ${formData.level.toLowerCase()} this entity request?`,
                        'Yes, Reject Request',
                        'Cancel'
                    );

                    if (!confirmResult.isConfirmed) return;

                    // Show loading state
                    SwalPro.loading('Processing Rejection',
                        'Please wait while we process your rejection...');
                    $spinner.removeClass('d-none');
                    $btn.prop('disabled', true);

                    try {
                        const response = await $.ajax({
                            url: '/client-party/reject',
                            type: 'POST',
                            data: formData
                        });

                        // Success with specific messaging
                        await SwalPro.rejectionSuccess(formData.level);
                        $('#cancelModal').modal('hide');

                        // Reload the page to refresh data and counts
                        setTimeout(() => {
                            location.reload();
                        }, 1500);

                    } catch (xhr) {
                        console.error('Rejection failed:', xhr.responseJSON?.message ||
                            'Unknown error');

                        // Enhanced error handling
                        if (xhr.status === 422) {
                            await SwalPro.error(
                                'Validation Error',
                                'Please check your input and try again.'
                            );
                        } else if (xhr.status === 403) {
                            await SwalPro.error(
                                'Permission Denied',
                                'You do not have permission to reject this entity request.'
                            );
                        } else {
                            await SwalPro.error(
                                'Rejection Failed',
                                xhr.responseJSON?.message ||
                                'An unexpected error occurred. Please try again.'
                            );
                        }
                    } finally {
                        $spinner.addClass('d-none');
                        $btn.prop('disabled', false);
                        Swal.close();
                    }
                });

                // Enhanced Reject Entity Function
                async function rejectEntity(id) {
                    // Show loading while checking entity
                    SwalPro.loading('Verifying Entity', 'Checking entity permissions...');

                    try {
                        const data = await $.ajax({
                            url: '/client-party/show/' + id,
                            type: 'GET'
                        });

                        Swal.close();

                        if (data.LegalEntityType && data.LegalEntityType.trim()) {
                            $('#cancelModal').modal('show');
                            $('#entityID').val(id);
                        } else {
                            await SwalPro.warning(
                                'Cannot Reject Entity',
                                'Only legal entities can be rejected through this interface. Operational entities use a different workflow.'
                            );
                        }
                    } catch (xhr) {
                        Swal.close();
                        console.error('Error fetching entity:', xhr);

                        if (xhr.status === 404) {
                            await SwalPro.error(
                                'Entity Not Found',
                                'The requested entity could not be found. It may have been deleted or moved.'
                            );
                        } else {
                            await SwalPro.error(
                                'Verification Failed',
                                'Unable to verify entity type. Please try again or contact support.'
                            );
                        }
                    }
                }

                // Add enhanced tooltips to info boxes
                $('[data-toggle="tooltip"]').tooltip();

                // Add pulse animation to active filters
                $('.info-box-link').on('click', function() {
                    $('.info-box').removeClass('border border-primary');
                    $(this).find('.info-box').addClass('border border-primary');
                });
            });
        })(jQuery);
    </script>
@stop
