<?php
declare(strict_types=1);
namespace Viserio\Component\HttpFoundation\Tests\Provider;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Component\Config\Provider\ConfigServiceProvider;
use Viserio\Component\Container\Container;
use Viserio\Component\Contract\Config\Repository as RepositoryContract;
use Viserio\Component\Contract\Foundation\Kernel as KernelContract;
use Viserio\Component\Contract\Profiler\Profiler as ProfilerContract;
use Viserio\Component\Contract\Routing\Route as RouteContract;
use Viserio\Component\Contract\Routing\Router as RouterContract;
use Viserio\Component\HttpFactory\Provider\HttpFactoryServiceProvider;
use Viserio\Component\HttpFoundation\Provider\FoundationDataCollectorServiceProvider;
use Viserio\Component\Profiler\Provider\ProfilerServiceProvider;

/**
 * @internal
 */
final class FoundationDataCollectorServiceProviderTest extends MockeryTestCase
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
                    'viserio_http' => true,
                ],
            ],
        ]);

        $profiler = $container->get(ProfilerContract::class);

        $this->assertInstanceOf(ProfilerContract::class, $profiler);

        $this->assertArrayHasKey('time-data-collector', $profiler->getCollectors());
        $this->assertArrayHasKey('memory-data-collector', $profiler->getCollectors());
        $this->assertArrayHasKey('viserio-http-data-collector', $profiler->getCollectors());
    }

    /**
     * @return \Mockery\MockInterface|\Psr\Http\Message\ServerRequestInterface
     */
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
