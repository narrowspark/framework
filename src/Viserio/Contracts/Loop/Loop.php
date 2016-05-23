<?php
namespace Viserio\Contracts\Loop;

interface Loop
{
    /**
     * Executes a single tick, processing callbacks and handling any available I/O.
     *
     * @param bool $blocking Determines if the tick should block and wait for I/O if no other tasks are scheduled.
     */
    public function tick(bool $blocking = true);

    /**
     * Schedules a callback to be executed immediately in the next tick.
     *
     * This function should be used when a callback needs to be executed later,
     * but needs to do so before any more event callbacks are invoked.
     *
     * @param callable $callback
     */
    public function nextTick(callable $callback);

    /**
     * Schedules a callback to be executed in the future.
     *
     * This function is typically used to queue up callbacks for asynchronous
     * events, usually from an event device.
     *
     * @param callable $callback
     */
    public function futureTick(callable $callback);

    /**
     * Stops the event loop.
     */
    public function stop();

    /**
     * Runs the event loop until there are no more events to process.
     */
    public function run();

    /**
     * Determines if the event loop is running.
     *
     * @return bool
     */
    public function isRunning(): bool;

    /**
     * Removes all events (I/O, timers, callbacks, signal handlers, etc.) from the loop.
     */
    public function flush();

    /**
     * Determines if there are any pending events in the loop. Returns true if there are no pending events.
     *
     * @return bool
     */
    public function isEmpty(): bool;

    /**
     * Performs any reinitializing necessary after forking.
     */
    public function reInit();

    /**
     * Sets the maximum number of callbacks set with schedule() that will be executed per tick.
     *
     * @param int|null $depth
     *
     * @return int Current max depth if $depth = null or previous max depth otherwise.
     */
    public function maxScheduleDepth(int $depth = null): int;

    /**
     * Define a callback function to be run after all I/O has been handled in the current tick.
     * Callbacks are called in the order defined.
     *
     * @param callable     $callback
     * @param mixed[]|null $args     Array of arguments to be passed to the callback function.
     */
    public function schedule(callable $callback, array $args = null);
}
