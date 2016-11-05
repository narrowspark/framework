<?php
declare(strict_types=1);
namespace Viserio\Mail\Tests;

use Narrowspark\TestingHelper\Traits\MockeryTrait;
use Swift_Attachment;
use Swift_Message;
use Swift_Mime_Message;
use Viserio\Mail\Message;

class MessageTest extends \PHPUnit_Framework_TestCase
{
    use MockeryTrait;

    /**
     * @var \Swift_Mime_Message
     */
    protected $swift;

    /**
     * @var \Viserio\Mail\Message
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

        $this->assertSame('Content-Type: image/jpeg;', $image[0]);
        $this->assertSame('name=bar.jpg', $name[0]);
    }

    public function testDataAttachment()
    {
        $message = new Message(new Swift_Message());
        $message->attachData(__DIR__ . '/Fixture/foo.jpg', 'name', ['mime' => 'image/jpeg']);

        $stringMessage = (string) $message->getSwiftMessage();

        preg_match('/Content-Type: image\/jpeg;/', $stringMessage, $image);
        preg_match('/name=name/', $stringMessage, $name);

        $this->assertSame('Content-Type: image/jpeg;', $image[0]);
        $this->assertSame('name=name', $name[0]);
    }

    public function testEmbed()
    {
        $message = new Message(new Swift_Message());
        $message->embed(__DIR__ . '/Fixture/foo.jpg');

        $stringMessage = (string) $message->getSwiftMessage();

        preg_match('/Content-Type: image\/jpeg;/', $stringMessage, $image);
        preg_match('/name=foo.jpg/', $stringMessage, $name);

        $this->assertSame('Content-Type: image/jpeg;', $image[0]);
        $this->assertSame('name=foo.jpg', $name[0]);
    }

    public function testEmbedData()
    {
        $message = new Message(new Swift_Message());
        $message->embedData(__DIR__ . '/Fixture/foo.jpg', 'name', 'image/jpeg');

        $stringMessage = (string) $message->getSwiftMessage();

        preg_match('/Content-Type: image\/jpeg;/', $stringMessage, $image);
        preg_match('/name=name/', $stringMessage, $name);

        $this->assertSame('Content-Type: image/jpeg;', $image[0]);
        $this->assertSame('name=name', $name[0]);
    }

    public function testFromMethod()
    {
        $this->swift->shouldReceive('setFrom')
            ->once()
            ->with('foo@bar.baz', 'Foo');

        $this->assertInstanceOf(Message::class, $this->message->from('foo@bar.baz', 'Foo'));
    }

    public function testSenderMethod()
    {
        $this->swift->shouldReceive('setSender')
            ->once()
            ->with('foo@bar.baz', 'Foo');

        $this->assertInstanceOf(Message::class, $this->message->sender('foo@bar.baz', 'Foo'));
    }

    public function testReturnPathMethod()
    {
        $this->swift->shouldReceive('setReturnPath')
            ->once()
            ->with('foo@bar.baz');

        $this->assertInstanceOf(Message::class, $this->message->returnPath('foo@bar.baz'));
    }

    public function testToMethod()
    {
        $this->swift->shouldReceive('addTo')
            ->once()
            ->with('foo@bar.baz', 'Foo');

        $this->assertInstanceOf(Message::class, $this->message->to('foo@bar.baz', 'Foo'));
    }

    public function testToMethodWithOverride()
    {
        $this->swift->shouldReceive('setTo')
            ->once()
            ->with('foo@bar.baz', 'Foo');

        $this->assertInstanceOf(Message::class, $this->message->to('foo@bar.baz', 'Foo', true));
    }

    public function testCcMethod()
    {
        $this->swift->shouldReceive('addCc')
            ->once()
            ->with('foo@bar.baz', 'Foo');

        $this->assertInstanceOf(Message::class, $this->message->cc('foo@bar.baz', 'Foo'));
    }

    public function testBccMethod()
    {
        $this->swift->shouldReceive('addBcc')
            ->once()
            ->with('foo@bar.baz', 'Foo');

        $this->assertInstanceOf(Message::class, $this->message->bcc('foo@bar.baz', 'Foo'));
    }

    public function testReplyToMethod()
    {
        $this->swift->shouldReceive('addReplyTo')
            ->with('foo@bar.baz', 'Foo');
        $this->assertInstanceOf(Message::class, $this->message->replyTo('foo@bar.baz', 'Foo'));
    }

    public function testSubjectMethod()
    {
        $this->swift->shouldReceive('setSubject')
            ->once()
            ->with('foo');

        $this->assertInstanceOf(Message::class, $this->message->subject('foo'));
    }

    public function testPriorityMethod()
    {
        $this->swift->shouldReceive('setPriority')
            ->once()
            ->with(1);

        $this->assertInstanceOf(Message::class, $this->message->priority(1));
    }

    public function testGetSwiftMessageMethod()
    {
        $this->assertInstanceOf(Swift_Mime_Message::class, $this->message->getSwiftMessage());
    }
}
