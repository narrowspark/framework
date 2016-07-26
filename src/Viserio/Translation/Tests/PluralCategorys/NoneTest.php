<?php
declare(strict_types=1);
namespace Viserio\Translation\Tests\PluralCategorys;

use Viserio\Translation\PluralCategorys\None;

class NoneTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider category
     */
    public function testGetCategory($count, $expected)
    {
        $actual = (new None())->category($count);
        $this->assertEquals($expected, $this->intToString($actual));
    }

    public function category()
    {
        return [
            [0, 'other'],
            [301, 'other'],
            [999, 'other'],
            [1.31, 'other'],
        ];
    }

    protected function intToString($int)
    {
        switch ($int) {
            case 0:
                $actual = 'other';
                break;
        }

        return $actual;
    }
}
