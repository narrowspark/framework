<?php
declare(strict_types=1);
namespace Viserio\Connect\Tests\Adapter\Database;

use Narrowspark\TestingHelper\Traits\MockeryTrait;
use PDO;
use Viserio\Connect\Adapters\Database\SQLiteConnector;

class SQLiteConnectorTest extends \PHPUnit_Framework_TestCase
{
    use MockeryTrait;

    public function setUp()
    {
        if (! class_exists(PDO::class)) {
            $this->markTestSkipped('PDO module is not installed.');
        }
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Database does not exist.
     */
    public function testSQLiteDatabaseNotFound()
    {
        $config = ['database' => __DIR__ . 'notfound.db'];
        $connection = $this->createMock(PDO::class);

        $connector = $this->getMockBuilder(SQLiteConnector::class)
            ->setMethods(['createConnection', 'getOptions'])
            ->getMock();

        $connector->connect($config);
    }

    public function testSQLiteFileDatabasesMayBeConnectedTo()
    {
        $dsn = 'sqlite:' . __DIR__;
        $config = ['database' => __DIR__];
        $connection = $this->mock(PDO::class);

        $connector = $this->getMockBuilder(SQLiteConnector::class)
            ->setMethods(['createConnection', 'getOptions'])
            ->getMock();
        $connector->expects($this->once())
            ->method('getOptions')
            ->with($this->equalTo($config))
            ->will($this->returnValue(['options']));
        $connector->expects($this->once())
            ->method('createConnection')
            ->with($this->equalTo($dsn), $this->equalTo($config), $this->equalTo(['options']))
            ->will($this->returnValue($connection));

        $this->assertSame($connector->connect($config), $connection);
    }

    public function testSQLiteMemoryDatabasesMayBeConnectedTo()
    {
        $dsn = 'sqlite::memory:';
        $config = ['database' => ':memory:'];
        $connection = $this->mock(PDO::class);

        $connector = $this->getMockBuilder(SQLiteConnector::class)
            ->setMethods(['createConnection', 'getOptions'])
            ->getMock();
        $connector->expects($this->once())
            ->method('getOptions')
            ->with($this->equalTo($config))
            ->will($this->returnValue(['options']));
        $connector->expects($this->once())
            ->method('createConnection')
            ->with($this->equalTo($dsn), $this->equalTo($config), $this->equalTo(['options']))
            ->will($this->returnValue($connection));

        $this->assertSame($connector->connect($config), $connection);
    }
}
