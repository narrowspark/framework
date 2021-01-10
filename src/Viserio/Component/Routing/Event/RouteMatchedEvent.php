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

namespace Viserio\Component\Routing\Event;

use Psr\Http\Message\ServerRequestInterface;
use Viserio\Component\Events\Traits\EventTrait;
use Viserio\Contract\Events\Event as EventContract;
use Viserio\Contract\Routing\Dispatcher as DispatcherContract;
use Viserio\Contract\Routing\Route as RouteContract;

class RouteMatchedEvent implements EventContract
{
    use EventTrait;

    /**
     * Create a new route matched event.
     */
    public function __construct(DispatcherContract $dispatcher, RouteContract $route, ServerRequestInterface $request)
    {
        $this->name = 'route.matched';
        $this->target = $dispatcher;
        $this->parameters = ['route' => $route, 'server_request' => $request];
    }

    /**
     * Get matched route instance.
     */
    public function getRoute(): RouteContract
    {
        return $this->parameters['route'];
    }

    /**
     * Get server request instance.
     */
    public function getServerRequest(): ServerRequestInterface
    {
        return $this->parameters['server_request'];
    }
}
