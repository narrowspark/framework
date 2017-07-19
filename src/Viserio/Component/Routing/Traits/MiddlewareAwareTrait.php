<?php
declare(strict_types=1);
namespace Viserio\Component\Routing\Traits;

use Interop\Http\ServerMiddleware\MiddlewareInterface;
use LogicException;
use RuntimeException;

trait MiddlewareAwareTrait
{
    /**
     * All middlewares.
     *
     * @var array
     */
    protected $middlewares = [];

    /**
     * List of removed middlewares.
     *
     * @var array
     */
    protected $bypassedMiddlewares = [];

    /**
     * Register a short-hand name for a middleware.
     *
     * @param string        $name
     * @param object|string $middleware
     *
     * @throws \RuntimeException if wrong type is given or alias exists
     * @throws \LogicException
     *
     * @return $this
     */
    public function aliasMiddleware(string $name, $middleware)
    {
        if (isset($this->middlewares[$name])) {
            throw new RuntimeException(\sprintf('Alias [%s] already exists.', $name));
        }

        if (\is_string($middleware) || \is_object($middleware)) {
            $this->validateMiddlewareClass($middleware);

            $this->middlewares[$name] = $middleware;

            return $this;
        }

        throw new RuntimeException(\sprintf('Expected string or object; received [%s].', \gettype($middleware)));
    }

    /**
     * Set the middlewares to the route.
     *
     * @param array|object|string $middlewares
     *
     * @throws \RuntimeException if wrong type is given
     * @throws \LogicException
     *
     * @return $this
     */
    public function withMiddleware($middlewares)
    {
        $this->validateMiddlewareInput($middlewares);
        $this->validateMiddlewareClass($middlewares);

        if (\is_string($middlewares) || \is_object($middlewares)) {
            $name = \is_object($middlewares) ? \get_class($middlewares) : $middlewares;

            $this->middlewares[$name] = $middlewares;

            return $this;
        }

        foreach ($middlewares as $middleware) {
            $name = \is_object($middleware) ? \get_class($middleware) : $middleware;

            $this->middlewares[$name] = $middleware;
        }

        return $this;
    }

    /**
     * Remove the given middlewares from the route/controller.
     * If no middleware is passed, all middlewares will be removed.
     *
     * @param null|array|string $middlewares
     *
     * @throws \RuntimeException
     * @throws \LogicException
     *
     * @return $this
     */
    public function withoutMiddleware($middlewares = null)
    {
        if ($middlewares === null) {
            $this->middlewares = [];

            return $this;
        }

        $this->validateMiddlewareInput($middlewares);
        $this->validateMiddlewareClass($middlewares);

        if (\is_string($middlewares)) {
            $this->bypassedMiddlewares[$middlewares] = $middlewares;

            return $this;
        }

        foreach ($middlewares as $middleware) {
            $this->bypassedMiddlewares[$middleware] = $middleware;
        }

        return $this;
    }

    /**
     * Check if given input is a string, object or array.
     *
     * @param array|object|string $middlewares
     *
     * @throws \RuntimeException
     *
     * @return void
     */
    private function validateMiddlewareInput($middlewares): void
    {
        if (\is_array($middlewares) || \is_string($middlewares) || \is_object($middlewares)) {
            return;
        }

        throw new RuntimeException(\sprintf('Expected string, object or array; received [%s].', \gettype($middlewares)));
    }

    /**
     * Check if given middleware class has \Interop\Http\ServerMiddleware\MiddlewareInterface implemented.
     *
     * @param array|object|string $middlewares
     *
     * @throws \LogicException
     *
     * @return void
     */
    private function validateMiddlewareClass($middlewares): void
    {
        $middlewareCheck = function ($middleware): void {
            if (! \in_array(MiddlewareInterface::class, \class_implements($middleware), true)) {
                throw new LogicException(
                    \sprintf('%s is not implemented in [%s].', MiddlewareInterface::class, $middleware)
                );
            }
        };

        if (
            (\is_string($middlewares) && ! isset($this->middlewares[$middlewares])) ||
            \is_object($middlewares)
        ) {
            $middlewareCheck($middlewares);
        } elseif (\is_array($middlewares)) {
            foreach ($middlewares as $name => $middleware) {
                if (! isset($this->middlewares[$middleware])) {
                    $middlewareCheck($middleware);
                }
            }
        }
    }
}
