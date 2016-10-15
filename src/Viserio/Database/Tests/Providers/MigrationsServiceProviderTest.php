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
use Viserio\Database\Providers\MigrationsServiceProvider;

class MigrationsServiceProviderTest extends \PHPUnit_Framework_TestCase
{
    public function testProvider()
    {
        $container = new Container();
        $container->register(new ConfigServiceProvider());
        $container->register(new DatabaseServiceProvider());
        $container->register(new MigrationsServiceProvider());

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
            'migrations' => [
                'path' => env('DB_MIGRATION_PATH', __DIR__.'/../Stub/'),
                'namespace' => 'Database\\Migrations',
                'name' => 'migration',
                'table_name' => 'migration',
            ]
        ]);

        $this->assertTrue(is_array($container->get('migrations.commands')));
    }

    public function testProviderWithoutConfigManager()
    {
        $container = new Container();
        $container->register(new DatabaseServiceProvider());
        $container->register(new MigrationsServiceProvider());

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
            'migrations' => [
                'path' => env('DB_MIGRATION_PATH', __DIR__.'/../Stub/'),
                'namespace' => 'Database\\Migrations',
                'name' => 'migration',
                'table_name' => 'migration',
            ],
        ]);

        $this->assertTrue(is_array($container->get('migrations.commands')));
    }

    public function testProviderWithoutConfigManagerAndNamespace()
    {
        $container = new Container();
        $container->register(new DatabaseServiceProvider());
        $container->register(new MigrationsServiceProvider());

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
        $container->instance('viserio.database.migrations.options', [
            'migrations' => [
                'path' => env('DB_MIGRATION_PATH', __DIR__.'/../Stub/'),
                'namespace' => 'Database\\Migrations',
                'name' => 'migration',
                'table_name' => 'migration',
            ],
        ]);

        $this->assertTrue(is_array($container->get('migrations.commands')));
    }
}
