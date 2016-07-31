<?php
declare(strict_types=1);
namespace Viserio\Queue;

use Aws\Sqs\SqsClient;
use Interop\Container\ContainerInterface as ContainerInteropInterface;
use Narrowspark\Arr\StaticArr as Arr;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use Pheanstalk\{
    Pheanstalk,
    PheanstalkInterface
};
use Viserio\Contracts\{
    Config\Manager as ConfigContract,
    Encryption\Encrypter as EncrypterContract,
    Events\Dispatcher as DispatcherContract,
    Queue\Monitor as MonitorContract
};
use Viserio\Queue\Connectors\{
    AzureQueue,
    BeanstalkdQueue,
    NullQueue,
    RabbitMQQueue,
    RedisQueue,
    SqsQueue,
    SyncQueue
};
use Viserio\Support\AbstractConnectionManager;

class QueueManager extends AbstractConnectionManager implements MonitorContract
{
    /**
     * Encrypter instance.
     *
     * @var \Viserio\Contracts\Encryption\Encrypter
     */
    protected $encrypter;

    /**
     * Event Dispatcher instance.
     *
     * @var \Viserio\Contracts\Events\Dispatcher
     */
    protected $dispatcher;

    /**
     * Create a new queue manager instance.
     *
     * @param \Viserio\Contracts\Config\Manager       $config
     * @param \Interop\Container\ContainerInterface   $container
     * @param \Viserio\Contracts\Encryption\Encrypter $encrypter
     */
    public function __construct(
        ConfigContract $config,
        ContainerInteropInterface $container,
        EncrypterContract $encrypter
    ) {
        $this->config = $config;
        $this->container = $container;
        $this->encrypter = $encrypter;
    }

    /**
     * {@inheritdoc}
     */
    public function failing($callback)
    {
        $this->container->get('events')->on('viserio.job.failed', $callback);
    }

    /**
     * {@inheritdoc}
     */
    public function stopping($callback)
    {
        $this->container->get('events')->on('viserio.worker.stopping', $callback);
    }

    /**
     * {@inheritdoc}
     */
    public function exceptionOccurred($callback)
    {
        $this->container->get('events')->on('viserio.job.exception.occurred', $callback);
    }

    /**
     * Register an event listener for the before job event.
     *
     * @param mixed $callback
     *
     * @return void
     */
    public function before($callback)
    {
        $this->container->get('events')->on('viserio.job.processing', $callback);
    }

    /**
     * Register an event listener for the after job event.
     *
     * @param mixed $callback
     *
     * @return void
     */
    public function after($callback)
    {
        $this->container->get('events')->on('viserio.job.processed', $callback);
    }

    /**
     * {@inheritdoc}
     */
    public function connection(string $name = null)
    {
        $name = $name ?? $this->getDefaultConnection();

        if (! isset($this->connections[$name])) {
            $config = $this->getConnectionConfig($name);
            $connect = $this->createConnection($config);

            $connect->setContainer($this->container);
            $connect->setEncrypter($this->encrypter);

            $this->connections[$name] = $connect;
        }

        return $this->connections[$name];
    }

    /**
     * Get the event dispatcher implementation.
     *
     * @return \Viserio\Contracts\Events\Dispatcher
     */
    public function getEventDispatcher(): DispatcherContract
    {
        return $this->dispatcher;
    }

    /**
     * Set the event dispatcher implementation.
     *
     * @param \Viserio\Contracts\Events\Dispatcher $dispatcher
     *
     * @return $this
     */
    public function setEventDispatcher(DispatcherContract $dispatcher): QueueManager
    {
        $this->dispatcher = $dispatcher;

        return $this;
    }

    /**
     * Get the encrypter implementation.
     *
     * @return \Viserio\Contracts\Encryption\Encrypter
     */
    public function getEncrypter(): EncrypterContract
    {
        return $this->encrypter;
    }

    /**
     * Set the encrypter implementation.
     *
     * @param \Viserio\Contracts\Encryption\Encrypter $encrypter
     *
     * @return $this
     */
    public function setEncrypter(EncrypterContract $encrypter): QueueManager
    {
        $this->encrypter = $encrypter;

        return $this;
    }

    /**
     * Create Beanstalkd connection.
     *
     * @return \Viserio\Queue\Connectors\BeanstalkdQueue
     */
    protected function createBeanstalkdConnection(array $config): BeanstalkdQueue
    {
        $pheanstalk = new Pheanstalk(
            $config['host'],
            Arr::get($config, 'port', PheanstalkInterface::DEFAULT_PORT)
        );

        return new BeanstalkdQueue(
            $pheanstalk,
            $config['queue'],
            Arr::get($config, 'ttr', Pheanstalk::DEFAULT_TTR)
        );
    }

    /**
     * Create Null connection.
     *
     * @return \Viserio\Queue\Connectors\NullQueue
     */
    protected function createNullConnection(array $config): NullQueue
    {
        return new NullQueue();
    }

    /**
     * Create Sync connection.
     *
     * @return \Viserio\Queue\Connectors\SyncQueue
     */
    protected function createSyncConnection(array $config): SyncQueue
    {
        return new SyncQueue();
    }

    /**
     * Create Sqs connection.
     *
     * @return \Viserio\Queue\Connectors\SqsQueue
     */
    protected function createSqsConnection(array $config): SqsQueue
    {
        $config = array_merge([
            'version' => 'latest',
            'http' => [
                'timeout' => 60,
                'connect_timeout' => 60,
            ],
        ], $config);

        if ($config['key'] && $config['secret']) {
            $config['credentials'] = Arr::only($config, ['key', 'secret']);
        }

        return new SqsQueue(
            new SqsClient($config),
            $config['queue'],
            Arr::get($config, 'prefix', '')
        );
    }

    /**
     * Create Redis connection.
     *
     * @return \Viserio\Queue\Connectors\RedisQueue
     */
    protected function createRedisConnection(array $config): RedisQueue
    {
        $connect = new ConnectManager($this->config);

        $queue = new RedisQueue(
            $connect->connection($config['connection']),
            $config['queue'],
            Arr::get($config, 'expire', 90)
        );

        return $queue;
    }

    /**
     * Create RabbitMQ connection.
     *
     * @return \Viserio\Queue\Connectors\RabbitMQQueue
     */
    protected function createRabitmqConnection(array $config): RabbitMQQueue
    {
        $connection = new AMQPStreamConnection(
            $config['host'],
            $config['port'],
            $config['login'],
            $config['password'],
            $config['vhost']
        );

        return new RabbitMQQueue(
            $connection,
            $config
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfigName(): string
    {
        return 'queue';
    }
}
