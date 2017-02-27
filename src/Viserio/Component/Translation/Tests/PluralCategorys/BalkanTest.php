<?php
declare(strict_types=1);
namespace Viserio\Component\Translation\Tests\PluralCategorys;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Translation\PluralCategorys\Balkan;

class BalkanTest extends TestCase
{
    /**
     * @dataProvider category
     *
     * @param mixed $count
     * @param mixed $expected
     */
    public function testGetCategory($count, $expected)
    {
        $actual = (new Balkan())->category($count);
        self::assertEquals($expected, $this->intToString($actual));
    }

    public function category()
    {
        return [
            [1, 'one'],
            ['1', 'one'],
            [1.0, 'one'],
            ['1.0', 'one'],
            [21, 'one'],
            [31, 'one'],
            [41, 'one'],
            [51, 'one'],
            [61, 'one'],
            [2, 'few'],
            ['2', 'few'],
            [2.0, 'few'],
            ['2.0', 'few'],
            [3, 'few'],
            [4, 'few'],
            [22, 'few'],
            [23, 'few'],
            [24, 'few'],
            [142, 'few'],
            [143, 'few'],
            [144, 'few'],
            [0, 'many'],
            [5, 'many'],
            ['5', 'many'],
            [5.0, 'many'],
            ['5.0', 'many'],
            [6, 'many'],
            [7, 'many'],
            [11, 'many'],
            [12, 'many'],
            [15, 'many'],
            [20, 'many'],
            [25, 'many'],
            [26, 'many'],
            [28, 'many'],
            [29, 'many'],
            [30, 'many'],
            [65, 'many'],
            [66, 'many'],
            [68, 'many'],
            [69, 'many'],
            [70, 'many'],
            [112, 'many'],
            [1112, 'many'],
            [1.31, 'other'],
            [2.31, 'other'],
            [5.31, 'other'],
        ];
    }

    /**
     * @param int $int
     */
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
                $actual = 'many';
                break;
            case 3:
                $actual = 'other';
                break;
        }

        return $actual;
    }
}
