<?php
namespace Viserio\Connect\Adapters\Database;

class OracleConnector extends AbstractDatabaseConnector
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

        // Next we will set the "names" and "collation" on the clients connections so
        // a correct character set will be used by this client. The collation also
        // is set on the server but needs to be set here on this client objects.
        $connection->prepare(sprintf('set names \'%s\'', $config['charset']))->execute();
        $connection->prepare('set sql_mode=\'ANSI_QUOTES\'')->execute();

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
        extract($config, EXTR_SKIP);

        return isset($config['port']) ?
        sprintf('oci:host=%s;port=%s;dbname=%s', $server, $port, $database) :
        sprintf('oci:host=%s;dbname=%s', $server, $database);
    }
}
