<?php
declare(strict_types=1);
namespace Viserio\Component\Queue\Jobs;

use Aws\Sqs\SqsClient;
use Psr\Container\ContainerInterface;

class SqsJob extends AbstractJob
{
    /**
     * The Amazon SQS client instance.
     *
     * @var \Aws\Sqs\SqsClient
     */
    protected $sqs;
    /**
     * The Amazon SQS job instance.
     *
     * @var array
     */
    protected $job = [];

    /**
     * Create a new job instance.
     *
     * @param \Psr\Container\ContainerInterface $container
     * @param \Aws\Sqs\SqsClient                $sqs
     * @param string                            $queue
     * @param array                             $job
     */
    public function __construct(
        ContainerInterface $container,
        SqsClient $sqs,
        string $queue,
        array $job
    ) {
        $this->sqs       = $sqs;
        $this->job       = $job;
        $this->queue     = $queue;
        $this->container = $container;
    }

    public function run()
    {
        $this->resolveAndrun(json_decode($this->getRawBody(), true));
    }

    /**
     * {@inheritdoc}
     */
    public function getRawBody(): string
    {
        return $this->job['Body'];
    }

    /**
     * {@inheritdoc}
     */
    public function delete()
    {
        parent::delete();

        $this->sqs->deleteMessage([
            'QueueUrl' => $this->queue, 'ReceiptHandle' => $this->job['ReceiptHandle'],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function release(int $delay = 0)
    {
        parent::release($delay);

        $this->sqs->changeMessageVisibility([
            'QueueUrl'          => $this->queue,
            'ReceiptHandle'     => $this->job['ReceiptHandle'],
            'VisibilityTimeout' => $delay,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function attempts(): int
    {
        return (int) $this->job['Attributes']['ApproximateReceiveCount'];
    }

    /**
     * {@inheritdoc}
     */
    public function getJobId(): string
    {
        return $this->job['MessageId'];
    }

    /**
     * Get the underlying SQS client instance.
     *
     * @return \Aws\Sqs\SqsClient
     */
    public function getSqs(): SqsClient
    {
        return $this->sqs;
    }

    /**
     * Get the underlying raw SQS job.
     *
     * @return array
     */
    public function getSqsJob(): array
    {
        return $this->job;
    }
}
