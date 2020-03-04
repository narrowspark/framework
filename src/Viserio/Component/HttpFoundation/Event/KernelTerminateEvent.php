<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018-2020 Daniel Bannert
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/narrowspark/automatic
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
