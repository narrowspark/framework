<?php
declare(strict_types=1);
namespace Viserio\Component\Contract\Queue;

interface Factory
{
    /**
     * Get a connection instance.
     *
     * @param null|string $name
     *
     * @return mixed
     */
    public function getConnection(string $name = null);
}
