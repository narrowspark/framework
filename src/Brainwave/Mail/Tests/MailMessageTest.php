<?php

namespace Brainwave\Mail\Test;

/*
 * Narrowspark - a PHP 5 framework
 *
 * @author      Daniel Bannert <info@anolilab.de>
 * @copyright   2015 Daniel Bannert
 * @link        http://www.narrowspark.de
 * @license     http://www.narrowspark.com/license
 * @version     0.9.8-dev
 * @package     Narrowspark/framework
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

use Mockery as Mock;

/**
 * MailMessageTest.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.5-dev
 */
class MailMessageTest extends \PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        Mock::close();
    }

    public function testBasicAttachment()
    {
        $swift = new \Swift_Mailer();
        $message = $this->getMock('Brainwave\Mail\Message', ['createAttachmentFromPath'], [$swift]);
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
        $message = $this->getMock('Brainwave\Mail\Message', ['createAttachmentFromData'], [$swift]);
        $attachment = Mock::mock('StdClass');
        $message->expects($this->once())->method('createAttachmentFromData')->with($this->equalTo('foo'), $this->equalTo('name'))->will($this->returnValue($attachment));
        $swift->shouldReceive('attach')->once()->with($attachment);
        $attachment->shouldReceive('setContentType')->once()->with('image/jpeg');
        $message->attachData('foo', 'name', ['mime' => 'image/jpeg']);
    }
}
