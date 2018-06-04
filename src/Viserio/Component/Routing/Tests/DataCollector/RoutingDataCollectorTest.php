<?php
declare(strict_types=1);
namespace Viserio\Component\Routing\Tests\DataCollector;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Component\Contract\Routing\RouteCollection as RouteCollectionContract;
use Viserio\Component\Routing\DataCollector\RoutingDataCollector;
use Viserio\Component\Routing\Route;

/**
 * @internal
 */
final class RoutingDataCollectorTest extends MockeryTestCase
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

        $this->assertSame([
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

        $this->assertSame([
            'icon'  => \file_get_contents(__DIR__ . '/../../Resource/icons/ic_directions_white_24px.svg'),
            'label' => 'Routes',
            'value' => 1,
        ], $collector->getMenu());
    }
}
