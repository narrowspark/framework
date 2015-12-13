<?php
namespace Viserio\Connect;

use InvalidArgumentException;
use PDO;
use RuntimeException;
use Viserio\Connect\Adapters\Database\DblibConnector;
use Viserio\Connect\Adapters\Database\Firebirdconnector;
use Viserio\Connect\Adapters\Database\GoogleCloudSQLConnector;
use Viserio\Connect\Adapters\Database\MariaDBConnector;
use Viserio\Connect\Adapters\Database\MongoConnector;
use Viserio\Connect\Adapters\Database\MSSQLConnector;
use Viserio\Connect\Adapters\Database\MySqlConnector;
use Viserio\Connect\Adapters\Database\OdbcConnection;
use Viserio\Connect\Adapters\Database\OracleConnector;
use Viserio\Connect\Adapters\Database\PostgreSQLConnector;
use Viserio\Connect\Adapters\Database\SQLiteConnector;
use Viserio\Connect\Adapters\Database\SqlServerConnector;
use Viserio\Connect\Adapters\MemcachedConnector;
use Viserio\Connect\Adapters\PredisConnector;
use Viserio\Contracts\Config\Manager as ConfigManager;
use Viserio\Contracts\Connect\ConnectionFactory as ConnectionFactoryContract;
use Viserio\Contracts\Connect\Connector as ConnectorContract;

class ConnectionFactory implements ConnectionFactoryContract
{
    /**
     * All supported connectors.
     *
     * @var array;
     */
    protected $connectors = [
        'dblib'          => DblibConnector::class,
        'firebird'       => Firebirdconnector::class,
        'googlecloudsql' => GoogleCloudSQLConnector::class,
        'mariadb'        => MariaDBConnector::class,
        'mongo'          => MongoConnector::class,
        'mssql'          => MSSQLConnector::class,
        'mysql'          => MySqlConnector::class,
        'odbc'           => OdbcConnection::class,
        'oracle'         => OracleConnector::class,
        'postgresql'     => PostgreSQLConnector::class,
        'sqllite'        => SQLiteConnector::class,
        'sqlserver'      => SqlServerConnector::class,
        'sybase'         => SybaseConnector::class,
        'memcached'      => MemcachedConnector::class,
        'predis'         => PredisConnector::class,
    ];

    /**
     * The active connection instance.
     *
     * @var object
     */
    protected $connection;

    /**
     * The custom connection resolvers.
     *
     * @var array
     */
    protected $extensions = [];

    /**
     * Conifg instace.
     *
     * @var ConfigManager
     */
    protected $config;

    /**
     * @param ConfigManager $config
     */
    public function __construct(ConfigManager $config)
    {
        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     */
    public function connection($name)
    {
        if ($this->connection === null) {
            $this->connection = $this->makeConnection(
                $name,
                $this->getConnectionConfig($name)
            );
        }

        return $this->connection;
    }

    /**
     * {@inheritdoc}
     */
    public function reconnect($name)
    {
        $this->disconnect();

        return $this->connection($name);
    }

    /**
     * {@inheritdoc}
     */
    public function disconnect()
    {
        $this->connection = null;
    }

    /**
     * {@inheritdoc}
     */
    public function getConnectionConfig($name)
    {
        return $this->getConfig()->get($name, []);
    }

    /**
     * {@inheritdoc}
     */
    public function extend($name, ConnectorContract $resolver)
    {
        $this->extensions[$name] = $resolver;
    }

    /**
     * {@inheritdoc}
     */
    public function getExtensions()
    {
        return $this->extensions;
    }

    /**
     * {@inheritdoc}
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * Get the config instance.
     *
     * @return \Viserio\Contracts\Config\Manager
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * All supported PDO drivers.
     *
     * @return array
     */
    public function supportedPDODrivers()
    {
        return ['mysql', 'pgsql', 'sqlite', 'sqlsrv', 'dblib'];
    }

    /**
     * Get all available drivers on system.
     *
     * @return array
     */
    public function getAvailableDrivers()
    {
        $drivers = [];
        $pdoDrivers = array_intersect(
            $this->supportedPDODrivers(),
            str_replace('dblib', 'sqlsrv', PDO::getAvailableDrivers())
        );

        foreach ($pdoDrivers as $driver) {
            $drivers[] = $driver;
        }

        return $drivers;
    }

    /**
     * Create a existend connection.
     *
     * @param string $name
     * @param array  $config
     *
     * @throws \RuntimeException
     *
     * @return ConnectorContract
     */
    protected function makeConnection($name, array $config)
    {
        if (array_key_exists($name, $this->connectors)) {
            return (new $this->connectors[$name])->connect($config);
        } elseif (array_key_exists($name, $this->extensions)) {
            return (new $this->extensions[$name])->connect($config);
        }

        throw new RuntimeException($name.' connector dont exist.');
    }
}
