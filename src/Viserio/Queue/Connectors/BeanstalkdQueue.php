<?php
namespace Viserio\Queue\Connectors;

use Narrowspark\Arr\StaticArr as Arr;
use Pheanstalk\Job as PheanstalkJob;
use Pheanstalk\PheanstalkInterface;

class BeanstalkdQueue extends AbstractQueue
{
    /**
     * The Pheanstalk instance.
     *
     * @var \Pheanstalk\PheanstalkInterface
     */
    protected $pheanstalk;

    /**
     * The "time to run" for all pushed jobs.
     *
     * @var int
     */
    protected $timeToRun;

    /**
     * Create a new Beanstalkd queue instance.
     *
     * @param \Pheanstalk\PheanstalkInterface $pheanstalk
     * @param string                          $default
     * @param int                             $timeToRun
     */
    public function __construct(PheanstalkInterface $pheanstalk, string $default, int $timeToRun)
    {
        $this->default = $default;
        $this->timeToRun = $timeToRun;
        $this->pheanstalk = $pheanstalk;
    }

    /**
     * {@inheritdoc}
     */
    public function push(string $job, $data = '', string $queue = null)
    {
        return $this->pushRaw($this->createPayload($job, $data), $queue);
    }

    /**
     * {@inheritdoc}
     */
    public function pushRaw(string $payload, string $queue = null, array $options = [])
    {
        return $this->pheanstalk->useTube($this->getQueue($queue))->put(
            $payload,
            PheanstalkInterface::DEFAULT_PRIORITY,
            PheanstalkInterface::DEFAULT_DELAY,
            $this->timeToRun
        );
    }

    /**
     * {@inheritdoc}
     */
    public function later($delay, string $job, $data = '', string $queue = null)
    {
        $payload = $this->createPayload($job, $data);

        $pheanstalk = $this->pheanstalk->useTube($this->getQueue($queue));

        return $pheanstalk->put(
            $payload,
            PheanstalkInterface::DEFAULT_PRIORITY,
            $this->getSeconds($delay),
            $this->timeToRun
        );
    }

    /**
     * {@inheritdoc}
     */
    public function pop(string $queue = null)
    {
        $queue = $this->getQueue($queue);

        $job = $this->pheanstalk->watchOnly($queue)->reserve(0);

        if ($job instanceof PheanstalkJob) {
            return new BeanstalkdJob($this->container, $this->pheanstalk, $job, $queue);
        }
    }

    /**
     * Get the underlying Pheanstalk instance.
     *
     * @return \Pheanstalk\Pheanstalk
     */
    public function getPheanstalk()
    {
        return $this->pheanstalk;
    }
}
