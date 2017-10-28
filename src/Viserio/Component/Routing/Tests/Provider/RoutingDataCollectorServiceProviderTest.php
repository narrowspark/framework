<?php
declare(strict_types=1);
namespace Viserio\Component\Routing\Tests\Provider;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Component\Container\Container;
use Viserio\Component\Contract\Profiler\Profiler as ProfilerContract;
use Viserio\Component\Contract\Routing\RouteCollection as RouteCollectionContract;
use Viserio\Component\Contract\Routing\Router as RouterContract;
use Viserio\Component\HttpFactory\Provider\HttpFactoryServiceProvider;
use Viserio\Component\Profiler\Provider\ProfilerServiceProvider;
use Viserio\Component\Routing\Provider\RoutingDataCollectorServiceProvider;

class RoutingDataCollectorServiceProviderTest extends MockeryTestCase
{
    public function testGetServices(): void
    {
        $routes = $this->mock(RouteCollectionContract::class);
        $router = $this->mock(RouterContract::class);
        $router->shouldReceive('getRoutes')
            ->once()
            ->andReturn($routes);
        $router->shouldReceive('group')
            ->once();

        $container = new Container();
        $container->instance(ServerRequestInterface::class, $this->getRequest());
        $container->instance(RouterContract::class, $router);
        $container->register(new HttpFactoryServiceProvider());
        $container->register(new ProfilerServiceProvider());
        $container->register(new RoutingDataCollectorServiceProvider());

        $container->instance(
            'config',
            [
                'viserio' => [
                    'profiler' => [
                        'enable'    => true,
                        'collector' => [
                            'routes' => true,
                        ],
                    ],
                ],
            ]
        );

        $profiler = $container->get(ProfilerContract::class);

        self::assertInstanceOf(ProfilerContract::class, $profiler);

        self::assertTrue(\array_key_exists('time-data-collector', $profiler->getCollectors()));
        self::assertTrue(\array_key_exists('memory-data-collector', $profiler->getCollectors()));
        self::assertTrue(\array_key_exists('routing-data-collector', $profiler->getCollectors()));
    }

    private function getRequest()
    {
        $request = $this->mock(ServerRequestInterface::class);
        $request->shouldReceive('getHeaderLine')
            ->with('REQUEST_TIME_FLOAT')
            ->andReturn(false);
        $request->shouldReceive('getHeaderLine')
            ->with('REQUEST_TIME')
            ->andReturn(false);

        return $request;
    }
}
