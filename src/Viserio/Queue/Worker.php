<?php
declare(strict_types=1);
namespace Viserio\Queue;

use ErrorException;
use ParseError;
use Throwable;
use TypeError;
use Viserio\Contracts\Events\EventManager as EventManagerContract;
use Viserio\Contracts\Events\Traits\EventsAwareTrait;
use Viserio\Contracts\Exception\Handler as ExceptionHandlerContract;
use Viserio\Contracts\Queue\Exception\TimeoutException;
use Viserio\Contracts\Queue\FailedJobProvider as FailedJobProviderContract;
use Viserio\Contracts\Queue\Job as JobContract;
use Viserio\Contracts\Queue\QueueConnector as QueueConnectorContract;
use Viserio\Contracts\Queue\Worker as WorkerContract;

class Worker implements WorkerContract
{
    use EventsAwareTrait;

    /**
     * The queue manager instance.
     *
     * @var \Viserio\Queue\QueueManager
     */
    protected $manager;

    /**
     * The failed job provider implementation.
     *
     * @var \Viserio\Contracts\Queue\FailedJobProvider
     */
    protected $failed;

    /**
     * The event dispatcher instance.
     *
     * @var \Viserio\Contracts\Events\EventManager
     */
    protected $events;

    /**
     * The exception handler instance.
     *
     * @var \Viserio\Contracts\Exception\Handler
     */
    protected $exceptions;

    /**
     * Create a new queue worker.
     *
     * @param \Viserio\Queue\QueueManager                     $manager
     * @param \Viserio\Contracts\Queue\FailedJobProvider|null $failed
     * @param \Viserio\Contracts\Events\EventManager|null     $events
     */
    public function __construct(
        QueueManager $manager,
        FailedJobProviderContract $failed = null,
        EventManagerContract $events = null
    ) {
        $this->manager = $manager;
        $this->failed  = $failed;
        $this->events  = $events;
    }

    /**
     * {@inheritdoc}
     */
    public function daemon(
        string $connectionName,
        string $queue = null,
        int $delay = 0,
        int $memory = 128,
        int $timeout = 60,
        int $sleep = 3,
        int $maxTries = 0
    ) {
        while (true) {
            if ($this->daemonShouldRun()) {
                $this->runNextJobForDaemon(
                    $connectionName,
                    $queue,
                    $delay,
                    $timeout,
                    $sleep,
                    $maxTries
                );
            } else {
                $this->sleep($sleep);
            }

            if ($this->memoryExceeded($memory)) {
                $this->stop();
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function runNextJob(
        string $connectionName,
        string $queue = null,
        int $delay = 0,
        int $sleep = 3,
        int $maxTries = 0
    ) {
        try {
            $connection = $this->manager->connection($connectionName);

            $job = $this->getNextJob($connection, $queue);

            if ($job !== null) {
                return $this->process(
                    $connectionName,
                    $job,
                    $maxTries,
                    $delay
                );
            }
        } catch (Throwable $exception) {
            if ($this->exceptions) {
                $this->exceptions->report($this->getErrorException($exception));
            }
        }

        $this->sleep($sleep);
    }

    /**
     * {@inheritdoc}
     */
    public function process(string $connection, JobContract $job, int $maxTries = 0, int $delay = 0)
    {
        if ($maxTries > 0 && $job->attempts() > $maxTries) {
            return $this->logFailedJob($connection, $job);
        }

        try {
            $this->raiseBeforeJobEvent($connection, $job);

            // Here we will run off the job and let it process. We will catch any exceptions so
            // they can be reported to the developers logs, etc. Once the job is finished the
            // proper events will be emited to let any listeners know this job has finished.
            $job->run();

            $this->raiseAfterJobEvent($connection, $job);
        } catch (Throwable $e) {
            $this->handleJobException($connection, $job, $delay, $e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function memoryExceeded(int $memoryLimit): bool
    {
        return (memory_get_usage() / 1024 / 1024) >= $memoryLimit;
    }

    /**
     * {@inheritdoc}
     */
    public function stop()
    {
        if ($this->events !== null) {
            $this->events->trigger('viserio.worker.stopping');
        }

        die;
    }

    /**
     * {@inheritdoc}
     */
    public function sleep(int $seconds)
    {
        sleep($seconds);
    }

    /**
     * {@inheritdoc}
     */
    public function setExceptionHandler(ExceptionHandlerContract $handler): WorkerContract
    {
        $this->exceptions = $handler;

        return $this;
    }

    /**
     * Get the queue manager instance.
     *
     * @return \Viserio\Queue\QueueManager
     */
    public function getManager(): QueueManager
    {
        return $this->manager;
    }

    /**
     * Run the next job for the daemon worker.
     *
     * @param string $connectionName
     * @param string $queue
     * @param int    $delay
     * @param int    $timeout
     * @param int    $sleep
     * @param int    $maxTries
     */
    protected function runNextJobForDaemon(
        $connectionName,
        $queue,
        $delay,
        $timeout,
        $sleep,
        $maxTries
    ) {
        if ($processId = pcntl_fork()) {
            $this->waitForChildProcess($processId, $timeout);
        } else {
            try {
                $this->runNextJob($connectionName, $queue, $delay, $sleep, $maxTries);
            } catch (Throwable $exception) {
                if ($this->exceptions) {
                    $this->exceptions->report($this->getErrorException($exception));
                }
            } finally {
                exit;
            }
        }
    }

    /**
     * Get the next job from the queue connection.
     *
     * @param \Viserio\Contracts\Queue\QueueConnector $connection
     * @param string|null                             $queue
     *
     * @return \Viserio\Contracts\Queue\Job|null
     */
    protected function getNextJob(QueueConnectorContract $connection, string $queue)
    {
        if ($queue !== null) {
            return $connection->pop();
        }

        foreach (explode(',', $queue) as $queue) {
            if (! is_null($job = $connection->pop($queue))) {
                return $job;
            }
        }
    }

    /**
     * Determine if the daemon should process on this iteration.
     *
     * @return bool
     */
    protected function daemonShouldRun(): bool
    {
        if ($this->events !== null) {
            return $this->events->trigger('viserio.queue.looping') !== false;
        }

        return true;
    }

    /**
     * Wait for the given child process to finish.
     *
     * @param int $processId
     * @param int $timeout
     */
    protected function waitForChildProcess(int $processId, int $timeout)
    {
        declare(ticks=1) {
            pcntl_signal(SIGALRM, function () use ($processId, $timeout) {
                posix_kill($processId, SIGKILL);

                if ($this->exceptions) {
                    $this->exceptions->report(
                        new TimeoutException(
                            sprintf('Queue child process timed out after %s seconds.', $timeout)
                        )
                    );
                }
            }, true);

            pcntl_alarm($timeout);

            pcntl_waitpid($processId, $status);

            pcntl_alarm(0);
        }
    }

    /**
     * Log a failed job into storage.
     *
     * @param string                       $connection
     * @param \Viserio\Contracts\Queue\Job $job
     *
     * @return void|null
     */
    protected function logFailedJob(string $connection, JobContract $job)
    {
        if ($this->failed === null) {
            return;
        }

        $failedId = $this->failed->log($connection, $job->getQueue(), $job->getRawBody());

        $job->delete();

        $job->failed();

        if ($this->events !== null) {
            $this->events->trigger(
                'viserio.job.failed',
                [
                    'connection' => $connection,
                    'job'        => $job,
                    'data'       => json_decode($job->getRawBody(), true),
                    'failedId'   => $failedId,
                ]
            );
        }
    }

    /**
     * Raise the before queue job event.
     *
     * @param string                       $connection
     * @param \Viserio\Contracts\Queue\Job $job
     */
    protected function raiseBeforeJobEvent(string $connection, JobContract $job)
    {
        if ($this->events !== null) {
            $this->events->trigger(
                'viserio.job.processing',
                [
                    'connection' => $connection,
                    'job'        => $job,
                    'data'       => json_decode($job->getRawBody(), true),
                ]
            );
        }
    }

    /**
     * Raise the after queue job event.
     *
     * @param string                       $connection
     * @param \Viserio\Contracts\Queue\Job $job
     */
    protected function raiseAfterJobEvent(string $connection, JobContract $job)
    {
        if ($this->events !== null) {
            $this->events->trigger(
                'viserio.job.processed',
                [
                    'connection' => $connection,
                    'job'        => $job,
                    'data'       => json_decode($job->getRawBody(), true),
                ]
            );
        }
    }

    /**
     * Handle an exception that occurred while the job was running.
     *
     * @param string                       $connection
     * @param \Viserio\Contracts\Queue\Job $job
     * @param int                          $delay
     * @param \Throwable                   $exception
     *
     * @throws \Throwable
     */
    protected function handleJobException(string $connection, JobContract $job, int $delay, Throwable $exception)
    {
        // If we catch an exception, we will attempt to release the job back onto the queue
        // so it is not lost entirely. This'll let the job be retried at a later time by
        // another listener (or this same one). We will re-throw this exception after.
        try {
            if ($this->events !== null) {
                $this->events->trigger(
                    'viserio.job.exception.occurred',
                    [
                        'connection' => $connection,
                        'job'        => $job,
                        'data'       => json_decode($job->getRawBody(), true),
                        'exception'  => $exception,
                    ]
                );
            }
        } finally {
            if (! $job->isDeleted()) {
                $job->release($delay);
            }
        }

        throw $exception;
    }

    /**
     * Get a ErrorException instance.
     *
     * @param \ParseError|\TypeError|\Throwable $exception
     *
     * @return \ErrorException
     */
    private function getErrorException($exception): ErrorException
    {
        if ($exception instanceof ParseError) {
            $message  = 'Parse error: ' . $exception->getMessage();
            $severity = E_PARSE;
        } elseif ($exception instanceof TypeError) {
            $message  = 'Type error: ' . $exception->getMessage();
            $severity = E_RECOVERABLE_ERROR;
        } else {
            $message  = $exception->getMessage();
            $severity = E_ERROR;
        }

        return new ErrorException(
            $message,
            $exception->getCode(),
            $severity,
            $exception->getFile(),
            $exception->getLine()
        );
    }
}
