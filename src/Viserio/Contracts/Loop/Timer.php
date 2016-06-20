<?php
namespace Viserio\Contracts\Loop;

interface Timer
{
    /**
     * Get the loop with which this timer is associated
     *
     * @return LoopInterface
     */
    public function getLoop(): \Viserio\Contracts\Loop\LoopInterface;

    /**
     * Get the interval after which this timer will execute, in seconds
     *
     * @return float
     */
    public function getInterval(): float;

    /**
     * Get the callback that will be executed when this timer elapses
     *
     * @return callable
     */
    public function getCallback(): callable;

    /**
     * Set arbitrary data associated with timer
     *
     * @param mixed $data
     */
    public function setData($data);

    /**
     * Get arbitrary data associated with timer
     *
     * @return mixed
     */
    public function getData();

    /**
     * Determine whether the time is periodic
     *
     * @return bool
     */
    public function isPeriodic(): bool;

    /**
     * Determine whether the time is active
     *
     * @return bool
     */
    public function isActive(): bool;

    /**
     * Cancel this timer
     */
    public function cancel();
}
