<?php
namespace Viserio\Contracts\Filesystem;

interface Connector
{
    /**
     * Establish a connection.
     *
     * @param array $config
     *
     * @throws \RuntimeException|\InvalidArgumentException
     *
     * @return \League\Flysystem\AdapterInterface
     */
    public function connect(array $config);
}
