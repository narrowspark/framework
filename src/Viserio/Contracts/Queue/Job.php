<?php
namespace Viserio\Contracts\Queue;

interface Job
{
    const STATUS_UNKNOWN = 0;

    const STATUS_REQUEUE = 1;

    const STATUS_FAILED  = 2;

    const STATUS_DELETE  = 3;

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
    public function getInfo();

    /**
     * @return array
     */
    public function getMetadata();

    /**
     * @return null|string
     */
    public function getFailMessage();

    /**
     * @return int
     */
    public function getAttempts();

    /**
     * @return int
     */
    public function getStatus();

    /**
     * Mark the job as failed.
     *
     * @param string   $message Info about the failure
     * @param int|null $delay   The requeue delay
     */
    public function fail($message, $delay = null);

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
