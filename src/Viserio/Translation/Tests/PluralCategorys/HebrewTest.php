<?php
declare(strict_types=1);
namespace Viserio\Translation\Tests\PluralCategorys;

use Viserio\Translation\PluralCategorys\Hebrew;

class HebrewTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider category
     */
    public function testGetCategory($count, $expected)
    {
        $actual = (new Hebrew())->category($count);
        $this->assertEquals($expected, $this->intToString($actual));
    }

    public function category()
    {
        return [
            [1, 'one'],
            ['1', 'one'],
            [1.0, 'one'],
            ['1.0', 'one'],
            [2, 'two'],
            ['2', 'two'],
            [2.0, 'two'],
            ['2.0', 'two'],
            [10, 'many'],
            ['10', 'many'],
            [10.0, 'many'],
            ['10.0', 'many'],
            [20, 'many'],
            [100, 'many'],
            [0, 'other'],
            [3, 'other'],
            [9, 'other'],
            [77, 'other'],
            [301, 'other'],
            [999, 'other'],
            [1.31, 'other'],
            [5.45, 'other'],
        ];
    }

    /**
     * @param integer $int
     */
    protected function intToString($int)
    {
        switch ($int) {
            case 0:
                $actual = 'one';
                break;
            case 1:
                $actual = 'two';
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
