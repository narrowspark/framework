<?php
declare(strict_types=1);
namespace Viserio\Events\Providers;

use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProvider;
use Viserio\Contracts\Events\EventManager as EventManagerContract;
use Viserio\Events\EventManager;

class EventDataCollectorServiceProvider implements ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function getServices()
    {
        return [
            EventManagerContract::class => [self::class, 'createEventEventManager'],
        ];
    }

    public static function createEventEventManager(ContainerInterface $container): EventManager
    {
        return new EventManager($container);
    }
}
