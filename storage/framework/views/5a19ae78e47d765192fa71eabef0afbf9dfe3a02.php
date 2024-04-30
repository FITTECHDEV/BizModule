

<?php $__env->startSection('title', 'Users-Dashboard'); ?>

<?php $__env->startSection('content'); ?>
<div class="container mt-2">
    <div class="card shadow">
        <div class="card-header">
            <div class="card-title">
                <h2>Users Management</h2>
            </div>
            <div class="card-tools">
                <?php if(Auth::user()->can('users-list')): ?>
                    <a class="btn btn-md btn-outline-success" href="<?php echo e(route('users.create')); ?>"><i
                            class="fas fa-plus"></i> Create New User</a>
                <?php endif; ?>
            </div>
        </div>
        <div class="card-body">
            <table class="table table-sm table-bordered display text-sm" style="width:100%" id="users-table">
                <thead>
                    <th>LoginID</th>
                    <th>FullName</th>
                    <th>Email</th>
                    <th>Office</th>
                    <th>Roles</th>
                    <th>Action</th>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>





<?php $__env->stopSection(); ?>

<?php $__env->startSection('css'); ?>


<link href="//cdnjs.cloudflare.com/ajax/libs/x-editable/1.5.0/jquery-editable/css/jquery-editable.css" rel="stylesheet"/>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('js'); ?>



<script>
    $(function() {
        $('#users-table').DataTable({
            processing: true,
            serverSide: true,
            "searching": true,
            stateSave: true,
            ajax: '<?php echo route('datatables.users'); ?>',
            columns: [{
                        data: 'LoginID',
                        name: 'LoginID'
                    },
                    {
                        data: 'FullName',
                        name: 'FullName'
                    },
                    {
                        data: 'Email',
                        name: 'Email'
                    },
                    {
                        data: 'Office',
                        name: 'Office'
                    },
                    {
                        data: 'Roles',
                        name: 'Roles'
                    },
                    {
                        data: 'Action',
                        name: 'Action',
                        orderable: false,
                        searchable: false
                    }
            ]
        });

        // $.fn.editable.defaults.mode = 'inline';

        // $.ajaxSetup({
        //     headers: {
        //         'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'
        //     }
        // });

        // $('.updateAjax').editable({
        //     url: "<?php echo e(route('users.update_ajax')); ?>",
        //     type: 'text',
        //     pk: 1,
        //     name: 'name',
        //     title: 'Enter name'
        // });
    });
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('adminlte::page', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\JosephSifuna\fit_service_desk\resources\views/users/index.blade.php ENDPATH**/ ?>