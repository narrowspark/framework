<?php
declare(strict_types=1);
namespace Viserio\Component\Mail\Tests\Fixture;

use Mockery;
use Swift_Mailer;
use Swift_Mime_Message;
use Swift_Transport;
use Swift_Message;

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

    public function createMessage($service = 'message')
    {
        return new Swift_Message();
    }
}
