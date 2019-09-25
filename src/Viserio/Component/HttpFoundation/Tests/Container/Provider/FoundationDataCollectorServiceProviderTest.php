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
// use Viserio\Component\Config\Provider\ConfigServiceProvider;
// use Viserio\Component\Container\Container;
// use Viserio\Contract\Config\Repository as RepositoryContract;
// use Viserio\Contract\Foundation\Kernel as KernelContract;
// use Viserio\Contract\Profiler\Profiler as ProfilerContract;
// use Viserio\Contract\Routing\Route as RouteContract;
// use Viserio\Contract\Routing\Router as RouterContract;
// use Viserio\Component\HttpFactory\Provider\HttpFactoryServiceProvider;
// use Viserio\Component\HttpFoundation\Provider\FoundationDataCollectorServiceProvider;
// use Viserio\Component\Profiler\Provider\ProfilerServiceProvider;
//
///**
// * @internal
// */
// final class FoundationDataCollectorServiceProviderTest extends MockeryTestCase
// {
//    public function testGetServices(): void
//    {
//        $route  = \Mockery::mock(RouteContract::class);
//        $router = \Mockery::mock(RouterContract::class);
//        $router->shouldReceive('group')
//            ->once();
//        $router->shouldReceive('getCurrentRoute')
//            ->once()
//            ->andReturn($route);
//
//        $kernel = \Mockery::mock(KernelContract::class);
//        $kernel->shouldReceive('getRoutesPath')
//            ->once()
//            ->andReturn('');
//
//        $container = new Container();
//        $container->bind(ServerRequestInterface::class, $this->getRequest());
//        $container->bind(RouterContract::class, $router);
//        $container->bind(KernelContract::class, $kernel);
//        $container->register(new HttpFactoryServiceProvider());
//        $container->register(new ConfigServiceProvider());
//        $container->register(new ProfilerServiceProvider());
//        $container->register(new FoundationDataCollectorServiceProvider());
//
//        $container->get(RepositoryContract::class)->set('viserio', [
//            'profiler' => [
//                'enable'    => true,
//                'collector' => [
//                    'viserio_http' => true,
//                ],
//            ],
//        ]);
//
//        $profiler = $container->get(ProfilerContract::class);
//
//        $this->assertInstanceOf(ProfilerContract::class, $profiler);
//
//        $this->assertArrayHasKey('time-data-collector', $profiler->getCollectors());
//        $this->assertArrayHasKey('memory-data-collector', $profiler->getCollectors());
//        $this->assertArrayHasKey('viserio-http-data-collector', $profiler->getCollectors());
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
