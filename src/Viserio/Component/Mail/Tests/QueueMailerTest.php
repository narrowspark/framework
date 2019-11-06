<?php

declare(strict_types=1);

/**
 * This file is part of Narrowspark Framework.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Viserio\Component\Mail\Tests;

use Mockery;
use Mockery\MockInterface;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Psr\Container\ContainerInterface;
use Swift_Mailer;
use Swift_Mime_SimpleMessage;
use Swift_Transport;
use Viserio\Component\Mail\QueueMailer;
use Viserio\Component\Mail\Tests\Fixture\FailingSwiftMailerStub;
use Viserio\Contract\Mail\Message as MessageContract;
use Viserio\Contract\Queue\QueueConnector as QueueContract;
use Viserio\Contract\View\Factory as ViewFactoryContract;
use Viserio\Contract\View\View as ViewContract;

/**
 * @internal
 *
 * @small
 */
final class QueueMailerTest extends MockeryTestCase
{
    /** @var \Mockery\MockInterface|\Viserio\Contract\View\Factory */
    private $viewFactoryMock;

    /** @var \Mockery\MockInterface|Swift_Mailer */
    private $swiftMock;

    /** @var \Mockery\MockInterface|\Viserio\Contract\Mail\Message */
    private $messageMock;

    /** @var \Mockery\MockInterface|\Viserio\Contract\Queue\QueueConnector */
    private $queueMock;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->viewFactoryMock = Mockery::mock(ViewFactoryContract::class);
        $this->swiftMock = Mockery::mock(Swift_Mailer::class);
        $this->messageMock = Mockery::mock(MessageContract::class);
        $this->queueMock = Mockery::mock(QueueContract::class);
    }

    public function testMailerCanResolveMailerClasses(): void
    {
        $mockMailer = $this->createAMockMailerObject();

        $mailer = $this->arrangeMailerWithMessage();

        $container = Mockery::mock(ContainerInterface::class);
        $container->shouldReceive('get')
            ->once()
            ->with('FooMailer')
            ->andReturn(static function () use ($mockMailer) {
                return $mockMailer;
            });

        $mailer->setContainer($container);

        $this->arrangeSetBody($mailer);

        $mimeMessage = Mockery::mock(Swift_Mime_SimpleMessage::class);

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

        $mimeMessage = Mockery::mock(Swift_Mime_SimpleMessage::class);
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
                $this->assertEquals(['info@narrowspark.de' => 'Daniel Bannert'], $message->getFrom());

                return 1;
            });
        $mailer->send('foo', ['data'], static function ($mail): void {
        });
    }

    public function testFailedRecipientsAreAppendedAndCanBeRetrieved(): void
    {
        unset($_SERVER['__mailer.test']);

        $swift = new FailingSwiftMailerStub(Mockery::mock(Swift_Transport::class));

        /** @var \Mockery\MockInterface|\Viserio\Component\Mail\QueueMailer $mailer */
        $mailer = Mockery::mock(QueueMailer::class . '[createMessage]', [$swift, $this->queueMock, []])
            ->shouldAllowMockingProtectedMethods();
        $mailer->shouldReceive('createMessage')
            ->once()
            ->andReturn($this->messageMock);

        $this->arrangeSetBody($mailer);

        $mimeMessage = Mockery::mock(Swift_Mime_SimpleMessage::class);

        $this->messageMock->shouldReceive('getSwiftMessage')
            ->once()
            ->andReturn($mimeMessage);

        $mailer->send('foo', ['data'], static function ($m): void {
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
        $transport = Mockery::mock(Swift_Transport::class);
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
        $mailer = Mockery::mock(QueueMailer::class . '[createMessage]', [$this->swiftMock, $this->queueMock, []])
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
        $view = Mockery::mock(ViewContract::class);

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
