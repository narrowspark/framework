<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\Tests\Provider;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Component\Config\Provider\ConfigServiceProvider;
use Viserio\Component\Container\Container;
use Viserio\Component\Contract\Config\Repository as RepositoryContract;
use Viserio\Component\Contract\Foundation\Kernel as KernelContract;
use Viserio\Component\Contract\Profiler\Profiler as ProfilerContract;
use Viserio\Component\Contract\Routing\Route as RouteContract;
use Viserio\Component\Contract\Routing\Router as RouterContract;
use Viserio\Component\Foundation\Provider\FoundationDataCollectorServiceProvider;
use Viserio\Component\HttpFactory\Provider\HttpFactoryServiceProvider;
use Viserio\Component\Profiler\Provider\ProfilerServiceProvider;

class FoundationDataCollectorServiceProviderTest extends MockeryTestCase
{
    public function testGetServices(): void
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
        $kernel->shouldReceive('getRootDir')
            ->once()
            ->andReturn('');
        $kernel->shouldReceive('getEnvironment')
            ->once()
            ->andReturn('local');
        $kernel->shouldReceive('isDebug')
            ->once()
            ->andReturn(true);

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

        self::assertArrayHasKey('time-data-collector', $profiler->getCollectors());
        self::assertArrayHasKey('memory-data-collector', $profiler->getCollectors());
        self::assertArrayHasKey('narrowspark', $profiler->getCollectors());
        self::assertArrayHasKey('viserio-http-data-collector', $profiler->getCollectors());
        self::assertArrayHasKey('files-loaded-collector', $profiler->getCollectors());
    }

    private function getRequest()
    {
        $request = $this->mock(ServerRequestInterface::class);
        $request->shouldReceive('getHeaderLine')
            ->with('request_time_float')
            ->andReturn(false);
        $request->shouldReceive('getHeaderLine')
            ->with('request_time')
            ->andReturn(false);

        return $request;
    }
}
