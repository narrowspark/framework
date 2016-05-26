<?php
namespace Viserio\Connect\Tests\Adapter\Database;

use Narrowspark\TestingHelper\Traits\MockeryTrait;

class SQLiteConnectorTest extends \PHPUnit_Framework_TestCase
{
    use MockeryTrait;

    public function setUp()
    {
        if (! class_exists('PDO')) {
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
        $connection = $this->getMock('stdClass');

        $connector = $this->getMock(
            'Viserio\Connect\Adapters\Database\SQLiteConnector',
            ['createConnection', 'getOptions']
        );

        $this->assertSame($connector->connect($config), $connection);
    }

    public function testSQLiteFileDatabasesMayBeConnectedTo()
    {
        $dsn = 'sqlite:' . __DIR__;
        $config = ['database' => __DIR__];
        $connection = $this->mock('stdClass');

        $connector = $this->getMock(
            'Viserio\Connect\Adapters\Database\SQLiteConnector',
            ['createConnection', 'getOptions']
        );
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
        $connection = $this->mock('stdClass');

        $connector = $this->getMock(
            'Viserio\Connect\Adapters\Database\SQLiteConnector',
            ['createConnection', 'getOptions']
        );
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
