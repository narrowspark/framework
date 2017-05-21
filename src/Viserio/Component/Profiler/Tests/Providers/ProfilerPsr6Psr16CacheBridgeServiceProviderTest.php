<?php
declare(strict_types=1);
namespace Viserio\Component\Profiler\Tests\Providers;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Component\Container\Container;
use Viserio\Component\Contracts\Profiler\Profiler as ProfilerContract;
use Viserio\Component\HttpFactory\Providers\HttpFactoryServiceProvider;
use Viserio\Component\OptionsResolver\Providers\OptionsResolverServiceProvider;
use Viserio\Component\Profiler\DataCollectors\Bridge\Cache\TraceableCacheItemDecorator;
use Viserio\Component\Profiler\Providers\ProfilerPsr6Psr16CacheBridgeServiceProvider;
use Viserio\Component\Profiler\Providers\ProfilerServiceProvider;

class ProfilerPsr6Psr16CacheBridgeServiceProviderTest extends MockeryTestCase
{
    public function testProvider()
    {
        $container = new Container();
        $container->instance(CacheItemPoolInterface::class, $this->mock(CacheItemPoolInterface::class));
        $container->instance(ServerRequestInterface::class, $this->getRequest());
        $container->register(new HttpFactoryServiceProvider());
        $container->register(new OptionsResolverServiceProvider());
        $container->register(new ProfilerServiceProvider());
        $container->register(new ProfilerPsr6Psr16CacheBridgeServiceProvider());

        $container->instance('config', ['viserio' => ['profiler' => ['enable' => true]]]);

        self::assertInstanceOf(ProfilerContract::class, $container->get(ProfilerContract::class));
        self::assertInstanceOf(TraceableCacheItemDecorator::class, $container->get(CacheItemPoolInterface::class));
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
