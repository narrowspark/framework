<?php
declare(strict_types=1);
namespace Viserio\Routing\Providers;

use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProvider;
use Viserio\Contracts\Events\Dispatcher as DispatcherContract;
use Viserio\Contracts\Routing\Router as RouterContract;
use Viserio\Routing\Router;

class RoutingServiceProvider implements ServiceProvider
{
    const PACKAGE = 'viserio.routing';

    /**
     * {@inheritdoc}
     */
    public function getServices()
    {
        return [
            RouterContract::class => [self::class, 'createRouter'],
            'router' => function (ContainerInterface $container) {
                return $container->get(RouterContract::class);
            },
            'route' => function (ContainerInterface $container) {
                return $container->get(Router::class);
            },
            Router::class => function (ContainerInterface $container) {
                return $container->get(RouterContract::class);
            },
        ];
    }

    public static function createRouter(ContainerInterface $container): Router
    {
        $router = new Router($container);

        if ($container->has(DispatcherContract::class)) {
            $router->setEventsDispatcher($container->get(DispatcherContract::class));
        }

        return $router;
    }
}
