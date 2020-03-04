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

interface FailedJobProvider
{
    /**
     * Log a failed job into storage.
     */
    public function log(string $connection, string $queue, string $payload): ?int;

    /**
     * Get a list of all of the failed jobs.
     */
    public function getAll(): array;

    /**
     * Get a single failed job.
     */
    public function find($id): array;

    /**
     * Delete a single failed job from storage.
     */
    public function delete($id): bool;

    /**
     * Clear all of the failed jobs from storage.
     */
    public function clear();
}
