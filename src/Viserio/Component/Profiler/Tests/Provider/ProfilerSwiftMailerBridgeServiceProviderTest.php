<?php
declare(strict_types=1);
namespace Viserio\Component\Profiler\Tests\Provider;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Psr\Http\Message\ServerRequestInterface;
use Swift_Mailer;
use Swift_SmtpTransport;
use Viserio\Component\Container\Container;
use Viserio\Component\Contracts\Profiler\Profiler as ProfilerContract;
use Viserio\Component\HttpFactory\Provider\HttpFactoryServiceProvider;
use Viserio\Component\Profiler\Provider\ProfilerServiceProvider;
use Viserio\Component\Profiler\Provider\ProfilerSwiftMailerBridgeServiceProvider;

class ProfilerSwiftMailerBridgeServiceProviderTest extends MockeryTestCase
{
    public function testProvider(): void
    {
        $container = new Container();
        $container->instance(ServerRequestInterface::class, $this->getRequest());
        $container->instance(Swift_Mailer::class, new Swift_Mailer(new Swift_SmtpTransport('smtp.example.org', 25)));
        $container->register(new HttpFactoryServiceProvider());
        $container->register(new ProfilerServiceProvider());
        $container->register(new ProfilerSwiftMailerBridgeServiceProvider());

        $container->instance('config', ['viserio' => ['profiler' => ['enable' => true]]]);

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
