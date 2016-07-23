<?php
declare(strict_types=1);
namespace Viserio\Mail\Tests;

use Narrowspark\TestingHelper\Traits\MockeryTrait;
use stdClass;
use Psr\Log\LoggerInterface;
use Swift_Mime_Message;
use Swift_Mailer;
use Swift_Transport;
use Viserio\Mail\Mailer;
use Viserio\Mail\Tests\Fixture\FailingSwiftMailerStub;
use Viserio\Contracts\{
    Events\Dispatcher as EventsDispatcherContract,
    View\Factory as ViewFactoryContract
};

class MailerTest extends \PHPUnit_Framework_TestCase
{
    use MockeryTrait;

    public function testMailerSendSendsMessageWithProperViewContent()
    {
        unset($_SERVER['__mailer.test']);

        $mailer = $this->getMockBuilder(Mailer::class)
            ->setConstructorArgs($this->getMocks())
            ->setMethods(['createMessage'])
            ->getMock();

        $message = $this->mock(Swift_Mime_Message::class);
        $mailer->expects($this->once())
            ->method('createMessage')
            ->will($this->returnValue($message));

        $view = $this->mock(stdClass::class);

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

        $message->shouldReceive('getSwiftMessage')
            ->once()
            ->andReturn($message);
        $mailer->getSwiftMailer()
            ->shouldReceive('send')
            ->once()
            ->with($message, []);
        $mailer->send('foo', ['data'], function ($m) {
            $_SERVER['__mailer.test'] = $m;
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

        $message = $this->mock(Swift_Mime_Message::class);

        $mailer->expects($this->once())
            ->method('createMessage')
            ->will($this->returnValue($message));

        $view = $this->mock(stdClass::class);

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

        $message->shouldReceive('getSwiftMessage')
            ->once()
            ->andReturn($message);
        $mailer->getSwiftMailer()
            ->shouldReceive('send')
            ->once()
            ->with($message, []);
        $mailer->send(['foo', 'bar'], ['data'], function ($m) {
            $_SERVER['__mailer.test'] = $m;
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

        $message = $this->mock(Swift_Mime_Message::class);

        $mailer->expects($this->once())
            ->method('createMessage')
            ->will($this->returnValue($message));

        $view = $this->mock(stdClass::class);

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

        $message->shouldReceive('getSwiftMessage')
            ->once()
            ->andReturn($message);
        $mailer->getSwiftMailer()
            ->shouldReceive('send')
            ->once()
            ->with($message, []);
        $mailer->send(['html' => 'foo', 'text' => 'bar'], ['data'], function ($m) {
            $_SERVER['__mailer.test'] = $m;
        });

        unset($_SERVER['__mailer.test']);
    }

    public function testMessagesCanBeLoggedInsteadOfSent()
    {
        $mailer = $this->getMockBuilder(Mailer::class)
            ->setConstructorArgs($this->getMocks())
            ->setMethods(['createMessage'])
            ->getMock();

        $message = $this->mock(Swift_Mime_Message::class);

        $mailer->expects($this->once())
            ->method('createMessage')
            ->will($this->returnValue($message));

        $view = $this->mock(stdClass::class);

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

        $message->shouldReceive('getTo')
            ->once()
            ->andReturn(['info@narrowspark.de' => 'Daniel']);
        $message->shouldReceive('getSwiftMessage')
            ->once()
            ->andReturn($message);
        $mailer->getSwiftMailer()
            ->shouldReceive('send')
            ->never();

        $logger = $this->mock(LoggerInterface::class);
        $logger->shouldReceive('info')
            ->once()
            ->with('Pretending to mail message to: info@narrowspark.de');

        $mailer->setLogger($logger);
        $mailer->pretend();
        $mailer->send('foo', ['data'], function ($m) {
        });
    }

    public function testMailerCanResolveMailerClasses()
    {
        $mailer = $this->getMockBuilder(Mailer::class)
            ->setConstructorArgs($this->getMocks())
            ->setMethods(['createMessage'])
            ->getMock();

        $message = $this->mock(Swift_Mime_Message::class);

        $mailer->expects($this->once())
            ->method('createMessage')
            ->will($this->returnValue($message));

        $view = $this->mock(stdClass::class);

        $mockMailer = $this->mock(stdClass::class);
        $mockMailer->shouldReceive('mail')
            ->once()
            ->with($message);

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

        $message->shouldReceive('getSwiftMessage')
            ->once()
            ->andReturn($message);

        $mailer->getSwiftMailer()
            ->shouldReceive('send')
            ->once()
            ->with($message, []);
        $mailer->send('foo', ['data'], 'FooMailer');
    }

    public function testGlobalFromIsRespectedOnAllMessages()
    {
        unset($_SERVER['__mailer.test']);

        $mailer = $this->getMailer();

        $view = $this->mock(stdClass::class);

        $mailer->getViewFactory()
            ->shouldReceive('create')
            ->once()
            ->andReturn($view);

        $view->shouldReceive('render')
            ->once()
            ->andReturn('rendered.view');

        $this->setSwiftMailer($mailer);

        $me = $this;

        $mailer->alwaysFrom('info@narrowspark.de', 'Daniel Bannert');
        $mailer->getSwiftMailer()
            ->shouldReceive('send')
            ->once()
            ->with($this->type(Swift_Message::class), [])
            ->andReturnUsing(function ($message) use ($me) {
                $me->assertEquals(['info@narrowspark.de' => 'Daniel Bannert'], $message->getFrom());
            });
        $mailer->send('foo', ['data'], function ($m) {
        });
    }

    public function testFailedRecipientsAreAppendedAndCanBeRetrieved()
    {
        unset($_SERVER['__mailer.test']);

        $mailer = $this->getMailer();
        $mailer->getSwiftMailer()
            ->shouldReceive('getTransport')
            ->andReturn($transport = $this->mock(Swift_Transport::class));

        $transport->shouldReceive('stop');

        $view = $this->mock(stdClass::class);

        $mailer->getViewFactory()
            ->shouldReceive('create')
            ->once()
            ->andReturn($view);

        $view->shouldReceive('render')
            ->once()
            ->andReturn('rendered.view');

        $swift = new FailingSwiftMailerStub();

        $this->setSwiftMailer($mailersend('foo', ['data'], function ($m) {
        }));

        $this->assertEquals(['info@narrowspark.de'], $mailer->failures());
    }

    public function setSwiftMailer($mailer)
    {
        $swift = $this->mock(Swift_Mailer::class);
        $swift->shouldReceive('getTransport')
            ->andReturn($transport = $this->mock(Swift_Transport::class));

        $transport->shouldReceive('stop');
    }

    public function getTransport()
    {
        $transport = $this->mock(Swift_Transport::class);
        $transport->shouldReceive('stop');

        return $transport;
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
            $this->mock(ViewFactoryContract::class)
        ];
    }
}
