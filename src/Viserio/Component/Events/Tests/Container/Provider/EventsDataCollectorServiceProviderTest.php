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

// namespace Viserio\Component\Events\Tests\Container\Provider;
//
// use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
// use Psr\Http\Message\ServerRequestInterface;
// use Viserio\Component\Container\Container;
// use Viserio\Contract\Profiler\Profiler as ProfilerContract;
// use Viserio\Component\Events\Provider\EventsDataCollectorServiceProvider;
// use Viserio\Component\Events\Provider\EventsServiceProvider;
// use Viserio\Component\HttpFactory\Provider\HttpFactoryServiceProvider;
// use Viserio\Component\Profiler\Provider\ProfilerServiceProvider;
//
///**
// * @internal
// */
// final class EventsDataCollectorServiceProviderTest extends MockeryTestCase
// {
//    public function testProvider(): void
//    {
//        $container = new Container();
//        $container->bind(ServerRequestInterface::class, $this->getRequest());
//        $container->register(new HttpFactoryServiceProvider());
//        $container->register(new ProfilerServiceProvider());
//        $container->register(new EventsServiceProvider());
//        $container->register(new EventsDataCollectorServiceProvider());
//
//        $container->bind(
//            'config',
//            [
//                'viserio' => [
//                    'profiler' => [
//                        'enable'    => true,
//                        'collector' => [
//                            'events' => true,
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
//        $this->assertArrayHasKey('viserio-events-data-collector', $profiler->getCollectors());
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
