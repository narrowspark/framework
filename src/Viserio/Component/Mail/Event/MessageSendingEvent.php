<?php

declare(strict_types=1);

/**
 * This file is part of Narrowspark Framework.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Viserio\Component\Mail\Event;

use Swift_Mime_SimpleMessage;
use Viserio\Component\Events\Traits\EventTrait;
use Viserio\Contract\Events\Event as EventContract;
use Viserio\Contract\Mail\Mailer as MailerContract;

class MessageSendingEvent implements EventContract
{
    use EventTrait;

    /**
     * Create a new message sending event.
     *
     * @param \Viserio\Contract\Mail\Mailer $mailer
     * @param Swift_Mime_SimpleMessage      $message
     */
    public function __construct(MailerContract $mailer, Swift_Mime_SimpleMessage $message)
    {
        $this->name = 'message.sending';
        $this->target = $mailer;
        $this->parameters = ['message' => $message];
    }

    /**
     * Get swift message.
     *
     * @return Swift_Mime_SimpleMessage
     */
    public function getMessage(): Swift_Mime_SimpleMessage
    {
        return $this->parameters['message'];
    }
}
