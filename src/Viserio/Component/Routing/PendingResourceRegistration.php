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
     * @return $this
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
     * @return $this
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
     * @return $this
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
     * @return $this
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
     * @return $this
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
     * @return $this
     */
    public function addParameter(string $previous, string $new): self
    {
        $this->options['parameters'][$previous] = $new;

        return $this;
    }

    /**
     * Adds a middleware or a array of middlewares to the route.
     *
     * @param string|array|object $middlewares
     *
     * @throws \LogicException   if \Interop\Http\ServerMiddleware\MiddlewareInterface was not found
     * @throws \RuntimeException
     *
     * @return $this
     */
    public function withMiddleware($middlewares): self
    {
        $this->validateInput($middlewares);

        if (is_array($middlewares)) {
            $this->validateMiddleware($middlewares);
        }

        $this->options['middlewares'] = $middlewares;

        return $this;
    }

    /**
     * Remove the given middlewares from the route/controller.
     *
     * @param mixed $middlewares
     *
     * @throws \LogicException
     *
     * @return $this
     */
    public function withoutMiddleware($middlewares): self
    {
        $this->validateMiddlewareClass($middlewares);

        $this->options['bypass'] = $middlewares;

        return $this;
    }
}
