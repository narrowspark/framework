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
     * @throws \Viserio\Component\Contract\Routing\Exception\LogicException   if \Psr\Http\Server\MiddlewareInterface was not found
     * @throws \Viserio\Component\Contract\Routing\Exception\RuntimeException if wrong input is given
     *
     * @return \Viserio\Component\Contract\Routing\MiddlewareAware
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
     * @return \Viserio\Component\Contract\Routing\MiddlewareAware
     */
    public function withoutMiddleware($middleware): self;
}
