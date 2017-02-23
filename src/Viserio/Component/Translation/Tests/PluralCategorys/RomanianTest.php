<?php
declare(strict_types=1);
namespace Viserio\Component\Translation\Tests\PluralCategorys;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Translation\PluralCategorys\Romanian;

class RomanianTest extends TestCase
{
    /**
     * @dataProvider category
     *
     * @param mixed $count
     * @param mixed $expected
     */
    public function testGetCategory($count, $expected)
    {
        $actual = (new Romanian())->category($count);
        self::assertEquals($expected, $this->intToString($actual));
    }

    public function category()
    {
        return [
            [1, 'one'],
            ['1', 'one'],
            [1.0, 'one'],
            ['1.0', 'one'],
            [0, 'few'],
            [2, 'few'],
            ['2', 'few'],
            [2.0, 'few'],
            ['2.0', 'few'],
            [3, 'few'],
            [5, 'few'],
            [9, 'few'],
            [10, 'few'],
            [15, 'few'],
            [18, 'few'],
            [19, 'few'],
            [101, 'few'],
            [109, 'few'],
            [110, 'few'],
            [111, 'few'],
            [117, 'few'],
            [119, 'few'],
            [201, 'few'],
            [209, 'few'],
            [210, 'few'],
            [211, 'few'],
            [217, 'few'],
            [219, 'few'],
            [20, 'other'],
            [23, 'other'],
            [35, 'other'],
            [89, 'other'],
            [99, 'other'],
            [100, 'other'],
            [120, 'other'],
            [121, 'other'],
            [200, 'other'],
            [220, 'other'],
            [300, 'other'],
            [1.31, 'one'],
            [2.31, 'few'],
            [20.31, 'other'],
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
