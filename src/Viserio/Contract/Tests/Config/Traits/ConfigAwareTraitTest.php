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

namespace Viserio\Contract\Config\Tests\Traits;

use Mockery;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Contract\Config\Repository as RepositoryContract;
use Viserio\Contract\Config\Traits\ConfigAwareTrait;

/**
 * @internal
 *
 * @small
 */
final class ConfigAwareTraitTest extends MockeryTestCase
{
    use ConfigAwareTrait;

    public function testGetAndSetConfig(): void
    {
        $this->setConfig(Mockery::mock(RepositoryContract::class));

        self::assertNotNull($this->config);
    }
}
