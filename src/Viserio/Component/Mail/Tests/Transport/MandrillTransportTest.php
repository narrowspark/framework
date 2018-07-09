<?php
declare(strict_types=1);
namespace Viserio\Component\Mail\Tests\Transport;

use GuzzleHttp\Client as HttpClient;
use PHPUnit\Framework\TestCase;
use Swift_Message;
use Viserio\Component\Mail\Tests\Fixture\MandrillTransportStub;

/**
 * @internal
 */
final class MandrillTransportTest extends TestCase
{
    public function testSetAndGetKey(): void
    {
        $client = $this->getMockBuilder(HttpClient::class)
            ->getMock();
        $transport = new MandrillTransportStub($client, 'API_KEY');
        $transport->setKey('test');

        static::assertSame('test', $transport->getKey());
    }

    public function testSend(): void
    {
        $message = new Swift_Message('Foo subject', 'Bar body');
        $message->setTo('me@example.com');
        $message->setBcc('you@example.com');
        $message->setCc('cc@example.com');

        $client = $this->getMockBuilder(HttpClient::class)
            ->setMethods(['post'])
            ->getMock();

        $transport = new MandrillTransportStub($client, 'testkey');

        $client->expects(static::once())
            ->method('post')
            ->with(
                static::equalTo('https://mandrillapp.com/api/1.0/messages/send-raw.json'),
                static::equalTo([
                    'form_params' => [
                        'key'         => 'testkey',
                        'raw_message' => $message->toString(),
                        'async'       => false,
                        'to'          => ['me@example.com', 'cc@example.com', 'you@example.com'],
                    ],
                ])
            );

        $transport->send($message);
    }
}
