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

namespace Viserio\Component\Routing;

use BadMethodCallException;
use Viserio\Component\Routing\Traits\MiddlewareAwareTrait;
use Viserio\Contract\Routing\MiddlewareAware as MiddlewareAwareContract;

abstract class AbstractController implements MiddlewareAwareContract
{
    use MiddlewareAwareTrait;

    /**
     * Handle calls to missing methods on the controller.
     *
     * @throws BadMethodCallException
     */
    public function __call(string $method, array $parameters)
    {
        throw new BadMethodCallException(\sprintf('Method [%s] does not exist.', $method));
    }

    /**
     * Get all middleware, including the ones from the controller.
     */
    public function gatherMiddleware(): array
    {
        return $this->middleware;
    }

    /**
     * Return all disabled middleware.
     */
    public function gatherDisabledMiddleware(): array
    {
        return $this->bypassedMiddleware;
    }
}
