<?php
namespace Viserio\Support;

use Closure;
use RuntimeException;
use Viserio\Contracts\Config\Manager as ConfigContract;
use Viserio\Support\Traits\ContainerAwareTrait;

abstract class AbstractConnectionManager
{
    use ContainerAwareTrait;

    /**
     * All supported connectors.
     *
     * @var array
     */
    protected $supportedConnectors = [];

    /**
     * The active connection instances.
     *
     * @var array
     */
    protected $connections = [];

    /**
     * The custom connection resolvers.
     *
     * @var array
     */
    protected $customCreators = [];

    /**
     * Conifg instace.
     *
     * @var \Viserio\Contracts\Config\Manager
     */
    protected $config;

    /**
     * Create a new manager instance.
     *
     * @param \Viserio\Contracts\Config\Manager $config
     *
     * @return void
     */
    public function __construct(ConfigContract $config)
    {
        $this->config = $config;
    }

    /**
     * Dynamically pass methods to the default connection.
     *
     * @param string $method
     * @param array  $parameters
     *
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return call_user_func_array([$this->connection(), $method], $parameters);
    }

    /**
     * Set a config manager
     *
     * @param \Viserio\Contracts\Config\Manager $config
     *
     * @return void
     */
    public function setConfig(ConfigContract $config)
    {
        $this->config = $config;
    }

    /**
     * Get the config instance.
     *
     * @return \Viserio\Contracts\Config\Manager
     */
    public function getConfig(): ConfigContract
    {
        return $this->config;
    }

    /**
     * Get a connection instance.
     *
     * @param string $connectionName
     * @param array  $config
     *
     * @return object
     */
    public function connection(string $connectionName, array $config = [])
    {
        $connectionName = $connectionName ?? $this->getDefaultDriver();

        if (! $this->hasDriver($connectionName)) {
            throw new RuntimeException(
                sprintf('The connection [%s] is not supported.', $connectionName)
            );
        }

        // If the given driver has not been created before, we will create the instances
        // here and cache it so we can return it next time very quickly. If there is
        // already a driver created by this name, we'll just return that instance.
        if (! isset($this->connections[$connectionName])) {
            $config['name'] = $connectionName;

            $settings = array_merge(
                $this->getConnectionConfig($connectionName),
                $config
            );

            $this->connections[$connectionName] = $this->createDriver($connectionName, $settings);
        }

        return $this->connections[$connectionName];
    }

    /**
     * Reconnect to the given connection.
     *
     * @param string $name
     *
     * @return object
     */
    public function reconnect(string $name = null)
    {
        $name = $name ?? $this->getDefaultConnection();

        $this->disconnect($name);

        return $this->connection($name);
    }

     /**
     * Disconnect from the given connection.
     *
     * @param string $name
     *
     * @return void
     */
    public function disconnect(string $name = null)
    {
        $name = $name ?? $this->getDefaultConnection();

        unset($this->connections[$name]);
    }

    /**
     * Get the default connection name.
     *
     * @return string
     */
    public function getDefaultConnection(): string
    {
        return $this->config->get($this->getConfigName() . '.default');
    }

    /**
     * Set the default connection name.
     *
     * @param string $name
     *
     * @return void
     */
    public function setDefaultConnection(string $name)
    {
        $this->config->set($this->getConfigName() . '.default', $name);
    }

     /**
     * Register a custom connection creator.
     *
     * @param string   $driver
     * @param \Closure $callback
     *
     * @return void
     */
    public function extend(string $driver, Closure $callback)
    {
        $this->customCreators[$driver] = $callback->bindTo($this, $this);
    }

    /**
     * Return all of the created connections.
     *
     * @return object[]
     */
    public function getConnections(): array
    {
        return $this->connections;
    }

    /**
     * Check if the given connect is supported.
     *
     * @param string $connect
     *
     * @return bool
     */
    public function hasConnection(string $connect): bool
    {
        return isset($this->supportedConnectors[$connect]) ||
            in_array($connect, $this->supportedConnectors, true) ||
            isset($this->customCreators[$connect]);
    }

    /**
     * Get the configuration for a connection.
     *
     * @param string $name
     *
     * @throws \InvalidArgumentException
     *
     * @return array
     */
    public function getConnectionConfig(string $name): array
    {
        $name = $name ?? $this->getDefaultConnection();

        $connections = $this->config->get($this->getConfigName() . '.connections');

        if (!isset($connections[$name]) && !is_array($connections[$name])) {
            return [
                'name' => $name
            ];
        }

        $config = $connections[$name];
        $config['name'] = $name;

        return $config;
    }

    /**
     * Create the connection instance.
     *
     * @param string $connection
     * @param array  $config
     *
     * @return mixed
     */
    protected function createConnection(string $connection, array $config)
    {
         $method = 'create' . Str::studly($connection) . 'Connection';

        // We'll check to see if a creator method exists for the given connection. If not we
        // will check for a custom connection creator, which allows developers to create
        // connections using their own customized connection creator Closure to create it.
        if (isset($this->customCreators[$connection])) {
            return $this->callCustomCreator($connection, $config);
        } elseif (method_exists($this, $method)) {
            return $this->$method($config);
        } elseif (isset($this->supportedConnectors[$connection]) &&
            class_exists($this->supportedConnectors[$connection])
        ) {
            return new $this->supportedConnectors[$connection]();
        }

        throw new RuntimeException(sprintf('Connection [%s] not supported.', $connection));
    }

    /**
     * Call a custom connection creator.
     *
     * @param string $connection
     * @param array  $config
     *
     * @return mixed
     */
    protected function callCustomCreator(string $connection, array $config = [])
    {
        return $this->customCreators[$connection]($config);
    }

     /**
     * Get the configuration name.
     *
     * @return string
     */
    abstract protected function getConfigName(): string;
}
