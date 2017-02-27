<?php
declare(strict_types=1);
namespace Viserio\Component\Translation\Tests\PluralCategorys;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Translation\PluralCategorys\Slovenian;

class SlovenianTest extends TestCase
{
    /**
     * @dataProvider category
     *
     * @param mixed $count
     * @param mixed $expected
     */
    public function testGetCategory($count, $expected)
    {
        $actual = (new Slovenian())->category($count);
        self::assertEquals($expected, $this->intToString($actual));
    }

    public function category()
    {
        return [
            [1, 'one'],
            ['1', 'one'],
            [1.0, 'one'],
            ['1.0', 'one'],
            [101, 'one'],
            [201, 'one'],
            [301, 'one'],
            [401, 'one'],
            [501, 'one'],
            [2, 'two'],
            ['2', 'two'],
            [2.0, 'two'],
            ['2.0', 'two'],
            [102, 'two'],
            [202, 'two'],
            [302, 'two'],
            [402, 'two'],
            [502, 'two'],
            [3, 'few'],
            [4, 'few'],
            ['4', 'few'],
            [4.0, 'few'],
            ['4.0', 'few'],
            [103, 'few'],
            [104, 'few'],
            [203, 'few'],
            [204, 'few'],
            [0, 'other'],
            [5, 'other'],
            [6, 'other'],
            [8, 'other'],
            [10, 'other'],
            [11, 'other'],
            [29, 'other'],
            [60, 'other'],
            [99, 'other'],
            [100, 'other'],
            [105, 'other'],
            [189, 'other'],
            [200, 'other'],
            [205, 'other'],
            [300, 'other'],
            [1.31, 'other'],
            [2.31, 'other'],
            [3.31, 'other'],
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
                $actual = 'two';
                break;
            case 2:
                $actual = 'few';
                break;
            case 3:
                $actual = 'other';
                break;
        }

        return $actual;
    }
}
