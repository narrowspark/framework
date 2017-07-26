<?php
declare(strict_types=1);
namespace Viserio\Component\Routing\Tests\DataCollector;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Component\Contracts\Routing\RouteCollection as RouteCollectionContract;
use Viserio\Component\Routing\DataCollector\RoutingDataCollector;
use Viserio\Component\Routing\Route;

class RoutingDataCollectorTest extends MockeryTestCase
{
    public function testCollect(): void
    {
        $route  = new Route('GET', '/test', ['domain' => 'test.com']);
        $routes = $this->mock(RouteCollectionContract::class);
        $routes->shouldReceive('getRoutes')
            ->twice()
            ->andReturn([$route]);
        $collector = new RoutingDataCollector($routes);

        $collector->collect(
            $this->mock(ServerRequestInterface::class),
            $this->mock(ResponseInterface::class)
        );

        $data = $collector->getData();

        self::assertSame([
            'routes'  => [$route],
            'counted' => 1,
        ], $data);
    }

    public function testGetMenu(): void
    {
        $route  = new Route('GET', '/test', ['domain' => 'test.com']);
        $routes = $this->mock(RouteCollectionContract::class);
        $routes->shouldReceive('getRoutes')
            ->twice()
            ->andReturn([$route]);
        $collector = new RoutingDataCollector($routes);

        $collector->collect(
            $this->mock(ServerRequestInterface::class),
            $this->mock(ResponseInterface::class)
        );

        self::assertSame([
            'icon'  => \file_get_contents(__DIR__ . '/../../DataCollector/Resources/icons/ic_directions_white_24px.svg'),
            'label' => 'Routes',
            'value' => 1,
        ], $collector->getMenu());
    }
}
