<?php
declare(strict_types=1);
namespace Viserio\Provider\Doctrine\Tests\Provider;

use Narrowspark\Collection\Collection;
use PHPUnit\Framework\TestCase;
use Viserio\Component\Container\Container;
use Viserio\Provider\Doctrine\Connection;
use Viserio\Provider\Doctrine\Provider\DoctrineDBALServiceProvider;

/**
 * @internal
 */
final class DoctrineDBALServiceProviderDatabaseConnectionTest extends TestCase
{
    public function testDatabaseConnection(): void
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

        $stmt       = $conn->query($sql);
        $collection = $stmt->fetch();

        $this->assertInstanceOf(Collection::class, $collection);
        $this->assertSame(['name' => 'narrowspark'], $collection->all());

        $stmt = $conn->query('SELECT name FROM text WHERE id = 2');

        $this->assertFalse($stmt->fetch());
    }
}
