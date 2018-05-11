<?php
declare(strict_types=1);
namespace Viserio\Component\Log\Tests\Provider;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Component\Container\Container;
use Viserio\Component\Contract\Profiler\Profiler as ProfilerContract;
use Viserio\Component\Events\Provider\EventsServiceProvider;
use Viserio\Component\HttpFactory\Provider\HttpFactoryServiceProvider;
use Viserio\Component\Log\Logger;
use Viserio\Component\Log\LogManager;
use Viserio\Component\Log\Provider\LoggerDataCollectorServiceProvider;
use Viserio\Component\Log\Provider\LoggerServiceProvider;
use Viserio\Component\Profiler\Provider\ProfilerServiceProvider;

class LoggerDataCollectorServiceProviderTest extends MockeryTestCase
{
    public function testProvider(): void
    {
        $container = new Container();
        $container->instance(ServerRequestInterface::class, $this->getRequest());
        $container->register(new EventsServiceProvider());
        $container->register(new LoggerServiceProvider());
        $container->register(new HttpFactoryServiceProvider());
        $container->register(new ProfilerServiceProvider());
        $container->register(new LoggerDataCollectorServiceProvider());

        $container->instance('config', [
            'viserio' => [
                'logging' => [
                    'name' => 'narrowspark',
                    'path' => __DIR__,
                ],
                'profiler' => [
                    'enable'    => true,
                    'collector' => [
                        'logs' => true,
                    ],
                ],
            ],
        ]);

        self::assertInstanceOf(Logger::class, $container->get(LogManager::class)->getDriver());
        self::assertInstanceOf(ProfilerContract::class, $container->get(ProfilerContract::class));
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
