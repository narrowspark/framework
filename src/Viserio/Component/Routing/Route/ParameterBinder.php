<?php
declare(strict_types=1);
namespace Viserio\Component\Routing\Route;

use Psr\Http\Message\ServerRequestInterface;
use Viserio\Component\Contracts\Routing\Route as RouteContract;

class ParameterBinder
{
    /**
     * The route instance.
     *
     * @var \Viserio\Component\Contracts\Routing\Route
     */
    protected $route;

    /**
     * Create a new Route parameter binder instance.
     *
     * @param  \Viserio\Component\Contracts\Routing\Route $route
     */
    public function __construct(RouteContract $route)
    {
        $this->route = $route;
    }

    /**
     * Get the parameters for the route.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request

     * @return array
     */
    public function getParameters(ServerRequestInterface $request): array
    {
    }
}
