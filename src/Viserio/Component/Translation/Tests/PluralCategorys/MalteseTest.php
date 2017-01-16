<?php
declare(strict_types=1);
namespace Viserio\Component\Translation\Tests\PluralCategorys;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Translation\PluralCategorys\Maltese;

class MalteseTest extends TestCase
{
    /**
     * @dataProvider category
     *
     * @param mixed $count
     * @param mixed $expected
     */
    public function testGetCategory($count, $expected)
    {
        $actual = (new Maltese())->category($count);
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
            ['0', 'few'],
            [0.0, 'few'],
            ['0.0', 'few'],
            [2, 'few'],
            [3, 'few'],
            [5, 'few'],
            [7, 'few'],
            [9, 'few'],
            [10, 'few'],
            [102, 'few'],
            [103, 'few'],
            [105, 'few'],
            [107, 'few'],
            [109, 'few'],
            [110, 'few'],
            [202, 'few'],
            [203, 'few'],
            [205, 'few'],
            [207, 'few'],
            [209, 'few'],
            [210, 'few'],
            [11, 'many'],
            ['11', 'many'],
            [11.0, 'many'],
            ['11.0', 'many'],
            [12, 'many'],
            [13, 'many'],
            [14, 'many'],
            [16, 'many'],
            [19, 'many'],
            [111, 'many'],
            [112, 'many'],
            [113, 'many'],
            [114, 'many'],
            [116, 'many'],
            [119, 'many'],
            [20, 'other'],
            [21, 'other'],
            [32, 'other'],
            [43, 'other'],
            [83, 'other'],
            [99, 'other'],
            [100, 'other'],
            [101, 'other'],
            [120, 'other'],
            [200, 'other'],
            [201, 'other'],
            [220, 'other'],
            [301, 'other'],
            [1.31, 'other'],
            [2.31, 'other'],
            [11.31, 'other'],
            [20.31, 'other'],
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
                $actual = 'many';
                break;
            case 3:
                $actual = 'other';
                break;
        }

        return $actual;
    }
}
