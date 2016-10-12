<?php
declare(strict_types=1);
namespace Viserio\Routing\Middlewares;

use Interop\Http\Middleware\DelegateInterface;
use Interop\Http\Middleware\ServerMiddlewareInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Contracts\Routing\Route as RouteContract;

class FoundMiddleware implements ServerMiddlewareInterface
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
        DelegateInterface $delegate
    ) {
        // Add route to the request's attributes in case a middleware or handler needs access to the route
        $request = $request->withAttribute('route', $this->route);

        return $this->route->run($request);
    }
}
