<?php
namespace Viserio\Queue\Connectors;

class GoogleAppEngineQueue extends AbstractQueue
{
    /**
     * {@inheritdoc}
     */
    public function push(string $job, $data = '', string $queue = null)
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
    public function later($delay, string $job, $data = '', string $queue = null)
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
