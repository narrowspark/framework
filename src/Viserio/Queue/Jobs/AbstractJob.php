<?php
namespace Viserio\Queue\Jobs;

use Viserio\Contracts\Queue\Job as JobContract;

abstract class AbstractJob implements JobContract
{
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
    public function delete()
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
    public function release(int $delay = 0)
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

    }

    /**
     * {@inheritdoc}
     */
    public function failed()
    {

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
}
