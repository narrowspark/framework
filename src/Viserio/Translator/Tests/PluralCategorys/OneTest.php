<?php
namespace Viserio\Translator\Tests\PluralCategorys;

use Viserio\Translator\PluralCategorys\One;

class OneTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider category
     */
    public function testGetCategory($count, $expected)
    {
        $actual = (new One())->category($count);
        $this->assertEquals($expected, $this->intToString($actual));
    }

    public function category()
    {
        return [
            [1, 'one'],
            ['1', 'one'],
            [1.0, 'one'],
            ['1.0', 'one'],
            [0, 'other'],
            [10, 'other'],
            [301, 'other'],
            [999, 'other'],
            [1.31, 'other'],
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
