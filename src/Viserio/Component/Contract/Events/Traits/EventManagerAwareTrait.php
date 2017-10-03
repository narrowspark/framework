<?php
declare(strict_types=1);
namespace Viserio\Component\Contract\Events\Traits;

use Viserio\Component\Contract\Events\EventManager as EventManagerContract;

trait EventManagerAwareTrait
{
    /**
     * Event manager instance.
     *
     * @var null|\Viserio\Component\Contract\Events\EventManager
     */
    protected $eventManager;

    /**
     * Set a event manager instance.
     *
     * @param \Viserio\Component\Contract\Events\EventManager $eventManager
     *
     * @return $this
     */
    public function setEventManager(EventManagerContract $eventManager)
    {
        $this->eventManager = $eventManager;

        return $this;
    }
}
