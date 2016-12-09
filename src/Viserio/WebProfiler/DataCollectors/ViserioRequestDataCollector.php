<?php
declare(strict_types=1);
namespace Viserio\WebProfiler\DataCollectors;

use Closure;
use ReflectionFunction;
use ReflectionMethod;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Viserio\Contracts\WebProfiler\TabAware as TabAwareContract;
use Viserio\Contracts\Session\Store as StoreContract;
use Viserio\Contracts\WebProfiler\TooltipAware as TooltipAwareContract;
use Viserio\Contracts\WebProfiler\AssetAware as AssetAwareContract;
use Viserio\Contracts\Routing\Route as RouteContract;
use Viserio\Contracts\Routing\Router as RouterContract;
use Viserio\Contracts\WebProfiler\PanelAware as PanelAwareContract;
use Viserio\Contracts\Config\Repository as RepositoryContract;

class ViserioRequestDataCollector extends AbstractDataCollector implements TabAwareContract, TooltipAwareContract, AssetAwareContract, PanelAwareContract
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
     * @var \Viserio\Contracts\Routing\Route
     */
    protected $route;

    /**
     * Config instance.
     *
     * @var \Viserio\Contracts\Config\Repository
     */
    protected $config;

    /**
     * [__construct description]
     *
     * @param \Viserio\Contracts\Routing\Router    $router
     * @param \Viserio\Contracts\Config\Repository $config
     */
    public function __construct(RouterContract $router, RepositoryContract $config)
    {
        $this->route = $router->getCurrentRoute();
        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     */
    public function collect(ServerRequestInterface $serverRequest, ResponseInterface $response)
    {
        $this->serverRequest = $serverRequest;
        $this->response = $response;

        $sessions = [];

        foreach ($this->serverRequest->getAttributes() as $name => $value) {
            if ($value instanceof StoreContract) {
                $sessions[] = $value;
            }
        }

        return $this->sessions = $sessions;
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'viserio-request';
    }

    /**
     * {@inheritdoc}
     */
    public function getTabPosition(): string
    {
        return 'left';
    }

    /**
     * {@inheritdoc}
     */
    public function getTab(): array
    {
        $statusCode = $this->response->getStatusCode();
        $status = '';

        // Successful 2xx
        if ($statusCode >= 200 && $statusCode <= 226) {
            $status = 'request-status-green';
        // Redirection 3xx
        } elseif ($statusCode >= 300 && $statusCode <= 308) {
            $status = 'request-status-yellow';
        // Client Error 4xx
        } elseif ($statusCode >= 400 && $statusCode <= 511) {
            $status = 'request-status-red';
        }

        $tabInfos = [
            'status' => $statusCode,
            'class' => $status,
            'label' => '',
            'value' => ''
        ];

        if ($this->route !== null) {
            $tabInfos = array_merge(
                $tabInfos,
                [
                    'label' => '@',
                    'value' => $this->route->getName(),
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
            'Uri' => $routeInfos['uri'],
            'With Middlewares' => $routeInfos['middlewares'],
            'Without Middlewares' => $routeInfos['without_middlewares'] ?? '',
            'Namespace' => $routeInfos['namespace'],
            'Prefix' => $routeInfos['prefix'] ?? 'null',
            'File' => $routeInfos['file'] ?? '',
        ]);

        return $html;
    }

    /**
     * {@inheritdoc}
     */
    public function getPanel(): string
    {
        $html = '';

        return $html;
    }

    /**
     * {@inheritdoc}
     */
    public function getAssets(): array
    {
        return [
            'css' => __DIR__ . '/../Resources/css/widgets/viserio/request.css',
        ];
    }

    /**
     * Get the route information for a given route.
     *
     * @param \Viserio\Contracts\Routing\Route $route
     *
     * @return array
     */
    protected function getRouteInformation(RouteContract $route): array
    {
        $routesPath = realpath($this->config->get('path.app', ''));

        $methods = $route->getMethods();
        $uri = reset($methods) . ' ' . $route->getUri();
        $action = $route->getAction();

        $result = [
           'uri' => $uri ?: '-',
        ];

        $result = array_merge($result, $action);

        if (isset($action['controller']) && strpos($action['controller'], '@') !== false) {
            list($controller, $method) = explode('@', $action['controller']);

            if (class_exists($controller) && method_exists($controller, $method)) {
                $reflector = new ReflectionMethod($controller, $method);
            }

            unset($result['uses']);
        } elseif (isset($action['uses']) && $action['uses'] instanceof Closure) {
            $reflector = new ReflectionFunction($action['uses']);
            $result['uses'] = $this->formatVar($result['uses']);
        }

        if (isset($reflector)) {
            $filename = ltrim(str_replace($routesPath, '', $reflector->getFileName()), '/');
            $result['file'] = $filename . ':' . $reflector->getStartLine() . '-' . $reflector->getEndLine();
        }

        if ($middleware = $this->getMiddlewares($route)) {
            $result['middlewares'] = $middleware;
        }

        if ($middleware = $this->getWithoutMiddlewares($route)) {
            $result['without_middlewares'] = $middleware;
        }

        return $result;
    }

     /**
     * Get middleware
     *
     * @param \Viserio\Contracts\Routing\Route $route
     *
     * @return string
     */
    protected function getMiddlewares(RouteContract $route): string
    {
        $middleware = array_keys($route->gatherMiddleware()['middlewares']);

        return implode(', ', $middleware);
    }

     /**
     * Get without middleware
     *
     * @param \Viserio\Contracts\Routing\Route $route
     *
     * @return string
     */
    protected function getWithoutMiddlewares(RouteContract $route): string
    {
        $middleware = array_keys($route->gatherMiddleware()['without_middlewares']);

        return implode(', ', $middleware);
    }
}
