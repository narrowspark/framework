<?php
namespace Viserio\Connect\Tests\Adapter\Database;

use Mockery as Mock;
use Viserio\Connect\Adapters\Database\DblibConnector;

class DblibConnectorTest extends \PHPUnit_Framework_TestCase
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

    /**
     * @dataProvider mySqlConnectProvider
     */
    public function testFirebirdDatabasesMayBeConnectedTo($dsn, $config)
    {
        $connection = Mock::mock('stdClass');
        $connector  = $this->getMock(
            'Viserio\Connect\Adapters\Database\DblibConnector',
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

    public function mySqlConnectProvider()
    {
        return [
            [
                'dblib:host=foo;dbname=bar;charset=\'utf8\'',
                [
                    'server' => 'foo',
                    'database' => 'bar',
                    'charset' => 'utf8'
                ]
            ],
            [
                'dblib:host=foo:11221;dbname=bar;charset=\'utf8\'',
                [
                    'server' => 'foo',
                    'database' => 'bar',
                    'port' => '11221',
                    'charset' => 'utf8',
                    'strict' => true
                ]
            ],
            [
                'dblib:host=foo;dbname=bar',
                [
                    'server' => 'foo',
                    'database' => 'bar',
                    'strict' => true
                ]
            ],
        ];
    }
}
