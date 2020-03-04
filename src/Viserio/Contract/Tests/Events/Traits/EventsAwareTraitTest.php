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

namespace Viserio\Contract\Events\Tests\Traits;

use Mockery;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Contract\Events\EventManager as EventManagerContract;
use Viserio\Contract\Events\Traits\EventManagerAwareTrait;

/**
 * @internal
 *
 * @small
 * @coversNothing
 */
final class EventsAwareTraitTest extends MockeryTestCase
{
    use EventManagerAwareTrait;

    public function testGetAndsetEventManager(): void
    {
        $this->setEventManager(Mockery::mock(EventManagerContract::class));

        self::assertNotNull($this->eventManager);
    }
}
