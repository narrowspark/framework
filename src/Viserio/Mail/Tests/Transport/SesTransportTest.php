<?php

declare(strict_types=1);
namespace Viserio\Mail\Tests\Transport;

use Aws\Ses\SesClient;
use Swift_Message;
use Viserio\Mail\Transport\Ses as SesTransport;

class SesTransportTest extends \PHPUnit_Framework_TestCase
{
    public function testSend()
    {
        $message = new Swift_Message('Foo subject', 'Bar body');
        $message->setSender('myself@example.com');
        $message->setTo('me@example.com');
        $message->setBcc('you@example.com');

        $client = $this->getMockBuilder(SesClient::class)
            ->setMethods(['sendRawEmail'])
            ->disableOriginalConstructor()
            ->getMock();

        $transport = new SesTransport($client);

        $client->expects($this->once())
            ->method('sendRawEmail')
            ->with(
                $this->equalTo([
                    'Source' => 'myself@example.com',
                    'RawMessage' => ['Data' => (string) $message],
                ])
            );

        $transport->send($message);
    }
}
