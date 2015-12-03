<?php
namespace Viserio\Database;

use Viserio\Contracts\Cache\Manager as CacheContract;
use Viserio\Contracts\Config\Manager as ConfigContract;
use Viserio\Contracts\Database\Connection as ConnectionContract;
use Viserio\Contracts\Database\ConnectionResolver as ConnectionResolverContract;
use Viserio\Database\Connection\ConnectionFactory;
use Viserio\Support\Arr;

class DatabaseManager implements ConnectionResolverContract
{
    /**
     * The application instance.
     *
     * @var \Viserio\Contracts\Config\Manager
     */
    protected $config;

    /**
     * The cache instance.
     *
     * @var \Viserio\Contracts\Cache\Manager|null
     */
    protected $cache = null;

    /**
     * The database connection factory instance.
     *
     * @var \Viserio\Database\Connection\ConnectionFactory
     */
    protected $factory;

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
     * Create a new database manager instance.
     *
     * @param \Viserio\Contracts\Config\Manager              $config
     * @param \Viserio\Database\Connection\ConnectionFactory $factory
     */
    public function __construct(ConfigContract $config, ConnectionFactory $factory)
    {
        $this->config = $config;
        $this->factory = $factory;
    }

    /**
     * Set a cache instance.
     *
     * @param \Viserio\Contracts\Cache\Manager $cache
     *
     * @return self
     */
    public function setCache(CacheContract $cache)
    {
        $this->cache = $cache;

        return $this;
    }

    /**
     * Get a database connection instance.
     *
     * @param string|null $name
     *
     * @return \Viserio\Database\Connection\Connection
     */
    public function connection($name = null)
    {
        // If we haven't created this connection, we'll create it based on the config
        // provided in the application. Once we've created the connections we will
        // set the "fetch mode" for PDO which determines the query return types.
        if (!isset($this->connections[$name])) {
            $connection = $this->makeConnection($name);

            $this->connections[$name] = $this->prepare($connection);
        }

        return $this->connections[$name];
    }

    /**
     * Disconnect from the given database and remove from local cache.
     *
     * @param string|null $name
     */
    public function purge($name = null)
    {
        $this->disconnect($name);

        unset($this->connections[$name]);
    }

    /**
     * Disconnect from the given database.
     *
     * @param string|null $name
     */
    public function disconnect($name = null)
    {
        if (isset($this->connections[$name = $name ?: $this->getDefaultConnection()])) {
            $this->connections[$name]->disconnect();
        }
    }

    /**
     * Reconnect to the given database.
     *
     * @param string|null $name
     *
     * @return \Viserio\Database\Connection\Connection
     */
    public function reconnect($name = null)
    {
        $this->disconnect($name = $name ?: $this->getDefaultConnection());

        if (!isset($this->connections[$name])) {
            return $this->connection($name);
        } else {
            return $this->refreshPdoConnections($name);
        }
    }

    /**
     * Refresh the PDO connections on a given connection.
     *
     * @param string $name
     *
     * @return \Viserio\Database\Connection\Connection
     */
    protected function refreshPdoConnections($name)
    {
        $fresh = $this->makeConnection($name);

        return $this->connections[$name]->setPdo($fresh->getPdo());
    }

    /**
     * Make the database connection instance.
     *
     * @param string $name
     *
     * @return \Viserio\Database\Connection\Connection
     */
    protected function makeConnection($name)
    {
        $config = $this->getConfig($name);

        // First we will check by the connection name to see if an extension has been
        // registered specifically for that connection. If it has we will call the
        // Closure and pass it the config allowing it to resolve the connection.
        if (isset($this->extensions[$name])) {
            return call_user_func($this->extensions[$name], $config, $name);
        }

        $driver = $config['driver'];

        // Next we will check to see if an extension has been registered for a driver
        // and will call the Closure if so, which allows us to have a more generic
        // resolver for the drivers themselves which applies to all connections.
        if (isset($this->extensions[$driver])) {
            return call_user_func($this->extensions[$driver], $config, $name);
        }

        return $this->factory->make($config, $name);
    }

    /**
     * Prepare the database connection instance.
     *
     * @param ConnectionContract $connection
     *
     * @return \Viserio\Database\Connection\Connection
     */
    protected function prepare(ConnectionContract $connection)
    {
        $connection->setFetchMode($this->config->get('database::fetch'));

        // The database connection can also utilize a cache manager instance when cache
        // functionality is used on queries, which provides an expressive interface
        // to caching fluent queries that are executed.
        if ($this->cache !== null) {
            $connection->setCacheManager($this->cache);
        }

        // Here we'll set a reconnector callback. This reconnector can be any callable
        // so we will set a Closure to reconnect from this manager with the name of
        // the connection, which will allow us to reconnect from the connections.
        $connection->setReconnector(function (ConnectionContract $connection) {
            $this->reconnect($connection->getName());
        });

        return $connection;
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
    protected function getConfig($name = '')
    {
        $name = $name !== '' ? $name : $this->getDefaultConnection();

        // To get the database connection configuration, we will just pull each of the
        // connection configurations and get the configurations for the given name.
        // If the configuration doesn't exist, we'll throw an exception and bail.
        $connections = $this->config->get('database::connections');
        $config = Arr::get($connections, $name);

        if ($config === null) {
            throw new \InvalidArgumentException(sprintf('Database [%s] not configured.', $name));
        }

        return $config;
    }

    /**
     * Get the default connection name.
     *
     * @return string
     */
    public function getDefaultConnection()
    {
        return $this->config->get('database::default');
    }

    /**
     * Set the default connection name.
     *
     * @param string $name
     */
    public function setDefaultConnection($name)
    {
        $this->config->set('database::default', $name);
    }

    /**
     * Register an extension connection resolver.
     *
     * @param string   $name
     * @param callable $resolver
     */
    public function extend($name, callable $resolver)
    {
        $this->extensions[$name] = $resolver;
    }

    /**
     * Return all of the created connections.
     *
     * @return array
     */
    public function getConnections()
    {
        return $this->connections;
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
}
