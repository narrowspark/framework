<?php
namespace Viserio\Connect\Tests\Adapter\Database;


use Narrowspark\TestingHelper\Traits\MockeryTrait;

class DblibConnectorTest extends \PHPUnit_Framework_TestCase
{
    use MockeryTrait;

    protected function setUp()
    {
        if (!class_exists('PDO')) {
            $this->markTestSkipped('PDO module not installed');
        }
    }

    /**
     * @dataProvider dblibConnectProvider
     */
    public function testDblibDatabasesMayBeConnectedTo($dsn, $config)
    {
        $connection = $this->mock('stdClass');
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

    public function dblibConnectProvider()
    {
        return [
            [
                'dblib:host=foo;dbname=bar;charset=\'utf8\'',
                [
                    'server' => 'foo',
                    'database' => 'bar',
                    'charset' => 'utf8',
                ],
            ],
            [
                'dblib:host=foo:11221;dbname=bar;charset=\'utf8\'',
                [
                    'server' => 'foo',
                    'database' => 'bar',
                    'port' => '11221',
                    'charset' => 'utf8',
                    'strict' => true,
                ],
            ],
            [
                'dblib:host=foo;dbname=bar',
                [
                    'server' => 'foo',
                    'database' => 'bar',
                    'strict' => true,
                ],
            ],
        ];
    }
}
