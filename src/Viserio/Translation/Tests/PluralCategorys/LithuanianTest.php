<?php
declare(strict_types=1);
namespace Viserio\Translation\Tests\PluralCategorys;

use Viserio\Translation\PluralCategorys\Lithuanian;

class LithuanianTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider category
     */
    public function testGetCategory($count, $expected)
    {
        $actual = (new Lithuanian())->category($count);
        $this->assertEquals($expected, $this->intToString($actual));
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
            [101, 'one'],
            [2, 'few'],
            ['2', 'few'],
            [2.0, 'few'],
            ['2.0', 'few'],
            [3, 'few'],
            [5, 'few'],
            [7, 'few'],
            [9, 'few'],
            [22, 'few'],
            [23, 'few'],
            [25, 'few'],
            [27, 'few'],
            [29, 'few'],
            [32, 'few'],
            [33, 'few'],
            [35, 'few'],
            [37, 'few'],
            [39, 'few'],
            [0, 'other'],
            [10, 'other'],
            [11, 'other'],
            [12, 'other'],
            [13, 'other'],
            [15, 'other'],
            [18, 'other'],
            [20, 'other'],
            [30, 'other'],
            [40, 'other'],
            [50, 'other'],
            [0.31, 'other'],
            [1.31, 'other'],
            [1.99, 'other'],
        ];
    }

    protected function intToString($int)
    {
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
