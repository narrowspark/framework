<?php
declare(strict_types=1);
namespace Viserio\Component\Profiler\Tests\Provider;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Component\Container\Container;
use Viserio\Component\Contract\Profiler\Profiler as ProfilerContract;
use Viserio\Component\HttpFactory\Provider\HttpFactoryServiceProvider;
use Viserio\Component\Profiler\DataCollector\Bridge\Cache\TraceableCacheItemDecorator;
use Viserio\Component\Profiler\Provider\ProfilerPsr6Psr16CacheBridgeServiceProvider;
use Viserio\Component\Profiler\Provider\ProfilerServiceProvider;

/**
 * @internal
 */
final class ProfilerPsr6Psr16CacheBridgeServiceProviderTest extends MockeryTestCase
{
    public function testProvider(): void
    {
        $container = new Container();
        $container->instance(CacheItemPoolInterface::class, $this->mock(CacheItemPoolInterface::class));
        $container->instance(ServerRequestInterface::class, $this->getRequest());
        $container->register(new HttpFactoryServiceProvider());
        $container->register(new ProfilerServiceProvider());
        $container->register(new ProfilerPsr6Psr16CacheBridgeServiceProvider());

        $container->instance('config', ['viserio' => ['profiler' => ['enable' => true]]]);

        $this->assertInstanceOf(ProfilerContract::class, $container->get(ProfilerContract::class));
        $this->assertInstanceOf(TraceableCacheItemDecorator::class, $container->get(CacheItemPoolInterface::class));
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
