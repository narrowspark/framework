<?php
declare(strict_types=1);
namespace Viserio\Component\Contracts\Events\Traits;

use RuntimeException;
use Viserio\Component\Contracts\Events\EventManager as EventManagerContract;

trait EventManagerAwareTrait
{
    /**
     * Event manager instance.
     *
     * @var null|\Viserio\Component\Contracts\Events\EventManager
     */
    protected $eventManager;

    /**
     * Set a event manager instance.
     *
     * @param \Viserio\Component\Contracts\Events\EventManager $eventManager
     *
     * @return $this
     */
    public function setEventManager(EventManagerContract $eventManager)
    {
        $this->eventManager = $eventManager;

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
        if (! $this->eventManager) {
            throw new RuntimeException('EventManager is not set up.');
        }

        return $this->eventManager;
    }
}
