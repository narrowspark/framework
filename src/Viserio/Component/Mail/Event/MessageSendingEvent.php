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
     */
    public function __construct(MailerContract $mailer, Swift_Mime_SimpleMessage $message)
    {
        $this->name = 'message.sending';
        $this->target = $mailer;
        $this->parameters = ['message' => $message];
    }

    /**
     * Get swift message.
     */
    public function getMessage(): Swift_Mime_SimpleMessage
    {
        return $this->parameters['message'];
    }
}
