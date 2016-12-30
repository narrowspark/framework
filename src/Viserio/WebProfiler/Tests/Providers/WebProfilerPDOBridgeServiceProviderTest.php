<?php
declare(strict_types=1);
namespace Viserio\WebProfiler\Tests\Providers;

use Viserio\Container\Container;
use PDO;
use PDOStatement;
use Viserio\WebProfiler\DataCollectors\Bridge\PDO\PDODataCollector;
use Viserio\WebProfiler\DataCollectors\Bridge\PDO\TraceablePDODecorater;
use Viserio\WebProfiler\DataCollectors\Bridge\PDO\TraceablePDOStatementDecorater;
use Viserio\WebProfiler\Providers\WebProfilerPDOBridgeServiceProvider;
use Viserio\WebProfiler\Providers\WebProfilerServiceProvider;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\HttpFactory\Providers\HttpFactoryServiceProvider;
use Viserio\Contracts\WebProfiler\WebProfiler as WebProfilerContract;
use Mockery as Mock;
use Narrowspark\TestingHelper\Traits\MockeryTrait;

class WebProfilerPDOBridgeServiceProviderTest extends \PHPUnit_Framework_TestCase
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
        $container->register(new WebProfilerServiceProvider());
        $container->register(new WebProfilerPDOBridgeServiceProvider());

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
