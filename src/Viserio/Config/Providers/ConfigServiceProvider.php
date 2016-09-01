<?php
declare(strict_types=1);
namespace Viserio\Config\Providers;

use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProvider;
use Viserio\Config\Manager as ConfigManager;
use Viserio\Config\Repository;

class ConfigServiceProvider implements ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function getServices()
    {
        return [
            Repository::class => [self::class, 'createRepository'],
            ConfigManager::class => [self::class, 'createConfigManager'],
            'config' => function (ContainerInterface $container) {
                return $container->get(ConfigManager::class);
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
