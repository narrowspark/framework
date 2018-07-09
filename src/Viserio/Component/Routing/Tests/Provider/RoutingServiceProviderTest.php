<?php
declare(strict_types=1);
namespace Viserio\Component\Routing\Tests\Provider;

use Interop\Http\Factory\UriFactoryInterface;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Component\Container\Container;
use Viserio\Component\Contract\Routing\UrlGenerator as UrlGeneratorContract;
use Viserio\Component\Events\Provider\EventsServiceProvider;
use Viserio\Component\Routing\Generator\UrlGenerator;
use Viserio\Component\Routing\Provider\RoutingServiceProvider;
use Viserio\Component\Routing\Router;

/**
 * @internal
 */
final class RoutingServiceProviderTest extends MockeryTestCase
{
    public function testProvider(): void
    {
        $container = new Container();
        $container->register(new RoutingServiceProvider());
        $container->register(new EventsServiceProvider());
        $container->instance(ServerRequestInterface::class, $this->mock(ServerRequestInterface::class));
        $container->instance(UriFactoryInterface::class, $this->mock(UriFactoryInterface::class));

        $container->instance('config', [
            'viserio' => [
                'routing' => [
                    'path' => '',
                ],
            ],
        ]);

        static::assertInstanceOf(Router::class, $container->get(Router::class));
        static::assertInstanceOf(UrlGeneratorContract::class, $container->get(UrlGeneratorContract::class));
        static::assertInstanceOf(UrlGeneratorContract::class, $container->get(UrlGenerator::class));
        static::assertInstanceOf(Router::class, $container->get('router'));
    }

    public function testGetUrlGeneratorProvider(): void
    {
        $container = new Container();
        $container->register(new RoutingServiceProvider());
        $container->register(new EventsServiceProvider());
        $container->instance(ServerRequestInterface::class, $this->mock(ServerRequestInterface::class));

        $container->instance('config', [
            'viserio' => [
                'routing' => [
                    'path' => '',
                ],
            ],
        ]);

        static::assertNull($container->get(UrlGeneratorContract::class));
        static::assertNull($container->get(UrlGenerator::class));
    }
}
