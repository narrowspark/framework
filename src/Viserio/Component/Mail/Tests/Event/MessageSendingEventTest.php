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

namespace Viserio\Component\Mail\Tests\Event;

use Mockery;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Swift_Mime_SimpleMessage;
use Viserio\Component\Mail\Event\MessageSendingEvent;
use Viserio\Contract\Mail\Mailer as MailerContract;

/**
 * @internal
 *
 * @small
 */
final class MessageSendingEventTest extends MockeryTestCase
{
    /** @var \Viserio\Component\Mail\Event\MessageSendingEvent */
    private $event;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->event = new MessageSendingEvent(
            Mockery::mock(MailerContract::class),
            Mockery::mock(Swift_Mime_SimpleMessage::class)
        );
    }

    public function testGetMessage(): void
    {
        self::assertInstanceOf(Swift_Mime_SimpleMessage::class, $this->event->getMessage());
    }
}
