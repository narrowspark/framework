<?php
declare(strict_types=1);
namespace Viserio\Database\Tests\Providers;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Driver;
use Viserio\Config\Providers\ConfigServiceProvider;
use Viserio\Container\Container;
use Viserio\Database\Providers\DatabaseServiceProvider;

class DatabaseServiceProviderTest extends \PHPUnit_Framework_TestCase
{
    public function testProvider()
    {
        $container = new Container();
        $container->register(new ConfigServiceProvider());
        $container->register(new DatabaseServiceProvider());

        $container->get('config')->set('database', [
            'dbal.host' => 'DB_HOST',
            'dbal.user' => 'DB_DATABASE_USER',
            'dbal.password' => 'DB_DATABASE_PASSWORD',
            'dbal.port' => 'DB_PORT',
            'dbal.dbname' => 'DB_DATABASE_NAME',
            'dbal.charset' => 'DB_CHARSET', 'utf8',
            'dbal.driverOptions' => [1002 => 'SET NAMES utf8'],
        ]);

        $this->assertInstanceOf(Driver\PDOMySql\Driver::class, $container->get(Driver::class));
        $this->assertInstanceOf(Connection::class, $container->get(Connection::class));
        $this->assertInstanceOf(Connection::class, $container->get('db'));
        $this->assertInstanceOf(Connection::class, $container->get('database'));
    }

    public function testProviderWithoutConfigManager()
    {
        $container = new Container();
        $container->register(new DatabaseServiceProvider());

        $container->instance('options', [
            'dbal.host' => 'DB_HOST',
            'dbal.user' => 'DB_DATABASE_USER',
            'dbal.password' => 'DB_DATABASE_PASSWORD',
            'dbal.port' => 'DB_PORT',
            'dbal.dbname' => 'DB_DATABASE_NAME',
            'dbal.charset' => 'DB_CHARSET', 'utf8',
            'dbal.driverOptions' => [1002 => 'SET NAMES utf8'],
        ]);

        $this->assertInstanceOf(Driver\PDOMySql\Driver::class, $container->get(Driver::class));
        $this->assertInstanceOf(Connection::class, $container->get(Connection::class));
        $this->assertInstanceOf(Connection::class, $container->get('db'));
        $this->assertInstanceOf(Connection::class, $container->get('database'));
    }

    public function testProviderWithoutConfigManagerAndNamespace()
    {
        $container = new Container();
        $container->register(new DatabaseServiceProvider());

        $container->instance('viserio.database.options', [
            'dbal.host' => 'DB_HOST',
            'dbal.user' => 'DB_DATABASE_USER',
            'dbal.password' => 'DB_DATABASE_PASSWORD',
            'dbal.port' => 'DB_PORT',
            'dbal.dbname' => 'DB_DATABASE_NAME',
            'dbal.charset' => 'DB_CHARSET', 'utf8',
            'dbal.driverOptions' => [1002 => 'SET NAMES utf8'],
        ]);

        $this->assertInstanceOf(Driver\PDOMySql\Driver::class, $container->get(Driver::class));
        $this->assertInstanceOf(Connection::class, $container->get(Connection::class));
        $this->assertInstanceOf(Connection::class, $container->get('db'));
        $this->assertInstanceOf(Connection::class, $container->get('database'));
    }
}
