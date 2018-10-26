<?php
declare(strict_types=1);
namespace Viserio\Component\WebServer\Tests\Provider;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\VarDumper\Dumper\ContextProvider\SourceContextProvider;
use Symfony\Component\VarDumper\Server\Connection;
use Symfony\Component\VarDumper\Server\DumpServer;
use Viserio\Component\Container\Container;
use Viserio\Component\Http\ServerRequest;
use Viserio\Component\WebServer\Provider\WebServerServiceProvider;
use Viserio\Component\WebServer\RequestContextProvider;

/**
 * @internal
 */
final class WebServerServiceProviderTest extends MockeryTestCase
{
    public function testProvider(): void
    {
        $container = new Container();
        $container->register(new WebServerServiceProvider());
        $container->instance('config', ['viserio' => []]);
        $container->instance(ServerRequestInterface::class, new ServerRequest('/'));
        $container->instance(SourceContextProvider::class, new SourceContextProvider(null, __DIR__));
        $container->instance(LoggerInterface::class, $this->mock(LoggerInterface::class));

        $this->assertInstanceOf(RequestContextProvider::class, $container->get(RequestContextProvider::class));
        $this->assertInstanceOf(Connection::class, $container->get(Connection::class));
        $this->assertInstanceOf(DumpServer::class, $container->get(DumpServer::class));
    }

    public function testGetDimensions(): void
    {
        $this->assertSame(['viserio', 'webserver'], WebServerServiceProvider::getDimensions());
    }

    public function testGetDefaultOptions(): void
    {
        $this->assertSame(
            [
                'debug_server' => [
                    'host' => 'tcp://127.0.0.1:9912',
                ],
            ],
            WebServerServiceProvider::getDefaultOptions()
        );
    }
}
