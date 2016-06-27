<?php
namespace Viserio\Mail\Tests;

use Narrowspark\TestingHelper\Traits\MockeryTrait;
use Swift_Mailer;

class MailMessageTest extends \PHPUnit_Framework_TestCase
{
    use MockeryTrait;

    public function testBasicAttachment()
    {
        $swift = new Swift_Mailer();
        $message = $this->getMockBuilder('Viserio\Mail\Message')
            ->setConstructorArgs([$swift])
            ->setMethods(['createAttachmentFromPath'])
            ->getMock();
        $attachment = Mock::mock('StdClass');
        $message->expects($this->once())->method('createAttachmentFromPath')->with($this->equalTo('foo.jpg'))->will($this->returnValue($attachment));
        $swift->shouldReceive('attach')->once()->with($attachment);
        $attachment->shouldReceive('setContentType')->once()->with('image/jpeg');
        $attachment->shouldReceive('setFilename')->once()->with('bar.jpg');
        $message->attach('foo.jpg', ['mime' => 'image/jpeg', 'as' => 'bar.jpg']);
    }

    public function testDataAttachment()
    {
        $swift = new Swift_Mailer();
        $message = $this->getMockBuilder('Viserio\Mail\Message')
            ->setConstructorArgs([$swift])
            ->setMethods(['createAttachmentFromData'])
            ->getMock();
        $attachment = Mock::mock('StdClass');
        $message->expects($this->once())->method('createAttachmentFromData')->with($this->equalTo('foo'), $this->equalTo('name'))->will($this->returnValue($attachment));
        $swift->shouldReceive('attach')->once()->with($attachment);
        $attachment->shouldReceive('setContentType')->once()->with('image/jpeg');
        $message->attachData('foo', 'name', ['mime' => 'image/jpeg']);
    }
}
