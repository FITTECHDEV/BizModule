<?php $__env->startComponent('mail::message'); ?>
# Hi <?php echo e($ticket->user->FirstName ?? 'User'); ?>,
This is to inform you that your  ticket <a href="<?php echo e($url); ?>">#<?php echo e($ticket->TicketNo); ?></a> has been successfully <b>Solved</b>.
Kindly confirm and close the ticket.
<hr>
<p>
    #Title: <?php echo ucwords($ticket->Title); ?> <br>
</p>
<p>
    <?php echo ucfirst($ticket->Description); ?>

</p>

<?php $__env->startComponent('mail::button', ['url' => $url, 'color' => 'success']); ?>
    View Ticket in FIT E-Ticket
<?php echo $__env->renderComponent(); ?>

Thanks,<br>
<?php echo e(config('app.name')); ?>

<?php echo $__env->renderComponent(); ?>
<?php /**PATH C:\inetpub\wwwroot\Projects\Production\erp_production\resources\views/emails/ticket/ticket_solved.blade.php ENDPATH**/ ?>