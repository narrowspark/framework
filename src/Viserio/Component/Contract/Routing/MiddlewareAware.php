<?php
declare(strict_types=1);
namespace Viserio\Component\Contract\Routing;

interface MiddlewareAware
{
    /**
     * Adds a middleware or a array of middlewares to the route/controller.
     *
     * @param array|object|string $middlewares
     *
     * @throws \LogicException   if \Interop\Http\ServerMiddleware\MiddlewareInterface was not found
     * @throws \RuntimeException if wrong input is given
     *
     * @return $this
     */
    public function withMiddleware($middlewares): self;

    /**
     * Remove the given middlewares from the route/controller.
     * If no middleware is passed, all middlewares will be removed.
     *
     * @param array|object|string $middlewares
     *
     * @throws \RuntimeException if wrong input is given
     *
     * @return $this
     */
    public function withoutMiddleware($middlewares): self;
}
