<?php

declare(strict_types=1);

/**
 * This file is part of Narrowspark Framework.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Viserio\Component\Events\DataCollector;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;
use SplObjectStorage;
use Symfony\Component\Stopwatch\Stopwatch;
use Throwable;
use Viserio\Component\Events\Event;
use Viserio\Component\Events\EventManager;
use Viserio\Contract\Events\Event as EventContract;
use Viserio\Contract\Events\EventManager as EventManagerContract;
use Viserio\Contract\Events\Traits\EventManagerAwareTrait;

/**
 * Some of this code has been ported from Symfony. The original
 * code is (c) Fabien Potencier <fabien@symfony.com>.
 *
 * @method array getListeners(string $eventName = null)
 */
class TraceableEventManager implements EventManagerContract, LoggerAwareInterface
{
    use LoggerAwareTrait;
    use EventManagerAwareTrait;

    /**
     * List of called listeners.
     *
     * @var array
     */
    private $called = [];

    /**
     * List of wrapped listeners.
     *
     * @var array
     */
    private $wrappedListeners = [];

    /**
     * List of orphaned listeners.
     *
     * @var array
     */
    private $orphanedEvents = [];

    /** @var null|\Symfony\Component\Stopwatch\Stopwatch */
    private $stopwatch;

    /**
     * Create a new TraceableEventManager instance.
     *
     * @param null|\Viserio\Component\Events\EventManager $eventManager
     * @param \Symfony\Component\Stopwatch\Stopwatch      $stopwatch
     *
     * @throws \Viserio\Contract\Events\Exception\RuntimeException
     */
    public function __construct(EventManager $eventManager, Stopwatch $stopwatch)
    {
        $this->eventManager = $eventManager;
        $this->stopwatch = $stopwatch;
        $this->logger = new NullLogger();
    }

    /**
     * Proxy all method calls to the original event manager.
     *
     * @param string $method
     * @param array  $parameters
     *
     * @return mixed
     */
    public function __call(string $method, array $parameters)
    {
        return $this->eventManager->{$method}(...$parameters);
    }

    /**
     * Gets the orphaned events.
     *
     * @return array An array of orphaned events
     */
    public function getOrphanedEvents(): array
    {
        return $this->orphanedEvents;
    }

    /**
     * {@inheritdoc}
     */
    public function attach(string $eventName, $listener, int $priority = 0): void
    {
        $this->eventManager->attach($eventName, $listener, $priority);
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
            $this->logger->debug(\sprintf('The [%s] event is already stopped. No listeners have been called.', $event->getName()));

            return false;
        }

        $this->preProcess($event->getName());

        $stopWatch = $this->stopwatch->start($event->getName(), 'section');

        $return = $this->eventManager->trigger($event, $target, $argv);

        if ($stopWatch->isStarted()) {
            $stopWatch->stop();
        }

        $this->postProcess($event->getName());

        return $return;
    }

    /**
     * {@inheritdoc}
     */
    public function detach(string $eventName, $listener): bool
    {
        if (isset($this->wrappedListeners[$eventName])) {
            foreach ($this->wrappedListeners[$eventName] as $index => $wrappedListener) {
                /** @var WrappedListener $wrappedListener */
                if ($wrappedListener->getWrappedListener() === $listener) {
                    $listener = $wrappedListener;
                    unset($this->wrappedListeners[$eventName][$index]);

                    break;
                }
            }
        }

        return $this->eventManager->detach($eventName, $listener);
    }

    /**
     * {@inheritdoc}
     */
    public function clearListeners(string $eventName): void
    {
        $this->eventManager->clearListeners($eventName);
    }

    /**
     * Gets the called listeners.
     *
     * @return array An array of called listeners
     */
    public function getCalledListeners(): array
    {
        $called = [];

        foreach ($this->called as $eventName => $listeners) {
            foreach ($listeners as $listener) {
                $called[$eventName][] = $listener->getInfo($eventName);
            }
        }

        return $called;
    }

    /**
     * Gets the not called listeners.
     *
     * @return array An array of not called listeners
     */
    public function getNotCalledListeners(): array
    {
        try {
            $allListeners = $this->eventManager->getListeners();
        } catch (Throwable $e) {
            $this->logger->info('An exception was thrown while getting the uncalled listeners.', ['exception' => $e]);

            // unable to retrieve the uncalled listeners
            return [];
        }

        $notCalled = [];

        foreach ($allListeners as $eventName => $listeners) {
            foreach ($listeners as $listener) {
                $called = false;

                if (isset($this->called[$eventName])) {
                    foreach ($this->called[$eventName] as $calledListener) {
                        /** @var WrappedListener $calledListener */
                        if ($calledListener->getWrappedListener() === $listener) {
                            $called = true;

                            break;
                        }
                    }
                }

                if (! $called) {
                    if (! $listener instanceof WrappedListener) {
                        $listener = new WrappedListener($listener, null, $this->stopwatch, $this);
                    }

                    $notCalled[$eventName][] = $listener->getInfo($eventName);
                }
            }
        }

        \uasort($notCalled, [$this, 'sortListenersByPriority']);

        return $notCalled;
    }

    /**
     * Resets this to its initial state.
     *
     * @return void
     */
    public function reset(): void
    {
        $this->called = $this->orphanedEvents = [];
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
        // we might have wrapped listeners for the event (if called while dispatching)
        // in that case get the priority by wrapper
        if (isset($this->wrappedListeners[$eventName])) {
            /** @var \Viserio\Component\Events\DataCollector\WrappedListener $wrappedListener */
            foreach ($this->wrappedListeners[$eventName] as $index => $wrappedListener) {
                if ($wrappedListener->getWrappedListener() === $listener) {
                    return $this->eventManager->getListenerPriority($eventName, $wrappedListener);
                }
            }
        }

        return $this->eventManager->getListenerPriority($eventName, $listener);
    }

    /**
     * @param string $eventName
     *
     * @return void
     */
    private function preProcess(string $eventName): void
    {
        if (\count($this->eventManager->getListeners($eventName)) === 0) {
            $this->orphanedEvents[] = $eventName;
        }

        foreach ($this->eventManager->getListeners($eventName) as $listener) {
            $priority = $this->getListenerPriority($eventName, $listener);
            $wrappedListener = new WrappedListener($listener, null, $this->stopwatch, $this);

            $this->wrappedListeners[$eventName][] = $wrappedListener;

            $this->eventManager->detach($eventName, $listener);
            $this->eventManager->attach($eventName, $wrappedListener, $priority);
        }
    }

    /**
     * @param string $eventName
     */
    private function postProcess(string $eventName): void
    {
        unset($this->wrappedListeners[$eventName]);

        $skipped = false;

        foreach ($this->eventManager->getListeners($eventName) as $listener) {
            if (! $listener instanceof WrappedListener) {
                continue;
            }

            // Unwrap listener
            $priority = $this->getListenerPriority($eventName, $listener);

            $this->eventManager->detach($eventName, $listener);
            $this->eventManager->attach($eventName, $listener->getWrappedListener(), $priority);

            $context = ['event' => $eventName, 'listener' => $listener->getPretty()];

            if ($listener->wasCalled()) {
                $this->logger->debug('Notified event "{event}" to listener "{listener}".', $context);

                if (! isset($this->called[$eventName])) {
                    $this->called[$eventName] = new SplObjectStorage();
                }

                $this->called[$eventName]->attach($listener);
            }

            if ($skipped) {
                $this->logger->debug('Listener "{listener}" was not called for event "{event}".', $context);
            }

            if ($listener->isPropagationStopped()) {
                $this->logger->debug('Listener "{listener}" stopped propagation of the event "{event}".', $context);

                $skipped = true;
            }
        }
    }

    /**
     * @param array $a
     * @param array $b
     *
     * @return int
     */
    private function sortListenersByPriority(array $a, array $b): int
    {
        if (\is_int($a['priority']) && ! \is_int($b['priority'])) {
            return 1;
        }

        if (! \is_int($a['priority']) && \is_int($b['priority'])) {
            return -1;
        }

        if ($a['priority'] === $b['priority']) {
            return 0;
        }

        if ($a['priority'] > $b['priority']) {
            return -1;
        }

        return 1;
    }
}
