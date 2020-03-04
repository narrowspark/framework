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

namespace Viserio\Component\Pagination\Tests\Adapter;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Pagination\Adapter\ArrayAdapter;

/**
 * @internal
 *
 * @small
 * @coversNothing
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
