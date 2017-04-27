<?php
declare(strict_types=1);
namespace Viserio\Component\Routing\Tests\Providers;

use Interop\Http\Factory\UriFactoryInterface;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Component\Container\Container;
use Viserio\Component\Contracts\Routing\UrlGenerator as UrlGeneratorContract;
use Viserio\Component\Events\Providers\EventsServiceProvider;
use Viserio\Component\Routing\Generator\UrlGenerator;
use Viserio\Component\Routing\Providers\RoutingServiceProvider;
use Viserio\Component\Routing\Router;
use Viserio\Component\OptionsResolver\Providers\OptionsResolverServiceProvider;

class RoutingServiceProviderTest extends MockeryTestCase
{
    public function testProvider()
    {
        $container = new Container();
        $container->register(new OptionsResolverServiceProvider());
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

        self::assertInstanceOf(Router::class, $container->get(Router::class));
        self::assertInstanceOf(UrlGeneratorContract::class, $container->get(UrlGeneratorContract::class));
        self::assertInstanceOf(UrlGeneratorContract::class, $container->get(UrlGenerator::class));
        self::assertInstanceOf(Router::class, $container->get('router'));
    }
}
