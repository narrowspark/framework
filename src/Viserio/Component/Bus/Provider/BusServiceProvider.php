<?php
declare(strict_types=1);
namespace Viserio\Component\Bus\Provider;

use Interop\Container\ServiceProvider;
use Psr\Container\ContainerInterface;
use Viserio\Component\Bus\Dispatcher;
use Viserio\Component\Contract\Bus\Dispatcher as DispatcherContract;

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
            'bus'                     => function (ContainerInterface $container) {
                return $container->get(DispatcherContract::class);
            },
        ];
    }

    /**
     * Create a new Bus instance.
     *
     * @param \Psr\Container\ContainerInterface $container
     *
     * @return \Viserio\Component\Contract\Bus\Dispatcher
     */
    public static function registerBusDispatcher(ContainerInterface $container): DispatcherContract
    {
        return new Dispatcher($container);
    }
}
