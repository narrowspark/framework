<?php
declare(strict_types=1);
namespace Viserio\Component\Queue\Connector;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Exception\AMQPRuntimeException;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPTable;
use Viserio\Component\Queue\Job\RabbitMQJob;

class RabbitMQQueue extends AbstractQueue
{
    /**
     * The AMQPStreamConnection instance.
     *
     * @var \PhpAmqpLib\Connection\AMQPStreamConnection
     */
    protected $connection;

    /**
     * The AMQPChannel instance.
     *
     * @var \PhpAmqpLib\Channel\AMQPChannel
     */
    protected $channel;

    /**
     * Declare exchange.
     *
     * @var bool
     */
    protected $declareExchange;

    /**
     * Declare bind queue.
     *
     * @var string
     */
    protected $declareBindQueue;

    /**
     * Config queue.
     *
     * @var array
     */
    protected $configQueue;

    /**
     * Config exchange.
     *
     * @var array
     */
    protected $configExchange;

    /**
     * Attempts.
     *
     * @var int
     */
    private $attempts;

    /**
     * Create a new RabbitMQ queue instance.
     *
     * @param \PhpAmqpLib\Connection\AMQPStreamConnection $amqpConnection
     * @param array                                       $config
     */
    public function __construct(AMQPStreamConnection $amqpConnection, $config)
    {
        $this->connection       = $amqpConnection;
        $this->default          = $config['queue'];
        $this->configQueue      = $config['queue_params'];
        $this->configExchange   = $config['exchange_params'];
        $this->declareExchange  = $config['exchange_declare'];
        $this->declareBindQueue = $config['queue_declare_bind'];

        $this->channel = $this->connection->channel();
    }

    /**
     * {@inheritdoc}
     */
    public function push($job, $data = '', string $queue = null)
    {
        return $this->pushRaw($this->createPayload($job, $data), $queue, []);
    }

    /**
     * {@inheritdoc}
     */
    public function pushRaw(string $payload, string $queue = null, array $options = [])
    {
        $queue = $this->getQueue($queue);

        $this->declareQueue($queue);

        if (isset($options['delay']) && $options['delay'] > 0) {
            [$queue, $exchange] = $this->declareDelayedQueue($queue, $options['delay']);
        } else {
            [$queue, $exchange] = $this->declareQueue($queue);
        }

        $headers = [
            'Content-Type'  => 'application/json',
            'delivery_mode' => 2,
        ];

        if ($this->attempts !== null) {
            $headers['application_headers'] = ['attempts_count' => ['I', $this->attempts]];
        }

        $message = new AMQPMessage($payload, $headers);

        // push task to a queue
        $this->channel->basic_publish($message, $exchange, $queue);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function later($delay, $job, $data = '', string $queue = null)
    {
        return $this->pushRaw($this->createPayload($job, $data), $queue, ['delay' => $delay]);
    }

    /**
     * {@inheritdoc}
     */
    public function pop(string $queue = null)
    {
        $queue = $this->getQueue($queue);

        try {
            $this->declareQueue($queue);
        } catch (AMQPRuntimeException $exception) {
            $this->connection->reconnect();
            $this->declareQueue($queue);
        }

        // get envelope
        $message = $this->channel->basic_get($queue);

        if ($message instanceof AMQPMessage) {
            return new RabbitMQJob($this->container, $this, $this->channel, $queue, $message);
        }
    }

    /**
     * Sets the attempts member variable to be used in message generation.
     *
     * @param int $count
     */
    public function setAttempts(int $count)
    {
        $this->attempts = $count;
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
        $delay       = $this->getSeconds($delay);
        $destination = $this->getQueue($destination);
        $name        = $this->getQueue($destination) . '_deferred_' . $delay;

        $destinationExchange = $this->configExchange['name'] ?: $destination;
        $exchange            = $this->configExchange['name'] ?: $destination;

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
                'x-dead-letter-exchange'    => $destinationExchange,
                'x-dead-letter-routing-key' => $destination,
                'x-message-ttl'             => $delay * 1000,
            ])
        );

        // bind queue to the exchange
        $this->channel->queue_bind($name, $exchange, $name);

        return [$name, $exchange];
    }
}
