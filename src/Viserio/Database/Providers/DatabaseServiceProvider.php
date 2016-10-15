<?php
declare(strict_types=1);
namespace Viserio\Database\Providers;

use Doctrine\Common\EventManager;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\DriverManager;
use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProvider;
use Viserio\Config\Manager as ConfigManager;
use Viserio\Database\Connection;

class DatabaseServiceProvider implements ServiceProvider
{
    const PACKAGE = 'viserio.database';

    /**
     * {@inheritdoc}
     */
    public function getServices()
    {
        return [
            Connection::class => [self::class, 'createConnection'],
            Configuration::class => [self::class, 'createConfiguration'],
            EventManager::class => [self::class, 'createEventManager'],
            'db' => function (ContainerInterface $container) {
                return $container->get(Connection::class);
            },
            'database' => function (ContainerInterface $container) {
                return $container->get(Connection::class);
            },
        ];
    }

    public static function createConnection(ContainerInterface $container): Connection
    {
        if ($container->has(ConfigManager::class)) {
            $config = $container->get(ConfigManager::class)->get('database');
        } else {
            $config = self::get($container, 'options');
        }

        return DriverManager::getConnection(
            self::parseConfig($config),
            $container->get(Configuration::class),
            $container->get(EventManager::class)
        );
    }

    public static function createConfiguration(): Configuration
    {
        return new Configuration();
    }

    public static function createEventManager(): EventManager
    {
        return new EventManager();
    }

    private static function parseConfig($config): array
    {
        $connections = $config['connections'][$config['default']];
        $config = array_merge($config, $connections);

        if (strpos($config['default'], 'sqlite') === false) {
            $config['user'] = $connections['username'];
            $config['dbname'] = $connections['database'];

            if (empty($config['dbname'])) {
                throw new DBALException('The "database" must be set in the config or container entry "database"');
            }
        } else {
            if (isset($connections['username'])) {
                $config['user'] = $connections['username'];
            }

            $config['path'] = $connections['database'];
        }

        unset($config['default'], $config['connections'], $config['username'], $config['database']);

        $config['wrapperClass'] = $config['wrapperClass'] ?? Connection::class;

        return $config;
    }

    /**
     * Returns the entry named PACKAGE.$name, of simply $name if PACKAGE.$name is not found.
     *
     * @param ContainerInterface $container
     * @param string             $name
     *
     * @return mixed
     */
    private static function get(ContainerInterface $container, string $name, $default = null)
    {
        $namespacedName = self::PACKAGE . '.' . $name;

        return $container->has($namespacedName) ? $container->get($namespacedName) :
            ($container->has($name) ? $container->get($name) : $default);
    }
}
