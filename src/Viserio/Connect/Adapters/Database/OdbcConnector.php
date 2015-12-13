<?php
namespace Viserio\Connect\Adapters\Database;

class OdbcConnector extends AbstractDatabaseConnector
{
    /**
     * {@inheritdoc}
     */
    public function connect(array $config)
    {
        return $this->createConnection(
            $this->getDsn($config),
            $config,
            $this->getOptions($config)
        );
    }


    /**
     * Get the DSN string for a Odbc connection.
     *
     * @param array $config
     *
     * @return string
     */
    protected function getDsn(array $config)
    {
        $options = array_map(function ($key) use ($config) {
            return sprintf('%s=%s', $key, $config[$key]);
        }, array_keys($config));

        return 'odbc:'.implode(';', $options);
    }
}
