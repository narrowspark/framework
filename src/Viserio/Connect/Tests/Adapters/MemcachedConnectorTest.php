<?php
namespace Viserio\Connect\Tests\Adapters;

use Mockery as Mock;
use Viserio\Connect\Adapters\MemcachedConnector;

class MemcachedConnectorTest extends \PHPUnit_Framework_TestCase
{
    protected function tearDown()
    {
        Mock::close();
    }

    public function testConnect()
    {
        $config = [
            'servers' => [
                [
                    'host' => 'localhost',
                    'port' => 11211,
                    'weight' => 100,
                ],
            ],
        ];

        $memcached = Mock::mock('stdClass');
        $memcached->shouldReceive('addServer')->once()->with('localhost', 11211, 100);
        $memcached->shouldReceive('getServerList')->once()->andReturn(null);
        $memcached->shouldReceive('getVersion')->once()->andReturn([]);

        $connector = $this->getMock('Viserio\Connect\Adapters\MemcachedConnector', ['getMemcached']);
        $connector->expects($this->once())->method('getMemcached')->will($this->returnValue($memcached));

        $this->assertSame($connector->connect($config), $memcached);
    }

    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage Could not establish Memcached connection.
     */
    public function testExceptionThrownOnBadConnection()
    {
        $config = [
            'servers' => [
                [
                    'host' => 'localhost',
                    'port' => 11211,
                    'weight' => 100,
                ],
            ],
        ];

        $memcached = Mock::mock('stdClass');
        $memcached->shouldReceive('addServer')->once()->with('localhost', 11211, 100);
        $memcached->shouldReceive('getServerList')->once()->andReturn(null);
        $memcached->shouldReceive('getVersion')->once()->andReturn(['255.255.255']);

        $connector = $this->getMock('Viserio\Connect\Adapters\MemcachedConnector', ['getMemcached']);
        $connector->expects($this->once())->method('getMemcached')->will($this->returnValue($memcached));

        $connector->connect($config);
    }

    public function testAddMemcachedOptions()
    {
        if (!extension_loaded('memcached')) {
            $this->markTestSkipped('Memcached is not loaded.');
        }

        $config = [
            'options' => [
                'OPT_NO_BLOCK'         => true,
                'OPT_AUTO_EJECT_HOSTS' => true,
                'OPT_CONNECT_TIMEOUT'  => 2000,
                'OPT_POLL_TIMEOUT'     => 2000,
                'OPT_RETRY_TIMEOUT'    => 2,
            ],
            'servers' => [
                [
                    'host' => 'localhost',
                    'port' => 11211,
                    'weight' => 100,
                ],
            ],
        ];

        $connector = (new MemcachedConnector())->connect($config);

        $this->assertSame(1, $connector->getOption(\Memcached::OPT_NO_BLOCK));
        $this->assertSame(1, $connector->getOption(\Memcached::OPT_AUTO_EJECT_HOSTS));
        $this->assertSame(2000, $connector->getOption(\Memcached::OPT_CONNECT_TIMEOUT));
        $this->assertSame(2000, $connector->getOption(\Memcached::OPT_POLL_TIMEOUT));
        $this->assertSame(2, $connector->getOption(\Memcached::OPT_RETRY_TIMEOUT));
    }

    /**
     * Need memcached with sasl support.
     */
    public function testAddSaslAuth()
    {
        if (!extension_loaded('memcached')) {
            $this->markTestSkipped('Memcached is not loaded.');
        }

        if (getenv('TRAVIS')) {
            $this->markTestSkipped('Memcached dont support sasl on travis.');
        }

        $config = [
            'sasl' => [
                'username' => 'test',
                'password' => 'testpassword',
            ],
            'servers' => [
                [
                    'host' => 'localhost',
                    'port' => 11211,
                    'weight' => 100,
                ],
            ],
        ];

        $connector = (new MemcachedConnector())->connect($config);

        $this->assertSame(1, $connector->getOption(\Memcached::OPT_BINARY_PROTOCOL));
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage No Memcached servers added.
     */
    public function testNoMemcachedServerAdded()
    {
        $config = [];

        $memcached = Mock::mock('stdClass');
        $memcached->shouldReceive('getVersion')->once()->andReturn('');
        $memcached->shouldReceive('getServerList')->once()->andReturn($config);

        $connector = $this->getMock('Viserio\Connect\Adapters\MemcachedConnector', ['getMemcached']);
        $connector->expects($this->once())->method('getMemcached')->will($this->returnValue($memcached));

        $connector->connect($config);
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Invalid Memcached option: [Memcached::OPT_NO_FAIL]
     */
    public function testAddBadMemcachedOptionsToThrowExeption()
    {
        if (!extension_loaded('memcached')) {
            $this->markTestSkipped('Memcached is not loaded.');
        }

        $config = [
            'options' => [
                'OPT_NO_FAIL' => true,
            ],
        ];

        (new MemcachedConnector())->connect($config);
    }
}
