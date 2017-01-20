<?php
declare(strict_types=1);
namespace Viserio\Component\Queue\Tests;

use Interop\Container\ContainerInterface as ContainerInteropInterface;
use Narrowspark\TestingHelper\Traits\MockeryTrait;
use PHPUnit\Framework\TestCase;
use Viserio\Component\Contracts\Config\Repository as RepositoryContract;
use Viserio\Component\Contracts\Encryption\Encrypter as EncrypterContract;
use Viserio\Component\Queue\QueueManager;
use Viserio\Component\Queue\Tests\Fixture\TestQueue;

class QueueManagerTest extends TestCase
{
    use MockeryTrait;

    public function testConnection()
    {
        $container = $this->mock(ContainerInteropInterface::class);

        $config = $this->mock(RepositoryContract::class);
        $config->shouldReceive('offsetExists')
            ->once()
            ->with('viserio')
            ->andReturn(true);
        $config->shouldReceive('offsetGet')
            ->once()
            ->with('viserio')
            ->andReturn([
                'queue' => [
                    'connections' => [
                    ],
                ],
            ]);
        $container->shouldReceive('has')
            ->once()
            ->with(RepositoryContract::class)
            ->andReturn(true);
        $container->shouldReceive('get')
            ->once()
            ->with(RepositoryContract::class)
            ->andReturn($config);

        $manager = new QueueManager(
            $container,
            $this->mock(EncrypterContract::class)
        );

        $manager->extend('testqueue', function ($config) {
            return new TestQueue();
        });

        $connection = $manager->getConnection('testqueue');

        self::assertInstanceOf(ContainerInteropInterface::class, $connection->getContainer());
        self::assertInstanceOf(EncrypterContract::class, $connection->getEncrypter());
    }

    public function testSetAndGetEncrypter()
    {
        $container = $this->mock(ContainerInteropInterface::class);

        $config = $this->mock(RepositoryContract::class);
        $config->shouldReceive('offsetExists')
            ->once()
            ->with('viserio')
            ->andReturn(true);
        $config->shouldReceive('offsetGet')
            ->once()
            ->with('viserio')
            ->andReturn([
                'queue' => [
                    'connections' => [
                    ],
                ],
            ]);
        $container->shouldReceive('has')
            ->once()
            ->with(RepositoryContract::class)
            ->andReturn(true);
        $container->shouldReceive('get')
            ->once()
            ->with(RepositoryContract::class)
            ->andReturn($config);

        $manager = new QueueManager(
            $container,
            $this->mock(EncrypterContract::class)
        );

        self::assertInstanceOf(EncrypterContract::class, $manager->getEncrypter());

        $manager->setEncrypter($this->mock(EncrypterContract::class));

        self::assertInstanceOf(EncrypterContract::class, $manager->getEncrypter());
    }
}
