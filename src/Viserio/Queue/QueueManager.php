<?php
namespace Viserio\Queue;

use Interop\Container\ContainerInterface as ContainerInteropInterface;
use Narrowspark\Arr\StaticArr as Arr;
use Pheanstalk\{
    Pheanstalk,
    PheanstalkInterface
};
use Viserio\Contracts\{
    Config\Manager as ConfigContract,
    Encryption\Encrypter as EncrypterContract,
    Queue\Monitor as MonitorContract
};
use Viserio\Queue\Events\{
    WorkerStopping
};
use Viserio\Queue\Connectors\{
    BeanstalkdQueue
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
     * Constructor.b
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
            $connect->setEncrypter($this->container->get('encrypter'));

            $this->connections[$name] = $connect->connect($config);
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
     * @return self
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
     * @return self
     */
    public function setEncrypter(EncrypterContract $encrypter): QueueManager
    {
        $this->encrypter = $encrypter;

        return $this;
    }

    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    protected function getConfigName(): string
    {
        return 'queue';
    }
}
