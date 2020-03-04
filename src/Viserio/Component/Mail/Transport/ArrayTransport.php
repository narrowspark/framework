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
     * Retrieve the array of messages.
     */
    public function getMessages(): array
    {
        return $this->messages;
    }

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
     * Clear all of the messages from the local array.
     */
    public function reset(): void
    {
        $this->messages = [];
    }
}
