<?php
declare(strict_types=1);
namespace Viserio\Component\Queue\Connector;

use Aws\Sqs\SqsClient;
use Viserio\Component\Queue\Job\SqsJob;

class SqsQueue extends AbstractQueue
{
    /**
     * The Amazon SQS instance.
     *
     * @var \Aws\Sqs\SqsClient
     */
    protected $sqs;

    /**
     * The sqs prefix url.
     *
     * @var string
     */
    protected $prefix;

    /**
     * The job creator callback.
     *
     * @var null|callable
     */
    protected $jobCreator;

    /**
     * Create a new Amazon SQS queue instance.
     *
     * @param \Aws\Sqs\SqsClient $sqs
     * @param string             $default
     * @param string             $prefix
     */
    public function __construct(SqsClient $sqs, $default, $prefix = '')
    {
        $this->sqs     = $sqs;
        $this->prefix  = $prefix;
        $this->default = $default;
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
        $response = $this->sqs->sendMessage(['QueueUrl' => $this->getQueue($queue), 'MessageBody' => $payload]);

        return $response->get('MessageId');
    }

    /**
     * {@inheritdoc}
     */
    public function later($delay, $job, $data = '', string $queue = null)
    {
        $payload = $this->createPayload($job, $data);

        $delay = $this->getSeconds($delay);

        return $this->sqs->sendMessage([
            'QueueUrl' => $this->getQueue($queue), 'MessageBody' => $payload, 'DelaySeconds' => $delay,
        ])->get('MessageId');
    }

    /**
     * {@inheritdoc}
     */
    public function pop(string $queue = null)
    {
        $queue = $this->getQueue($queue);

        $response = $this->sqs->receiveMessage(
            ['QueueUrl' => $queue, 'AttributeNames' => ['ApproximateReceiveCount']]
        );

        if (\count($response['Messages']) > 0) {
            if ($this->jobCreator) {
                return \call_user_func($this->jobCreator, $this->container, $this->sqs, $queue, $response);
            }

            return new SqsJob($this->container, $this->sqs, $queue, $response['Messages'][0]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getQueue($queue): string
    {
        $queue = parent::getQueue($queue);

        if (\filter_var($queue, FILTER_VALIDATE_URL) !== false) {
            return $queue;
        }

        return \rtrim($this->prefix, '/') . '/' . $queue;
    }

    /**
     * Define the job creator callback for the connection.
     *
     * @param callable $callback
     *
     * @return $this
     */
    public function createJobsUsing(callable $callback): SqsQueue
    {
        $this->jobCreator = $callback;

        return $this;
    }

    /**
     * Get the underlying SQS instance.
     *
     * @return \Aws\Sqs\SqsClient
     */
    public function getSqs(): SqsClient
    {
        return $this->sqs;
    }
}
