<?php
declare(strict_types=1);
namespace Viserio\Database\Connectors;

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
        extract($config, EXTR_SKIP);

        // Here we'll verify that the Firebird database exists before going any further
        // as the developer probably wants to know if the database exists and this
        // Firebird driver will not throw any exception if it does not by default.
        if (empty($database)) {
            throw new InvalidArgumentException('Database does not exist.');
        }

        $dsn = sprintf('firebird:dbname=%s:%s', $server, $database);

        return $this->createConnection($dsn, $config, $this->getOptions($config));
    }
}
