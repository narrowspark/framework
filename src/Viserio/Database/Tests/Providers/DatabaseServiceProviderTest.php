<?php
declare(strict_types=1);
namespace Viserio\Database\Tests\Providers;

use Doctrine\Common\EventManager;
use Doctrine\DBAL\Configuration;
use Narrowspark\Collection\Collection;
use Symfony\Component\Console\Helper\HelperSet;
use Viserio\Config\Providers\ConfigServiceProvider;
use Viserio\Container\Container;
use Viserio\Database\Connection;
use Viserio\Database\Providers\DatabaseServiceProvider;
use PHPUnit\Framework\TestCase;

class DatabaseServiceProviderTest extends TestCase
{
    public function testProvider()
    {
        $container = new Container();
        $container->register(new ConfigServiceProvider());
        $container->register(new DatabaseServiceProvider());

        $container->get('config')->set('database', [
            'default'     => 'mysql',
            'connections' => [
                'mysql' => [
                    'driver'        => 'pdo_mysql',
                    'host'          => 'DB_HOST',
                    'port'          => 'DB_PORT',
                    'database'      => 'DB_DATABASE_NAME',
                    'username'      => 'DB_DATABASE_USER',
                    'password'      => 'DB_DATABASE_PASSWORD',
                    'charset'       => 'DB_CHARSET', 'UTF8',
                    'driverOptions' => [1002 => 'SET NAMES utf8'],
                ],
            ],
        ]);

        self::assertInstanceOf(Configuration::class, $container->get(Configuration::class));
        self::assertInstanceOf(EventManager::class, $container->get(EventManager::class));
        self::assertInstanceOf(Connection::class, $container->get(Connection::class));
        self::assertInstanceOf(Connection::class, $container->get('db'));
        self::assertInstanceOf(Connection::class, $container->get('database'));
        self::assertInstanceOf(HelperSet::class, $container->get('database.command.helper'));
        self::assertTrue(is_array($container->get('database.commands')));
    }

    public function testProviderWithoutConfigManager()
    {
        $container = new Container();
        $container->register(new DatabaseServiceProvider());

        $container->instance('options', [
            'default'     => 'mysql',
            'connections' => [
                'mysql' => [
                    'driver'        => 'pdo_mysql',
                    'host'          => 'DB_HOST',
                    'port'          => 'DB_PORT',
                    'database'      => 'DB_DATABASE_NAME',
                    'username'      => 'DB_DATABASE_USER',
                    'password'      => 'DB_DATABASE_PASSWORD',
                    'charset'       => 'DB_CHARSET', 'UTF8',
                    'driverOptions' => [1002 => 'SET NAMES utf8'],
                ],
            ],
        ]);

        self::assertInstanceOf(Connection::class, $container->get(Connection::class));
        self::assertInstanceOf(Connection::class, $container->get('db'));
        self::assertInstanceOf(Connection::class, $container->get('database'));
    }

    public function testProviderWithoutConfigManagerAndNamespace()
    {
        $container = new Container();
        $container->register(new DatabaseServiceProvider());

        $container->instance('viserio.database.options', [
            'default'     => 'sqlite',
            'connections' => [
                'mysql' => [
                    'driver'        => 'pdo_mysql',
                    'host'          => 'DB_HOST',
                    'port'          => 'DB_PORT',
                    'database'      => 'DB_DATABASE_NAME',
                    'username'      => 'DB_DATABASE_USER',
                    'password'      => 'DB_DATABASE_PASSWORD',
                    'charset'       => 'DB_CHARSET', 'UTF8',
                    'driverOptions' => [1002 => 'SET NAMES utf8'],
                ],
                'sqlite' => [
                    'driver'   => 'pdo_sqlite',
                    'username' => 'DB_DATABASE_USER',
                    'database' => __DIR__ . '/../Stub/database.sqlite',
                    'memory'   => true,
                ],
            ],
        ]);

        self::assertInstanceOf(Connection::class, $container->get(Connection::class));
        self::assertInstanceOf(Connection::class, $container->get('db'));
        self::assertInstanceOf(Connection::class, $container->get('database'));
    }

    public function testDatabaseConnection()
    {
        $container = new Container();
        $container->register(new DatabaseServiceProvider());

        $container->instance('viserio.database.options', [
            'default'     => 'sqlite',
            'connections' => [
                'sqlite' => [
                    'driver'   => 'pdo_sqlite',
                    'database' => __DIR__ . '/../Stub/database.sqlite',
                    'memory'   => true,
                ],
            ],
        ]);

        $conn = $container->get(Connection::class);
        $sql  = 'SELECT name FROM text WHERE id = 1';

        $collection = $conn->fetchArray($sql);

        self::assertInstanceOf(Collection::class, $collection);
        self::assertSame([0 => 'narrowspark'], $collection->all());

        $collection = $conn->fetchAll($sql);

        self::assertInstanceOf(Collection::class, $collection);
        self::assertSame(['name' => 'narrowspark'], $collection->all());

        $collection = $conn->fetchAssoc($sql);

        self::assertInstanceOf(Collection::class, $collection);
        self::assertSame(['name' => 'narrowspark'], $collection->all());

        $stmt = $conn->prepare($sql);
        $stmt->execute();

        $collection = $stmt->fetchAll();

        self::assertInstanceOf(Collection::class, $collection);
        self::assertSame(['name' => 'narrowspark'], $collection->all());

        $stmt       = $conn->query($sql);
        $collection = $stmt->fetch();

        self::assertInstanceOf(Collection::class, $collection);
        self::assertSame(['name' => 'narrowspark'], $collection->all());

        $stmt = $conn->query('SELECT name FROM text WHERE id = 2');

        self::assertFalse($stmt->fetch());
    }
}
