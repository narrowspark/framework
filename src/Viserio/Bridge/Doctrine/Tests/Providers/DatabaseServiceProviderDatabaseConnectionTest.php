<?php
declare(strict_types=1);
namespace Viserio\Bridge\Doctrine\Tests\Providers;

use Narrowspark\Collection\Collection;
use PHPUnit\Framework\TestCase;
use Viserio\Bridge\Doctrine\Connection;
use Viserio\Bridge\Doctrine\Providers\DatabaseServiceProvider;
use Viserio\Component\Container\Container;

/**
 * @runTestsInSeparateProcesses
 */
class DatabaseServiceProviderDatabaseConnectionTest extends TestCase
{
    public function testDatabaseConnection()
    {
        $container = new Container();
        $container->register(new DatabaseServiceProvider());

        $container->instance('config', [
            'viserio' => [
                'doctrine' => [
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
                            'database' => __DIR__ . '/../Stub/database.sqlite',
                            'memory'   => true,
                        ],
                    ],
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
