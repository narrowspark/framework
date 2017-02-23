<?php
declare(strict_types=1);
namespace Viserio\Component\Translation\Tests\PluralCategorys;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Translation\PluralCategorys\Breton;

class BretonTest extends TestCase
{
    /**
     * @dataProvider category
     *
     * @param mixed $count
     * @param mixed $expected
     */
    public function testGetCategory($count, $expected)
    {
        $actual = (new Breton())->category($count);
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
            [2, 'two'],
            ['2', 'two'],
            [2.0, 'two'],
            ['2.0', 'two'],
            [22, 'two'],
            [32, 'two'],
            [42, 'two'],
            [52, 'two'],
            [62, 'two'],
            [3, 'few'],
            ['3', 'few'],
            [3.0, 'few'],
            ['3.0', 'few'],
            [4, 'few'],
            [9, 'few'],
            [23, 'few'],
            [24, 'few'],
            [29, 'few'],
            [43, 'few'],
            [44, 'few'],
            [49, 'few'],
            [53, 'few'],
            [54, 'few'],
            [59, 'few'],
            [1000000, 'many'],
            [2000000, 'many'],
            [3000000.0, 'many'],
            ['4000000', 'many'],
            ['5000000.0', 'many'],
            [0, 'other'],
            [5, 'other'],
            [6, 'other'],
            [7, 'other'],
            [8, 'other'],
            [10, 'other'],
            [11, 'other'],
            [12, 'other'],
            [13, 'other'],
            [14, 'other'],
            [15, 'other'],
            [16, 'other'],
            [17, 'other'],
            [18, 'other'],
            [19, 'other'],
            [20, 'other'],
            [25, 'other'],
            [26, 'other'],
            [27, 'other'],
            [28, 'other'],
            [73, 'other'],
            [74, 'other'],
            [79, 'other'],
            [93, 'other'],
            [94, 'other'],
            [99, 'other'],
            [1.2, 'other'],
            [2.07, 'other'],
            [3.94, 'other'],
            [5.81, 'other'],
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
                $actual = 'two';
                break;
            case 2:
                $actual = 'few';
                break;
            case 3:
                $actual = 'many';
                break;
            case 4:
                $actual = 'other';
                break;
        }

        return $actual;
    }
}
