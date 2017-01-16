<?php
declare(strict_types=1);
namespace Viserio\Component\Contracts\Events;

interface EventManager
{
    /**
     * Subscribe to an event.
     *
     * @param string $eventName
     * @param mixed  $listener
     * @param int    $priority
     */
    public function attach(string $eventName, $listener, int $priority = 0);

    /**
     * Removes a specific listener from an event.
     *
     * If the listener could not be found, this method will return false. If it
     * was removed it will return true.
     *
     * @param string   $eventName
     * @param callable $listener
     *
     * @return bool
     */
    public function detach(string $eventName, $listener): bool;

    /**
     * RClear all listeners for a given event.
     *
     * If the eventName argument is specified, all listeners for that event are
     * removed.
     *
     * @param string|null $eventName
     *
     * @return void
     */
    public function clearListeners(string $eventName): void;

    /**
     * Emits an event.
     *
     * This method will return true if 0 or more listeners were succesfully
     * handled. False is returned if one of the events broke the event chain.
     *
     * Note that returning false from an event subscriber breaks propagation
     * and returns false.
     *
     * @param string|\Viserio\Component\Contracts\Events\Event $event
     * @param object|string|null                               $target
     * @param array                                            $argv
     *
     * @return bool
     */
    public function trigger($event, $target = null, array $argv = []): bool;
}
