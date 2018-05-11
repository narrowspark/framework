<?php
declare(strict_types=1);
namespace Viserio\Component\Mail\Tests\Event;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Swift_Mime_SimpleMessage;
use Viserio\Component\Contract\Mail\Mailer as MailerContract;
use Viserio\Component\Mail\Event\MessageSentEvent;

class MessageSentEventTest extends MockeryTestCase
{
    /**
     * @var \Viserio\Component\Mail\Event\MessageSentEvent
     */
    private $event;

    /**
     * {@inheritdoc}
     */
    public function setUp(): void
    {
        $this->event = new MessageSentEvent(
            $this->mock(MailerContract::class),
            $this->mock(Swift_Mime_SimpleMessage::class),
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
