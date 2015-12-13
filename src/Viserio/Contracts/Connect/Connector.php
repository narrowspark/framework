<?php
namespace Viserio\Contracts\Connect;

interface Connector
{
    /**
     * Establish a connection.
     *
     * @param array $config
     *
     * @throws \RuntimeException|\InvalidArgumentException
     *
     * @return object
     */
    public function connect(array $config);
}
