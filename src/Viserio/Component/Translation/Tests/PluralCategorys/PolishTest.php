<?php
declare(strict_types=1);
namespace Viserio\Component\Translation\Tests\PluralCategorys;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Translation\PluralCategorys\Polish;

class PolishTest extends TestCase
{
    /**
     * @dataProvider category
     *
     * @param mixed $count
     * @param mixed $expected
     */
    public function testGetCategory($count, $expected)
    {
        $actual = (new Polish())->category($count);
        self::assertEquals($expected, $this->intToString($actual));
    }

    public function category()
    {
        return [
            [1, 'one'],
            ['1', 'one'],
            [1.0, 'one'],
            ['1.0', 'one'],
            [2, 'few'],
            ['2', 'few'],
            [2.0, 'few'],
            ['2.0', 'few'],
            [3, 'few'],
            [4, 'few'],
            [32, 'few'],
            [33, 'few'],
            [34, 'few'],
            [102, 'few'],
            [103, 'few'],
            [104, 'few'],
            [0, 'other'],
            [5, 'other'],
            [6, 'other'],
            [10, 'other'],
            [12, 'other'],
            [15, 'other'],
            [20, 'other'],
            [21, 'other'],
            [22, 'other'],
            [23, 'other'],
            [24, 'other'],
            [25, 'other'],
            [30, 'other'],
            [31, 'other'],
            [47, 'other'],
            [1.31, 'other'],
            [2.31, 'other'],
            [5.31, 'other'],
        ];
    }

    protected function intToString($int)
    {
        $actual = '';

        switch ($int) {
            case 0:
                $actual = 'one';
                break;
            case 1:
                $actual = 'few';
                break;
            case 2:
                $actual = 'other';
                break;
        }

        return $actual;
    }
}
