<?php
declare(strict_types=1);
namespace Viserio\Routing\Middlewares;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Contracts\Middleware\Delegate as DelegateContract;
use Viserio\Contracts\Middleware\ServerMiddleware as ServerMiddlewareContract;
use Viserio\Contracts\Routing\Route as RouteContract;

class FoundMiddleware implements ServerMiddlewareContract
{
    /**
     * A route instance.
     *
     * @var \Viserio\Contracts\Routing\Route
     */
    protected $route;

    /**
     * Create a found middleware instance.
     *
     * @param \Viserio\Contracts\Routing\Route $route
     */
    public function __construct(RouteContract $route)
    {
        $this->route = $route;
    }

    /**
     * {@inheritdoc}
     */
    public function process(
        ServerRequestInterface $request,
        DelegateContract $frame
    ): ResponseInterface {
        // Add route to the request's attributes in case a middleware or handler needs access to the route
        $request = $request->withAttribute('route', $this->route);

        return $this->route->run($request);
    }
}
