<?php

namespace Brainwave\Database\Connection;

/**
 * Narrowspark - a PHP 5 framework.
 *
 * @author      Daniel Bannert <info@anolilab.de>
 * @copyright   2015 Daniel Bannert
 *
 * @link        http://www.narrowspark.de
 *
 * @license     http://www.narrowspark.com/license
 *
 * @version     0.10.0-dev
 */

use Brainwave\Contracts\Container\Container as ContainerContract;
use Brainwave\Database\Connectors\GoogleCloudConnector;
use Brainwave\Database\Connectors\MariaDBConnector;
use Brainwave\Database\Connectors\MSSQLConnector;
use Brainwave\Database\Connectors\MySqlConnector;
use Brainwave\Database\Connectors\OracleConnector;
use Brainwave\Database\Connectors\OdbcConnection;
use Brainwave\Database\Connectors\PostgreSQLConnector;
use Brainwave\Database\Connectors\SQLiteConnector;
use Brainwave\Database\Connectors\SqlServerConnector;
use Brainwave\Database\Connectors\SybaseConnector;
use Brainwave\Support\Arr;

/**
 * ConnectionFactory.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.2-dev
 */
class ConnectionFactory
{
    /**
     * The container instance.
     *
     * @var \Brainwave\Contracts\Container\Container
     */
    protected $container;

    /**
     * Create a new connection factory instance.
     *
     * @param ContainerContract $container
     */
    public function __construct(ContainerContract $container)
    {
        $this->container = $container;
    }

    /**
     * Establish a PDO connection based on the configuration.
     *
     * @param array       $config
     * @param string|null $name
     *
     * @return Connection
     */
    public function make(array $config, $name = null)
    {
        $config = $this->parseConfig($config, $name);

        return $this->createSingleConnection($config);
    }

    /**
     * Parse and prepare the database configuration.
     *
     * @param array  $config
     * @param string $name
     *
     * @return array
     */
    protected function parseConfig(array $config, $name)
    {
        return Arr::add(Arr::add($config, 'prefix', ''), 'name', $name);
    }

    /**
     * Create a single database connection instance.
     *
     * @param array $config
     *
     * @return Connection
     */
    protected function createSingleConnection(array $config)
    {
        $pdo = $this->createConnector($config)->connect($config);

        return $this->createConnection(
            $pdo,
            $config['dbname'],
            $config['prefix'],
            $config
        );
    }

    /**
     * Create a connector instance based on the configuration.
     *
     * @param array $config
     *
     * @throws \InvalidArgumentException
     *
     * @return PostgreSQLConnector|MSSQLConnector|MySqlConnector|SybaseConnector|GoogleCloudConnector|SQLiteConnector|SqlServerConnector|OracleConnector|OdbcConnector|FirebirdConnector
     */
    public function createConnector(array $config)
    {
        if (!isset($config['driver'])) {
            throw new \InvalidArgumentException('A driver must be specified.');
        }

        if ($this->container->bound($key = "db.connector.{$config['driver']}")) {
            return $this->container->make($key);
        }

        switch ($config['driver']) {
            case 'mysql':
                $connector = new MySqlConnector();
                break;
            case 'mariadb':
                $connector = new MariaDBConnector();
                break;
            case 'pgsql':
                $connector = new PostgreSQLConnector();
                break;
            case 'mssql':
                $connector = new MSSQLConnector();
                break;
            case 'sybase':
                $connector = new SybaseConnector();
                break;
            case 'cloudsql':
                $connector = new GoogleCloudConnector();
                break;
            case 'sqlite':
                $connector = new SQLiteConnector();
                break;
            case 'sqlsrv':
                $connector = new SqlServerConnector();
                break;
            case 'oracle':
                $connector = new OracleConnector();
                break;
            case 'odbc':
                $connector = new OdbcConnector();
                break;
            case 'firebird':
                $connector = new FirebirdConnector();
                break;
            default:
                throw new \InvalidArgumentException(sprintf('Unsupported driver [$s]', $config['driver']));
        }

        $this->container->bind(sprintf('db.connector.%s', $config['driver']), function ($connector) {
            return $connector;
        });

        return $connector;
    }

    /**
     * Create a new connection instance.
     *
     * @param \PDO   $connection
     * @param string $database
     * @param string $prefix
     * @param array  $config
     *
     * @throws \InvalidArgumentException
     *
     * @return Connection
     */
    protected function createConnection(
        \PDO $connection,
        $database,
        $prefix = '',
        array $config = []
    ) {
        return new Connection($connection, $database, $prefix, $config);
    }
}
