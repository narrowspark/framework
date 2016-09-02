<?php
declare(strict_types=1);
namespace Viserio\Events\Providers;

use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProvider;
use Viserio\Events\Dispatcher;

class EventsServiceProvider implements ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function getServices()
    {
        return [
            Dispatcher::class => [self::class, 'createEventDispatcher'],
            'events' => function (ContainerInterface $container) {
                return $container->get(Dispatcher::class);
            },
        ];
    }

    public static function createEventDispatcher(ContainerInterface $container): Dispatcher
    {
        return new Dispatcher($container);
    }
}
