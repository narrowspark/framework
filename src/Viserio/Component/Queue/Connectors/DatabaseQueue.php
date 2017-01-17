<?php
declare(strict_types=1);
namespace Viserio\Component\Queue\Connectors;

class DatabaseQueue extends AbstractQueue
{
    /**
     * {@inheritdoc}
     */
    public function push($job, $data = '', string $queue = null)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function pushRaw(string $payload, string $queue = null, array $options = [])
    {
    }

    /**
     * {@inheritdoc}
     */
    public function later($delay, $job, $data = '', string $queue = null)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function pop(string $queue = null)
    {
    }
}
