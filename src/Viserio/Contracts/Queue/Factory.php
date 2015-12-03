<?php
namespace Viserio\Contracts\Queue;

interface Factory
{
    /**
     * Resolve a queue connection instance.
     *
     * @param string $name
     *
     * @return \Viserio\Contracts\Queue\Queue
     */
    public function connection($name = null);
}
