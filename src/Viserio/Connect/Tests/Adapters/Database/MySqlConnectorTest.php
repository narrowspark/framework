<?php
namespace Viserio\Connect\Tests\Adapter\Database;

use Narrowspark\TestingHelper\Traits\MockeryTrait;

class MySqlConnectorTest extends \PHPUnit_Framework_TestCase
{
    use MockeryTrait;

    public function setUp()
    {
        $this->allowMockingNonExistentMethods(true);

        if (!class_exists('PDO')) {
            $this->markTestSkipped('PDO module is not installed.');
        }
    }

    /**
     * @dataProvider mySqlConnectProvider
     */
    public function testMySqlConnectCallsCreateConnectionWithProperArguments($dsn, $config)
    {
        $connection = $this->mock('stdClass');

        $connector = $this->getMock(
            'Viserio\Connect\Adapters\Database\MySqlConnector',
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
            ->with('set names \'utf8\' collate \'utf8_unicode_ci\'')
            ->andReturn($connection);

        if (isset($config['strict'])) {
            $connection->shouldReceive('prepare')
                ->once()
                ->with('set session sql_mode=\'STRICT_ALL_TABLES\'')
                ->andReturn($connection);
        } else {
            $connection->shouldReceive('prepare')
                ->once()
                ->with('set session sql_mode=\'\'')
                ->andReturn($connection);
        }

        if (isset($config['timezone'])) {
            $connection->shouldReceive('prepare')
                ->once()
                ->with('set time_zone=\'Europe/London\'')
                ->andReturn($connection);
        }

        $connection->shouldReceive('execute')->between(1, 3);
        $connection->shouldReceive('exec')->zeroOrMoreTimes();

        $this->assertSame($connector->connect($config), $connection);
    }

    public function mySqlConnectProvider()
    {
        return [
            [
                'mysql:host=foo;dbname=bar',
                [
                    'server' => 'foo',
                    'database' => 'bar',
                    'collation' => 'utf8_unicode_ci',
                    'charset' => 'utf8',
                ],
            ],
            [
                'mysql:host=foo;dbname=bar',
                [
                    'server' => 'foo',
                    'database' => 'bar',
                    'collation' => 'utf8_unicode_ci',
                    'charset' => 'utf8',
                    'strict' => true,
                ],
            ],
            [
                'mysql:host=foo;port=111;dbname=bar',
                [
                    'server' => 'foo',
                    'database' => 'bar',
                    'port' => 111,
                    'collation' => 'utf8_unicode_ci',
                    'charset' => 'utf8',
                ],
            ],
            [
                'mysql:unix_socket=baz;dbname=bar',
                [
                    'server' => 'foo',
                    'database' => 'bar',
                    'port' => 111,
                    'unix_socket' => 'baz',
                    'collation' => 'utf8_unicode_ci',
                    'charset' => 'utf8',
                ],
            ],
            [
                'mysql:unix_socket=baz;dbname=bar',
                [
                    'server' => 'foo',
                    'database' => 'bar',
                    'port' => 111,
                    'unix_socket' => 'baz',
                    'collation' => 'utf8_unicode_ci',
                    'charset' => 'utf8',
                    'timezone' => 'Europe/London',
                ],
            ],
        ];
    }
}
