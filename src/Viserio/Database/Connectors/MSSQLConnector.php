<?php
declare(strict_types=1);
namespace Viserio\Database\Connectors;

use Viserio\Support\Str;

class MSSQLConnector extends AbstractDatabaseConnector
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
        // a correct character set will be used by this client.
        $connection->prepare(sprintf('set names \'%s\'', $config['charset']))->execute();

        // Keep MSSQL QUOTED_IDENTIFIER is ON for standard quoting
        $connection->prepare('set quoted_identifier on')->execute();

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
        return $this->configIsWin() ? $this->getSqlsrvDsn($config) : $this->getDblibDsn($config);
    }

    /**
     * Determine if the given configuration array is Win.
     *
     * @return bool
     */
    protected function configIsWin()
    {
        return Str::containsAny(PHP_OS, [
            'WIN32',
            'WINNT',
            'Windows',
        ]);
    }

    /**
     * Get the DSN string for a socket configuration.
     *
     * @param array $config
     *
     * @return string
     */
    protected function getSqlsrvDsn(array $config)
    {
        extract($config, EXTR_SKIP);

        return isset($config['port']) ?
        sprintf('sqlsrv:server=%s,%s;database=%s', $server, $port, $database) :
        sprintf('sqlsrv:server=%s;database=%s', $server, $database);
    }

    /**
     * Get the DSN string for a socket configuration.
     *
     * @param array $config
     *
     * @return string
     */
    protected function getDblibDsn(array $config)
    {
        extract($config, EXTR_SKIP);

        return isset($config['port']) ?
        sprintf('dblib:host=%s:%s;database=%s', $server, $port, $database) :
        sprintf('dblib:host=%s;database=%s', $server, $database);
    }
}
