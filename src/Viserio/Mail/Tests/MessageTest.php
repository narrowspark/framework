<?php
declare(strict_types=1);
namespace Viserio\Mail\Tests;

use Swift_Mime_Message;
use Swift_Attachment;
use Viserio\Mail\Message;

class MessageTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var \Swift_Mime_Message
     */
    protected $swift;

    /**
     * @var \Illuminate\Mail\Message
     */
    protected $message;

    public function setUp()
    {
        parent::setUp();

        $this->swift = $this->getMock(Swift_Mime_Message::class);
        $this->message = new Message($this->swift);
    }

    public function testBasicAttachment()
    {
        $swift = $this->swift;

        $message = $this->getMockBuilder(Message::class)
            ->setConstructorArgs([$swift])
            ->setMethods(['createAttachmentFromPath'])
            ->setConstructorArgs([$swift])
            ->getMock();

        $attachment = $this->getMockBuilder(Swift_Attachment::class)->getMock();

        $message->expects($this->once())
            ->method('createAttachmentFromPath')
            ->with($this->equalTo('foo.jpg'))
            ->will($this->returnValue($attachment));

        $swift->expects($this->once())
            ->method('attach')
            ->with($attachment);

        $attachment->shouldReceive('setContentType')
            ->once()
            ->with('image/jpeg');

        $attachment->shouldReceive('setFilename')
            ->once()
            ->with('bar.jpg');

        $message->attach('foo.jpg', ['mime' => 'image/jpeg', 'as' => 'bar.jpg']);
    }

    public function testDataAttachment()
    {
        $swift = $this->swift;

        $message = $this->getMockBuilder(Message::class)
            ->setConstructorArgs([$swift])
            ->setMethods(['createAttachmentFromData'])
            ->setConstructorArgs([$swift])
            ->getMock();

        $attachment = $this->getMockBuilder(Swift_Attachment::class)->getMock();

        $message->expects($this->once())
            ->method('createAttachmentFromData')
            ->with($this->equalTo('foo'), $this->equalTo('name'))
            ->will($this->returnValue($attachment));

        $swift->expects($this->once())
            ->method('attach')
            ->with($attachment);

        $attachment->shouldReceive('setContentType')
            ->once()
            ->with('image/jpeg');

        $message->attachData('foo', 'name', ['mime' => 'image/jpeg']);
    }

    public function testFromMethod()
    {
        $this->swift->expects($this->once())
            ->method('setFrom')
            ->with('foo@bar.baz', 'Foo');
        $this->assertInstanceOf(Message::class, $this->message->from('foo@bar.baz', 'Foo'));
    }

    public function testSenderMethod()
    {
        $this->swift->expects($this->once())
            ->method('setSender')
            ->with('foo@bar.baz', 'Foo');
        $this->assertInstanceOf(Message::class, $this->message->sender('foo@bar.baz', 'Foo'));
    }

    public function testReturnPathMethod()
    {
        $this->swift->expects($this->once())
            ->method('setReturnPath')
            ->with('foo@bar.baz');
        $this->assertInstanceOf(Message::class, $this->message->returnPath('foo@bar.baz'));
    }

    public function testToMethod()
    {
        $this->swift->expects($this->once())
            ->method('addTo')
            ->with('foo@bar.baz', 'Foo');
        $this->assertInstanceOf(Message::class, $this->message->to('foo@bar.baz', 'Foo', false));
    }

    public function testToMethodWithOverride()
    {
        $this->swift->expects($this->once())
            ->method('setTo')
            ->with('foo@bar.baz', 'Foo');
        $this->assertInstanceOf(Message::class, $this->message->to('foo@bar.baz', 'Foo', true));
    }

    public function testCcMethod()
    {
        $this->swift->expects($this->once())
            ->method('addCc')
            ->with('foo@bar.baz', 'Foo');
        $this->assertInstanceOf(Message::class, $this->message->cc('foo@bar.baz', 'Foo'));
    }

    public function testBccMethod()
    {
        $this->swift->expects($this->once())
            ->method('addBcc')
            ->with('foo@bar.baz', 'Foo');
        $this->assertInstanceOf(Message::class, $this->message->bcc('foo@bar.baz', 'Foo'));
    }

    public function testReplyToMethod()
    {
        $this->swift->expects($this->once())
            ->method('addReplyTo')
            ->with('foo@bar.baz', 'Foo');
        $this->assertInstanceOf(Message::class, $this->message->replyTo('foo@bar.baz', 'Foo'));
    }

    public function testSubjectMethod()
    {
        $this->swift->expects($this->once())
            ->method('setSubject')
            ->with('foo');
        $this->assertInstanceOf(Message::class, $this->message->subject('foo'));
    }

    public function testPriorityMethod()
    {
        $this->swift->expects($this->once())
            ->method('setPriority')
            ->with(1);
        $this->assertInstanceOf(Message::class, $this->message->priority(1));
    }

    public function testGetSwiftMessageMethod()
    {
        $this->assertInstanceOf(Swift_Mime_Message::class, $this->message->getSwiftMessage());
    }
}
