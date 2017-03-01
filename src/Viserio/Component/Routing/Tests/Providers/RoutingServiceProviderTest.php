<?php
declare(strict_types=1);
namespace Viserio\Component\Routing\Tests\Providers;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Container\Container;
use Viserio\Component\Events\Providers\EventsServiceProvider;
use Viserio\Component\Routing\Providers\RoutingServiceProvider;
use Viserio\Component\Routing\Router;
use Viserio\Component\Routing\UrlGenerator;
use Viserio\Component\Contracts\Routing\UrlGenerator as UrlGeneratorContract;
use Psr\Http\Message\ServerRequestInterface;
use Interop\Http\Factory\UriFactoryInterface;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;

class RoutingServiceProviderTest extends MockeryTestCase
{
    public function testProvider()
    {
        $container = new Container();
        $container->register(new RoutingServiceProvider());
        $container->register(new EventsServiceProvider());
        $container->instance(ServerRequestInterface::class, $this->mock(ServerRequestInterface::class));
        $container->instance(UriFactoryInterface::class, $this->mock(UriFactoryInterface::class));

        self::assertInstanceOf(Router::class, $container->get(Router::class));
        self::assertInstanceOf(UrlGeneratorContract::class, $container->get(UrlGeneratorContract::class));
        self::assertInstanceOf(UrlGeneratorContract::class, $container->get(UrlGenerator::class));
        self::assertInstanceOf(Router::class, $container->get('router'));
    }
}
