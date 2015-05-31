<?php

namespace Brainwave\Mail\Test;

/*
 * Narrowspark - a PHP 5 framework
 *
 * @author      Daniel Bannert <info@anolilab.de>
 * @copyright   2015 Daniel Bannert
 * @link        http://www.narrowspark.de
 * @license     http://www.narrowspark.com/license
 * @version     0.10.0-dev
 * @package     Narrowspark/framework
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

use Brainwave\Mail\Mailer;
use Mockery as Mock;

/**
 * MailMailerTest.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.5-dev
 */
class MailMailerTest extends \PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        Mock::close();
    }

    public function testMailerSendSendsMessageWithProperViewContent()
    {
        unset($_SERVER['__mailer.test']);
        $mailer = $this->getMock('\Brainwave\Mail\Mailer', ['createMessage'], $this->getMocks());
        $message = Mock::mock('\Swift_Mime_Message');
        $mailer->expects($this->once())->method('createMessage')->will($this->returnValue($message));
        $view = Mock::mock('\StdClass');
        $mailer->getViewFactory()->shouldReceive('make')->once()->with('foo', ['data', 'message' => $message])->andReturn($view);
        $view->shouldReceive('render')->once()->andReturn('rendered.view');
        $message->shouldReceive('setBody')->once()->with('rendered.view', 'text/html');
        $message->shouldReceive('setFrom')->never();

        $this->setSwiftMailer($mailer);

        $message->shouldReceive('getSwiftMessage')->once()->andReturn($message);
        $mailer->getSwiftMailer()->shouldReceive('send')->once()->with($message, []);
        $mailer->send('foo', ['data'], function ($m) { $_SERVER['__mailer.test'] = $m; });
        unset($_SERVER['__mailer.test']);
    }

    public function testMailerSendSendsMessageWithProperPlainViewContent()
    {
        unset($_SERVER['__mailer.test']);
        $mailer = $this->getMock('\Brainwave\Mail\Mailer', ['createMessage'], $this->getMocks());
        $message = Mock::mock('\Swift_Mime_Message');
        $mailer->expects($this->once())->method('createMessage')->will($this->returnValue($message));
        $view = Mock::mock('\StdClass');
        $mailer->getViewFactory()->shouldReceive('make')->once()->with('foo', ['data', 'message' => $message])->andReturn($view);
        $mailer->getViewFactory()->shouldReceive('make')->once()->with('bar', ['data', 'message' => $message])->andReturn($view);
        $view->shouldReceive('render')->twice()->andReturn('rendered.view');
        $message->shouldReceive('setBody')->once()->with('rendered.view', 'text/html');
        $message->shouldReceive('addPart')->once()->with('rendered.view', 'text/plain');
        $message->shouldReceive('setFrom')->never();

        $this->setSwiftMailer($mailer);

        $message->shouldReceive('getSwiftMessage')->once()->andReturn($message);
        $mailer->getSwiftMailer()->shouldReceive('send')->once()->with($message, []);
        $mailer->send(['foo', 'bar'], ['data'], function ($m) { $_SERVER['__mailer.test'] = $m; });
        unset($_SERVER['__mailer.test']);
    }

    public function testMailerSendSendsMessageWithProperPlainViewContentWhenExplicit()
    {
        unset($_SERVER['__mailer.test']);
        $mailer = $this->getMock('\Brainwave\Mail\Mailer', ['createMessage'], $this->getMocks());
        $message = Mock::mock('\Swift_Mime_Message');
        $mailer->expects($this->once())->method('createMessage')->will($this->returnValue($message));
        $view = Mock::mock('\StdClass');
        $mailer->getViewFactory()->shouldReceive('make')->once()->with('foo', ['data', 'message' => $message])->andReturn($view);
        $mailer->getViewFactory()->shouldReceive('make')->once()->with('bar', ['data', 'message' => $message])->andReturn($view);
        $view->shouldReceive('render')->twice()->andReturn('rendered.view');
        $message->shouldReceive('setBody')->once()->with('rendered.view', 'text/html');
        $message->shouldReceive('addPart')->once()->with('rendered.view', 'text/plain');
        $message->shouldReceive('setFrom')->never();

        $this->setSwiftMailer($mailer);

        $message->shouldReceive('getSwiftMessage')->once()->andReturn($message);
        $mailer->getSwiftMailer()->shouldReceive('send')->once()->with($message, []);
        $mailer->send(['html' => 'foo', 'text' => 'bar'], ['data'], function ($m) { $_SERVER['__mailer.test'] = $m; });
        unset($_SERVER['__mailer.test']);
    }

    public function testMessagesCanBeLoggedInsteadOfSent()
    {
        $mailer = $this->getMock('\Brainwave\Mail\Mailer', ['createMessage'], $this->getMocks());
        $message = Mock::mock('\Swift_Mime_Message');
        $mailer->expects($this->once())->method('createMessage')->will($this->returnValue($message));
        $view = Mock::mock('\StdClass');
        $mailer->getViewFactory()->shouldReceive('make')->once()->with('foo', ['data', 'message' => $message])->andReturn($view);
        $view->shouldReceive('render')->once()->andReturn('rendered.view');
        $message->shouldReceive('setBody')->once()->with('rendered.view', 'text/html');
        $message->shouldReceive('setFrom')->never();

        $this->setSwiftMailer($mailer);

        $message->shouldReceive('getTo')->once()->andReturn(['info@narrowspark.de' => 'Daniel']);
        $message->shouldReceive('getSwiftMessage')->once()->andReturn($message);
        $mailer->getSwiftMailer()->shouldReceive('send')->never();
        $logger = Mock::mock('\Psr\Log\LoggerInterface');
        $logger->shouldReceive('info')->once()->with('Pretending to mail message to: info@narrowspark.de');
        $mailer->setLogger($logger);
        $mailer->pretend();
        $mailer->send('foo', ['data'], function ($m) {});
    }

    public function testMailerCanResolveMailerClasses()
    {
        $mailer = $this->getMock('\Brainwave\Mail\Mailer', ['createMessage'], $this->getMocks());
        $message = Mock::mock('\Swift_Mime_Message');
        $mailer->expects($this->once())->method('createMessage')->will($this->returnValue($message));
        $view = Mock::mock('\StdClass');
        $mockMailer = Mock::mock('\StdClass');

        $mockMailer->shouldReceive('mail')->once()->with($message);
        $mailer->getViewFactory()->shouldReceive('make')->once()->with('foo', ['data', 'message' => $message])->andReturn($view);
        $view->shouldReceive('render')->once()->andReturn('rendered.view');
        $message->shouldReceive('setBody')->once()->with('rendered.view', 'text/html');
        $message->shouldReceive('setFrom')->never();

        $this->setSwiftMailer($mailer);

        $message->shouldReceive('getSwiftMessage')->once()->andReturn($message);
        $mailer->getSwiftMailer()->shouldReceive('send')->once()->with($message, []);
        $mailer->send('foo', ['data'], 'FooMailer');
    }

    public function testGlobalFromIsRespectedOnAllMessages()
    {
        unset($_SERVER['__mailer.test']);
        $mailer = $this->getMailer();
        $view = Mock::mock('\StdClass');
        $mailer->getViewFactory()->shouldReceive('make')->once()->andReturn($view);
        $view->shouldReceive('render')->once()->andReturn('rendered.view');

        $this->setSwiftMailer($mailer);

        $mailer->alwaysFrom('info@narrowspark.de', 'Daniel Bannert');
        $me = $this;
        $mailer->getSwiftMailer()->shouldReceive('send')->once()->with(Mock::type('\Swift_Message'), [])->andReturnUsing(function ($message) use ($me) {
            $me->assertEquals(['info@narrowspark.de' => 'Daniel Bannert'], $message->getFrom());
        });
        $mailer->send('foo', ['data'], function ($m) {});
    }

    public function testFailedRecipientsAreAppendedAndCanBeRetrieved()
    {
        unset($_SERVER['__mailer.test']);
        $mailer = $this->getMailer();
        $mailer->getSwiftMailer()->shouldReceive('getTransport')->andReturn($transport = m::mock('\Swift_Transport'));
        $transport->shouldReceive('stop');
        $view = Mock::mock('\StdClass');
        $mailer->getViewFactory()->shouldReceive('make')->once()->andReturn($view);
        $view->shouldReceive('render')->once()->andReturn('rendered.view');
        $swift = new \Brainwave\Mail\Test\FailingSwiftMailerStub();

        $this->setSwiftMailer($mailersend('foo', ['data'], function ($m) {});

        $this->assertEquals(['info@narrowspark.de'], $mailer->failures());
    }

    public function setSwiftMailer($mailer)
    {
        $swift = Mock::mock('\Swift_Mailer');
        $swift->shouldReceive('getTransport')->andReturn($transport = Mock::mock('\Swift_Transport'));
        $transport->shouldReceive('stop');

        $this->setSwiftMailer($mailermailer;
    }


    public function getTransport()
    {
        $transport = Mock::mock('\Swift_Transport');
        $transport->shouldReceive('stop');

        return $transport;
    }

    protected function getMailer()
    {
        return new Mailer(Mock::mock('\Swift_Mailer'), Mock::mock('\Brainwave\Contracts\View\Factory'), Mock::mock('\Symfony\Component\EventDispatcher\EventDispatcherInterface'));
    }

    protected function getMocks()
    {
        return [Mock::mock('\Swift_Mailer'), Mock::mock('\Brainwave\Contracts\View\Factory'), Mock::mock('\Symfony\Component\EventDispatcher\EventDispatcherInterface')];
    }
}

class FailingSwiftMailerStub
{
    public function send($message, &$failed)
    {
        $failed[] = 'info@narrowspark.de';
    }
}
