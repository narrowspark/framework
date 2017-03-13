<?php
declare(strict_types=1);
namespace Viserio\Bridge\Doctrine\ORM\Configuration;

use Viserio\Component\Support\AbstractConnectionManager;
use Viserio\Component\Contracts\OptionsResolver\ProvidesDefaultOptions as ProvidesDefaultOptionsContract;

class ConnectionManager extends AbstractConnectionManager implements ProvidesDefaultOptionsContract
{
    /**
     * {@inheritdoc}
     */
    public function getDefaultOptions(): iterable
    {
        return [
            'default' => 'mysql',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getDimensions(): iterable
    {
        return ['viserio', 'doctrine', $this->getConfigName()];
    }

    /**
     * Create an mysql connection array.
     *
     * @param array $config
     *
     * @return array
     */
    protected function createMysqlConnection(array $config): array
    {
        return [
            'driver'      => 'pdo_mysql',
            'host'        => $config['host'],
            'dbname'      => $config['database'],
            'user'        => $config['username'],
            'password'    => $config['password'],
            'charset'     => $config['charset'],
            'port'        => $config['port'],
            'unix_socket' => $config['unix_socket'],
            'prefix'      => $config['prefix'],
        ];
    }

    /**
     * Create an oracle connection array.
     *
     * @param array $config
     *
     * @return array
     */
    protected function createOracleConnection(array $config): array
    {
        return [
            'driver'      => 'oci8',
            'host'        => $config['host'],
            'dbname'      => $config['database'],
            'user'        => $config['username'],
            'password'    => $config['password'],
            'charset'     => $config['charset'],
            'port'        => $config['port'],
            'prefix'      => $config['prefix'],
        ];
    }

    /**
     * Create an pgsql connection array.
     *
     * @param array $config
     *
     * @return array
     */
    protected function createPgsqlConnection(array $config): array
    {
        return [
            'driver'      => 'pdo_pgsql',
            'host'        => $config['host'],
            'dbname'      => $config['database'],
            'user'        => $config['username'],
            'password'    => $config['password'],
            'charset'     => $config['charset'],
            'port'        => $config['port'],
            'sslmode'     => $config['sslmode'],
            'prefix'      => $config['prefix'],
        ];
    }

    /**
     * Create an sqlite connection array.
     *
     * @param array $config
     *
     * @return array
     */
    protected function createSqliteConnection(array $config): array
    {
        return [
            'driver'      => 'pdo_sqlite',
            'user'        => $config['username'],
            'password'    => $config['password'],
            'memory'      => (':memory' != '' && mb_substr($config['database'], 0, mb_strlen(':memory')) === ':memory'),
            'path'        => $config['database'],
            'prefix'      => $config['prefix'],
        ];
    }

    /**
     * Create an sqlsrv connection array.
     *
     * @param array $config
     *
     * @return array
     */
    protected function createSqlsrvConnection(array $config): array
    {
        return [
            'driver'      => 'pdo_sqlsrv',
            'host'        => $config['host'],
            'dbname'      => $config['database'],
            'user'        => $config['username'],
            'password'    => $config['password'],
            'charset'     => $config['charset'],
            'port'        => $config['port'],
            'prefix'      => $config['prefix'],
        ];
    }


    /**
     * {@inheritdoc}
     */
    protected function getConfigName(): string
    {
        return 'connections';
    }
}
