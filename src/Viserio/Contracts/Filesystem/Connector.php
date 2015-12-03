<?php
namespace Viserio\Contracts\Filesystem;

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
     * Establish a connection.
     *
     * @param array $config
     *
     * @return object
     */
    public function connect(array $config);
}
