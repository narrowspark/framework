<?php
namespace Viserio\Queue\Jobs;

use Interop\Container\ContainerInterface;
use WindowsAzure\Queue\Models\WindowsAzureQueueMessage;
use WindowsAzure\Queue\QueueRestProxy;

class AzureJob extends AbstractJob
{
    /**
     * The Microsoft Azure client instance.
     *
     * @var WindowsAzure\Queue\QueueRestProxy
     */
    protected $azure;

    /**
     * The queue URL that the job belongs to.
     *
     * @var string
     */
    protected $queue;

    /**
     * The Microsoft Azure job instance.
     *
     * @var WindowsAzure\Queue\Models\WindowsAzureQueueMessage
     */
    protected $job;

    /**
     * Create a new job instance.
     *
     * @param \Interop\Container\ContainerInterface               $container
     * @param \WindowsAzure\Queue\QueueRestProxy                  $azure
     * @param string                                              $queue
     * @param \WindowsAzure\Queue\Models\WindowsAzureQueueMessage $job
     */
    public function __construct(
        ContainerInterface $container,
        QueueRestProxy $azure,
        string $queue,
        WindowsAzureQueueMessage $job
    ) {
        $this->container = $container;
        $this->azure = $azure;
        $this->queue = $queue;
        $this->job = $job;
    }

    /**
     * {@inheritdoc}
     */
    public function run()
    {
        $this->resolveAndRun(json_decode($this->getRawBody(), true));
    }

    /**
     * {@inheritdoc}
     */
    public function getRawBody(): string
    {
        return $this->job->getMessageText();
    }

    /**
     * {@inheritdoc}
     */
    public function delete()
    {
        parent::delete();

        $messageId = $this->job->getMessageId();
        $popReceipt = $this->job->getPopReceipt();

        $this->azure->deleteMessage($this->queue, $messageId, $popReceipt);
    }

    /**
     * {@inheritdoc}
     */
    public function release(int $delay = 0)
    {
        parent::release($delay);

        $messageId = $this->job->getMessageId();
        $popReceipt = $this->job->getPopReceipt();
        $messageText = $this->job->getMessageText();

        $this->azure->updateMessage($this->queue, $messageId, $popReceipt, $messageText, $delay);
    }

    /**
     * {@inheritdoc}
     */
    public function attempts(): int
    {
        return $this->job->getDequeueCount();
    }

    /**
     * {@inheritdoc}
     */
    public function getJobId(): string
    {
        return $this->job->getMessageId();
    }

    /**
     * Get the underlying Azure service instance.
     *
     * @return \WindowsAzure\Queue\QueueRestProxy
     */
    public function getAzure(): QueueRestProxy
    {
        return $this->azure;
    }

    /**
     * Get the underlying raw Azure job.
     *
     * @return array
     */
    public function getAzureJob(): array
    {
        return $this->job;
    }
}
