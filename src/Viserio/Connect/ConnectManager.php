<?php
declare(strict_types=1);
namespace Viserio\Connect;

use PDO;
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

    protected function createDblibConnection(array $config)
    {
        return (new DblibConnector())->connect($config);
    }

    protected function createFirebirdConnection(array $config)
    {
        return (new Firebirdconnector())->connect($config);
    }

    protected function createGooglecloudsqlConnection(array $config)
    {
        return (new GoogleCloudSQLConnector())->connect($config);
    }

    protected function createMariadbConnection(array $config)
    {
        return (new MariaDBConnector())->connect($config);
    }

    protected function createMongoConnection(array $config)
    {
        return (new MongoConnector())->connect($config);
    }

    protected function createMssqlConnection(array $config)
    {
        return (new MSSQLConnector())->connect($config);
    }

    protected function createMysqlConnection(array $config)
    {
        return (new MySqlConnector())->connect($config);
    }

    protected function createOdbcConnection(array $config)
    {
        return (new OdbcConnection())->connect($config);
    }

    protected function createOracleConnection(array $config)
    {
        return (new OracleConnector())->connect($config);
    }

    protected function createPostgresqlConnection(array $config)
    {
        return (new PostgreSQLConnector())->connect($config);
    }

    protected function createSQLiteConnection(array $config)
    {
        return (new SQLiteConnector())->connect($config);
    }

    protected function createSqlserverConnection(array $config)
    {
        return (new SqlServerConnector())->connect($config);
    }

    protected function createSybaseConnection(array $config)
    {
        return (new SybaseConnector())->connect($config);
    }

    protected function createMemcachedConnection(array $config)
    {
        return (new MemcachedConnector())->connect($config);
    }

    protected function createPredisConnection(array $config)
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
