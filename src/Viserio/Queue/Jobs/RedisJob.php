<?php
namespace Viserio\Queue\Jobs;

use Narrowspark\Arr\StaticArr as Arr;
use Interop\Container\ContainerInterface;
use Viserio\Queue\Connectors\RedisQueue;

class RedisJob extends AbstractJob
{
    /**
     * The Redis queue instance.
     *
     * @var \Viserio\Queue\Connectors\RedisQueue
     */
    protected $redis;

    /**
     * The Redis raw job payload.
     *
     * @var string
     */
    protected $job;

    /**
     * The JSON decoded version of "$job".
     *
     * @var array
     */
    protected $decoded = [];

    /**
     * The Redis job payload inside the reserved queue.
     *
     * @var string
     */
    protected $reserved;

    /**
     * Create a new job instance.
     *
     * @param \Interop\Container\ContainerInterface $container
     * @param \Viserio\Queue\Connectors\RedisQueue  $redis
     * @param string                                $job
     * @param string                                $reserved
     * @param string                                $queue
     */
    public function __construct(
        ContainerInterface $container,
        RedisQueue $redis,
        string $job,
        string $reserved,
        string $queue
    ) {
        $this->job = $job;
        $this->redis = $redis;
        $this->queue = $queue;
        $this->reserved = $reserved;
        $this->container = $container;
        $this->decoded = json_decode($job, true);
    }

    /**
     * {@inheritdoc}
     */
    public function run()
    {
        $this->resolveAndRun($this->decoded);
    }

    /**
     * {@inheritdoc}
     */
    public function getRawBody(): string
    {
        return $this->job;
    }

    /**
     * {@inheritdoc}
     */
    public function delete()
    {
        parent::delete();

        $this->redis->deleteReserved($this->queue, $this->reserved);
    }

    /**
     * {@inheritdoc}
     */
    public function release(int $delay = 0)
    {
        parent::release($delay);

        $this->redis->deleteAndRelease($this->queue, $this->reserved, $delay);
    }

    /**
     * {@inheritdoc}
     */
    public function attempts(): int
    {
        return Arr::get($this->decoded, 'attempts');
    }

    /**
     * {@inheritdoc}
     */
    public function getJobId(): string
    {
        return Arr::get($this->decoded, 'id');
    }

    /**
     * Get the underlying queue driver instance.
     *
     * @return \Viserio\Queue\Connectors\RedisQueue
     */
    public function getRedisQueue()
    {
        return $this->redis;
    }

    /**
     * Get the underlying reserved Redis job.
     *
     * @return string
     */
    public function getReservedJob()
    {
        return $this->reserved;
    }
}
