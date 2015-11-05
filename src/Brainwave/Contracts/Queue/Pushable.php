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
 * @version     0.9.7-dev
 */

/**
 * Pushable.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.7-dev
 */
interface Pushable
{
    /**
     * Push a new message onto the queue.
     *
     * @param mixed    $data     The job's data
     * @param string   $info     Info text (used for logging)
     * @param array    $metadata Additional data about the job
     * @param int|null $delay    Delay in seconds (null for adapter default)
     */
    public function push($data, $info, array $metadata = [], $delay = null);
}
