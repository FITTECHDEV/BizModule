@extends('adminlte::page')

@section('title', 'Pending Entity Approvals')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0">Pending Entity Approvals</h1>
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
                            <span class="info-box-number">{{ $counts['not_submitted'] }}</span>
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
                        </div>
                    </div>
                </a>
            </div>
        </div>

        <!-- DataTable -->
        <div class="card shadow-sm position-relative">
            <div class="card-header bg-light">
                <h3 class="card-title">Entities Pending Approval</h3>
                <div class="card-tools">
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
        <div class="modal fade" id="cancelModal" tabindex="-1" aria-labelledby="cancelModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-light">
                        <h5 class="modal-title" id="cancelModalLabel">Reject Entity</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="cancelForm">
                            <input type="hidden" name="entityID" id="entityID">
                            <div class="form-group">
                                <label for="level" class="form-label">Rejection Type</label>
                                <select name="level" id="level" class="form-control">
                                    <option value="" selected disabled>Select an option</option>
                                    <option value="Not Submitted">Not Submitted (Draft)</option>
                                    <option value="Review">Reject for Review</option>
                                    <option value="Rejected">Reject Permanently</option>
                                </select>
                                <div class="invalid-feedback">Please select a rejection type.</div>
                            </div>
                            <div class="form-group">
                                <label for="reason" class="form-label">Reason</label>
                                <textarea name="reason" id="reason" class="form-control" rows="4" placeholder="Enter rejection reason"></textarea>
                                <div class="invalid-feedback">Please provide a reason.</div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" id="close-modal" data-bs-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary" id="submit-entity">
                            <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                            Save changes
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <style>
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
            font-weight: 500;
        }
        .info-box-number {
            font-size: 1.2rem;
            font-weight: bold;
        }
        .info-box-link {
            text-decoration: none !important;
            color: inherit;
        }
        /* DataTable Styling */
        #client-party-table {
            font-size: 0.9rem;
        }
        #client-party-table thead th {
            background-color: #f8f9fa;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.85rem;
        }
        #client-party-table tbody tr:hover {
            background-color: #f1f3f5;
        }
        .dataTables_wrapper .dataTables_filter input {
            border-radius: 20px;
            padding: 5px 10px;
        }
        /* Modal Styling */
        #cancelModal .modal-content {
            border-radius: 10px;
        }
        #cancelModal .form-control {
            border-radius: 5px;
        }
        #cancelModal .btn-primary {
            position: relative;
        }
        #cancelModal .spinner-border {
            position: absolute;
            left: 10px;
            top: 50%;
            transform: translateY(-50%);
        }
        #cancelModal .invalid-feedback {
            display: none;
        }
        #cancelModal .is-invalid ~ .invalid-feedback {
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
        }
    </style>
@stop

@section('js')
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

            window.user_raise_ticket = `{{ Auth::user()->can('request-client-party') ? 'true' : 'false' }}`;
            window.user_view_my_office = `{{ !Auth::user()->can('approve-all-entity-request') ? 'true' : 'false' }}`;
            window.office = `{{ Auth::user()->OfficeCode ?? '' }}`;

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
                    url: '{!! route('datatables.approvalEntityRequests') !!}',
                    beforeSend: function() {
                        $('#table-loader').removeClass('d-none');
                    },
                    complete: function() {
                        $('#table-loader').addClass('d-none');
                    },
                    error: function(xhr) {
                        $('#table-loader').addClass('d-none');
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
                            let actions = `
                                <div class="btn-group">
                                    <a href="/client-party/show/${row.Id}" class="btn btn-sm btn-flat btn-info" title="View"><i class="fas fa-eye"></i></a>
                                    <a href="#" id="deny" code="${row.Id}" class="btn btn-sm btn-flat btn-danger" title="Reject"><i class="fas fa-times"></i></a>
                            `;
                            if (row.Status === 'PendingCountryLevelApproval' || row.Status === 'PendingGroupLevelApproval' || row.Status === 'Submitted') {
                                actions += `<a href="/client-party/show/${row.Id}" class="btn btn-sm btn-flat btn-success" title="Approve"><i class="fas fa-check"></i></a>`;
                            }
                            actions += `</div>`;
                            return actions;
                        }
                    }
                ],
                language: {
                    processing: '<div class="spinner-border text-primary" role="status"><span class="sr-only">Loading...</span></div>'
                }
            });

            // New Request Button
            if (window.user_raise_ticket === 'true') {
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

            // Office Filter
            if (window.user_view_my_office === 'true') {
                searchOffice(window.office);
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
            $('#allPending').on('click', function() {
                searchTable('PendingCountryLevelApproval|PendingGroupLevelApproval|Submitted', true);
            });

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
                        },
                        complete: function() {
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
@stop
