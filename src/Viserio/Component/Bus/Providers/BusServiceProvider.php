<?php
declare(strict_types=1);
namespace Viserio\Component\Bus\Providers;

use Interop\Container\ServiceProvider;
use Psr\Container\ContainerInterface;
use Viserio\Component\Bus\Dispatcher;
use Viserio\Component\Contracts\Bus\Dispatcher as DispatcherContract;

class BusServiceProvider implements ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function getServices()
    {
        return [
            DispatcherContract::class => [self::class, 'registerBusDispatcher'],
            Dispatcher::class         => function (ContainerInterface $container) {
                return $container->get(DispatcherContract::class);
            },
        ];
    }

    public static function registerBusDispatcher(ContainerInterface $container): DispatcherContract
    {
        return new Dispatcher($container);
    }
}
