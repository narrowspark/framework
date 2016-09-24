<?php
declare(strict_types=1);
namespace Viserio\Database\Tests\Connectors;

use Narrowspark\TestingHelper\Traits\MockeryTrait;
use PDO;
use Viserio\Database\Connectors\OdbcConnector;

class OdbcConnectorTest extends \PHPUnit_Framework_TestCase
{
    use MockeryTrait;

    public function setUp()
    {
        if (! class_exists(PDO::class)) {
            $this->markTestSkipped('PDO module is not installed.');
        }
    }

    /**
     * @dataProvider odbcConnectProvider
     */
    public function testConnect($dsn, $config)
    {
        $connection = $this->mock(PDO::class);

        $connector = $this->getMockBuilder(OdbcConnector::class)
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
                    'DATABASE' => 'bar',
                ],
            ],
            [
                'odbc:server=foo;DATABASE=bar',
                [
                    'server' => 'foo',
                    'DATABASE' => 'bar',
                ],
            ],
        ];
    }
}
