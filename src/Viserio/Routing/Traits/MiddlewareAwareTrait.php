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
    protected $middlewares = [
        'with' => [],
        'without' => [],
    ];

    /**
     * All of the middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [];

    /**
     * The priority-sorted list of middleware.
     *
     * Forces the listed middleware to always be in the given order.
     *
     * @var array
     */
    public $middlewarePriority = [];

    /**
     * {@inheritdoc}
     */
    public function withMiddleware(ServerMiddlewareInterface $middleware)
    {
        $this->middlewares['with'][] = $middleware;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function withoutMiddleware(ServerMiddlewareInterface $middleware)
    {
        $this->middlewares['without'][] = $middleware;

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function getMiddlewares(): array
    {
        return $this->middlewares;
    }
}
