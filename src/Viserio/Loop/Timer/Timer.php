<?php
namespace Viserio\Loop;

use Viserio\Contracts\Loop\Timer as TimerContract;

/**
 * Timer.
 *
 * @author  Daniel Bannert
 *
 * @since   0.10.0
 */
class Timer
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
