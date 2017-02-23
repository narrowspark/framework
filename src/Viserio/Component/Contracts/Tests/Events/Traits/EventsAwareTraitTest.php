<?php
declare(strict_types=1);
namespace Viserio\Component\Contracts\Events\Tests\Traits;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Component\Contracts\Events\EventManager as EventManagerContract;
use Viserio\Component\Contracts\Events\Traits\EventsAwareTrait;

class EventsAwareTraitTest extends MockeryTestCase
{
    use EventsAwareTrait;

    public function testGetAndsetEventManager()
    {
        $this->setEventManager($this->mock(EventManagerContract::class));

        self::assertInstanceOf(EventManagerContract::class, $this->getEventManager());
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage EventManager is not set up.
     */
    public function testgetEventManagerThrowExceptionIfEventsDispatcherIsNotSet()
    {
        $this->getEventManager();
    }
}
