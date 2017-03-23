<?php
declare(strict_types=1);
namespace Viserio\Component\Routing\DataCollectors;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Component\Contracts\Routing\RouteCollection as RouteCollectionContract;
use Viserio\Component\Contracts\WebProfiler\PanelAware as PanelAwareContract;
use Viserio\Component\WebProfiler\DataCollectors\AbstractDataCollector;

class RoutingDataCollector extends AbstractDataCollector implements PanelAwareContract
{
    /**
     * Router instance.
     *
     * @var \Viserio\Component\Contracts\Routing\RouteCollection
     */
    protected $routes;

    /**
     * Create a new viserio routes data collector.
     *
     * @param \Viserio\Component\Contracts\Routing\RouteCollection $routes
     */
    public function __construct(RouteCollectionContract $routes)
    {
        $this->routes = $routes;
    }

    /**
     * {@inheritdoc}
     */
    public function collect(ServerRequestInterface $serverRequest, ResponseInterface $response): void
    {
        $this->data = [
            'routes'  => $this->routes->getRoutes(),
            'counted' => count($this->routes->getRoutes()),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getMenu(): array
    {
        return [
            'icon'  => file_get_contents(__DIR__ . '/Resources/icons/ic_directions_white_24px.svg'),
            'label' => 'Routes',
            'value' => $this->data['counted'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getPanel(): string
    {
        $headers = [0 => 'Methods', 2 => 'Path', 3 => 'Name', 4 => 'Action', 5 => 'With Middleware', 6 => 'Without Middleware'];
        $data    = [];

        foreach ($this->data['routes'] as $route) {
            $middlewares        = $route->gatherMiddleware();
            $middleware         = array_values($middlewares['middlewares']);
            $withoutMiddlewares = array_values($middlewares['without_middlewares']);

            $routeData = [
                0 => implode(' | ', $route->getMethods()),
                2 => $route->getUri(),
                3 => $route->getName() ?? '',
                4 => $route->getActionName(),
                5 => implode(', ', $middleware),
                6 => implode(', ', $withoutMiddlewares),
            ];

            if ($route->getDomain() !== null) {
                $routeData[1] = 'Domain';
            }

            $data[] = $routeData;
        }

        if (isset($data[0][1])) {
            $headers[1] = 'Domain';
        }

        sort($data, SORT_NUMERIC);
        sort($headers, SORT_NUMERIC);

        return $this->createTable(
            $data,
            [
                'headers'   => $headers,
                'vardumper' => false,
            ]
        );
    }
}
