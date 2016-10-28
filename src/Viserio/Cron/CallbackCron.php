<?php
declare(strict_types=1);
namespace Viserio\Cron;

use InvalidArgumentException;
use LogicException;
use Viserio\Contracts\Cron\Cron as CronContract;

class CallbackCron extends Cron
{
    /**
     * The callback to call.
     *
     * @var string
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
     * @param string|callable $callback
     * @param array           $parameters
     *
     * @throws \InvalidArgumentException
     */
    public function __construct($callback, array $parameters = [])
    {
        if (! is_string($callback) && ! is_callable($callback)) {
            throw new InvalidArgumentException(
                'Invalid scheduled callback cron job. Must be string or callable.'
            );
        }

        $this->callback = $callback;
        $this->parameters = $parameters;
    }

    /**
     * Run the given cron job.
     *
     * @return mixed
     */
    public function run()
    {
        if ($this->description) {
            touch($this->mutexPath());
        }

        try {
            $response = $this->getInvoker()->call($this->callback, $this->parameters);
        } finally {
            if ($this->description) {
                @unlink($this->getMutexPath());
            }
        }

        $this->callAfterCallbacks();

        return $response;
    }

    /**
     * Do not allow the cron job to overlap each other.
     *
     * @throws \LogicException
     *
     * @return $this
     */
    public function withoutOverlapping(): CronContract
    {
        if (! isset($this->description)) {
            throw new LogicException("A scheduled cron job name is required to prevent overlapping. Use the 'name' method before 'withoutOverlapping'.");
        }

        return $this->skip(function () {
            return file_exists($this->getMutexPath());
        });
    }

    /**
     * Get the summary of the event for display.
     *
     * @return string
     */
    public function getSummaryForDisplay(): string
    {
        if (is_string($this->description)) {
            return $this->description;
        }

        return is_string($this->callback) ? $this->callback : 'Closure';
    }

    /**
     * Get the mutex path for the scheduled command.
     *
     * @return string
     */
    protected function getMutexPath(): string
    {
        return $this->mutexPath . DIRECTORY_SEPARATOR . 'schedule-' . sha1($this->description);
    }
}
