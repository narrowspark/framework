<?php
declare(strict_types=1);
namespace Viserio\Component\Queue\Jobs;

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Container\ContainerInterface;
use Viserio\Component\Queue\Connectors\RabbitMQQueue;

class RabbitMQJob extends AbstractJob
{
    /**
     * The PRabbitMQQueue instance.
     *
     * @var \Viserio\Component\Queue\Connectors\RabbitMQQueue
     */
    protected $connection;

    /**
     * The PhpAmqpLib AMQPChannel instance.
     *
     * @var \PhpAmqpLib\Channel\AMQPChannel
     */
    protected $channel;

    /**
     * The PhpAmqpLib AMQPMessage instance.
     *
     * @var \PhpAmqpLib\Message\AMQPMessage
     */
    protected $message;

    /**
     * Create a new job instance.
     *
     * @param \Psr\Container\ContainerInterface                 $container
     * @param \Viserio\Component\Queue\Connectors\RabbitMQQueue $connection
     * @param \PhpAmqpLib\Channel\AMQPChannel                   $channel
     * @param string                                            $queue
     * @param \PhpAmqpLib\Message\AMQPMessage                   $message
     */
    public function __construct(
        ContainerInterface $container,
        RabbitMQQueue $connection,
        AMQPChannel $channel,
        $queue,
        AMQPMessage $message
    ) {
        $this->container  = $container;
        $this->connection = $connection;
        $this->channel    = $channel;
        $this->queue      = $queue;
        $this->message    = $message;
    }

    /**
     * {@inheritdoc}
     */
    public function run()
    {
        $this->resolveAndRun(json_decode($this->message->body, true));
    }

    /**
     * {@inheritdoc}
     */
    public function getRawBody(): string
    {
        return $this->message->body;
    }

    /**
     * {@inheritdoc}
     */
    public function delete()
    {
        parent::delete();

        $this->channel->basic_ack($this->message->delivery_info['delivery_tag']);
    }

    /**
     * {@inheritdoc}
     */
    public function release(int $delay = 0)
    {
        $this->delete();

        $body     = $this->message->body;
        $body     = json_decode($body, true);
        $attempts = $this->attempts();
        $job      = unserialize($body['data']['command']);

        // write attempts to job
        $job->attempts = $attempts + 1;
        $data          = $body['data'];

        if ($delay > 0) {
            $this->connection->later($delay, $job, $data, $this->getQueue());
        } else {
            $this->connection->push($job, $data, $this->getQueue());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function attempts(): int
    {
        $body = json_decode($this->message->body, true);
        $job  = unserialize($body['data']['command']);

        if (is_object($job) && property_exists($job, 'attempts')) {
            return (int) $job->attempts;
        }

        return 0;
    }

    /**
     * {@inheritdoc}
     */
    public function getJobId(): string
    {
        return $this->message->get('correlation_id');
    }

    /**
     * Get the underlying queue driver instance.
     *
     * @return \Viserio\Component\Queue\Connectors\RabbitMQQueue
     */
    public function getRabbitMQQueue(): RabbitMQQueue
    {
        return $this->connection;
    }
}
