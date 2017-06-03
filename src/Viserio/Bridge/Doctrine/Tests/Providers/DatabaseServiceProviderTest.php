<?php
declare(strict_types=1);
namespace Viserio\Bridge\Doctrine\Tests\Providers;

use Doctrine\Common\EventManager;
use Doctrine\DBAL\Configuration;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Helper\HelperSet;
use Viserio\Bridge\Doctrine\Connection;
use Viserio\Bridge\Doctrine\Providers\DatabaseServiceProvider;
use Viserio\Component\Container\Container;

/**
 * @runTestsInSeparateProcesses
 */
class DatabaseServiceProviderTest extends TestCase
{
    public function testProvider()
    {
        $container = new Container();
        $container->register(new DatabaseServiceProvider());

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
}
