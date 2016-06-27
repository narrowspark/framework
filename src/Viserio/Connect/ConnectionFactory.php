<?php
namespace Viserio\Connect;

use Interop\Container\ContainerInterface;
use PDO;
use RuntimeException;
use Viserio\Connect\Adapters\Database\{
    DblibConnector,
    Firebirdconnector,
    GoogleCloudSQLConnector,
    MariaDBConnector,
    MongoConnector,
    MSSQLConnector,
    MySqlConnector,
    OdbcConnection,
    OracleConnector,
    PostgreSQLConnector,
    SQLiteConnector,
    SqlServerConnector
};
use Viserio\Connect\Adapters\{
    MemcachedConnector,
    PredisConnector
};
use Viserio\Contracts\Connect\{
    ConnectionFactory as ConnectionFactoryContract,
    Connector as ConnectorContract
};

class ConnectionFactory implements ConnectionFactoryContract
{
    /**
     * All supported connectors.
     *
     * @var array
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
     * @var \Interop\Container\ContainerInterface
     */
    protected $container;

    /**
     * @param \Interop\Container\ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function connection(string $name)
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
    public function reconnect(string $name)
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
    public function getConnectionConfig(string $name): array
    {
        return $this->container->get($name, []);
    }

    /**
     * {@inheritdoc}
     */
    public function extend(string $name, ConnectorContract $resolver): ConnectionFactoryContract
    {
        $this->extensions[$name] = $resolver;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getExtensions(): array
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
     * Get the container instance.
     *
     * @return \Interop\Container\ContainerInterface
     */
    public function getContainer(): ContainerInterface
    {
        return $this->container;
    }

    /**
     * All supported PDO drivers.
     *
     * @return string[]
     */
    public function supportedPDODrivers(): array
    {
        return ['mysql', 'pgsql', 'sqlite', 'sqlsrv', 'dblib'];
    }

    /**
     * Get all available drivers on system.
     *
     * @return array
     */
    public function getAvailableDrivers(): array
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
     * @return object
     */
    protected function makeConnection($name, array $config)
    {
        if (array_key_exists($name, $this->connectors)) {
            return (new $this->connectors[$name]())->connect($config);
        } elseif (array_key_exists($name, $this->extensions)) {
            return (new $this->extensions[$name]())->connect($config);
        }

        throw new RuntimeException($name . ' connector dont exist.');
    }
}
