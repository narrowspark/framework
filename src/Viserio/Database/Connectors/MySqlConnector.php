<?php
declare(strict_types=1);
namespace Viserio\Database\Connectors;

use PDO;

class MySqlConnector extends AbstractDatabaseConnector
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

        if (isset($config['unix_socket'])) {
            $connection->exec(sprintf('use %s', $config['database']));
        }

        // Next we will set the "names" and "collation" on the clients connections so
        // a correct character set will be used by this client. The collation also
        // is set on the server but needs to be set here on this client objects.
        $collate = ($config['collation'] !== null ? sprintf(' collate \'%s\'', $config['collation']) : '');

        $connection->prepare(sprintf('set names \'%s\'%s', $config['charset'], $collate))->execute();

        // Next, we will check to see if a timezone has been specified in this config
        // and if it has we will issue a statement to modify the timezone with the
        // database. Setting this DB timezone is an optional configuration item.
        if (isset($config['timezone'])) {
            $connection->prepare(
                sprintf('set time_zone=\'%s\'', $config['timezone'])
            )->execute();
        }

        $this->setModes($connection, $config);

        return $connection;
    }

    /**
     * Create a DSN string from a configuration. Chooses socket or host/port based on
     * the 'unix_socket' config value.
     *
     * @param array $config
     *
     * @return string
     */
    protected function getDsn(array $config)
    {
        return $this->configHasSocket($config) ? $this->getSocketDsn($config) : $this->getHostDsn($config);
    }

    /**
     * Determine if the given configuration array has a UNIX socket value.
     *
     * @param array $config
     *
     * @return bool
     */
    protected function configHasSocket(array $config)
    {
        return isset($config['unix_socket']) && ! empty($config['unix_socket']);
    }

    /**
     * Get the DSN string for a socket configuration.
     *
     * @param array $config
     *
     * @return string
     */
    protected function getSocketDsn(array $config)
    {
        return sprintf('mysql:unix_socket=%s;dbname=%s', $config['unix_socket'], $config['database']);
    }

    /**
     * Get the DSN string for a host / port configuration.
     *
     * @param array $config
     *
     * @return string
     */
    protected function getHostDsn(array $config)
    {
        extract($config, EXTR_SKIP);

        return array_key_exists('port', $config) ?
            sprintf('mysql:host=%s;port=%s;dbname=%s', $server, $port, $database) :
            sprintf('mysql:host=%s;dbname=%s', $server, $database);
    }

    /**
     * Set the modes for the connection.
     *
     * @param \PDO  $connection
     * @param array $config
     */
    protected function setModes(PDO $connection, array $config)
    {
        if (isset($config['modes'])) {
            $connection->prepare(sprintf('set session sql_mode="%s"', implode(',', $config['modes'])))->execute();
        } elseif (isset($config['strict'])) {
            if ($config['strict']) {
                $connection->prepare(
                    "set session sql_mode='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION'"
                )->execute();
            } else {
                $connection->prepare("set session sql_mode='NO_ENGINE_SUBSTITUTION'")->execute();
            }
        }
    }
}
