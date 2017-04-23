<?php
declare(strict_types=1);
namespace Viserio\Component\Log\Tests\Providers;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Component\Container\Container;
use Viserio\Component\Contracts\Profiler\Profiler as ProfilerContract;
use Viserio\Component\HttpFactory\Providers\HttpFactoryServiceProvider;
use Viserio\Component\Log\DataCollectors\LogParser;
use Viserio\Component\Log\Providers\LogsDataCollectorServiceProvider;
use Viserio\Component\OptionsResolver\Providers\OptionsResolverServiceProvider;
use Viserio\Component\Profiler\Providers\ProfilerServiceProvider;

class LogsDataCollectorServiceProviderTest extends MockeryTestCase
{
    public function testProvider()
    {
        $container = new Container();
        $container->register(new OptionsResolverServiceProvider());
        $container->instance(ServerRequestInterface::class, $this->getRequest());
        $container->register(new HttpFactoryServiceProvider());
        $container->register(new ProfilerServiceProvider());
        $container->register(new LogsDataCollectorServiceProvider());

        $container->instance('config', [
            'viserio' => [
                'profiler' => [
                    'enable'        => true,
                    'logs_storages' => [__DIR__],
                    'collector'     => [
                        'logs' => true,
                    ],
                ],
            ],
        ]);

        self::assertInstanceOf(LogParser::class, $container->get(LogParser::class));
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
