<?php
declare(strict_types=1);
namespace Viserio\Component\Mail\Tests;

use Mockery as Mock;
use Mockery\MockInterface;
use Narrowspark\TestingHelper\ArrayContainer;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use stdClass;
use Swift_Mailer;
use Swift_Message;
use Swift_Mime_SimpleMessage;
use Swift_Transport;
use Viserio\Component\Contract\Events\EventManager as EventManagerContract;
use Viserio\Component\Contract\Mail\Message as MessageContract;
use Viserio\Component\Contract\View\Factory as ViewFactoryContract;
use Viserio\Component\Contract\View\View as ViewContract;
use Viserio\Component\Mail\Event\MessageSendingEvent;
use Viserio\Component\Mail\Event\MessageSentEvent;
use Viserio\Component\Mail\Mailer;

class MailerTest extends MockeryTestCase
{
    /**
     * @var \Mockery\MockInterface|\Viserio\Component\Contract\View\Factory
     */
    private $viewFactoryMock;

    /**
     * @var \Mockery\MockInterface|\Swift_Mailer
     */
    private $swiftMock;

    /**
     * @var \Mockery\MockInterface|\Viserio\Component\Contract\Mail\Message
     */
    private $messageMock;

    /**
     * {@inheritdoc}
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->viewFactoryMock = $this->mock(ViewFactoryContract::class);
        $this->swiftMock       = $this->mock(Swift_Mailer::class);
        $this->messageMock     = $this->mock(MessageContract::class);
    }

    public function testMailerSendSendsMessageWithProperViewContent(): void
    {
        unset($_SERVER['__mailer.test']);

        $mailer = $this->arrangeMailerWithMessage();
        $mailer->setViewFactory($this->viewFactoryMock);

        $view = $this->mock(ViewContract::class);

        $this->viewFactoryMock->shouldReceive('create')
            ->once()
            ->with('foo', ['data', 'message' => $this->messageMock])
            ->andReturn($view);

        $view->shouldReceive('render')
            ->once()
            ->andReturn('rendered.view');

        $this->messageMock->shouldReceive('setBody')
            ->once()
            ->with('rendered.view', 'text/html');

        $this->messageMock->shouldReceive('setFrom')
            ->never();
        $this->messageMock->shouldReceive('to')
            ->once()
            ->with('foo@bar.baz', null, true);
        $this->messageMock->shouldReceive('cc')
            ->once();
        $this->messageMock->shouldReceive('bcc')
            ->once();

        $this->arrangeGetTransport();

        $mailer->alwaysTo('foo@bar.baz');

        $this->arrangeSendWithMimeMessage();

        $mailer->send('foo', ['data'], function ($mail): void {
            $_SERVER['__mailer.test'] = $mail;
        });

        unset($_SERVER['__mailer.test']);
    }

    public function testMailerSendSendsMessageWithProperPlainViewContent(): void
    {
        unset($_SERVER['__mailer.test']);

        $mailer = $this->arrangeMailerWithMessage();
        $mailer->setViewFactory($this->viewFactoryMock);

        $view = $this->mock(ViewContract::class);

        $this->viewFactoryMock->shouldReceive('create')
            ->once()
            ->with('foo', ['data', 'message' => $this->messageMock])
            ->andReturn($view);
        $this->viewFactoryMock->shouldReceive('create')
            ->once()
            ->with('bar', ['data', 'message' => $this->messageMock])
            ->andReturn($view);

        $view->shouldReceive('render')
            ->twice()
            ->andReturn('rendered.view');

        $this->messageMock->shouldReceive('setBody')
            ->once()
            ->with('rendered.view', 'text/html');
        $this->messageMock->shouldReceive('addPart')
            ->once()
            ->with('rendered.view', 'text/plain');
        $this->messageMock->shouldReceive('setFrom')
            ->never();

        $this->arrangeSendWithMimeMessage();

        $this->swiftMock
            ->shouldReceive('getTransport->stop')
            ->once();

        $mailer->send(['foo', 'bar'], ['data'], function ($mail): void {
            $_SERVER['__mailer.test'] = $mail;
        });

        unset($_SERVER['__mailer.test']);
    }

    public function testMailerSendSendsMessageWithProperPlainViewContentWhenExplicit(): void
    {
        unset($_SERVER['__mailer.test']);

        $mailer = $this->mock(Mailer::class . '[createMessage]', [$this->swiftMock, []])
            ->shouldAllowMockingProtectedMethods();
        $mailer->setViewFactory($this->viewFactoryMock);

        $mailer->shouldReceive('createMessage')
            ->once()
            ->andReturn($this->messageMock);

        $view = $this->mock(ViewContract::class);

        $this->viewFactoryMock->shouldReceive('create')
            ->once()
            ->with('foo', ['data', 'message' => $this->messageMock])
            ->andReturn($view);
        $this->viewFactoryMock->shouldReceive('create')
            ->once()
            ->with('bar', ['data', 'message' => $this->messageMock])
            ->andReturn($view);

        $view->shouldReceive('render')
            ->twice()
            ->andReturn('rendered.view');

        $this->messageMock->shouldReceive('setBody')
            ->once()
            ->with('rendered.view', 'text/html');
        $this->messageMock->shouldReceive('addPart')
            ->once()
            ->with('rendered.view', 'text/plain');
        $this->messageMock->shouldReceive('setFrom')
            ->never();

        $this->arrangeGetTransport();

        $this->arrangeSendWithMimeMessage();

        $mailer->send(['html' => 'foo', 'text' => 'bar'], ['data'], function ($m): void {
            $_SERVER['__mailer.test'] = $m;
        });

        unset($_SERVER['__mailer.test']);
    }

    public function testMailerRawSend(): void
    {
        unset($_SERVER['__mailer.test']);

        $mailer = $this->arrangeMailerWithMessage();

        $this->messageMock->shouldReceive('setBody')
            ->once()
            ->with('foo', 'text/plain');
        $this->messageMock->shouldReceive('setFrom')
            ->never();

        $this->arrangeGetTransport();

        $mimeMessage = $this->mock(Swift_Mime_SimpleMessage::class);

        $this->messageMock->shouldReceive('getSwiftMessage')
            ->once()
            ->andReturn($mimeMessage);

        $callback = function ($mail): void {
            $_SERVER['__mailer.test'] = $mail;
        };

        $this->swiftMock->shouldReceive('send')
            ->once()
            ->with($mimeMessage, [])
            ->andReturn(1);

        $mailer->raw('foo', $callback);

        unset($_SERVER['__mailer.test']);
    }

    public function testMailerPlainSend(): void
    {
        unset($_SERVER['__mailer.test']);

        $mailer = $this->arrangeMailerWithMessage();

        $this->messageMock->shouldReceive('setBody')
            ->once()
            ->with('foo', 'text/plain');
        $this->messageMock->shouldReceive('setFrom')
            ->never();

        $this->arrangeGetTransport();

        $event = $this->mock(EventManagerContract::class);
        $event->shouldReceive('trigger')
            ->once()
            ->with(Mock::type(MessageSendingEvent::class))
            ->andReturn(true);
        $event->shouldReceive('trigger')
            ->once()
            ->with(Mock::type(MessageSentEvent::class))
            ->andReturn(true);

        $mailer->setEventManager($event);

        $mimeMessage = $this->mock(Swift_Mime_SimpleMessage::class);

        $this->messageMock->shouldReceive('getSwiftMessage')
            ->twice()
            ->andReturn($mimeMessage);

        $this->swiftMock->shouldReceive('send')
            ->once()
            ->with($mimeMessage, [])
            ->andReturn(1);

        $mailer->plain('foo', [], function ($mail): void {
            $_SERVER['__mailer.test'] = $mail;
        });

        unset($_SERVER['__mailer.test']);
    }

    /**
     * @expectedException \Viserio\Component\Contract\Mail\Exception\UnexpectedValueException
     * @expectedExceptionMessage Invalid view.
     */
    public function testMailerToThrowExceptionOnView(): void
    {
        $mailer = new Mailer($this->swiftMock, []);

        $mailer->send(new stdClass());
    }

    /**
     * @expectedException \Invoker\Exception\NotCallableException
     * @expectedExceptionMessage Instance of stdClass is not a callable
     */
    public function testMailerToThrowExceptionOnCallbackWithContainer(): void
    {
        $this->swiftMock->shouldReceive('createMessage')
            ->andReturn(new Swift_Message());

        $mailer = new Mailer($this->swiftMock, []);
        $mailer->setContainer(new ArrayContainer([]));

        $mailer->send('test', [], new stdClass());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Callback is not valid.
     */
    public function testMailerToThrowExceptionOnCallback(): void
    {
        $this->swiftMock->shouldReceive('createMessage')
            ->andReturn(new Swift_Message());

        $mailer = new Mailer($this->swiftMock, []);

        $mailer->send('test', [], new stdClass());
    }

    public function testSendWithFailedEvent(): void
    {
        $mailer = $this->arrangeMailerWithMessage();

        $this->messageMock->shouldReceive('setBody')
            ->never();
        $this->messageMock->shouldReceive('setFrom')
            ->never();

        $this->swiftMock->shouldReceive('getTransport')
            ->never();
        $this->swiftMock->shouldReceive('createMessage')
            ->andReturn(new Swift_Message());

        $event  = $this->mock(EventManagerContract::class);
        $event->shouldReceive('trigger')
            ->once()
            ->with(Mock::type(MessageSendingEvent::class))
            ->andReturn(false);
        $event->shouldReceive('trigger')
            ->once()
            ->with(Mock::type(MessageSentEvent::class))
            ->andReturn(true);

        $mailer->setEventManager($event);

        $mimeMessage = $this->mock(Swift_Mime_SimpleMessage::class);

        $this->messageMock->shouldReceive('getSwiftMessage')
            ->twice()
            ->andReturn($mimeMessage);

        $this->swiftMock->shouldReceive('send')
            ->never();

        self::assertSame(0, $mailer->send([], []));
    }

    public function testMacroable(): void
    {
        Mailer::macro('foo', function () {
            return 'bar';
        });

        $mailer = new Mailer($this->swiftMock, []);

        $this->assertEquals('bar', $mailer->foo());
    }

    /**
     * {@inheritdoc}
     */
    protected function assertPreConditions(): void
    {
        parent::assertPreConditions();

        $this->allowMockingNonExistentMethods(true);
    }

    /**
     * @return \Mockery\MockInterface
     */
    private function arrangeMailerWithMessage(): MockInterface
    {
        $mailer = $this->mock(Mailer::class . '[createMessage]', [$this->swiftMock, []])
            ->shouldAllowMockingProtectedMethods();

        $mailer->shouldReceive('createMessage')
            ->once()
            ->andReturn($this->messageMock);

        return $mailer;
    }

    private function arrangeSendWithMimeMessage(): void
    {
        $mimeMessage = $this->mock(Swift_Mime_SimpleMessage::class);

        $this->messageMock->shouldReceive('getSwiftMessage')
            ->once()
            ->andReturn($mimeMessage);

        $this->swiftMock->shouldReceive('send')
            ->once()
            ->with($mimeMessage, [])
            ->andReturn(1);
    }

    private function arrangeGetTransport(): void
    {
        $transport = $this->mock(Swift_Transport::class);
        $transport->shouldReceive('stop');

        $this->swiftMock->shouldReceive('getTransport')
            ->once()
            ->andReturn($transport);
        $this->swiftMock->shouldReceive('createMessage')
            ->andReturn(new Swift_Message());
    }
}
