<?php
declare(strict_types=1);
namespace Viserio\Component\Queue\Tests\Connector;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Pheanstalk\Job;
use Pheanstalk\Pheanstalk;
use Psr\Container\ContainerInterface;
use Viserio\Component\Contracts\Encryption\Encrypter as EncrypterContract;
use Viserio\Component\Queue\Connector\BeanstalkdQueue;
use Viserio\Component\Queue\Job\BeanstalkdJob;

class BeanstalkdQueueTest extends MockeryTestCase
{
    public function testPushProperlyPushesJobOntoBeanstalkd()
    {
        $encrypter = $this->mock(EncrypterContract::class);
        $encrypter->shouldReceive('encrypt');

        $queue = new BeanstalkdQueue($this->mock(Pheanstalk::class), 'default', 90);
        $queue->setEncrypter($encrypter);

        $pheanstalk = $queue->getPheanstalk();
        $pheanstalk->shouldReceive('useTube')
            ->once()
            ->with('stack')
            ->andReturn($pheanstalk);
        $pheanstalk->shouldReceive('useTube')
            ->once()
            ->with('default')
            ->andReturn($pheanstalk);
        $pheanstalk->shouldReceive('put')
            ->twice()
            ->with(json_encode(['job' => 'foo', 'data' => ['someData']]), 1024, 0, 90);

        $queue->push('foo', ['someData'], 'stack');
        $queue->push('foo', ['someData']);
    }

    public function testDelayedPushProperlyPushesJobOntoBeanstalkd()
    {
        $encrypter = $this->mock(EncrypterContract::class);
        $encrypter->shouldReceive('encrypt');

        $queue = new BeanstalkdQueue($this->mock(Pheanstalk::class), 'default', 90);
        $queue->setEncrypter($encrypter);

        $pheanstalk = $queue->getPheanstalk();
        $pheanstalk->shouldReceive('useTube')
            ->once()
            ->with('stack')
            ->andReturn($pheanstalk);
        $pheanstalk->shouldReceive('useTube')
            ->once()
            ->with('default')
            ->andReturn($pheanstalk);
        $pheanstalk->shouldReceive('put')
            ->twice()
            ->with(
                json_encode(['job' => 'foo', 'data' => ['someData']]),
                Pheanstalk::DEFAULT_PRIORITY,
                5,
                90
            );

        $queue->later(5, 'foo', ['someData'], 'stack');
        $queue->later(5, 'foo', ['someData']);
    }

    public function testDeleteProperlyRemoveJobsOffBeanstalkd()
    {
        $encrypter = $this->mock(EncrypterContract::class);
        $encrypter->shouldReceive('encrypt');

        $queue = new BeanstalkdQueue($this->mock(Pheanstalk::class), 'default', 90);

        $pheanstalk = $queue->getPheanstalk();
        $pheanstalk->shouldReceive('useTube')
            ->once()
            ->with('default')
            ->andReturn($pheanstalk);
        $pheanstalk->shouldReceive('delete')
            ->once()
            ->with(1);

        $queue->deleteMessage('default', 1);
    }

    public function testPopProperlyPopsJobOffOfBeanstalkd()
    {
        $encrypter = $this->mock(EncrypterContract::class);
        $encrypter->shouldReceive('encrypt');

        $queue = new BeanstalkdQueue($this->mock(Pheanstalk::class), 'default', 90);
        $queue->setEncrypter($encrypter);
        $queue->setContainer($this->mock(ContainerInterface::class));

        $pheanstalk = $queue->getPheanstalk();
        $pheanstalk->shouldReceive('watchOnly')
            ->once()
            ->with('default')
            ->andReturn($pheanstalk);

        $job = $this->mock(Job::class);
        $pheanstalk->shouldReceive('reserve')
            ->once()
            ->andReturn($job);

        $result = $queue->pop();

        self::assertInstanceOf(BeanstalkdJob::class, $result);
    }
}
