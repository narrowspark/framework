<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018-2020 Daniel Bannert
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/narrowspark/automatic
 */

namespace Viserio\Component\Mail\Tests\Transport;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Psr7\Response;
use Mockery;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Swift_Message;
use Viserio\Component\Mail\Transport\SparkPostTransport;

/**
 * @internal
 *
 * @small
 * @coversNothing
 */
final class SparkPostTest extends MockeryTestCase
{
    /** @var \GuzzleHttp\Client|\Mockery\MockInterface */
    private $httpMock;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->httpMock = Mockery::mock(HttpClient::class);
    }

    public function testSetAndGetKey(): void
    {
        $transport = new SparkPostTransport($this->httpMock, 'API_KEY');
        $transport->setKey('test');

        self::assertSame('test', $transport->getKey());
    }

    public function testSetAndGetOptions(): void
    {
        $transport = new SparkPostTransport($this->httpMock, 'API_KEY');
        $transport->setOptions(['key' => 'test']);

        self::assertSame(['key' => 'test'], $transport->getOptions());
    }

    public function testSend(): void
    {
        $this->arrangeSend('https://api.sparkpost.com/api/v1/transmissions');
    }

    public function testSendWithSparkEu(): void
    {
        $this->arrangeSend('https://api.eu.sparkpost.com/api/v1/transmissions');
    }

    /**
     * {@inheritdoc}
     */
    protected function allowMockingNonExistentMethods(bool $allow = false): void
    {
        parent::allowMockingNonExistentMethods(true);
    }

    private function arrangeSend($endpoint): void
    {
        $message = new Swift_Message('Foo subject', 'Bar body');
        $message->setSender('myself@example.com');
        $message->setTo('me@example.com');
        $message->setBcc('you@example.com');

        $transport = new SparkPostTransport($this->httpMock, 'SPARKPOST_API_KEY', [], $endpoint);

        $message2 = clone $message;
        $message2->setBcc([]);

        $this->arrangeClientPost($this->httpMock, $message2, $endpoint);

        $transport->send($message);
    }

    /**
     * @param \GuzzleHttp\Client|\Mockery\MockInterface $client
     */
    private function arrangeClientPost($client, Swift_Message $message2, string $endpoint): void
    {
        $client->shouldReceive('post')
            ->with(
                $endpoint,
                [
                    'headers' => [
                        'Authorization' => 'SPARKPOST_API_KEY',
                    ],
                    'json' => [
                        'recipients' => [
                            [
                                'address' => [
                                    'name' => null,
                                    'email' => 'me@example.com',
                                ],
                            ],
                            [
                                'address' => [
                                    'name' => null,
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
            ->andReturn(new Response());
    }
}
