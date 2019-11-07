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
use Viserio\Contract\Cache\Manager;
use Viserio\Contract\Cache\Traits\CacheManagerAwareTrait;

/**
 * @internal
 *
 * @small
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
