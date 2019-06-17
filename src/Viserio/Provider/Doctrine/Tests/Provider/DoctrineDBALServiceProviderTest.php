<?php
declare(strict_types=1);
namespace Viserio\Provider\Doctrine\Tests\Provider;

use Doctrine\Common\EventManager;
use Doctrine\DBAL\Configuration;
use PHPUnit\Framework\TestCase;
use Viserio\Component\Container\Container;
use Viserio\Provider\Doctrine\Connection;
use Viserio\Provider\Doctrine\ConnectionManager;
use Viserio\Provider\Doctrine\Provider\DoctrineDBALServiceProvider;

/**
 * @internal
 */
final class DoctrineDBALServiceProviderTest extends TestCase
{
    public function testProvider(): void
    {
        $container = new Container();
        $container->register(new DoctrineDBALServiceProvider());

        $container->instance('config', [
            'viserio' => [
                'doctrine' => [
                    'dbal' => [
                        'default' => 'mysql',
                    ],
                ],
            ],
        ]);

        $this->assertInstanceOf(Configuration::class, $container->get(Configuration::class));
        $this->assertInstanceOf(EventManager::class, $container->get(EventManager::class));
        $this->assertInstanceOf(ConnectionManager::class, $container->get(ConnectionManager::class));
        $this->assertInstanceOf(Connection::class, $container->get(Connection::class));
        $this->assertInstanceOf(Connection::class, $container->get('db'));
        $this->assertInstanceOf(Connection::class, $container->get('database'));
    }
}
