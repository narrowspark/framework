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
use Viserio\Component\Pagination\Adapter\ArrayAdapter;

/**
 * @internal
 *
 * @small
 */
final class ArrayAdapterTest extends TestCase
{
    public function testGetItems(): void
    {
        $adapter = new ArrayAdapter(['test'], 1);

        self::assertSame(['test'], $adapter->getItems());
    }

    public function testGetItemsPerPage(): void
    {
        $adapter = new ArrayAdapter(['test'], 1);

        self::assertSame(1, $adapter->getItemsPerPage());
    }
}
