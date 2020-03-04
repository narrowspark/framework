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

namespace Viserio\Component\Mail\Tests\Fixture;

use Mockery;
use Swift_Mailer;
use Swift_Message;
use Swift_Mime_SimpleMessage;
use Swift_Transport;

class FailingSwiftMailerStub extends Swift_Mailer
{
    public function send(Swift_Mime_SimpleMessage $message, &$failedRecipients = null): int
    {
        $failedRecipients[] = 'info@narrowspark.de';

        return 1;
    }

    public function getTransport()
    {
        $transport = Mockery::mock(Swift_Transport::class);
        $transport->shouldReceive('stop');

        return $transport;
    }

    public function createMessage($service = 'message')
    {
        return new Swift_Message();
    }
}
