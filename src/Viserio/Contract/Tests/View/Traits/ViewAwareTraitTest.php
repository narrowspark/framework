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

namespace Viserio\Contract\View\Tests\Traits;

use Mockery;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Contract\View\Factory as ViewFactoryContract;
use Viserio\Contract\View\Traits\ViewAwareTrait;

/**
 * @internal
 *
 * @small
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
