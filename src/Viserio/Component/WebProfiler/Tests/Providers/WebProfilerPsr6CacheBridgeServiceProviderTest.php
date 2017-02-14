<?php
declare(strict_types=1);
namespace Viserio\Component\WebProfiler\Tests\Providers;

use Mockery as Mock;
use Narrowspark\TestingHelper\Traits\MockeryTrait;
use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Component\Container\Container;
use Viserio\Component\Contracts\WebProfiler\WebProfiler as WebProfilerContract;
use Viserio\Component\HttpFactory\Providers\HttpFactoryServiceProvider;
use Viserio\Component\WebProfiler\DataCollectors\Bridge\Cache\TraceableCacheItemDecorater;
use Viserio\Component\WebProfiler\Providers\WebProfilerPsr6CacheBridgeServiceProvider;
use Viserio\Component\WebProfiler\Providers\WebProfilerServiceProvider;
use Viserio\Component\OptionsResolver\Providers\OptionsResolverServiceProvider;

class WebProfilerPsr6CacheBridgeServiceProviderTest extends TestCase
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
        $container->register(new OptionsResolverServiceProvider());
        $container->register(new WebProfilerServiceProvider());
        $container->register(new WebProfilerPsr6CacheBridgeServiceProvider());

        $container->instance('config', ['viserio' => ['webprofiler' => ['enable' => true]]]);

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
