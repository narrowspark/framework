<?php
declare(strict_types=1);
namespace Viserio\Queue;

use Viserio\Contracts\Queue\InteractsWithQueue as InteractsWithQueueContract;
use Viserio\Contracts\Queue\Job as JobContract;

abstract class AbstractInteractsWithQueue implements InteractsWithQueueContract
{
    /**
     * The underlying queue job instance.
     *
     * @var \Viserio\Contracts\Queue\Job
     */
    protected $job;

    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function setJob(JobContract $job): InteractsWithQueueContract
    {
        $this->job = $job;

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function attempts(): int
    {
        return $this->job ? $this->job->attempts() : 1;
    }

    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function delete()
    {
        if ($this->job) {
            return $this->job->delete();
        }
    }

    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function failed()
    {
        if ($this->job) {
            return $this->job->failed();
        }
    }

    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function release(int $delay = 0)
    {
        if ($this->job) {
            return $this->job->release($delay);
        }
    }
}
