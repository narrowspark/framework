<?php
declare(strict_types=1);
namespace Viserio\Bus\Providers;

use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProvider;
use Viserio\Bus\Dispatcher;
use Viserio\Contracts\Bus\Dispatcher as DispatcherContract;

class BusServiceProvider implements ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function getServices()
    {
        return [
            Dispatcher::class => [self::class, 'registerBusDispatcher'],
            DispatcherContract::class => function(ContainerInterface $container) {
                return $container->get(Dispatcher::class);
            }
        ];
    }

    public static function registerBusDispatcher(ContainerInterface $container)
    {
        return new Dispatcher($container);
    }
}
