

<?php $__env->startSection('title', 'Tickets'); ?>

<?php $__env->startSection('content_header'); ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="row pt-1 pb-0 m-0">
    <div class="col-12 col-sm-6 col-md-3">
        <a href="#" id="failed">
            <div class="info-box mb-1">
                <span class="info-box-icon bg-danger elevation-1"><i class="fas fa-times"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text text-center">Failed</span>
                    <span class="info-box-number text-center">
                        <?php echo e($efris_data->firstWhere('EfrisStatus', 'FAILED')->NoOfInvoice ?? 0); ?>

                    </span>
                </div>
                <!-- /.info-box-content -->
            </div>
        </a>
    </div>
    <!-- /.col -->
    <div class="col-12 col-sm-6 col-md-3">
        <a href="#" id="pending">
            <div class="info-box mb-1">
                <span class="info-box-icon bg-warning elevation-1"><i class="fas fa-clock"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text text-center">Pending</span>
                    <span class="info-box-number text-center">
                        <?php echo e($efris_data->firstWhere('EfrisStatus', 'PENDING')->NoOfInvoice ?? 0); ?>

                    </span>
                </div>
                <!-- /.info-box-content -->
            </div>
        </a>
        <!-- /.info-box -->
    </div>
    <!-- /.col -->
    <!-- fix for small devices only -->
    <div class="clearfix hidden-md-up"></div>
    <!-- /.col -->
    <div class="col-12 col-sm-6 col-md-3">
        <a href="#" id="submitted">
            <div class="info-box mb-1">
                <span class="info-box-icon bg-success elevation-1"><i class="fas fa-thumbs-up"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text text-center">Submitted</span>
                    <span class="info-box-number text-center">
                        <?php echo e($efris_data->firstWhere('EfrisStatus', 'SUCCESS')->NoOfInvoice ?? 0); ?>

                    </span>
                </div>
                <!-- /.info-box-content -->
            </div>
        </a>
    </div>
    <div class="col-12 col-sm-6 col-md-3">
        <a href="#" id="allInvoiceCreds">
            <div class="info-box mb-1">
                <span class="info-box-icon bg-purple elevation-1"><i class="fa fa-list" aria-hidden="true"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text text-center">Total</span>
                    <span class="info-box-number text-center">
                        <?php echo e($efris_data->firstWhere('EfrisStatus', 'TOTAL')->NoOfInvoice ?? 0); ?>

                    </span>
                </div>
                <!-- /.info-box-content -->
            </div>
        </a>
        <!-- /.info-box -->
    </div>
</div>
<div class="row justify-content-center pt-0">
    <div class="col-12 col-sm-12 col-md-12 ">
        <div class="card table-responsive">
            <div class="card-body">
                <table class="table table-bordered text-sm" id="invcred-table">
                    <thead>
                        <th>InvoiceNo</th>
                        <th>Type</th>
                        <th>Customer</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Remarks</th>
                        <th>Action</th>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('css'); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('js'); ?>
<script>
    let url = 'datatables/efris';
    var invcred_table = $('#invcred-table').DataTable({
                "order": [[3, "desc"]],
                orderCellsTop: true,
                fixedHeader: true,
                processing: true,
                serverSide: true,
                autosave: true,
                ajax: url,
                "autoWidth": false,
                "columnDefs":[{"width": "30%", "targets": 5}],
                columns: [{
                        data: 'DocNo',
                        name: 'InvoiceNo'
                    },
                    {
                        data: 'DocType',
                        name: 'Type'
                    },
                    {
                        data: 'DebtorName',
                        name: 'Customer'
                    },
                    {
                        data: 'DocDate',
                        name: 'Date'
                    },
                    {
                        data: 'DocStatus',
                        name: 'Status',
                    },
                    {
                        data: 'Remarks',
                        name: 'SubmissionStatus'
                    },
                    {
                        data: 'Action',
                        name: 'Action',
                        orderable: false,
                        searchable: false
                    }
                ],
            });

            function editInvoice(id, action, docNo = null){

                if (action === 're-trigger') {
                    $.ajax({
                        url: '/efris/re-trigger/' + docNo,
                        type: 'GET',
                        datatype: 'json',
                        success: function(response) {
                            alert(response[0].Status+" : "+response[0].Status_Remark);
                            refresh();
                        }
                    });
                }
            }


        searchTable("FAILED");

        function refresh(){
            invcred_table.ajax.reload(null, false);
        }

        $('#pending').on('click', function() {
            searchTable("Pending");
        });

        $('#failed').on('click', function() {
            searchTable("FAILED");
        });

        $('#submitted').on('click', function() {
            searchTable("SUCCESS");
        });

        $('#allInvoiceCreds').on('click', function() {
            searchTable(" ");
        });

        function searchTable(param) {
            let column = 4;
            invcred_table.column(column).search(param, false, false, false).draw();
        }


</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('adminlte::page', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\inetpub\wwwroot\Projects\UAT\erp_uat\resources\views/efris/index.blade.php ENDPATH**/ ?>