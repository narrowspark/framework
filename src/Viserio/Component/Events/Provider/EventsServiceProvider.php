<?php
declare(strict_types=1);
namespace Viserio\Component\Events\Provider;

use Interop\Container\ServiceProvider;
use Psr\Container\ContainerInterface;
use Viserio\Component\Contracts\Events\EventManager as EventManagerContract;
use Viserio\Component\Events\EventManager;

class EventsServiceProvider implements ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function getServices()
    {
        return [
            EventManagerContract::class => function (): EventManager {
                return new EventManager();
            },
            EventManager::class         => function (ContainerInterface $container) {
                return $container->get(EventManagerContract::class);
            },
            'events' => function (ContainerInterface $container) {
                return $container->get(EventManagerContract::class);
            },
        ];
    }
}
