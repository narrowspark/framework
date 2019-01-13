<?php
declare(strict_types=1);
namespace Viserio\Component\Log\Provider;

use Interop\Container\ServiceProviderInterface;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Viserio\Component\Contract\Events\EventManager as EventManagerContract;
use Viserio\Component\Log\LogManager;

class LoggerServiceProvider implements ServiceProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function getFactories(): array
    {
        return [
            LogManager::class => [self::class, 'createLogManger'],
            'log'             => static function (ContainerInterface $container) {
                return $container->get(LogManager::class);
            },
            LoggerInterface::class => static function (ContainerInterface $container) {
                return $container->get(LogManager::class)->getDriver();
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
