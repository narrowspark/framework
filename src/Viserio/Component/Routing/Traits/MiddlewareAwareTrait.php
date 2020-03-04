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

namespace Viserio\Component\Routing\Traits;

use Viserio\Contract\Routing\Exception\RuntimeException;
use Viserio\Contract\Routing\Exception\UnexpectedValueException;
use Viserio\Contract\Routing\MiddlewareAware as MiddlewareAwareContract;

trait MiddlewareAwareTrait
{
    use MiddlewareValidatorTrait;

    /**
     * List of registered middleware.
     *
     * @var array
     */
    protected $middleware = [];

    /**
     * List of removed middleware.
     *
     * @var array
     */
    protected $bypassedMiddleware = [];

    /**
     * Register a short-hand name for a middleware.
     *
     * @param \Psr\Http\Server\MiddlewareInterface|string $middleware
     *
     * @throws \Viserio\Contract\Routing\Exception\RuntimeException         if alias exists
     * @throws \Viserio\Contract\Routing\Exception\UnexpectedValueException if wrong type is given
     *
     * @return $this
     */
    public function aliasMiddleware(string $name, $middleware)
    {
        if (isset($this->middleware[$name])) {
            throw new RuntimeException(\sprintf('Alias [%s] already exists.', $name));
        }

        if (\is_string($middleware) || \is_object($middleware)) {
            $className = $this->getMiddlewareClassName($middleware);

            if (\class_exists($className)) {
                $this->validateMiddleware($className);
            }

            $this->middleware[$name] = $middleware;

            return $this;
        }

        throw new UnexpectedValueException(\sprintf('Expected string or object; received [%s].', \gettype($middleware)));
    }

    /**
     * Adds a middleware or a array of middleware to the route/controller.
     *
     * @param array|\Psr\Http\Server\MiddlewareInterface|string $middleware
     *
     * @throws \Viserio\Contract\Routing\Exception\UnexpectedValueException if wrong type is given
     */
    public function withMiddleware($middleware): MiddlewareAwareContract
    {
        $this->validateInput($middleware);

        if (\is_string($middleware) || \is_object($middleware)) {
            $className = \is_object($middleware) ? \get_class($middleware) : $middleware;

            if (\class_exists($className)) {
                $this->validateMiddleware($className);
            }

            $this->middleware[$className] = $middleware;

            return $this;
        }

        foreach ($middleware as $middleware) {
            $className = $this->getMiddlewareClassName($middleware);

            if (\class_exists($className)) {
                $this->validateMiddleware($className);
            }

            $this->middleware[$className] = $middleware;
        }

        return $this;
    }

    /**
     * Remove the given middleware from the route/controller.
     * If no middleware is passed, all middleware will be removed.
     *
     * @param null|array|string $middleware
     *
     * @throws \Viserio\Contract\Routing\Exception\UnexpectedValueException if wrong type is given
     */
    public function withoutMiddleware($middleware = null): MiddlewareAwareContract
    {
        if ($middleware === null) {
            $this->middleware = [];

            return $this;
        }

        $this->validateInput($middleware);

        if (\is_object($middleware) || \is_string($middleware)) {
            $name = \is_object($middleware) ? \get_class($middleware) : $middleware;

            $this->bypassedMiddleware[$name] = true;

            return $this;
        }

        foreach ($middleware as $name => $middleware) {
            $middleware = $this->getMiddlewareClassName($middleware);
            $name = \is_numeric($name) ? $middleware : $name;

            $this->bypassedMiddleware[$name] = true;
        }

        return $this;
    }
}
