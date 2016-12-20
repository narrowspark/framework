<?php
declare(strict_types=1);
namespace Viserio\Events\Providers;

use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProvider;
use Viserio\Contracts\Events\Dispatcher as DispatcherContract;
use Viserio\Events\Dispatcher;

class EventsServiceProvider implements ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function getServices()
    {
        return [
            DispatcherContract::class => [self::class, 'createEventDispatcher'],
            Dispatcher::class         => function (ContainerInterface $container) {
                return $container->get(DispatcherContract::class);
            },
            'events' => function (ContainerInterface $container) {
                return $container->get(DispatcherContract::class);
            },
        ];
    }

    public static function createEventDispatcher(ContainerInterface $container): Dispatcher
    {
        return new Dispatcher($container);
    }
}
