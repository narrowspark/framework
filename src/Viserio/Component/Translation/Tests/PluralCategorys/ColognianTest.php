<?php
declare(strict_types=1);
namespace Viserio\Component\Translation\Tests\PluralCategorys;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Translation\PluralCategorys\Colognian;

class ColognianTest extends TestCase
{
    /**
     * @dataProvider category
     *
     * @param mixed $count
     * @param mixed $expected
     */
    public function testGetCategory($count, $expected)
    {
        $actual = (new Colognian())->category($count);
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
            [2, 'other'],
            [3, 'other'],
            [4, 'other'],
            [5, 'other'],
            [6, 'other'],
            [7, 'other'],
            [9, 'other'],
            [10, 'other'],
            [12, 'other'],
            [14, 'other'],
            [19, 'other'],
            [69, 'other'],
            [198, 'other'],
            [384, 'other'],
            [999, 'other'],
            [0.2, 'other'],
            [1.07, 'other'],
            [2.94, 'other'],
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
                $actual = 'other';
                break;
        }

        return $actual;
    }
}
