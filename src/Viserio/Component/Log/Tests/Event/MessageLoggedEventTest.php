<?php
declare(strict_types=1);
namespace Viserio\Component\Log\Tests\Event;

use Monolog\Logger as MonologLogger;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Component\Log\Event\MessageLoggedEvent;
use Viserio\Component\Log\Logger;

/**
 * @internal
 */
final class MessageLoggedEventTest extends MockeryTestCase
{
    /**
     * @var \Viserio\Component\Log\Event\MessageLoggedEvent
     */
    private $event;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->event = new MessageLoggedEvent(
            $this->mock(MonologLogger::class),
            'error',
            'test',
            ['data' => 'infos']
        );
    }

    public function testGetName(): void
    {
        $this->assertSame(Logger::MESSAGE, $this->event->getName());
    }

    public function testGetTarget(): void
    {
        $this->assertInstanceOf(MonologLogger::class, $this->event->getTarget());
    }

    public function testGetLevel(): void
    {
        $this->assertSame('error', $this->event->getLevel());
    }

    public function testGetMessage(): void
    {
        $this->assertSame('test', $this->event->getMessage());
    }

    public function testGetContext(): void
    {
        $this->assertEquals(['data' => 'infos'], $this->event->getContext());
    }
}
