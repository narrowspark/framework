<?php
declare(strict_types=1);
namespace Viserio\Component\Cron;

use Psr\Log\NullLogger;
use Viserio\Component\Contract\Cron\Cron as CronContract;
use Viserio\Component\Contract\Cron\Exception\InvalidArgumentException;
use Viserio\Component\Contract\Cron\Exception\LogicException;

class CallbackCron extends Cron
{
    /**
     * The callback to call.
     *
     * @var callable|string
     */
    protected $callback;

    /**
     * The parameters to pass to the method.
     *
     * @var array
     */
    protected $parameters;

    /**
     * Create a new callback cron instance.
     *
     * @param callable|string $callback
     * @param array           $parameters
     *
     * @throws \Viserio\Component\Contract\Cron\Exception\InvalidArgumentException
     */
    public function __construct($callback, array $parameters = [])
    {
        if (! \is_string($callback) && ! \is_callable($callback)) {
            throw new InvalidArgumentException(
                'Invalid scheduled callback cron job. Must be string or callable.'
            );
        }

        parent::__construct('');

        $this->callback   = $callback;
        $this->parameters = $parameters;
        $this->logger     = new NullLogger();
    }

    /**
     * Run the given cron job.
     *
     * @return mixed
     */
    public function run()
    {
        if ($this->description) {
            $item = $this->cachePool->getItem($this->getMutexName());
            $item->set($this->getMutexName());
            $item->expiresAfter(1440);

            $this->cachePool->save($item);
        }

        $this->callBeforeCallbacks();

        try {
            $response = $this->getInvoker()->call($this->callback, $this->parameters);
        } finally {
            if ($this->description) {
                $this->cachePool->deleteItem($this->getMutexName());
            }
        }

        $this->callAfterCallbacks();

        return $response;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Viserio\Component\Contract\Cron\Exception\LogicException
     */
    public function withoutOverlapping(int $expiresAt = 1440): CronContract
    {
        if ($this->description === null) {
            throw new LogicException(
                'A scheduled cron job description is required to prevent overlapping. ' .
                "Use the 'setDescription' method before 'withoutOverlapping'."
            );
        }

        return parent::withoutOverlapping($expiresAt);
    }

    /**
     * Get the summary of the event for display.
     *
     * @return string
     */
    public function getSummaryForDisplay(): string
    {
        if (\is_string($this->description)) {
            return $this->description;
        }

        return \is_string($this->callback) ? $this->callback : 'Closure';
    }

    /**
     * Get the mutex name for the scheduled command.
     *
     * @return string
     */
    protected function getMutexName(): string
    {
        return 'schedule-mutex-' . \sha1($this->expression . $this->description);
    }
}
