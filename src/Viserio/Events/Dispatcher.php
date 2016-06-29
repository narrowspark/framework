<?php
namespace Viserio\Events;

use Interop\Container\ContainerInterface as ContainerContract;
use Viserio\Contracts\Events\Dispatcher as DispatcherContract;
use Viserio\Support\Invoker;
use Viserio\Support\Str;
use Viserio\Support\Traits\ContainerAwareTrait;

class Dispatcher implements DispatcherContract
{
    use ContainerAwareTrait;

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
    public function on(string $eventName, $listener, int $priority = 0)
    {
        if ($this->hasWildcards($eventName)) {
            $this->addListenerPattern(new ListenerPattern($eventName, $listener, $priority));
        } else {
            $this->listeners[$eventName][$priority][] = $listener;
            unset($this->sorted[$eventName]);
        }
    }

    /**
     * {@inhertidoc}
     */
    public function once(string $eventName, $listener, int $priority = 0)
    {
        $wrapper = null;
        $wrapper = function () use ($eventName, $listener, &$wrapper) {
            $this->off($eventName, $wrapper);

            return $this->invoker->call($listener, func_get_args());
        };

        $this->on($eventName, $wrapper, $priority);
    }

    /**
     * {@inhertidoc}
     */
    public function emit(string $eventName, array $arguments = [], $continue = null): bool
    {
        $listeners = $this->getListeners($eventName);

        if ($continue === null) {
            return $this->continueEmit($listeners, $arguments);
        }

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
    public function getListeners(string $eventName): array
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

    /**
     * {@inhertidoc}
     */
    public function off(string $eventName, $listener): bool
    {
        if ($this->hasWildcards($eventName)) {
            $this->removeListenerPattern($eventName, $listener);

            return true;
        }

        if (! $this->hasListeners($eventName)) {
            return false;
        }

        foreach ($this->listeners[$eventName] as $priority => $listeners) {
            if (($key = array_search($listener, $listeners, true)) !== false) {
                unset($this->listeners[$eventName][$priority][$key], $this->sorted[$eventName]);

                return true;
            }
        }

        return false;
    }

    /**
     * {@inhertidoc}
     */
    public function removeAllListeners($eventName = null)
    {
        if ($eventName !== null) {
            unset($this->listeners[$eventName], $this->syncedEvents[$eventName]);
        } else {
            $this->listeners = $this->syncedEvents = [];
        }
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
        return count($this->getListeners($eventName));
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
     * If the continue is specified, this callback will be called every
     * time before the next event handler is called.
     *
     * @param array $listeners
     * @param array $arguments
     *
     * @return bool
     */
    protected function continueEmit(array $listeners, array $arguments): bool
    {
        foreach ($listeners as $listener) {
            $result = false;

            if ($listener !== null) {
                $result = $this->invoker->call($listener, $arguments);
            }

            if ($result === false) {
                return false;
            }
        }

        return true;
    }
}
