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
use Psr\Cache\CacheItemPoolInterface;
use Viserio\Contract\Cache\Traits\CacheItemPoolAwareTrait;

/**
 * @internal
 *
 * @small
 * @coversNothing
 */
final class CacheItemPoolAwareTraitTest extends MockeryTestCase
{
    use CacheItemPoolAwareTrait;

    public function testGetAndSetCache(): void
    {
        $this->setCacheItemPool(Mockery::mock(CacheItemPoolInterface::class));

        self::assertNotNull($this->cacheItemPool);
    }
}
