<?php
declare(strict_types=1);
namespace Viserio\Component\Routing\Traits;

use Interop\Http\ServerMiddleware\MiddlewareInterface;
use TypeError;
use Viserio\Component\Contracts\Routing\Exception\LogicException;

trait MiddlewareValidatorTrait
{
    /**
     * Check if given input is a string, object or array.
     *
     * @param array|object|string $middlewares
     *
     * @throws \TypeError
     *
     * @return void
     */
    protected function validateInput($middlewares): void
    {
        if (is_array($middlewares) || is_string($middlewares) || is_object($middlewares)) {
            return;
        }

        throw new TypeError(sprintf('Expected string, object or array; received [%s].', gettype($middlewares)));
    }

    /**
     * Validates if given object or string has a middleware interface.
     *
     * @param \Interop\Http\ServerMiddleware\MiddlewareInterface|string $middleware
     *
     * @throws \LogicException if \Interop\Http\ServerMiddleware\MiddlewareInterface was not found
     *
     * @return void
     */
    protected function validateMiddleware($middleware): void
    {
        $middleware = $this->getMiddlewareClassName($middleware);
        $interfaces = \class_implements($middleware);

        if (! isset($interfaces[MiddlewareInterface::class])) {
            throw new LogicException(
                sprintf('%s is not implemented in [%s].', MiddlewareInterface::class, $middleware)
            );
        }
    }

    /**
     * If input is a object returns full class name else the given string input.
     *
     * @param \Interop\Http\ServerMiddleware\MiddlewareInterface|string $middleware
     *
     * @return string
     */
    protected function getMiddlewareClassName($middleware): string
    {
        return \is_object($middleware) ? \get_class($middleware) : $middleware;
    }
}
