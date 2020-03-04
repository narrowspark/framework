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

namespace Viserio\Contract\Parser\Tests\Traits;

use Mockery;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Contract\Parser\Loader as LoaderContract;
use Viserio\Contract\Parser\Traits\ParserAwareTrait;

/**
 * @internal
 *
 * @small
 * @coversNothing
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
