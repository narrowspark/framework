<?php
declare(strict_types=1);
namespace Viserio\Component\Routing\Tests;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Routing\SortedMiddleware;

/**
 * @internal
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

        static::assertEquals($expected, (new SortedMiddleware($priority, $middleware))->getAll());
        static::assertEquals([], (new SortedMiddleware(['First'], []))->getAll());
        static::assertEquals(['First'], (new SortedMiddleware(['First'], ['First']))->getAll());
        static::assertEquals(['First', 'Second'], (new SortedMiddleware(['First', 'Second'], ['Second', 'First']))->getAll());

        $closure = function (): void {
        };
        static::assertEquals(['Second', $closure], (new SortedMiddleware(['First', 'Second'], ['Second', $closure]))->getAll());
    }
}
