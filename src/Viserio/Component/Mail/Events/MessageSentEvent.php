<?php
declare(strict_types=1);
namespace Viserio\Component\Mail\Events;

use Swift_Message;
use Viserio\Component\Contracts\Events\Event as EventContract;
use Viserio\Component\Contracts\Mail\Mailer as MailerContract;
use Viserio\Component\Events\Traits\EventTrait;

class MessageSentEvent implements EventContract
{
    use EventTrait;

    /**
     * Create a new message sent event.
     *
     * @param \Viserio\Component\Contracts\Mail\Mailer $mailer
     * @param \Swift_Message                           $message
     * @param int                                      $recipients
     */
    public function __construct(MailerContract $mailer, Swift_Message $message, int $recipients)
    {
        $this->name       = 'message.sending';
        $this->target     = $mailer;
        $this->parameters = ['message' => $message, 'recipients' => $recipients];
    }

    /**
     * Get swift message.
     *
     * @return \Swift_Message
     */
    public function getMessage(): Swift_Message
    {
        return $this->parameters['message'];
    }

    /**
     * Get recipients.
     *
     * @return int
     */
    public function getRecipients(): int
    {
        return $this->parameters['recipients'];
    }
}
