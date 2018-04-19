<?php
declare(strict_types=1);
namespace Viserio\Component\Mail\Tests;

use Mockery;
use Mockery\MockInterface;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Psr\Container\ContainerInterface;
use Swift_Mailer;
use Swift_Mime_SimpleMessage;
use Swift_Transport;
use Viserio\Component\Contract\Mail\Message as MessageContract;
use Viserio\Component\Contract\Queue\QueueConnector as QueueContract;
use Viserio\Component\Contract\View\Factory as ViewFactoryContract;
use Viserio\Component\Contract\View\View as ViewContract;
use Viserio\Component\Mail\QueueMailer;
use Viserio\Component\Mail\Tests\Fixture\FailingSwiftMailerStub;

class QueueMailerTest extends MockeryTestCase
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
     * @var \Mockery\MockInterface|\Viserio\Component\Contract\Queue\QueueConnector
     */
    private $queueMock;

    /**
     * {@inheritdoc}
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->viewFactoryMock = $this->mock(ViewFactoryContract::class);
        $this->swiftMock       = $this->mock(Swift_Mailer::class);
        $this->messageMock     = $this->mock(MessageContract::class);
        $this->queueMock       = $this->mock(QueueContract::class);
    }

    public function testMailerCanResolveMailerClasses(): void
    {
        $mockMailer = $this->createAMockMailerObject();

        $mailer = $this->arrangeMailerWithMessage();

        $container = $this->mock(ContainerInterface::class);
        $container->shouldReceive('get')
            ->once()
            ->with('FooMailer')
            ->andReturn(function () use ($mockMailer) {
                return $mockMailer;
            });

        $mailer->setContainer($container);

        $this->arrangeSetBody($mailer);

        $mimeMessage = $this->mock(Swift_Mime_SimpleMessage::class);

        $this->messageMock->shouldReceive('getSwiftMessage')
            ->once()
            ->andReturn($mimeMessage);

        $this->arrangeSwiftTransport();

        $this->swiftMock
            ->shouldReceive('send')
            ->once()
            ->with($mimeMessage, [])
            ->andReturn(1);

        $mailer->send('foo', ['data'], 'FooMailer');
    }

    public function testGlobalFromIsRespectedOnAllMessages(): void
    {
        unset($_SERVER['__mailer.test']);

        $mailer = $this->arrangeMailerWithMessage();

        $this->arrangeSetBody($mailer);

        $mailer->alwaysFrom('info@narrowspark.de', 'Daniel Bannert');

        $mimeMessage = $this->mock(Swift_Mime_SimpleMessage::class);
        $mimeMessage->shouldReceive('getFrom')
            ->once()
            ->andReturn(['info@narrowspark.de' => 'Daniel Bannert']);

        $this->messageMock->shouldReceive('getSwiftMessage')
            ->once()
            ->andReturn($mimeMessage);

        $this->arrangeSwiftTransport();

        $this->swiftMock
            ->shouldReceive('send')
            ->once()
            ->with(Mockery::type(Swift_Mime_SimpleMessage::class), [])
            ->andReturnUsing(function ($message) {
                self::assertEquals(['info@narrowspark.de' => 'Daniel Bannert'], $message->getFrom());

                return 1;
            });
        $mailer->send('foo', ['data'], function ($mail): void {
        });
    }

    public function testFailedRecipientsAreAppendedAndCanBeRetrieved(): void
    {
        unset($_SERVER['__mailer.test']);

        $swift = new FailingSwiftMailerStub($this->mock(Swift_Transport::class));

        $mailer = $this->mock(QueueMailer::class . '[createMessage]', [$swift, $this->queueMock, []])
            ->shouldAllowMockingProtectedMethods();
        $mailer->shouldReceive('createMessage')
            ->once()
            ->andReturn($this->messageMock);

        $this->arrangeSetBody($mailer);

        $mimeMessage = $this->mock(Swift_Mime_SimpleMessage::class);

        $this->messageMock->shouldReceive('getSwiftMessage')
            ->once()
            ->andReturn($mimeMessage);

        $mailer->send('foo', ['data'], function ($m): void {
        });

        self::assertEquals(['info@narrowspark.de'], $mailer->failures());
    }

    /**
     * {@inheritdoc}
     */
    protected function allowMockingNonExistentMethods($allow = false): void
    {
        parent::allowMockingNonExistentMethods(true);
    }

    private function arrangeSwiftTransport(): void
    {
        $transport = $this->mock(Swift_Transport::class);
        $transport->shouldReceive('stop');

        $this->swiftMock->shouldReceive('getTransport')
            ->once()
            ->andReturn($transport);
    }

    /**
     * @return object
     */
    private function createAMockMailerObject(): object
    {
        $message = $this->messageMock;

        return new class($message) {
            private $message;

            public function __construct($message)
            {
                $this->message = $message;
            }

            public function mail()
            {
                return $this->message;
            }
        };
    }

    /**
     * @return Mockery\MockInterface
     */
    private function arrangeMailerWithMessage(): MockInterface
    {
        $mailer = $this->mock(QueueMailer::class . '[createMessage]', [$this->swiftMock, $this->queueMock, []])
            ->shouldAllowMockingProtectedMethods();
        $mailer->shouldReceive('createMessage')
            ->once()
            ->andReturn($this->messageMock);

        return $mailer;
    }

    /**
     * @param \Mockery\MockInterface $mailer
     */
    private function arrangeSetBody($mailer): void
    {
        $view = $this->mock(ViewContract::class);

        $this->viewFactoryMock->shouldReceive('create')
            ->once()
            ->andReturn($view);

        $mailer->setViewFactory($this->viewFactoryMock);

        $view->shouldReceive('render')
            ->once()
            ->andReturn('rendered.view');

        $this->messageMock->shouldReceive('setBody')
            ->once()
            ->with('rendered.view', 'text/html');
        $this->messageMock->shouldReceive('setFrom')
            ->never();
    }
}
