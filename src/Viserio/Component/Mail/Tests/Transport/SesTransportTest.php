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

use Aws\Ses\SesClient;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Swift_Message;
use Viserio\Component\Mail\Tests\Transport\Fixture\SendRawEmailMock;
use Viserio\Component\Mail\Transport\SesTransport;
use Viserio\Component\Support\Str;

/**
 * @internal
 *
 * @small
 */
final class SesTransportTest extends MockeryTestCase
{
    /** @var \Aws\Ses\SesClient|\Mockery\MockInterface */
    private $httpMock;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->httpMock = \Mockery::mock(SesClient::class);
    }

    public function testSend(): void
    {
        $message = new Swift_Message('Foo subject', 'Bar body');
        $message->setSender('myself@example.com');
        $message->setTo('me@example.com');
        $message->setBcc('you@example.com');

        $transport = new SesTransport($this->httpMock);

        $messageId = Str::random(32);
        $sendRawEmailMock = new SendRawEmailMock($messageId);

        $this->httpMock
            ->shouldReceive('sendRawEmail')
            ->with([
                'Source' => 'myself@example.com',
                'RawMessage' => ['Data' => (string) $message],
            ])
            ->andReturn($sendRawEmailMock);

        $transport->send($message);

        self::assertEquals($messageId, $message->getHeaders()->get('X-SES-Message-ID')->getFieldBody());
    }

    /**
     * {@inheritdoc}
     */
    protected function allowMockingNonExistentMethods(bool $allow = false): void
    {
        parent::allowMockingNonExistentMethods(true);
    }
}
