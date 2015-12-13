<?php
namespace Viserio\Connect\Adapters\Database;

class PostgreSQLConnector extends AbstractDatabaseConnector
{
    /**
     * {@inheritdoc}
     */
    public function connect(array $config)
    {
        // First we'll create the basic DSN and connection instance connecting to the
        // using the configuration option specified by the developer. We will also
        // set the default character set on the connections to UTF-8 by default.
        $connection = $this->createConnection(
            $this->getDsn($config),
            $config,
            $this->getOptions($config)
        );

        $connection->prepare(sprintf('set names \'%s\'', $config['charset']))->execute();

        if (isset($config['timezone'])) {
            $connection->prepare(sprintf('set timezone=\'%s\'', $config['timezone']))->execute();
        }

        // Unlike MySQL, Postgres allows the concept of "schema" and a default schema
        // may have been specified on the connections. If that is the case we will
        // set the default schema search paths to the specified database schema.
        if (isset($config['schema'])) {
            $schema = $this->formatSchema($config['schema']);
            $connection->prepare(sprintf('set search_path to %s', $schema))->execute();
        }

        // Postgres allows an application_name to be set by the user and this name is
        // used to when monitoring the application with pg_stat_activity. So we'll
        // determine if the option has been specified and run a statement if so.
        if (isset($config['application_name'])) {
            $connection->prepare(sprintf('set application_name to \'%s\'', $config['application_name']))->execute();
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

        $dsn = sprintf('pgsql:%sdbname=%s', $server, $database);

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

    /**
     * Format the schema for the DSN.
     *
     * @param  array|string  $schema
     * @return string
     */
    protected function formatSchema($schema)
    {
        if (is_array($schema)) {
            return '\''.implode('\', \'', $schema).'\'';
        } else {
            return '\''.$schema.'\'';
        }
    }
}
