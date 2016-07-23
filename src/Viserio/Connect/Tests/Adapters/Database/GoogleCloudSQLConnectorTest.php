<?php

declare(strict_types=1);
namespace Viserio\Connect\Tests\Adapter\Database;

use Narrowspark\TestingHelper\Traits\MockeryTrait;
use Viserio\Connect\Adapters\Database\GoogleCloudSQLConnector;

class GoogleCloudSQLConnectorTest extends \PHPUnit_Framework_TestCase
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
        $connector = new GoogleCloudSQLConnector();
        $config = [
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

        $connector = $this->getMockBuilder('Viserio\Connect\Adapters\Database\GoogleCloudSQLConnector')
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

        $connection->shouldReceive('prepare')->once()->with('set names \'utf8\'')->andReturn($connection);
        $connection->shouldReceive('prepare')->once()->with('set sql_mode=\'ANSI_QUOTES\'')->andReturn($connection);
        $connection->shouldReceive('execute')->twice();

        $this->assertSame($connector->connect($config), $connection);
    }
}
