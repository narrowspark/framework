<?php
declare(strict_types=1);
namespace Viserio\Component\Routing\Providers;

use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProvider;
use Interop\Http\Factory\UriFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Component\Contracts\Events\EventManager as EventManagerContract;
use Viserio\Component\Contracts\Routing\Router as RouterContract;
use Viserio\Component\Contracts\Routing\UrlGenerator as UrlGeneratorContract;
use Viserio\Component\Routing\Generator\UrlGenerator;
use Viserio\Component\Routing\Router;

class RoutingServiceProvider implements ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function getServices()
    {
        return [
            RouterContract::class => [self::class, 'createRouter'],
            'route' => function (ContainerInterface $container) {
                return $container->get(Router::class);
            },
            'router'              => function (ContainerInterface $container) {
                return $container->get(RouterContract::class);
            },
            Router::class => function (ContainerInterface $container) {
                return $container->get(RouterContract::class);
            },
            UrlGeneratorContract::class => [self::class, 'createUrlGenerator'],
            UrlGenerator::class         => function (ContainerInterface $container) {
                return $container->get(UrlGeneratorContract::class);
            },
        ];
    }

    public static function createRouter(ContainerInterface $container): Router
    {
        $router = new Router($container);

        if ($container->has(EventManagerContract::class)) {
            $router->setEventManager($container->get(EventManagerContract::class));
        }

        return $router;
    }

    public static function createUrlGenerator(ContainerInterface $container): UrlGenerator
    {
        return new UrlGenerator(
            $container->get(RouterContract::class)->getRoutes(),
            $container->get(ServerRequestInterface::class),
            $container->get(UriFactoryInterface::class)
        );
    }
}
