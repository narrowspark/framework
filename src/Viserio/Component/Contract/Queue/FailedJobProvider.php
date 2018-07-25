<?php
declare(strict_types=1);
namespace Viserio\Component\Contract\Queue;

interface FailedJobProvider
{
    /**
     * Log a failed job into storage.
     *
     * @param string $connection
     * @param string $queue
     * @param string $payload
     *
     * @return null|int
     */
    public function log(string $connection, string $queue, string $payload): ?int;

    /**
     * Get a list of all of the failed jobs.
     *
     * @return array
     */
    public function getAll(): array;

    /**
     * Get a single failed job.
     *
     * @param mixed $id
     *
     * @return array
     */
    public function find($id): array;

    /**
     * Delete a single failed job from storage.
     *
     * @param mixed $id
     *
     * @return bool
     */
    public function delete($id): bool;

    /**
     * Clear all of the failed jobs from storage.
     */
    public function clear();
}
