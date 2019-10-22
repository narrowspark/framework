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

namespace Viserio\Contract\Container\Tests\Traits;

use Narrowspark\TestingHelper\ArrayContainer;
use PHPUnit\Framework\TestCase;
use Viserio\Contract\Container\Traits\ContainerAwareTrait;

/**
 * @internal
 *
 * @small
 */
final class ContainerAwareTraitTest extends TestCase
{
    use ContainerAwareTrait;

    public function testGetAndSetContainer(): void
    {
        $this->setContainer(new ArrayContainer([]));

        self::assertNotNull($this->container);
    }
}
