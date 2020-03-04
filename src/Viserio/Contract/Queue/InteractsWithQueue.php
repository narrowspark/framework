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

interface InteractsWithQueue
{
    /**
     * Set the base queue job instance.
     *
     * @param \Viserio\Contract\Queue\Job $job
     *
     * @return static
     */
    public function setJob(Job $job);

    /**
     * Get the number of times the job has been attempted.
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
     */
    public function release(int $delay = 0);
}
