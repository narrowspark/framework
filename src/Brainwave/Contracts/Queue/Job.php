<?php

namespace Brainwave\Contracts\Queue;

/**
 * Narrowspark - a PHP 5 framework.
 *
 * @author      Daniel Bannert <info@anolilab.de>
 * @copyright   2015 Daniel Bannert
 *
 * @link        http://www.narrowspark.de
 *
 * @license     http://www.narrowspark.com/license
 *
 * @version     0.9.8-dev
 */

/**
 * Job.
 *
 * @author  Daniel Bannert
 *
 * @since   0.10-dev
 */
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
