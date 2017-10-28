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

class EventsDataCollectorServiceProviderTest extends MockeryTestCase
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

        static::assertTrue(array_key_exists('time-data-collector', $profiler->getCollectors()));
        static::assertTrue(array_key_exists('memory-data-collector', $profiler->getCollectors()));
        static::assertTrue(array_key_exists('viserio-events-data-collector', $profiler->getCollectors()));
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
