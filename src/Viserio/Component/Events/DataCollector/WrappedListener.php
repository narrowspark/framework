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

namespace Viserio\Component\Events\DataCollector;

use Closure;
use Symfony\Component\Stopwatch\Stopwatch;
use Symfony\Component\VarDumper\Caster\ClassStub;
use Viserio\Component\Events\Traits\EventTrait;
use Viserio\Contract\Events\Event as EventContract;

/**
 * Some of this code has been ported from Symfony. The original
 * code is (c) Fabien Potencier <fabien@symfony.com>.
 */
class WrappedListener
{
    use EventTrait;

    /**
     * Event manager instance.
     *
     * @var null|\Viserio\Component\Events\DataCollector\TraceableEventManager
     */
    protected $eventManager;

    /**
     * Given event listener.
     *
     * @var array|Closure|string
     */
    private $listener;

    /**
     * Is event listener called?
     *
     * @var bool
     */
    private $called = false;

    /**
     * A Stopwatch instance.
     *
     * @var \Symfony\Component\Stopwatch\Stopwatch
     */
    private $stopwatch;

    /**
     * A pretty sting info about the listener.
     *
     * @var string
     */
    private $pretty;

    /** @var \Symfony\Component\VarDumper\Caster\ClassStub */
    private $stub;

    /**
     * Returns true if ClassStub exists or false if not.
     *
     * @var bool
     */
    private static $hasClassStub;

    /**
     * Create a new WrappedListener instance.
     *
     * @param array|Closure|string                                               $listener
     * @param null|\Viserio\Component\Events\DataCollector\TraceableEventManager $eventManager
     */
    public function __construct(
        $listener,
        ?string $name,
        Stopwatch $stopwatch,
        ?TraceableEventManager $eventManager = null
    ) {
        $this->listener = $listener;
        $this->stopwatch = $stopwatch;
        $this->eventManager = $eventManager;

        $this->analyzeListener($listener);

        if ($name !== null) {
            $this->name = $name;
        }

        if (self::$hasClassStub === null) {
            self::$hasClassStub = \class_exists(ClassStub::class);
        }
    }

    public function __invoke(EventContract $event): void
    {
        $this->called = true;

        $stopWatch = $this->stopwatch->start($this->name, 'event_listener');

        \call_user_func($this->listener, $event);

        if ($stopWatch->isStarted()) {
            $stopWatch->stop();
        }

        if ($event->isPropagationStopped()) {
            $this->propagationStopped = true;
        }
    }

    /**
     * Get a pretty info string about the called event.
     */
    public function getPretty(): string
    {
        return $this->pretty;
    }

    /**
     * Get the original listener.
     *
     * @return array|Closure|string
     */
    public function getWrappedListener()
    {
        return $this->listener;
    }

    /**
     * Was the event called?
     */
    public function wasCalled(): bool
    {
        return $this->called;
    }

    /**
     * Get information's about given event.
     */
    public function getInfo(string $eventName): array
    {
        if ($this->stub === null) {
            $this->stub = self::$hasClassStub ? new ClassStub($this->pretty . '()', $this->listener) : $this->pretty . '()';
        }

        return [
            'priority' => $this->eventManager !== null ? $this->eventManager->getListenerPriority($eventName, $this->listener) : null,
            'pretty' => $this->pretty,
            'stub' => $this->stub,
        ];
    }

    /**
     * @param array|Closure|string $listener
     */
    private function analyzeListener($listener): void
    {
        if (\is_array($listener)) {
            $this->name = \is_object($listener[0]) ? \get_class($listener[0]) : $listener[0];
            $this->pretty = $this->name . '::' . $listener[1];
        } elseif ($listener instanceof Closure) {
            $this->pretty = $this->name = 'closure';
        } elseif (\is_string($listener)) {
            $this->pretty = $this->name = $listener;
        } else {
            $this->name = \get_class($listener);
            $this->pretty = $this->name . '::__invoke';
        }
    }
}
