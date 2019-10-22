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

namespace Viserio\Component\Routing\Tests\Fixture;

use Viserio\Component\Routing\Traits\MiddlewareAwareTrait;
use Viserio\Contract\Routing\MiddlewareAware as MiddlewareAwareContract;

class MiddlewareHandler implements MiddlewareAwareContract
{
    use MiddlewareAwareTrait;

    public function __construct(bool $resetMiddleware = false, bool $resetBypassedMiddleware = false)
    {
        if ($resetMiddleware) {
            $this->middleware = [];
        }

        if ($resetBypassedMiddleware) {
            $this->bypassedMiddleware = [];
        }
    }

    /**
     * @return array
     */
    public function getMiddleware(): array
    {
        return $this->middleware;
    }

    /**
     * @return array
     */
    public function getBypassedMiddleware(): array
    {
        return $this->bypassedMiddleware;
    }
}
