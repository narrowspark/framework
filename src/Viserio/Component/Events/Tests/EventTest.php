<?php
declare(strict_types=1);
namespace Viserio\Component\Events\Tests;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Events\Event;

class EventTest extends TestCase
{
    /**
     * @var null|\Viserio\Component\Events\Event
     */
    private $event;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->event = new Event('test', $this, ['invoker' => $this]);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Event name cant be empty.
     */
    public function testSetName(): void
    {
        new Event('', $this, ['invoker' => $this]);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The event name must only contain the characters A-Z, a-z, 0-9, _, and '.'.
     */
    public function testSetNameWithInvalidName(): void
    {
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
