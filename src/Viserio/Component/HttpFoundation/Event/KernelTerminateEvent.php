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

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Component\Events\Traits\EventTrait;
use Viserio\Contract\Events\Event as EventContract;
use Viserio\Contract\HttpFoundation\Terminable as TerminableContract;

class KernelTerminateEvent implements EventContract
{
    use EventTrait;

    /**
     * Create a new kernel terminate event.
     *
     * @param \Viserio\Contract\HttpFoundation\Terminable $kernel
     * @param \Psr\Http\Message\ServerRequestInterface    $serverRequest
     * @param \Psr\Http\Message\ResponseInterface         $response
     */
    public function __construct(
        TerminableContract $kernel,
        ServerRequestInterface $serverRequest,
        ResponseInterface $response
    ) {
        $this->name = TerminableContract::TERMINATE;
        $this->target = $kernel;
        $this->parameters = ['server_request' => $serverRequest, 'response' => $response];
    }
}
