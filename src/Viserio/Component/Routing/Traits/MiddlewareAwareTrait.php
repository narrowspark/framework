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
     * @param string $name
     * @param string $middleware
     *
     * @throws \LogicException
     *
     * @return $this
     */
    public function aliasMiddleware(string $name, string $middleware)
    {
        $this->validateMiddlewareClass($middleware);

        $this->middlewares[$name] = $middleware;

        return $this;
    }

    /**
     * Set the middlewares to the route.
     *
     * @param string|array $middlewares
     *
     * @throws \RuntimeException
     * @throws \LogicException
     *
     * @return $this
     */
    public function withMiddleware($middlewares)
    {
        $this->validateMiddlewareInput($middlewares);
        $this->validateMiddlewareClass($middlewares);

        if (is_string($middlewares)) {
            $this->middlewares[] = $middlewares;

            return $this;
        }

        foreach ($middlewares as $middleware) {
            $this->middlewares[] = $middleware;
        }

        return $this;
    }

    /**
     * Remove the given middlewares from the route/controller.
     * If no middleware is passed, all middlewares will be removed.
     *
     * @param string|array|null $middlewares
     *
     * @throws \RuntimeException
     * @throws \LogicException
     *
     * @return $this
     */
    public function withoutMiddleware($middlewares = null)
    {
        if ($middlewares === null) {
            $this->middlewares[] = [];

            return $this;
        }

        $this->validateMiddlewareInput($middlewares);
        $this->validateMiddlewareClass($middlewares);

        if (is_string($middlewares)) {
            $this->bypassedMiddlewares[] = $middlewares;

            return $this;
        }

        foreach ($middlewares as $middleware) {
            $this->bypassedMiddlewares[] = $middleware;
        }

        return $this;
    }

    /**
     * Check if given input is a string or array.
     *
     * @param string|array $middlewares
     *
     * @throws \RuntimeException
     *
     * @return void
     */
    private function validateMiddlewareInput($middlewares): void
    {
        if (is_array($middlewares) || is_string($middlewares)) {
            return;
        }

        throw new RuntimeException(sprintf('Expected string or array; received [%s].', gettype($middlewares)));
    }

    /**
     * Check if given middleware class has \Interop\Http\ServerMiddleware\MiddlewareInterface implemented.
     *
     * @param string|array $middlewares
     *
     * @throws \LogicException
     *
     * @return void
     */
    private function validateMiddlewareClass($middlewares): void
    {
        $middlewareCheck = function ($middleware) {
            if (! in_array(MiddlewareInterface::class, class_implements($middleware))) {
                throw new LogicException(
                    sprintf('%s is not implemented in [%s].', MiddlewareInterface::class, $middleware)
                );
            }
        };

        if (is_string($middlewares)) {
            $middlewareCheck($middlewares);
        } elseif (is_array($middlewares)) {
            foreach ($middlewares as $middleware) {
                $middlewareCheck($middlewares);
            }
        }
    }
}
