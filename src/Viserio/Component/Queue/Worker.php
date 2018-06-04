<?php
declare(strict_types=1);
namespace Viserio\Component\Queue;

use ErrorException;
use ParseError;
use Throwable;
use TypeError;
use Viserio\Component\Contract\Events\Traits\EventManagerAwareTrait;
use Viserio\Component\Contract\Queue\Exception\TimeoutException;
use Viserio\Component\Contract\Queue\FailedJobProvider as FailedJobProviderContract;
use Viserio\Component\Contract\Queue\Job as JobContract;
use Viserio\Component\Contract\Queue\QueueConnector as QueueConnectorContract;
use Viserio\Component\Contract\Queue\Worker as WorkerContract;

class Worker implements WorkerContract
{
    use EventManagerAwareTrait;

    /**
     * The queue manager instance.
     *
     * @var \Viserio\Component\Queue\QueueManager
     */
    protected $manager;

    /**
     * The failed job provider implementation.
     *
     * @var \Viserio\Component\Contract\Queue\FailedJobProvider
     */
    protected $failed;

    /**
     * Create a new queue worker.
     *
     * @param \Viserio\Component\Queue\QueueManager                    $manager
     * @param null|\Viserio\Component\Contract\Queue\FailedJobProvider $failed
     * @param null|\Viserio\Component\Contract\Events\EventManager     $events
     */
    public function __construct(
        QueueManager $manager,
        FailedJobProviderContract $failed = null
    ) {
        $this->manager = $manager;
        $this->failed  = $failed;
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
    ): void {
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
            // proper events will be
            // emited to let any listeners know this job has finished.
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
        return (\memory_get_usage() / 1024 / 1024) >= $memoryLimit;
    }

    /**
     * {@inheritdoc}
     */
    public function stop(): void
    {
        if ($this->eventManager !== null) {
            $this->eventManager->trigger('viserio.worker.stopping');
        }

        die;
    }

    /**
     * {@inheritdoc}
     */
    public function sleep(int $seconds): void
    {
        \sleep($seconds);
    }

    /**
     * Get the queue manager instance.
     *
     * @return \Viserio\Component\Queue\QueueManager
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
    ): void {
        if ($processId = \pcntl_fork()) {
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
     * @param \Viserio\Component\Contract\Queue\QueueConnector $connection
     * @param null|string                                      $queue
     *
     * @return null|\Viserio\Component\Contract\Queue\Job
     */
    protected function getNextJob(QueueConnectorContract $connection, string $queue)
    {
        if ($queue !== null) {
            return $connection->pop();
        }

        foreach (\explode(',', $queue) as $queue) {
            if (null !== ($job = $connection->pop($queue))) {
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
        if ($this->eventManager !== null) {
            return $this->eventManager->trigger('viserio.queue.looping') !== false;
        }

        return true;
    }

    /**
     * Wait for the given child process to finish.
     *
     * @param int $processId
     * @param int $timeout
     */
    protected function waitForChildProcess(int $processId, int $timeout): void
    {
        declare(ticks=1) {
            \pcntl_signal(\SIGALRM, function () use ($processId, $timeout): void {
                \posix_kill($processId, \SIGKILL);

                if ($this->exceptions) {
                    $this->exceptions->report(
                        new TimeoutException(
                            \sprintf('Queue child process timed out after %s seconds.', $timeout)
                        )
                    );
                }
            }, true);

            \pcntl_alarm($timeout);

            \pcntl_waitpid($processId, $status);

            \pcntl_alarm(0);
        }
    }

    /**
     * Log a failed job into storage.
     *
     * @param string                                $connection
     * @param \Viserio\Component\Contract\Queue\Job $job
     *
     * @return null|void
     */
    protected function logFailedJob(string $connection, JobContract $job)
    {
        if ($this->failed === null) {
            return;
        }

        $failedId = $this->failed->log($connection, $job->getQueue(), $job->getRawBody());

        $job->delete();

        $job->failed();

        if ($this->eventManager !== null) {
            $this->eventManager->trigger(
                'viserio.job.failed',
                [
                    'connection' => $connection,
                    'job'        => $job,
                    'data'       => \json_decode($job->getRawBody(), true),
                    'failedId'   => $failedId,
                ]
            );
        }
    }

    /**
     * Raise the before queue job event.
     *
     * @param string                                $connection
     * @param \Viserio\Component\Contract\Queue\Job $job
     */
    protected function raiseBeforeJobEvent(string $connection, JobContract $job): void
    {
        if ($this->eventManager !== null) {
            $this->eventManager->trigger(
                'viserio.job.processing',
                [
                    'connection' => $connection,
                    'job'        => $job,
                    'data'       => \json_decode($job->getRawBody(), true),
                ]
            );
        }
    }

    /**
     * Raise the after queue job event.
     *
     * @param string                                $connection
     * @param \Viserio\Component\Contract\Queue\Job $job
     */
    protected function raiseAfterJobEvent(string $connection, JobContract $job): void
    {
        if ($this->eventManager !== null) {
            $this->eventManager->trigger(
                'viserio.job.processed',
                [
                    'connection' => $connection,
                    'job'        => $job,
                    'data'       => \json_decode($job->getRawBody(), true),
                ]
            );
        }
    }

    /**
     * Handle an exception that occurred while the job was running.
     *
     * @param string                                $connection
     * @param \Viserio\Component\Contract\Queue\Job $job
     * @param int                                   $delay
     * @param \Throwable                            $exception
     *
     * @throws \Throwable
     */
    protected function handleJobException(string $connection, JobContract $job, int $delay, Throwable $exception): void
    {
        // If we catch an exception, we will attempt to release the job back onto the queue
        // so it is not lost entirely. This'll let the job be retried at a later time by
        // another listener (or this same one). We will re-throw this exception after.
        try {
            if ($this->eventManager !== null) {
                $this->eventManager->trigger(
                    'viserio.job.exception.occurred',
                    [
                        'connection' => $connection,
                        'job'        => $job,
                        'data'       => \json_decode($job->getRawBody(), true),
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
     * @param \ParseError|\Throwable|\TypeError $exception
     *
     * @return \ErrorException
     */
    private function getErrorException($exception): ErrorException
    {
        if ($exception instanceof ParseError) {
            $message  = 'Parse error: ' . $exception->getMessage();
            $severity = \E_PARSE;
        } elseif ($exception instanceof TypeError) {
            $message  = 'Type error: ' . $exception->getMessage();
            $severity = \E_RECOVERABLE_ERROR;
        } else {
            $message  = $exception->getMessage();
            $severity = \E_ERROR;
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
