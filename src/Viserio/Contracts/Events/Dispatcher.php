<?php
namespace Viserio\Contracts\Events;

interface Dispatcher
{
    /**
     * Subscribe to an event.
     *
     * @param string $eventName
     * @param mixed  $listener
     * @param int    $priority
     */
    public function on(string $eventName, $listener, int $priority = 0);

    /**
     * Subscribe to an event exactly once.
     *
     * @param string $eventName
     * @param mixed  $listener
     * @param int    $priority
     */
    public function once(string $eventName, $listener, int $priority = 0);

    /**
     * Emits an event.
     *
     * This method will return true if 0 or more listeners were succesfully
     * handled. false is returned if one of the events broke the event chain.
     *
     * If the continue is specified, this callback will be called every
     * time before the next event handler is called.
     *
     * If the continue returns false, event propagation stops. This
     * allows you to use the eventEmitter as a means for listeners to implement
     * functionality in your application, and break the event loop as soon as
     * some condition is fulfilled.
     *
     * Note that returning false from an event subscriber breaks propagation
     * and returns false, but if the continue-callback stops propagation, this
     * is still considered a 'successful' operation and returns true.
     *
     * Lastly, if there are 5 event handlers for an event. The continue
     * will be called at most 4 times.
     *
     * @param string $eventName
     * @param array  $arguments
     * @param mixed  $continue
     *
     * @return bool
     */
    public function emit(string $eventName, array $arguments = [], $continue = null): bool;

    /**
     * Returns the list of listeners for an event.
     *
     * The list is returned as an array, and the list of events are sorted by
     * their priority.
     *
     * @param string $eventName
     *
     * @return array
     */
    public function getListeners(string $eventName): array;

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
    public function off(string $eventName, $listener): bool;

    /**
     * Removes all listeners.
     *
     * If the eventName argument is specified, all listeners for that event are
     * removed. If it is not specified, every listener for every event is
     * removed.
     *
     * @param string $eventName
     */
    public function removeAllListeners($eventName = null);

    /**
     * Determine if a given event has listeners.
     *
     * @param string $eventName
     *
     * @return bool
     */
    public function hasListeners(string $eventName): bool;
}
