<?php
declare(strict_types=1);
namespace Viserio\Component\Queue\Providers;

use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProvider;
use Viserio\Component\Contracts\Config\Repository as RepositoryContract;
use Viserio\Component\Contracts\Encryption\Encrypter as EncrypterContract;
use Viserio\Component\Queue\QueueManager;

class QueueServiceProvider implements ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function getServices()
    {
        return [
            QueueManager::class => [self::class, 'createQueueManager'],
            'queue'             => function (ContainerInterface $container) {
                return $container->get(QueueManager::class);
            },
            'queue.connection' => [self::class, 'createQueueConnection'],
        ];
    }

    public static function createQueueManager(ContainerInterface $container): QueueManager
    {
        return new QueueManager(
            $container,
            $container->get(EncrypterContract::class)
        );
    }

    public static function createQueueConnection(ContainerInterface $container)
    {
        return $container->get(RepositoryContract::class)->connection();
    }
}
