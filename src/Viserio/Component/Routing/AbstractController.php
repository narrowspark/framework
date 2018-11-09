<?php
declare(strict_types=1);
namespace Viserio\Component\Routing;

use BadMethodCallException;
use Viserio\Component\Contract\Routing\MiddlewareAware as MiddlewareAwareContract;
use Viserio\Component\Routing\Traits\MiddlewareAwareTrait;

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
