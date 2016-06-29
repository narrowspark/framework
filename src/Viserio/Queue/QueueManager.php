<?php
namespace Viserio\Queue;

use Interop\Container\ContainerInterface as ContainerInteropInterface;
use Viserio\Contracts\{
    Config\Manager as ConfigContract,
    Queue\Monitor as MonitorContract
};
use Viserio\Queue\Events\{
    WorkerStopping
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
     * @var EncrypterContract
     */
    private $encrypter;

    /**
     * Constructor.b
     *
     * @param \Viserio\Contracts\Config\Manager     $config
     * @param \Interop\Container\ContainerInterface $container
     */
    public function __construct(
        ConfigContract $config,
        ContainerInteropInterface $container
    ) {
        $this->config = $config;
        $this->container = $container;
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
     * {@inheritdoc}
     */
    protected function getConfigName(): string
    {
        return 'queue';
    }
}
