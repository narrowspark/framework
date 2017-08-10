<?php
declare(strict_types=1);
namespace Viserio\Component\Routing\Traits;

use TypeError;
use Viserio\Component\Contracts\Routing\Exception\RuntimeException;
use Viserio\Component\Contracts\Routing\MiddlewareAware as MiddlewareAwareContract;

trait MiddlewareAwareTrait
{
    use MiddlewareValidatorTrait;

    /**
     * List of registered middlewares.
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
     * @param string                                                    $name
     * @param \Interop\Http\ServerMiddleware\MiddlewareInterface|string $middleware
     *
     * @throws \RuntimeException if alias exists
     * @throws \TypeError        if wrong type is given
     *
     * @return $this
     */
    public function aliasMiddleware(string $name, $middleware)
    {
        if (isset($this->middlewares[$name])) {
            throw new RuntimeException(\sprintf('Alias [%s] already exists.', $name));
        }

        if (\is_string($middleware) || \is_object($middleware)) {
            $className = $this->getMiddlewareClassName($middleware);

            if (\class_exists($className)) {
                $this->validateMiddleware($className);
            }

            $this->middlewares[$name] = $middleware;

            return $this;
        }

        throw new TypeError(\sprintf('Expected string or object; received [%s].', \gettype($middleware)));
    }

    /**
     * Adds a middleware or a array of middlewares to the route/controller.
     *
     * @param \Interop\Http\ServerMiddleware\MiddlewareInterface|array|string $middlewares
     *
     * @throws \TypeError if wrong type is given
     *
     * @return \Viserio\Component\Contracts\Routing\MiddlewareAware
     */
    public function withMiddleware($middlewares): MiddlewareAwareContract
    {
        $this->validateInput($middlewares);

        if (\is_string($middlewares) || \is_object($middlewares)) {
            $className = \is_object($middlewares) ? \get_class($middlewares) : $middlewares;

            if (class_exists($className)) {
                $this->validateMiddleware($className);
            }

            $this->middlewares[$className] = $middlewares;

            return $this;
        }

        foreach ($middlewares as $middleware) {
            $className = $this->getMiddlewareClassName($middleware);

            if (class_exists($className)) {
                $this->validateMiddleware($className);
            }

            $this->middlewares[$className] = $middleware;
        }

        return $this;
    }

    /**
     * Remove the given middlewares from the route/controller.
     * If no middleware is passed, all middlewares will be removed.
     *
     * @param null|array|string $middlewares
     *
     * @throws \TypeError if wrong type is given
     *
     * @return \Viserio\Component\Contracts\Routing\MiddlewareAware
     */
    public function withoutMiddleware($middlewares = null): MiddlewareAwareContract
    {
        if ($middlewares === null) {
            $this->middlewares = [];

            return $this;
        }

        $this->validateInput($middlewares);

        if (\is_object($middlewares) || \is_string($middlewares)) {
            $name = is_object($middlewares) ? get_class($middlewares) : $middlewares;

            $this->bypassedMiddlewares[$name] = true;

            return $this;
        }

        foreach ($middlewares as $name => $middleware) {
            $middleware = $this->getMiddlewareClassName($middleware);
            $name       = \is_numeric($name) ? $middleware : $name;

            $this->bypassedMiddlewares[$name] = true;
        }

        return $this;
    }
}
