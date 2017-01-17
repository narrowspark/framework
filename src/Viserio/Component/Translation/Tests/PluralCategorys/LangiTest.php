<?php
declare(strict_types=1);
namespace Viserio\Component\Translation\Tests\PluralCategorys;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Translation\PluralCategorys\Langi;

class LangiTest extends TestCase
{
    /**
     * @dataProvider category
     *
     * @param mixed $count
     * @param mixed $expected
     */
    public function testGetCategory($count, $expected)
    {
        $actual = (new Langi())->category($count);
        self::assertEquals($expected, $this->intToString($actual));
    }

    public function category()
    {
        return [
            [0, 'zero'],
            [0.0, 'zero'],
            [0.01, 'one'],
            [0.51, 'one'],
            ['0.71', 'one'],
            [1, 'one'],
            [1.0, 'one'],
            [1.31, 'one'],
            [1.88, 'one'],
            ['1.99', 'one'],
            [2, 'other'],
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
            [2.31, 'other'],
        ];
    }

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
