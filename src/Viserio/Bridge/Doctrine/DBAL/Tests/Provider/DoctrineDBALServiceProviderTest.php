<?php
declare(strict_types=1);
namespace Viserio\Bridge\Doctrine\Tests\Provider;

use Doctrine\Common\EventManager;
use Doctrine\DBAL\Configuration;
use PHPUnit\Framework\TestCase;
use Viserio\Bridge\Doctrine\DBAL\Connection;
use Viserio\Bridge\Doctrine\DBAL\Provider\DoctrineDBALServiceProvider;
use Viserio\Component\Container\Container;

class DoctrineDBALServiceProviderTest extends TestCase
{
    public function testProvider()
    {
        $container = new Container();
        $container->register(new DoctrineDBALServiceProvider());

        $container->instance('config', [
            'viserio' => [
                'doctrine' => [
                    'dbal' => [
                        'default'     => 'mysql',
                        'connections' => [
                            'mysql' => [
                                'driver'        => 'pdo_mysql',
                                'host'          => 'DB_HOST',
                                'port'          => 'DB_PORT',
                                'database'      => 'DB_DATABASE_NAME',
                                'username'      => 'DB_DATABASE_USER',
                                'password'      => 'DB_DATABASE_PASSWORD',
                                'charset'       => 'UTF8',
                                'driverOptions' => [1002 => 'SET NAMES utf8'],
                            ],
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
    }
}
