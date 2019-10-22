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

namespace Viserio\Component\HttpFoundation\Event;

use Psr\Http\Message\ServerRequestInterface;
use Viserio\Component\Events\Traits\EventTrait;
use Viserio\Contract\Events\Event as EventContract;
use Viserio\Contract\HttpFoundation\HttpKernel as HttpKernelContract;

class KernelRequestEvent implements EventContract
{
    use EventTrait;

    /**
     * Create a new kernel request event.
     *
     * @param \Viserio\Contract\HttpFoundation\HttpKernel $kernel
     * @param \Psr\Http\Message\ServerRequestInterface    $serverRequest
     */
    public function __construct(HttpKernelContract $kernel, ServerRequestInterface $serverRequest)
    {
        $this->name = HttpKernelContract::REQUEST;
        $this->target = $kernel;
        $this->parameters = ['server_request' => $serverRequest];
    }
}
