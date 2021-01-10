<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018-2020 Daniel Bannert
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/narrowspark/automatic
 */

namespace Viserio\Contract\Queue;

use Closure;
use DateTime;

interface QueueConnector
{
    /**
     * Push a new job onto the queue.
     *
     * @param Closure|object|string $job
     */
    public function push($job, $data = '', ?string $queue = null);

    /**
     * Push a raw payload onto the queue.
     */
    public function pushRaw(string $payload, ?string $queue = null, array $options = []);

    /**
     * Push a new job onto the queue after a delay.
     *
     * @param DateTime|int          $delay
     * @param Closure|object|string $job
     */
    public function later($delay, $job, $data = '', ?string $queue = null);

    /**
     * Pop the next job off of the queue.
     *
     * @return null|\Viserio\Contract\Queue\Job
     */
    public function pop(?string $queue = null): ?Job;

    /**
     * Push a new job onto the queue.
     *
     * @param Closure|object|string $job
     */
    public function pushOn(string $queue, $job, $data = '');

    /**
     * Push a new job onto the queue after a delay.
     *
     * @param DateTime|int          $delay
     * @param Closure|object|string $job
     */
    public function laterOn(string $queue, $delay, $job, $data = '');

    /**
     * Push an array of jobs onto the queue.
     */
    public function bulk(array $jobs, $data = '', ?string $queue = null);

    /**
     * Get the queue or return the default.
     *
     * @param null|string $queue
     */
    public function getQueue($queue): string;
}
