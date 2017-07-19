<?php
declare(strict_types=1);
namespace Viserio\Component\Queue\Tests\Connector;

use Exception;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Opis\Closure\SerializableClosure;
use Psr\Container\ContainerInterface;
use stdClass;
use Viserio\Component\Contracts\Encryption\Encrypter as EncrypterContract;
use Viserio\Component\Queue\Connector\SyncQueue;
use Viserio\Component\Queue\Job\SyncJob;
use Viserio\Component\Queue\QueueClosure;
use Viserio\Component\Queue\Tests\Fixture\FailingSyncQueueHandler;
use Viserio\Component\Queue\Tests\Fixture\SyncQueueHandler;

class SyncQueueTest extends MockeryTestCase
{
    public function testPushShouldRunJobInstantly(): void
    {
        unset($_SERVER['__sync.test']);

        $sync    = new SyncQueue();
        $closure = function ($job): bool {
            $_SERVER['__sync.test'] = true;

            $job->delete();

            return true;
        };

        $events = $this->mock(stdClass::class);
        $events->shouldReceive('trigger');

        $encrypter = $this->mock(EncrypterContract::class);
        $encrypter->shouldReceive('encrypt')
            ->once();
        $encrypter->shouldReceive('decrypt')
            ->once()
            ->andReturn(\serialize(new SerializableClosure($closure)));

        $container = $this->mock(ContainerInterface::class);
        $container->shouldReceive('has')
            ->with('events')
            ->times(4)
            ->andReturn(true);
        $container->shouldReceive('get')
            ->with('events')
            ->times(4)
            ->andReturn($events);
        $container->shouldReceive('get')
            ->with(QueueClosure::class)
            ->andReturn(new QueueClosure($encrypter));
        $container->shouldReceive('get')
            ->with('SyncQueueHandler')
            ->andReturn(new SyncQueueHandler());

        $sync->setContainer($container);
        $sync->setEncrypter($encrypter);
        $sync->push($closure);

        self::assertTrue($_SERVER['__sync.test']);

        unset($_SERVER['__sync.test']);

        $sync->push('SyncQueueHandler', ['foo' => 'bar']);

        self::assertInstanceOf(SyncJob::class, $_SERVER['__sync.test'][0]);
        self::assertEquals(['foo' => 'bar'], $_SERVER['__sync.test'][1]);
    }

    public function testFailedJobGetsHandledWhenAnExceptionIsThrown(): void
    {
        unset($_SERVER['__sync.failed']);

        $events = $this->mock(stdClass::class);
        $events->shouldReceive('trigger')
            ->times(3);

        $container = $this->mock(ContainerInterface::class);
        $container->shouldReceive('has')
            ->with('events')
            ->andReturn(true);
        $container->shouldReceive('get')
            ->with('events')
            ->andReturn($events);
        $container->shouldReceive('get')
            ->with('FailingSyncQueueHandler')
            ->andReturn(new FailingSyncQueueHandler());

        $encrypter = $this->mock(EncrypterContract::class);

        $sync = new SyncQueue();

        $sync->setContainer($container);
        $sync->setEncrypter($encrypter);

        try {
            $sync->push('FailingSyncQueueHandler', ['foo' => 'bar']);
        } catch (Exception $e) {
            self::assertTrue($_SERVER['__sync.failed']);
        }
    }
}
