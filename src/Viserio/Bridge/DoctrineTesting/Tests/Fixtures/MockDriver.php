<?php
declare(strict_types=1);
namespace Viserio\Bridge\Doctrine\Testing\Tests\Fixtures;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver;
use Doctrine\DBAL\Driver\Connection as DriverConnection;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use PHPUnit\Framework\MockObject\Generator;
use PHPUnit\Framework\MockObject\MockObject;

class MockDriver implements Driver
{
    /**
     * {@inheritdoc}
     */
    public function connect(array $params, $username = null, $password = null, array $driverOptions = [])
    {
        return $this->getMock(DriverConnection::class);
    }

    /**
     * {@inheritdoc}
     */
    public function getDatabasePlatform()
    {
        return $this->getMock(AbstractPlatform::class);
    }

    /**
     * {@inheritdoc}
     */
    public function getSchemaManager(Connection $conn)
    {
        return $this->getMock(AbstractSchemaManager::class);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'mock';
    }

    /**
     * {@inheritdoc}
     */
    public function getDatabase(Connection $conn)
    {
        return 'mock';
    }

    /**
     * Create a new phpunit mock.
     *
     * @param string $className
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getMock(string $className): MockObject
    {
        $generator = new Generator();

        return $generator->getMock($className, [], [], '', false);
    }
}
