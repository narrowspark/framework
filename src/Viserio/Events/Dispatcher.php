<?php
declare(strict_types=1);
namespace Viserio\Events;

use Interop\Container\ContainerInterface as ContainerContract;
use Viserio\Contracts\Container\Traits\ContainerAwareTrait;
use Viserio\Contracts\Events\EventManager as EventManagerContract;
use Viserio\Events\Traits\ValidateNameTrait;
use Viserio\Support\Invoker;
use Viserio\Support\Str;

class Dispatcher implements EventManagerContract
{
    use ContainerAwareTrait;
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
     * Invoker instance.
     *
     * @var \Viserio\Support\Invoker
     */
    protected $invoker;

    /**
     * Wildcard patterns.
     *
     * @var array
     */
    private $patterns = [];

    /**
     * Create a new event dispatcher instance.
     *
     * @param ContainerContract $container
     */
    public function __construct(ContainerContract $container)
    {
        $this->container = $container;

        $invoker = new Invoker();
        $invoker->injectByTypeHint(true)
            ->injectByParameterName(true)
            ->setContainer($container);

        $this->invoker = $invoker;
    }

    /**
     * {@inhertidoc}
     */
    public function attach($event, $callback, $priority = 0)
    {
        if ($this->hasWildcards($event)) {
            $this->addListenerPattern(new ListenerPattern($event, $callback, $priority));
        } else {
            $this->listeners[$event][$priority][] = $callback;

            unset($this->sorted[$event]);
        }
    }

    /**
     * {@inhertidoc}
     */
    public function trigger($event, $target = null, $argv = [])
    {
        $listeners = $this->getListeners($event);
        $counter = count($listeners);

        foreach ($listeners as $listener) {
            --$counter;
            $result = false;

            if ($listener !== null) {
                $result = $this->invoker->call($listener, $arguments);
            }

            if ($result === false) {
                return false;
            }

            if ($counter > 0) {
                $repeater = $this->invoker->call($continue);

                if (! $repeater) {
                    break;
                }
            }
        }

        return true;
    }

    /**
     * {@inhertidoc}
     */
    public function detach($event, $callback)
    {
        if ($this->hasWildcards($event)) {
            $this->removeListenerPattern($event, $callback);

            return true;
        }

        if (! $this->hasListeners($event)) {
            return false;
        }

        foreach ($this->listeners[$event] as $priority => $listeners) {
            if (($key = array_search($callback, $listeners, true)) !== false) {
                unset($this->listeners[$event][$priority][$key], $this->sorted[$event]);

                return true;
            }
        }

        return false;
    }

    /**
     * {@inhertidoc}
     */
    public function clearListeners($event)
    {
        $this->detach($event, null);
    }

    /**
     * Sort the listeners for a given event by priority.
     *
     * @param string $eventName
     *
     * @return array
     */
    protected function sortListeners($eventName)
    {
        $this->sorted[$eventName] = [];

        // If listeners exist for the given event, we will sort them by the priority
        // so that we can call them in the correct order. We will cache off these
        // sorted event listeners so we do not have to re-sort on every events.

        if (isset($this->listeners[$eventName])) {
            krsort($this->listeners[$eventName]);
            $this->sorted[$eventName] = call_user_func_array(
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
    protected function hasWildcards($subject): bool
    {
        return Str::contains($subject, '*') || Str::contains($subject, '#');
    }

    /**
     * Binds all patterns that match the specified event name.
     *
     * @param string $eventName
     */
    protected function bindPatterns(string $eventName)
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
     * @param ListenerPattern $pattern
     */
    protected function addListenerPattern(ListenerPattern $pattern)
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
     */
    protected function removeListenerPattern(string $eventPattern, $listener)
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

    /**
     * Determine if a given event has listeners.
     *
     * @param string $eventName
     *
     * @return bool
     */
    protected function hasListeners(string $eventName): bool
    {
        return (bool) count($this->getListeners($eventName));
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
    protected function getListeners(string $eventName): array
    {
        $this->bindPatterns($eventName);

        if (! isset($this->listeners[$eventName])) {
            return [];
        }

        if (! isset($this->sorted[$eventName])) {
            $this->sortListeners($eventName);
        }

        return $this->sorted[$eventName];
    }
}
