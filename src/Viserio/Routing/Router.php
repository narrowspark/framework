<?php
declare(strict_types=1);
namespace Viserio\Routing;

use Closure;
use Interop\Container\ContainerInterface;
use Interop\Http\Middleware\MiddlewareInterface;
use Interop\Http\Middleware\ServerMiddlewareInterface;
use LogicException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Contracts\Container\Traits\ContainerAwareTrait;
use Viserio\Contracts\Events\Traits\EventsAwareTrait;
use Viserio\Contracts\Routing\Route as RouteContract;
use Viserio\Contracts\Routing\RouteCollection as RouteCollectionContract;
use Viserio\Contracts\Routing\Router as RouterContract;
use Viserio\Middleware\Dispatcher as MiddlewareDispatcher;
use Viserio\Routing\Traits\MiddlewareAwareTrait;
use Viserio\Support\Invoker;

class Router implements RouterContract
{
    use ContainerAwareTrait;
    use EventsAwareTrait;
    use MiddlewareAwareTrait;

    /**
     * The route collection instance.
     *
     * @var \Viserio\Routing\RouteCollection
     */
    protected $routes;

    /**
     * Invoker instance.
     *
     * @var \Viserio\Support\Invoker
     */
    protected $invoker;

    /**
     * The currently dispatched route instance.
     *
     * @var \Viserio\Contracts\Routing\Route
     */
    protected $current;

    /**
     * The route group attribute stack.
     *
     * @var array
     */
    protected $groupStack = [];

    /**
     * The globally available parameter patterns.
     *
     * @var string[]
     */
    protected $globalParameterConditions = [];

    /**
     * The globally available parameter patterns.
     *
     * @var array
     */
    protected $patterns = [];

    /**
     * Flag for refresh the cache file on every call.
     *
     * @var bool
     */
    protected $refreshCache = false;

    /**
     * Path to the cached router file.
     *
     * @var string
     */
    protected $path;

    /**
     * Create a new Router instance.
     *
     * @param \Interop\Container\ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->routes = new RouteCollection();
    }

    /**
     * Set the cache path for compiled routes.
     *
     * @param string $path
     *
     * @return \Viserio\Contracts\Routing\Router
     */
    public function setCachePath(string $path): RouterContract
    {
        $this->path = $path;

        return $this;
    }

    /**
     * Get the cache path for the compiled routes.
     *
     * @return string
     */
    public function getCachePath(): string
    {
        return $this->path;
    }

    /**
     * Refresh cache file on development.
     *
     * @param bool $refreshCache
     *
     * @return \Viserio\Contracts\Routing\Router
     */
    public function refreshCache(bool $refreshCache): RouterContract
    {
        $this->refreshCache = $refreshCache;

        return $this;
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
    public function head(string $uri, $action = null): RouteContract
    {
        return $this->addRoute('HEAD', $uri, $action);
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
        return $this->addRoute(self::HTTP_METHOD_VARS, $uri, $action);
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
    public function pattern(string $key, string $pattern): RouterContract
    {
        $this->patterns[$key] = $pattern;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function patterns(array $patterns): RouterContract
    {
        foreach ($patterns as $key => $pattern) {
            $this->pattern($key, $pattern);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function getPatterns(): array
    {
        return $this->patterns;
    }

    /**
     * {@inheritdoc}
     */
    public function setParameter(string $parameterName, string $expression): RouterContract
    {
        $this->globalParameterConditions[$parameterName] = $expression;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addParameters(array $parameterPatternMap): RouterContract
    {
        $this->globalParameterConditions += $parameterPatternMap;

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function removeParameter(string $name)
    {
        unset($this->globalParameterConditions[$name]);
    }

    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function getParameters(): array
    {
        return $this->globalParameterConditions;
    }

    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function getCurrentRoute()
    {
        return $this->current;
    }

    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function getRoutes(): RouteCollectionContract
    {
        return $this->routes;
    }

    /**
     * {@inheritdoc}
     */
    public function dispatch(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $middlewareDispatcher = new MiddlewareDispatcher($response);

        if (isset($this->middlewares['with'])) {
            foreach ($this->middlewares['with'] as $middleware) {
                $middlewareDispatcher->withMiddleware($middleware);
            }
        }

        if (isset($this->middlewares['without'])) {
            foreach ($this->middlewares['without'] as $middleware) {
                $middlewareDispatcher->withoutMiddleware($middleware);
            }
        }

        $dispatcher = new Dispatcher(
            $this->path,
            $this->routes,
            $middlewareDispatcher,
            $this->refreshCache,
            $this->globalParameterConditions
        );

        if ($this->events !== null) {
            $dispatcher->setEventsDispatcher($this->events);

            $this->events = $dispatcher->getEventsDispatcher();
        }

        $middlewareDispatcher = $dispatcher->handle($request);

        $this->current = $dispatcher->getCurrentRoute();

        return $middlewareDispatcher->process($request);
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
        return $this->routes->add($this->createRoute($methods, $uri, $action));
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
        // If the route is routing to a controller we will parse the route action into
        // an acceptable array format before registering it and creating this route
        // instance itself. We need to build the Closure that will call this out.
        if ($this->actionReferencesController($action)) {
            $action = $this->convertToControllerAction($action);
        }

        $route = new Route($methods, $this->prefix($uri), $action);
        $route->setContainer($this->getContainer());
        $route->setInvoker($this->getInvoker());

        $this->addWhereClausesToRoute($route);

        return $route;
    }

    /**
     * Add the necessary where clauses to the route based on its initial registration.
     *
     * @param \Viserio\Contracts\Routing\Route $route
     *
     * @return \Viserio\Contracts\Routing\Route
     */
    protected function addWhereClausesToRoute(RouteContract $route): RouteContract
    {
        $where = $route->getAction()['where'] ?? [];
        $patern = array_merge($this->patterns, $where);

        foreach ($patern as $name => $value) {
            $route->where($name, $value);
        }

        return $route;
    }

    /**
     * Determine if the action is routing to a controller.
     *
     * @param string|array|\Closure $action
     *
     * @return bool
     */
    protected function actionReferencesController($action): bool
    {
        if ($action instanceof Closure) {
            return false;
        }

        return is_string($action) || (isset($action['uses']) && is_string($action['uses']));
    }

    /**
     * Add a controller based route action to the action array.
     *
     * @param array|string $action
     *
     * @return array
     */
    protected function convertToControllerAction($action): array
    {
        if (is_string($action)) {
            $action = ['uses' => $action];
        }

        // Here we will set this controller name on the action array just so we always
        // have a copy of it for reference if we need it. This can be used while we
        // search for a controller name or do some other type of fetch operation.
        $action['controller'] = $action['uses'];

        return $action;
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
        return '/' . trim($uri, '/');
    }

    /**
     * Set configured invoker.
     *
     * @return \Viserio\Support\Invoker
     */
    protected function getInvoker(): Invoker
    {
        if ($this->invoker === null) {
            $this->invoker = (new Invoker())
                ->injectByTypeHint(true)
                ->injectByParameterName(true)
                ->setContainer($this->getContainer());
        }

        return $this->invoker;
    }
}
