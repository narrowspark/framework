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

namespace Viserio\Component\Routing\Tests;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Routing\SortedMiddleware;

/**
 * @internal
 *
 * @small
 * @coversNothing
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
