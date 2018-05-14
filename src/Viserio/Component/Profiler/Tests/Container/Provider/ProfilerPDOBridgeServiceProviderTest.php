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
// use PDO;
// use Psr\Http\Message\ServerRequestInterface;
// use Viserio\Component\Container\Container;
// use Viserio\Contract\Profiler\Profiler as ProfilerContract;
// use Viserio\Component\HttpFactory\Provider\HttpFactoryServiceProvider;
// use Viserio\Component\Profiler\DataCollector\Bridge\PDO\TraceablePDODecorater;
// use Viserio\Component\Profiler\Provider\ProfilerPDOBridgeServiceProvider;
// use Viserio\Component\Profiler\Provider\ProfilerServiceProvider;
//
///**
// * @internal
// */
// final class ProfilerPDOBridgeServiceProviderTest extends MockeryTestCase
// {
//    public function testProvider(): void
//    {
//        $container = new Container();
//        $container->bind(PDO::class, new PDO('sqlite:' . \dirname(__DIR__) . \DIRECTORY_SEPARATOR . 'Stub' . \DIRECTORY_SEPARATOR . 'database.sqlite'));
//        $container->bind(ServerRequestInterface::class, $this->getRequest());
//        $container->register(new HttpFactoryServiceProvider());
//        $container->register(new ProfilerServiceProvider());
//        $container->register(new ProfilerPDOBridgeServiceProvider());
//
//        $container->bind('config', ['viserio' => ['profiler' => ['enable' => true]]]);
//
//        $this->assertInstanceOf(ProfilerContract::class, $container->get(ProfilerContract::class));
//        $this->assertInstanceOf(TraceablePDODecorater::class, $container->get(PDO::class));
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
