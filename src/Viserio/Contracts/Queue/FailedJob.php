<?php
namespace Viserio\Contracts\Queue;

interface FailedJob
{
    /**
     * Log a failed job into storage.
     *
     * @param string $connection
     * @param string $queue
     * @param string $payload
     */
    public function log(string $connection, string $queue, string $payload);

    /**
     * Get a list of all of the failed jobs.
     *
     * @return array
     */
    public function all(): array;

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
    public function forget($id): bool;

    /**
     * Flush all of the failed jobs from storage.
     */
    public function flush();
}
