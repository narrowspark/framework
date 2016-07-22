<?php
declare(strict_types=1);
namespace Viserio\Queue\Tests;

use RuntimeException;
use Narrowspark\TestingHelper\Traits\MockeryTrait;
use stdClass;
use Viserio\Contracts\{
    Events\Dispatcher as DispatcherContract,
    Exception\Handler as ExceptionHandlerContract,
    Exception\Exception\FatalThrowableError,
    Queue\FailedJobProvider as FailedJobProviderContract,
    Queue\Job as JobContract,
    Queue\QueueConnector as QueueConnectorContract
};
use Viserio\Queue\{
    Worker,
    QueueManager
};

class WorkerTest extends \PHPUnit_Framework_TestCase
{
    use MockeryTrait;

    public function testJobIsPoppedOffQueueAndProcessed()
    {
        $worker = $this->getMockBuilder(Worker::class)
            ->setMethods(['process'])
            ->setConstructorArgs([$manager = $this->mock(QueueManager::class)])
            ->getMock();

        $manager->shouldReceive('connection')
            ->once()
            ->with('connection')
            ->andReturn($connection = $this->mock(stdClass::class));

        $job = $this->mock(JobContract::class);

        $connection->shouldReceive('pop')
            ->once()
            ->with('queue')
            ->andReturn($job);

        $worker->expects($this->once())
            ->method('getNextJob');

        $worker->expects($this->once())
            ->method('process')
            ->with($this->equalTo('connection'), $this->equalTo($job), $this->equalTo(0), $this->equalTo(0));

        $worker->runNextJob('connection', 'queue');
    }

    /**
     * @expectedException RuntimeException
     */
    public function testJobIsReleasedWhenExceptionIsThrown()
    {
        $worker = new Worker($this->mock(QueueManager::class));

        $job = $this->mock(JobContract::class);
        $job->shouldReceive('run')
            ->once()
            ->andReturnUsing(function () {
                throw new RuntimeException;
            });
        $job->shouldReceive('isDeleted')->once()->andReturn(false);
        $job->shouldReceive('release')->once()->with(5);

        $worker->process('connection', $job, 0, 5);
    }

    /**
     * @expectedException RuntimeException
     */
    public function testJobIsNotReleasedWhenExceptionIsThrownButJobIsDeleted()
    {
        $worker = new Worker($this->mock(QueueManager::class));

        $job = $this->mock(JobContract::class);
        $job->shouldReceive('run')->once()->andReturnUsing(function () {
            throw new RuntimeException;
        });
        $job->shouldReceive('isDeleted')->once()->andReturn(true);
        $job->shouldReceive('release')->never();

        $worker->process('connection', $job, 0, 5);
    }

    public function testWorkerLogsJobToFailedQueueIfMaxTriesHasBeenExceeded()
    {
        $worker = new Worker(
            $this->mock(QueueManager::class),
            $failer = $this->mock(FailedJobProviderContract::class)
        );

        $job = $this->mock(JobContract::class);
        $job->shouldReceive('attempts')
            ->once()
            ->andReturn(10);
        $job->shouldReceive('getQueue')
            ->once()
            ->andReturn('queue');
        $job->shouldReceive('getRawBody')
            ->once()
            ->andReturn('body');
        $job->shouldReceive('delete')
            ->once();
        $job->shouldReceive('failed')
            ->once();
        $failer->shouldReceive('log')
            ->once()
            ->with('connection', 'queue', 'body');

        $worker->process('connection', $job, 3, 0);

        $this->assertInstanceOf(QueueManager::class, $worker->getManager());
    }

    public function testWorkerSleepsIfNoJobIsPresentAndSleepIsEnabled()
    {
        $worker = $this->getMockBuilder(Worker::class)
            ->setMethods(['process', 'sleep'])
            ->setConstructorArgs([$manager = $this->mock(QueueManager::class)])
            ->getMock();

        $manager->shouldReceive('connection')
            ->once()
            ->with('connection')
            ->andReturn($connection = $this->mock(stdClass::class));

        $connection->shouldReceive('pop')
            ->once()
            ->with('queue')
            ->andReturn(null);

        $worker->expects($this->never())
            ->method('process');
        $worker->expects($this->once())
            ->method('sleep')
            ->with($this->equalTo(3));
        $worker->expects($this->once())
            ->method('getNextJob');
        $worker->runNextJob('connection', 'queue', 0, 3);
    }

    public function testJobIsPoppedOffFirstQueueInListAndProcessed()
    {
        $worker = $this->getMockBuilder(Worker::class)
            ->setMethods(['process'])
            ->setConstructorArgs([$manager = $this->mock(QueueManager::class)])
            ->getMock();

        $manager->shouldReceive('connection')
            ->once()
            ->with('connection')
            ->andReturn($connection = $this->mock(stdClass::class));
        $manager->shouldReceive('getName')->andReturn('connection');

        $job = $this->mock(JobContract::class);

        $connection->shouldReceive('pop')
            ->once()
            ->with('queue1')
            ->andReturn(null);
        $connection->shouldReceive('pop')
            ->once()
            ->with('queue2')
            ->andReturn($job);

        $worker->expects($this->once())
            ->method('process')
            ->with($this->equalTo('connection'), $this->equalTo($job), $this->equalTo(0), $this->equalTo(0));
        $worker->expects($this->once())
            ->method('getNextJob');
        $worker->runNextJob('connection', 'queue1,queue2');
    }
}
