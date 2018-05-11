<?php
declare(strict_types=1);
namespace Viserio\Component\Contract\Routing;

interface MiddlewareAware
{
    /**
     * Adds a middleware or a array of middleware to the route/controller.
     *
     * @param array|object|string $middleware
     *
     * @throws \LogicException   if \Psr\Http\Server\MiddlewareInterface was not found
     * @throws \RuntimeException if wrong input is given
     *
     * @return $this
     */
    public function withMiddleware($middleware): self;

    /**
     * Remove the given middleware from the route/controller.
     * If no middleware is passed, all middleware will be removed.
     *
     * @param array|object|string $middleware
     *
     * @throws \RuntimeException if wrong input is given
     *
     * @return $this
     */
    public function withoutMiddleware($middleware): self;
}
