<?php
namespace Viserio\Contracts\Queue;

interface Job
{
    const STATUS_UNKNOWN = 0;

    const STATUS_REQUEUE = 1;

    const STATUS_FAILED = 2;

    const STATUS_DELETE = 3;

    /**
     * @return mixed
     */
    public function getId();

    /**
     * @return mixed
     */
    public function getData();

    /**
     * @return string
     */
    public function getInfo(): string;

    /**
     * @return array
     */
    public function getMetadata(): array;

    /**
     * @return null|string
     */
    public function getFailMessage();

    /**
     * @return int
     */
    public function getAttempts(): int;

    /**
     * @return int
     */
    public function getStatus(): int;

    /**
     * Mark the job as failed.
     *
     * @param string   $message Info about the failure
     * @param int|null $delay   The requeue delay
     */
    public function fail(string $message, int $delay = null);

    /**
     * Mark the job as requeue.
     *
     * @param int|null $delay The requeue delay
     */
    public function requeue($delay = null);

    /**
     * Mark the job for deletion.
     */
    public function delete();
}
