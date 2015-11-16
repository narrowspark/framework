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

<<<<<<< HEAD:src/Brainwave/Loop/Timer/TimerManager.php
use Brainwave\Contracts\Loop\Loop as LoopContract;
use Brainwave\Contracts\Loop\Timer as TimerContract;
=======
use Viserio\Contracts\Loop\Loop as LoopContract;
use Viserio\Contracts\Loop\Timer as TimerContract;
>>>>>>> develop:src/Viserio/Loop/Timer/TimerManager.php

/**
 * TimerManager.
 *
 * @author  Daniel Bannert
 *
 * @since   0.10.0-dev
 */
class TimerManager implements TimerContract
{
    /**
     * The loop with which this timer is associated.
     *
     * @var \Viserio\Contracts\Loop\Loop
     */
    protected $loop;

    /**
     * The interval after which this timer will execute, in seconds.
     *
     * @var float
     */
    protected $interval;

    /**
     * The callback that will be executed when this timer elapses.
     *
     * @var callable
     */
    protected $callback;

    /**
     * Whether the time is periodic.
     *
     * @var bool
     */
    protected $periodic;

    /**
     * Arbitrary data associated with timer.
     *
     * @var [type]
     */
    protected $data;

    /**
     * Constructor.
     *
     * @param LoopContract $loop
     * @param float        $interval
     * @param callable     $callback
     * @param boolean      $periodic
     */
    public function __construct(LoopContract $loop, $interval, callable $callback, $periodic = false)
    {
        $this->loop     = $loop;
        $this->interval = (float) floatval($interval);
        $this->callback = $callback;
        $this->periodic = (bool) $periodic;
    }

    /**
     * {@inheritdoc}
     */
    public function getLoop()
    {
        return $this->loop;
    }
    /**
     * {@inheritdoc}
     */
    public function getInterval()
    {
        return $this->interval;
    }
    /**
     * {@inheritdoc}
     */
    public function getCallback()
    {
        return $this->callback;
    }
    /**
     * {@inheritdoc}
     */
    public function setData($data)
    {
        $this->data = $data;
    }
    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        return $this->data;
    }
    /**
     * {@inheritdoc}
     */
    public function isPeriodic()
    {
        return $this->periodic;
    }
    /**
     * {@inheritdoc}
     */
    public function isActive()
    {
        return $this->loop->isTimerActive($this);
    }
    /**
     * {@inheritdoc}
     */
    public function cancel()
    {
        $this->loop->cancelTimer($this);
    }
}
