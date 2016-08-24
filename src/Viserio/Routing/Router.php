<?php
declare(strict_types=1);
namespace Viserio\Routing;

use Closure;
use Interop\Container\ContainerInterface;
use Narrowspark\Arr\StaticArr as Arr;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Contracts\Container\Traits\ContainerAwareTrait;
use Viserio\Contracts\Middleware\Middleware as MiddlewareContract;
use Viserio\Contracts\Routing\Route as RouteContract;
use Viserio\Contracts\Routing\Router as RouterContract;
use Viserio\Middleware\Dispatcher as MiddlewareDispatcher;

class Router implements RouterContract
{
    use ContainerAwareTrait;

    /**
     * The route collection instance.
     *
     * @var \Viserio\Routing\RouteCollection
     */
    protected $routes;

    /**
     * The route group attribute stack.
     *
     * @var array
     */
    protected $groupStack = [];

    /**
     * All of the middlewares.
     *
     * @var array
     */
    protected $withMiddlewares = [];

    /**
     * All to remove middlewares.
     *
     * @var array
     */
    protected $withoutMiddlewares = [];

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
     * Flag for development mode.
     *
     * @var bool
     */
    protected $isDevelopMode = true;

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
    }

    /**
     * Route collection is in develop mode.
     *
     * @param bool $isDev
     */
    public function isDevelopMode(bool $isDev)
    {
        $this->isDevelopMode = $isDev;
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
     * Merge the given array with the last group stack.
     *
     * @param array $new
     *
     * @return array
     */
    public function mergeWithLastGroup($new)
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
     * Set a global where pattern on all routes.
     *
     * @param string $key
     * @param string $pattern
     */
    public function pattern(string $key, string $pattern)
    {
        $this->patterns[$key] = $pattern;
    }

    /**
     * Set a group of global where patterns on all routes.
     *
     * @param array $patterns
     */
    public function patterns(array $patterns)
    {
        foreach ($patterns as $key => $pattern) {
            $this->pattern($key, $pattern);
        }
    }

    /**
     * Get the global "where" patterns.
     *
     * @return array
     */
    public function getPatterns(): array
    {
        return $this->patterns;
    }

    /**
     * Defines the supplied parameter name to be globally associated with the expression.
     *
     * @param string $parameterName
     * @param string $expression
     *
     * @return $this
     */
    public function setParameter(string $parameterName, string $expression)
    {
        $this->globalParameterConditions[$parameterName] = $expression;

        return $this;
    }

    /**
     * Defines the supplied parameter name to be globally associated with the expression.
     *
     * @param string[] $parameterPatternMap
     *
     * @return $this
     */
    public function addParameters(array $parameterPatternMap)
    {
        $this->globalParameterConditions += $parameterPatternMap;

        return $this;
    }

    /**
     * Removes the global expression associated with the supplied parameter name.
     *
     * @param string $name
     */
    public function removeParameter(string $name)
    {
        unset($this->globalParameterConditions[$name]);
    }

    /**
     * Get all global parameters for all routes.
     *
     * @return array
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
        $this->withMiddlewares[] = $middleware;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function withoutMiddleware(MiddlewareContract $middleware): RouterContract
    {
        $this->withoutMiddlewares[] = $middleware;

        return $this;
    }

    /**
     * Dispatch router for HTTP request.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface      $response
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function dispatch(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $dispatcher = new Dispatcher(
            $this->path,
            $this->routes,
            new MiddlewareDispatcher($response),
            $this->isDevelopMode
        );
        $middlewareDispatcher = $dispatcher->handle($request);

        foreach ($this->withMiddlewares as $withMiddleware) {
            $middlewareDispatcher->withMiddleware($withMiddleware);
        }

        foreach ($this->withoutMiddlewares as $withoutMiddleware) {
            $middlewareDispatcher->withoutMiddleware($withoutMiddleware);
        }

        return $middlewareDispatcher->process($request);
    }

    /**
     * Get the underlying route collection.
     *
     * @return \Viserio\Routing\RouteCollection
     */
    public function getRoutes()
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
}
