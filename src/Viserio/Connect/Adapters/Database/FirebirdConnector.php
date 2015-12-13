<?php
namespace Viserio\Connect\Adapters\Database;

use InvalidArgumentException;

class FirebirdConnector extends AbstractDatabaseConnector
{
    /**
     * {@inheritdoc}
     */
    public function connect(array $config)
    {
        // First we will create the basic DSN setup as well as the port if it is in
        // in the configuration options. This will give us the basic DSN we will
        // need to establish the PDO connections and return them back for use.
        extract($config);

        // Here we'll verify that the Firebird database exists before going any further
        // as the developer probably wants to know if the database exists and this
        // Firebird driver will not throw any exception if it does not by default.
        if (empty($database)) {
            throw new InvalidArgumentException('Database does not exist.');
        }

        $dsn = sprintf('firebird:dbname=%s:%s', $server, $database);

        // We need to grab the PDO options that should be used while making the brand
        // new connection instance. The PDO options control various aspects of the
        // connection's behavior, and some might be specified by the developers.
        return $this->createConnection($dsn, $config, $this->getOptions($config));
    }
}
