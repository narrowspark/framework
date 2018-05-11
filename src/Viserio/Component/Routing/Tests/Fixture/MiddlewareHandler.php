<?php
declare(strict_types=1);
namespace Viserio\Component\Routing\Tests\Fixture;

use Viserio\Component\Contract\Routing\MiddlewareAware as MiddlewareAwareContract;
use Viserio\Component\Routing\Traits\MiddlewareAwareTrait;

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
