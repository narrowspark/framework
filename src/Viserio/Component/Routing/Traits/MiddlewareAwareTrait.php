<?php
declare(strict_types=1);
namespace Viserio\Component\Routing\Traits;

use RuntimeException;

trait MiddlewareAwareTrait
{
    use MiddlewareValidatorTrait;

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
     * Adds a middleware or a array of middlewares to the route/controller.
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
        $this->validateInput($middlewares);
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

        $this->validateInput($middlewares);
        $this->validateMiddlewareClass($middlewares);

        if (\is_object($middlewares) || \is_string($middlewares)) {
            $name = is_object($middlewares) ? get_class($middlewares) : $middlewares;

            $this->bypassedMiddlewares[$name] = true;

            return $this;
        }

        foreach ($middlewares as $name => $middleware) {
            $middleware = is_object($middleware) ? get_class($middleware) : $middleware;
            $name       = is_numeric($name) ? $middleware : $name;

            $this->bypassedMiddlewares[$name] = true;
        }

        return $this;
    }
}
