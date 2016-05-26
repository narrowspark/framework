<?php
namespace Viserio\Events;

use Viserio\Contracts\Events\Dispatcher as DispatcherContract;

class ListenerPattern
{
    /**
     * Wildcards separators.
     *
     * @var array
     */
    private $wildcardsSeparators = [
        // Trailing single-wildcard with separator prefix
        '/\\\\\.\\\\\*$/'     => '(?:\.\w+)?',
        // Single-wildcard with separator prefix
        '/\\\\\.\\\\\*/'      => '(?:\.\w+)',
        // Single-wildcard without separator prefix
        '/(?<!\\\\\.)\\\\\*/' => '(?:\w+)',
        // Multi-wildcard with separator prefix
        '/\\\\\.#/'           => '(?:\.\w+)*',
        // Multi-wildcard without separator prefix
        '/(?<!\\\\\.)#/'      => '(?:|\w+(?:\.\w+)*)',
    ];

    /**
     * The event priority.
     *
     * @var array
     */
    protected $priority = [];

    /**
     * The regex for the event.
     *
     * @var array
     */
    protected $regex = [];

    /**
     * The event.
     *
     * @var callback
     */
    protected $listener;

    /**
     * The event.
     *
     * @var callback
     */
    protected $listenerProvider;

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
     * Constructor.
     *
     * @param string   $eventPattern
     * @param callback $listener
     * @param int      $priority
     */
    public function __construct(string $eventPattern, $listener, int $priority = 0)
    {
        $this->eventPattern     = $eventPattern;
        $this->listenerProvider = function () use ($listener) {
            return $listener;
        };
        $this->priority         = $priority;

        $this->regex            = $this->createRegex($eventPattern);
    }

    /**
     * Get the event pattern.
     *
     * @return string
     */
    public function getEventPattern(): string
    {
        return $this->eventPattern;
    }

    /**
     * Get the listener.
     *
     * @return mixed
     */
    public function getListener()
    {
        if (!isset($this->listener) && isset($this->listenerProvider)) {
            $this->listener = call_user_func($this->listenerProvider);
            unset($this->listenerProvider);
        }

        return $this->listener;
    }

    /**
     * Adds this pattern's listener to an event.
     *
     * @param DispatcherContract $dispatcher
     * @param string             $eventName
     */
    public function bind(DispatcherContract $dispatcher, $eventName)
    {
        if (isset($this->events[$eventName])) {
            return;
        }

        $dispatcher->on($eventName, $this->getListener(), $this->priority);
        $this->events[$eventName] = true;
    }

    /**
     * Removes this pattern's listener from all events to which it was
     * previously added.
     *
     * @param DispatcherContract $dispatcher
     */
    public function unbind(DispatcherContract $dispatcher)
    {
        foreach ($this->events as $eventName => $value) {
            $dispatcher->removeListener($eventName, $this->getListener());
        }

        $this->events = [];
    }

    /**
     * Tests if this pattern matches and event name.
     *
     * @param string $eventName
     *
     * @return bool
     */
    final public function test(string $eventName): bool
    {
        return preg_match($this->regex, $eventName);
    }

    /**
     * Transforms an event pattern into a regular expression.
     *
     * @param string $eventPattern
     *
     * @return string
     */
    private function createRegex(string $eventPattern): string
    {
        return sprintf('/^%s$/', preg_replace(
            array_keys($this->wildcardsSeparators),
            array_values($this->wildcardsSeparators),
            preg_quote($eventPattern, '/')
        ));
    }
}
