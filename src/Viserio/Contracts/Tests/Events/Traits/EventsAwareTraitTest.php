<?php
declare(strict_types=1);
namespace Viserio\Contracts\Events\Tests\Traits;

use Narrowspark\TestingHelper\Traits\MockeryTrait;
use PHPUnit\Framework\TestCase;
use Viserio\Contracts\Events\Dispatcher;
use Viserio\Contracts\Events\Traits\EventsAwareTrait;

class EventsAwareTraitTest extends TestCase
{
    use MockeryTrait;
    use EventsAwareTrait;

    public function testGetAndSetEventsDispatcher()
    {
        $this->setEventsDispatcher($this->mock(Dispatcher::class));

        self::assertInstanceOf(Dispatcher::class, $this->getEventsDispatcher());
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Events dispatcher is not set up.
     */
    public function testGetEventsDispatcherThrowExceptionIfEventsDispatcherIsNotSet()
    {
        $this->getEventsDispatcher();
    }
}
