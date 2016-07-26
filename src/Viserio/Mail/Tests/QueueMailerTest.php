<?php
declare(strict_types=1);
namespace Viserio\Mail\Tests;

use Narrowspark\TestingHelper\Traits\MockeryTrait;
use Interop\Container\ContainerInterface;
use Swift_Mailer;
use Swift_Mime_Message;
use Swift_Transport;
use stdClass;
use Viserio\Mail\{
    QueueMailer,
    Tests\Fixture\FailingSwiftMailerStub
};
use Viserio\Contracts\{
    Mail\Message as MessageContract,
    View\Factory as ViewFactoryContract,
    View\View as ViewContract
};

class QueueMailerTest extends \PHPUnit_Framework_TestCase
{
    use MockeryTrait;

    public function testMailerCanResolveMailerClasses()
    {
        $message = $this->mock(MessageContract::class);

        $mockMailer = $this->mock(stdClass::class);
        $mockMailer->shouldReceive('mail')
            // ->once()
            ->with($message);

        $container = $this->mock(ContainerInterface::class);
        $container->shouldReceive('get')
            ->once()
            ->with('FooMailer')
            ->andReturn(function () use ($mockMailer) {
                return $mockMailer;
            });

        $mailer = $this->getMockBuilder(QueueMailer::class)
            ->setConstructorArgs($this->getMocks())
            ->setMethods(['createMessage'])
            ->getMock();

        $mailer->setContainer($container);

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

        $mailer->getSwiftMailer()
            ->shouldReceive('send')
            ->once()
            ->with($mimeMessage, [])
            ->andReturn(1);
        $mailer->send('foo', ['data'], 'FooMailer');
    }

    // public function testGlobalFromIsRespectedOnAllMessages()
    // {
    //     unset($_SERVER['__mailer.test']);

    //     $mailer = $this->getMailer();

    //     $view = $this->mock(stdClass::class);

    //     $mailer->getViewFactory()
    //         ->shouldReceive('create')
    //         ->once()
    //         ->andReturn($view);

    //     $view->shouldReceive('render')
    //         ->once()
    //         ->andReturn('rendered.view');

    //     $this->setSwiftMailer($mailer);

    //     $me = $this;

    //     $mailer->alwaysFrom('info@narrowspark.de', 'Daniel Bannert');
    //     $mailer->getSwiftMailer()
    //         ->shouldReceive('send')
    //         ->once()
    //         ->with($this->type(Swift_Message::class), [])
    //         ->andReturnUsing(function ($message) use ($me) {
    //             $me->assertEquals(['info@narrowspark.de' => 'Daniel Bannert'], $message->getFrom());
    //         });
    //     $mailer->send('foo', ['data'], function ($m) {
    //     });
    // }

    // public function testFailedRecipientsAreAppendedAndCanBeRetrieved()
    // {
    //     unset($_SERVER['__mailer.test']);

    //     $mailer = $this->getMailer();
    //     $mailer->getSwiftMailer()
    //         ->shouldReceive('getTransport')
    //         ->andReturn($transport = $this->mock(Swift_Transport::class));

    //     $transport->shouldReceive('stop');

    //     $view = $this->mock(stdClass::class);

    //     $mailer->getViewFactory()
    //         ->shouldReceive('create')
    //         ->once()
    //         ->andReturn($view);

    //     $view->shouldReceive('render')
    //         ->once()
    //         ->andReturn('rendered.view');

    //     $swift = new FailingSwiftMailerStub();

    //     $this->setSwiftMailer($mailersend('foo', ['data'], function ($m) {
    //     }));

    //     $this->assertEquals(['info@narrowspark.de'], $mailer->failures());
    // }

    public function setSwiftMailer($mailer)
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
