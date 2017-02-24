<?php
declare(strict_types=1);
namespace Viserio\Component\Mail\Transport;

use Swift_Events_EventListener;
use Swift_Events_SendEvent;
use Swift_Mime_Message;
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
    public function start()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function stop()
    {
        return true;
    }

    /**
     * Register a plug-in with the transport.
     *
     * @param \Swift_Events_EventListener $plugin
     *
     * @codeCoverageIgnore
     */
    public function registerPlugin(Swift_Events_EventListener $plugin)
    {
        array_push($this->plugins, $plugin);
    }

    /**
     * Iterate through registered plugins and execute plugins' methods.
     *
     * @param \Swift_Mime_Message $message
     *
     * @return void
     */
    protected function beforeSendPerformed(Swift_Mime_Message $message): void
    {
        $event = new Swift_Events_SendEvent($this, $message);

        foreach ($this->plugins as $plugin) {
            if (method_exists($plugin, 'beforeSendPerformed')) {
                $plugin->beforeSendPerformed($event);
            }
        }
    }

    /**
     * Get the number of recipients.
     *
     * @param \Swift_Mime_Message $message
     *
     * @return int
     */
    protected function numberOfRecipients(Swift_Mime_Message $message): int
    {
        $to  = is_null($message->getTo()) ? [] : $message->getTo();
        $cc  = is_null($message->getCc()) ? [] : $message->getCc();
        $bcc = is_null($message->getBcc()) ? [] : $message->getBcc();

        return count(array_merge($to, $cc, $bcc));
    }

    /**
     * Iterate through registered plugins and execute plugins' methods.
     *
     * @param \Swift_Mime_Message $message
     *
     * @return void
     */
    protected function sendPerformed(Swift_Mime_Message $message): void
    {
        $event = new Swift_Events_SendEvent($this, $message);

        foreach ($this->plugins as $plugin) {
            if (method_exists($plugin, 'sendPerformed')) {
                $plugin->sendPerformed($event);
            }
        }
    }
}
