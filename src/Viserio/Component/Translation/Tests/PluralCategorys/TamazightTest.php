<?php
declare(strict_types=1);
namespace Viserio\Component\Translation\Tests\PluralCategorys;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Translation\PluralCategorys\Tamazight;

class TamazightTest extends TestCase
{
    /**
     * @dataProvider category
     *
     * @param mixed $count
     * @param mixed $expected
     */
    public function testGetCategory($count, $expected)
    {
        $actual = (new Tamazight())->category($count);
        self::assertEquals($expected, $this->intToString($actual));
    }

    public function category()
    {
        return [
            [0, 'one'],
            [1, 'one'],
            ['1', 'one'],
            [1.0, 'one'],
            ['1.0', 'one'],
            [11, 'one'],
            [12, 'one'],
            [19, 'one'],
            [20, 'one'],
            [21, 'one'],
            [32, 'one'],
            [43, 'one'],
            [54, 'one'],
            [65, 'one'],
            [76, 'one'],
            [87, 'one'],
            [98, 'one'],
            [99, 'one'],
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
            [1.2, 'other'],
            [2.07, 'other'],
            [3.31, 'other'],
            [11.31, 'one'],
            [100.31, 'other'],
        ];
    }

    protected function intToString($int)
    {
        switch ($int) {
            case 0:
                $actual = 'one';
                break;
            case 1:
                $actual = 'other';
                break;
        }

        return $actual;
    }
}
