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
     * @param string $method
     * @param array  $parameters
     *
     * @throws \BadMethodCallException
     *
     * @return mixed
     */
    public function __call(string $method, array $parameters)
    {
        throw new BadMethodCallException(\sprintf('Method [%s] does not exist.', $method));
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

    /**
     * Return all disabled middleware.
     *
     * @return array
     */
    public function gatherDisabledMiddleware(): array
    {
        return $this->bypassedMiddleware;
    }
}
