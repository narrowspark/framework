<?php
declare(strict_types=1);
namespace Viserio\Component\Mail\Tests\Transport;

use PHPUnit\Framework\TestCase;
use Swift_Message;
use Viserio\Component\Mail\Transport\ArrayTransport;

class ArrayTransportTest extends TestCase
{
    public function testSend(): void
    {
        $message = new Swift_Message('Foo subject', 'Bar body');
        $message->setSender('myself@example.com');
        $message->setTo('me@example.com');
        $message->setBcc('you@example.com');

        $transport = new ArrayTransport();
        $transport->send($message);

        self::assertSame(1, \count($transport->getMessages()));
        self::assertSame($message, $transport->getMessages()[0]);

        $transport->flush();

        self::assertSame(0, \count($transport->getMessages()));
    }
}
