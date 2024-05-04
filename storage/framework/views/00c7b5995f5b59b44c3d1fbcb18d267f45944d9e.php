<!DOCTYPE html>
<html lang="en">
<!-- <html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>"> -->

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <!-- <link rel="icon" href="https://freightintime.com/wp-content/uploads/2019/04/freight-in-time-logo-Edit.gif"
        sizes="32x32" />-->
    <!-- <link rel="icon" href="https://freightintime.com/wp-content/uploads/2019/04/freight-in-time-logo-Edit.gif"
        sizes="192x192" />-->
   <!-- <link rel="apple-touch-icon"
         href="https://freightintime.com/wp-content/uploads/2019/04/freight-in-time-logo-Edit.gif" /> -->

    <meta http-equiv="refresh" content="<?php echo e(config('session.lifetime') * 60); ?>">

    <?php echo $__env->yieldContent('meta_tags'); ?>

    <title>
        <?php echo $__env->yieldContent('title_prefix', config('adminlte.title_prefix', '')); ?>
        <?php echo $__env->yieldContent('title', config('adminlte.title', 'FIT SERVICE DESK')); ?>
        <?php echo $__env->yieldContent('title_postfix', config('adminlte.title_postfix', '')); ?>
    </title>

    <?php echo $__env->yieldContent('adminlte_css_pre'); ?>

    <link rel="stylesheet" href="<?php echo e(asset('vendor/fontawesome-free/css/all.min.css')); ?>">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">
    <?php if(!config('adminlte.enabled_laravel_mix')): ?>

    <link rel="stylesheet" href="<?php echo e(asset('vendor/overlayScrollbars/css/OverlayScrollbars.min.css')); ?>">


    <?php echo $__env->make('adminlte::plugins', ['type' => 'css'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    <link rel="stylesheet" href="<?php echo e(asset('vendor/adminlte/dist/css/adminlte.min.css')); ?>">
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">
    <?php else: ?>
    <link rel="stylesheet" href="<?php echo e(asset(config('adminlte.laravel_mix_css_path', 'css/app.css'))); ?>">

    <link rel="stylesheet" href="<?php echo e(asset('vendor/overlayScrollbars/css/OverlayScrollbars.min.css')); ?>">

    <link rel="stylesheet" href="<?php echo e(asset('vendor/adminlte/dist/css/adminlte.min.css')); ?>">
    <?php endif; ?>

    <?php if(config('adminlte.livewire')): ?>
        <?php if(app()->version() >= 7): ?>
            @livewireStyles
        <?php else: ?>
            <livewire:styles />
        <?php endif; ?>
    <?php endif; ?>

    <link rel="stylesheet" href="//cdn.datatables.net/1.10.25/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.25/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.dataTables.min.css">

    <?php echo $__env->yieldContent('adminlte_css'); ?>

    <!-- Favicon -->
    <?php if(config('adminlte.use_ico_only')): ?>
    
    <?php elseif(config('adminlte.use_full_favicon')): ?>

    
    <?php endif; ?>

</head>

<body class="<?php echo $__env->yieldContent('classes_body'); ?>" <?php echo $__env->yieldContent('body_data'); ?>>
    <!-- sweet alert -->
    <?php echo $__env->make('sweetalert::alert', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <!-- Preloader -->
    <div class="preloader flex-column justify-content-center align-items-center">
        <img class="animation__shake" src="<?php echo e(asset('site_imgs/banner.gif')); ?>" alt="call_log" height="60" width="60">
    </div>
    <!-- alerts -->
    

    <!-- Body Content -->
    <?php echo $__env->yieldContent('body'); ?>

    <!-- Base Scripts -->
    <?php if(!config('adminlte.enabled_laravel_mix')): ?>
        <script src="<?php echo e(asset('vendor/jquery/jquery.min.js')); ?>"></script>
        <script src="<?php echo e(asset('vendor/bootstrap/js/bootstrap.bundle.min.js')); ?>"></script>
        <script src="<?php echo e(asset('vendor/overlayScrollbars/js/jquery.overlayScrollbars.min.js')); ?>"></script>

    <!-- Configured Scripts -->
    <?php echo $__env->make('adminlte::plugins', ['type' => 'js'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    <script src="<?php echo e(asset('vendor/adminlte/dist/js/adminlte.min.js')); ?>"></script>
    <?php else: ?>
    <script src="<?php echo e(asset(config('adminlte.laravel_mix_js_path', 'js/app.js'))); ?>"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous">
    </script>

    <?php endif; ?>


    <!-- Livewire Script -->

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    <script src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>

    

    <?php echo $__env->yieldContent('adminlte_js'); ?>

    
    
    
    <script type="text/javascript">
        $(function() {
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
            $('[data-toggle="tooltip"]').tooltip();
        });

        $(document).on({
            ajaxStart: function() {
                $('#shipment_timeline').addClass('d-none');
                $('#timeline_loader').removeClass('d-none');
                $('#shipment-container').css('min-height', '0px');
            },
            ajaxStop: function() {
                $('#timeline_loader').addClass('d-none');
            }
        });
    </script>


</body>

</html>
<?php /**PATH C:\inetpub\wwwroot\Projects\Production\erp_production\resources\views/vendor/adminlte/master.blade.php ENDPATH**/ ?>