<?php
declare(strict_types=1);
namespace Viserio\Component\Log\Provider;

use Psr\Container\ContainerInterface;
use Viserio\Component\Contract\Container\ServiceProvider as ServiceProviderContract;
use Viserio\Component\Contract\Events\EventManager as EventManagerContract;
use Viserio\Component\Log\LogManager;

class LoggerServiceProvider implements ServiceProviderContract
{
    /**
     * {@inheritdoc}
     */
    public function getFactories(): array
    {
        return [
            LogManager::class => [self::class, 'createLogManger'],
            'log'             => function (ContainerInterface $container) {
                return $container->get(LogManager::class);
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
     * Create a log manager instance.
     *
     * @param \Psr\Container\ContainerInterface $container
     *
     * @return \Viserio\Component\Log\LogManager
     */
    public static function createLogManger(ContainerInterface $container): LogManager
    {
        $manager = new LogManager($container->get('config'));

        if ($container->has(EventManagerContract::class)) {
            $manager->setEventManager($container->get(EventManagerContract::class));
        }

        return $manager;
    }
}
