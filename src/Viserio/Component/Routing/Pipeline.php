<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018-2020 Daniel Bannert
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/narrowspark/automatic
 */

namespace Viserio\Component\Routing;

use Closure;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Viserio\Component\Pipeline\Pipeline as BasePipeline;
use Viserio\Contract\Container\Factory as FactoryContract;
use Viserio\Contract\Routing\Exception\RuntimeException;

class Pipeline extends BasePipeline
{
    /**
     * {@inheritdoc}
     */
    protected $method = 'process';

    /**
     * {@inheritdoc}
     */
    protected function getSlice(): Closure
    {
        return function ($stack, $pipe) {
            return function ($passable) use ($stack, $pipe) {
                $slice = parent::getSlice();

                $callable = $slice($this->getRequestHandlerMiddleware($stack), $pipe);

                return $callable($passable);
            };
        };
    }

    /**
     * Resolve from container.
     *
     * @throws \Viserio\Contract\Routing\Exception\RuntimeException
     */
    protected function sliceThroughContainer($traveler, $stack, string $stage)
    {
        [$name, $parameters] = $this->parseStageString($stage);
        $parameters = \array_merge([$traveler, $stack], $parameters);
        $class = null;

        if ($this->container->has($name)) {
            $class = $this->container->get($name);
        } elseif ($this->container instanceof FactoryContract) {
            $class = $this->container->make($name);
        } else {
            throw new RuntimeException(\sprintf('Class [%s] is not being managed by the container.', $name));
        }

        return $this->getInvoker()->call([$class, $this->method], $parameters);
    }

    /**
     * Private delegate callable middleware for the pipe.
     */
    private function getRequestHandlerMiddleware(callable $middleware): RequestHandlerInterface
    {
        return new class($middleware) implements RequestHandlerInterface {
            /** @var callable */
            private $middleware;

            /**
             * Create a new delegate callable middleware instance.
             */
            public function __construct(callable $middleware)
            {
                $this->middleware = $middleware;
            }

            /**
             * {@inheritdoc}
             */
            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                return \call_user_func($this->middleware, $request);
            }
        };
    }
}
