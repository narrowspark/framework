<?php

declare(strict_types=1);
namespace Viserio\Contracts\Support;

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
