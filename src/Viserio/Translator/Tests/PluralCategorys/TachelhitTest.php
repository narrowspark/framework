<?php
namespace Viserio\Translator\Tests\PluralCategorys;

use Viserio\Translator\PluralCategorys\Tachelhit;

class TachelhitTest extends \PHPUnit_Framework_TestCase
{
    protected $object;

    public function setUp()
    {
        parent::setUp();
        $this->object = new Tachelhit();
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
            ['0', 'one'],
            [0.0, 'one'],
            ['0.0', 'one'],
            [1, 'one'],
            [2, 'few'],
            [3, 'few'],
            [5, 'few'],
            [8, 'few'],
            [10, 'few'],
            ['10', 'few'],
            [10.0, 'few'],
            ['10.0', 'few'],
            [11, 'other'],
            [19, 'other'],
            [69, 'other'],
            [198, 'other'],
            [384, 'other'],
            [999, 'other'],
            [1.31, 'other'],
            [2.31, 'other'],
            [11.31, 'other'],
        ];
    }
}
