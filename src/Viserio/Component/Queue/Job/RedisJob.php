<?php
declare(strict_types=1);
namespace Viserio\Component\Queue\Job;

use Narrowspark\Arr\Arr;
use Psr\Container\ContainerInterface;
use Viserio\Component\Queue\Connector\RedisQueue;

class RedisJob extends AbstractJob
{
    /**
     * The Redis queue instance.
     *
     * @var \Viserio\Component\Queue\Connector\RedisQueue
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
     * @param \Psr\Container\ContainerInterface             $container
     * @param \Viserio\Component\Queue\Connector\RedisQueue $redis
     * @param string                                        $job
     * @param string                                        $reserved
     * @param string                                        $queue
     */
    public function __construct(
        ContainerInterface $container,
        RedisQueue $redis,
        string $job,
        string $reserved,
        string $queue
    ) {
        $this->container = $container;
        $this->redis     = $redis;
        $this->job       = $job;
        $this->reserved  = $reserved;
        $this->queue     = $queue;
        $this->decoded   = \json_decode($job, true);
    }

    /**
     * {@inheritdoc}
     */
    public function run(): void
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
    public function delete(): void
    {
        parent::delete();

        $this->redis->deleteReserved($this->queue, $this->reserved);
    }

    /**
     * {@inheritdoc}
     */
    public function release(int $delay = 0): void
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
     * @return \Viserio\Component\Queue\Connector\RedisQueue
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
