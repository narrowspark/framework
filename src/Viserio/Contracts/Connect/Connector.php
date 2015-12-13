<?php
namespace Viserio\Contracts\Connect;

interface Connector
{
    /**
     * Establish a connection.
     *
     * @param array $config
     *
     * @throws \RuntimeException
     *
     * @return object
     */
    public function connect(array $config);
}
