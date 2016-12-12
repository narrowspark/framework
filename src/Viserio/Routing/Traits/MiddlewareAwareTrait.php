<?php
declare(strict_types=1);
namespace Viserio\Routing\Traits;

use Interop\Http\Middleware\ServerMiddlewareInterface;
use LogicException;

trait MiddlewareAwareTrait
{
    /**
     * All middlewares.
     *
     * @var array
     */
    protected $middlewares = [];

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
        $this->checkMiddlewareClass($middleware);

        $this->middleware[$name] = $middleware;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function withMiddleware(string $middleware)
    {
        $this->checkMiddlewareClass($middleware);

        $this->middlewares[] = $middleware;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function withoutMiddleware(string $middleware)
    {
        foreach ($this->middlewares as $key => $value) {
            if ($value === $middleware || $key === $middleware) {
                unset($this->middlewares[$key]);

                return $this;
            }
        }
    }

    /**
     * Check a middleware class if \Interop\Http\Middleware\ServerMiddlewareInterface is implemented.
     *
     * @param string $middleware
     *
     * @throws \LogicException
     */
    private function checkMiddlewareClass(string $middleware)
    {
        if (!in_array(ServerMiddlewareInterface::class, class_implements($middleware))) {
            throw new LogicException(
                sprintf(
                    '\Interop\Http\Middleware\ServerMiddlewareInterface is not implemented in [%s]',
                    $middleware
                )
            );
        }
    }
}
