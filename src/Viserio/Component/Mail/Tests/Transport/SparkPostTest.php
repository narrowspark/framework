<?php
declare(strict_types=1);
namespace Viserio\Component\Mail\Tests\Transport;

use GuzzleHttp\Client as HttpClient;
use PHPUnit\Framework\TestCase;
use Swift_Message;
use Viserio\Component\Mail\Transport\SparkPost as SparkPostTransport;

class SparkPostTest extends TestCase
{
    public function testSend()
    {
        $message = new Swift_Message('Foo subject', 'Bar body');
        $message->setSender('myself@example.com');
        $message->setTo('me@example.com');
        $message->setBcc('you@example.com');

        $client = $this->getMockBuilder(HttpClient::class)
            ->setMethods(['post'])
            ->getMock();

        $transport = new SparkPostTransport($client, 'SPARKPOST_API_KEY');

        $message2 = clone $message;
        $message2->setBcc([]);

        $client->expects($this->once())
            ->method('post')
            ->with(
                $this->equalTo(
                    'https://api.sparkpost.com/api/v1/transmissions'
                ),
                $this->equalTo(
                    [
                        'headers' => [
                            'Authorization' => 'SPARKPOST_API_KEY',
                        ],
                        'json' => [
                            'recipients' => [
                                [
                                    'address' => 'me@example.com',
                                ],
                                [
                                    'address' => 'you@example.com',
                                ],
                            ],
                            'content' => [
                                'email_rfc822' => (string) $message2,
                            ],
                        ],
                    ]
                )
            );

        $transport->send($message);
    }
}
