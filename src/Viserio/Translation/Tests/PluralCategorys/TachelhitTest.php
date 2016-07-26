<?php
declare(strict_types=1);
namespace Viserio\Translation\Tests\PluralCategorys;

use Viserio\Translation\PluralCategorys\Tachelhit;

class TachelhitTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider category
     */
    public function testGetCategory($count, $expected)
    {
        $actual = (new Tachelhit())->category($count);
        $this->assertEquals($expected, $this->intToString($actual));
    }

    public function category()
    {
        return [
            [0, 'one'],
            ['0', 'one'],
            [0.0, 'one'],
            ['0.0', 'one'],
            [1, 'one'],
            [2, 'few'],
            [3, 'few'],
            [5, 'few'],
            [8, 'few'],
            [10, 'few'],
            ['10', 'few'],
            [10.0, 'few'],
            ['10.0', 'few'],
            [11, 'other'],
            [19, 'other'],
            [69, 'other'],
            [198, 'other'],
            [384, 'other'],
            [999, 'other'],
            [1.31, 'other'],
            [2.31, 'other'],
            [11.31, 'other'],
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
