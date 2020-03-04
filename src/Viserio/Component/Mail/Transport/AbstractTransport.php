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

use Swift_Events_EventListener;
use Swift_Events_SendEvent;
use Swift_Mime_SimpleMessage;
use Swift_Transport;

abstract class AbstractTransport implements Swift_Transport
{
    /**
     * The plug-ins registered with the transport.
     *
     * @var array
     */
    protected $plugins = [];

    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function isStarted(): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function start(): void
    {
    }

    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function stop(): void
    {
    }

    /**
     * Register a plug-in with the transport.
     */
    public function registerPlugin(Swift_Events_EventListener $plugin): void
    {
        $this->plugins[] = $plugin;
    }

    /**
     * Iterate through registered plugins and execute plugins' methods.
     */
    protected function beforeSendPerformed(Swift_Mime_SimpleMessage $message): void
    {
        $event = new Swift_Events_SendEvent($this, $message);

        foreach ($this->plugins as $plugin) {
            if (\method_exists($plugin, 'beforeSendPerformed')) {
                $plugin->beforeSendPerformed($event);
            }
        }
    }

    /**
     * Get the number of recipients.
     */
    protected function numberOfRecipients(Swift_Mime_SimpleMessage $message): int
    {
        $to = $message->getTo() ?? [];
        $cc = $message->getCc() ?? [];
        $bcc = $message->getBcc() ?? [];

        return \count(\array_merge($to, $cc, $bcc));
    }

    /**
     * Iterate through registered plugins and execute plugins' methods.
     */
    protected function sendPerformed(Swift_Mime_SimpleMessage $message): void
    {
        $event = new Swift_Events_SendEvent($this, $message);

        foreach ($this->plugins as $plugin) {
            if (\method_exists($plugin, 'sendPerformed')) {
                $plugin->sendPerformed($event);
            }
        }
    }
}
