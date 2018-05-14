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

//
// use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
// use Psr\Http\Message\ServerRequestInterface;
// use Viserio\Component\Container\Container;
// use Viserio\Contract\Profiler\Profiler as ProfilerContract;
// use Viserio\Contract\Routing\RouteCollection as RouteCollectionContract;
// use Viserio\Contract\Routing\Router as RouterContract;
// use Viserio\Component\HttpFactory\Provider\HttpFactoryServiceProvider;
// use Viserio\Component\Profiler\Provider\ProfilerServiceProvider;
// use Viserio\Component\Routing\Provider\RoutingDataCollectorServiceProvider;
//
///**
// * @internal
// */
// final class RoutingDataCollectorServiceProviderTest extends MockeryTestCase
// {
//    public function testGetServices(): void
//    {
//        $routes = \Mockery::mock(RouteCollectionContract::class);
//        $router = \Mockery::mock(RouterContract::class);
//        $router->shouldReceive('getRoutes')
//            ->once()
//            ->andReturn($routes);
//        $router->shouldReceive('group')
//            ->once();
//
//        $container = new Container();
//        $container->bind(ServerRequestInterface::class, $this->getRequest());
//        $container->bind(RouterContract::class, $router);
//        $container->register(new HttpFactoryServiceProvider());
//        $container->register(new ProfilerServiceProvider());
//        $container->register(new RoutingDataCollectorServiceProvider());
//
//        $container->bind(
//            'config',
//            [
//                'viserio' => [
//                    'profiler' => [
//                        'enable'    => true,
//                        'collector' => [
//                            'routes' => true,
//                        ],
//                    ],
//                ],
//            ]
//        );
//
//        $profiler = $container->get(ProfilerContract::class);
//
//        $this->assertInstanceOf(ProfilerContract::class, $profiler);
//
//        $this->assertArrayHasKey('time-data-collector', $profiler->getCollectors());
//        $this->assertArrayHasKey('memory-data-collector', $profiler->getCollectors());
//        $this->assertArrayHasKey('routing-data-collector', $profiler->getCollectors());
//    }
//
//    /**
//     * @return \Mockery\MockInterface|\Psr\Http\Message\ServerRequestInterface
//     */
//    private function getRequest()
//    {
//        $request = \Mockery::mock(ServerRequestInterface::class);
//        $request->shouldReceive('getHeaderLine')
//            ->with('request_time_float')
//            ->andReturn(false);
//        $request->shouldReceive('getHeaderLine')
//            ->with('request_time')
//            ->andReturn(false);
//
//        return $request;
//    }
// }
