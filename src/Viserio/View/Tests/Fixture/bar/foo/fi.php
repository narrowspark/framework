<?php declare(strict_types=1);
echo $__env->create('layout', \Narrowspark\Arr\StaticArr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php throw new Exception('section exception message') ?>
