<?php
namespace Viserio\Queue;

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
use Viserio\Queue\Events\{
    WorkerStopping
};
use Viserio\Queue\Connectors\{
    AzureQueue,
    BeanstalkdQueue,
    RabbitMQQueue
};
use Viserio\Support\AbstractConnectionManager;
use WindowsAzure\Common\ServicesBuilder;

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
    public function looping($callback)
    {
        $this->container->get('events')->on('', $callback);
    }

    /**
     * {@inheritdoc}
     */
    public function failing($callback)
    {
        $this->container->get('events')->on('', $callback);
    }

    /**
     * {@inheritdoc}
     */
    public function stopping($callback)
    {
        $this->container->get('events')->on(WorkerStopping::class, $callback);
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
    public function getDispatcher(): DispatcherContract
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
    public function setDispatcher(DispatcherContract $dispatcher): QueueManager
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
     * @param \Viserio\Contracts\Encryption\Encrypter  $encrypter
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
     * Create RabbitMQ connection.
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
     * Create RabbitMQ connection.
     */
    protected function createAzureConnection(array $config): RabbitMQQueue
    {
        $connectionString = sprintf(
            'DefaultEndpointsProtocol=%s;AccountName=%s;AccountKey=%s',
            $config['protocol'],
            $config['account'],
            $config['key']
        );

        $azure = ServicesBuilder::getInstance()->createQueueService($connectionString);

        return new AzureQueue($azure, $config['queue']);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfigName(): string
    {
        return 'queue';
    }
}
