<?php
declare(strict_types=1);
namespace Viserio\Foundation\Tests\Providers;

use Mockery as Mock;
use Narrowspark\TestingHelper\Traits\MockeryTrait;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Config\Providers\ConfigServiceProvider;
use Viserio\Container\Container;
use Viserio\Contracts\Config\Repository as RepositoryContract;
use Viserio\Contracts\Routing\Route as RouteContract;
use Viserio\Contracts\Routing\Router as RouterContract;
use Viserio\Contracts\WebProfiler\WebProfiler as WebProfilerContract;
use Viserio\Foundation\Providers\FoundationDataCollectorsServiceProvider;
use Viserio\HttpFactory\Providers\HttpFactoryServiceProvider;
use Viserio\WebProfiler\Providers\WebProfilerServiceProvider;

class FoundationDataCollectorsServiceProviderTest extends TestCase
{
    use MockeryTrait;

    public function tearDown()
    {
        parent::tearDown();

        $this->allowMockingNonExistentMethods(true);

        // Verify Mockery expectations.
        Mock::close();
    }

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
        $container->register(new HttpFactoryServiceProvider());
        $container->register(new ConfigServiceProvider());
        $container->register(new WebProfilerServiceProvider());
        $container->register(new FoundationDataCollectorsServiceProvider());

        $container->get(RepositoryContract::class)->set('webprofiler', [
            'collector' => [
                'narrowspark' => true,
                'viserio'     => [
                    'http' => true,
                ],
                'files' => true,
            ],
        ])->set('path.base', '/');

        static::assertInstanceOf(WebProfilerContract::class, $container->get(WebProfilerContract::class));

        $profiler = $container->get(WebProfilerContract::class);

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
