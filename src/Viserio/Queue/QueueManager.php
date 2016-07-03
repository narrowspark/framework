<?php
namespace Viserio\Queue;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use Interop\Container\ContainerInterface as ContainerInteropInterface;
use Narrowspark\Arr\StaticArr as Arr;
use Pheanstalk\{
    Pheanstalk,
    PheanstalkInterface
};
use IronMQ\IronMQ;
use Psr\Http\Message\RequestInterface;
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
    BeanstalkdQueue,
    IronQueue,
    RabbitMQQueue
};
use Viserio\Support\AbstractConnectionManager;

class QueueManager extends AbstractConnectionManager implements MonitorContract
{
    /**
     * All supported drivers.
     *
     * @var array
     */
    protected $supportedDrivers = [
        'null'
    ];

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
     * The current request instance.
     *
     * @var \Psr\Http\Message\RequestInterface
     */
    protected $request;

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
     * Get the request instance.
     *
     * @return \Psr\Http\Message\RequestInterface
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Set the request instance.
     *
     * @param \Psr\Http\Message\RequestInterface $request
     *
     * @return $this
     */
    public function setRequest(RequestInterface $request): QueueManager
    {
        $this->request = $request;

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
     * Create IronMQ connection.
     */
    protected function createIronmqConnection(array $config): IronQueue
    {
        $ironConfig = ['token' => $config['token'], 'project_id' => $config['project']];

        if (isset($config['host'])) {
            $ironConfig['host'] = $config['host'];
        }

        $iron = new IronMQ($ironConfig);

        if (isset($config['ssl_verifypeer'])) {
            $iron->ssl_verifypeer = $config['ssl_verifypeer'];
        }

        return new IronQueue(
            $iron,
            $this->request,
            $config['queue'],
            $config['timeout']
        );
    }

    /**
     * Create RabbitMQ connection.
     */
    protected function createRabitmqconnection(array $config): RabbitMQQueue
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
