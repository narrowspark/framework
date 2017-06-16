<?php
declare(strict_types=1);
namespace Viserio\Provider\Doctrine\Tests\Provider;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Container\Container;
use Viserio\Component\Support\Env;
use Viserio\Provider\Doctrine\Provider\DatabaseServiceProvider;
use Viserio\Provider\Doctrine\Provider\MigrationsServiceProvider;

class MigrationsServiceProviderTest extends TestCase
{
    public function testProvider()
    {
        $container = new Container();
        $container->register(new DatabaseServiceProvider());
        $container->register(new MigrationsServiceProvider());

        $container->instance('config', [
            'viserio' => [
                'doctrine' => [
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
                    'migrations' => [
                        'path'       => Env::get('DB_MIGRATION_PATH', __DIR__ . '/../Stub/'),
                        'namespace'  => 'Database\\Migrations',
                        'name'       => 'migration',
                        'table_name' => 'migration',
                    ],
                ],
            ],
        ]);

        self::assertTrue(is_array($container->get('migrations.commands')));
    }
}