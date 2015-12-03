<?php
namespace Viserio\Mail\Transport;

use Psr\Log\LoggerInterface;

/**
 * Log.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.1
 */
class Log extends Transport
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
     * Send Email.
     *
     * @param \Swift_Mime_Message $message
     * @param string|null         $failedRecipients
     *
     * @return Log|null
     */
    public function send(\Swift_Mime_Message $message, &$failedRecipients = null)
    {
        $this->logger->debug($this->getMimeEntityString($message));
    }

    /**
     * Get a loggable string out of a Swiftmailer entity.
     *
     * @param \Swift_Mime_MimeEntity $entity
     *
     * @return string
     */
    protected function getMimeEntityString(\Swift_Mime_MimeEntity $entity)
    {
        $string = (string) $entity->getHeaders().PHP_EOL.$entity->getBody();

        foreach ($entity->getChildren() as $children) {
            $string .= PHP_EOL.PHP_EOL.$this->getMimeEntityString($children);
        }

        return $string;
    }
}
