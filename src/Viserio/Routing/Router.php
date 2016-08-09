<?php
declare(strict_types=1);
namespace Viserio\Routing;

use Closure;
use Interop\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use RapidRoute\{
    InvalidRoutePatternException,
    RouteParser
};
use Viserio\Contracts\{
    Container\Traits\ContainerAwareTrait,
    Events\Traits\EventsAwareTrait,
    Routing\Route as RouteContract,
    Routing\Router as RouterContract,
    Routing\RouteGroup as RouteGroupContract
};

class Router implements RouterContract
{
    use ContainerAwareTrait;
    use EventsAwareTrait;

    /**
     * The route collection instance.
     *
     * @var \Viserio\Routing\RouteCollection
     */
    protected $routes;

    /**
     * Create a new Router instance.
     *
     * @param \Interop\Container\ContainerInterface $container
     * @param \RapidRoute\RouteParser               $parser
     */
    public function __construct(ContainerInterface $container, RouteParser $parser)
    {
        $this->routes = new RouteCollection;
        $this->container = $container;
        $this->parser = $parser;
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
        return $this->routes->addRoute($this->createRoute($methods, $uri, $action));
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
        list($patternString, $conditions) = $this->parseRoutingPattern($uri);

        $pattern = $this->parser->parse(
            $patternString,
            $conditions
        );

        $route = $this->newRoute(
            $methods,
            $this->prefix($uri),
            $action
        );

        foreach ($pattern as $key => $value) {
            $route->setParameter($key, $value);
        }

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

    protected function parseRoutingPattern($pattern)
    {
        if(is_string($pattern)) {
            return [$pattern, []];
        }

        if(is_array($pattern)) {
            if(!isset($pattern[0]) || !is_string($pattern[0])) {
                throw new InvalidRoutePatternException(sprintf(
                    'Cannot add route: route pattern array must have the first element containing the pattern string, %s given',
                    isset($pattern[0]) ? gettype($pattern[0]) : 'none'
                ));
            }

            $patternString = $pattern[0];
            $parameterConditions = $pattern;

            unset($parameterConditions[0]);

            return [$patternString, $parameterConditions];
        }

        throw new InvalidRoutePatternException(sprintf(
            'Cannot add route: route pattern must be a pattern string or array, %s given',
            gettype($pattern)
        ));
    }

    /**
     * Prefix the given URI with the last prefix.
     *
     * @param  string  $uri
     * @return string
     */
    protected function prefix($uri)
    {
        return trim('/'.trim($uri, '/'), '/') ?: '/';
    }
}
