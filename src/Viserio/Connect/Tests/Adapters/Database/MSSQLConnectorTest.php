<?php
namespace Viserio\Connect\Tests\Adapter\Database;

use Narrowspark\TestingHelper\Traits\MockeryTrait;
use Viserio\Support\Str;

class MSSQLConnectorTest extends \PHPUnit_Framework_TestCase
{
    use MockeryTrait;

    protected function setUp()
    {
        if (!class_exists('PDO')) {
            $this->markTestSkipped('PDO module is not installed.');
        }
    }

    /**
     * @dataProvider mssqlWinConnectProvider
     */
    public function testWinConnect($dsn, $config)
    {
        $contain = Str::containsAny(PHP_OS, [
            'WIN32',
            'WINNT',
            'Windows',
        ]);

        if (!$contain) {
            $this->markTestSkipped('Can only run on windows.');
        }

        $connection = $this->mock('stdClass');

        $connector = $this->getMock(
            'Viserio\Connect\Adapters\Database\MSSQLConnector',
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
        $connection->shouldReceive('prepare')->once()->with('set quoted_identifier on')->andReturn($connection);
        $connection->shouldReceive('execute')->twice();

        $this->assertSame($connector->connect($config), $connection);
    }

    public function mssqlWinConnectProvider()
    {
        return [
            [
                'sqlsrv:server=foo;database=bar',
                [
                    'server' => 'foo',
                    'database' => 'bar',
                    'charset' => 'utf8',
                ],
            ],
            [
                'sqlsrv:server=foo,111;database=bar',
                [
                    'server' => 'foo',
                    'database' => 'bar',
                    'port' => 111,
                    'charset' => 'utf8',
                ],
            ],
        ];
    }

    /**
     * @dataProvider mssqlOtherConnectProvider
     */
    public function testOtherConnect($dsn, $config)
    {
        $contain = Str::containsAny(PHP_OS, [
            'WIN32',
            'WINNT',
            'Windows',
        ]);

        if ($contain) {
            $this->markTestSkipped('Can\'t run on windows.');
        }

        $connection = $this->mock('stdClass');

        $connector = $this->getMock(
            'Viserio\Connect\Adapters\Database\MSSQLConnector',
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
        $connection->shouldReceive('prepare')->once()->with('set quoted_identifier on')->andReturn($connection);
        $connection->shouldReceive('execute')->twice();

        $this->assertSame($connector->connect($config), $connection);
    }

    public function mssqlOtherConnectProvider()
    {
        return [
            [
                'dblib:host=foo;database=bar',
                [
                    'server' => 'foo',
                    'database' => 'bar',
                    'charset' => 'utf8',
                ],
            ],
            [
                'dblib:host=foo:111;database=bar',
                [
                    'server' => 'foo',
                    'database' => 'bar',
                    'port' => 111,
                    'charset' => 'utf8',
                ],
            ],
        ];
    }
}
