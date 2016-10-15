<?php
declare(strict_types=1);
namespace Viserio\Database\Providers;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Driver;
use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProvider;
use Viserio\Config\Manager as ConfigManager;

class DatabaseProvider implements ServiceProvider
{
    const PACKAGE = 'viserio.database';

    /**
     * {@inheritdoc}
     */
    public function getServices()
    {
        return [
            Connection::class => [self::class, 'createConnection'],
            Driver::class => [self::class, 'getDriver'],
            'db' => function (ContainerInterface $container) {
                return $container->get(Connection::class);
            },
            'database' => function (ContainerInterface $container) {
                return $container->get(Connection::class);
            },
        ];
    }

    public static function createConnection(ContainerInterface $container) : Connection
    {
        if ($container->has(ConfigManager::class)) {
            $config = $container->get(ConfigManager::class)->get('database');
        } else {
            $config = self::get($container, 'options');
        }

        if (isset($config['dbal.dbname']) || empty($config['dbal.dbname'])) {
            throw new DBALException('The "dbname" must be set in the container entry "dbal.dbname"');
        }

        $driver = $container->get(Driver::class);
        $connection = new Connection($config, $driver);

        return $connection;
    }

    public static function getDriver():Driver
    {
        return new Driver\PDOMySql\Driver();
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
