<?php
namespace Viserio\Contracts\Queue;

interface Pushable
{
    /**
     * Push a new message onto the queue.
     *
     * @param mixed    $data     The job's data
     * @param string   $info     Info text (used for logging)
     * @param array    $metadata Additional data about the job
     * @param int|null $delay    Delay in seconds (null for adapter default)
     */
    public function push($data, string $info, array $metadata = [], int $delay = null);
}
