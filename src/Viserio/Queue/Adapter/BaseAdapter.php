<?php
namespace Viserio\Queue\Adapter;

abstract class BaseAdapter
{
    /**
     * Calculate the number of seconds with the given delay.
     *
     * @param \DateTime|int $delay
     *
     * @return int
     */
    protected function getSeconds($delay)
    {
        if ($delay instanceof \DateTime) {
            return max(0, $delay->getTimestamp() - $this->getTime());
        }

        return (int) $delay;
    }

    /**
     * Get the current system time.
     *
     * @return int
     */
    protected function getTime()
    {
        return time();
    }
}
