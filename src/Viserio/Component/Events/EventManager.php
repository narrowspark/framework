<?php
declare(strict_types=1);
namespace Viserio\Component\Events;

use Closure;
use Viserio\Component\Contract\Events\Event as EventContract;
use Viserio\Component\Contract\Events\EventManager as EventManagerContract;
use Viserio\Component\Events\Traits\ValidateNameTrait;

class EventManager implements EventManagerContract
{
    use ValidateNameTrait;

    /**
     * The registered event listeners.
     *
     * @var array
     */
    protected $listeners = [];

    /**
     * The sorted event listeners.
     *
     * @var array
     */
    private $sorted = [];

    /**
     * The synced events.
     *
     * @var array
     */
    private $syncedEvents = [];

    /**
     * Wildcard patterns.
     *
     * @var array
     */
    private $patterns = [];

    /**
     * Returns the list of listeners for an event.
     *
     * The list is returned as an array, and the list of events are sorted by
     * their priority.
     *
     * @param null|string $eventName
     *
     * @return array
     *
     * @internal
     */
    public function getListeners(string $eventName = null): array
    {
        if ($eventName === null) {
            foreach ($this->listeners as $name => $eventListeners) {
                if (! isset($this->sorted[$name])) {
                    $this->sortListeners($name);
                }
            }

            return array_filter($this->sorted);
        }

        $this->validateEventName($eventName);

        $this->bindPatterns($eventName);

        if (! isset($this->listeners[$eventName])) {
            return [];
        }

        if (! isset($this->sorted[$eventName])) {
            $this->sortListeners($eventName);
        }

        return $this->sorted[$eventName];
    }

    /**
     * Gets the listener priority for a specific event.
     *
     * Returns null if the event or the listener does not exist.
     *
     * @param string         $eventName The name of the event
     * @param array|callable $listener  The listener
     *
     * @return null|int The event listener priority
     *
     * @internal
     */
    public function getListenerPriority(string $eventName, $listener): ?int
    {
        if (empty($this->listeners[$eventName])) {
            return null;
        }

        if (is_array($listener) && isset($listener[0]) && $listener[0] instanceof Closure) {
            $listener[0] = $listener[0]();
        }

        foreach ($this->listeners[$eventName] as $priority => $listeners) {
            foreach ($listeners as $key => $value) {
                if ($value !== $listener && is_array($value) && isset($value[0]) && $value[0] instanceof Closure) {
                    $value[0]                                     = $value[0]();
                    $this->listeners[$eventName][$priority][$key] = $value;
                }

                if ($listener === $value) {
                    return $priority;
                }
            }
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function attach(string $eventName, $listener, int $priority = 0): void
    {
        $this->validateEventName($eventName);

        if ($this->hasWildcards($eventName)) {
            $this->addListenerPattern(new ListenerPattern($eventName, $listener, $priority));
        } else {
            $this->listeners[$eventName][$priority][] = $listener;

            unset($this->sorted[$eventName]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function trigger($event, $target = null, array $argv = []): bool
    {
        if ((! \is_object($event) && ! ($event instanceof EventContract)) && \is_string($event)) {
            $event = new Event($event, $target, $argv);
        }

        if ($event->isPropagationStopped()) {
            return false;
        }

        $listeners = $this->getListeners($event->getName());

        foreach ($listeners as $listener) {
            $result = false;

            if ($listener !== null) {
                $result = $listener($event);
            }

            if ($result === false) {
                return false;
            }
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function detach(string $eventName, $listener): bool
    {
        $this->validateEventName($eventName);

        if ($this->hasWildcards($eventName)) {
            $this->removeListenerPattern($eventName, $listener);

            return true;
        }

        if (\count($this->getListeners($eventName)) === 0) {
            return false;
        }

        if (is_array($listener) && isset($listener[0]) && $listener[0] instanceof \Closure) {
            $listener[0] = $listener[0]();
        }

        $bool = false;

        foreach ($this->listeners[$eventName] as $priority => $listeners) {
            foreach ($listeners as $key => $value) {
                if ($listener !== $value && is_array($value) && isset($value[0]) && $value[0] instanceof Closure) {
                    $value[0] = $value[0]();
                }

                if ($value === $listener) {
                    unset($listeners[$key], $this->sorted[$eventName]);

                    $bool = true;
                } else {
                    $listeners[$key] = $value;
                }
            }

            if ($listeners) {
                $this->listeners[$eventName][$priority] = $listeners;
            } else {
                unset($this->listeners[$eventName][$priority]);

                $bool = true;
            }
        }

        return $bool;
    }

    /**
     * {@inheritdoc}
     */
    public function clearListeners(string $eventName): void
    {
        $this->validateEventName($eventName);

        unset($this->listeners[$eventName], $this->syncedEvents[$eventName]);
    }

    /**
     * Sort the listeners for a given event by priority.
     *
     * @param string $eventName
     *
     * @return void
     */
    protected function sortListeners(string $eventName): void
    {
        $this->sorted[$eventName] = [];

        // If listeners exist for the given event, we will sort them by the priority
        // so that we can call them in the correct order. We will cache off these
        // sorted event listeners so we do not have to re-sort on every events.
        if (isset($this->listeners[$eventName])) {
            krsort($this->listeners[$eventName]);

            foreach ($this->listeners[$eventName] as $priority => $listeners) {
                foreach ($listeners as $k => $listener) {
                    if (is_array($listener) && isset($listener[0]) && $listener[0] instanceof Closure) {
                        $listener[0]                                = $listener[0]();
                        $this->listeners[$eventName][$priority][$k] = $listener;
                    }

                    $this->sorted[$eventName][] = $listener;
                }
            }
        }
    }

    /**
     * Binds all patterns that match the specified event name.
     *
     * @param string $eventName
     *
     * @return void
     */
    protected function bindPatterns(string $eventName): void
    {
        if (isset($this->syncedEvents[$eventName])) {
            return;
        }

        foreach ($this->patterns as $eventPattern => $patterns) {
            /** @var ListenerPattern $pattern */
            foreach ($patterns as $pattern) {
                if ($pattern->test($eventName)) {
                    $pattern->bind($this, $eventName);
                }
            }
        }

        $this->syncedEvents[$eventName] = true;
    }

    /**
     * Removes an event listener from any events to which it was applied due to
     * pattern matching.
     *
     * This method cannot be used to remove a listener from a pattern that was
     * never registered.
     *
     * @param string $eventPattern
     * @param mixed  $listener
     *
     * @return void
     */
    protected function removeListenerPattern(string $eventPattern, $listener): void
    {
        if (empty($this->patterns[$eventPattern])) {
            return;
        }

        /** @var ListenerPattern $pattern */
        foreach ($this->patterns[$eventPattern] as $key => $pattern) {
            if ($listener == $pattern->getListener()) {
                $pattern->unbind($this);

                unset($this->patterns[$eventPattern][$key]);
            }
        }
    }

    /**
     * Checks whether a string contains any wildcard characters.
     *
     * @param string $subject
     *
     * @return bool
     */
    private function hasWildcards(string $subject): bool
    {
        return \mb_strpos($subject, '*') !== false || \mb_strpos($subject, '#') !== false;
    }

    /**
     * Adds an event listener for all events matching the specified pattern.
     *
     * This method will lazily register the listener when a matching event is
     * dispatched.
     *
     * @param \Viserio\Component\Events\ListenerPattern $pattern
     *
     * @return void
     */
    private function addListenerPattern(ListenerPattern $pattern): void
    {
        $this->patterns[$pattern->getEventPattern()][] = $pattern;

        foreach ($this->syncedEvents as $eventName => $value) {
            if ($pattern->test($eventName)) {
                unset($this->syncedEvents[$eventName]);
            }
        }
    }
}
