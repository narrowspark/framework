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

use LogicException;
use RuntimeException;
use Viserio\Component\Routing\Traits\MiddlewareValidatorTrait;
use Viserio\Contract\Routing\MiddlewareAware as MiddlewareAwareContract;
use Viserio\Contract\Routing\PendingResourceRegistration as PendingResourceRegistrationContract;

class PendingResourceRegistration implements PendingResourceRegistrationContract
{
    use MiddlewareValidatorTrait;

    /**
     * The resource registrar.
     *
     * @var \Viserio\Component\Routing\ResourceRegistrar
     */
    protected $registrar;

    /**
     * The resource name.
     *
     * @var string
     */
    protected $name;

    /**
     * The resource controller.
     *
     * @var string
     */
    protected $controller;

    /**
     * The resource options.
     *
     * @var array
     */
    protected $options = [];

    /**
     * Create a new pending resource registration instance.
     *
     * @param \Viserio\Component\Routing\ResourceRegistrar $registrar
     */
    public function __construct(ResourceRegistrar $registrar, string $name, string $controller, array $options)
    {
        $this->name = $name;
        $this->options = $options;
        $this->registrar = $registrar;
        $this->controller = $controller;
    }

    /**
     * Handle the object's destruction.
     *
     * @return void
     */
    public function __destruct()
    {
        $this->registrar->register($this->name, $this->controller, $this->options);
    }

    /**
     * {@inheritdoc}
     */
    public function setName(string $method, string $name): PendingResourceRegistrationContract
    {
        $this->options['names'][$method] = $name;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function only(array $methods): PendingResourceRegistrationContract
    {
        $this->options['only'] = $methods;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function except(array $methods): PendingResourceRegistrationContract
    {
        $this->options['except'] = $methods;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addNames(array $names): PendingResourceRegistrationContract
    {
        $this->options['names'] = $names;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setParameters(array $parameters): PendingResourceRegistrationContract
    {
        $this->options['parameters'] = $parameters;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setParameter(string $previous, string $new): PendingResourceRegistrationContract
    {
        $this->options['parameters'][$previous] = $new;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function withMiddleware($middleware): MiddlewareAwareContract
    {
        $this->validateGivenMiddleware($middleware);

        $this->options['middleware'] = $middleware;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function withoutMiddleware($middleware): MiddlewareAwareContract
    {
        $this->validateGivenMiddleware($middleware);

        $this->options['bypass'] = $middleware;

        return $this;
    }

    /**
     * First:  Validates the given input.
     * Second: Checks if given middleware or list of middleware have the right interface.
     *
     * @param array|object|string $middleware
     *
     * @throws RuntimeException if wrong input is given
     * @throws LogicException   if \Psr\Http\Server\MiddlewareInterface was not found
     */
    private function validateGivenMiddleware($middleware): void
    {
        $this->validateInput($middleware);

        if (\is_array($middleware)) {
            foreach ($middleware as $mw) {
                $this->validateMiddleware($mw);
            }
        } else {
            $this->validateMiddleware($middleware);
        }
    }
}
