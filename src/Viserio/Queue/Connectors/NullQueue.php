<?php
namespace Viserio\Queue\Connectors;

class NullQueue extends AbstractQueue
{
    /**
     * {@inheritdoc}
     */
    public function push($job, $data = '', string $queue = null)
    {
        //
    }

    /**
     * {@inheritdoc}
     */
    public function pushRaw(string $payload, string $queue = null, array $options = [])
    {
        //
    }

    /**
     * {@inheritdoc}
     */
    public function later($delay, $job, $data = '', string $queue = null)
    {
        //
    }

    /**
     * {@inheritdoc}
     */
    public function pop(string $queue = null)
    {
        //
    }
}
