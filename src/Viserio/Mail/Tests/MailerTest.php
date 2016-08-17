<?php
declare(strict_types=1);
namespace Viserio\Mail\Tests;

use Narrowspark\TestingHelper\Traits\MockeryTrait;
use Swift_Mailer;
use Swift_Mime_Message;
use Swift_Transport;
use Viserio\Contracts\Mail\Message as MessageContract;
use Viserio\Contracts\View\Factory as ViewFactoryContract;
use Viserio\Contracts\View\View as ViewContract;
use Viserio\Mail\Mailer;

class MailerTest extends \PHPUnit_Framework_TestCase
{
    use MockeryTrait;

    public function testMailerSendSendsMessageWithProperViewContent()
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

        $this->setSwiftMailer($mailer);

        $mimeMessage = $this->mock(Swift_Mime_Message::class);

        $message->shouldReceive('getSwiftMessage')
            ->once()
            ->andReturn($mimeMessage);

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

        $this->setSwiftMailer($mailer);

        $mimeMessage = $this->mock(Swift_Mime_Message::class);

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

        $this->setSwiftMailer($mailer);

        $mimeMessage = $this->mock(Swift_Mime_Message::class);

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

    protected function setSwiftMailer($mailer)
    {
        $transport = $this->mock(Swift_Transport::class);
        $transport->shouldReceive('stop');

        $swift = $this->mock(Swift_Mailer::class);
        $swift->shouldReceive('getTransport')
            ->once()
            ->andReturn($transport);

        $mailer->setSwiftMailer($swift);

        return $mailer;
    }

    protected function getMailer()
    {
        return new Mailer(
            $this->mock(Swift_Mailer::class),
            $this->mock(ViewFactoryContract::class)
        );
    }

    protected function getMocks(): array
    {
        return [
            $this->mock(Swift_Mailer::class),
            $this->mock(ViewFactoryContract::class),
        ];
    }
}
