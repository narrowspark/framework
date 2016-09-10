<?php
declare(strict_types=1);
namespace Viserio\Queue;

use Aws\Sqs\SqsClient;
use Interop\Container\ContainerInterface as ContainerInteropInterface;
use Narrowspark\Arr\StaticArr as Arr;
use Pheanstalk\Pheanstalk;
use Pheanstalk\PheanstalkInterface;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use RuntimeException;
use Viserio\Connect\ConnectManager;
use Viserio\Contracts\Config\Manager as ConfigContract;
use Viserio\Contracts\Container\Traits\ContainerAwareTrait;
use Viserio\Contracts\Encryption\Encrypter as EncrypterContract;
use Viserio\Contracts\Encryption\Traits\EncrypterAwareTrait;
use Viserio\Contracts\Events\Dispatcher as DispatcherContract;
use Viserio\Contracts\Events\Traits\EventsAwareTrait;
use Viserio\Contracts\Queue\Factory as FactoryContract;
use Viserio\Contracts\Queue\Monitor as MonitorContract;
use Viserio\Queue\Connectors\BeanstalkdQueue;
use Viserio\Queue\Connectors\NullQueue;
use Viserio\Queue\Connectors\RabbitMQQueue;
use Viserio\Queue\Connectors\RedisQueue;
use Viserio\Queue\Connectors\SqsQueue;
use Viserio\Queue\Connectors\SyncQueue;
use Viserio\Support\AbstractConnectionManager;

class QueueManager extends AbstractConnectionManager implements MonitorContract, FactoryContract
{
    use ContainerAwareTrait;
    use EventsAwareTrait;
    use EncrypterAwareTrait;

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
        $this->container->get(DispatcherContract::class)->attach('viserio.job.failed', $callback);
    }

    /**
     * {@inheritdoc}
     */
    public function stopping($callback)
    {
        $this->container->get(DispatcherContract::class)->attach('viserio.worker.stopping', $callback);
    }

    /**
     * {@inheritdoc}
     */
    public function exceptionOccurred($callback)
    {
        $this->container->get(DispatcherContract::class)->attach('viserio.job.exception.occurred', $callback);
    }

    /**
     * Register an event listener for the before job event.
     *
     * @param mixed $callback
     */
    public function before($callback)
    {
        $this->container->get(DispatcherContract::class)->attach('viserio.job.processing', $callback);
    }

    /**
     * Register an event listener for the after job event.
     *
     * @param mixed $callback
     */
    public function after($callback)
    {
        $this->container->get(DispatcherContract::class)->attach('viserio.job.processed', $callback);
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
     * {@inheritdoc}
     */
    public function getEventsDispatcher(): DispatcherContract
    {
        if (! $this->events || $this->container->has(DispatcherContract::class)) {
            throw new RuntimeException('Events dispatcher is not set up.');
        }

        return $this->events ?? $this->container->get(DispatcherContract::class);
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
