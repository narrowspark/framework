<?php
declare(strict_types=1);
namespace Viserio\Component\Routing;

use Viserio\Component\Contract\Routing\MiddlewareAware as MiddlewareAwareContract;
use Viserio\Component\Contract\Routing\PendingResourceRegistration as PendingResourceRegistrationContract;
use Viserio\Component\Routing\Traits\MiddlewareValidatorTrait;

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
     * @param string                                       $name
     * @param string                                       $controller
     * @param array                                        $options
     */
    public function __construct(ResourceRegistrar $registrar, string $name, string $controller, array $options)
    {
        $this->name       = $name;
        $this->options    = $options;
        $this->registrar  = $registrar;
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
    public function setName(string $method, string $name): PendingResourceRegistrationContract
    {
        $this->options['names'][$method] = $name;

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
    public function withMiddleware($middlewares): MiddlewareAwareContract
    {
        $this->validateGivenMiddleware($middlewares);

        $this->options['middlewares'] = $middlewares;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function withoutMiddleware($middlewares): MiddlewareAwareContract
    {
        $this->validateGivenMiddleware($middlewares);

        $this->options['bypass'] = $middlewares;

        return $this;
    }

    /**
     * First:  Validates the given input.
     * Second: Checks if given middleware or list of middlewares have the right interface.
     *
     * @param array|object|string $middlewares
     *
     * @throws \RuntimeException if wrong input is given
     * @throws \LogicException   if \Psr\Http\Server\MiddlewareInterface was not found
     *
     * @return void
     */
    private function validateGivenMiddleware($middlewares): void
    {
        $this->validateInput($middlewares);

        if (\is_array($middlewares)) {
            foreach ($middlewares as $middleware) {
                $this->validateMiddleware($middleware);
            }
        } else {
            $this->validateMiddleware($middlewares);
        }
    }
}
