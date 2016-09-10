<?php
declare(strict_types=1);
namespace Viserio\Queue\Providers;

use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProvider;
use Viserio\Contracts\Config\Manager as ConfigContract;
use Viserio\Contracts\Encryption\Encrypter as EncrypterContract;
use Viserio\Queue\QueueManager;

class QueueServiceProvider implements ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function getServices()
    {
        return [
            QueueManager::class => [self::class, 'createQueueManager'],
            'queue' => function (ContainerInterface $container) {
                return $container->get(QueueManager::class);
            },
            'queue.connection' => [self::class, 'createQueueConnection'],
        ];
    }

    public static function createQueueManager(ContainerInterface $container): QueueManager
    {
        return new QueueManager(
            $container->get(ConfigContract::class),
            $container,
            $container->get(EncrypterContract::class)
        );
    }

    public static function createQueueConnection(ContainerInterface $container)
    {
        return $container->get(ConfigContract::class)->connection();
    }
}
