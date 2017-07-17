<?php
declare(strict_types=1);
namespace Viserio\Component\Routing;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Component\Contracts\Container\Factory as FactoryContract;
use Viserio\Component\Contracts\Container\Traits\ContainerAwareTrait;
use Viserio\Component\Contracts\Routing\Route as RouteContract;
use Viserio\Component\Routing\Route\Action as RouteAction;
use Viserio\Component\Routing\Route\Parser as RouteParser;
use Viserio\Component\Routing\Traits\MiddlewareAwareTrait;
use Viserio\Component\Support\Traits\InvokerAwareTrait;

class Route implements RouteContract
{
    use ContainerAwareTrait;
    use InvokerAwareTrait;
    use MiddlewareAwareTrait;

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
    protected $httpMethods = [];

    /**
     * The route action array.
     *
     * @var array
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
        $this->action      = RouteAction::parse($uri, $action);

        if (in_array('GET', $this->httpMethods) && ! in_array('HEAD', $this->httpMethods)) {
            $this->httpMethods[] = 'HEAD';
        }

        if (isset($this->action['prefix'])) {
            $this->addPrefix($this->action['prefix']);
        }

        if (isset($this->action['suffix'])) {
            $this->addSuffix($this->action['suffix']);
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
     * Get route identifier.
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
    public function getDomain(): ?string
    {
        if (isset($this->action['domain'])) {
            return str_replace(['http://', 'https://'], '', $this->action['domain']);
        }

        return null;
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
    public function getName(): ?string
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
    public function where($name, ?string $expression = null): RouteContract
    {
        foreach ($this->parseWhere($name, $expression) as $name => $expression) {
            $this->wheres[$name] = $expression;
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function gatherMiddleware(): array
    {
        if (isset($this->action['middlewares'])) {
            $this->withMiddleware($this->action['middlewares']);
        }

        return array_unique(
            array_merge(
                $this->middlewares,
                $this->getControllerMiddlewares()
            ),
            SORT_REGULAR
        );
    }

    /**
     * {@inheritdoc}
     */
    public function gatherDisabledMiddlewares(): array
    {
        if (isset($this->action['bypass'])) {
            $this->withoutMiddleware($this->action['bypass']);
        }

        return array_merge(
            $this->bypassedMiddlewares,
            $this->getControllerDisabledMiddlewares()
        );
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
    public function addSuffix(string $suffix): RouteContract
    {
        $uri = rtrim($this->uri) . ltrim($suffix);

        $this->uri = trim($uri);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getSuffix(): ?string
    {
        return $this->action['suffix'] ?? null;
    }

    /**
     * {@inheritdoc}
     */
    public function addParameter($name, $value): RouteContract
    {
        $this->parameters[$name] = $value;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getParameter(string $name, $default = null)
    {
        if (isset($this->parameters[$name])) {
            return $this->parameters[$name];
        }

        return $default;
    }

    /**
     * {@inheritdoc}
     */
    public function hasParameter(string $name): bool
    {
        return isset($this->parameters[$name]);
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
    public function forgetParameter(string $name): void
    {
        $this->parameters;

        unset($this->parameters[$name]);
    }

    /**
     * {@inheritdoc}
     */
    public function getSegments(): array
    {
        return RouteParser::parse($this->uri, $this->wheres);
    }

    /**
     * {@inheritdoc}
     */
    public function getController()
    {
        [$class] = explode('@', $this->action['uses']);

        if ($this->controller === null) {
            if ($this->container !== null) {
                $container = $this->getContainer();

                if ($container->has($class)) {
                    $this->controller = $container->get($class);
                } elseif ($container instanceof FactoryContract) {
                    $this->controller = $container->resolve($class);
                }
            } else {
                $this->controller = new $class();
            }
        }

        return $this->controller;
    }

    /**
     * {@inheritdoc}
     */
    public function run(ServerRequestInterface $serverRequest): ResponseInterface
    {
        if ($this->isControllerAction()) {
            return $this->getController()->{$this->getControllerMethod()}();
        }

        return $this->getInvoker()->call(
            $this->action['uses'],
            [$serverRequest, $this->parameters]
        );
    }

    /**
     * Parse arguments to the where method into an array.
     *
     * @param array|string $name
     * @param string|null  $expression
     *
     * @return array
     */
    protected function parseWhere($name, ?string $expression): array
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
     * Get the bound route controller middlewares.
     *
     * @return array
     */
    protected function getControllerMiddlewares(): array
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
     * Get the bound route controller disabled middlewares.
     *
     * @return array
     */
    protected function getControllerDisabledMiddlewares(): array
    {
        if (! $this->isControllerAction()) {
            return [];
        }

        $controller = $this->getController();

        if (method_exists($controller, 'gatherDisabledMiddlewares')) {
            return $controller->gatherDisabledMiddlewares();
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
     * Get the controller method used for the route.
     *
     * @return string
     */
    protected function getControllerMethod(): string
    {
        return explode('@', $this->action['uses'])[1];
    }
}
