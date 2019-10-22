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
// use Viserio\Component\Foundation\Provider\FoundationDataCollectorServiceProvider;
// use Viserio\Component\HttpFactory\Provider\HttpFactoryServiceProvider;
// use Viserio\Component\Profiler\Provider\ProfilerServiceProvider;
//
///**
// * @internal
// */
// final class FoundationDataCollectorServiceProviderTest extends MockeryTestCase
// {
//    public function testGetServices(): void
//    {
//        $kernel = \Mockery::mock(KernelContract::class);
//        $kernel->shouldReceive('getRootDir')
//            ->once()
//            ->andReturn('');
//        $kernel->shouldReceive('getEnvironment')
//            ->once()
//            ->andReturn('local');
//        $kernel->shouldReceive('isDebug')
//            ->once()
//            ->andReturn(true);
//
//        $container = new Container();
//        $container->bind(ServerRequestInterface::class, $this->getRequest());
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
//                    'narrowspark' => true,
//                    'files'       => true,
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
//        $this->assertArrayHasKey('narrowspark', $profiler->getCollectors());
//        $this->assertArrayHasKey('files-loaded-collector', $profiler->getCollectors());
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
