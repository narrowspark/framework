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

namespace Viserio\Component\Events;

use Closure;
use Viserio\Contract\Events\EventManager as EventManagerContract;

class ListenerPattern
{
    /**
     * The event priority.
     *
     * @var int
     */
    protected $priority;

    /**
     * The regex for the event.
     *
     * @var string
     */
    protected $regex;

    /**
     * The event.
     */
    protected $listener;

    /**
     * The listener provider.
     */
    protected $provider;

    /**
     * Pattern for the event.
     *
     * @var string
     */
    protected $eventPattern;

    /**
     * All added events.
     *
     * @var array
     */
    protected $events = [];

    /**
     * Wildcards separators.
     *
     * @var array
     */
    private static $wildcardsSeparators = [
        // Trailing single-wildcard with separator prefix
        '/\\\\\.\\\\\*$/' => '(?:\.\w+)?',
        // Single-wildcard with separator prefix
        '/\\\\\.\\\\\*/' => '(?:\.\w+)',
        // Single-wildcard without separator prefix
        '/(?<!\\\\\.)\\\\\*/' => '(?:\w+)',
        // Multi-wildcard with separator prefix
        '/\\\\\.#/' => '(?:\.\w+)*',
        // Multi-wildcard without separator prefix
        '/(?<!\\\\\.)#/' => '(?:|\w+(?:\.\w+)*)',
    ];

    /**
     * Create a new listener pattern instance.
     */
    public function __construct(string $eventPattern, $listener, int $priority = 0)
    {
        if ($listener instanceof Closure
            || \is_string($listener)
            || \is_array($listener)
            || \is_callable($listener)
        ) {
            $this->provider = $listener;
        } else {
            $this->provider = static function () use ($listener) {
                return $listener;
            };
        }

        $this->eventPattern = $eventPattern;
        $this->priority = $priority;
        $this->regex = $this->createRegex($eventPattern);
    }

    /**
     * Get the listener.
     */
    public function getListener()
    {
        if ($this->listener === null && $this->provider !== null) {
            $this->listener = $this->provider;
            $this->provider = null;
        }

        return $this->listener;
    }

    /**
     * Get the event pattern.
     */
    public function getEventPattern(): string
    {
        return $this->eventPattern;
    }

    /**
     * Adds this pattern's listener to an event.
     */
    public function bind(EventManagerContract $dispatcher, string $eventName): void
    {
        if (isset($this->events[$eventName])) {
            return;
        }

        $dispatcher->attach($eventName, $this->getListener(), $this->priority);
        $this->events[$eventName] = true;
    }

    /**
     * Removes this pattern's listener from all events to which it was
     * previously added.
     */
    public function unbind(EventManagerContract $dispatcher): void
    {
        foreach ($this->events as $eventName => $value) {
            $dispatcher->detach($eventName, $this->getListener());
        }

        $this->events = [];
    }

    /**
     * Tests if this pattern matches and event name.
     */
    final public function test(string $eventName): bool
    {
        return (bool) \preg_match($this->regex, $eventName);
    }

    /**
     * Transforms an event pattern into a regular expression.
     */
    private function createRegex(string $eventPattern): string
    {
        return \sprintf('/^%s$/i', \preg_replace(
            \array_keys(self::$wildcardsSeparators),
            \array_values(self::$wildcardsSeparators),
            \str_replace('\#', '#', \preg_quote($eventPattern, '/'))
        ));
    }
}
