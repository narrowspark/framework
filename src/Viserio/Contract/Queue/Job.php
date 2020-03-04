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

interface Job
{
    /**
     * Runs the job.
     */
    public function run();

    /**
     * Delete the job from the queue.
     */
    public function delete();

    /**
     * Determine if the job has been deleted.
     */
    public function isDeleted(): bool;

    /**
     * Release the job back into the queue.
     */
    public function release(int $delay = 0);

    /**
     * Determine if the job was released back into the queue.
     */
    public function isReleased(): bool;

    /**
     * Determine if the job has been deleted or released.
     */
    public function isDeletedOrReleased(): bool;

    /**
     * Get the number of times the job has been attempted.
     */
    public function attempts(): int;

    /**
     * Get the name of the queued job class.
     */
    public function getName(): string;

    /**
     * Call the failed method on the job instance.
     */
    public function failed();

    /**
     * Get the name of the queue the job belongs to.
     */
    public function getQueue(): string;

    /**
     * Get the raw body string for the job.
     */
    public function getRawBody(): string;

    /**
     * Get the job identifier.
     */
    public function getJobId(): string;

    /**
     * Get the resolved name of the queued job class.
     */
    public function resolveName(): string;
}
