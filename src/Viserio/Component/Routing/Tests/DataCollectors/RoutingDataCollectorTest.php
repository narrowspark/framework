<?php
declare(strict_types=1);
namespace Viserio\Component\Routing\Tests\DataCollectors;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Component\Contracts\Routing\RouteCollection as RouteCollectionContract;
use Viserio\Component\Routing\DataCollectors\RoutingDataCollector;
use Viserio\Component\Routing\Route;

class RoutingDataCollectorTest extends MockeryTestCase
{
    public function testCollect()
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

    public function testGetMenu()
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
            'icon'  => file_get_contents(__DIR__ . '/../../DataCollectors/Resources/icons/ic_directions_white_24px.svg'),
            'label' => 'Routes',
            'value' => 1,
        ], $collector->getMenu());
    }

    public function testGetPanel()
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

        self::assertSame('<table class="row"><thead><tr><th scope="col" class="Methods">Methods</th><th scope="col" class="Path">Path</th><th scope="col" class="Name">Name</th><th scope="col" class="Action">Action</th><th scope="col" class="With Middleware">With Middleware</th><th scope="col" class="Without Middleware">Without Middleware</th><th scope="col" class="Domain">Domain</th></tr></thead><tbody><tr><td>GET | HEAD</td><td>/test</td><td></td><td>Closure</td><td></td><td></td><td>Domain</td></tr></tbody></table>', $collector->getPanel());
    }
}
