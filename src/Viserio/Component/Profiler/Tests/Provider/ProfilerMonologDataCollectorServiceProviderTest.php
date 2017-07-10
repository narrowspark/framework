<?php
declare(strict_types=1);
namespace Viserio\Component\Profiler\Tests\Provider;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Component\Container\Container;
use Viserio\Component\Contract\Profiler\Profiler as ProfilerContract;
use Viserio\Component\Events\Provider\EventsServiceProvider;
use Viserio\Component\HttpFactory\Provider\HttpFactoryServiceProvider;
use Viserio\Component\Log\Provider\LoggerServiceProvider;
use Viserio\Component\Profiler\Provider\ProfilerMonologDataCollectorServiceProvider;
use Viserio\Component\Profiler\Provider\ProfilerServiceProvider;

class ProfilerMonologDataCollectorServiceProviderTest extends MockeryTestCase
{
    public function testProvider(): void
    {
        $container = new Container();
        $container->instance(ServerRequestInterface::class, $this->getRequest());
        $container->register(new EventsServiceProvider());
        $container->register(new LoggerServiceProvider());
        $container->register(new HttpFactoryServiceProvider());
        $container->register(new ProfilerServiceProvider());
        $container->register(new ProfilerMonologDataCollectorServiceProvider());

        $container->instance('config', [
            'viserio' => [
                'log' => [
                    'env' => 'prod',
                ],
                'profiler' => [
                    'enable'    => true,
                    'collector' => [
                        'logs' => true,
                    ],
                ],
            ],
        ]);

        self::assertInstanceOf(ProfilerContract::class, $container->get(ProfilerContract::class));
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
