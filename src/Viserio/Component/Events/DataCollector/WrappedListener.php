<?php
declare(strict_types=1);
namespace Viserio\Component\Events\DataCollector;

use Closure;
use Symfony\Component\Stopwatch\Stopwatch;
use Symfony\Component\VarDumper\Caster\ClassStub;
use Viserio\Component\Contract\Events\Event as EventContract;
use Viserio\Component\Contract\Events\EventManager as EventManagerContract;
use Viserio\Component\Contract\Events\Traits\EventManagerAwareTrait;
use Viserio\Component\Events\Traits\EventTrait;

/**
 * Some of this code has been ported from Symfony. The original
 * code is (c) Fabien Potencier <fabien@symfony.com>.
 */
class WrappedListener
{
    use EventTrait;
    use EventManagerAwareTrait;

    /**
     * @var array|\Closure|string
     */
    private $listener;

    /**
     * @var bool
     */
    private $called = false;

    /**
     * @var \Symfony\Component\Stopwatch\Stopwatch
     */
    private $stopwatch;

    /**
     * @var string
     */
    private $pretty;

    /**
     * @var \Symfony\Component\VarDumper\Caster\ClassStub
     */
    private $stub;

    /**
     * @var bool
     */
    private static $hasClassStub;

    /**
     * WrappedListener constructor.
     *
     * @param array|\Closure|string                                 $listener
     * @param null|string                                           $name
     * @param \Symfony\Component\Stopwatch\Stopwatch                $stopwatch
     * @param null|\Viserio\Component\Contract\Events\EventManager $eventManager
     */
    public function __construct($listener, ?string $name, Stopwatch $stopwatch, EventManagerContract $eventManager = null)
    {
        $this->listener     = $listener;
        $this->stopwatch    = $stopwatch;
        $this->eventManager = $eventManager;

        $this->analyzeListener($listener);

        if ($name !== null) {
            $this->name = $name;
        }

        if (self::$hasClassStub === null) {
            self::$hasClassStub = class_exists(ClassStub::class);
        }
    }

    /**
     * @param \Viserio\Component\Contract\Events\Event $event
     *
     * @return void
     */
    public function __invoke(EventContract $event): void
    {
        $this->called = true;

        $stopWatch = $this->stopwatch->start($this->name, 'event_listener');

        call_user_func($this->listener, $event);

        if ($stopWatch->isStarted()) {
            $stopWatch->stop();
        }

        if ($event->isPropagationStopped()) {
            $this->propagationStopped = true;
        }
    }

    /**
     * @return array|Closure|string
     */
    public function getWrappedListener()
    {
        return $this->listener;
    }

    /**
     * @return bool
     */
    public function wasCalled()
    {
        return $this->called;
    }

    /**
     * @return string
     */
    public function getPretty(): string
    {
        return $this->pretty;
    }

    /**
     * @param string $eventName
     *
     * @return array
     */
    public function getInfo(string  $eventName): array
    {
        if ($this->stub === null) {
            $this->stub = self::$hasClassStub ? new ClassStub($this->pretty . '()', $this->listener) : $this->pretty . '()';
        }

        return [
            'priority' => $this->eventManager !== null ? $this->eventManager->getListenerPriority($eventName, $this->listener) : null,
            'pretty'   => $this->pretty,
            'stub'     => $this->stub,
        ];
    }

    /**
     * @param array|\Closure|string $listener
     *
     * @return void
     */
    private function analyzeListener($listener): void
    {
        if (is_array($listener)) {
            $this->name   = is_object($listener[0]) ? get_class($listener[0]) : $listener[0];
            $this->pretty = $this->name . '::' . $listener[1];
        } elseif ($listener instanceof Closure) {
            $this->pretty = $this->name = 'closure';
        } elseif (is_string($listener)) {
            $this->pretty = $this->name = $listener;
        } else {
            $this->name   = get_class($listener);
            $this->pretty = $this->name . '::__invoke';
        }
    }
}
