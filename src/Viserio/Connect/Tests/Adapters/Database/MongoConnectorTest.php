<?php
namespace Viserio\Connect\Tests\Adapter\Database;


use Narrowspark\TestingHelper\Traits\MockeryTrait;
use Viserio\Connect\Adapters\Database\MongoConnector;

class MongoConnectorTest extends \PHPUnit_Framework_TestCase
{
    use MockeryTrait;

    public function testGetDefaultOption()
    {
        $connector = new MongoConnector();
        $options = [
            'connect' => true,
        ];

        $this->assertTrue(is_array($connector->getDefaultOptions()));
        $this->assertSame($options, $connector->getDefaultOptions());
    }

    public function testSetDefaultOption()
    {
        $options = [
            'replicaSet' => false,
            'persist'    => false,
            'connect'    => false,
        ];

        $connector = new MongoConnector();
        $connector->setDefaultOptions($options);

        $this->assertTrue(is_array($connector->getDefaultOptions()));
        $this->assertSame($options, $connector->getDefaultOptions());
    }

    public function testGetOptions()
    {
        $connector = new MongoConnector();
        $config = [
            'options' => [
                'connect' => true,
            ],
        ];

        $this->assertTrue(is_array($connector->getOptions($config)));
    }

    public function testConnect()
    {
        $dsn = 'mongodb://localhost:27017';
        $config = [
            'server' => 'localhost',
            'port'   => '27017',
            'options' => [
                'connect' => true,
            ],
        ];
        $connection = $this->mock('stdClass');

        $connector = $this->getMock(
            'Viserio\Connect\Adapters\Database\MongoConnector',
            ['createConnection', 'getOptions']
        );
        $connector->expects($this->once())
            ->method('getOptions')
            ->with($this->equalTo($config))
            ->will($this->returnValue(['options' => ['connect' => true]]));
        $connector->expects($this->once())
            ->method('createConnection')
            ->with($this->equalTo($dsn), $this->equalTo($config), $this->equalTo(['options' => ['connect' => true]]))
            ->will($this->returnValue($connection));

        $this->assertSame($connector->connect($config), $connection);
    }

    public function testConnectWithUserAndPassword()
    {
        $dsn = 'mongodb://test:test@localhost:27017';
        $config = [
            'server'   => 'localhost',
            'port'     => '27017',
            'username' => 'test',
            'password' => 'test',
            'options' => [
                'connect' => true,
            ],
        ];
        $connection = $this->mock('stdClass');

        $connector = $this->getMock(
            'Viserio\Connect\Adapters\Database\MongoConnector',
            ['createConnection', 'getOptions']
        );
        $connector->expects($this->once())
            ->method('getOptions')
            ->with($this->equalTo($config))
            ->will($this->returnValue(['options' => ['connect' => true]]));
        $connector->expects($this->once())
            ->method('createConnection')
            ->with($this->equalTo($dsn), $this->equalTo($config), $this->equalTo(['options' => ['connect' => true]]))
            ->will($this->returnValue($connection));

        $this->assertSame($connector->connect($config), $connection);
    }
}
