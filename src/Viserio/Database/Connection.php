<?php
declare(strict_types=1);
namespace Viserio\Database;

use Viserio\Contracts\Database\Connection as ConnectionContract;
use Viserio\Support\AbstractConnectionManager;

class Connection extends AbstractConnectionManager implements ConnectionContract
{
    /**
     * The active PDO connection.
     *
     * @var \PDO
     */
    protected $pdo;

    /**
     * The active PDO connection used for reads.
     *
     * @var \PDO
     */
    protected $readPdo;

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
    protected function createDblibConnection(array $config): PDO
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
}
