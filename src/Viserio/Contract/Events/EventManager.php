<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018-2020 Daniel Bannert
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/narrowspark/automatic
 */

namespace Viserio\Contract\Events;

use Closure;

interface EventManager
{
    /**
     * Subscribe to an event.
     *
     * @param null|callable|Closure $listener
     */
    public function attach(string $eventName, $listener, int $priority = 0): void;

    /**
     * Removes a specific listener from an event.
     *
     * If the listener could not be found, this method will return false. If it
     * was removed it will return true.
     *
     * @param null|callable|Closure $listener
     */
    public function detach(string $eventName, $listener): bool;

    /**
     * Clear all listeners for a given event.
     *
     * If the eventName argument is specified, all listeners for that event are
     * removed.
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
     * @param string|\Viserio\Contract\Events\Event $event
     * @param null|object|string                    $target
     */
    public function trigger($event, $target = null, array $argv = []): bool;
}
