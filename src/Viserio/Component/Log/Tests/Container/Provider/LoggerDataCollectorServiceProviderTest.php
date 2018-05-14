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
// use Viserio\Component\Events\Provider\EventsServiceProvider;
// use Viserio\Component\HttpFactory\Provider\HttpFactoryServiceProvider;
// use Viserio\Component\Log\Logger;
// use Viserio\Component\Log\LogManager;
// use Viserio\Component\Log\Provider\LoggerDataCollectorServiceProvider;
// use Viserio\Component\Log\Provider\LoggerServiceProvider;
// use Viserio\Component\Profiler\Provider\ProfilerServiceProvider;
//
///**
// * @internal
// */
// final class LoggerDataCollectorServiceProviderTest extends MockeryTestCase
// {
//    public function testProvider(): void
//    {
//        $container = new Container();
//        $container->bind(ServerRequestInterface::class, $this->getRequest());
//        $container->register(new EventsServiceProvider());
//        $container->register(new LoggerServiceProvider());
//        $container->register(new HttpFactoryServiceProvider());
//        $container->register(new ProfilerServiceProvider());
//        $container->register(new LoggerDataCollectorServiceProvider());
//
//        $container->bind('config', [
//            'viserio' => [
//                'logging' => [
//                    'name' => 'narrowspark',
//                    'path' => __DIR__,
//                    'env'  => 'prod',
//                ],
//                'profiler' => [
//                    'enable'    => true,
//                    'collector' => [
//                        'logs' => true,
//                    ],
//                ],
//            ],
//        ]);
//
//        $this->assertInstanceOf(Logger::class, $container->get(LogManager::class)->getDriver());
//        $this->assertInstanceOf(ProfilerContract::class, $container->get(ProfilerContract::class));
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
