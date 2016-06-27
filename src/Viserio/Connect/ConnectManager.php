<?php
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
    protected $supportedConnectors = [
        'dblib' => DblibConnector::class,
        'firebird' => Firebirdconnector::class,
        'googlecloudsql' => GoogleCloudSQLConnector::class,
        'mariadb' => MariaDBConnector::class,
        'mongo' => MongoConnector::class,
        'mssql' => MSSQLConnector::class,
        'mysql' => MySqlConnector::class,
        'odbc' => OdbcConnection::class,
        'oracle' => OracleConnector::class,
        'postgresql' => PostgreSQLConnector::class,
        'sqllite' => SQLiteConnector::class,
        'sqlserver' => SqlServerConnector::class,
        'sybase' => SybaseConnector::class,
        'memcached' => MemcachedConnector::class,
        'predis' => PredisConnector::class,
    ];

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
     * {@inheritdoc}
     */
    protected function getConfigName(): string
    {
        return 'connect';
    }
}
