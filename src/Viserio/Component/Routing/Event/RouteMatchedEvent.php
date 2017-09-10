<?php
declare(strict_types=1);
namespace Viserio\Component\Routing\Event;

use Psr\Http\Message\ServerRequestInterface;
use Viserio\Component\Contract\Events\Event as EventContract;
use Viserio\Component\Contract\Routing\Dispatcher as DispatcherContract;
use Viserio\Component\Contract\Routing\Route as RouteContract;
use Viserio\Component\Events\Traits\EventTrait;

class RouteMatchedEvent implements EventContract
{
    use EventTrait;

    /**
     * Create a new route matched event.
     *
     * @param \Viserio\Component\Contract\Routing\Dispatcher $dispatcher
     * @param \Viserio\Component\Contract\Routing\Route      $route
     * @param \Psr\Http\Message\ServerRequestInterface        $request
     */
    public function __construct(DispatcherContract $dispatcher, RouteContract $route, ServerRequestInterface $request)
    {
        $this->name       = 'route.matched';
        $this->target     = $dispatcher;
        $this->parameters = ['route' => $route, 'server_request' => $request];
    }

    /**
     * Get matched route instance.
     *
     * @return \Viserio\Component\Contract\Routing\Route
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
