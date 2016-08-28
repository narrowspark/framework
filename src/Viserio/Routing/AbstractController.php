<?php
declare(strict_types=1);
namespace Viserio\Routing;

use Viserio\Contracts\Middleware\Middleware as MiddlewareContract;

abstract class AbstractController
{
    /**
     * All middlewares.
     *
     * @var array
     */
    protected $middleware = [
        'with' => [],
        'without' => [],
    ];

    /**
     * Add a middleware to route.
     *
     * @return $this
     */
    public function withMiddleware(MiddlewareContract $middleware)
    {
        $this->middleware['with'][] = $middleware;

        return $this;
    }

    /**
     * Remove a middleware from route.
     *
     * @return $this
     */
    public function withoutMiddleware(MiddlewareContract $middleware)
    {
        $this->middleware['without'][] = $middleware;

        return $this;
    }

    /**
     * Get all middleware, including the ones from the controller.
     *
     * @return array
     */
    public function gatherMiddleware(): array
    {
        return $this->middleware;
    }
}
