<?php
namespace Viserio\Database\Connectors;

use Viserio\Contracts\Database\Connector as ConnectorContract;

class SQLiteConnector extends Connectors implements ConnectorContract
{
    /**
     * Establish a database connection.
     *
     * @param array $config
     *
     * @throws \InvalidArgumentException
     *
     * @return \PDO
     */
    public function connect(array $config)
    {
        // SQLite supports "in-memory" databases that only last as long as the owning
        // connection does. These are useful for tests or for short lifetime store
        // querying. In-memory databases may only have a single open connection.
        if ($config['database'] === ':memory:') {
            return $this->createConnection('sqlite::memory:', $config, $this->getOptions($config));
        }

        $path = realpath($config['database']);

        // Here we'll verify that the SQLite database exists before going any further
        // as the developer probably wants to know if the database exists and this
        // SQLite driver will not throw any exception if it does not by default.
        if ($path === false) {
            throw new \InvalidArgumentException('Database does not exist.');
        }

        return $this->createConnection(sprintf('sqlite:%s', $path), $config, $this->getOptions($config));
    }
}
