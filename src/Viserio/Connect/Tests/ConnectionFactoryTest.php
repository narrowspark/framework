<?php
namespace Viserio\Connect\Tests;

use Narrowspark\TestingHelper\ArrayContainer;
use Viserio\Connect\Adapters\MemcachedConnector;
use Viserio\Connect\Adapters\PredisConnector;
use Viserio\Connect\ConnectionFactory;

class ConnectionFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testExtend()
    {
        $factory = new ConnectionFactory(new ArrayContainer());
        $factory->extend('memcached', new MemcachedConnector());

        $this->assertTrue(is_array($factory->getExtensions()));
    }

    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage fail connector dont exist.
     */
    public function testConnectionToThrowRuntimeException()
    {
        $config = new ArrayContainer();
        $config->set('fail', [
                'server' => 'localhost',
        ]);

        $factory = new ConnectionFactory($config);
        $factory->connection('fail');
    }

    public function testConnection()
    {
        $config = new ArrayContainer();
        $config->set('predis', [
            'servers' => 'localhost',
        ]);
        $factory = new ConnectionFactory($config);

        $this->assertInstanceOf('Predis\Client', $factory->connection('predis'));
        $this->assertInstanceOf('Predis\Client', $factory->getConnection());
    }

    public function testExtensionsConnection()
    {
        $config = new ArrayContainer();
        $config->set('predis2', [
            'servers' => 'localhost',
        ]);
        $factory = new ConnectionFactory($config);
        $factory->extend('predis2', new PredisConnector());

        $this->assertInstanceOf('Predis\Client', $factory->connection('predis2'));

        $factory->reconnect('predis2');
        $this->assertInstanceOf('Predis\Client', $factory->connection('predis2'));
    }

    public function testSupportedPDODrivers()
    {
        $factory = new ConnectionFactory(new ArrayContainer());

        $this->assertTrue(is_array($factory->supportedPDODrivers()));
    }

    public function testGetAvailableDrivers()
    {
        $factory = new ConnectionFactory(new ArrayContainer());

        $this->assertTrue(is_array($factory->getAvailableDrivers()));
    }

    public function testGetConfig()
    {
        $factory = new ConnectionFactory(new ArrayContainer());

        $this->assertInstanceOf('Interop\Container\ContainerInterface', $factory->getContainer());
    }

    public function testGetConnectionConfig()
    {
        $config = new ArrayContainer();
        $config->set('pdo', [
            'pdo' => [
                'server' => 'localhost',
            ],
        ]);
        $factory = new ConnectionFactory($config);

        $this->assertTrue(is_array($factory->getConnectionConfig('pdo')));
    }
}
