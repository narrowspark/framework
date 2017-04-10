<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\DataCollectors;

use Closure;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionFunction;
use ReflectionMethod;
use Viserio\Component\Contracts\Config\Repository as RepositoryContract;
use Viserio\Component\Contracts\Routing\Route as RouteContract;
use Viserio\Component\Contracts\Routing\Router as RouterContract;
use Viserio\Component\Contracts\Session\Store as StoreContract;
use Viserio\Component\Contracts\Profiler\AssetAware as AssetAwareContract;
use Viserio\Component\Contracts\Profiler\PanelAware as PanelAwareContract;
use Viserio\Component\Contracts\Profiler\TooltipAware as TooltipAwareContract;
use Viserio\Component\Profiler\DataCollectors\AbstractDataCollector;

class ViserioHttpDataCollector extends AbstractDataCollector implements
    TooltipAwareContract,
    AssetAwareContract,
    PanelAwareContract
{
    /**
     * A server request instance.
     *
     * @var \Psr\Http\Message\ServerRequestInterface
     */
    protected $serverRequest;

    /**
     * A response instance.
     *
     * @var \Psr\Http\Message\ResponseInterface
     */
    protected $response;

    /**
     * List of all request sessions.
     *
     * @var array
     */
    protected $sessions;

    /**
     * Current route.
     *
     * @var \Viserio\Component\Contracts\Routing\Route
     */
    protected $route;

    /**
     * Config instance.
     *
     * @var \Viserio\Component\Contracts\Config\Repository
     */
    protected $config;

    /**
     * Create a new viserio request and response data collector.
     *
     * @param \Viserio\Component\Contracts\Routing\Router    $router
     * @param \Viserio\Component\Contracts\Config\Repository $config
     */
    public function __construct(RouterContract $router, RepositoryContract $config)
    {
        $this->route         = $router->getCurrentRoute();
        $this->serverRequest = $this->route->getServerRequest();
        $this->config        = $config;
    }

    /**
     * {@inheritdoc}
     */
    public function collect(ServerRequestInterface $serverRequest, ResponseInterface $response): void
    {
        $this->response = $response;

        $sessions = [];

        foreach ($this->serverRequest->getAttributes() as $name => $value) {
            if ($value instanceof StoreContract) {
                $sessions[] = $value;
            }
        }

        $this->sessions = $sessions;
    }

    /**
     * {@inheritdoc}
     */
    public function getMenu(): array
    {
        $statusCode = $this->response->getStatusCode();
        $status     = '';

        // Successful 2xx
        if ($statusCode >= 200 && $statusCode <= 226) {
            $status = 'response-status-green';
        // Redirection 3xx
        } elseif ($statusCode >= 300 && $statusCode <= 308) {
            $status = 'response-status-yellow';
        // Client Error 4xx
        } elseif ($statusCode >= 400 && $statusCode <= 511) {
            $status = 'response-status-red';
        }

        $tabInfos = [
            'label' => $statusCode,
            'value' => '',
        ];

        if ($this->route !== null && $this->route->getName() !== null) {
            $tabInfos = array_merge(
                $tabInfos,
                [
                    'label' => '@',
                    'value' => $this->route->getName(),
                ]
            );
        } elseif ($this->route !== null) {
            $tabInfos = array_merge(
                $tabInfos,
                [
                    'class'  => $status,
                    'value'  => implode(' | ', $this->route->getMethods()),
                ]
            );
        }

        return $tabInfos;
    }

    /**
     * {@inheritdoc}
     */
    public function getTooltip(): string
    {
        $routeInfos = $this->getRouteInformation($this->route);

        $html = $this->createTooltipGroup([
            'Methods'             => $routeInfos['methods'],
            'Uri'                 => $routeInfos['uri'],
            'With Middlewares'    => $routeInfos['middlewares'],
            'Without Middlewares' => $routeInfos['without_middlewares'] ?? '',
            'Namespace'           => $routeInfos['namespace'],
            'Prefix'              => $routeInfos['prefix'] ?? 'null',
            'File'                => $routeInfos['file'] ?? '',
        ]);

        return $html;
    }

    /**
     * {@inheritdoc}
     */
    public function getPanel(): string
    {
        $session     = $this->serverRequest->getAttribute('session');
        $sessionMeta = [];

        if ($session !== null) {
            $sessionMeta = [
                'firstTrace'        => $session->getFirstTrace(),
                'lastTrace'         => $session->getLastTrace(),
                'regenerationTrace' => $session->getRegenerationTrace(),
                'requestsCount'     => $session->getRequestsCount(),
                'fingerprint'       => $session->getFingerprint(),
            ];
        }

        $html = $this->createTabs([
            [
                'name'    => 'Request',
                'content' => $this->createTable(
                    $this->serverRequest->getQueryParams(),
                    ['name' => 'Get Parameters']
                ) . $this->createTable(
                    $this->serverRequest->getParsedBody() ?? [],
                    ['name' => 'Post Parameters']
                ) . $this->createTable(
                    $this->prepareRequestAttributes($this->serverRequest->getAttributes()),
                    ['name' => 'Request Attributes']
                ) . $this->createTable(
                    $this->splitOnAttributeDelimiter($this->serverRequest->getHeaderLine('Cookie')),
                    ['name' => 'Cookies']
                ) . $this->createTable(
                    $this->prepareRequestHeaders($this->serverRequest->getHeaders()),
                    ['name' => 'Request Headers']
                ) . $this->createTable(
                    $this->prepareServerParams($this->serverRequest->getServerParams()),
                    ['name' => 'Server Parameters']
                ),
            ],
            [
                'name'    => 'Response',
                'content' => $this->createTable(
                    $this->response->getHeaders(),
                    [
                        'name'    => 'Response Headers',
                        'headers' => [
                            'key' => 'Header',
                        ],
                    ]
                ) . $this->createTable(
                    $this->serverRequest->getHeader('Set-Cookie'),
                    ['name' => 'Cookies']
                ),
            ],
            [
                'name'    => 'Session',
                'content' => $this->createTable(
                    $sessionMeta,
                    ['name' => 'Session Metadata']
                ) . $this->createTable(
                    $session !== null ? $session->getAll() : [],
                    ['name' => 'Session Attributes']
                ),
            ],
            [
                'name'    => 'Flashes',
                'content' => $this->createTable(
                    $session !== null ? $session->get('_flash') : [],
                    ['name' => 'Flashes']
                ),
            ],
        ]);

        return $html;
    }

    /**
     * {@inheritdoc}
     */
    public function getAssets(): array
    {
        return [
            'css' => __DIR__ . '/Resources/css/request-response.css',
        ];
    }

    /**
     * Get the route information for a given route.
     *
     * @param \Viserio\Component\Contracts\Routing\Route $route
     *
     * @return array
     */
    protected function getRouteInformation(RouteContract $route): array
    {
        $routesPath = realpath($this->config->get('path.app', ''));
        $action     = $route->getAction();

        $result = [
           'uri'     => $route->getUri() ?: '-',
           'methods' => count($route->getMethods()) > 1 ?
                implode(' | ', $route->getMethods()) :
                $route->getMethods(),
        ];

        $result = array_merge($result, $action);

        if (isset($action['controller']) && mb_strpos($action['controller'], '@') !== false) {
            list($controller, $method) = explode('@', $action['controller']);

            if (class_exists($controller) && method_exists($controller, $method)) {
                $reflector = new ReflectionMethod($controller, $method);
            }

            unset($result['uses']);
        } elseif (isset($action['uses']) && $action['uses'] instanceof Closure) {
            $reflector      = new ReflectionFunction($action['uses']);
            $result['uses'] = $this->cloneVar($result['uses']);
        }

        if (isset($reflector)) {
            $filename       = ltrim(str_replace($routesPath, '', $reflector->getFileName()), '/');
            $result['file'] = $filename . ': ' . $reflector->getStartLine() . ' - ' . $reflector->getEndLine();
        }

        $middlewares = $route->gatherMiddleware();

        if ($middleware = $this->getMiddlewares($middlewares)) {
            $result['middlewares'] = $middleware;
        }

        if ($middleware = $this->getWithoutMiddlewares($middlewares)) {
            $result['without_middlewares'] = $middleware;
        }

        return $result;
    }

    /**
     * Get middleware.
     *
     * @param array $middlewares
     *
     * @return string
     */
    protected function getMiddlewares(array $middlewares): string
    {
        $middleware = array_keys($middlewares['middlewares']);

        return implode(', ', $middleware);
    }

    /**
     * Get without middleware.
     *
     * @param array $middlewares
     *
     * @return string
     */
    protected function getWithoutMiddlewares(array $middlewares): string
    {
        $middleware = array_keys($middlewares['without_middlewares']);

        return implode(', ', $middleware);
    }

    /**
     * spplit string on attributes delimiter to array.
     *
     * @param string $string
     *
     * @return array
     */
    protected function splitOnAttributeDelimiter(string $string): array
    {
        return array_filter(preg_split('@\s*[;]\s*@', $string));
    }

    /**
     * Prepare request attributes, check of route object.
     *
     * @param array $attributes
     *
     * @return array
     */
    protected function prepareRequestAttributes(array $attributes): array
    {
        $preparedAttributes = [];

        foreach ($attributes as $key => $value) {
            if ($key === '_route') {
                if (is_object($value)) {
                    $value = [
                        'Uri'        => $value->getUri(),
                        'Parameters' => $value->getParameters(),
                    ];
                }

                $preparedAttributes[$key] = $value;
            } else {
                $preparedAttributes[$key] = $value;
            }
        }

        return $preparedAttributes;
    }

    /**
     * Prepare request headers.
     *
     * @param array $headers
     *
     * @return array
     */
    protected function prepareRequestHeaders(array $headers): array
    {
        $preparedHeaders = [];

        foreach ($headers as $key => $value) {
            if (count($value) === 1) {
                $preparedHeaders[$key] = $value[0];
            } else {
                $preparedHeaders[$key] = $value;
            }
        }

        return $preparedHeaders;
    }

    /**
     * Prepare server parameter.
     * Hide all keys with a _KEY|_PASSWORD|_PW|_SECRET in it.
     *
     * @param array $params
     *
     * @return array
     */
    protected function prepareServerParams(array $params): array
    {
        $preparedParams = [];

        foreach ($params as $key => $value) {
            if (preg_match('/(_KEY|_PASSWORD|_PW|_SECRET)/s', $key)) {
                $preparedParams[$key] = '******';
            } else {
                $preparedParams[$key] = $value;
            }
        }

        return $preparedParams;
    }
}
