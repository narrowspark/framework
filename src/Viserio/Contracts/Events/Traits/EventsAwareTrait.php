<?php
declare(strict_types=1);
namespace Viserio\Contracts\Events\Traits;

use RuntimeException;
use Viserio\Contracts\Events\EventManager as EventManagerContract;

trait EventsAwareTrait
{
    /**
     * Event manager instance.
     *
     * @var \Viserio\Contracts\Events\EventManager|null
     */
    protected $events;

    /**
     * Set a event manager instance.
     *
     * @param \Viserio\Contracts\Events\EventManager $events
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
     * @return \Viserio\Contracts\Events\EventManager
     */
    public function getEventManager(): EventManagerContract
    {
        if (! $this->events) {
            throw new RuntimeException('EventManager is not set up.');
        }

        return $this->events;
    }
}
