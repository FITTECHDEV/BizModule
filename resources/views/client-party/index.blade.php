@extends('adminlte::page')

@section('title', 'Client Party Dashboard')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="m-0">Client Party Dashboard</h1>
            <small class="text-muted">
                <i class="fas fa-calendar-alt"></i>
                Showing entity requests from last 3 months ({{ \Carbon\Carbon::now()->subMonths(3)->format('M d, Y') }} -
                {{ \Carbon\Carbon::now()->format('M d, Y') }})
            </small>
        </div>
    </div>
@stop

@section('plugins.Datatables', true)

@section('content')
    <div class="container-fluid p-3">
        <!-- Date Range Info Alert -->
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <i class="fas fa-info-circle"></i>
            <strong>Data Scope:</strong> This dashboard displays entity requests created within the last 3 months only.
            Older records are not shown to improve performance and focus on recent activity.
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>

        <!-- Info Boxes -->
        <div class="row">
            {{-- 1. Not Submitted --}}
            <div class="col-12 col-sm-6 col-md-4 col-lg-auto mb-3">
                <a href="#" id="notSubmitted" class="info-box-link">
                    <div class="info-box bg-gradient-secondary shadow-sm hover-scale">
                        <span class="info-box-icon"><i class="fas fa-save"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text" data-short="Draft" data-full="Not Submitted">Draft</span>
                            <span class="info-box-number">
                                {{ $clientParty->clientPartyCount('Status', 'Not Submitted') }}
                            </span>
                            <span class="info-box-description text-xs">Last 3 mo</span>
                        </div>
                    </div>
                </a>
            </div>

            {{-- 2. Country Approval --}}
            <div class="col-12 col-sm-6 col-md-4 col-lg-auto mb-3">
                <a href="#" id="pendingCountry" class="info-box-link">
                    <div class="info-box bg-gradient-primary shadow-sm hover-scale">
                        <span class="info-box-icon"><i class="fas fa-hourglass-half"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text" data-short="Country" data-full="Country Approval">Country</span>
                            <span class="info-box-number">
                                {{ $clientParty->clientPartyCount('Status', 'PendingCountryLevelApproval') }}
                            </span>
                            <span class="info-box-description text-xs">Last 3 mo</span>
                        </div>
                    </div>
                </a>
            </div>

            {{-- 3. Group Approval --}}
            <div class="col-12 col-sm-6 col-md-4 col-lg-auto mb-3">
                <a href="#" id="pendingGroup" class="info-box-link">
                    <div class="info-box bg-gradient-info shadow-sm hover-scale">
                        <span class="info-box-icon"><i class="fas fa-hourglass-half"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text" data-short="Group" data-full="Group Approval">Group</span>
                            <span class="info-box-number">
                                {{ $clientParty->clientPartyCount('Status', 'PendingGroupLevelApproval') }}
                            </span>
                            <span class="info-box-description text-xs">Last 3 mo</span>
                        </div>
                    </div>
                </a>
            </div>

            {{-- 4. Submitted --}}
            <div class="col-12 col-sm-6 col-md-4 col-lg-auto mb-3">
                <a href="#" id="submitted" class="info-box-link">
                    <div class="info-box bg-gradient-warning shadow-sm hover-scale">
                        <span class="info-box-icon"><i class="fas fa-paper-plane"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text" data-short="Submitted" data-full="Submitted">Submitted</span>
                            <span class="info-box-number">
                                {{ $clientParty->clientPartyCount('Status', 'Submitted') }}
                            </span>
                            <span class="info-box-description text-xs">Last 3 mo</span>
                        </div>
                    </div>
                </a>
            </div>

            {{-- 5. Pending IT Verification --}}
            <div class="col-12 col-sm-6 col-md-4 col-lg-auto mb-3">
                <a href="#" id="pendingITVerification" class="info-box-link">
                    <div class="info-box bg-gradient-purple shadow-sm hover-scale">
                        <span class="info-box-icon"><i class="fas fa-laptop-code"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text" data-short="IT Verify" data-full="Pending IT Verification">IT
                                Verify</span>
                            <span class="info-box-number">
                                {{ $clientParty->clientPartyCount('Status', 'PendingITVerification') }}
                            </span>
                            <span class="info-box-description text-xs">Last 3 mo</span>
                        </div>
                    </div>
                </a>
            </div>

            {{-- 6. Created --}}
            <div class="col-12 col-sm-6 col-md-4 col-lg-auto mb-3">
                <a href="#" id="created" class="info-box-link">
                    <div class="info-box bg-gradient-success shadow-sm hover-scale">
                        <span class="info-box-icon"><i class="fas fa-check-circle"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text" data-short="Created" data-full="Created">Created</span>
                            <span class="info-box-number">
                                {{ $clientParty->clientPartyCount('Status', 'Created') }}
                            </span>
                            <span class="info-box-description text-xs">Last 3 mo</span>
                        </div>
                    </div>
                </a>
            </div>

            {{-- 7. Rejected --}}
            <div class="col-12 col-sm-6 col-md-4 col-lg-auto mb-3">
                <a href="#" id="rejected" class="info-box-link">
                    <div class="info-box bg-gradient-danger shadow-sm hover-scale">
                        <span class="info-box-icon"><i class="fas fa-times-circle"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text" data-short="Rejected" data-full="Rejected">Rejected</span>
                            <span class="info-box-number">
                                {{ $clientParty->clientPartyCount('Status', 'Rejected') }}
                            </span>
                            <span class="info-box-description text-xs">Last 3 mo</span>
                        </div>
                    </div>
                </a>
            </div>

            {{-- 8. Review --}}
            <div class="col-12 col-sm-6 col-md-4 col-lg-auto mb-3">
                <a href="#" id="review" class="info-box-link">
                    <div class="info-box bg-gradient-info shadow-sm hover-scale">
                        <span class="info-box-icon"><i class="fas fa-sync-alt"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text" data-short="Review" data-full="Review">Review</span>
                            <span class="info-box-number">
                                {{ $clientParty->clientPartyCount('Status', 'Review') }}
                            </span>
                            <span class="info-box-description text-xs">Last 3 mo</span>
                        </div>
                    </div>
                </a>
            </div>

            {{-- 9. All Requests --}}
            <div class="col-12 col-sm-6 col-md-4 col-lg-auto mb-3">
                <a href="#" id="allEntityRequests" class="info-box-link">
                    <div class="info-box bg-gradient-dark shadow-sm hover-scale">
                        <span class="info-box-icon"><i class="fas fa-list"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text" data-short="All" data-full="All Requests">All</span>
                            <span class="info-box-number">
                                {{ $clientParty->clientPartyCount('Status', '*') }}
                            </span>
                            <span class="info-box-description text-xs">Last 3 mo</span>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        <!-- DataTable -->
        <div class="card shadow-sm position-relative">
            <div class="card-header bg-light">
                <h3 class="card-title">
                    Entity Requests
                    <small class="text-muted">(Last 3 Months)</small>
                </h3>
                <div class="card-tools">
                    <span class="badge badge-info">
                        <i class="fas fa-calendar-alt"></i>
                        {{ \Carbon\Carbon::now()->subMonths(3)->format('M Y') }} -
                        {{ \Carbon\Carbon::now()->format('M Y') }}
                    </span>
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body p-0">
                <!-- Spinner Loader -->
                <div id="table-loader" class="loader-overlay d-none">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover table-hover table-bordered text-nowrap" id="client-party-table">
                        <thead class="bg-light">
                            <tr>
                                <th>Code</th>
                                <th>Company Name</th>
                                <th>Office</th>
                                <th title="Operation Entity Type">OET</th>
                                <th title="Legal Entity Type">LET</th>
                                <th>Status</th>
                                <th>Requested By</th>
                                <th>Requested On</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Enhanced Rejection Modal with SweetAlert2 Style -->
        <div class="modal fade" id="cancel-entity-modal" tabindex="-1" role="dialog"
            aria-labelledby="cancel-entity-label-modal" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg">
                    <div class="modal-header bg-gradient-danger text-white border-0">
                        <h5 class="modal-title d-flex align-items-center" id="cancel-entity-label-modal">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            Reject Entity Request
                        </h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close modal">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="alert alert-warning border-0 bg-light" role="alert">
                            <i class="fas fa-info-circle text-warning mr-2"></i>
                            <strong>Warning:</strong> This action will reject the entity request. Please provide a clear
                            reason for rejection.
                        </div>
                        <form id="entity-modal-form">
                            <input type="hidden" name="entityID" id="entity-id-ID">
                            <div class="form-group">
                                <label for="level" class="form-group-label font-weight-bold">
                                    <i class="fas fa-layer-group mr-2 text-primary"></i>
                                    Rejection Type
                                </label>
                                <select name="level" id="level" class="form-control form-control-lg border-2">
                                    <option value="" disabled selected>Select rejection type...</option>
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
                        <button type="button" class="btn btn-secondary btn-lg px-4" data-dismiss="modal">
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
        #cancel-entity-modal {
            font-family: 'Roboto', Arial, sans-serif !important;
        }

        /* Enhanced Info Box Styling with Hover Expansion */
        .info-box {
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .info-box-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, 0.2);
            transition: all 0.3s ease;
        }

        .info-box-link {
            text-decoration: none !important;
            color: inherit;
        }

  /* Base styles for cards in a row */
    @media (min-width: 992px) {
    .row {
        display: flex;
        flex-wrap: nowrap;
        gap: 8px;
        margin-bottom: 1.5rem;
        align-items: stretch;
    }

    .row > [class*='col-lg-'] {
        flex: 0 0 calc(11.11% - 8px);
        max-width: calc(11.11% - 8px);
        transition: flex 0.4s cubic-bezier(0.4, 0, 0.2, 1),
                    max-width 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }

    /* When hovering over the row, shrink all cards slightly */
    .row:hover > [class*='col-lg-'] {
        flex: 0 0 calc(10.5% - 8px);
        max-width: calc(10.5% - 8px);
    }

    /* Then expand the hovered card */
    .row > [class*='col-lg-']:hover {
        flex: 0 0 calc(16% - 8px) !important;
        max-width: calc(16% - 8px) !important;
        z-index: 10;
    }

    .info-box {
        height: 95px;
        display: flex;
        align-items: center;
        position: relative;
    }

    .info-box:hover {
        transform: scale(1.02);
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15), 0 10px 20px rgba(0, 0, 0, 0.1) !important;
    }

    .info-box-content {
        padding: 8px 6px;
        flex: 1;
        display: flex;
        flex-direction: column;
        justify-content: center;
        overflow: hidden;
    }

    .info-box-text {
        font-size: 0.7rem;
        line-height: 1.2;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* Show full text on hover */
    .info-box:hover .info-box-text {
        font-size: 0.75rem;
        white-space: normal;
        word-break: break-word;
    }

    .info-box-number {
        font-size: 1.6rem;
        margin: 4px 0;
        font-weight: 700;
        line-height: 1;
        transition: all 0.3s ease;
    }

    .info-box:hover .info-box-number {
        font-size: 1.75rem;
    }

    .info-box-description {
        font-size: 0.6rem;
        opacity: 0.9;
        font-style: italic;
        white-space: nowrap;
        transition: all 0.3s ease;
    }

    .info-box:hover .info-box-description {
        font-size: 0.65rem;
        opacity: 1;
    }

    .info-box-icon {
        width: 45px;
        font-size: 1.4rem;
        flex-shrink: 0;
        transition: all 0.3s ease;
    }

    .info-box:hover .info-box-icon {
        font-size: 1.5rem;
        width: 48px;
    }
    }

/* Extra large screens */
    @media (min-width: 1400px) {
    .row:hover > [class*='col-lg-'] {
        flex: 0 0 calc(10.2% - 8px);
        max-width: calc(10.2% - 8px);
    }

    .row > [class*='col-lg-']:hover {
        flex: 0 0 calc(18% - 8px) !important;
        max-width: calc(18% - 8px) !important;
    }

    .info-box-text {
        font-size: 0.75rem;
    }

    .info-box:hover .info-box-text {
        font-size: 0.8rem;
    }

    .info-box-number {
        font-size: 1.7rem;
    }

    .info-box:hover .info-box-number {
        font-size: 1.85rem;
    }
}     /* Enhanced DataTable Styling */
        #client-party-table {
            font-size: 0.9rem;
            font-weight: 400;
        }

        #client-party-table th {
            color: #333;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.8rem;
            letter-spacing: 0.5px;
            border: none;
        }

        #client-party-table tbody tr {
            transition: all 0.2s ease;
        }

        #client-party-table tbody tr:hover {
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
            transform: scale(1.01);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
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
        }

        /* Enhanced Modal Styling */
        #cancel-entity-modal .modal-content {
            border-radius: 15px;
            border: none;
        }

        #cancel-entity-modal .modal-header {
            border-radius: 15px 15px 0 0;
        }

        #cancel-entity-modal .form-control {
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        #cancel-entity-modal .form-control:focus {
            transform: scale(1.02);
            box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
        }

        #cancel-entity-modal .btn {
            border-radius: 25px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        #cancel-entity-modal .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        #cancel-entity-modal .invalid-feedback {
            display: none;
            font-weight: 500;
        }

        #cancel-entity-modal .is-invalid~.invalid-feedback {
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
        }
    </style>
@stop

@section('js')
    <!-- SweetAlert2 JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js"></script>

    <script src="{{ asset('vendor/adminlte/plugins/jquery/jquery.min.js') }}"></script>
    <script>
        console.log('jQuery defined:', typeof jQuery !== 'undefined');
    </script>

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

        /* Ajax CSRF Setup */
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $(document).ready(function() {
            // Show loader before DataTable AJAX request
            $('#table-loader').removeClass('d-none');

            const clientPartyTable = $('#client-party-table').DataTable({
                responsive: true,
                order: [
                    [7, 'desc']
                ], // Order by CreatedOn descending
                processing: true,
                serverSide: true,
                searching: true,
                stateSave: true,
                dom: '<"d-flex justify-content-between align-items-center mb-3"lfB>t<"d-flex justify-content-between"ip>',
                buttons: [{
                        extend: 'csv',
                        className: 'btn btn-sm btn-outline-secondary',
                        text: '<i class="fas fa-file-csv"></i> CSV',
                        title: 'Entity_Requests_Last_3_Months_' + new Date().toISOString().slice(0, 10)
                    },
                    {
                        extend: 'pdf',
                        className: 'btn btn-sm btn-outline-secondary',
                        text: '<i class="fas fa-file-pdf"></i> PDF',
                        title: 'Entity Requests (Last 3 Months)'
                    }
                ],
                ajax: {
                    url: '{!! route('datatables.allEntityRequests') !!}',
                    beforeSend: function() {
                        $('#table-loader').removeClass('d-none');
                    },
                    complete: function() {
                        $('#table-loader').addClass('d-none');
                    },
                    error: async function(xhr) {
                        $('#table-loader').addClass('d-none');
                        console.error('DataTable load failed:', xhr);

                        // 🎯 Professional error handling with SweetAlert2
                        if (xhr.status === 0) {
                            await SwalPro.networkError();
                        } else {
                            await SwalPro.error(
                                'Failed to Load Data',
                                `Unable to load entity requests: ${xhr.responseJSON?.message || 'Unknown server error'}`
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
                                data === 'PendingGroupLevelApproval' ? 'Pending Group Approval' :
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
                        data: 'CreatedOn',
                        name: 'CreatedOn'
                    },
                    {
                        data: 'Action',
                        name: 'Action',
                        orderable: false,
                        searchable: false
                    }
                ],
                language: {
                    processing: '<div class="d-flex align-items-center"><div class="spinner-border text-primary mr-3" role="status"></div><span>Loading entity requests...</span></div>',
                    emptyTable: 'No entity requests found in the last 3 months',
                    search: '<i class="fas fa-search mr-2"></i>',
                    searchPlaceholder: 'Search entity requests...'
                }
            });

            // Add note about 3-month filter to the search area
            $('#client-party-table_filter').append(
                '<small class="text-muted ml-2"><i class="fas fa-info-circle"></i> Showing last 3 months only</small>'
            );

            // New Request Button with enhanced styling
            if ({{ Auth::user()->can('request-client-party') ? 'true' : 'false' }}) {
                $('#client-party-table_filter').prepend(`
                    <div class="btn-group mr-3">
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

            // Enhanced Filter Buttons with loading feedback
            function createFilterHandler(status, statusName) {
                return async function(e) {
                    e.preventDefault();

                    // Visual feedback
                    const $this = $(this);
                    const originalHtml = $this.html();
                    $this.addClass('shadow-lg').find('.info-box').css('transform', 'scale(0.95)');

                    searchTable(status);

                    // Show brief success feedback
                    setTimeout(() => {
                        $this.removeClass('shadow-lg').find('.info-box').css('transform', '');
                        if (status === '') {
                            SwalPro.success('Filter Applied', 'Showing all entity requests', 1500);
                        } else {
                            SwalPro.success('Filter Applied', `Showing ${statusName} requests`,
                                1500);
                        }
                    }, 300);
                };
            }

            $('#notSubmitted').on('click', createFilterHandler('Not Submitted', 'Not Submitted'));
            $('#pendingCountry').on('click', createFilterHandler('PendingCountryLevelApproval',
                'Pending Country Approval'));
            $('#pendingGroup').on('click', createFilterHandler('PendingGroupLevelApproval',
                'Pending Group Approval'));
            $('#submitted').on('click', createFilterHandler('Submitted', 'Submitted'));
            $('#pendingITVerification').on('click', createFilterHandler('PendingITVerification',
                'Pending IT Verification')); // ← ADD THIS
            $('#created').on('click', createFilterHandler('Created', 'Created'));
            $('#rejected').on('click', createFilterHandler('Rejected', 'Rejected'));
            $('#review').on('click', createFilterHandler('Review', 'Review'));
            $('#allEntityRequests').on('click', createFilterHandler('', 'All'));

            function searchTable(param) {
                $('#table-loader').removeClass('d-none');
                clientPartyTable.column(5).search(param, false, false, false).draw();
            }

            // Hide loader after draw
            clientPartyTable.on('draw.dt', function() {
                $('#table-loader').addClass('d-none');
            });

            // 🔥 Enhanced Rejection Modal Handlers
            $('#client-party-table').on('click', 'a#deny', function() {
                rejectEntity($(this).attr('code'));
            });

            $('#cancel-entity-modal').on('hidden.bs.modal', function() {
                $('#entity-modal-form')[0].reset();
                $('#level, #reason').removeClass('is-invalid');
            });

            // 🔥 Enhanced Submit Entity Handler with SweetAlert2
            $('#submit-entity').on('click', async function() {
                const $btn = $(this);
                const $spinner = $btn.find('.spinner-border');
                const formData = {
                    id: $('#entity-id-ID').val(),
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

                    // 🎯 Success with specific messaging
                    await SwalPro.rejectionSuccess(formData.level);
                    $('#cancel-entity-modal').modal('hide');
                    clientPartyTable.ajax.reload();

                } catch (xhr) {
                    console.error('Rejection failed:', xhr.responseJSON?.message || 'Unknown error');

                    // 🎯 Enhanced error handling
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

            // 🔥 Enhanced Reject Entity Function
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
                        $('#cancel-entity-modal').modal('show');
                        $('#entity-id-ID').val(id);
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
            // Text expansion on hover
            $('.info-box').hover(
                function() {
                    // On mouse enter - show full text
                    const $text = $(this).find('.info-box-text');
                    const fullText = $text.attr('data-full');
                    if (fullText) {
                        $text.text(fullText);
                    }
                },
                function() {
                    // On mouse leave - show short text
                    const $text = $(this).find('.info-box-text');
                    const shortText = $text.attr('data-short');
                    if (shortText) {
                        $text.text(shortText);
                    }
                }
            );
        });
    </script>

    <!-- Other Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bs-stepper/dist/js/bs-stepper.min.js"></script>
    <script src="{{ asset('js/client-party/party-entity.js') }}"></script>
    <script src="{{ asset('js/client-party/validation.js') }}"></script>
    <script src="{{ asset('js/client-party/sub-form.js') }}"></script>
    <script src="{{ asset('js/client-party/jquery-number/jquery.number.min.js') }}"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
    <script>
        window.User = {
            value: '{{ auth()->user()->can('edit-client-party') }}'
        };
    </script>
    <script src="{{ asset('js/client-party/show-create-client-party.js') }}"></script>
@stop
