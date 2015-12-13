<?php
namespace Viserio\Connect\Adapters\Database;

use InvalidArgumentException;

class DblibConnector extends AbstractDatabaseConnector
{
    /**
     * {@inheritdoc}
     */
    public function connect(array $config)
    {
        // First we will create the basic DSN setup as well as the port if it is in
        // in the configuration options. This will give us the basic DSN we will
        // need to establish the PDO connections and return them back for use.
        $port    = isset($config['port']) ? sprintf(':%s', $config['port']) : '';
        $charset = isset($config['charset']) ? sprintf(';charset=\'%s\'', $config['charset']) : '';

        $dsn = sprintf(
            'dblib:host=%s%s;dbname=%s%s',
            $config['server'],
            $port,
            $config['database'],
            $charset
        );

        // We need to grab the PDO options that should be used while making the brand
        // new connection instance. The PDO options control various aspects of the
        // connection's behavior, and some might be specified by the developers.
        return $this->createConnection($dsn, $config, $this->getOptions($config));
    }
}
