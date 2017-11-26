<?php
declare(strict_types=1);
namespace Viserio\Bridge\Doctrine\DBAL\Tests;

use Doctrine\Common\EventManager;
use Doctrine\ORM\Configuration;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Bridge\Doctrine\DBAL\Connection;
use Viserio\Bridge\Doctrine\DBAL\ConnectionManager;

class ConnectionManagerTest extends MockeryTestCase
{
    /**
     * @var \Viserio\Bridge\Doctrine\DBAL\ConnectionManager
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
        self::assertInstanceOf(Connection::class, $this->manager->getConnection());
    }

    public function testMysqlConnection(): void
    {
        self::assertInstanceOf(Connection::class, $this->manager->getConnection('mysql'));
    }

    public function testSqliteConnection(): void
    {
        self::assertInstanceOf(Connection::class, $this->manager->getConnection('sqlite'));
    }

    public function testPgsqlConnection(): void
    {
        self::assertInstanceOf(Connection::class, $this->manager->getConnection('pgsql'));
    }

    public function testSqlsrvConnection(): void
    {
        self::assertInstanceOf(Connection::class, $this->manager->getConnection('sqlsrv'));
    }

    public function testSetAndGetDoctrineEventManager(): void
    {
        self::assertNull($this->manager->getDoctrineEventManager());

        $this->manager->setDoctrineEventManager($this->mock(EventManager::class));

        self::assertInstanceOf(EventManager::class, $this->manager->getDoctrineEventManager());
    }

    public function testSetAndGetDoctrineConfiguration(): void
    {
        self::assertNull($this->manager->getDoctrineConfiguration());

        $this->manager->setDoctrineConfiguration($this->mock(Configuration::class));

        self::assertInstanceOf(Configuration::class, $this->manager->getDoctrineConfiguration());
    }
}
