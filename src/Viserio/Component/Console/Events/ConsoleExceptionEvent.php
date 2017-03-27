<?php
declare(strict_types=1);
namespace Viserio\Component\Console\Events;

use Throwable;
use Viserio\Component\Contracts\Console\Application as ApplicationContract;
use Viserio\Component\Contracts\Events\Event as EventContract;
use Viserio\Component\Events\Traits\EventTrait;

class ConsoleExceptionEvent implements EventContract
{
    use EventTrait;

    /**
     * A Exception instance.
     *
     * @var \Throwable|null
     */
    private $exception;

    /**
     * Create a new command exception event.
     *
     * @param \Viserio\Component\Contracts\Console\Application $application
     * @param array                                            $params
     *
     * @codeCoverageIgnore
     */
    public function __construct(ApplicationContract $application, array $params)
    {
        $this->name       = 'command.exception';
        $this->target     = $application;
        $this->parameters = $params;
    }

    /**
     * Returns the thrown exception.
     *
     * @return \Throwable The thrown exception
     */
    public function getException(): Throwable
    {
        return $this->exception ?? $this->parameters['exception'];
    }

    /**
     * Replaces the thrown exception.
     *
     * This exception will be thrown if no response is set in the event.
     *
     * @param \Throwable $exception The thrown exception
     *
     * @return void
     */
    public function setException(Throwable $exception): void
    {
        $this->exception = $exception;
    }

    /**
     * Gets the exit code.
     *
     * @return int The command exit code
     */
    public function getExitCode(): int
    {
        return $this->parameters['exit_code'];
    }
}
