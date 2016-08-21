<?php
declare(strict_types=1);
namespace Viserio\Routing;

use Closure;
use Interop\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Contracts\Container\Traits\ContainerAwareTrait;
use Viserio\Contracts\Events\Traits\EventsAwareTrait;
use Viserio\Contracts\Routing\Route as RouteContract;
use Viserio\Contracts\Routing\RouteGroup as RouteGroupContract;
use Viserio\Contracts\Routing\Router as RouterContract;
use Viserio\Contracts\Support\Arrayable as ArrayableContract;

class Router implements RouterContract, ArrayableContract
{
    use ContainerAwareTrait;
    use EventsAwareTrait;

    /**
     * The route collection instance.
     *
     * @var array
     */
    protected $routes = [];

    /**
     * An flattened array of all of the routes.
     *
     * @var array
     */
    protected $allRoutes = [];

    /**
     * @var \Viserio\Routing\RouteGroup[]
     */
    protected $groups = [];

    /**
     * Used global parameters in all routes.
     *
     * @var string[]
     */
    protected $globalParameterConditions = [];

    /**
     * Create a new Router instance.
     *
     * @param \Interop\Container\ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function get(string $uri, $action = null): RouteContract
    {
        return $this->addRoute(['GET', 'HEAD'], $uri, $action);
    }

    /**
     * {@inheritdoc}
     */
    public function post(string $uri, $action = null): RouteContract
    {
        return $this->addRoute('POST', $uri, $action);
    }

    /**
     * {@inheritdoc}
     */
    public function put(string $uri, $action = null): RouteContract
    {
        return $this->addRoute('PUT', $uri, $action);
    }

    /**
     * {@inheritdoc}
     */
    public function patch(string $uri, $action = null): RouteContract
    {
        return $this->addRoute('PATCH', $uri, $action);
    }

    /**
     * {@inheritdoc}
     */
    public function delete(string $uri, $action = null): RouteContract
    {
        return $this->addRoute('DELETE', $uri, $action);
    }

    /**
     * {@inheritdoc}
     */
    public function options(string $uri, $action = null): RouteContract
    {
        return $this->addRoute('OPTIONS', $uri, $action);
    }

    /**
     * {@inheritdoc}
     */
    public function any(string $uri, $action = null): RouteContract
    {
        $verbs = ['GET', 'HEAD', 'POST', 'PUT', 'PATCH', 'DELETE'];

        return $this->addRoute($verbs, $uri, $action);
    }

    /**
     * {@inheritdoc}
     */
    public function match($methods, $uri, $action = null): RouteContract
    {
        return $this->addRoute(array_map('strtoupper', (array) $methods), $uri, $action);
    }

    /**
     * {@inheritdoc}
     */
    public function getGroup(): RouteGroupContract
    {
        return $this->group;
    }

    /**
     * {@inheritdoc}
     */
    public function group(array $attributes, Closure $callback): RouterContract
    {
        $this->group = $group;

        return $this;
    }

    /**
     * Defines the supplied parameter name to be globally associated with the pattern
     *
     * @param string $parameterName
     * @param string $pattern
     *
     * @return $this
     */
    public function setGlobalParameter(string $parameterName, string $pattern)
    {
        $this->globalParameterConditions[$parameterName] = $pattern;

        return $this;
    }

    /**
     * Defines the supplied parameter name to be globally associated with the pattern
     *
     * @param string[] $parameterPatternMap
     *
     * @return $this
     */
    public function addGlobalParameters(array $parameterPatternMap)
    {
        $this->globalParameterConditions += $parameterPatternMap;

        return $this;
    }

    /**
     * Removes the global pattern associated with the supplied parameter name
     *
     * @param string $name
     */
    public function removeGlobalParameter(string $name)
    {
        unset($this->globalParameterConditions[$name]);
    }

    /**
     * Get all global parameters for all routes.
     *
     * @return array
     */
    public function getGlobalParameters(): array
    {
        return $this->globalParameterConditions;
    }

    /**
     * Dispatch router for HTTP request.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request The current HTTP request object
     *
     * @return array
     */
    public function dispatch(ServerRequestInterface $request)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(): array
    {
        return $this->allRoutes;
    }

    /**
     * Add a route to the underlying route collection.
     *
     * @param array|string               $methods
     * @param string                     $uri
     * @param \Closure|array|string|null $action
     *
     * @return \Viserio\Contracts\Routing\Route
     */
    protected function addRoute($methods, string $uri, $action): RouteContract
    {
        $route = $this->createRoute($methods, $uri, $action);

        $domainAndUri = $route->getDomain() . $route->getUri();

        foreach ($route->getMethods() as $method) {
            $this->routes[$method][$domainAndUri] = $route;
        }

        return $this->allRoutes[$method . $domainAndUri] = $route;
    }

    /**
     * Create a new route instance.
     *
     * @param array|string $methods
     * @param string       $uri
     * @param mixed        $action
     *
     * @return \Viserio\Contracts\Routing\Route
     */
    protected function createRoute($methods, string $uri, $action): RouteContract
    {
        $route = $this->newRoute(
            $methods,
            $this->prefix($uri),
            $action,
            $this->getGlobalParameters()
        );

        return $route;
    }

    /**
     * Create a new Route object.
     *
     * @param array|string $methods
     * @param string       $uri
     * @param mixed        $action
     *
     * @return \Viserio\Contracts\Routing\Route
     */
    protected function newRoute($methods, string $uri, $action): Route
    {
        return (new Route($methods, $uri, $action))
            ->setRouter($this)
            ->setContainer($this->container);
    }

    /**
     * Prefix the given URI with the last prefix.
     *
     * @param string $uri
     *
     * @return string
     */
    protected function prefix($uri)
    {
        return trim('/' . trim($uri, '/'), '/') ?: '/';
    }
}
