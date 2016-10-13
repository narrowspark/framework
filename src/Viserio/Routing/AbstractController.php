<?php
declare(strict_types=1);
namespace Viserio\Routing;

use Viserio\Routing\Traits\MiddlewareAwareTrait;

abstract class AbstractController
{
    use MiddlewareAwareTrait;

    /**
     * Get all middleware, including the ones from the controller.
     *
     * @return array
     */
    public function gatherMiddleware(): array
    {
        return $this->middlewares;
    }
}
