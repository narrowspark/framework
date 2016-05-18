<?php
namespace Viserio\Events\Traits;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;

trait EventAwareTrait
{
    /**
     * Event manager for triggering translator events.
     *
     * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
     */
    protected $events;

    /**
     * Sets a event.
     *
     * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $event
     *
     * @return self
     */
    public function setEvent(EventDispatcherInterface $event)
    {
        $this->events = $event;

        return $this;
    }

    /**
     * Returns the event instance.
     *
     * @return \Symfony\Component\EventDispatcher\EventDispatcherInterface
     */
    public function getEvent()
    {
        return $this->events;
    }
}
