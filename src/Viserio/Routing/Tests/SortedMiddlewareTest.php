<?php
declare(strict_types=1);
namespace Viserio\Routing\Tests;

use Viserio\Routing\SortedMiddleware;
use PHPUnit\Framework\TestCase;

class SortedMiddlewareTest extends TestCase
{
    public function testMiddlewareCanBeSortedByPriority()
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

        $closure = function () {
        };
        static::assertEquals(['Second', $closure], (new SortedMiddleware(['First', 'Second'], ['Second', $closure]))->getAll());
    }
}
