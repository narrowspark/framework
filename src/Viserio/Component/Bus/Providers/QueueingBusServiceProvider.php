<?php
declare(strict_types=1);
namespace Viserio\Component\Bus\Providers;

use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProvider;
use Viserio\Component\Bus\QueueingDispatcher;
use Viserio\Component\Contracts\Bus\QueueingDispatcher as QueueingDispatcherContract;
use Viserio\Component\Contracts\Queue\Factory as FactoryContract;

class QueueingBusServiceProvider implements ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function getServices()
    {
        return [
            QueueingDispatcher::class         => [self::class, 'registerBusDispatcher'],
            QueueingDispatcherContract::class => function (ContainerInterface $container) {
                return $container->get(QueueingDispatcher::class);
            },
        ];
    }

    public static function registerBusDispatcher(ContainerInterface $container)
    {
        $bus = new QueueingDispatcher($container, function ($connection = null) use ($container) {
            return $container->get(FactoryContract::class)->connection($connection);
        });
        $bus->setContainer($container);

        return $bus;
    }
}
