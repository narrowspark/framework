<?php
declare(strict_types=1);
namespace Viserio\Component\Queue\Jobs;

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
        $this->payload   = $payload;
    }

    /**
     * {@inheritdoc}
     */
    public function run()
    {
        $this->resolveAndRun(json_decode($this->payload, true));
    }

    /**
     * {@inheritdoc}
     */
    public function getRawBody(): string
    {
        return $this->payload;
    }

    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function attempts(): int
    {
        return 1;
    }

    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function getJobId(): string
    {
        return '';
    }
}
