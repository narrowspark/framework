<?php
declare(strict_types=1);
namespace Viserio\Component\Routing\Route;

use BadMethodCallException;
use Closure;
use InvalidArgumentException;
use Viserio\Component\Contracts\Routing\Route as RouteContract;
use Viserio\Component\Contracts\Routing\Router as RouterContract;
use Viserio\Component\Routing\PendingResourceRegistration;

class Registrar
{
    /**
     * The router instance.
     *
     * @var \Viserio\Component\Contracts\Routing\Router
     */
    protected $router;

    /**
     * The attributes to pass on to the router.
     *
     * @var array
     */
    protected $attributes = [];

    /**
     * The methods to dynamically pass through to the router.
     *
     * @var array
     */
    protected static $passthru = [
        'get',
        'post',
        'put',
        'patch',
        'delete',
        'options',
        'any',
    ];

    /**
     * The attributes that can be set through this class.
     *
     * @var array
     */
    protected static $allowedAttributes = [
        'as',
        'domain',
        'middleware',
        'name',
        'namespace',
        'prefix',
    ];

    /**
     * The attributes that are aliased.
     *
     * @var array
     */
    protected $aliases = [
        'name' => 'as',
    ];

    /**
     * Create a new route registrar instance.
     *
     * @param \Viserio\Component\Contracts\Routing\Router $router
     */
    public function __construct(RouterContract $router)
    {
        $this->router = $router;
    }

    /**
     * Dynamically handle calls into the route registrar.
     *
     * @param string $method
     * @param array  $parameters
     *
     * @throws \BadMethodCallException
     * @throws \InvalidArgumentException
     *
     * @return $this|\Viserio\Component\Contracts\Routing\Route
     */
    public function __call($method, $parameters)
    {
        if (isset(self::$passthru[$method])) {
            return $this->registerRoute($method, ...$parameters);
        }

        if (isset(self::$allowedAttributes[$method])) {
            return $this->attribute($method, $parameters[0]);
        }

        throw new BadMethodCallException(sprintf('Method [%s] does not exist.', $method));
    }

    /**
     * Set the value for a given attribute.
     *
     * @param string $key
     * @param mixed  $value
     *
     * @throws \InvalidArgumentException
     *
     * @return $this
     */
    public function attribute(string $key, $value): self
    {
        if (! isset(self::$allowedAttributes[$key])) {
            throw new InvalidArgumentException(sprintf('Attribute [%s] does not exist.', $key));
        }

        $alias                    = $this->aliases[$key] ?? $key;
        $this->attributes[$alias] = $value;

        return $this;
    }

    /**
     * Route a resource to a controller.
     *
     * @param string $name
     * @param string $controller
     * @param array  $options
     *
     * @return \Viserio\Component\Routing\PendingResourceRegistration
     */
    public function resource(string $name, string $controller, array $options = []): PendingResourceRegistration
    {
        return $this->router->resource($name, $controller, $this->attributes + $options);
    }

    /**
     * Create a route group with shared attributes.
     *
     * @param \Closure|string $callback
     *
     * @return void
     */
    public function group($callback): void
    {
        $this->router->group($this->attributes, $callback);
    }

    /**
     * Register a new route with the given verbs.
     *
     * @param array|string               $methods
     * @param string                     $uri
     * @param null|array|\Closure|string $action
     *
     * @return \Viserio\Component\Contracts\Routing\Route
     */
    public function match(string $methods, string $uri, $action = null): RouteContract
    {
        return $this->router->match($methods, $uri, $this->compileAction($action));
    }

    /**
     * Register a new route with the router.
     *
     * @param string                     $method
     * @param string                     $uri
     * @param null|array|\Closure|string $action
     *
     * @return \Viserio\Component\Contracts\Routing\Route
     */
    protected function registerRoute(string $method, string $uri, $action = null): RouteContract
    {
        if (! is_array($action)) {
            $action = array_merge($this->attributes, $action ? ['uses' => $action] : []);
        }

        return $this->router->{$method}($uri, $this->compileAction($action));
    }

    /**
     * Compile the action into an array including the attributes.
     *
     * @param null|array|\Closure|string $action
     *
     * @return array
     */
    protected function compileAction($action): array
    {
        if ($action === null) {
            return $this->attributes;
        }

        if (is_string($action) || $action instanceof Closure) {
            $action = ['uses' => $action];
        }

        return array_merge($this->attributes, $action);
    }
}
