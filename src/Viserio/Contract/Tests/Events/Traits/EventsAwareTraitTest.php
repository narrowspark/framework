<?php

declare(strict_types=1);

/**
 * This file is part of Narrowspark Framework.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Viserio\Contract\Events\Tests\Traits;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Contract\Events\EventManager as EventManagerContract;
use Viserio\Contract\Events\Traits\EventManagerAwareTrait;

/**
 * @internal
 *
 * @small
 */
final class EventsAwareTraitTest extends MockeryTestCase
{
    use EventManagerAwareTrait;

    public function testGetAndsetEventManager(): void
    {
        $this->setEventManager(\Mockery::mock(EventManagerContract::class));

        self::assertNotNull($this->eventManager);
    }
}
