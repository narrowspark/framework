<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018-2020 Daniel Bannert
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/narrowspark/automatic
 */

namespace Viserio\Component\Routing\Tests\DataCollector;

use Mockery;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Component\Routing\DataCollector\RoutingDataCollector;
use Viserio\Component\Routing\Route;
use Viserio\Contract\Routing\RouteCollection as RouteCollectionContract;

/**
 * @internal
 *
 * @small
 * @coversNothing
 */
final class RoutingDataCollectorTest extends MockeryTestCase
{
    public function testCollect(): void
    {
        $route = new Route('GET', '/test', ['domain' => 'test.com']);
        $routes = Mockery::mock(RouteCollectionContract::class);
        $routes->shouldReceive('getRoutes')
            ->twice()
            ->andReturn([$route]);
        $collector = new RoutingDataCollector($routes);

        $collector->collect(
            Mockery::mock(ServerRequestInterface::class),
            Mockery::mock(ResponseInterface::class)
        );

        $data = $collector->getData();

        self::assertSame([
            'routes' => [$route],
            'counted' => 1,
        ], $data);
    }

    public function testGetMenu(): void
    {
        $route = new Route('GET', '/test', ['domain' => 'test.com']);
        $routes = Mockery::mock(RouteCollectionContract::class);
        $routes->shouldReceive('getRoutes')
            ->twice()
            ->andReturn([$route]);
        $collector = new RoutingDataCollector($routes);

        $collector->collect(
            Mockery::mock(ServerRequestInterface::class),
            Mockery::mock(ResponseInterface::class)
        );

        self::assertSame([
            'icon' => \file_get_contents(\dirname(__DIR__, 2) . \DIRECTORY_SEPARATOR . 'Resource' . \DIRECTORY_SEPARATOR . 'icons' . \DIRECTORY_SEPARATOR . 'ic_directions_white_24px.svg'),
            'label' => 'Routes',
            'value' => 1,
        ], $collector->getMenu());
    }
}
