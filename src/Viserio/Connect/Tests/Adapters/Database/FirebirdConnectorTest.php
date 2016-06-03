<?php
namespace Viserio\Connect\Tests\Adapter\Database;

use Narrowspark\TestingHelper\Traits\MockeryTrait;
use Viserio\Connect\Adapters\Database\FirebirdConnector;

class FirebirdConnectorTest extends \PHPUnit_Framework_TestCase
{
    use MockeryTrait;

    public function setUp()
    {
        if (! class_exists('PDO')) {
            $this->markTestSkipped('PDO module is not installed.');
        }
    }

    /**
     * @expectedException \PDOException
     */
    public function testConnectThrowPDOException()
    {
        $connector = new FirebirdConnector();
        $config = [
            'server'   => '',
            'database' => 'stc\Connect\Tests\Fixture\employee.fdb',
            'username' => '',
            'password' => '',
        ];

        $this->assertSame('PDO', $connector->connect($config));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Database does not exist.
     */
    public function testFirebirdDatabasesMayBeNotConnectedTo()
    {
        $config = ['server' => 'localhost', 'database' => null, 'username' => '', 'password' => ''];
        $connection = $this->mock('stdClass');

        $connector = $this->createMock(
            'Viserio\Connect\Adapters\Database\FirebirdConnector',
            ['createConnection', 'getOptions']
        );

        $this->assertSame($connector->connect($config), $connection);
    }

    public function testFirebirdDatabasesMayBeConnectedTo()
    {
        $dsn = 'firebird:dbname=localhost:stc\Connect\Tests\Fixture\employee.fdb';
        $config = [
            'server' => 'localhost',
            'database' => 'stc\Connect\Tests\Fixture\employee.fdb',
            'username' => '',
            'password' => '',
        ];
        $connection = $this->mock('stdClass');

        $connector = $this->createMock(
            'Viserio\Connect\Adapters\Database\FirebirdConnector',
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
