<?php
declare(strict_types=1);
namespace Viserio\Component\Bus\Provider;

use Interop\Container\ServiceProvider;
use Psr\Container\ContainerInterface;
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
            QueueingDispatcherContract::class => [self::class, 'registerBusDispatcher'],
            QueueingDispatcher::class         => function (ContainerInterface $container) {
                return $container->get(QueueingDispatcherContract::class);
            },
            'bus'                             => function (ContainerInterface $container) {
                return $container->get(QueueingDispatcherContract::class);
            },
        ];
    }

    public static function registerBusDispatcher(ContainerInterface $container): QueueingDispatcherContract
    {
        return new QueueingDispatcher($container, function ($connection = null) use ($container) {
            return $container->get(FactoryContract::class)->connection($connection);
        });
    }
}
