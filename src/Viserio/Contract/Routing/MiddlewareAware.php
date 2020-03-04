<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018-2020 Daniel Bannert
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/narrowspark/automatic
 */

namespace Viserio\Contract\Routing;

use RuntimeException;

interface MiddlewareAware
{
    /**
     * Adds a middleware or a array of middleware to the route/controller.
     *
     * @param array|object|string $middleware
     *
     * @throws \Viserio\Contract\Routing\Exception\LogicException   if \Psr\Http\Server\MiddlewareInterface was not found
     * @throws \Viserio\Contract\Routing\Exception\RuntimeException if wrong input is given
     */
    public function withMiddleware($middleware): self;

    /**
     * Remove the given middleware from the route/controller.
     * If no middleware is passed, all middleware will be removed.
     *
     * @param array|object|string $middleware
     *
     * @throws RuntimeException if wrong input is given
     */
    public function withoutMiddleware($middleware): self;
}
