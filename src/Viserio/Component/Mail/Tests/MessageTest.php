<?php
declare(strict_types=1);
namespace Viserio\Component\Mail\Tests;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Swift_Message;
use Swift_Mime_SimpleMessage;
use Viserio\Component\Mail\Message;

class MessageTest extends MockeryTestCase
{
    /**
     * @var \Swift_Mime_SimpleMessage
     */
    protected $swift;

    /**
     * @var \Viserio\Component\Mail\Message
     */
    protected $message;

    /**
     * {@inheritdoc}
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->swift = $this->mock(Swift_Mime_SimpleMessage::class);

        $this->message = new Message($this->swift);
    }

    public function testBasicAttachment(): void
    {
        $message = new Message(new Swift_Message());
        $message->attach(__DIR__ . '/Fixture/foo.jpg', ['mime' => 'image/jpeg', 'as' => 'bar.jpg']);

        $stringMessage = (string) $message->getSwiftMessage();

        \preg_match('/Content-Type: image\/jpeg;/', $stringMessage, $image);
        \preg_match('/name=bar.jpg/', $stringMessage, $name);

        self::assertSame('Content-Type: image/jpeg;', $image[0]);
        self::assertSame('name=bar.jpg', $name[0]);
    }

    public function testDataAttachment(): void
    {
        $message = new Message(new Swift_Message());
        $message->attachData(__DIR__ . '/Fixture/foo.jpg', 'name', ['mime' => 'image/jpeg']);

        $stringMessage = (string) $message->getSwiftMessage();

        \preg_match('/Content-Type: image\/jpeg;/', $stringMessage, $image);
        \preg_match('/name=name/', $stringMessage, $name);

        self::assertSame('Content-Type: image/jpeg;', $image[0]);
        self::assertSame('name=name', $name[0]);
    }

    public function testEmbed(): void
    {
        $message = new Message(new Swift_Message());
        $message->embed(__DIR__ . '/Fixture/foo.jpg');

        $stringMessage = (string) $message->getSwiftMessage();

        \preg_match('/Content-Type: image\/jpeg;/', $stringMessage, $image);
        \preg_match('/name=foo.jpg/', $stringMessage, $name);

        self::assertSame('Content-Type: image/jpeg;', $image[0]);
        self::assertSame('name=foo.jpg', $name[0]);
    }

    public function testEmbedData(): void
    {
        $message = new Message(new Swift_Message());
        $message->embedData(__DIR__ . '/Fixture/foo.jpg', 'name', 'image/jpeg');

        $stringMessage = (string) $message->getSwiftMessage();

        \preg_match('/Content-Type: image\/jpeg;/', $stringMessage, $image);
        \preg_match('/name=name/', $stringMessage, $name);

        self::assertSame('Content-Type: image/jpeg;', $image[0]);
        self::assertSame('name=name', $name[0]);
    }

    public function testFromMethod(): void
    {
        $this->swift->shouldReceive('setFrom')
            ->once()
            ->with('foo@bar.baz', 'Foo');

        self::assertInstanceOf(Message::class, $this->message->from('foo@bar.baz', 'Foo'));
    }

    public function testSenderMethod(): void
    {
        $this->swift->shouldReceive('setSender')
            ->once()
            ->with('foo@bar.baz', 'Foo');

        self::assertInstanceOf(Message::class, $this->message->sender('foo@bar.baz', 'Foo'));
    }

    public function testReturnPathMethod(): void
    {
        $this->swift->shouldReceive('setReturnPath')
            ->once()
            ->with('foo@bar.baz');

        self::assertInstanceOf(Message::class, $this->message->returnPath('foo@bar.baz'));
    }

    public function testToMethod(): void
    {
        $this->swift->shouldReceive('setTo')
            ->once()
            ->with(['foo@bar.baz', 'foobar@foobar.baz'], 'Foo');

        self::assertInstanceOf(Message::class, $this->message->to(['foo@bar.baz', 'foobar@foobar.baz'], 'Foo'));
    }

    public function testToMethodWithOverride(): void
    {
        $this->swift->shouldReceive('setTo')
            ->once()
            ->with('foo@bar.baz', 'Foo');

        self::assertInstanceOf(Message::class, $this->message->to('foo@bar.baz', 'Foo', true));
    }

    public function testCcMethod(): void
    {
        $this->swift->shouldReceive('addCc')
            ->once()
            ->with('foo@bar.baz', 'Foo');

        self::assertInstanceOf(Message::class, $this->message->cc('foo@bar.baz', 'Foo'));

        $this->swift->shouldReceive('setCc')
            ->once()
            ->with('foo@bar.baz', 'Foo');

        self::assertInstanceOf(Message::class, $this->message->cc('foo@bar.baz', 'Foo', true));
    }

    public function testBccMethod(): void
    {
        $this->swift->shouldReceive('addBcc')
            ->once()
            ->with('foo@bar.baz', 'Foo');

        self::assertInstanceOf(Message::class, $this->message->bcc('foo@bar.baz', 'Foo'));

        $this->swift->shouldReceive('setBcc')
            ->once()
            ->with('foo@bar.baz', 'Foo');

        self::assertInstanceOf(Message::class, $this->message->bcc('foo@bar.baz', 'Foo', true));
    }

    public function testReplyToMethod(): void
    {
        $this->swift->shouldReceive('addReplyTo')
            ->with('foo@bar.baz', 'Foo');
        self::assertInstanceOf(Message::class, $this->message->replyTo('foo@bar.baz', 'Foo'));
    }

    public function testSubjectMethod(): void
    {
        $this->swift->shouldReceive('setSubject')
            ->once()
            ->with('foo');

        self::assertInstanceOf(Message::class, $this->message->subject('foo'));
    }

    public function testPriorityMethod(): void
    {
        $this->swift->shouldReceive('setPriority')
            ->once()
            ->with(1);

        self::assertInstanceOf(Message::class, $this->message->priority(1));
    }

    public function testGetSwiftMessageMethod(): void
    {
        self::assertInstanceOf(Swift_Mime_SimpleMessage::class, $this->message->getSwiftMessage());
    }
}
