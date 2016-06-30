<?php
namespace Viserio\Queue\Connectors;

use Narrowspark\Arr\StaticArr as Arr;

class RedisQueue extends AbstractQueue
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

    /**
     * Set additional meta on a payload string.
     *
     * @param string $payload
     * @param string $key
     * @param string $value
     *
     * @return string
     */
    protected function setMeta(string $payload, string $key, string $value): string
    {
        $payload = json_decode($payload, true);

        return json_encode(Arr::set($payload, $key, $value));
    }
}
