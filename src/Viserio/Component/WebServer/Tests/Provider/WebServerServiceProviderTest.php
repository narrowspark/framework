<?php
declare(strict_types=1);
namespace Viserio\Component\WebServer\Tests\Provider;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\VarDumper\Server\Connection;
use Symfony\Component\VarDumper\Server\DumpServer;
use Viserio\Component\Container\Container;
use Viserio\Component\Http\ServerRequest;
use Viserio\Component\WebServer\Provider\WebServerServiceProvider;
use Viserio\Component\WebServer\RequestContextProvider;

/**
 * @internal
 */
final class WebServerServiceProviderTest extends TestCase
{
    public function testProvider(): void
    {
        $container = new Container();
        $container->register(new WebServerServiceProvider());
        $container->instance('config', ['viserio' => []]);
        $container->instance(ServerRequestInterface::class, new ServerRequest('/'));

        static::assertInstanceOf(RequestContextProvider::class, $container->get(RequestContextProvider::class));
        static::assertInstanceOf(Connection::class, $container->get(Connection::class));
        static::assertInstanceOf(DumpServer::class, $container->get(DumpServer::class));
    }

    public function testGetDimensions(): void
    {
        static::assertSame(['viserio', 'webserver'], WebServerServiceProvider::getDimensions());
    }

    public function testGetDefaultOptions(): void
    {
        static::assertSame(
            [
                'debug_server' => [
                    'host' => 'tcp://127.0.0.1:9912',
                ],
            ],
            WebServerServiceProvider::getDefaultOptions()
        );
    }
}
