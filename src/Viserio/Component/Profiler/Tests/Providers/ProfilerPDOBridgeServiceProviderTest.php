<?php
declare(strict_types=1);
namespace Viserio\Component\Profiler\Tests\Providers;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use PDO;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Component\Container\Container;
use Viserio\Component\Contracts\Profiler\Profiler as ProfilerContract;
use Viserio\Component\HttpFactory\Providers\HttpFactoryServiceProvider;
use Viserio\Component\OptionsResolver\Providers\OptionsResolverServiceProvider;
use Viserio\Component\Profiler\DataCollectors\Bridge\PDO\TraceablePDODecorater;
use Viserio\Component\Profiler\Providers\ProfilerPDOBridgeServiceProvider;
use Viserio\Component\Profiler\Providers\ProfilerServiceProvider;

class ProfilerPDOBridgeServiceProviderTest extends MockeryTestCase
{
    public function testProvider()
    {
        $container = new Container();
        $container->instance(PDO::class, new PDO('sqlite:' . __DIR__ . '/../Stub/database.sqlite'));
        $container->instance(ServerRequestInterface::class, $this->getRequest());
        $container->register(new HttpFactoryServiceProvider());
        $container->register(new OptionsResolverServiceProvider());
        $container->register(new ProfilerServiceProvider());
        $container->register(new ProfilerPDOBridgeServiceProvider());

        $container->instance('config', ['viserio' => ['profiler' => ['enable' => true]]]);

        self::assertInstanceOf(ProfilerContract::class, $container->get(ProfilerContract::class));
        self::assertInstanceOf(TraceablePDODecorater::class, $container->get(PDO::class));
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
