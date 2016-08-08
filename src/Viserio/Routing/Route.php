<?php
declare(strict_types=1);
namespace Viserio\Routing;

use LogicException;
use Narrowspark\Arr\StaticArr as Arr;
use RapidRoute\RouteSegments\ParameterSegment;
use UnexpectedValueException;
use Viserio\Contracts\{
    Container\Traits\ContainerAwareTrait,
    Routing\Route as RouteContract,
    Routing\Router as RouterContract
};
use Viserio\Support\{
    Invoker,
    Str
};

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
     * @var string|string[]
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
     * The default values for the route.
     *
     * @var array
     */
    protected $defaults = [];

    /**
     * The regular expression requirements.
     *
     * @var array
     */
    protected $wheres = [];

    /**
     * The array of matched parameters.
     *
     * @var array|null
     */
    protected $parameters;

    /**
     * The parameter names for the route.
     *
     * @var array|null
     */
    protected $parameterNames;

    /**
     * The router instance used by the route.
     *
     * @var \Viserio\Contracts\Routing\Router
     */
    protected $router;

    /**
     * Invoker instance.
     *
     * @var \Viserio\Support\Invoker
     */
    protected $invoker;

     /**
     * Create a new Route instance.
     *
     * @param array|string        $methods
     * @param string              $uri
     * @param \Closure|array|null $action
     */
    public function __construct($methods, $uri, $action)
    {
        $this->uri = $uri;
        $this->methods = (array) $methods;
        $this->action = $this->parseAction($action);

        if (in_array('GET', $this->methods) && ! in_array('HEAD', $this->methods)) {
            $this->methods[] = 'HEAD';
        }

        if (isset($this->action['prefix'])) {
            $this->prefix($this->action['prefix']);
        }
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
    public function setUri(string $uri): RouteContract
    {
        $this->uri = $uri;

        return $this;
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
    public function httpOnly(): bool
    {
        return in_array('http', $this->action, true);
    }

    /**
     * {@inheritdoc}
     */
    public function httpsOnly(): bool
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
    public function prefix(string $prefix): RouteContract
    {
        $uri = rtrim($prefix, '/').'/'.ltrim($this->uri, '/');

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
        $this->parameters();

        $this->parameters[$name] = $value;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getParameter(string $name, $default = null)
    {
        return Arr::get($this->parameters(), $name, $default);
    }

    /**
     * {@inheritdoc}
     */
    public function hasParameter(string $name): bool
    {
        return Arr::has($this->parameters(), $name);
    }

    /**
     * {@inheritdoc}
     */
    public function getParameters(): array
    {
        if (isset($this->parameters)) {
            return $this->parameters;
        }

        throw new LogicException('Route is not bound.');
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
        $this->parameters();

        unset($this->parameters[$name]);
    }

    /**
     * {@inheritdoc}
     */
    public function isStatic(): bool
    {
        foreach($this->parameters as $parameter) {
            if ($parameter instanceof ParameterSegment) {
                return false;
            }
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function run()
    {
        $this->initInvoker();

        return $this->invoker->call(
            $this->action['uses'],
            array_values($this->getParameters())
        );
    }

    /**
     * {@inheritdoc}
     */
    public function setRouter(RouterContract $router): RouteContract
    {
        $this->router = $router;

        return $this;
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
        return $this->parameter($key);
    }

    /**
     * Set configured invoker.
     *
     * @return \Viserio\Support\Invoker;
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

    /**
     * Parse the route action into a standard array.
     *
     * @param callable|array|null $action
     *
     * @return array
     *
     * @throws \UnexpectedValueException
     */
    protected function parseAction($action): array
    {
        // If no action is passed in right away, we assume the user will make use of
        // fluent routing. In that case, we set a default closure, to be executed
        // if the user never explicitly sets an action to handle the given uri.
        if (is_null($action)) {
            return ['uses' => function () {
                throw new LogicException("Route for [{$this->uri}] has no action.");
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
            $action['uses'] = Arr::first($action, function ($value, $key) {
                return is_callable($value) && is_numeric($key);
            });
        }

        if (is_string($action['uses']) && ! Str::contains($action['uses'], '::')) {
            if (! method_exists($action, '__invoke')) {
                throw new UnexpectedValueException(sprintf(
                    'Invalid route action: [%s]',
                    $action
                ));
            }

            $action['uses'] = $action.'::__invoke';
        }

        return $action;
    }
}
