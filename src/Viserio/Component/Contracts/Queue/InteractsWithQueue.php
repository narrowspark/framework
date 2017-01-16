<?php
declare(strict_types=1);
namespace Viserio\Component\Contracts\Queue;

interface InteractsWithQueue
{
    /**
     * Set the base queue job instance.
     *
     * @param \Viserio\Component\Contracts\Queue\Job $job
     *
     * @return $this
     */
    public function setJob(Job $job): InteractsWithQueue;

    /**
     * Get the number of times the job has been attempted.
     *
     * @return int
     */
    public function attempts(): int;

    /**
     * Delete the job from the queue.
     */
    public function delete();

    /**
     * Fail the job from the queue.
     */
    public function failed();

    /**
     * Release the job back into the queue.
     *
     * @param int $delay
     */
    public function release(int $delay = 0);
}
