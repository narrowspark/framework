<?php
declare(strict_types=1);
namespace Viserio\Component\WebProfiler\Tests\Providers;

use Mockery as Mock;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Psr\Http\Message\ServerRequestInterface;
use Swift_Mailer;
use Swift_SmtpTransport;
use Viserio\Component\Container\Container;
use Viserio\Component\Contracts\WebProfiler\WebProfiler as WebProfilerContract;
use Viserio\Component\HttpFactory\Providers\HttpFactoryServiceProvider;
use Viserio\Component\OptionsResolver\Providers\OptionsResolverServiceProvider;
use Viserio\Component\WebProfiler\Providers\WebProfilerServiceProvider;
use Viserio\Component\WebProfiler\Providers\WebProfilerSwiftMailerBridgeServiceProvider;

class WebProfilerSwiftMailerBridgeServiceProviderTest extends MockeryTestCase
{
    public function testProvider()
    {
        $container = new Container();
        $container->instance(ServerRequestInterface::class, $this->getRequest());
        $container->instance(Swift_Mailer::class, Swift_Mailer::newInstance(Swift_SmtpTransport::newInstance('smtp.example.org', 25)));
        $container->register(new HttpFactoryServiceProvider());
        $container->register(new OptionsResolverServiceProvider());
        $container->register(new WebProfilerServiceProvider());
        $container->register(new WebProfilerSwiftMailerBridgeServiceProvider());

        $container->instance('config', ['viserio' => ['webprofiler' => ['enable' => true]]]);

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
