<?php

namespace Brainwave\Database\Connectors;

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

use Brainwave\Contracts\Database\Connector as ConnectorContract;

/**
 * MySqlConnector.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.2-dev
 */
class MySqlConnector extends Connectors implements ConnectorContract
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

        extract($config);

        // We need to grab the PDO options that should be used while making the brand
        // new connection instance. The PDO options control various aspects of the
        // connection's behavior, and some might be specified by the developers.
        $connection = $this->createConnection($dsn, $config, $this->getOptions($config));

        if (isset($config['unix_socket'])) {
            $connection->exec(sprintf('use %s', $config['database']));
        }

        // Next we will set the "names" and "collation" on the clients connections so
        // a correct character set will be used by this client. The collation also
        // is set on the server but needs to be set here on this client objects.
        $collate = ($collation !== null ? sprintf(' collate %s', $collation) : '');

        $connection->prepare(sprintf('set names %s %s', $charset, $collate))->execute();

        // Next, we will check to see if a timezone has been specified in this config
        // and if it has we will issue a statement to modify the timezone with the
        // database. Setting this DB timezone is an optional configuration item.
        if (isset($config['timezone'])) {
            $connection->prepare(
                sprintf('set time_zone=%s', $config['timezone'])
            )->execute();
        }

        // If the "strict" option has been configured for the connection we'll enable
        // strict mode on all of these tables. This enforces some extra rules when
        // using the MySQL database system and is a quicker way to enforce them.
        if (isset($config['strict']) && $config['strict']) {
            $connection->prepare("set session sql_mode='STRICT_ALL_TABLES'")->execute();
        }

        return $connection;
    }

    /**
     * Create a DSN string from a configuration. Chooses socket or host/port based on
     * the 'unix_socket' config value.
     *
     * @param array $config
     *
     * @return string
     */
    protected function getDsn(array $config)
    {
        return $this->configHasSocket($config) ? $this->getSocketDsn($config) : $this->getHostDsn($config);
    }

    /**
     * Determine if the given configuration array has a UNIX socket value.
     *
     * @param array $config
     *
     * @return bool
     */
    protected function configHasSocket(array $config)
    {
        return isset($config['unix_socket']) && !empty($config['unix_socket']);
    }

    /**
     * Get the DSN string for a socket configuration.
     *
     * @param array $config
     *
     * @return string
     */
    protected function getSocketDsn(array $config)
    {
        return sprintf('mysql:unix_socket=%s;dbname=%s', $config['unix_socket'], $config['database']);
    }

    /**
     * Get the DSN string for a host / port configuration.
     *
     * @param array $config
     *
     * @return string
     */
    protected function getHostDsn(array $config)
    {
        extract($config);

        return isset($port) ?
        sprintf('mysql:host=%s;port=%s;dbname=%s', $server, $port, $dbname) :
        sprintf('mysql:host=%s;dbname=%s', $server, $port);
    }
}
