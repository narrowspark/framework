<?php
declare(strict_types=1);
namespace Viserio\Component\Mail\Tests\Transport;

use Aws\Ses\SesClient;
use PHPUnit\Framework\TestCase;
use Swift_Message;
use Viserio\Component\Mail\Tests\Transport\Fixture\SendRawEmailMock;
use Viserio\Component\Mail\Transport\SesTransport;
use Viserio\Component\Support\Str;

/**
 * @internal
 */
final class SesTransportTest extends TestCase
{
    public function testSend(): void
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

        $messageId        = Str::random(32);
        $sendRawEmailMock = new SendRawEmailMock($messageId);

        $client->expects(static::once())
            ->method('sendRawEmail')
            ->with(
                static::equalTo([
                    'Source'     => 'myself@example.com',
                    'RawMessage' => ['Data' => (string) $message],
                ])
            )->willReturn($sendRawEmailMock);

        $transport->send($message);

        static::assertEquals($messageId, $message->getHeaders()->get('X-SES-Message-ID')->getFieldBody());
    }
}
