<?php
declare(strict_types=1);
namespace Viserio\Component\Mail\Event;

use Swift_Mime_SimpleMessage;
use Viserio\Component\Contracts\Events\Event as EventContract;
use Viserio\Component\Contracts\Mail\Mailer as MailerContract;
use Viserio\Component\Events\Traits\EventTrait;

class MessageSendingEvent implements EventContract
{
    use EventTrait;

    /**
     * Create a new message sending event.
     *
     * @param \Viserio\Component\Contracts\Mail\Mailer $mailer
     * @param \Swift_Mime_SimpleMessage                $message
     */
    public function __construct(MailerContract $mailer, Swift_Mime_SimpleMessage $message)
    {
        $this->name       = 'message.sending';
        $this->target     = $mailer;
        $this->parameters = ['message' => $message];
    }

    /**
     * Get swift message.
     *
     * @return \Swift_Mime_SimpleMessage
     */
    public function getMessage(): Swift_Mime_SimpleMessage
    {
        return $this->parameters['message'];
    }
}
