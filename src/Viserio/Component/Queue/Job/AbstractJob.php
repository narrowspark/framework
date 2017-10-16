<?php
declare(strict_types=1);
namespace Viserio\Component\Queue\Job;

use Cake\Chronos\Chronos;
use DateTime;
use Narrowspark\Arr\Arr;
use Viserio\Component\Contract\Container\Traits\ContainerAwareTrait;
use Viserio\Component\Contract\Queue\Job as JobContract;
use Viserio\Component\Queue\CallQueuedHandler;

abstract class AbstractJob implements JobContract
{
    use ContainerAwareTrait;

    /**
     * The job handler instance.
     *
     * @var mixed
     */
    protected $instance;

    /**
     * The name of the queue the job belongs to.
     *
     * @var string
     */
    protected $queue;

    /**
     * Indicates if the job has been deleted.
     *
     * @var bool
     */
    protected $deleted = false;

    /**
     * Indicates if the job has been released.
     *
     * @var bool
     */
    protected $released = false;

    /**
     * {@inheritdoc}
     */
    public function delete(): void
    {
        $this->deleted = true;
    }

    /**
     * {@inheritdoc}
     */
    public function isDeleted(): bool
    {
        return $this->deleted;
    }

    /**
     * {@inheritdoc}
     */
    abstract public function run();

    /**
     * {@inheritdoc}
     */
    public function release(int $delay = 0): void
    {
        $this->released = true;
    }

    /**
     * {@inheritdoc}
     */
    public function isReleased(): bool
    {
        return $this->released;
    }

    /**
     * {@inheritdoc}
     */
    public function isDeletedOrReleased(): bool
    {
        return $this->isDeleted() || $this->isReleased();
    }

    /**
     * {@inheritdoc}
     */
    abstract public function attempts(): int;

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return \json_decode($this->getRawBody(), true)['job'];
    }

    /**
     * {@inheritdoc}
     */
    public function failed(): void
    {
        $payload = \json_decode($this->getRawBody(), true);

        [$class, $method] = $this->parseJob($payload['job']);

        $this->instance = $this->container->get($class);

        if (\method_exists($this->instance, 'failed')) {
            $this->instance->failed($payload['data']);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getQueue(): string
    {
        return $this->queue;
    }

    /**
     * {@inheritdoc}
     */
    abstract public function getRawBody(): string;

    /**
     * {@inheritdoc}
     */
    abstract public function getJobId(): string;

    /**
     * {@inheritdoc}
     */
    public function resolveName(): string
    {
        $name    = $this->getName();
        $payload = \json_decode($this->getRawBody(), true);

        if ($name === \sprintf('%s@call', CallQueuedHandler::class)) {
            return Arr::get($payload, 'data.commandName', $name);
        }

        return $name;
    }

    /**
     * Resolve and run the job handler method.
     *
     * @param array $payload
     */
    protected function resolveAndRun(array $payload): void
    {
        [$class, $method] = $this->parseJob($payload['job']);

        $this->instance = $this->container->get($class);

        $this->instance->{$method}($this, $payload['data']);
    }

    /**
     * Parse the job declaration into class and method.
     *
     * @param string $job
     *
     * @return array
     */
    protected function parseJob(string $job): array
    {
        $segments = \explode('@', $job);

        return \count($segments) > 1 ? $segments : [$segments[0], 'run'];
    }

    /**
     * Calculate the number of seconds with the given delay.
     *
     * @param \DateTime|int $delay
     *
     * @return int
     */
    protected function getSeconds($delay): int
    {
        if ($delay instanceof DateTime) {
            return \max(0, $delay->getTimestamp() - $this->getTime());
        }

        return (int) $delay;
    }

    /**
     * Get the current system time.
     *
     * @return int
     */
    protected function getTime(): int
    {
        return Chronos::now()->getTimestamp();
    }
}
