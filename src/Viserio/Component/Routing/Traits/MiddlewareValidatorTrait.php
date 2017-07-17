<?php
declare(strict_types=1);
namespace Viserio\Component\Routing\Traits;

use LogicException;
use RuntimeException;
use Interop\Http\ServerMiddleware\MiddlewareInterface;

trait MiddlewareValidatorTrait
{

    /**
     * Check if given middleware class has \Interop\Http\ServerMiddleware\MiddlewareInterface implemented.
     *
     * @param string|object|array $middlewares
     *
     * @throws \LogicException
     *
     * @return void
     */
    protected function validateMiddlewareClass($middlewares): void
    {
        if ($this->isClassName($middlewares) || is_object($middlewares)
        ) {
            $this->validateMiddleware($middlewares);
        } elseif (is_array($middlewares)) {
            foreach ($middlewares as $name => $middleware) {
                if (! isset($this->middlewares[$middleware], $this->middlewares[$name])) {
                    $this->validateMiddleware($middleware);
                }
            }
        }
    }

    /**
     * Check if given input is a string, object or array.
     *
     * @param string|object|array $middlewares
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
     * @param string|object $middleware
     *
     * @throws \LogicException
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
     * @param string|object|array $middlewares
     *
     * @return bool
     */
    private function isClassName($middlewares): bool
    {
        return is_string($middlewares) && class_exists($middlewares) && ! isset($this->middlewares[$middlewares]);
    }
}
