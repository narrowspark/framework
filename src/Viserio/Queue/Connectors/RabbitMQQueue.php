<?php
namespace Viserio\Queue\Connectors;

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPTable;
use Viserio\Queue\Jobs\RabbitMQJob;

class RabbitMQQueue extends AbstractQueue
{
    /**
     * Create a new RabbitMQ queue instance.
     *
     * @param \PhpAmqpLib\Connection\AMQPStreamConnection $amqpConnection
     * @param array                                       $config
     */
    public function __construct(AMQPStreamConnection $amqpConnection, $config)
    {
        $this->connection = $amqpConnection;
        $this->defaultQueue = $config['queue'];
        $this->configQueue = $config['queue_params'];
        $this->configExchange = $config['exchange_params'];
        $this->declareExchange = $config['exchange_declare'];
        $this->declareBindQueue = $config['queue_declare_bind'];

        $this->channel = $this->getChannel();
    }

    /**
     * {@inheritdoc}
     */
    public function push($job, $data = '', $queue = null)
    {
        return $this->pushRaw($this->createPayload($job, $data), $queue, []);
    }

    /**
     * {@inheritdoc}
     */
    public function pushRaw($payload, $queue = null, array $options = [])
    {
        $queue = $this->getQueue($queue);

        $this->declareQueue($queue);

        if (isset($options['delay']) && $options['delay'] > 0) {
            list($queue, $exchange) = $this->declareDelayedQueue($queue, $options['delay']);
        } else {
            list($queue, $exchange) = $this->declareQueue($queue);
        }

        // push job to a queue
        $message = new AMQPMessage($payload, [
            'Content-Type' => 'application/json',
            'delivery_mode' => 2,
        ]);

        // push task to a queue
        $this->channel->basic_publish($message, $exchange, $queue);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function later($delay, $job, $data = '', $queue = null)
    {
        return $this->pushRaw($this->createPayload($job, $data), $queue, ['delay' => $delay]);
    }

    /**
     * {@inheritdoc}
     */
    public function pop($queue = null)
    {
        $queue = $this->getQueue($queue);

        // declare queue if not exists
        $this->declareQueue($queue);

        // get envelope
        $message = $this->channel->basic_get($queue);

        if ($message instanceof AMQPMessage) {
            return new RabbitMQJob($this->container, $this, $this->channel, $queue, $message);
        }

        return;
    }

    /**
     * Get the AMQPChannel.
     *
     * @return \PhpAmqpLib\Channel\AMQPChannel
     */
    protected function getChannel(): AMQPChannel
    {
        return $this->connection->channel();
    }

    /**
     * @param $name
     *
     * @return array
     */
    protected function declareQueue(string $name): array
    {
        $name = $this->getQueue($name);

        $exchange = $this->configExchange['name'] ?: $name;

        if ($this->declareExchange) {
            // declare exchange
            $this->channel->exchange_declare(
                $exchange,
                $this->configExchange['type'],
                $this->configExchange['passive'],
                $this->configExchange['durable'],
                $this->configExchange['auto_delete']
            );
        }

        if ($this->declareBindQueue) {
            // declare queue
            $this->channel->queue_declare(
                $name,
                $this->configQueue['passive'],
                $this->configQueue['durable'],
                $this->configQueue['exclusive'],
                $this->configQueue['auto_delete']
            );
            // bind queue to the exchange
            $this->channel->queue_bind($name, $exchange, $name);
        }

        return [$name, $exchange];
    }

    /**
     * @param string        $destination
     * @param \DateTime|int $delay
     *
     * @return array
     */
    protected function declareDelayedQueue(string $destination, $delay): array
    {
        $delay = $this->getSeconds($delay);
        $destination = $this->getQueue($destination);
        $name = $this->getQueue($destination) . '_deferred_' . $delay;

        $destinationExchange = $this->configExchange['name'] ?: $destination;
        $exchange = $this->configExchange['name'] ?: $destination;

        // declare exchange
        $this->channel->exchange_declare(
            $exchange,
            $this->configExchange['type'],
            $this->configExchange['passive'],
            $this->configExchange['durable'],
            $this->configExchange['auto_delete']
        );

        // declare queue
        $this->channel->queue_declare(
            $name,
            $this->configQueue['passive'],
            $this->configQueue['durable'],
            $this->configQueue['exclusive'],
            $this->configQueue['auto_delete'],
            false,
            new AMQPTable([
                'x-dead-letter-exchange' => $destinationExchange,
                'x-dead-letter-routing-key' => $destination,
                'x-message-ttl' => $delay * 1000,
            ])
        );

        // bind queue to the exchange
        $this->channel->queue_bind($name, $exchange, $name);

        return [$name, $exchange];
    }
}
