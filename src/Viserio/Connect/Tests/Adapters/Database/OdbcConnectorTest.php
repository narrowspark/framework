<?php
namespace Viserio\Connect\Tests\Adapter\Database;

use Mockery as Mock;

class OdbcConnectorTest extends \PHPUnit_Framework_TestCase
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
     * @dataProvider odbcConnectProvider
     */
    public function testConnect($dsn, $config)
    {
        $connection = Mock::mock('stdClass');

        $connector = $this->getMock(
            'Viserio\Connect\Adapters\Database\OdbcConnector',
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

    public function odbcConnectProvider()
    {
        return [
            [
                'odbc:DRIVER={SQL Server};SERVER=foo;DATABASE=bar',
                [
                    'DRIVER' => '{SQL Server}',
                    'SERVER' => 'foo',
                    'DATABASE' => 'bar'
                ]
            ],
            [
                'odbc:server=foo;DATABASE=bar',
                [
                    'server' => 'foo',
                    'DATABASE' => 'bar'
                ]
            ],
        ];
    }
}
