<?php
declare(strict_types=1);
namespace Viserio\Component\Queue\Job;

use Pheanstalk\Job as PheanstalkJob;
use Pheanstalk\Pheanstalk;
use Psr\Container\ContainerInterface;

class BeanstalkdJob extends AbstractJob
{
    /**
     * The Pheanstalk instance.
     *
     * @var \Pheanstalk\Pheanstalk
     */
    protected $pheanstalk;

    /**
     * The Pheanstalk job instance.
     *
     * @var \Pheanstalk\Job
     */
    protected $job;

    /**
     * Create a new job instance.
     *
     * @param \Psr\Container\ContainerInterface $container
     * @param \Pheanstalk\Pheanstalk            $pheanstalk
     * @param \Pheanstalk\Job                   $job
     * @param string                            $queue
     */
    public function __construct(
        ContainerInterface $container,
        Pheanstalk $pheanstalk,
        PheanstalkJob $job,
        string $queue
    ) {
        $this->job        = $job;
        $this->queue      = $queue;
        $this->container  = $container;
        $this->pheanstalk = $pheanstalk;
    }

    /**
     * {@inheritdoc}
     */
    public function run(): void
    {
        $this->resolveAndRun(\json_decode($this->getRawBody(), true));
    }

    /**
     * {@inheritdoc}
     */
    public function getRawBody(): string
    {
        return $this->job->getData();
    }

    /**
     * {@inheritdoc}
     */
    public function delete(): void
    {
        parent::delete();

        $this->pheanstalk->delete($this->job);
    }

    /**
     * {@inheritdoc}
     */
    public function release(int $delay = 0): void
    {
        parent::release($delay);

        $priority = Pheanstalk::DEFAULT_PRIORITY;

        $this->pheanstalk->release($this->job, $priority, $delay);
    }

    /**
     * Bury the job in the queue.
     */
    public function bury(): void
    {
        $this->release();

        $this->pheanstalk->bury($this->job);
    }

    /**
     * {@inheritdoc}
     */
    public function getJobId(): string
    {
        return (string) $this->job->getId();
    }

    /**
     * {@inheritdoc}
     */
    public function attempts(): int
    {
        $stats = $this->pheanstalk->statsJob($this->job);

        return (int) $stats->reserves;
    }

    /**
     * Get the underlying Pheanstalk instance.
     *
     * @return \Pheanstalk\Pheanstalk
     */
    public function getPheanstalk(): Pheanstalk
    {
        return $this->pheanstalk;
    }

    /**
     * Get the underlying Pheanstalk job.
     *
     * @return \Pheanstalk\Job
     */
    public function getPheanstalkJob(): PheanstalkJob
    {
        return $this->job;
    }
}
