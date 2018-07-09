<?php
declare(strict_types=1);
namespace Viserio\Component\Mail\Tests\Transport;

use PHPUnit\Framework\TestCase;
use Swift_Message;
use Viserio\Component\Mail\Transport\ArrayTransport;

/**
 * @internal
 */
final class ArrayTransportTest extends TestCase
{
    public function testSend(): void
    {
        $message = new Swift_Message('Foo subject', 'Bar body');
        $message->setSender('myself@example.com');
        $message->setTo('me@example.com');
        $message->setBcc('you@example.com');

        $transport = new ArrayTransport();
        $transport->send($message);

        static::assertCount(1, $transport->getMessages());
        static::assertSame($message, $transport->getMessages()[0]);

        $transport->flush();

        static::assertCount(0, $transport->getMessages());
    }
}
