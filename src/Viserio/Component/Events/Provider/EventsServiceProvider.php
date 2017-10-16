<?php
declare(strict_types=1);
namespace Viserio\Component\Events\Provider;

use Interop\Container\ServiceProviderInterface;
use Psr\Container\ContainerInterface;
use Viserio\Component\Contract\Events\EventManager as EventManagerContract;
use Viserio\Component\Events\EventManager;

class EventsServiceProvider implements ServiceProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function getFactories(): array
    {
        return [
            EventManagerContract::class => function (): EventManager {
                return new EventManager();
            },
            EventManager::class => function (ContainerInterface $container) {
                return $container->get(EventManagerContract::class);
            },
            'events' => function (ContainerInterface $container) {
                return $container->get(EventManagerContract::class);
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
}
