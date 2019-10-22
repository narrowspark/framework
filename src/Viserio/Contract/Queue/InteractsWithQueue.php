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
