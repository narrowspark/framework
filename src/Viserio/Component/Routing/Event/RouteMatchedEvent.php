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
     *
     * @param \Viserio\Contract\Routing\Dispatcher     $dispatcher
     * @param \Viserio\Contract\Routing\Route          $route
     * @param \Psr\Http\Message\ServerRequestInterface $request
     */
    public function __construct(DispatcherContract $dispatcher, RouteContract $route, ServerRequestInterface $request)
    {
        $this->name = 'route.matched';
        $this->target = $dispatcher;
        $this->parameters = ['route' => $route, 'server_request' => $request];
    }

    /**
     * Get matched route instance.
     *
     * @return \Viserio\Contract\Routing\Route
     */
    public function getRoute(): RouteContract
    {
        return $this->parameters['route'];
    }

    /**
     * Get server request instance.
     *
     * @return \Psr\Http\Message\ServerRequestInterface
     */
    public function getServerRequest(): ServerRequestInterface
    {
        return $this->parameters['server_request'];
    }
}
