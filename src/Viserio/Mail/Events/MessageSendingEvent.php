<?php
declare(strict_types=1);
namespace Viserio\Mail\Events;

use Viserio\Contracts\Events\Event as EventContract;
use Viserio\Events\Traits\EventTrait;
use Viserio\Contracts\Mail\Mailer as MailerContract;

class MessageSendingEvent implements EventContract
{
    use EventTrait;

    /**
     * Create a new cerebro starting event.
     *
     * @param \Viserio\Contracts\Mail\Mailer $mailer
     * @param array                          $param
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
