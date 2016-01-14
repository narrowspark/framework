<?php
namespace Viserio\Connect\Tests\Adapter\Database;

use Mockery as Mock;
use PDO;

class SqlServerConnectorTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        if (!class_exists('PDO')) {
            $this->markTestSkipped('PDO module not installed');
        }
    }

    protected function tearDown()
    {
        Mock::close();
    }

    public function testSqlServerConnectCallsCreateConnectionWithProperArguments()
    {
        $config = ['host' => 'foo', 'database' => 'bar'];

        $dsn = $this->getDsn($config);
        $connection = Mock::mock('stdClass');

        $connector = $this->getMock(
            'Viserio\Connect\Adapters\Database\SqlServerConnector',
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

    public function testSqlServerConnectCallsCreateConnectionWithOptionalArguments()
    {
        $config = ['host' => 'foo', 'database' => 'bar', 'port' => 111, 'appname' => 'baz', 'charset' => 'utf-8'];
        $dsn = $this->getDsn($config);

        $connection = Mock::mock('stdClass');
        $connector = $this->getMock(
            'Viserio\Connect\Adapters\Database\SqlServerConnector',
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

    protected function getDsn(array $config)
    {
        extract($config, EXTR_SKIP);
        if (in_array('dblib', PDO::getAvailableDrivers(), true)) {
            $port = isset($config['port']) ? ':' . $port : '';
            $appname = isset($config['appname']) ? ';appname=' . $config['appname'] : '';
            $charset = isset($config['charset']) ? ';charset=' . $config['charset'] : '';

            return "dblib:host={$host}{$port};dbname={$database}{$appname}{$charset}";
        } else {
            $port = isset($config['port']) ? ',' . $port : '';
            $appname = isset($config['appname']) ? ';APP=' . $config['appname'] : '';

            return "sqlsrv:Server={$host}{$port};Database={$database}{$appname}";
        }
    }
}
