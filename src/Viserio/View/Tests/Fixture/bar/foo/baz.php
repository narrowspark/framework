<?php echo $__env->make('layout', \Viserio\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php $__env->getVirtuoso()->startSection('content'); ?>
<?php throw new Exception('section exception message') ?>
<?php $__env->getVirtuoso()->stopSection(); ?>
