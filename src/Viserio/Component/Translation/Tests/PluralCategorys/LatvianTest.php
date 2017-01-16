<?php
declare(strict_types=1);
namespace Viserio\Component\Translation\Tests\PluralCategorys;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Translation\PluralCategorys\Latvian;

class LatvianTest extends TestCase
{
    /**
     * @dataProvider category
     *
     * @param mixed $count
     * @param mixed $expected
     */
    public function testGetCategory($count, $expected)
    {
        $actual = (new Latvian())->category($count);
        self::assertEquals($expected, $this->intToString($actual));
    }

    public function category()
    {
        return [
            [0, 'zero'],
            ['0', 'zero'],
            [0.0, 'zero'],
            ['0.0', 'zero'],
            [1, 'one'],
            ['1', 'one'],
            [1.0, 'one'],
            ['1.0', 'one'],
            [21, 'one'],
            [31, 'one'],
            [41, 'one'],
            [51, 'one'],
            [101, 'one'],
            [2, 'other'],
            [3, 'other'],
            [8, 'other'],
            [10, 'other'],
            [11, 'other'],
            [15, 'other'],
            [19, 'other'],
            [20, 'other'],
            [22, 'other'],
            [30, 'other'],
            [32, 'other'],
            [40, 'other'],
            [111, 'other'],
            [0.31, 'other'],
            [1.31, 'other'],
            [1.99, 'other'],
        ];
    }

    protected function intToString($int)
    {
        switch ($int) {
            case 0:
                $actual = 'zero';
                break;
            case 1:
                $actual = 'one';
                break;
            case 2:
                $actual = 'other';
                break;
        }

        return $actual;
    }
}
