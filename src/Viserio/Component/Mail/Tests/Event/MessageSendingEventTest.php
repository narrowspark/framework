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
 * @coversNothing
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
