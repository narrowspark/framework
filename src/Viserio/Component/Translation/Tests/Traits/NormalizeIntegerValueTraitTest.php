<?php
declare(strict_types=1);
namespace Viserio\Component\Translation\Tests\Traits;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Translation\Traits\NormalizeIntegerValueTrait;

class NormalizeIntegerValueTraitTest extends TestCase
{
    use NormalizeIntegerValueTrait;

    /**
     * @dataProvider provideIsInteger
     *
     * @param mixed $value
     * @param mixed $expected
     */
    public function testNormalizeInteger($value, $expected)
    {
        $actual = $this->normalizeInteger($value);
        self::assertSame($expected, $actual);
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
