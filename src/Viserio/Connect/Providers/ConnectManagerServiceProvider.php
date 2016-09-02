<?php
declare(strict_types=1);
namespace Viserio\Connect\Providers;

use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProvider;
use Viserio\Config\Manager as ConfigManager;
use Viserio\Connect\ConnectManager;
use Viserio\Contracts\Connect\ConnectManager as ConnectManagerContract;

class ConnectManagerServiceProvider implements ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function getServices()
    {
        return [
            ConnectManager::class => [self::class, 'createConnectManager'],
            ConnectManagerContract::class => function (ContainerInterface $container) {
                return $container->get(ConnectManager::class);
            },
            'connect' => function (ContainerInterface $container) {
                return $container->get(ConnectManager::class);
            },
        ];
    }

    public static function createConnectManager(ContainerInterface $container): ConnectManager
    {
        $connect = new ConnectManager($container->get(ConfigManager::class));
        $connect->setContainer($container);

        return $connect;
    }
}
