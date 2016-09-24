<?php
declare(strict_types=1);
namespace Viserio\Database\Tests\Connectors;

use Narrowspark\TestingHelper\Traits\MockeryTrait;
use PDO;
use Viserio\Database\Connectors\MySqlConnector;

class MySqlConnectorTest extends \PHPUnit_Framework_TestCase
{
    use MockeryTrait;

    public function setUp()
    {
        $this->allowMockingNonExistentMethods(true);

        if (! class_exists(PDO::class)) {
            $this->markTestSkipped('PDO module is not installed.');
        }
    }

    /**
     * @dataProvider mySqlConnectProvider
     */
    public function testMySqlConnectCallsCreateConnectionWithProperArguments($dsn, $config)
    {
        $connection = $this->mock(PDO::class);

        $connector = $this->getMockBuilder(MySqlConnector::class)
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

        $connection->shouldReceive('prepare')
            ->once()
            ->with('set names \'utf8\' collate \'utf8_unicode_ci\'')
            ->andReturn($connection);

        if (isset($config['strict'])) {
            $connection->shouldReceive('prepare')
                ->once()
                ->with('set session sql_mode=\'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION\'')
                ->andReturn($connection);
        } elseif(isset($config['modes'])) {
            $connection->shouldReceive('prepare')
                ->once()
                ->with(sprintf('set session sql_mode="%s"', implode(',', $config['modes'])))
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
                    'modes' => [
                        'NO_BACKSLASH_ESCAPES',
                        'NO_AUTO_CREATE_USER',
                    ]
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
