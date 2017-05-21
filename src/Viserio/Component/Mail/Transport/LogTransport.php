<?php
declare(strict_types=1);
namespace Viserio\Component\Mail\Transport;

use Psr\Log\LoggerInterface;
use Swift_Message;
use Swift_Mime_SimpleMessage;

class LogTransport extends AbstractTransport
{
    /**
     * The Logger instance.
     *
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * Create a new log transport instance.
     *
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function send(Swift_Mime_SimpleMessage $message, &$failedRecipients = null): int
    {
        $this->beforeSendPerformed($message);

        $this->logger->debug($this->getMimeEntityString($message));

        $this->sendPerformed($message);

        return $this->numberOfRecipients($message);
    }

    /**
     * {@inheritdoc}
     */
    public function ping(): bool
    {
        return true;
    }

    /**
     * Get a loggable string out of a Swiftmailer entity.
     *
     * @param \Swift_Message $entity
     *
     * @return string
     */
    protected function getMimeEntityString(Swift_Message $entity): string
    {
        $string = (string) $entity->getHeaders() . PHP_EOL . $entity->getBody();

        foreach ($entity->getChildren() as $children) {
            $string .= PHP_EOL . PHP_EOL . $this->getMimeEntityString($children);
        }

        return $string;
    }
}
