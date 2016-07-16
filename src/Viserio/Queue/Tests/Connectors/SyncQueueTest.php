<?php
namespace Viserio\Queue\Tests\Connectors;

use stdClass;
use Interop\Container\ContainerInterface;
use SuperClosure\Serializer;
use Narrowspark\TestingHelper\Traits\MockeryTrait;
use Viserio\Queue\{
    Connectors\SyncQueue,
    Jobs\SyncJob,
    QueueClosure
};
use Viserio\Contracts\Encryption\Encrypter as EncrypterContract;
use Viserio\Queue\Tests\Fixture\SyncQueueTestHandler;

class SyncQueueTest extends \PHPUnit_Framework_TestCase
{
    use MockeryTrait;

    public function testPushShouldRunJobInstantly()
    {
        unset($_SERVER['__sync.test']);

        /*
         * Test Synced Closure
         */
        $sync = new SyncQueue;
        $closure = function ($job) {
            $_SERVER['__sync.test'] = true;

            $job->delete();
        };

        $events = $this->mock(stdClass::class);
        $events->shouldReceive('emit');

        $encrypter = $this->mock(EncrypterContract::class);
        $encrypter->shouldReceive('encrypt')
            ->once();
        $encrypter->shouldReceive('decrypt')
            ->once()
            ->andReturn((new Serializer)->serialize($closure));

        $container = $this->mock(ContainerInterface::class);
        $container->shouldReceive('has')
            ->with('events')
            ->andReturn(true);
        $container->shouldReceive('get')
            ->with('events')
            ->andReturn($events);
        $container->shouldReceive('get')
            ->with(QueueClosure::class)
            ->andReturn(new QueueClosure($encrypter));
        $container->shouldReceive('get')
            ->with('SyncQueueTestHandler')
            ->andReturn(new SyncQueueTestHandler());

        $sync->setContainer($container);
        $sync->setEncrypter($encrypter);
        $sync->push($closure);

        $this->assertTrue($_SERVER['__sync.test']);

        unset($_SERVER['__sync.test']);

        /*
         * Test Synced Class Handler
         */
        $sync->push('SyncQueueTestHandler', ['foo' => 'bar']);

        $this->assertInstanceOf(SyncJob::class, $_SERVER['__sync.test'][0]);
        $this->assertEquals(['foo' => 'bar'], $_SERVER['__sync.test'][1]);
    }
}
