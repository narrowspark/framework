<?php
declare(strict_types=1);
namespace Viserio\Mail\Tests\Transport;

use Aws\Ses\SesClient;
use Swift_Message;
use Viserio\Mail\Tests\Transport\Fixture\SendRawEmailMock;
use Viserio\Mail\Transport\Ses as SesTransport;
use Viserio\Support\Str;

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

        $messageId = Str::random(32);
        $sendRawEmailMock = new SendRawEmailMock($messageId);

        $client->expects($this->once())
            ->method('sendRawEmail')
            ->with(
                $this->equalTo([
                    'Source' => 'myself@example.com',
                    'RawMessage' => ['Data' => (string) $message],
                ])
            )->willReturn($sendRawEmailMock);

        $transport->send($message);

        self::assertEquals($messageId, $message->getHeaders()->get('X-SES-Message-ID')->getFieldBody());
    }
}
