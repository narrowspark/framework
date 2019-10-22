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
// use Psr\Cache\CacheItemPoolInterface;
// use Psr\Http\Message\ServerRequestInterface;
// use Viserio\Component\Container\Container;
// use Viserio\Contract\Profiler\Profiler as ProfilerContract;
// use Viserio\Component\HttpFactory\Provider\HttpFactoryServiceProvider;
// use Viserio\Component\Profiler\DataCollector\Bridge\Cache\TraceableCacheItemDecorator;
// use Viserio\Component\Profiler\Provider\ProfilerPsr6Psr16CacheBridgeServiceProvider;
// use Viserio\Component\Profiler\Provider\ProfilerServiceProvider;
//
///**
// * @internal
// */
// final class ProfilerPsr6Psr16CacheBridgeServiceProviderTest extends MockeryTestCase
// {
//    public function testProvider(): void
//    {
//        $container = new Container();
//        $container->bind(CacheItemPoolInterface::class, \Mockery::mock(CacheItemPoolInterface::class));
//        $container->bind(ServerRequestInterface::class, $this->getRequest());
//        $container->register(new HttpFactoryServiceProvider());
//        $container->register(new ProfilerServiceProvider());
//        $container->register(new ProfilerPsr6Psr16CacheBridgeServiceProvider());
//
//        $container->bind('config', ['viserio' => ['profiler' => ['enable' => true]]]);
//
//        $this->assertInstanceOf(ProfilerContract::class, $container->get(ProfilerContract::class));
//        $this->assertInstanceOf(TraceableCacheItemDecorator::class, $container->get(CacheItemPoolInterface::class));
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
