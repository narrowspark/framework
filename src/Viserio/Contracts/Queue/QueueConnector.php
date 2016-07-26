<?php
declare(strict_types=1);
namespace Viserio\Contracts\Queue;

interface QueueConnector
{
    /**
     * Push a new job onto the queue.
     *
     * @param string|object|\Closure $job
     * @param mixed                  $data
     * @param string|null            $queue
     *
     * @return mixed
     */
    public function push($job, $data = '', string $queue = null);

    /**
     * Push a raw payload onto the queue.
     *
     * @param string      $payload
     * @param string|null $queue
     * @param array       $options
     *
     * @return mixed
     */
    public function pushRaw(string $payload, string $queue = null, array $options = []);

    /**
     * Push a new job onto the queue after a delay.
     *
     * @param \DateTime|int          $delay
     * @param string|object|\Closure $job
     * @param mixed                  $data
     * @param string|null            $queue
     *
     * @return mixed
     */
    public function later($delay, $job, $data = '', string $queue = null);

    /**
     * Pop the next job off of the queue.
     *
     * @param string|null $queue
     *
     * @return \Viserio\Contracts\Queue\Job|null
     */
    public function pop(string $queue = null);

    /**
     * Push a new job onto the queue.
     *
     * @param string                 $queue
     * @param string|object|\Closure $job
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
     * @param string|object|\Closure $job
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
     * @param string|null $queue
     *
     * @return mixed
     */
    public function bulk(array $jobs, $data = '', string $queue = null);

    /**
     * Get the queue or return the default.
     *
     * @param string|null $queue
     *
     * @return string
     */
    public function getQueue($queue): string;
}
