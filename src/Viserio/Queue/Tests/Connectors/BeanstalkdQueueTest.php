<?php
namespace Viserio\Queue\Tests\Connectors;

use Narrowspark\TestingHelper\Traits\MockeryTrait;
use Pheanstalk\Pheanstalk;
use Viserio\Contracts\Encryption\Encrypter as EncrypterContract;
use Viserio\Queue\Connectors\BeanstalkdQueue;

class BeanstalkdQueueTest extends \PHPUnit_Framework_TestCase
{
    use MockeryTrait;

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
            ->with(json_encode(['job' => 'foo', 'data' => ['data']]), 1024, 0, 90);

        $queue->push('foo', ['data'], 'stack');
        $queue->push('foo', ['data']);
    }
}
