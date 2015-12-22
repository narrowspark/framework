<?php
namespace Viserio\Connect\Adapters\Database;

class DblibConnector extends AbstractDatabaseConnector
{
    /**
     * {@inheritdoc}
     */
    public function connect(array $config)
    {
        // We need to grab the PDO options that should be used while making the brand
        // new connection instance. The PDO options control various aspects of the
        // connection's behavior, and some might be specified by the developers.
        $connection = $this->createConnection(
            $this->getDsn($config),
            $config,
            $this->getOptions($config)
        );

        // Next we will set the "names" on the clients connections so
        // a correct character set will be used by this client. The collation also
        // is set on the server but needs to be set here on this client objects.
        $connection->prepare(sprintf('set names %s', $config['charset']))->execute();

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
        extract($config);

        return isset($config['port']) ?
        sprintf('dblib:host=%s:%s;dbname=%s', $server, $port, $database) :
        sprintf('dblib:host=%s;dbname=%s', $server, $database);
    }
}
