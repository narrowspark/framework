<?php
declare(strict_types=1);
namespace Viserio\Bridge\Doctrine\Tests\Provider;

use Narrowspark\Collection\Collection;
use PHPUnit\Framework\TestCase;
use Viserio\Bridge\Doctrine\DBAL\Connection;
use Viserio\Bridge\Doctrine\DBAL\Provider\DoctrineDBALServiceProvider;
use Viserio\Component\Container\Container;

class DoctrineDBALServiceProviderDatabaseConnectionTest extends TestCase
{
    public function testDatabaseConnection()
    {
        $container = new Container();
        $container->register(new DoctrineDBALServiceProvider());

        $container->instance('config', [
            'viserio' => [
                'doctrine' => [
                    'dbal' => [
                        'default'     => 'sqlite',
                        'connections' => [
                            'sqlite' => [
                                'driver'   => 'pdo_sqlite',
                                'database' => \dirname(__DIR__) . '/Stub/database.sqlite',
                                'memory'   => true,
                            ],
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
