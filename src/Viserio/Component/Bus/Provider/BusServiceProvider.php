<?php
declare(strict_types=1);
namespace Viserio\Component\Bus\Provider;

use Interop\Container\ServiceProviderInterface;
use Psr\Container\ContainerInterface;
use Viserio\Component\Bus\Dispatcher;
use Viserio\Component\Contract\Bus\Dispatcher as DispatcherContract;

class BusServiceProvider implements ServiceProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function getFactories(): array
    {
        return [
            DispatcherContract::class => [self::class, 'registerBusDispatcher'],
            Dispatcher::class         => function (ContainerInterface $container) {
                return $container->get(DispatcherContract::class);
            },
            'bus' => function (ContainerInterface $container) {
                return $container->get(DispatcherContract::class);
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
