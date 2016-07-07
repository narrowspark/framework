<?php
namespace Viserio\Queue;

use Viserio\Contracts\Queue\{
    Job as JobContract,
    InteractsWithQueue as InteractsWithQueueContract
};

abstract class AnstractInteractsWithQueue implements InteractsWithQueueContract
{
    /**
     * The underlying queue job instance.
     *
     * @var \Viserio\Contracts\Queue\Job
     */
    protected $job;

    /**
     * {@inheritdoc}
     */
    public function setJob(JobContract $job): InteractsWithQueueContract
    {
        $this->job = $job;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function attempts(): int
    {
        return $this->job ? $this->job->attempts() : 1;
    }

    /**
     * {@inheritdoc}
     */
    public function delete()
    {
        if ($this->job) {
            return $this->job->delete();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function failed()
    {
        if ($this->job) {
            return $this->job->failed();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function release(int $delay = 0)
    {
        if ($this->job) {
            return $this->job->release($delay);
        }
    }
}
