<?php
declare(strict_types=1);
namespace Viserio\Bridge\Doctrine\Tests\Provider;

use Doctrine\Common\EventManager;
use Doctrine\DBAL\Configuration;
use PHPUnit\Framework\TestCase;
use Viserio\Bridge\Doctrine\DBAL\Connection;
use Viserio\Bridge\Doctrine\DBAL\ConnectionManager;
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
                        'default' => 'mysql'
                    ],
                ],
            ],
        ]);

        self::assertInstanceOf(Configuration::class, $container->get(Configuration::class));
        self::assertInstanceOf(EventManager::class, $container->get(EventManager::class));
        self::assertInstanceOf(ConnectionManager::class, $container->get(ConnectionManager::class));
        self::assertInstanceOf(Connection::class, $container->get(Connection::class));
        self::assertInstanceOf(Connection::class, $container->get('db'));
        self::assertInstanceOf(Connection::class, $container->get('database'));
    }
}
