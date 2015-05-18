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
 * @version     0.9.8-dev
 */

use Brainwave\Contracts\Database\Connector as ConnectorContract;

/**
 * FirebirdConnector.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.8-dev
 */
class FirebirdConnector extends Connectors implements ConnectorContract
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
        // First we will create the basic DSN setup as well as the port if it is in
        // in the configuration options. This will give us the basic DSN we will
        // need to establish the PDO connections and return them back for use.
        extract($config);

        $path = realpath($database);

        // Here we'll verify that the Firebird database exists before going any further
        // as the developer probably wants to know if the database exists and this
        // SQLite driver will not throw any exception if it does not by default.
        if ($path === false) {
            throw new \InvalidArgumentException('Database does not exist.');
        }

        $dsn = sprintf('firebird:dbname=%s:%s', $server, $path);

        // We need to grab the PDO options that should be used while making the brand
        // new connection instance. The PDO options control various aspects of the
        // connection's behavior, and some might be specified by the developers.
        return $this->createConnection($dsn, $config, $this->getOptions($config));
    }
}
