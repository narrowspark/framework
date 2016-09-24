<?php
declare(strict_types=1);
namespace Viserio\Database\Connectors;

class DblibConnector extends AbstractDatabaseConnector
{
    /**
     * {@inheritdoc}
     */
    public function connect(array $config)
    {
        $connection = $this->createConnection(
            $this->getDsn($config),
            $config,
            $this->getOptions($config)
        );

        // Next we will set the "names" on the clients connections so
        // a correct character set will be used by this client. The collation also
        // is set on the server but needs to be set here on this client objects.
        $charset = isset($config['charset']) ? $config['charset'] : 'utf8';
        $connection->prepare(sprintf('set names \'%s\'', $charset))->execute();

        return $connection;
    }

    /**
     * Create a DSN string from a configuration.
     *
     * @param array $config
     *
     * @return string
     */
    protected function getDsn(array $config): string
    {
        extract($config, EXTR_SKIP);

        return isset($config['port']) ?
            sprintf('dblib:host=%s:%s;dbname=%s', $server, $port, $database) :
            sprintf('dblib:host=%s;dbname=%s', $server, $database);
    }
}
