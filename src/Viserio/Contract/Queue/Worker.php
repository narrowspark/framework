<?php

declare(strict_types=1);

/**
 * This file is part of Narrowspark Framework.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Viserio\Contract\Queue;

use Throwable;

interface Worker
{
    /**
     * Listen to the given queue in a loop.
     *
     * @param string      $connectionName
     * @param null|string $queue
     * @param int         $delay
     * @param int         $memory
     * @param int         $timeout
     * @param int         $sleep
     * @param int         $maxTries
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
     * @param string                      $connection
     * @param \Viserio\Contract\Queue\Job $job
     * @param int                         $maxTries
     * @param int                         $delay
     *
     * @throws Throwable
     */
    public function process(string $connection, Job $job, int $maxTries = 0, int $delay = 0);

    /**
     * Run the next job on the queue.
     *
     * @param string      $connectionName
     * @param null|string $queue
     * @param int         $delay
     * @param int         $sleep
     * @param int         $maxTries
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
     *
     * @param int $memoryLimit
     *
     * @return bool
     */
    public function memoryExceeded(int $memoryLimit): bool;

    /**
     * Stop listening and bail out of the script.
     */
    public function stop();

    /**
     * Sleep the script for a given number of seconds.
     *
     * @param int $seconds
     */
    public function sleep(int $seconds);
}
