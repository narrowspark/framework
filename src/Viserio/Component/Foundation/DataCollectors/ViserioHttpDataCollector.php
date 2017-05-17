<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\DataCollectors;

use Closure;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionFunction;
use ReflectionMethod;
use Viserio\Component\Contracts\Config\Repository as RepositoryContract;
use Viserio\Component\Contracts\Profiler\AssetAware as AssetAwareContract;
use Viserio\Component\Contracts\Profiler\PanelAware as PanelAwareContract;
use Viserio\Component\Contracts\Profiler\TooltipAware as TooltipAwareContract;
use Viserio\Component\Contracts\Routing\Route as RouteContract;
use Viserio\Component\Contracts\Routing\Router as RouterContract;
use Viserio\Component\Contracts\Session\Store as StoreContract;
use Viserio\Component\Cookie\RequestCookies;
use Viserio\Component\Cookie\ResponseCookies;
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
        $this->route  = $router->getCurrentRoute();
        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     */
    public function collect(ServerRequestInterface $serverRequest, ResponseInterface $response): void
    {
        $this->response      = $response;
        $this->serverRequest = $serverRequest;

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
            'class' => $status,
            'value' => '',
        ];

        if ($this->route !== null && $this->route->getName() !== null) {
            return array_merge(
                $tabInfos,
                [
                    'label' => '@',
                    'value' => $this->route->getName(),
                ]
            );
        } elseif ($this->route !== null) {
            return array_merge(
                $tabInfos,
                [
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
        $request     = $this->serverRequest;
        $response    = $this->response;
        $session     = $request->getAttribute('session');
        $sessionMeta = [];

        if ($session !== null) {
            $sessionMeta = [
                'Created'           => date(DATE_RFC2822, $session->getFirstTrace()),
                'Last used'         => date(DATE_RFC2822, $session->getLastTrace()),
                'Last regeneration' => date(DATE_RFC2822, $session->getRegenerationTrace()),
                'requestsCount'     => $session->getRequestsCount(),
                'fingerprint'       => $session->getFingerprint(),
            ];
        }

        $html = $this->createTabs([
            [
                'name'    => 'Request',
                'content' => $this->createTable(
                    $request->getQueryParams(),
                    [
                        'name'       => 'Get Parameters',
                        'empty_text' => 'No GET parameters',
                    ]
                ) . $this->createTable(
                    $request->getParsedBody() ?? [],
                    [
                        'name'       => 'Post Parameters',
                        'empty_text' => 'No POST parameters',
                    ]
                ) . $this->createTable(
                    $this->prepareRequestAttributes($request->getAttributes()),
                    ['name' => 'Request Attributes']
                ) . $this->createTable(
                    $this->prepareRequestHeaders($request->getHeaders()),
                    ['name' => 'Request Headers']
                ) . $this->createTable(
                    $this->prepareServerParams($request->getServerParams()),
                    ['name' => 'Server Parameters']
                ),
            ],
            [
                'name'    => 'Response',
                'content' => $this->createTable(
                    $response->getHeaders(),
                    ['name'    => 'Response Headers']
                ),
            ],
            $this->createCookieTab($request, $response),
            [
                'name'    => 'Session',
                'content' => $this->createTable(
                    $sessionMeta,
                    [
                        'name'       => 'Session Metadata',
                        'empty_text' => 'No session metadata',
                    ]
                ) . $this->createTable(
                    $session !== null ? $session->getAll() : [],
                    [
                        'name'       => 'Session Attributes',
                        'empty_text' => 'No session attributes',
                    ]
                ),
            ],
            [
                'name'    => 'Flashes',
                'content' => $this->createTable(
                    $session !== null && $session->has('_flash') ? $session->get('_flash') : [],
                    [
                        'name'       => 'Flashes',
                        'empty_text' => 'No flash messages were created',
                    ]
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

    protected function createCookieTab(ServerRequestInterface $serverRequest, ResponseInterface $response): ?array
    {
        if (! (class_exists(RequestCookies::class) && class_exists(ResponseCookies::class))) {
            return null;
        }

        $requestCookies = $responseCookies = [];

        foreach (RequestCookies::fromRequest($serverRequest)->getAll() as $cookie) {
            $requestCookies[$cookie->getName()] = $cookie->getValue();
        }

        foreach (ResponseCookies::fromResponse($response)->getAll() as $cookie) {
            $responseCookies[$cookie->getName()] = $cookie->getValue();
        }

        return [
            'name'    => 'Cookies',
            'content' => $this->createTable(
                $requestCookies,
                [
                    'name'       => 'Request Cookies',
                    'empty_text' => 'No request cookies',
                ]
            ) . $this->createTable(
                $responseCookies,
                [
                    'name'       => 'Response Cookies',
                    'empty_text' => 'No response cookies',
                ]
            ),
        ];
    }

    /**
     * Prepare request and response cookie infos and create a cookie tab.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $serverRequest
     * @param \Psr\Http\Message\ResponseInterface      $response
     *
     * @return array
     */
    protected function createCookieTab(ServerRequestInterface $serverRequest, ResponseInterface $response): array
    {
        if (! (class_exists(RequestCookies::class) && class_exists(ResponseCookies::class))) {
            return [];
        }

        $requestCookies = $responseCookies = [];

        foreach (RequestCookies::fromRequest($serverRequest)->getAll() as $cookie) {
            $requestCookies[$cookie->getName()] = $cookie->getValue();
        }

        foreach (ResponseCookies::fromResponse($response)->getAll() as $cookie) {
            $responseCookies[$cookie->getName()] = $cookie->getValue();
        }

        return [
            'name'    => 'Cookies',
            'content' => $this->createTable(
                $requestCookies,
                [
                    'name'       => 'Request Cookies',
                    'empty_text' => 'No request cookies',
                ]
            ) . $this->createTable(
                $responseCookies,
                [
                    'name'       => 'Response Cookies',
                    'empty_text' => 'No response cookies',
                ]
            ),
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

        $result['middlewares']         = implode(', ', $route->gatherMiddleware());
        $result['without_middlewares'] = implode(', ', $route->gatherDisabledMiddlewares());

        return $result;
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
            } elseif ($value instanceof StoreContract) {
                $preparedAttributes[$key] = $value->getId();
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
