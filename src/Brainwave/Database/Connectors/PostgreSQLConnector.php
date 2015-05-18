<?php

namespace Brainwave\Database\Connectors;

/**
 * Narrowspark - a PHP 5 framework.
 *
 * @author      Daniel Bannert <info@anolilab.de>
 * @copyright   2015 Daniel Bannert
 *
 * @link        http://www.narrowspark.de
 *
 * @license     http://www.narrowspark.com/license
 *
 * @version     0.9.8-dev
 */

use Brainwave\Contracts\Database\Connector as ConnectorContract;
use PDO;

/**
 * PostgreSQLConnector.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.2-dev
 */
class PostgreSQLConnector extends Connectors implements ConnectorContract
{
    /**
     * The default PDO connection options.
     *
     * @var array
     */
    protected $options = [
            PDO::ATTR_CASE => PDO::CASE_NATURAL,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_ORACLE_NULLS => PDO::NULL_NATURAL,
            PDO::ATTR_STRINGIFY_FETCHES => false,
    ];

    /**
     * Establish a database connection.
     *
     * @param array $config
     *
     * @return \PDO
     */
    public function connect(array $config)
    {
        // First we'll create the basic DSN and connection instance connecting to the
        // using the configuration option specified by the developer. We will also
        // set the default character set on the connections to UTF-8 by default.
        $dsn = $this->getDsn($config);

        $connection = $this->createConnection($dsn, $config, $this->getOptions($config));

        $charset = $config['charset'];

        $connection->prepare(sprintf('set names %s', $charset))->execute();

        if (isset($config['timezone'])) {
            $timezone = $config['timezone'];
            $connection->prepare(sprintf('set timezone=%s', $timezone))->execute();
        }

        // Unlike MySQL, Postgres allows the concept of "schema" and a default schema
        // may have been specified on the connections. If that is the case we will
        // set the default schema search paths to the specified database schema.
        if (isset($config['schema'])) {
            $schema = $config['schema'];

            $connection->prepare(sprintf('set search_path to \"%s\"', $schema))->execute();
        }

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
        extract($config);

        $server = isset($server) ? sprintf('host=%s;', $server) : '';

        $dsn = sprintf('pgsql:%sdbname=%s', $server, $dbname);

        // If a port was specified, we will add it to this Postgres DSN connections
        // format. Once we have done that we are ready to return this connection
        // string back out for usage, as this has been fully constructed here.
        if (isset($config['port'])) {
            $dsn .= sprintf(';port=%s', $port);
        }

        if (isset($config['sslmode'])) {
            $dsn .= sprintf(';sslmode=%s', $sslmode);
        }

        return $dsn;
    }
}
