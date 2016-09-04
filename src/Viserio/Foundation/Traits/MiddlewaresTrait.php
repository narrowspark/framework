<?php
declare(strict_types=1);
namespace Viserio\Foundation\Traits;

use Stack\Builder as StackBuilder;

trait MiddlewaresTrait
{
    /**
     * All of the developer defined middlewares.
     *
     * @var \SplPriorityQueue
     */
    protected $middlewares;

    /**
     * @var \Stack\Builder
     */
    protected $stack;

    /**
     * Add a middleware to the application.
     *
     * @param \Closure|string|array $middleware
     * @param int|null              $priority
     */
    public function addMiddleware($middleware, $priority = null)
    {
        $this->middlewares->insert($middleware, (int) $priority);
    }

    /**
     * Resolve stack middlewares.
     *
     * @return \Stack\Builder
     */
    protected function resolveStack()
    {
        if ($this->stack !== null) {
            return $this->stack;
        }

        $this->stack = new StackBuilder();

        foreach ($this->middlewares as $middleware) {
            if (! is_array($middleware)) {
                $middleware = [$middleware];
            }

            call_user_func_array([$this->stack, 'push'], $middleware);
        }

        return $this->stack;
    }
}
