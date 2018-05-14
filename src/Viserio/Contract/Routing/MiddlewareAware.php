<?php

declare(strict_types=1);

/**
 * This file is part of Narrowspark Framework.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Viserio\Contract\Routing;

interface MiddlewareAware
{
    /**
     * Adds a middleware or a array of middleware to the route/controller.
     *
     * @param array|object|string $middleware
     *
     * @throws \Viserio\Contract\Routing\Exception\LogicException   if \Psr\Http\Server\MiddlewareInterface was not found
     * @throws \Viserio\Contract\Routing\Exception\RuntimeException if wrong input is given
     *
     * @return self
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
     * @return self
     */
    public function withoutMiddleware($middleware): self;
}
