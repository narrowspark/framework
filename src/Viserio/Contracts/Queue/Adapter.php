<?php
namespace Viserio\Contracts\Queue;

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
 * Adapter.
 *
 * @author  Daniel Bannert
 *
 * @since   0.10-dev
 */
interface Adapter
{
    /**
     * Push a new message onto the queue.
     *
     * @param $message The job to push
     */
    public function push($message);

    /**
     * Pop the next job off of the queue.
     *
     * @return Job|null
     */
    public function pop();

    /**
     * Release the job back onto the queue (increases it's attempt count).
     *
     * @param $job
     */
    public function release($job);

    /**
     * Delete a job from the queue.
     *
     * @param $job
     */
    public function delete($job);
}
