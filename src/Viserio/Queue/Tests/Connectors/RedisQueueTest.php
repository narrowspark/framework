<?php
namespace Viserio\Queue\Connectors\Tests;

use Carbon\Carbon;
use Interop\Container\ContainerInterface;
use Narrowspark\TestingHelper\Traits\MockeryTrait;
use Predis\Client;
use Viserio\Contracts\Encryption\Encrypter as EncrypterContract;
use Viserio\Queue\{
    Jobs\RedisJob,
    Connectors\RedisQueue
};

class RedisQueueTest extends \PHPUnit_Framework_TestCase
{
    use MockeryTrait;

    public function testDelayedPushWithDateTimeProperlyPushesJobOntoRedis()
    {
        $date = Carbon::now();
        $encrypter = $this->mock(EncrypterContract::class);
        $encrypter->shouldReceive('encrypt');

        $queue = $this->getMockBuilder(RedisQueue::class)
            ->setMethods(['getSeconds', 'getTime', 'getRandomId'])
            ->setConstructorArgs([$redis = $this->mock(Client::class)])->getMock();
        $queue->setEncrypter($encrypter);
        $queue->expects($this->once())->method('getRandomId')->will($this->returnValue('foo'));
        $queue->expects($this->once())->method('getSeconds')->with($date)->will($this->returnValue(1));
        $queue->expects($this->once())->method('getTime')->will($this->returnValue(1));

        $redis->shouldReceive('zadd')->once()->with(
            'queues:default:delayed',
            2,
            json_encode(['job' => 'foo', 'data' => ['data'], 'id' => 'foo', 'attempts' => 1])
        );

        $queue->later($date, 'foo', ['data']);
    }
}
