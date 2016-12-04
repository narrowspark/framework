<?php
declare(strict_types=1);
namespace Viserio\Routing\Traits;

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
