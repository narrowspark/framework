<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\Tests\Providers;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Component\Config\Providers\ConfigServiceProvider;
use Viserio\Component\Container\Container;
use Viserio\Component\Contracts\Config\Repository as RepositoryContract;
use Viserio\Component\Contracts\Routing\Route as RouteContract;
use Viserio\Component\Contracts\Routing\Router as RouterContract;
use Viserio\Component\Contracts\Profiler\Profiler as ProfilerContract;
use Viserio\Component\Foundation\Providers\FoundationDataCollectorServiceProvider;
use Viserio\Component\HttpFactory\Providers\HttpFactoryServiceProvider;
use Viserio\Component\OptionsResolver\Providers\OptionsResolverServiceProvider;
use Viserio\Component\Profiler\Providers\ProfilerServiceProvider;

class FoundationDataCollectorServiceProviderTest extends MockeryTestCase
{
    public function testGetServices()
    {
        $serverRequest = $this->mock(ServerRequestInterface::class);
        $route         = $this->mock(RouteContract::class);
        $route->shouldReceive('getServerRequest')
            ->once()
            ->andReturn($serverRequest);
        $router = $this->mock(RouterContract::class);
        $router->shouldReceive('group')
            ->once();
        $router->shouldReceive('getCurrentRoute')
            ->once()
            ->andReturn($route);

        $container = new Container();
        $container->instance(ServerRequestInterface::class, $this->getRequest());
        $container->instance(RouterContract::class, $router);
        $container->register(new OptionsResolverServiceProvider());
        $container->register(new HttpFactoryServiceProvider());
        $container->register(new ConfigServiceProvider());
        $container->register(new ProfilerServiceProvider());
        $container->register(new FoundationDataCollectorServiceProvider());

        $container->get(RepositoryContract::class)->set('viserio', [
            'profiler' => [
                'enable'    => true,
                'collector' => [
                    'narrowspark'  => true,
                    'viserio_http' => true,
                    'files'        => true,
                ],
            ],
        ]);
        $container->get(RepositoryContract::class)->set('path.base', '/');

        $profiler = $container->get(ProfilerContract::class);

        static::assertInstanceOf(ProfilerContract::class, $profiler);

        static::assertTrue(array_key_exists('time-data-collector', $profiler->getCollectors()));
        static::assertTrue(array_key_exists('memory-data-collector', $profiler->getCollectors()));
        static::assertTrue(array_key_exists('narrowspark', $profiler->getCollectors()));
        static::assertTrue(array_key_exists('viserio-http-data-collector', $profiler->getCollectors()));
        static::assertTrue(array_key_exists('files-loaded-collector', $profiler->getCollectors()));
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
