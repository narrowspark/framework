<?php
namespace Viserio\Events;

use Viserio\Contracts\Events\Dispatcher as DispatcherContract;
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
     * The wildcard listeners.
     *
     * @var array
     */
    protected $wildcards = [];

    /**
     * The sorted event listeners.
     *
     * @var array
     */
    protected $sorted = [];

    /**
     * {@inhertidoc}
     */
    public function on(string $eventName, $listener, int $priority = 100)
    {
        if ($this->hasWildcards($eventName)) {
        } else {
        }
    }

    /**
     * {@inhertidoc}
     */
    public function once(string $eventName, $listener, int $priority = 100)
    {
    }

    /**
     * {@inhertidoc}
     */
    public function emit(string $eventName, array $arguments = [], callable $continueCallback = null): bool
    {
    }

    /**
     * {@inhertidoc}
     */
    public function getListeners(string $eventName): array
    {
    }

    /**
     * {@inhertidoc}
     */
    public function off(string $eventName, callable $listener): bool
    {
    }

    /**
     * {@inhertidoc}
     */
    public function removeAllListeners($eventName = null)
    {
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
        if (isset($this->wildcards[$eventName])) {
            return;
        }

        foreach ($this->patterns as $eventPattern => $patterns) {
            foreach ($patterns as $pattern) {
                if ($pattern->test($eventName)) {
                    $pattern->bind($this->dispatcher, $eventName);
                }
            }
        }

        $this->wildcards[$eventName] = true;
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

        foreach ($this->wildcards as $eventName => $value) {
            if ($pattern->test($eventName)) {
                unset($this->wildcards[$eventName]);
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
     * @param string   $eventPattern
     * @param callback $listener
     */
    protected function removeListenerPattern(string $eventPattern, callback $listener)
    {
        if (!isset($this->patterns[$eventPattern])) {
            return;
        }

        foreach ($this->patterns[$eventPattern] as $key => $pattern) {
            if ($listener == $pattern->getListener()) {
                $pattern->unbind($this->dispatcher);

                unset($this->patterns[$eventPattern][$key]);
            }
        }
    }
}
