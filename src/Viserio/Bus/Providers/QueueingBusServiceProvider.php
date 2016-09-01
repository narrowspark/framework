<?php
declare(strict_types=1);
namespace Viserio\Bus\Providers;

use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProvider;
use Viserio\Bus\QueueingDispatcher;
use Viserio\Contracts\Bus\QueueingDispatcher as QueueingDispatcherContract;
use Viserio\Contracts\Queue\Factory as FactoryContract;

class QueueingBusServiceProvider implements ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function getServices()
    {
        return [
            QueueingDispatcher::class => [self::class, 'registerBusDispatcher'],
            QueueingDispatcherContract::class => function (ContainerInterface $container) {
                return $container->get(QueueingDispatcher::class);
            },
        ];
    }

    public static function registerBusDispatcher(ContainerInterface $container)
    {
        return new QueueingDispatcher($container, function ($connection = null) use ($container) {
            return $container->get(FactoryContract::class)->connection($connection);
        });
    }
}
