<?php
declare(strict_types=1);
namespace Viserio\Component\Contract\Filesystem;

use League\Flysystem\AdapterInterface;

interface Connector
{
    /**
     * Establish a connection.
     *
     * @throws \Viserio\Component\Contract\OptionsResolver\Exception\InvalidArgumentException On wrong configuration
     *
     * @return \League\Flysystem\AdapterInterface;
     */
    public function connect(): AdapterInterface;
}
