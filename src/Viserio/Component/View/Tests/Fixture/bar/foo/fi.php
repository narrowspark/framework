<?php declare(strict_types=1);
$vars = \get_defined_vars();
$data = $vars['__data'] ?? null;
$path = $vars['__path'] ?? null;
echo $__env->create('layout', [$data, $path])->render(); ?>
<?php throw new Exception('section exception message'); ?>
