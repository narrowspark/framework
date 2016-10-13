<?php
declare(strict_types=1);
namespace Viserio\Routing\Traits;

use Interop\Http\Middleware\MiddlewareInterface;
use Interop\Http\Middleware\ServerMiddlewareInterface;
use LogicException;

trait MiddlewareAwareTrait {
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
    public function withMiddleware($middleware)
    {

        if ($middleware instanceof MiddlewareInterface || $middleware instanceof ServerMiddlewareInterface) {
            $this->middlewares['with'][] = $middleware;

            return $this;
        }

        throw new LogicException('Unsupported middleware type.');
    }

    /**
     * {@inheritdoc}
     */
    public function withoutMiddleware($middleware)
    {
        if ($middleware instanceof MiddlewareInterface || $middleware instanceof ServerMiddlewareInterface) {
            $this->middlewares['without'][] = $middleware;

            return $this;
        }

        throw new LogicException('Unsupported middleware type.');
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
