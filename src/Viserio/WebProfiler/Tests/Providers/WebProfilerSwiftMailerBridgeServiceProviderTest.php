<?php
declare(strict_types=1);
namespace Viserio\WebProfiler\Tests\Providers;

use Viserio\Container\Container;
use Swift_Mailer;
use Swift_SmtpTransport;
use Viserio\Contracts\WebProfiler\WebProfiler as WebProfilerContract;
use Viserio\WebProfiler\Providers\WebProfilerSwiftMailerBridgeServiceProvider;
use Mockery as Mock;
use Narrowspark\TestingHelper\Traits\MockeryTrait;
use Viserio\WebProfiler\Providers\WebProfilerServiceProvider;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\HttpFactory\Providers\HttpFactoryServiceProvider;

class WebProfilerSwiftMailerBridgeServiceProviderTest extends \PHPUnit_Framework_TestCase
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
        $container->instance(ServerRequestInterface::class, $this->getRequest());
        $container->instance(Swift_Mailer::class, Swift_Mailer::newInstance(Swift_SmtpTransport::newInstance('smtp.example.org', 25)));
        $container->register(new HttpFactoryServiceProvider());
        $container->register(new WebProfilerServiceProvider());
        $container->register(new WebProfilerSwiftMailerBridgeServiceProvider());

        self::assertInstanceOf(WebProfilerContract::class, $container->get(WebProfilerContract::class));
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
