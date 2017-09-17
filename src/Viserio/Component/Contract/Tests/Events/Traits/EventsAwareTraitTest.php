<?php
declare(strict_types=1);
namespace Viserio\Component\Contract\Events\Tests\Traits;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Component\Contract\Events\EventManager as EventManagerContract;
use Viserio\Component\Contract\Events\Traits\EventsAwareTrait;

class EventsAwareTraitTest extends MockeryTestCase
{
    use EventsAwareTrait;

    public function testGetAndsetEventManager(): void
    {
        $this->setEventManager($this->mock(EventManagerContract::class));

        self::assertInstanceOf(EventManagerContract::class, $this->getEventManager());
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage EventManager is not set up.
     */
    public function testgetEventManagerThrowExceptionIfEventsDispatcherIsNotSet(): void
    {
        $this->getEventManager();
    }
}
