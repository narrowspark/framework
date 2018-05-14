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

namespace Viserio\Component\Pagination\Tests\Adapter;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Pagination\Adapter\NullAdapter;

/**
 * @internal
 *
 * @small
 */
final class NullAdapterTest extends TestCase
{
    public function testGetItems(): void
    {
        $adapter = new NullAdapter();

        self::assertSame([], $adapter->getItems());
    }

    public function testGetItemsPerPage(): void
    {
        $adapter = new NullAdapter();

        self::assertSame(0, $adapter->getItemsPerPage());
    }
}
