<?php
declare(strict_types=1);
namespace Viserio\Component\Routing;

use Closure;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Spatie\Macroable\Macroable;
use Viserio\Component\Contract\Container\Traits\ContainerAwareTrait;
use Viserio\Component\Contract\Routing\Dispatcher as DispatcherContract;
use Viserio\Component\Contract\Routing\PendingResourceRegistration as PendingResourceRegistrationContract;
use Viserio\Component\Contract\Routing\Route as RouteContract;
use Viserio\Component\Contract\Routing\RouteCollection as RouteCollectionContract;
use Viserio\Component\Contract\Routing\Router as RouterContract;
use Viserio\Component\Routing\Route\Collection as RouteCollection;
use Viserio\Component\Routing\Route\Group as RouteGroup;
use Viserio\Component\Support\Traits\InvokerAwareTrait;

class Router implements RouterContract
{
    use ContainerAwareTrait;
    use InvokerAwareTrait;
    use Macroable {
        __call as macroCall;
    }

    /**
     * The route collection instance.
     *
     * @var \Viserio\Component\Routing\Route\Collection
     */
    protected $routes;

    /**
     * The dispatcher instance.
     *
     * @var \Viserio\Component\Contract\Routing\Dispatcher
     */
    protected $dispatcher;

    /**
     * The globally available parameter patterns.
     *
     * @var string[]
     */
    protected $globalParameterConditions = [];

    /**
     * The route group attribute stack.
     *
     * @var array
     */
    protected $groupStack = [];

    /**
     * The globally available parameter patterns.
     *
     * @var array
     */
    protected $patterns = [];

    /**
     * Create a new Router instance.
     *
     * @param \Viserio\Component\Contract\Routing\Dispatcher $dispatcher
     */
    public function __construct(DispatcherContract $dispatcher)
    {
        $this->dispatcher = $dispatcher;
        $this->routes     = new RouteCollection();
    }

    /**
     * Dynamically handle calls into the router instance.
     *
     * @param string $method
     * @param array  $parameters
     *
     * @throws \BadMethodCallException
     *
     * @return mixed
     */
    public function __call(string $method, array $parameters)
    {
        return $this->macroCall($method, $parameters);
    }

    /**
     * {@inheritdoc}
     */
    public function getRoutes(): RouteCollectionContract
    {
        return $this->routes;
    }

    /**
     * {@inheritdoc}
     */
    public function getDispatcher(): DispatcherContract
    {
        return $this->dispatcher;
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
    public function hasGroupStack(): bool
    {
        return \count($this->groupStack) !== 0;
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
    public function get(string $uri, $action = null): RouteContract
    {
        return $this->addRoute([self::METHOD_GET, self::METHOD_HEAD], $uri, $action);
    }

    /**
     * {@inheritdoc}
     */
    public function post(string $uri, $action = null): RouteContract
    {
        return $this->addRoute(self::METHOD_POST, $uri, $action);
    }

    /**
     * {@inheritdoc}
     */
    public function put(string $uri, $action = null): RouteContract
    {
        return $this->addRoute(self::METHOD_PUT, $uri, $action);
    }

    /**
     * {@inheritdoc}
     */
    public function patch(string $uri, $action = null): RouteContract
    {
        return $this->addRoute(self::METHOD_PATCH, $uri, $action);
    }

    /**
     * {@inheritdoc}
     */
    public function head(string $uri, $action = null): RouteContract
    {
        return $this->addRoute(self::METHOD_HEAD, $uri, $action);
    }

    /**
     * {@inheritdoc}
     */
    public function delete(string $uri, $action = null): RouteContract
    {
        return $this->addRoute(self::METHOD_DELETE, $uri, $action);
    }

    /**
     * {@inheritdoc}
     */
    public function options(string $uri, $action = null): RouteContract
    {
        return $this->addRoute(self::METHOD_OPTIONS, $uri, $action);
    }

    /**
     * {@inheritdoc}
     */
    public function any(string $uri, $action = null): RouteContract
    {
        return $this->addRoute(
            [
                self::METHOD_HEAD,
                self::METHOD_GET,
                self::METHOD_POST,
                self::METHOD_PUT,
                self::METHOD_PATCH,
                self::METHOD_DELETE,
                self::METHOD_PURGE,
                self::METHOD_OPTIONS,
                self::METHOD_TRACE,
                self::METHOD_CONNECT,
                self::METHOD_TRACE,
                self::METHOD_LINK,
                self::METHOD_UNLINK,
            ],
            $uri,
            $action
        );
    }

    /**
     * {@inheritdoc}
     */
    public function match($methods, string $uri, $action = null): RouteContract
    {
        return $this->addRoute(\array_map('strtoupper', (array) $methods), $uri, $action);
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
    public function addParameter(string $parameterName, string $expression): RouterContract
    {
        $this->globalParameterConditions[$parameterName] = $expression;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function removeParameter(string $name): void
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
    public function resources(array $resources): void
    {
        foreach ($resources as $name => $controller) {
            $this->resource($name, $controller);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function resource(string $name, string $controller, array $options = []): PendingResourceRegistrationContract
    {
        return new PendingResourceRegistration(
            new ResourceRegistrar($this),
            $name,
            $controller,
            $options
        );
    }

    /**
     * {@inheritdoc}
     */
    public function group(array $attributes, $routes): void
    {
        $this->updateGroupStack($attributes);

        $router = $this;

        if ($routes instanceof Closure) {
            $routes($router);
        } else {
            require $routes;
        }

        \array_pop($this->groupStack);
    }

    /**
     * {@inheritdoc}
     */
    public function mergeWithLastGroup(array $new): array
    {
        return RouteGroup::merge($new, \end($this->groupStack));
    }

    /**
     * {@inheritdoc}
     */
    public function getLastGroupSuffix(): string
    {
        if ($this->hasGroupStack()) {
            $last = \end($this->groupStack);

            return $last['suffix'] ?? '';
        }

        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function getLastGroupPrefix(): string
    {
        if ($this->hasGroupStack()) {
            $last = \end($this->groupStack);

            return $last['prefix'] ?? '';
        }

        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function getCurrentRoute(): ?RouteContract
    {
        return $this->dispatcher->getCurrentRoute();
    }

    /**
     * {@inheritdoc}
     */
    public function dispatch(ServerRequestInterface $request): ResponseInterface
    {
        $dispatcher = $this->dispatcher;

        if ($this->container !== null && \method_exists($dispatcher, 'setContainer')) {
            $dispatcher->setContainer($this->container);
        }

        return $dispatcher->handle($this->routes, $request);
    }

    /**
     * Add a route to the underlying route collection.
     *
     * @param array|string               $methods
     * @param string                     $uri
     * @param null|array|\Closure|string $action
     *
     * @return \Viserio\Component\Contract\Routing\Route
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
     * @return \Viserio\Component\Contract\Routing\Route
     */
    protected function createRoute($methods, string $uri, $action): RouteContract
    {
        // If the route is routing to a controller we will parse the route action into
        // an acceptable array format before registering it and creating this route
        // instance itself. We need to build the Closure that will call this out.
        if ($this->actionReferencesController($action)) {
            $action = $this->convertToControllerAction($action);
        }

        $route = new Route($methods, $this->prefix($this->suffix($uri)), $action);

        if ($this->container !== null) {
            $route->setContainer($this->container);
        }

        $route->setInvoker($this->getInvoker());

        if ($this->hasGroupStack()) {
            $this->mergeGroupAttributesIntoRoute($route);
        }

        $this->addWhereClausesToRoute($route);

        foreach ($this->globalParameterConditions as $key => $value) {
            $route->addParameter($key, $value);
        }

        return $route;
    }

    /**
     * Add the necessary where clauses to the route based on its initial registration.
     *
     * @param \Viserio\Component\Contract\Routing\Route $route
     *
     * @return void
     */
    protected function addWhereClausesToRoute(RouteContract $route): void
    {
        $where   = $route->getAction()['where'] ?? [];
        $pattern = \array_merge($this->patterns, $where);

        foreach ($pattern as $name => $value) {
            $route->where($name, $value);
        }
    }

    /**
     * Merge the group stack with the controller action.
     *
     * @param \Viserio\Component\Contract\Routing\Route $route
     *
     * @return void
     */
    protected function mergeGroupAttributesIntoRoute(RouteContract $route): void
    {
        $action = $this->mergeWithLastGroup($route->getAction());

        $route->setAction($action);
    }

    /**
     * Determine if the action is routing to a controller.
     *
     * @param array|\Closure|string $action
     *
     * @return bool
     */
    protected function actionReferencesController($action): bool
    {
        if ($action instanceof Closure) {
            return false;
        }

        return \is_string($action) || (isset($action['uses']) && \is_string($action['uses']));
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
        if (\is_string($action)) {
            $action = ['uses' => $action];
        }

        if ($this->hasGroupStack()) {
            $action['uses'] = $this->prependGroupNamespace($action['uses']);
        }

        $action['controller'] = $action['uses'];

        return $action;
    }

    /**
     * Prepend the last group uses onto the use clause.
     *
     * @param string $uses
     *
     * @return string
     */
    protected function prependGroupNamespace(string $uses): string
    {
        $group = \end($this->groupStack);

        return isset($group['namespace']) && \mb_strpos($uses, '\\') !== 0 ?
            $group['namespace'] . '\\' . $uses :
            $uses;
    }

    /**
     * Prefix the given URI with the last prefix.
     *
     * @param string $uri
     *
     * @return string
     */
    protected function prefix(string $uri): string
    {
        $trimmed = \trim($this->getLastGroupPrefix(), '/') . '/' . \trim($uri, '/');

        if (! $trimmed) {
            return '/';
        }

        if (\mb_strpos($trimmed, '/') === 0) {
            return $trimmed;
        }

        return '/' . $trimmed;
    }

    /**
     * Suffix the given URI with the last suffix.
     *
     * @param string $uri
     *
     * @return string
     */
    protected function suffix(string $uri): string
    {
        return \trim($uri) . \trim($this->getLastGroupSuffix());
    }

    /**
     * Update the group stack with the given attributes.
     *
     * @param array $attributes
     *
     * @return void
     */
    protected function updateGroupStack(array $attributes): void
    {
        if ($this->hasGroupStack()) {
            $attributes = RouteGroup::merge($attributes, \end($this->groupStack));
        }

        $this->groupStack[] = $attributes;
    }
}
