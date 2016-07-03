<?php
namespace Viserio\Queue\Jobs;

use Interop\Container\ContainerInterface;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;
use Viserio\Queue\Connectors\RabbitMQQueue;

class RabbitMQJob extends AbstractJob
{
    protected $connection;

    protected $channel;

    protected $queue;

    protected $message;

    public function __construct(
        ContainerInterface $container,
        RabbitMQQueue $connection,
        AMQPChannel $channel,
        $queue,
        AMQPMessage $message
    ) {
        $this->container = $container;
        $this->connection = $connection;
        $this->channel = $channel;
        $this->queue = $queue;
        $this->message = $message;
    }

    /**
     * {@inheritdoc}
     */
    public function run()
    {
        $this->resolveAndFire(json_decode($this->message->body, true));
    }

    /**
     * {@inheritdoc}
     */
    public function getRawBody()
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

        $body = $this->message->body;
        $body = json_decode($body, true);
        $attempts = $this->attempts();
        $job = unserialize($body['data']['command']);

        // write attempts to job
        $job->attempts = $attempts + 1;
        $data = $body['data'];

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
        $job = unserialize($body['data']['command']);

        if (is_object($job) && property_exists($job, 'attempts')) {
            return (int) $job->attempts;
        }

        return 0;
    }

    /**
     * {@inheritdoc}
     */
    public function getJobId()
    {
        return $this->message->get('correlation_id');
    }

    /**
     */
    public function getRabbitMQJob()
    {
    }
}
