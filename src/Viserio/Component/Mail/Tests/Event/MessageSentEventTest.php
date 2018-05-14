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

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Swift_Mime_SimpleMessage;
use Viserio\Component\Mail\Event\MessageSentEvent;
use Viserio\Contract\Mail\Mailer as MailerContract;

/**
 * @internal
 *
 * @small
 */
final class MessageSentEventTest extends MockeryTestCase
{
    /** @var \Viserio\Component\Mail\Event\MessageSentEvent */
    private $event;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->event = new MessageSentEvent(
            \Mockery::mock(MailerContract::class),
            \Mockery::mock(Swift_Mime_SimpleMessage::class),
            1
        );
    }

    public function testGetMessage(): void
    {
        self::assertInstanceOf(Swift_Mime_SimpleMessage::class, $this->event->getMessage());
    }

    public function testGetRecipients(): void
    {
        self::assertSame(1, $this->event->getRecipients());
    }
}
