<?php
declare(strict_types=1);
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
                ],
            ]);

        $factory = new ConnectManager($config);

        $this->assertInstanceOf(Client::class, $factory->connection('predis'));
        $this->assertTrue($factory->connection() instanceof Client);
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
}
