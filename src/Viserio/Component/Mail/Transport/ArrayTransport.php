<?php
declare(strict_types=1);
namespace Viserio\Component\Mail\Transport;

use Swift_Mime_SimpleMessage;

class ArrayTransport extends AbstractTransport
{
    /**
     * The array of Swift Messages.
     *
     * @var array
     */
    protected $messages = [];

    /**
     * {@inheritdoc}
     */
    public function send(Swift_Mime_SimpleMessage $message, &$failedRecipients = null): int
    {
        $this->beforeSendPerformed($message);

        $this->messages[] = $message;

        return $this->numberOfRecipients($message);
    }

    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function ping(): bool
    {
        return true;
    }

    /**
     * Retrieve the array of messages.
     *
     * @return array
     */
    public function getMessages(): array
    {
        return $this->messages;
    }

    /**
     * Clear all of the messages from the local array.
     *
     * @return void
     */
    public function reset(): void
    {
        $this->messages = [];
    }
}
