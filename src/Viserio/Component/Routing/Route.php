<?php
declare(strict_types=1);
namespace Viserio\Component\Routing;

use Interop\Container\Exception\NotFoundException;
use Narrowspark\Arr\Arr;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
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
     * A server request instance.
     *
     * @var \Psr\Http\Message\ServerRequestInterface
     */
    protected $serverRequest;

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
    public function getServerRequest(): ServerRequestInterface
    {
        return $this->serverRequest;
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
        // Merge middlewares from Action.
        $middlewares        = Arr::get($this->action, 'middlewares', []);
        $withoutMiddlewares = Arr::get($this->action, 'without_middlewares', []);

        $mergedMiddlewares = [
            'middlewares' => array_unique(array_merge(
                $this->middlewares['middlewares'] ?? [],
                is_array($middlewares) ? $middlewares : [$middlewares],
                $this->getControllerMiddleware()
            ), SORT_REGULAR),
            'without_middlewares' => array_unique(array_merge(
                $this->middlewares['without_middlewares'] ?? [],
                is_array($withoutMiddlewares) ? $withoutMiddlewares : [$withoutMiddlewares]
            ), SORT_REGULAR),
        ];

        return $this->middlewares = $mergedMiddlewares;
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
        list($class) = explode('@', $this->action['uses']);

        if (! $this->controller) {
            $container = $this->getContainer();

            try {
                $this->controller = $container->get($class);
            } catch (NotFoundException $exception) {
                if (method_exists($container, 'make')) {
                    $this->controller = $container->make($class);
                } else {
                    throw new $exception();
                }
            }
        }

        return $this->controller;
    }

    /**
     * {@inheritdoc}
     */
    public function run(ServerRequestInterface $request): ResponseInterface
    {
        $this->serverRequest = $request;

        if ($this->isControllerAction()) {
            return $this->getController()->{$this->getControllerMethod()}();
        }

        return $this->getInvoker()->call(
            $this->action['uses'],
            [$request, $this->parameters]
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
     * Get the controller method used for the route.
     *
     * @return string
     */
    protected function getControllerMethod(): string
    {
        return explode('@', $this->action['uses'])[1];
    }
}
