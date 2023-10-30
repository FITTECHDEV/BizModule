<?php $__env->startSection('title', 'Dashboard'); ?>

<?php $__env->startSection('content_header'); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <div class="container-fluid p-2">
        <div class="row">
            <div class="col-md-3">

                <!-- Profile Image -->
                <div class="card card-primary card-outline">
                    <div class="card-body box-profile">
                        <div class="text-center">
                            
                            <img class="" src="https://freightintime.com/wp-content/uploads/2019/04/freight-in-time-logo-Edit.gif"
                                alt="User profile picture">
                        </div>

                        <h3 class="profile-username text-center"><?php echo e(ucwords($user->full_name)); ?></h3>

                        <p class="text-muted text-center">
                            Role(s) <br>
                            <?php if(!empty($user->getRoleNames())): ?>
                                <?php $__currentLoopData = $user->getRoleNames(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <label class="badge badge-success"><?php echo e($v); ?></label>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            <?php endif; ?>
                        </p>

                        <ul class="list-group list-group-unbordered mb-3">
                            <li class="list-group-item">
                                <b>Tickets Open</b> <a
                                    class="float-right"><?php echo e($user->tickets->where('Status', 1)->count()); ?></a>
                            </li>
                            <li class="list-group-item">
                                <b>Tickets Solved</b> <a
                                    class="float-right"><?php echo e($user->tickets->where('Status', 2)->count()); ?></a>
                            </li>
                            <li class="list-group-item">
                                <b>Ticket Closed</b> <a
                                    class="float-right"><?php echo e($user->tickets->where('Status', 3)->count()); ?></a>
                            </li>
                            <li class="list-group-item">
                                <b>Ticket Re-opened</b> <a
                                    class="float-right"><?php echo e($user->tickets->where('Status', 4)->count()); ?></a>
                            </li>
                            <li class="list-group-item">
                                <b>Ticket Settled</b> <a
                                    class="float-right"><?php echo e($user->tickets->where('Status', 5)->count()); ?></a>
                            </li>
                            <li class="list-group-item">
                                <b>Tickets Raised</b> <a class="float-right"><?php echo e($user->tickets->count()); ?></a>
                            </li>
                        </ul>
                    </div>
                    <!-- /.card-body -->
                </div>
                <!-- /.card -->


            </div>
            <!-- /.col -->
            <div class="col-md-9">
                <div class="card">
                    <div class="card-header p-2">
                        <ul class="nav nav-pills">
                            <li class="nav-item"><a class="nav-link active" href="#profile" data-toggle="tab">Profile</a></li>
                            <li class="nav-item"><a class="nav-link " href="#settings" data-toggle="tab">Settings</a>

                            
                            </li>
                        </ul>
                    </div><!-- /.card-header -->
                    <div class="card-body">
                        <div class="tab-content">
                            
                            <div class="active tab-pane" id="profile">
                                <div class="row">
                                    <div class="col-sm-4">
                                        <div class="form-group">
                                            <label for="inputName" class="col-form-label">First Name</label> <br>
                                            <div class="updateAjax" data-name="FirstName" data-type="text"
                                                data-pk="<?php echo e($user->ID); ?>" data-title="Enter First Name">
                                                <?php echo e($user->FirstName); ?></div>
                                        </div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="form-group">
                                            <label for="inputName" class="col-form-label">Last Name</label> <br>
                                            <div class="updateAjax " data-name="LastName" data-type="text"
                                                data-pk="<?php echo e($user->ID); ?>" data-title="Enter Last Name">
                                                <?php echo e($user->LastName); ?></div>
                                        </div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="form-group">
                                            <label for="inputName" class="col-form-label">Login ID</label> <br>
                                            <div class="updateAjax " data-name="LoginID" data-type="text"
                                                data-pk="<?php echo e($user->ID); ?>" data-title="Enter Login ID">
                                                <?php echo e($user->LoginID); ?></div>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group ">
                                    <label for="inputEmail" class="col-form-label">Email : </label>
                                    <div class="updateAjax" data-name="Email" data-type="text"
                                        data-pk="<?php echo e($user->ID); ?>" data-title="Enter Login ID"> <?php echo e($user->Email); ?>

                                    </div>
                                </div>
                                <form action="<?php echo e(route('user.update.password', [$user])); ?>" method="POST" class="form-horizontal">
                                    <?php echo csrf_field(); ?>
                                    <div class="row  justify-content-center">
                                        <div class="form-group col-xs-6 col-sm-6 col-md-6">
                                            <strong>Password:</strong>
                                            <input type="password" name="password" id="password" placeholder="Password"
                                                class="form-control <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                                            <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                <span class="invalid-feedback">
                                                    <strong><?php echo e($message); ?></strong>
                                                </span>
                                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                        </div>
                                    </div>
                                    <div class="row  justify-content-center">
                                        <div class="form-group col-xs-6 col-sm-6 col-md-6">
                                            <strong>Confirm Password:</strong>
                                            <input type="password" name="confirm-password" id="confirm-password"
                                                placeholder="confirm-password"
                                                class="form-control <?php $__errorArgs = ['confirm-password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                                            <?php $__errorArgs = ['confirm-password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                <span class="invalid-feedback">
                                                    <strong><?php echo e($message); ?></strong>
                                                </span>
                                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                        </div>
                                    </div>
                                    <div class=" row justify-content-center">
                                        <div class="form-group col-xs-6 col-sm-6 col-md-6">
                                            <button type="submit" class="btn btn-outline-danger" >Change Password</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            <div class="tab-pane" id="settings">
                                <label for="">Email Settings</label>
                                <table class="ml-2">
                                    <tr>
                                        <th>
                                            Receive All Emails
                                        </th>
                                        <td>
                                           &nbsp; <input type="checkbox" name="all_emails" id="all_emails">
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>
                                            Ticket Emails
                                        </th>
                                        <td>
                                           &nbsp; <input type="checkbox" name="all_emails" id="all_emails">
                                        </td>
                                    </tr>
                                </table>

                            </div>

                            <!-- /.tab-pane -->
                        </div>
                        <!-- /.tab-content -->
                    </div><!-- /.card-body -->
                </div>
                <!-- /.nav-tabs-custom -->
            </div>
            <!-- /.col -->
        </div>
        <!-- /.row -->
    </div><!-- /.container-fluid -->
<?php $__env->stopSection(); ?>

<?php $__env->startSection('css'); ?>
    <link href="//cdnjs.cloudflare.com/ajax/libs/x-editable/1.5.0/jquery-editable/css/jquery-editable.css"
        rel="stylesheet" />

<?php $__env->stopSection(); ?>

<?php $__env->startSection('js'); ?>
<script>
    $.fn.poshytip = {
        defaults: null
    }
</script>
<script src="//cdnjs.cloudflare.com/ajax/libs/x-editable/1.5.0/jquery-editable/js/jquery-editable-poshytip.min.js"></script>
<script>
    $(function() {
        $.fn.editable.defaults.mode = 'inline';

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'
            }
        });

        $('.updateAjax').editable({
            url: "<?php echo e(route('users.update_ajax')); ?>",
            type: 'text',
            pk: 1,
            name: 'name',
            title: 'Enter name'
        });


    });
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('adminlte::page', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\inetpub\wwwroot\fit_service_desk\resources\views/users/profile.blade.php ENDPATH**/ ?>