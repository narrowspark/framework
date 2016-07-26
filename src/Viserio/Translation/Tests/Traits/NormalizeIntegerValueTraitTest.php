<?php
declare(strict_types=1);
namespace Viserio\Translation\Tests\Traits;

use Viserio\Translation\Traits\NormalizeIntegerValueTrait;

class NormalizeIntegerValueTraitTest extends \PHPUnit_Framework_TestCase
{
    use NormalizeIntegerValueTrait;

    /**
     * @dataProvider provideIsInteger
     */
    public function testNormalizeInteger($value, $expected)
    {
        $actual = $this->normalizeInteger($value);
        $this->assertSame($expected, $actual);
    }

    public function provideIsInteger()
    {
        return [
            [0, 0],
            [0.0, 0],
            ['0.0', 0],
            [1, 1],
            [2, 2],
            [1.0, 1],
            ['1', 1],
            [' 1 ', 1],
            ['1.0', 1],
            [1.1, 1.1],
            ['1.1', 1.1],
            [14.31, 14.31],
            ['14.31', 14.31],
            [100.432, 100.432],
        ];
    }
}
