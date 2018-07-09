<?php
declare(strict_types=1);
namespace Viserio\Component\Mail\Tests\Transport;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Swift_Message;
use Viserio\Component\Mail\Transport\SparkPostTransport;

/**
 * @internal
 */
final class SparkPostTest extends TestCase
{
    public function testSetAndGetKey(): void
    {
        $client = $this->getMockBuilder(HttpClient::class)
            ->getMock();
        $transport = new SparkPostTransport($client, 'API_KEY');
        $transport->setKey('test');

        static::assertSame('test', $transport->getKey());
    }

    public function testSetAndGetOptions(): void
    {
        $client = $this->getMockBuilder(HttpClient::class)
            ->getMock();
        $transport = new SparkPostTransport($client, 'API_KEY');
        $transport->setOptions(['key' => 'test']);

        static::assertSame(['key' => 'test'], $transport->getOptions());
    }

    public function testSend(): void
    {
        $this->arrangeSend('https://api.sparkpost.com/api/v1/transmissions');
    }

    public function testSendWithSparkEu(): void
    {
        $this->arrangeSend('https://api.eu.sparkpost.com/api/v1/transmissions');
    }

    private function arrangeSend($endpoint): void
    {
        $message = new Swift_Message('Foo subject', 'Bar body');
        $message->setSender('myself@example.com');
        $message->setTo('me@example.com');
        $message->setBcc('you@example.com');

        $client = $this->getMockBuilder(HttpClient::class)
            ->setMethods(['post'])
            ->getMock();

        $transport = new SparkPostTransport($client, 'SPARKPOST_API_KEY', [], $endpoint);

        $message2 = clone $message;
        $message2->setBcc([]);

        $this->arrangeClientPost($client, $message2, $endpoint);

        $transport->send($message);
    }

    /**
     * @param \PHPUnit\Framework\MockObject\MockObject $client
     * @param \Swift_Message                           $message2
     * @param string                                   $endpoint
     */
    private function arrangeClientPost(MockObject $client, Swift_Message $message2, string $endpoint): void
    {
        $client->expects(static::once())
            ->method('post')
            ->with(
                static::equalTo($endpoint),
                static::equalTo(
                    [
                        'headers' => [
                            'Authorization' => 'SPARKPOST_API_KEY',
                        ],
                        'json' => [
                            'recipients' => [
                                [
                                    'address' => [
                                        'name'  => null,
                                        'email' => 'me@example.com',
                                    ],
                                ],
                                [
                                    'address' => [
                                        'name'  => null,
                                        'email' => 'you@example.com',
                                    ],
                                ],
                            ],
                            'content' => [
                                'email_rfc822' => (string) $message2,
                            ],
                        ],
                    ]
                )
            )
            ->willReturn(new Response());
    }
}
