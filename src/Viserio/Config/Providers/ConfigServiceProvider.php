<?php
declare(strict_types=1);
namespace Viserio\Config\Providers;

use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProvider;
use Viserio\Config\Manager as ConfigManager;
use Viserio\Config\Repository;

class ConfigServiceProvider extends ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function getServices()
    {
        return [
            ConfigManager::class => [self::class, 'createConfigManager'],
            Repository::class => new Repository(),
        ];
    }

    public static function createConfigManager(ContainerInterface $container): ConfigManager
    {
        return new ConfigManager($container->get(Repository::class));
    }
}
