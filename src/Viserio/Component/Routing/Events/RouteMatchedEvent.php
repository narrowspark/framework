<?php
declare(strict_types=1);
namespace Viserio\Component\Routing\Events;

use Viserio\Component\Contracts\Events\Event as EventContract;
use Viserio\Component\Contracts\Routing\Route as RouteContract;
use Viserio\Component\Contracts\Routing\Router as RouterContract;
use Viserio\Component\Events\Traits\EventTrait;

class RouteMatchedEvent implements EventContract
{
    use EventTrait;

    /**
     * Create a new route matched event.
     *
     * @param Viserio\Component\Contracts\Routing\Router $router
     * @param \Viserio\Component\Contracts\Routing\Route $route
     * @param \Psr\Http\Message\ServerRequestInterface   $request
     */
    public function __construct(RouterContract $router, RouteContract $route, ServerRequestInterface $request)
    {
        $this->name       = 'route.matched';
        $this->target     = $router;
        $this->parameters = ['route' => $route, 'server_request' => $request];
    }

    /**
     * Get matched route instance.
     *
     * @return \Viserio\Component\Contracts\Routing\Route
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
