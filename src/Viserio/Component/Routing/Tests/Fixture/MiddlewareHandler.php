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

    public function getMiddleware(): array
    {
        return $this->middleware;
    }

    public function getBypassedMiddleware(): array
    {
        return $this->bypassedMiddleware;
    }
}
