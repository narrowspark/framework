<?php
declare(strict_types=1);
namespace Viserio\Component\Contracts\Filesystem;

interface Connector
{
    /**
     * Establish a connection.
     *
     * @param array $config
     *
     * @throws \Viserio\Component\Contracts\Filesystem\Exception\InvalidArgumentException
     *
     * @return object
     */
    public function connect(array $config): object;
}
