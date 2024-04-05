<?php $__env->startSection('title', 'Client-Party-Dashboard'); ?>

<?php $__env->startSection('content_header'); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('plugins.Datatables', true); ?>

<?php $__env->startSection('content'); ?>

    <div class="p-2">
        <div class="row pt-2 pb-0">
            <div class="col-12 col-sm-6 col-md-3">
                <a href="#" id="submitted">
                    <div class="info-box mb-3">
                        <span class="info-box-icon bg-warning elevation-1"><i class="fa fa-hand-paper"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text text-center">Submitted</span>
                            <span
                                class="info-box-number text-center"><?php echo e($clientParty->clientPartyCount('Status', 'Submitted')); ?></span>
                        </div>
                        <!-- /.info-box-content -->
                    </div>
                </a>
                <!-- /.info-box -->
            </div>
            <!-- /.col -->
            <div class="col-12 col-sm-6 col-md-2">
                <a href="#" id="created">
                    <div class="info-box mb-3">
                        <span class="info-box-icon bg-success elevation-1">
                            <i class="fas fa-check-circle"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text text-center">Created </span>
                            <span
                                class="info-box-number text-center"><?php echo e($clientParty->clientPartyCount('Status', 'Created')); ?></span>
                        </div>
                        <!-- /.info-box-content -->
                    </div>
                </a>

            </div>
            <!-- /.col -->
            <!-- fix for small devices only -->
            <div class="clearfix hidden-md-up"></div>
            <!-- /.col -->
            <div class="col-12 col-sm-6 col-md-2">
                <a href="#" id="pending">
                    <div class="info-box">
                        <span class="info-box-icon bg-primary elevation-1">
                            <i class="fa fa-ban"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text text-center">Pending</span>
                            <span
                                class="info-box-number text-center"><?php echo e($clientParty->clientPartyCount('Status', 'PendingManagerApproval')); ?></span>
                        </div>
                        <!-- /.info-box-content -->
                    </div>
                </a>
            </div>
            <div class="col-12 col-sm-6 col-md-2">
                <a href="#" id="rejected">
                    <div class="info-box">
                        <span class="info-box-icon bg-danger elevation-1"><i class="fa fa-times-circle"
                                aria-hidden="true"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text text-center">Rejected</span>
                            <span
                                class="info-box-number text-center"><?php echo e($clientParty->clientPartyCount('Status', 'Rejected')); ?></span>
                        </div>
                        <!-- /.info-box-content -->
                    </div>
                </a>
                <!-- /.info-box -->
            </div>
            <div class="col-12 col-sm-6 col-md-3">
                <a href="#" id="allEntityRequests">
                    <div class="info-box mb-3">
                        <span class="info-box-icon bg-info elevation-1"><i class="fa fa-calculator"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text text-center">Total Requests</span>
                            <span
                                class="info-box-number text-center"><?php echo e($clientParty->clientPartyCount('Status', '*')); ?></span>
                        </div>
                        <!-- /.info-box-content -->
                    </div>
                </a>

                <!-- /.info-box -->
            </div>
        </div>
        <!-- /.badge row -->
        <div class="row">
            <div class="col-xl-12 col-lg-12 col-sm-12 col-md-12 ">
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered table-hover display text-sm" style="width:100%"
                                id="client-party-table">
                                <thead>
                                    <th>CODE</th>
                                    <th>Company NAME</th>
                                    <th>OFFICE</th>
                                    <th title="Operation Entity Type">OET</th>
                                    <th title="Legal Entity Type">LET</th>
                                    <th>Status</th>
                                    <th>RequestedBy</th>
                                    <th>RequestedOn</th>
                                    <th>Action</th>
                                </thead>
                                <tbody>
                                    
                                </tbody>
                            </table>
                            <!-- Modal -->
                            <div class="modal fade" id="cancelModal" tabindex="-1" role="dialog"
                                aria-labelledby="exampleModalLabel" aria-hidden="true">
                                <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="exampleModalLabel">Reject Entity Options</h5>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
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
                                            <button type="button" class="btn btn-secondary"
                                                id="close-modal">Close</button>
                                            <button type="button" class="btn btn-primary" id="submit-entity">Save
                                                changes</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
        <!-- /.table row -->
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('css'); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('js'); ?>
    <script type="text/javascript">
        /*Ajax setup CSRF*/
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
            // open modal
            $('#cancelModal').modal('show');
            // set the entity id
            $('#entityID').val(id);
        }

        $('#submit-entity').on('click', function() {
            // validate form data
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
                        location.reload(); /**/
                    }
                });
            }
        });



        $(document).ready(function() {
            window.user_raise_ticket = `<?php echo e(Auth::user()->can('request-client-party')); ?>`;

            let clientPartyTable = $('#client-party-table').DataTable({
                "order": [
                    [7, 'desc']
                ],
                processing: true,
                serverSide: true,
                "searching": true,
                stateSave: true,
                ajax: '<?php echo route('datatables.allEntityRequests'); ?>',
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
                        name: 'Office',
                    },
                    {
                        data: 'OperatioEntityType',
                        name: 'OperatioEntityType',
                    },
                    {
                        data: 'LegalEntityType',
                        name: 'LegalEntityType',
                    },
                    {
                        data: 'Status',
                        name: 'Status',
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
                columnDefs: [],
                initComplete: function() {

                }
            });

            if (window.user_raise_ticket) {
                $('#client-party-table_filter').css('margin-left', '2px').append(
                    '<a href="/client-party/create" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i>New Request</a>'
                    );
            }
            $('#submitted').on('click', function() {
                searchTable('Submitted');
            });

            $('#created').on('click', function() {
                searchTable('Created');
            });

            $('#pending').on('click', function() {
                searchTable('Pending');
            });

            $('#rejected').on('click', function() {
                searchTable('Rejected');
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
<?php $__env->stopSection(); ?>

<?php echo $__env->make('adminlte::page', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\inetpub\wwwroot\erp\resources\views/client-party/index.blade.php ENDPATH**/ ?>