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
// use Viserio\Contract\Routing\Router as RouterContract;
// use Viserio\Component\Events\Provider\EventsServiceProvider;
// use Viserio\Component\HttpFactory\Provider\HttpFactoryServiceProvider;
// use Viserio\Component\Profiler\AssetsRenderer;
// use Viserio\Component\Profiler\Profiler;
// use Viserio\Component\Profiler\Provider\ProfilerServiceProvider;
// use Viserio\Component\Routing\Provider\RoutingServiceProvider;
//
///**
// * @internal
// */
// final class ProfilerServiceProviderTest extends MockeryTestCase
// {
//    public function testProvider(): void
//    {
//        $container = new Container();
//        $container->bind(ServerRequestInterface::class, $this->getRequest());
//        $container->register(new HttpFactoryServiceProvider());
//        $container->register(new EventsServiceProvider());
//
//        $container->register(new ProfilerServiceProvider());
//
//        $container->bind('config', ['viserio' => ['profiler' => ['enable' => true]]]);
//
//        $this->assertInstanceOf(AssetsRenderer::class, $container->get(AssetsRenderer::class));
//        $this->assertInstanceOf(Profiler::class, $container->get(ProfilerContract::class));
//        $this->assertInstanceOf(ProfilerContract::class, $container->get(ProfilerContract::class));
//    }
//
//    public function testRouteGroups(): void
//    {
//        $container = new Container();
//        $container->bind(ServerRequestInterface::class, $this->getRequest());
//        $container->register(new HttpFactoryServiceProvider());
//        $container->register(new EventsServiceProvider());
//        $container->register(new RoutingServiceProvider());
//        $container->register(new ProfilerServiceProvider());
//
//        $container->bind('config', ['viserio' => ['profiler' => ['enable' => true]]]);
//
//        $router = $container->get(RouterContract::class);
//        $routes = $router->getRoutes()->getRoutes();
//
//        $action1 = $routes[0]->getAction();
//        $action2 = $routes[1]->getAction();
//
//        $this->assertEquals('Viserio\Component\Profiler\Controller\AssetController@css', $action1['controller']);
//        $this->assertEquals('Viserio\Component\Profiler\Controller\AssetController@js', $action2['controller']);
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
