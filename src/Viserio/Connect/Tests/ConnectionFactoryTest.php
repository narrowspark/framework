<?php
namespace Viserio\Connect\Tests;

use Mockery as Mock;
use Viserio\Connect\Adapters\MemcachedConnector;
use Viserio\Connect\ConnectionFactory;
use Viserio\Connect\Adapters\PredisConnector;

class ConnectionFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testExtend()
    {
        $config = Mock::mock('Viserio\Contracts\Config\Manager');
        $factory = new ConnectionFactory($config);
        $factory->extend('memcached', new MemcachedConnector());

        $this->assertTrue(is_array($factory->getExtensions()));
    }

    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage fail connector dont exist.
     */
    public function testConnectionToThrowRuntimeException()
    {
        $config = Mock::mock('Viserio\Contracts\Config\Manager');
        $config->shouldReceive('get')->once()->with('fail', [])->andReturn([
            'fail' => [
                'server' => 'localhost',
            ],
        ]);
        $factory = new ConnectionFactory($config);
        $factory->connection('fail');
    }

    public function testConnection()
    {
        $config = Mock::mock('Viserio\Contracts\Config\Manager');
        $config->shouldReceive('get')->once()->with('predis', [])->andReturn([
            'servers' => 'localhost'
        ]);
        $factory = new ConnectionFactory($config);

        $this->assertInstanceOf('Predis\Client', $factory->connection('predis'));
        $this->assertInstanceOf('Predis\Client', $factory->getConnection());
    }

    public function testExtensionsConnection()
    {
        $config = Mock::mock('Viserio\Contracts\Config\Manager');
        $config->shouldReceive('get')->once()->with('predis2', [])->andReturn([
            'servers' => 'localhost'
        ]);
        $factory = new ConnectionFactory($config);
        $factory->extend('predis2', new PredisConnector());

        $this->assertInstanceOf('Predis\Client', $factory->connection('predis2'));

        $factory->reconnect('predis2');
        $this->assertInstanceOf('Predis\Client', $factory->connection('predis2'));
    }

    public function testSupportedPDODrivers()
    {
        $config = Mock::mock('Viserio\Contracts\Config\Manager');
        $factory = new ConnectionFactory($config);

        $this->assertTrue(is_array($factory->supportedPDODrivers()));
    }

    public function testGetAvailableDrivers()
    {
        $config = Mock::mock('Viserio\Contracts\Config\Manager');
        $factory = new ConnectionFactory($config);

        $this->assertTrue(is_array($factory->getAvailableDrivers()));
    }

    public function testGetConfig()
    {
        $config = Mock::mock('Viserio\Contracts\Config\Manager');
        $factory = new ConnectionFactory($config);

        $this->assertInstanceOf('Viserio\Contracts\Config\Manager', $factory->getConfig());
    }

    public function testGetConnectionConfig()
    {
        $config = Mock::mock('Viserio\Contracts\Config\Manager');
        $config->shouldReceive('get')->once()->with('pdo', [])->andReturn([
            'pdo' => [
                'server' => 'localhost',
            ],
        ]);
        $factory = new ConnectionFactory($config);

        $this->assertTrue(is_array($factory->getConnectionConfig('pdo')));
    }
}
