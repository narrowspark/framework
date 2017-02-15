<?php
declare(strict_types=1);
namespace Viserio\Bridge\Doctrine\Tests\Providers;

use PHPUnit\Framework\TestCase;
use Viserio\Bridge\Doctrine\Providers\DatabaseServiceProvider;
use Viserio\Bridge\Doctrine\Providers\MigrationsServiceProvider;
use Viserio\Component\Container\Container;
use Viserio\Component\OptionsResolver\Providers\OptionsResolverServiceProvider;
use Viserio\Component\Support\Env;

class MigrationsServiceProviderTest extends TestCase
{
    public function testProvider()
    {
        $container = new Container();
        $container->register(new OptionsResolverServiceProvider());
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
