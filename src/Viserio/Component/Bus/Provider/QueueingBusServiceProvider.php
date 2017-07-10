<?php
declare(strict_types=1);
namespace Viserio\Component\Bus\Provider;

use Interop\Container\ServiceProviderInterface;
use Psr\Container\ContainerInterface;
use Viserio\Component\Bus\QueueingDispatcher;
use Viserio\Component\Contract\Bus\QueueingDispatcher as QueueingDispatcherContract;
use Viserio\Component\Contract\Queue\Factory as FactoryContract;

class QueueingBusServiceProvider implements ServiceProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function getFactories(): array
    {
        return [
            QueueingDispatcherContract::class => [self::class, 'registerBusQueueingDispatcher'],
            QueueingDispatcher::class         => function (ContainerInterface $container) {
                return $container->get(QueueingDispatcherContract::class);
            },
            'bus'                             => function (ContainerInterface $container) {
                return $container->get(QueueingDispatcherContract::class);
            },
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getExtensions(): array
    {
        return [];
    }

    /**
     * Create a new QueueingDispatcher instance.
     *
     * @param \Psr\Container\ContainerInterface $container
     *
     * @return \Viserio\Component\Contract\Bus\QueueingDispatcher
     */
    public static function registerBusQueueingDispatcher(ContainerInterface $container): QueueingDispatcherContract
    {
        return new QueueingDispatcher($container, function ($connection = null) use ($container) {
            return $container->get(FactoryContract::class)->connection($connection);
        });
    }
}
