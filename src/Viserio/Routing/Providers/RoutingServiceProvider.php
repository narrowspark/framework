<?php
declare(strict_types=1);
namespace Viserio\Routing\Providers;

use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProvider;
use Viserio\Config\Manager as ConfigManager;
use Viserio\Contracts\Events\Dispatcher as DispatcherContract;
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
            Router::class => [self::class, 'createRouter'],
            'router' => function (ContainerInterface $container) {
                return $container->get(Router::class);
            },
        ];
    }

    public static function createRouter(ContainerInterface $container): Router
    {
        if ($container->has(ConfigManager::class)) {
            $config = $container->get(ConfigManager::class)->get('routing');
        } else {
            $config = self::get($container, 'options');
        }

        $router = new Router($config['path'], $container);
        $router->setContainer($container);

        if ($container->has(DispatcherContract::class)) {
            $router->setEventsDispatcher($container->get(DispatcherContract::class));
        }

        return $router;
    }

    /**
     * Returns the entry named PACKAGE.$name, of simply $name if PACKAGE.$name is not found.
     *
     * @param ContainerInterface $container
     * @param string             $name
     *
     * @return mixed
     */
    private static function get(ContainerInterface $container, string $name, $default = null)
    {
        $namespacedName = self::PACKAGE . '.' . $name;

        return $container->has($namespacedName) ? $container->get($namespacedName) :
            ($container->has($name) ? $container->get($name) : $default);
    }
}
