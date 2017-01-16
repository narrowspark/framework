<?php
declare(strict_types=1);
namespace Viserio\Component\Translation\Tests\PluralCategorys;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Translation\PluralCategorys\Irish;

class IrishTest extends TestCase
{
    /**
     * @dataProvider category
     *
     * @param mixed $count
     * @param mixed $expected
     */
    public function testGetCategory($count, $expected)
    {
        $actual = (new Irish())->category($count);
        self::assertEquals($expected, $this->intToString($actual));
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
            [3, 'few'],
            ['3', 'few'],
            [3.0, 'few'],
            ['3.0', 'few'],
            [4, 'few'],
            [5, 'few'],
            [6, 'few'],
            [7, 'many'],
            ['7', 'many'],
            [7.0, 'many'],
            ['7.0', 'many'],
            [8, 'many'],
            [9, 'many'],
            [10, 'many'],
            [0, 'other'],
            [11, 'other'],
            [77, 'other'],
            [301, 'other'],
            [999, 'other'],
            [1.2, 'other'],
            [2.07, 'other'],
            [3.94, 'other'],
            [7.81, 'other'],
            [11.68, 'other'],
        ];
    }

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
