<?php
declare(strict_types=1);
namespace Viserio\Contracts\Queue;

interface Factory
{
    /**
     * Get a connection instance.
     *
     * @param string|null $name
     *
     * @return mixed
     */
    public function connection(string $name = null);
}
