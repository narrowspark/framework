<?php
declare(strict_types=1);
namespace Viserio\Component\Queue\Connector;

use Narrowspark\Arr\Arr;
use Predis\Client;
use Viserio\Component\Queue\Job\RedisJob;
use Viserio\Component\Support\Str;

class RedisQueue extends AbstractQueue
{
    /**
     * The Redis database instance.
     *
     * @var \Predis\Client
     */
    protected $redis;

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
     * @param int            $expire
     */
    public function __construct(Client $redis, string $default = 'default', int $expire = 90)
    {
        $this->redis   = $redis;
        $this->default = $default;
        $this->expire  = $expire;
    }

    /**
     * {@inheritdoc}
     */
    public function push($job, $data = '', string $queue = null)
    {
        return $this->pushRaw($this->createPayload($job, $data), $queue);
    }

    /**
     * {@inheritdoc}
     */
    public function pushRaw(string $payload, string $queue = null, array $options = [])
    {
        $this->redis->rpush($this->getQueue($queue), $payload);

        return Arr::get(json_decode($payload, true), 'id');
    }

    /**
     * {@inheritdoc}
     */
    public function later($delay, $job, $data = '', string $queue = null)
    {
        $payload = $this->createPayload($job, $data);

        $delay = $this->getSeconds($delay);

        $this->redis->zadd($this->getQueue($queue) . ':delayed', $this->getTime() + $delay, $payload);

        return Arr::get(json_decode($payload, true), 'id');
    }

    /**
     * {@inheritdoc}
     */
    public function pop(string $queue = null)
    {
        $original = $queue ?: $this->default;

        $queue = $this->getQueue($queue);

        $this->migrateExpiredJobs($queue . ':delayed', $queue);

        if (! is_null($this->expire)) {
            $this->migrateExpiredJobs($queue . ':reserved', $queue);
        }

        $script = <<<'LUA'
local job = redis.call('lpop', KEYS[1])
local reserved = false
if(job ~= false) then
    reserved = cjson.decode(job)
    reserved['attempts'] = reserved['attempts'] + 1
    reserved = cjson.encode(reserved)
    redis.call('zadd', KEYS[2], KEYS[3], reserved)
end
return {job, reserved}
LUA;

        list($job, $reserved) = $this->redis->eval(
            $script,
            3,
            $queue,
            $queue . ':reserved',
            $this->getTime() + $this->expire
        );

        if ($reserved) {
            return new RedisJob($this->getContainer(), $this, $job, $reserved, $original);
        }
    }

    /**
     * Delete a reserved job from the queue.
     *
     * @param string $queue
     * @param string $job
     */
    public function deleteReserved(string $queue, string $job)
    {
        $this->redis->zrem($this->getQueue($queue) . ':reserved', $job);
    }

    /**
     * Delete a reserved job from the reserved queue and release it.
     *
     * @param string $queue
     * @param string $job
     * @param int    $delay
     */
    public function deleteAndRelease(string $queue, string $job, int $delay)
    {
        $queue  = $this->getQueue($queue);
        $script = <<<'LUA'
redis.call('zrem', KEYS[2], KEYS[3])
redis.call('zadd', KEYS[1], KEYS[4], KEYS[3])
return true
LUA;
        $this->redis->eval(
            $script,
            4,
            $queue . ':delayed',
            $queue . ':reserved',
            $job,
            $this->getTime() + $delay
        );
    }

    /**
     * Migrate the delayed jobs that are ready to the regular queue.
     *
     * @param string $from
     * @param string $to
     */
    public function migrateExpiredJobs(string $from, string $to)
    {
        $script = <<<'LUA'
local val = redis.call('zrangebyscore', KEYS[1], '-inf', KEYS[3])
if(next(val) ~= nil) then
    redis.call('zremrangebyrank', KEYS[1], 0, #val - 1)
    for i = 1, #val, 100 do
        redis.call('rpush', KEYS[2], unpack(val, i, math.min(i+99, #val)))
    end
end
return true
LUA;

        $this->redis->eval($script, 3, $from, $to, $this->getTime());
    }

    /**
     * {@inheritdoc}
     */
    public function getQueue($queue): string
    {
        return 'queues:' . parent::getQueue($queue);
    }

    /**
     * Get the underlying Redis instance.
     *
     * @return \Predis\Client
     */
    public function getRedis(): Client
    {
        return $this->redis;
    }

    /**
     * {@inheritdoc}
     */
    protected function createPayload($job, $data = '', string $queue = null): string
    {
        $payload = parent::createPayload($job, $data);

        $payload = $this->setMeta($payload, 'id', $this->getRandomId());

        return $this->setMeta($payload, 'attempts', 1);
    }

    /**
     * Set additional meta on a payload string.
     *
     * @param string     $payload
     * @param string     $key
     * @param string|int $value
     *
     * @return string
     */
    protected function setMeta(string $payload, string $key, $value): string
    {
        $payload = json_decode($payload, true);

        return json_encode(Arr::set($payload, $key, (string) $value));
    }

    /**
     * Get a random ID string.
     *
     * @return string
     */
    protected function getRandomId(): string
    {
        return Str::random(32);
    }
}
