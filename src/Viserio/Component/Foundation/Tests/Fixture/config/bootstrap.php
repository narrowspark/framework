<?php
declare(strict_types=1);

return [
    \Viserio\Component\Foundation\Bootstrap\ConfigureKernel::class        => ['global'],
    \Viserio\Component\Exception\Bootstrap\ConsoleHandleExceptions::class => ['console'],
    \Viserio\Component\Exception\Bootstrap\HttpHandleExceptions::class    => ['http'],
];
