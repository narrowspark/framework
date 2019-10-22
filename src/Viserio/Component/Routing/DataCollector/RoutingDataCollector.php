<?php

declare(strict_types=1);

/**
 * This file is part of Narrowspark Framework.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Viserio\Component\Routing\DataCollector;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Component\Profiler\DataCollector\AbstractDataCollector;
use Viserio\Contract\Profiler\PanelAware as PanelAwareContract;
use Viserio\Contract\Routing\RouteCollection as RouteCollectionContract;

class RoutingDataCollector extends AbstractDataCollector implements PanelAwareContract
{
    /**
     * Router instance.
     *
     * @var \Viserio\Contract\Routing\RouteCollection
     */
    protected $routes;

    /**
     * Create a new viserio routes data collector.
     *
     * @param \Viserio\Contract\Routing\RouteCollection $routes
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
            'routes' => $this->routes->getRoutes(),
            'counted' => \count($this->routes->getRoutes()),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getMenu(): array
    {
        return [
            'icon' => \file_get_contents(\dirname(__DIR__, 1) . \DIRECTORY_SEPARATOR . 'Resource' . \DIRECTORY_SEPARATOR . 'icons' . \DIRECTORY_SEPARATOR . 'ic_directions_white_24px.svg'),
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
        $data = [];

        foreach ($this->data['routes'] as $route) {
            $routeData = [
                0 => \implode(' | ', $route->getMethods()),
                2 => $route->getUri(),
                3 => $route->getName() ?? '-',
                4 => $route->getActionName(),
                5 => \implode(', ', $route->gatherMiddleware()),
                6 => \implode(', ', $route->gatherDisabledMiddleware()),
            ];

            if ($route->getDomain() !== null) {
                $routeData[1] = 'Domain';
            }

            $data[] = $routeData;
        }

        if (isset($data[0][1])) {
            $headers[1] = 'Domain';
        }

        \sort($data, \SORT_NUMERIC);
        \sort($headers, \SORT_NUMERIC);

        return $this->createTable(
            $data,
            [
                'headers' => $headers,
                'vardumper' => false,
            ]
        );
    }
}
