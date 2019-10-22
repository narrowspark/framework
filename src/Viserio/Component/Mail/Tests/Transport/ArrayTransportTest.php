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

namespace Viserio\Component\Mail\Tests\Transport;

use PHPUnit\Framework\TestCase;
use Swift_Message;
use Viserio\Component\Mail\Transport\ArrayTransport;

/**
 * @internal
 *
 * @small
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

        self::assertCount(1, $transport->getMessages());
        self::assertSame($message, $transport->getMessages()[0]);

        $transport->reset();

        self::assertCount(0, $transport->getMessages());
    }
}
