

<?php $__env->startSection('title', 'All Tickets'); ?>

<?php $__env->startSection('content_header'); ?>

<?php $__env->stopSection(); ?>



<?php $__env->startSection('content'); ?>
<div class="">
    <div class="">
        <div class="row pt-1 pb-0 m-0 justify-content-center">
            <div class="col-12 col-sm-6 col-md-3">
                <a href="#" id="open">
                    <div class="info-box mb-1">
                        <span class="info-box-icon bg-warning elevation-1"><i class="fas fa-envelope-open"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text text-center">Open </span>
                            <span class="info-box-number text-center"><?php echo e($ticket->ticketCount('Status', 1)); ?></span>
                        </div>
                        <!-- /.info-box-content -->
                    </div>
                </a>
                <!-- /.info-box -->
            </div>
            <!-- /.col -->
            <div class="col-12 col-sm-6 col-md-2">
                <a href="#" id="solved">
                    <div class="info-box mb-1">
                        <span class="info-box-icon bg-primary elevation-1"><i class="fas fa-check-circle"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text text-center">Solved </span>
                            <span class="info-box-number text-center"><?php echo e($ticket->ticketCount('Status', 2)); ?></span>
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
                <a href="/archive" target="_blank">
                    <div class="info-box mb-1">
                        <span class="info-box-icon bg-success elevation-1"><i class="fas fa-thumbs-up"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text text-center">Closed</span>
                            <span class="info-box-number text-center"><?php echo e($ticket->ticketCount('Status', 3)); ?></span>
                        </div>
                        <!-- /.info-box-content -->
                    </div>
                </a>
            </div>
            <div class="col-12 col-sm-6 col-md-2">
                <a href="#" id="onHold">
                    <div class="info-box mb-1">
                        <span class="info-box-icon bg-purple elevation-1"><i class="fa fa-ban"
                                aria-hidden="true"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text text-center">On Hold</span>
                            <span class="info-box-number text-center"><?php echo e(__($ticket->ticketCount('Status', '0'))); ?></span>
                        </div>
                        <!-- /.info-box-content -->
                    </div>
                </a>
                <!-- /.info-box -->
            </div>
            <div class="col-12 col-sm-6 col-md-3">
                <a href="/archive" target="_blank">
                    <div class="info-box mb-1">
                        <span class="info-box-icon bg-info elevation-1"><i class="fa fa-calculator"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text text-center">All Tickets</span>
                            <span class="info-box-number text-center"><?php echo e($ticket->ticketCount('Status', '*')); ?></span>
                        </div>
                        <!-- /.info-box-content -->
                    </div>
                </a>
                <!-- /.info-box -->
            </div>
        </div>
        <div class="row justify-content-center mb-0">
            <div class="col-md-3 text-center">
                <h5>All Tickets</h5>
            </div>
            <div class="col-md-2 mb-0"><a href="#" id="ShowStopper" class="card text-center mb-1 pb-0">
                    <div><i class="badge badge-danger"><?php echo e($ticket->ticketCount('Priority', 'ShowStopper')); ?></i>
                        ShowStopper</div>
                </a> </div>
            <div class="col-md-2 mb-0"><a href="#" id="High" class="card text-center mb-1 pb-0">
                    <div><i class="badge badge-warning"><?php echo e($ticket->ticketCount('Priority', 'High')); ?></i> High</div>
                </a> </div>
            <div class="col-md-2 mb-0"><a href="#" id="Medium" class="card text-center mb-1 pb-0">
                    <div><i class="badge badge-primary"><?php echo e($ticket->ticketCount('Priority', 'Medium')); ?></i> Medium
                    </div>
                </a> </div>
            <div class="col-md-3 mb-0"><a href="#" id="Low" class="card text-center mb-1 pb-0">
                    <div><i class="badge badge-info"><?php echo e($ticket->ticketCount('Priority', 'Low')); ?></i> Low</div>
                </a> </div>
        </div>
        <!-- table row -->
        <div class="row justify-content-center pt-0">
            <div class="col-12 col-sm-12 col-md-12 ">
                <div class="card table-responsive">
                    <div class="card-body">
                        <table class="table table-bordered text-sm" id="tickets-table">
                            <thead>
                                <th>TicketNo</th>
                                <th>Title</th>
                                <th>Solution</th>
                                <th>Category</th>
                                <th>Sub-Category</th>
                                <th>CreatedBy</th>
                                <th>Office</th>
                                <th>AssignedTo</th>
                                <th>CreatedOn</th>
                                <th>Status</th>
                                <th>Priority</th>
                                <th>Action</th>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <!-- /.table row -->
    </div>
</div>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('css'); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('js'); ?>
<script>
    $(function() {

        window.user_raise_ticket = `<?php echo e(Auth::user()->can('ticket-raise')); ?>`;

        let allTicketTable = $('#tickets-table').DataTable({
            "order": [[ 8, "desc" ]],
            processing: true,
            serverSide: true,
            "searching": true,
            stateSave: true,
            ajax: '<?php echo route('datatables.all_tickets'); ?>',
            columns: [{
                        data: 'TicketNo',
                        name: 'TicketNo'
                    },
                    {
                        data: 'Title',
                        name: 'Title'
                    },
                    {
                        data: 'SolutionName',
                        name: 'SolutionName'
                    },
                    {
                        data: 'ModuleName',
                        name: 'ModuleName'
                    },
                    {
                        data: 'SubModuleName',
                        name: 'SubModuleName',
                    },
                    {
                        data: 'CreatedBy',
                        name: 'CreatedBy'
                    },
                    {
                        data: 'OfficeName',
                        name: 'OfficeName'
                    },
                    {
                        data: 'AssignedTo',
                        name: 'AssignedTo'
                    },
                    {
                        data: 'CreatedOn',
                        name: 'CreatedOn'
                    },
                    {
                        data: 'Status',
                        name: 'Status'
                    },
                    {
                        data: 'Priority',
                        name: 'Priority'
                    },

                    {
                        data: 'Action',
                        name: 'Action',
                        orderable: false,
                        searchable: false
                    }
            ],
            "createdRow": function( row, data, dataIndex){
                if( data['AssignedTo'] ==  'Not Assigned'){
                    $(row).css('background-color','#FAEEB9');
                }
            },
            initComplete: function() {
                // this.api().columns().every(function() {
                //     var column = this;
                //     var input = document.createElement('input');
                //     $(input).appendTo($(column.footer()).empty())
                //         .on('change', function() {
                //             var val = $.fn.dataTable.util.escapeRegex($(this).val());
                //             column.search(val ? val : '', true, false).draw();
                //         });
                // });
            }
        });

        if (window.user_raise_ticket) {
            $('#tickets-table_filter').css('margin-left', '2px').append('<a href="/tickets/create" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i>New Ticket</a><button class="btn btn-sm btn-outline-warning" id="clear">Clear filter</button>');
        }

        searchTable(1);

        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            didOpen: function(toast) {
            toast.addEventListener('mouseenter', Swal.stopTimer);
            toast.addEventListener('mouseleave', Swal.resumeTimer);
            }
        });

        $('#clear').on('click', function() {
            searchTable(' ');
            Toast.fire({
                icon: 'success',
                title: 'Table Refreshed',
                timer: 3000
            });
        });

        $('#open').on('click', function() {
            searchTable(1);
        });

        $('#solved').on('click', function() {
            searchTable(2);
        });

        $('#closed').on('click', function() {
            searchTable(3);
        });

        $('#onHold').on('click', function() {
            searchTable(0);
        });

        $('#allTickets').on('click', function() {
            searchTable(' ');
        });

        function searchTable(param) {
            let column = 9;
            if((typeof(param) != 'number') && (param != ' ')){
                column = 10;
            }
            allTicketTable.column(column).search(param, false, false, false).draw();
        }


        $('#ShowStopper').on('click', function() {
            searchTable('ShowStopper');
        });
        $('#High').on('click', function() {
            searchTable('High');
        });
        $('#Medium').on('click', function() {
            searchTable('Medium');
        });
        $('#Low').on('click', function() {
            searchTable('Low');
        });

    });
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('adminlte::page', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\inetpub\wwwroot\Projects\Production\erp_production\resources\views/tickets/index.blade.php ENDPATH**/ ?>