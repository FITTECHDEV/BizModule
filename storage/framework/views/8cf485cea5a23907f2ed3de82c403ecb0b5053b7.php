

<?php $__env->startSection('title', 'Home-Dashboard'); ?>

<?php $__env->startSection('content_header'); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('plugins.Datatables', true); ?>
<?php $__env->startSection('css'); ?>
<style>
    .service-card {
        text-align: center;
        box-shadow: 0 2px 2px rgba(0, 0, 0, 0.2);
        -moz-transition: all 0.3s cubic-bezier(0.165, 0.84, 0.44, .1);
        -o-transition: all 0.3s cubic-bezier(0.165, 0.84, 0.44, .1);
        transition: all 0.3s cubic-bezier(0.165, 0.84, 0.44, .1);
        margin-bottom: 10%;
    }

    .service-card:hover {
        box-shadow: 4px 4px 4px rgba(58, 105, 243, 0.3);
        -webkit-transform: translate(0, -8px);
        -moz-transform: translate(0, -8px);
        -ms-transform: translate(0, -8px);
        -o-transform: translate(0, -8px);
        transform: translate(0, -8px);
        box-shadow: 0 40px 40px rgba(0, 0, 0, 0.2);
    }

    .service-card i {
        font-size: 150px;
        padding: 10% 0;
        background-color: #f3f3f3;
        color: #31D0D4;
        box-shadow: 0 6px 6px rgba(0, 0, 0, 0.2);
        position: relative;
        z-index: 1;
    }

    .service-card:hover i {
        color: #fff;
        transition: all ease-in-out 0.5s;
    }

    .service-card i:after {
        content: "";
        position: absolute;
        bottom: 0;
        left: 0;
        width: 100%;
        height: 100%;
        transform: scaleY(0);
        transform-origin: bottom center;
        background: linear-gradient(to left, #8487E2, #31D0D4, #31D0D4, #8487E2);
        z-index: -1;
        transition: transform 0.5s;
    }

    .service-card:hover i::after {
        transform: scaleY(1);
    }

    /* .service-card h3{
  margin-top: 2%;
  font-size: 44px;
  font-weight: bold;
  color: #464646;
} */
</style>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>
<div class="p-2">   
    <div class="row justify-content-center">
        <div class="col-md-3 mb-2">
            <a href="/tickets">
                <div class="d-flex h-100 flex-column card card-widget widget-user shadow service-card">
                    <div class="widget-user-header bg-info">
                        <h3 class="widget-user-username">TICKETING SYSTEM</h3>
                        <h5 class="widget-user-desc">Raise A Concern.</h5>
                    </div>
                    <div class="widget-user-image">
                        <img class="img-circle elevation-2" src="<?php echo e(asset('site_imgs/images.png')); ?>"
                            alt="Phone Avatar">
                    </div>
                    <div class="card-footer">
                        <div class="row justify-content-center">
                            <?php $__currentLoopData = $ticketStatistics; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ticketStat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="col-sm-4 border">
                                <div class="description-block">
                                    <h5 class="description-header"><span class="badge badge-info"><?php echo e($ticketStat->StatusCount); ?></span> </h5>
                                    <span class="description-text small"><?php echo e($ticketStat->StatusName); ?></span>
                                </div>
                            </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>
                </div>
            </a>

        </div>
        <div class="col-md-3 mb-2">
            <a href="/client-party?my_request=true">
                <div class="d-flex h-100 flex-column card card-widget widget-user shadow service-card">
                    <div class="widget-user-header bg-primary">
                        <h3 class="widget-user-username">CLIENT PARTY SYSTEM</h3>
                        <h5 class="widget-user-desc">Entity Creation & Mapping.</h5>
                    </div>
                    <div class="widget-user-image">
                        <img class="img-circle elevation-2" src="<?php echo e(asset('site_imgs/handshake.jpg')); ?>"
                            alt="Phone Avatar">
                    </div>
                    <div class="card-footer">
                        <div class="row justify-content-center">
                            <?php $__currentLoopData = $entityStatistics; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $entityStats): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="col-sm-4 border">
                                <div class="description-block">
                                    <h5 class="description-header"><span class="badge badge-primary"><?php echo e($entityStats->StatusCount); ?></span></h5>
                                    <span class="description-text small"><?php echo e($entityStats->StatusName); ?></span>
                                </div>
                            </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <?php if(Auth::user()->can('tims-access')): ?>
        <div class="col-md-3 mb-2">
            <a href="/tims">
                <div class="d-flex h-100 flex-column card card-widget widget-user shadow service-card">
                    <div class="widget-user-header bg-maroon">
                        <h3 class="widget-user-username">TIMS INTERGRATION</h3>
                        <h5 class="widget-user-desc">KRA Dashboard</h5>
                    </div>
                    <div class="widget-user-image">
                        <img class="img-circle elevation-2" src="<?php echo e(asset('site_imgs/kenya_flag.png')); ?>"
                            alt="Kenya Flag">
                    </div>
                    <div class="card-footer">
                        <div class="row justify-content-center">
                            <?php $__currentLoopData = $tims_stats; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $entityStats): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="col-sm-4 border">
                                <div class="description-block">
                                    <h5 class="description-header"><span class="badge badge-primary"><?php echo e($entityStats->StatusCount); ?></span></h5>
                                    <span class="description-text small"><?php echo e($entityStats->StatusName); ?></span>
                                </div>
                            </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <?php endif; ?>
        <?php if(Auth::user()->can('efris-access')): ?>
        <div class="col-md-3 mb-2">
            <a href="/efris">
                <div class="d-flex h-100 flex-column card card-widget widget-user shadow service-card">
                    <div class="widget-user-header bg-maroon">
                        <h3 class="widget-user-username">EFRIS INTERGRATION</h3>
                        <h5 class="widget-user-desc">EFRIS Dashboard</h5>
                    </div>
                    <div class="widget-user-image">
                        <img class="img-circle elevation-2" src="<?php echo e(asset('site_imgs/Custom-Icon-Design-Round-World-Flags-Uganda.ico')); ?>" alt="Phone Avatar">
                    </div>
                    <div class="card-footer">
                        <div class="row justify-content-center">
                            <?php $__currentLoopData = $efris_stats; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $entityStats): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="col-sm-4 border">
                                <div class="description-block">
                                    <h5 class="description-header">
                                        <span class="badge badge-primary"><?php echo e($entityStats->EfrisStatus); ?></span>
                                    </h5>
                                        <span class="description-text small"><?php echo e($entityStats->NoOfInvoice); ?></span>
                                </div>
                            </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <?php endif; ?>
        
        <div class="col-md-3 mb-2">
            <a href="<?php echo e(URL::signedRoute('quote-logger')); ?>">
                <div class="d-flex h-100 flex-column card card-widget widget-user shadow service-card">
                    <div class="widget-user-header bg-teal">
                        <h3 class="widget-user-username">QUOTE LOGGER</h3>
                        <h5 class="widget-user-desc">Quote-Log System</h5>
                    </div>
                    <div class="widget-user-image">

                        <img class="img-circle elevation-2"
                            src="<?php echo e(asset('site_imgs/quotation2.png')); ?>" alt="Phone Avatar">
                    </div>
                    <div class="card-footer">
                        <div class="row justify-content-center">
                            
                        </div>
                    </div>
                </div>
            </a>
        </div>


    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('js'); ?>
<script>
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('adminlte::page', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\JosephSifuna\fit_service_desk\resources\views/dashboard/ticket.blade.php ENDPATH**/ ?>