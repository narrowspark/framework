<?php
declare(strict_types=1);
namespace Viserio\Component\Mail\Tests\Transport;

use GuzzleHttp\Client as HttpClient;
use PHPUnit\Framework\TestCase;
use Swift_Message;
use Viserio\Component\Mail\Transport\MailgunTransport;

/**
 * @internal
 */
final class MailgunTest extends TestCase
{
    public function testSetAndGetDomain(): void
    {
        $client = $this->getMockBuilder(HttpClient::class)
            ->getMock();
        $transport = new MailgunTransport($client, 'API_KEY', 'narrowspark');
        $transport->setDomain('anolilab.com');

        $this->assertSame('anolilab.com', $transport->getDomain());
    }

    public function testSetAndGetKey(): void
    {
        $client = $this->getMockBuilder(HttpClient::class)
            ->getMock();
        $transport = new MailgunTransport($client, 'API_KEY', 'narrowspark');
        $transport->setKey('test');

        $this->assertSame('test', $transport->getKey());
    }

    public function testSend(): void
    {
        $message = new Swift_Message('Foo subject', 'Bar body');
        $message->setSender('myself@example.com');
        $message->setTo('me@example.com');
        $message->setBcc('you@example.com');

        $client = $this->getMockBuilder(HttpClient::class)
            ->setMethods(['post'])
            ->getMock();

        $transport = new MailgunTransport($client, 'API_KEY', 'narrowspark');

        $message2 = clone $message;
        $message2->setBcc([]);

        $client->expects($this->once())
            ->method('post')
            ->with(
                $this->equalTo(
                    'https://api.mailgun.net/v3/narrowspark/messages.mime'
                ),
                $this->equalTo(
                    [
                        'auth' => [
                            'api',
                            'API_KEY',
                        ],
                        'multipart' => [
                            ['name' => 'to', 'contents' => 'me@example.com'],
                            ['name' => 'cc', 'contents' => ''],
                            ['name' => 'bcc', 'contents' => 'you@example.com'],
                            ['name' => 'message', 'contents' => $message->toString(), 'filename' => 'message.mime'],
                        ],
                    ]
                )
            );

        $transport->send($message);
    }
}
