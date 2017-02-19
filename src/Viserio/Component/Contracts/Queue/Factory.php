<?php
declare(strict_types=1);
namespace Viserio\Component\Contracts\Queue;

interface Factory
{
    /**
     * Get a connection instance.
     *
     * @param string|null $name
     *
     * @return mixed
     */
    public function getConnection(string $name = null);
}
