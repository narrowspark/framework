<?php
declare(strict_types=1);
namespace Viserio\Component\Contracts\Events\Traits;

use RuntimeException;
use Viserio\Component\Contracts\Events\EventManager as EventManagerContract;

trait EventsAwareTrait
{
    /**
     * Event manager instance.
     *
     * @var \Viserio\Component\Contracts\Events\EventManager|null
     */
    protected $events;

    /**
     * Set a event manager instance.
     *
     * @param \Viserio\Component\Contracts\Events\EventManager $events
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
     * @return \Viserio\Component\Contracts\Events\EventManager
     */
    public function getEventManager(): EventManagerContract
    {
        if (! $this->events) {
            throw new RuntimeException('EventManager is not set up.');
        }

        return $this->events;
    }
}
