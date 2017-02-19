<?php
declare(strict_types=1);
namespace Viserio\Component\Translation\Tests\PluralCategorys;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Translation\PluralCategorys\Arabic;

class ArabicTest extends TestCase
{
    /**
     * @dataProvider category
     *
     * @param mixed $count
     * @param mixed $expected
     */
    public function testGetCategory($count, $expected)
    {
        $actual = (new Arabic())->category($count);
        self::assertEquals($expected, $this->intToString($actual));
    }

    public function category()
    {
        return [
            [0, 'zero'],
            ['0', 'zero'],
            [0.0, 'zero'],
            ['0.0', 'zero'],
            [1, 'one'],
            ['1', 'one'],
            [1.0, 'one'],
            ['1.0', 'one'],
            [2, 'two'],
            [3, 'few'],
            [4, 'few'],
            [6, 'few'],
            [8, 'few'],
            [10, 'few'],
            [103, 'few'],
            ['103', 'few'],
            [103.0, 'few'],
            ['103.0', 'few'],
            [104, 'few'],
            [106, 'few'],
            [108, 'few'],
            [110, 'few'],
            [203, 'few'],
            [204, 'few'],
            [206, 'few'],
            [208, 'few'],
            [210, 'few'],
            [11, 'many'],
            ['11', 'many'],
            [11.0, 'many'],
            [15, 'many'],
            [25, 'many'],
            [36, 'many'],
            [47, 'many'],
            [58, 'many'],
            [69, 'many'],
            [71, 'many'],
            [82, 'many'],
            [93, 'many'],
            [99, 'many'],
            [111, 'many'],
            [115, 'many'],
            [125, 'many'],
            [136, 'many'],
            [147, 'many'],
            [158, 'many'],
            [169, 'many'],
            [171, 'many'],
            [182, 'many'],
            [193, 'many'],
            [199, 'many'],
            [211, 'many'],
            [215, 'many'],
            [225, 'many'],
            [236, 'many'],
            [247, 'many'],
            [258, 'many'],
            [269, 'many'],
            [271, 'many'],
            [282, 'many'],
            [293, 'many'],
            [299, 'many'],
            [100, 'other'],
            [101, 'other'],
            [102, 'other'],
            [200, 'other'],
            [201, 'other'],
            [202, 'other'],
            [300, 'other'],
            [301, 'other'],
            [302, 'other'],
            [0.31, 'other'],
            [1.31, 'other'],
            [2.31, 'other'],
            [3.31, 'other'],
            [11.31, 'other'],
            [100.31, 'other'],
            [103.1, 'other'],
        ];
    }

    /**
     * @param int $int
     */
    protected function intToString($int)
    {
        switch ($int) {
            case 0:
                $actual = 'zero';
                break;
            case 1:
                $actual = 'one';
                break;
            case 2:
                $actual = 'two';
                break;
            case 3:
                $actual = 'few';
                break;
            case 4:
                $actual = 'many';
                break;
            case 5:
                $actual = 'other';
                break;
        }

        return $actual;
    }
}
