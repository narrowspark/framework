<?php
declare(strict_types=1);
namespace Viserio\Mail\Tests\Fixture;

use Mockery;
use Swift_Mailer;
use Swift_Mime_Message;
use Swift_Transport;

class FailingSwiftMailerStub extends Swift_Mailer
{
    public function send(Swift_Mime_Message $message, &$failedRecipients = null)
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
}
