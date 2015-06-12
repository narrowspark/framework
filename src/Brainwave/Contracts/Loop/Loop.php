<?php

namespace Brainwave\Contracts\Loop;

/**
 * Narrowspark - a PHP 5 framework.
 *
 * @author      Daniel Bannert <info@anolilab.de>
 * @copyright   2015 Daniel Bannert
 *
 * @link        http://www.narrowspark.de
 *
 * @license     http://www.narrowspark.com/license
 *
 * @version     0.10-dev
 */

/**
 * Loop.
 *
 * @author  Daniel Bannert
 *
 * @since   0.10-dev
 */
interface Loop
{
    /**
     * Determines if the necessary components for the loop class are available.
     *
     * @return bool
     */
    public static function enabled();

    /**
     * Executes a single tick, processing callbacks and handling any available I/O.
     *
     * @param bool $blocking Determines if the tick should block and wait for I/O if no other tasks are scheduled.
     */
    public function tick($blocking = true);

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
    public function isRunning();

    /**
     * Removes all events (I/O, timers, callbacks, signal handlers, etc.) from the loop.
     */
    public function flush();

    /**
     * Determines if there are any pending events in the loop. Returns true if there are no pending events.
     *
     * @return bool
     */
    public function isEmpty();

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
    public function maxScheduleDepth($depth = null);

    /**
     * Define a callback function to be run after all I/O has been handled in the current tick.
     * Callbacks are called in the order defined.
     *
     * @param callable $callback
     * @param mixed[]|null $args Array of arguments to be passed to the callback function.
     */
    public function schedule(callable $callback, array $args = null);
}
