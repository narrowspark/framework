<?php
declare(strict_types=1);
namespace Viserio\Component\Profiler\Tests\Provider;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use PDO;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Component\Container\Container;
use Viserio\Component\Contracts\Profiler\Profiler as ProfilerContract;
use Viserio\Component\HttpFactory\Provider\HttpFactoryServiceProvider;
use Viserio\Component\Profiler\DataCollector\Bridge\PDO\TraceablePDODecorater;
use Viserio\Component\Profiler\Provider\ProfilerPDOBridgeServiceProvider;
use Viserio\Component\Profiler\Provider\ProfilerServiceProvider;

class ProfilerPDOBridgeServiceProviderTest extends MockeryTestCase
{
    public function testProvider(): void
    {
        $container = new Container();
        $container->instance(PDO::class, new PDO('sqlite:' . __DIR__ . '/../Stub/database.sqlite'));
        $container->instance(ServerRequestInterface::class, $this->getRequest());
        $container->register(new HttpFactoryServiceProvider());
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
