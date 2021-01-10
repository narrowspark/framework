<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018-2020 Daniel Bannert
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/narrowspark/automatic
 */

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
     *
     * @codeCoverageIgnore
     */
    public function ping(): bool
    {
        return true;
    }

    /**
     * Get a loggable string out of a Swiftmailer entity.
     */
    protected function getMimeEntityString(Swift_Message $entity): string
    {
        $string = (string) $entity->getHeaders() . "\n" . $entity->getBody();

        foreach ($entity->getChildren() as $children) {
            $string .= "\n\n" . $this->getMimeEntityString($children);
        }

        return $string;
    }
}
