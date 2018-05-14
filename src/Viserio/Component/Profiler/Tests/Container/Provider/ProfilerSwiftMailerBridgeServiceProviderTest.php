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
// use Swift_Mailer;
// use Swift_SmtpTransport;
// use Viserio\Component\Container\Container;
// use Viserio\Contract\Profiler\Profiler as ProfilerContract;
// use Viserio\Component\HttpFactory\Provider\HttpFactoryServiceProvider;
// use Viserio\Component\Profiler\Provider\ProfilerServiceProvider;
// use Viserio\Component\Profiler\Provider\ProfilerSwiftMailerBridgeServiceProvider;
//
///**
// * @internal
// */
// final class ProfilerSwiftMailerBridgeServiceProviderTest extends MockeryTestCase
// {
//    public function testProvider(): void
//    {
//        $container = new Container();
//        $container->bind(ServerRequestInterface::class, $this->getRequest());
//        $container->bind(Swift_Mailer::class, new Swift_Mailer(new Swift_SmtpTransport('smtp.example.org', 25)));
//        $container->register(new HttpFactoryServiceProvider());
//        $container->register(new ProfilerServiceProvider());
//        $container->register(new ProfilerSwiftMailerBridgeServiceProvider());
//
//        $container->bind('config', ['viserio' => ['profiler' => ['enable' => true]]]);
//
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
