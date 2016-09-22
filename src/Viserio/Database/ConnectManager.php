<?php
declare(strict_types=1);
namespace Viserio\Database;

use Viserio\Database\Connectors\DblibConnector;
use Viserio\Database\Connectors\Firebirdconnector;
use Viserio\Database\Connectors\GoogleCloudSQLConnector;
use Viserio\Database\Connectors\MariaDBConnector;
use Viserio\Database\Connectors\MSSQLConnector;
use Viserio\Database\Connectors\MySqlConnector;
use Viserio\Database\Connectors\PostgreSQLConnector;
use Viserio\Database\Connectors\SQLiteConnector;
use Viserio\Database\Connectors\SqlServerConnector;
use Viserio\Database\Connectors\OdbcConnector;
use Viserio\Support\AbstractConnectionManager;

class ConnectManager extends AbstractConnectionManager
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
     * Create an instance of the SqlServer connection.
     *
     * @param array $config
     *
     * @return \PDO
     */
    protected function createOdbcConnection(array $config): PDO
    {
        return (new OdbcConnector())->connect($config);
    }
}
