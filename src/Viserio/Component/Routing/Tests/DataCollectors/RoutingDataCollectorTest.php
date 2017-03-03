<?php
declare(strict_types=1);
namespace Viserio\Component\Routing\Tests\DataCollectors;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Component\Contracts\Routing\RouteCollection as RouteCollectionContract;
use Viserio\Component\Routing\DataCollectors\RoutingDataCollector;
use Viserio\Component\Routing\Route;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class RoutingDataCollectorTest extends MockeryTestCase
{
    public function testCollect()
    {
        $route = new Route('GET', '/test', ['domain' => 'test.com']);
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
            'routes' => [$route],
            'counted' => 1,
        ], $data);
    }
}
