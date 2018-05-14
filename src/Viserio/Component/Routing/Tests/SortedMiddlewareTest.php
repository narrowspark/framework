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

namespace Viserio\Component\Routing\Tests;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Routing\SortedMiddleware;

/**
 * @internal
 *
 * @small
 */
final class SortedMiddlewareTest extends TestCase
{
    public function testMiddlewareCanBeSortedByPriority(): void
    {
        $priority = [
            'First',
            'Second',
            'Third',
        ];

        $middleware = [
            'Something',
            'Something',
            'Something',
            'Something',
            'Second',
            'Otherthing',
            'First',
            'Third',
            'Second',
        ];

        $expected = [
            'Something',
            'First',
            'Second',
            'Otherthing',
            'Third',
        ];

        self::assertEquals($expected, (new SortedMiddleware($priority, $middleware))->getAll());
        self::assertEquals([], (new SortedMiddleware(['First'], []))->getAll());
        self::assertEquals(['First'], (new SortedMiddleware(['First'], ['First']))->getAll());
        self::assertEquals(['First', 'Second'], (new SortedMiddleware(['First', 'Second'], ['Second', 'First']))->getAll());

        $closure = static function (): void {
        };
        self::assertEquals(['Second', $closure], (new SortedMiddleware(['First', 'Second'], ['Second', $closure]))->getAll());
    }
}
