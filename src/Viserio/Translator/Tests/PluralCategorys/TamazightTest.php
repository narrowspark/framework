<?php
namespace Viserio\Translator\Tests\PluralCategorys;

use Viserio\Translator\PluralCategorys\Tamazight;

class TamazightTest extends \PHPUnit_Framework_TestCase
{
    protected $object;

    public function setUp()
    {
        $this->object = new Tamazight();
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
}
