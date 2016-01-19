<?php
namespace Viserio\Connect\Tests\Adapter\Database;


use Narrowspark\TestingHelper\Traits\MockeryTrait;
use Viserio\Connect\Adapters\Database\GoogleCloudSQLConnector;

class GoogleCloudSQLConnectorTest extends \PHPUnit_Framework_TestCase
{
    use MockeryTrait;

    protected function setUp()
    {
        if (!class_exists('PDO')) {
            $this->markTestSkipped('PDO module not installed');
        }
    }

    /**
     * @expectedException \PDOException
     */
    public function testConnectThrowPDOException()
    {
        $connector = new GoogleCloudSQLConnector();
        $config    = [
            'server'   => '',
            'database' => '',
            'username' => '',
            'password' => '',
            'charset'  => 'utf-8',
        ];

        $this->assertSame('PDO', $connector->connect($config));
    }

    public function testConnect()
    {
        $dsn = 'mysql:unix_socket=/cloudsql/foo;dbname=bar';
        $config = ['server' => 'foo', 'database' => 'bar', 'charset' => 'utf8'];
        $connection = $this->mock('stdClass');

        $connector = $this->getMock(
            'Viserio\Connect\Adapters\Database\GoogleCloudSQLConnector',
            ['createConnection', 'getOptions']
        );
        $connector->expects($this->once())
            ->method('getOptions')
            ->with($this->equalTo($config))->will($this->returnValue(['options']));
        $connector->expects($this->once())
            ->method('createConnection')
            ->with($this->equalTo($dsn), $this->equalTo($config), $this->equalTo(['options']))
            ->will($this->returnValue($connection));

        $connection->shouldReceive('prepare')->once()->with('set names \'utf8\'')->andReturn($connection);
        $connection->shouldReceive('prepare')->once()->with('set sql_mode=\'ANSI_QUOTES\'')->andReturn($connection);
        $connection->shouldReceive('execute')->twice();

        $this->assertSame($connector->connect($config), $connection);
    }
}
