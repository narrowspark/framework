<?php
declare(strict_types=1);
namespace Viserio\Database\Connectors;

class GoogleCloudSQLConnector extends AbstractDatabaseConnector
{
    /**
     * {@inheritdoc}
     */
    public function connect(array $config)
    {
        $dsn = $this->getDsn($config);

        // We need to grab the PDO options that should be used while making the brand
        // new connection instance. The PDO options control various aspects of the
        // connection's behavior, and some might be specified by the developers.
        $connection = $this->createConnection($dsn, $config, $this->getOptions($config));

        // Next we will set the "names" on the clients connections so
        // a correct character set will be used by this client. The collation also
        // is set on the server but needs to be set here on this client objects.
        $charset = isset($config['charset']) ? $config['charset'] : 'utf8';
        $connection->prepare(sprintf('set names \'%s\'', $charset))->execute();

        $connection->prepare("set sql_mode='ANSI_QUOTES'")->execute();

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
        // First we will create the basic DSN setup as well as the port if it is in
        // in the configuration options. This will give us the basic DSN we will
        // need to establish the PDO connections and return them back for use.
        extract($config, EXTR_SKIP);

        return sprintf('mysql:unix_socket=/cloudsql/%s;dbname=%s', $server, $database);
    }
}
