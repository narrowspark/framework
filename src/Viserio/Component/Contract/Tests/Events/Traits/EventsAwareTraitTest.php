<?php
declare(strict_types=1);
namespace Viserio\Component\Contract\Events\Tests\Traits;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Component\Contract\Events\EventManager as EventManagerContract;
use Viserio\Component\Contract\Events\Traits\EventManagerAwareTrait;

/**
 * @internal
 */
final class EventsAwareTraitTest extends MockeryTestCase
{
    use EventManagerAwareTrait;

    public function testGetAndsetEventManager(): void
    {
        $this->setEventManager($this->mock(EventManagerContract::class));

        $this->assertInstanceOf(EventManagerContract::class, $this->eventManager);
    }
}
