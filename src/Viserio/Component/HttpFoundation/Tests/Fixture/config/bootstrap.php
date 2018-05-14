<?php

declare(strict_types=1);

/**
 * This file is part of Narrowspark Framework.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

return [
    Viserio\Component\Foundation\Bootstrap\ConfigureKernelBootstrap::class => ['global'],
    Viserio\Component\Exception\Bootstrap\ConsoleHandleExceptionsBootstrap::class => ['console'],
    Viserio\Component\Exception\Bootstrap\HttpHandleExceptionsBootstrap::class => ['http'],
];
