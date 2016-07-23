<?php

declare(strict_types=1);
namespace Viserio\Queue\Connectors;

use Throwable;
use Viserio\Contracts\Queue\Job as JobContract;
use Viserio\Exception\Exception\FatalThrowableError;
use Viserio\Queue\Jobs\SyncJob;

class SyncQueue extends AbstractQueue
{
    /**
     * {@inheritdoc}
     */
    public function push($job, $data = '', string $queue = null)
    {
        $queueJob = new SyncJob(
            $this->container,
            $this->createPayload($job, $data, $queue)
        );

        try {
            $this->raiseBeforeJobEvent($queueJob);

            $queueJob->run();

            $this->raiseAfterJobEvent($queueJob);
        } catch (Throwable $exception) {
            $this->raiseExceptionOccurredJobEvent($queueJob, new FatalThrowableError($exception));
            $this->handleFailedJob($queueJob);

            throw $exception;
        }

        return 0;
    }

    /**
     * {@inheritdoc}
     */
    public function pushRaw(string $payload, string $queue = null, array $options = [])
    {
        //
    }

    /**
     * {@inheritdoc}
     */
    public function later($delay, $job, $data = '', string $queue = null)
    {
        return $this->push($job, $data, $queue);
    }

    /**
     * {@inheritdoc}
     */
    public function pop(string $queue = null)
    {
        //
    }

    /**
     * Raise the before queue job event.
     *
     * @param \Viserio\Contracts\Queue\Job $job
     */
    protected function raiseBeforeJobEvent(JobContract $job)
    {
        if ($this->container->has('events')) {
            $this->container->get('events')->emit(
                'viserio.job.processing',
                [
                    'connection' => 'sync',
                    'job' => $job,
                    'data' => json_decode($job->getRawBody(), true),
                ]
            );
        }
    }

    /**
     * Raise the after queue job event.
     *
     * @param \Viserio\Contracts\Queue\Job $job
     */
    protected function raiseAfterJobEvent(JobContract $job)
    {
        if ($this->container->has('events')) {
            $this->container->get('events')->emit(
                'viserio.job.processed',
                [
                    'connection' => 'sync',
                    'job' => $job,
                    'data' => json_decode($job->getRawBody(), true),
                ]
            );
        }
    }

    /**
     * Raise the exception occurred queue job event.
     *
     * @param \Viserio\Contracts\Queue\Job $job
     * @param \Throwable                   $exception
     */
    protected function raiseExceptionOccurredJobEvent(JobContract $job, Throwable $exception)
    {
        if ($this->container->has('events')) {
            $this->container->get('events')->emit(
                'viserio.job.exception.occurred',
                [
                    'connection' => 'sync',
                    'job' => $job,
                    'data' => json_decode($job->getRawBody(), true),
                    'exception' => $exception,
                ]
            );
        }
    }

    /**
     * Handle the failed job.
     *
     * @param \Viserio\Contracts\Queue\Job $job
     */
    protected function handleFailedJob(JobContract $job)
    {
        $job->failed();

        if ($this->container->has('events')) {
            $this->container->get('events')->emit(
                'viserio.job.failed',
                [
                    'connection' => 'sync',
                    'job' => $job,
                    'data' => json_decode($job->getRawBody(), true),
                ]);
        }
    }
}
