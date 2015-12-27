<?php
namespace Viserio\Translator\Tests\Traits;

use Viserio\Translator\Traits\NormalizeIntegerValueTrait;

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
            [1, 1],
            [2, 2],
            [1.0, 1],
            ['1', 1],
            ['1.0', 1],
            [1.1, 1.1],
            ['1.1', 1.1],
            [100.432, 100.432]
        ];
    }
}
