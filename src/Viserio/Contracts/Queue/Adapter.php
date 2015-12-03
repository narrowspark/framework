<?php
namespace Viserio\Contracts\Queue;

interface Adapter
{
    /**
     * Push a new message onto the queue.
     *
     * @param $message The job to push
     */
    public function push($message);

    /**
     * Pop the next job off of the queue.
     *
     * @return Job|null
     */
    public function pop();

    /**
     * Release the job back onto the queue (increases it's attempt count).
     *
     * @param $job
     */
    public function release($job);

    /**
     * Delete a job from the queue.
     *
     * @param $job
     */
    public function delete($job);
}
