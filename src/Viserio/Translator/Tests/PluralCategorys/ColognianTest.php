<?php
namespace Viserio\Translator\Tests\PluralCategorys;

use Viserio\Translator\PluralCategorys\Colognian;

class ColognianTest extends \PHPUnit_Framework_TestCase
{
    protected $object;

    public function setUp()
    {
        $this->object = new Colognian();
    }

    /**
     * @dataProvider category
     */
    public function testGetCategory($count, $expected)
    {
        $actual = $this->object->category($count);
        $this->assertEquals($expected, $actual);
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
}
