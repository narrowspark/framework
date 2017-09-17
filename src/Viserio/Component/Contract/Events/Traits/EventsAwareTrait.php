<?php
declare(strict_types=1);
namespace Viserio\Component\Contract\Events\Traits;

use RuntimeException;
use Viserio\Component\Contract\Events\EventManager as EventManagerContract;

trait EventsAwareTrait
{
    /**
     * Event manager instance.
     *
     * @var null|\Viserio\Component\Contract\Events\EventManager
     */
    protected $events;

    /**
     * Set a event manager instance.
     *
     * @param \Viserio\Component\Contract\Events\EventManager $events
     *
     * @return $this
     */
    public function setEventManager(EventManagerContract $events)
    {
        $this->events = $events;

        return $this;
    }

    /**
     * Get the event manager instance.
     *
     * @throws \RuntimeException
     *
     * @return \Viserio\Component\Contract\Events\EventManager
     */
    public function getEventManager(): EventManagerContract
    {
        if (! $this->events) {
            throw new RuntimeException('EventManager is not set up.');
        }

        return $this->events;
    }
}
