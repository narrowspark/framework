<?php
declare(strict_types=1);
namespace Viserio\Connect;

use PDO;
use Predis\Client as PredisClient;
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
use Viserio\Contracts\Connect\ConnectManager as ConnectManagerContract;
use Viserio\Support\AbstractConnectionManager;

class ConnectManager extends AbstractConnectionManager implements ConnectManagerContract
{
    /**
     * {@inheritdoc}
     */
    public function supportedPDODrivers(): array
    {
        return ['mysql', 'pgsql', 'sqlite', 'sqlsrv', 'dblib'];
    }

    /**
     * {@inheritdoc}
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
     * Create an instance of the Dblib connection.
     *
     * @param array $config
     *
     * @return \PDO
     */
    protected function createDblibConnection(array $config)
    {
        return (new DblibConnector())->connect($config);
    }

    /**
     * Create an instance of the Firebird connection.
     *
     * @param array $config
     *
     * @return \PDO
     */
    protected function createFirebirdConnection(array $config): PDO
    {
        return (new Firebirdconnector())->connect($config);
    }

    /**
     * Create an instance of the Googlecloudsql connection.
     *
     * @param array $config
     *
     * @return \PDO
     */
    protected function createGooglecloudsqlConnection(array $config): PDO
    {
        return (new GoogleCloudSQLConnector())->connect($config);
    }

    /**
     * Create an instance of the MariaDB connection.
     *
     * @param array $config
     *
     * @return \PDO
     */
    protected function createMariadbConnection(array $config): PDO
    {
        return (new MariaDBConnector())->connect($config);
    }

    /**
     * Create an instance of the Mongo connection.
     *
     * @param array $config
     *
     * @return \Mongo
     */
    protected function createMongoConnection(array $config)
    {
        return (new MongoConnector())->connect($config);
    }

    /**
     * Create an instance of the MSSQL connection.
     *
     * @param array $config
     *
     * @return \PDO
     */
    protected function createMssqlConnection(array $config): PDO
    {
        return (new MSSQLConnector())->connect($config);
    }

    /**
     * Create an instance of the MySql connection.
     *
     * @param array $config
     *
     * @return \PDO
     */
    protected function createMysqlConnection(array $config): PDO
    {
        return (new MySqlConnector())->connect($config);
    }

    /**
     * Create an instance of the Odbc connection.
     *
     * @param array $config
     *
     * @return \PDO
     */
    protected function createOdbcConnection(array $config): PDO
    {
        return (new OdbcConnection())->connect($config);
    }

    /**
     * Create an instance of the Oracle connection.
     *
     * @param array $config
     *
     * @return \PDO
     */
    protected function createOracleConnection(array $config): PDO
    {
        return (new OracleConnector())->connect($config);
    }

    /**
     * Create an instance of the PostgreSQL connection.
     *
     * @param array $config
     *
     * @return \PDO
     */
    protected function createPostgresqlConnection(array $config): PDO
    {
        return (new PostgreSQLConnector())->connect($config);
    }

    /**
     * Create an instance of the SQLite connection.
     *
     * @param array $config
     *
     * @return \PDO
     */
    protected function createSQLiteConnection(array $config): PDO
    {
        return (new SQLiteConnector())->connect($config);
    }

    /**
     * Create an instance of the SqlServer connection.
     *
     * @param array $config
     *
     * @return \PDO
     */
    protected function createSqlserverConnection(array $config): PDO
    {
        return (new SqlServerConnector())->connect($config);
    }

    /**
     * Create an instance of the Memcached connection.
     *
     * @param array $config
     *
     * @return \Memcached
     */
    protected function createMemcachedConnection(array $config): Memcached
    {
        return (new MemcachedConnector())->connect($config);
    }

    /**
     * Create an instance of the Memcached connection.
     *
     * @param array $config
     *
     * @return \Predis\Client
     */
    protected function createPredisConnection(array $config): PredisClient
    {
        return (new PredisConnector())->connect($config);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfigName(): string
    {
        return 'connect';
    }
}
