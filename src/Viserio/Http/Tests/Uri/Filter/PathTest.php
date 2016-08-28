<?php
declare(strict_types=1);
namespace Viserio\Http\Tests\Uri\Filter;

use Viserio\Http\Uri\Filter\Path;

class PathTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider validPathEncoding
     *
     * @param string $raw
     * @param string $parsed
     */
    public function testFilter($raw, $parsed)
    {
        $path = new Path();

        $this->assertSame($parsed, $path->filter($raw));
    }

    public function validPathEncoding()
    {
        return [
            ['toto', 'toto'],
            ['bar---', 'bar---'],
            ['', ''],
            ['"bad"', '%22bad%22'],
            ['<not good>', '%3Cnot%20good%3E'],
            ['{broken}', '%7Bbroken%7D'],
            ['`oops`', '%60oops%60'],
            ['\\slashy', '%5Cslashy'],
            ['%7Etoto', '~toto'],
            ['%7etoto', '~toto'],
            ['foo^bar', 'foo%5Ebar'],
            ['foo^bar/baz', 'foo%5Ebar/baz'],
            ['to?to', 'to%3Fto'],
            ['to#to', 'to%23to'],
            ['/a/b/c/./../../g', '/a/g'],
            ['mid/content=5/../6', 'mid/6'],
            ['a/b/c', 'a/b/c'],
            ['a/b/c/.', 'a/b/c/'],
            ['/a/b/c', '/a/b/c'],
        ];
    }
}
