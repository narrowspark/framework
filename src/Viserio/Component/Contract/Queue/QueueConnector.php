<?php
declare(strict_types=1);
namespace Viserio\Component\Contract\Queue;

interface QueueConnector
{
    /**
     * Push a new job onto the queue.
     *
     * @param \Closure|object|string $job
     * @param mixed                  $data
     * @param null|string            $queue
     *
     * @return mixed
     */
    public function push($job, $data = '', string $queue = null);

    /**
     * Push a raw payload onto the queue.
     *
     * @param string      $payload
     * @param null|string $queue
     * @param array       $options
     *
     * @return mixed
     */
    public function pushRaw(string $payload, string $queue = null, array $options = []);

    /**
     * Push a new job onto the queue after a delay.
     *
     * @param \DateTime|int          $delay
     * @param \Closure|object|string $job
     * @param mixed                  $data
     * @param null|string            $queue
     *
     * @return mixed
     */
    public function later($delay, $job, $data = '', string $queue = null);

    /**
     * Pop the next job off of the queue.
     *
     * @param null|string $queue
     *
     * @return null|\Viserio\Component\Contract\Queue\Job
     */
    public function pop(string $queue = null): ?\Viserio\Component\Contract\Queue\Job;

    /**
     * Push a new job onto the queue.
     *
     * @param string                 $queue
     * @param \Closure|object|string $job
     * @param mixed                  $data
     *
     * @return mixed
     */
    public function pushOn(string $queue, $job, $data = '');

    /**
     * Push a new job onto the queue after a delay.
     *
     * @param string                 $queue
     * @param \DateTime|int          $delay
     * @param \Closure|object|string $job
     * @param mixed                  $data
     *
     * @return mixed
     */
    public function laterOn(string $queue, $delay, $job, $data = '');

    /**
     * Push an array of jobs onto the queue.
     *
     * @param array       $jobs
     * @param mixed       $data
     * @param null|string $queue
     *
     * @return mixed
     */
    public function bulk(array $jobs, $data = '', string $queue = null);

    /**
     * Get the queue or return the default.
     *
     * @param null|string $queue
     *
     * @return string
     */
    public function getQueue($queue): string;
}
