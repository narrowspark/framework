<?php
declare(strict_types=1);
namespace Viserio\Component\Queue\Connector;

use Pheanstalk\Job as PheanstalkJob;
use Pheanstalk\Pheanstalk;
use Viserio\Component\Queue\Job\BeanstalkdJob;

class BeanstalkdQueue extends AbstractQueue
{
    /**
     * The Pheanstalk instance.
     *
     * @var \Pheanstalk\Pheanstalk
     */
    protected $pheanstalk;

    /**
     * The "time to run" for all pushed jobs.
     *
     * @var int
     */
    protected $timeToRun = Pheanstalk::DEFAULT_TTR;

    /**
     * Create a new Beanstalkd queue instance.
     *
     * @param \Pheanstalk\Pheanstalk $pheanstalk
     * @param string                 $default
     * @param int                    $timeToRun
     */
    public function __construct(Pheanstalk $pheanstalk, string $default, int $timeToRun)
    {
        $this->default    = $default;
        $this->timeToRun  = $timeToRun;
        $this->pheanstalk = $pheanstalk;
    }

    /**
     * {@inheritdoc}
     */
    public function push($job, $data = '', string $queue = null)
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
            Pheanstalk::DEFAULT_PRIORITY,
            Pheanstalk::DEFAULT_DELAY,
            $this->timeToRun
        );
    }

    /**
     * {@inheritdoc}
     */
    public function later($delay, $job, $data = '', string $queue = null)
    {
        $payload = $this->createPayload($job, $data);

        $pheanstalk = $this->pheanstalk->useTube($this->getQueue($queue));

        return $pheanstalk->put(
            $payload,
            Pheanstalk::DEFAULT_PRIORITY,
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
     * Delete a message from the Beanstalk queue.
     *
     * @param string $queue
     * @param int    $id
     */
    public function deleteMessage(string $queue, int $id)
    {
        $this->pheanstalk->useTube($this->getQueue($queue))->delete($id);
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
