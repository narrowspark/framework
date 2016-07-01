<?php
namespace Viserio\Queue\Connectors;

use Predis\Client;
use Narrowspark\Arr\StaticArr as Arr;

class RedisQueue extends AbstractQueue
{
    /**
     * The Redis database instance.
     *
     * @var \Predis\Client
     */
    protected $redis;

    /**
     * The connection name.
     *
     * @var string
     */
    protected $connection;

    /**
     * The name of the default queue.
     *
     * @var string
     */
    protected $default;

    /**
     * The expiration time of a job.
     *
     * @var int
     */
    protected $expire = 90;

    /**
     * Create a new Redis queue instance.
     *
     * @param \Predis\Client $redis
     * @param string         $default
     * @param string         $connection
     * @param int            $expire
     */
    public function __construct(
        Client $redis,
        string $default = 'default',
        string $connection = null,
        int $expire = 90
    ) {
        $this->redis = $redis;
        $this->default = $default;
        $this->connection = $connection;
        $this->expire = $expire;
    }

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
