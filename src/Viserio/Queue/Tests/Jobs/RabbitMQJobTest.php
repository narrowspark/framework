<?php
declare(strict_types=1);
namespace Viserio\Queue\Tests\Jobs;

use Interop\Container\ContainerInterface;
use Narrowspark\TestingHelper\Traits\MockeryTrait;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;
use Viserio\Queue\Connectors\RabbitMQQueue;
use Viserio\Queue\Jobs\RabbitMQJob;

class RabbitMQJobTest extends \PHPUnit_Framework_TestCase
{
    use MockeryTrait;

    protected function getJob()
    {
        $message = $this->mock(AMQPMessage::class);
        $message->delivery_info['delivery_tag'] = 'test';

        $channel = $this->mock(AMQPChannel::class);

        return new RabbitMQJob(
            $this->mock(ContainerInterface::class),
            $this->mock(RabbitMQQueue::class),
            $channel,
            'default',
            $message
        );
    }
}
