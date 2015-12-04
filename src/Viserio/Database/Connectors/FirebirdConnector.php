<?php
namespace Viserio\Database\Connectors;

use Viserio\Contracts\Database\Connector as ConnectorContract;

/**
 * FirebirdConnector.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.8
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
