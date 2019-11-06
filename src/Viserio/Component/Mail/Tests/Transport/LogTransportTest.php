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

use Mockery;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Psr\Log\LoggerInterface;
use Swift_Message;
use Viserio\Component\Mail\Transport\LogTransport;

/**
 * @internal
 *
 * @small
 */
final class LogTransportTest extends MockeryTestCase
{
    public function testSend(): void
    {
        $message = new Swift_Message('Foo subject', 'Bar body');
        $message->setSender('myself@example.com');
        $message->setTo('me@example.com');
        $message->setBcc('you@example.com');

        $logger = Mockery::mock(LoggerInterface::class);
        $logger->shouldReceive('debug')
            ->once()
            ->with($this->getMimeEntityString($message));

        $transport = new LogTransport($logger);
        $transport->send($message);
    }

    /**
     * Get a loggable string out of a Swiftmailer entity.
     *
     * @param Swift_Message $entity
     *
     * @return string
     */
    protected function getMimeEntityString(Swift_Message $entity): string
    {
        $string = (string) $entity->getHeaders() . "\n" . $entity->getBody();

        foreach ($entity->getChildren() as $children) {
            $string .= "\n\n" . $this->getMimeEntityString($children);
        }

        return $string;
    }
}
