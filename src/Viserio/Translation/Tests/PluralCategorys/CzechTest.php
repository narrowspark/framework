<?php
declare(strict_types=1);
namespace Viserio\Translation\Tests\PluralCategorys;

use Viserio\Translation\PluralCategorys\Czech;

class CzechTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider category
     */
    public function testGetCategory($count, $expected)
    {
        $actual = (new Czech())->category($count);
        $this->assertEquals($expected, $this->intToString($actual));
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
            [0, 'other'],
            [5, 'other'],
            [7, 'other'],
            [17, 'other'],
            [28, 'other'],
            [39, 'other'],
            [40, 'other'],
            [51, 'other'],
            [63, 'other'],
            [111, 'other'],
            [597, 'other'],
            [846, 'other'],
            [999, 'other'],
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
