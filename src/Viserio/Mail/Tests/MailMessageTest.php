<?php
namespace Viserio\Mail\Test;

use Mockery as Mock;

class MailMessageTest extends \PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        Mock::close();
    }

    public function testBasicAttachment()
    {
        $swift = new \Swift_Mailer();
        $message = $this->getMock('Viserio\Mail\Message', ['createAttachmentFromPath'], [$swift]);
        $attachment = Mock::mock('StdClass');
        $message->expects($this->once())->method('createAttachmentFromPath')->with($this->equalTo('foo.jpg'))->will($this->returnValue($attachment));
        $swift->shouldReceive('attach')->once()->with($attachment);
        $attachment->shouldReceive('setContentType')->once()->with('image/jpeg');
        $attachment->shouldReceive('setFilename')->once()->with('bar.jpg');
        $message->attach('foo.jpg', ['mime' => 'image/jpeg', 'as' => 'bar.jpg']);
    }

    public function testDataAttachment()
    {
        $swift = new \Swift_Mailer();
        $message = $this->getMock('Viserio\Mail\Message', ['createAttachmentFromData'], [$swift]);
        $attachment = Mock::mock('StdClass');
        $message->expects($this->once())->method('createAttachmentFromData')->with($this->equalTo('foo'), $this->equalTo('name'))->will($this->returnValue($attachment));
        $swift->shouldReceive('attach')->once()->with($attachment);
        $attachment->shouldReceive('setContentType')->once()->with('image/jpeg');
        $message->attachData('foo', 'name', ['mime' => 'image/jpeg']);
    }
}
