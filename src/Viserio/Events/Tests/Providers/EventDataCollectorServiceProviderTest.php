<?php
declare(strict_types=1);
namespace Viserio\Events\Tests\Providers;

use Mockery as Mock;
use Narrowspark\TestingHelper\Traits\MockeryTrait;
use PHPUnit\Framework\TestCase;
use Viserio\Container\Container;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\HttpFactory\Providers\HttpFactoryServiceProvider;
use Viserio\Events\Providers\EventDataCollectorServiceProvider;
use Viserio\WebProfiler\Providers\WebProfilerServiceProvider;
use Viserio\Contracts\WebProfiler\WebProfiler as WebProfilerContract;
use Viserio\Events\Providers\EventsServiceProvider;

class EventDataCollectorServiceProviderTest extends TestCase
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
        $container->instance(ServerRequestInterface::class, $this->getRequest());
        $container->instance('options', ['collector' => ['events' => true]]);
        $container->register(new HttpFactoryServiceProvider());
        $container->register(new WebProfilerServiceProvider());
        $container->register(new EventsServiceProvider());
        $container->register(new EventDataCollectorServiceProvider());

        static::assertInstanceOf(WebProfilerContract::class, $container->get(WebProfilerContract::class));

        $profiler = $container->get(WebProfilerContract::class);

        static::assertTrue(array_key_exists('time-data-collector', $profiler->getCollectors()));
        static::assertTrue(array_key_exists('memory-data-collector', $profiler->getCollectors()));
        static::assertTrue(array_key_exists('viserio-event-data-collector', $profiler->getCollectors()));
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
