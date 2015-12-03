<?php
namespace Viserio\Database\Connectors;

use Viserio\Contracts\Database\Connector as ConnectorContract;

class OdbcConnector extends Connectors implements ConnectorContract
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
        $options = $this->getOptions($config);
        $dsn = $this->getDsn($config);

        return $this->createConnection($dsn, $config, $options);
    }


    /**
     * Get the DSN string for a DbLib connection.
     *
     * @param array $config
     *
     * @return string
     */
    protected function getDsn(array $config)
    {
        $arguments = $config['dsn'];
        $arguments['Driver'] = '{'.$arguments['Driver'].'}';

        $options = array_map(function ($key) use ($arguments) {
            return sprintf('%s=%s', $key, $arguments[$key]);
        }, array_keys($arguments));

        return 'odbc:'.implode(';', $options);
    }
}
