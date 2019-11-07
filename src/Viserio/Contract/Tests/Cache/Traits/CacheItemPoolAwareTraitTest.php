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

namespace Viserio\Contract\Cache\Tests\Traits;

use Mockery;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Psr\Cache\CacheItemPoolInterface;
use Viserio\Contract\Cache\Traits\CacheItemPoolAwareTrait;

/**
 * @internal
 *
 * @small
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
