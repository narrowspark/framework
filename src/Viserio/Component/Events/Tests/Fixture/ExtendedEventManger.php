<?php
declare(strict_types=1);
namespace Viserio\Component\Events\Tests\Fixture;

use Viserio\Component\Events\EventManager;

class ExtendedEventManger extends EventManager
{
    /**
     * Determine if a given event has listeners.
     *
     * @param string $eventName
     *
     * @return bool
     */
    public function hasListeners(string $eventName): bool
    {
        return \count($this->getListeners($eventName)) !== 0;
    }
}
