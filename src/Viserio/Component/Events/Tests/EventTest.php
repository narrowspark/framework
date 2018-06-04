<?php
declare(strict_types=1);
namespace Viserio\Component\Events\Tests;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Events\Event;

/**
 * @internal
 */
final class EventTest extends TestCase
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

    public function testSetName(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Event name cant be empty.');

        new Event('', $this, ['invoker' => $this]);
    }

    public function testSetNameWithInvalidName(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The event name must only contain the characters A-Z, a-z, 0-9, _, and \'.\'.');

        new Event('te-st', $this, ['invoker' => $this]);
    }

    public function testGetName(): void
    {
        $this->assertSame('test', $this->event->getName());
    }

    public function testGetTarget(): void
    {
        $this->assertEquals($this, $this->event->getTarget());
    }

    public function testGetParams(): void
    {
        $p = $this->event->getParams();

        $this->assertArrayHasKey('invoker', $p);
    }

    public function testStopPropagation(): void
    {
        $this->assertFalse($this->event->isPropagationStopped());

        $this->event->stopPropagation();

        $this->assertTrue($this->event->isPropagationStopped());
    }
}
