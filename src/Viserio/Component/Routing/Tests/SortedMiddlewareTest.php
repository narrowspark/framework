<?php
declare(strict_types=1);
namespace Viserio\Component\Routing\Tests;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Routing\SortedMiddleware;

class SortedMiddlewareTest extends TestCase
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

        $closure = function (): void {
        };
        self::assertEquals(['Second', $closure], (new SortedMiddleware(['First', 'Second'], ['Second', $closure]))->getAll());
    }
}
