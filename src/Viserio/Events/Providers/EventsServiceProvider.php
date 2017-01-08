<?php
declare(strict_types=1);
namespace Viserio\Events\Providers;

use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProvider;
use Viserio\Contracts\Events\EventManager as EventManagerContract;
use Viserio\Events\EventManager;

class EventsServiceProvider implements ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function getServices()
    {
        return [
            EventManagerContract::class => [self::class, 'createEventEventManager'],
            EventManager::class         => function (ContainerInterface $container) {
                return $container->get(EventManagerContract::class);
            },
            'events' => function (ContainerInterface $container) {
                return $container->get(EventManagerContract::class);
            },
        ];
    }

    public static function createEventEventManager(): EventManager
    {
        return new EventManager();
    }
}
