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

namespace Viserio\Contract\Cache\Tests\Traits;

use Mockery;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Contract\Cache\Manager;
use Viserio\Contract\Cache\Traits\CacheManagerAwareTrait;

/**
 * @internal
 *
 * @small
 * @coversNothing
 */
final class CacheManagerAwareTraitTest extends MockeryTestCase
{
    use CacheManagerAwareTrait;

    public function testGetAndSetCache(): void
    {
        $this->setCacheManager(Mockery::mock(Manager::class));

        self::assertNotNull($this->cacheManager);
    }
}
