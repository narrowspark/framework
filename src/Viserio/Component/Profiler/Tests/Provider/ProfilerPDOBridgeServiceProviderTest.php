<?php
declare(strict_types=1);
namespace Viserio\Component\Profiler\Tests\Provider;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use PDO;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Component\Container\Container;
use Viserio\Component\Contract\Profiler\Profiler as ProfilerContract;
use Viserio\Component\HttpFactory\Provider\HttpFactoryServiceProvider;
use Viserio\Component\Profiler\DataCollector\Bridge\PDO\TraceablePDODecorater;
use Viserio\Component\Profiler\Provider\ProfilerPDOBridgeServiceProvider;
use Viserio\Component\Profiler\Provider\ProfilerServiceProvider;

/**
 * @internal
 */
final class ProfilerPDOBridgeServiceProviderTest extends MockeryTestCase
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

        $this->assertInstanceOf(ProfilerContract::class, $container->get(ProfilerContract::class));
        $this->assertInstanceOf(TraceablePDODecorater::class, $container->get(PDO::class));
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
