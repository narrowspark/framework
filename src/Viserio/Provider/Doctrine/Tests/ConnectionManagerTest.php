<?php
declare(strict_types=1);
namespace Viserio\Provider\Doctrine\Tests;

use Doctrine\Common\EventManager;
use Doctrine\ORM\Configuration;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Provider\Doctrine\Connection;
use Viserio\Provider\Doctrine\ConnectionManager;

/**
 * @internal
 */
final class ConnectionManagerTest extends MockeryTestCase
{
    /**
     * @var \Viserio\Provider\Doctrine\ConnectionManager
     */
    private $manager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->manager = new ConnectionManager([
            'viserio' => [
                'doctrine' => [
                    'dbal' => [
                        'default' => 'mysql',
                    ],
                ],
            ],
        ]);
    }

    public function testDefaultConnection(): void
    {
        $this->assertInstanceOf(Connection::class, $this->manager->getConnection());
    }

    public function testMysqlConnection(): void
    {
        $this->assertInstanceOf(Connection::class, $this->manager->getConnection('mysql'));
    }

    public function testSqliteConnection(): void
    {
        $this->assertInstanceOf(Connection::class, $this->manager->getConnection('sqlite'));
    }

    public function testPgsqlConnection(): void
    {
        $this->assertInstanceOf(Connection::class, $this->manager->getConnection('pgsql'));
    }

    public function testSqlsrvConnection(): void
    {
        $this->assertInstanceOf(Connection::class, $this->manager->getConnection('sqlsrv'));
    }

    public function testSetAndGetDoctrineEventManager(): void
    {
        $this->assertNull($this->manager->getDoctrineEventManager());

        $this->manager->setDoctrineEventManager($this->mock(EventManager::class));

        $this->assertInstanceOf(EventManager::class, $this->manager->getDoctrineEventManager());
    }

    public function testSetAndGetDoctrineConfiguration(): void
    {
        $this->assertNull($this->manager->getDoctrineConfiguration());

        $this->manager->setDoctrineConfiguration($this->mock(Configuration::class));

        $this->assertInstanceOf(Configuration::class, $this->manager->getDoctrineConfiguration());
    }
}
