<?php
declare(strict_types=1);
namespace Viserio\Component\Events\Tests\Provider;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Component\Container\Container;
use Viserio\Component\Contract\Profiler\Profiler as ProfilerContract;
use Viserio\Component\Events\Provider\EventsDataCollectorServiceProvider;
use Viserio\Component\Events\Provider\EventsServiceProvider;
use Viserio\Component\HttpFactory\Provider\HttpFactoryServiceProvider;
use Viserio\Component\Profiler\Provider\ProfilerServiceProvider;

/**
 * @internal
 */
final class EventsDataCollectorServiceProviderTest extends MockeryTestCase
{
    public function testProvider(): void
    {
        $container = new Container();
        $container->instance(ServerRequestInterface::class, $this->getRequest());
        $container->register(new HttpFactoryServiceProvider());
        $container->register(new ProfilerServiceProvider());
        $container->register(new EventsServiceProvider());
        $container->register(new EventsDataCollectorServiceProvider());

        $container->instance(
            'config',
            [
                'viserio' => [
                    'profiler' => [
                        'enable'    => true,
                        'collector' => [
                            'events' => true,
                        ],
                    ],
                ],
            ]
        );

        $profiler = $container->get(ProfilerContract::class);

        static::assertInstanceOf(ProfilerContract::class, $profiler);

        static::assertArrayHasKey('time-data-collector', $profiler->getCollectors());
        static::assertArrayHasKey('memory-data-collector', $profiler->getCollectors());
        static::assertArrayHasKey('viserio-events-data-collector', $profiler->getCollectors());
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
