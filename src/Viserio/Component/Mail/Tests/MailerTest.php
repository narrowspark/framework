<?php
declare(strict_types=1);
namespace Viserio\Component\Mail\Tests;

use Mockery as Mock;
use Narrowspark\TestingHelper\ArrayContainer;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use stdClass;
use Swift_Mailer;
use Swift_Message;
use Swift_Mime_SimpleMessage;
use Swift_Transport;
use Viserio\Component\Contracts\Events\EventManager as EventManagerContract;
use Viserio\Component\Contracts\Mail\Message as MessageContract;
use Viserio\Component\Contracts\View\Factory as ViewFactoryContract;
use Viserio\Component\Contracts\View\View as ViewContract;
use Viserio\Component\Mail\Events\MessageSendingEvent;
use Viserio\Component\Mail\Events\MessageSentEvent;
use Viserio\Component\Mail\Mailer;

class MailerTest extends MockeryTestCase
{
    public function testMailerSendSendsMessageWithProperViewContent()
    {
        unset($_SERVER['__mailer.test']);

        $mailer = $this->getMockBuilder(Mailer::class)
            ->setMethods(['createMessage'])
            ->setConstructorArgs($this->getMocks())
            ->getMock();
        $mailer->setViewFactory($this->mock(ViewFactoryContract::class));

        $message = $this->mock(MessageContract::class);

        $mailer->expects($this->once())
            ->method('createMessage')
            ->will($this->returnValue($message));

        $view = $this->mock(ViewContract::class);

        $mailer->getViewFactory()
            ->shouldReceive('create')
            ->once()
            ->with('foo', ['data', 'message' => $message])
            ->andReturn($view);

        $view->shouldReceive('render')
            ->once()
            ->andReturn('rendered.view');

        $message->shouldReceive('setBody')
            ->once()
            ->with('rendered.view', 'text/html');
        $message->shouldReceive('setFrom')
            ->never();
        $message->shouldReceive('to')
            ->once()
            ->with('foo@bar.baz', null, true);
        $message->shouldReceive('cc')
            ->once();
        $message->shouldReceive('bcc')
            ->once();

        $mailer = $this->setSwiftMailer($mailer);

        $mimeMessage = $this->mock(Swift_Mime_SimpleMessage::class);

        $message->shouldReceive('getSwiftMessage')
            ->once()
            ->andReturn($mimeMessage);

        $mailer->alwaysTo('foo@bar.baz');
        $mailer->getSwiftMailer()->shouldReceive('send')
            ->once()
            ->with($mimeMessage, [])
            ->andReturn(1);
        $mailer->send('foo', ['data'], function ($mail) {
            $_SERVER['__mailer.test'] = $mail;
        });

        unset($_SERVER['__mailer.test']);
    }

    public function testMailerSendSendsMessageWithProperPlainViewContent()
    {
        unset($_SERVER['__mailer.test']);

        $mailer = $this->getMockBuilder(Mailer::class)
            ->setConstructorArgs($this->getMocks())
            ->setMethods(['createMessage'])
            ->getMock();
        $mailer->setViewFactory($this->mock(ViewFactoryContract::class));

        $message = $this->mock(MessageContract::class);

        $mailer->expects($this->once())
            ->method('createMessage')
            ->will($this->returnValue($message));

        $view = $this->mock(ViewContract::class);

        $mailer->getViewFactory()
            ->shouldReceive('create')
            ->once()
            ->with('foo', ['data', 'message' => $message])
            ->andReturn($view);
        $mailer->getViewFactory()
            ->shouldReceive('create')
            ->once()
            ->with('bar', ['data', 'message' => $message])
            ->andReturn($view);

        $view->shouldReceive('render')
            ->twice()
            ->andReturn('rendered.view');

        $message->shouldReceive('setBody')
            ->once()
            ->with('rendered.view', 'text/html');
        $message->shouldReceive('addPart')
            ->once()
            ->with('rendered.view', 'text/plain');
        $message->shouldReceive('setFrom')
            ->never();

        $mailer = $this->setSwiftMailer($mailer);

        $mimeMessage = $this->mock(Swift_Mime_SimpleMessage::class);

        $message->shouldReceive('getSwiftMessage')
            ->once()
            ->andReturn($mimeMessage);
        $mailer->getSwiftMailer()
            ->shouldReceive('send')
            ->once()
            ->with($mimeMessage, [])
            ->andReturn(1);
        $mailer->send(['foo', 'bar'], ['data'], function ($mail) {
            $_SERVER['__mailer.test'] = $mail;
        });

        unset($_SERVER['__mailer.test']);
    }

    public function testMailerSendSendsMessageWithProperPlainViewContentWhenExplicit()
    {
        unset($_SERVER['__mailer.test']);

        $mailer = $this->getMockBuilder(Mailer::class)
            ->setConstructorArgs($this->getMocks())
            ->setMethods(['createMessage'])
            ->getMock();
        $mailer->setViewFactory($this->mock(ViewFactoryContract::class));

        $message = $this->mock(MessageContract::class);

        $mailer->expects($this->once())
            ->method('createMessage')
            ->will($this->returnValue($message));

        $view = $this->mock(ViewContract::class);

        $mailer->getViewFactory()
            ->shouldReceive('create')
            ->once()
            ->with('foo', ['data', 'message' => $message])
            ->andReturn($view);
        $mailer->getViewFactory()
            ->shouldReceive('create')
            ->once()
            ->with('bar', ['data', 'message' => $message])
            ->andReturn($view);

        $view->shouldReceive('render')
            ->twice()
            ->andReturn('rendered.view');

        $message->shouldReceive('setBody')
            ->once()
            ->with('rendered.view', 'text/html');
        $message->shouldReceive('addPart')
            ->once()
            ->with('rendered.view', 'text/plain');
        $message->shouldReceive('setFrom')
            ->never();

        $mailer = $this->setSwiftMailer($mailer);

        $mimeMessage = $this->mock(Swift_Mime_SimpleMessage::class);

        $message->shouldReceive('getSwiftMessage')
            ->once()
            ->andReturn($mimeMessage);
        $mailer->getSwiftMailer()
            ->shouldReceive('send')
            ->once()
            ->with($mimeMessage, [])
            ->andReturn(1);
        $mailer->send(['html' => 'foo', 'text' => 'bar'], ['data'], function ($m) {
            $_SERVER['__mailer.test'] = $m;
        });

        unset($_SERVER['__mailer.test']);
    }

    public function testMailerRawSend()
    {
        unset($_SERVER['__mailer.test']);

        $mailer = $this->getMockBuilder(Mailer::class)
            ->setMethods(['createMessage'])
            ->setConstructorArgs($this->getMocks())
            ->getMock();

        $message = $this->mock(MessageContract::class);

        $mailer->expects($this->once())
            ->method('createMessage')
            ->will($this->returnValue($message));

        $message->shouldReceive('setBody')
            ->once()
            ->with('foo', 'text/plain');
        $message->shouldReceive('setFrom')
            ->never();

        $mailer = $this->setSwiftMailer($mailer);

        $mimeMessage = $this->mock(Swift_Mime_SimpleMessage::class);

        $message->shouldReceive('getSwiftMessage')
            ->once()
            ->andReturn($mimeMessage);

        $callback = function ($mail) {
            $_SERVER['__mailer.test'] = $mail;
        };

        $mailer->getSwiftMailer()->shouldReceive('send')
            ->once()
            ->with($mimeMessage, [])
            ->andReturn(1);
        $mailer->raw('foo', $callback);

        unset($_SERVER['__mailer.test']);
    }

    public function testMailerPlainSend()
    {
        unset($_SERVER['__mailer.test']);
        $event  = $this->mock(EventManagerContract::class);
        $mailer = $this->getMockBuilder(Mailer::class)
            ->setMethods(['createMessage'])
            ->setConstructorArgs($this->getMocks())
            ->getMock();

        $message = $this->mock(MessageContract::class);

        $mailer->expects($this->once())
            ->method('createMessage')
            ->will($this->returnValue($message));

        $message->shouldReceive('setBody')
            ->once()
            ->with('foo', 'text/plain');
        $message->shouldReceive('setFrom')
            ->never();

        $mailer      = $this->setSwiftMailer($mailer);
        $mimeMessage = $this->mock(Swift_Mime_SimpleMessage::class);

        $event->shouldReceive('trigger')
            ->once()
            ->with(Mock::type(MessageSendingEvent::class))
            ->andReturn(true);
        $event->shouldReceive('trigger')
            ->once()
            ->with(Mock::type(MessageSentEvent::class))
            ->andReturn(true);

        $mailer->setEventManager($event);

        $message->shouldReceive('getSwiftMessage')
            ->twice()
            ->andReturn($mimeMessage);

        $mailer->getSwiftMailer()->shouldReceive('send')
            ->once()
            ->with($mimeMessage, [])
            ->andReturn(1);
        $mailer->plain('foo', [], function ($mail) {
            $_SERVER['__mailer.test'] = $mail;
        });

        unset($_SERVER['__mailer.test']);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Invalid view.
     */
    public function testMailerToThrowExceptionOnView()
    {
        $mailer = new Mailer(
            $this->mock(Swift_Mailer::class),
            ['viserio' => ['mail' => []]]
        );

        $mailer->send(new stdClass());
    }

    /**
     * @expectedException \Invoker\Exception\NotCallableException
     * @expectedExceptionMessage Instance of stdClass is not a callable
     */
    public function testMailerToThrowExceptionOnCallbackWithContainer()
    {
        $swift = $this->mock(Swift_Mailer::class);
        $swift->shouldReceive('createMessage')
            ->andReturn(new Swift_Message());
        $mailer = new Mailer(
            $swift,
            ['viserio' => ['mail' => []]]
        );
        $mailer->setContainer(new ArrayContainer([]));

        $mailer->send('test', [], new stdClass());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Callback is not valid.
     */
    public function testMailerToThrowExceptionOnCallback()
    {
        $swift = $this->mock(Swift_Mailer::class);
        $swift->shouldReceive('createMessage')
            ->andReturn(new Swift_Message());
        $mailer = new Mailer(
            $swift,
            ['viserio' => ['mail' => []]]
        );

        $mailer->send('test', [], new stdClass());
    }

    public function testSendWithFailedEvent()
    {
        $event  = $this->mock(EventManagerContract::class);
        $mailer = $this->getMockBuilder(Mailer::class)
            ->setMethods(['createMessage'])
            ->setConstructorArgs($this->getMocks())
            ->getMock();

        $message = $this->mock(MessageContract::class);

        $mailer->expects($this->once())
            ->method('createMessage')
            ->will($this->returnValue($message));

        $message->shouldReceive('setBody')
            ->never();
        $message->shouldReceive('setFrom')
            ->never();

        $swift = $this->mock(Swift_Mailer::class);
        $swift->shouldReceive('getTransport')
            ->never();
        $swift->shouldReceive('createMessage')
            ->andReturn(new Swift_Message());

        $mailer->setSwiftMailer($swift);

        $mimeMessage = $this->mock(Swift_Mime_SimpleMessage::class);

        $event->shouldReceive('trigger')
            ->once()
            ->with(Mock::type(MessageSendingEvent::class))
            ->andReturn(false);
        $event->shouldReceive('trigger')
            ->once()
            ->with(Mock::type(MessageSentEvent::class))
            ->andReturn(true);

        $mailer->setEventManager($event);

        $message->shouldReceive('getSwiftMessage')
            ->twice()
            ->andReturn($mimeMessage);

        $mailer->getSwiftMailer()->shouldReceive('send')
            ->never();

        self::assertSame(0, $mailer->send([], []));
    }

    protected function setSwiftMailer($mailer)
    {
        $transport = $this->mock(Swift_Transport::class);
        $transport->shouldReceive('stop');

        $swift = $this->mock(Swift_Mailer::class);
        $swift->shouldReceive('getTransport')
            ->once()
            ->andReturn($transport);
        $swift->shouldReceive('createMessage')
            ->andReturn(new Swift_Message());

        $mailer->setSwiftMailer($swift);

        return $mailer;
    }

    public function testMacroable()
    {
        Mailer::macro('foo', function () {
            return 'bar';
        });

        $mailer = new Mailer(
            $this->mock(Swift_Mailer::class),
            ['viserio' => ['mail' => []]]
        );

        $this->assertEquals('bar', $mailer->foo());
    }

    protected function getMocks(): array
    {
        return [
            $this->mock(Swift_Mailer::class),
            'config' => ['viserio' => ['mail' => []]],
        ];
    }
}
