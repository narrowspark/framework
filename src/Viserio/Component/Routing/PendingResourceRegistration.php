<?php
declare(strict_types=1);
namespace Viserio\Component\Routing;

use Viserio\Component\Routing\Traits\MiddlewareValidatorTrait;

class PendingResourceRegistration
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
     * Set the methods the controller should apply to.
     *
     * @param string[] $methods
     *
     * @return \Viserio\Component\Routing\PendingResourceRegistration
     */
    public function only(array $methods): self
    {
        $this->options['only'] = $methods;

        return $this;
    }

    /**
     * Set the methods the controller should exclude.
     *
     * @param string[] $methods
     *
     * @return \Viserio\Component\Routing\PendingResourceRegistration
     */
    public function except(array $methods): self
    {
        $this->options['except'] = $methods;

        return $this;
    }

    /**
     * Set the route names for controller actions.
     *
     * @param string[] $names
     *
     * @return \Viserio\Component\Routing\PendingResourceRegistration
     */
    public function addNames(array $names): self
    {
        $this->options['names'] = $names;

        return $this;
    }

    /**
     * Set the route name for a controller action.
     *
     * @param string $method
     * @param string $name
     *
     * @return \Viserio\Component\Routing\PendingResourceRegistration
     */
    public function setName(string $method, string $name): self
    {
        $this->options['names'][$method] = $name;

        return $this;
    }

    /**
     * Override the route parameter names.
     *
     * @param array $parameters
     *
     * @return \Viserio\Component\Routing\PendingResourceRegistration
     */
    public function setParameters(array $parameters): self
    {
        $this->options['parameters'] = $parameters;

        return $this;
    }

    /**
     * Override a route parameter's name.
     *
     * @param string $previous
     * @param string $new
     *
     * @return \Viserio\Component\Routing\PendingResourceRegistration
     */
    public function addParameter(string $previous, string $new): self
    {
        $this->options['parameters'][$previous] = $new;

        return $this;
    }

    /**
     * Set a middleware to the resource.
     *
     * @param mixed $middlewares
     *
     * @throws \LogicException
     *
     * @return \Viserio\Component\Routing\PendingResourceRegistration
     */
    public function setMiddlewares($middlewares): self
    {
        $this->validateMiddlewareClass($middlewares);

        $this->options['middlewares'] = $middlewares;

        return $this;
    }
}
