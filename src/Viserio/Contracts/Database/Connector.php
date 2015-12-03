<?php
namespace Viserio\Contracts\Database;

/**
 * Connector.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.4
 */
interface Connector
{
    /**
     * Establish a database connection.
     *
     * @param array $config
     *
     * @return \PDO
     */
    public function connect(array $config);
}
