<?php
declare(strict_types=1);
namespace Viserio\Component\Events;

use Viserio\Component\Contracts\Events\Event as EventContract;
use Viserio\Component\Contracts\Events\EventManager as EventManagerContract;
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
     * The synced events.
     *
     * @var array
     */
    protected $syncedEvents = [];

    /**
     * The sorted event listeners.
     *
     * @var array
     */
    protected $sorted = [];

    /**
     * Wildcard patterns.
     *
     * @var array
     */
    private $patterns = [];

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

        $listeners = $this->getListeners($event->getName());

        foreach ($listeners as $listener) {
            $result = false;

            if ($event->isPropagationStopped()) {
                return false;
            }

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
     * Returns the list of listeners for an event.
     *
     * The list is returned as an array, and the list of events are sorted by
     * their priority.
     *
     * @param string $eventName
     *
     * @return array
     */
    public function getListeners(string $eventName): array
    {
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
     * {@inheritdoc}
     */
    public function detach(string $eventName, $listener): bool
    {
        $this->validateEventName($eventName);

        if ($this->hasWildcards($eventName)) {
            $this->removeListenerPattern($eventName, $listener);

            return true;
        }

        if (! $this->hasListeners($eventName)) {
            return false;
        }

        foreach ($this->listeners[$eventName] as $priority => $listeners) {
            if (($key = \array_search($listener, $listeners, true)) !== false) {
                unset($this->listeners[$eventName][$priority][$key], $this->sorted[$eventName]);

                return true;
            }
        }

        return false;
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
     * Determine if a given event has listeners.
     *
     * @param string $eventName
     *
     * @return bool
     */
    public function hasListeners(string $eventName): bool
    {
        return (bool) \count($this->getListeners($eventName));
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
            \krsort($this->listeners[$eventName]);
            $this->sorted[$eventName] = \call_user_func_array(
                'array_merge',
                $this->listeners[$eventName]
            );
        }
    }

    /**
     * Checks whether a string contains any wildcard characters.
     *
     * @param string $subject
     *
     * @return bool
     */
    protected function hasWildcards(string $subject): bool
    {
        return \mb_strpos($subject, '*') !== false || \mb_strpos($subject, '#') !== false;
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
            foreach ($patterns as $pattern) {
                if ($pattern->test($eventName)) {
                    $pattern->bind($this, $eventName);
                }
            }
        }

        $this->syncedEvents[$eventName] = true;
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
    protected function addListenerPattern(ListenerPattern $pattern): void
    {
        $this->patterns[$pattern->getEventPattern()][] = $pattern;

        foreach ($this->syncedEvents as $eventName => $value) {
            if ($pattern->test($eventName)) {
                unset($this->syncedEvents[$eventName]);
            }
        }
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
        if (! isset($this->patterns[$eventPattern])) {
            return;
        }

        foreach ($this->patterns[$eventPattern] as $key => $pattern) {
            if ($listener == $pattern->getListener()) {
                $pattern->unbind($this);

                unset($this->patterns[$eventPattern][$key]);
            }
        }
    }
}
