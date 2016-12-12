<?php
declare(strict_types=1);
namespace Viserio\Routing\Tests;

use Viserio\Routing\SortedMiddleware;

class RoutingSortedMiddlewareTest extends \PHPUnit_Framework_TestCase
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

        $this->assertEquals($expected, (new SortedMiddleware($priority, $middleware))->getAll());
        $this->assertEquals([], (new SortedMiddleware(['First'], []))->getAll());
        $this->assertEquals(['First'], (new SortedMiddleware(['First'], ['First']))->getAll());
        $this->assertEquals(['First', 'Second'], (new SortedMiddleware(['First', 'Second'], ['Second', 'First']))->getAll());

        $closure = function () {
        };
        $this->assertEquals(['Second', $closure], (new SortedMiddleware(['First', 'Second'], ['Second', $closure]))->getAll());
    }
}
