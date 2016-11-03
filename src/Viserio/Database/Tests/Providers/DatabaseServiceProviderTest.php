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

class DatabaseServiceProviderTest extends \PHPUnit_Framework_TestCase
{
    public function testProvider()
    {
        $container = new Container();
        $container->register(new ConfigServiceProvider());
        $container->register(new DatabaseServiceProvider());

        $container->get('config')->set('database', [
            'default' => 'mysql',
            'connections' => [
                'mysql' => [
                    'driver' => 'pdo_mysql',
                    'host' => 'DB_HOST',
                    'port' => 'DB_PORT',
                    'database' => 'DB_DATABASE_NAME',
                    'username' => 'DB_DATABASE_USER',
                    'password' => 'DB_DATABASE_PASSWORD',
                    'charset' => 'DB_CHARSET', 'UTF8',
                    'driverOptions' => [1002 => 'SET NAMES utf8'],
                ],
            ],
        ]);

        $this->assertInstanceOf(Configuration::class, $container->get(Configuration::class));
        $this->assertInstanceOf(EventManager::class, $container->get(EventManager::class));
        $this->assertInstanceOf(Connection::class, $container->get(Connection::class));
        $this->assertInstanceOf(Connection::class, $container->get('db'));
        $this->assertInstanceOf(Connection::class, $container->get('database'));
        $this->assertInstanceOf(HelperSet::class, $container->get('database.command.helper'));
        $this->assertTrue(is_array($container->get('database.commands')));
    }

    public function testProviderWithoutConfigManager()
    {
        $container = new Container();
        $container->register(new DatabaseServiceProvider());

        $container->instance('options', [
            'default' => 'mysql',
            'connections' => [
                'mysql' => [
                    'driver' => 'pdo_mysql',
                    'host' => 'DB_HOST',
                    'port' => 'DB_PORT',
                    'database' => 'DB_DATABASE_NAME',
                    'username' => 'DB_DATABASE_USER',
                    'password' => 'DB_DATABASE_PASSWORD',
                    'charset' => 'DB_CHARSET', 'UTF8',
                    'driverOptions' => [1002 => 'SET NAMES utf8'],
                ],
            ],
        ]);

        $this->assertInstanceOf(Connection::class, $container->get(Connection::class));
        $this->assertInstanceOf(Connection::class, $container->get('db'));
        $this->assertInstanceOf(Connection::class, $container->get('database'));
    }

    public function testProviderWithoutConfigManagerAndNamespace()
    {
        $container = new Container();
        $container->register(new DatabaseServiceProvider());

        $container->instance('viserio.database.options', [
            'default' => 'sqlite',
            'connections' => [
                'mysql' => [
                    'driver' => 'pdo_mysql',
                    'host' => 'DB_HOST',
                    'port' => 'DB_PORT',
                    'database' => 'DB_DATABASE_NAME',
                    'username' => 'DB_DATABASE_USER',
                    'password' => 'DB_DATABASE_PASSWORD',
                    'charset' => 'DB_CHARSET', 'UTF8',
                    'driverOptions' => [1002 => 'SET NAMES utf8'],
                ],
                'sqlite' => [
                    'driver' => 'pdo_sqlite',
                    'username' => 'DB_DATABASE_USER',
                    'database' => __DIR__ . '/../Stub/database.sqlite',
                    'memory' => true,
                ],
            ],
        ]);

        $this->assertInstanceOf(Connection::class, $container->get(Connection::class));
        $this->assertInstanceOf(Connection::class, $container->get('db'));
        $this->assertInstanceOf(Connection::class, $container->get('database'));
    }

    public function testDatabaseConnection()
    {
        $container = new Container();
        $container->register(new DatabaseServiceProvider());

        $container->instance('viserio.database.options', [
            'default' => 'sqlite',
            'connections' => [
                'sqlite' => [
                    'driver' => 'pdo_sqlite',
                    'database' => __DIR__ . '/../Stub/database.sqlite',
                    'memory' => true,
                ],
            ],
        ]);

        $conn = $container->get(Connection::class);
        $sql = 'SELECT name FROM text WHERE id = 1';

        $collection = $conn->fetchArray($sql);

        $this->assertInstanceOf(Collection::class, $collection);
        $this->assertSame([0 => 'narrowspark'], $collection->all());

        $collection = $conn->fetchAll($sql);

        $this->assertInstanceOf(Collection::class, $collection);
        $this->assertSame(['name' => 'narrowspark'], $collection->all());

        $collection = $conn->fetchAssoc($sql);

        $this->assertInstanceOf(Collection::class, $collection);
        $this->assertSame(['name' => 'narrowspark'], $collection->all());

        $stmt = $conn->prepare($sql);
        $stmt->execute();

        $collection = $stmt->fetchAll();

        $this->assertInstanceOf(Collection::class, $collection);
        $this->assertSame(['name' => 'narrowspark'], $collection->all());

        $stmt = $conn->query($sql);
        $collection = $stmt->fetch();

        $this->assertInstanceOf(Collection::class, $collection);
        $this->assertSame(['name' => 'narrowspark'], $collection->all());

        $stmt = $conn->query('SELECT name FROM text WHERE id = 2');

        $this->assertFalse($stmt->fetch());
    }
}
