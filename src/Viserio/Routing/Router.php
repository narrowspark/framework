<?php
declare(strict_types=1);
namespace Viserio\Routing;

use Closure;
use Interop\Container\ContainerInterface;
use Narrowspark\Arr\StaticArr as Arr;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Contracts\Container\Traits\ContainerAwareTrait;
use Viserio\Contracts\Events\Traits\EventsAwareTrait;
use Viserio\Contracts\Middleware\Middleware as MiddlewareContract;
use Viserio\Contracts\Routing\Route as RouteContract;
use Viserio\Contracts\Routing\RouteCollection as RouteCollectionContract;
use Viserio\Contracts\Routing\Router as RouterContract;
use Viserio\Middleware\Dispatcher as MiddlewareDispatcher;
use Viserio\Support\Invoker;

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
     * All middlewares.
     *
     * @var array
     */
    protected $middlewares = [];

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
     * @param string                                $path
     * @param \Interop\Container\ContainerInterface $container
     */
    public function __construct(string $path, ContainerInterface $container)
    {
        $this->path = $path;
        $this->container = $container;
        $this->routes = new RouteCollection();

        $this->initInvoker();
    }

    /**
     * Refresh cache file on development.
     *
     * @param bool $refreshCache
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
    public function group(array $attributes, Closure $callback)
    {
        if (! empty($this->groupStack)) {
            $attributes = $this->mergeGroup($attributes, end($this->groupStack));
        }

        $this->groupStack[] = $attributes;

        // Once we have updated the group stack, we will execute the user Closure and
        // merge in the groups attributes when the route is created. After we have
        // run the callback, we will pop the attributes off of this group stack.
        call_user_func($callback, $this);

        array_pop($this->groupStack);
    }

    /**
     * {@inheritdoc}
     */
    public function mergeWithLastGroup(array $new): array
    {
        return $this->mergeGroup($new, end($this->groupStack));
    }

    /**
     * {@inheritdoc}
     */
    public function hasGroupStack(): bool
    {
        return ! empty($this->groupStack);
    }

    /**
     * {@inheritdoc}
     */
    public function getGroupStack(): array
    {
        return $this->groupStack;
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
     */
    public function removeParameter(string $name)
    {
        unset($this->globalParameterConditions[$name]);
    }

    /**
     * {@inheritdoc}
     */
    public function getParameters(): array
    {
        return $this->globalParameterConditions;
    }

    /**
     * {@inheritdoc}
     */
    public function withMiddleware(MiddlewareContract $middleware): RouterContract
    {
        $this->middlewares['with'][] = $middleware;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function withoutMiddleware(MiddlewareContract $middleware): RouterContract
    {
        $this->middlewares['without'][] = $middleware;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getMiddlewares(): array
    {
        return $this->middlewares;
    }

    /**
     * Get the currently dispatched route instance.
     *
     * @return \Viserio\Contracts\Routing\Route|null
     */
    public function getCurrentRoute()
    {
        return $this->current;
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
            $this->refreshCache
        );

        if ($this->events !== null) {
            $dispatcher->setEventsDispatcher($this->events);

            $this->events = $dispatcher->getEventsDispatcher();
        }

        $middlewareDispatcher = $dispatcher->handle($request);
        $this->current = $this->getCurrentRoute();

        return $middlewareDispatcher->process($request);
    }

    /**
     * {@inheritdoc}
     */
    public function getRoutes(): RouteCollectionContract
    {
        return $this->routes;
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
        $route->setContainer($this->container);
        $route->setInvoker($this->invoker);

        // If we have groups that need to be merged, we will merge them now after this
        // route has already been created and is ready to go. After we're done with
        // the merge we will be ready to return the route back out to the caller.
        if ($this->hasGroupStack()) {
            $action = $this->mergeWithLastGroup($route->getAction());

            $route->setAction($action);
        }

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

        // Here we'll merge any group "uses" statement if necessary so that the action
        // has the proper clause for this property. Then we can simply set the name
        // of the controller on the action and return the action array for usage.
        if (! empty($this->groupStack)) {
            $action['uses'] = $this->prependGroupUses($action['uses']);
        }

        // Here we will set this controller name on the action array just so we always
        // have a copy of it for reference if we need it. This can be used while we
        // search for a controller name or do some other type of fetch operation.
        $action['controller'] = $action['uses'];

        return $action;
    }

    /**
     * Merge the given group attributes.
     *
     * @param array $new
     * @param array $old
     *
     * @return array
     */
    protected function mergeGroup(array $new, array $old): array
    {
        $new['namespace'] = $this->formatUsesPrefix($new, $old);
        $new['prefix'] = $this->formatGroupPrefix($new, $old);

        if (isset($new['domain'])) {
            unset($old['domain']);
        }

        $new['where'] = array_merge($old['where'] ?? [], $new['where'] ?? []);

        if (isset($old['as'])) {
            $new['as'] = $old['as'] . ($new['as'] ?? '');
        }

        return array_merge_recursive(Arr::except($old, ['namespace', 'prefix', 'where', 'as']), $new);
    }

    /**
     * Format the uses prefix for the new group attributes.
     *
     * @param array $new
     * @param array $old
     *
     * @return string|null
     */
    protected function formatUsesPrefix(array $new, array $old)
    {
        if (isset($new['namespace'])) {
            return isset($old['namespace'])
                    ? trim($old['namespace'], '\\') . '\\' . trim($new['namespace'], '\\')
                    : trim($new['namespace'], '\\');
        }

        return $old['namespace'] ?? null;
    }

    /**
     * Format the prefix for the new group attributes.
     *
     * @param array $new
     * @param array $old
     *
     * @return string|null
     */
    protected function formatGroupPrefix(array $new, array $old)
    {
        $oldPrefix = $old['prefix'] ?? null;

        if (isset($new['prefix'])) {
            return trim($oldPrefix, '/') . '/' . trim($new['prefix'], '/');
        }

        return $oldPrefix;
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
        return '/' . trim(trim($this->getLastGroupPrefix(), '/') . '/' . trim($uri, '/'), '/');
    }

    /**
     * Get the prefix from the last group on the stack.
     *
     * @return string
     */
    protected function getLastGroupPrefix(): string
    {
        if (! empty($this->groupStack)) {
            $last = end($this->groupStack);

            return isset($last['prefix']) ? $last['prefix'] : '';
        }

        return '';
    }

    /**
     * Prepend the last group uses onto the use clause.
     *
     * @param string $uses
     *
     * @return string
     */
    protected function prependGroupUses(string $uses): string
    {
        $group = end($this->groupStack);

        return isset($group['namespace']) && strpos($uses, '\\') !== 0 ? $group['namespace'] . '\\' . $uses : $uses;
    }

    /**
     * Set configured invoker.
     *
     * @return \Viserio\Support\Invoker
     */
    protected function initInvoker(): Invoker
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
