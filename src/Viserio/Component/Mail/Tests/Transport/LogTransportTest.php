<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018-2020 Daniel Bannert
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/narrowspark/automatic
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
 * @coversNothing
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
