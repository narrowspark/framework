<?php
namespace Viserio\Connect\Tests\Adapter\Database;

use Narrowspark\TestingHelper\Traits\MockeryTrait;

class PostgreSQLConnectorTest extends \PHPUnit_Framework_TestCase
{
    use MockeryTrait;

    public function setUp()
    {
        $this->allowMockingNonExistentMethods(true);

        if (!class_exists('PDO')) {
            $this->markTestSkipped('PDO module is not installed.');
        }
    }

    public function testPostgresConnectCallsCreateConnectionWithProperArguments()
    {
        $dsn = 'pgsql:host=foo;dbname=bar;port=111';
        $config = [
            'server' => 'foo',
            'database' => 'bar',
            'port' => 111,
            'charset' => 'utf8',
        ];
        $connection = $this->mock('stdClass');

        $connector = $this->getMock(
            'Viserio\Connect\Adapters\Database\PostgreSQLConnector',
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
        $connection->shouldReceive('prepare')->once()->with('set names \'utf8\'')->andReturn($connection);
        $connection->shouldReceive('execute')->once();

        $this->assertSame($connector->connect($config), $connection);
    }

    public function testPostgresConnectCallsCreateConnectionWithProperArgumentsAndTimezone()
    {
        $dsn = 'pgsql:host=foo;dbname=bar;port=111';
        $config = [
            'server' => 'foo',
            'database' => 'bar',
            'port' => 111,
            'charset' => 'utf8',
            'timezone' => 'Europe/London',
        ];
        $connection = $this->mock('stdClass');

        $connector = $this->getMock(
            'Viserio\Connect\Adapters\Database\PostgreSQLConnector',
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
        $connection->shouldReceive('prepare')->once()->with('set names \'utf8\'')->andReturn($connection);
        $connection->shouldReceive('prepare')->once()->with('set timezone=\'Europe/London\'')->andReturn($connection);
        $connection->shouldReceive('execute')->twice();

        $this->assertSame($connector->connect($config), $connection);
    }

    public function testPostgresConnectSSLModeCallsCreateConnectionWithProperArguments()
    {
        $dsn = 'pgsql:host=foo;dbname=bar;port=111;sslmode=verify-ca';
        $config = [
            'server' => 'foo',
            'database' => 'bar',
            'port' => 111,
            'charset' => 'utf8',
            'sslmode' => 'verify-ca',
        ];

        $connection = $this->mock('stdClass');
        $connector = $this->getMock(
            'Viserio\Connect\Adapters\Database\PostgreSQLConnector',
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
        $connection->shouldReceive('prepare')->once()->with('set names \'utf8\'')->andReturn($connection);
        $connection->shouldReceive('execute')->once();

        $this->assertSame($connector->connect($config), $connection);
    }

    public function testPostgresSearchPathIsSet()
    {
        $dsn = 'pgsql:host=foo;dbname=bar';
        $config = ['server' => 'foo', 'database' => 'bar', 'schema' => 'public', 'charset' => 'utf8'];
        $connection = $this->mock('stdClass');

        $connector = $this->getMock(
            'Viserio\Connect\Adapters\Database\PostgreSQLConnector',
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

        $connection->shouldReceive('prepare')->once()->with('set names \'utf8\'')->andReturn($connection);
        $connection->shouldReceive('prepare')->once()->with('set search_path to \'public\'')->andReturn($connection);
        $connection->shouldReceive('execute')->twice();

        $this->assertSame($connector->connect($config), $connection);
    }

    public function testPostgresSearchPathArraySupported()
    {
        $dsn = 'pgsql:host=foo;dbname=bar';
        $config = [
            'server' => 'foo',
            'database' => 'bar',
            'schema' => ['public', 'user'],
            'charset' => 'utf8',
        ];
        $connection = $this->mock('stdClass');

        $connector = $this->getMock(
            'Viserio\Connect\Adapters\Database\PostgreSQLConnector',
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

        $connection->shouldReceive('prepare')
        ->once()
        ->with('set names \'utf8\'')
        ->andReturn($connection);
        $connection->shouldReceive('prepare')
        ->once()
        ->with('set search_path to \'public\', \'user\'')
        ->andReturn($connection);
        $connection->shouldReceive('execute')->twice();

        $this->assertSame($connector->connect($config), $connection);
    }

    public function testPostgresApplicationNameIsSet()
    {
        $dsn = 'pgsql:host=foo;dbname=bar';
        $config = [
            'server' => 'foo',
            'database' => 'bar',
            'charset' => 'utf8',
            'application_name' => 'Narrowspark',
        ];
        $connection = $this->mock('stdClass');

        $connector = $this->getMock(
            'Viserio\Connect\Adapters\Database\PostgreSQLConnector',
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

        $connection->shouldReceive('prepare')
            ->once()
            ->with('set names \'utf8\'')
            ->andReturn($connection);
        $connection->shouldReceive('prepare')
            ->once()
            ->with('set application_name to \'Narrowspark\'')
            ->andReturn($connection);
        $connection->shouldReceive('execute')->twice();

        $this->assertSame($connector->connect($config), $connection);
    }
}
