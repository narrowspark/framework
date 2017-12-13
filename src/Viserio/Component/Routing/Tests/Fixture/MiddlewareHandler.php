<?php
declare(strict_types=1);
namespace Viserio\Component\Routing\Tests\Fixture;

use Viserio\Component\Contract\Routing\MiddlewareAware as MiddlewareAwareContract;
use Viserio\Component\Routing\Traits\MiddlewareAwareTrait;

class MiddlewareHandler implements MiddlewareAwareContract
{
    use MiddlewareAwareTrait;

    public function __construct(bool $resetMiddlewares = false, bool $resetBypassedMiddlewares = false)
    {
        if ($resetMiddlewares) {
            $this->middlewares = [];
        }

        if ($resetBypassedMiddlewares) {
            $this->bypassedMiddlewares = [];
        }
    }

    /**
     * @return array
     */
    public function getMiddlewares(): array
    {
        return $this->middlewares;
    }

    /**
     * @return array
     */
    public function getBypassedMiddlewares(): array
    {
        return $this->bypassedMiddlewares;
    }
}
