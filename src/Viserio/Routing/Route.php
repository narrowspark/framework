<?php
declare(strict_types=1);
namespace Viserio\Routing;

use LogicException;
use Narrowspark\Arr\StaticArr as Arr;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use UnexpectedValueException;
use Viserio\Contracts\Container\Traits\ContainerAwareTrait;
use Viserio\Contracts\Middleware\Middleware as MiddlewareContract;
use Viserio\Contracts\Routing\Route as RouteContract;
use Viserio\Support\Invoker;

class Route implements RouteContract
{
    use ContainerAwareTrait;

    /**
     * The URI pattern the route responds to.
     *
     * @var string
     */
    protected $uri;

    /**
     * The HTTP methods the route responds to.
     *
     * @var array
     */
    protected $httpMethods;

    /**
     * The route action array.
     *
     * @var \Closure|array
     */
    protected $action;

    /**
     * The controller instance.
     *
     * @var mixed
     */
    protected $controller;

    /**
     * The array of matched parameters.
     *
     * @var array
     */
    protected $parameters = [];

    /**
     * The regular expression requirements.
     *
     * @var array
     */
    protected $wheres = [];

    /**
     * All middlewares.
     *
     * @var array
     */
    protected $middleware = [
        'with' => [],
        'without' => [],
    ];

    /**
     * Invoker instance.
     *
     * @var \Viserio\Support\Invoker
     */
    protected $invoker;

    /**
     * Route identifier.
     *
     * @var string
     */
    protected $identifier;

    /**
     * Create a new Route instance.
     *
     * @param array|string        $methods
     * @param string              $uri
     * @param \Closure|array|null $action
     */
    public function __construct($methods, string $uri, $action)
    {
        $this->uri = $uri;
        // According to RFC methods are defined in uppercase (See RFC 7231)
        $this->httpMethods = array_map('strtoupper', (array) $methods);
        $this->action = $this->parseAction($action);

        if (in_array('GET', $this->httpMethods) && ! in_array('HEAD', $this->httpMethods)) {
            $this->httpMethods[] = 'HEAD';
        }

        if (isset($this->action['prefix'])) {
            $this->addPrefix($this->action['prefix']);
        }
    }

    /**
     * Dynamically access route parameters.
     *
     * @param string $key
     *
     * @return mixed
     */
    public function __get($key)
    {
        return $this->getParameter($key);
    }

    /**
     * Set the invoker instance.
     *
     * @param \Viserio\Support\Invoker $invoker
     */
    public function setInvoker(Invoker $invoker)
    {
        $this->invoker = $invoker;
    }

    /**
     * Get route identifier
     *
     * @return string
     */
    public function getIdentifier(): string
    {
        return implode($this->httpMethods, '|') . $this->getDomain() . $this->uri;
    }

    /**
     * {@inheritdoc}
     */
    public function getDomain()
    {
        return $this->action['domain'] ?? null;
    }

    /**
     * {@inheritdoc}
     */
    public function getUri(): string
    {
        return $this->uri;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->action['as'] ?? null;
    }

    /**
     * {@inheritdoc}
     */
    public function setName(string $name): RouteContract
    {
        $this->action['as'] = isset($this->action['as']) ? $this->action['as'] . $name : $name;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getMethods(): array
    {
        return $this->httpMethods;
    }

    /**
     * {@inheritdoc}
     */
    public function where($name, string $expression = null): RouteContract
    {
        foreach ($this->parseWhere($name, $expression) as $name => $expression) {
            $this->wheres[$name] = $expression;
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function withMiddleware(MiddlewareContract $middleware): RouteContract
    {
        $this->middleware['with'][] = $middleware;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function withoutMiddleware(MiddlewareContract $middleware): RouteContract
    {
        $this->middleware['without'][] = $middleware;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function gatherMiddleware(): array
    {
        // Merge middlewares from Action.
        $with = Arr::get($this->action, 'middleware.with', []);
        $without = Arr::get($this->action, 'middleware.without', []);

        $this->middleware = Arr::merge($this->middleware, $this->getControllerMiddleware());

        $this->middleware['with'] = array_merge(
            $this->middleware['with'],
            is_array($with) ? $with : [$with]
        );
        $this->middleware['without'] = array_merge(
            $this->middleware['without'],
            is_array($without) ? $without : [$without]
        );

        return $this->middleware;
    }

    /**
     * {@inheritdoc}
     */
    public function isHttpOnly(): bool
    {
        return in_array('http', $this->action, true);
    }

    /**
     * {@inheritdoc}
     */
    public function isHttpsOnly(): bool
    {
        return in_array('https', $this->action, true);
    }

    /**
     * {@inheritdoc}
     */
    public function getActionName(): string
    {
        return $this->action['controller'] ?? 'Closure';
    }

    /**
     * {@inheritdoc}
     */
    public function getAction(): array
    {
        return $this->action;
    }

    /**
     * {@inheritdoc}
     */
    public function setAction(array $action): RouteContract
    {
        $this->action = $action;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addPrefix(string $prefix): RouteContract
    {
        $uri = rtrim($prefix, '/') . '/' . ltrim($this->uri, '/');

        $this->uri = trim($uri, '/');

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getPrefix(): string
    {
        return $this->action['prefix'] ?? '';
    }

    /**
     * {@inheritdoc}
     */
    public function setParameter($name, $value): RouteContract
    {
        $this->parameters[$name] = $value;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getParameter(string $name, $default = null)
    {
        return Arr::get($this->parameters, $name, $default);
    }

    /**
     * {@inheritdoc}
     */
    public function hasParameter(string $name): bool
    {
        return Arr::has($this->parameters, $name);
    }

    /**
     * {@inheritdoc}
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * {@inheritdoc}
     */
    public function hasParameters(): bool
    {
        return isset($this->parameters);
    }

    /**
     * {@inheritdoc}
     */
    public function forgetParameter(string $name)
    {
        $this->parameters;

        unset($this->parameters[$name]);
    }

    /**
     * {@inheritdoc}
     */
    public function getSegments(): array
    {
        return (new RouteParser())->parse($this->uri, $this->wheres);
    }

    /**
     * {@inheritdoc}
     */
    public function run(ServerRequestInterface $request): ResponseInterface
    {
        if ($this->isControllerAction()) {
            return $this->getController()->{$this->getControllerMethod()}();
        }

        return $this->invoker->call(
            $this->action['uses'],
            [$request, $this->parameters]
        );
    }

    /**
     * Parse the route action into a standard array.
     *
     * @param callable|array|null $action
     *
     * @throws \UnexpectedValueException
     *
     * @return array
     */
    protected function parseAction($action): array
    {
        // If no action is passed in right away, we assume the user will make use of
        // fluent routing. In that case, we set a default closure, to be executed
        // if the user never explicitly sets an action to handle the given uri.
        if (is_null($action)) {
            return ['uses' => function () {
                throw new LogicException(sprintf('Route for [%s] has no action.', $this->uri));
            }];
        }

        // If the action is already a Closure instance, we will just set that instance
        // as the "uses" property.
        if (is_callable($action)) {
            return ['uses' => $action];
        }

        // If no "uses" property has been set, we will dig through the array to find a
        // Closure instance within this list. We will set the first Closure we come across.
        if (! isset($action['uses'])) {
            $action['uses'] = Arr::first($action, function ($key, $value) {
                return is_callable($value) && is_numeric($key);
            });
        }

        if (is_string($action['uses']) && strpos($action['uses'], '::') === false) {
            if (! method_exists($action, '__invoke')) {
                throw new UnexpectedValueException(sprintf(
                    'Invalid route action: [%s]',
                    $action['uses']
                ));
            }

            $action['uses'] = $action . '::__invoke';
        }

        return $action;
    }

    /**
     * Parse arguments to the where method into an array.
     *
     * @param array|string $name
     * @param string       $expression
     *
     * @return array
     */
    protected function parseWhere($name, string $expression): array
    {
        if (is_string($name)) {
            return [$name => $expression];
        }

        $arr = [];

        foreach ($name as $paramName) {
            $arr[$paramName] = $expression;
        }

        return $arr;
    }

    /**
     * Get the middleware for the route's controller.
     *
     * @return array
     */
    protected function getControllerMiddleware(): array
    {
        if (! $this->isControllerAction()) {
            return [];
        }

        $controller = $this->getController();

        if (method_exists($controller, 'gatherMiddleware')) {
            return $controller->gatherMiddleware();
        }

        return [];
    }

    /**
     * Checks whether the route's action is a controller.
     *
     * @return bool
     */
    protected function isControllerAction(): bool
    {
        return is_string($this->action['uses']);
    }

    /**
     * Get the controller instance for the route.
     *
     * @return mixed
     */
    protected function getController()
    {
        list($class) = explode('::', $this->action['uses']);

        if (! $this->controller) {
            $this->controller = $this->getContainer()->get($class);
        }

        return $this->controller;
    }

    /**
     * Get the controller method used for the route.
     *
     * @return string
     */
    protected function getControllerMethod(): string
    {
        return explode('::', $this->action['uses'])[1];
    }
}
