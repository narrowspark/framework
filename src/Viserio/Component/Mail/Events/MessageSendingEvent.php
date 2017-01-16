<?php
declare(strict_types=1);
namespace Viserio\Component\Mail\Events;

use Viserio\Component\Contracts\Events\Event as EventContract;
use Viserio\Component\Contracts\Mail\Mailer as MailerContract;
use Viserio\Component\Events\Traits\EventTrait;

class MessageSendingEvent implements EventContract
{
    use EventTrait;

    /**
     * Create a new cerebro starting event.
     *
     * @param \Viserio\Component\Contracts\Mail\Mailer $mailer
     * @param array                                    $param
     *
     * @codeCoverageIgnore
     */
    public function __construct(MailerContract $mailer, array $param)
    {
        $this->name       = 'message.sending';
        $this->target     = $mailer;
        $this->parameters = $param;
    }
}
