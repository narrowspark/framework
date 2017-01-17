<?php
declare(strict_types=1);
namespace Viserio\Component\Queue;

use Aws\Sqs\SqsClient;
use Interop\Container\ContainerInterface as ContainerInteropInterface;
use Narrowspark\Arr\Arr;
use Pheanstalk\Pheanstalk;
use Pheanstalk\PheanstalkInterface;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use Viserio\Component\Connect\ConnectManager;
use Viserio\Component\Contracts\Config\Repository as RepositoryContract;
use Viserio\Component\Contracts\Container\Traits\ContainerAwareTrait;
use Viserio\Component\Contracts\Encryption\Encrypter as EncrypterContract;
use Viserio\Component\Contracts\Encryption\Traits\EncrypterAwareTrait;
use Viserio\Component\Contracts\Events\EventManager as EventManagerContract;
use Viserio\Component\Contracts\Events\Traits\EventsAwareTrait;
use Viserio\Component\Contracts\Queue\Factory as FactoryContract;
use Viserio\Component\Contracts\Queue\Monitor as MonitorContract;
use Viserio\Component\Queue\Connectors\BeanstalkdQueue;
use Viserio\Component\Queue\Connectors\NullQueue;
use Viserio\Component\Queue\Connectors\RabbitMQQueue;
use Viserio\Component\Queue\Connectors\RedisQueue;
use Viserio\Component\Queue\Connectors\SqsQueue;
use Viserio\Component\Queue\Connectors\SyncQueue;
use Viserio\Component\Support\AbstractConnectionManager;

class QueueManager extends AbstractConnectionManager implements MonitorContract, FactoryContract
{
    use ContainerAwareTrait;
    use EventsAwareTrait;
    use EncrypterAwareTrait;

    /**
     * Create a new queue manager instance.
     *
     * @param \Viserio\Component\Contracts\Config\Repository    $config
     * @param \Interop\Container\ContainerInterface             $container
     * @param \Viserio\Component\Contracts\Encryption\Encrypter $encrypter
     */
    public function __construct(
        RepositoryContract $config,
        ContainerInteropInterface $container,
        EncrypterContract $encrypter
    ) {
        $this->config    = $config;
        $this->container = $container;
        $this->encrypter = $encrypter;
    }

    /**
     * {@inheritdoc}
     */
    public function failing($callback)
    {
        $this->container->get(EventManagerContract::class)->attach('viserio.job.failed', $callback);
    }

    /**
     * {@inheritdoc}
     */
    public function stopping($callback)
    {
        $this->container->get(EventManagerContract::class)->attach('viserio.worker.stopping', $callback);
    }

    /**
     * {@inheritdoc}
     */
    public function exceptionOccurred($callback)
    {
        $this->container->get(EventManagerContract::class)->attach('viserio.job.exception.occurred', $callback);
    }

    /**
     * Register an event listener for the before job event.
     *
     * @param mixed $callback
     */
    public function before($callback)
    {
        $this->container->get(EventManagerContract::class)->attach('viserio.job.processing', $callback);
    }

    /**
     * Register an event listener for the after job event.
     *
     * @param mixed $callback
     */
    public function after($callback)
    {
        $this->container->get(EventManagerContract::class)->attach('viserio.job.processed', $callback);
    }

    /**
     * {@inheritdoc}
     */
    public function connection(string $name = null)
    {
        $name = $name ?? $this->getDefaultConnection();

        if (! isset($this->connections[$name])) {
            $config  = $this->getConnectionConfig($name);
            $connect = $this->createConnection($config);

            $connect->setContainer($this->container);
            $connect->setEncrypter($this->encrypter);

            $this->connections[$name] = $connect;
        }

        return $this->connections[$name];
    }

    /**
     * Create Beanstalkd connection.
     *
     * @param array $config
     *
     * @return \Viserio\Component\Queue\Connectors\BeanstalkdQueue
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
     * @param array $config
     *
     * @return \Viserio\Component\Queue\Connectors\NullQueue
     */
    protected function createNullConnection(array $config): NullQueue
    {
        return new NullQueue();
    }

    /**
     * Create Sync connection.
     *
     * @param array $config
     *
     * @return \Viserio\Component\Queue\Connectors\SyncQueue
     */
    protected function createSyncConnection(array $config): SyncQueue
    {
        return new SyncQueue();
    }

    /**
     * Create Sqs connection.
     *
     * @param array $config
     *
     * @return \Viserio\Component\Queue\Connectors\SqsQueue
     */
    protected function createSqsConnection(array $config): SqsQueue
    {
        $config = array_merge([
            'version' => 'latest',
            'http'    => [
                'timeout'         => 60,
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
     * @param array $config
     *
     * @return \Viserio\Component\Queue\Connectors\RedisQueue
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
     * @param array $config
     *
     * @return \Viserio\Component\Queue\Connectors\RabbitMQQueue
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
