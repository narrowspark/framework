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
     * {@inheritdoc}
     */
    public function withMiddleware(string $middleware)
    {
        if (! in_array(ServerMiddlewareInterface::class, class_implements($middleware))) {
            throw new LogicException(sprintf(
                '[%s] should implement \Interop\Http\Middleware\ServerMiddlewareInterface',
                $middleware
            ));
        }

        $this->middlewares[] = $middleware;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function withoutMiddleware(string $middleware)
    {
        foreach ($this->middlewares as $key => $value) {
            if ($value === $middleware) {
                unset($this->middlewares[$key]);

                return $this;
            }
        }
    }
}
