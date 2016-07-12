<?php
namespace Viserio\Queue\Connectors;

use WindowsAzure\Common\ServicesBuilder;
use WindowsAzure\Queue\Models\CreateMessageOptions;
use WindowsAzure\Queue\Models\ListMessagesOptions;
use WindowsAzure\Queue\Models\PeekMessagesOptions;
use WindowsAzure\Queue\QueueRestProxy;

class AzureQueue extends AbstractQueue
{
    /**
     * The Microsoft Azure instance.
     *
     * @var \WindowsAzure\Queue\QueueRestProxy
     */
    protected $azure;

    /**
     * Create a new Microsoft Azure queue instance.
     *
     * @param \WindowsAzure\Queue\QueueRestProxy $azure
     * @param string                             $default
     */
    public function __construct(QueueRestProxy $azure, $default)
    {
        $this->azure = $azure;
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
        return $this->azure->createMessage($this->getQueue($queue), $payload);
    }

    /**
     * {@inheritdoc}
     */
    public function later($delay, $job, $data = '', string $queue = null)
    {
        $options = new CreateMessageOptions();
        $options->setVisibilityTimeoutInSeconds($this->getSeconds($delay));

        return $this->azure->createMessage(
            $this->getQueue($queue),
            $this->createPayload($job, $data),
            $options
        );
    }

    /**
     * {@inheritdoc}
     */
    public function pop(string $queue = null)
    {
        $options = new ListMessagesOptions();
        $options->setNumberOfMessages(1);

        $queue = $this->getQueue($queue);
        $listMessagesResult = $this->azure->listMessages($queue, $options);
        $messages = $listMessagesResult->getQueueMessages();

        if (count($messages)) {
            return new AzureJob($this->container, $this->azure, $queue, $messages[0]);
        }
    }

    /**
     * [peak description]
     *
     * @param string|null $queue
     *
     * @return mixed
     */
    public function peak($queue = null)
    {
        $options = new PeekMessagesOptions();
        $options->setNumberOfMessages(32);

        $queue = $this->getQueue($queue);

        $peekMessagesResult = $this->azure->peekMessages($queue, $options);
        $messages = $peekMessagesResult->getQueueMessages();

        return $messages;
    }

    /**
     * {@inheritdoc}
     */
    public function getQueue($queue): string
    {
        $queue = parent::getQueue($queue);

        $this->azure->createQueue($queue);

        return $queue;
    }

    /**
     * Get the underlying Azure instance.
     *
     * @return \WindowsAzure\Common\ServicesBuilder
     */
    public function getAzure(): ServicesBuilder
    {
        return $this->azure;
    }
}
