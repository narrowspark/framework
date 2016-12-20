<?php
declare(strict_types=1);
namespace Viserio\Events;

use Interop\Container\ContainerInterface;
use Viserio\Contracts\Container\Traits\ContainerAwareTrait;
use Viserio\Contracts\Events\Dispatcher as DispatcherContract;
use Viserio\Events\Traits\ValidateNameTrait;
use Viserio\Support\Traits\InvokerAwareTrait;

class Dispatcher implements DispatcherContract
{
    use ContainerAwareTrait;
    use InvokerAwareTrait;
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
     * Create a new event dispatcher instance.
     *
     * @param \Interop\Container\ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * {@inhertidoc}.
     *
     * @param string $eventName
     * @param mixed  $listener
     * @param int    $priority
     */
    public function attach(string $eventName, $listener, int $priority = 0)
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
     * {@inhertidoc}.
     *
     * @param string $eventName
     * @param mixed  $listener
     * @param int    $priority
     */
    public function once(string $eventName, $listener, int $priority = 0)
    {
        $this->validateEventName($eventName);

        $wrapper = null;
        $wrapper = function () use ($eventName, $listener, &$wrapper) {
            $this->detach($eventName, $wrapper);

            return $this->getInvoker()->call($listener, func_get_args());
        };

        $this->attach($eventName, $wrapper, $priority);
    }

    /**
     * {@inhertidoc}.
     *
     * @param string $eventName
     * @param array  $arguments
     */
    public function trigger(string $eventName, array $arguments = []): bool
    {
        $listeners = $this->getListeners($eventName);

        foreach ($listeners as $listener) {
            $result = false;

            if ($listener !== null) {
                $result = $this->getInvoker()->call($listener, $arguments);
            }

            if ($result === false) {
                return false;
            }
        }

        return true;
    }

    /**
     * {@inhertidoc}.
     *
     * @param string $eventName
     */
    public function getListeners(string $eventName): array
    {
        $this->validateEventName($eventName);

        $this->bindPatterns($eventName);

        if (!isset($this->listeners[$eventName])) {
            return [];
        }

        if (!isset($this->sorted[$eventName])) {
            $this->sortListeners($eventName);
        }

        return $this->sorted[$eventName];
    }

    /**
     * {@inhertidoc}.
     *
     * @param string $eventName
     * @param mixed  $listener
     */
    public function detach(string $eventName, $listener): bool
    {
        $this->validateEventName($eventName);

        if ($this->hasWildcards($eventName)) {
            $this->removeListenerPattern($eventName, $listener);

            return true;
        }

        if (!$this->hasListeners($eventName)) {
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
     * {@inhertidoc}.
     *
     * @param null|mixed $eventName
     */
    public function removeAllListeners($eventName = null)
    {
        if ($eventName !== null) {
            $this->validateEventName($eventName);

            unset($this->listeners[$eventName], $this->syncedEvents[$eventName]);
        } else {
            $this->listeners = $this->syncedEvents = [];
        }
    }

    /**
     * {@inhertidoc}.
     *
     * @param string $eventName
     */
    public function hasListeners(string $eventName): bool
    {
        return (bool) count($this->getListeners($eventName));
    }

    /**
     * Sort the listeners for a given event by priority.
     *
     * @param string $eventName
     */
    protected function sortListeners(string $eventName)
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
    protected function hasWildcards(string $subject): bool
    {
        return mb_strpos($subject, '*') !== false || mb_strpos($subject, '#') !== false;
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
        if (!isset($this->patterns[$eventPattern])) {
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
