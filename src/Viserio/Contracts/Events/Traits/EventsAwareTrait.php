<?php

declare(strict_types=1);
namespace Viserio\Contracts\Events\Traits;

use RuntimeException;
use Viserio\Contracts\Events\Dispatcher as DispatcherContract;

trait EventsAwareTrait
{
    /**
     * Event dispatcher instance.
     *
     * @var \Viserio\Contracts\Events\Dispatcher|null
     */
    protected $events;

    /**
     * Set a event dispatcher instance.
     *
     * @param \Viserio\Contracts\Events\Dispatcher $events
     *
     * @return $this
     */
    public function setEventsDispatcher(DispatcherContract $events)
    {
        $this->events = $events;

        return $this;
    }

    /**
     * Get the event dispatcher instance.
     *
     * @throws \RuntimeException
     *
     * @return \Viserio\Contracts\Events\Dispatcher
     */
    public function getEventsDispatcher(): DispatcherContract
    {
        if (! $this->events) {
            throw new RuntimeException('Events dispatcher is not set up.');
        }

        return $this->events;
    }
}
