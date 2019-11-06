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

namespace Viserio\Contract\Parser\Tests\Traits;

use Mockery;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Contract\Parser\Loader as LoaderContract;
use Viserio\Contract\Parser\Traits\ParserAwareTrait;

/**
 * @internal
 *
 * @small
 */
final class LoaderAwareTraitTest extends MockeryTestCase
{
    use ParserAwareTrait;

    public function testGetAndSetLoader(): void
    {
        $this->setLoader(Mockery::mock(LoaderContract::class));

        self::assertNotNull($this->loader);
    }
}
