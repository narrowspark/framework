<?php
declare(strict_types=1);
namespace Viserio\Component\Translation\Tests\PluralCategorys;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Translation\PluralCategorys\Manx;

class ManxTest extends TestCase
{
    /**
     * @dataProvider category
     *
     * @param mixed $count
     * @param mixed $expected
     */
    public function testGetCategory($count, $expected)
    {
        $actual = (new Manx())->category($count);
        self::assertEquals($expected, $this->intToString($actual));
    }

    public function category()
    {
        return [
            [0, 'one'],
            ['0', 'one'],
            [0.0, 'one'],
            ['0.0', 'one'],
            [1, 'one'],
            [2, 'one'],
            [11, 'one'],
            [12, 'one'],
            [20, 'one'],
            [21, 'one'],
            [22, 'one'],
            [3, 'other'],
            [4, 'other'],
            [5, 'other'],
            [6, 'other'],
            [7, 'other'],
            [8, 'other'],
            [9, 'other'],
            [10, 'other'],
            [13, 'other'],
            [14, 'other'],
            [15, 'other'],
            [16, 'other'],
            [17, 'other'],
            [18, 'other'],
            [19, 'other'],
            [23, 'other'],
            [25, 'other'],
            [29, 'other'],
            [30, 'other'],
            [0.31, 'other'],
            [1.2, 'other'],
            [2.07, 'other'],
            [3.31, 'other'],
            [11.31, 'other'],
            [21.11, 'other'],
            [100.31, 'other'],
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
                $actual = 'other';
                break;
        }

        return $actual;
    }
}
