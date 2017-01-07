<?php
declare(strict_types=1);
namespace Viserio\Contracts\Events\Tests\Traits;

use Narrowspark\TestingHelper\Traits\MockeryTrait;
use PHPUnit\Framework\TestCase;
use Viserio\Contracts\Events\EventManager as EventManagerContract;
use Viserio\Contracts\Events\Traits\EventsAwareTrait;

class EventsAwareTraitTest extends TestCase
{
    use MockeryTrait;
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
