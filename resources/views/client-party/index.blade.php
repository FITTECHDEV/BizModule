@extends('adminlte::page')

@section('title', 'Client Party Dashboard')

@section('content_header')
    <div class="d-flex justify-content-between n-items-center">
        <h1 class="m-0">Client Party Dashboard</h1>
    </div>
@stop

@section('plugins.Datatables', true)

@section('content')
    <div class="container-fluid p-3">
        <!-- Info Boxes -->
        <div class="row">
            <div class="col-12 col-sm-6 col-md-3 col-lg-2 mb-3">
                <a href="#" id="notSubmitted" class="info-box-link">
                    <div class="info-box bg-gradient-secondary shadow-sm hover-scale">
                        <span class="info-box-icon"><i class="fas fa-save"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Not Submitted</span>
                            <span class="info-box-number">{{ $clientParty->clientPartyCount('Status', 'Not Submitted') }}</span>
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
                            <span class="info-box-number">{{ $clientParty->clientPartyCount('Status', 'PendingCountryLevelApproval') }}</span>
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
                            <span class="info-box-number">{{ $clientParty->clientPartyCount('Status', 'PendingGroupLevelApproval') }}</span>
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
                            <span class="info-box-number">{{ $clientParty->clientPartyCount('Status', 'Submitted') }}</span>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-12 col-sm-6 col-md-3 col-lg-2 mb-3">
                <a href="#" id="created" class="info-box-link">
                    <div class="info-box bg-gradient-success shadow-sm hover-scale">
                        <span class="info-box-icon"><i class="fas fa-check-circle"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Created</span>
                            <span class="info-box-number">{{ $clientParty->clientPartyCount('Status', 'Created') }}</span>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-12 col-sm-6 col-md-3 col-lg-2 mb-3">
                <a href="#" id="rejected" class="info-box-link">
                    <div class="info-box bg-gradient-danger shadow-sm hover-scale">
                        <span class="info-box-icon"><i class="fas fa-times-circle"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Rejected</span>
                            <span class="info-box-number">{{ $clientParty->clientPartyCount('Status', 'Rejected') }}</span>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-12 col-sm-6 col-md-3 col-lg-2 mb-3">
                <a href="#" id="review" class="info-box-link">
                    <div class="info-box bg-gradient-info shadow-sm hover-scale">
                        <span class="info-box-icon"><i class="fas fa-sync-alt"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Review</span>
                            <span class="info-box-number">{{ $clientParty->clientPartyCount('Status', 'Review') }}</span>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-12 col-sm-6 col-md-3 col-lg-2 mb-3">
                <a href="#" id="allEntityRequests" class="info-box-link">
                    <div class="info-box bg-gradient-dark shadow-sm hover-scale">
                        <span class="info-box-icon"><i class="fas fa-list"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">All Requests</span>
                            <span class="info-box-number">{{ $clientParty->clientPartyCount('Status', '*') }}</span>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        <!-- DataTable -->
        <div class="card shadow-sm position-relative">
            <div class="card-header bg-light">
                <h3 class="card-title">Entity Requests</h3>
                <div class="card-tools">
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

        <!-- Rejection Modal -->
        <div class="modal fade" id="cancel-entity-modal" tabindex="-entity-modal" class="modal fade" id="modal-tab" aria-labelledby="cancel-entity-tab" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-light">
                        <h5 class="modal-title" id="cancel-entity-label-modal">Reject Entity</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close modal">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form id="entity-modal-form">
                            <input type="hidden" name="entityID" id="entity-id-ID">
                            <div class="form-group">
                                <label for="level" class="form-group-label">Rejection Type</label>
                                <select name="level" id="level" class="form-control">
                                    <option value="" disabled selected>Select an option</option>
                                    <option value="Not Submitted">Not Submitted (Draft)</option>
                                    <option value="Review">Reject for Review</option>
                                    <option value="Rejected">Reject Permanently</option>
                                </select>
                                <div class="invalid-feedback">Please select an invalid feedback type.</div>
                            </div>
                            <div class="form-group">
                                <label for="reason" class="reasoning" class="form-label">Reason</label>
                                <textarea name="reason" id="reason" class="form-control" rows="reason-rows" placeholder="Enter reason for reason"></textarea>
                                <div class="invalid-feedback">Please provide a reason.</div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="modal-footer-button" id="modal-footer-close">Cancel</button>
                        <button type="button" class="btn-primary-modal" id="submit-entity">
                            <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                            <button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
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

        /* Info Box Styling */
        .info-box {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            border-radius: 8px;
            overflow: hidden;
        }
        .info-box:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2) !important;
        }
        .info-box-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 50px;
            font-size: 1.5rem;
        }
        .info-box-content {
            padding: 10px;
        }
        .info-box-text {
            font-size: 0.9rem;
            font-weight: 400;
        }
        .info-box-number {
            font-size: 1.2rem;
            font-weight: 700;
        }
        .info-box-link {
            text-decoration: none !important;
            color: inherit;
        }

        /* DataTable Styling */
        #client-party-table {
            font-size: 0.9rem;
            font-weight: 400;
        }
        #client-party-table th {
            background-color: rgba(153, 190, 255, 0.667);
            font-weight: 500;
            text-transform: uppercase;
            font-size: 0.8rem;
        }
        #client-party-table tbody tr:hover {
            background-color: rgba(51, 160, 255, 0.333);
        }
        .dataTables_wrapper .dataTables_filter input {
            width: 200px;
            border-radius: 4px;
            padding: 6px 12px;
            border: 1px solid #ced4da;
            font-size: 0.9rem;
        }
        .dataTables_wrapper .dataTables_filter label {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* Modal Styling */
        #cancel-entity-modal .modal-content {
            border-radius: 10px;
        }
        #cancel-entity-modal .form-control {
            border-radius: 5px;
        }
        #cancel-entity-modal .btn-primary {
            position: relative;
        }
        #cancel-entity-modal .spinner-border {
            position: absolute;
            left: 10px;
            top: 50%;
            transform: translateY(-50%);
        }
        #cancel-entity-modal .invalid-feedback {
            display: none;
        }
        #cancel-entity-modal .is-invalid ~ .invalid-feedback {
            display: block;
        }

        /* Loader Styling */
        .loader-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.7);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
        }

        /* Responsive Adjustments */
        @media (max-width: 767px) {
            .info-box-text {
                font-size: 0.8rem;
            }
            .info-box-number {
                font-size: 1rem;
            }
            #client-party-table {
                font-size: 0.8rem;
            }
            .dataTables_wrapper .dataTables_filter input {
                width: 150px; /* Slightly smaller for mobile */
            }
        }
    </style>
@stop

@section('js')
    <script src="{{ asset('vendor/adminlte/plugins/jquery/jquery.min.js') }}"></script>
    <script>
        console.log('jQuery defined:', typeof jQuery !== 'undefined');
    </script>
    <script>
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
                order: [[7, 'desc']],
                processing: true,
                serverSide: true,
                searching: true,
                stateSave: true,
                dom: '<"d-flex justify-content-between align-items-center mb-3"lfB>t<"d-flex justify-content-between"ip>',
                buttons: [
                    {
                        extend: 'csv',
                        className: 'btn btn-sm btn-outline-secondary',
                        text: '<i class="fas fa-file-csv"></i> CSV'
                    },
                    {
                        extend: 'pdf',
                        className: 'btn btn-sm btn-outline-secondary',
                        text: '<i class="fas fa-file-pdf"></i> PDF'
                    }
                ],
                ajax: {
                    url: '{!! route('datatables.allEntityRequests') !!}',
                    beforeSend: function() {
                        $('#table-loader').removeClass('d-none'); // Ensure loader is visible
                    },
                    complete: function() {
                        $('#table-loader').addClass('d-none'); // Hide loader after request
                    },
                    error: function(xhr) {
                        $('#table-loader').addClass('d-none'); // Hide loader on error
                        console.error('DataTable load failed:', xhr);
                        alert('Failed to load table data: ' + (xhr.responseJSON?.message || 'Unknown error'));
                    }
                },
                columns: [
                    { data: 'Code', name: 'Code' },
                    { data: 'Name', name: 'Name' },
                    { data: 'Office', name: 'Office' },
                    { data: 'OperatioEntityType', name: 'OperatioEntityType' },
                    { data: 'LegalEntityType', name: 'LegalEntityType' },
                    {
                        data: 'Status',
                        name: 'Status',
                        render: function(data) {
                            return data === 'Not Submitted' ? 'Not Submitted' :
                                   data === 'Submitted' ? 'Submitted' :
                                   data === 'PendingCountryLevelApproval' ? 'Pending Country Approval' :
                                   data === 'PendingGroupLevelApproval' ? 'Pending Group Approval' :
                                   data === 'Created' ? 'Created' :
                                   data === 'Rejected' ? 'Rejected' :
                                   data === 'Review' ? 'Review' : data;
                        }
                    },
                    { data: 'CreatedBy', name: 'CreatedBy' },
                    { data: 'CreatedOn', name: 'CreatedOn' },
                    {
                        data: 'Action',
                        name: 'Action',
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row) {
                            return `
                                <div class="btn-group">
                                    <a href="/client-party/show/${row.Id}" class="btn btn-sm btn-flat btn-info" title="View"><i class="fas fa-eye"></i></a>
                                    <a href="#" id="deny" code="${row.Id}" class="btn btn-sm btn-flat btn-danger" title="Reject"><i class="fas fa-times"></i></a>
                                </div>
                            `;
                        }
                    }
                ],
                language: {
                    processing: '<div class="spinner-border text-primary" role="status"><span class="sr-only">Loading...</span></div>'
                }
            });

            // New Request Button
            if ({{ Auth::user()->can('request-client-party') ? 'true' : 'false' }}) {
                $('#client-party-table_filter').prepend(`
                    <div class="btn-group mr-2">
                        <button type="button" class="btn btn-primary btn-sm dropdown-toggle" data-bs-toggle="dropdown">
                            <i class="fas fa-plus"></i> New Request
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="/client-party/create?type=operational">Operational Entity</a></li>
                            <li><a class="dropdown-item" href="/client-party/create?type=accounting">Accounting Entity</a></li>
                        </ul>
                    </div>
                `);
            }

            // Filter Buttons
            $('#notSubmitted').on('click', function() {
                searchTable('Not Submitted');
            });
            $('#pendingCountry').on('click', function() {
                searchTable('PendingCountryLevelApproval');
            });
            $('#pendingGroup').on('click', function() {
                searchTable('PendingGroupLevelApproval');
            });
            $('#submitted').on('click', function() {
                searchTable('Submitted');
            });
            $('#created').on('click', function() {
                searchTable('Created');
            });
            $('#rejected').on('click', function() {
                searchTable('Rejected');
            });
            $('#review').on('click', function() {
                searchTable('Review');
            });
            $('#allEntityRequests').on('click', function() {
                searchTable('');
            });

            function searchTable(param) {
                $('#table-loader').removeClass('d-none'); // Show loader during filter
                clientPartyTable.column(5).search(param, false, false, false).draw();
            }

            // Hide loader after draw (including filtering)
            clientPartyTable.on('draw.dt', function() {
                $('#table-loader').addClass('d-none');
            });

            // Rejection Modal Handlers
            $('#client-party-table').on('click', 'a#deny', function() {
                rejectEntity($(this).attr('code'));
            });

            $('#close-modal').on('click', function() {
                $('#cancelForm')[0].reset();
                $('#cancelModal').modal('hide');
                $('#level, #reason').removeClass('is-invalid');
            });

            $('#submit-entity').on('click', function() {
                const $btn = $(this);
                const $spinner = $btn.find('.spinner-border');
                const formData = {
                    id: $('#entityID').val(),
                    level: $('#level').val(),
                    reason: $('#reason').val(),
                };

                let isValid = true;
                if (!formData.level) {
                    $('#level').addClass('is-invalid');
                    isValid = false;
                } else {
                    $('#level').removeClass('is-invalid');
                }
                if (!formData.reason.trim()) {
                    $('#reason').addClass('is-invalid');
                    isValid = false;
                } else {
                    $('#reason').removeClass('is-invalid');
                }

                if (isValid) {
                    $spinner.removeClass('d-none');
                    $btn.prop('disabled', true);

                    $.ajax({
                        url: '/client-party/reject',
                        type: 'POST',
                        data: formData,
                        success: function(response) {
                            alert(`Entity successfully ${formData.level.toLowerCase()}.`);
                            location.reload();
                        },
                        error: function(xhr) {
                            console.error('Rejection failed:', xhr.responseJSON?.message || 'Unknown error');
                            alert('Failed to reject entity: ' + (xhr.responseJSON?.message || 'Unknown error'));
                            $spinner.addClass('d-none');
                            $btn.prop('disabled', false);
                        }
                    });
                }
            });

            function rejectEntity(id) {
                $.ajax({
                    url: '/client-party/show/' + id,
                    type: 'GET',
                    success: function(data) {
                        if (data.LegalEntityType && data.LegalEntityType.trim()) {
                            $('#cancelModal').modal('show');
                            $('#entityID').val(id);
                        } else {
                            alert('Only legal entities can be rejected.');
                        }
                    },
                    error: function(xhr) {
                        console.error('Error fetching entity:', xhr);
                        alert('Failed to verify entity type.');
                    }
                });
            }
        });
    </script>
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
