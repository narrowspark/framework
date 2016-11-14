<?php
declare(strict_types=1);
namespace Viserio\Config\Providers;

use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProvider;
use Viserio\Config\Manager as ConfigManager;
use Viserio\Contracts\Config\Repository as RepositoryContract;
use Viserio\Config\Repository;
use Viserio\Contracts\Parsers\Loader as LoaderContract;
use Viserio\Contracts\Config\Manager as ManagerContract;

class ConfigServiceProvider implements ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function getServices()
    {
        return [
            RepositoryContract::class => [self::class, 'createRepository'],
            Repository::class => function (ContainerInterface $container) {
                return $container->get(RepositoryContract::class);
            },
            ManagerContract::class => [self::class, 'createConfigManager'],
            ConfigManager::class => function (ContainerInterface $container) {
                return $container->get(ManagerContract::class);
            },
            'config' => function (ContainerInterface $container) {
                return $container->get(ManagerContract::class);
            },
        ];
    }

    public static function createConfigManager(ContainerInterface $container): ConfigManager
    {
        return new ConfigManager($container->get(Repository::class));
    }

    public static function createRepository(): Repository
    {
        return new Repository();
    }
}
