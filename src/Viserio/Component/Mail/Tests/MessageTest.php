<?php
declare(strict_types=1);
namespace Viserio\Component\Mail\Tests;

use Narrowspark\TestingHelper\Traits\MockeryTrait;
use PHPUnit\Framework\TestCase;
use Swift_Message;
use Swift_Mime_Message;
use Viserio\Component\Mail\Message;

class MessageTest extends TestCase
{
    use MockeryTrait;

    /**
     * @var \Swift_Mime_Message
     */
    protected $swift;

    /**
     * @var \Viserio\Component\Mail\Message
     */
    protected $message;

    public function setUp()
    {
        parent::setUp();

        $this->swift = $this->mock(Swift_Mime_Message::class);

        $this->message = new Message($this->swift);
    }

    public function testBasicAttachment()
    {
        $message = new Message(new Swift_Message());
        $message->attach(__DIR__ . '/Fixture/foo.jpg', ['mime' => 'image/jpeg', 'as' => 'bar.jpg']);

        $stringMessage = (string) $message->getSwiftMessage();

        preg_match('/Content-Type: image\/jpeg;/', $stringMessage, $image);
        preg_match('/name=bar.jpg/', $stringMessage, $name);

        self::assertSame('Content-Type: image/jpeg;', $image[0]);
        self::assertSame('name=bar.jpg', $name[0]);
    }

    public function testDataAttachment()
    {
        $message = new Message(new Swift_Message());
        $message->attachData(__DIR__ . '/Fixture/foo.jpg', 'name', ['mime' => 'image/jpeg']);

        $stringMessage = (string) $message->getSwiftMessage();

        preg_match('/Content-Type: image\/jpeg;/', $stringMessage, $image);
        preg_match('/name=name/', $stringMessage, $name);

        self::assertSame('Content-Type: image/jpeg;', $image[0]);
        self::assertSame('name=name', $name[0]);
    }

    public function testEmbed()
    {
        $message = new Message(new Swift_Message());
        $message->embed(__DIR__ . '/Fixture/foo.jpg');

        $stringMessage = (string) $message->getSwiftMessage();

        preg_match('/Content-Type: image\/jpeg;/', $stringMessage, $image);
        preg_match('/name=foo.jpg/', $stringMessage, $name);

        self::assertSame('Content-Type: image/jpeg;', $image[0]);
        self::assertSame('name=foo.jpg', $name[0]);
    }

    public function testEmbedData()
    {
        $message = new Message(new Swift_Message());
        $message->embedData(__DIR__ . '/Fixture/foo.jpg', 'name', 'image/jpeg');

        $stringMessage = (string) $message->getSwiftMessage();

        preg_match('/Content-Type: image\/jpeg;/', $stringMessage, $image);
        preg_match('/name=name/', $stringMessage, $name);

        self::assertSame('Content-Type: image/jpeg;', $image[0]);
        self::assertSame('name=name', $name[0]);
    }

    public function testFromMethod()
    {
        $this->swift->shouldReceive('setFrom')
            ->once()
            ->with('foo@bar.baz', 'Foo');

        self::assertInstanceOf(Message::class, $this->message->from('foo@bar.baz', 'Foo'));
    }

    public function testSenderMethod()
    {
        $this->swift->shouldReceive('setSender')
            ->once()
            ->with('foo@bar.baz', 'Foo');

        self::assertInstanceOf(Message::class, $this->message->sender('foo@bar.baz', 'Foo'));
    }

    public function testReturnPathMethod()
    {
        $this->swift->shouldReceive('setReturnPath')
            ->once()
            ->with('foo@bar.baz');

        self::assertInstanceOf(Message::class, $this->message->returnPath('foo@bar.baz'));
    }

    public function testToMethod()
    {
        $this->swift->shouldReceive('setTo')
            ->once()
            ->with(['foo@bar.baz', 'foobar@foobar.baz'], 'Foo');

        self::assertInstanceOf(Message::class, $this->message->to(['foo@bar.baz', 'foobar@foobar.baz'], 'Foo'));
    }

    public function testToMethodWithOverride()
    {
        $this->swift->shouldReceive('setTo')
            ->once()
            ->with('foo@bar.baz', 'Foo');

        self::assertInstanceOf(Message::class, $this->message->to('foo@bar.baz', 'Foo', true));
    }

    public function testCcMethod()
    {
        $this->swift->shouldReceive('addCc')
            ->once()
            ->with('foo@bar.baz', 'Foo');

        self::assertInstanceOf(Message::class, $this->message->cc('foo@bar.baz', 'Foo'));
    }

    public function testBccMethod()
    {
        $this->swift->shouldReceive('addBcc')
            ->once()
            ->with('foo@bar.baz', 'Foo');

        self::assertInstanceOf(Message::class, $this->message->bcc('foo@bar.baz', 'Foo'));
    }

    public function testReplyToMethod()
    {
        $this->swift->shouldReceive('addReplyTo')
            ->with('foo@bar.baz', 'Foo');
        self::assertInstanceOf(Message::class, $this->message->replyTo('foo@bar.baz', 'Foo'));
    }

    public function testSubjectMethod()
    {
        $this->swift->shouldReceive('setSubject')
            ->once()
            ->with('foo');

        self::assertInstanceOf(Message::class, $this->message->subject('foo'));
    }

    public function testPriorityMethod()
    {
        $this->swift->shouldReceive('setPriority')
            ->once()
            ->with(1);

        self::assertInstanceOf(Message::class, $this->message->priority(1));
    }

    public function testGetSwiftMessageMethod()
    {
        self::assertInstanceOf(Swift_Mime_Message::class, $this->message->getSwiftMessage());
    }
}
