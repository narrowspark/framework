<?php
namespace Viserio\Loop;

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
 * @version     0.10.0-dev
 */

use Viserio\Contracts\Loop\Timer as TimerContract;

/**
 * Timer.
 *
 * @author  Daniel Bannert
 *
 * @since   0.10.0-dev
 */
class Timer implements TimerContract
{
    /**
     * [$time description]
     *
     * @var string
     */
    protected $time;

    /**
     * [$timers description]
     *
     * @var \SplObjectStorage
     */
    protected $timers;

    /**
     * [$scheduler description]
     *
     * @var \SplPriorityQueue
     */
    protected $scheduler;

    /**
     * Construct.
     */
    public function __construct()
    {
        $this->timers    = new \SplObjectStorage();
        $this->scheduler = new \SplPriorityQueue();
    }

    /**
     * [updateTime description]
     *
     * @return string
     */
    public function updateTime()
    {
        return $this->time = microtime(true);
    }

    /**
     * [getTime description]
     *
     * @return string
     */
    public function getTime()
    {
        return $this->time ?: $this->updateTime();
    }
}
