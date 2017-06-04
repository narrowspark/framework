<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\Tests\Providers;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Component\Config\Providers\ConfigServiceProvider;
use Viserio\Component\Container\Container;
use Viserio\Component\Contracts\Config\Repository as RepositoryContract;
use Viserio\Component\Contracts\Foundation\Kernel as KernelContract;
use Viserio\Component\Contracts\Profiler\Profiler as ProfilerContract;
use Viserio\Component\Contracts\Routing\Route as RouteContract;
use Viserio\Component\Contracts\Routing\Router as RouterContract;
use Viserio\Component\Foundation\Providers\FoundationDataCollectorServiceProvider;
use Viserio\Component\HttpFactory\Providers\HttpFactoryServiceProvider;
use Viserio\Component\Profiler\Providers\ProfilerServiceProvider;

class FoundationDataCollectorServiceProviderTest extends MockeryTestCase
{
    public function testGetServices()
    {
        $route  = $this->mock(RouteContract::class);
        $router = $this->mock(RouterContract::class);
        $router->shouldReceive('group')
            ->once();
        $router->shouldReceive('getCurrentRoute')
            ->once()
            ->andReturn($route);

        $kernel = $this->mock(KernelContract::class);
        $kernel->shouldReceive('getRoutesPath')
            ->once()
            ->andReturn('');
        $kernel->shouldReceive('getProjectDir')
            ->once()
            ->andReturn('');

        $container = new Container();
        $container->instance(ServerRequestInterface::class, $this->getRequest());
        $container->instance(RouterContract::class, $router);
        $container->instance(KernelContract::class, $kernel);
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

        $profiler = $container->get(ProfilerContract::class);

        self::assertInstanceOf(ProfilerContract::class, $profiler);

        self::assertTrue(array_key_exists('time-data-collector', $profiler->getCollectors()));
        self::assertTrue(array_key_exists('memory-data-collector', $profiler->getCollectors()));
        self::assertTrue(array_key_exists('narrowspark', $profiler->getCollectors()));
        self::assertTrue(array_key_exists('viserio-http-data-collector', $profiler->getCollectors()));
        self::assertTrue(array_key_exists('files-loaded-collector', $profiler->getCollectors()));
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
