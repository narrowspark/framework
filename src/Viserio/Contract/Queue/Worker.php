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

use Throwable;

interface Worker
{
    /**
     * Listen to the given queue in a loop.
     */
    public function daemon(
        string $connectionName,
        ?string $queue = null,
        int $delay = 0,
        int $memory = 128,
        int $timeout = 60,
        int $sleep = 3,
        int $maxTries = 0
    );

    /**
     * Process a given job from the queue.
     *
     * @param \Viserio\Contract\Queue\Job $job
     *
     * @throws Throwable
     */
    public function process(string $connection, Job $job, int $maxTries = 0, int $delay = 0);

    /**
     * Run the next job on the queue.
     */
    public function runNextJob(
        string $connectionName,
        ?string $queue = null,
        int $delay = 0,
        int $sleep = 3,
        int $maxTries = 0
    );

    /**
     * Determine if the memory limit has been exceeded.
     */
    public function memoryExceeded(int $memoryLimit): bool;

    /**
     * Stop listening and bail out of the script.
     */
    public function stop();

    /**
     * Sleep the script for a given number of seconds.
     */
    public function sleep(int $seconds);
}
