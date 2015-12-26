<?php
namespace Viserio\Translator\Tests\PluralCategorys;

use Viserio\Translator\PluralCategorys\Latvian;

class LatvianTest extends \PHPUnit_Framework_TestCase
{
    protected $object;

    public function setUp()
    {
        $this->object = new Latvian();
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
            [21, 'one'],
            [31, 'one'],
            [41, 'one'],
            [51, 'one'],
            [101, 'one'],
            [2, 'other'],
            [3, 'other'],
            [8, 'other'],
            [10, 'other'],
            [11, 'other'],
            [15, 'other'],
            [19, 'other'],
            [20, 'other'],
            [22, 'other'],
            [30, 'other'],
            [32, 'other'],
            [40, 'other'],
            [111, 'other'],
            [0.31, 'other'],
            [1.31, 'other'],
            [1.99, 'other'],
        ];
    }
}
