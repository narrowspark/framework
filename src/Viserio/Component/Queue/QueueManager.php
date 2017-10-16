<?php
declare(strict_types=1);
namespace Viserio\Component\Queue;

use Aws\Sqs\SqsClient;
use Narrowspark\Arr\Arr;
use Pheanstalk\Pheanstalk;
use Pheanstalk\PheanstalkInterface;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use Psr\Container\ContainerInterface as ContainerInteropInterface;
use Viserio\Component\Contract\Encryption\Encrypter as EncrypterContract;
use Viserio\Component\Contract\Encryption\Traits\EncrypterAwareTrait;
use Viserio\Component\Contract\Events\EventManager as EventManagerContract;
use Viserio\Component\Contract\Events\Traits\EventManagerAwareTrait;
use Viserio\Component\Contract\Queue\Factory as FactoryContract;
use Viserio\Component\Contract\Queue\Monitor as MonitorContract;
use Viserio\Component\Queue\Connector\BeanstalkdQueue;
use Viserio\Component\Queue\Connector\NullQueue;
use Viserio\Component\Queue\Connector\RabbitMQQueue;
use Viserio\Component\Queue\Connector\RedisQueue;
use Viserio\Component\Queue\Connector\SqsQueue;
use Viserio\Component\Queue\Connector\SyncQueue;
use Viserio\Component\Support\AbstractConnectionManager;

class QueueManager extends AbstractConnectionManager implements MonitorContract, FactoryContract
{
    use EventManagerAwareTrait;
    use EncrypterAwareTrait;

    /**
     * Create a new queue manager instance.
     *
     * @param \Psr\Container\ContainerInterface                $container
     * @param \Viserio\Component\Contract\Encryption\Encrypter $encrypter
     */
    public function __construct(
        ContainerInteropInterface $container,
        EncrypterContract $encrypter
    ) {
        $this->container = $container;
        $this->encrypter = $encrypter;

        parent::__construct($container);
    }

    /**
     * {@inheritdoc}
     */
    public function failing($callback): void
    {
        $this->container->get(EventManagerContract::class)->attach('viserio.job.failed', $callback);
    }

    /**
     * {@inheritdoc}
     */
    public function stopping($callback): void
    {
        $this->container->get(EventManagerContract::class)->attach('viserio.worker.stopping', $callback);
    }

    /**
     * {@inheritdoc}
     */
    public function exceptionOccurred($callback): void
    {
        $this->container->get(EventManagerContract::class)->attach('viserio.job.exception.occurred', $callback);
    }

    /**
     * Register an event listener for the before job event.
     *
     * @param mixed $callback
     */
    public function before($callback): void
    {
        $this->container->get(EventManagerContract::class)->attach('viserio.job.processing', $callback);
    }

    /**
     * Register an event listener for the after job event.
     *
     * @param mixed $callback
     */
    public function after($callback): void
    {
        $this->container->get(EventManagerContract::class)->attach('viserio.job.processed', $callback);
    }

    /**
     * {@inheritdoc}
     */
    public function getConnection(string $name = null)
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
     * @return \Viserio\Component\Queue\Connector\BeanstalkdQueue
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
     * @return \Viserio\Component\Queue\Connector\NullQueue
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
     * @return \Viserio\Component\Queue\Connector\SyncQueue
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
     * @return \Viserio\Component\Queue\Connector\SqsQueue
     */
    protected function createSqsConnection(array $config): SqsQueue
    {
        $config = \array_merge([
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
     * @return \Viserio\Component\Queue\Connector\RedisQueue
     */
    protected function createRedisConnection(array $config): RedisQueue
    {
        $queue = new RedisQueue(
            $this->container->get('redis'),
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
     * @return \Viserio\Component\Queue\Connector\RabbitMQQueue
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
    protected static function getConfigName(): string
    {
        return 'queue';
    }
}
