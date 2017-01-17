<?php
declare(strict_types=1);
namespace Viserio\Component\Contracts\Support;

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
