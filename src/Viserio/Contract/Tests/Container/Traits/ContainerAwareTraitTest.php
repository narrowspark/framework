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

namespace Viserio\Contract\Container\Tests\Traits;

use Narrowspark\TestingHelper\ArrayContainer;
use PHPUnit\Framework\TestCase;
use Viserio\Contract\Container\Traits\ContainerAwareTrait;

/**
 * @internal
 *
 * @small
 * @coversNothing
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
