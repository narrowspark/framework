<?php

declare(strict_types=1);

/**
 * This file is part of Narrowspark Framework.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Viserio\Component\Mail\Tests\Transport;

use GuzzleHttp\Client as HttpClient;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Swift_Message;
use Viserio\Component\Mail\Tests\Fixture\MandrillTransportStub;

/**
 * @internal
 *
 * @small
 */
final class MandrillTransportTest extends MockeryTestCase
{
    /** @var \GuzzleHttp\Client|\Mockery\MockInterface */
    private $httpMock;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->httpMock = \Mockery::mock(HttpClient::class);
    }

    public function testSetAndGetKey(): void
    {
        $transport = new MandrillTransportStub($this->httpMock, 'API_KEY');
        $transport->setKey('test');

        self::assertSame('test', $transport->getKey());
    }

    public function testSend(): void
    {
        $message = new Swift_Message('Foo subject', 'Bar body');
        $message->setTo('me@example.com');
        $message->setBcc('you@example.com');
        $message->setCc('cc@example.com');

        $this->httpMock
            ->shouldReceive('post')
            ->with(
                'https://mandrillapp.com/api/1.0/messages/send-raw.json',
                [
                    'form_params' => [
                        'key' => 'testkey',
                        'raw_message' => $message->toString(),
                        'async' => false,
                        'to' => ['me@example.com', 'cc@example.com', 'you@example.com'],
                    ],
                ]
            );

        $transport = new MandrillTransportStub($this->httpMock, 'testkey');

        $transport->send($message);
    }

    /**
     * {@inheritdoc}
     */
    protected function allowMockingNonExistentMethods(bool $allow = false): void
    {
        parent::allowMockingNonExistentMethods(true);
    }
}
