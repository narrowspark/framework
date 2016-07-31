<?php
declare(strict_types=1);
namespace Viserio\Connect\Tests\Adapter\Database;

use Narrowspark\TestingHelper\Traits\MockeryTrait;

class DblibConnectorTest extends \PHPUnit_Framework_TestCase
{
    use MockeryTrait;

    public function setUp()
    {
        $this->allowMockingNonExistentMethods(true);

        if (! class_exists('PDO')) {
            $this->markTestSkipped('PDO module is not installed.');
        }
    }

    /**
     * @dataProvider dblibConnectProvider
     */
    public function testDblibDatabasesMayBeConnectedTo($dsn, $config)
    {
        $connection = $this->mock('stdClass');

        $connector = $this->getMockBuilder('Viserio\Connect\Adapters\Database\DblibConnector')
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
        $connection->shouldReceive('execute')->once();

        $this->assertSame($connector->connect($config), $connection);
    }

    public function dblibConnectProvider()
    {
        return [
            [
                'dblib:host=foo;dbname=bar',
                [
                    'server' => 'foo',
                    'database' => 'bar',
                    'charset' => 'utf8',
                ],
            ],
            [
                'dblib:host=foo:11221;dbname=bar',
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
