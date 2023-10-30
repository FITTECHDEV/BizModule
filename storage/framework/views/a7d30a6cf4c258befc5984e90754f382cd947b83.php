<?php $__env->startComponent('mail::message'); ?>
# Hi

## Entity <a href="<?php echo e($url); ?>"><?php echo e($entityCode); ?></a> Rejected

### Reason : <?php echo e($reason); ?>


Kindly click the button below to Review and Resubmit your Entity for creation.

<?php $__env->startComponent('mail::button', ['url' => $url]); ?>
Review Entity
<?php echo $__env->renderComponent(); ?>

Thanks,<br>
<?php echo e(config('app.name')); ?>

<?php echo $__env->renderComponent(); ?>
<?php /**PATH C:\inetpub\wwwroot\fit_service_desk\resources\views/emails/reject-entity.blade.php ENDPATH**/ ?>