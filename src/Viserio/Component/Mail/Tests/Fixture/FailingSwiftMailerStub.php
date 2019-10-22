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
