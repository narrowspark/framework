<?php
namespace Viserio\Support;

use Closure;
use RuntimeException;
use Viserio\Contracts\{
    Config\Manager as ConfigContract,
    Support\Connector as ConnectorContract
};
use Viserio\Support\Traits\ContainerAwareTrait;

abstract class AbstractConnectionManager
{
    use ContainerAwareTrait;

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
    protected $extensions = [];

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
     * Get a connection instance.
     *
     * @param string|null $name
     * @param array       $config
     *
     * @return object
     */
    public function connection(string $name = null, array $config = [])
    {
        $name = $name ?? $this->getDefaultConnection();

        if (! $this->hasConnection($name)) {
            throw new RuntimeException(
                sprintf('The connection [%s] is not supported.', $name)
            );
        }

        // If the given driver has not been created before, we will create the instances
        // here and cache it so we can return it next time very quickly. If there is
        // already a driver created by this name, we'll just return that instance.
        if (! isset($this->connections[$name])) {
            $settings = array_merge(
                $this->getConnectionConfig($name),
                $config
            );

            $this->connections[$name] = $this->makeConnection($settings);
        }

        return $this->connections[$name];
    }

    /**
     * Reconnect to the given connection.
     *
     * @param string|null $name
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
     * @param string|null $name
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
        return $this->config->get($this->getConfigName() . '.default', '');
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
        $this->extensions[$driver] = $callback->bindTo($this, $this);
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
            isset($this->extensions[$connect]);
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

        $connections = $this->config->get($this->getConfigName() . '.connections', []);

        if (! isset($connections[$name])) {
            return [
                'name' => $name
            ];
        }

        $config = $connections[$name];
        $config['name'] = $name;

        return $config;
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
     * Create the connection instance.
     *
     * @param array $config
     *
     * @return mixed
     */
    abstract protected function createConnection(array $config);

    /**
     * Create the connection instance.
     *
     * @param array $config
     *
     * @return mixed
     */
    protected function makeConnection(array $config)
    {
        $method = 'create' . Str::studly($config['name']) . 'Connection';

        if (isset($this->extensions[$config['name']])) {
            return $this->callCustomCreator($config['name'], $config);
        } elseif (method_exists($this, $method)) {
            return $this->$method($config);
        }

        return $this->createConnection($config);
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
        return $this->extensions[$connection]($config);
    }

     /**
     * Get the configuration name.
     *
     * @return string
     */
    abstract protected function getConfigName(): string;
}
