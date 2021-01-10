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
use Viserio\Component\Pagination\Adapter\NullAdapter;

/**
 * @internal
 *
 * @small
 * @coversNothing
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
