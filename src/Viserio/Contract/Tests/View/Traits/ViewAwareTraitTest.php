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

namespace Viserio\Contract\View\Tests\Traits;

use Mockery;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Contract\View\Factory as ViewFactoryContract;
use Viserio\Contract\View\Traits\ViewAwareTrait;

/**
 * @internal
 *
 * @small
 * @coversNothing
 */
final class ViewAwareTraitTest extends MockeryTestCase
{
    use ViewAwareTrait;

    public function testGetAndSetViewFactory(): void
    {
        $this->setViewFactory(Mockery::mock(ViewFactoryContract::class));

        self::assertNotNull($this->viewFactory);
    }
}
