<?php
declare(strict_types=1);
namespace Viserio\Routing;

use Interop\Http\Middleware\MiddlewareInterface;
use Interop\Http\Middleware\ServerMiddlewareInterface;
use LogicException;

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
     * @throws \LogicException
     *
     * @return $this
     */
    public function withMiddleware($middleware)
    {
        if ($middleware instanceof MiddlewareInterface || $middleware instanceof ServerMiddlewareInterface) {
            $this->middleware['with'][] = $middleware;

            return $this;
        }

        throw new LogicException('Unsupported middleware type.');
    }

    /**
     * Remove a middleware from route.
     *
     * @throws \LogicException
     *
     * @return $this
     */
    public function withoutMiddleware($middleware)
    {
        if ($middleware instanceof MiddlewareInterface || $middleware instanceof ServerMiddlewareInterface) {
            $this->middleware['without'][] = $middleware;

            return $this;
        }

        throw new LogicException('Unsupported middleware type.');
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
