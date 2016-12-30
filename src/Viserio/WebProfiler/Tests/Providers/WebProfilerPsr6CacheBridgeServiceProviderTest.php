<?php
declare(strict_types=1);
namespace Viserio\WebProfiler\Tests\Providers;

use Viserio\Container\Container;
use Psr\Cache\CacheItemPoolInterface;
use Viserio\WebProfiler\DataCollectors\Bridge\Cache\TraceableCacheItemDecorater;
use Mockery as Mock;
use Narrowspark\TestingHelper\Traits\MockeryTrait;
use Viserio\WebProfiler\Providers\WebProfilerPsr6CacheBridgeServiceProvider;
use Viserio\WebProfiler\Providers\WebProfilerServiceProvider;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\HttpFactory\Providers\HttpFactoryServiceProvider;
use Viserio\Contracts\WebProfiler\WebProfiler as WebProfilerContract;

class WebProfilerPsr6CacheBridgeServiceProviderTest extends \PHPUnit_Framework_TestCase
{
    use MockeryTrait;

    public function tearDown()
    {
        parent::tearDown();

        $this->allowMockingNonExistentMethods(true);

        // Verify Mockery expectations.
        Mock::close();
    }

    public function testProvider()
    {
        $container = new Container();
        $container->instance(CacheItemPoolInterface::class, $this->mock(CacheItemPoolInterface::class));
        $container->instance(ServerRequestInterface::class, $this->getRequest());
        $container->register(new HttpFactoryServiceProvider());
        $container->register(new WebProfilerServiceProvider());
        $container->register(new WebProfilerPsr6CacheBridgeServiceProvider());

        self::assertInstanceOf(WebProfilerContract::class, $container->get(WebProfilerContract::class));
        self::assertInstanceOf(TraceableCacheItemDecorater::class, $container->get(CacheItemPoolInterface::class));
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
