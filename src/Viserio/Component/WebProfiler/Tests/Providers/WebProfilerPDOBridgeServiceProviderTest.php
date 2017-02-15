<?php
declare(strict_types=1);
namespace Viserio\Component\WebProfiler\Tests\Providers;

use Mockery as Mock;
use Narrowspark\TestingHelper\Traits\MockeryTrait;
use PDO;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Component\Container\Container;
use Viserio\Component\Contracts\WebProfiler\WebProfiler as WebProfilerContract;
use Viserio\Component\HttpFactory\Providers\HttpFactoryServiceProvider;
use Viserio\Component\OptionsResolver\Providers\OptionsResolverServiceProvider;
use Viserio\Component\WebProfiler\DataCollectors\Bridge\PDO\TraceablePDODecorater;
use Viserio\Component\WebProfiler\Providers\WebProfilerPDOBridgeServiceProvider;
use Viserio\Component\WebProfiler\Providers\WebProfilerServiceProvider;

/**
 * @runTestsInSeparateProcesses
 */
class WebProfilerPDOBridgeServiceProviderTest extends TestCase
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
        $container->instance(PDO::class, new PDO('sqlite:' . __DIR__ . '/../Stub/database.sqlite'));
        $container->instance(ServerRequestInterface::class, $this->getRequest());
        $container->register(new HttpFactoryServiceProvider());
        $container->register(new OptionsResolverServiceProvider());
        $container->register(new WebProfilerServiceProvider());
        $container->register(new WebProfilerPDOBridgeServiceProvider());

        $container->instance('config', ['viserio' => ['webprofiler' => ['enable' => true]]]);

        self::assertInstanceOf(WebProfilerContract::class, $container->get(WebProfilerContract::class));
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
