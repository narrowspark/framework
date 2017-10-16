<?php
declare(strict_types=1);
namespace Viserio\Component\Contract\Queue;

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
     *
     * @return bool
     */
    public function isDeleted(): bool;

    /**
     * Release the job back into the queue.
     *
     * @param int $delay
     */
    public function release(int $delay = 0);

    /**
     * Determine if the job was released back into the queue.
     *
     * @return bool
     */
    public function isReleased(): bool;

    /**
     * Determine if the job has been deleted or released.
     *
     * @return bool
     */
    public function isDeletedOrReleased(): bool;

    /**
     * Get the number of times the job has been attempted.
     *
     * @return int
     */
    public function attempts(): int;

    /**
     * Get the name of the queued job class.
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Call the failed method on the job instance.
     */
    public function failed();

    /**
     * Get the name of the queue the job belongs to.
     *
     * @return string
     */
    public function getQueue(): string;

    /**
     * Get the raw body string for the job.
     *
     * @return string
     */
    public function getRawBody(): string;

    /**
     * Get the job identifier.
     *
     * @return string
     */
    public function getJobId(): string;

    /**
     * Get the resolved name of the queued job class.
     *
     * @return string
     */
    public function resolveName(): string;
}
