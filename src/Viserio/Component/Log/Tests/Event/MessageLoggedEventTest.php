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

namespace Viserio\Component\Log\Tests\Event;

use Mockery;
use Monolog\Logger as MonologLogger;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Component\Log\Event\MessageLoggedEvent;
use Viserio\Component\Log\Logger;

/**
 * @internal
 *
 * @small
 * @coversNothing
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
            Mockery::mock(MonologLogger::class),
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
