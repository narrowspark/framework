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

namespace Viserio\Component\Events\Tests;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Viserio\Component\Events\Event;

/**
 * @internal
 *
 * @small
 * @coversNothing
 */
final class EventTest extends TestCase
{
    /** @var null|\Viserio\Component\Events\Event */
    private $event;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->event = new Event('test', $this, ['invoker' => $this]);
    }

    public function testSetName(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Event name cant be empty.');

        new Event('', $this, ['invoker' => $this]);
    }

    public function testSetNameWithInvalidName(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The event name must only contain the characters A-Z, a-z, 0-9, _, and \'.\'.');

        new Event('te-st', $this, ['invoker' => $this]);
    }

    public function testGetName(): void
    {
        self::assertSame('test', $this->event->getName());
    }

    public function testGetTarget(): void
    {
        self::assertEquals($this, $this->event->getTarget());
    }

    public function testGetParams(): void
    {
        $p = $this->event->getParams();

        self::assertArrayHasKey('invoker', $p);
    }

    public function testStopPropagation(): void
    {
        self::assertFalse($this->event->isPropagationStopped());

        $this->event->stopPropagation();

        self::assertTrue($this->event->isPropagationStopped());
    }
}
