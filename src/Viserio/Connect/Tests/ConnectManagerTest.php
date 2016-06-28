<?php
namespace Viserio\Connect\Tests;

use Narrowspark\TestingHelper\Traits\MockeryTrait;
use Predis\Client;
use Viserio\Contracts\Config\Manager as ConfigContract;
use Viserio\Connect\{
    ConnectManager,
    Adapters\MemcachedConnector,
    Adapters\PredisConnector
};

class ConnectManagerTest extends \PHPUnit_Framework_TestCase
{
    use MockeryTrait;

    public function testExtend()
    {
        $config = $this->mock(ConfigContract::class);
        $config->shouldReceive('get')
            ->once();

        $factory = new ConnectManager($config);
        $factory->extend('memcached', function() {
            return new MemcachedConnector();
        });

        $this->assertInstanceOf(MemcachedConnector::class, $factory->connection('memcached'));
    }

    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage The connection [fail] is not supported.
     */
    public function testConnectionToThrowRuntimeException()
    {
        $config = $this->mock(ConfigContract::class);
        $config->shouldReceive('get')->never();

        $factory = new ConnectManager($config);
        $factory->connection('fail');
    }

    public function testConnection()
    {
        $config = $this->mock(ConfigContract::class);
        $config->shouldReceive('get')
            ->once()
            ->with('connect.default', '')
            ->andReturn('predis');
        $config->shouldReceive('get')
            ->once()
            ->with('connect.connections', [])
            ->andReturn([
                'predis' => [
                    'servers' => 'localhost',
                ]
            ]);

        $factory = new ConnectManager($config);

        $this->assertInstanceOf(Client::class, $factory->connection('predis'));
        $this->assertTrue($factory->connection() instanceof Client);
    }

    public function testExtensionsConnection()
    {
        $config = $this->mock(ConfigContract::class);
        $config->shouldReceive('get')
            ->twice()
            ->with('connect.connections', [])
            ->andReturn([
                'predis2' => [
                    'servers' => 'localhost',
                ]
            ]);

        $factory = new ConnectManager($config);
        $factory->extend('predis2', function($options) {
            return (new PredisConnector())->connect($options);
        });

        $this->assertInstanceOf(Client::class, $factory->connection('predis2'));

        $factory->reconnect('predis2');

        $this->assertInstanceOf(Client::class, $factory->connection('predis2'));
    }

    public function testSupportedPDODrivers()
    {
        $config = $this->mock(ConfigContract::class);

        $factory = new ConnectManager($config);

        $this->assertTrue(is_array($factory->supportedPDODrivers()));
    }

    public function testGetAvailableDrivers()
    {
        $config = $this->mock(ConfigContract::class);

        $factory = new ConnectManager($config);

        $this->assertTrue(is_array($factory->getAvailableDrivers()));
    }

    public function testGetConfig()
    {
        $config = $this->mock(ConfigContract::class);

        $factory = new ConnectManager($config);

        $this->assertInstanceOf(ConfigContract::class, $factory->getConfig());
    }

    public function testGetConnectionConfig()
    {
        $config = $this->mock(ConfigContract::class);
        $config->shouldReceive('get')
            ->once()
            ->with('connect.connections', [])
            ->andReturn([
                'pdo' => [
                    'servers' => 'localhost',
                ]
            ]);

        $factory = new ConnectManager($config);

        $this->assertTrue(is_array($factory->getConnectionConfig('pdo')));
    }
}
