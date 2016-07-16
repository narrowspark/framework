<?php
namespace Viserio\Queue\Tests;

use Interop\Container\ContainerInterface as ContainerInteropInterface;
use Narrowspark\TestingHelper\Traits\MockeryTrait;
use Viserio\Contracts\{
    Config\Manager as ConfigContract,
    Encryption\Encrypter as EncrypterContract,
    Events\Dispatcher as DispatcherContract
};
use Viserio\Queue\QueueManager;
use Viserio\Queue\Tests\Fixture\TestQueue;

class QueueManagerTest extends \PHPUnit_Framework_TestCase
{
    use MockeryTrait;

    public function testConnection()
    {
        $config = $this->mock(ConfigContract::class);
        $config->shouldReceive('get')
            ->once()
            ->with('queue.connections', [])
            ->andReturn([]);

        $manager = new QueueManager(
            $config,
            $this->mock(ContainerInteropInterface::class),
            $this->mock(EncrypterContract::class)
        );

        $manager->extend('testqueue', function($config) {
            return new TestQueue();
        });

        $connection = $manager->connection('testqueue');

        $this->assertInstanceOf(ContainerInteropInterface::class, $connection->getContainer());
        $this->assertInstanceOf(EncrypterContract::class, $connection->getEncrypter());
    }

    public function testSetAndGetEncrypter()
    {
        $config = $this->mock(ConfigContract::class);

        $manager = new QueueManager(
            $this->mock(ConfigContract::class),
            $this->mock(ContainerInteropInterface::class),
            $this->mock(EncrypterContract::class)
        );

        $this->assertInstanceOf(EncrypterContract::class, $manager->getEncrypter());

        $manager->setEncrypter($this->mock(EncrypterContract::class));

        $this->assertInstanceOf(EncrypterContract::class, $manager->getEncrypter());
    }

    public function testSetAndGetDispatcher()
    {
        $config = $this->mock(ConfigContract::class);

        $manager = new QueueManager(
            $config,
            $this->mock(ContainerInteropInterface::class),
            $this->mock(EncrypterContract::class)
        );

        $manager->setEventDispatcher($this->mock(DispatcherContract::class));

        $this->assertInstanceOf(DispatcherContract::class, $manager->getEventDispatcher());
    }
}
