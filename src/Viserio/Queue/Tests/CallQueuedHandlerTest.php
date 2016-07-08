<?php
namespace Viserio\Queue\Tests;

use Interop\Container\ContainerInterface;
use Narrowspark\TestingHelper\{
    ArrayContainer,
    Traits\MockeryTrait
};
use stdClass;
use Viserio\Contracts\{
    Encryption\Encrypter as EncrypterContract,
    Queue\Job as JobContract,
    Bus\Dispatcher as DispatcherContract
};
use Viserio\Bus\QueueingDispatcher;
use Viserio\Queue\{
    CallQueuedHandler,
    Jobs\RedisJob,
    Connectors\RedisQueue,
    Tests\Fixture\InteractsWithQueue
};

class CallQueuedHandlerTest extends \PHPUnit_Framework_TestCase
{
    use MockeryTrait;

    public function testCall()
    {
        $command = serialize(new stdClass());

        $job = $this->mock(JobContract::class);
        $job->shouldReceive('isDeletedOrReleased')
            ->once()
            ->andReturn(false);
        $job->shouldReceive('delete')
            ->once();

        $encrypter = $this->mock(EncrypterContract::class);
        $encrypter->shouldReceive('decrypt')
            ->once()
            ->with($command)
            ->andReturn($command);

        $container = new ArrayContainer();
        $handler = $this->mock(stdClass::class);
        $handler->shouldReceive('handle')
            ->once()
            ->andReturn('foo');

        $container->set('stdClass', $handler);

        $dispatcher = new QueueingDispatcher($container);
        $dispatcher->mapUsing(function () {
            return 'stdClass@handle';
        });

        $callHandler = new CallQueuedHandler(
            $dispatcher,
            $encrypter
        );

        $callHandler->call($job, ['command' => $command]);
    }

    public function testFailed()
    {
        $redisContainer = $this->mock(ContainerInterface::class);
        $redisContainer->shouldReceive('get');

        $job = new RedisJob(
            $redisContainer,
            $this->mock(RedisQueue::class),
            json_encode(['job' => 'foo', 'data' => ['data'], 'attempts' => 1]),
            json_encode(['job' => 'foo', 'data' => ['data'], 'attempts' => 2]),
            'default'
        );

        $command = serialize($job);

        $encrypter = $this->mock(EncrypterContract::class);
        $encrypter->shouldReceive('decrypt')
            ->once()
            ->with($command)
            ->andReturn($command);

        $dispatcher = new QueueingDispatcher(new ArrayContainer());
        $dispatcher->mapUsing(function () {
            return 'stdClass@handle';
        });

        $callHandler = new CallQueuedHandler(
            $dispatcher,
            $encrypter
        );

        $callHandler->failed(['command' => $command]);
    }

    public function testCallWithInteractsWithQueue()
    {
        $command = serialize(new InteractsWithQueue());

        $job = $this->mock(JobContract::class);
        $job->shouldReceive('isDeletedOrReleased')
            ->once()
            ->andReturn(false);
        $job->shouldReceive('delete')
            ->once();

        $encrypter = $this->mock(EncrypterContract::class);
        $encrypter->shouldReceive('decrypt')
            ->once()
            ->with($command)
            ->andReturn($command);

        $container = new ArrayContainer();
        $handler = $this->mock(stdClass::class);
        $handler->shouldReceive('handle')
            ->once()
            ->andReturn('foo');

        $container->set('stdClass', $handler);

        $dispatcher = new QueueingDispatcher($container);
        $dispatcher->mapUsing(function () {
            return 'stdClass@handle';
        });

        $callHandler = new CallQueuedHandler(
            $dispatcher,
            $encrypter
        );

        $callHandler->call($job, ['command' => $command]);
    }
}
