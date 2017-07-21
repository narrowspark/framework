<?php
declare(strict_types=1);
namespace Viserio\Component\Routing\Traits;

use Interop\Http\ServerMiddleware\MiddlewareInterface;
use LogicException;
use RuntimeException;

trait MiddlewareValidatorTrait
{
    /**
     * Check if given input is a string, object or array.
     *
     * @param array|object|string $middlewares
     *
     * @throws \RuntimeException
     *
     * @return void
     */
    protected function validateInput($middlewares): void
    {
        if (is_array($middlewares) || is_string($middlewares) || is_object($middlewares)) {
            return;
        }

        throw new RuntimeException(sprintf('Expected string, object or array; received [%s].', gettype($middlewares)));
    }

    /**
     * Validates if given object or string has a middleware interface.
     *
     * @param object|string $middleware
     *
     * @throws \LogicException if \Interop\Http\ServerMiddleware\MiddlewareInterface was not found
     *
     * @return void
     */
    protected function validateMiddleware($middleware): void
    {
        $interfaces = class_implements($middleware);

        if (! isset($interfaces[MiddlewareInterface::class])) {
            throw new LogicException(
                sprintf('%s is not implemented in [%s].', MiddlewareInterface::class, $middleware)
            );
        }
    }

    /**
     * Check if input is a class name.
     *
     * @param array|object|string $middlewares
     *
     * @return bool
     */
    protected function isClassName($middlewares): bool
    {
        return is_string($middlewares) && class_exists($middlewares);
    }
}
