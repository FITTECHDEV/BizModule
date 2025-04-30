@extends('adminlte::page')

@section('title', 'Client-Party-Dashboard')

@section('content_header')
@stop

@section('plugins.Datatables', true)

@section('content')
    <div class="p-2">
        <!-- Existing content unchanged -->
        <div class="row pt-2 pb-0">
            <div class="col-12 col-sm-6 col-md-2">
                <a href="#" id="pending">
                    <div class="info-box">
                        <span class="info-box-icon bg-primary elevation-1">
                            <i class="fa fa-ban"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text text-center">Manager Approval</span>
                            <span class="info-box-number text-center">{{ $clientParty->clientPartyCount('Status', 'PendingManagerApproval') }}</span>
                        </div>
                    </div>
                </a>
            </div>
            <!-- Other info boxes unchanged -->
            <div class="col-12 col-sm-6 col-md-2">
                <a href="#" id="allEntityRequests">
                    <div class="info-box mb-3">
                        <span class="info-box-icon bg-info elevation-1"><i class="fa fa-calculator"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text text-center">Total Requests</span>
                            <span class="info-box-number text-center">{{ $clientParty->clientPartyCount('Status', '*') }}</span>
                        </div>
                    </div>
                </a>
            </div>
        </div>
        <div class="row">
            <div class="col-xl-12 col-lg-12 col-sm-12 col-md-12">
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered table-hover display text-sm" style="width:100%" id="client-party-table">
                                <thead>
                                    <th>CODE</th>
                                    <th>Company Name</th>
                                    <th>OFFICE</th>
                                    <th title="Operation Entity Type">OET</th>
                                    <th title="Legal Entity Type">LET</th>
                                    <th>Status</th>
                                    <th>RequestedBy</th>
                                    <th>RequestedOn</th>
                                    <th>Action</th>
                                </thead>
                                <tbody></tbody>
                            </table>
                            <div class="modal fade" id="cancelModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="exampleModalLabel">Reject Entity Options</h5>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">×</span>
                                            </button>
                                        </div>
                                        <div class="modal-body">
                                            <form action="" id="cancelForm">
                                                <input type="hidden" name="entityID" id="entityID">
                                                <div class="form-group">
                                                    <label for="level" class="control-label">Level</label>
                                                    <select name="level" id="level" class="form-control">
                                                        <option value="" selected disabled>Select an option</option>
                                                        <option value="Review">Reject for Review</option>
                                                        <option value="Rejected">Reject Permanently</option>
                                                    </select>
                                                </div>
                                                <div class="form-group">
                                                    <label for="reason" class="control-label">Reason</label>
                                                    <textarea name="reason" id="reason" class="form-control"></textarea>
                                                </div>
                                            </form>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" id="close-modal">Close</button>
                                            <button type="button" class="btn btn-primary" id="submit-entity">Save changes</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
@stop

@section('js')
    <!-- Explicitly include jQuery first -->
    <script src="{{ asset('vendor/adminlte/plugins/jquery/jquery.min.js') }}"></script>
    <!-- DataTables Responsive requires DataTables -->
    <script src="{{ asset('vendor/adminlte/plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('vendor/adminlte/plugins/datatables-responsive/dataTables.responsive.min.js') }}"></script>
    <script>
        // Debug jQuery loading
        console.log('jQuery defined:', typeof jQuery !== 'undefined');
    </script>
    <script type="text/javascript">
        /* Ajax setup CSRF */
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $('#close-modal').click(function() {
            $('#cancelForm')[0].reset();
            $('#cancelModal').modal('hide');
        });

        function rejectEntity(id) {
            $.ajax({
                url: '/client-party/show/' + id,
                type: 'GET',
                success: function(data) {
                    if (data.LegalEntityType && data.LegalEntityType.trim() !== '') {
                        $('#cancelModal').modal('show');
                        $('#entityID').val(id);
                    } else {
                        alert('Only legal entities can be rejected.');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error fetching entity:', status, error);
                    alert('Unable to verify entity type.');
                }
            });
        }

        $('#submit-entity').on('click', function() {
            let form_data = {
                id: $('#entityID').val(),
                level: $('#level option:selected').val(),
                reason: $('#reason').val(),
            };

            if (form_data.level == "") {
                $('#level').addClass('is-invalid');
            } else if (form_data.reason == "") {
                $('#reason').addClass('is-invalid');
            } else {
                $.ajax({
                    url: '/client-party/reject',
                    type: 'POST',
                    datatype: 'json',
                    data: form_data,
                    success: function(response) {
                        location.reload();
                    },
                    error: function(xhr, status, error) {
                        console.error('Rejection error:', status, error);
                        alert('Failed to reject entity.');
                    }
                });
            }
        });

        $(document).ready(function() {
            window.user_raise_ticket = `{{ Auth::user()->can('request-client-party') }}`;

            let clientPartyTable = $('#client-party-table').DataTable({
                responsive: true, // Enable Responsive extension
                order: [[7, 'desc']],
                processing: true,
                serverSide: true,
                searching: true,
                stateSave: true,
                ajax: '{!! route('datatables.allEntityRequests') !!}',
                columns: [
                    { data: 'Code', name: 'Code' },
                    { data: 'Name', name: 'Name' },
                    { data: 'Office', name: 'Office' },
                    { data: 'OperatioEntityType', name: 'OperatioEntityType' },
                    { data: 'LegalEntityType', name: 'LegalEntityType' },
                    { data: 'Status', name: 'Status' },
                    { data: 'CreatedBy', name: 'CreatedBy' },
                    { data: 'CreatedOn', name: 'CreatedOn' },
                    { data: 'Action', name: 'Action', orderable: false, searchable: false }
                ],
                columnDefs: [],
                initComplete: function() {}
            });

            if (window.user_raise_ticket) {
                $('#client-party-table_filter').css('margin-left', '2px').append(`
                    <div class="btn-group">
                        <button type="button" class="btn btn-primary btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-plus"></i> New Request
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item request-option" href="/client-party/create?type=operational">Operational Entity Request</a></li>
                            <li><a class="dropdown-item request-option" href="/client-party/create?type=accounting">Accounting Entity Request</a></li>
                        </ul>
                    </div>
                `);
            }

            $('#submitted').on('click', function() {
                searchTable('Submitted');
            });

            $('#created').on('click', function() {
                searchTable('Created');
            });

            $('#pending').on('click', function() {
                searchTable('PendingManagerApproval');
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
                clientPartyTable.column(5).search(param, false, false, false).draw();
            }

            $('#client-party-table').on('click', 'tbody tr td a#deny', function() {
                rejectEntity($(this).attr('code'));
            });
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
        }
    </script>
    <script src="{{ asset('js/client-party/show-create-client-party.js') }}"></script>
@stop
