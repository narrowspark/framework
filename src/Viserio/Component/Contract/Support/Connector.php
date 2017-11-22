<?php
declare(strict_types=1);
namespace Viserio\Component\Contract\Support;

interface Connector
{
    /**
     * Establish a connection.
     *
     * @param array $config
     *
     * @throws \InvalidArgumentException|\RuntimeException
     *
     * @return object
     */
    public function connect(array $config): object;
}
