

<?php $__env->startSection('title', 'Role Management'); ?>

<?php $__env->startSection('content'); ?>
    <div class="container mt-2">
        <div class="card shadow">
            <div class="card-header">
                <div class="card-title">
                    <h2>Role Management</h2>
                </div>
                <div class="card-tools">
                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('role-create')): ?>
                        <a class="btn btn-outline-success btn-md" href="<?php echo e(route('roles.create')); ?>">
                            <i class="fas fa-plus"></i> Create New Role
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="card-body table-responsive">
                <table class="table table-bordered table-sm" id="roles-table">
                    <thead>
                        <th>No</th>
                        <th>Name</th>
                        <th>Action</th>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td><?php echo e(++$i); ?></td>
                                <td><?php echo e($role->name); ?></td>
                                <td>
                                    <a class="btn btn-info btn-xs" href="<?php echo e(route('roles.show', $role->id)); ?>">Show</a>
                                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('role-edit')): ?>
                                        <a class="btn btn-primary btn-xs" href="<?php echo e(route('roles.edit', $role->id)); ?>">Edit</a>
                                    <?php endif; ?>
                                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('role-delete')): ?>
                                        <?php echo Form::open(['method' => 'DELETE', 'route' => ['roles.destroy', $role->id], 'style' => 'display:inline']); ?>

                                        <?php echo Form::submit('Delete', ['class' => 'btn btn-danger btn-xs', 'onclick' => 'return confirm("Are you sure ?")']); ?>

                                        <?php echo Form::close(); ?>

                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php echo $roles->render(); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('css'); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('js'); ?>
    <script>
        $(function() {
            $('#roles-table').DataTable({

            })
        })
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('adminlte::page', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\JosephSifuna\fit_service_desk\resources\views/roles/index.blade.php ENDPATH**/ ?>