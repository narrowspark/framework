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

namespace Viserio\Component\Log\Tests\Event;

use Monolog\Logger as MonologLogger;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Component\Log\Event\MessageLoggedEvent;
use Viserio\Component\Log\Logger;

/**
 * @internal
 *
 * @small
 */
final class MessageLoggedEventTest extends MockeryTestCase
{
    /** @var \Viserio\Component\Log\Event\MessageLoggedEvent */
    private $event;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->event = new MessageLoggedEvent(
            \Mockery::mock(MonologLogger::class),
            'error',
            'test',
            ['data' => 'infos']
        );
    }

    public function testGetName(): void
    {
        self::assertSame(Logger::MESSAGE, $this->event->getName());
    }

    public function testGetTarget(): void
    {
        self::assertInstanceOf(MonologLogger::class, $this->event->getTarget());
    }

    public function testGetLevel(): void
    {
        self::assertSame('error', $this->event->getLevel());
    }

    public function testGetMessage(): void
    {
        self::assertSame('test', $this->event->getMessage());
    }

    public function testGetContext(): void
    {
        self::assertEquals(['data' => 'infos'], $this->event->getContext());
    }
}
