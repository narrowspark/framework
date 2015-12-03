<?php
namespace Viserio\Database\Connectors;

use Viserio\Contracts\Database\Connector as ConnectorContract;

/**
 * MSSQLConnector.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.2
 */
class MSSQLConnector extends Connectors implements ConnectorContract
{
    /**
     * Establish a database connection.
     *
     * @param array $config
     *
     * @return \PDO
     */
    public function connect(array $config)
    {
        $dsn = $this->getDsn($config);

        // We need to grab the PDO options that should be used while making the brand
        // new connection instance. The PDO options control various aspects of the
        // connection's behavior, and some might be specified by the developers.
        $connection = $this->createConnection($dsn, $config, $this->getOptions($config));

        // Next we will set the "names" on the clients connections so
        // a correct character set will be used by this client.
        $charset = $config['charset'];

        $connection->prepare(sprintf('set names %s', $charset))->execute();

        // Keep MSSQL QUOTED_IDENTIFIER is ON for standard quoting
        $connection->prepare('set quoted_identifier on')->execute();

        return $connection;
    }

    /**
     * Create a DSN string from a configuration.
     *
     * @param array $config
     *
     * @return string
     */
    protected function getDsn(array $config)
    {
        return $this->configIsWin() ? $this->getSqlsrvDsn($config) : $this->getDblibDsn($config);
    }

    /**
     * Determine if the given configuration array is Win.
     *
     * @return bool
     */
    protected function configIsWin()
    {
        return (strstr(PHP_OS, 'WIN')) ? true : false;
    }

    /**
     * Get the DSN string for a socket configuration.
     *
     * @param array $config
     *
     * @return string
     */
    protected function getSqlsrvDsn(array $config)
    {
        extract($config);

        return isset($config['port']) ?
        sprintf('sqlsrv:server=%s,%s;database=%s', $server, $port, $dbname) :
        sprintf('sqlsrv:server=%s;database=%s', $server, $dbname);
    }

    /**
     * Get the DSN string for a socket configuration.
     *
     * @param array $config
     *
     * @return string
     */
    protected function getDblibDsn(array $config)
    {
        extract($config);

        return isset($config['port']) ?
        sprintf('dblib:host=%s:%s;database=%s', $server, $port, $dbname) :
        sprintf('dblib:host=%s;database=%s', $server, $dbname);
    }
}
