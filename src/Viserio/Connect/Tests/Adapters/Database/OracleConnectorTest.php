<?php
namespace Viserio\Connect\Tests\Adapter\Database;


use Narrowspark\TestingHelper\Traits\MockeryTrait;

class OracleConnectorTest extends \PHPUnit_Framework_TestCase
{
    use MockeryTrait;

    protected function setUp()
    {
        if (!class_exists('PDO')) {
            $this->markTestSkipped('PDO module not installed');
        }
    }

    /**
     * @dataProvider oracleConnectProvider
     */
    public function testConnect($dsn, $config)
    {
        $connection = $this->mock('stdClass');

        $connector = $this->getMock(
            'Viserio\Connect\Adapters\Database\OracleConnector',
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
        $connection->shouldReceive('prepare')->once()->with('set sql_mode=\'ANSI_QUOTES\'')->andReturn($connection);
        $connection->shouldReceive('execute')->twice();

        $this->assertSame($connector->connect($config), $connection);
    }

    public function oracleConnectProvider()
    {
        return [
            [
                'oci:host=foo;port=111;dbname=bar',
                [
                    'server' => 'foo',
                    'port' => 111,
                    'database' => 'bar',
                    'charset' => 'utf8',
                ],
            ],
            [
                'oci:host=foo;dbname=bar',
                [
                    'server' => 'foo',
                    'database' => 'bar',
                    'charset' => 'utf8',
                ],
            ],
        ];
    }
}
