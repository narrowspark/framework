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
