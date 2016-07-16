<?php
namespace Viserio\Queue\Jobs;

use Interop\Container\ContainerInterface;

class SyncJob extends AbstractJob
{
    /**
     * The class name of the job.
     *
     * @var string
     */
    protected $job;

    /**
     * The queue message data.
     *
     * @var string
     */
    protected $payload;

    /**
     * Create a new job instance.
     *
     * @param \Interop\Container\ContainerInterface $container
     * @param string                                $payload
     */
    public function __construct(ContainerInterface $container, $payload)
    {
        $this->container = $container;
        $this->payload = $payload;
    }

    /**
     * Fire the job.
     *
     * @return void
     */
    public function run()
    {
        $this->resolveAndRun(json_decode($this->payload, true));
    }

    /**
     * Get the raw body string for the job.
     *
     * @return string
     */
    public function getRawBody(): string
    {
        return $this->payload;
    }

    /**
     * Get the number of times the job has been attempted.
     *
     * @return int
     */
    public function attempts(): int
    {
        return 1;
    }

    /**
     * Get the job identifier.
     *
     * @return string
     */
    public function getJobId(): string
    {
        return '';
    }
}
